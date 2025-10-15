<?php
// Satış görselleri yükleme endpoint - Standalone

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$satis_id = isset($_POST['satis_id']) ? intval($_POST['satis_id']) : 0;

if (!$satis_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Satış ID bulunamadı'
    ]);
    exit;
}

if (!isset($_FILES['satis_gorseller']) || empty($_FILES['satis_gorseller']['name'][0])) {
    echo json_encode([
        'success' => false,
        'message' => 'Dosya seçilmedi'
    ]);
    exit;
}

try {
    // Database connection
    $mysqli = new mysqli('localhost', 'ilekasoft_crmuser', 'KaleW356!', 'ilekasoft_crmdb');
    $mysqli->set_charset("utf8mb4");
    
    if ($mysqli->connect_error) {
        throw new Exception("MySQL connection failed: " . $mysqli->connect_error);
    }
    
    // Mevcut satis_dosya değerini al
    $query = "SELECT satis_dosya FROM satisFaturasi WHERE satis_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $satis_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    $mevcut_dosyalar = [];
    if (!empty($row['satis_dosya'])) {
        $mevcut_dosyalar = array_map('trim', explode(',', $row['satis_dosya']));
    }
    
    // Upload dizini
    $upload_dir = __DIR__ . '/assets/uploads/faturalar/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $yukle_count = count($_FILES['satis_gorseller']['name']);
    $uploaded_files = [];
    
    for ($i = 0; $i < $yukle_count; $i++) {
        if ($_FILES['satis_gorseller']['error'][$i] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['satis_gorseller']['tmp_name'][$i];
            $file_name = $_FILES['satis_gorseller']['name'][$i];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Dosya uzantısı kontrolü
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            if (!in_array($file_ext, $allowed_extensions)) {
                continue;
            }
            
            // Benzersiz dosya adı oluştur
            $new_file_name = md5(uniqid(rand(), true)) . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($tmp_name, $upload_path)) {
                // Veritabanı için relative path
                $uploaded_files[] = 'faturalar/' . $new_file_name;
            }
        }
    }
    
    if (empty($uploaded_files)) {
        echo json_encode([
            'success' => false,
            'message' => 'Dosya yüklenemedi'
        ]);
        exit;
    }
    
    // Yeni dosyaları mevcut listesine ekle
    $tum_dosyalar = array_merge($mevcut_dosyalar, $uploaded_files);
    $satis_dosya_str = implode(',', $tum_dosyalar);
    
    // Veritabanını güncelle
    $update_query = "UPDATE satisFaturasi SET satis_dosya = ? WHERE satis_id = ?";
    $update_stmt = $mysqli->prepare($update_query);
    $update_stmt->bind_param("si", $satis_dosya_str, $satis_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Veritabanı güncelleme hatası: " . $update_stmt->error);
    }
    
    $update_stmt->close();
    $mysqli->close();
    
    echo json_encode([
        'success' => true,
        'message' => count($uploaded_files) . ' dosya başarıyla yüklendi',
        'satis_dosya' => $satis_dosya_str,
        'uploaded_files' => $uploaded_files
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Hata: ' . $e->getMessage()
    ]);
}
?>
