<?php
// Database Migration Runner
// Bu script migration'ı PHP üzerinden çalıştırır

// CodeIgniter database konfigürasyonunu kullan
require_once('application/config/database.php');

// Database bağlantısını oluştur
$mysqli = new mysqli(
    $db['default']['hostname'],
    $db['default']['username'], 
    $db['default']['password'],
    $db['default']['database']
);

// Bağlantı kontrolü
if ($mysqli->connect_error) {
    die("Bağlantı hatası: " . $mysqli->connect_error);
}

// Character set ayarla
$mysqli->set_charset($db['default']['char_set']);

echo "=== CRM Database Migration Runner ===\n";
echo "Database: " . $db['default']['database'] . "\n";
echo "Kullanıcı: " . $db['default']['username'] . "\n\n";

// Migration dosyasını oku
$migrationFile = 'database_migrations/fix_kullanici_sorumluluk_bolgesi.sql';
if (!file_exists($migrationFile)) {
    die("Migration dosyası bulunamadı: $migrationFile\n");
}

$sql = file_get_contents($migrationFile);

// SQL komutlarını ayır ve çalıştır
$commands = explode(';', $sql);
$successCount = 0;
$errorCount = 0;

foreach ($commands as $command) {
    $command = trim($command);
    if (empty($command)) continue;
    
    // Yorumları ve boş satırları atla
    if (strpos($command, '--') === 0) continue;
    
    echo "Çalıştırılıyor: " . substr($command, 0, 100) . "...\n";
    
    if ($mysqli->multi_query($command)) {
        do {
            if ($result = $mysqli->store_result()) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        if (isset($row['result'])) {
                            echo "  -> " . $row['result'] . "\n";
                        }
                    }
                }
                $result->free();
            }
            $successCount++;
        } while ($mysqli->next_result());
    } else {
        echo "  HATA: " . $mysqli->error . "\n";
        $errorCount++;
    }
}

echo "\n=== Migration Tamamlandı ===\n";
echo "Başarılı: $successCount komut\n";
echo "Hatalı: $errorCount komut\n\n";

// Tablo yapısını kontrol et
echo "=== Tablo Yapısı Kontrolü ===\n";
$result = $mysqli->query("DESCRIBE kullanici_sorumluluk_bolgesi");
if ($result) {
    echo "kullanici_sorumluluk_bolgesi tablosu alanları:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    $result->free();
}

// Ulkeler tablosu kontrolü
echo "\n=== Ulkeler Tablosu Kontrolü ===\n";
$result = $mysqli->query("SELECT COUNT(*) as count FROM ulkeler LIMIT 1");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Ulkeler tablosunda " . $row['count'] . " kayıt var\n";
    $result->free();
}

$mysqli->close();
echo "\nMigration tamamlandı!\n";
?>
