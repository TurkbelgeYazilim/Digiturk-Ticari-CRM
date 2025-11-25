<?php

// require_once satırı kaldırıldı, CodeIgniter kendi çekirdeğini otomatik yükler

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Illegal extends CI_Controller {

    public function __construct() {

        parent::__construct();

        $this->load->database();

        $this->load->library('session');

        $this->load->model('Vt');

    }



    // AJAX: potansiyel_cari filtreli liste

    public function potansiyelCariFiltrele() {

        $filters = $this->input->post();

        $this->db->from('potansiyel_cari');

        if (!empty($filters['sezon'])) {

            $this->db->where('sezon_id', $filters['sezon']);

        }

        if (!empty($filters['cari_grup'])) {

            $this->db->where('potansiyel_cari_grup', $filters['cari_grup']);

        }

        if (!empty($filters['sektor'])) {

            $this->db->where('sektor_id', $filters['sektor']);

        }

        if (!empty($filters['il'])) {

            $this->db->where('potansiyel_il_id', $filters['il']);

        }

        if (!empty($filters['ilce'])) {

            $this->db->where('potansiyel_ilce_id', $filters['ilce']);

        }

        if (!empty($filters['mahalle'])) {

            $this->db->where('potansiyel_mahalle', $filters['mahalle']);

        }

        if (!empty($filters['potansiyel_cari_firmaTelefon'])) {

            $this->db->where('potansiyel_cari_firmaTelefon', $filters['potansiyel_cari_firmaTelefon']);

        }        

        $this->db->order_by('potansiyel_cari_id', 'DESC');

        $result = $this->db->get()->result();

        $data = array();

        foreach($result as $row) {

            // Sezon adı

            $sezon_adi = '';

            if ($row->sezon_id) {

                $sezon = $this->db->get_where('sezonlar', ['sezon_id' => $row->sezon_id])->row();

                $sezon_adi = $sezon ? $sezon->sezon_adi : '';

            }

            // Sektör adı

            $sektor_adi = '';

            if ($row->sektor_id) {

                $sektor = $this->db->get_where('sektorler', ['sektor_id' => $row->sektor_id])->row();

                $sektor_adi = $sektor ? $sektor->sektor_adi : '';

            }

            // İl adı

            $il_adi = '';

            if ($row->potansiyel_il_id) {

                $il = $this->db->get_where('iller', ['id' => $row->potansiyel_il_id])->row();

                $il_adi = $il ? $il->il : '';

            }

            // İlçe adı

            $ilce_adi = '';

            if ($row->potansiyel_ilce_id) {

                $ilce = $this->db->get_where('ilceler', ['id' => $row->potansiyel_ilce_id])->row();

                $ilce_adi = $ilce ? $ilce->ilce : '';

            }

            $data[] = array_merge((array)$row, [

                'sezon_adi' => $sezon_adi,

                'sektor_adi' => $sektor_adi,

                'il_adi' => $il_adi,

                'ilce_adi' => $ilce_adi

            ]);

        }

        echo json_encode([

            'status' => 'success',

            'data' => $data

        ]);

    }



    // İllegal Tespit Oluştur sayfası

    public function illegal_tespit_olustur() {
        $data = [];
        
        // Edit parametresi kontrolü
        $edit_id = $this->input->get('edit');
        if (!empty($edit_id)) {
            // Tespit bilgilerini çek
            $this->db->select('
                it.*,
                ic.illegal_cari_isletme_adi,
                ic.illegal_cari_firmaTelefon,
                ic.illegal_cari_ulke,
                ic.illegal_cari_il,
                ic.illegal_cari_ilce,
                ic.illegal_cari_adres
            ');
            $this->db->from('illegal_tespit it');
            $this->db->join('illegal_cari ic', 'it.illegal_cari_id = ic.illegal_cari_id', 'left');
            $this->db->where('it.illegal_tespit_id', $edit_id);
            $tespit_data = $this->db->get()->row();
            
            if ($tespit_data) {
                $data['edit_data'] = $tespit_data;
            } else {
                $data['error_message'] = 'İllegal tespit kaydı bulunamadı (ID: ' . $edit_id . ')';
            }
        }
        
        $this->load->view('illegal/illegal_tespit_olustur', $data);
    }

    // Cari Autocomplete
    public function cari_autocomplete() {
        header('Content-Type: application/json');
        
        $term = $this->input->post('term');
        $returnData = array();
        
        if (strlen($term) >= 3) {
            $this->db->select('illegal_cari_id as id, illegal_cari_isletme_adi, illegal_cari_ad, illegal_cari_soyad, illegal_cari_firmaTelefon');
            $this->db->from('illegal_cari');
            $this->db->group_start();
            $this->db->like('illegal_cari_isletme_adi', $term);
            $this->db->or_like('illegal_cari_ad', $term);
            $this->db->or_like('illegal_cari_soyad', $term);
            $this->db->or_like('illegal_cari_firmaTelefon', $term);
            $this->db->group_end();
            $this->db->where('illegal_cari_durum', 1);
            $this->db->limit(20);
            
            $query = $this->db->get();
            $results = $query->result();
            
            foreach ($results as $row) {
                $display_text = $row->illegal_cari_isletme_adi;
                if ($row->illegal_cari_ad || $row->illegal_cari_soyad) {
                    $display_text .= ' (' . trim($row->illegal_cari_ad . ' ' . $row->illegal_cari_soyad) . ')';
                }
                if ($row->illegal_cari_firmaTelefon) {
                    $display_text .= ' - ' . $row->illegal_cari_firmaTelefon;
                }
                
                $returnData[] = [
                    'id' => $row->id,
                    'value' => $display_text,
                    'label' => $display_text
                ];
            }
        }
        
        echo json_encode($returnData);
        die();
    }

    // İllegal Tespit Kaydet AJAX endpoint - Güncellenmiş
    public function illegal_tespit_kaydet() {
        header('Content-Type: application/json');
        
        // Session kontrolü
        $control2 = session("r", "login_info");
        $u_id = $control2->kullanici_id ?? 1;
        
        // Edit işlemi mi kontrol et
        $illegal_tespit_id = $this->input->post('illegal_tespit_id');
        $is_edit = !empty($illegal_tespit_id);
        
        // Verileri al ve doğrula
        $cari_bilgi = trim($this->input->post('illegal_cari_bilgi'));
        $cari_id = $this->input->post('illegal_cari_id');
        $tespit_tarih = $this->input->post('illegal_tespit_tarih');
        $tespit_saat = $this->input->post('illegal_tespit_saat');
        $takim_id = $this->input->post('illegal_tespit_takim_id');
        $rakip_takim_id = $this->input->post('illegal_tespit_rakip_takim_id');
        $personel_id = $this->input->post('illegal_tespit_personel_id');
        $il = $this->input->post('illegal_cari_il');
        $ilce = $this->input->post('illegal_cari_ilce');
        
        if (empty($cari_bilgi)) {
            echo json_encode(['status' => 'error', 'message' => 'Cari bilgisi zorunludur!']);
            die();
        }
        
        if (empty($tespit_tarih)) {
            echo json_encode(['status' => 'error', 'message' => 'Tespit tarihi zorunludur!']);
            die();
        }
        
        if (empty($tespit_saat)) {
            echo json_encode(['status' => 'error', 'message' => 'Tespit saati zorunludur!']);
            die();
        }
        
        if (empty($takim_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Takım seçimi zorunludur!']);
            die();
        }
        
        if (empty($rakip_takim_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Rakip takım seçimi zorunludur!']);
            die();
        }
        
        if (empty($personel_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Tespiti yapan personel seçimi zorunludur!']);
            die();
        }
        
        if (empty($il)) {
            echo json_encode(['status' => 'error', 'message' => 'İl seçimi zorunludur!']);
            die();
        }
        
        if (empty($ilce)) {
            echo json_encode(['status' => 'error', 'message' => 'İlçe seçimi zorunludur!']);
            die();
        }
        
        // Cari bilgilerini kaydet/güncelle
        $cari_data = array(
            'illegal_cari_isletme_adi' => $cari_bilgi,
            'illegal_cari_firmaTelefon' => $this->input->post('illegal_cari_telefon'),
            'illegal_cari_ulke' => $this->input->post('illegal_cari_ulke'),
            'illegal_cari_il' => $il,
            'illegal_cari_ilce' => $ilce,
            'illegal_cari_adres' => $this->input->post('illegal_cari_adres')
        );
        
        if ($is_edit && !empty($cari_id)) {
            // Edit modunda mevcut cari'yi güncelle
            $this->db->where('illegal_cari_id', $cari_id);
            $this->db->update('illegal_cari', $cari_data);
        } else if (!empty($cari_id)) {
            // Mevcut cari'yi güncelle
            $this->db->where('illegal_cari_id', $cari_id);
            $this->db->update('illegal_cari', $cari_data);
        } else {
            // Yeni cari oluştur
            $cari_data['illegal_cari_olusturan'] = $u_id;
            $cari_data['illegal_cari_olusturmaTarihi'] = date('Y-m-d');
            $cari_data['illegal_cari_durum'] = 1;
            
            if ($this->db->insert('illegal_cari', $cari_data)) {
                $cari_id = $this->db->insert_id();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Cari kayıt hatası oluştu!']);
                die();
            }
        }
        
        // Dosya yükleme işlemleri
        $tespit_gorsel_files = '';
        $tutanak_gorsel_files = '';
        
        // Edit modunda mevcut dosyaları koru
        if ($is_edit) {
            $existing_data = $this->db->get_where('illegal_tespit', ['illegal_tespit_id' => $illegal_tespit_id])->row();
            if ($existing_data) {
                $tespit_gorsel_files = $existing_data->illegal_tespit_gorsel;
                $tutanak_gorsel_files = $existing_data->illegal_tespit_tutanak_gorsel;
            }
        }
        
        // Tespit görselleri yükleme
        if (!empty($_FILES['illegal_tespit_gorsel']['name'][0])) {
            $upload_path = './assets/uploads/illegal_tespit/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            
            $tespit_files = array();
            foreach ($_FILES['illegal_tespit_gorsel']['name'] as $key => $name) {
                if (!empty($name)) {
                    $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                    $new_filename = 'tespit_' . time() . '_' . $key . '.' . $file_extension;
                    $target_file = $upload_path . $new_filename;
                    
                    if (move_uploaded_file($_FILES['illegal_tespit_gorsel']['tmp_name'][$key], $target_file)) {
                        $tespit_files[] = $new_filename;
                    }
                }
            }
            if (!empty($tespit_files)) {
                $new_tespit_files = implode(',', $tespit_files);
                if ($is_edit && !empty($tespit_gorsel_files)) {
                    $tespit_gorsel_files .= ',' . $new_tespit_files;
                } else {
                    $tespit_gorsel_files = $new_tespit_files;
                }
            }
        }
        
        // Tutanak görselleri yükleme
        if (!empty($_FILES['illegal_tespit_tutanak_gorsel']['name'][0])) {
            $upload_path = './assets/uploads/illegal_tespit/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            
            $tutanak_files = array();
            foreach ($_FILES['illegal_tespit_tutanak_gorsel']['name'] as $key => $name) {
                if (!empty($name)) {
                    $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                    $new_filename = 'tutanak_' . time() . '_' . $key . '.' . $file_extension;
                    $target_file = $upload_path . $new_filename;
                    
                    if (move_uploaded_file($_FILES['illegal_tespit_tutanak_gorsel']['tmp_name'][$key], $target_file)) {
                        $tutanak_files[] = $new_filename;
                    }
                }
            }
            if (!empty($tutanak_files)) {
                $new_tutanak_files = implode(',', $tutanak_files);
                if ($is_edit && !empty($tutanak_gorsel_files)) {
                    $tutanak_gorsel_files .= ',' . $new_tutanak_files;
                } else {
                    $tutanak_gorsel_files = $new_tutanak_files;
                }
            }
        }
        
        // Veritabanı verilerini hazırla
        $data = array(
            'illegal_cari_id' => $cari_id,
            'illegal_tespit_gorsel' => $tespit_gorsel_files,
            'illegal_tespit_tutanak_gorsel' => $tutanak_gorsel_files,
            'illegal_tespit_aciklama' => $this->input->post('illegal_tespit_aciklama'),
            'illegal_tespit_tarih' => $tespit_tarih,
            'illegal_tespit_saat' => $tespit_saat,
            'illegal_tespit_personel_id' => $personel_id,
            'illegal_tespit_takim_id' => $takim_id,
            'illegal_tespit_rakip_takim_id' => $rakip_takim_id,
            'illegal_tespit_stokGrup_id' => $this->input->post('illegal_tespit_stokGrup_id') ?: null,
            'illegal_tespit_sezon_id' => $this->get_aktif_sezon_id(),
            'illegal_tespit_imzali' => $this->input->post('illegal_tespit_imzali') ? 1 : 0
        );
        
        if ($is_edit) {
            // Güncelleme işlemi
            $this->db->where('illegal_tespit_id', $illegal_tespit_id);
            if ($this->db->update('illegal_tespit', $data)) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'İllegal tespit başarıyla güncellendi!',
                    'id' => $illegal_tespit_id
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Güncelleme sırasında veritabanı hatası oluştu!']);
            }
        } else {
            // Yeni kayıt
            $data['illegal_tespit_olusturan'] = $u_id;
            $data['illegal_tespit_olusturmaTarihi'] = date('Y-m-d');
            
            if ($this->db->insert('illegal_tespit', $data)) {
                $insert_id = $this->db->insert_id();
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'İllegal tespit başarıyla kaydedildi!',
                    'id' => $insert_id
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası oluştu!']);
            }
        }
        die();
    }

    // Personeller listesi
    public function get_personeller() {
        header('Content-Type: application/json');
        
        try {
            // Grup ID filtresi (varsa)
            $grup_id = $this->input->post('grup_id') ?: $this->input->get('grup_id');
            
            // Kullanıcılar tablosundan sadece mevcut olan sütunları seç
            $this->db->select('kullanici_id, kullanici_ad, kullanici_soyad, grup_id');
            $this->db->from('kullanicilar');
            $this->db->where('kullanici_durum', 1); // Aktif kullanıcılar
            
            // Grup ID filtresi varsa ekle
            if (!empty($grup_id)) {
                $this->db->where('grup_id', $grup_id);
            }
            
            $this->db->order_by('kullanici_ad', 'ASC');
            $query = $this->db->get();
            
            if ($query) {
                $result = $query->result();
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                // DB hatası varsa manuel veri ekle
                $sample_users = [
                    (object)['kullanici_id' => 1, 'kullanici_ad' => 'Admin', 'kullanici_soyad' => 'User'],
                    (object)['kullanici_id' => 2, 'kullanici_ad' => 'Test', 'kullanici_soyad' => 'Personel'],
                    (object)['kullanici_id' => 3, 'kullanici_ad' => 'Demo', 'kullanici_soyad' => 'Kullanıcı']
                ];
                echo json_encode(['status' => 'success', 'data' => $sample_users]);
            }
        } catch (Exception $e) {
            // Hata durumunda sample data döndür
            $sample_users = [
                (object)['kullanici_id' => 1, 'kullanici_ad' => 'Admin', 'kullanici_soyad' => 'User'],
                (object)['kullanici_id' => 2, 'kullanici_ad' => 'Test', 'kullanici_soyad' => 'Personel'],
                (object)['kullanici_id' => 3, 'kullanici_ad' => 'Demo', 'kullanici_soyad' => 'Kullanıcı']
            ];
            echo json_encode(['status' => 'success', 'data' => $sample_users]);
        }
        die();
    }

    // İllegal Cariler listesi
    public function get_illegal_cariler() {
        header('Content-Type: application/json');
        
        $this->db->select('illegal_cari_id, illegal_cari_isletme_adi, illegal_cari_ad, illegal_cari_soyad');
        $this->db->from('illegal_cari');
        $this->db->where('illegal_cari_durum', 1);
        $this->db->order_by('illegal_cari_isletme_adi', 'ASC');
        $result = $this->db->get()->result();
        
        echo json_encode(['status' => 'success', 'data' => $result]);
        die();
    }

    // Stok Grupları listesi
    public function get_stok_gruplari() {
        header('Content-Type: application/json');
        
        try {
            // Stok grup sayısını kontrol et, eğer yoksa örnek veriler ekle
            $count = $this->db->count_all('stokgruplari');
            if ($count == 0) {
                $this->add_sample_stok_gruplari();
            }
            
            $this->db->select('stokGrup_id, stokGrup_ad');
            $this->db->from('stokgruplari');
            $this->db->order_by('stokGrup_ad', 'ASC');
            $result = $this->db->get()->result();
            
            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Stok grupları yüklenirken hata oluştu: ' . $e->getMessage()]);
        }
        die();
    }
    
    // Örnek stok grupları ekleme
    private function add_sample_stok_gruplari() {
        $sample_gruplar = [
            ['stokGrup_ad' => 'Dijital Platformlar', 'stokGrup_kodu' => 'DIG001'],
            ['stokGrup_ad' => 'Televizyon Yayını', 'stokGrup_kodu' => 'TV001'],
            ['stokGrup_ad' => 'Radyo Yayını', 'stokGrup_kodu' => 'RAD001'],
            ['stokGrup_ad' => 'İnternet Yayını', 'stokGrup_kodu' => 'INT001'],
            ['stokGrup_ad' => 'Mobil Uygulamalar', 'stokGrup_kodu' => 'MOB001']
        ];
        
        foreach ($sample_gruplar as $grup) {
            $grup['stokGrup_olusturan'] = 1;
            $grup['stokGrup_olusturanAnaHesap'] = 1;
            $grup['stokGrup_olusturmaTarihi'] = date('Y-m-d H:i:s');
            $this->db->insert('stokgruplari', $grup);
        }
    }



    // İllegal Listele sayfası

    public function illegal_listele() {
        // Yetki kontrolü - Modül 1620
        if (!grup_modul_yetkisi_var(1620)) {
            redirect(base_url());
            return;
        }
        
        $this->load->view('illegal/illegal_listele');
    }

    // İllegal listesi verileri - AJAX endpoint
    public function get_illegal_listesi() {
        header('Content-Type: application/json');
        
        try {
            // Oturum bilgisi
            $control2 = session("r", "login_info");
            $kullanici_id = $control2->kullanici_id;
            $kullanici_grup_id = $control2->grup_id;
            
            // Filtre parametreleri
            $cari_durum = $this->input->get('cari_durum');
            $imzali = $this->input->get('imzali');
            $ulke = $this->input->get('ulke');
            $il = $this->input->get('il');
            $tarih_baslangic = $this->input->get('tarih_baslangic');
            $tarih_bitis = $this->input->get('tarih_bitis');
            
            // Ülke yetkisi kontrolü (kgy_yetki = 5)
            // Admin grubu (kullanici_grubu id=1) hariç
            $ulke_kisitlamasi_var = false;
            $yetkili_ulkeler = array();
            
            if ($kullanici_grup_id != 1) {
                // Kullanıcının ülke görme yetkisi var mı kontrol et (kgy_yetki = 5, kgy_modul = 1620)
                $this->db->where('kgy_grupID', $kullanici_grup_id);
                $this->db->where('kgy_modul', 1620);
                $this->db->where('kgy_yetki', 5);
                $yetki_var = $this->db->count_all_results('kullanici_grubu_yetkisi') > 0;
                
                if ($yetki_var) {
                    // Kullanıcının atanmış ülkelerini al
                    $this->db->select('kullanici_ulke');
                    $this->db->where('kullanici_id', $kullanici_id);
                    $kullanici_query = $this->db->get('kullanicilar');
                    
                    if ($kullanici_query->num_rows() > 0) {
                        $kullanici_ulke = $kullanici_query->row()->kullanici_ulke;
                        
                        if (!empty($kullanici_ulke)) {
                            // Virgülle ayrılmış ülke ID'lerini diziye çevir
                            $yetkili_ulkeler = array_filter(array_map('trim', explode(',', $kullanici_ulke)));
                            $ulke_kisitlamasi_var = true;
                        }
                    }
                }
            }
            
            // Ana sorgu - illegal_tespit ve illegal_cari JOIN
            $this->db->select('
                it.illegal_tespit_id,
                it.illegal_tespit_tarih,
                it.illegal_tespit_saat,
                it.illegal_tespit_imzali,
                it.illegal_tespit_aciklama,
                ic.illegal_cari_id,
                ic.illegal_cari_isletme_adi,
                ic.illegal_cari_ad,
                ic.illegal_cari_soyad,
                ic.illegal_cari_firmaTelefon,
                ic.illegal_cari_durum,
                ic.illegal_cari_ulke,
                ic.illegal_cari_il,
                it1.illegal_tespit_takim_kisa_kod as takim_kisa_kod,
                it2.illegal_tespit_takim_kisa_kod as rakip_takim_kisa_kod,
                sg.stokGrup_ad as hizmet_adi,
                CONCAT(k.kullanici_ad, " ", k.kullanici_soyad) as personel_adi,
                ulk.ulke_adi,
                il.il as il_adi,
                ilc.ilce as ilce_adi
            ');
            
            $this->db->from('illegal_tespit it');
            $this->db->join('illegal_cari ic', 'it.illegal_cari_id = ic.illegal_cari_id', 'left');
            $this->db->join('illegal_tespit_takimlar it1', 'it.illegal_tespit_takim_id = it1.illegal_tespit_takim_id', 'left');
            $this->db->join('illegal_tespit_takimlar it2', 'it.illegal_tespit_rakip_takim_id = it2.illegal_tespit_takim_id', 'left');
            $this->db->join('stokgruplari sg', 'it.illegal_tespit_stokGrup_id = sg.stokGrup_id', 'left');
            $this->db->join('kullanicilar k', 'it.illegal_tespit_personel_id = k.kullanici_id', 'left');
            $this->db->join('ulkeler ulk', 'ic.illegal_cari_ulke = ulk.id', 'left');
            $this->db->join('iller il', 'ic.illegal_cari_il = il.id', 'left');
            $this->db->join('ilceler ilc', 'ic.illegal_cari_ilce = ilc.id', 'left');
            
            // Ülke kısıtlaması varsa filtrele
            if ($ulke_kisitlamasi_var && !empty($yetkili_ulkeler)) {
                $this->db->where_in('ic.illegal_cari_ulke', $yetkili_ulkeler);
            }
            
            // Filtreler
            if (!empty($cari_durum)) {
                $this->db->where('ic.illegal_cari_durum', $cari_durum);
            }
            
            if (!empty($imzali)) {
                $this->db->where('it.illegal_tespit_imzali', $imzali);
            }
            
            if (!empty($ulke)) {
                $this->db->where('ic.illegal_cari_ulke', $ulke);
            }
            
            if (!empty($il)) {
                $this->db->where('ic.illegal_cari_il', $il);
            }
            
            if (!empty($tarih_baslangic)) {
                $this->db->where('it.illegal_tespit_tarih >=', $tarih_baslangic);
            }
            
            if (!empty($tarih_bitis)) {
                $this->db->where('it.illegal_tespit_tarih <=', $tarih_bitis);
            }
            
            $this->db->order_by('it.illegal_tespit_id', 'DESC');
            $query = $this->db->get();
            $results = $query->result();
            
            // Veriyi formatla
            $data = array();
            foreach ($results as $row) {
                $cari_bilgi = '';
                if (!empty($row->illegal_cari_isletme_adi)) {
                    $cari_bilgi = $row->illegal_cari_isletme_adi;
                } else {
                    $cari_bilgi = trim($row->illegal_cari_ad . ' ' . $row->illegal_cari_soyad);
                }
                
                // İl/İlçe formatı
                $il_ilce = '';
                if (!empty($row->il_adi)) {
                    $il_ilce = $row->il_adi;
                    if (!empty($row->ilce_adi)) {
                        $il_ilce .= ' / ' . $row->ilce_adi;
                    }
                } else {
                    $il_ilce = '-';
                }
                
                // Takım bilgilerini birleştir
                $takimlar = '';
                if (!empty($row->takim_kisa_kod)) {
                    $takimlar = $row->takim_kisa_kod;
                    if (!empty($row->rakip_takim_kisa_kod)) {
                        $takimlar .= ' vs ' . $row->rakip_takim_kisa_kod;
                    }
                } else if (!empty($row->rakip_takim_kisa_kod)) {
                    $takimlar = $row->rakip_takim_kisa_kod;
                }
                
                // Tarih ve saat birleştir
                $tarih_saat = '';
                if (!empty($row->illegal_tespit_tarih)) {
                    $tarih_saat = date('d.m.Y', strtotime($row->illegal_tespit_tarih));
                    if (!empty($row->illegal_tespit_saat)) {
                        $tarih_saat .= ' ' . $row->illegal_tespit_saat;
                    }
                }
                
                // Yetkilendirme kontrol et
                // Admin grubu (kg_id=1) için kısıtlama yok
                $login_info = $this->session->userdata('login_info');
                $yetkiler = $this->session->userdata('yetkiler') ?? [];
                
                // Admin mi kontrol et (grup_id=1)
                $is_admin = false;
                if (isset($login_info->grup_id) && $login_info->grup_id == 1) {
                    $is_admin = true;
                } elseif (isset($login_info->kullanici_id)) {
                    // Veritabanından kontrol et
                    $user_check = $this->db->get_where('kullanicilar', ['kullanici_id' => $login_info->kullanici_id])->row();
                    if ($user_check && isset($user_check->grup_id) && $user_check->grup_id == 1) {
                        $is_admin = true;
                    }
                }
                
                // Sil butonu yetkisi: Admin VEYA yetki kodu 4
                $sil_yetkisi = $is_admin || (isset($yetkiler[1620]) && in_array(4, (array)$yetkiler[1620]));
                
                // Düzenle butonu yetkisi: Admin VEYA yetki kodu 3
                $duzenle_yetkisi = $is_admin || (isset($yetkiler[1620]) && in_array(3, (array)$yetkiler[1620]));
                
                // İşlem butonları
                $islemler_html = '<div class="btn-group">';
                if ($duzenle_yetkisi) {
                    $islemler_html .= '<a href="' . base_url('illegal/illegal-tespit-olustur?edit=' . $row->illegal_tespit_id) . '" class="btn btn-sm btn-primary" title="Düzenle">
                        <i class="fa fa-edit"></i>
                    </a>';
                }
                if ($sil_yetkisi) {
                    $islemler_html .= '<button onclick="deleteRecord(' . $row->illegal_tespit_id . ', \'tespit\')" class="btn btn-sm btn-danger" title="Sil">
                        <i class="fa fa-trash"></i>
                    </button>';
                }
                $islemler_html .= '</div>';
                
                $data[] = array(
                    'illegal_tespit_id' => $row->illegal_tespit_id,
                    'cari_bilgi' => $cari_bilgi ?: 'Belirtilmemiş',
                    'telefon' => $row->illegal_cari_firmaTelefon ?: '-',
                    'il_ilce' => $il_ilce,
                    'tarih_saat' => $tarih_saat ?: '-',
                    'takimlar' => $takimlar ?: '-',
                    'hizmet_adi' => $row->hizmet_adi ?: '-',
                    'personel_adi' => $row->personel_adi ?: '-',
                    'imzali_durum' => $row->illegal_tespit_imzali ? 
                        '<i class="fa fa-check-circle imzali-yes"></i> İmzalı' : 
                        '<i class="fa fa-times-circle imzali-no"></i> İmzasız',
                    'islemler' => $islemler_html
                );
            }
            
            echo json_encode(['data' => $data]);
            
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        die();
    }

    // İstatistikler - AJAX endpoint
    public function get_statistics() {
        header('Content-Type: application/json');
        
        try {
            // Toplam cari sayısı
            $this->db->where('illegal_cari_durum', 1);
            $toplam_cari = $this->db->count_all_results('illegal_cari');
            
            // Toplam tespit sayısı
            $toplam_tespit = $this->db->count_all_results('illegal_tespit');
            
            // İmzalı tespit sayısı
            $this->db->where('illegal_tespit_imzali', 1);
            $imzali_tespit = $this->db->count_all_results('illegal_tespit');
            
            // Bugün yapılan tespit sayısı
            $this->db->where('illegal_tespit_tarih', date('Y-m-d'));
            $bugun_tespit = $this->db->count_all_results('illegal_tespit');
            
            echo json_encode([
                'status' => 'success',
                'toplam_cari' => $toplam_cari,
                'toplam_tespit' => $toplam_tespit,
                'imzali_tespit' => $imzali_tespit,
                'bugun_tespit' => $bugun_tespit
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        die();
    }

    // Kayıt silme - AJAX endpoint
    public function delete_record() {
        header('Content-Type: application/json');
        
        $id = $this->input->post('id');
        $type = $this->input->post('type');
        
        if (empty($id) || empty($type)) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz parametreler']);
            die();
        }
        
        try {
            if ($type === 'tespit') {
                $this->db->where('illegal_tespit_id', $id);
                $result = $this->db->delete('illegal_tespit');
                $message = 'Tespit kaydı silindi';
            } elseif ($type === 'cari') {
                $this->db->where('illegal_cari_id', $id);
                $result = $this->db->delete('illegal_cari');
                $message = 'Cari kaydı silindi';
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz tür']);
                die();
            }
            
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => $message]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Silme işlemi başarısız']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        die();
    }

    // Filtre için ülke listesi (ulkeler tablosundan tüm ülkeler)
    public function get_filter_ulkeler() {
        header('Content-Type: application/json');
        
        try {
            // Oturum bilgisi
            $control2 = session("r", "login_info");
            $kullanici_id = $control2->kullanici_id;
            $kullanici_grup_id = $control2->grup_id;
            
            // Ülke yetkisi kontrolü (kgy_yetki = 5)
            // Admin grubu (kullanici_grubu id=1) değilse kontrol et
            if ($kullanici_grup_id != 1) {
                // Kullanıcının ülke görme yetkisi var mı kontrol et (kgy_yetki = 5, kgy_modul = 1620)
                $this->db->where('kgy_grupID', $kullanici_grup_id);
                $this->db->where('kgy_modul', 1620);
                $this->db->where('kgy_yetki', 5);
                $yetki_var = $this->db->count_all_results('kullanici_grubu_yetkisi') > 0;
                
                if ($yetki_var) {
                    // Kullanıcının atanmış ülkelerini al
                    $this->db->select('kullanici_ulke');
                    $this->db->where('kullanici_id', $kullanici_id);
                    $kullanici_query = $this->db->get('kullanicilar');
                    
                    if ($kullanici_query->num_rows() > 0) {
                        $kullanici_ulke = $kullanici_query->row()->kullanici_ulke;
                        
                        if (!empty($kullanici_ulke)) {
                            // Virgülle ayrılmış ülke ID'lerini diziye çevir
                            $yetkili_ulkeler = array_filter(array_map('trim', explode(',', $kullanici_ulke)));
                            
                            if (!empty($yetkili_ulkeler)) {
                                // Sadece yetkili ülkeleri göster
                                $this->db->select('id, ulke_adi');
                                $this->db->from('ulkeler');
                                $this->db->where_in('id', $yetkili_ulkeler);
                                $this->db->order_by('ulke_adi', 'ASC');
                                
                                $query = $this->db->get();
                                
                                if ($query) {
                                    $result = $query->result();
                                    echo json_encode(['status' => 'success', 'data' => $result]);
                                } else {
                                    echo json_encode(['status' => 'success', 'data' => []]);
                                }
                                die();
                            }
                        }
                    }
                }
            }
            
            // Admin veya kısıtlama yoksa tüm ülkeleri göster
            $this->db->select('id, ulke_adi');
            $this->db->from('ulkeler');
            $this->db->order_by('ulke_adi', 'ASC');
            $query = $this->db->get();
            
            if ($query) {
                $result = $query->result();
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                echo json_encode(['status' => 'success', 'data' => []]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        die();
    }

    // Filtre için ülke listesi (İşlemler sayfası için - yetki kontrolü ile)
    public function get_filter_ulkeler_islemler() {
        header('Content-Type: application/json');
        
        try {
            // Oturum bilgisi
            $control2 = session("r", "login_info");
            $kullanici_id = $control2->kullanici_id;
            $kullanici_grup_id = $control2->grup_id;
            
            // Ülke yetkisi kontrolü (kgy_yetki = 5)
            // Admin grubu (kullanici_grubu id=1) değilse kontrol et
            if ($kullanici_grup_id != 1) {
                // Kullanıcının ülke görme yetkisi var mı kontrol et (kgy_yetki = 5, kgy_modul = 1625)
                $this->db->where('kgy_grupID', $kullanici_grup_id);
                $this->db->where('kgy_modul', 1625);
                $this->db->where('kgy_yetki', 5);
                $yetki_var = $this->db->count_all_results('kullanici_grubu_yetkisi') > 0;
                
                if ($yetki_var) {
                    // Kullanıcının atanmış ülkelerini al
                    $this->db->select('kullanici_ulke');
                    $this->db->where('kullanici_id', $kullanici_id);
                    $kullanici_query = $this->db->get('kullanicilar');
                    
                    if ($kullanici_query->num_rows() > 0) {
                        $kullanici_ulke = $kullanici_query->row()->kullanici_ulke;
                        
                        if (!empty($kullanici_ulke)) {
                            // Virgülle ayrılmış ülke ID'lerini diziye çevir
                            $yetkili_ulkeler = array_filter(array_map('trim', explode(',', $kullanici_ulke)));
                            
                            if (!empty($yetkili_ulkeler)) {
                                // Sadece yetkili ülkeleri göster
                                $this->db->select('id, ulke_adi');
                                $this->db->from('ulkeler');
                                $this->db->where_in('id', $yetkili_ulkeler);
                                $this->db->order_by('ulke_adi', 'ASC');
                                
                                $query = $this->db->get();
                                
                                if ($query) {
                                    $result = $query->result();
                                    echo json_encode(['status' => 'success', 'data' => $result]);
                                } else {
                                    echo json_encode(['status' => 'success', 'data' => []]);
                                }
                                die();
                            }
                        }
                    }
                }
            }
            
            // Admin veya kısıtlama yoksa tüm ülkeleri göster
            $this->db->select('id, ulke_adi');
            $this->db->from('ulkeler');
            $this->db->order_by('ulke_adi', 'ASC');
            $query = $this->db->get();
            
            if ($query) {
                $result = $query->result();
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                echo json_encode(['status' => 'success', 'data' => []]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        die();
    }

    // Filtre için il listesi (illegal_cari tablosundaki kayıtlardan)
    public function get_filter_iller() {
        header('Content-Type: application/json');
        
        try {
            $ulke_id = $this->input->get('ulke_id');
            
            $this->db->select('il.id, il.il');
            $this->db->from('illegal_cari ic');
            $this->db->join('iller il', 'ic.illegal_cari_il = il.id', 'inner');
            $this->db->where('ic.illegal_cari_il IS NOT NULL');
            $this->db->where('ic.illegal_cari_durum', 1);
            
            // Ülke filtresi varsa
            if (!empty($ulke_id)) {
                $this->db->where('ic.illegal_cari_ulke', $ulke_id);
            }
            
            $this->db->group_by('il.id');
            $this->db->order_by('il.il', 'ASC');
            
            $query = $this->db->get();
            
            if ($query) {
                $result = $query->result();
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                echo json_encode(['status' => 'success', 'data' => []]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        die();
    }



    // İllegal Ayarları sayfası

    public function illegal_ayar() {

        $this->load->view('illegal/illegal_ayar');

    }



    // --- SEZONLAR ---

    public function sezonlar_listele() {
        $this->load->database();

        $sezonlar = $this->db->get('sezonlar')->result();

        echo json_encode(['status'=>'success','data'=>$sezonlar]);

    }

    public function sezon_detay() {
        $this->load->database();
        
        $id = $this->input->get('id');
        
        if(!$id) {
            echo json_encode(['status'=>'error','msg'=>'ID zorunlu']);
            return;
        }
        
        $sezon = $this->db->get_where('sezonlar', ['sezon_id'=>$id])->row();
        
        if(!$sezon) {
            echo json_encode(['status'=>'error','msg'=>'Sezon bulunamadı']);
            return;
        }
        
        echo json_encode(['status'=>'success','data'=>$sezon]);
    }

    public function sezon_ekle() {
        $this->load->database();

        $ad = $this->input->post('sezon_adi');
        $durum = $this->input->post('sezon_durum');

        if(!$ad) { 
            echo json_encode(['status'=>'error','msg'=>'Sezon adı zorunlu']); 
            return; 
        }
        
        if(strlen($ad) > 10) {
            echo json_encode(['status'=>'error','msg'=>'Sezon adı maksimum 10 karakter olabilir']);
            return;
        }
        
        // Aynı sezon adı var mı kontrol et
        $this->db->where('sezon_adi', $ad);
        $existing = $this->db->get('sezonlar')->row();
        if($existing) {
            echo json_encode(['status'=>'error','msg'=>'Bu sezon adı zaten mevcut']);
            return;
        }

        $data = [
            'sezon_adi' => $ad,
            'sezon_durum' => $durum ? 1 : 0
        ];

        $this->db->insert('sezonlar', $data);

        echo json_encode(['status'=>'success']);

    }

    public function sezon_guncelle() {
        $this->load->database();

        $id = $this->input->post('sezon_id');
        $ad = $this->input->post('sezon_adi');
        $durum = $this->input->post('sezon_durum');

        if(!$id || !$ad) { 
            echo json_encode(['status'=>'error','msg'=>'Eksik parametre']); 
            return; 
        }
        
        if(strlen($ad) > 10) {
            echo json_encode(['status'=>'error','msg'=>'Sezon adı maksimum 10 karakter olabilir']);
            return;
        }
        
        // Aynı sezon adı var mı kontrol et (kendisi hariç)
        $this->db->where('sezon_adi', $ad);
        $this->db->where('sezon_id !=', $id);
        $existing = $this->db->get('sezonlar')->row();
        if($existing) {
            echo json_encode(['status'=>'error','msg'=>'Bu sezon adı zaten mevcut']);
            return;
        }

        $data = [
            'sezon_adi' => $ad,
            'sezon_durum' => $durum ? 1 : 0
        ];

        $this->db->update('sezonlar', $data, ['sezon_id'=>$id]);

        echo json_encode(['status'=>'success']);

    }

    public function sezon_sil() {
        $this->load->database();

        $id = $this->input->post('sezon_id');

        if(!$id) { echo json_encode(['status'=>'error','msg'=>'ID zorunlu']); return; }

        $this->db->delete('sezonlar', ['sezon_id'=>$id]);

        echo json_encode(['status'=>'success']);

    }

    // TAKIM İŞLEMLERİ
    public function takimlar_listele() {
        header('Content-Type: application/json');
        $this->load->database();
        
        try {
            // Takım sayısını kontrol et, eğer yoksa örnek veriler ekle
            $count = $this->db->count_all('illegal_tespit_takimlar');
            if ($count == 0) {
                $this->add_sample_takimlar();
            }
            
            // Takımları getir
            $this->db->select('illegal_tespit_takim_id, illegal_tespit_takim_adi, illegal_tespit_takim_kisa_kod, illegal_tespit_takim_logo');
            $this->db->from('illegal_tespit_takimlar');
            $this->db->order_by('illegal_tespit_takim_adi', 'ASC');
            $takimlar = $this->db->get()->result_array();
            
            echo json_encode(['status'=>'success','data'=>$takimlar]);
        } catch (Exception $e) {
            echo json_encode(['status'=>'error','message'=>'Takımlar yüklenirken hata oluştu: ' . $e->getMessage()]);
        }
        die();
    }
    
    // Örnek takımlar ekleme
    private function add_sample_takimlar() {
        $sample_takimlar = [
            ['illegal_tespit_takim_adi' => 'Denetim Takımı 1', 'illegal_tespit_takim_kisa_kod' => 'DT1'],
            ['illegal_tespit_takim_adi' => 'Denetim Takımı 2', 'illegal_tespit_takim_kisa_kod' => 'DT2'],
            ['illegal_tespit_takim_adi' => 'Saha Takımı', 'illegal_tespit_takim_kisa_kod' => 'ST'],
            ['illegal_tespit_takim_adi' => 'Teknik Takım', 'illegal_tespit_takim_kisa_kod' => 'TT'],
            ['illegal_tespit_takim_adi' => 'Kontrol Takımı', 'illegal_tespit_takim_kisa_kod' => 'KT']
        ];
        
        foreach ($sample_takimlar as $takim) {
            $takim['illegal_tespit_takim_olusturan'] = 1;
            $takim['illegal_tespit_takim_olusturmaTarihi'] = date('Y-m-d');
            $this->db->insert('illegal_tespit_takimlar', $takim);
        }
    }

    public function takim_ekle() {
        $this->load->database();
        
        $ad = $this->input->post('takim_adi');
        $kisaKod = $this->input->post('takim_kisa_kod');
        
        if(!$ad) { 
            echo json_encode(['status'=>'error','msg'=>'Takım adı zorunlu']); 
            return; 
        }
        
        if(strlen($ad) > 255) {
            echo json_encode(['status'=>'error','msg'=>'Takım adı maksimum 255 karakter olabilir']);
            return;
        }
        
        if($kisaKod && strlen($kisaKod) > 20) {
            echo json_encode(['status'=>'error','msg'=>'Kısa kod maksimum 20 karakter olabilir']);
            return;
        }
        
        // Aynı takım adı var mı kontrol et
        $this->db->where('illegal_tespit_takim_adi', $ad);
        $existing = $this->db->get('illegal_tespit_takimlar')->row();
        if($existing) {
            echo json_encode(['status'=>'error','msg'=>'Bu takım adı zaten mevcut']);
            return;
        }
        
        $data = [
            'illegal_tespit_takim_adi' => $ad,
            'illegal_tespit_takim_kisa_kod' => $kisaKod,
            'illegal_tespit_takim_olusturan' => $this->session->userdata('kullanici_id') ?: 187,
            'illegal_tespit_takim_olusturmaTarihi' => date('Y-m-d')
        ];
        
        // Logo işlemleri
        $mevcutLogo = $this->input->post('mevcut_logo');
        if($mevcutLogo) {
            // Mevcut logodan seçim yapıldı
            $data['illegal_tespit_takim_logo'] = $mevcutLogo;
            $data['illegal_tespit_takim_logo_yuklenme_tarihi'] = date('Y-m-d H:i:s');
        } elseif(!empty($_FILES['takim_logo']['name'])) {
            // Yeni logo yükleme
            $config['upload_path'] = './uploads/team-logos/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = 2048; // 2MB
            $config['file_name'] = 'team_' . time() . '_' . $_FILES['takim_logo']['name'];
            
            if(!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }
            
            $this->load->library('upload', $config);
            
            if($this->upload->do_upload('takim_logo')) {
                $uploadData = $this->upload->data();
                $data['illegal_tespit_takim_logo'] = 'uploads/team-logos/' . $uploadData['file_name'];
                $data['illegal_tespit_takim_logo_yuklenme_tarihi'] = date('Y-m-d H:i:s');
            } else {
                echo json_encode(['status'=>'error','msg'=>'Logo yüklenemedi: ' . $this->upload->display_errors()]);
                return;
            }
        }
        
        $this->db->insert('illegal_tespit_takimlar', $data);
        echo json_encode(['status'=>'success']);
    }

    public function takim_guncelle() {
        $this->load->database();
        
        $id = $this->input->post('takim_id');
        $ad = $this->input->post('takim_adi');
        $kisaKod = $this->input->post('takim_kisa_kod');
        
        if(!$id || !$ad) { 
            echo json_encode(['status'=>'error','msg'=>'Eksik parametre']); 
            return; 
        }
        
        if(strlen($ad) > 255) {
            echo json_encode(['status'=>'error','msg'=>'Takım adı maksimum 255 karakter olabilir']);
            return;
        }
        
        if($kisaKod && strlen($kisaKod) > 20) {
            echo json_encode(['status'=>'error','msg'=>'Kısa kod maksimum 20 karakter olabilir']);
            return;
        }
        
        // Aynı takım adı var mı kontrol et (kendisi hariç)
        $this->db->where('illegal_tespit_takim_adi', $ad);
        $this->db->where('illegal_tespit_takim_id !=', $id);
        $existing = $this->db->get('illegal_tespit_takimlar')->row();
        if($existing) {
            echo json_encode(['status'=>'error','msg'=>'Bu takım adı zaten mevcut']);
            return;
        }
        
        $data = [
            'illegal_tespit_takim_adi' => $ad,
            'illegal_tespit_takim_kisa_kod' => $kisaKod
        ];
        
        // Logo işlemleri
        $mevcutLogo = $this->input->post('mevcut_logo');
        if($mevcutLogo) {
            // Mevcut logodan seçim yapıldı
            $data['illegal_tespit_takim_logo'] = $mevcutLogo;
            $data['illegal_tespit_takim_logo_yuklenme_tarihi'] = date('Y-m-d H:i:s');
        } elseif(!empty($_FILES['takim_logo']['name'])) {
            // Yeni logo yükleme
            $config['upload_path'] = './uploads/team-logos/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = 2048; // 2MB
            $config['file_name'] = 'team_' . time() . '_' . $_FILES['takim_logo']['name'];
            
            if(!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }
            
            $this->load->library('upload', $config);
            
            if($this->upload->do_upload('takim_logo')) {
                $uploadData = $this->upload->data();
                $data['illegal_tespit_takim_logo'] = 'uploads/team-logos/' . $uploadData['file_name'];
                $data['illegal_tespit_takim_logo_yuklenme_tarihi'] = date('Y-m-d H:i:s');
            } else {
                echo json_encode(['status'=>'error','msg'=>'Logo yüklenemedi: ' . $this->upload->display_errors()]);
                return;
            }
        }
        
        $this->db->update('illegal_tespit_takimlar', $data, ['illegal_tespit_takim_id'=>$id]);
        echo json_encode(['status'=>'success']);
    }

    public function takim_sil() {
        $this->load->database();
        
        $id = $this->input->post('takim_id');
        
        if(!$id) { 
            echo json_encode(['status'=>'error','msg'=>'ID zorunlu']); 
            return; 
        }
        
        $this->db->delete('illegal_tespit_takimlar', ['illegal_tespit_takim_id'=>$id]);
        echo json_encode(['status'=>'success']);
    }
    
    public function takim_detay() {
        header('Content-Type: application/json');
        $this->load->database();
        
        $id = $this->input->get('id');
        
        if(!$id) { 
            echo json_encode(['status'=>'error','msg'=>'ID zorunlu']); 
            return; 
        }
        
        $this->db->where('illegal_tespit_takim_id', $id);
        $takim = $this->db->get('illegal_tespit_takimlar')->row();
        
        if($takim) {
            echo json_encode(['status'=>'success','data'=>$takim]);
        } else {
            echo json_encode(['status'=>'error','msg'=>'Takım bulunamadı']);
        }
    }
    
    public function get_team_logos() {
        header('Content-Type: application/json');
        
        $logoPath = FCPATH . 'assets/img/team-logos/';
        $logos = [];
        
        if(is_dir($logoPath)) {
            $files = scandir($logoPath);
            foreach($files as $file) {
                if($file != '.' && $file != '..' && preg_match('/\.(png|jpg|jpeg|gif)$/i', $file)) {
                    $logos[] = [
                        'file' => $file,
                        'path' => 'assets/img/team-logos/' . $file,
                        'name' => ucwords(str_replace(['-', '_', '.png', '.jpg', '.jpeg', '.gif'], [' ', ' ', '', '', '', ''], $file))
                    ];
                }
            }
        }
        
        // Alfabetik sırala
        usort($logos, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        echo json_encode(['status'=>'success','data'=>$logos]);
    }

    // --- STATÜLER ---
    
    public function statuler_listele() {
        $this->load->database();
        
        $this->db->order_by('illegal_statu_sira_no', 'ASC');
        $this->db->order_by('illegal_statu_id', 'ASC');
        $statuler = $this->db->get('illegal_statu')->result();
        
        echo json_encode(['status'=>'success','data'=>$statuler]);
    }
    
    // Statü listesi (select için)
    public function get_statuler_for_select() {
        header('Content-Type: application/json');
        
        try {
            $this->db->select('illegal_statu_id, illegal_statu_adi, illegal_statu_renk');
            $this->db->from('illegal_statu');
            $this->db->where('illegal_statu_durum', 1);
            $this->db->order_by('illegal_statu_sira_no', 'ASC');
            $this->db->order_by('illegal_statu_id', 'ASC');
            
            $query = $this->db->get();
            
            if ($query) {
                $result = $query->result();
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Statü bulunamadı']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
    }
    
    public function statu_detay() {
        $this->load->database();
        
        $id = $this->input->get('id');
        
        if(!$id) {
            echo json_encode(['status'=>'error','msg'=>'ID zorunlu']);
            return;
        }
        
        $statu = $this->db->get_where('illegal_statu', ['illegal_statu_id'=>$id])->row();
        
        if(!$statu) {
            echo json_encode(['status'=>'error','msg'=>'Statü bulunamadı']);
            return;
        }
        
        echo json_encode(['status'=>'success','data'=>$statu]);
    }
    
    public function statu_ekle() {
        $this->load->database();
        
        $adi = $this->input->post('statu_adi');
        $aciklama = $this->input->post('statu_aciklama');
        $renk = $this->input->post('statu_renk');
        $sira_no = $this->input->post('statu_sira_no');
        $durum = $this->input->post('statu_durum');
        
        if(!$adi) {
            echo json_encode(['status'=>'error','msg'=>'Statü adı zorunlu']);
            return;
        }
        
        if(strlen($adi) > 100) {
            echo json_encode(['status'=>'error','msg'=>'Statü adı maksimum 100 karakter olabilir']);
            return;
        }
        
        // Aynı statü adı var mı kontrol et
        $this->db->where('illegal_statu_adi', $adi);
        $existing = $this->db->get('illegal_statu')->row();
        if($existing) {
            echo json_encode(['status'=>'error','msg'=>'Bu statü adı zaten mevcut']);
            return;
        }
        
        $data = [
            'illegal_statu_adi' => $adi,
            'illegal_statu_aciklama' => $aciklama,
            'illegal_statu_renk' => $renk ?: '#007BFF',
            'illegal_statu_sira_no' => $sira_no ?: null,
            'illegal_statu_durum' => $durum ? 1 : 0,
            'illegal_statu_olusturan' => $this->session->userdata('kullanici_id') ?: 187,
            'illegal_statu_olusturmaTarihi' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('illegal_statu', $data);
        echo json_encode(['status'=>'success']);
    }
    
    public function statu_guncelle() {
        $this->load->database();
        
        $id = $this->input->post('statu_id');
        $adi = $this->input->post('statu_adi');
        $aciklama = $this->input->post('statu_aciklama');
        $renk = $this->input->post('statu_renk');
        $sira_no = $this->input->post('statu_sira_no');
        $durum = $this->input->post('statu_durum');
        
        if(!$id || !$adi) {
            echo json_encode(['status'=>'error','msg'=>'Eksik parametre']);
            return;
        }
        
        if(strlen($adi) > 100) {
            echo json_encode(['status'=>'error','msg'=>'Statü adı maksimum 100 karakter olabilir']);
            return;
        }
        
        // Aynı statü adı var mı kontrol et (kendisi hariç)
        $this->db->where('illegal_statu_adi', $adi);
        $this->db->where('illegal_statu_id !=', $id);
        $existing = $this->db->get('illegal_statu')->row();
        if($existing) {
            echo json_encode(['status'=>'error','msg'=>'Bu statü adı zaten mevcut']);
            return;
        }
        
        $data = [
            'illegal_statu_adi' => $adi,
            'illegal_statu_aciklama' => $aciklama,
            'illegal_statu_renk' => $renk ?: '#007BFF',
            'illegal_statu_sira_no' => $sira_no ?: null,
            'illegal_statu_durum' => $durum ? 1 : 0,
            'illegal_statu_guncelleyen' => $this->session->userdata('kullanici_id') ?: 187,
            'illegal_statu_guncellemeTarihi' => date('Y-m-d H:i:s')
        ];
        
        $this->db->update('illegal_statu', $data, ['illegal_statu_id'=>$id]);
        echo json_encode(['status'=>'success']);
    }
    
    public function statu_sil() {
        $this->load->database();
        
        $id = $this->input->post('statu_id');
        
        if(!$id) {
            echo json_encode(['status'=>'error','msg'=>'ID zorunlu']);
            return;
        }
        
        // Bu statüyü kullanan kayıt var mı kontrol et
        $this->db->where('illegal_tespit_islemler_statu', $id);
        $usage_check = $this->db->get('illegal_tespit_islemler')->num_rows();
        
        if($usage_check > 0) {
            echo json_encode(['status'=>'error','msg'=>'Bu statü kullanımda olduğu için silinemez']);
            return;
        }
        
        $this->db->delete('illegal_statu', ['illegal_statu_id'=>$id]);
        echo json_encode(['status'=>'success']);
    }

    // --- CARI GRUPLAR ---

    public function cari_gruplar_listele() {

        $this->load->database();

        $gruplar = $this->db->get('cariGruplari')->result();

        echo json_encode(['status'=>'success','data'=>$gruplar]);

    }

    public function cari_grup_ekle() {

        $this->load->database();

        $ad = $this->input->post('cari_grup');

        if(!$ad) { echo json_encode(['status'=>'error','msg'=>'Cari grup adı zorunlu']); return; }

        $this->db->insert('cariGruplari', ['cariGrup_ad'=>$ad]);

        echo json_encode(['status'=>'success']);

    }

    public function cari_grup_guncelle() {

        $this->load->database();

        $id = $this->input->post('cari_grup_id');

        $ad = $this->input->post('cari_grup');

        if(!$id || !$ad) { echo json_encode(['status'=>'error','msg'=>'Eksik parametre']); return; }

        $this->db->update('cariGruplari', ['cariGrup_ad'=>$ad], ['cariGrup_id'=>$id]);

        echo json_encode(['status'=>'success']);

    }

    public function cari_grup_sil() {

        $this->load->database();

        $id = $this->input->post('cari_grup_id');

        if(!$id) { echo json_encode(['status'=>'error','msg'=>'ID zorunlu']); return; }

        $this->db->delete('cariGruplari', ['cariGrup_id'=>$id]);

        echo json_encode(['status'=>'success']);

    }



    // --- SEKTÖRLER ---

    public function sektorler_listele() {

        $this->load->database();

        $sektorler = $this->db->get('sektorler')->result();

        echo json_encode(['status'=>'success','data'=>$sektorler]);

    }

    public function sektor_ekle() {

        $this->load->database();

        $ad = $this->input->post('sektor_adi');

        if(!$ad) { echo json_encode(['status'=>'error','msg'=>'Sektör adı zorunlu']); return; }

        $this->db->insert('sektorler', ['sektor_adi'=>$ad]);

        echo json_encode(['status'=>'success']);

    }

    public function sektor_guncelle() {

        $this->load->database();

        $id = $this->input->post('sektor_id');

        $ad = $this->input->post('sektor_adi');

        if(!$id || !$ad) { echo json_encode(['status'=>'error','msg'=>'Eksik parametre']); return; }

        $this->db->update('sektorler', ['sektor_adi'=>$ad], ['sektor_id'=>$id]);

        echo json_encode(['status'=>'success']);

    }

    public function sektor_sil() {

        $this->load->database();

        $id = $this->input->post('sektor_id');

        if(!$id) { echo json_encode(['status'=>'error','msg'=>'ID zorunlu']); return; }

        $this->db->delete('sektorler', ['sektor_id'=>$id]);

        echo json_encode(['status'=>'success']);

    }



    // --- MAHALLELER ---

    // AJAX: Seçilen il ve ilçe id'sine göre potansiyel_cari tablosundan mahalleleri döndürür

    public function get_potansiyel_mahalleler() {

        $il_id = $this->input->post('il_id');

        $ilce_id = $this->input->post('ilce_id');

        if (!$il_id || !$ilce_id) {

            echo json_encode(['status' => 'error', 'msg' => 'İl ve ilçe zorunlu']);

            return;

        }

        $this->db->select('potansiyel_mahalle');

        $this->db->from('potansiyel_cari');

        $this->db->where('potansiyel_il_id', $il_id);

        $this->db->where('potansiyel_ilce_id', $ilce_id);

        $this->db->where('potansiyel_mahalle IS NOT NULL');

        $this->db->where('potansiyel_mahalle !=', '');

        $this->db->distinct();

        $query = $this->db->get();

        $mahalleler = array();

        foreach ($query->result() as $row) {

            $mahalleler[] = $row->potansiyel_mahalle;

        }

        echo json_encode(['status' => 'success', 'data' => $mahalleler]);

    }



    // AJAX: Telefon search (potansiyel_cari_firmaTelefon)

    public function telefon_search() {

        $q = $this->input->post('q');

        $this->db->select('potansiyel_cari_firmaTelefon');

        $this->db->from('potansiyel_cari');

        if ($q) {

            $this->db->like('potansiyel_cari_firmaTelefon', $q);

        }

        $this->db->where('potansiyel_cari_firmaTelefon IS NOT NULL');

        $this->db->where('potansiyel_cari_firmaTelefon !=', '');

        $this->db->distinct();

        $query = $this->db->get();

        $telefonlar = array();

        foreach ($query->result() as $row) {

            $telefonlar[] = $row->potansiyel_cari_firmaTelefon;

        }

        echo json_encode($telefonlar);

    }



    // Autocomplete for Potansiyel Cari

    public function potansiyel_cari_autocomplete() {

        header('Content-Type: application/json');

        $term = $this->input->get('term');

        $returnData = array();

        

        // Debug log

        log_message('debug', 'Autocomplete called with term: ' . $term);

        

        if (strlen($term) >= 4) {

            $this->db->select('id, potansiyel_cari_ad, potansiyel_cari_firmaTelefon');

            $this->db->from('potansiyel_cari');

            $this->db->group_start();

            $this->db->like('potansiyel_cari_ad', $term);

            $this->db->or_like('potansiyel_cari_firmaTelefon', $term);

            $this->db->group_end();

            $this->db->limit(20);

            

            $query = $this->db->get();

            $results = $query->result();

            

            foreach ($results as $row) {

                $display_text = $row->potansiyel_cari_ad;

                if ($row->potansiyel_cari_firmaTelefon) {

                    $display_text .= ' (' . $row->potansiyel_cari_firmaTelefon . ')';

                }

                

                $returnData[] = [

                    'id' => $row->id,

                    'value' => $display_text,

                    'label' => $display_text,

                    'cari_ad' => $row->potansiyel_cari_ad,

                    'telefon' => $row->potansiyel_cari_firmaTelefon

                ];

            }

        }

        

        log_message('debug', 'Returning data: ' . json_encode($returnData));

        echo json_encode($returnData);

        die;

    }



    // Test endpoint for autocomplete

    public function test_autocomplete() {

        header('Content-Type: application/json');

        

        // Test query

        $this->db->select('id, potansiyel_cari_ad, potansiyel_cari_soyad, potansiyel_cari_firmaTelefon');

        $this->db->from('potansiyel_cari');

        $this->db->limit(5);

        $query = $this->db->get();

        

        $test_data = [];

        foreach ($query->result() as $row) {

            $test_data[] = [

                'id' => $row->id,

                'ad' => $row->potansiyel_cari_ad,

                'soyad' => $row->potansiyel_cari_soyad,

                'telefon' => $row->potansiyel_cari_firmaTelefon

            ];

        }

        

        echo json_encode([

            'status' => 'success',

            'message' => 'Test endpoint working',

            'sample_data' => $test_data,

            'base_url' => base_url(),

            'current_url' => current_url()

        ]);

        die;

    }



    // Potansiyel Satış Ekle

    public function potansiyel_satis_ekle() {

        $control2 = session("r", "login_info");

        $u_id = $control2->kullanici_id ?? 1;

        

        $data = [

            'potansiyel_cari_id' => $this->input->post('potansiyel_cari_id'),

            'durum_id' => $this->input->post('durum_id'),

            'fiyat1' => $this->input->post('fiyat1'),

            'fiyat2' => $this->input->post('fiyat2'),

            'fiyat3' => $this->input->post('fiyat3'),

            'aciklama' => $this->input->post('aciklama'),

            'islemi_yapan' => $u_id,

            'islem_tarihi' => date('Y-m-d H:i:s')

        ];

        

        if ($this->db->insert('potansiyel_satis', $data)) {

            $this->session->set_flashdata('success', 'Potansiyel satış başarıyla eklendi.');

        } else {

            $this->session->set_flashdata('error', 'Potansiyel satış eklenirken hata oluştu.');

        }

        

        redirect('illegal/illegal_listele');

    }



    // Test endpoint for potansiyel_cari table structure

    public function test_potansiyel_cari_structure() {

        $query = $this->db->query('DESCRIBE potansiyel_cari');

        $fields = $query->result_array();

        echo json_encode($fields);

        die;

    }

    // Test endpoints - remove after testing
    public function test_endpoints() {
        echo "<h3>Testing Endpoints</h3>";
        
        echo "<h4>1. Test Countries (get_ulkeler)</h4>";
        echo "<a href='" . base_url('home/get_ulkeler') . "' target='_blank'>Test get_ulkeler</a><br>";
        
        echo "<h4>2. Test Cities (get_iller)</h4>";
        echo "<a href='" . base_url('home/get_iller') . "' target='_blank'>Test get_iller (all)</a><br>";
        
        echo "<h4>3. Test Districts (get_ilceler)</h4>";
        echo "<a href='" . base_url('home/get_ilceler') . "' target='_blank'>Test get_ilceler</a><br>";
        
        echo "<h4>4. Test Form</h4>";
        echo "<a href='" . base_url('illegal/illegal-tespit-olustur') . "' target='_blank'>Go to Form</a><br>";
    }

    // Aktif sezon ID'sini getir
    private function get_aktif_sezon_id() {
        try {
            $this->db->select('sezon_id');
            $this->db->from('sezonlar');
            $this->db->where('sezon_durum', 1);
            $this->db->limit(1);
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                $result = $query->row();
                return $result->sezon_id;
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    // Ülke listesi getir
    public function get_ulkeler() {
        header('Content-Type: application/json');
        
        try {
            $this->db->select('id, ulke_adi');
            $this->db->from('ulkeler');
            $this->db->order_by('ulke_adi', 'ASC');
            $query = $this->db->get();
            
            if ($query) {
                $ulkeler = $query->result_array();
                echo json_encode($ulkeler);
            } else {
                echo json_encode([]);
            }
        } catch (Exception $e) {
            echo json_encode([]);
        }
    }

    // İşlemler sayfası
    public function illegal_islemler() {
        // Yetki kontrolü - Modül 1625
        if (!grup_modul_yetkisi_var(1625)) {
            redirect(base_url());
            return;
        }
        
        // Statü parametresini al
        $statu_id = $this->input->get('statu');
        
        // Eğer statü parametresi varsa, statü bilgisini çek
        $statu_adi = '';
        if ($statu_id) {
            $statu = $this->db->get_where('illegal_statu', ['illegal_statu_id' => $statu_id])->row();
            if ($statu) {
                $statu_adi = $statu->illegal_statu_adi;
            }
        }
        
        $data = [
            'statu_id' => $statu_id,
            'statu_adi' => $statu_adi
        ];
        
        $this->load->view('illegal/illegal_islemler', $data);
    }

    // İşlemler listesi getir
    public function get_islemler() {
        header('Content-Type: application/json');
        
        try {
            // Oturum bilgisi
            $control2 = session("r", "login_info");
            $kullanici_id = $control2->kullanici_id;
            $kullanici_grup_id = $control2->grup_id;
            
            // Statü filtresi kontrolü
            $statu_id = $this->input->get('statu');
            
            // Ülke yetkisi kontrolü (kgy_yetki = 5)
            // Admin grubu (kullanici_grubu id=1) hariç
            $ulke_kisitlamasi_var = false;
            $yetkili_ulkeler = array();
            
            if ($kullanici_grup_id != 1) {
                // Kullanıcının ülke görme yetkisi var mı kontrol et (kgy_yetki = 5, kgy_modul = 1625)
                $this->db->where('kgy_grupID', $kullanici_grup_id);
                $this->db->where('kgy_modul', 1625);
                $this->db->where('kgy_yetki', 5);
                $yetki_var = $this->db->count_all_results('kullanici_grubu_yetkisi') > 0;
                
                if ($yetki_var) {
                    // Kullanıcının atanmış ülkelerini al
                    $this->db->select('kullanici_ulke');
                    $this->db->where('kullanici_id', $kullanici_id);
                    $kullanici_query = $this->db->get('kullanicilar');
                    
                    if ($kullanici_query->num_rows() > 0) {
                        $kullanici_ulke = $kullanici_query->row()->kullanici_ulke;
                        
                        if (!empty($kullanici_ulke)) {
                            // Virgülle ayrılmış ülke ID'lerini diziye çevir
                            $yetkili_ulkeler = array_filter(array_map('trim', explode(',', $kullanici_ulke)));
                            $ulke_kisitlamasi_var = true;
                        }
                    }
                }
            }
            
            $this->db->select('
                iti.*,
                it.illegal_tespit_tarih,
                ic.illegal_cari_isletme_adi as tespit_bilgi,
                ic.illegal_cari_ulke,
                u.kullanici_ad,
                u.kullanici_soyad,
                CONCAT(u.kullanici_ad, " ", COALESCE(u.kullanici_soyad, "")) as personel_ad
            ');
            $this->db->from('illegal_tespit_islemler iti');
            $this->db->join('illegal_tespit it', 'iti.illegal_tespit_id = it.illegal_tespit_id', 'left');
            $this->db->join('illegal_cari ic', 'it.illegal_cari_id = ic.illegal_cari_id', 'left');
            $this->db->join('kullanicilar u', 'iti.illegal_tespit_islemler_olusturan = u.kullanici_id', 'left');
            
            // Ülke kısıtlaması varsa filtrele
            if ($ulke_kisitlamasi_var && !empty($yetkili_ulkeler)) {
                $this->db->where_in('ic.illegal_cari_ulke', $yetkili_ulkeler);
            }
            
            // Statü filtresi uygula
            if ($statu_id) {
                $this->db->where('iti.illegal_tespit_islemler_statu', $statu_id);
            }
            
            $this->db->order_by('iti.illegal_tespit_islemler_id', 'DESC');
            
            $query = $this->db->get();
            
            if ($query) {
                $result = $query->result();
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Veri bulunamadı']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
    }

    // Tespit listesi (select için)
    public function get_tespitler_for_select() {
        header('Content-Type: application/json');
        
        try {
            // illegal_cari sütunlarını kontrol et
            $cari_columns = $this->db->list_fields('illegal_cari');
            
            // illegal_cari_isletme_adi yoksa alternatif sütun kullan
            $isletme_adi_column = 'ic.illegal_cari_isletme_adi';
            if (!in_array('illegal_cari_isletme_adi', $cari_columns)) {
                if (in_array('illegal_cari_firmaAdi', $cari_columns)) {
                    $isletme_adi_column = 'ic.illegal_cari_firmaAdi';
                } else {
                    $isletme_adi_column = 'CONCAT("Tespit #", it.illegal_tespit_id)';
                }
            }
            
            $this->db->select('
                it.illegal_tespit_id,
                it.illegal_tespit_tarih,
                ' . $isletme_adi_column . ' as illegal_cari_isletme_adi
            ');
            $this->db->from('illegal_tespit it');
            $this->db->join('illegal_cari ic', 'it.illegal_cari_id = ic.illegal_cari_id', 'left');
            $this->db->order_by('it.illegal_tespit_id', 'DESC');
            
            $query = $this->db->get();
            
            if ($query) {
                $result = $query->result();
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Tespit bulunamadı']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
    }

    // İşlem istatistikleri
    public function get_islem_statistics() {
        header('Content-Type: application/json');
        
        try {
            // Toplam işlem sayısı
            $this->db->from('illegal_tespit_islemler');
            $toplam_islem = $this->db->count_all_results();
            
            // Bugün yapılan işlemler
            $this->db->from('illegal_tespit_islemler');
            $this->db->where('illegal_tespit_islemler_olusturmaTarihi', date('Y-m-d'));
            $bugun_islem = $this->db->count_all_results();
            
            // Görsel ekli işlemler
            $this->db->from('illegal_tespit_islemler');
            $this->db->where('illegal_tespit_islemler_gorsel !=', '');
            $this->db->where('illegal_tespit_islemler_gorsel IS NOT NULL');
            $gorsel_islem = $this->db->count_all_results();
            
            // Aktif tespit sayısı
            $this->db->from('illegal_tespit');
            $aktif_tespit = $this->db->count_all_results();
            
            $statistics = [
                'toplam_islem' => $toplam_islem,
                'bugun_islem' => $bugun_islem,
                'gorsel_islem' => $gorsel_islem,
                'aktif_tespit' => $aktif_tespit
            ];
            
            echo json_encode(['status' => 'success', 'data' => $statistics]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'İstatistik hatası: ' . $e->getMessage()]);
        }
    }

    // İşlem kaydet
    public function islem_kaydet() {
        header('Content-Type: application/json');
        
        // Session kontrolü
        $control2 = session("r", "login_info");
        $u_id = $control2->kullanici_id ?? 1;
        
        // Edit işlemi mi kontrol et
        $islem_id = $this->input->post('islem_id');
        $is_edit = !empty($islem_id);
        
        // Verileri al ve doğrula
        $illegal_tespit_id = $this->input->post('illegal_tespit_id');
        $aciklama = trim($this->input->post('illegal_tespit_islemler_aciklama'));
        $tarih = $this->input->post('illegal_tespit_islemler_olusturmaTarihi');
        
        if (empty($illegal_tespit_id)) {
            echo json_encode(['status' => 'error', 'message' => 'İllegal tespit seçimi zorunludur!']);
            die();
        }
        
        // Dosya yükleme işlemi
        $gorsel_path = '';
        if (!empty($_FILES['illegal_tespit_islemler_gorsel']['name'])) {
            $upload_path = './assets/uploads/illegal_islemler/';
            
            // Klasör yoksa oluştur
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['illegal_tespit_islemler_gorsel']['name'], PATHINFO_EXTENSION);
            $filename = 'islem_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $full_path = $upload_path . $filename;
            
            if (move_uploaded_file($_FILES['illegal_tespit_islemler_gorsel']['tmp_name'], $full_path)) {
                $gorsel_path = './assets/uploads/illegal_islemler/' . $filename;
            }
        }
        
        // İşlem verilerini hazırla
        $islem_data = array(
            'illegal_tespit_id' => $illegal_tespit_id,
            'illegal_tespit_islemler_aciklama' => $aciklama,
            'illegal_tespit_islemler_olusturmaTarihi' => $tarih ?: date('Y-m-d')
        );
        
        // Dosya yüklenmişse ekle
        if (!empty($gorsel_path)) {
            $islem_data['illegal_tespit_islemler_gorsel'] = $gorsel_path;
        }
        
        try {
            if ($is_edit) {
                // Güncelleme işlemi
                $this->db->where('illegal_tespit_islemler_id', $islem_id);
                $result = $this->db->update('illegal_tespit_islemler', $islem_data);
            } else {
                // Yeni kayıt
                $islem_data['illegal_tespit_islemler_olusturan'] = $u_id;
                $result = $this->db->insert('illegal_tespit_islemler', $islem_data);
            }
            
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => $is_edit ? 'İşlem güncellendi!' : 'İşlem eklendi!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Veritabanı kayıt hatası!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Kayıt hatası: ' . $e->getMessage()]);
        }
    }

    // İşlem detayı getir (POST)
    public function get_islem() {
        header('Content-Type: application/json');
        
        $islem_id = $this->input->post('islem_id');
        
        if (empty($islem_id)) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem ID gerekli!']);
            die();
        }
        
        try {
            $this->db->from('illegal_tespit_islemler');
            $this->db->where('illegal_tespit_islemler_id', $islem_id);
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                $result = $query->row();
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'İşlem bulunamadı!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        die();
    }

    // İşlem ID'ye göre getir (GET - AJAX için)
    public function get_islem_by_id() {
        header('Content-Type: application/json');
        
        $islem_id = $this->input->get('id');
        
        if (empty($islem_id)) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem ID gerekli!']);
            die();
        }
        
        try {
            $this->db->from('illegal_tespit_islemler');
            $this->db->where('illegal_tespit_islemler_id', $islem_id);
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                $result = $query->row();
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'İşlem bulunamadı!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        die();
    }

    // İşlem oluştur (create_islem endpoint)
    public function create_islem() {
        header('Content-Type: application/json');
        
        // Session kontrolü
        $control2 = session("r", "login_info");
        $u_id = $control2->kullanici_id ?? 1;
        
        // Verileri al ve doğrula
        $illegal_tespit_id = $this->input->post('illegal_tespit_id');
        $aciklama = trim($this->input->post('illegal_tespit_islemler_aciklama'));
        $statu = trim($this->input->post('illegal_tespit_islemler_statu'));
        $tarih = $this->input->post('illegal_tespit_islemler_tarih');
        
        if (empty($illegal_tespit_id)) {
            echo json_encode(['status' => 'error', 'message' => 'İllegal tespit seçimi zorunludur!']);
            die();
        }
        
        if (empty($statu)) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem statüsü zorunludur!']);
            die();
        }
        
        if (empty($tarih)) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem tarihi zorunludur!']);
            die();
        }
        
        // Dosya yükleme işlemi
        $gorsel_path = '';
        if (!empty($_FILES['illegal_tespit_islemler_gorsel']['name'])) {
            $upload_path = './assets/uploads/illegal_islemler/';
            
            // Klasör yoksa oluştur
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['illegal_tespit_islemler_gorsel']['name'], PATHINFO_EXTENSION);
            $filename = 'islem_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $full_path = $upload_path . $filename;
            
            if (move_uploaded_file($_FILES['illegal_tespit_islemler_gorsel']['tmp_name'], $full_path)) {
                $gorsel_path = $full_path;
            }
        }
        
        // İşlem verilerini hazırla
        $islem_data = array(
            'illegal_tespit_id' => $illegal_tespit_id,
            'illegal_tespit_islemler_aciklama' => $aciklama,
            'illegal_tespit_islemler_statu' => $statu,
            'illegal_tespit_islemler_tarih' => $tarih,
            'illegal_tespit_islemler_olusturmaTarihi' => date('Y-m-d'),
            'illegal_tespit_islemler_olusturan' => $u_id
        );
        
        // Dosya yüklenmişse ekle
        if (!empty($gorsel_path)) {
            $islem_data['illegal_tespit_islemler_gorsel'] = $gorsel_path;
        }
        
        try {
            if ($this->db->insert('illegal_tespit_islemler', $islem_data)) {
                $insert_id = $this->db->insert_id();
                echo json_encode(['status' => 'success', 'message' => 'İşlem başarıyla eklendi!', 'id' => $insert_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Veritabanı kayıt hatası!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Kayıt hatası: ' . $e->getMessage()]);
        }
        die();
    }

    // İşlem güncelle (update_islem endpoint)
    public function update_islem() {
        header('Content-Type: application/json');
        
        // Session kontrolü
        $control2 = session("r", "login_info");
        $u_id = $control2->kullanici_id ?? 1;
        
        // Edit işlemi mi kontrol et
        $islem_id = $this->input->post('islem_id');
        
        if (empty($islem_id)) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem ID gerekli!']);
            die();
        }
        
        // Verileri al ve doğrula
        $illegal_tespit_id = $this->input->post('illegal_tespit_id');
        $aciklama = trim($this->input->post('illegal_tespit_islemler_aciklama'));
        $statu = trim($this->input->post('illegal_tespit_islemler_statu'));
        $tarih = $this->input->post('illegal_tespit_islemler_tarih');
        
        if (empty($illegal_tespit_id)) {
            echo json_encode(['status' => 'error', 'message' => 'İllegal tespit seçimi zorunludur!']);
            die();
        }
        
        if (empty($statu)) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem statüsü zorunludur!']);
            die();
        }
        
        if (empty($tarih)) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem tarihi zorunludur!']);
            die();
        }
        
        // Mevcut kaydı getir
        $this->db->select('illegal_tespit_islemler_gorsel');
        $this->db->from('illegal_tespit_islemler');
        $this->db->where('illegal_tespit_islemler_id', $islem_id);
        $query = $this->db->get();
        
        $gorsel_path = '';
        if ($query && $query->num_rows() > 0) {
            $row = $query->row();
            $gorsel_path = $row->illegal_tespit_islemler_gorsel;
        }
        
        // Yeni dosya yüklenmişse eski dosyayı sil
        if (!empty($_FILES['illegal_tespit_islemler_gorsel']['name'])) {
            if (!empty($gorsel_path) && file_exists($gorsel_path)) {
                unlink($gorsel_path);
            }
            
            $upload_path = './assets/uploads/illegal_islemler/';
            
            // Klasör yoksa oluştur
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['illegal_tespit_islemler_gorsel']['name'], PATHINFO_EXTENSION);
            $filename = 'islem_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $full_path = $upload_path . $filename;
            
            if (move_uploaded_file($_FILES['illegal_tespit_islemler_gorsel']['tmp_name'], $full_path)) {
                $gorsel_path = $full_path;
            }
        }
        
        // İşlem verilerini hazırla
        $islem_data = array(
            'illegal_tespit_id' => $illegal_tespit_id,
            'illegal_tespit_islemler_aciklama' => $aciklama,
            'illegal_tespit_islemler_statu' => $statu,
            'illegal_tespit_islemler_tarih' => $tarih
        );
        
        // Dosya yüklenmişse ekle
        if (!empty($gorsel_path)) {
            $islem_data['illegal_tespit_islemler_gorsel'] = $gorsel_path;
        }
        
        try {
            $this->db->where('illegal_tespit_islemler_id', $islem_id);
            if ($this->db->update('illegal_tespit_islemler', $islem_data)) {
                echo json_encode(['status' => 'success', 'message' => 'İşlem başarıyla güncellendi!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Güncelleme başarısız!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Güncelleme hatası: ' . $e->getMessage()]);
        }
        die();
    }

    // DataTable için işlemler listesi (filtreleme destekli)
    public function get_islemler_datatable() {
        header('Content-Type: application/json');
        
        try {
            // Oturum bilgisi
            $control2 = session("r", "login_info");
            $kullanici_id = $control2->kullanici_id;
            $kullanici_grup_id = $control2->grup_id;
            
            // Filtreler (POST veya GET olabilir)
            $ulke = $this->input->post('ulke') ?: $this->input->get('ulke');
            $tespit_id = $this->input->post('tespit_id') ?: $this->input->get('tespit_id');
            $statu = $this->input->post('statu') ?: $this->input->get('statu');
            $personel_id = $this->input->post('personel_id') ?: $this->input->get('personel_id');
            $tarih_baslangic = $this->input->post('tarih_baslangic') ?: $this->input->get('tarih_baslangic');
            $tarih_bitis = $this->input->post('tarih_bitis') ?: $this->input->get('tarih_bitis');
            
            // Debug log
            log_message('debug', 'get_islemler_datatable - Statu filtresi: ' . $statu);
            
            // Ülke yetkisi kontrolü (kgy_yetki = 5)
            // Admin grubu (kullanici_grubu id=1) hariç
            $ulke_kisitlamasi_var = false;
            $yetkili_ulkeler = array();
            
            if ($kullanici_grup_id != 1) {
                // Kullanıcının ülke görme yetkisi var mı kontrol et (kgy_yetki = 5, kgy_modul = 1625)
                $this->db->where('kgy_grupID', $kullanici_grup_id);
                $this->db->where('kgy_modul', 1625);
                $this->db->where('kgy_yetki', 5);
                $yetki_var = $this->db->count_all_results('kullanici_grubu_yetkisi') > 0;
                
                if ($yetki_var) {
                    // Kullanıcının atanmış ülkelerini al
                    $this->db->select('kullanici_ulke');
                    $this->db->where('kullanici_id', $kullanici_id);
                    $kullanici_query = $this->db->get('kullanicilar');
                    
                    if ($kullanici_query->num_rows() > 0) {
                        $kullanici_ulke = $kullanici_query->row()->kullanici_ulke;
                        
                        if (!empty($kullanici_ulke)) {
                            // Virgülle ayrılmış ülke ID'lerini diziye çevir
                            $yetkili_ulkeler = array_filter(array_map('trim', explode(',', $kullanici_ulke)));
                            $ulke_kisitlamasi_var = true;
                        }
                    }
                }
            }
            
            $this->db->select('
                iti.illegal_tespit_islemler_id,
                it.illegal_tespit_tarih,
                ic.illegal_cari_isletme_adi as tespit_bilgi,
                ic.illegal_cari_ulke,
                iti.illegal_tespit_islemler_statu as statu_id,
                ist.illegal_statu_adi as statu_adi,
                ist.illegal_statu_renk as statu_renk,
                iti.illegal_tespit_islemler_aciklama as aciklama,
                u.kullanici_ad,
                u.kullanici_soyad,
                CONCAT(u.kullanici_ad, " ", COALESCE(u.kullanici_soyad, "")) as personel_ad,
                iti.illegal_tespit_islemler_gorsel as gorsel,
                iti.illegal_tespit_islemler_tarih as tarih
            ');
            $this->db->from('illegal_tespit_islemler iti');
            $this->db->join('illegal_tespit it', 'iti.illegal_tespit_id = it.illegal_tespit_id', 'left');
            $this->db->join('illegal_cari ic', 'it.illegal_cari_id = ic.illegal_cari_id', 'left');
            $this->db->join('illegal_statu ist', 'iti.illegal_tespit_islemler_statu = ist.illegal_statu_id', 'left');
            $this->db->join('kullanicilar u', 'iti.illegal_tespit_islemler_olusturan = u.kullanici_id', 'left');
            
            // Ülke kısıtlaması varsa filtrele
            if ($ulke_kisitlamasi_var && !empty($yetkili_ulkeler)) {
                $this->db->where_in('ic.illegal_cari_ulke', $yetkili_ulkeler);
            }
            
            // Filtre uygula
            if (!empty($ulke)) {
                $this->db->where('ic.illegal_cari_ulke', $ulke);
            }
            
            if (!empty($tespit_id)) {
                $this->db->where('iti.illegal_tespit_id', $tespit_id);
            }
            
            if (!empty($statu)) {
                $this->db->where('iti.illegal_tespit_islemler_statu', $statu);
            }
            
            if (!empty($personel_id)) {
                $this->db->where('iti.illegal_tespit_islemler_olusturan', $personel_id);
            }
            
            if (!empty($tarih_baslangic)) {
                $this->db->where('DATE(iti.illegal_tespit_islemler_tarih) >=', $tarih_baslangic);
            }
            
            if (!empty($tarih_bitis)) {
                $this->db->where('DATE(iti.illegal_tespit_islemler_tarih) <=', $tarih_bitis);
            }
            
            $this->db->order_by('iti.illegal_tespit_islemler_id', 'DESC');
            
            $query = $this->db->get();
            $result = $query->result();
            
            // Düzenleme ve silme butonları için yetkilendirme kontrolü
            // Admin grubu (kg_id=1) için kısıtlama yok
            $login_info = $this->session->userdata('login_info');
            $yetkiler = $this->session->userdata('yetkiler') ?? [];
            
            // Admin mi kontrol et (grup_id=1 veya kg_id=1)
            $is_admin = false;
            if (isset($login_info->grup_id) && $login_info->grup_id == 1) {
                $is_admin = true;
            } elseif (isset($login_info->kullanici_id)) {
                // Veritabanından kontrol et
                $user = $this->db->get_where('kullanicilar', ['kullanici_id' => $login_info->kullanici_id])->row();
                if ($user && isset($user->grup_id) && $user->grup_id == 1) {
                    $is_admin = true;
                }
            }
            
            // Sil butonu yetkisi: Admin VEYA yetki kodu 4
            $sil_yetkisi = $is_admin || (isset($yetkiler[1625]) && in_array(4, (array)$yetkiler[1625]));
            
            // Düzenle butonu yetkisi: Admin VEYA yetki kodu 3
            $duzenle_yetkisi = $is_admin || (isset($yetkiler[1625]) && in_array(3, (array)$yetkiler[1625]));
            
            $data = [];
            foreach ($result as $row) {
                // Tespit bilgisi
                $tespit_bilgi = $row->tespit_bilgi ?: 'Tespit #' . $row->illegal_tespit_id;
                
                // Statü badge'i (illegal_statu tablosundan gelen renk ve isim kullan)
                $statu_badge = '';
                if (!empty($row->statu_adi)) {
                    $statu_renk = $row->statu_renk ?: '#6c757d';
                    $statu_badge = '<span class="badge" style="background-color: ' . $statu_renk . '; color: white;">' . $row->statu_adi . '</span>';
                } else {
                    $statu_badge = '<span class="badge badge-secondary">Tanımsız</span>';
                }
                
                // Açıklama (uzunsa kes)
                $aciklama = $row->aciklama ?: '-';
                if (strlen($aciklama) > 50) {
                    $aciklama = substr($aciklama, 0, 50) . '...';
                }
                
                // Görsel (varsa thumbnail göster)
                $gorsel_html = '-';
                if ($row->gorsel && file_exists($row->gorsel)) {
                    $gorsel_html = '<img src="' . base_url($row->gorsel) . '" class="img-thumbnail" style="max-width: 50px; max-height: 50px;" alt="Görsel">';
                }
                
                // İşlem butonları
                $islemler_html = '';
                if ($duzenle_yetkisi) {
                    $islemler_html .= '<button class="btn btn-sm btn-primary" onclick="editRecord(' . $row->illegal_tespit_islemler_id . ')" title="Düzenle"><i class="fa fa-edit"></i></button> ';
                }
                if ($sil_yetkisi) {
                    $islemler_html .= '<button class="btn btn-sm btn-danger" onclick="deleteRecord(' . $row->illegal_tespit_islemler_id . ')" title="Sil"><i class="fa fa-trash"></i></button>';
                }
                
                $data[] = [
                    'illegal_tespit_islemler_id' => $row->illegal_tespit_islemler_id,
                    'tespit_bilgi' => $tespit_bilgi,
                    'statu' => $statu_badge,
                    'aciklama' => $aciklama,
                    'personel_ad' => $row->personel_ad ?: 'Bilinmiyor',
                    'tarih' => $row->tarih ?: '-',
                    'gorsel' => $gorsel_html,
                    'islemler' => $islemler_html
                ];
            }
            
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Veri yükleme hatası: ' . $e->getMessage()]);
        }
    }

    // İşlem sil endpoint
    public function delete_islem() {
        header('Content-Type: application/json');
        
        $islem_id = $this->input->post('id');
        
        if (empty($islem_id)) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem ID gerekli!']);
            die();
        }
        
        try {
            // Önce dosyayı sil
            $this->db->select('illegal_tespit_islemler_gorsel');
            $this->db->from('illegal_tespit_islemler');
            $this->db->where('illegal_tespit_islemler_id', $islem_id);
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                $row = $query->row();
                if (!empty($row->illegal_tespit_islemler_gorsel) && file_exists($row->illegal_tespit_islemler_gorsel)) {
                    unlink($row->illegal_tespit_islemler_gorsel);
                }
            }
            
            // Kaydı sil
            $this->db->where('illegal_tespit_islemler_id', $islem_id);
            if ($this->db->delete('illegal_tespit_islemler')) {
                echo json_encode(['status' => 'success', 'message' => 'İşlem silindi!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Silme işlemi başarısız!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Silme hatası: ' . $e->getMessage()]);
        }
        die();
    }

    // Personel listesi için select (filter dropdown için)
    public function get_personeller_for_select() {
        header('Content-Type: application/json');
        
        try {
            // İşlem yapan personelleri getir (GROUP BY ile tekil)
            $sql = "SELECT u.kullanici_id, u.kullanici_ad, u.kullanici_soyad 
                    FROM illegal_tespit_islemler iti 
                    INNER JOIN kullanicilar u ON iti.illegal_tespit_islemler_olusturan = u.kullanici_id 
                    WHERE iti.illegal_tespit_islemler_olusturan IS NOT NULL 
                    GROUP BY u.kullanici_id, u.kullanici_ad, u.kullanici_soyad 
                    ORDER BY u.kullanici_ad ASC";
            
            $query = $this->db->query($sql);
            $result = $query->result();
            
            echo json_encode([
                'status' => 'success', 
                'data' => $result
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Personel listesi yükleme hatası: ' . $e->getMessage()]);
        }
    }

    // İşlem sil
    public function islem_sil() {
        header('Content-Type: application/json');
        
        $islem_id = $this->input->post('islem_id');
        
        if (empty($islem_id)) {
            echo json_encode(['status' => 'error', 'message' => 'İşlem ID gerekli!']);
            die();
        }
        
        try {
            // Önce dosyayı sil
            $this->db->select('illegal_tespit_islemler_gorsel');
            $this->db->from('illegal_tespit_islemler');
            $this->db->where('illegal_tespit_islemler_id', $islem_id);
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                $row = $query->row();
                if (!empty($row->illegal_tespit_islemler_gorsel) && file_exists($row->illegal_tespit_islemler_gorsel)) {
                    unlink($row->illegal_tespit_islemler_gorsel);
                }
            }
            
            // Kaydı sil
            $this->db->where('illegal_tespit_islemler_id', $islem_id);
            $result = $this->db->delete('illegal_tespit_islemler');
            
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'İşlem silindi!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Silme işlemi başarısız!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Silme hatası: ' . $e->getMessage()]);
        }
    }

    // Excel Yükleme Sayfası
    public function illegal_tespit_excel_yukle() {
        // Yetki kontrolü - Modül 1620
        if (!grup_modul_yetkisi_var(1620)) {
            redirect(base_url());
            return;
        }
        
        $this->load->view('illegal/illegal_tespit_excel_yukle');
    }

    // Excel Şablon İndirme
    public function excel_sablon_indir() {
        $file_path = FCPATH . 'assets/uploads/templates/illegal_tespit_sablon.xlsx';
        
        if (!file_exists($file_path)) {
            // Şablon yoksa oluştur
            $this->create_excel_template();
        }
        
        if (file_exists($file_path)) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="illegal_tespit_sablon.xlsx"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            show_error('Şablon dosyası oluşturulamadı!');
        }
    }

    // Excel Şablon Oluşturma
    private function create_excel_template() {
        require_once FCPATH . 'vendor/autoload.php';
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Başlıklar
        $headers = [
            'A1' => 'Cari İşletme Adı *',
            'B1' => 'Telefon',
            'C1' => 'Ülke ID *',
            'D1' => 'İl ID *',
            'E1' => 'İlçe ID *',
            'F1' => 'Adres',
            'G1' => 'Tespit Tarihi * (YYYY-MM-DD)',
            'H1' => 'Tespit Saati * (HH:MM)',
            'I1' => 'Takım ID *',
            'J1' => 'Rakip Takım ID *',
            'K1' => 'Personel ID *',
            'L1' => 'Stok Grup ID',
            'M1' => 'İmzalı (1/0)',
            'N1' => 'Açıklama'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF667eea');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
        }
        
        // Örnek veri satırı
        $sheet->setCellValue('A2', 'Örnek İşletme A.Ş.');
        $sheet->setCellValue('B2', '05321234567');
        $sheet->setCellValue('C2', '1');
        $sheet->setCellValue('D2', '34');
        $sheet->setCellValue('E2', '1601');
        $sheet->setCellValue('F2', 'Örnek Mahalle, Örnek Sokak No:1');
        $sheet->setCellValue('G2', date('Y-m-d'));
        $sheet->setCellValue('H2', '20:00');
        $sheet->setCellValue('I2', '1');
        $sheet->setCellValue('J2', '2');
        $sheet->setCellValue('K2', '187');
        $sheet->setCellValue('L2', '1');
        $sheet->setCellValue('M2', '1');
        $sheet->setCellValue('N2', 'Örnek açıklama');
        
        // Sütun genişlikleri
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(40);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(12);
        $sheet->getColumnDimension('L')->setWidth(15);
        $sheet->getColumnDimension('M')->setWidth(12);
        $sheet->getColumnDimension('N')->setWidth(40);
        
        // Kaydet
        $upload_dir = FCPATH . 'assets/uploads/templates/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($upload_dir . 'illegal_tespit_sablon.xlsx');
    }

    // Excel Kontrol Et
    public function excel_kontrol_et() {
        header('Content-Type: application/json');
        
        if (empty($_FILES['excel_file']['name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Excel dosyası seçilmedi!']);
            die();
        }
        
        try {
            require_once FCPATH . 'vendor/autoload.php';
            
            $file_path = $_FILES['excel_file']['tmp_name'];
            $spreadsheet = IOFactory::load($file_path);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            
            $data = [];
            $validCount = 0;
            $errorCount = 0;
            $warningCount = 0;
            
            // Satır satır oku (2. satırdan başla, 1. satır başlık)
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = [
                    'row_number' => $row,
                    'cari_isletme_adi' => trim($sheet->getCell('A' . $row)->getValue()),
                    'cari_telefon' => trim($sheet->getCell('B' . $row)->getValue()),
                    'cari_ulke' => trim($sheet->getCell('C' . $row)->getValue()),
                    'cari_il' => trim($sheet->getCell('D' . $row)->getValue()),
                    'cari_ilce' => trim($sheet->getCell('E' . $row)->getValue()),
                    'cari_adres' => trim($sheet->getCell('F' . $row)->getValue()),
                    'tespit_tarih' => trim($sheet->getCell('G' . $row)->getValue()),
                    'tespit_saat' => trim($sheet->getCell('H' . $row)->getValue()),
                    'takim' => trim($sheet->getCell('I' . $row)->getValue()),
                    'rakip_takim' => trim($sheet->getCell('J' . $row)->getValue()),
                    'personel_id' => trim($sheet->getCell('K' . $row)->getValue()),
                    'stok_grup_id' => trim($sheet->getCell('L' . $row)->getValue()),
                    'imzali' => trim($sheet->getCell('M' . $row)->getValue()),
                    'aciklama' => trim($sheet->getCell('N' . $row)->getValue()),
                    'errors' => [],
                    'is_valid' => true
                ];
                
                // Boş satır kontrolü
                if (empty($rowData['cari_isletme_adi']) && empty($rowData['tespit_tarih'])) {
                    continue; // Boş satırları atla
                }
                
                // Zorunlu alanları kontrol et
                if (empty($rowData['cari_isletme_adi'])) {
                    $rowData['errors'][] = 'Cari işletme adı zorunlu';
                    $rowData['is_valid'] = false;
                }
                
                if (empty($rowData['cari_ulke'])) {
                    $rowData['errors'][] = 'Ülke ID zorunlu';
                    $rowData['is_valid'] = false;
                }
                
                if (empty($rowData['cari_il'])) {
                    $rowData['errors'][] = 'İl ID zorunlu';
                    $rowData['is_valid'] = false;
                }
                
                if (empty($rowData['cari_ilce'])) {
                    $rowData['errors'][] = 'İlçe ID zorunlu';
                    $rowData['is_valid'] = false;
                }
                
                if (empty($rowData['tespit_tarih'])) {
                    $rowData['errors'][] = 'Tespit tarihi zorunlu';
                    $rowData['is_valid'] = false;
                } else {
                    // Tarih formatını kontrol et
                    $date = DateTime::createFromFormat('Y-m-d', $rowData['tespit_tarih']);
                    if (!$date || $date->format('Y-m-d') !== $rowData['tespit_tarih']) {
                        $rowData['errors'][] = 'Geçersiz tarih formatı (YYYY-MM-DD olmalı)';
                        $rowData['is_valid'] = false;
                    }
                }
                
                if (empty($rowData['tespit_saat'])) {
                    $rowData['errors'][] = 'Tespit saati zorunlu';
                    $rowData['is_valid'] = false;
                }
                
                if (empty($rowData['takim'])) {
                    $rowData['errors'][] = 'Takım ID zorunlu';
                    $rowData['is_valid'] = false;
                }
                
                if (empty($rowData['rakip_takim'])) {
                    $rowData['errors'][] = 'Rakip takım ID zorunlu';
                    $rowData['is_valid'] = false;
                }
                
                if (empty($rowData['personel_id'])) {
                    $rowData['errors'][] = 'Personel ID zorunlu';
                    $rowData['is_valid'] = false;
                }
                
                // Veritabanı kontrolü
                if (!empty($rowData['cari_ulke'])) {
                    $ulke_check = $this->db->get_where('ulkeler', ['id' => $rowData['cari_ulke']])->row();
                    if (!$ulke_check) {
                        $rowData['errors'][] = 'Geçersiz Ülke ID';
                        $rowData['is_valid'] = false;
                    }
                }
                
                if (!empty($rowData['cari_il'])) {
                    $il_check = $this->db->get_where('iller', ['id' => $rowData['cari_il']])->row();
                    if (!$il_check) {
                        $rowData['errors'][] = 'Geçersiz İl ID';
                        $rowData['is_valid'] = false;
                    }
                }
                
                if (!empty($rowData['cari_ilce'])) {
                    $ilce_check = $this->db->get_where('ilceler', ['id' => $rowData['cari_ilce']])->row();
                    if (!$ilce_check) {
                        $rowData['errors'][] = 'Geçersiz İlçe ID';
                        $rowData['is_valid'] = false;
                    }
                }
                
                if (!empty($rowData['takim'])) {
                    $takim_check = $this->db->get_where('illegal_tespit_takimlar', ['illegal_tespit_takim_id' => $rowData['takim']])->row();
                    if (!$takim_check) {
                        $rowData['errors'][] = 'Geçersiz Takım ID';
                        $rowData['is_valid'] = false;
                    }
                }
                
                if (!empty($rowData['rakip_takim'])) {
                    $rakip_check = $this->db->get_where('illegal_tespit_takimlar', ['illegal_tespit_takim_id' => $rowData['rakip_takim']])->row();
                    if (!$rakip_check) {
                        $rowData['errors'][] = 'Geçersiz Rakip Takım ID';
                        $rowData['is_valid'] = false;
                    }
                }
                
                if (!empty($rowData['personel_id'])) {
                    $personel_check = $this->db->get_where('kullanicilar', ['kullanici_id' => $rowData['personel_id']])->row();
                    if (!$personel_check) {
                        $rowData['errors'][] = 'Geçersiz Personel ID';
                        $rowData['is_valid'] = false;
                    }
                }
                
                if (!empty($rowData['stok_grup_id'])) {
                    $stok_check = $this->db->get_where('stokgruplari', ['stokGrup_id' => $rowData['stok_grup_id']])->row();
                    if (!$stok_check) {
                        $rowData['errors'][] = 'Geçersiz Stok Grup ID';
                        $rowData['is_valid'] = false;
                    }
                }
                
                if ($rowData['is_valid']) {
                    $validCount++;
                } else {
                    $errorCount++;
                }
                
                $data[] = $rowData;
            }
            
            $summary = [
                'total' => count($data),
                'valid' => $validCount,
                'errors' => $errorCount,
                'warnings' => $warningCount
            ];
            
            echo json_encode([
                'status' => 'success',
                'data' => $data,
                'summary' => $summary
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Excel dosyası işlenirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    // Excel Verilerini Kaydet
    public function excel_kaydet() {
        header('Content-Type: application/json');
        
        // Session kontrolü
        $control2 = session("r", "login_info");
        $u_id = $control2->kullanici_id ?? 1;
        
        // JSON verisini al
        $json = file_get_contents('php://input');
        $request = json_decode($json, true);
        
        if (empty($request['data'])) {
            echo json_encode(['status' => 'error', 'message' => 'Kaydedilecek veri bulunamadı!']);
            die();
        }
        
        $data = $request['data'];
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        try {
            // Transaction başlat
            $this->db->trans_start();
            
            foreach ($data as $row) {
                // Sadece geçerli kayıtları işle
                if (!$row['is_valid']) {
                    continue;
                }
                
                // Cari kayıt oluştur veya bul
                $cari_data = [
                    'illegal_cari_isletme_adi' => $row['cari_isletme_adi'],
                    'illegal_cari_firmaTelefon' => $row['cari_telefon'],
                    'illegal_cari_ulke' => $row['cari_ulke'],
                    'illegal_cari_il' => $row['cari_il'],
                    'illegal_cari_ilce' => $row['cari_ilce'],
                    'illegal_cari_adres' => $row['cari_adres']
                ];
                
                // Mevcut cari var mı kontrol et
                $this->db->where('illegal_cari_isletme_adi', $row['cari_isletme_adi']);
                $existing_cari = $this->db->get('illegal_cari')->row();
                
                if ($existing_cari) {
                    $cari_id = $existing_cari->illegal_cari_id;
                } else {
                    $cari_data['illegal_cari_olusturan'] = $u_id;
                    $cari_data['illegal_cari_olusturmaTarihi'] = date('Y-m-d');
                    $cari_data['illegal_cari_durum'] = 1;
                    
                    if ($this->db->insert('illegal_cari', $cari_data)) {
                        $cari_id = $this->db->insert_id();
                    } else {
                        $errorCount++;
                        $errors[] = 'Satır ' . $row['row_number'] . ': Cari kaydedilemedi';
                        continue;
                    }
                }
                
                // Tespit kaydı oluştur
                $tespit_data = [
                    'illegal_cari_id' => $cari_id,
                    'illegal_tespit_tarih' => $row['tespit_tarih'],
                    'illegal_tespit_saat' => $row['tespit_saat'],
                    'illegal_tespit_takim_id' => $row['takim'],
                    'illegal_tespit_rakip_takim_id' => $row['rakip_takim'],
                    'illegal_tespit_personel_id' => $row['personel_id'],
                    'illegal_tespit_stokGrup_id' => !empty($row['stok_grup_id']) ? $row['stok_grup_id'] : null,
                    'illegal_tespit_imzali' => !empty($row['imzali']) ? 1 : 0,
                    'illegal_tespit_aciklama' => $row['aciklama'],
                    'illegal_tespit_sezon_id' => $this->get_aktif_sezon_id(),
                    'illegal_tespit_olusturan' => $u_id,
                    'illegal_tespit_olusturmaTarihi' => date('Y-m-d')
                ];
                
                if ($this->db->insert('illegal_tespit', $tespit_data)) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = 'Satır ' . $row['row_number'] . ': Tespit kaydedilemedi';
                }
            }
            
            // Transaction tamamla
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Veritabanı hatası oluştu!'
                ]);
            } else {
                $message = "$successCount kayıt başarıyla eklendi.";
                if ($errorCount > 0) {
                    $message .= " $errorCount kayıt eklenemedi.";
                }
                
                echo json_encode([
                    'status' => 'success',
                    'message' => $message,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Kayıt sırasında hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

}

