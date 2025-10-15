<?php
// Stok bilgileri endpoint - Standalone
// Manual database connection - CodeIgniter bypass

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug table structure check
if (isset($_GET['debug_table'])) {
    try {
        $mysqli = new mysqli('localhost', 'ilekasoft_crmuser', 'KaleW356!', 'ilekasoft_crmdb');
        $mysqli->set_charset('utf8mb4');
        
        if ($mysqli->connect_error) {
            throw new Exception("MySQL connection failed: " . $mysqli->connect_error);
        }
        
        $table_name = isset($_GET['table']) ? $_GET['table'] : 'satisFaturasiStok';
        
        $columns = [];
        $result = $mysqli->query("DESCRIBE $table_name");
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        // Get sample data
        $sample_result = $mysqli->query("SELECT * FROM $table_name LIMIT 2");
        $sample_data = [];
        if ($sample_result) {
            while ($row = $sample_result->fetch_assoc()) {
                $sample_data[] = $row;
            }
        }
        
        $mysqli->close();
        
        echo json_encode([
            'table_name' => $table_name,
            'table_columns' => $columns,
            'sample_data' => $sample_data
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// POST verilerini al
$raw_post = file_get_contents('php://input');
parse_str($raw_post, $post_data);

$satis_id = isset($_POST['satis_id']) ? $_POST['satis_id'] : (
    isset($post_data['satis_id']) ? $post_data['satis_id'] : (
        isset($_GET['satis_id']) ? $_GET['satis_id'] : null
    )
);
$cari_id = isset($_POST['cari_id']) ? $_POST['cari_id'] : (
    isset($post_data['cari_id']) ? $post_data['cari_id'] : (
        isset($_GET['cari_id']) ? $_GET['cari_id'] : null
    )
);

error_log("DEBUG: stok_bilgileri_endpoint called with satis_id: $satis_id, cari_id: $cari_id");
error_log("DEBUG: POST data: " . print_r($_POST, true));
error_log("DEBUG: Raw POST: " . $raw_post);
error_log("DEBUG: Parsed POST: " . print_r($post_data, true));

// satis_id null ise hata, ama 0 olabilir (yeni kayıt için)
if ($satis_id === null || $satis_id === '') {
    // Debug: mevcut satış ID'leri göster
    try {
        $mysqli = new mysqli('localhost', 'ilekasoft_crmuser', 'KaleW356!', 'ilekasoft_crmdb');
        $mysqli->set_charset("utf8mb4");
        
        $sample_query = "SELECT DISTINCT satisStok_satisFaturasiID FROM satisFaturasiStok LIMIT 10";
        $sample_result = $mysqli->query($sample_query);
        $sample_ids = [];
        if ($sample_result) {
            while ($row = $sample_result->fetch_assoc()) {
                $sample_ids[] = $row['satisStok_satisFaturasiID'];
            }
        }
        $mysqli->close();
    } catch (Exception $e) {
        $sample_ids = ['error' => $e->getMessage()];
    }
    
    echo json_encode([
        'success' => false, 
        'message' => 'Satış ID bilgisi eksik',
        'debug' => [
            'post' => $_POST,
            'raw_post' => $raw_post,
            'parsed_post' => $post_data,
            'get' => $_GET,
            'server_method' => $_SERVER['REQUEST_METHOD'],
            'sample_satis_ids' => $sample_ids
        ]
    ]);
    exit;
}

try {
    // Database config
    $host = 'localhost';
    $dbname = 'ilekasoft_crmdb';
    $username = 'ilekasoft_crmuser';
    $password = 'KaleW356!';
    
    // MySQLi connection
    $mysqli = new mysqli($host, $username, $password, $dbname);
    
    // Set charset
    $mysqli->set_charset("utf8mb4");
    
    if ($mysqli->connect_error) {
        throw new Exception("MySQL connection failed: " . $mysqli->connect_error);
    }
    
    // Yeni kayıt için (satis_id = 0) boş array döndür
    $data = [];
    
    if ($satis_id > 0) {
        // Simplified query with only existing columns
        $query = "
            SELECT 
                sfs.satisStok_id,
                sfs.satisStok_satisFaturasiID,
                sfs.satisStok_stokID as stok_id,
                sfs.satisStok_miktar,
                sfs.satisStok_fiyatMiktar,
                sfs.satisStok_kdv,
                sfs.satisStok_abonelikBitisTarihi,
                COALESCE(s.stok_ad, 'Bilinmeyen Stok') as stok_adi,
                s.stok_kodu
            FROM satisFaturasiStok sfs
            LEFT JOIN stok s ON s.stok_id = sfs.satisStok_stokID
            WHERE sfs.satisStok_satisFaturasiID = ?
            ORDER BY sfs.satisStok_id ASC";
        
        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }
        
        $stmt->bind_param("i", $satis_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $stmt->close();
    }
    
    // Tahsilat detayları sorgusu - Kullanıcının istediği sorgu
    $tahsilat_query = "
        -- Önce cari ID değişkeni tanımla
        SET @CariID := ?;
        
        -- Rapor sorgusu
        SELECT 'Çek' AS Kalem,
               COALESCE((
                 SELECT GROUP_CONCAT(CONCAT(DATE_FORMAT(c.cek_vadeTarih, '%Y-%m-%d'), ' / ', FORMAT(c.cek_tutar, 2))
                                      ORDER BY c.cek_vadeTarih SEPARATOR ' | ')
                 FROM cek c
                 WHERE c.cek_cariID = @CariID
               ), '—') AS Detay,
               COALESCE((
                 SELECT FORMAT(SUM(c.cek_tutar), 2)
                 FROM cek c
                 WHERE c.cek_cariID = @CariID
               ), '0.00') AS Toplam
        UNION ALL
        SELECT 'Banka & Pos' AS Kalem,
               COALESCE((
                 SELECT GROUP_CONCAT(CONCAT(DATE_FORMAT(b.bh_tarih, '%Y-%m-%d'), ' / ', FORMAT(b.bh_giris, 2))
                                      ORDER BY b.bh_tarih SEPARATOR ' | ')
                 FROM bankahareketleri b
                 WHERE b.bh_cariID = @CariID
               ), '—') AS Detay,
               COALESCE((
                 SELECT FORMAT(SUM(b.bh_giris), 2)
                 FROM bankahareketleri b
                 WHERE b.bh_cariID = @CariID
               ), '0.00') AS Toplam
        UNION ALL
        SELECT 'Senet' AS Kalem,
               COALESCE((
                 SELECT GROUP_CONCAT(CONCAT(DATE_FORMAT(s.senet_vadeTarih, '%Y-%m-%d'), ' / ', FORMAT(s.senet_tutar, 2))
                                      ORDER BY s.senet_vadeTarih SEPARATOR ' | ')
                 FROM senet s
                 WHERE s.senet_cariID = @CariID
               ), '—') AS Detay,
               COALESCE((
                 SELECT FORMAT(SUM(s.senet_tutar), 2)
                 FROM senet s
                 WHERE s.senet_cariID = @CariID
               ), '0.00') AS Toplam
        UNION ALL
        SELECT 'Nakit (Kasa)' AS Kalem,
               COALESCE((
                 SELECT GROUP_CONCAT(CONCAT(DATE_FORMAT(k.kh_tarih, '%Y-%m-%d'), ' / ', FORMAT(k.kh_giris, 2))
                                      ORDER BY k.kh_tarih SEPARATOR ' | ')
                 FROM kasahareketleri k
                 WHERE k.kh_cariID = @CariID
               ), '—') AS Detay,
               COALESCE((
                 SELECT FORMAT(SUM(k.kh_giris), 2)
                 FROM kasahareketleri k
                 WHERE k.kh_cariID = @CariID
               ), '0.00') AS Toplam;
    ";
    
    // Tahsilat verilerini al
    $tahsilat_data = [];
    if ($cari_id) {
        // SET sorgusu ayrı çalıştır
        $mysqli->query("SET @CariID := " . intval($cari_id));
        
        // Ana sorguyu çalıştır - onay durumu ile birlikte
        $tahsilat_result = $mysqli->query("
            SELECT 
                'Çek' AS Kalem,
                COALESCE((
                    SELECT GROUP_CONCAT(CONCAT(DATE_FORMAT(c.cek_vadeTarih, '%Y-%m-%d'), ' / ', FORMAT(c.cek_tutar, 2))
                                         ORDER BY c.cek_vadeTarih SEPARATOR ' | ')
                    FROM cek c
                    WHERE c.cek_cariID = " . intval($cari_id) . "
                ), '—') AS Detay,
                COALESCE((
                    SELECT FORMAT(SUM(c.cek_tutar), 2)
                    FROM cek c
                    WHERE c.cek_cariID = " . intval($cari_id) . "
                ), '0.00') AS Toplam,
                'cek_group' as unique_id,
                '2' as tip_kod,
                COALESCE((
                    SELECT mtd.onay_durumu 
                    FROM muhasebe_tahsilat_durum mtd 
                    WHERE mtd.tahsilat_tipi = 2 
                    AND mtd.kayit_id IN (SELECT c.cek_id FROM cek c WHERE c.cek_cariID = " . intval($cari_id) . ") 
                    AND mtd.onay_durumu = 1 
                    LIMIT 1
                ), 0) as onay_durumu
            UNION ALL
            SELECT 
                'Banka & Pos' AS Kalem,
                COALESCE((
                    SELECT GROUP_CONCAT(CONCAT(DATE_FORMAT(b.bh_tarih, '%Y-%m-%d'), ' / ', FORMAT(b.bh_giris, 2))
                                         ORDER BY b.bh_tarih SEPARATOR ' | ')
                    FROM bankahareketleri b
                    WHERE b.bh_cariID = " . intval($cari_id) . "
                ), '—') AS Detay,
                COALESCE((
                    SELECT FORMAT(SUM(b.bh_giris), 2)
                    FROM bankahareketleri b
                    WHERE b.bh_cariID = " . intval($cari_id) . "
                ), '0.00') AS Toplam,
                'bh_group' as unique_id,
                '1' as tip_kod,
                COALESCE((
                    SELECT mtd.onay_durumu 
                    FROM muhasebe_tahsilat_durum mtd 
                    WHERE mtd.tahsilat_tipi = 1 
                    AND mtd.kayit_id IN (SELECT b.bh_id FROM bankahareketleri b WHERE b.bh_cariID = " . intval($cari_id) . ") 
                    AND mtd.onay_durumu = 1 
                    LIMIT 1
                ), 0) as onay_durumu
            UNION ALL
            SELECT 
                'Senet' AS Kalem,
                COALESCE((
                    SELECT GROUP_CONCAT(CONCAT(DATE_FORMAT(s.senet_vadeTarih, '%Y-%m-%d'), ' / ', FORMAT(s.senet_tutar, 2))
                                         ORDER BY s.senet_vadeTarih SEPARATOR ' | ')
                    FROM senet s
                    WHERE s.senet_cariID = " . intval($cari_id) . "
                ), '—') AS Detay,
                COALESCE((
                    SELECT FORMAT(SUM(s.senet_tutar), 2)
                    FROM senet s
                    WHERE s.senet_cariID = " . intval($cari_id) . "
                ), '0.00') AS Toplam,
                's_group' as unique_id,
                '4' as tip_kod,
                COALESCE((
                    SELECT mtd.onay_durumu 
                    FROM muhasebe_tahsilat_durum mtd 
                    WHERE mtd.tahsilat_tipi = 4 
                    AND mtd.kayit_id IN (SELECT s.senet_id FROM senet s WHERE s.senet_cariID = " . intval($cari_id) . ") 
                    AND mtd.onay_durumu = 1 
                    LIMIT 1
                ), 0) as onay_durumu
            UNION ALL
            SELECT 
                'Nakit (Kasa)' AS Kalem,
                COALESCE((
                    SELECT GROUP_CONCAT(CONCAT(DATE_FORMAT(k.kh_tarih, '%Y-%m-%d'), ' / ', FORMAT(k.kh_giris, 2))
                                         ORDER BY k.kh_tarih SEPARATOR ' | ')
                    FROM kasahareketleri k
                    WHERE k.kh_cariID = " . intval($cari_id) . "
                ), '—') AS Detay,
                COALESCE((
                    SELECT FORMAT(SUM(k.kh_giris), 2)
                    FROM kasahareketleri k
                    WHERE k.kh_cariID = " . intval($cari_id) . "
                ), '0.00') AS Toplam,
                'kh_group' as unique_id,
                '3' as tip_kod,
                COALESCE((
                    SELECT mtd.onay_durumu 
                    FROM muhasebe_tahsilat_durum mtd 
                    WHERE mtd.tahsilat_tipi = 3 
                    AND mtd.kayit_id IN (SELECT k.kh_id FROM kasahareketleri k WHERE k.kh_cariID = " . intval($cari_id) . ") 
                    AND mtd.onay_durumu = 1 
                    LIMIT 1
                ), 0) as onay_durumu
        ");
        
        if ($tahsilat_result) {
            while ($row = $tahsilat_result->fetch_assoc()) {
                $tahsilat_data[] = $row;
            }
        }
    }
    
    // Satis faturası bilgilerini al
    $satis_info = null;
    if ($satis_id > 0) {
        $satis_query = "SELECT satis_faturaTarihi, satis_faturaNo, satis_aciklama, satis_dosya FROM satisFaturasi WHERE satis_id = ?";
        $satis_stmt = $mysqli->prepare($satis_query);
        $satis_stmt->bind_param("i", $satis_id);
        $satis_stmt->execute();
        $satis_result = $satis_stmt->get_result();
        $satis_info = $satis_result->fetch_assoc();
        $satis_stmt->close();
    }
    
    $mysqli->close();
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'tahsilat_data' => $tahsilat_data,
        'satis_info' => $satis_info
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'satis_id' => $satis_id,
            'cari_id' => $cari_id,
            'error' => $e->getMessage()
        ]
    ]);
}
?>
