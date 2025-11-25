<?php
/**
 * Vade Hatırlatma SMS Cron Job
 * 
 * Çek ve Senet vade tarihlerine 10 gün ve 3 gün kala müşterilere SMS gönderir
 * 
 * Kullanım: Her gün sabah 09:00'da çalıştırılmalı
 * Cron Ayarı: 0 9 * * * /usr/bin/php /path/to/vade_hatirlatma_sms_cron.php
 * 
 * Test için: php vade_hatirlatma_sms_cron.php --test
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
$log_file = __DIR__ . '/../logs/vade_hatirlatma_sms_' . date('Y-m-d') . '.log';
$log_dir = dirname($log_file);
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Test modu kontrolü (CLI veya Web)
$test_mode = false;
$selected_template = 'sablon1'; // Varsayılan şablon

if (php_sapi_name() === 'cli') {
    // CLI modunda
    $test_mode = (isset($argv[1]) && $argv[1] === '--test');
    $selected_template = isset($argv[2]) ? $argv[2] : 'sablon1';
} else {
    // Web modunda
    $test_mode = (isset($_GET['mode']) && $_GET['mode'] === 'test');
    $selected_template = isset($_GET['template']) ? $_GET['template'] : 'sablon1';
    // Web modunda content type ayarla
    header('Content-Type: text/plain; charset=utf-8');
}

// SMS Şablonları
$sms_templates = array(
    'sablon1' => "Sayin Musterimiz,\n\n{ODEME_TURU} odemenizin vade tarihi {VADE_TARIHI} gunudur.\n\nKonuyla ilgili detayli bilgi ve destek icin 0552 173 10 37 numarali telefondan Burcu Hanim ile iletisime gecebilirsiniz.\n\nBilgilerinize sunar, iyi gunler dileriz.",
    
    'sablon2' => "Sayin Musterimiz,\n\n{ODEME_TURU} vade tarihi: {VADE_TARIHI}\n\nBilgi icin: 0552 173 10 37\n\nTesekkurler.",
    
    'sablon3' => "Sayin Musterimiz,\n\n{ODEME_TURU} odemenizin vade tarihi {VADE_TARIHI} gunudur.\n\nOdeme detaylari:\n- Tutar: {TUTAR} TL\n- Vade: {VADE_TARIHI}\n\nHerhangi bir sorunuz icin 0552 173 10 37 numarali telefondan Burcu Hanim ile iletisime gecebilirsiniz.\n\nSaygilarimizla,\nIlekaSoft CRM",
    
    'sablon4' => "Degerli Musterimiz,\n\nBu mesaj {ODEME_TURU} odemenizin vade tarihinin {VADE_TARIHI} gununde oldugunu hatirlatmak amaciyla gonderilmistir.\n\nHerhangi bir sorunuz veya degisiklik talebi icin 0552 173 10 37 numarali telefondan Burcu Hanim ile gorusebilirsiniz.\n\nIyi gunler dileriz.",
    
    'sablon5' => "Sayin Musterimiz,\n\n{ODEME_TURU} vade tarihi: {VADE_TARIHI}\n\nDetayli bilgi ve destek icin:\n📞 0552 173 10 37 (Burcu Hanim)\n💬 WhatsApp: https://wa.me/905521731037\n\nTesekkurler."
);

// Seçilen şablonu kontrol et
if (!isset($sms_templates[$selected_template])) {
    $selected_template = 'sablon1';
}

$sms_template = $sms_templates[$selected_template];

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

/**
 * SMS Gönderme Fonksiyonu
 */
function send_sms_cron($phone, $message, $config) {
    // Telefon numarasını temizle
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Başındaki 0'ı kaldır ve 90 ekle
    $phone = ltrim($phone, '0');
    if (substr($phone, 0, 2) !== '90') {
        $phone = '90' . $phone;
    }
    
    // XML Body oluştur
    $xml_body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml_body .= '<MainmsgBody>' . "\n";
    $xml_body .= '    <UserName>' . $config['sms_username'] . '</UserName>' . "\n";
    $xml_body .= '    <PassWord>' . $config['sms_password'] . '</PassWord>' . "\n";
    $xml_body .= '    <Action>' . $config['sms_action'] . '</Action>' . "\n";
    $xml_body .= '    <Mesgbody>' . htmlspecialchars($message) . '</Mesgbody>' . "\n";
    $xml_body .= '    <Numbers>' . $phone . '</Numbers>' . "\n";
    $xml_body .= '    <Originator>' . $config['sms_originator'] . '</Originator>' . "\n";
    $xml_body .= '    <SDate></SDate>' . "\n";
    $xml_body .= '</MainmsgBody>';
    
    // CURL ile API'ye istek gönder
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
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return array(
        'success' => ($http_code == 200 && empty($error)),
        'response' => $response,
        'http_code' => $http_code,
        'error' => $error
    );
}

try {
    log_message('=== Vade Hatırlatma SMS Cron Job Başladı ===');
    
    if ($test_mode) {
        log_message('TEST MODU AKTIF - SMS\'ler gönderilmeyecek, sadece rapor oluşturulacak', 'WARNING');
    }
    
    // Database bağlantısı
    require_once(__DIR__ . '/../application/config/database.php');
    
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
    require_once(__DIR__ . '/../application/config/sms.php');
    log_message('SMS konfigürasyonu yüklendi');
    
    // Vade tarihi yaklaşan çek ve senetleri sorgula
    $query = "
        SELECT 
            c.cari_id,
            c.cari_ad AS isletme_adi,
            c.cari_soyad AS yetkili_adi_soyadi,
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
           AND ms.durum <> 2           -- Ödeme Alınmadı
           AND ms.onay_durumu <> 1     -- Onaylanmadı
        LEFT JOIN cek ck 
            ON ck.cek_cariID = c.cari_id
        LEFT JOIN muhasebe_tahsilat_durum mc 
            ON mc.kayit_id = ck.cek_id 
           AND mc.tahsilat_tipi = 2
           AND mc.durum <> 2           -- Ödeme Alınmadı
           AND mc.onay_durumu <> 1     -- Onaylanmadı
        WHERE 
            c.cari_durum = 1
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
    
    $total_count = $result->num_rows;
    log_message("Toplam {$total_count} adet vade hatırlatması bulundu");
    
    $success_count = 0;
    $error_count = 0;
    $skip_count = 0;
    
    // SMS gönderme tablosu (aynı kişiye birden fazla SMS gönderilmemesi için)
    $sent_phones = array();
    
    while ($row = $result->fetch_assoc()) {
        $isletme = $row['isletme_adi'];
        $yetkili = $row['yetkili_adi_soyadi'];
        $telefon = $row['telefon'];
        $odeme_turu = $row['odeme_turu'];
        $vade_tarihi = $row['vade_tarihi'];
        $kalan_gun = $row['kalan_gun'];
        $tutar = number_format($row['tutar'], 2, ',', '.');
        
        // Telefon kontrolü
        if (empty($telefon)) {
            log_message("ATLA: {$isletme} - Telefon numarası yok", 'WARNING');
            $skip_count++;
            continue;
        }
        
        // Aynı telefona daha önce SMS gönderilmiş mi kontrol et
        if (in_array($telefon, $sent_phones)) {
            log_message("ATLA: {$isletme} ({$telefon}) - Bu numaraya bugün daha önce SMS gönderildi", 'WARNING');
            $skip_count++;
            continue;
        }
        
        // Vade tarihini formatla
        $vade_tarihi_formatted = date('d F Y', strtotime($vade_tarihi));
        
        // Türkçe ay isimleri
        $ay_isimleri = array(
            'January' => 'Ocak', 'February' => 'Şubat', 'March' => 'Mart',
            'April' => 'Nisan', 'May' => 'Mayıs', 'June' => 'Haziran',
            'July' => 'Temmuz', 'August' => 'Ağustos', 'September' => 'Eylül',
            'October' => 'Ekim', 'November' => 'Kasım', 'December' => 'Aralık'
        );
        
        foreach ($ay_isimleri as $en => $tr) {
            $vade_tarihi_formatted = str_replace($en, $tr, $vade_tarihi_formatted);
        }
        
        // SMS mesajını oluştur
        $mesaj = "Sayin Musterimiz,\n\n";
        $mesaj .= "{$odeme_turu} odemenizin vade tarihi {$vade_tarihi_formatted} gunudur.\n\n";
        $mesaj .= "Konuyla ilgili detayli bilgi ve destek icin 0552 173 10 37 numarali telefondan Burcu Hanim ile iletisime gecebilirsiniz.\n\n";
        $mesaj .= "Bilgilerinize sunar, iyi gunler dileriz.";
        
        log_message("İşleniyor: {$isletme} - {$odeme_turu} - {$vade_tarihi_formatted} ({$kalan_gun} gün kaldı)");
        
        // Test modunda SMS gönderme
        if ($test_mode) {
            log_message("TEST: SMS gönderilmedi - Telefon: {$telefon}", 'INFO');
            log_message("TEST: Mesaj içeriği:\n{$mesaj}", 'INFO');
            $success_count++;
            $sent_phones[] = $telefon;
        } else {
            // SMS gönder
            $sms_result = send_sms_cron($telefon, $mesaj, $config);
            
            if ($sms_result['success']) {
                log_message("BAŞARILI: {$isletme} - SMS gönderildi ({$telefon})", 'SUCCESS');
                $success_count++;
                $sent_phones[] = $telefon;
                
                // SMS gönderim kaydını veritabanına kaydet
                $insert_log = $conn->prepare("
                    INSERT INTO sms_log 
                    (cari_id, telefon, mesaj, tip, durum, gonderim_tarihi, odeme_turu, kayit_id, tahsilat_tipi)
                    VALUES (?, ?, ?, 'vade_hatirlatma', 'basarili', NOW(), ?, ?, ?)
                ");
                
                if ($insert_log) {
                    $insert_log->bind_param('isssii', 
                        $row['cari_id'], 
                        $telefon, 
                        $mesaj, 
                        $odeme_turu,
                        $row['kayit_id'],
                        $row['tahsilat_tipi']
                    );
                    $insert_log->execute();
                    $insert_log->close();
                }
                
                // API rate limit için bekleme (saniyede 1 SMS)
                sleep(1);
            } else {
                log_message("HATA: {$isletme} - SMS gönderilemedi: " . $sms_result['error'], 'ERROR');
                log_message("HTTP Kodu: " . $sms_result['http_code'], 'ERROR');
                $error_count++;
            }
        }
    }
    
    $result->close();
    $conn->close();
    
    // Özet
    log_message('');
    log_message('=== ÖZET ===');
    log_message("Toplam Kayıt: {$total_count}");
    log_message("Başarılı: {$success_count}");
    log_message("Hata: {$error_count}");
    log_message("Atlanan: {$skip_count}");
    log_message('');
    log_message('=== Vade Hatırlatma SMS Cron Job Tamamlandı ===');
    
} catch (Exception $e) {
    log_message('FATAL ERROR: ' . $e->getMessage(), 'ERROR');
    log_message('Stack Trace: ' . $e->getTraceAsString(), 'ERROR');
    exit(1);
}

exit(0);
