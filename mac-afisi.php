<?php
// Bağımsız maç afişi oluşturucu sayfası
// Standalone PHP dosyası - Login gerektirmez, direkt erişilebilir

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı bağlantı bilgileri
$db_config = [
    'hostname' => 'localhost',
    'username' => 'ilekasoft_crmuser',
    'password' => 'KaleW356!',
    'database' => 'ilekasoft_crmdb'
];

// Veritabanı PDO bağlantısı
function getPDOConnection() {
    global $db_config;
    $hostname = $db_config['hostname'];
    $username = $db_config['username']; 
    $password = $db_config['password'];
    $database = $db_config['database'];
    
    try {
        $pdo = new PDO("mysql:host=$hostname;dbname=$database;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        return null;
    }
}

/**
 * Türkçe karakterleri doğru büyük harfe çevir
 */
function turkishUpper($text) {
    $search = ['i', 'ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'I', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'];
    $replace = ['İ', 'I', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç', 'İ', 'I', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'];
    return mb_strtoupper(str_replace($search, $replace, $text), 'UTF-8');
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
 * Takım logosunu bul ve indir (Wikipedia API + Cache)
 */
function getTeamLogo($team_name) {
    // Cache klasörü
    $cache_dir = __DIR__ . '/assets/img/team-logos/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }
    
    // Dosya adı oluştur (Türkçe karakterleri düzgün dönüştür)
    $safe_name = $team_name;
    $safe_name = str_replace(['ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ş', 'Ş', 'ö', 'Ö', 'ç', 'Ç'], 
                             ['i', 'i', 'g', 'g', 'u', 'u', 's', 's', 'o', 'o', 'c', 'c'], 
                             $safe_name);
    $safe_name = preg_replace('/[^a-z0-9]+/i', '-', strtolower($safe_name));
    $safe_name = trim($safe_name, '-');
    $cache_file = $cache_dir . $safe_name . '.png';
    
    // Cache'de varsa kullan (ÖNCE kontrol et - manuel liste için gereksiz indirme olmasın)
    if (file_exists($cache_file)) {
        return 'assets/img/team-logos/' . $safe_name . '.png';
    }
    
    // Manuel logo listesi - Dosya isimleri sistemle uyumlu (Türkçe→İngilizce, küçük harf, tire)
    $manual_logos = [
        // Süper Lig
        'Alanyaspor' => 'alanyaspor.png',
        'Antalyaspor' => 'antalyaspor.png',
        'Beşiktaş' => 'besiktas.png',
        'Çaykur Rizespor' => 'rizespor.png',
        'Rizespor' => 'rizespor.png',
        'Eyüpspor' => 'eyupspor.png',
        'Fatih Karagümrük' => 'fatih-karagumruk.png',
        'Fenerbahçe' => 'fenerbahce.png',
        'Galatasaray' => 'galatasaray.png',
        'Gaziantep' => 'gaziantep.png',
        'Göztepe' => 'goztepe.png',
        'İstanbul Başakşehir' => 'basaksehir.png',
        'Başakşehir' => 'basaksehir.png',
        'Kasımpaşa' => 'kasimpasa.png',
        'Kayserispor' => 'kayserispor.png',
        'Konyaspor' => 'konyaspor.png',
        'Samsunspor' => 'samsunspor.png',
        'Trabzonspor' => 'trabzonspor.png',
        'Bodrum' => 'bodrum.png',
        'Sivasspor' => 'sivasspor.png',
        'Hatayspor' => 'hatayspor.png',
        
        // TFF 1. Lig
        'Adana Demirspor' => 'adana-demirspor.png',
        'Bandırmaspor' => 'bandirmaspor.png',
        'Boluspor' => 'boluspor.png',
        'İstanbulspor' => 'istanbulspor.png',
        'Erzurumspor' => 'erzurumspor.png',
        'Iğdır' => 'igdir.png',
        'Amed' => 'amed.png',
        'Çorum' => 'corum.png',
        'Ümraniyespor' => 'umraniyespor.png',
        'Sakaryaspor' => 'sakaryaspor.png',
        'A.Keçiörengücü' => 'a-keciorengücu.png',
        'Keçiörengücü' => 'a-keciorengücu.png',
        'Manisa' => 'manisa.png',
        'Pendikspor' => 'pendikspor.png',
        'Sarıyer' => 'sariyer.png',
        'Serikspor' => 'serikspor.png',
        'Vanspor' => 'vanspor.png',
        'Kocaelispor' => 'kocaelispor.png',
        'Gençlerbirliği' => 'genclerbirligi.png',
    ];
    
    // Manuel listede var mı kontrol et
    $logo_file = null;
    foreach ($manual_logos as $team => $filename) {
        if (stripos($team_name, $team) !== false || stripos($team, $team_name) !== false) {
            $logo_file = $filename;
            break;
        }
    }
    
    // Manuel listede varsa dosyayı kullan
    if ($logo_file) {
        $manual_path = $cache_dir . $logo_file;
        if (file_exists($manual_path)) {
            return 'assets/img/team-logos/' . $logo_file;
        }
    }
    
    // Hiçbir şey bulunamazsa placeholder
    return createPlaceholderLogo($team_name, $safe_name);
}

/**
 * Wikipedia'dan logo ara
 */
function searchWikipediaLogo($team_name) {
    // Wikipedia API ile arama
    $search_url = 'https://tr.wikipedia.org/w/api.php?' . http_build_query([
        'action' => 'query',
        'format' => 'json',
        'titles' => $team_name,
        'prop' => 'pageimages',
        'piprop' => 'original',
        'pilicense' => 'any'
    ]);
    
    $response = @file_get_contents($search_url);
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['query']['pages'])) {
            foreach ($data['query']['pages'] as $page) {
                if (isset($page['original']['source'])) {
                    return $page['original']['source'];
                }
            }
        }
    }
    
    return null;
}

/**
 * Placeholder logo oluştur
 */
function createPlaceholderLogo($team_name, $safe_name) {
    $cache_dir = __DIR__ . '/assets/img/team-logos/';
    // Otomatik oluşturulan logoların başına _ ekle
    $cache_file = $cache_dir . '_' . $safe_name . '.png';
    
    // 200x200 boyutunda şeffaf PNG
    $img = imagecreatetruecolor(200, 200);
    
    // Şeffaflık
    imagealphablending($img, false);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    imagesavealpha($img, true);
    
    // Daire çiz (takım rengi olarak rastgele)
    $color = imagecolorallocate($img, rand(50, 200), rand(50, 200), rand(50, 200));
    imagefilledellipse($img, 100, 100, 180, 180, $color);
    
    // İlk harfleri yaz
    $white = imagecolorallocate($img, 255, 255, 255);
    $initials = mb_strtoupper(mb_substr($team_name, 0, 2, 'UTF-8'), 'UTF-8');
    
    $font = __DIR__ . '/assets/fonts/arial-bold.ttf';
    if (file_exists($font)) {
        imagettftext($img, 60, 0, 60, 125, $white, $font, $initials);
    }
    
    imagepng($img, $cache_file);
    imagedestroy($img);
    
    return 'assets/img/team-logos/_' . $safe_name . '.png';
}

/**
 * Veritabanından maç sonuçlarını çek
 */
function fetchMatchesFromDatabase() {
    $pdo = getPDOConnection();
    if (!$pdo) {
        return [];
    }
    
    try {
        // Sadece aktif ve gelecek maçları getir - Takım ID'leri ile JOIN
        $sql = "SELECT 
                    m.*,
                    t1.illegal_tespit_takim_adi AS takim1_adi,
                    t2.illegal_tespit_takim_adi AS takim2_adi
                FROM mac_afisi m
                LEFT JOIN illegal_tespit_takimlar t1 ON m.mac_afisi_takim1_id = t1.illegal_tespit_takim_id
                LEFT JOIN illegal_tespit_takimlar t2 ON m.mac_afisi_takim2_id = t2.illegal_tespit_takim_id
                WHERE m.mac_afisi_durum = 1 
                AND m.mac_afisi_tarihi >= CURDATE() 
                ORDER BY m.mac_afisi_tarihi ASC, m.mac_afisi_saati ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $maclar = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $formatted_matches = [];
        foreach ($maclar as $mac) {
            // Takım isimlerini belirle (önce ID'den gelen, yoksa eski text alanından)
            $takim1_adi = !empty($mac['takim1_adi']) ? $mac['takim1_adi'] : $mac['mac_afisi_takim1'];
            $takim2_adi = !empty($mac['takim2_adi']) ? $mac['takim2_adi'] : $mac['mac_afisi_takim2'];
            
            // API formatına dönüştür
            $formatted_matches[] = [
                'fixture' => [
                    'date' => $mac['mac_afisi_tarihi'] . ' ' . $mac['mac_afisi_saati']
                ],
                'teams' => [
                    'home' => [
                        'name' => cleanTeamName($takim1_adi),
                        'logo' => getTeamLogo(cleanTeamName($takim1_adi))
                    ],
                    'away' => [
                        'name' => cleanTeamName($takim2_adi),
                        'logo' => getTeamLogo(cleanTeamName($takim2_adi))
                    ]
                ],
                'league' => ['name' => $mac['mac_afisi_lig']]
            ];
        }
        
        return $formatted_matches;
    } catch(PDOException $e) {
        return [];
    }
}

/**
 * Tüm takımları listele (dropdown için)
 */
function getAllTeams() {
    $pdo = getPDOConnection();
    if (!$pdo) {
        return [];
    }
    
    try {
        $sql = "SELECT illegal_tespit_takim_id, illegal_tespit_takim_adi 
                FROM illegal_tespit_takimlar 
                ORDER BY illegal_tespit_takim_adi ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Form gönderildi mi kontrol et
$afisolustur = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['afisolustur'])) {
    $afisolustur = true;
}

// Logo cache temizle isteği
if (isset($_GET['temizle_cache']) && $_GET['temizle_cache'] === '1') {
    // Logo cache temizle
    $cache_dir = __DIR__ . '/assets/img/team-logos/';
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '*.png');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Maç verilerini veritabanından çek
$maclar = [];
$veri_kaynagi = 'Veritabanı';

$maclar = fetchMatchesFromDatabase();

// Takımları getir (dropdown için)
$takimlar = getAllTeams();

// Şablonları listele
$sablon_klasoru = __DIR__ . '/assets/img/mac-afis/';
$sablonlar = [];
if (is_dir($sablon_klasoru)) {
    $dosyalar = scandir($sablon_klasoru);
    foreach ($dosyalar as $dosya) {
        if (in_array(strtolower(pathinfo($dosya, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])) {
            $sablonlar[] = $dosya;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maç Afişi Oluşturucu</title>
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
            max-width: 800px;
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
        
        .form-container {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .preview-container {
            margin-top: 30px;
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .preview-container img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            margin-bottom: 20px;
        }
        
        .btn-download {
            display: inline-block;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 5px;
        }
        
        .btn-download:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-share {
            display: inline-block;
            padding: 12px 30px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 5px;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }
        
        .btn-share:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        
        .share-buttons {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-whatsapp {
            background: #25D366;
        }
        
        .btn-whatsapp:hover {
            background: #1da851;
        }
        
        .btn-facebook {
            background: #1877f2;
        }
        
        .btn-facebook:hover {
            background: #145dbf;
        }
        
        .btn-twitter {
            background: #1DA1F2;
        }
        
        .btn-twitter:hover {
            background: #0d8bd9;
        }
        
        .info-text {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <form method="POST" action="">
                <?php if (count($maclar) > 0): ?>
                <div class="form-group">
                    <label for="mac_index">⚽ Maç Seçimi</label>
                    <select name="mac_index" id="mac_index" onchange="updateMatchInfo()" required>
                        <option value="">Maç Seçimi Yapın</option>
                        <?php 
                        $bugun = new DateTime();
                        $bugun->setTime(0, 0, 0); // Günün başlangıcı
                        
                        // Maçları yeniden veritabanından çek (ID bilgisi ile)
                        $pdo = getPDOConnection();
                        if ($pdo) {
                            $sql = "SELECT 
                                        m.*,
                                        t1.illegal_tespit_takim_adi AS takim1_adi,
                                        t2.illegal_tespit_takim_adi AS takim2_adi
                                    FROM mac_afisi m
                                    LEFT JOIN illegal_tespit_takimlar t1 ON m.mac_afisi_takim1_id = t1.illegal_tespit_takim_id
                                    LEFT JOIN illegal_tespit_takimlar t2 ON m.mac_afisi_takim2_id = t2.illegal_tespit_takim_id
                                    WHERE m.mac_afisi_durum = 1 
                                    AND m.mac_afisi_tarihi >= CURDATE() 
                                    ORDER BY m.mac_afisi_tarihi ASC, m.mac_afisi_saati ASC";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            $maclar_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($maclar_raw as $index => $mac_raw): 
                                $tarih = new DateTime($mac_raw['mac_afisi_tarihi'] . ' ' . $mac_raw['mac_afisi_saati']);
                                
                                // Bugünden önceki maçları atla
                                if ($tarih < $bugun) {
                                    continue;
                                }
                                
                                $tarih_str = $tarih->format('d.m.Y H:i');
                                $ev = !empty($mac_raw['takim1_adi']) ? $mac_raw['takim1_adi'] : $mac_raw['mac_afisi_takim1'];
                                $deplasman = !empty($mac_raw['takim2_adi']) ? $mac_raw['takim2_adi'] : $mac_raw['mac_afisi_takim2'];
                                $lig = $mac_raw['mac_afisi_lig'];
                                
                                // Gün adını bul (Türkçe)
                                $gun_adi_ing = $tarih->format('l');
                                $gun_adlari = [
                                    'Monday' => 'Pazartesi',
                                    'Tuesday' => 'Salı',
                                    'Wednesday' => 'Çarşamba',
                                    'Thursday' => 'Perşembe',
                                    'Friday' => 'Cuma',
                                    'Saturday' => 'Cumartesi',
                                    'Sunday' => 'Pazar'
                                ];
                                $gun_adi = $gun_adlari[$gun_adi_ing] ?? '';
                        ?>
                            <option value="<?= $index ?>" 
                                    data-ev-id="<?= $mac_raw['mac_afisi_takim1_id'] ?>"
                                    data-deplasman-id="<?= $mac_raw['mac_afisi_takim2_id'] ?>"
                                    data-ev="<?= htmlspecialchars($ev) ?>"
                                    data-deplasman="<?= htmlspecialchars($deplasman) ?>"
                                    data-tarih="<?= $tarih->format('d.m.Y') ?>"
                                    data-gun="<?= htmlspecialchars($gun_adi) ?>"
                                    data-saat="<?= $tarih->format('H:i') ?>"
                                    data-lig="<?= htmlspecialchars($lig) ?>">
                                <?= $tarih->format('d.m.Y H:i') ?> - <?= htmlspecialchars($gun_adi) ?> | <?= htmlspecialchars($ev) ?> vs <?= htmlspecialchars($deplasman) ?> (<?= htmlspecialchars($lig) ?>)
                            </option>
                        <?php 
                            endforeach;
                        }
                        ?>
                    </select>
                    <p class="info-text">Listeden bir maç seçin</p>
                </div>
                <?php else: ?>
                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; text-align: center;">
                    <h4 style="color: #856404; margin-bottom: 10px;">📭 Maç Bulunamadı</h4>
                    <p style="color: #856404;">Henüz gelecek tarihli maç kaydı bulunmuyor.</p>
                    <a href="mac-afisi_yukle.php" style="color: #667eea; text-decoration: underline; margin-top: 10px; display: inline-block;">Maç Eklemek İçin Tıklayın</a>
                </div>
                <?php endif; ?>
                
                <input type="hidden" name="ev_sahibi_id" id="ev_sahibi_id">
                <input type="hidden" name="deplasman_id" id="deplasman_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="mac_tarihi">📅 Maç Tarihi</label>
                        <input type="text" name="mac_tarihi" id="mac_tarihi" placeholder="Örn: 27.10.2024" value="<?= $_POST['mac_tarihi'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="mac_saati">🕐 Maç Saati</label>
                        <input type="text" name="mac_saati" id="mac_saati" placeholder="Örn: 19:00" value="<?= $_POST['mac_saati'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="sablon">🎨 Maç Afişi Şablonu</label>
                    <select name="sablon" id="sablon" required>
                        <option value="">Şablon Seçiniz</option>
                        <?php foreach ($sablonlar as $sablon): ?>
                            <option value="<?= $sablon ?>" <?= (isset($_POST['sablon']) && $_POST['sablon'] === $sablon) ? 'selected' : '' ?>>
                                <?= pathinfo($sablon, PATHINFO_FILENAME) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="info-text">Şablon tasarımını seçin</p>
                </div>
                
                <div class="form-group">
                    <label for="isletme_adi">🏢 İşletme Adı</label>
                    <input type="text" name="isletme_adi" id="isletme_adi" placeholder="Örn: REX GASTRO PUB" value="<?= $_POST['isletme_adi'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="isletme_tel">📞 İşletme Telefonu</label>
                    <input type="tel" name="isletme_tel" id="isletme_tel" placeholder="Örn: 0 543 627 62 62" value="<?= $_POST['isletme_tel'] ?? '' ?>" required>
                    <p class="info-text">Boşluklarla veya tire ile yazabilirsiniz</p>
                </div>
                
                <button type="submit" name="afisolustur" class="btn-submit">
                    🎨 Afiş Oluştur
                </button>
            </form>
            
            <script>
            function updateMatchInfo() {
                const select = document.getElementById('mac_index');
                const selectedOption = select.options[select.selectedIndex];
                
                if (selectedOption.value !== '') {
                    const tarih = selectedOption.getAttribute('data-tarih') || '';
                    const gun = selectedOption.getAttribute('data-gun') || '';
                    const tarihGun = tarih + (gun ? ' ' + gun : '');
                    
                    // Takım ID'lerini hidden input'a yaz
                    const evId = selectedOption.getAttribute('data-ev-id');
                    const deplasmanId = selectedOption.getAttribute('data-deplasman-id');
                    
                    document.getElementById('ev_sahibi_id').value = evId || '';
                    document.getElementById('deplasman_id').value = deplasmanId || '';
                    
                    document.getElementById('mac_tarihi').value = tarihGun;
                    document.getElementById('mac_saati').value = selectedOption.getAttribute('data-saat') || '';
                } else {
                    // Temizle
                    document.getElementById('ev_sahibi_id').value = '';
                    document.getElementById('deplasman_id').value = '';
                    document.getElementById('mac_tarihi').value = '';
                    document.getElementById('mac_saati').value = '';
                }
            }
            </script>
            
            <?php if ($afisolustur): ?>
                <?php
                // Afiş oluşturma işlemi
                $sablon = $_POST['sablon'] ?? '';
                $isletme_adi = $_POST['isletme_adi'] ?? '';
                $isletme_tel = $_POST['isletme_tel'] ?? '';
                $ev_sahibi_id = $_POST['ev_sahibi_id'] ?? '';
                $deplasman_id = $_POST['deplasman_id'] ?? '';
                $mac_tarihi = $_POST['mac_tarihi'] ?? '';
                $mac_saati = $_POST['mac_saati'] ?? '';
                
                // Takım isimlerini ID'den çek
                $ev_sahibi = '';
                $deplasman = '';
                
                $pdo = getPDOConnection();
                if ($pdo && !empty($ev_sahibi_id)) {
                    $stmt = $pdo->prepare("SELECT illegal_tespit_takim_adi FROM illegal_tespit_takimlar WHERE illegal_tespit_takim_id = ?");
                    $stmt->execute([$ev_sahibi_id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $ev_sahibi = $result['illegal_tespit_takim_adi'];
                    }
                }
                
                if ($pdo && !empty($deplasman_id)) {
                    $stmt = $pdo->prepare("SELECT illegal_tespit_takim_adi FROM illegal_tespit_takimlar WHERE illegal_tespit_takim_id = ?");
                    $stmt->execute([$deplasman_id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $deplasman = $result['illegal_tespit_takim_adi'];
                    }
                }
                
                // Şablon yolunu oluştur
                $template_path = __DIR__ . '/assets/img/mac-afis/' . $sablon;
                
                if (!file_exists($template_path)) {
                    echo '<div class="preview-container" style="background: #fff3cd; color: #856404; padding: 20px;">';
                    echo '<strong>⚠️ Hata:</strong> Şablon dosyası bulunamadı.';
                    echo '</div>';
                } else {
                    // Dosya uzantısına göre görsel oluştur
                    $ext = strtolower(pathinfo($template_path, PATHINFO_EXTENSION));
                    if ($ext === 'png') {
                        $img = imagecreatefrompng($template_path);
                    } elseif (in_array($ext, ['jpg', 'jpeg'])) {
                        $img = imagecreatefromjpeg($template_path);
                    } else {
                        $img = false;
                    }
                    
                    if ($img === false) {
                        echo '<div class="preview-container" style="background: #f8d7da; color: #721c24; padding: 20px;">';
                        echo '<strong>❌ Hata:</strong> Görsel yüklenemedi.';
                        echo '</div>';
                    } else {
                        // Renk tanımla
                        $altin_rengi = imagecolorallocate($img, 218, 165, 32);
                        $beyaz = imagecolorallocate($img, 255, 255, 255);
                        $mor = imagecolorallocate($img, 139, 69, 172);
                        $siyah = imagecolorallocate($img, 0, 0, 0);
                        
                        // Font yolu (proje içindeki font)
                        $font_bold = __DIR__ . '/assets/fonts/arial-bold.ttf';
                        $font_normal = __DIR__ . '/assets/fonts/arial.ttf';
                        
                        // Hangisi varsa kullan
                        $font = file_exists($font_bold) ? $font_bold : $font_normal;
                        
                        // Görsel boyutları
                        $genislik = imagesx($img);
                        $yukseklik = imagesy($img);
                        
                        // 1. Tarih ve saat bilgisi (EN ÜSTTE)
                        if (!empty($mac_tarihi) || !empty($mac_saati)) {
                            $tarih_y_start = 180; // Başlangıç pozisyonu (120'den 180'e)
                            
                            // Tarihi parse et ve gün adını bul
                            if (!empty($mac_tarihi)) {
                                // Eğer gün adı varsa (örn: "27.10.2024 Pazar"), ayır
                                $tarih_gun_parts = explode(' ', $mac_tarihi);
                                $sadece_tarih = $tarih_gun_parts[0]; // "27.10.2024"
                                $mevcut_gun_adi = isset($tarih_gun_parts[1]) ? $tarih_gun_parts[1] : ''; // "Pazar" veya boş
                                
                                $tarih_parts = explode('.', $sadece_tarih);
                                if (count($tarih_parts) === 3) {
                                    $gun = $tarih_parts[0];
                                    $ay = $tarih_parts[1];
                                    $yil = $tarih_parts[2];
                                    
                                    // Eğer gün adı yoksa hesapla
                                    if (empty($mevcut_gun_adi)) {
                                        $timestamp = mktime(0, 0, 0, $ay, $gun, $yil);
                                        $gun_adi_ing = date('l', $timestamp);
                                        $gun_adlari = [
                                            'Monday' => 'Pazartesi',
                                            'Tuesday' => 'Salı',
                                            'Wednesday' => 'Çarşamba',
                                            'Thursday' => 'Perşembe',
                                            'Friday' => 'Cuma',
                                            'Saturday' => 'Cumartesi',
                                            'Sunday' => 'Pazar'
                                        ];
                                        $gun_adi = $gun_adlari[$gun_adi_ing] ?? '';
                                    } else {
                                        $gun_adi = $mevcut_gun_adi;
                                    }
                                    
                                    // 1. satır: Tarih + Gün adı
                                    $tarih_text = $sadece_tarih . ' ' . $gun_adi;
                                    $bbox = imagettfbbox(72, 0, $font, $tarih_text);
                                    $text_width = $bbox[2] - $bbox[0];
                                    $tarih_x = ($genislik - $text_width) / 2;
                                    
                                    imagettftext($img, 72, 0, $tarih_x, $tarih_y_start, $altin_rengi, $font, $tarih_text);
                                }
                            }
                            
                            // 2. satır: Saat
                            if (!empty($mac_saati)) {
                                $saat_text = 'Saat: ' . $mac_saati;
                                $bbox = imagettfbbox(80, 0, $font, $saat_text);
                                $text_width = $bbox[2] - $bbox[0];
                                $saat_x = ($genislik - $text_width) / 2;
                                
                                imagettftext($img, 80, 0, $saat_x, $tarih_y_start + 90, $altin_rengi, $font, $saat_text);
                            }
                        }
                        
                        // 2. Takım logolarını yükle ve ekle (Tarih altında) - Temizlenmiş isimlerle
                        $ev_sahibi_temiz = cleanTeamName($ev_sahibi);
                        $deplasman_temiz = cleanTeamName($deplasman);
                        
                        $ev_logo_relative = getTeamLogo($ev_sahibi_temiz);
                        $deplasman_logo_relative = getTeamLogo($deplasman_temiz);
                        
                        // Windows için backslash kullan
                        $ev_logo_path = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ev_logo_relative);
                        $deplasman_logo_path = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $deplasman_logo_relative);
                        
                        $logo_size = 400; // Logo boyutu (2 katı büyütüldü)
                        $logo_y = 320; // Tarihin altında (280'den 320'ye - üstte daha fazla boşluk)
                        
                        // Ana görselde alpha blending'i aç
                        imagealphablending($img, true);
                        imagesavealpha($img, true);
                        
                        // Ev sahibi logosu (sol)
                        if (file_exists($ev_logo_path)) {
                            $ev_logo = @imagecreatefrompng($ev_logo_path);
                            if ($ev_logo !== false) {
                                // Logo için alpha blending
                                imagealphablending($ev_logo, true);
                                imagesavealpha($ev_logo, true);
                                
                                imagecopyresampled($img, $ev_logo, 
                                    ($genislik / 2) - $logo_size - 100, $logo_y, 
                                    0, 0, 
                                    $logo_size, $logo_size, 
                                    imagesx($ev_logo), imagesy($ev_logo));
                                imagedestroy($ev_logo);
                            }
                        }
                        
                        // Deplasman logosu (sağ)
                        if (file_exists($deplasman_logo_path)) {
                            $deplasman_logo = @imagecreatefrompng($deplasman_logo_path);
                            if ($deplasman_logo !== false) {
                                // Logo için alpha blending
                                imagealphablending($deplasman_logo, true);
                                imagesavealpha($deplasman_logo, true);
                                
                                imagecopyresampled($img, $deplasman_logo, 
                                    ($genislik / 2) + 100, $logo_y, 
                                    0, 0, 
                                    $logo_size, $logo_size, 
                                    imagesx($deplasman_logo), imagesy($deplasman_logo));
                                imagedestroy($deplasman_logo);
                            }
                        }
                        
                        // 3. Takım isimlerini yaz (Logoların altında - alt alta)
                        if (!empty($ev_sahibi) && !empty($deplasman)) {
                            $font_size_mac = 110; // 90'dan 110'a büyütüldü
                            $max_genislik = $genislik - 100;
                            $mac_y_start = $logo_y + $logo_size + 180; // Logoların altında (140'tan 180'e - altta daha fazla boşluk)
                            
                            // Sarı renk (altın sarısı)
                            $sari = imagecolorallocate($img, 255, 215, 0);
                            
                            // Ev sahibi takım (üstte) - Temizlenmiş isim kullan
                            $ev_sahibi_upper = turkishUpper($ev_sahibi_temiz);
                            $font_size_ev = $font_size_mac;
                            
                            // Ev sahibi için boyut ayarla
                            do {
                                $bbox = imagettfbbox($font_size_ev, 0, $font, $ev_sahibi_upper);
                                $text_width = $bbox[2] - $bbox[0];
                                
                                if ($text_width > $max_genislik && $font_size_ev > 20) {
                                    $font_size_ev -= 2;
                                } else {
                                    break;
                                }
                            } while ($font_size_ev > 20);
                            
                            $bbox = imagettfbbox($font_size_ev, 0, $font, $ev_sahibi_upper);
                            $text_width = $bbox[2] - $bbox[0];
                            $ev_x = ($genislik - $text_width) / 2;
                            
                            imagettftext($img, $font_size_ev, 0, $ev_x, $mac_y_start, $sari, $font, $ev_sahibi_upper);
                            
                            // Deplasman takım (altta) - Temizlenmiş isim kullan
                            $deplasman_upper = turkishUpper($deplasman_temiz);
                            $font_size_deplasman = $font_size_mac;
                            
                            // Deplasman için boyut ayarla
                            do {
                                $bbox = imagettfbbox($font_size_deplasman, 0, $font, $deplasman_upper);
                                $text_width = $bbox[2] - $bbox[0];
                                
                                if ($text_width > $max_genislik && $font_size_deplasman > 20) {
                                    $font_size_deplasman -= 2;
                                } else {
                                    break;
                                }
                            } while ($font_size_deplasman > 20);
                            
                            $bbox = imagettfbbox($font_size_deplasman, 0, $font, $deplasman_upper);
                            $text_width = $bbox[2] - $bbox[0];
                            $deplasman_x = ($genislik - $text_width) / 2;
                            
                            imagettftext($img, $font_size_deplasman, 0, $deplasman_x, $mac_y_start + 200, $sari, $font, $deplasman_upper);
                        }
                        
                        // 4. İşletme bilgilerini yaz (altta - mor arka planda)
                        $isletme_y = $yukseklik - 95;
                        
                        // İşletme adı için font boyutu ayarla (uzun ise küçült)
                        $isletme_adi_upper = turkishUpper($isletme_adi);
                        $font_size_adi = 85; // 68'den 85'e büyütüldü
                        $max_genislik = $genislik - 80; // Kenarlardan boşluk
                        
                        // İşletme adı sığıyor mu kontrol et
                        do {
                            $bbox_adi = imagettfbbox($font_size_adi, 0, $font, $isletme_adi_upper);
                            $adi_genislik = $bbox_adi[2] - $bbox_adi[0];
                            
                            if ($adi_genislik > $max_genislik && $font_size_adi > 16) {
                                $font_size_adi -= 1;
                            } else {
                                break;
                            }
                        } while ($font_size_adi > 16);
                        
                        // Yeniden hesapla (son boyut ile)
                        $bbox_adi = imagettfbbox($font_size_adi, 0, $font, $isletme_adi_upper);
                        $adi_genislik = $bbox_adi[2] - $bbox_adi[0];
                        $adi_x = ($genislik - $adi_genislik) / 2;
                        
                        // Telefonu ortala
                        $bbox_tel = imagettfbbox(64, 0, $font, $isletme_tel);
                        $tel_genislik = $bbox_tel[2] - $bbox_tel[0];
                        $tel_x = ($genislik - $tel_genislik) / 2;
                        
                        imagettftext($img, $font_size_adi, 0, $adi_x, $isletme_y, $beyaz, $font, $isletme_adi_upper);
                        imagettftext($img, 64, 0, $tel_x, $isletme_y + 80, $beyaz, $font, $isletme_tel);
                        
                        // Görsel kaydet
                        $output_dir = __DIR__ . '/tmp/';
                        if (!is_dir($output_dir)) {
                            mkdir($output_dir, 0777, true);
                        }
                        
                        $filename = 'mac_afisi_' . time() . '.' . $ext;
                        $output_path = $output_dir . $filename;
                        
                        // Dosya tipine göre kaydet
                        if ($ext === 'png') {
                            imagepng($img, $output_path, 9);
                        } else {
                            imagejpeg($img, $output_path, 95);
                        }
                        imagedestroy($img);
                        
                        // Önizleme göster
                        $afiş_url = 'https://' . $_SERVER['HTTP_HOST'] . '/tmp/' . $filename;
                        
                        echo '<div class="preview-container">';
                        echo '<h3 style="margin-bottom: 20px; color: #333;">✅ Afişiniz Hazır!</h3>';
                        echo '<img src="tmp/' . $filename . '" alt="Maç Afişi" id="afis-img">';
                        echo '<br>';
                        echo '<div style="margin-top: 20px;">';
                        echo '<a href="tmp/' . $filename . '" download="mac-afisi.' . $ext . '" class="btn-download">📥 Afişi İndir</a>';
                        echo '<button onclick="shareImage(\'' . $afiş_url . '\')" class="btn-share">📤 Paylaş</button>';
                        echo '</div>';
                        echo '</div>';
                        
                        // JavaScript - Görsel paylaşma
                        echo '<script>
                        async function shareImage(imageUrl) {
                            try {
                                // Görseli fetch ile al
                                const response = await fetch(imageUrl);
                                const blob = await response.blob();
                                const file = new File([blob], "mac-afisi.jpg", { type: blob.type });
                                
                                // Web Share API ile paylaş
                                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                                    await navigator.share({
                                        files: [file],
                                        title: "Maç Afişi",
                                        text: "' . $isletme_adi_upper . '"
                                    });
                                } else {
                                    // Desteklemiyorsa WhatsApp\'a yönlendir
                                    window.open("https://wa.me/?text=Maç%20afişimizi%20görün!%20" + encodeURIComponent(imageUrl), "_blank");
                                }
                            } catch (error) {
                                console.error("Paylaşım hatası:", error);
                                // Hata durumunda WhatsApp\'a yönlendir
                                window.open("https://wa.me/?text=Maç%20afişimizi%20görün!%20" + encodeURIComponent(imageUrl), "_blank");
                            }
                        }
                        </script>';
                    }
                }
                ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
