<?php

/**

 * CRM Günlük Rapor Sistemi - Durum Özeti

 * Tüm sistemin çalışma durumunu kontrol eder

 */



echo "<!DOCTYPE html>";

echo "<html><head>";

echo "<meta charset='UTF-8'>";

echo "<title>CRM Rapor Sistemi - Durum Özeti</title>";

echo "<style>";

echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";

echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";

echo ".status-ok { color: #27ae60; font-weight: bold; }";

echo ".status-warning { color: #f39c12; font-weight: bold; }";

echo ".status-error { color: #e74c3c; font-weight: bold; }";

echo ".card { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff; }";

echo ".card.success { border-left-color: #27ae60; }";

echo ".card.warning { border-left-color: #f39c12; }";

echo ".card.error { border-left-color: #e74c3c; }";

echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";

echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";

echo "th { background: #f2f2f2; }";

echo ".btn { display: inline-block; padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }";

echo ".btn:hover { background: #0056b3; }";

echo "</style>";

echo "</head><body>";



echo "<div class='container'>";

echo "<h1>🔄 CRM Günlük Rapor Sistemi - Durum Özeti</h1>";

echo "<p><strong>Son Kontrol:</strong> " . date('d.m.Y H:i:s') . "</p>";



// Veritabanı kontrolü

define('BASEPATH', TRUE);

require_once 'application/config/database.php';



$host = $db['default']['hostname'];

$user = $db['default']['username'];

$pass = $db['default']['password'];

$database = $db['default']['database'];



$db_status = "error";

$admin_count = 0;

$genel_mudur_count = 0;

$bolge_mudur_count = 0;



try {

    $mysqli = new mysqli($host, $user, $pass, $database);

    

    if (!$mysqli->connect_error) {

        $db_status = "success";

        

        // Kullanıcı sayıları

        $admin_result = $mysqli->query("SELECT COUNT(*) as count FROM kullanicilar WHERE grup_id = 1 AND kullanici_durum = 1 AND kullanici_eposta IS NOT NULL AND kullanici_eposta != ''");

        $admin_count = $admin_result->fetch_assoc()['count'];

        

        $gm_result = $mysqli->query("SELECT COUNT(*) as count FROM kullanicilar WHERE grup_id = 7 AND kullanici_durum = 1 AND kullanici_eposta IS NOT NULL AND kullanici_eposta != ''");

        $genel_mudur_count = $gm_result->fetch_assoc()['count'];

        

        $bm_result = $mysqli->query("SELECT COUNT(*) as count FROM kullanicilar WHERE grup_id = 6 AND kullanici_durum = 1 AND kullanici_eposta IS NOT NULL AND kullanici_eposta != ''");

        $bolge_mudur_count = $bm_result->fetch_assoc()['count'];

    }

} catch (Exception $e) {

    $db_status = "error";

}



// Sistem Durumu Kartları

echo "<div class='card " . ($db_status == "success" ? "success" : "error") . "'>";

echo "<h3>💾 Veritabanı Bağlantısı</h3>";

if ($db_status == "success") {

    echo "<p class='status-ok'>✅ Bağlantı başarılı</p>";

    echo "<p><strong>Host:</strong> $host</p>";

    echo "<p><strong>Database:</strong> $database</p>";

} else {

    echo "<p class='status-error'>❌ Bağlantı hatası</p>";

}

echo "</div>";



echo "<div class='card " . ($admin_count > 0 ? "success" : "warning") . "'>";

echo "<h3>👨‍💼 Admin Kullanıcıları (Hata Bildirimi)</h3>";

if ($admin_count > 0) {

    echo "<p class='status-ok'>✅ $admin_count admin kullanıcı hazır</p>";

} else {

    echo "<p class='status-warning'>⚠️ Admin kullanıcı yok veya email eksik</p>";

}

echo "</div>";



echo "<div class='card " . ($genel_mudur_count > 0 ? "success" : "warning") . "'>";

echo "<h3>🏢 Genel Müdür (Grup 7)</h3>";

if ($genel_mudur_count > 0) {

    echo "<p class='status-ok'>✅ $genel_mudur_count genel müdür rapor alacak</p>";

} else {

    echo "<p class='status-warning'>⚠️ Genel müdür bulunamadı</p>";

}

echo "</div>";



echo "<div class='card " . ($bolge_mudur_count > 0 ? "success" : "warning") . "'>";

echo "<h3>🏪 Bölge Müdürleri (Grup 6)</h3>";

if ($bolge_mudur_count > 0) {

    echo "<p class='status-ok'>✅ $bolge_mudur_count bölge müdürü rapor alacak</p>";

} else {

    echo "<p class='status-warning'>⚠️ Bölge müdürü bulunamadı</p>";

}

echo "</div>";



// SMTP Test

$smtp_status = "unknown";

$smtp_error = "";



$socket = @stream_socket_client("ssl://smtp.gmail.com:465", $errno, $errstr, 10);

if ($socket) {

    $smtp_status = "success";

    fclose($socket);

} else {

    $smtp_status = "error";

    $smtp_error = "$errno: $errstr";

}



echo "<div class='card " . ($smtp_status == "success" ? "success" : "error") . "'>";

echo "<h3>📧 SMTP Bağlantısı (Gmail)</h3>";

if ($smtp_status == "success") {

    echo "<p class='status-ok'>✅ smtp.gmail.com:465 erişilebilir</p>";

    echo "<p><strong>Gönderen:</strong> noreply@turkbelge.com.tr</p>";

} else {

    echo "<p class='status-error'>❌ SMTP bağlantı hatası</p>";

    echo "<p><strong>Hata:</strong> $smtp_error</p>";

}

echo "</div>";



// Cron Job Bilgisi

echo "<div class='card warning'>";

echo "<h3>⏰ Cron Job Kurulumu</h3>";

echo "<p class='status-warning'>⚠️ Manuel kurulum gerekli</p>";

echo "<p><strong>Komut:</strong></p>";

echo "<code>0 10 * * * /usr/local/bin/php /home/turkbelg/public_html/crm/gunluk_rapor_cron.php</code>";

echo "<p><strong>Açıklama:</strong> Her gün saat 10:00'da çalışır</p>";

echo "</div>";



// Test Linkleri

echo "<h3>🔧 Test ve Yönetim</h3>";

echo "<a href='rapor/test' class='btn' target='_blank'>📊 Manuel Rapor Testi</a>";

echo "<a href='rapor/debug' class='btn' target='_blank'>🔍 Kullanıcı Debug</a>";

echo "<a href='smtp_test.php' class='btn' target='_blank'>📧 SMTP Test</a>";

echo "<a href='admin_test.php' class='btn' target='_blank'>👥 Admin Listesi</a>";

echo "<a href='hata_test.php' class='btn' target='_blank'>🚨 Hata Test</a>";



// Rapor Programı

echo "<h3>📅 Günlük Rapor Programı</h3>";

echo "<table>";

echo "<tr><th>Saat</th><th>İşlem</th><th>Alıcılar</th></tr>";

echo "<tr><td>10:00</td><td>Genel Müdür Raporu</td><td>$genel_mudur_count kişi (Grup 7)</td></tr>";

echo "<tr><td>10:01</td><td>Bölge Müdür Raporları</td><td>$bolge_mudur_count kişi (Grup 6)</td></tr>";

echo "<tr><td>Hata Durumu</td><td>Admin Bildirimi</td><td>$admin_count kişi (Grup 1)</td></tr>";

echo "</table>";



// Sistem Özeti

$total_issues = 0;

if ($admin_count == 0) $total_issues++;

if ($genel_mudur_count == 0) $total_issues++;

if ($bolge_mudur_count == 0) $total_issues++;

if ($smtp_status != "success") $total_issues++;



echo "<h3>📋 Sistem Özeti</h3>";

if ($total_issues == 0) {

    echo "<div class='card success'>";

    echo "<p class='status-ok'>✅ Sistem tamamen hazır - Günlük raporlar otomatik gönderilecek</p>";

    echo "</div>";

} else {

    echo "<div class='card " . ($total_issues > 2 ? "error" : "warning") . "'>";

    echo "<p class='status-warning'>⚠️ $total_issues adet uyarı/hata bulundu</p>";

    echo "<p>Lütfen yukarıdaki sorunları gözden geçirin.</p>";

    echo "</div>";

}



echo "<hr>";

echo "<p><small>Son güncelleme: " . date('d.m.Y H:i:s') . " | CRM Günlük Rapor Sistemi v1.0</small></p>";

echo "</div>";



echo "</body></html>";

?>

