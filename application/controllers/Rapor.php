<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Rapor extends CI_Controller {

    

    public function __construct()

    {

        parent::__construct();

        error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

        $this->load->model('Vt', 'vt');

        $this->load->database();

        $this->load->library('email');

        $this->load->library('session');

        $this->load->helper('date');

        $this->load->helper('url');



        // Session kontrolü - gunluk_rapor için bypass

        $requested_method = $this->uri->segment(2);

        if ($requested_method !== 'gunluk_rapor') {

            $control = session("r", "login");

            if (!$control) {

                redirect("check");

            }

        }

    }    /**

     * Test endpoint to verify which file is being executed

     */

    public function test_dosya_kontrol()

    {

        $dosya_yolu = __FILE__;

        $son_degisiklik = filemtime(__FILE__);

        $tarih = date('Y-m-d H:i:s', $son_degisiklik);

        

        echo "Çalışan dosya: " . $dosya_yolu . "\n";

        echo "Son değişiklik: " . $tarih . "\n";

        echo "Test zamanı: " . date('Y-m-d H:i:s') . "\n";

        echo "UNIK TEST KODU: RAPOR-2025-06-25-17:05\n";

        

        log_message('info', 'Test dosya kontrol çağrıldı - Dosya: ' . $dosya_yolu . ' - Son değişiklik: ' . $tarih);

    }



        /**

     * Günlük raporları gönder - Cron job tarafından çağrılır

     */

    public function gunluk_rapor()

    {

        try {

            // OpCache temizleme deneyelim

            if (function_exists('opcache_reset')) {

                opcache_reset();

                log_message('info', 'OpCache temizlendi');

            }

            

            $this->load->helper('url');

              // Önceki günün tarih bilgileri - Her sabah saat 9'da önceki günün raporu gönderilecek

            $onceki_gun = date('Y-m-d', strtotime('-1 day'));

            $onceki_gun_formatted = date('d.m.Y', strtotime('-1 day'));

            

            log_message('info', 'Günlük rapor işlemi başlatıldı - Önceki Gün Tarihi: ' . $onceki_gun_formatted);

            log_message('info', 'UNIK TEST MESAJI - KOD VERSIYON 2025-07-26-09:00 - ÖNCEKİ GÜN VERİLERİ');

              // Test modu kontrolü - Önce Admin grubuna gönder

            $test_modu = false; // CANLI: Gerçek müdürlere gönderim AÇIK

              if ($test_modu) {

                $this->admin_test_raporu($onceki_gun, $onceki_gun_formatted);

            } else {

                // Genel Müdür raporunu hem Genel Müdür hem Bölge Müdür gruplarına gönder

                $this->genel_mudur_raporu($onceki_gun, $onceki_gun_formatted);

                

                // Bölge Müdürleri raporları geçici askıya alındı

                // $this->bolge_mudur_raporlari($onceki_gun, $onceki_gun_formatted);

            }

            

            log_message('info', 'Günlük rapor işlemi tamamlandı');

            

            if ($this->input->is_cli_request()) {

                echo "Rapor gönderim işlemi tamamlandı - " . date('Y-m-d H:i:s') . "\n";

            } else {

                echo json_encode(['status' => 'success', 'message' => 'Raporlar başarıyla gönderildi']);

            }

            

        } catch (Exception $e) {

            log_message('error', 'Günlük rapor hatası: ' . $e->getMessage());

            $this->hata_bildirimi($e->getMessage());

            

            if ($this->input->is_cli_request()) {

                echo "HATA: " . $e->getMessage() . "\n";

            } else {

                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);

            }

        }

    }



    /**

     * Test modu - Admin grubuna rapor gönder

     */

    private function admin_test_raporu($tarih, $tarih_formatted)

    {        log_message('info', 'Test modu - Admin grubuna rapor gönderiliyor');        // Admin grubu kullanıcılarını al (grup_id = 1)

        $adminler = $this->vt->multiple('kullanicilar', ['grup_id' => 1, 'kullanici_durum' => 1]);

        

        if (empty($adminler)) {

            throw new Exception('Admin kullanıcı bulunamadı');

        }

        

        // Genel müdür raporunu hazırla (test için)

        $rapor_html = $this->genel_mudur_rapor_hazirla($tarih, $tarih_formatted);        foreach ($adminler as $admin) {

            if (!empty($admin->kullanici_eposta)) {

                $admin_adi = trim($admin->kullanici_ad . ' ' . $admin->kullanici_soyad);

                $konu = "[TEST] Günlük CRM Raporu - " . $tarih_formatted;

                $this->rapor_gonder($admin->kullanici_eposta, $admin_adi, $konu, $rapor_html);

                log_message('info', 'Test raporu gönderildi: ' . $admin->kullanici_eposta);

            }

        }

    }    /**

     * Genel Müdür raporu gönder - Hem Genel Müdür hem Bölge Müdür gruplarına

     */

    private function genel_mudur_raporu($tarih, $tarih_formatted)

    {

        log_message('info', 'Genel Müdür raporu hazırlanıyor (Genel+Bölge Müdür gruplarına)');

        

        // Hem Genel Müdür (grup_id = 7) hem de Bölge Müdür (grup_id = 6) gruplarını al

        $genel_mudurler = $this->vt->multiple('kullanicilar', ['grup_id' => 7, 'kullanici_durum' => 1]);

        $bolge_mudurler = $this->vt->multiple('kullanicilar', ['grup_id' => 6, 'kullanici_durum' => 1]);

        

        // Tüm alıcıları birleştir

        $tum_alicilar = array_merge($genel_mudurler, $bolge_mudurler);

        

        if (empty($tum_alicilar)) {

            log_message('warning', 'Genel Müdür ve Bölge Müdür bulunamadı');

            return;

        }

        

        // Raporu hazırla

        $rapor_html = $this->genel_mudur_rapor_hazirla($tarih, $tarih_formatted);

        

        // Her müdüre gönder

        foreach ($tum_alicilar as $mudur) {

            if (!empty($mudur->kullanici_eposta)) {

                $mudur_adi = trim($mudur->kullanici_ad . ' ' . $mudur->kullanici_soyad);

                $konu = "Günlük CRM Raporu - " . $tarih_formatted;

                $this->rapor_gonder($mudur->kullanici_eposta, $mudur_adi, $konu, $rapor_html);

                log_message('info', 'Günlük rapor gönderildi: ' . $mudur->kullanici_eposta);

            }

        }

    }    /**

     * Bölge Müdürleri raporlarını gönder

     * ASKIYA ALINDI - İleride kullanmak için yorum satırında tutuldu

     */

    /*

    private function bolge_mudur_raporlari($tarih, $tarih_formatted)

    {

        log_message('info', 'Bölge Müdür raporları hazırlanıyor');

        

        // Bölge Müdürlerini al (grup_id = 6)

        $bolge_mudurler = $this->vt->multiple('kullanicilar', ['grup_id' => 6, 'kullanici_durum' => 1]);

        

        if (empty($bolge_mudurler)) {

            log_message('warning', 'Bölge Müdürü bulunamadı');

            return;

        }

        

        foreach ($bolge_mudurler as $mudur) {

            if (!empty($mudur->kullanici_eposta)) {

                // Bu bölge müdürü ve astlarının raporunu hazırla

                $rapor_html = $this->bolge_mudur_rapor_hazirla($mudur->kullanici_id, $tarih, $tarih_formatted);

                

                $mudur_adi = trim($mudur->kullanici_ad . ' ' . $mudur->kullanici_soyad);

                $konu = "Günlük Bölge Raporu - " . $tarih_formatted;

                $this->rapor_gonder($mudur->kullanici_eposta, $mudur_adi, $konu, $rapor_html);

                log_message('info', 'Bölge Müdür raporu gönderildi: ' . $mudur->kullanici_eposta);

            }

        }

    }

    *//**

     * Genel Müdür raporu HTML içeriğini hazırla

     */

    private function genel_mudur_rapor_hazirla($tarih, $tarih_formatted)

    {

        // Genel_Mudur_Rapor.txt'den alınan kapsamlı SQL sorgusu - 12 sütunlu tam rapor

        $sql = "SELECT 

                    CONCAT(u.kullanici_ad, ' ', u.kullanici_soyad) as personel_adi,

                    COALESCE(m.yeni_musteri_sayisi, 0) as toplam_musteri,

                    CONCAT(COALESCE(sf.sozlesme_adedi, 0), ' (', FORMAT(COALESCE(sf.sozlesme_ciro, 0), 2), ' TL)') as satis_sozlesmesi,

                    CONCAT(COALESCE(bh.tahsilat_adedi, 0), ' (', FORMAT(COALESCE(bh.tahsilat_toplam, 0), 2), ' TL)') as tahsilat_banka_pos,

                    CONCAT(COALESCE(kh.nakit_adedi, 0), ' (', FORMAT(COALESCE(kh.nakit_toplam, 0), 2), ' TL)') as tahsilat_nakit,

                    CONCAT(COALESCE(ck.cek_adedi, 0), ' (', FORMAT(COALESCE(ck.cek_toplam, 0), 2), ' TL)') as tahsilat_cek,

                    COALESCE(p.yeni_potansiyel_sayisi, 0) as catkapi_musteri_sayisi,

                    COALESCE(ps.iptal_isletme_yok, 0) as iptal_isletme_yok,

                    COALESCE(ps.iptal_unvan_degismis, 0) as iptal_unvan_degismis,

                    COALESCE(ps.iptal_istemiyor, 0) as iptal_istemiyor,

                    COALESCE(ps.teklif_verildi, 0) as teklif_verildi,

                    CONCAT(COALESCE(ps.satildi, 0), ' (', FORMAT(COALESCE(ps.satildi_tutar, 0), 2), ' TL)') as satildi_teklif

                FROM kullanicilar u

                LEFT JOIN (

                    SELECT 

                        cari_olusturan,

                        COUNT(*) as yeni_musteri_sayisi

                    FROM cari 

                    WHERE cari_olusturmaTarihi = ?

                        AND cari_durum = 1

                    GROUP BY cari_olusturan

                ) m ON m.cari_olusturan = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        satis_olusturan,

                        COUNT(*) as sozlesme_adedi,

                        SUM(satis_vergiDahilToplam) as sozlesme_ciro

                    FROM satisFaturasi 

                    WHERE satis_faturaTarihi = ?

                    GROUP BY satis_olusturan

                ) sf ON sf.satis_olusturan = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        bh_olusturan,

                        COUNT(*) as tahsilat_adedi,

                        SUM(bh_giris) as tahsilat_toplam

                    FROM bankaHareketleri 

                    WHERE bh_olusturmaTarihi = ?

                        AND bh_giris > 0

                    GROUP BY bh_olusturan

                ) bh ON bh.bh_olusturan = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        kh_olusturan,

                        COUNT(*) as nakit_adedi,

                        SUM(kh_giris) as nakit_toplam

                    FROM kasaHareketleri 

                    WHERE kh_olusturmaTarihi = ?

                        AND kh_giris > 0

                    GROUP BY kh_olusturan

                ) kh ON kh.kh_olusturan = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        cek_kullaniciID,

                        COUNT(*) as cek_adedi,

                        SUM(cek_tutar) as cek_toplam

                    FROM cek 

                    WHERE cek_sistemKayitTarihi = ?

                        AND cek_tutar > 0

                    GROUP BY cek_kullaniciID

                ) ck ON ck.cek_kullaniciID = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        olusturan_kullanici,

                        COUNT(*) as yeni_potansiyel_sayisi

                    FROM potansiyel_cari 

                    WHERE DATE(olusturma_zamani) = ?

                    GROUP BY olusturan_kullanici

                ) p ON p.olusturan_kullanici = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        islemi_yapan,

                        SUM(CASE WHEN durum_id = 1 THEN 1 ELSE 0 END) as iptal_isletme_yok,

                        SUM(CASE WHEN durum_id = 2 THEN 1 ELSE 0 END) as iptal_unvan_degismis,

                        SUM(CASE WHEN durum_id = 3 THEN 1 ELSE 0 END) as iptal_istemiyor,

                        SUM(CASE WHEN durum_id = 4 THEN 1 ELSE 0 END) as teklif_verildi,

                        SUM(CASE WHEN durum_id = 5 THEN 1 ELSE 0 END) as satildi,

                        SUM(CASE WHEN durum_id = 5 THEN fiyat1 ELSE 0 END) as satildi_tutar

                    FROM potansiyel_satis 

                    WHERE DATE(islem_tarihi) = ?

                    GROUP BY islemi_yapan

                ) ps ON ps.islemi_yapan = u.kullanici_id

                WHERE u.kullanici_durum = 1

                ORDER BY COALESCE(m.yeni_musteri_sayisi, 0) DESC";

        

        $query = $this->db->query($sql, [$tarih, $tarih, $tarih, $tarih, $tarih, $tarih, $tarih]);

        $sonuclar = $query->result();

        

        // Debug: SQL ve sonuçları logla

        log_message('info', 'Günlük rapor SQL çalıştırıldı - Sonuç sayısı: ' . count($sonuclar));

        log_message('info', 'SQL sorgusu: ' . $this->db->last_query());

        

        return $this->rapor_html_olustur($sonuclar, '', $tarih_formatted);

    }



    /**

    /**

     * Bölge Müdür raporu HTML içeriğini hazırla

     */

    

    /**

     * Bölge Müdür raporu HTML içeriğini hazırla

     * ASKIYA ALINDI - İleride kullanmak için yorum satırında tutuldu

     */

    /*

    private function bolge_mudur_rapor_hazirla($mudur_id, $tarih, $tarih_formatted)

    {

        // Bu müdür ve astlarının ID'lerini al

        $kullanici_ids = [$mudur_id];

          // Astlarını bul (kullanici_sorumluMudur = $mudur_id)

        $astlar = $this->vt->multiple('kullanicilar', ['kullanici_sorumluMudur' => $mudur_id, 'kullanici_durum' => 1]);

        foreach ($astlar as $ast) {

            $kullanici_ids[] = $ast->kullanici_id;

        }

          $kullanici_ids_str = implode(',', $kullanici_ids);

          // DÜZELTILMIŞ SQL sorgusu - Genel Müdür raporu ile aynı format (12 sütun)

        $sql = "SELECT 

                    CONCAT(u.kullanici_ad, ' ', u.kullanici_soyad) as personel_adi,

                    COALESCE(m.yeni_musteri_sayisi, 0) as toplam_musteri,

                    CONCAT(COALESCE(sf.sozlesme_adedi, 0), ' (', FORMAT(COALESCE(sf.sozlesme_ciro, 0), 2), ' TL)') as satis_sozlesmesi,

                    CONCAT(COALESCE(bh.tahsilat_adedi, 0), ' (', FORMAT(COALESCE(bh.tahsilat_toplam, 0), 2), ' TL)') as tahsilat_banka_pos,

                    CONCAT(COALESCE(kh.nakit_adedi, 0), ' (', FORMAT(COALESCE(kh.nakit_toplam, 0), 2), ' TL)') as tahsilat_nakit,

                    CONCAT(COALESCE(ck.cek_adedi, 0), ' (', FORMAT(COALESCE(ck.cek_toplam, 0), 2), ' TL)') as tahsilat_cek,

                    COALESCE(p.yeni_potansiyel_sayisi, 0) as catkapi_musteri_sayisi,

                    COALESCE(ps.iptal_isletme_yok, 0) as iptal_isletme_yok,

                    COALESCE(ps.iptal_unvan_degismis, 0) as iptal_unvan_degismis,

                    COALESCE(ps.iptal_istemiyor, 0) as iptal_istemiyor,

                    COALESCE(ps.teklif_verildi, 0) as teklif_verildi,

                    CONCAT(COALESCE(ps.satildi, 0), ' (', FORMAT(COALESCE(ps.satildi_tutar, 0), 2), ' TL)') as satildi_teklif

                FROM kullanicilar u

                LEFT JOIN (

                    SELECT 

                        cari_olusturan,

                        COUNT(*) as yeni_musteri_sayisi

                    FROM cari 

                    WHERE cari_olusturmaTarihi = ? AND cari_olusturan IN ($kullanici_ids_str)

                        AND cari_durum = 1

                    GROUP BY cari_olusturan

                ) m ON m.cari_olusturan = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        olusturan_kullanici,

                        COUNT(*) as yeni_potansiyel_sayisi

                    FROM potansiyel_cari 

                    WHERE DATE(olusturma_zamani) = ? AND olusturan_kullanici IN ($kullanici_ids_str)

                    GROUP BY olusturan_kullanici

                ) p ON p.olusturan_kullanici = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        satis_olusturan,

                        COUNT(*) as sozlesme_adedi,

                        SUM(satis_vergiDahilToplam) as sozlesme_ciro

                    FROM satisFaturasi 

                    WHERE satis_faturaTarihi = ? AND satis_olusturan IN ($kullanici_ids_str)

                    GROUP BY satis_olusturan

                ) sf ON sf.satis_olusturan = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        bh_olusturan,

                        COUNT(*) as tahsilat_adedi,

                        SUM(bh_giris) as tahsilat_toplam

                    FROM bankaHareketleri 

                    WHERE bh_olusturmaTarihi = ? AND bh_olusturan IN ($kullanici_ids_str)

                        AND bh_giris > 0

                    GROUP BY bh_olusturan

                ) bh ON bh.bh_olusturan = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        kh_olusturan,

                        COUNT(*) as nakit_adedi,

                        SUM(kh_giris) as nakit_toplam

                    FROM kasaHareketleri 

                    WHERE kh_olusturmaTarihi = ? AND kh_olusturan IN ($kullanici_ids_str)

                        AND kh_giris > 0

                    GROUP BY kh_olusturan

                ) kh ON kh.kh_olusturan = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        cek_kullaniciID,

                        COUNT(*) as cek_adedi,

                        SUM(cek_tutar) as cek_toplam

                    FROM cek 

                    WHERE cek_sistemKayitTarihi = ? AND cek_kullaniciID IN ($kullanici_ids_str)

                        AND cek_tutar > 0

                    GROUP BY cek_kullaniciID

                ) ck ON ck.cek_kullaniciID = u.kullanici_id

                LEFT JOIN (

                    SELECT 

                        islemi_yapan,

                        SUM(CASE WHEN durum_id = 1 THEN 1 ELSE 0 END) as iptal_isletme_yok,

                        SUM(CASE WHEN durum_id = 2 THEN 1 ELSE 0 END) as iptal_unvan_degismis,

                        SUM(CASE WHEN durum_id = 3 THEN 1 ELSE 0 END) as iptal_istemiyor,

                        SUM(CASE WHEN durum_id = 4 THEN 1 ELSE 0 END) as teklif_verildi,

                        SUM(CASE WHEN durum_id = 5 THEN 1 ELSE 0 END) as satildi,

                        SUM(CASE WHEN durum_id = 5 THEN fiyat1 ELSE 0 END) as satildi_tutar

                    FROM potansiyel_satis 

                    WHERE DATE(islem_tarihi) = ? AND islemi_yapan IN ($kullanici_ids_str)

                    GROUP BY islemi_yapan

                ) ps ON ps.islemi_yapan = u.kullanici_id

                WHERE u.kullanici_durum = 1 AND u.kullanici_id IN ($kullanici_ids_str)

                ORDER BY COALESCE(sf.sozlesme_ciro, 0) DESC";        

        $query = $this->db->query($sql, [$tarih, $tarih, $tarih, $tarih, $tarih, $tarih, $tarih]);

        $sonuclar = $query->result();

        

        return $this->rapor_html_olustur($sonuclar, 'Bölge Müdür Raporu', $tarih_formatted);

    }

    */



    /**

     * Rapor HTML tablosunu oluştur

    /**

     * HTML rapor tablosu oluştur

     */

    private function rapor_html_olustur($sonuclar, $rapor_tipi, $tarih_formatted)

    {

        // Debug: HTML oluşturma başlangıcı

        log_message('info', 'HTML rapor oluşturuluyor - Sonuç sayısı: ' . count($sonuclar));

        if (!empty($sonuclar)) {

            $first_result = $sonuclar[0];

            log_message('info', 'İlk sonuç alanları: ' . implode(', ', array_keys((array)$first_result)));

        }

        

        $html = "

        <!DOCTYPE html>

        <html>

        <head>

            <meta charset='UTF-8'>

            <style>

                body { font-family: Arial, sans-serif; margin: 20px; }

                .header { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }

                .header h2 { color: #343a40; margin: 0; }

                .header p { color: #6c757d; margin: 5px 0 0 0; }

                table { border-collapse: collapse; width: 100%; margin-top: 10px; }

                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }

                th { background-color: #f2f2f2; font-weight: bold; }

                tr:nth-child(even) { background-color: #f9f9f9; }

                .number { text-align: right; }

                .positive-value { background-color: #6d3eca; color: white; font-weight: bold; padding: 4px 8px; border-radius: 4px; }

                .footer { margin-top: 20px; padding: 15px; background-color: #e9ecef; border-radius: 5px; font-size: 12px; color: #6c757d; }

            </style>

        </head>

        <body>";

        

        // Sadece rapor tipi boş değilse başlık göster

        if (!empty($rapor_tipi)) {

            $html .= "

            <div class='header'>

                <h2>$rapor_tipi</h2>

                <p>Tarih: $tarih_formatted</p>

                <p>Oluşturulma: " . date('d.m.Y H:i:s') . "</p>

            </div>";

        } else {

            $html .= "

            <div class='header'>

                <p>Tarih: $tarih_formatted</p>

                <p>Oluşturulma: " . date('d.m.Y H:i:s') . "</p>

            </div>";

        }

        

        $html .= "

            <table>

                <thead>

                    <tr>

                        <th>Personel Adı</th>

                        <th>Toplam Müşteri</th>

                        <th>Satış Sözleşmesi</th>

                        <th>Tahsilat-Banka/Pos</th>

                        <th>Tahsilat-Nakit</th>

                        <th>Tahsilat-Çek</th>

                        <th>ÇatKapı Müşteri</th>

                        <th>İptal-İşletme Yok</th>

                        <th>İptal-Ünvan Değişmiş</th>

                        <th>İptal-İstemiyor</th>

                        <th>Teklif Verildi</th>

                        <th>Satıldı (Teklif)</th>

                    </tr>

                </thead>

                <tbody>";

        

        if (empty($sonuclar)) {

            $html .= "<tr><td colspan='12' style='text-align: center; color: #6c757d;'>Bu tarih için veri bulunamadı.</td></tr>";

        } else {

            foreach ($sonuclar as $satir) {

                $html .= "<tr>";

                $html .= "<td>" . htmlspecialchars($satir->personel_adi) . "</td>";

                

                // Sayısal değerleri kontrol et ve 0'dan büyükse vurgula

                $toplam_musteri = intval($satir->toplam_musteri);

                $catkapi_musteri = intval($satir->catkapi_musteri_sayisi);

                $iptal_isletme = intval($satir->iptal_isletme_yok);

                $iptal_unvan = intval($satir->iptal_unvan_degismis);

                $iptal_istemiyor = intval($satir->iptal_istemiyor);

                $teklif_verildi = intval($satir->teklif_verildi);

                

                // Sözleşme sayısını al (parantez içindeki sayıdan)

                $satis_sozlesmesi_parts = explode(' (', $satir->satis_sozlesmesi);

                $sozlesme_sayisi = intval($satis_sozlesmesi_parts[0]);

                

                // Tahsilat sayılarını al

                $tahsilat_banka_parts = explode(' (', $satir->tahsilat_banka_pos);

                $tahsilat_banka_sayisi = intval($tahsilat_banka_parts[0]);

                

                $tahsilat_nakit_parts = explode(' (', $satir->tahsilat_nakit);

                $tahsilat_nakit_sayisi = intval($tahsilat_nakit_parts[0]);

                

                $tahsilat_cek_parts = explode(' (', $satir->tahsilat_cek);

                $tahsilat_cek_sayisi = intval($tahsilat_cek_parts[0]);

                

                // Satıldı teklif sayısını al

                $satildi_teklif_parts = explode(' (', $satir->satildi_teklif);

                $satildi_sayisi = intval($satildi_teklif_parts[0]);

                

                $html .= "<td class='number" . ($toplam_musteri > 0 ? " positive-value" : "") . "'>" . number_format($toplam_musteri) . "</td>";

                $html .= "<td class='number" . ($sozlesme_sayisi > 0 ? " positive-value" : "") . "'>" . htmlspecialchars($satir->satis_sozlesmesi) . "</td>";

                $html .= "<td class='number" . ($tahsilat_banka_sayisi > 0 ? " positive-value" : "") . "'>" . htmlspecialchars($satir->tahsilat_banka_pos) . "</td>";

                $html .= "<td class='number" . ($tahsilat_nakit_sayisi > 0 ? " positive-value" : "") . "'>" . htmlspecialchars($satir->tahsilat_nakit) . "</td>";

                $html .= "<td class='number" . ($tahsilat_cek_sayisi > 0 ? " positive-value" : "") . "'>" . htmlspecialchars($satir->tahsilat_cek) . "</td>";

                $html .= "<td class='number" . ($catkapi_musteri > 0 ? " positive-value" : "") . "'>" . number_format($catkapi_musteri) . "</td>";

                $html .= "<td class='number" . ($iptal_isletme > 0 ? " positive-value" : "") . "'>" . number_format($iptal_isletme) . "</td>";

                $html .= "<td class='number" . ($iptal_unvan > 0 ? " positive-value" : "") . "'>" . number_format($iptal_unvan) . "</td>";

                $html .= "<td class='number" . ($iptal_istemiyor > 0 ? " positive-value" : "") . "'>" . number_format($iptal_istemiyor) . "</td>";

                $html .= "<td class='number" . ($teklif_verildi > 0 ? " positive-value" : "") . "'>" . number_format($teklif_verildi) . "</td>";

                $html .= "<td class='number" . ($satildi_sayisi > 0 ? " positive-value" : "") . "'>" . htmlspecialchars($satir->satildi_teklif) . "</td>";

                $html .= "</tr>";

            }

        }

        

        $html .= "

                </tbody>

            </table>

            

            <div class='footer'>

                Bu rapor otomatik olarak oluşturulmuştur - İleka Soft CRM Sistemi<br>

                Bu e-postayı yanıtlamayın. Sorularınız için destek ekibimizle iletişime geçin.

            </div>

        </body>

        </html>";

        

        // Debug: HTML içeriğini logla

        log_message('info', 'HTML rapor içeriği (ilk 500 karakter): ' . substr($html, 0, 500));

        

        return $html;    }



    /**

     * E-posta gönder

     */

    private function rapor_gonder($email, $isim, $konu, $html_icerik)

    {

        try {

            // Email library'yi yükle

            $this->load->library('email');

            

            // Debug için web'de göster

            if (!$this->input->is_cli_request()) {

                echo "<div style='background: #e3f2fd; padding: 10px; border: 1px solid #2196f3; margin: 10px 0;'>";

                echo "<h4>📧 Email Gönderim Detayları:</h4>";

                echo "<p><strong>Alıcı:</strong> {$email}</p>";

                echo "<p><strong>İsim:</strong> {$isim}</p>";

                echo "<p><strong>Konu:</strong> {$konu}</p>";

                echo "<p><strong>Zaman:</strong> " . date('Y-m-d H:i:s') . "</p>";

                echo "<p><strong>SMTP Server:</strong> smtp.gmail.com:465 (SSL)</p>";

                echo "<p><strong>Sender:</strong> noreply@turkbelge.com.tr</p>";

                echo "</div>";

            }

            

            // E-posta ayarları - Gmail SMTP (CodeIgniter için optimize)

            $config = [

                'protocol' => 'smtp',

                'smtp_host' => 'smtp.gmail.com',

                'smtp_port' => 465,

                'smtp_user' => 'noreply@turkbelge.com.tr',

                'smtp_pass' => 'tbfm ybso mzjp eave',

                'smtp_crypto' => 'ssl',

                'smtp_timeout' => 30,

                'mailtype' => 'html',

                'charset' => 'utf-8',

                'newline' => "\r\n",

                'crlf' => "\r\n",

                'wordwrap' => true,

                'validate' => false,

                'priority' => 3,

                'bcc_batch_mode' => false,

                'bcc_batch_size' => 200

            ];

            

            $this->email->initialize($config);

            $this->email->clear();

            

            $this->email->from('bilgi@ileka.com.tr, 'IlekaSoft CRM Sistemi');

            $this->email->to($email);

            $this->email->subject($konu);

            $this->email->message($html_icerik);

            

            // Debug: Email içeriğini logla

            log_message('info', 'Email gönderimi - Alıcı: ' . $email . ', İçerik uzunluğu: ' . strlen($html_icerik) . ' karakter');

            log_message('info', 'Email HTML içeriği (ilk 1000 karakter): ' . substr($html_icerik, 0, 1000));

            

            // Email göndermeyi dene

            $gonderim_sonucu = $this->email->send();

            

            if ($gonderim_sonucu) {

                log_message('info', "E-posta başarıyla gönderildi: $email");

                

                // Debug için web'de göster

                if (!$this->input->is_cli_request()) {

                    echo "<div style='background: #e8f5e8; padding: 10px; border: 1px solid #4caf50; margin: 10px 0;'>";

                    echo "<h4>✅ Email Başarıyla Gönderildi!</h4>";

                    echo "<p>Alıcı: {$email}</p>";

                    echo "<p>Gönderim Saati: " . date('Y-m-d H:i:s') . "</p>";

                    echo "<p>SMTP Response: Email sent successfully</p>";

                    echo "</div>";

                    

                    // Email debug çıktısını göster

                    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;'>";

                    echo "<h5>📧 SMTP Debug Çıktısı:</h5>";

                    echo "<pre>" . htmlspecialchars($this->email->print_debugger()) . "</pre>";

                    echo "</div>";

                }

                return true;

            } else {

                $error = $this->email->print_debugger();

                log_message('error', "E-posta gönderim hatası ($email): $error");

                

                // Debug için web'de de göster

                if (!$this->input->is_cli_request()) {

                    echo "<div style='background: #ffebee; padding: 10px; border: 1px solid #f44336; margin: 10px 0;'>";

                    echo "<h4>❌ E-posta Gönderim Hatası:</h4>";

                    echo "<p><strong>Alıcı:</strong> {$email}</p>";

                    echo "<p><strong>SMTP Response:</strong> Email send failed</p>";

                    echo "</div>";

                    

                    // Email hata çıktısını göster

                    echo "<div style='background: #ffebee; padding: 10px; margin: 10px 0; border: 1px solid #f44336;'>";

                    echo "<h5>🚫 SMTP Hata Detayları:</h5>";

                    echo "<pre>" . htmlspecialchars($error) . "</pre>";

                    echo "</div>";

                }

                return false;

            }

              } catch (Exception $e) {

            log_message('error', "E-posta gönderim exception ($email): " . $e->getMessage());

            

            // Debug için web'de göster

            if (!$this->input->is_cli_request()) {

                echo "<div style='background: #ffebee; padding: 10px; border: 1px solid #f44336; margin: 10px 0;'>";

                echo "<h4>❌ E-posta Exception Hatası:</h4>";

                echo "<p><strong>Alıcı:</strong> {$email}</p>";

                echo "<p><strong>Hata:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";

                echo "<p><strong>Dosya:</strong> " . $e->getFile() . "</p>";

                echo "<p><strong>Satır:</strong> " . $e->getLine() . "</p>";

                echo "</div>";

            }

            return false;

        }

    }



    /**

     * Hata durumunda admin grubuna bildirim gönder

     */

    private function hata_bildirimi($hata_mesaji)

    {

        try {

            $adminler = $this->vt->multiple('kullanicilar', ['grup_id' => 1, 'kullanici_durum' => 1]);

            

            $html = "

            <h3>CRM Günlük Rapor Sistemi Hatası</h3>

            <p><strong>Tarih:</strong> " . date('d.m.Y H:i:s') . "</p>

            <p><strong>Hata:</strong> " . htmlspecialchars($hata_mesaji) . "</p>

            <p>Lütfen sistemi kontrol edin ve gerekli müdahaleyi yapın.</p>

            ";            foreach ($adminler as $admin) {

                if (!empty($admin->kullanici_eposta)) {

                    $admin_adi = trim($admin->kullanici_ad . ' ' . $admin->kullanici_soyad);

                    $this->rapor_gonder(

                        $admin->kullanici_eposta, 

                        $admin_adi,

                        'CRM Rapor Sistemi Hatası - ' . date('d.m.Y'),

                        $html

                    );

                }

            }

            

        } catch (Exception $e) {

            log_message('error', 'Hata bildirimi gönderilemedi: ' . $e->getMessage());

        }

    }    /**

     * Manuel test için - Sadece Admin kullanıcıları erişebilir

     */

    public function test()

    {

        // Header.php'deki gibi session kontrolü

        $control2 = session("r", "login_info");

        if (!$control2) {

            echo "<h3>Erişim Reddedildi</h3>";

            echo "<p style='color: red;'>Session bulunamadı - Login olun</p>";

            echo "<p><a href='" . base_url() . "giris'>Login Sayfasına Git</a></p>";

            return;

        }

          // Admin yetki kontrolü

        $user_id = $control2->kullanici_id;

        $user_info = $this->db->query("SELECT grup_id FROM kullanicilar WHERE kullanici_id = ?", [$user_id])->row();

        

        if (!$user_info || $user_info->grup_id != 1) {

            echo "<h3>Erişim Reddedildi</h3>";

            echo "<p style='color: red;'>Bu sayfaya sadece Admin kullanıcıları erişebilir.</p>";

            echo "<p>Kullanıcı ID: $user_id</p>";

            echo "<p>Kullanıcı Grup ID: " . ($user_info ? $user_info->grup_id : 'Bulunamadı') . "</p>";

            echo "<p><a href='" . base_url() . "rapor/debug'>Debug Sayfasına Git</a></p>";

            return;

        }

        

        echo "<h3>Günlük Rapor Test</h3>";

        echo "<p style='color: green;'>✓ Admin kullanıcı onaylandı. Test başlatılıyor...</p>";

        echo "<p>Kullanıcı ID: $user_id</p>";

        echo "<p>Kullanıcı Grup ID: " . $user_info->grup_id . "</p>";

        

        // Email ayarlarını göster

        echo "<hr><h4>📧 Email Konfigürasyonu:</h4>";

        echo "<p><strong>SMTP Host:</strong> smtp.gmail.com</p>";

        echo "<p><strong>SMTP Port:</strong> 465 (SSL)</p>";

        echo "<p><strong>Gönderen:</strong> bilgi@ileka.com.tr</p>";

        echo "<p><strong>Gönderen Adı:</strong> IlekaSoft CRM Sistemi</p>";

        

        // Admin kullanıcılarını göster

        $adminler = $this->vt->multiple('kullanicilar', ['grup_id' => 1, 'kullanici_durum' => 1]);

        echo "<hr><h4>👥 Admin Kullanıcıları (Alıcılar):</h4>";

        if (empty($adminler)) {

            echo "<p style='color: red;'>❌ Admin kullanıcı bulunamadı!</p>";

        } else {            echo "<ul>";

            foreach ($adminler as $admin) {

                $admin_adi = trim($admin->kullanici_ad . ' ' . $admin->kullanici_soyad);

                $email_status = !empty($admin->kullanici_eposta) ? "✅" : "❌ Email boş";

                echo "<li><strong>{$admin_adi}</strong> - {$admin->kullanici_eposta} {$email_status}</li>";

            }

            echo "</ul>";

        }

        

        echo "<hr>";

        

        try {

            $this->gunluk_rapor();

            echo "<p style='color: green;'>✓ Test tamamlandı</p>";

        } catch (Exception $e) {

            echo "<p style='color: red;'>✗ Test hatası: " . $e->getMessage() . "</p>";

            echo "<p>Stack trace:</p>";

            echo "<pre>" . $e->getTraceAsString() . "</pre>";

        }

    }

      /**

     * Kullanıcı bilgilerini kontrol et

     */

    public function debug()

    {

        echo "<h3>Kullanıcı Debug Bilgileri</h3>";

        

        // Header.php'deki gibi session kontrolü

        $control2 = session("r", "login_info");

        if (!$control2) {

            echo "<p style='color: red;'>Session bulunamadı (login_info) - Login olun</p>";

            echo "<p>Kontrol edilen session key: 'login_info'</p>";

            return;

        }

        

        echo "<p style='color: green;'>✓ Session bulundu</p>";        echo "<p>Session User ID: " . $control2->kullanici_id . "</p>";

        echo "<p>Session Email: " . $control2->kullanici_eposta . "</p>";

        echo "<p>Session Grup ID: " . $control2->grup_id . "</p>";

        

        // Kullanıcı bilgilerini veritabanından da al

        $user_info = $this->db->query("SELECT * FROM kullanicilar WHERE kullanici_id = ?", [$control2->kullanici_id])->row();

          if ($user_info) {

            echo "<h4>Veritabanı Kullanıcı Bilgileri:</h4>";

            echo "<p>ID: " . $user_info->kullanici_id . "</p>";

            echo "<p>Ad: " . $user_info->kullanici_ad . " " . $user_info->kullanici_soyad . "</p>";

            echo "<p>Email: " . $user_info->kullanici_eposta . "</p>";

            echo "<p>Grup ID: " . $user_info->grup_id . "</p>";

            

            // Grup adını al

            if ($user_info->grup_id) {

                $grup_info = $this->db->query("SELECT * FROM kullanici_grubu WHERE kg_id = ?", [$user_info->grup_id])->row();

                echo "<p>Grup Adı: " . ($grup_info ? $grup_info->kg_adi : 'Bulunamadı') . "</p>";

            }

            

            if ($user_info->grup_id == 1) {

                echo "<p style='color: green;'>✓ Admin yetkisi var - Test sayfasına erişebilir</p>";

                echo "<p><a href='" . base_url() . "rapor/test'>Test Sayfasına Git</a></p>";

            } else {

                echo "<p style='color: orange;'>⚠ Admin yetkisi yok (Grup ID: " . $user_info->grup_id . ")</p>";

            }

        } else {

            echo "<p style='color: red;'>Kullanıcı bilgileri veritabanında bulunamadı</p>";

        }

    }

    

    /**

     * Cron job isteği mi kontrol et

     */

    private function is_cron_request()

    {

        // User-Agent kontrolü ile cron job'dan gelip gelmediğini anla

        $user_agent = $this->input->server('HTTP_USER_AGENT');

        return (strpos($user_agent, 'CRM Cron Job') !== false) || 

               ($this->uri->segment(1) === 'rapor' && $this->uri->segment(2) === 'gunluk_rapor');

    }

    

    /**

     * Basit test method'u

     */

    public function basit_test()

    {

        echo "Basit test method'u çalışıyor - " . date('Y-m-d H:i:s');

        exit;

    }

}

?>

