<?php
// filepath: application/controllers/Tahsilat.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tahsilat extends CI_Controller {
    public function __construct() {
        parent::__construct();
        error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
        $this->load->model('vt');
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('general');
        $this->load->helper('url');
        
        // Giriş kontrolü (örnek)
        $control = session('r', 'login');
        if (!$control) {
            redirect('check');
        }
    }
    
    // Mobil cihaz kontrolü
    private function is_mobile_device() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        return preg_match('/Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $user_agent);
    }
    
    public function tahsilat_kaydet() {
        // Mobil cihaz kontrolü
        $is_mobile = $this->is_mobile_device();
        
        // Formdan gelen veriler
        $cari_id = $this->input->post('cari_id');
        $tahsilat_cariKodu = $this->input->post('tahsilat_cariKodu');
        $tahsilat_cariAdi = $this->input->post('tahsilat_cariAdi');
        $tahsilat_odemeTipi = $this->input->post('tahsilat_odemeTipi');
        $tahsilat_kasaID = $this->input->post('tahsilat_kasaID');
        // Disabled alanlardan değer alamıyorsak hidden input'tan al
        if (!$tahsilat_kasaID) {
            $tahsilat_kasaID = $this->input->post('tahsilat_kasaID_hidden');
        }
        
        // Debug için log ekle
        error_log("Debug tahsilat_kaydet - kasaID normal: " . var_export($this->input->post('tahsilat_kasaID'), true));
        error_log("Debug tahsilat_kaydet - kasaID hidden: " . var_export($this->input->post('tahsilat_kasaID_hidden'), true));
        error_log("Debug tahsilat_kaydet - final kasaID: " . var_export($tahsilat_kasaID, true));
        $tahsilat_bankaID = $this->input->post('tahsilat_bankaID');
        $tahsilat_tarih = $this->input->post('tahsilat_tarih');
        $tahsilat_tutar = $this->input->post('tahsilat_tutar');
        $tahsilat_aciklama = $this->input->post('tahsilat_aciklama');

        // Validasyon kontrolleri
        if (!$cari_id) {
            $this->session->set_flashdata('tahsilat_hata', 'Lütfen geçerli bir cari seçiniz.');
            redirect('tahsilat/tahsilat-olustur');
            return;
        }

        if (!$tahsilat_odemeTipi) {
            $this->session->set_flashdata('tahsilat_hata', 'Lütfen ödeme türü seçiniz.');
            redirect('tahsilat/tahsilat-olustur');
            return;
        }

        if ($tahsilat_odemeTipi == 1 && !$tahsilat_kasaID) {
            $this->session->set_flashdata('tahsilat_hata', 'Kasa ödemesi için lütfen kasa seçiniz.');
            redirect('tahsilat/tahsilat-olustur');
            return;
        }        if ($tahsilat_odemeTipi == 2 && !$tahsilat_bankaID) {
            $this->session->set_flashdata('tahsilat_hata', 'Banka ödemesi için lütfen banka seçiniz.');
            redirect('tahsilat/tahsilat-olustur');
            return;
        }// Çek ödemesi validasyonu
        if ($tahsilat_odemeTipi == 3) {
            $cek_bankaID = $this->input->post('cek_bankaID');
            $cek_bankaSube = $this->input->post('cek_bankaSube');

            if (!$cek_bankaID || !$cek_bankaSube) {
                $this->session->set_flashdata('tahsilat_hata', 'Çek ödemesi için çek bankası ve banka şubesi bilgilerini doldurunuz.');
                redirect('tahsilat/tahsilat-olustur');
                return;
            }
        }

        // Senet ödemesi validasyonu
        if ($tahsilat_odemeTipi == 4) {
            $senet_tutar = $this->input->post('senet_tutar');
            $senet_vade = $this->input->post('senet_vade');

            // En az bir senet girişinin dolu olduğunu kontrol et
            $valid_senet_count = 0;
            if ($senet_tutar && $senet_vade) {
                for ($i = 0; $i < count($senet_tutar); $i++) {
                    if (!empty($senet_tutar[$i]) && !empty($senet_vade[$i])) {
                        $valid_senet_count++;
                    }
                }
            }

            if ($valid_senet_count == 0) {
                $this->session->set_flashdata('tahsilat_hata', 'Senet ödemesi için en az bir senet bilgisi giriniz (tutar ve vade tarihi).');
                redirect('tahsilat/tahsilat-olustur');
                return;
            }
        }

        // Ana tutar alanı validasyonu (Senet ödemesi dışında zorunlu)
        if ($tahsilat_odemeTipi != 4 && (empty($tahsilat_tutar) || $tahsilat_tutar <= 0)) {
            $this->session->set_flashdata('tahsilat_hata', 'Lütfen geçerli bir tutar giriniz.');
            redirect('tahsilat/tahsilat-olustur');
            return;
        }

        // Get user and company info
        $control2 = session("r", "login_info");
        $u_id = $control2->kullanici_id;
        $anaHesap = anaHesapBilgisi();
        
        // Tarihi formatla
        $tarih_parts = explode('.', $tahsilat_tarih);
        if (count($tarih_parts) == 3) {
            $formatted_tarih = $tarih_parts[2] . '-' . $tarih_parts[1] . '-' . $tarih_parts[0];
        } else {
            $formatted_tarih = date('Y-m-d');
        }

        // Generate unique document number
        $belge_no = 'THS-' . date('Ymd') . '-' . time();        try {
            $this->db->trans_start();            // Insert into appropriate movement table based on payment type
            if ($tahsilat_odemeTipi == 1) { // Kasa
                $kasa_data = [
                    'kh_kasaID' => $tahsilat_kasaID,
                    'kh_cariID' => $cari_id,
                    'kh_tarih' => $formatted_tarih,
                    'kh_giris' => $tahsilat_tutar, // Tahsilat için giriş alanını kullan
                    'kh_turu' => 8, // Tahsilat türü
                    'kh_aciklama' => $tahsilat_aciklama,
                    'kh_belgeNumarasi' => $belge_no,
                    'kh_olusturan' => $u_id,
                    'kh_olusturanAnaHesap' => $anaHesap,
                    'kh_olusturmaTarihi' => date('Y-m-d'),
                    'kh_olusturmaSaati' => date('H:i:s')
                ];
                $this->db->insert('kasaHareketleri', $kasa_data);            } elseif ($tahsilat_odemeTipi == 2) { // Banka / POS
                // Banka dosya yükleme işlemi - Manual upload without CodeIgniter Upload library
                $uploaded_file_name = null;
                if (isset($_FILES['banka_gorsel']) && !empty($_FILES['banka_gorsel']['name'])) {
                    $upload_path = './assets/uploads/dekontlar/';
                    $allowed_types = array('gif', 'jpg', 'png', 'jpeg', 'pdf');
                    $max_size = 5120000; // 5MB in bytes
                    
                    // Upload dizinini kontrol et ve yoksa oluştur
                    if (!is_dir($upload_path)) {
                        mkdir($upload_path, 0755, true);
                    }
                    
                    $file_info = pathinfo($_FILES['banka_gorsel']['name']);
                    $file_ext = strtolower($file_info['extension']);
                    
                    // Dosya türü kontrolü
                    if (in_array($file_ext, $allowed_types) && $_FILES['banka_gorsel']['size'] <= $max_size) {
                        // Güvenli dosya adı oluştur
                        $encrypted_name = md5(uniqid(rand(), true)) . '.' . $file_ext;
                        $upload_file = $upload_path . $encrypted_name;
                        
                        if (move_uploaded_file($_FILES['banka_gorsel']['tmp_name'], $upload_file)) {
                            $uploaded_file_name = $encrypted_name;
                        }
                    }
                }

                $banka_data = [
                    'bh_bankaID' => $tahsilat_bankaID,
                    'bh_cariID' => $cari_id,
                    'bh_tarih' => $formatted_tarih,
                    'bh_giris' => $tahsilat_tutar, // Gelen havale/EFT/POS için giris alanını kullan
                    'bh_turu' => 1, // Gelen Havale/EFT/POS türü
                    'bh_aciklama' => $tahsilat_aciklama,
                    'bh_belgeNumarasi' => $belge_no,
                    'bh_gorsel' => $uploaded_file_name, // Yüklenen dosya adı
                    'bh_olusturan' => $u_id,
                    'bh_olusturanAnaHesap' => $anaHesap,
                    'bh_olusturmaTarihi' => date('Y-m-d'),
                    'bh_olusturmaSaati' => date('H:i:s')
                ];
                $this->db->insert('bankaHareketleri', $banka_data);
                  } elseif ($tahsilat_odemeTipi == 3) { // Çek
                // Çek bilgilerini al
                $cek_bankaID = $this->input->post('cek_bankaID');
                $cek_bankaSube = $this->input->post('cek_bankaSube');

                // Çek dosya yükleme işlemi - Manual upload without CodeIgniter Upload library
                $uploaded_file_name = null;
                if (isset($_FILES['cek_gorsel']) && !empty($_FILES['cek_gorsel']['name'])) {
                    $upload_path = './assets/uploads/cekler/';
                    $allowed_types = array('gif', 'jpg', 'png', 'jpeg', 'pdf');
                    $max_size = 5120000; // 5MB in bytes
                    
                    // Upload dizinini kontrol et ve yoksa oluştur
                    if (!is_dir($upload_path)) {
                        mkdir($upload_path, 0755, true);
                    }
                    
                    $file_info = pathinfo($_FILES['cek_gorsel']['name']);
                    $file_ext = strtolower($file_info['extension']);
                    
                    // Dosya türü kontrolü
                    if (in_array($file_ext, $allowed_types) && $_FILES['cek_gorsel']['size'] <= $max_size) {
                        // Güvenli dosya adı oluştur
                        $encrypted_name = md5(uniqid(rand(), true)) . '.' . $file_ext;
                        $upload_file = $upload_path . $encrypted_name;
                        
                        if (move_uploaded_file($_FILES['cek_gorsel']['tmp_name'], $upload_file)) {
                            $uploaded_file_name = $encrypted_name;
                        }
                    }
                }

                // Çek tablosuna kaydet
                $cek_data = [
                    'cek_cariID' => $cari_id,
                    'cek_hareketTipi' => 1, // Alınan çek (sabit)
                    'cek_kayitTarihi' => $formatted_tarih,
                    'cek_notAciklama' => $tahsilat_aciklama,
                    'cek_portfoyNo' => $belge_no, // Belge numarasını portföy no olarak kullan
                    'cek_seriNo' => 'CHK-' . time(), // Otomatik seri no
                    'cek_borcluID' => $cari_id, // Çeki veren kişi (tahsilat yapılan cari)
                    'cek_vadeTarih' => $formatted_tarih, // Tahsilat tarihi ile aynı
                    'cek_bankaID' => $cek_bankaID,
                    'cek_bankaSube' => $cek_bankaSube,
                    'cek_tutar' => $tahsilat_tutar,
                    'cek_gorsel' => $uploaded_file_name, // Yüklenen dosya adı
                    'cek_durum' => 0, // Aktif durum
                    'cek_kullaniciID' => $u_id,
                    'cek_olusturanAnaHesap' => $anaHesap,
                    'cek_sistemKayitTarihi' => date('Y-m-d'),
                    'cek_sistemKayitSaati' => date('H:i:s')                ];
                $this->db->insert('cek', $cek_data);
                  } elseif ($tahsilat_odemeTipi == 4) { // Senet
                // Senet kontrol bilgilerini al
                $senet_birim_tutari = $this->input->post('senet_tutari');
                $senet_adedi = $this->input->post('senet_adedi'); 
                $senet_ilk_vade = $this->input->post('senet_vade_tarihi');
                
                // Dinamik senet bilgilerini al
                $senet_tutar_array = $this->input->post('senet_tutar');
                $senet_vade_array = $this->input->post('senet_vade');

                // Validasyon: Kontrol alanları dolu olmalı
                if (empty($senet_birim_tutari) || empty($senet_adedi) || empty($senet_ilk_vade)) {
                    $this->session->set_flashdata('tahsilat_hata', 'Senet bilgileri eksik. Senet tutarı, adedi ve vade tarihi gereklidir.');
                    redirect(base_url() . 'tahsilat/olustur');
                    return;
                }

                // Validasyon: Senet adedi makul sınırlar içinde olmalı
                if ($senet_adedi < 1 || $senet_adedi > 10) {
                    $this->session->set_flashdata('tahsilat_hata', 'Senet adedi 1-10 arasında olmalıdır.');
                    redirect(base_url() . 'tahsilat/olustur');
                    return;
                }                // Her bir senet için kayıt yap
                if ($senet_tutar_array && $senet_vade_array && count($senet_tutar_array) == $senet_adedi) {
                    for ($i = 0; $i < count($senet_tutar_array); $i++) {
                        if (!empty($senet_tutar_array[$i]) && !empty($senet_vade_array[$i])) {
                            // Vade tarihini formatla
                            $vade_parts = explode('.', $senet_vade_array[$i]);
                            if (count($vade_parts) == 3) {
                                $formatted_vade = $vade_parts[2] . '-' . $vade_parts[1] . '-' . $vade_parts[0];
                            } else {
                                $formatted_vade = date('Y-m-d');
                            }

                            // Dosya yükleme işlemi - Manual upload without CodeIgniter Upload library
                            $uploaded_file_name = null;
                            if (isset($_FILES['senet_gorsel']['name'][$i]) && !empty($_FILES['senet_gorsel']['name'][$i])) {
                                $upload_path = './assets/uploads/senetler/';
                                $allowed_types = array('gif', 'jpg', 'png', 'jpeg');
                                $max_size = 5120000; // 5MB in bytes
                                
                                // Upload dizinini kontrol et ve yoksa oluştur
                                if (!is_dir($upload_path)) {
                                    mkdir($upload_path, 0755, true);
                                }
                                
                                $file_name = $_FILES['senet_gorsel']['name'][$i];
                                $file_tmp = $_FILES['senet_gorsel']['tmp_name'][$i];
                                $file_size = $_FILES['senet_gorsel']['size'][$i];
                                $file_error = $_FILES['senet_gorsel']['error'][$i];
                                
                                if ($file_error == 0 && $file_size <= $max_size) {
                                    $file_info = pathinfo($file_name);
                                    $file_ext = strtolower($file_info['extension']);
                                    
                                    // Dosya türü kontrolü
                                    if (in_array($file_ext, $allowed_types)) {
                                        // Güvenli dosya adı oluştur
                                        $encrypted_name = md5(uniqid(rand(), true)) . '.' . $file_ext;
                                        $upload_file = $upload_path . $encrypted_name;
                                        
                                        if (move_uploaded_file($file_tmp, $upload_file)) {
                                            $uploaded_file_name = $encrypted_name;
                                        }
                                    }
                                }
                            }

                            // Senet tablosuna kaydet
                            $senet_data = [
                                'senet_cariID' => $cari_id,
                                'senet_hareketTipi' => 1, // Alınan senet (sabit)
                                'senet_kayitTarihi' => $formatted_tarih,
                                'senet_notAciklama' => $tahsilat_aciklama . ' - Senet ' . ($i + 1) . '/' . $senet_adedi,
                                'senet_portfoyNo' => $belge_no . '-S' . ($i + 1), // Belge numarası + senet sırası
                                'senet_seriNo' => 'SNT-' . time() . '-' . ($i + 1), // Otomatik seri no
                                'senet_borcluID' => $cari_id, // Senedi veren kişi (tahsilat yapılan cari)
                                'senet_vadeTarih' => $formatted_vade,
                                'senet_tutar' => $senet_tutar_array[$i],
                                'senet_gorsel' => $uploaded_file_name, // Yüklenen dosya adı
                                'senet_durum' => 0, // Aktif durum
                                'senet_kullaniciID' => $u_id,
                                'senet_olusturanAnaHesap' => $anaHesap,
                                'senet_sistemKayitTarihi' => date('Y-m-d'),
                                'senet_sistemKayitSaati' => date('H:i:s')
                            ];                            $this->db->insert('senet', $senet_data);
                        }
                    }
                } else {
                    $this->session->set_flashdata('tahsilat_hata', 'Senet detay bilgileri eksik veya tutarsız.');
                    redirect(base_url() . 'tahsilat/olustur');
                    return;
                }
            }

            // Calculate total amount based on payment type
            $total_amount = $tahsilat_tutar; // Default to main amount field
            
            // For Senet payments, calculate total from individual senet amounts
            if ($tahsilat_odemeTipi == 4) {
                $total_amount = 0;
                $senet_tutar_array = $this->input->post('senet_tutar');
                if ($senet_tutar_array && is_array($senet_tutar_array)) {
                    foreach ($senet_tutar_array as $tutar) {
                        if (!empty($tutar) && is_numeric($tutar)) {
                            $total_amount += floatval($tutar);
                        }
                    }
                }
                  // Tahsilat tutarını senet toplamı ile güncelle
                $tahsilat_tutar = $total_amount;
            }            // Update cari balance (if cari hareketleri table exists)
            $cari_data = [
                'ch_cariID' => $cari_id,
                'ch_tarih' => $formatted_tarih,
                'ch_alacak' => $total_amount, // Use calculated total amount
                'ch_turu' => $tahsilat_odemeTipi == 3 ? 8 : ($tahsilat_odemeTipi == 4 ? 9 : 7), // 8: Alınan çek, 9: Alınan senet, 7: Tahsilat
                'ch_aciklama' => $tahsilat_aciklama,
                'ch_belgeNumarasi' => $belge_no,
                'ch_olusturan' => $u_id,
                'ch_olusturanAnaHesap' => $anaHesap,
                'ch_olusturmaTarihi' => date('Y-m-d'),
                'ch_olusturmaSaati' => date('H:i:s')
            ];
            
            // Insert into cariHareketleri using raw SQL (consistent with other controllers)
            $ch_cariID = $cari_data['ch_cariID'];
            $ch_tarih = $cari_data['ch_tarih'];
            $ch_alacak = $cari_data['ch_alacak'];
            $ch_turu = $cari_data['ch_turu'];
            $ch_aciklama = $cari_data['ch_aciklama'];
            $ch_belgeNumarasi = $cari_data['ch_belgeNumarasi'];
            $ch_olusturan = $cari_data['ch_olusturan'];
            $ch_olusturanAnaHesap = $cari_data['ch_olusturanAnaHesap'];
            $ch_olusturmaTarihi = $cari_data['ch_olusturmaTarihi'];
            $ch_olusturmaSaati = $cari_data['ch_olusturmaSaati'];
            
            $cari_sql = "INSERT INTO cariHareketleri (ch_belgeNumarasi, ch_turu, ch_cariID, ch_paraBirimi, ch_alacak, ch_tarih, ch_aciklama, ch_olusturan, ch_olusturanAnaHesap, ch_olusturmaTarihi, ch_olusturmaSaati) VALUES ('$ch_belgeNumarasi', $ch_turu, $ch_cariID, 'TL', $ch_alacak, '$ch_tarih', '$ch_aciklama', '$ch_olusturan', '$ch_olusturanAnaHesap', '$ch_olusturmaTarihi', '$ch_olusturmaSaati')";
            
            $cari_insert_result = $this->db->query($cari_sql);
            if (!$cari_insert_result) {
                error_log("TAHSILAT DEBUG: cariHareketleri insert failed with SQL: " . $cari_sql);
                $db_error = $this->db->error();
                error_log("TAHSILAT DEBUG: Database error: " . json_encode($db_error));
            } else {
                error_log("TAHSILAT DEBUG: cariHareketleri insert successful");
            }

            $this->db->trans_complete();            if ($this->db->trans_status() === FALSE) {
                if ($tahsilat_odemeTipi == 3) {
                    $this->session->set_flashdata('tahsilat_hata', 'Çek kaydedilirken bir hata oluştu.');
                } else {
                    $this->session->set_flashdata('tahsilat_hata', 'Tahsilat kaydedilirken bir hata oluştu.');
                }
            } else {
                if ($tahsilat_odemeTipi == 3) {
                    $this->session->set_flashdata('tahsilat_ok', 'Çek başarıyla kaydedildi.');
                } else {
                    $this->session->set_flashdata('tahsilat_ok', 'Tahsilat başarıyla kaydedildi.');
                }
            }

        } catch (Exception $e) {
            $this->session->set_flashdata('tahsilat_hata', 'Tahsilat kaydedilirken bir hata oluştu: ' . $e->getMessage());
        }

        redirect('tahsilat/tahsilat-olustur');
    }    public function tahsilat_olustur() {
        // Yetki kontrolü
        if (!grup_modul_yetkisi_var(510)) {
            redirect(base_url('home'));
            return;
        }
        
        // Get current user's ID
        $control2 = session("r", "login_info");
        $kullanici_id = $control2->kullanici_id;
        $u_id = $kullanici_id;
        
        // Find user's assigned kasa using the helper function
        $user_assigned_kasas = getUserResponsibleVaults($kullanici_id);
        $user_assigned_kasa = !empty($user_assigned_kasas) ? $user_assigned_kasas[0] : null;
        
        // Pass data to view
        $data['baslik'] = 'Tahsilat / Tahsilat Oluştur';
        $data['user_assigned_kasa'] = $user_assigned_kasa;
        
        // Add permission variables for sidebar
        // Cari permissions
        // $data['yetkiSorgul1_cari'] = yetkiSorgula($u_id, 1, 1);
        // $data['yetkiSorgul2_cari'] = yetkiSorgula($u_id, 1, 2);
        // $data['yetkiSorgul3_cari'] = yetkiSorgula($u_id, 1, 3);
        // $data['yetkiSorgul4_cari'] = yetkiSorgula($u_id, 1, 4);
        // $data['yetkiSorgul5_cari'] = yetkiSorgula($u_id, 1, 5);
        // $data['yetkiSorgul6_cari'] = yetkiSorgula($u_id, 1, 6);
        // $data['yetkiSorgul7_cari'] = yetkiSorgula($u_id, 1, 7);
        // $data['yetkiSorgul8_cari'] = yetkiSorgula($u_id, 1, 8);
        // $data['yetkiSorgul9_cari'] = yetkiSorgula($u_id, 1, 9);
        // Stok permissions
        // $data['yetkiSorgul1_stok'] = yetkiSorgula($u_id, 2, 1);
        // $data['yetkiSorgul2_stok'] = yetkiSorgula($u_id, 2, 2);
        // $data['yetkiSorgul3_stok'] = yetkiSorgula($u_id, 2, 3);
        // $data['yetkiSorgul4_stok'] = yetkiSorgula($u_id, 2, 4);
        // $data['yetkiSorgul5_stok'] = yetkiSorgula($u_id, 2, 5);
        // $data['yetkiSorgul6_stok'] = yetkiSorgula($u_id, 2, 6);
        // $data['yetkiSorgul7_stok'] = yetkiSorgula($u_id, 2, 7);
        // Fatura permissions
        // $data['yetkiSorgul1_fatura'] = yetkiSorgula($u_id, 3, 1);
        // $data['yetkiSorgul2_fatura'] = yetkiSorgula($u_id, 3, 2);
        // $data['yetkiSorgul3_fatura'] = yetkiSorgula($u_id, 3, 3);
        // $data['yetkiSorgul4_fatura'] = yetkiSorgula($u_id, 3, 4);
        // $data['yetkiSorgul5_fatura'] = yetkiSorgula($u_id, 3, 5);
        // $data['yetkiSorgul6_fatura'] = yetkiSorgula($u_id, 3, 6);
        // $data['yetkiSorgul7_efatura'] = yetkiSorgula($u_id, 3, 7);
        // $data['yetkiSorgul8_earsiv'] = yetkiSorgula($u_id, 3, 8);
        // Kasa permissions
        // $data['yetkiSorgul1_kasa'] = yetkiSorgula($u_id, 4, 1);
        // $data['yetkiSorgul2_kasa'] = yetkiSorgula($u_id, 4, 2);
        // $data['yetkiSorgul3_kasa'] = yetkiSorgula($u_id, 4, 3);
        // $data['yetkiSorgul4_kasa'] = yetkiSorgula($u_id, 4, 4);
        // $data['yetkiSorgul5_kasa'] = yetkiSorgula($u_id, 4, 5);
        // $data['yetkiSorgul6_kasa'] = yetkiSorgula($u_id, 4, 6);
        // $data['yetkiSorgul7_kasa'] = yetkiSorgula($u_id, 4, 7);
        // $data['yetkiSorgul8_kasa'] = yetkiSorgula($u_id, 4, 8);
        // $data['yetkiSorgul9_kasa'] = yetkiSorgula($u_id, 4, 9);
        // $data['yetkiSorgul10_kasa'] = yetkiSorgula($u_id, 4, 10);
        // $data['yetkiSorgul11_kasa'] = yetkiSorgula($u_id, 4, 11);
        // $data['yetkiSorgul12_kasa'] = yetkiSorgula($u_id, 4, 12);
        // Banka permissions
        // $data['yetkiSorgul1_banka'] = yetkiSorgula($u_id, 5, 1);
        // $data['yetkiSorgul2_banka'] = yetkiSorgula($u_id, 5, 2);
        // $data['yetkiSorgul3_banka'] = yetkiSorgula($u_id, 5, 3);
        // $data['yetkiSorgul4_banka'] = yetkiSorgula($u_id, 5, 4);
        // $data['yetkiSorgul5_banka'] = yetkiSorgula($u_id, 5, 5);
        // $data['yetkiSorgul6_banka'] = yetkiSorgula($u_id, 5, 6);
        // $data['yetkiSorgul7_banka'] = yetkiSorgula($u_id, 5, 7);
        // $data['yetkiSorgul8_banka'] = yetkiSorgula($u_id, 5, 8);
        // $data['yetkiSorgul9_banka'] = yetkiSorgula($u_id, 5, 9);
        // $data['yetkiSorgul10_banka'] = yetkiSorgula($u_id, 5, 10);
        // $data['yetkiSorgul11_banka'] = yetkiSorgula($u_id, 5, 11);
        // $data['yetkiSorgul12_banka'] = yetkiSorgula($u_id, 5, 12);
        // Giderler permissions
        // $data['yetkiSorgul1_giderler'] = yetkiSorgula($u_id, 7, 1);
        // $data['yetkiSorgul2_giderler'] = yetkiSorgula($u_id, 7, 2);
        // $data['yetkiSorgul3_giderler'] = yetkiSorgula($u_id, 7, 3);
        
        $this->load->view('tahsilat/tahsilat-olustur', $data);
    }
    
    /**
     * Tahsilat Listesi Sayfası
     * Kullanıcının kendi tahsilat kayıtlarını listeler
     */
    public function tahsilat_listesi() {
        // Memory limit artır (performans için)
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 300);
        
        $data["baslik"] = "Tahsilat / Tahsilat Listesi";
        $anaHesap = anaHesapBilgisi();
        
        // Giriş yapan kullanıcı bilgisi
        $control = session("r", "login_info");
        if (!$control || !isset($control->kullanici_id)) {
            redirect(base_url('check'));
            return;
        }
        $u_id = $control->kullanici_id;
        
        // Çek senkronizasyonu - çek tablosundaki kayıtları muhasebe_tahsilat_durum'a ekle
        $this->senkronizeCekler();
        
        // Banka hareketleri senkronizasyonu - bankaHareketleri tablosundaki kayıtları muhasebe_tahsilat_durum'a ekle
        $this->senkronizeBankaHareketleri();
        
        // Kasa hareketleri senkronizasyonu - kasaHareketleri tablosundaki kayıtları muhasebe_tahsilat_durum'a ekle
        $this->senkronizeKasaHareketleri();
        
        // Senet senkronizasyonu - senet tablosundaki kayıtları muhasebe_tahsilat_durum'a ekle
        $this->senkronizeSenetler();
        
        // Muhasebe tahsilat durum tablosunun varlığını kontrol et
        $tableExists = $this->db->query("SHOW TABLES LIKE 'muhasebe_tahsilat_durum'")->num_rows();
        
        if ($tableExists == 0) {
            // Tablo yoksa boş veri döndür ve kullanıcıyı bilgilendir
            $data["tahsilatlar"] = array();
            $data["toplam_tutar"] = 0;
            $data["toplam_adet"] = 0;
            $data["error_message"] = "Muhasebe tahsilat durum tablosu henüz oluşturulmamış. Lütfen 'muhase_database_setup.sql' dosyasını veritabanında çalıştırın.";
        } else {
            // Bağımlı tabloların varlığını da kontrol et
            $requiredTables = ['bankaHareketleri', 'cek', 'kasaHareketleri', 'senet', 'cari', 'kullanicilar'];
            $missingTables = [];
            
            foreach($requiredTables as $table) {
                $exists = $this->db->query("SHOW TABLES LIKE '$table'")->num_rows();
                if($exists == 0) {
                    $missingTables[] = $table;
                }
            }
            
            if(!empty($missingTables)) {
                $data["tahsilatlar"] = array();
                $data["toplam_tutar"] = 0;
                $data["toplam_adet"] = 0;
                $data["error_message"] = "Eksik tablolar tespit edildi: " . implode(', ', $missingTables) . ". Lütfen veritabanı kurulumunu tamamlayın.";
            } else {
                // Ana sorgu: Sadece giriş yapan kullanıcının tahsilatlarını getir
                $tahsilatlarQ = "SELECT 
                   mtd.id,
                   mtd.tahsilat_tipi,
                   CASE 
                       WHEN mtd.tahsilat_tipi = 1 THEN 'Banka'
                       WHEN mtd.tahsilat_tipi = 2 THEN 'Çek'
                       WHEN mtd.tahsilat_tipi = 3 THEN 'Kasa'
                       WHEN mtd.tahsilat_tipi = 4 THEN 'Senet'
                       ELSE 'Bilinmiyor'
                   END as tahsilat_tipi_adi,
                   mtd.kayit_id,
                   CASE 
                       WHEN mtd.durum = 1 THEN 'Onay Bekliyor'
                       WHEN mtd.durum = 2 THEN 'Onaylandı'
                       WHEN mtd.durum = 3 THEN 'Reddedildi'
                       ELSE 'Bilinmiyor'
                   END as durum_adi,
                   mtd.durum,
                   COALESCE(bh_cari.cari_ad, c_cari.cari_ad, kh_cari.cari_ad, s_cari.cari_ad) as musteri_adi,
                   COALESCE(bh.bh_gorsel, c.cek_gorsel, kh.kh_gorsel, s.senet_gorsel) as gorsel,
                   COALESCE(bh.bh_giris, c.cek_tutar, kh.kh_giris, s.senet_tutar) as tutar,
                   -- Çek ek bilgileri
                   c.cek_seriNo as cek_seri_no,
                   c.cek_vadeTarih as cek_vade_tarih,
                   c.cek_portfoyNo as cek_portfoy_no,
                   c.cek_durum as cek_durum,
                   -- Senet ek bilgileri
                   s.senet_seriNo as senet_seri_no,
                   s.senet_vadeTarih as senet_vade_tarih,
                   s.senet_portfoyNo as senet_portfoy_no,
                   s.senet_durum as senet_durum,
                   s.senet_gorsel as senet_gorsel,
                   -- Banka ek bilgileri
                   bh.bh_belgeNumarasi as banka_belge_no,
                   bh.bh_turu as banka_turu,
                   bh.bh_bankaID as banka_id,
                   bh.bh_tarih as banka_tarih,
                   bh.bh_aciklama as banka_aciklama,
                   b.banka_bankaAd as banka_adi,
                   b.banka_hesapNo as banka_hesap_no,
                   -- Kasa ek bilgileri
                   kh.kh_belgeNumarasi as kasa_belge_no,
                   kh.kh_turu as kasa_turu,
                   kh.kh_kasaID as kasa_id,
                   kh.kh_tarih as kasa_tarih,
                   kh.kh_aciklama as kasa_aciklama,
                   k.kasa_adi as kasa_adi,
                   k.kasa_kodu as kasa_kodu,
                   CONCAT(onay_k.kullanici_ad, ' ', onay_k.kullanici_soyad) as onay_yapan_personel,
                   CASE 
                       WHEN mtd.tahsilat_tipi = 1 THEN CONCAT(bh_k.kullanici_ad, ' ', bh_k.kullanici_soyad)
                       WHEN mtd.tahsilat_tipi = 2 THEN CONCAT(c_k.kullanici_ad, ' ', c_k.kullanici_soyad)
                       WHEN mtd.tahsilat_tipi = 3 THEN CONCAT(kh_k.kullanici_ad, ' ', kh_k.kullanici_soyad)
                       WHEN mtd.tahsilat_tipi = 4 THEN CONCAT(s_k.kullanici_ad, ' ', s_k.kullanici_soyad)
                   END as islemi_yapan_personel,
                   mtd.islem_tarihi,
                   mtd.olusturma_tarihi,
                   mtd.aciklama
                FROM muhasebe_tahsilat_durum mtd
                
                -- Onay yapan personel
                LEFT JOIN kullanicilar onay_k ON mtd.islemi_yapan = onay_k.kullanici_id
                
                -- Banka hareketleri
                LEFT JOIN bankaHareketleri bh ON mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id
                LEFT JOIN kullanicilar bh_k ON bh.bh_olusturan = bh_k.kullanici_id
                LEFT JOIN cari bh_cari ON bh.bh_cariID = bh_cari.cari_id
                LEFT JOIN banka b ON bh.bh_bankaID = b.banka_id
                
                -- Çek
                LEFT JOIN cek c ON mtd.tahsilat_tipi = 2 AND mtd.kayit_id = c.cek_id
                LEFT JOIN kullanicilar c_k ON c.cek_kullaniciID = c_k.kullanici_id
                LEFT JOIN cari c_cari ON c.cek_cariID = c_cari.cari_id
                
                -- Kasa hareketleri
                LEFT JOIN kasaHareketleri kh ON mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id
                LEFT JOIN kullanicilar kh_k ON kh.kh_olusturan = kh_k.kullanici_id
                LEFT JOIN cari kh_cari ON kh.kh_cariID = kh_cari.cari_id
                LEFT JOIN kasa k ON kh.kh_kasaID = k.kasa_id
                
                -- Senet
                LEFT JOIN senet s ON mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id
                LEFT JOIN kullanicilar s_k ON s.senet_kullaniciID = s_k.kullanici_id
                LEFT JOIN cari s_cari ON s.senet_cariID = s_cari.cari_id
                
                WHERE mtd.tahsilat_tipi IN (1, 2, 3, 4)
                AND (
                    (mtd.tahsilat_tipi = 1 AND bh_cari.cari_olusturan = '$u_id') OR
                    (mtd.tahsilat_tipi = 2 AND c_cari.cari_olusturan = '$u_id') OR
                    (mtd.tahsilat_tipi = 3 AND kh_cari.cari_olusturan = '$u_id') OR
                    (mtd.tahsilat_tipi = 4 AND s_cari.cari_olusturan = '$u_id')
                )
                ORDER BY mtd.olusturma_tarihi DESC";
                
                $data["tahsilatlar"] = $this->db->query($tahsilatlarQ)->result();
                
                // İstatistikler
                $data["toplam_adet"] = count($data["tahsilatlar"]);
                
                // Toplam tutar hesapla
                $toplam_tutar = 0;
                foreach($data["tahsilatlar"] as $tahsilat) {
                    if($tahsilat->tutar) {
                        $toplam_tutar += $tahsilat->tutar;
                    }
                }
                $data["toplam_tutar"] = $toplam_tutar;
                
                // Durum bazında istatistikler
                $onay_bekleyen = 0;
                $onaylanan = 0;
                $reddedilen = 0;
                
                foreach($data["tahsilatlar"] as $tahsilat) {
                    switch($tahsilat->durum) {
                        case 1: $onay_bekleyen++; break;
                        case 2: $onaylanan++; break;
                        case 3: $reddedilen++; break;
                    }
                }
                
                $data["onay_bekleyen_adet"] = $onay_bekleyen;
                $data["onaylanan_adet"] = $onaylanan;
                $data["reddedilen_adet"] = $reddedilen;
            }
        }
        
        // Sayfa yükle
        $this->load->view('tahsilat/tahsilat-listesi', $data);
    }
    
    /**
     * Tahsilat Listesi AJAX
     * Filtrelenmiş tahsilat kayıtlarını JSON olarak döner
     */
    public function tahsilat_listesi_ajax() {
        // Yetki kontrolü
        if (!grup_modul_yetkisi_var(511)) {
            echo json_encode(['status' => 'error', 'message' => 'Yetkiniz bulunmuyor']);
            return;
        }
        
        // Filtre parametreleri
        $cari_filter = $this->input->post('cari_filter');
        $odeme_tipi_filter = $this->input->post('odeme_tipi_filter');
        $baslangic_tarih = $this->input->post('baslangic_tarih');
        $bitis_tarih = $this->input->post('bitis_tarih');
        
        try {
            // Ana sorgu - Tüm tahsilat kayıtlarını getir
            $this->db->select('t.*, c.cari_adi, k.kullanici_ad as olusturan_kullanici_adi');
            $this->db->from('tahsilat t');
            $this->db->join('cari c', 't.tahsilat_cariID = c.cari_id', 'left');
            $this->db->join('kullanicilar k', 't.tahsilat_olusturanKullanici = k.kullanici_id', 'left');
            
            // Filtreler
            if (!empty($cari_filter)) {
                $this->db->where('t.tahsilat_cariID', $cari_filter);
            }
            
            if (!empty($odeme_tipi_filter)) {
                $this->db->where('t.tahsilat_odemeTipi', $odeme_tipi_filter);
            }
            
            if (!empty($baslangic_tarih)) {
                $this->db->where('DATE(t.tahsilat_tarih) >=', $baslangic_tarih);
            }
            
            if (!empty($bitis_tarih)) {
                $this->db->where('DATE(t.tahsilat_tarih) <=', $bitis_tarih);
            }
            
            $this->db->order_by('t.tahsilat_tarih', 'DESC');
            $this->db->limit(1000); // Performans için limit
            
            $result = $this->db->get()->result_array();
            
            echo json_encode([
                'status' => 'success',
                'data' => $result,
                'message' => count($result) . ' kayıt bulundu'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Veritabanı hatası: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Tahsilat Düzenleme Sayfası
     */
    public function tahsilat_duzenle($id) {
        // Yetki kontrolü
        if (!grup_modul_yetkisi_var(511)) {
            redirect('home');
            return;
        }
        
        $data["baslik"] = "Tahsilat / Tahsilat Düzenle";
        
        // Tahsilat bilgilerini getir
        $tahsilat = $this->db->select('t.*, c.cari_adi, k.kullanici_ad as olusturan_kullanici_adi')
                             ->from('tahsilat t')
                             ->join('cari c', 't.tahsilat_cariID = c.cari_id', 'left')
                             ->join('kullanicilar k', 't.tahsilat_olusturanKullanici = k.kullanici_id', 'left')
                             ->where('t.tahsilat_id', $id)
                             ->get()
                             ->row();
        
        if (!$tahsilat) {
            redirect('tahsilat/tahsilat-listesi');
            return;
        }
        
        $data['tahsilat'] = $tahsilat;
        
        // Cari listesi
        $data['cariler'] = $this->db->select('cari_id, cari_kodu, cari_adi')
                                   ->from('cari')
                                   ->where('cari_durum', 1)
                                   ->order_by('cari_adi', 'ASC')
                                   ->get()
                                   ->result();

        // Kasa listesi
        $data['kasalar'] = $this->db->select('kasa_id, kasa_adi')
                                   ->from('kasa')
                                   ->where('kasa_durum', 1)
                                   ->order_by('kasa_adi', 'ASC')
                                   ->get()
                                   ->result();

        // Banka listesi
        $data['bankalar'] = $this->db->select('banka_id, banka_adi')
                                    ->from('banka')
                                    ->where('banka_durum', 1)
                                    ->order_by('banka_adi', 'ASC')
                                    ->get()
                                    ->result();
        
        $this->load->view('tahsilat/tahsilat-duzenle', $data);
    }
    
    /**
     * Tahsilat Detay Sayfası
     */
    public function tahsilat_detay($id) {
        // Yetki kontrolü
        if (!grup_modul_yetkisi_var(511)) {
            redirect('home');
            return;
        }
        
        $data["baslik"] = "Tahsilat / Tahsilat Detayı";
        
        // Tahsilat bilgilerini getir
        $tahsilat = $this->db->select('t.*, c.cari_adi, k.kullanici_ad as olusturan_kullanici_adi')
                             ->from('tahsilat t')
                             ->join('cari c', 't.tahsilat_cariID = c.cari_id', 'left')
                             ->join('kullanicilar k', 't.tahsilat_olusturanKullanici = k.kullanici_id', 'left')
                             ->where('t.tahsilat_id', $id)
                             ->get()
                             ->row();
        
        if (!$tahsilat) {
            redirect('tahsilat/tahsilat-listesi');
            return;
        }
        
        $data['tahsilat'] = $tahsilat;
        
        $this->load->view('tahsilat/tahsilat-detay', $data);
    }
    
    /**
     * Tahsilat Silme
     */
    public function tahsilat_sil($id) {
        // Yetki kontrolü
        if (!grup_modul_yetkisi_var(511)) {
            echo json_encode(['status' => 'error', 'message' => 'Yetkiniz bulunmuyor']);
            return;
        }
        
        try {
            $this->db->where('tahsilat_id', $id);
            $this->db->delete('tahsilat');
            
            if ($this->db->affected_rows() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Tahsilat başarıyla silindi']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Tahsilat bulunamadı']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Tahsilat Excel Export
     */
    public function tahsilat_excel_export() {
        // Yetki kontrolü
        if (!grup_modul_yetkisi_var(511)) {
            redirect('home');
            return;
        }
        
        // Filtre parametreleri
        $cari_filter = $this->input->get('cari_filter');
        $odeme_tipi_filter = $this->input->get('odeme_tipi_filter');
        $baslangic_tarih = $this->input->get('baslangic_tarih');
        $bitis_tarih = $this->input->get('bitis_tarih');
        
        try {
            // Ana sorgu
            $this->db->select('t.*, c.cari_adi, k.kullanici_ad as olusturan_kullanici_adi');
            $this->db->from('tahsilat t');
            $this->db->join('cari c', 't.tahsilat_cariID = c.cari_id', 'left');
            $this->db->join('kullanicilar k', 't.tahsilat_olusturanKullanici = k.kullanici_id', 'left');
            
            // Filtreler
            if (!empty($cari_filter)) {
                $this->db->where('t.tahsilat_cariID', $cari_filter);
            }
            
            if (!empty($odeme_tipi_filter)) {
                $this->db->where('t.tahsilat_odemeTipi', $odeme_tipi_filter);
            }
            
            if (!empty($baslangic_tarih)) {
                $this->db->where('DATE(t.tahsilat_tarih) >=', $baslangic_tarih);
            }
            
            if (!empty($bitis_tarih)) {
                $this->db->where('DATE(t.tahsilat_tarih) <=', $bitis_tarih);
            }
            
            $this->db->order_by('t.tahsilat_tarih', 'DESC');
            
            $result = $this->db->get()->result_array();
            
            // Excel dosyası oluştur
            $filename = 'tahsilat_listesi_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Basit CSV formatında çıktı (Excel uyumlu)
            $output = fopen('php://output', 'w');
            
            // UTF-8 BOM ekle
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Başlık satırı
            fputcsv($output, [
                'ID',
                'Tarih',
                'Cari',
                'Ödeme Tipi',
                'Tutar',
                'Açıklama',
                'Oluşturan'
            ], ';');
            
            // Veri satırları
            foreach ($result as $row) {
                $odeme_tipi = $this->getOdemeTipiText($row['tahsilat_odemeTipi']);
                
                fputcsv($output, [
                    $row['tahsilat_id'],
                    $row['tahsilat_tarih'],
                    $row['cari_adi'] ?: 'Bilinmiyor',
                    $odeme_tipi,
                    number_format($row['tahsilat_tutar'], 2, ',', '.') . ' ₺',
                    $row['tahsilat_aciklama'] ?: '-',
                    $row['olusturan_kullanici_adi'] ?: 'Bilinmiyor'
                ], ';');
            }
            
            fclose($output);
            
        } catch (Exception $e) {
            echo "Hata: " . $e->getMessage();
        }
    }
    
    /**
     * Tahsilat Güncelleme
     */
    public function tahsilat_guncelle() {
        try {
            if (!function_exists('grup_modul_yetkisi_var')) {
                echo 'FUNC_ERROR'; exit;
            }
            
            if (!grup_modul_yetkisi_var(511, 3)) {
                echo 'NO_AUTH'; exit;
            }

            $tip = $this->input->post('tip');
            $id = $this->input->post('id');
            $tutar = $this->input->post('tutar');
            $aciklama = $this->input->post('aciklama');
            
            // Parametreleri kontrol et
            if(empty($tip) || empty($id)) {
                echo 'MISSING_PARAMS'; exit;
            }

            $table = '';
            $id_field = '';
            $tutar_field = '';
            $aciklama_field = '';
            $gorsel_field = '';
            $gorsel_path = '';

            switch ($tip) {
                case 1: // Banka
                    $table = 'bankaHareketleri'; $id_field = 'bh_id'; $tutar_field = 'bh_giris'; $aciklama_field = 'bh_aciklama'; $gorsel_field = 'bh_gorsel'; $gorsel_path = './assets/uploads/dekontlar/';
                    break;
                case 2: // Çek
                    $table = 'cek'; $id_field = 'cek_id'; $tutar_field = 'cek_tutar'; $aciklama_field = 'cek_notAciklama'; $gorsel_field = 'cek_gorsel'; $gorsel_path = './assets/uploads/cekler/';
                    break;
                case 3: // Kasa
                    $table = 'kasahareketleri'; $id_field = 'kh_id'; $tutar_field = 'kh_giris'; $aciklama_field = 'kh_aciklama'; $gorsel_field = 'kh_gorsel'; $gorsel_path = './assets/uploads/dekontlar/';
                    break;
                case 4: // Senet
                    $table = 'senet'; $id_field = 'senet_id'; $tutar_field = 'senet_tutar'; $aciklama_field = 'senet_notAciklama'; $gorsel_field = 'senet_gorsel'; $gorsel_path = './assets/uploads/senetler/';
                    break;
                default:
                    echo 'ERROR'; exit;
            }

            // Mevcut görseli bul
            $row = $this->db->get_where($table, [$id_field => $id])->row();
            $old_gorsel = $row ? $row->$gorsel_field : null;

            // Dosya yükleme (Kasa tipi için görsel yükleme engellendi)
            if (!empty($_FILES['gorsel']['name'])) {
                file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Dosya yükleme başlıyor. Tip: $tip, Dosya: " . $_FILES['gorsel']['name'] . "\n", FILE_APPEND);
                
                // Kasa tipi (tip=3) için görsel yükleme engelle
                if ($tip == 3) {
                    file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Kasa tipi için görsel yükleme engellendi\n", FILE_APPEND);
                    echo 'KASA_GORSEL_HATASI'; exit;
                }
                
                $config['upload_path'] = $gorsel_path;
                $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf';
                $config['max_size'] = 4096;
                $config['encrypt_name'] = TRUE;
                $this->load->library('upload', $config);
                
                file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Upload config: " . json_encode($config) . "\n", FILE_APPEND);
                
                if ($this->upload->do_upload('gorsel')) {
                    $upload_data = $this->upload->data();
                    $new_gorsel = $upload_data['file_name'];
                    file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Dosya başarıyla yüklendi: $new_gorsel\n", FILE_APPEND);
                    
                    // Eski dosyayı sil
                    if ($old_gorsel) {
                        // Eski görsel JSON formatında ise decode et
                        $old_gorsel_decoded = json_decode($old_gorsel, true);
                        $old_file = is_array($old_gorsel_decoded) ? $old_gorsel_decoded[0] : $old_gorsel;
                        if ($old_file && file_exists($gorsel_path . $old_file)) {
                            @unlink($gorsel_path . $old_file);
                            file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Eski dosya silindi: $old_file\n", FILE_APPEND);
                        }
                    }
                } else {
                    $upload_errors = $this->upload->display_errors();
                    file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Upload hatası: $upload_errors\n", FILE_APPEND);
                    $new_gorsel = $old_gorsel;
                }
            } else {
                file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Yeni dosya yüklenmedi, mevcut görsel korunuyor\n", FILE_APPEND);
                $new_gorsel = $old_gorsel;
            }
            
            // Görsel alanını doğru formatta kaydet (string olarak, JSON array değil)
            // Eğer mevcut değer JSON array formatındaysa decode et
            if ($new_gorsel && !is_null($new_gorsel) && $new_gorsel !== '') {
                $decoded = json_decode($new_gorsel, true);
                if (is_array($decoded) && count($decoded) > 0) {
                    // JSON array formatından string'e çevir
                    $new_gorsel = $decoded[0];
                }
                // Dosya adı string formatında olmalı, JSON array formatında değil
            }

            $update = [
                $tutar_field => $tutar,
                $aciklama_field => $aciklama,
                $gorsel_field => $new_gorsel
            ];
            
            file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Update verisi: " . json_encode($update) . "\n", FILE_APPEND);
            file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Tablo: $table, ID field: $id_field, ID: $id\n", FILE_APPEND);
            
            $this->db->where($id_field, $id)->update($table, $update);

            if ($this->db->affected_rows() > 0) {
                // Ana tablo güncellendikten sonra carihareketleri tablosunu da güncelle
                $this->updateCariHareketleri($tip, $id, $tutar);
                
                file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Başarılı, Response: OK\n", FILE_APPEND);
                echo 'OK';
            } else {
                // Kayıt bulunamadı detayları
                $check_record = $this->db->get_where($table, [$id_field => $id])->row();
                if ($check_record) {
                    file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Kayıt var ama güncelleme yapılmadı. Response: ERROR_NO_UPDATE\n", FILE_APPEND);
                    echo 'ERROR_NO_UPDATE';
                } else {
                    file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Kayıt bulunamadı: $table tablosunda $id_field = $id. Response: ERROR_NOT_FOUND\n", FILE_APPEND);
                    echo 'ERROR_NOT_FOUND';
                }
            }
        } catch (Exception $e) {
            file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            echo 'ERROR: ' . $e->getMessage();
        } catch (Error $e) {
            file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - Fatal Error: " . $e->getMessage() . "\n", FILE_APPEND);
            echo 'FATAL_ERROR: ' . $e->getMessage();
        }
        exit;
    }
    
    /**
     * CariHareketleri tablosunu güncelle
     */
    private function updateCariHareketleri($tip, $id, $tutar) {
        try {
            $ch_field = '';
            $source_table = '';
            
            // Tip'e göre carihareketleri tablosundaki foreign key alanını ve kaynak tabloyu belirle
            switch ($tip) {
                case 1: // Banka
                    $ch_field = 'ch_bankaID';
                    $source_table = 'bankaHareketleri';
                    $source_id_field = 'bh_id';
                    $source_belge_field = 'bh_belgeNumarasi';
                    break;
                case 2: // Çek
                    $ch_field = 'ch_cekID';
                    $source_table = 'cek';
                    $source_id_field = 'cek_id';
                    $source_belge_field = 'cek_belgeNumarasi';
                    break;
                case 3: // Kasa
                    $ch_field = 'ch_khID';
                    $source_table = 'kasahareketleri';
                    $source_id_field = 'kh_id';
                    $source_belge_field = 'kh_belgeNumarasi';
                    break;
                case 4: // Senet
                    $ch_field = 'ch_senetID';
                    $source_table = 'senet';
                    $source_id_field = 'senet_id';
                    $source_belge_field = 'senet_belgeNumarasi';
                    break;
                default:
                    file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - CariHareketleri güncelleme: Geçersiz tip: $tip\n", FILE_APPEND);
                    return;
            }
            
            // Önce foreign key ile eşleşen kayıtları güncellemeyi dene
            $update_data = [
                'ch_alacak' => $tutar
            ];
            
            $this->db->where($ch_field, $id)->update('carihareketleri', $update_data);
            $affected_rows = $this->db->affected_rows();
            
            // Eğer foreign key ile eşleşme yoksa, belge numarasına göre eşleştirmeyi dene
            if ($affected_rows == 0) {
                // Kaynak tablodan belge numarasını al
                $source_record = $this->db->get_where($source_table, [$source_id_field => $id])->row();
                
                if ($source_record && !empty($source_record->$source_belge_field)) {
                    // Belge numarasına göre carihareketleri tablosunu güncelle
                    $this->db->where('ch_belgeNumarasi', $source_record->$source_belge_field);
                    $this->db->update('carihareketleri', $update_data);
                    $affected_rows = $this->db->affected_rows();
                    
                    // Aynı zamanda ch_khID alanını da güncelle
                    if ($affected_rows > 0) {
                        $this->db->where('ch_belgeNumarasi', $source_record->$source_belge_field);
                        $this->db->update('carihareketleri', [$ch_field => $id]);
                        
                        file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - CariHareketleri belge numarasına göre güncellendi: $affected_rows satır. Belge: {$source_record->$source_belge_field}, $ch_field = $id ayarlandı\n", FILE_APPEND);
                    }
                }
            }
            
            file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - CariHareketleri güncelleme: $affected_rows satır güncellendi. Field: $ch_field, ID: $id, Tutar: $tutar\n", FILE_APPEND);
            
        } catch (Exception $e) {
            file_put_contents('./tahsilat_debug.log', date('Y-m-d H:i:s') . " - CariHareketleri güncelleme hatası: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
    
    /**
     * Ödeme tipi metni döndür
     */
    private function getOdemeTipiText($tip) {
        switch($tip) {
            case '1': return 'Kasa';
            case '2': return 'Banka / POS';
            case '3': return 'Çek';
            case '4': return 'Senet';
            default: return 'Bilinmiyor';
        }
    }
    
    /**
     * Çek tablosundaki kayıtları muhasebe_tahsilat_durum tablosuna senkronize et
     */
    private function senkronizeCekler()
    {
        try {
            // Muhasebe tablosunda olmayan çekleri bul
            $ceklerQ = "SELECT c.cek_id 
                        FROM cek c 
                        LEFT JOIN muhasebe_tahsilat_durum mtd ON mtd.tahsilat_tipi = 2 AND mtd.kayit_id = c.cek_id 
                        WHERE mtd.id IS NULL";
            
            $cekler = $this->db->query($ceklerQ)->result();
            
            // Her çek için muhasebe tablosuna kayıt ekle
            foreach($cekler as $cek) {
                $data = array(
                    'tahsilat_tipi' => 2, // 2 = Çek
                    'kayit_id' => $cek->cek_id,
                    'durum' => 1, // 1 = Onay bekliyor
                    'olusturma_tarihi' => date('Y-m-d H:i:s'),
                    'aciklama' => 'Çek tablosundan otomatik senkronize edildi'
                );
                
                $this->db->insert('muhasebe_tahsilat_durum', $data);
            }
            
            if(count($cekler) > 0) {
                error_log("Muhasebe senkronizasyonu: " . count($cekler) . " çek kaydı eklendi.");
            }
            
        } catch (Exception $e) {
            error_log("Çek senkronizasyonu hatası: " . $e->getMessage());
        }
    }
    
    /**
     * Banka hareketleri tablosundaki kayıtları muhasebe_tahsilat_durum tablosuna senkronize et
     */
    private function senkronizeBankaHareketleri()
    {
        try {
            // Muhasebe tablosunda olmayan banka hareketlerini bul (sadece giriş hareketleri - tahsilat)
            $bankaHareketleriQ = "SELECT bh.bh_id 
                        FROM bankaHareketleri bh 
                        LEFT JOIN muhasebe_tahsilat_durum mtd ON mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id 
                        WHERE mtd.id IS NULL AND bh.bh_giris > 0";
            
            $bankaHareketleri = $this->db->query($bankaHareketleriQ)->result();
            
            // Her banka hareketi için muhasebe tablosuna kayıt ekle
            foreach($bankaHareketleri as $hareket) {
                $data = array(
                    'tahsilat_tipi' => 1, // 1 = Banka
                    'kayit_id' => $hareket->bh_id,
                    'durum' => 1, // 1 = Onay bekliyor
                    'olusturma_tarihi' => date('Y-m-d H:i:s'),
                    'aciklama' => 'Banka hareketleri tablosundan otomatik senkronize edildi'
                );
                
                $this->db->insert('muhasebe_tahsilat_durum', $data);
            }
            
            if(count($bankaHareketleri) > 0) {
                error_log("Muhasebe senkronizasyonu: " . count($bankaHareketleri) . " banka hareketi kaydı eklendi.");
            }
            
        } catch (Exception $e) {
            error_log("Banka hareketleri senkronizasyonu hatası: " . $e->getMessage());
        }
    }
    
    /**
     * Kasa hareketleri tablosundaki kayıtları muhasebe_tahsilat_durum tablosuna senkronize et
     * Açılış bakiyesi kayıtları hariç tutulur
     */
    private function senkronizeKasaHareketleri()
    {
        try {
            // Muhasebe tablosunda olmayan kasa hareketlerini bul (sadece giriş hareketleri - tahsilat)
            // Açılış bakiyesi kayıtlarını hariç tut
            $kasaHareketleriQ = "SELECT kh.kh_id 
                        FROM kasaHareketleri kh 
                        LEFT JOIN muhasebe_tahsilat_durum mtd ON mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id 
                        WHERE mtd.id IS NULL 
                        AND kh.kh_giris > 0 
                        AND (kh.kh_aciklama IS NULL OR kh.kh_aciklama NOT LIKE '%Açılış Bakiyesi%')
                        AND (kh.kh_aciklama IS NULL OR kh.kh_aciklama NOT LIKE '%Otomatik Oluşturulan%')";
            
            $kasaHareketleri = $this->db->query($kasaHareketleriQ)->result();
            
            // Her kasa hareketi için muhasebe tablosuna kayıt ekle
            foreach($kasaHareketleri as $hareket) {
                $data = array(
                    'tahsilat_tipi' => 3, // 3 = Kasa
                    'kayit_id' => $hareket->kh_id,
                    'durum' => 1, // 1 = Onay bekliyor
                    'olusturma_tarihi' => date('Y-m-d H:i:s'),
                    'aciklama' => 'Kasa hareketleri tablosundan otomatik senkronize edildi'
                );
                
                $this->db->insert('muhasebe_tahsilat_durum', $data);
            }
            
            if(count($kasaHareketleri) > 0) {
                error_log("Muhasebe senkronizasyonu: " . count($kasaHareketleri) . " kasa hareketi kaydı eklendi.");
            }
            
        } catch (Exception $e) {
            error_log("Kasa hareketleri senkronizasyonu hatası: " . $e->getMessage());
        }
    }
    
    /**
     * Senet tablosundaki kayıtları muhasebe_tahsilat_durum tablosuna senkronize et
     */
    private function senkronizeSenetler()
    {
        try {
            // Muhasebe tablosunda olmayan senetleri bul
            $senetlerQ = "SELECT s.senet_id 
                        FROM senet s 
                        LEFT JOIN muhasebe_tahsilat_durum mtd ON mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id 
                        WHERE mtd.id IS NULL";
            
            $senetler = $this->db->query($senetlerQ)->result();
            
            // Her senet için muhasebe tablosuna kayıt ekle
            foreach($senetler as $senet) {
                $data = array(
                    'tahsilat_tipi' => 4, // 4 = Senet
                    'kayit_id' => $senet->senet_id,
                    'durum' => 1, // 1 = Onay bekliyor
                    'olusturma_tarihi' => date('Y-m-d H:i:s'),
                    'aciklama' => 'Senet tablosundan otomatik senkronize edildi'
                );
                
                $this->db->insert('muhasebe_tahsilat_durum', $data);
            }
            
            if(count($senetler) > 0) {
                error_log("Muhasebe senkronizasyonu: " . count($senetler) . " senet kaydı eklendi.");
            }
            
        } catch (Exception $e) {
            error_log("Senet senkronizasyonu hatası: " . $e->getMessage());
        }
    }
}
