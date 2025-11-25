<?php
// Stok bilgileri kaydet endpoint - Standalone
// Manual database connection - CodeIgniter bypass

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// POST verilerini al
$satis_id = isset($_POST['satis_id']) ? intval($_POST['satis_id']) : 0;
$cari_id = isset($_POST['cari_id']) ? intval($_POST['cari_id']) : 0;
$sozlesme_tarihi = isset($_POST['sozlesme_tarihi']) ? $_POST['sozlesme_tarihi'] : null;
$sozlesme_no = isset($_POST['sozlesme_no']) ? $_POST['sozlesme_no'] : null;
$sozlesme_aciklama = isset($_POST['sozlesme_aciklama']) ? $_POST['sozlesme_aciklama'] : null;

// Stok bilgileri JSON formatında geliyor
$stok_bilgileri = [];
if (isset($_POST['stok_bilgileri'])) {
    $stok_bilgileri = json_decode($_POST['stok_bilgileri'], true);
    if (!$stok_bilgileri) {
        $stok_bilgileri = [];
    }
}

error_log("DEBUG: stok_kaydet called with satis_id: $satis_id, cari_id: $cari_id");
error_log("DEBUG: stok_bilgileri: " . json_encode($stok_bilgileri));
error_log("DEBUG: sozlesme_tarihi: $sozlesme_tarihi, sozlesme_no: $sozlesme_no");

// satis_id 0 olabilir (yeni kayıt için), ama cari_id ve stok_bilgileri zorunlu
if ($satis_id === null || $satis_id === '' || !$cari_id || !$stok_bilgileri || !is_array($stok_bilgileri)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Gerekli veriler eksik',
        'debug' => [
            'satis_id' => $satis_id,
            'cari_id' => $cari_id,
            'stok_count' => is_array($stok_bilgileri) ? count($stok_bilgileri) : 0,
            'raw_post' => $raw_post,
            'post_data' => $post_data
        ]
    ]);
    exit;
}

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'ilekasoft_crmdb';
    $username = 'ilekasoft_crmuser';
    $password = 'KaleW356!';
    
    // MySQLi connection
    $mysqli = new mysqli($host, $username, $password, $dbname);
    $mysqli->set_charset("utf8mb4");
    
    if ($mysqli->connect_error) {
        throw new Exception("MySQL connection failed: " . $mysqli->connect_error);
    }
    
    // Türkçe sayı formatını parse etmek için fonksiyon
    function parseTurkishNumber($value) {
        if (empty($value)) return 0;
        
        // String'e çevir ve temizle
        $numStr = trim(strval($value));
        
        // Sadece rakam, nokta ve virgül bırak
        $numStr = preg_replace('/[^\d.,]/', '', $numStr);
        
        // Eğer boşsa 0 döndür
        if (empty($numStr)) return 0;
        
        // Türkçe format kontrolü (414.000,50 formatında)
        if (strpos($numStr, '.') !== false && strpos($numStr, ',') !== false) {
            // Hem nokta hem virgül var - Türkçe format
            // Noktaları kaldır (binlik ayırıcı), virgülü ondalık ayırıcı yap
            $numStr = str_replace('.', '', $numStr);
            $numStr = str_replace(',', '.', $numStr);
        } else if (strpos($numStr, ',') !== false) {
            // Sadece virgül var
            $parts = explode(',', $numStr);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                // Ondalık ayırıcı olarak virgül kullanılmış
                $numStr = str_replace(',', '.', $numStr);
            } else {
                // Binlik ayırıcı olarak virgül kullanılmış
                $numStr = str_replace(',', '', $numStr);
            }
        } else if (strpos($numStr, '.') !== false) {
            // Sadece nokta var
            $parts = explode('.', $numStr);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                // Ondalık ayırıcı olarak nokta kullanılmış - değiştirme
            } else {
                // Binlik ayırıcı olarak nokta kullanılmış
                $numStr = str_replace('.', '', $numStr);
            }
        }
        
        return floatval($numStr);
    }
    
    // Transaction başlat
    $mysqli->autocommit(false);
    
    // Eğer yeni kayıt ise (satis_id = 0), önce satisFaturasi kaydı oluştur
    if ($satis_id == 0) {
        $insert_satis_query = "
            INSERT INTO satisFaturasi (
                satis_cariID,
                satis_faturaTarihi,
                satis_faturaNo,
                satis_aciklama,
                satis_araToplam,
                satis_kdvToplam,
                satis_genelToplam,
                satis_netTutar,
                satis_vergiDahilToplam,
                satis_olusturmaTarihi,
                satis_istisna_id,
                satis_vergiMuafiyetSebep
            ) VALUES (?, ?, ?, ?, 0, 0, 0, 0, 0, CURDATE(), 0, '')
        ";
        
        $insert_satis_stmt = $mysqli->prepare($insert_satis_query);
        if (!$insert_satis_stmt) {
            throw new Exception("Satis insert prepare failed: " . $mysqli->error);
        }
        
        $insert_satis_stmt->bind_param("isss", $cari_id, $sozlesme_tarihi, $sozlesme_no, $sozlesme_aciklama);
        if (!$insert_satis_stmt->execute()) {
            throw new Exception("Satis insert execution failed: " . $insert_satis_stmt->error);
        }
        
        $satis_id = $mysqli->insert_id;
        $insert_satis_stmt->close();
        
        error_log("DEBUG: Yeni satisFaturasi kaydı oluşturuldu, satis_id: $satis_id");
    } else {
        // Mevcut kayıt için stokları sil
        $delete_query = "DELETE FROM satisFaturasiStok WHERE satisStok_satisFaturasiID = ?";
        $delete_stmt = $mysqli->prepare($delete_query);
        
        if (!$delete_stmt) {
            throw new Exception("Delete prepare failed: " . $mysqli->error);
        }
        
        $delete_stmt->bind_param("i", $satis_id);
        if (!$delete_stmt->execute()) {
            throw new Exception("Delete execution failed: " . $delete_stmt->error);
        }
        $delete_stmt->close();
    }
    
    $toplam_tutar_kdv_dahil = 0;
    $inserted_count = 0;
    
    // Fatura dosya upload klasörünü oluştur
    $upload_dir = 'uploads/faturalar/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Yeni stok kayıtlarını ekle
    $insert_query = "
        INSERT INTO satisFaturasiStok (
            satisStok_satisFaturasiID,
            satisStok_stokID,
            satisStok_miktar,
            satisStok_birimFiyat,
            satisStok_fiyatMiktar,
            satisStok_kdv,
            satisStok_indirimTutari,
            satisStok_tevkifat_id,
            satisStok_satirIskonto,
            satisStok_indirimlifiyat,
            satisStok_abonelikBitisTarihi,
            satisStok_faturaKesildi,
            satisStok_faturaDosyasi
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $insert_stmt = $mysqli->prepare($insert_query);
    if (!$insert_stmt) {
        throw new Exception("Insert prepare failed: " . $mysqli->error);
    }
    
    foreach ($stok_bilgileri as $stok_index => $stok) {
        if (empty($stok['stok_adi']) || empty($stok['miktar']) || !isset($stok['birim_fiyat'])) {
            continue;
        }
        
        $stok_id = !empty($stok['stok_id']) ? intval($stok['stok_id']) : null;
        $miktar = parseTurkishNumber($stok['miktar']);
        $birim_fiyat_kdv_dahil = parseTurkishNumber($stok['birim_fiyat']); // KDV dahil fiyat
        $kdv_orani = parseTurkishNumber($stok['kdv_orani']);
        $abonelik_bitis_tarihi = !empty($stok['abonelik_bitis_tarihi']) ? $stok['abonelik_bitis_tarihi'] : null;
        $fatura_kesildi = isset($stok['fatura_kesildi']) ? intval($stok['fatura_kesildi']) : 0;
        
        error_log("DEBUG: Parsed values - miktar: $miktar, birim_fiyat: $birim_fiyat_kdv_dahil, kdv_orani: $kdv_orani, fatura_kesildi: $fatura_kesildi");
        
        // Toplam hesapla (KDV dahil fiyat olduğu için direkt çarp)
        $satir_toplam = $miktar * $birim_fiyat_kdv_dahil;
        $toplam_tutar_kdv_dahil += $satir_toplam;
        
        // Default values for missing fields
        $indirim_tutari = 0;
        $tevkifat_id = 0;
        $satir_iskonto = 0;
        $indirimli_fiyat = $birim_fiyat_kdv_dahil; // Same as birim fiyat since no discount
        
        // Fatura dosyası upload
        $fatura_dosyasi_path = null;
        $row_index = isset($stok['fatura_dosyasi_index']) ? $stok['fatura_dosyasi_index'] : $stok_index;
        $file_key = 'fatura_dosyasi_' . $row_index;
        
        if ($fatura_kesildi == 1 && isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES[$file_key]['tmp_name'];
            $file_name = $_FILES[$file_key]['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Dosya tipi kontrolü
            $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
            if (in_array($file_ext, $allowed_types)) {
                // Benzersiz dosya adı oluştur
                $new_file_name = 'fatura_' . $satis_id . '_' . $stok_id . '_' . time() . '.' . $file_ext;
                $target_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $target_path)) {
                    $fatura_dosyasi_path = $target_path;
                    error_log("DEBUG: Fatura dosyası yüklendi: $target_path");
                } else {
                    error_log("ERROR: Fatura dosyası yüklenemedi: $target_path");
                }
            } else {
                error_log("ERROR: Geçersiz dosya uzantısı: $file_ext");
            }
        }
        
        // Veritabanına kaydet
        $insert_stmt->bind_param("iidddddiiddis", 
            $satis_id, 
            $stok_id, 
            $miktar, 
            $birim_fiyat_kdv_dahil,
            $satir_toplam, // satisStok_fiyatMiktar
            $kdv_orani,
            $indirim_tutari,
            $tevkifat_id,
            $satir_iskonto,
            $indirimli_fiyat,
            $abonelik_bitis_tarihi,
            $fatura_kesildi,
            $fatura_dosyasi_path
        );

        if (!$insert_stmt->execute()) {
            throw new Exception("Insert execution failed: " . $insert_stmt->error);
        }

        $inserted_count++;
    }
    
    $insert_stmt->close();
    
    if ($inserted_count == 0) {
        throw new Exception("Hiçbir geçerli stok kaydı bulunamadı");
    }
    
    // Ana fatura tablosunu güncelle - tüm stokların toplamını hesapla
    $kdv_haric_toplam = 0;
    $kdv_toplam = 0;
    $kdv_dahil_toplam = $toplam_tutar_kdv_dahil;
    
    // Her stok için KDV hariç tutarı hesapla
    foreach ($stok_bilgileri as $stok) {
        if (empty($stok['stok_adi']) || empty($stok['miktar']) || !isset($stok['birim_fiyat'])) {
            continue;
        }
        
        $miktar = parseTurkishNumber($stok['miktar']);
        $birim_fiyat_kdv_dahil = parseTurkishNumber($stok['birim_fiyat']);
        $kdv_orani = parseTurkishNumber($stok['kdv_orani']);
        
        $satir_toplam_kdv_dahil = $miktar * $birim_fiyat_kdv_dahil;
        $satir_toplam_kdv_haric = round($satir_toplam_kdv_dahil / (1 + $kdv_orani / 100), 2);
        $satir_kdv_tutari = round($satir_toplam_kdv_dahil - $satir_toplam_kdv_haric, 2);
        
        $kdv_haric_toplam += $satir_toplam_kdv_haric;
        $kdv_toplam += $satir_kdv_tutari;
    }
    
    // Final rounding
    $kdv_haric_toplam = round($kdv_haric_toplam, 2);
    $kdv_toplam = round($kdv_toplam, 2);
    
    // satisFaturasi tablosunu güncelle - sözleşme bilgileri dahil
    $update_invoice_query = "
        UPDATE satisFaturasi SET 
            satis_araToplam = ?,
            satis_kdvToplam = ?,
            satis_genelToplam = ?,
            satis_netTutar = ?,
            satis_vergiDahilToplam = ?,
            satis_faturaTarihi = ?,
            satis_faturaNo = ?,
            satis_aciklama = ?
        WHERE satis_id = ?
    ";
    
    $update_stmt = $mysqli->prepare($update_invoice_query);
    if (!$update_stmt) {
        throw new Exception("Invoice update prepare failed: " . $mysqli->error);
    }
    
    $update_stmt->bind_param("dddddsssi", 
        $kdv_haric_toplam,
        $kdv_toplam, 
        $kdv_dahil_toplam,
        $kdv_haric_toplam,
        $kdv_dahil_toplam,
        $sozlesme_tarihi,
        $sozlesme_no,
        $sozlesme_aciklama,
        $satis_id
    );
    
    if (!$update_stmt->execute()) {
        throw new Exception("Invoice update execution failed: " . $update_stmt->error);
    }
    
    $update_stmt->close();
    
    // Transaction'ı tamamla
    $mysqli->commit();
    $mysqli->close();
    
    echo json_encode([
        'success' => true, 
        'message' => "$inserted_count stok kaydı başarıyla güncellendi",
        'data' => [
            'inserted_count' => $inserted_count,
            'toplam_tutar_kdv_dahil' => $toplam_tutar_kdv_dahil,
            'kdv_haric_toplam' => $kdv_haric_toplam,
            'kdv_toplam' => $kdv_toplam,
            'fatura_guncellendi' => true
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($mysqli)) {
        $mysqli->rollback();
        $mysqli->close();
    }
    
    echo json_encode([
        'success' => false, 
        'message' => 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage(),
        'debug' => [
            'satis_id' => $satis_id,
            'cari_id' => $cari_id,
            'error' => $e->getMessage()
        ]
    ]);
}
?>
