<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Aktivasyon extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();

        $this->load->model('vt');

        $this->load->library('session');

        $this->load->helper('url');

        $this->load->database();



        $control = session("r", "login");



        // GIB yetki kontrolünü güvenli hale getir

        $gib_yetki = 0;

        try {

            $gib_yetki = gibYetki();

        } catch (Exception $e) {

            error_log("GIB Yetki kontrolü hatası: " . $e->getMessage());

            $gib_yetki = 0;

        }



        if ($gib_yetki == 1)

            redirect("home/hata");



        if (!$control) {

            redirect("check");

        }

    }



    public function index()

    {

        // Aktivasyon ana menü yetkisi kontrolü (ID: 900)

        if (!grup_modul_yetkisi_var(900)) {

            redirect("home/hata");

        }



        $data["baslik"] = "Aktivasyon / Ana Sayfa";

        

        // Son 10 aktivasyonu çek

        $aktivasyonQuery = "

            SELECT a.*, c.cari_ad, ad.aktivasyon_durum_adi, ad.aktivasyon_durum_renk, k.kullanici_ad

            FROM aktivasyon a

            LEFT JOIN cari c ON a.aktivasyon_cari_id = c.cari_id

            LEFT JOIN aktivasyon_durum ad ON a.aktivasyon_durum_id = ad.aktivasyon_durum_id

            LEFT JOIN kullanicilar k ON a.kullanici_id = k.kullanici_id

            ORDER BY a.olusturma_tarihi DESC

            LIMIT 10

        ";

        $data["aktivasyonlar"] = $this->db->query($aktivasyonQuery)->result();

        

        $this->load->view('aktivasyon/index', $data);

    }



    public function olustur($aktivasyon_id = null)

    {

        // Aktivasyon oluştur yetkisi kontrolü (ID: 901)

        if (!grup_modul_yetkisi_var(901)) {

            redirect("home/hata");

        }



        $data["baslik"] = $aktivasyon_id ? "Digiturk Aktivasyon / Düzenle" : "Digiturk Aktivasyon / Oluştur";

        

        // Cari listesi - stokGrup_id 1 ve 279 olan müşteriler (Digiturk)

        $cariQuery = "SELECT DISTINCT c.cari_id, c.cari_ad, c.cari_firmaTelefon, 

                             i.il as il_adi, ilce.ilce as ilce_adi

                      FROM cari c

                      LEFT JOIN iller i ON c.cari_il = i.id

                      LEFT JOIN ilceler ilce ON c.cari_ilce = ilce.id

                      JOIN satisfaturasi sf ON sf.satis_cariID = c.cari_id

                      JOIN satisfaturasistok sfs ON sfs.satisStok_satisFaturasiID = sf.satis_id

                      JOIN stok s ON s.stok_id = sfs.satisStok_stokID

                      JOIN stokgruplari sg ON sg.stokGrup_id = s.stok_stokGrupKoduID

                      WHERE c.cari_durum = 1 

                      AND sg.stokGrup_id IN (1, 279)

                      ORDER BY c.cari_ad ASC";

        $data["cari_listesi"] = $this->db->query($cariQuery)->result();



        // Aktivasyon durum listesi

        $durumQuery = "SELECT aktivasyon_durum_id, aktivasyon_durum_adi FROM aktivasyon_durum WHERE durum = 1 ORDER BY aktivasyon_durum_adi ASC";

        $data["durum_listesi"] = $this->db->query($durumQuery)->result();



        // Düzenleme modu ise mevcut veriyi çek

        if ($aktivasyon_id) {

            $aktivasyonQuery = "SELECT * FROM aktivasyon WHERE aktivasyon_id = ?";

            $aktivasyon = $this->db->query($aktivasyonQuery, array($aktivasyon_id))->row();

            

            if (!$aktivasyon) {

                redirect("aktivasyon");

            }

            

            $data["aktivasyon"] = $aktivasyon;

        }



        $this->load->view('aktivasyon/olustur', $data);

    }

    public function ssport_olustur($aktivasyon_id = null)

    {

        // S SPORT Aktivasyon oluştur yetkisi kontrolü (ID: 903)

        if (!grup_modul_yetkisi_var(903)) {

            redirect("home/hata");

        }



        $data["baslik"] = $aktivasyon_id ? "S SPORT Aktivasyon / Düzenle" : "S SPORT Aktivasyon / Oluştur";

        

        // Cari listesi - Ana hesap kısıtlaması kaldırıldı

        $cariQuery = "SELECT c.cari_id, c.cari_ad, c.cari_firmaTelefon, 

                             i.il as il_adi, ilce.ilce as ilce_adi

                      FROM cari c

                      LEFT JOIN iller i ON c.cari_il = i.id

                      LEFT JOIN ilceler ilce ON c.cari_ilce = ilce.id

                      WHERE c.cari_durum = 1 

                      ORDER BY c.cari_ad ASC";

        $data["cari_listesi"] = $this->db->query($cariQuery)->result();



        // Aktivasyon durum listesi

        $durumQuery = "SELECT aktivasyon_durum_id, aktivasyon_durum_adi FROM aktivasyon_durum WHERE durum = 1 ORDER BY aktivasyon_durum_adi ASC";

        $data["durum_listesi"] = $this->db->query($durumQuery)->result();



        // Düzenleme modu ise mevcut veriyi çek

        if ($aktivasyon_id) {

            $aktivasyonQuery = "SELECT * FROM aktivasyon WHERE aktivasyon_id = ?";

            $aktivasyon = $this->db->query($aktivasyonQuery, array($aktivasyon_id))->row();

            

            if (!$aktivasyon) {

                redirect("aktivasyon");

            }

            

            $data["aktivasyon"] = $aktivasyon;

        }



        $this->load->view('aktivasyon/ssport_olustur', $data);

    }

    public function tabii_olustur($aktivasyon_id = null)

    {

        // TABII Aktivasyon oluştur yetkisi kontrolü (ID: 904)

        if (!grup_modul_yetkisi_var(904)) {

            redirect("home/hata");

        }



        $data["baslik"] = $aktivasyon_id ? "TABII Aktivasyon / Düzenle" : "TABII Aktivasyon / Oluştur";

        

        // Cari listesi - Ana hesap kısıtlaması kaldırıldı

        $cariQuery = "SELECT c.cari_id, c.cari_ad, c.cari_firmaTelefon, 

                             i.il as il_adi, ilce.ilce as ilce_adi

                      FROM cari c

                      LEFT JOIN iller i ON c.cari_il = i.id

                      LEFT JOIN ilceler ilce ON c.cari_ilce = ilce.id

                      WHERE c.cari_durum = 1 

                      ORDER BY c.cari_ad ASC";

        $data["cari_listesi"] = $this->db->query($cariQuery)->result();



        // Aktivasyon durum listesi

        $durumQuery = "SELECT aktivasyon_durum_id, aktivasyon_durum_adi FROM aktivasyon_durum WHERE durum = 1 ORDER BY aktivasyon_durum_adi ASC";

        $data["durum_listesi"] = $this->db->query($durumQuery)->result();



        // Düzenleme modu ise mevcut veriyi çek

        if ($aktivasyon_id) {

            $aktivasyonQuery = "SELECT * FROM aktivasyon WHERE aktivasyon_id = ?";

            $aktivasyon = $this->db->query($aktivasyonQuery, array($aktivasyon_id))->row();

            

            if (!$aktivasyon) {

                redirect("aktivasyon");

            }

            

            $data["aktivasyon"] = $aktivasyon;

        }



        $this->load->view('aktivasyon/tabii_olustur', $data);

    }

    public function ssport_kaydet()
    {
        // S SPORT Aktivasyon oluştur yetkisi kontrolü (ID: 903)
        if (!grup_modul_yetkisi_var(903)) {
            redirect("home/hata");
        }

        $aktivasyon_id = $this->input->post('aktivasyon_id');
        $cari_id = $this->input->post('cari_id');
        $uye_no = $this->input->post('uye_no');
        $kampanya_kodu = $this->input->post('kampanya_kodu');
        $aktivasyon_tarihi = $this->input->post('aktivasyon_tarihi');
        $eposta = $this->input->post('eposta');
        $telefon = $this->input->post('telefon');
        $adi = $this->input->post('adi');
        $soyadi = $this->input->post('soyadi');
        $aciklama = $this->input->post('aciklama');
        $durum_id = $this->input->post('durum_id');

        // Kullanıcı ID'sini session'dan al
        $control = session("r", "login_info");
        $kullanici_id = $control->kullanici_id;

        $data = array(
            'aktivasyon_cari_id' => $cari_id,
            'aktivasyon_uye_no' => $uye_no,
            'aktivasyon_kampanya_kodu' => $kampanya_kodu,
            'aktivasyon_tarihi' => $aktivasyon_tarihi ? date('Y-m-d H:i:s', strtotime($aktivasyon_tarihi)) : null,
            'aktivasyon_eposta' => $eposta,
            'aktivasyon_telefon' => $telefon,
            'aktivasyon_adi' => $adi,
            'aktivasyon_soyad' => $soyadi,
            'kullanici_id' => $kullanici_id,
            'aktivasyon_aciklama' => $aciklama,
            'aktivasyon_durum_id' => $durum_id,
            'aktivasyon_stok' => 2  // S SPORT için her zaman 2 (1-DIGITURK, 2-S SPORT, 3-TABII, 4-EXXEN)
        );

        if ($aktivasyon_id) {
            // Güncelleme
            $where = array('aktivasyon_id' => $aktivasyon_id);
            $result = $this->vt->update('aktivasyon', $where, $data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'S SPORT Aktivasyon başarıyla güncellendi.');
            } else {
                $this->session->set_flashdata('error', 'S SPORT Aktivasyon güncellenirken bir hata oluştu.');
            }
        } else {
            // Yeni kayıt
            $result = $this->vt->insert('aktivasyon', $data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'S SPORT Aktivasyon başarıyla oluşturuldu.');
            } else {
                $this->session->set_flashdata('error', 'S SPORT Aktivasyon oluşturulurken bir hata oluştu.');
            }
        }

        redirect('aktivasyon');
    }

    public function tabii_kaydet()
    {
        // TABII Aktivasyon oluştur yetkisi kontrolü (ID: 904)
        if (!grup_modul_yetkisi_var(904)) {
            redirect("home/hata");
        }

        $aktivasyon_id = $this->input->post('aktivasyon_id');
        $cari_id = $this->input->post('cari_id');
        $uye_no = $this->input->post('uye_no');
        $kampanya_kodu = $this->input->post('kampanya_kodu');
        $aktivasyon_tarihi = $this->input->post('aktivasyon_tarihi');
        $eposta = $this->input->post('eposta');
        $telefon = $this->input->post('telefon');
        $adi = $this->input->post('adi');
        $soyadi = $this->input->post('soyadi');
        $aciklama = $this->input->post('aciklama');
        $durum_id = $this->input->post('durum_id');

        // Kullanıcı ID'sini session'dan al
        $control = session("r", "login_info");
        $kullanici_id = $control->kullanici_id;

        $data = array(
            'aktivasyon_cari_id' => $cari_id,
            'aktivasyon_uye_no' => $uye_no,
            'aktivasyon_kampanya_kodu' => $kampanya_kodu,
            'aktivasyon_tarihi' => $aktivasyon_tarihi ? date('Y-m-d H:i:s', strtotime($aktivasyon_tarihi)) : null,
            'aktivasyon_eposta' => $eposta,
            'aktivasyon_telefon' => $telefon,
            'aktivasyon_adi' => $adi,
            'aktivasyon_soyad' => $soyadi,
            'kullanici_id' => $kullanici_id,
            'aktivasyon_aciklama' => $aciklama,
            'aktivasyon_durum_id' => $durum_id,
            'aktivasyon_stok' => 3  // TABII için her zaman 3 (1-DIGITURK, 2-S SPORT, 3-TABII, 4-EXXEN)
        );

        if ($aktivasyon_id) {
            // Güncelleme
            $where = array('aktivasyon_id' => $aktivasyon_id);
            $result = $this->vt->update('aktivasyon', $where, $data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'TABII Aktivasyon başarıyla güncellendi.');
            } else {
                $this->session->set_flashdata('error', 'TABII Aktivasyon güncellenirken bir hata oluştu.');
            }
        } else {
            // Yeni kayıt
            $result = $this->vt->insert('aktivasyon', $data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'TABII Aktivasyon başarıyla oluşturuldu.');
            } else {
                $this->session->set_flashdata('error', 'TABII Aktivasyon oluşturulurken bir hata oluştu.');
            }
        }

        redirect('aktivasyon');
    }



    public function kaydet()

    {

        // Aktivasyon oluştur yetkisi kontrolü (ID: 901)

        if (!grup_modul_yetkisi_var(901)) {

            redirect("home/hata");

        }



        $aktivasyon_id = $this->input->post('aktivasyon_id');

        $cari_id = $this->input->post('cari_id');

        $uye_no = $this->input->post('uye_no');

        $kutu_no = $this->input->post('kutu_no');

        $kart_no = $this->input->post('kart_no');

        $aciklama = $this->input->post('aciklama');

        $durum_id = $this->input->post('durum_id');



        // Kullanıcı ID'sini session'dan al

        $control = session("r", "login_info");

        $kullanici_id = $control->kullanici_id;



        $data = array(

            'aktivasyon_cari_id' => $cari_id,

            'aktivasyon_uye_no' => $uye_no,

            'aktivasyon_kutu_no' => $kutu_no,

            'aktivasyon_kart_no' => $kart_no,

            'kullanici_id' => $kullanici_id,

            'aktivasyon_aciklama' => $aciklama,

            'aktivasyon_durum_id' => $durum_id,

            'aktivasyon_stok' => 1  // DIGITURK için her zaman 1 (1-DIGITURK, 2-S SPORT, 3-TABII, 4-EXXEN)

        );



        if ($aktivasyon_id) {

            // Güncelleme

            $where = array('aktivasyon_id' => $aktivasyon_id);

            $result = $this->vt->update('aktivasyon', $where, $data);

            

            if ($result) {

                $this->session->set_flashdata('success', 'Digiturk Aktivasyon başarıyla güncellendi.');

            } else {

                $this->session->set_flashdata('error', 'Digiturk Aktivasyon güncellenirken bir hata oluştu.');

            }

        } else {

            // Yeni kayıt

            $result = $this->vt->insert('aktivasyon', $data);

            

            if ($result) {

                $this->session->set_flashdata('success', 'Digiturk Aktivasyon başarıyla oluşturuldu.');

            } else {

                $this->session->set_flashdata('error', 'Digiturk Aktivasyon oluşturulurken bir hata oluştu.');

            }

        }



        redirect('aktivasyon');

    }



    public function listele()

    {

        // Aktivasyon listesi yetkisi kontrolü (ID: 902)

        if (!grup_modul_yetkisi_var(902)) {

            redirect("home/hata");

        }



        $data["baslik"] = "Aktivasyon / Liste";

        

        $aktivasyonQuery = "

            SELECT a.*, c.cari_ad, ad.aktivasyon_durum_adi, ad.aktivasyon_durum_renk, k.kullanici_ad

            FROM aktivasyon a

            LEFT JOIN cari c ON a.aktivasyon_cari_id = c.cari_id

            LEFT JOIN aktivasyon_durum ad ON a.aktivasyon_durum_id = ad.aktivasyon_durum_id

            LEFT JOIN kullanicilar k ON a.kullanici_id = k.kullanici_id

            ORDER BY a.olusturma_tarihi DESC

        ";

        $data["aktivasyonlar"] = $this->db->query($aktivasyonQuery)->result();



        $this->load->view('aktivasyon/listele', $data);

    }



    public function sil($aktivasyon_id)

    {

        // Aktivasyon oluştur yetkisi kontrolü (ID: 901)

        if (!grup_modul_yetkisi_var(901)) {

            redirect("home/hata");

        }



        $result = $this->vt->del('aktivasyon', 'aktivasyon_id', $aktivasyon_id);



        if ($result) {

            $this->session->set_flashdata('success', 'Digiturk Aktivasyon başarıyla silindi.');

        } else {

            $this->session->set_flashdata('error', 'Aktivasyon silinirken bir hata oluştu.');

        }



        redirect('aktivasyon');

    }



    // AJAX için cari arama

    public function cari_ara()

    {

        try {

            $term = $this->input->get('q');

            $stok_grup_id = $this->input->get('stok_grup_id');

            

            // Varsayılan: Digiturk (1)

            if (empty($stok_grup_id)) {

                $stok_grup_id = '1';

            }

            

            // Virgülle ayrılmış değerleri diziye çevir

            $stokGrupList = array();

            if (strpos($stok_grup_id, ',') !== false) {

                $parts = explode(',', $stok_grup_id);

                foreach ($parts as $p) {

                    $p = trim($p);

                    if ($p !== '' && is_numeric($p)) {

                        $stokGrupList[] = (int)$p;

                    }

                }

            } else {

                if (is_numeric($stok_grup_id)) {

                    $stokGrupList[] = (int)$stok_grup_id;

                }

            }

            

            if (empty($stokGrupList)) {

                $stokGrupList = array(1);

            }

            

            // IN için placeholder oluştur

            $placeholders = implode(',', array_fill(0, count($stokGrupList), '?'));

            

            $query = "SELECT DISTINCT c.cari_id, c.cari_ad, c.cari_firmaTelefon, 

                             i.il as il_adi, ilce.ilce as ilce_adi

                      FROM cari c

                      LEFT JOIN iller i ON c.cari_il = i.id

                      LEFT JOIN ilceler ilce ON c.cari_ilce = ilce.id

                      JOIN satisfaturasi sf ON sf.satis_cariID = c.cari_id

                      JOIN satisfaturasistok sfs ON sfs.satisStok_satisFaturasiID = sf.satis_id

                      JOIN stok s ON s.stok_id = sfs.satisStok_stokID

                      JOIN stokgruplari sg ON sg.stokGrup_id = s.stok_stokGrupKoduID

                      WHERE c.cari_durum = 1 

                      AND sg.stokGrup_id IN ($placeholders)

                      AND c.cari_ad LIKE ? 

                      ORDER BY c.cari_ad ASC 

                      LIMIT 20";

            

            // Parametreleri birleştir

            $params = $stokGrupList;

            $params[] = "%$term%";

            

            $result = $this->db->query($query, $params)->result();

            

            $data = array();

            foreach ($result as $row) {

                $displayText = $row->cari_ad;

                

                // İl, İlçe ve Telefon bilgilerini ekle

                $extraInfo = array();

                if (!empty($row->il_adi)) {

                    $extraInfo[] = $row->il_adi;

                }

                if (!empty($row->ilce_adi)) {

                    $extraInfo[] = $row->ilce_adi;

                }

                if (!empty($row->cari_firmaTelefon)) {

                    $extraInfo[] = $row->cari_firmaTelefon;

                }

                

                if (!empty($extraInfo)) {

                    $displayText .= ' (' . implode(', ', $extraInfo) . ')';

                }

                

                $data[] = array(

                    'id' => $row->cari_id,

                    'text' => $displayText

                );

            }

            

            header('Content-Type: application/json');

            echo json_encode($data);

        } catch (Exception $e) {

            header('Content-Type: application/json');

            echo json_encode(array('error' => $e->getMessage()));

        }

    }



    // AJAX için cari detayları getir

    public function cari_detay_getir()

    {

        try {

            $cari_id = $this->input->get('cari_id');

            

            if (!$cari_id) {

                header('Content-Type: application/json');

                echo json_encode(array('error' => 'Cari ID gerekli'));

                return;

            }

            

            // Cari bilgilerini personel ve evrak/görsel bilgileriyle birlikte getir

            $query = "SELECT c.cari_id, c.cari_ad, c.cari_olusturan, c.evrak_dosya, c.fotograf_dosya,

                             k.kullanici_ad, k.kullanici_soyad

                      FROM cari c

                      LEFT JOIN kullanicilar k ON c.cari_olusturan = k.kullanici_id

                      WHERE c.cari_id = ? AND c.cari_durum = 1";

            

            $result = $this->db->query($query, array($cari_id))->row();

            

            if (!$result) {

                header('Content-Type: application/json');

                echo json_encode(array('error' => 'Cari bulunamadı'));

                return;

            }



            // Satış faturası bilgilerini getir (en son kaydı)

            $satisQuery = "SELECT satis_id, satis_kdvToplam, satis_aciklama, satis_dosya

                          FROM satisFaturasi 

                          WHERE satis_cariID = ? 

                          ORDER BY satis_id DESC 

                          LIMIT 1";

            

            $satisResult = $this->db->query($satisQuery, array($cari_id))->row();

            

            // Personel adı soyadı birleştir

            $personel_adi = '';

            if (!empty($result->kullanici_ad) || !empty($result->kullanici_soyad)) {

                $personel_adi = trim($result->kullanici_ad . ' ' . $result->kullanici_soyad);

            }

            

            // Evrak dosyalarını parse et

            $evrak_dosyalar = array();

            if (!empty($result->evrak_dosya)) {

                $evraklar = explode(',', $result->evrak_dosya);

                foreach ($evraklar as $evrak) {

                    $evrak = trim($evrak);

                    if (!empty($evrak)) {

                        $evrak_dosyalar[] = array(

                            'dosya' => $evrak,

                            'link' => base_url('assets/uploads/' . $evrak)

                        );

                    }

                }

            }

            

            // Fotoğraf dosyalarını parse et

            $fotograf_dosyalar = array();

            if (!empty($result->fotograf_dosya)) {

                $fotograflar = explode(',', $result->fotograf_dosya);

                foreach ($fotograflar as $fotograf) {

                    $fotograf = trim($fotograf);

                    if (!empty($fotograf)) {

                        $fotograf_dosyalar[] = array(

                            'dosya' => $fotograf,

                            'link' => base_url('assets/uploads/' . $fotograf)

                        );

                    }

                }

            }

            

            // Sözleşme görsel dosyalarını parse et

            $sozlesme_dosyalar = array();

            if ($satisResult && !empty($satisResult->satis_dosya)) {

                $sozlesmeDosyalar = explode(',', $satisResult->satis_dosya);

                foreach ($sozlesmeDosyalar as $sozlesmeDosya) {

                    $sozlesmeDosya = trim($sozlesmeDosya);

                    if (!empty($sozlesmeDosya)) {

                        $sozlesme_dosyalar[] = array(

                            'dosya' => $sozlesmeDosya,

                            'link' => base_url('assets/uploads/' . $sozlesmeDosya)

                        );

                    }

                }

            }

            

            // Tahsilat bilgilerini getir (muhasebe_tahsilat_durum tablosundan)

            $tahsilatlar = array();

            

            // Önce tablo varlığını kontrol et

            $tableExists = $this->db->query("SHOW TABLES LIKE 'muhasebe_tahsilat_durum'")->num_rows();

            

            if ($tableExists > 0) {

                $tahsilatQuery = "SELECT 

                                   mtd.id,

                                   mtd.tahsilat_tipi,

                                   mtd.kayit_id,

                                   CASE 

                                       WHEN mtd.tahsilat_tipi = 1 THEN 'Banka'

                                       WHEN mtd.tahsilat_tipi = 2 THEN 'Çek'

                                       WHEN mtd.tahsilat_tipi = 3 THEN 'Kasa'

                                       WHEN mtd.tahsilat_tipi = 4 THEN 'Senet'

                                       ELSE 'Bilinmiyor'

                                   END as tahsilat_tipi_adi,

                                   mtd.durum,

                                   CASE 

                                       WHEN mtd.durum = 1 THEN 'Onay Bekliyor'

                                       WHEN mtd.durum = 2 THEN 'Onaylandı'

                                       WHEN mtd.durum = 3 THEN 'Reddedildi'

                                       ELSE 'Bilinmiyor'

                                   END as durum_adi,

                                   COALESCE(bh.bh_giris, c.cek_tutar, kh.kh_giris, s.senet_tutar) as tutar,

                                   COALESCE(bh.bh_gorsel, c.cek_gorsel, kh.kh_gorsel, s.senet_gorsel) as gorsel,

                                   mtd.olusturma_tarihi

                                FROM muhasebe_tahsilat_durum mtd

                                -- Banka hareketleri

                                LEFT JOIN bankaHareketleri bh ON mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id

                                LEFT JOIN cari bh_cari ON bh.bh_cariID = bh_cari.cari_id

                                -- Çek

                                LEFT JOIN cek c ON mtd.tahsilat_tipi = 2 AND mtd.kayit_id = c.cek_id

                                LEFT JOIN cari c_cari ON c.cek_cariID = c_cari.cari_id

                                -- Kasa hareketleri

                                LEFT JOIN kasaHareketleri kh ON mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id

                                LEFT JOIN cari kh_cari ON kh.kh_cariID = kh_cari.cari_id

                                -- Senet

                                LEFT JOIN senet s ON mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id

                                LEFT JOIN cari s_cari ON s.senet_cariID = s_cari.cari_id

                                WHERE (bh_cari.cari_id = ? OR c_cari.cari_id = ? OR kh_cari.cari_id = ? OR s_cari.cari_id = ?)

                                AND mtd.tahsilat_tipi IN (1, 2, 3, 4)

                                ORDER BY mtd.olusturma_tarihi DESC";

                                

                try {

                    $tahsilatResult = $this->db->query($tahsilatQuery, array($cari_id, $cari_id, $cari_id, $cari_id))->result();

                    

                    foreach ($tahsilatResult as $tahsilat) {

                        $gorsel_link = '';

                        if (!empty($tahsilat->gorsel)) {

                            // Tahsilat tipine göre doğru klasör yolunu belirle

                            if($tahsilat->tahsilat_tipi == 1) {

                                // Banka - dekontlar klasörü

                                $gorsel_link = base_url('assets/uploads/dekontlar/' . $tahsilat->gorsel);

                            } elseif($tahsilat->tahsilat_tipi == 2) {

                                // Çek - cekler klasörü

                                $gorsel_link = base_url('assets/uploads/cekler/' . $tahsilat->gorsel);

                            } elseif($tahsilat->tahsilat_tipi == 4) {

                                // Senet - senetler klasörü

                                $gorsel_link = base_url('assets/uploads/senetler/' . $tahsilat->gorsel);

                            } else {

                                // Diğer durumlar - dekontlar klasörü (Kasa vs.)

                                $gorsel_link = base_url('assets/uploads/dekontlar/' . $tahsilat->gorsel);

                            }

                        }

                        

                        $tahsilatlar[] = array(

                            'id' => $tahsilat->kayit_id,

                            'tahsilat_tipi' => $tahsilat->tahsilat_tipi_adi,

                            'tahsilat_tipi_kodu' => $tahsilat->tahsilat_tipi,

                            'tutar' => $tahsilat->tutar ? number_format($tahsilat->tutar, 2, ',', '.') . ' ₺' : '0,00 ₺',

                            'durum' => $tahsilat->durum_adi,

                            'durum_code' => $tahsilat->durum,

                            'gorsel' => $tahsilat->gorsel,

                            'gorsel_link' => $gorsel_link,

                            'olusturma_tarihi' => $tahsilat->olusturma_tarihi

                        );

                    }

                } catch (Exception $e) {

                    // Hata durumunda boş array döndür

                    error_log('Tahsilat verileri alınırken hata: ' . $e->getMessage());

                }

            }

            

            $data = array(

                'personel' => $personel_adi,

                'evrak_dosyalar' => $evrak_dosyalar,

                'fotograf_dosyalar' => $fotograf_dosyalar,

                'sozlesme_tutar' => $satisResult ? number_format($satisResult->satis_kdvToplam, 2, ',', '.') . ' ₺' : 'Belirtilmemiş',

                'sozlesme_aciklama' => $satisResult && !empty($satisResult->satis_aciklama) ? $satisResult->satis_aciklama : 'Açıklama bulunmuyor',

                'sozlesme_dosyalar' => $sozlesme_dosyalar,

                'sozlesme_id' => $satisResult ? $satisResult->satis_id : null,

                'duzenle_link' => base_url('cari/cari-karti-duzenle/' . $result->cari_id),

                'tahsilatlar' => $tahsilatlar

            );

            

            header('Content-Type: application/json');

            echo json_encode($data);

            

        } catch (Exception $e) {

            header('Content-Type: application/json');

            echo json_encode(array('error' => $e->getMessage()));

        }

    }



    // Tahsilat görseli yükleme fonksiyonu

    public function tahsilat_gorsel_yukle()

    {

        try {

            // Yetki kontrolü

            if (!grup_modul_yetkisi_var(901)) {

                header('Content-Type: application/json');

                echo json_encode(array('success' => false, 'message' => 'Yetkiniz bulunmamaktadır.'));

                return;

            }



            $tahsilat_id = postval('tahsilat_id');

            $tahsilat_tipi = postval('tahsilat_tipi'); // 1: Banka, 2: Çek, 3: Kasa, 4: Senet

            

            if (!$tahsilat_id) {

                header('Content-Type: application/json');

                echo json_encode(array('success' => false, 'message' => 'Tahsilat ID gerekli.'));

                return;

            }



            // Tahsilat tipine göre tablo ve klasör belirle

            $tablo = '';

            $id_field = '';

            $gorsel_field = '';

            $upload_folder = '';

            

            switch($tahsilat_tipi) {

                case '1': // Banka

                    $tablo = 'bankaHareketleri';

                    $id_field = 'bh_id';

                    $gorsel_field = 'bh_gorsel';

                    $upload_folder = 'dekontlar';

                    break;

                case '2': // Çek

                    $tablo = 'cek';

                    $id_field = 'cek_id';

                    $gorsel_field = 'cek_gorsel';

                    $upload_folder = 'cekler';

                    break;

                case '3': // Kasa

                    $tablo = 'kasaHareketleri';

                    $id_field = 'kh_id';

                    $gorsel_field = 'kh_gorsel';

                    $upload_folder = 'dekontlar';

                    break;

                case '4': // Senet

                    $tablo = 'senet';

                    $id_field = 'senet_id';

                    $gorsel_field = 'senet_gorsel';

                    $upload_folder = 'senetler';

                    break;

                default:

                    header('Content-Type: application/json');

                    echo json_encode(array('success' => false, 'message' => 'Geçersiz tahsilat tipi.'));

                    return;

            }



            // Dosya yükleme ayarları

            $config['upload_path'] = "./assets/uploads/$upload_folder/";

            $config['allowed_types'] = 'gif|jpg|png|jpeg';

            $config['max_size'] = 5120; // 5MB

            $config['encrypt_name'] = TRUE;

            $config['remove_spaces'] = TRUE;



            // Upload klasörü yoksa oluştur

            if (!is_dir($config['upload_path'])) {

                mkdir($config['upload_path'], 0755, true);

            }



            $this->load->library('upload', $config);



            if ($this->upload->do_upload('gorsel')) {

                $upload_data = $this->upload->data();

                

                // Eski görseli sil (varsa)

                $eski_gorsel_query = "SELECT $gorsel_field FROM $tablo WHERE $id_field = ?";

                $eski_gorsel = $this->db->query($eski_gorsel_query, array($tahsilat_id))->row();

                

                if ($eski_gorsel && !empty($eski_gorsel->{$gorsel_field})) {

                    $eski_dosya_yolu = "./assets/uploads/$upload_folder/" . $eski_gorsel->{$gorsel_field};

                    if (file_exists($eski_dosya_yolu)) {

                        unlink($eski_dosya_yolu);

                    }

                }

                

                // Yeni görsel adını veritabanına kaydet

                $update_data = array(

                    $gorsel_field => $upload_data['file_name']

                );

                

                $where = array($id_field => $tahsilat_id);

                $result = $this->vt->update($tablo, $where, $update_data);

                

                if ($result) {

                    header('Content-Type: application/json');

                    echo json_encode(array(

                        'success' => true, 

                        'message' => 'Görsel başarıyla yüklendi.',

                        'file_name' => $upload_data['file_name']

                    ));

                } else {

                    // Yüklenen dosyayı sil (veritabanı kaydı başarısız)

                    unlink($upload_data['full_path']);

                    header('Content-Type: application/json');

                    echo json_encode(array('success' => false, 'message' => 'Veritabanı güncellenirken hata oluştu.'));

                }

            } else {

                $error = $this->upload->display_errors('', '');

                header('Content-Type: application/json');

                echo json_encode(array('success' => false, 'message' => $error));

            }

            

        } catch (Exception $e) {

            header('Content-Type: application/json');

            echo json_encode(array('success' => false, 'message' => $e->getMessage()));

        }

    }

}

