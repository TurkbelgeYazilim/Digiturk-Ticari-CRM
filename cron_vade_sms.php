<?php
/**
 * Vade Hatırlatma SMS Cron Job - Standalone
 * 
 * Bu dosya Plesk Cron Job tarafından doğrudan çalıştırılır
 * Plesk → Görev Planlayıcı → Run a PHP Script
 * 
 * Zamanlama: Her gün 09:00
 * 
 * @author İlekaSoft CRM
 * @date 2025-11-18
 */

// BASEPATH tanımla
define('BASEPATH', true);

// Zaman aşımı sınırını kaldır
set_time_limit(0);
ini_set('max_execution_time', 0);

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log dosyası
$log_file = __DIR__ . '/logs/vade_sms_cron_' . date('Y-m-d') . '.log';
$log_dir = dirname($log_file);
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

/**
 * Log fonksiyonu
 */
function log_message($message, $type = 'INFO') {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry;
}

try {
    log_message('=== VADE HATIRLATMA SMS CRON JOB BAŞLADI ===');
    
    // Database bağlantısı
    require_once(__DIR__ . '/application/config/database.php');
    
    $conn = new mysqli(
        $db['default']['hostname'],
        $db['default']['username'],
        $db['default']['password'],
        $db['default']['database']
    );
    
    if ($conn->connect_error) {
        throw new Exception('Database bağlantı hatası: ' . $conn->connect_error);
    }
    
    $conn->set_charset('utf8mb4');
    log_message('Database bağlantısı başarılı');
    
    // SMS Config yükle
    require_once(__DIR__ . '/application/config/sms.php');
    log_message('SMS konfigürasyonu yüklendi');
    
    // SMS şablonunu yükle
    $template_file = __DIR__ . '/application/config/sms_template.php';
    if (file_exists($template_file)) {
        require($template_file);
        $sms_template = $config['sms_sablonu'];
        log_message('SMS şablonu config dosyasından yüklendi');
    } else {
        // Varsayılan şablon
        $sms_template = "Sayin Musterimiz,\n\n[ODEME_TURU] odemenizin vade tarihi [VADE_TARIHI] gunudur.\n\nKonuyla ilgili detayli bilgi ve destek icin 0552 173 10 37 numarali telefondan Burcu Hanim ile iletisime gecebilirsiniz.\n\nBilgilerinize sunar, iyi gunler dileriz.";
        log_message('Varsayılan SMS şablonu kullanılıyor', 'WARNING');
    }
    
    // Vade tarihi yaklaşan çek ve senetleri sorgula
    $query = "
        SELECT 
            c.cari_id,
            c.cari_ad AS isletme_adi,
            c.cari_firmaTelefon AS telefon,
            CASE 
                WHEN s.senet_id IS NOT NULL THEN 'Senet'
                WHEN ck.cek_id IS NOT NULL THEN 'Çek'
                ELSE NULL
            END AS odeme_turu,
            COALESCE(s.senet_vadeTarih, ck.cek_vadeTarih) AS vade_tarihi,
            COALESCE(
                DATEDIFF(s.senet_vadeTarih, CURDATE()),
                DATEDIFF(ck.cek_vadeTarih, CURDATE())
            ) AS kalan_gun,
            COALESCE(s.senet_tutar, ck.cek_tutar) AS tutar,
            COALESCE(s.senet_id, ck.cek_id) AS kayit_id,
            CASE 
                WHEN s.senet_id IS NOT NULL THEN 4
                WHEN ck.cek_id IS NOT NULL THEN 2
                ELSE NULL
            END AS tahsilat_tipi
        FROM cari c
        LEFT JOIN senet s 
            ON s.senet_cariID = c.cari_id
        LEFT JOIN muhasebe_tahsilat_durum ms 
            ON ms.kayit_id = s.senet_id 
               AND ms.tahsilat_tipi = 4
               AND ms.durum <> 2
               AND ms.onay_durumu <> 1
        LEFT JOIN cek ck 
            ON ck.cek_cariID = c.cari_id
        LEFT JOIN muhasebe_tahsilat_durum mc 
            ON mc.kayit_id = ck.cek_id 
               AND mc.tahsilat_tipi = 2
               AND mc.durum <> 2
               AND mc.onay_durumu <> 1
        WHERE 
            c.cari_durum = 1
            AND c.cari_firmaTelefon IS NOT NULL
            AND c.cari_firmaTelefon != ''
            AND LENGTH(REGEXP_REPLACE(c.cari_firmaTelefon, '[^0-9]', '')) >= 10
            AND (
                DATEDIFF(s.senet_vadeTarih, CURDATE()) IN (10, 3)
                OR DATEDIFF(ck.cek_vadeTarih, CURDATE()) IN (10, 3)
            )
            AND (s.senet_id IS NOT NULL OR ck.cek_id IS NOT NULL)
        ORDER BY 
            c.cari_ad
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Sorgu hatası: ' . $conn->error);
    }
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    
    log_message('Toplam bulunan müşteri: ' . count($customers));
    
    if (empty($customers)) {
        log_message('Vade tarihi yaklaşan müşteri bulunamadı. İşlem tamamlandı.');
        log_message('=== CRON JOB BAŞARIYLA TAMAMLANDI ===');
        exit(0);
    }
    
    $basarili = 0;
    $basarisiz = 0;
    $tekrar = 0;
    
    foreach ($customers as $index => $customer) {
        $sira = $index + 1;
        log_message("[$sira/" . count($customers) . "] İşleniyor: {$customer['isletme_adi']}");
        
        // Telefon kontrolü
        if (empty($customer['telefon']) || strlen(preg_replace('/[^0-9]/', '', $customer['telefon'])) < 10) {
            log_message("  → ATLANDI: Geçerli telefon numarası yok (" . ($customer['telefon'] ?: 'BOŞ') . ")", 'WARNING');
            $basarisiz++;
            continue;
        }
        
        // Bu müşteriye bu kayıt için bugün SMS gönderilmiş mi kontrol et
        $check_query = "SELECT id FROM sms_log 
                        WHERE cari_id = ? 
                        AND kayit_id = ? 
                        AND tahsilat_tipi = ? 
                        AND DATE(gonderim_tarihi) = CURDATE()";
        
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param('iii', $customer['cari_id'], $customer['kayit_id'], $customer['tahsilat_tipi']);
        $stmt->execute();
        $check_result = $stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            log_message("  → ATLANDI: Bu müşteriye bugün zaten SMS gönderilmiş");
            $tekrar++;
            $stmt->close();
            continue;
        }
        $stmt->close();
        
        // Tarihi formatla
        $aylar = ['Ocak', 'Subat', 'Mart', 'Nisan', 'Mayis', 'Haziran', 
                  'Temmuz', 'Agustos', 'Eylul', 'Ekim', 'Kasim', 'Aralik'];
        $tarih = new DateTime($customer['vade_tarihi']);
        $vade_tarihi_fmt = $tarih->format('d') . ' ' . $aylar[(int)$tarih->format('m') - 1] . ' ' . $tarih->format('Y');
        
        // Ödeme türünü büyük harfe çevir
        $odeme_turu = mb_convert_case($customer['odeme_turu'], MB_CASE_UPPER, 'UTF-8');
        $odeme_turu = str_replace(['İ', 'Ş', 'Ğ', 'Ü', 'Ö', 'Ç'], ['I', 'S', 'G', 'U', 'O', 'C'], $odeme_turu);
        
        // Mesajı hazırla
        $mesaj = str_replace('[ODEME_TURU]', $odeme_turu, $sms_template);
        $mesaj = str_replace('[VADE_TARIHI]', $vade_tarihi_fmt, $mesaj);
        
        // Telefonu formatla
        $telefon = preg_replace('/[^0-9]/', '', $customer['telefon']);
        if (substr($telefon, 0, 1) === '0') {
            $telefon = '90' . substr($telefon, 1);
        } elseif (substr($telefon, 0, 2) !== '90') {
            $telefon = '90' . $telefon;
        }
        
        // SMS gönder
        $xml_body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml_body .= '<MainmsgBody>' . "\n";
        $xml_body .= '    <UserName>' . $config['sms_username'] . '</UserName>' . "\n";
        $xml_body .= '    <PassWord>' . $config['sms_password'] . '</PassWord>' . "\n";
        $xml_body .= '    <Action>' . $config['sms_action'] . '</Action>' . "\n";
        $xml_body .= '    <Mesgbody>' . htmlspecialchars($mesaj) . '</Mesgbody>' . "\n";
        $xml_body .= '    <Numbers>' . $telefon . '</Numbers>' . "\n";
        $xml_body .= '    <Originator>' . $config['sms_originator'] . '</Originator>' . "\n";
        $xml_body .= '    <SDate></SDate>' . "\n";
        $xml_body .= '</MainmsgBody>';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $config['sms_api_url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml',
            'Content-Length: ' . strlen($xml_body)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $config['sms_timeout']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Durum
        $durum = ($http_code == 200 && empty($curl_error)) ? 'basarili' : 'basarisiz';
        $hata_mesaji = ($durum === 'basarisiz') ? ($curl_error ?: $response) : null;
        
        // Logla
        $insert_query = "INSERT INTO sms_log 
            (cari_id, telefon, mesaj, tip, durum, hata_mesaji, odeme_turu, kayit_id, tahsilat_tipi, api_response) 
            VALUES (?, ?, ?, 'vade_hatirlatma', ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param(
            'isssssiis',
            $customer['cari_id'],
            $telefon,
            $mesaj,
            $durum,
            $hata_mesaji,
            $customer['odeme_turu'],
            $customer['kayit_id'],
            $customer['tahsilat_tipi'],
            $response
        );
        
        $stmt->execute();
        $stmt->close();
        
        if ($durum === 'basarili') {
            log_message("  ✓ BAŞARILI: SMS gönderildi");
            log_message("  Telefon: {$telefon}");
            log_message("  Ödeme: {$customer['odeme_turu']} - Vade: {$customer['vade_tarihi']} ({$customer['kalan_gun']} gün kala)");
            $basarili++;
        } else {
            log_message("  ✗ BAŞARISIZ: " . ($curl_error ?: $response), 'ERROR');
            log_message("  HTTP Code: {$http_code}", 'ERROR');
            $basarisiz++;
        }
        
        // API'yi yormamak için bekleme (500ms)
        usleep(500000);
    }
    
    $conn->close();
    
    log_message('');
    log_message('=== ÖZET RAPOR ===');
    log_message("Toplam Bulunan: " . count($customers));
    log_message("Başarılı: {$basarili}");
    log_message("Başarısız: {$basarisiz}");
    log_message("Tekrar (Bugün Gönderilmiş): {$tekrar}");
    log_message('=== CRON JOB BAŞARIYLA TAMAMLANDI ===');
    
    exit(0);
    
} catch (Exception $e) {
    log_message('FATAL ERROR: ' . $e->getMessage(), 'ERROR');
    log_message('Stack Trace: ' . $e->getTraceAsString(), 'ERROR');
    exit(1);
}
