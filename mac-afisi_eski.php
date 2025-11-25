<?php
// Bağımsız maç afişi oluşturucu sayfası
// Login gerektirmez, direkt erişilebilir

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Form gönderildi mi kontrol et
$afisolustur = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['afisolustur'])) {
    $afisolustur = true;
}

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
                <div class="form-group">
                    <label for="sablon">� Maç Afişi Şablonu</label>
                    <select name="sablon" id="sablon" required>
                        <option value="">Şablon Seçiniz</option>
                        <?php foreach ($sablonlar as $sablon): ?>
                            <option value="<?= $sablon ?>" <?= (isset($_POST['sablon']) && $_POST['sablon'] === $sablon) ? 'selected' : '' ?>>
                                <?= pathinfo($sablon, PATHINFO_FILENAME) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="info-text">Şablonda takım ve maç bilgileri hazır</p>
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
            
            <?php if ($afisolustur): ?>
                <?php
                // Afiş oluşturma işlemi
                $sablon = $_POST['sablon'] ?? '';
                $isletme_adi = $_POST['isletme_adi'] ?? '';
                $isletme_tel = $_POST['isletme_tel'] ?? '';
                
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
                        // Renk tanımla (altın sarısı)
                        $altin_rengi = imagecolorallocate($img, 218, 165, 32);
                        $beyaz = imagecolorallocate($img, 255, 255, 255);
                        $mor = imagecolorallocate($img, 139, 69, 172);
                        
                        // Font yolu (proje içindeki font)
                        $font_bold = __DIR__ . '/assets/fonts/arial-bold.ttf';
                        $font_normal = __DIR__ . '/assets/fonts/arial.ttf';
                        
                        // Hangisi varsa kullan
                        $font = file_exists($font_bold) ? $font_bold : $font_normal;
                        
                        // Görsel boyutları
                        $genislik = imagesx($img);
                        $yukseklik = imagesy($img);
                        
                        // Sadece işletme bilgilerini yaz (altta - mor arka planda)
                        $isletme_y = $yukseklik - 95;
                        
                        // İşletme adı için font boyutu ayarla (uzun ise küçült)
                        $isletme_adi_upper = mb_strtoupper($isletme_adi, 'UTF-8');
                        $font_size_adi = 68;
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
                        $bbox_tel = imagettfbbox(48, 0, $font, $isletme_tel);
                        $tel_genislik = $bbox_tel[2] - $bbox_tel[0];
                        $tel_x = ($genislik - $tel_genislik) / 2;
                        
                        imagettftext($img, $font_size_adi, 0, $adi_x, $isletme_y, $beyaz, $font, $isletme_adi_upper);
                        imagettftext($img, 48, 0, $tel_x, $isletme_y + 70, $beyaz, $font, $isletme_tel);
                        
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
