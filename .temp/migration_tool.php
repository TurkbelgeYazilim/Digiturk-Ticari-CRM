<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Database Migration Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 4px solid #007bff;
        }
        .table-info {
            margin: 20px 0;
        }
        .table-info table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .table-info th,
        .table-info td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table-info th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .step {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .step h3 {
            margin-top: 0;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️ CRM Database Migration Tool</h1>
        
        <div class="status info">
            <strong>ℹ️ Migration Durumu:</strong> kullanici_sorumluluk_bolgesi tablosuna yeni alanlar eklenecek
        </div>

        <?php
        // Migration durumunu kontrol et
        $migrationNeeded = false;
        $connectionError = false;
        $tableStatus = [];
        
        try {
            // Database bağlantısı
            require_once('application/config/database.php');
            $mysqli = new mysqli(
                $db['default']['hostname'],
                $db['default']['username'], 
                $db['default']['password'],
                $db['default']['database']
            );

            if ($mysqli->connect_error) {
                throw new Exception("Bağlantı hatası: " . $mysqli->connect_error);
            }

            $mysqli->set_charset($db['default']['char_set']);
            
            // Mevcut tablo yapısını kontrol et
            $result = $mysqli->query("DESCRIBE kullanici_sorumluluk_bolgesi");
            if ($result) {
                $columns = [];
                while ($row = $result->fetch_assoc()) {
                    $columns[] = $row['Field'];
                }
                
                $requiredColumns = ['baslangic_tarihi', 'bitis_tarihi', 'ulke_id', 'aciklama'];
                $missingColumns = array_diff($requiredColumns, $columns);
                
                if (!empty($missingColumns)) {
                    $migrationNeeded = true;
                    echo '<div class="status warning">';
                    echo '<strong>⚠️ Migration Gerekli:</strong> Şu alanlar eksik: ' . implode(', ', $missingColumns);
                    echo '</div>';
                } else {
                    echo '<div class="status success">';
                    echo '<strong>✅ Migration Tamamlanmış:</strong> Tüm gerekli alanlar mevcut';
                    echo '</div>';
                }
                
                $tableStatus = [
                    'exists' => true,
                    'columns' => $columns,
                    'missing' => $missingColumns
                ];
            }
            
            $mysqli->close();
            
        } catch (Exception $e) {
            $connectionError = true;
            echo '<div class="status error">';
            echo '<strong>❌ Bağlantı Hatası:</strong> ' . $e->getMessage();
            echo '</div>';
        }
        ?>

        <?php if (!$connectionError): ?>
        
        <div class="step">
            <h3>📊 Mevcut Tablo Durumu</h3>
            <?php if (isset($tableStatus['exists']) && $tableStatus['exists']): ?>
                <div class="table-info">
                    <p><strong>Tablo:</strong> kullanici_sorumluluk_bolgesi</p>
                    <p><strong>Mevcut Alan Sayısı:</strong> <?= count($tableStatus['columns']) ?></p>
                    
                    <?php if ($migrationNeeded): ?>
                        <p><strong>Eksik Alanlar:</strong> 
                            <span style="color: #dc3545;"><?= implode(', ', $tableStatus['missing']) ?></span>
                        </p>
                    <?php else: ?>
                        <p style="color: #28a745;"><strong>✅ Tüm alanlar mevcut</strong></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($migrationNeeded): ?>
        <div class="step">
            <h3>🚀 Migration Çalıştır</h3>
            <p>Aşağıdaki butona tıklayarak migration'ı başlatabilirsiniz:</p>
            
            <form method="post" style="margin: 15px 0;">
                <button type="submit" name="run_migration" class="btn btn-success" 
                        onclick="return confirm('Migration çalıştırılacak. Devam etmek istediğinizden emin misiniz?')">
                    ▶️ Migration'ı Çalıştır
                </button>
            </form>
            
            <div class="status warning">
                <strong>⚠️ Uyarı:</strong> Migration çalıştırmadan önce veritabanı backup'ı almanız önerilir.
            </div>
        </div>
        <?php endif; ?>

        <?php
        // Migration çalıştırma
        if (isset($_POST['run_migration']) && $migrationNeeded) {
            echo '<div class="step">';
            echo '<h3>⚡ Migration Sonuçları</h3>';
            
            try {
                $mysqli = new mysqli(
                    $db['default']['hostname'],
                    $db['default']['username'], 
                    $db['default']['password'],
                    $db['default']['database']
                );

                if ($mysqli->connect_error) {
                    throw new Exception("Bağlantı hatası: " . $mysqli->connect_error);
                }

                $mysqli->set_charset($db['default']['char_set']);
                
                $migrationFile = 'database_migrations/fix_kullanici_sorumluluk_bolgesi.sql';
                if (!file_exists($migrationFile)) {
                    throw new Exception("Migration dosyası bulunamadı: $migrationFile");
                }

                $sql = file_get_contents($migrationFile);
                $commands = explode(';', $sql);
                $results = [];
                
                foreach ($commands as $command) {
                    $command = trim($command);
                    if (empty($command) || strpos($command, '--') === 0) continue;
                    
                    if ($mysqli->multi_query($command)) {
                        do {
                            if ($result = $mysqli->store_result()) {
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        if (isset($row['result'])) {
                                            $results[] = $row['result'];
                                        }
                                    }
                                }
                                $result->free();
                            }
                        } while ($mysqli->next_result());
                    } else {
                        if (!empty($mysqli->error)) {
                            $results[] = "HATA: " . $mysqli->error;
                        }
                    }
                }
                
                echo '<div class="status success">';
                echo '<strong>✅ Migration Tamamlandı!</strong>';
                echo '</div>';
                
                if (!empty($results)) {
                    echo '<pre>';
                    foreach ($results as $result) {
                        echo htmlspecialchars($result) . "\n";
                    }
                    echo '</pre>';
                }
                
                // Yeni tablo yapısını göster
                $result = $mysqli->query("DESCRIBE kullanici_sorumluluk_bolgesi");
                if ($result) {
                    echo '<h4>📋 Güncellenmiş Tablo Yapısı:</h4>';
                    echo '<table>';
                    echo '<tr><th>Alan</th><th>Tür</th><th>Null</th><th>Default</th></tr>';
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['Field']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['Type']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['Null']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['Default'] ?? '') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    $result->free();
                }
                
                $mysqli->close();
                
            } catch (Exception $e) {
                echo '<div class="status error">';
                echo '<strong>❌ Migration Hatası:</strong> ' . $e->getMessage();
                echo '</div>';
            }
            
            echo '</div>';
        }
        ?>

        <div class="step">
            <h3>📖 Manuel Migration</h3>
            <p>Eğer otomatik migration çalışmıyorsa, aşağıdaki SQL komutlarını manuel olarak çalıştırabilirsiniz:</p>
            <pre>-- kullanici_sorumluluk_bolgesi tablosuna yeni alanlar ekle
ALTER TABLE kullanici_sorumluluk_bolgesi 
ADD COLUMN baslangic_tarihi DATE NULL COMMENT 'Sorumluluk başlangıç tarihi';

ALTER TABLE kullanici_sorumluluk_bolgesi 
ADD COLUMN bitis_tarihi DATE NULL COMMENT 'Sorumluluk bitiş tarihi';

ALTER TABLE kullanici_sorumluluk_bolgesi 
ADD COLUMN ulke_id VARCHAR(2) DEFAULT 'TR' COMMENT 'Ülke kodu';

ALTER TABLE kullanici_sorumluluk_bolgesi 
ADD COLUMN aciklama TEXT NULL COMMENT 'Ek açıklama';</pre>
        </div>

        <div class="step">
            <h3>🔍 Doğrulama</h3>
            <p>Migration tamamlandıktan sonra aşağıdaki komutu çalıştırarak kontrol edebilirsiniz:</p>
            <pre>DESCRIBE kullanici_sorumluluk_bolgesi;</pre>
            
            <a href="database_migrations/verify_migration.sql" class="btn" target="_blank">
                📄 Verification Script'i İndir
            </a>
        </div>

        <?php endif; ?>

        <div class="step">
            <h3>📚 Yardım ve Dokümantasyon</h3>
            <p>Detaylı bilgi için dokümantasyon dosyalarını inceleyebilirsiniz:</p>
            <a href="DATABASE_MIGRATION_README.md" class="btn" target="_blank">📖 Migration Dokümantasyonu</a>
            <a href="database_migrations/" class="btn btn-warning" target="_blank">📁 Migration Dosyaları</a>
        </div>
    </div>
</body>
</html>
