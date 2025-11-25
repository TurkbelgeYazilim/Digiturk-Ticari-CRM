<?php
// Mac afişi maç bilgileri yükleme sayfası
// Standalone PHP dosyası - Tek sayfa çözümü
// Tarih: 28.10.2025

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantı bilgileri
$db_host = 'localhost';
$db_name = 'ilekasoft_crmdb';
$db_user = 'ilekasoft_crmuser';
$db_pass = 'KaleW356!';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

/**
 * Takım isminden sponsor ve prefix'leri temizle
 */
function cleanTeamName($team_name) {
    // Başta olanlar (prefix)
    $prefixes = [
        'Zecorner ', 'Mısırlı.com.tr ', 'Corendon ', 'ikas ', 'Rams ', 
        'Hesap.com ', 'Tümosan ', 'Çaykur ', 'TÜMOSAN ', 'İKAS ', 
        'RAMS ', 'HESAP.COM ', 'CORENDON ', 'ZECORNER ', 'MISIRLI.COM.TR ',
        'İstanbul ', 'ISTANBUL '
    ];
    
    // Sonda olanlar (suffix)
    $suffixes = [
        ' FUTBOL KULÜBÜ A.Ş.', ' FUTBOL KULÜBÜ', ' A.Ş.', ' FK', ' SK', ' AS'
    ];
    
    // Baştan temizle
    foreach ($prefixes as $prefix) {
        if (stripos($team_name, $prefix) === 0) {
            $team_name = substr($team_name, strlen($prefix));
        }
    }
    
    // Sondan temizle
    foreach ($suffixes as $suffix) {
        $len = strlen($suffix);
        if (substr($team_name, -$len) === $suffix || 
            strtoupper(substr($team_name, -$len)) === strtoupper($suffix)) {
            $team_name = substr($team_name, 0, -$len);
        }
    }
    
    return trim($team_name);
}

/**
 * Takım var mı kontrol et (kaydetmeden sadece kontrol)
 */
function checkTakimExists($pdo, $takim_adi) {
    // Önce tam eşleşme var mı kontrol et (case-insensitive)
    $sql = "SELECT illegal_tespit_takim_id FROM illegal_tespit_takimlar WHERE LOWER(illegal_tespit_takim_adi) = LOWER(?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$takim_adi]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result['illegal_tespit_takim_id'];
    }
    
    // Bulunamadıysa, benzer isim ara (LIKE ile)
    $sql = "SELECT illegal_tespit_takim_id FROM illegal_tespit_takimlar WHERE LOWER(illegal_tespit_takim_adi) LIKE LOWER(?) LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['%' . $takim_adi . '%']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result['illegal_tespit_takim_id'];
    }
    
    // Tersini dene
    $sql = "SELECT illegal_tespit_takim_id, illegal_tespit_takim_adi FROM illegal_tespit_takimlar";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $takimlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($takimlar as $takim) {
        if (stripos($takim_adi, $takim['illegal_tespit_takim_adi']) !== false) {
            return $takim['illegal_tespit_takim_id'];
        }
    }
    
    return null; // Bulunamadı
}

/**
 * Takım adından ID bul veya yeni takım ekle
 */
function getTakimIdByName($pdo, $takim_adi) {
    // Önce tam eşleşme var mı kontrol et (case-insensitive)
    $sql = "SELECT illegal_tespit_takim_id FROM illegal_tespit_takimlar WHERE LOWER(illegal_tespit_takim_adi) = LOWER(?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$takim_adi]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result['illegal_tespit_takim_id'];
    }
    
    // Bulunamadıysa, benzer isim ara (LIKE ile)
    $sql = "SELECT illegal_tespit_takim_id FROM illegal_tespit_takimlar WHERE LOWER(illegal_tespit_takim_adi) LIKE LOWER(?) LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['%' . $takim_adi . '%']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result['illegal_tespit_takim_id'];
    }
    
    // Tersini dene (tablodaki isim, aranan ismin içinde mi?)
    $sql = "SELECT illegal_tespit_takim_id, illegal_tespit_takim_adi FROM illegal_tespit_takimlar";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $takimlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($takimlar as $takim) {
        // Tablodaki isim, aranan ismin içinde mi kontrol et (case-insensitive)
        if (stripos($takim_adi, $takim['illegal_tespit_takim_adi']) !== false) {
            return $takim['illegal_tespit_takim_id'];
        }
    }
    
    // Hiçbir şekilde bulunamadıysa yeni ekle
    $sql = "INSERT INTO illegal_tespit_takimlar (illegal_tespit_takim_adi, illegal_tespit_takim_olusturmaTarihi) VALUES (?, CURDATE())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$takim_adi]);
    
    return $pdo->lastInsertId();
}

/**
 * Tüm takımları listele
 */
function getAllTakimlar($pdo) {
    $sql = "SELECT illegal_tespit_takim_id, illegal_tespit_takim_adi FROM illegal_tespit_takimlar ORDER BY illegal_tespit_takim_adi ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// İptal işlemi
if (isset($_GET['cancel'])) {
    unset($_SESSION['maclar_parse']);
    unset($_SESSION['eslesmeyenler']);
    unset($_SESSION['show_onay']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Form işleme
$mesaj = '';
$mesaj_tip = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mac_ekle'])) {
        // Tek maç ekleme
        $tarihi = $_POST['mac_afisi_tarihi'] ?? '';
        $saati = $_POST['mac_afisi_saati'] ?? '';
        $takim1_id = $_POST['mac_afisi_takim1_id'] ?? '';
        $takim2_id = $_POST['mac_afisi_takim2_id'] ?? '';
        $lig = $_POST['mac_afisi_lig'] ?? '';
        $durum = isset($_POST['mac_afisi_durum']) ? 1 : 0; // checkbox kontrolü
        
        if (!empty($tarihi) && !empty($saati) && !empty($takim1_id) && !empty($takim2_id) && !empty($lig)) {
            try {
                $sql = "INSERT INTO mac_afisi (mac_afisi_tarihi, mac_afisi_saati, mac_afisi_takim1_id, mac_afisi_takim2_id, mac_afisi_lig, mac_afisi_durum) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$tarihi, $saati, $takim1_id, $takim2_id, $lig, $durum]);
                
                $mesaj = "Maç başarıyla eklendi!";
                $mesaj_tip = "success";
            } catch(PDOException $e) {
                $mesaj = "Hata: " . $e->getMessage();
                $mesaj_tip = "error";
            }
        } else {
            $mesaj = "Lütfen tüm zorunlu alanları doldurun!";
            $mesaj_tip = "error";
        }
    }
    
    if (isset($_POST['toplu_ekle_onay'])) {
        // Eşleştirme yapılmış toplu ekleme
        $maclar_json = $_POST['maclar_json'] ?? '';
        $takimlar_json = $_POST['takimlar_json'] ?? '';
        
        if (!empty($maclar_json) && !empty($takimlar_json)) {
            $maclar_data = json_decode($maclar_json, true);
            $takimlar_mapping = json_decode($takimlar_json, true);
            
            // Yeni takımları ekle
            $new_team_ids = [];
            foreach ($takimlar_mapping as $key => $value) {
                if (strpos($value, 'NEW:') === 0) {
                    $new_team_name = substr($value, 4);
                    // Yeni takım ekle
                    $sql = "INSERT INTO illegal_tespit_takimlar (illegal_tespit_takim_adi, illegal_tespit_takim_olusturmaTarihi) VALUES (?, CURDATE())";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$new_team_name]);
                    $new_team_ids[$key] = $pdo->lastInsertId();
                    $takimlar_mapping[$key] = $new_team_ids[$key];
                }
            }
            
            $basarili = 0;
            $hatali = 0;
            
            foreach ($maclar_data as $mac) {
                try {
                    // Takım ID'lerini al
                    $takim1_key = $mac['takim1_original'];
                    $takim2_key = $mac['takim2_original'];
                    
                    // Mapping'den al veya zaten var olan ID'yi kullan
                    $takim1_id = $mac['takim1_id'] ?? (isset($takimlar_mapping[$takim1_key]) ? $takimlar_mapping[$takim1_key] : null);
                    $takim2_id = $mac['takim2_id'] ?? (isset($takimlar_mapping[$takim2_key]) ? $takimlar_mapping[$takim2_key] : null);
                    
                    if ($takim1_id && $takim2_id) {
                        $sql = "INSERT INTO mac_afisi (mac_afisi_tarihi, mac_afisi_saati, mac_afisi_takim1_id, mac_afisi_takim2_id, mac_afisi_lig, mac_afisi_durum) 
                                VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$mac['tarih'], $mac['saat'], $takim1_id, $takim2_id, $mac['lig'], 1]);
                        $basarili++;
                    } else {
                        $hatali++;
                    }
                } catch(PDOException $e) {
                    $hatali++;
                }
            }
            
            // Session temizle
            unset($_SESSION['maclar_parse']);
            unset($_SESSION['eslesmeyenler']);
            unset($_SESSION['show_onay']);
            
            $mesaj = "Toplu ekleme tamamlandı. Başarılı: $basarili, Hatalı: $hatali";
            if (count($new_team_ids) > 0) {
                $mesaj .= " (" . count($new_team_ids) . " yeni takım eklendi)";
            }
            $mesaj_tip = $basarili > 0 ? "success" : "error";
        }
    }
    
    if (isset($_POST['toplu_ekle'])) {
        // Toplu ekleme - Ön kontrol
        $toplu_veri = $_POST['toplu_veri'] ?? '';
        
        if (!empty($toplu_veri)) {
            $satirlar = explode("\n", trim($toplu_veri));
            $basarili = 0;
            $hatali = 0;
            $mevcut_lig = 'Süper Lig'; // Varsayılan lig
            
            $maclar_parse = []; // Parse edilen maçlar
            $eslesmeyenler = []; // Eşleşmeyen takımlar
            
            foreach ($satirlar as $satir) {
                $satir = trim($satir);
                if (empty($satir)) continue;
                
                // Lig başlığı mı kontrol et (Tab içermeyen satırlar lig adıdır)
                if (strpos($satir, "\t") === false) {
                    // Bu bir lig başlığı
                    $lig_adi = trim($satir);
                    if (stripos($lig_adi, 'süper') !== false || stripos($lig_adi, 'super') !== false) {
                        $mevcut_lig = 'Süper Lig';
                    } elseif (stripos($lig_adi, '1. lig') !== false || stripos($lig_adi, '1 lig') !== false) {
                        $mevcut_lig = 'TFF 1. Lig';
                    } elseif (stripos($lig_adi, '2. lig') !== false || stripos($lig_adi, '2 lig') !== false) {
                        $mevcut_lig = 'TFF 2. Lig';
                    } elseif (stripos($lig_adi, '3. lig') !== false || stripos($lig_adi, '3 lig') !== false) {
                        $mevcut_lig = 'TFF 3. Lig';
                    } else {
                        $mevcut_lig = $lig_adi;
                    }
                    continue; // Bir sonraki satıra geç
                }
                
                // Format: 31.10.2025 20:00	RAMS BAŞAKŞEHİR FUTBOL KULÜBÜ	-	KOCAELİSPOR	Detaylar Detaylar
                $parcalar = preg_split('/\t+/', $satir);
                
                if (count($parcalar) >= 4) {
                    // Tarih ve saat ayrıştır
                    $tarih_saat = trim($parcalar[0]);
                    $tarih_saat_parts = explode(' ', $tarih_saat);
                    
                    if (count($tarih_saat_parts) >= 2) {
                        $tarih = $tarih_saat_parts[0]; // 31.10.2025
                        $saat = $tarih_saat_parts[1];  // 20:00
                        
                        // Tarihi Y-m-d formatına çevir
                        $tarih_parts = explode('.', $tarih);
                        if (count($tarih_parts) === 3) {
                            $tarih_sql = $tarih_parts[2] . '-' . sprintf('%02d', $tarih_parts[1]) . '-' . sprintf('%02d', $tarih_parts[0]);
                        } else {
                            continue;
                        }
                        
                        $takim1 = trim($parcalar[1]);
                        // "-" işaretini atla
                        $takim2 = isset($parcalar[3]) ? trim($parcalar[3]) : '';
                        
                        // Tire işaretlerini temizle
                        $takim1 = str_replace(['-', '–'], '', $takim1);
                        $takim2 = str_replace(['-', '–'], '', $takim2);
                        $takim1 = trim($takim1);
                        $takim2 = trim($takim2);
                        
                        // Sponsor ve prefix'leri temizle
                        $takim1_clean = cleanTeamName($takim1);
                        $takim2_clean = cleanTeamName($takim2);
                        
                        if (!empty($takim1_clean) && !empty($takim2_clean)) {
                            // Takım ID'lerini kontrol et (kaydetmeden)
                            $takim1_id = checkTakimExists($pdo, $takim1_clean);
                            $takim2_id = checkTakimExists($pdo, $takim2_clean);
                            
                            // Parse edilen maç bilgisini kaydet
                            $maclar_parse[] = [
                                'tarih' => $tarih_sql,
                                'saat' => $saat,
                                'takim1_original' => $takim1, // Orijinal isim
                                'takim1_clean' => $takim1_clean, // Temizlenmiş isim
                                'takim1_id' => $takim1_id,
                                'takim2_original' => $takim2, // Orijinal isim
                                'takim2_clean' => $takim2_clean, // Temizlenmiş isim
                                'takim2_id' => $takim2_id,
                                'lig' => $mevcut_lig
                            ];
                            
                            // Eşleşmeyenleri topla
                            if (!$takim1_id && !in_array($takim1_clean, array_column($eslesmeyenler, 'isim'))) {
                                $eslesmeyenler[] = [
                                    'isim' => $takim1_clean,
                                    'original' => $takim1
                                ];
                            }
                            if (!$takim2_id && !in_array($takim2_clean, array_column($eslesmeyenler, 'isim'))) {
                                $eslesmeyenler[] = [
                                    'isim' => $takim2_clean,
                                    'original' => $takim2
                                ];
                            }
                        } else {
                            $hatali++;
                        }
                    } else {
                        $hatali++;
                    }
                } else {
                    $hatali++;
                }
            }
            
            // Eğer eşleşmeyen takım varsa, onay sayfasına yönlendir
            if (count($eslesmeyenler) > 0) {
                // Eşleşmeyen takımlar var, kullanıcıya göster
                $_SESSION['maclar_parse'] = $maclar_parse;
                $_SESSION['eslesmeyenler'] = $eslesmeyenler;
                $_SESSION['show_onay'] = true;
            } else {
                // Tüm takımlar eşleşti, direkt kaydet
                foreach ($maclar_parse as $mac) {
                    try {
                        $sql = "INSERT INTO mac_afisi (mac_afisi_tarihi, mac_afisi_saati, mac_afisi_takim1_id, mac_afisi_takim2_id, mac_afisi_lig, mac_afisi_durum) 
                                VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$mac['tarih'], $mac['saat'], $mac['takim1_id'], $mac['takim2_id'], $mac['lig'], 1]);
                        $basarili++;
                    } catch(PDOException $e) {
                        $hatali++;
                    }
                }
                
                $mesaj = "Toplu ekleme tamamlandı. Başarılı: $basarili, Hatalı: $hatali";
                $mesaj_tip = $basarili > 0 ? "success" : "error";
            }
        } else {
            $mesaj = "Lütfen toplu veri girin!";
            $mesaj_tip = "error";
        }
    }
    
    if (isset($_POST['mac_guncelle'])) {
        // Maç güncelleme
        $mac_id = $_POST['mac_afisi_id'] ?? '';
        $tarihi = $_POST['mac_afisi_tarihi'] ?? '';
        $saati = $_POST['mac_afisi_saati'] ?? '';
        $takim1_id = $_POST['mac_afisi_takim1_id'] ?? '';
        $takim2_id = $_POST['mac_afisi_takim2_id'] ?? '';
        $lig = $_POST['mac_afisi_lig'] ?? '';
        $durum = isset($_POST['mac_afisi_durum']) ? 1 : 0;
        
        if (!empty($mac_id) && !empty($tarihi) && !empty($saati) && !empty($takim1_id) && !empty($takim2_id) && !empty($lig)) {
            try {
                $sql = "UPDATE mac_afisi SET 
                        mac_afisi_tarihi = ?, 
                        mac_afisi_saati = ?, 
                        mac_afisi_takim1_id = ?, 
                        mac_afisi_takim2_id = ?, 
                        mac_afisi_lig = ?, 
                        mac_afisi_durum = ? 
                        WHERE mac_afisi_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$tarihi, $saati, $takim1_id, $takim2_id, $lig, $durum, $mac_id]);
                
                $mesaj = "Maç başarıyla güncellendi!";
                $mesaj_tip = "success";
            } catch(PDOException $e) {
                $mesaj = "Hata: " . $e->getMessage();
                $mesaj_tip = "error";
            }
        } else {
            $mesaj = "Lütfen tüm zorunlu alanları doldurun!";
            $mesaj_tip = "error";
        }
    }
    
    if (isset($_POST['mac_sil'])) {
        // Maç silme
        $mac_id = $_POST['mac_afisi_id'] ?? '';
        
        if (!empty($mac_id)) {
            try {
                $sql = "DELETE FROM mac_afisi WHERE mac_afisi_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$mac_id]);
                
                $mesaj = "Maç başarıyla silindi!";
                $mesaj_tip = "success";
            } catch(PDOException $e) {
                $mesaj = "Hata: " . $e->getMessage();
                $mesaj_tip = "error";
            }
        }
    }
}

// Tüm takımları getir (dropdown için)
$takimlar = getAllTakimlar($pdo);

// Mevcut maçları listele
try {
    $sql = "SELECT 
                m.*,
                t1.illegal_tespit_takim_adi AS takim1_adi,
                t2.illegal_tespit_takim_adi AS takim2_adi
            FROM mac_afisi m
            LEFT JOIN illegal_tespit_takimlar t1 ON m.mac_afisi_takim1_id = t1.illegal_tespit_takim_id
            LEFT JOIN illegal_tespit_takimlar t2 ON m.mac_afisi_takim2_id = t2.illegal_tespit_takim_id
            ORDER BY m.mac_afisi_tarihi ASC, m.mac_afisi_saati ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $maclar = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $maclar = [];
    $mesaj = "Maçlar yüklenirken hata: " . $e->getMessage();
    $mesaj_tip = "error";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maç Afişi - Maç Bilgileri Yönetimi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 30px;
        }
        
        .tab {
            padding: 15px 25px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #666;
            border-radius: 10px 10px 0 0;
            margin-right: 5px;
            transition: all 0.3s;
        }
        
        .tab.active {
            background: #667eea;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            font-size: 14px;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .table-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        th {
            background: #667eea;
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            position: relative;
        }
        
        th:hover {
            background: #5a6fd8;
        }
        
        th.sortable::after {
            content: "↕️";
            position: absolute;
            right: 8px;
            font-size: 12px;
        }
        
        th.sort-asc::after {
            content: "🔼";
        }
        
        th.sort-desc::after {
            content: "🔽";
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .info-text {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
            line-height: 1.4;
        }
        
        .example-box {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }
        
        .example-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .example-text {
            font-family: monospace;
            font-size: 13px;
            color: #666;
            line-height: 1.4;
        }
        
        @media (max-width: 768px) {
            .form-row,
            .form-row-3 {
                grid-template-columns: 1fr;
            }
            
            .content {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 22px;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px 6px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚽ Maç Afişi - Maç Bilgileri Yönetimi</h1>
            <p>Maç bilgilerini ekleyin, düzenleyin ve yönetin</p>
        </div>
        
        <div class="content">
            <?php if (!empty($mesaj)): ?>
                <div class="alert alert-<?= $mesaj_tip === 'success' ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($mesaj) ?>
                </div>
            <?php endif; ?>
            
            <div class="tabs">
                <button class="tab active" onclick="showTab('tek-mac')">Tek Maç Ekle</button>
                <button class="tab" onclick="showTab('toplu-mac')">Toplu Maç Ekle</button>
                <button class="tab" onclick="showTab('mac-listesi')">Maç Listesi</button>
            </div>
            
            <!-- Tek Maç Ekleme -->
            <div id="tek-mac" class="tab-content active">
                <h3 style="margin-bottom: 20px; color: #333;">🆕 Yeni Maç Ekle</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mac_afisi_tarihi">📅 Maç Tarihi</label>
                            <input type="date" name="mac_afisi_tarihi" id="mac_afisi_tarihi" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="mac_afisi_saati">🕐 Maç Saati</label>
                            <input type="time" name="mac_afisi_saati" id="mac_afisi_saati" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mac_afisi_takim1_id">🏠 Ev Sahibi Takım</label>
                            <select name="mac_afisi_takim1_id" id="mac_afisi_takim1_id" required>
                                <option value="">Takım Seçiniz</option>
                                <?php foreach ($takimlar as $takim): ?>
                                    <option value="<?= $takim['illegal_tespit_takim_id'] ?>">
                                        <?= htmlspecialchars($takim['illegal_tespit_takim_adi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="mac_afisi_takim2_id">✈️ Deplasman Takım</label>
                            <select name="mac_afisi_takim2_id" id="mac_afisi_takim2_id" required>
                                <option value="">Takım Seçiniz</option>
                                <?php foreach ($takimlar as $takim): ?>
                                    <option value="<?= $takim['illegal_tespit_takim_id'] ?>">
                                        <?= htmlspecialchars($takim['illegal_tespit_takim_adi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mac_afisi_lig">🏆 Lig</label>
                            <select name="mac_afisi_lig" id="mac_afisi_lig" required>
                                <option value="">Lig Seçin</option>
                                <option value="Süper Lig">Süper Lig</option>
                                <option value="TFF 1. Lig">TFF 1. Lig</option>
                                <option value="TFF 2. Lig">TFF 2. Lig</option>
                                <option value="TFF 3. Lig">TFF 3. Lig</option>
                                <option value="Türkiye Kupası">Türkiye Kupası</option>
                                <option value="UEFA Şampiyonlar Ligi">UEFA Şampiyonlar Ligi</option>
                                <option value="UEFA Avrupa Ligi">UEFA Avrupa Ligi</option>
                                <option value="UEFA Konferans Ligi">UEFA Konferans Ligi</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="mac_afisi_durum">📊 Durum</label>
                            <div style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; background: white;">
                                <input type="checkbox" name="mac_afisi_durum" id="mac_afisi_durum" checked style="transform: scale(1.2);">
                                <label for="mac_afisi_durum" style="margin: 0; font-weight: normal; cursor: pointer;">Aktif</label>
                            </div>
                            <p class="info-text">İşaretli: Aktif, İşaretsiz: Pasif</p>
                        </div>
                    </div>
                    
                    <button type="submit" name="mac_ekle" class="btn btn-primary">
                        ➕ Maç Ekle
                    </button>
                </form>
            </div>
            
            <!-- Toplu Maç Ekleme -->
            <div id="toplu-mac" class="tab-content">
                <h3 style="margin-bottom: 20px; color: #333;">📋 Toplu Maç Ekleme</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="toplu_veri">📥 Maç Verileri</label>
                        <textarea name="toplu_veri" id="toplu_veri" rows="10" placeholder="Her satıra bir maç bilgisi girin..." required></textarea>
                        <p class="info-text">Her satıra bir maç bilgisi girin. Tab karakteri ile ayrılmış format kullanın.<br>
                        💡 Takım isimleri otomatik olarak temizlenip illegal_tespit_takimlar tablosundan eşleştirilir. Bulunamazsa yeni takım olarak eklenir.</p>
                    </div>
                    
                    <div class="example-box">
                        <div class="example-title">📝 Format Örneği:</div>
                        <div class="example-text">
Süper Lig<br>
22.11.2025 14:30	ZECORNER KAYSERİSPOR	-	GAZİANTEP FUTBOL KULÜBÜ A.Ş.	Detaylar<br>
22.11.2025 17:00	İKAS EYÜPSPOR	-	MISIRLI.COM.TR FATİH KARAGÜMRÜK	Detaylar<br>
22.11.2025 20:00	GALATASARAY A.Ş.	-	GENÇLERBİRLİĞİ	Detaylar<br>
<br>
1. Lig<br>
21.11.2025 20:00	MANİSA FUTBOL KULÜBÜ	-	ADANA DEMİRSPOR A.Ş.	Detaylar<br>
22.11.2025 13:30	BOLUSPOR	-	AMED SPORTİF FAALİYETLER	Detaylar<br>
                        </div>
                    </div>
                    
                    <button type="submit" name="toplu_ekle" class="btn btn-success" style="margin-top: 15px;">
                        📥 Toplu Ekle
                    </button>
                </form>
                
                <?php if (isset($_SESSION['show_onay']) && $_SESSION['show_onay']): ?>
                    <?php
                    $maclar_parse = $_SESSION['maclar_parse'] ?? [];
                    $eslesmeyenler = $_SESSION['eslesmeyenler'] ?? [];
                    ?>
                    
                    <div style="margin-top: 40px; padding: 25px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px;">
                        <h3 style="color: #856404; margin-bottom: 20px;">⚠️ Eşleşmeyen Takımlar Bulundu</h3>
                        <p style="color: #856404; margin-bottom: 20px;">Aşağıdaki takımlar veritabanında bulunamadı. Lütfen mevcut takımlardan seçin veya yeni takım olarak ekleyin.</p>
                        
                        <form method="POST" id="onayForm">
                            <?php foreach ($eslesmeyenler as $index => $eslesmeyen): ?>
                                <div class="form-group" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
                                    <label style="color: #333; font-weight: 600; margin-bottom: 10px; display: block;">
                                        📌 Takım: <span style="color: #dc3545;"><?= htmlspecialchars($eslesmeyen['isim']) ?></span>
                                        <span style="font-size: 12px; color: #666; font-weight: normal;">(Orijinal: <?= htmlspecialchars($eslesmeyen['original']) ?>)</span>
                                    </label>
                                    
                                    <select name="takim_mapping[<?= htmlspecialchars($eslesmeyen['original']) ?>]" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                                        <option value="">Seçim Yapın...</option>
                                        <optgroup label="Mevcut Takımlar">
                                            <?php foreach ($takimlar as $takim): ?>
                                                <option value="<?= $takim['illegal_tespit_takim_id'] ?>">
                                                    <?= htmlspecialchars($takim['illegal_tespit_takim_adi']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <optgroup label="Yeni Takım Olarak Ekle">
                                            <option value="new_<?= htmlspecialchars($eslesmeyen['isim']) ?>">
                                                ➕ Yeni Takım: <?= htmlspecialchars($eslesmeyen['isim']) ?>
                                            </option>
                                        </optgroup>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                            
                            <input type="hidden" name="maclar_json" value='<?= htmlspecialchars(json_encode($maclar_parse)) ?>'>
                            <input type="hidden" name="takimlar_json" id="takimlar_json" value="">
                            
                            <div style="margin-top: 20px; display: flex; gap: 10px;">
                                <button type="submit" name="toplu_ekle_onay" class="btn btn-success" onclick="return prepareSubmit()">
                                    ✅ Onayla ve Kaydet
                                </button>
                                <button type="button" class="btn btn-danger" onclick="cancelOnay()">
                                    ❌ İptal
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <script>
                    function prepareSubmit() {
                        const form = document.getElementById('onayForm');
                        const selects = form.querySelectorAll('select[name^="takim_mapping"]');
                        const mapping = {};
                        const maclarJson = JSON.parse(form.querySelector('input[name="maclar_json"]').value);
                        
                        // Eşleşmeleri topla
                        let allSelected = true;
                        selects.forEach(select => {
                            if (!select.value) {
                                allSelected = false;
                                return;
                            }
                            
                            const originalName = select.name.match(/\[(.*?)\]/)[1];
                            const selectedValue = select.value;
                            
                            // Yeni takım mı kontrol et
                            if (selectedValue.startsWith('new_')) {
                                const newTeamName = selectedValue.substring(4);
                                // AJAX ile yeni takım ekle ve ID al
                                mapping[originalName] = 'NEW:' + newTeamName;
                            } else {
                                mapping[originalName] = selectedValue;
                            }
                        });
                        
                        if (!allSelected) {
                            alert('Lütfen tüm takımlar için seçim yapın!');
                            return false;
                        }
                        
                        // Mapping'i hidden input'a yaz
                        document.querySelector('input[name="takimlar_json"]').value = JSON.stringify(mapping);
                        return true;
                    }
                    
                    function cancelOnay() {
                        if (confirm('İşlemi iptal etmek istediğinizden emin misiniz?')) {
                            window.location.href = '<?= $_SERVER['PHP_SELF'] ?>?cancel=1';
                        }
                    }
                    </script>
                <?php endif; ?>
            </div>
            
            <!-- Maç Listesi -->
            <div id="mac-listesi" class="tab-content">
                <div class="table-container">
                    <h3 style="margin-bottom: 20px; color: #333;">📋 Kayıtlı Maçlar (<?= count($maclar) ?> adet)</h3>
                    
                    <?php if (count($maclar) > 0): ?>
                        <table id="macTable">
                            <thead>
                                <tr>
                                    <th class="sortable" onclick="sortTable(0)">ID</th>
                                    <th class="sortable" onclick="sortTable(1)">Tarih</th>
                                    <th class="sortable" onclick="sortTable(2)">Saat</th>
                                    <th class="sortable" onclick="sortTable(3)">Ev Sahibi</th>
                                    <th class="sortable" onclick="sortTable(4)">Deplasman</th>
                                    <th class="sortable" onclick="sortTable(5)">Lig</th>
                                    <th class="sortable" onclick="sortTable(6)">Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($maclar as $mac): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($mac['mac_afisi_id']) ?></td>
                                        <td><?= date('d.m.Y', strtotime($mac['mac_afisi_tarihi'])) ?></td>
                                        <td><?= date('H:i', strtotime($mac['mac_afisi_saati'])) ?></td>
                                        <td>
                                            <div style="font-weight: 600; color: #333;">
                                                <?= htmlspecialchars($mac['takim1_adi'] ?? 'Bilinmiyor') ?>
                                            </div>
                                            <?php if (!empty($mac['takim1_adi'])): ?>
                                                <div style="font-size: 11px; color: #666; margin-top: 2px;">
                                                    ID: <?= $mac['mac_afisi_takim1_id'] ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: #333;">
                                                <?= htmlspecialchars($mac['takim2_adi'] ?? 'Bilinmiyor') ?>
                                            </div>
                                            <?php if (!empty($mac['takim2_adi'])): ?>
                                                <div style="font-size: 11px; color: #666; margin-top: 2px;">
                                                    ID: <?= $mac['mac_afisi_takim2_id'] ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($mac['mac_afisi_lig']) ?></td>
                                        <td>
                                            <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; 
                                                color: <?= $mac['mac_afisi_durum'] ? '#155724' : '#721c24' ?>;
                                                background: <?= $mac['mac_afisi_durum'] ? '#d4edda' : '#f8d7da' ?>;">
                                                <?= $mac['mac_afisi_durum'] ? 'Aktif' : 'Pasif' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn" style="background: #ffc107; color: #000; padding: 8px 15px; font-size: 14px; margin-right: 5px;" onclick="editMac(<?= $mac['mac_afisi_id'] ?>, '<?= $mac['mac_afisi_tarihi'] ?>', '<?= $mac['mac_afisi_saati'] ?>', <?= $mac['mac_afisi_takim1_id'] ?? 'null' ?>, <?= $mac['mac_afisi_takim2_id'] ?? 'null' ?>, '<?= htmlspecialchars($mac['mac_afisi_lig'], ENT_QUOTES) ?>', <?= $mac['mac_afisi_durum'] ?>)">
                                                ✏️ Düzenle
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Bu maçı silmek istediğinizden emin misiniz?')">
                                                <input type="hidden" name="mac_afisi_id" value="<?= $mac['mac_afisi_id'] ?>">
                                                <button type="submit" name="mac_sil" class="btn btn-danger">🗑️ Sil</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: #666;">
                            <h4>📭 Henüz maç kaydı bulunmuyor</h4>
                            <p>Yukarıdaki sekmelerden maç ekleyebilirsiniz.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Düzenleme Modal -->
            <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
                <div style="background: white; border-radius: 15px; padding: 30px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
                    <h3 style="margin-bottom: 20px; color: #333;">✏️ Maç Düzenle</h3>
                    
                    <form method="POST" id="editForm">
                        <input type="hidden" name="mac_afisi_id" id="edit_mac_id">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_tarihi">📅 Maç Tarihi</label>
                                <input type="date" name="mac_afisi_tarihi" id="edit_tarihi" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_saati">🕐 Maç Saati</label>
                                <input type="time" name="mac_afisi_saati" id="edit_saati" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_takim1">🏠 Ev Sahibi Takım</label>
                                <select name="mac_afisi_takim1_id" id="edit_takim1" required>
                                    <option value="">Takım Seçiniz</option>
                                    <?php foreach ($takimlar as $takim): ?>
                                        <option value="<?= $takim['illegal_tespit_takim_id'] ?>">
                                            <?= htmlspecialchars($takim['illegal_tespit_takim_adi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_takim2">✈️ Deplasman Takım</label>
                                <select name="mac_afisi_takim2_id" id="edit_takim2" required>
                                    <option value="">Takım Seçiniz</option>
                                    <?php foreach ($takimlar as $takim): ?>
                                        <option value="<?= $takim['illegal_tespit_takim_id'] ?>">
                                            <?= htmlspecialchars($takim['illegal_tespit_takim_adi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_lig">🏆 Lig</label>
                                <select name="mac_afisi_lig" id="edit_lig" required>
                                    <option value="">Lig Seçin</option>
                                    <option value="Süper Lig">Süper Lig</option>
                                    <option value="TFF 1. Lig">TFF 1. Lig</option>
                                    <option value="TFF 2. Lig">TFF 2. Lig</option>
                                    <option value="TFF 3. Lig">TFF 3. Lig</option>
                                    <option value="Türkiye Kupası">Türkiye Kupası</option>
                                    <option value="UEFA Şampiyonlar Ligi">UEFA Şampiyonlar Ligi</option>
                                    <option value="UEFA Avrupa Ligi">UEFA Avrupa Ligi</option>
                                    <option value="UEFA Konferans Ligi">UEFA Konferans Ligi</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_durum">📊 Durum</label>
                                <div style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; background: white;">
                                    <input type="checkbox" name="mac_afisi_durum" id="edit_durum" style="transform: scale(1.2);">
                                    <label for="edit_durum" style="margin: 0; font-weight: normal; cursor: pointer;">Aktif</label>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <button type="submit" name="mac_guncelle" class="btn btn-primary">
                                💾 Güncelle
                            </button>
                            <button type="button" class="btn btn-danger" onclick="closeEditModal()">
                                ❌ İptal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Düzenleme modal fonksiyonları
        function editMac(id, tarihi, saati, takim1_id, takim2_id, lig, durum) {
            document.getElementById('edit_mac_id').value = id;
            document.getElementById('edit_tarihi').value = tarihi;
            document.getElementById('edit_saati').value = saati;
            document.getElementById('edit_takim1').value = takim1_id || '';
            document.getElementById('edit_takim2').value = takim2_id || '';
            document.getElementById('edit_lig').value = lig;
            document.getElementById('edit_durum').checked = durum == 1;
            
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Modal dışına tıklayınca kapat
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        // Tab geçişi
        function showTab(tabName) {
            // Tüm tab içeriklerini gizle
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));
            
            // Tüm tab butonlarının active sınıfını kaldır
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Seçilen tab'ı göster
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        // Tablo sıralama
        let sortDirection = false;
        
        function sortTable(columnIndex) {
            const table = document.getElementById('macTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = Array.from(tbody.getElementsByTagName('tr'));
            
            // Sıralama yönünü değiştir
            sortDirection = !sortDirection;
            
            // Tüm başlıklardan sıralama sınıfını kaldır
            const headers = table.getElementsByTagName('th');
            for (let i = 0; i < headers.length; i++) {
                headers[i].classList.remove('sort-asc', 'sort-desc');
            }
            
            // Aktif başlığa sıralama sınıfını ekle
            headers[columnIndex].classList.add(sortDirection ? 'sort-asc' : 'sort-desc');
            
            // Satırları sırala
            rows.sort((a, b) => {
                const cellA = a.getElementsByTagName('td')[columnIndex].textContent.trim();
                const cellB = b.getElementsByTagName('td')[columnIndex].textContent.trim();
                
                // Sayısal karşılaştırma (ID için)
                if (columnIndex === 0) {
                    return sortDirection ? 
                        parseInt(cellA) - parseInt(cellB) : 
                        parseInt(cellB) - parseInt(cellA);
                }
                
                // Tarih karşılaştırması
                if (columnIndex === 1) {
                    const dateA = new Date(cellA.split('.').reverse().join('-'));
                    const dateB = new Date(cellB.split('.').reverse().join('-'));
                    return sortDirection ? dateA - dateB : dateB - dateA;
                }
                
                // Saat karşılaştırması
                if (columnIndex === 2) {
                    return sortDirection ? 
                        cellA.localeCompare(cellB) : 
                        cellB.localeCompare(cellA);
                }
                
                // Metin karşılaştırması
                return sortDirection ? 
                    cellA.localeCompare(cellB, 'tr') : 
                    cellB.localeCompare(cellA, 'tr');
            });
            
            // Sıralanmış satırları tekrar ekle
            rows.forEach(row => tbody.appendChild(row));
        }
        
        // Sayfa yüklendiğinde varsayılan olarak tarihe göre sırala
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('macTable')) {
                sortTable(1); // Tarih sütununa göre sırala
            }
        });
    </script>
</body>
</html>