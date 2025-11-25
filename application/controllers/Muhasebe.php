<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Muhasebe extends CI_Controller {

		public function __construct()

	{

		parent::__construct();

		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

		$this->load->model('vt');

		$this->load->database();

		$this->load->library('session');

		$this->load->helper('general');



		$control = session("r", "login");



		if (!$control) {

			redirect("check");

		}



		// Session kontrolü - varsa aktifleştir

		// sessionKontrolHelper();

	}	public function onayBekleyenTahsilatlar()

	{

		// Memory limit artır (performans için)

		ini_set('memory_limit', '512M');

		ini_set('max_execution_time', 600);

		

		$data["baslik"] = "Muhasebe / Onay Bekleyen Tahsilatlar";

		$anaHesap = anaHesapBilgisi();

				// Giriş yapan kullanıcı bilgisi

		$control = session("r", "login_info");

		if (!$control || !isset($control->kullanici_id)) {

			redirect(base_url('check'));

			return;

		}

		$u_id = $control->kullanici_id;

		

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

				$data["toplam_adet"] = 0;				$data["error_message"] = "Eksik tablolar tespit edildi: " . implode(', ', $missingTables) . ". Lütfen veritabanı kurulumunu tamamlayın.";

			} else {

				// Detaylı sorgu - Gerçek verilerle JOIN'ler

				$tahsilatlarQ = "SELECT DISTINCT

				   mtd.id,

				   mtd.tahsilat_tipi,

				   mtd.kayit_id,

				   mtd.durum,

				   mtd.islem_tarihi,

				   mtd.olusturma_tarihi,

				   mtd.aciklama,

				   CASE 

					   WHEN mtd.tahsilat_tipi = 1 THEN 'Banka Hareketi'

					   WHEN mtd.tahsilat_tipi = 2 THEN 'Çek'

					   WHEN mtd.tahsilat_tipi = 3 THEN 'Kasa Hareketi'

					   WHEN mtd.tahsilat_tipi = 4 THEN 'Senet'

					   ELSE 'Bilinmiyor'

				   END as tahsilat_tipi_adi,

				   CASE 

					   WHEN mtd.durum = 1 THEN 'Onay Bekliyor'

					   WHEN mtd.durum = 2 THEN 'Onaylandı'

					   WHEN mtd.durum = 3 THEN 'Reddedildi'

					   ELSE 'Bilinmiyor'

				   END as durum_adi,

				   COALESCE(c.cari_ad, c.cari_soyad, 'Bilinmiyor') as musteri_adi,

				   CASE 

					   WHEN mtd.tahsilat_tipi = 1 THEN bh.bh_gorsel

					   WHEN mtd.tahsilat_tipi = 2 THEN ck.cek_gorsel

					   WHEN mtd.tahsilat_tipi = 3 THEN kh.kh_gorsel

					   WHEN mtd.tahsilat_tipi = 4 THEN s.senet_gorsel

					   ELSE NULL

				   END as gorsel,

				   CASE 

					   WHEN mtd.tahsilat_tipi = 1 THEN COALESCE(bh.bh_giris, bh.bh_cikis, 0)

					   WHEN mtd.tahsilat_tipi = 2 THEN COALESCE(ck.cek_tutar, 0)

					   WHEN mtd.tahsilat_tipi = 3 THEN COALESCE(kh.kh_giris, kh.kh_cikis, 0)

					   WHEN mtd.tahsilat_tipi = 4 THEN COALESCE(s.senet_tutar, 0)

					   ELSE 0

				   END as tutar,

				   COALESCE(CONCAT(onay_personel.kullanici_ad, ' ', onay_personel.kullanici_soyad), onay_personel.kullanici_ad, 'Bilinmiyor') as onay_yapan_personel,

				   COALESCE(CONCAT(islem_personel.kullanici_ad, ' ', islem_personel.kullanici_soyad), islem_personel.kullanici_ad, 'Bilinmiyor') as islemi_yapan_personel,

				   (SELECT DISTINCT sg.stokGrup_ad 
					FROM satisFaturasi sf2
					JOIN satisFaturasiStok sfs2 ON sf2.satis_id = sfs2.satisStok_satisFaturasiID
					JOIN stok st2 ON sfs2.satisStok_stokID = st2.stok_id
					JOIN stokGruplari sg ON st2.stok_stokGrupKoduID = sg.stokGrup_id
					WHERE sf2.satis_cariID = c.cari_id
					LIMIT 1
				   ) as hizmet

				FROM muhasebe_tahsilat_durum mtd

				LEFT JOIN bankaHareketleri bh ON (mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id)

				LEFT JOIN cek ck ON (mtd.tahsilat_tipi = 2 AND mtd.kayit_id = ck.cek_id)

				LEFT JOIN kasaHareketleri kh ON (mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id)

				LEFT JOIN senet s ON (mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id)

				LEFT JOIN cari c ON (

					(mtd.tahsilat_tipi = 1 AND c.cari_id = bh.bh_cariID) OR

					(mtd.tahsilat_tipi = 2 AND c.cari_id = ck.cek_cariID) OR

					(mtd.tahsilat_tipi = 3 AND c.cari_id = kh.kh_cariID) OR

					(mtd.tahsilat_tipi = 4 AND c.cari_id = s.senet_cariID)

				)

				LEFT JOIN kullanicilar onay_personel ON mtd.islemi_yapan = onay_personel.kullanici_id

				LEFT JOIN kullanicilar islem_personel ON (

					(mtd.tahsilat_tipi = 1 AND islem_personel.kullanici_id = bh.bh_olusturan) OR

					(mtd.tahsilat_tipi = 2 AND islem_personel.kullanici_id = ck.cek_kullaniciID) OR

					(mtd.tahsilat_tipi = 3 AND islem_personel.kullanici_id = kh.kh_olusturan) OR

					(mtd.tahsilat_tipi = 4 AND islem_personel.kullanici_id = s.senet_kullaniciID)

				)

				WHERE mtd.durum = 1 AND mtd.tahsilat_tipi IN (1, 2, 3, 4)

				ORDER BY mtd.olusturma_tarihi DESC";

			

			// Veritabanı sorgusunu try-catch ile çalıştır

			try {

				$result = $this->db->query($tahsilatlarQ);

				if ($result) {

					$data["tahsilatlar"] = $result->result();

				} else {

					throw new Exception("Veritabanı sorgusu başarısız oldu.");

				}

			} catch (Exception $e) {

				$data["tahsilatlar"] = array();

				$data["error_message"] = "Veritabanı hatası: " . $e->getMessage();

				error_log("Muhasebe onay bekleyen tahsilatlar sorgu hatası: " . $e->getMessage());

			}

			

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

			}

		}

		

		// Debug - Veri kontrolü

		if (!isset($data["tahsilatlar"])) {

			$data["tahsilatlar"] = array();

			$data["toplam_tutar"] = 0;

			$data["toplam_adet"] = 0;

			$data["error_message"] = "Tahsilat verileri yüklenemedi.";

		}

		

		// Sayfa yükle - Try-catch ile hata yakalama

		try {

			$this->load->view("muhasebe/onay-bekleyen-tahsilatlar", $data);

		} catch (Exception $e) {

			// Hata durumunda basit hata sayfası göster

			echo "<h3>Sayfa Yükleme Hatası</h3>";

			echo "<p>Hata: " . $e->getMessage() . "</p>";

			echo "<p><a href='" . base_url('muhasebe') . "'>Muhasebe Ana Sayfaya Dön</a></p>";

		}

	}

	

	public function tahsilatOnay($tahsilat_id)

	{

		// Memory limit artır (performans için)

		ini_set('memory_limit', '256M');

		ini_set('max_execution_time', 300);

		

		$anaHesap = anaHesapBilgisi();

		$control = session("r", "login_info");

		$u_id = $control->kullanici_id;

		

		// Muhasebe tahsilat durum tablosundan bilgiyi kontrol et

		$tahsilatQ = "SELECT * FROM muhasebe_tahsilat_durum WHERE id = '$tahsilat_id'";

		$tahsilat = $this->db->query($tahsilatQ)->row();

		

		if ($tahsilat && $tahsilat->durum == 1) { // Sadece onay bekleyen durumda olanlar

			// Görsel yükleme işlemi

			$gorsel_path = null;

			$upload_error = null;

			

			if (!empty($_FILES['tahsilat_gorsel']['name'])) {

				$upload_path = FCPATH . 'assets/uploads/';

				if (!is_dir($upload_path)) {

					mkdir($upload_path, 0777, true);

				}

				

				// Dosya uzantısını kontrol et

				$file_extension = strtolower(pathinfo($_FILES['tahsilat_gorsel']['name'], PATHINFO_EXTENSION));

				$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

				

				if (in_array($file_extension, $allowed_extensions)) {

					// Dosya adını oluştur

					$file_name = 'tahsilat_' . $tahsilat_id . '_' . time() . '.' . $file_extension;

					$full_path = $upload_path . $file_name;

					

					// Dosya boyutunu kontrol et (5MB = 5242880 bytes)

					if ($_FILES['tahsilat_gorsel']['size'] <= 5242880) {

						if (move_uploaded_file($_FILES['tahsilat_gorsel']['tmp_name'], $full_path)) {

							$gorsel_path = $file_name; // Sadece dosya adını saklıyoruz

						} else {

							$upload_error = "Dosya yükleme sırasında hata oluştu.";

						}

					} else {

						$upload_error = "Dosya boyutu 5MB'dan büyük olamaz.";

					}

				} else {

					$upload_error = "Sadece JPG, JPEG, PNG ve GIF formatları desteklenmektedir.";

				}

			}

			

			// Onay işlemi

			$data = array(

				'durum' => 2, // 2 = Onaylandı

				'islem_tarihi' => date('Y-m-d H:i:s'),

				'islemi_yapan' => $u_id

			);

			

			// Görsel varsa ekle

			if ($gorsel_path) {

				$data['gorsel'] = $gorsel_path;

			}

			

			// Açıklama varsa ekle veya mevcut açıklamayı koru

			$aciklama = $this->input->post('aciklama');

			if (!empty($aciklama)) {

				// Mevcut açıklama varsa ona ekle, yoksa yeni açıklama yap

				$mevcut_aciklama = $tahsilat->aciklama ? $tahsilat->aciklama . "\n\n" : "";

				$data['aciklama'] = $mevcut_aciklama . "Onay Açıklaması: " . $aciklama;

			}

			

			$this->db->where('id', $tahsilat_id);

			$this->db->update('muhasebe_tahsilat_durum', $data);

			

			if ($this->db->affected_rows() > 0) {

				$success_message = 'Tahsilat başarıyla onaylandı.';

				if ($gorsel_path) {

					$success_message .= ' Görsel yüklendi.';

				}

				if ($upload_error) {

					$success_message .= ' Ancak: ' . $upload_error;

				}

				$this->session->set_flashdata('tahsilat_onay_ok', $success_message);

			} else {

				$this->session->set_flashdata('tahsilat_onay_hata', 'Tahsilat onaylama işlemi sırasında hata oluştu.');

			}

			

			logekle(1, 3); // Log ekle

		} else {

			$this->session->set_flashdata('tahsilat_onay_hata', 'Geçersiz tahsilat veya tahsilat zaten işlenmiş.');

		}

		

		redirect("muhasebe/tahsilat-listesi");

	}

	

	public function tahsilatRed($tahsilat_id)

	{

		// Memory limit artır (performans için)

		ini_set('memory_limit', '256M');

		ini_set('max_execution_time', 300);

		

		$anaHesap = anaHesapBilgisi();

		$control = session("r", "login_info");

		$u_id = $control->kullanici_id;

		

		// Muhasebe tahsilat durum tablosundan bilgiyi kontrol et

		$tahsilatQ = "SELECT * FROM muhasebe_tahsilat_durum WHERE id = '$tahsilat_id'";

		$tahsilat = $this->db->query($tahsilatQ)->row();

		

		if ($tahsilat && $tahsilat->durum == 1) { // Sadece onay bekleyen durumda olanlar

			// Red işlemi

			$data = array(

				'durum' => 3, // 3 = Reddedildi

				'islem_tarihi' => date('Y-m-d H:i:s'),

				'islemi_yapan' => $u_id

			);

			

			$this->db->where('id', $tahsilat_id);

			$this->db->update('muhasebe_tahsilat_durum', $data);

			

			$this->session->set_flashdata('tahsilat_red_ok', 'OK');

			logekle(1, 3); // Log ekle

		} else {			$this->session->set_flashdata('tahsilat_red_hata', 'OK');

		}

		

		redirect("muhasebe/tahsilat-listesi");

	}

	public function tahsilatGeriAl($tahsilat_id)
	{
		// Memory limit artır (performans için)
		ini_set('memory_limit', '256M');
		ini_set('max_execution_time', 300);
		
		$anaHesap = anaHesapBilgisi();
		$control = session("r", "login_info");
		if (!$control || !isset($control->kullanici_id)) {
			redirect(base_url('check'));
			return;
		}
		$u_id = $control->kullanici_id;
		
		// Muhasebe tahsilat durum tablosundan bilgiyi kontrol et
		$tahsilatQ = "SELECT * FROM muhasebe_tahsilat_durum WHERE id = '$tahsilat_id'";
		$tahsilat = $this->db->query($tahsilatQ)->row();
		
		if ($tahsilat && $tahsilat->durum == 2) { // Sadece onaylanmış durumda olanlar geri alınabilir
			// Geri alma işlemi - sadece durumu onay bekliyor'a çevir
			$tarih = date('Y-m-d H:i:s');
			$mevcut_aciklama = $tahsilat->aciklama ? $tahsilat->aciklama : '';
			$yeni_aciklama = $mevcut_aciklama . "\n\n[GERI ALMA] " . $tarih . " tarihinde kullanıcı tarafından geri alındı.";
			
			$data = array(
				'durum' => 1, // 1 = Onay bekliyor
				'aciklama' => $yeni_aciklama
			);
			
			$this->db->where('id', $tahsilat_id);
			$this->db->update('muhasebe_tahsilat_durum', $data);
			
			if ($this->db->affected_rows() > 0) {
				$this->session->set_flashdata('tahsilat_geri_al_ok', 'Tahsilat başarıyla geri alındı.');
			} else {
				$this->session->set_flashdata('tahsilat_geri_al_hata', 'Tahsilat geri alma işlemi sırasında hata oluştu.');
			}
			
			logekle(1, 3); // Log ekle
		} else {
			$this->session->set_flashdata('tahsilat_geri_al_hata', 'Geçersiz tahsilat veya tahsilat zaten onaylanmamış durumda.');
		}
		
		redirect("muhasebe/tahsilat-listesi");
	}

	public function tahsilatDetay($tahsilat_id)

	{

		// Memory limit artır (performans için)

		ini_set('memory_limit', '256M');

		ini_set('max_execution_time', 300);

		

		$anaHesap = anaHesapBilgisi();

		

		// Muhasebe tahsilat durum tablosundan detayı getir

		$tahsilatQ = "SELECT 

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

		   COALESCE(bh_cari.cari_kodu, c_cari.cari_kodu, kh_cari.cari_kodu, s_cari.cari_kodu) as musteri_kodu,

		   COALESCE(bh_cari.cari_firmaTelefon, c_cari.cari_firmaTelefon, kh_cari.cari_firmaTelefon, s_cari.cari_firmaTelefon) as musteri_telefon,

		   COALESCE(bh.bh_gorsel, c.cek_gorsel, kh.kh_gorsel, s.senet_gorsel) as gorsel,

		   COALESCE(bh.bh_giris, c.cek_tutar, kh.kh_giris, s.senet_tutar) as tutar,

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

		

		-- Senet

		LEFT JOIN senet s ON mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id

		LEFT JOIN kullanicilar s_k ON s.senet_kullaniciID = s_k.kullanici_id

		LEFT JOIN cari s_cari ON s.senet_cariID = s_cari.cari_id

		

		WHERE mtd.id = '$tahsilat_id'";

		

		$data["tahsilat"] = $this->db->query($tahsilatQ)->row();

		

		if ($data["tahsilat"]) {

			$data["baslik"] = "Muhasebe / Tahsilat Detayı";

			$this->load->view("muhasebe/tahsilat-detay", $data);

		} else {

			redirect('hata');

		}

	}

		public function tahsilatListesi()

	{

		// Memory limit artır (performans için)

		ini_set('memory_limit', '256M');

		ini_set('max_execution_time', 300);

		

		$data["baslik"] = "Muhasebe / Tahsilat Listesi";

		$anaHesap = anaHesapBilgisi();

		

		// Giriş yapan kullanıcı bilgisi

		$control = session("r", "login_info");

		if (!$control || !isset($control->kullanici_id)) {

			redirect(base_url('check'));

			return;

		}

		$u_id = $control->kullanici_id;

		

		// Filtre parametrelerini al

		$filtre_tahsilat_tipi = $this->input->get('tahsilat_tipi');

		$filtre_durum = $this->input->get('durum');

		$filtre_personel = $this->input->get('personel');

		$filtre_baslangic_tarih = $this->input->get('baslangic_tarih');

		$filtre_bitis_tarih = $this->input->get('bitis_tarih');

		

		// Özel sayfa kontrolü: Çek ve Senet Ödeme Beklenen Tahsilatlar

		$ozel_sayfa = false;

		if (is_array($filtre_tahsilat_tipi) && 

		    count($filtre_tahsilat_tipi) == 2 && 

		    in_array('2', $filtre_tahsilat_tipi) && 

		    in_array('4', $filtre_tahsilat_tipi) && 

		    $filtre_durum == '1' && 

		    empty($filtre_personel) && 

		    empty($filtre_bitis_tarih)) {

			$ozel_sayfa = true;

			// Eğer başlangıç tarihi boş ise bugünün tarihini ata

			if (empty($filtre_baslangic_tarih)) {

				$filtre_baslangic_tarih = date('Y-m-d');

			}

		}

		

		// Tahsilat tipi parametresini array olarak işle (multi select için)

		if (is_array($filtre_tahsilat_tipi)) {

			// Zaten array ise doğrudan kullan

			$tahsilat_tipi_array = array_map('intval', $filtre_tahsilat_tipi);

		} elseif (!empty($filtre_tahsilat_tipi)) {

			// Tek değer ise array'e çevir (geriye dönük uyumluluk için)

			$tahsilat_tipi_array = [intval($filtre_tahsilat_tipi)];

		} else {

			// Boş ise null

			$tahsilat_tipi_array = null;

		}

		

		// Filtreleri data'ya aktar

		$data['filtre_tahsilat_tipi'] = $filtre_tahsilat_tipi;

		$data['filtre_durum'] = $filtre_durum;

		$data['filtre_personel'] = $filtre_personel;

		$data['filtre_baslangic_tarih'] = $filtre_baslangic_tarih;

		$data['filtre_bitis_tarih'] = $filtre_bitis_tarih;

		$data['ozel_sayfa'] = $ozel_sayfa;

		

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

				// Ana sorgu: Muhasebe tahsilat durum tablosundan tüm tahsilatları çek (durum filtresi yok)

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

				   mtd.gorsel as onay_gorsel,

				   COALESCE(bh_cari.cari_ad, c_cari.cari_ad, kh_cari.cari_ad, s_cari.cari_ad) as musteri_adi,

				   COALESCE(bh.bh_gorsel, c.cek_gorsel, kh.kh_gorsel, s.senet_gorsel) as tahsilat_gorsel,

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

				

				-- Çek (ek dosyasından gelen yapıya göre güncellenmiş)

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

				ORDER BY mtd.olusturma_tarihi DESC";

				

				// Filtre koşullarını WHERE clause'a ekle

				$where_conditions = array();

				

				// Tahsilat tipi filtresi (multi select)

				if (!empty($tahsilat_tipi_array)) {

					$tahsilat_tipi_values = implode(',', $tahsilat_tipi_array);

					$where_conditions[] = "mtd.tahsilat_tipi IN ($tahsilat_tipi_values)";

				}

				

				// Durum filtresi

				if (!empty($filtre_durum)) {

					$where_conditions[] = "mtd.durum = " . intval($filtre_durum);

				}

				

				// Personel filtresi (İşlemi yapan personel)

				if (!empty($filtre_personel)) {

					$where_conditions[] = "(

						(mtd.tahsilat_tipi = 1 AND bh.bh_olusturan = " . intval($filtre_personel) . ") OR

						(mtd.tahsilat_tipi = 2 AND c.cek_kullaniciID = " . intval($filtre_personel) . ") OR

						(mtd.tahsilat_tipi = 3 AND kh.kh_olusturan = " . intval($filtre_personel) . ") OR

						(mtd.tahsilat_tipi = 4 AND s.senet_kullaniciID = " . intval($filtre_personel) . ")

					)";

				}

				

				// Tarih aralığı filtresi

				if (!empty($filtre_baslangic_tarih) || !empty($filtre_bitis_tarih)) {

					$tarih_conditions = array();

					

					if (!empty($filtre_baslangic_tarih)) {

						$baslangic = $this->db->escape($filtre_baslangic_tarih);

						$tarih_conditions[] = "(

							(mtd.tahsilat_tipi = 1 AND bh.bh_tarih >= $baslangic) OR

							(mtd.tahsilat_tipi = 2 AND c.cek_vadeTarih >= $baslangic) OR

							(mtd.tahsilat_tipi = 3 AND kh.kh_tarih >= $baslangic) OR

							(mtd.tahsilat_tipi = 4 AND s.senet_vadeTarih >= $baslangic)

						)";

					}

					

					if (!empty($filtre_bitis_tarih)) {

						$bitis = $this->db->escape($filtre_bitis_tarih);

						$tarih_conditions[] = "(

							(mtd.tahsilat_tipi = 1 AND bh.bh_tarih <= $bitis) OR

							(mtd.tahsilat_tipi = 2 AND c.cek_vadeTarih <= $bitis) OR

							(mtd.tahsilat_tipi = 3 AND kh.kh_tarih <= $bitis) OR

							(mtd.tahsilat_tipi = 4 AND s.senet_vadeTarih <= $bitis)

						)";

					}

					

					if (!empty($tarih_conditions)) {

						$where_conditions[] = "(" . implode(' AND ', $tarih_conditions) . ")";

					}

				}

				

				// WHERE koşullarını sorguya ekle

				if (!empty($where_conditions)) {

					$tahsilatlarQ = str_replace(

						"WHERE mtd.tahsilat_tipi IN (1, 2, 3, 4)",

						"WHERE mtd.tahsilat_tipi IN (1, 2, 3, 4) AND (" . implode(' AND ', $where_conditions) . ")",

						$tahsilatlarQ

					);

				}

				

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

				

				// Detaylı istatistikler

				$durum_stats = [

					'bekleyen' => 0,

					'tamamlanan' => 0,

					'reddedilen' => 0

				];

				

				$tip_stats = [

					'senet' => ['adet' => 0, 'tutar' => 0],

					'cek' => ['adet' => 0, 'tutar' => 0],

					'banka' => ['adet' => 0, 'tutar' => 0],

					'kasa' => ['adet' => 0, 'tutar' => 0]

				];

				

				$vadesi_gecen = ['adet' => 0, 'tutar' => 0];

				$bugun = date('Y-m-d');

				

				foreach($data["tahsilatlar"] as $tahsilat) {

					// Durum bazında sayım

					switch($tahsilat->durum) {

						case 1: $durum_stats['bekleyen']++; break;

						case 2: $durum_stats['tamamlanan']++; break;

						case 3: $durum_stats['reddedilen']++; break;

					}

					

					// Tip bazında sayım ve tutar

					$tutar = (float)$tahsilat->tutar ?: 0;

					

					switch($tahsilat->tahsilat_tipi) {

						case 1: // Banka

							$tip_stats['banka']['adet']++;

							$tip_stats['banka']['tutar'] += $tutar;

							break;

						case 2: // Çek

							$tip_stats['cek']['adet']++;

							$tip_stats['cek']['tutar'] += $tutar;

							

							// Vadesi geçen çekler

							if(isset($tahsilat->cek_vade_tarih) && $tahsilat->cek_vade_tarih && $tahsilat->cek_vade_tarih < $bugun && $tahsilat->durum != 2) {

								$vadesi_gecen['adet']++;

								$vadesi_gecen['tutar'] += $tutar;

							}

							break;

						case 3: // Kasa

							$tip_stats['kasa']['adet']++;

							$tip_stats['kasa']['tutar'] += $tutar;

							break;

						case 4: // Senet

							$tip_stats['senet']['adet']++;

							$tip_stats['senet']['tutar'] += $tutar;

							

							// Vadesi geçen senetler

							if(isset($tahsilat->senet_vade_tarih) && $tahsilat->senet_vade_tarih && $tahsilat->senet_vade_tarih < $bugun && $tahsilat->durum != 2) {

								$vadesi_gecen['adet']++;

								$vadesi_gecen['tutar'] += $tutar;

							}

							break;

					}

				}

				

				// İstatistikleri data'ya ekle

				$data["durum_stats"] = $durum_stats;

				$data["tip_stats"] = $tip_stats;

				$data["vadesi_gecen"] = $vadesi_gecen;

				

				// Eski format uyumluluğu için

				$data["onay_bekleyen_adet"] = $durum_stats['bekleyen'];

				$data["onaylanan_adet"] = $durum_stats['tamamlanan'];

				$data["reddedilen_adet"] = $durum_stats['reddedilen'];

			}

		}

		

		// Filtre için gerekli veriler

		// Personel listesi (Kullanıcılar)

		try {

			$data['personel_listesi'] = $this->db->query("SELECT kullanici_id, kullanici_ad, kullanici_soyad FROM kullanicilar WHERE kullanici_durum = 1 ORDER BY kullanici_ad ASC")->result();

		} catch (Exception $e) {

			$data['personel_listesi'] = array();

		}

		

		// Sayfa yükle

		$this->load->view("muhasebe/tahsilat-listesi", $data);

	}

	

	/**

	 * Senet Yönetimi - Muhasebe altında

	 */

	public function senet_yonetim()

	{

		// Memory limit artır (performans için)

		ini_set('memory_limit', '256M');

		ini_set('max_execution_time', 300);

		

		// Session kontrolü

		$control = session("r", "login_info");

		if (!$control || !isset($control->kullanici_id)) {

			redirect(base_url('check'));

			return;

		}

		

		// Yetki kontrolü - Senet Yönetimi (ID: 522)

		if (!grup_modul_yetkisi_var(522)) {

			show_error('Bu sayfaya erişim yetkiniz bulunmamaktadır. Gerekli yetki: Senet Yönetimi (ID: 522)', 403);

			return;

		}

		

		// Debug bilgisi - kullanıcı 187 için

		$debug_info = "";

		if ($control->kullanici_id == 187) {

			$debug_info = "<!-- SENET YONETIM DEBUG: User={$control->kullanici_id}, Group={$control->grup_id}, YetkiKontrolü=522 -->";

		}

		

		$data["baslik"] = "Muhasebe / Senet Yönetimi";

		

		// Debug bilgisini data'ya ekle

		if ($control->kullanici_id == 187) {

			$data['debug_info'] = $debug_info . "<!-- SENET YONETIM SUCCESS: Fonksiyon başarıyla başladı -->";

		}

		

		// Durum filtresi

		$durum_filter = $this->input->get('durum');

		$valid_durumlar = ['eldeki', 'bankaya-verilen', 'tahsil-edilen', 'protesto', 'vadesi-gecen'];

		

		if (!$durum_filter || !in_array($durum_filter, $valid_durumlar)) {

			$durum_filter = 'eldeki'; // Default

		}

		

		$data['durum_filter'] = $durum_filter;
		
		// Arama parametresi
		$search_param = $this->input->get('search');
		$data['search_param'] = $search_param;
				

		// Debug - Durum filtresi

		if ($control->kullanici_id == 187) {

			$data['debug_info'] .= "<!-- DURUM FILTER: $durum_filter -->";

		}

		

		// Durum ID'lerini map et

		$durum_map = [

			'eldeki' => 1,           // Eldeki Bekleyen Senetler

			'bankaya-verilen' => 2,  // Bankaya Verilen Senetler  

			'tahsil-edilen' => 3,    // Bankadan Tahsil Edilen Senetler

			'protesto' => 4,         // Protesto Olan Senetler

			'vadesi-gecen' => 5      // Vadesi Geçen Tahsilatlar

		];

		

		$durum_id = $durum_map[$durum_filter];

		

		// Debug - Durum ID

		if ($control->kullanici_id == 187) {

			$data['debug_info'] .= "<!-- DURUM ID: $durum_id -->";

		}

		

		// Sayfalama

		$page = $this->input->get('sayfa') ? $this->input->get('sayfa') : 1;

		$limit = 20;

		$offset = ($page - 1) * $limit;

		

		$anaHesap = anaHesapBilgisi();

		

		// Ana sorgu - Vadesi geçen durumu için özel sorgu

		if ($durum_filter == 'vadesi-gecen') {

			$senetlerQ = "

				SELECT s.*, 

					   COALESCE(CONCAT(c.cari_ad, ' ', c.cari_soyad), c.cari_ad, 'Bilinmiyor') as cari_ad,

					   c.cari_firmaTelefon as cari_telefon,

					   CONCAT_WS(' ', k.kullanici_ad, k.kullanici_soyad) as personel_adi,

					   s.senet_gorsel,

					   'Vadesi Geçen' as son_durum,

					   '#dc3545' as durum_rengi,

					   'fa-exclamation-triangle' as durum_ikonu,

					   NULL as son_hareket_tarihi,

					   NULL as banka_hesap_adi,

					   DATEDIFF(s.senet_vadeTarih, CURDATE()) as vade_gun_kalan

				FROM senet s

				LEFT JOIN cari c ON s.senet_cariID = c.cari_id

				LEFT JOIN kullanicilar k ON s.senet_kullaniciID = k.kullanici_id

				LEFT JOIN muhasebe_tahsilat_durum mtd ON (mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id)

				WHERE s.senet_vadeTarih < CURDATE()

				AND (mtd.durum IS NULL OR mtd.durum != 2)

				ORDER BY s.senet_vadeTarih ASC

				LIMIT $offset, $limit

			";

		} else {

			// Ana sorgu - Senet tablosu için doğru sütun adlarını kullan

			$senetlerQ = "

				SELECT s.*, 

					   COALESCE(CONCAT(c.cari_ad, ' ', c.cari_soyad), c.cari_ad, 'Bilinmiyor') as cari_ad,

					   c.cari_firmaTelefon as cari_telefon,

					   CONCAT_WS(' ', k.kullanici_ad, k.kullanici_soyad) as personel_adi,

					   s.senet_gorsel,

					   skt.skt_adi as son_durum,

					   skt.skt_renk as durum_rengi,

					   skt.skt_ikon as durum_ikonu,

					   sh_son.sh_tarih as son_hareket_tarihi,

					   b.banka_bankaAd as banka_hesap_adi,

					   DATEDIFF(s.senet_vadeTarih, CURDATE()) as vade_gun_kalan

				FROM senet s

				LEFT JOIN cari c ON s.senet_cariID = c.cari_id

				LEFT JOIN kullanicilar k ON s.senet_kullaniciID = k.kullanici_id

				LEFT JOIN (

					SELECT sh1.sh_senet_id, MAX(sh1.sh_id) as max_id

					FROM senet_hareketleri sh1

					GROUP BY sh1.sh_senet_id

				) son_hareket ON s.senet_id = son_hareket.sh_senet_id

				LEFT JOIN senet_hareketleri sh_son ON son_hareket.max_id = sh_son.sh_id

				LEFT JOIN senet_konum_tipleri skt ON sh_son.sh_konum_tip_id = skt.skt_id

				LEFT JOIN banka b ON sh_son.sh_banka_id = b.banka_id

				WHERE (sh_son.sh_konum_tip_id = '$durum_id' OR (sh_son.sh_konum_tip_id IS NULL AND '$durum_id' = '1'))

				ORDER BY s.senet_vadeTarih ASC

				LIMIT $offset, $limit

			";

		}

		

		// Arama koşulunu tüm durumlara uygula
		if (!empty($search_param)) {
			$search_escaped = $this->db->escape_like_str($search_param);
			$search_condition = " AND (
				c.cari_vergiNumarasi LIKE '%$search_escaped%' OR
				CONCAT(c.cari_ad, ' ', c.cari_soyad) LIKE '%$search_escaped%' OR
				c.cari_ad LIKE '%$search_escaped%' OR
				c.cari_firmaTelefon LIKE '%$search_escaped%'
			)";
			
			// WHERE koşulundan sonra arama koşulunu ekle
			if ($durum_filter == 'vadesi-gecen') {
				$senetlerQ = str_replace(
					"AND (mtd.durum IS NULL OR mtd.durum != 2)",
					"AND (mtd.durum IS NULL OR mtd.durum != 2) $search_condition",
					$senetlerQ
				);
			} else {
				$senetlerQ = str_replace(
					"WHERE (sh_son.sh_konum_tip_id = '$durum_id' OR (sh_son.sh_konum_tip_id IS NULL AND '$durum_id' = '1'))",
					"WHERE (sh_son.sh_konum_tip_id = '$durum_id' OR (sh_son.sh_konum_tip_id IS NULL AND '$durum_id' = '1')) $search_condition",
					$senetlerQ
				);
			}
		}
		
		// Sorguyu çalıştır ve hata kontrolü yap

		try {

			$senetlerResult = $this->db->query($senetlerQ);

			if (!$senetlerResult) {

				throw new Exception("Senet listesi sorgusu başarısız: " . $this->db->error()['message']);

			}

			$data['senetler'] = $senetlerResult->result();

			

			// Debug - Sorgu sonucu

			if ($control->kullanici_id == 187) {

				$data['debug_info'] .= "<!-- SENET SORGUSU: " . count($data['senetler']) . " senet bulundu -->";

				$data['debug_info'] .= "<!-- SQL SORGU: " . str_replace("\n", " ", $senetlerQ) . " -->";

				if (!empty($data['senetler'])) {

					$ilkSenet = $data['senetler'][0];

					$data['debug_info'] .= "<!-- İLK SENET: cari_ad='" . $ilkSenet->cari_ad . "', senet_cariID='" . $ilkSenet->senet_cariID . "' -->";

				}

			}

		} catch (Exception $e) {

			$data['senetler'] = array();

			$data['error_message'] = "Senet listesi yüklenirken hata oluştu: " . $e->getMessage();

			

			// Debug - Hata mesajı

			if ($control->kullanici_id == 187) {

				$data['debug_info'] .= "<!-- SENET SORGUSU HATA: " . $e->getMessage() . " -->";

			}

		}

		

		// Toplam sayı - Vadesi geçen durumu için özel sorgu

		if ($durum_filter == 'vadesi-gecen') {

			$countQ = "

				SELECT COUNT(*) as total

				FROM senet s

				LEFT JOIN muhasebe_tahsilat_durum mtd ON (mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id)

				WHERE s.senet_vadeTarih < CURDATE()

				AND (mtd.durum IS NULL OR mtd.durum != 2)

			";

		} else {

			$countQ = "

				SELECT COUNT(*) as total

				FROM senet s

				LEFT JOIN cari c ON s.senet_cariID = c.cari_id

				LEFT JOIN (

					SELECT sh1.sh_senet_id, MAX(sh1.sh_id) as max_id

					FROM senet_hareketleri sh1

					GROUP BY sh1.sh_senet_id

				) son_hareket ON s.senet_id = son_hareket.sh_senet_id

				LEFT JOIN senet_hareketleri sh_son ON son_hareket.max_id = sh_son.sh_id

				WHERE (sh_son.sh_konum_tip_id = '$durum_id' OR (sh_son.sh_konum_tip_id IS NULL AND '$durum_id' = '1'))

			";

		}

		

		// Arama koşulunu count sorgusuna da uygula
		if (!empty($search_param)) {
			$search_escaped = $this->db->escape_like_str($search_param);
			$search_condition = " AND (
				c.cari_vergiNumarasi LIKE '%$search_escaped%' OR
				CONCAT(c.cari_ad, ' ', c.cari_soyad) LIKE '%$search_escaped%' OR
				c.cari_ad LIKE '%$search_escaped%' OR
				c.cari_firmaTelefon LIKE '%$search_escaped%'
			)";
			
			if ($durum_filter == 'vadesi-gecen') {
				// Vadesi geçen count sorgusu için cari tablosunu da join etmeli
				$countQ = "
					SELECT COUNT(*) as total
					FROM senet s
					LEFT JOIN cari c ON s.senet_cariID = c.cari_id
					LEFT JOIN muhasebe_tahsilat_durum mtd ON (mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id)
					WHERE s.senet_vadeTarih < CURDATE()
					AND (mtd.durum IS NULL OR mtd.durum != 2)
					$search_condition
				";
			} else {
				$countQ = str_replace(
					"WHERE (sh_son.sh_konum_tip_id = '$durum_id' OR (sh_son.sh_konum_tip_id IS NULL AND '$durum_id' = '1'))",
					"WHERE (sh_son.sh_konum_tip_id = '$durum_id' OR (sh_son.sh_konum_tip_id IS NULL AND '$durum_id' = '1')) $search_condition",
					$countQ
				);
			}
		}

		

		try {

			$countResult = $this->db->query($countQ);

			if (!$countResult) {

				throw new Exception("Toplam sayı sorgusu başarısız: " . $this->db->error()['message']);

			}

			$data['total_count'] = $countResult->row()->total;

		} catch (Exception $e) {

			$data['total_count'] = 0;

			if (!isset($data['error_message'])) {

				$data['error_message'] = "Toplam sayı hesaplanırken hata oluştu: " . $e->getMessage();

			}

		}

		

		// Sayfalama

		$this->load->library('pagination');

		$pagination_url = "muhasebe/senet-yonetim?durum=$durum_filter";
		if (!empty($search_param)) {
			$pagination_url .= "&search=" . urlencode($search_param);
		}
		$config['base_url'] = base_url($pagination_url);

		$config['total_rows'] = $data['total_count'];

		$config['per_page'] = $limit;

		$config['page_query_string'] = TRUE;

		$config['query_string_segment'] = 'sayfa';

		$config['use_page_numbers'] = TRUE;

		

		// Pagination style

		$config['full_tag_open'] = '<nav><ul class="pagination justify-content-center">';

		$config['full_tag_close'] = '</ul></nav>';

		$config['first_link'] = 'İlk';

		$config['last_link'] = 'Son';

		$config['first_tag_open'] = '<li class="page-item">';

		$config['first_tag_close'] = '</li>';

		$config['prev_link'] = '&laquo;';

		$config['prev_tag_open'] = '<li class="page-item">';

		$config['prev_tag_close'] = '</li>';

		$config['next_link'] = '&raquo;';

		$config['next_tag_open'] = '<li class="page-item">';

		$config['next_tag_close'] = '</li>';

		$config['last_tag_open'] = '<li class="page-item">';

		$config['last_tag_close'] = '</li>';

		$config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';

		$config['cur_tag_close'] = '</a></li>';

		$config['num_tag_open'] = '<li class="page-item">';

		$config['num_tag_close'] = '</li>';

		$config['attributes'] = array('class' => 'page-link');

		

		$this->pagination->initialize($config);

		$data['pagination'] = $this->pagination->create_links();

		

		// Konum tipleri - Veritabanından doğru tablo adını kullanarak getir

		try {

			$data['konum_tipleri'] = $this->db->query("SELECT skt_id as kt_id, skt_adi as kt_ad, skt_renk as kt_ikon, skt_sira_no as kt_sira FROM senet_konum_tipleri WHERE skt_aktif = '1' ORDER BY skt_sira_no ASC")->result();

		} catch (Exception $e) {

			// Eğer tablo hata verirse fallback data kullan

			$data['konum_tipleri'] = array(

				(object) array(

					'kt_id' => 1,

					'kt_ad' => 'Eldeki',

					'kt_renk' => '#17a2b8',

					'kt_ikon' => 'fa-clock',

					'kt_sira' => 1

				),

				(object) array(

					'kt_id' => 2,

					'kt_ad' => 'Bankaya Verilen',

					'kt_renk' => '#ffc107',

					'kt_ikon' => 'fa-university',

					'kt_sira' => 2

				),

				(object) array(

					'kt_id' => 3,

					'kt_ad' => 'Tahsil Edilen',

					'kt_renk' => '#28a745',

					'kt_ikon' => 'fa-check',

					'kt_sira' => 3

				),

				(object) array(

					'kt_id' => 4,

					'kt_ad' => 'Protesto',

					'kt_renk' => '#dc3545',

					'kt_ikon' => 'fa-times',

					'kt_sira' => 4

				)

			);

		}

		

		// Bankalar

		try {

			$data['bankalar'] = $this->db->query("SELECT * FROM banka ORDER BY banka_bankaAd ASC")->result();

		} catch (Exception $e) {

			$data['bankalar'] = array();

		}

		

		// İstatistikler - hata olmadan çalıştırmaya çalış

		try {

			$data['istatistikler'] = $this->getSenetIstatistikleri();

		} catch (Exception $e) {

			$data['istatistikler'] = (object)[

				'toplam_senet' => 0,

				'toplam_tutar' => 0,

				'vadesi_gecen' => 0,

				'vadesi_yaklasan' => 0

			];

		}

		

		// Debug - Final

		if ($control->kullanici_id == 187) {

			$data['debug_info'] .= "<!-- VIEW YUKLENIYOR: muhasebe/senet-yonetim -->";

		}

		

		// Durum başlıkları

		$data['durum_basliklar'] = [

			'eldeki' => 'Eldeki Bekleyen Senetler',

			'bankaya-verilen' => 'Bankaya Verilen Senetler',

			'tahsil-edilen' => 'Bankadan Tahsil Edilen Senetler',

			'protesto' => 'Protesto Olan Senetler',

			'vadesi-gecen' => 'Vadesi Geçen Tahsilatlar'

		];

		

		$this->load->view('muhasebe/senet-yonetim', $data);

	}

	

	/**

	 * Senet istatistikleri - Doğru sütun adlarını kullan

	 */

	private function getSenetIstatistikleri()

	{

		$istatistikQ = "

			SELECT 

				COUNT(*) as toplam_senet,

				SUM(s.senet_tutar) as toplam_tutar,

				COUNT(CASE WHEN s.senet_vadeTarih < CURDATE() THEN 1 END) as vadesi_gecen,

				COUNT(CASE WHEN s.senet_vadeTarih BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as vadesi_yaklasan

			FROM senet s

		";



		return $this->db->query($istatistikQ)->row();

	}

	/**
	 * Senet Yönetimi Excel İndir
	 */
	public function senet_yonetim_excel()
	{
		// Yetki kontrolü - Senet Yönetimi (ID: 522)
		if (!grup_modul_yetkisi_var(522)) {
			show_error('Bu sayfaya erişim yetkiniz bulunmamaktadır. Gerekli yetki: Senet Yönetimi (ID: 522)', 403);
			return;
		}

		// Session kontrolü
		$control = session("r", "login_info");
		if (!$control || !isset($control->kullanici_id)) {
			redirect(base_url('check'));
			return;
		}

		// Durum filtresi
		$durum_filter = $this->input->get('durum');
		$valid_durumlar = ['eldeki', 'bankaya-verilen', 'tahsil-edilen', 'protesto', 'vadesi-gecen'];
		
		if (!$durum_filter || !in_array($durum_filter, $valid_durumlar)) {
			$durum_filter = 'eldeki'; // Default
		}

		// Arama parametresi
		$search_param = $this->input->get('search');

		// Durum ID'lerini map et
		$durum_map = [
			'eldeki' => 1,           // Eldeki Bekleyen Senetler
			'bankaya-verilen' => 2,  // Bankaya Verilen Senetler  
			'tahsil-edilen' => 3,    // Bankadan Tahsil Edilen Senetler
			'protesto' => 4,         // Protesto Olan Senetler
			'vadesi-gecen' => 5      // Vadesi Geçen Tahsilatlar
		];

		$durum_id = $durum_map[$durum_filter];

		// Ana sorgu - Vadesi geçen durumu için özel sorgu
		if ($durum_filter == 'vadesi-gecen') {
			$senetlerQ = "
				SELECT s.*, 
					   COALESCE(CONCAT(c.cari_ad, ' ', c.cari_soyad), c.cari_ad, 'Bilinmiyor') as cari_ad,
					   c.cari_firmaTelefon as cari_telefon,
					   CONCAT_WS(' ', k.kullanici_ad, k.kullanici_soyad) as personel_adi,
					   'Vadesi Geçen' as son_durum,
					   NULL as banka_hesap_adi,
					   DATEDIFF(s.senet_vadeTarih, CURDATE()) as vade_gun_kalan
				FROM senet s
				LEFT JOIN cari c ON s.senet_cariID = c.cari_id
				LEFT JOIN kullanicilar k ON s.senet_kullaniciID = k.kullanici_id
				LEFT JOIN muhasebe_tahsilat_durum mtd ON (mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id)
				WHERE s.senet_vadeTarih < CURDATE()
				AND (mtd.durum IS NULL OR mtd.durum != 2)
				ORDER BY s.senet_vadeTarih ASC
			";
		} else {
			// Ana sorgu - Senet tablosu için doğru sütun adlarını kullan
			$senetlerQ = "
				SELECT s.*, 
					   COALESCE(CONCAT(c.cari_ad, ' ', c.cari_soyad), c.cari_ad, 'Bilinmiyor') as cari_ad,
					   c.cari_firmaTelefon as cari_telefon,
					   CONCAT_WS(' ', k.kullanici_ad, k.kullanici_soyad) as personel_adi,
					   skt.skt_adi as son_durum,
					   b.banka_bankaAd as banka_hesap_adi,
					   DATEDIFF(s.senet_vadeTarih, CURDATE()) as vade_gun_kalan
				FROM senet s
				LEFT JOIN cari c ON s.senet_cariID = c.cari_id
				LEFT JOIN kullanicilar k ON s.senet_kullaniciID = k.kullanici_id
				LEFT JOIN (
					SELECT sh1.sh_senet_id, MAX(sh1.sh_id) as max_id
					FROM senet_hareketleri sh1
					GROUP BY sh1.sh_senet_id
				) son_hareket ON s.senet_id = son_hareket.sh_senet_id
				LEFT JOIN senet_hareketleri sh_son ON son_hareket.max_id = sh_son.sh_id
				LEFT JOIN senet_konum_tipleri skt ON sh_son.sh_konum_tip_id = skt.skt_id
				LEFT JOIN banka b ON sh_son.sh_banka_id = b.banka_id
				WHERE (sh_son.sh_konum_tip_id = '$durum_id' OR (sh_son.sh_konum_tip_id IS NULL AND '$durum_id' = '1'))
				ORDER BY s.senet_vadeTarih ASC
			";
		}

		// Arama koşulunu ekle
		if (!empty($search_param)) {
			$search_escaped = $this->db->escape_like_str($search_param);
			$search_condition = " AND (
				c.cari_vergiNumarasi LIKE '%$search_escaped%' OR
				CONCAT(c.cari_ad, ' ', c.cari_soyad) LIKE '%$search_escaped%' OR
				c.cari_ad LIKE '%$search_escaped%' OR
				c.cari_firmaTelefon LIKE '%$search_escaped%'
			)";
			
			if ($durum_filter == 'vadesi-gecen') {
				$senetlerQ = str_replace(
					"AND (mtd.durum IS NULL OR mtd.durum != 2)",
					"AND (mtd.durum IS NULL OR mtd.durum != 2) $search_condition",
					$senetlerQ
				);
			} else {
				$senetlerQ = str_replace(
					"WHERE (sh_son.sh_konum_tip_id = '$durum_id' OR (sh_son.sh_konum_tip_id IS NULL AND '$durum_id' = '1'))",
					"WHERE (sh_son.sh_konum_tip_id = '$durum_id' OR (sh_son.sh_konum_tip_id IS NULL AND '$durum_id' = '1')) $search_condition",
					$senetlerQ
				);
			}
		}

		// Verileri getir
		$senetler = $this->db->query($senetlerQ)->result();

		// Excel oluştur
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		// Başlık metni
		$durum_basliklar = [
			'eldeki' => 'Eldeki Bekleyen Senetler',
			'bankaya-verilen' => 'Bankaya Verilen Senetler',
			'tahsil-edilen' => 'Bankadan Tahsil Edilen Senetler',
			'protesto' => 'Protesto Olan Senetler',
			'vadesi-gecen' => 'Vadesi Geçen Tahsilatlar'
		];

		$sheet->setTitle('Senet Listesi');

		// Başlık satırı
		$sheet->setCellValue('A1', $durum_basliklar[$durum_filter]);
		$sheet->mergeCells('A1:I1');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
		$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

		// Tarih bilgisi
		$sheet->setCellValue('A2', 'Rapor Tarihi: ' . date('d.m.Y H:i'));
		$sheet->mergeCells('A2:I2');
		$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

		// Kolon başlıkları
		$headers = [
			'A3' => 'Sıra No',
			'B3' => 'Cari Adı',
			'C3' => 'Cari Telefon', 
			'D3' => 'Senet Tutarı (₺)',
			'E3' => 'Vade Tarihi',
			'F3' => 'Durum',
			'G3' => 'Kalan Gün',
			'H3' => 'Personel',
			'I3' => 'Banka'
		];

		foreach ($headers as $cell => $header) {
			$sheet->setCellValue($cell, $header);
		}

		// Başlık stilini ayarla
		$headerStyle = [
			'font' => ['bold' => true],
			'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => ['rgb' => 'E2E8F0']
			],
			'borders' => [
				'allBorders' => ['borderStyle' => Border::BORDER_THIN]
			]
		];
		$sheet->getStyle('A3:I3')->applyFromArray($headerStyle);

		// Verileri doldur
		$row = 4;
		$sira = 1;
		foreach ($senetler as $senet) {
			$sheet->setCellValue('A' . $row, $sira);
			$sheet->setCellValue('B' . $row, $senet->cari_ad ?: 'Bilinmiyor');
			$sheet->setCellValue('C' . $row, $senet->cari_telefon ?: '-');
			$sheet->setCellValue('D' . $row, number_format($senet->senet_tutar, 2));
			$sheet->setCellValue('E' . $row, date('d.m.Y', strtotime($senet->senet_vadeTarih)));
			$sheet->setCellValue('F' . $row, $senet->son_durum ?: 'Personel\'in Kendisinde');

			// Kalan gün hesapla
			if ($senet->vade_gun_kalan > 0) {
				$kalan_gun_text = $senet->vade_gun_kalan . ' gün';
			} elseif ($senet->vade_gun_kalan == 0) {
				$kalan_gun_text = 'Bugün';
			} else {
				$kalan_gun_text = abs($senet->vade_gun_kalan) . ' gün geçmiş';
			}
			$sheet->setCellValue('G' . $row, $kalan_gun_text);

			$sheet->setCellValue('H' . $row, $senet->personel_adi ?: '-');
			$sheet->setCellValue('I' . $row, $senet->banka_hesap_adi ?: '-');

			$row++;
			$sira++;
		}

		// Kolon genişliklerini ayarla
		$sheet->getColumnDimension('A')->setWidth(8);
		$sheet->getColumnDimension('B')->setWidth(25);
		$sheet->getColumnDimension('C')->setWidth(15);
		$sheet->getColumnDimension('D')->setWidth(15);
		$sheet->getColumnDimension('E')->setWidth(12);
		$sheet->getColumnDimension('F')->setWidth(20);
		$sheet->getColumnDimension('G')->setWidth(12);
		$sheet->getColumnDimension('H')->setWidth(20);
		$sheet->getColumnDimension('I')->setWidth(20);

		// Veri alanına border ekle
		if ($row > 4) {
			$dataStyle = [
				'borders' => [
					'allBorders' => ['borderStyle' => Border::BORDER_THIN]
				],
				'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
			];
			$sheet->getStyle('A4:I' . ($row - 1))->applyFromArray($dataStyle);

			// Tutar sütununu sağa hizala
			$sheet->getStyle('D4:D' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
		}

		// Dosya adını oluştur
		$filename = 'senet_listesi_' . $durum_filter . '.xlsx';

		// Header'ları ayarla
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		// Excel dosyasını çıktı ver
		$writer = new Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}



	public function senet_detay($id)

	{

		// Yetki kontrolü - Senet Yönetimi (ID: 522)

		if (!grup_modul_yetkisi_var(522)) {

			show_error('Bu sayfaya erişim yetkiniz bulunmamaktadır. Gerekli yetki: Senet Yönetimi (ID: 522)', 403);

			return;

		}

		

		$data["baslik"] = "Muhasebe / Senet Detayı";

		

		// Debug bilgisi ekle

		$data["debug_info"] = "<!-- DEBUG: Senet ID: $id -->";

		

		// Ana hesap kısıtlaması kaldırıldı - tüm senetleri göster

		$senetQ = "SELECT s.*, 

				   COALESCE(CONCAT(c.cari_ad, ' ', c.cari_soyad), c.cari_ad, 'Bilinmiyor') as cari_ad,

				   c.cari_firmaTelefon as cari_telefon

				   FROM senet s

				   LEFT JOIN cari c ON s.senet_cariID = c.cari_id

				   WHERE s.senet_id = '$id'";

		

		$data["senet"] = $this->db->query($senetQ)->row();

		

		if (!$data["senet"]) {

			// Debug: Senet bulunamadı bilgisi

			$data["error_message"] = "Senet bulunamadı (ID: $id)";

			$this->load->view("hata", $data);

			return;

		}

		

		// Senet hareketleri - ana hesap kısıtlaması kaldırıldı

		$hareketlerQ = "SELECT sh.*, 

						skt.skt_adi as konum_adi,

						skt.skt_renk as kt_renk,

						skt.skt_ikon as kt_ikon,

						b.banka_bankaAd as banka_ad,

						CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) as islem_yapan

						FROM senet_hareketleri sh

						LEFT JOIN senet_konum_tipleri skt ON sh.sh_konum_tip_id = skt.skt_id

						LEFT JOIN banka b ON sh.sh_banka_id = b.banka_id

						LEFT JOIN kullanicilar k ON sh.sh_kullanici_id = k.kullanici_id

						WHERE sh.sh_senet_id = '$id'

						ORDER BY sh.sh_tarih DESC, sh.sh_id DESC";

		

		$data["hareketler"] = $this->db->query($hareketlerQ)->result();

		

		// Konum tipleri ve bankalar bilgilerini de ekleyelim

		$data["konum_tipleri"] = $this->db->query("SELECT * FROM senet_konum_tipleri ORDER BY skt_adi")->result();

		$data["bankalar"] = $this->db->query("SELECT * FROM banka ORDER BY banka_bankaAd")->result();

		

		$this->load->view("muhasebe/senet-detay", $data);

	}



	public function senet_konum_degistir()

	{

		// Session kontrolü

		$control = session("r", "login_info");

		if (!$control || !isset($control->kullanici_id)) {

			redirect(base_url('check'));

			return;

		}



		// POST verilerini al

		$senet_id = $this->input->post('senet_id');

		$yeni_konum = $this->input->post('yeni_konum');

		$banka_id = $this->input->post('banka_id');

		$aciklama = $this->input->post('aciklama');



		// Veri doğrulama

		if (!$senet_id || !$yeni_konum) {

			$this->session->set_flashdata('hata', 'Gerekli alanlar eksik!');

			redirect('muhasebe/senet-detay/' . $senet_id);

			return;

		}



		// Senet varlığını kontrol et

		$senet = $this->db->query("SELECT * FROM senet WHERE senet_id = '$senet_id'")->row();

		if (!$senet) {

			$this->session->set_flashdata('hata', 'Senet bulunamadı!');

			redirect('muhasebe/senet-yonetim');

			return;

		}



		// Yeni hareket kaydı ekle

		$hareket_data = array(

			'sh_senet_id' => $senet_id,

			'sh_konum_tip_id' => $yeni_konum,

			'sh_banka_id' => $banka_id ? $banka_id : null,

			'sh_kullanici_id' => $control->kullanici_id,

			'sh_tarih' => date('Y-m-d H:i:s'),

			'sh_not' => $aciklama ? $aciklama : 'Konum değiştirildi'

		);



		try {

			$this->db->insert('senet_hareketleri', $hareket_data);

			$this->session->set_flashdata('basarili', 'Senet konumu başarıyla değiştirildi!');

		} catch (Exception $e) {

			$this->session->set_flashdata('hata', 'Konum değiştirme işlemi başarısız: ' . $e->getMessage());

		}



		// Detay sayfasına geri dön

		redirect('muhasebe/senet-detay/' . $senet_id);

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

	

	public function musteriListesi()

	{

		// Memory limit artır (performans için)

		ini_set('memory_limit', '512M');

		ini_set('max_execution_time', 600);

		

		$data["baslik"] = "Muhasebe / Müşteri Listesi";

		$anaHesap = anaHesapBilgisi();

		

		// Giriş yapan kullanıcı bilgisi

		$control = session("r", "login_info");

		if (!$control || !isset($control->kullanici_id)) {

			redirect(base_url('check'));

			return;

		}

		$u_id = $control->kullanici_id;

		

		// Yetki kontrolü - Müşteri Listesi (ID: 530)

		if (!grup_modul_yetkisi_var(530)) {

			show_error('Bu sayfaya erişim yetkiniz bulunmamaktadır. Gerekli yetki: Müşteri Listesi (ID: 530)', 403);

			return;

		}

		

		// Database kütüphanesini yükle

		$this->load->database();

		

		// Cari tablosunun varlığını kontrol et - basit

		$data["musteriler"] = array();

		$data["toplam_musteri"] = 0;

		

		try {

			// Basit sorgu - tüm cari kayıtlarını getir

			$musteriler = $this->vt->multiple("cari", array(), "cari_olusturmaTarihi", "DESC");

			if ($musteriler && is_array($musteriler)) {

				$data["musteriler"] = $musteriler;

				$data["toplam_musteri"] = count($musteriler);

			}

		} catch (Exception $e) {

			$data["error_message"] = "Veritabanı hatası: " . $e->getMessage();

			error_log("Muhasebe müşteri listesi sorgu hatası: " . $e->getMessage());

		}

		

		// Aktif müşteri sayısı hesapla

		$aktif_musteri = 0;

		if (!empty($data["musteriler"])) {

			foreach($data["musteriler"] as $musteri) {

				if(isset($musteri->cari_durum) && $musteri->cari_durum == 1) {

					$aktif_musteri++;

				}

			}

		}

		$data["aktif_musteri"] = $aktif_musteri;

		$data["pasif_musteri"] = $data["toplam_musteri"] - $aktif_musteri;

		

		// View yükle

		$this->load->view("muhasebe/musteri-listesi", $data);

	}
	
	/**
	 * Cari Silme Kontrolü ve Silme İşlemi
	 */
	public function cari_sil_kontrol()
	{
		// Yetki kontrolü
		if (!grup_modul_yetkisi_var(530)) {
			echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.']);
			return;
		}

		// Kütüphaneleri yükle
		$this->load->database();

		$cari_id = $this->input->post('cari_id');
		
		if (!$cari_id) {
			echo json_encode(['status' => 'error', 'message' => 'Cari ID belirtilmemiş.']);
			return;
		}

		// Cari bilgilerini getir
		$cari = $this->vt->single("cari", ["cari_id" => $cari_id]);
		if (!$cari) {
			echo json_encode(['status' => 'error', 'message' => 'Cari bulunamadı.']);
			return;
		}

		// İlişkili verileri kontrol et
		$bagimli_veriler = [];
		
		try {
			// Cari hareketleri kontrolü
			$this->db->where('ch_cariID', $cari_id);
			$cari_hareketleri = $this->db->count_all_results('carihareketleri');
			if ($cari_hareketleri > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Cari Hareketleri',
					'tablo_kodu' => 'cari_hareketleri',
					'adet' => $cari_hareketleri,
					'aciklama' => 'Bu cariye ait mali hareketler mevcut'
				];
			}

			// Satış faturaları kontrolü
			$this->db->where('satis_cariID', $cari_id);
			$satis_faturalari = $this->db->count_all_results('satisFaturasi');
			if ($satis_faturalari > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Satış Faturaları',
					'tablo_kodu' => 'satis_faturalari',
					'adet' => $satis_faturalari,
					'aciklama' => 'Bu cariye ait satış faturaları mevcut'
				];
			}

			// Alış faturaları kontrolü
			$this->db->where('alis_cariID', $cari_id);
			$alis_faturalari = $this->db->count_all_results('alisFaturasi');
			if ($alis_faturalari > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Alış Faturaları',
					'tablo_kodu' => 'alis_faturalari',
					'adet' => $alis_faturalari,
					'aciklama' => 'Bu cariye ait alış faturaları mevcut'
				];
			}

			// Proforma faturaları kontrolü
			$this->db->where('proforma_cariID', $cari_id);
			$proforma_faturalari = $this->db->count_all_results('proformaFaturasi');
			if ($proforma_faturalari > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Proforma Faturaları',
					'tablo_kodu' => 'proforma_faturalari',
					'adet' => $proforma_faturalari,
					'aciklama' => 'Bu cariye ait proforma faturaları mevcut'
				];
			}

			// Banka hareketleri kontrolü (giriş/çıkış)
			$this->db->where('bh_cariID', $cari_id);
			$banka_giris = $this->db->count_all_results('bankahareketleri');
			if ($banka_giris > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Banka Hareketleri',
					'tablo_kodu' => 'banka_hareketleri',
					'adet' => $banka_giris,
					'aciklama' => 'Bu cariye ait banka hareketleri mevcut'
				];
			}

			// Kasa hareketleri kontrolü
			$this->db->where('kh_cariID', $cari_id);
			$kasa_hareketleri = $this->db->count_all_results('kasahareketleri');
			if ($kasa_hareketleri > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Kasa Hareketleri',
					'tablo_kodu' => 'kasa_hareketleri',
					'adet' => $kasa_hareketleri,
					'aciklama' => 'Bu cariye ait kasa hareketleri mevcut'
				];
			}

			// Çek kontrolü
			$this->db->where('cek_cariID', $cari_id);
			$cekler = $this->db->count_all_results('cek');
			if ($cekler > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Çekler',
					'tablo_kodu' => 'cekler',
					'adet' => $cekler,
					'aciklama' => 'Bu cariye ait çek kayıtları mevcut'
				];
			}

			// Senet kontrolü
			$this->db->where('senet_cariID', $cari_id);
			$senetler = $this->db->count_all_results('senet');
			if ($senetler > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Senetler',
					'tablo_kodu' => 'senetler',
					'adet' => $senetler,
					'aciklama' => 'Bu cariye ait senet kayıtları mevcut'
				];
			}

			// Aktivasyon kontrolü
			$this->db->where('aktivasyon_cari_id', $cari_id);
			$aktivasyonlar = $this->db->count_all_results('aktivasyon');
			if ($aktivasyonlar > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Aktivasyonlar',
					'tablo_kodu' => 'aktivasyonlar',
					'adet' => $aktivasyonlar,
					'aciklama' => 'Bu cariye ait aktivasyon kayıtları mevcut'
				];
			}

			// Potansiyel satış kontrolü
			$this->db->where('potansiyel_cari_id', $cari_id);
			$potansiyel_satislar = $this->db->count_all_results('potansiyel_satis');
			if ($potansiyel_satislar > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Potansiyel Satışlar',
					'tablo_kodu' => 'potansiyel_satislar',
					'adet' => $potansiyel_satislar,
					'aciklama' => 'Bu cariye ait potansiyel satış kayıtları mevcut'
				];
			}

			// Muhasebe tahsilat durum kontrolü (indirekt bağlantı)
			$muhasebe_tahsilat = $this->db->query("
				SELECT COUNT(*) as toplam FROM muhasebe_tahsilat_durum mtd
				WHERE (
					(mtd.tahsilat_tipi = 1 AND mtd.kayit_id IN (SELECT bh_id FROM bankahareketleri WHERE bh_cariID = ?)) OR
					(mtd.tahsilat_tipi = 2 AND mtd.kayit_id IN (SELECT cek_id FROM cek WHERE cek_cariID = ?)) OR
					(mtd.tahsilat_tipi = 3 AND mtd.kayit_id IN (SELECT kh_id FROM kasahareketleri WHERE kh_cariID = ?)) OR
					(mtd.tahsilat_tipi = 4 AND mtd.kayit_id IN (SELECT senet_id FROM senet WHERE senet_cariID = ?))
				)
			", [$cari_id, $cari_id, $cari_id, $cari_id])->row()->toplam;
			
			if ($muhasebe_tahsilat > 0) {
				$bagimli_veriler[] = [
					'tablo' => 'Muhasebe Tahsilat Durum',
					'tablo_kodu' => 'muhasebe_tahsilat_durum',
					'adet' => $muhasebe_tahsilat,
					'aciklama' => 'Bu cariye ait muhasebe tahsilat durum kayıtları mevcut'
				];
			}

			// Eğer bağımlı veri varsa uyarı göster
			if (!empty($bagimli_veriler)) {
				echo json_encode([
					'status' => 'warning',
					'message' => 'Bu cari kaydının silinmesi durumunda aşağıdaki veriler de silinecektir:',
					'data' => $bagimli_veriler,
					'cari_bilgi' => [
						'id' => $cari->cari_id,
						'ad' => $cari->cari_ad . ($cari->cari_soyad ? ' ' . $cari->cari_soyad : ''),
						'telefon' => $cari->cari_firmaTelefon
					]
				]);
			} else {
				// Bağımlı veri yoksa doğrudan silme onayı iste
				echo json_encode([
					'status' => 'confirm',
					'message' => 'Bu cari kaydı silinecektir. Devam etmek istiyor musunuz?',
					'cari_bilgi' => [
						'id' => $cari->cari_id,
						'ad' => $cari->cari_ad . ($cari->cari_soyad ? ' ' . $cari->cari_soyad : ''),
						'telefon' => $cari->cari_firmaTelefon
					]
				]);
			}

		} catch (Exception $e) {
			echo json_encode(['status' => 'error', 'message' => 'Kontrol sırasında hata oluştu: ' . $e->getMessage()]);
		}
	}
	
	/**
	 * Cari Silme İşlemi - GÜVENLİ VERSİYON
	 */
	public function cari_sil()
	{
		// JSON header ayarla
		header('Content-Type: application/json');
		
		try {
			$cari_id = isset($_POST['cari_id']) ? $_POST['cari_id'] : null;
			$onay = isset($_POST['onay']) ? $_POST['onay'] : null;
			
			// Temel validasyon
			if (!$cari_id || $onay !== 'evet') {
				echo json_encode([
					'status' => 'error',
					'message' => 'Geçersiz parametreler'
				]);
				return;
			}
			
			// Numeric kontrolü
			if (!is_numeric($cari_id) || intval($cari_id) <= 0) {
				echo json_encode([
					'status' => 'error',
					'message' => 'Geçersiz Cari ID formatı'
				]);
				return;
			}
			
			$cari_id = intval($cari_id);
			
			// Yetki kontrolü - grup_modul_yetkisi_var fonksiyonu varsa kullan
			if (function_exists('grup_modul_yetkisi_var') && !grup_modul_yetkisi_var(530)) {
				echo json_encode([
					'status' => 'error',
					'message' => 'Bu işlem için yetkiniz bulunmamaktadır'
				]);
				return;
			}
			
			// Database ve model yükle
			if (!isset($this->db)) {
				$this->load->database();
			}
			if (!isset($this->vt)) {
				$this->load->model('vt');
			}
			
			// Cari var mı kontrol et
			$cari = $this->vt->single("cari", ["cari_id" => $cari_id]);
			if (!$cari) {
				echo json_encode([
					'status' => 'error',
					'message' => 'Cari bulunamadı'
				]);
				return;
			}
			
			// Transaction başlat
			$this->db->trans_start();
			
			$total_deleted = 0;
			
			// 1. İlişkili stok kayıtlarını sil
			$stok_queries = [
				"DELETE sfs FROM satisFaturasiStok sfs 
				 INNER JOIN satisFaturasi sf ON sfs.satisStok_satisFaturasiID = sf.satis_id 
				 WHERE sf.satis_cariID = ?",
				"DELETE als FROM alisFaturasiStok als 
				 INNER JOIN alisFaturasi af ON als.alisStok_alisFaturasiID = af.alis_id 
				 WHERE af.alis_cariID = ?",
				"DELETE pfs FROM proformaFaturasiStok pfs 
				 INNER JOIN proformaFaturasi pf ON pfs.proformaStok_proformaFaturasiID = pf.proforma_id 
				 WHERE pf.proforma_cariID = ?"
			];
			
			foreach ($stok_queries as $query) {
				$this->db->query($query, [$cari_id]);
				$total_deleted += $this->db->affected_rows();
			}
			
			// 2. Ana tabloları sil
			$tables_to_delete = [
				['table' => 'carihareketleri', 'where' => 'ch_cariID'],
				['table' => 'satisFaturasi', 'where' => 'satis_cariID'],
				['table' => 'alisFaturasi', 'where' => 'alis_cariID'],
				['table' => 'proformaFaturasi', 'where' => 'proforma_cariID'],
				['table' => 'bankahareketleri', 'where' => 'bh_cariID'],
				['table' => 'kasahareketleri', 'where' => 'kh_cariID'],
				['table' => 'cek', 'where' => 'cek_cariID'],
				['table' => 'senet', 'where' => 'senet_cariID'],
				['table' => 'aktivasyon', 'where' => 'aktivasyon_cari_id'],
				['table' => 'potansiyel_satis', 'where' => 'potansiyel_cari_id'],
				['table' => 'caridetaylibanka', 'where' => 'cdetayBanka_cariID'],
				['table' => 'caridetayliiletisim', 'where' => 'cdetay_cariID']
			];
			
			foreach ($tables_to_delete as $table_info) {
				$this->db->where($table_info['where'], $cari_id);
				$this->db->delete($table_info['table']);
				$total_deleted += $this->db->affected_rows();
			}
			
			// 3. Son olarak cari kaydını sil
			$this->db->where('cari_id', $cari_id);
			$this->db->delete('cari');
			$cari_deleted = $this->db->affected_rows();
			$total_deleted += $cari_deleted;
			
			if ($cari_deleted === 0) {
				throw new Exception("Cari kaydı silinemedi");
			}
			
			// Transaction'ı tamamla
			$this->db->trans_complete();
			
			if ($this->db->trans_status() === FALSE) {
				echo json_encode([
					'status' => 'error',
					'message' => 'Silme işlemi sırasında veritabanı hatası oluştu'
				]);
			} else {
				echo json_encode([
					'status' => 'success',
					'message' => $cari->cari_ad . ' adlı cari ve bağlı tüm veriler başarıyla silindi. (Toplam ' . $total_deleted . ' kayıt)'
				]);
			}
			
		} catch (Exception $e) {
			if (isset($this->db)) {
				$this->db->trans_rollback();
			}
			echo json_encode([
				'status' => 'error',
				'message' => 'Silme işlemi sırasında hata oluştu: ' . $e->getMessage()
			]);
		}
	}
	
	/**
	 * Tam Ana Rapor - ID: 531
	 */
	public function tam_ana_rapor()
	{
		// Memory limit artır (performans için)
		ini_set('memory_limit', '512M');
		ini_set('max_execution_time', 600);
		
		// Yetki kontrolü kaldırıldı - herkes erişebilir
		$control2 = session("r", "login");
		// if (!grup_modul_yetkisi_var(531)) {
		//	redirect(base_url("illegal"));
		// }
		
		$data = [];
		
		// Kullanıcı grup bilgisini view'e gönder
		$control = session("r", "login_info");
		$data['kullanici_grup_id'] = $control ? $control->grup_id : null;
		
		// Arama parametrelerini al
		$cari_search = $this->input->get('cari_search');
		$aktivasyon_durum = $this->input->get('aktivasyon_durum');
		$hizmet_onay_durumu = $this->input->get('hizmet_onay_durumu');
		$odeme_durumu = $this->input->get('odeme_durumu');
		
		try {
		// WHERE koşulları için dizi
		$where_conditions = ['c.cari_durum = 1'];
		
		// Cari arama koşulu ekleme
		if (!empty($cari_search)) {
			$search_term = $this->db->escape_like_str($cari_search);
			$where_conditions[] = "(
				c.cari_ad LIKE '%{$search_term}%' OR 
				c.cari_soyad LIKE '%{$search_term}%' OR 
				c.cari_vergiNumarasi LIKE '%{$search_term}%' OR 
				c.cari_firmaTelefon LIKE '%{$search_term}%'
			)";
		}
		
		// Hizmet onay durumu filtresi için ek WHERE koşulu
		// Bu filtre şimdilik devre dışı bırakılıyor, muhasebe_tahsilat_durum tablosu gerekliliği var
		$hizmet_onay_where = '';
		/*
		if (!empty($hizmet_onay_durumu)) {
			if ($hizmet_onay_durumu == 'onaylanan') {
				// En az bir tahsilat onaylanmış olanlar (muhasebe_tahsilat_durum tablosunda onay_durumu = 1 olanlar)
				$hizmet_onay_where = " AND EXISTS (
					SELECT 1 FROM muhasebe_tahsilat_durum mtd2 
					WHERE mtd2.cari_id = c.cari_id AND mtd2.onay_durumu = 1
				)";
			} elseif ($hizmet_onay_durumu == 'onaylanmayan') {
				// Hiç onaylanmış tahsilat olmayan veya hiç tahsilat kaydı olmayan
				$hizmet_onay_where = " AND (
					NOT EXISTS (
						SELECT 1 FROM muhasebe_tahsilat_durum mtd2 
						WHERE mtd2.cari_id = c.cari_id AND mtd2.onay_durumu = 1
					) OR NOT EXISTS (
						SELECT 1 FROM muhasebe_tahsilat_durum mtd2 
						WHERE mtd2.cari_id = c.cari_id
					)
				)";
			}
		}
		*/
		
		// WHERE koşullarını birleştir
		$where_clause = 'WHERE ' . implode(' AND ', $where_conditions) . $hizmet_onay_where;
		
		// Aktivasyon durum filtresi için HAVING koşulu
		$having_clause = '';
		if (!empty($aktivasyon_durum)) {
			$having_clause = "HAVING aktivasyon_durum = '" . $this->db->escape_str($aktivasyon_durum) . "'";
		}
		
		// Tam Ana Rapor sorgusu
		$query = "
			SELECT DISTINCT
				c.cari_id,
				c.cari_ad,
				c.cari_soyad,
				CASE 
					WHEN c.cari_bireysel = 0 OR c.cari_soyad IS NULL OR c.cari_soyad = '' 
					THEN c.cari_ad 
					ELSE CONCAT(c.cari_ad, ' ', c.cari_soyad)
				END as cari_isletme,
				
				-- Kayıt tarihi (en yeni önce sıralama için)
				COALESCE(c.cari_olusturmaTarihi, '2000-01-01') as kayit_tarihi,
				
				-- Satış sözleşmesi bilgileri (en yeni satış)
				(SELECT sf2.satis_id FROM satisFaturasi sf2 WHERE sf2.satis_cariID = c.cari_id ORDER BY sf2.satis_id DESC LIMIT 1) as satis_sozlesme_id,
				(SELECT sf2.satis_vergiDahilToplam FROM satisFaturasi sf2 WHERE sf2.satis_cariID = c.cari_id ORDER BY sf2.satis_id DESC LIMIT 1) as satis_sozlesme_tutar,
				(SELECT sf2.satis_vergiDahilToplam FROM satisFaturasi sf2 WHERE sf2.satis_cariID = c.cari_id ORDER BY sf2.satis_id DESC LIMIT 1) as satis_sozlesme_tutar_kdv_dahil,
				-- Toplam satış sayısı
				(SELECT COUNT(*) FROM satisFaturasi sf2 WHERE sf2.satis_cariID = c.cari_id) as toplam_satis_sayisi,
				-- Stok adı bilgisi (en yeni satıştan) - birden fazla stok göstermek için GROUP_CONCAT kullan
				(SELECT GROUP_CONCAT(
					CASE 
						WHEN s.stok_stokGrupKoduID = 1 THEN 'DIGITURK'
						WHEN s.stok_id = 16 THEN 'S SPORT'
						WHEN s.stok_id = 13 THEN 'TABII'
						ELSE s.stok_ad
					END
					SEPARATOR ', '
				 )
				 FROM satisFaturasi sf2 
				 INNER JOIN satisFaturasiStok sfs ON sfs.satisStok_satisFaturasiID = sf2.satis_id
				 INNER JOIN stok s ON sfs.satisStok_stokID = s.stok_id
				 WHERE sf2.satis_cariID = c.cari_id AND sf2.satis_id = (
					 SELECT sf3.satis_id FROM satisFaturasi sf3 WHERE sf3.satis_cariID = c.cari_id ORDER BY sf3.satis_id DESC LIMIT 1
				 )
				 GROUP BY sf2.satis_id
				) as satis_sozlesme_hizmeti,
				
				-- İşletme görsel sayısını hesapla (fotograf_dosya kolonundan)
				CASE 
					WHEN c.fotograf_dosya IS NOT NULL AND c.fotograf_dosya != '' 
					THEN (LENGTH(c.fotograf_dosya) - LENGTH(REPLACE(c.fotograf_dosya, ',', '')) + 1)
					ELSE 0
				END as isletme_gorsel_sayisi,
				
				-- Evrak sayısını hesapla  
				CASE 
					WHEN c.evrak_dosya IS NOT NULL AND c.evrak_dosya != '' 
					THEN (LENGTH(c.evrak_dosya) - LENGTH(REPLACE(c.evrak_dosya, ',', '')) + 1)
					ELSE 0
				END as evrak_sayisi,
				
				-- Satış görsel sayısını hesapla (en yeni satıştan)
				(SELECT CASE 
					WHEN sf2.satis_dosya IS NOT NULL AND sf2.satis_dosya != '' 
					THEN (LENGTH(sf2.satis_dosya) - LENGTH(REPLACE(sf2.satis_dosya, ',', '')) + 1)
					ELSE 0
				END FROM satisFaturasi sf2 WHERE sf2.satis_cariID = c.cari_id ORDER BY sf2.satis_id DESC LIMIT 1) as satis_gorsel_sayisi,
				
				-- Banka görsel sayısını hesapla
				COALESCE(banka_gorseller.banka_gorsel_sayisi, 0) as banka_gorsel_sayisi,
				
				-- Çek görsel sayısını hesapla
				COALESCE(cek_gorseller.cek_gorsel_sayisi, 0) as cek_gorsel_sayisi,
				
				-- Senet görsel sayısını hesapla
				COALESCE(senet_gorseller.senet_gorsel_sayisi, 0) as senet_gorsel_sayisi,
				
				-- Tahsilat görsel sayısını hesapla (carihareketleri ile bağlantılı tablolardan)
				COALESCE(tahsilat_gorseller.toplam_gorsel, 0) as tahsilat_gorsel_sayisi,
				
				NULL as evrak_gorselleri,
				NULL as sozlesme_gorselleri,
				NULL as tahsilat_gorselleri,
				
				-- Tahsilat tutarını farklı tablolardan toplama yap (banka, çek, kasa, senet)
				COALESCE(ch_toplam.toplam_tahsilat, 0) as tahsilat_tutar,
				
				-- Aktivasyon durumu (en yeni aktivasyondan)
				(SELECT COALESCE(ad2.aktivasyon_durum_adi, 'Tanımlanmamış') 
				 FROM aktivasyon a2 
				 LEFT JOIN aktivasyon_durum ad2 ON a2.aktivasyon_durum_id = ad2.aktivasyon_durum_id AND ad2.durum = 1
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_durum,
				 
				(SELECT COALESCE(ad2.aktivasyon_durum_renk, '#6c757d') 
				 FROM aktivasyon a2 
				 LEFT JOIN aktivasyon_durum ad2 ON a2.aktivasyon_durum_id = ad2.aktivasyon_durum_id AND ad2.durum = 1
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_durum_renk,
				 
				(SELECT a2.aktivasyon_durum_id 
				 FROM aktivasyon a2 
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_durum_id,
				 
				(SELECT ad2.durum 
				 FROM aktivasyon a2 
				 LEFT JOIN aktivasyon_durum ad2 ON a2.aktivasyon_durum_id = ad2.aktivasyon_durum_id 
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_durum_aktif,
				
				(SELECT a2.aktivasyon_id 
				 FROM aktivasyon a2 
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_id,
				 
				(SELECT a2.aktivasyon_tarihi 
				 FROM aktivasyon a2 
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_tarihi,
				 
				(SELECT a2.olusturma_tarihi 
				 FROM aktivasyon a2 
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as olusturma_tarihi,
				 
				(SELECT a2.aktivasyon_uye_no 
				 FROM aktivasyon a2 
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_uye_no,
				 
				(SELECT a2.aktivasyon_kutu_no 
				 FROM aktivasyon a2 
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_kutu_no,
				 
				(SELECT a2.aktivasyon_kart_no 
				 FROM aktivasyon a2 
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_kart_no,
				 
				(SELECT a2.aktivasyon_aciklama 
				 FROM aktivasyon a2 
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_aciklama,
				 
				(SELECT a2.aktivasyon_kampanya_kodu 
				 FROM aktivasyon a2 
				 WHERE a2.aktivasyon_cari_id = c.cari_id 
				 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_kampanya_kodu,
				
				CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) as personel,
				k.kullanici_eposta,
				k.kullanici_id
				
			FROM cari c
			
			-- Tahsilat tutarını farklı tablolardan toplama yap
			LEFT JOIN (
				SELECT 
					c2.cari_id,
					(
						-- Banka/Pos tahsilatları
						COALESCE((SELECT SUM(bh.bh_giris) FROM bankahareketleri bh WHERE bh.bh_cariID = c2.cari_id), 0) +
						-- Çek tahsilatları  
						COALESCE((SELECT SUM(ck.cek_tutar) FROM cek ck WHERE ck.cek_cariID = c2.cari_id), 0) +
						-- Kasa/Nakit tahsilatları
						COALESCE((SELECT SUM(kh.kh_giris) FROM kasahareketleri kh WHERE kh.kh_cariID = c2.cari_id), 0) +
						-- Senet tahsilatları
						COALESCE((SELECT SUM(s.senet_tutar) FROM senet s WHERE s.senet_cariID = c2.cari_id), 0)
					) as toplam_tahsilat
				FROM cari c2
			) ch_toplam ON c.cari_id = ch_toplam.cari_id
			
			-- Banka görselleri sayısını al
			LEFT JOIN (
				SELECT 
					bh_cariID,
					COUNT(CASE WHEN bh_gorsel IS NOT NULL AND bh_gorsel != '' THEN 1 END) as banka_gorsel_sayisi
				FROM bankahareketleri 
				WHERE bh_cariID IS NOT NULL
				GROUP BY bh_cariID
			) banka_gorseller ON c.cari_id = banka_gorseller.bh_cariID
			
			-- Çek görsel sayısını al
			LEFT JOIN (
				SELECT 
					cek_cariID,
					COUNT(CASE WHEN cek_gorsel IS NOT NULL AND cek_gorsel != '' THEN 1 END) as cek_gorsel_sayisi
				FROM cek 
				WHERE cek_cariID IS NOT NULL
				GROUP BY cek_cariID
			) cek_gorseller ON c.cari_id = cek_gorseller.cek_cariID
			
			-- Senet görsel sayısını al
			LEFT JOIN (
				SELECT 
					senet_cariID,
					COUNT(CASE WHEN senet_gorsel IS NOT NULL AND senet_gorsel != '' THEN 1 END) as senet_gorsel_sayisi
				FROM senet 
				WHERE senet_cariID IS NOT NULL
				GROUP BY senet_cariID
			) senet_gorseller ON c.cari_id = senet_gorseller.senet_cariID
			
			-- Tahsilat görsellerini say (bankahareketleri ve kasahareketleri)
			LEFT JOIN (
				SELECT 
					ch.ch_cariID,
					SUM(
						CASE 
							WHEN bh.bh_gorsel IS NOT NULL AND bh.bh_gorsel != '' 
							THEN (LENGTH(bh.bh_gorsel) - LENGTH(REPLACE(bh.bh_gorsel, ',', '')) + 1)
							ELSE 0
						END +
						CASE 
							WHEN kh.kh_gorsel IS NOT NULL AND kh.kh_gorsel != '' 
							THEN (LENGTH(kh.kh_gorsel) - LENGTH(REPLACE(kh.kh_gorsel, ',', '')) + 1)
							ELSE 0
						END
					) as toplam_gorsel
				FROM carihareketleri ch
				LEFT JOIN bankahareketleri bh ON ch.ch_bhID = bh.bh_id
				LEFT JOIN kasahareketleri kh ON ch.ch_khID = kh.kh_id
				WHERE ch.ch_alacak IS NOT NULL AND ch.ch_alacak > 0
				GROUP BY ch.ch_cariID
			) tahsilat_gorseller ON c.cari_id = tahsilat_gorseller.ch_cariID
			
			LEFT JOIN kullanicilar k ON c.cari_olusturan = k.kullanici_id
			
			{$where_clause}
			GROUP BY c.cari_id
			{$having_clause}
			ORDER BY 
				CASE WHEN c.cari_olusturmaTarihi IS NULL THEN 1 ELSE 0 END,
				c.cari_olusturmaTarihi DESC, 
				c.cari_ad, c.cari_soyad
		";
			
			$data["rapor_verileri"] = $this->db->query($query)->result();
			
			// Hizmet onay durumu filtresini backend'de uygula
			// Gerçek muhasebe_tahsilat_durum tablosunu kullanarak
			if (!empty($hizmet_onay_durumu) && !empty($data["rapor_verileri"])) {
				$filtered_data = [];
				
				foreach($data["rapor_verileri"] as $veri) {
					$cari_id = $veri->cari_id;
					
					// Bu cari için tahsilat onay durumunu gerçek tablodan kontrol et
					$onayliTahsilat = $this->checkTahsilatOnayDurumu($cari_id);
					
					if ($hizmet_onay_durumu == 'onaylanan' && $onayliTahsilat) {
						$filtered_data[] = $veri;
					} elseif ($hizmet_onay_durumu == 'onaylanmayan' && !$onayliTahsilat) {
						$filtered_data[] = $veri;
					}
				}
				
				$data["rapor_verileri"] = $filtered_data;
			}
			
			// Ödeme durumu filtresini backend'de uygula
			if (!empty($odeme_durumu) && !empty($data["rapor_verileri"])) {
				$filtered_data = [];
				
				foreach($data["rapor_verileri"] as $veri) {
					$cari_id = $veri->cari_id;
					
					// Bu cari için ödeme durumunu kontrol et
					$odemeDurumu = $this->checkOdemeDurumu($cari_id, $odeme_durumu);
					
					if ($odemeDurumu) {
						$filtered_data[] = $veri;
					}
				}
				
				$data["rapor_verileri"] = $filtered_data;
			}
			
			// İstatistikler
			$data["toplam_kayit"] = count($data["rapor_verileri"]);
			
			$toplam_satis_tutar = 0;
			$toplam_tahsilat_tutar = 0;
			$onaylanan_sayisi = 0;
			$onay_bekleyen_sayisi = 0;
			
			foreach($data["rapor_verileri"] as $veri) {
				if($veri->satis_sozlesme_tutar_kdv_dahil) {
					$toplam_satis_tutar += $veri->satis_sozlesme_tutar_kdv_dahil;
				}
				if($veri->tahsilat_tutar) {
					$toplam_tahsilat_tutar += $veri->tahsilat_tutar;
				}
				// Aktivasyon durumuna göre sayaçları güncelle
				if($veri->aktivasyon_durum == 'Aktif' || $veri->aktivasyon_durum == 'Tamamlandı') {
					$onaylanan_sayisi++;
				}
				if($veri->aktivasyon_durum == 'Beklemede' || $veri->aktivasyon_durum == 'İşlemde') {
					$onay_bekleyen_sayisi++;
				}
			}
			
			$data["toplam_satis_tutar"] = $toplam_satis_tutar;
			$data["toplam_tahsilat_tutar"] = $toplam_tahsilat_tutar;
			$data["onaylanan_sayisi"] = $onaylanan_sayisi;
			$data["onay_bekleyen_sayisi"] = $onay_bekleyen_sayisi;
			
			// Onaylanmış tahsilatlara ait cari ID'leri al
			$data["onaylanan_cari_idleri"] = $this->getOnaylananCariIdleri();
			
		} catch (Exception $e) {
			$data["error_message"] = "Veri yuklenirken bir hata olustu: " . $e->getMessage();
			$data["rapor_verileri"] = [];
			$data["onaylanan_cari_idleri"] = [];
		}
		
		// View yukle
		$this->load->view("muhasebe/tam-ana-rapor", $data);
	}
	
	/**
	 * Cari için tahsilat onay durumunu kontrol et
	 */
	private function checkTahsilatOnayDurumu($cari_id)
	{
		try {
			// Muhasebe tahsilat durum tablosunun varlığını kontrol et
			$tableExists = $this->db->query("SHOW TABLES LIKE 'muhasebe_tahsilat_durum'")->num_rows();
			
			if ($tableExists > 0) {
				// Tablo varsa, onaylanmış tahsilat var mı kontrol et (cari ile bağlantılı olarak)
				$query = "
					SELECT COUNT(*) as onayli_count 
					FROM muhasebe_tahsilat_durum mtd
					LEFT JOIN bankaHareketleri bh ON (mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id)
					LEFT JOIN cek ck ON (mtd.tahsilat_tipi = 2 AND mtd.kayit_id = ck.cek_id)
					LEFT JOIN kasaHareketleri kh ON (mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id)
					LEFT JOIN senet s ON (mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id)
					WHERE mtd.onay_durumu = 1 
					AND (
						(mtd.tahsilat_tipi = 1 AND bh.bh_cariID = ?) OR
						(mtd.tahsilat_tipi = 2 AND ck.cek_cariID = ?) OR
						(mtd.tahsilat_tipi = 3 AND kh.kh_cariID = ?) OR
						(mtd.tahsilat_tipi = 4 AND s.senet_cariID = ?)
					)
				";
				$result = $this->db->query($query, [$cari_id, $cari_id, $cari_id, $cari_id])->row();
				return ($result && $result->onayli_count > 0);
			} else {
				// Tablo yoksa, herhangi bir tahsilat var mı basit kontrol
				// Banka hareketleri kontrol et
				$banka_count = $this->db->where('bh_cariID', $cari_id)->where('bh_giris >', 0)->count_all_results('bankahareketleri');
				if ($banka_count > 0) return true;
				
				// Çek kontrol et
				$cek_count = $this->db->where('cek_cariID', $cari_id)->where('cek_tutar >', 0)->count_all_results('cek');
				if ($cek_count > 0) return true;
				
				// Kasa hareketleri kontrol et
				$kasa_count = $this->db->where('kh_cariID', $cari_id)->where('kh_giris >', 0)->count_all_results('kasahareketleri');
				if ($kasa_count > 0) return true;
				
				// Senet kontrol et
				$senet_count = $this->db->where('senet_cariID', $cari_id)->where('senet_tutar >', 0)->count_all_results('senet');
				if ($senet_count > 0) return true;
				
				return false;
			}
		} catch (Exception $e) {
			// Hata durumunda false döndür
			return false;
		}
	}
	
	/**
	 * Onaylanmış tahsilatlara ait cari ID'leri getir
	 * Verilen sorgudaki gibi onay_durumu = 1 olan tahsilatlara ait cari ID'leri döndürür
	 */
	private function getOnaylananCariIdleri()
	{
		try {
			// Muhasebe tahsilat durum tablosunun varlığını kontrol et
			$tableExists = $this->db->query("SHOW TABLES LIKE 'muhasebe_tahsilat_durum'")->num_rows();
			
			if ($tableExists > 0) {
				// Tablo varsa onaylanmış tahsilatlara ait cari ID'leri al
				$query = "
					SELECT DISTINCT 
						CASE 
							WHEN mtd.tahsilat_tipi = 1 THEN bh.bh_cariID
							WHEN mtd.tahsilat_tipi = 2 THEN ck.cek_cariID
							WHEN mtd.tahsilat_tipi = 3 THEN kh.kh_cariID
							WHEN mtd.tahsilat_tipi = 4 THEN s.senet_cariID
						END AS cari_id
					FROM muhasebe_tahsilat_durum AS mtd
					LEFT JOIN bankahareketleri AS bh ON mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id
					LEFT JOIN cek AS ck ON mtd.tahsilat_tipi = 2 AND mtd.kayit_id = ck.cek_id
					LEFT JOIN kasahareketleri AS kh ON mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id
					LEFT JOIN senet AS s ON mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id
					WHERE mtd.onay_durumu = 1
					  AND (
							bh.bh_cariID IS NOT NULL 
						 OR ck.cek_cariID IS NOT NULL 
						 OR kh.kh_cariID IS NOT NULL 
						 OR s.senet_cariID IS NOT NULL
					  )
				";
				
				$result = $this->db->query($query)->result();
				$cari_idleri = [];
				
				foreach ($result as $row) {
					if (!empty($row->cari_id)) {
						$cari_idleri[] = $row->cari_id;
					}
				}
				
				return $cari_idleri;
			}
			
			return [];
			
		} catch (Exception $e) {
			// Hata durumunda boş array döndür
			return [];
		}
	}
	
	/**
	 * Cari için ödeme durumunu kontrol et
	 */
	private function checkOdemeDurumu($cari_id, $odeme_durumu)
	{
		try {
			// Muhasebe tahsilat durum tablosunun varlığını kontrol et
			$tableExists = $this->db->query("SHOW TABLES LIKE 'muhasebe_tahsilat_durum'")->num_rows();
			
			if ($tableExists > 0) {
				// Tablo varsa, belirtilen ödeme durumuna sahip tahsilat var mı kontrol et
				$query = "
					SELECT COUNT(*) as durum_count 
					FROM muhasebe_tahsilat_durum mtd
					LEFT JOIN bankaHareketleri bh ON (mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id)
					LEFT JOIN cek ck ON (mtd.tahsilat_tipi = 2 AND mtd.kayit_id = ck.cek_id)
					LEFT JOIN kasaHareketleri kh ON (mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id)
					LEFT JOIN senet s ON (mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id)
					WHERE mtd.durum = ? 
					AND (
						(mtd.tahsilat_tipi = 1 AND bh.bh_cariID = ?) OR
						(mtd.tahsilat_tipi = 2 AND ck.cek_cariID = ?) OR
						(mtd.tahsilat_tipi = 3 AND kh.kh_cariID = ?) OR
						(mtd.tahsilat_tipi = 4 AND s.senet_cariID = ?)
					)
				";
				$result = $this->db->query($query, [$odeme_durumu, $cari_id, $cari_id, $cari_id, $cari_id])->row();
				return ($result && $result->durum_count > 0);
			} else {
				// Tablo yoksa filtreleme yapılamaz, tüm kayıtları göster
				return true;
			}
		} catch (Exception $e) {
			// Hata durumunda true döndür (filtreleme yapma)
			return true;
		}
	}
	
	/**
	 * Tahsilat detaylarını getir - AJAX
	 */
	public function getTahsilatDetay()
	{
		// AJAX isteği kontrolü - Geçici olarak devre dışı
		// if (!$this->input->is_ajax_request()) {
		//	show_404();
		//	return;
		// }
		
		$cari_id = $this->input->post('cari_id');
		
		if (!$cari_id) {
			echo json_encode(['success' => false, 'message' => 'Cari ID gerekli']);
			return;
		}
		
		try {
			// Tahsilat detaylarını farklı tablolardan getir - cari hareket ID'si, görsel alanları ve onay durumu ile birlikte
			$query = "
				-- Banka/Pos tahsilatları
				SELECT 
					CONCAT('bh_', bh.bh_id) as unique_id,
					bh.bh_id as source_id,
					'Banka/Pos' as tahsilat_tipi,
					bh.bh_tarih as tarih,
					bh.bh_belgeNumarasi as belge_no,
					bh.bh_giris as tutar,
					bh.bh_aciklama as aciklama,
					b.banka_bankaAd as detay,
					'1' as tip_kod,
					ch.ch_id as cari_hareket_id,
					bh.bh_gorsel as gorsel_dosya,
					'dekontlar' as gorsel_klasor,
					NULL as vade_tarih,
					COALESCE(mtd.onay_durumu, 0) as onay_durumu
				FROM bankahareketleri bh
				LEFT JOIN banka b ON bh.bh_bankaID = b.banka_id
				LEFT JOIN carihareketleri ch ON ch.ch_bankaID = bh.bh_id AND ch.ch_cariID = bh.bh_cariID
				LEFT JOIN muhasebe_tahsilat_durum mtd ON mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id
				WHERE bh.bh_cariID = ? AND bh.bh_giris > 0
				
				UNION ALL
				
				-- Çek tahsilatları  
				SELECT 
					CONCAT('cek_', c.cek_id) as unique_id,
					c.cek_id as source_id,
					'Çek' as tahsilat_tipi,
					c.cek_vadeTarih as tarih,
					c.cek_portfoyNo as belge_no,
					c.cek_tutar as tutar,
					c.cek_notAciklama as aciklama,
					CONCAT('Çek No: ', c.cek_portfoyNo) as detay,
					'2' as tip_kod,
					ch.ch_id as cari_hareket_id,
					c.cek_gorsel as gorsel_dosya,
					'cekler' as gorsel_klasor,
					c.cek_vadeTarih as vade_tarih,
					COALESCE(mtd.onay_durumu, 0) as onay_durumu
				FROM cek c
				LEFT JOIN carihareketleri ch ON ch.ch_cekID = c.cek_id AND ch.ch_cariID = c.cek_cariID
				LEFT JOIN muhasebe_tahsilat_durum mtd ON mtd.tahsilat_tipi = 2 AND mtd.kayit_id = c.cek_id
				WHERE c.cek_cariID = ? AND c.cek_tutar > 0
				
				UNION ALL
				
				-- Kasa/Nakit tahsilatları
				SELECT 
					CONCAT('kh_', kh.kh_id) as unique_id,
					kh.kh_id as source_id,
					'Kasa/Nakit' as tahsilat_tipi,
					kh.kh_tarih as tarih,
					kh.kh_belgeNumarasi as belge_no,
					kh.kh_giris as tutar,
					kh.kh_aciklama as aciklama,
					k.kasa_adi as detay,
					'3' as tip_kod,
					ch.ch_id as cari_hareket_id,
					NULL as gorsel_dosya,
					NULL as gorsel_klasor,
					NULL as vade_tarih,
					COALESCE(mtd.onay_durumu, 0) as onay_durumu
				FROM kasahareketleri kh
				LEFT JOIN kasa k ON kh.kh_kasaID = k.kasa_id
				LEFT JOIN carihareketleri ch ON ch.ch_kasaID = kh.kh_id AND ch.ch_cariID = kh.kh_cariID
				LEFT JOIN muhasebe_tahsilat_durum mtd ON mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id
				WHERE kh.kh_cariID = ? AND kh.kh_giris > 0
				
				UNION ALL
				
				-- Senet tahsilatları
				SELECT 
					CONCAT('s_', s.senet_id) as unique_id,
					s.senet_id as source_id,
					'Senet' as tahsilat_tipi,
					s.senet_vadeTarih as tarih,
					s.senet_portfoyNo as belge_no,
					s.senet_tutar as tutar,
					s.senet_notAciklama as aciklama,
					CONCAT('Senet No: ', s.senet_portfoyNo) as detay,
					'4' as tip_kod,
					ch.ch_id as cari_hareket_id,
					s.senet_gorsel as gorsel_dosya,
					'senetler' as gorsel_klasor,
					s.senet_vadeTarih as vade_tarih,
					COALESCE(mtd.onay_durumu, 0) as onay_durumu
				FROM senet s
				LEFT JOIN carihareketleri ch ON ch.ch_senetID = s.senet_id AND ch.ch_cariID = s.senet_cariID
				LEFT JOIN muhasebe_tahsilat_durum mtd ON mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id
				WHERE s.senet_cariID = ? AND s.senet_tutar > 0
				
				ORDER BY tarih DESC
			";
			
			$result = $this->db->query($query, [$cari_id, $cari_id, $cari_id, $cari_id])->result();
			
			// Her kayıt için çoklu görsel bilgilerini al
			foreach ($result as $row) {
				$row->gorseller = $this->getTahsilatGorselleri($row->tip_kod, $row->source_id);
			}
			
			// Tarihleri formatla
			foreach ($result as $row) {
				$row->tarih = date('d.m.Y', strtotime($row->tarih));
				// Vade tarihini formatla (sadece çek ve senet için)
				if ($row->vade_tarih) {
					$row->vade_tarih_formatted = date('d.m.Y', strtotime($row->vade_tarih));
				} else {
					$row->vade_tarih_formatted = '-';
				}
			}
			
			echo json_encode([
				'success' => true,
				'data' => $result,
				'count' => count($result)
			]);
			
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'message' => 'Veri alınırken hata oluştu: ' . $e->getMessage()
			]);
		}
	}
	
	/**
	 * Tahsilat görsel listesini getir (çoklu)
	 */
	private function getTahsilatGorselleri($tipKod, $sourceId)
	{
		$gorseller = [];
		
		try {
			switch($tipKod) {
				case '1': // Banka
					$record = $this->db->select('bh_gorsel')->where('bh_id', $sourceId)->get('bankahareketleri')->row();
					if ($record && $record->bh_gorsel) {
						$dosya_listesi = explode(',', $record->bh_gorsel);
						$dosya_listesi = array_filter($dosya_listesi); // Boş elemanları temizle
						foreach ($dosya_listesi as $index => $dosya) {
							$gorseller[] = [
								'id' => 'bh_' . $sourceId . '_' . $index,
								'dosya_adi' => $dosya,
								'klasor' => 'dekontlar',
								'url' => base_url('assets/uploads/dekontlar/' . $dosya),
								'tip' => $index === 0 ? 'ana' : 'ek'
							];
						}
					}
					break;
					
				case '2': // Çek
					$record = $this->db->select('cek_gorsel')->where('cek_id', $sourceId)->get('cek')->row();
					if ($record && $record->cek_gorsel) {
						$dosya_listesi = explode(',', $record->cek_gorsel);
						$dosya_listesi = array_filter($dosya_listesi);
						foreach ($dosya_listesi as $index => $dosya) {
							$gorseller[] = [
								'id' => 'cek_' . $sourceId . '_' . $index,
								'dosya_adi' => $dosya,
								'klasor' => 'cekler',
								'url' => base_url('assets/uploads/cekler/' . $dosya),
								'tip' => $index === 0 ? 'ana' : 'ek'
							];
						}
					}
					break;
					
				case '4': // Senet
					$record = $this->db->select('senet_gorsel')->where('senet_id', $sourceId)->get('senet')->row();
					if ($record && $record->senet_gorsel) {
						$dosya_listesi = explode(',', $record->senet_gorsel);
						$dosya_listesi = array_filter($dosya_listesi);
						foreach ($dosya_listesi as $index => $dosya) {
							$gorseller[] = [
								'id' => 's_' . $sourceId . '_' . $index,
								'dosya_adi' => $dosya,
								'klasor' => 'senetler',
								'url' => base_url('assets/uploads/senetler/' . $dosya),
								'tip' => $index === 0 ? 'ana' : 'ek'
							];
						}
					}
					break;
					
				case '3': // Kasa - görsel yok
				default:
					break;
			}
			
			// İleride ek görsel tablosu eklenirse burada sorgulanabilir
			// Örnek: tahsilat_gorselleri tablosu
			
		} catch (Exception $e) {
			// Hata durumunda boş array dön
		}
		
		return $gorseller;
	}
	
	/**
	 * Tahsilat görsel yükleme (çoklu dosya)
	 */
	public function uploadTahsilatGorsel()
	{
		try {
			$tip = $this->input->post('tip'); // 'bh', 'cek', 'senet'
			$id = $this->input->post('id');
			
			if (!$tip || !$id) {
				echo json_encode(['success' => false, 'message' => 'Tip ve ID gerekli']);
				return;
			}
			
			// Dosya yükleme konfigürasyonu
			$config['upload_path'] = './assets/uploads/';
			$config['allowed_types'] = 'gif|jpg|png|jpeg|pdf';
			$config['max_size'] = 10240; // 10MB
			$config['encrypt_name'] = TRUE;
			
			// Tip'e göre klasör belirleme
			switch($tip) {
				case 'bh':
					$config['upload_path'] .= 'dekontlar/';
					break;
				case 'cek':
					$config['upload_path'] .= 'cekler/';
					break;
				case 'senet':
					$config['upload_path'] .= 'senetler/';
					break;
				default:
					echo json_encode(['success' => false, 'message' => 'Geçersiz tip']);
					return;
			}
			
			// Klasör yoksa oluştur
			if (!is_dir($config['upload_path'])) {
				mkdir($config['upload_path'], 0777, true);
			}
			
			$this->load->library('upload', $config);
			
			$uploaded_files = [];
			$upload_errors = [];
			
			// Çoklu dosya kontrolü
			if (isset($_FILES['gorseller']) && is_array($_FILES['gorseller']['name'])) {
				// Çoklu dosya yükleme
				$file_count = count($_FILES['gorseller']['name']);
				
				for ($i = 0; $i < $file_count; $i++) {
					if ($_FILES['gorseller']['error'][$i] == 0) {
						// Dosya bilgilerini tek dosya formatına çevir
						$_FILES['single_file']['name'] = $_FILES['gorseller']['name'][$i];
						$_FILES['single_file']['type'] = $_FILES['gorseller']['type'][$i];
						$_FILES['single_file']['tmp_name'] = $_FILES['gorseller']['tmp_name'][$i];
						$_FILES['single_file']['error'] = $_FILES['gorseller']['error'][$i];
						$_FILES['single_file']['size'] = $_FILES['gorseller']['size'][$i];
						
						if ($this->upload->do_upload('single_file')) {
							$upload_data = $this->upload->data();
							$uploaded_files[] = $upload_data['file_name'];
						} else {
							$upload_errors[] = $this->upload->display_errors('', '');
						}
					}
				}
			} else {
				// Tek dosya yükleme (mevcut sistem)
				if ($this->upload->do_upload('gorsel')) {
					$upload_data = $this->upload->data();
					$uploaded_files[] = $upload_data['file_name'];
				} else {
					$upload_errors[] = $this->upload->display_errors('', '');
				}
			}
			
			if (!empty($uploaded_files)) {
				// Mevcut dosyaları al
				$existing_files = [];
				switch($tip) {
					case 'bh':
						$record = $this->db->select('bh_gorsel')->where('bh_id', $id)->get('bankahareketleri')->row();
						if ($record && $record->bh_gorsel) {
							// Virgülle ayrılmış string formatından array'e çevir
							$existing_files = explode(',', $record->bh_gorsel);
							$existing_files = array_filter($existing_files); // Boş elemanları temizle
						}
						break;
					case 'cek':
						$record = $this->db->select('cek_gorsel')->where('cek_id', $id)->get('cek')->row();
						if ($record && $record->cek_gorsel) {
							$existing_files = explode(',', $record->cek_gorsel);
							$existing_files = array_filter($existing_files);
						}
						break;
					case 'senet':
						$record = $this->db->select('senet_gorsel')->where('senet_id', $id)->get('senet')->row();
						if ($record && $record->senet_gorsel) {
							$existing_files = explode(',', $record->senet_gorsel);
							$existing_files = array_filter($existing_files);
						}
						break;
				}
				
				// Yeni dosyaları mevcut dosyalarla birleştir
				$all_files = array_merge($existing_files, $uploaded_files);
				$files_string = implode(',', $all_files);
				
				// Veritabanını güncelle
				switch($tip) {
					case 'bh':
						$this->db->where('bh_id', $id);
						$this->db->update('bankahareketleri', ['bh_gorsel' => $files_string]);
						break;
					case 'cek':
						$this->db->where('cek_id', $id);
						$this->db->update('cek', ['cek_gorsel' => $files_string]);
						break;
					case 'senet':
						$this->db->where('senet_id', $id);
						$this->db->update('senet', ['senet_gorsel' => $files_string]);
						break;
				}
				
				header('Content-Type: application/json');
				echo json_encode([
					'success' => true,
					'message' => count($uploaded_files) . ' görsel başarıyla yüklendi',
					'files' => $uploaded_files,
					'total_files' => count($all_files),
					'errors' => $upload_errors
				]);
			} else {
				header('Content-Type: application/json');
				echo json_encode([
					'success' => false,
					'message' => 'Yükleme hatası: ' . implode(', ', $upload_errors)
				]);
			}
			
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'message' => 'Hata: ' . $e->getMessage()
			]);
		}
	}
	
	/**
	 * Tahsilat görsel silme
	 */
	public function deleteTahsilatGorsel()
	{
		try {
			$tip = $this->input->post('tip');
			$id = $this->input->post('id');
			
			if (!$tip || !$id) {
				echo json_encode(['success' => false, 'message' => 'Tip ve ID gerekli']);
				return;
			}
			
			// Mevcut dosyayı al
			$dosya_adi = null;
			switch($tip) {
				case 'bh':
					$record = $this->db->select('bh_gorsel')->where('bh_id', $id)->get('bankahareketleri')->row();
					$dosya_adi = $record ? $record->bh_gorsel : null;
					$klasor = 'dekontlar';
					break;
				case 'cek':
					$record = $this->db->select('cek_gorsel')->where('cek_id', $id)->get('cek')->row();
					$dosya_adi = $record ? $record->cek_gorsel : null;
					$klasor = 'cekler';
					break;
				case 'senet':
					$record = $this->db->select('senet_gorsel')->where('senet_id', $id)->get('senet')->row();
					$dosya_adi = $record ? $record->senet_gorsel : null;
					$klasor = 'senetler';
					break;
				default:
					echo json_encode(['success' => false, 'message' => 'Geçersiz tip']);
					return;
			}
			
			// JSON array kontrolü ve dosya silme
			if ($dosya_adi) {
				$dosya_listesi = explode(',', $dosya_adi);
				$dosya_listesi = array_filter($dosya_listesi); // Boş elemanları temizle
				
				// Tüm dosyaları sil
				foreach ($dosya_listesi as $dosya) {
					$dosya_yolu = './assets/uploads/' . $klasor . '/' . $dosya;
					if (file_exists($dosya_yolu)) {
						unlink($dosya_yolu);
					}
				}
			}
			
			// Veritabanını güncelle
			switch($tip) {
				case 'bh':
					$this->db->where('bh_id', $id);
					$this->db->update('bankahareketleri', ['bh_gorsel' => null]);
					break;
				case 'cek':
					$this->db->where('cek_id', $id);
					$this->db->update('cek', ['cek_gorsel' => null]);
					break;
				case 'senet':
					$this->db->where('senet_id', $id);
					$this->db->update('senet', ['senet_gorsel' => null]);
					break;
			}
			
			echo json_encode([
				'success' => true,
				'message' => 'Görsel başarıyla silindi'
			]);
			
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		}
	}
	
	/**
	 * Tahsilat güncelleme (tutar ve vade tarihi)
	 */
	public function tahsilatGuncelle()
	{
		header('Content-Type: application/json');
		
		try {
			$degisiklikler = $this->input->post('degisiklikler');
			
			if (!$degisiklikler || !is_array($degisiklikler)) {
				echo json_encode(['success' => false, 'message' => 'Değişiklik verileri bulunamadı']);
				return;
			}
			
			$this->db->trans_start();
			$basarili_guncelleme = 0;
			$hata_mesajlari = [];
			
			foreach ($degisiklikler as $degisiklik) {
				$tip_kod = $degisiklik['tip_kod'];
				$source_id = $degisiklik['source_id'];
				$degisiklik_tipi = $degisiklik['degisiklik_tipi'];
				
				try {
					if ($degisiklik_tipi == 'tutar') {
						// Tutar güncelleme
						$yeni_tutar = floatval($degisiklik['yeni_tutar']);
						
						switch($tip_kod) {
							case '1': // Banka
								$this->db->where('bh_id', $source_id);
								$this->db->update('bankahareketleri', ['bh_giris' => $yeni_tutar]);
								break;
								
							case '2': // Çek
								$this->db->where('cek_id', $source_id);
								$this->db->update('cek', ['cek_tutar' => $yeni_tutar]);
								break;
								
							case '3': // Kasa
								$this->db->where('kh_id', $source_id);
								$this->db->update('kasahareketleri', ['kh_giris' => $yeni_tutar]);
								break;
								
							case '4': // Senet
								$this->db->where('senet_id', $source_id);
								$this->db->update('senet', ['senet_tutar' => $yeni_tutar]);
								break;
						}
						
						// Cari hareketi de güncelle
						if (isset($degisiklik['ch_id']) && $degisiklik['ch_id']) {
							$this->db->where('ch_id', $degisiklik['ch_id']);
							$this->db->update('carihareketleri', ['ch_alacak' => $yeni_tutar]);
						}
						
					} elseif ($degisiklik_tipi == 'vade_tarihi') {
						// Vade tarihi güncelleme (sadece çek ve senet için)
						$yeni_vade_tarihi = $degisiklik['yeni_vade_tarihi'];
						
						if ($tip_kod == '2') { // Çek
							$this->db->where('cek_id', $source_id);
							$this->db->update('cek', ['cek_vadeTarih' => $yeni_vade_tarihi]);
						} elseif ($tip_kod == '4') { // Senet
							$this->db->where('senet_id', $source_id);
							$this->db->update('senet', ['senet_vadeTarih' => $yeni_vade_tarihi]);
						}
					}
					
					$basarili_guncelleme++;
					
				} catch (Exception $e) {
					$hata_mesajlari[] = "ID {$source_id} güncellenirken hata: " . $e->getMessage();
				}
			}
			
			$this->db->trans_complete();
			
			if ($this->db->trans_status() === FALSE) {
				echo json_encode([
					'success' => false,
					'message' => 'Veritabanı işlemi başarısız: ' . implode(', ', $hata_mesajlari)
				]);
			} else {
				echo json_encode([
					'success' => true,
					'message' => "{$basarili_guncelleme} adet kayıt başarıyla güncellendi.",
					'basarili_guncelleme' => $basarili_guncelleme,
					'hata_sayisi' => count($hata_mesajlari)
				]);
			}
			
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'message' => 'Güncelleme sırasında hata oluştu: ' . $e->getMessage()
			]);
		}
	}

	/**
	 * Yeni tahsilat ekleme fonksiyonu
	 */
	public function yeniTahsilatEkle()
	{
		$tip_kod = $this->input->post('tip_kod');
		$tutar = $this->input->post('tutar');
		$tarih = $this->input->post('tarih');
		$belge_no = $this->input->post('belge_no');
		$aciklama = $this->input->post('aciklama');
		$cari_id = $this->input->post('cari_id');
		
		if (!$tip_kod || !$tutar || !$tarih || !$cari_id) {
			echo json_encode(['success' => false, 'message' => 'Gerekli alanlar eksik']);
			return;
		}
		
		try {
			$this->db->trans_start();
			
			$olusturan = $this->session->userdata('kullanici_id') ?? 1;
			$olusturanAnaHesap = $this->session->userdata('anaHesap_id') ?? 1;
			$bugun = date('Y-m-d');
			$suan = date('H:i:s');
			
			// Tip koduna göre ilgili tabloya kayıt ekle
			switch($tip_kod) {
				case '1': // Banka
					$banka_data = [
						'bh_belgeNumarasi' => $belge_no,
						'bh_turu' => 1, // Giriş
						'bh_giris' => $tutar,
						'bh_cikis' => 0,
						'bh_cariID' => $cari_id,
						'bh_bankaID' => 1, // Varsayılan banka
						'bh_tarih' => $tarih,
						'bh_aciklama' => $aciklama,
						'bh_olusturan' => $olusturan,
						'bh_olusturanAnaHesap' => $olusturanAnaHesap,
						'bh_olusturmaTarihi' => $bugun,
						'bh_olusturmaSaati' => $suan,
						'tahsilat_durum_id' => 2
					];
					$this->db->insert('bankahareketleri', $banka_data);
					$source_id = $this->db->insert_id();
					break;
					
				case '2': // Çek
					$cek_data = [
						'cek_cariID' => $cari_id,
						'cek_hareketTipi' => 1,
						'cek_kayitTarihi' => $tarih,
						'cek_notAciklama' => $aciklama,
						'cek_portfoyNo' => $belge_no,
						'cek_vadeTarih' => $tarih,
						'cek_tutar' => $tutar,
						'cek_durum' => 1,
						'cek_kullaniciID' => $olusturan,
						'cek_olusturanAnaHesap' => $olusturanAnaHesap,
						'cek_sistemKayitTarihi' => $bugun,
						'cek_sistemKayitSaati' => $suan
					];
					$this->db->insert('cek', $cek_data);
					$source_id = $this->db->insert_id();
					break;
					
				case '3': // Kasa
					$kasa_data = [
						'kh_belgeNumarasi' => $belge_no,
						'kh_turu' => 1,
						'kh_giris' => $tutar,
						'kh_cikis' => 0,
						'kh_cariID' => $cari_id,
						'kh_tarih' => $tarih,
						'kh_aciklama' => $aciklama,
						'kh_kasaID' => 1, // Varsayılan kasa
						'kh_olusturan' => $olusturan,
						'kh_olusturanAnaHesap' => $olusturanAnaHesap,
						'kh_olusturmaTarihi' => $bugun,
						'kh_olusturmaSaati' => $suan
					];
					$this->db->insert('kasahareketleri', $kasa_data);
					$source_id = $this->db->insert_id();
					break;
					
				case '4': // Senet
					$senet_data = [
						'senet_cariID' => $cari_id,
						'senet_hareketTipi' => 1,
						'senet_kayitTarihi' => $tarih,
						'senet_notAciklama' => $aciklama,
						'senet_portfoyNo' => $belge_no,
						'senet_vadeTarih' => $tarih,
						'senet_tutar' => $tutar,
						'senet_durum' => 1,
						'senet_kullaniciID' => $olusturan,
						'senet_olusturanAnaHesap' => $olusturanAnaHesap,
						'senet_sistemKayitTarihi' => $bugun,
						'senet_sistemKayitSaati' => $suan
					];
					$this->db->insert('senet', $senet_data);
					$source_id = $this->db->insert_id();
					break;
					
				default:
					throw new Exception('Geçersiz tahsilat tipi');
			}
			
			// Cari hareket kaydı ekle
			$cari_hareket_data = [
				'ch_cariID' => $cari_id,
				'ch_sfID' => $satis_id,
				'ch_tip' => 'Satış Faturası',
				'ch_borc' => 0,
				'ch_alacak' => $tutar,
				'ch_aciklama' => $aciklama,
				'ch_olusturan' => $olusturan,
				'ch_olusturanAnaHesap' => $olusturanAnaHesap,
				'ch_olusturmaTarihi' => $bugun,
				'ch_olusturmaSaati' => $suan
			];
			
			// İlgili ID'yi ayarla
			switch($tip_kod) {
				case '1': $cari_hareket_data['ch_bankaID'] = $source_id; break;
				case '2': $cari_hareket_data['ch_cekID'] = $source_id; break;
				case '3': $cari_hareket_data['ch_kasaID'] = $source_id; break;
				case '4': $cari_hareket_data['ch_senetID'] = $source_id; break;
			}
			
			$this->db->insert('carihareketleri', $cari_hareket_data);
			
			$this->db->trans_complete();
			
			if ($this->db->trans_status() === FALSE) {
				throw new Exception('Veritabanı işlemi başarısız');
			}
			
			echo json_encode(['success' => true, 'message' => 'Yeni tahsilat başarıyla eklendi']);
			
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => 'Tahsilat eklenirken hata oluştu: ' . $e->getMessage()]);
		}
	}
	
	/**
	 * Stok görsel yükleme fonksiyonu
	 */
	public function stokGorselYukle()
	{
		// Debug: Method called
		log_message('debug', 'stokGorselYukle method called');
		log_message('debug', 'POST data: ' . print_r($_POST, true));
		log_message('debug', 'FILES data: ' . print_r($_FILES, true));
		
		$satisStok_id = $this->input->post('satisStok_id');
		$stok_adi = $this->input->post('stok_adi');
		
		if (!$satisStok_id) {
			echo json_encode(['success' => false, 'message' => 'Stok ID gerekli']);
			return;
		}
		
		// $_FILES kontrolü - array syntax ile gelen dosyalar
		if (empty($_FILES['stok_gorseller']) || !isset($_FILES['stok_gorseller']['tmp_name'])) {
			echo json_encode(['success' => false, 'message' => 'Dosya seçilmedi']);
			return;
		}
		
		try {
			// Upload path - relative path kullan
			$upload_path = './assets/uploads/stok_gorselleri/';
			if (!is_dir($upload_path)) {
				if (!mkdir($upload_path, 0777, true)) {
					throw new Exception('Upload klasörü oluşturulamadı: ' . $upload_path);
				}
			}
			
			$uploaded_files = [];
			$files = $_FILES['stok_gorseller'];
			
			// Debug: File structure
			log_message('debug', 'File structure: ' . print_r($files, true));
			
			// Tek dosya mı yoksa çoklu dosya mı kontrolü
			if (is_array($files['tmp_name'])) {
				// Çoklu dosya yükleme işlemi
				$file_count = count($files['tmp_name']);
				log_message('debug', 'Multiple files detected: ' . $file_count);
				
				for ($i = 0; $i < $file_count; $i++) {
					if (isset($files['error'][$i]) && $files['error'][$i] == UPLOAD_ERR_OK) {
						$tmp_name = $files['tmp_name'][$i];
						$original_name = $files['name'][$i];
						$size = $files['size'][$i];
						$type = isset($files['type'][$i]) ? $files['type'][$i] : '';
						
						// Dosya boyutu kontrolü (max 10MB)
						if ($size > 10 * 1024 * 1024) {
							throw new Exception($original_name . ' dosyası çok büyük (max 10MB)');
						}
						
						// Dosya tipi kontrolü - daha geniş format desteği
						$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
						$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
						if (!in_array($ext, $allowed_extensions)) {
							throw new Exception($original_name . ' dosya tipi desteklenmiyor. İzin verilen: ' . implode(', ', $allowed_extensions));
						}
						
						// Dosya adını oluştur
						$filename = 'stok_' . $satisStok_id . '_' . uniqid() . '.' . $ext;
						$filepath = $upload_path . $filename;
						
						// Debug: File upload attempt
						log_message('debug', 'Attempting to upload: ' . $tmp_name . ' to ' . $filepath);
						
						// Dosyayı yükle
						if (move_uploaded_file($tmp_name, $filepath)) {
							$uploaded_files[] = $filename;
							log_message('debug', 'File uploaded successfully: ' . $filename);
						} else {
							log_message('error', 'Failed to upload file: ' . $original_name);
							throw new Exception($original_name . ' dosyası yüklenemedi');
						}
					} else {
						$error_code = isset($files['error'][$i]) ? $files['error'][$i] : 'Unknown';
						log_message('error', 'File upload error for file ' . $i . ': ' . $error_code);
					}
				}
			} else {
				// Tek dosya yükleme
				if ($files['error'] == UPLOAD_ERR_OK) {
					$tmp_name = $files['tmp_name'];
					$original_name = $files['name'];
					$size = $files['size'];
					$type = $files['type'];
					
					// Dosya boyutu kontrolü (max 10MB)
					if ($size > 10 * 1024 * 1024) {
						throw new Exception($original_name . ' dosyası çok büyük (max 10MB)');
					}
					
					// Dosya tipi kontrolü
					$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
					$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
					if (!in_array($ext, $allowed_extensions)) {
						throw new Exception($original_name . ' dosya tipi desteklenmiyor');
					}
					
					// Dosya adını oluştur
					$filename = 'stok_' . $satisStok_id . '_' . uniqid() . '.' . $ext;
					$filepath = $upload_path . $filename;
					
					// Dosyayı yükle
					if (move_uploaded_file($tmp_name, $filepath)) {
						$uploaded_files[] = $filename;
					} else {
						throw new Exception($original_name . ' dosyası yüklenemedi');
					}
				}
			}
			
			if (empty($uploaded_files)) {
				throw new Exception('Hiçbir dosya yüklenemedi');
			}
			
			// Debug: Uploaded files
			log_message('debug', 'Uploaded files: ' . print_r($uploaded_files, true));
			
			// Mevcut görsel listesini al
			$current_record = $this->db->select('satisStok_gorsel')->where('satisStok_id', $satisStok_id)->get('satisstok')->row();
			$current_images = [];
			
			if ($current_record && $current_record->satisStok_gorsel) {
				$current_images = explode(',', $current_record->satisStok_gorsel);
				$current_images = array_filter($current_images); // Boş elemanları temizle
			}
			
			// Yeni dosyaları ekle
			$all_images = array_merge($current_images, $uploaded_files);
			$image_string = implode(',', $all_images);
			
			// Debug: Database update
			log_message('debug', 'Updating database for satisStok_id: ' . $satisStok_id . ' with images: ' . $image_string);
			
			// Veritabanını güncelle
			$this->db->where('satisStok_id', $satisStok_id);
			$update_result = $this->db->update('satisstok', ['satisStok_gorsel' => $image_string]);
			
			if (!$update_result) {
				log_message('error', 'Database update failed: ' . $this->db->error());
				throw new Exception('Veritabanı güncellenemedi');
			}
			
			log_message('debug', 'Database updated successfully');
			
			echo json_encode([
				'success' => true, 
				'message' => count($uploaded_files) . ' dosya başarıyla yüklendi',
				'uploaded_files' => $uploaded_files
			]);
			
		} catch (Exception $e) {
			log_message('error', 'stokGorselYukle error: ' . $e->getMessage());
			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		}
	}
	
	/**
	 * Stok görsel silme fonksiyonu
	 */
	public function stokGorselSil()
	{
		$gorsel_id = $this->input->post('gorsel_id');
		$satisStok_id = $this->input->post('satisStok_id');
		
		if (!$gorsel_id || !$satisStok_id) {
			echo json_encode(['success' => false, 'message' => 'Gerekli parametreler eksik']);
			return;
		}
		
		try {
			// Dosya adını görsel ID'den çıkar
			$filename_parts = explode('_', $gorsel_id);
			$filename = end($filename_parts);
			
			// Mevcut görsel listesini al
			$current_record = $this->db->select('satisStok_gorsel')->where('satisStok_id', $satisStok_id)->get('satisstok')->row();
			
			if (!$current_record || !$current_record->satisStok_gorsel) {
				throw new Exception('Görsel bulunamadı');
			}
			
			$current_images = explode(',', $current_record->satisStok_gorsel);
			$current_images = array_filter($current_images);
			
			// Silinecek dosyayı listeden çıkar
			$updated_images = array_filter($current_images, function($img) use ($filename) {
				return $img !== $filename;
			});
			
			// Veritabanını güncelle
			$image_string = implode(',', $updated_images);
			$this->db->where('satisStok_id', $satisStok_id);
			$this->db->update('satisstok', ['satisStok_gorsel' => $image_string]);
			
			// Dosyayı fiziksel olarak sil
			$file_path = FCPATH . 'assets/uploads/stok_gorselleri/' . $filename;
			if (file_exists($file_path)) {
				unlink($file_path);
			}
			
			echo json_encode(['success' => true, 'message' => 'Görsel başarıyla silindi']);
			
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		}
	}

	/**
	 * Tahsilat silme fonksiyonu
	 */
	public function tahsilatSil()
	{
		try {
			// POST verilerini al
			$unique_id = $this->input->post('unique_id');
			$tip_kod = $this->input->post('tip_kod');
			$source_id = $this->input->post('source_id');
			$ch_id = $this->input->post('ch_id'); // Eski sistem için
			
			// Debug log
			error_log("Tahsilat Silme İsteği: unique_id=$unique_id, tip_kod=$tip_kod, source_id=$source_id, ch_id=$ch_id");
			
			// Parametre kontrolü
			if (empty($ch_id) && (empty($unique_id) || empty($tip_kod) || empty($source_id))) {
				throw new Exception('Gerekli parametreler eksik');
			}
			
			$this->db->trans_start();
			
			// Eski sistem ile uyumluluk (ch_id varsa)
			if (!empty($ch_id)) {
				// Cari hareket ID'si ile silme
				$cari_hareket = $this->db->where('ch_id', $ch_id)->get('cariHareketleri')->row();
				if (!$cari_hareket) {
					throw new Exception('Cari hareket kaydı bulunamadı');
				}
				
				// Cari hareket kaydını sil
				$this->db->where('ch_id', $ch_id)->delete('cariHareketleri');
				
				if ($this->db->affected_rows() == 0) {
					throw new Exception('Cari hareket kaydı silinemedi');
				}
				
				error_log("Cari hareket silindi: ch_id=$ch_id");
				
			} else {
				// Yeni sistem ile silme (unique_id, tip_kod, source_id)
				$deleted = false;
				
				switch ($tip_kod) {
					case '1': // Banka Hareketleri
						$record = $this->db->where('bh_id', $source_id)->get('bankaHareketleri')->row();
						if ($record) {
							// Görsel dosyasını sil
							if (!empty($record->bh_gorsel)) {
								$file_path = FCPATH . 'assets/uploads/dekontlar/' . $record->bh_gorsel;
								if (file_exists($file_path)) {
									unlink($file_path);
								}
							}
							
							// Banka hareket kaydını sil
							$this->db->where('bh_id', $source_id)->delete('bankaHareketleri');
							$deleted = $this->db->affected_rows() > 0;
						}
						break;
						
					case '2': // Çekler
						$record = $this->db->where('cek_id', $source_id)->get('cek')->row();
						if ($record) {
							// Görsel dosyasını sil
							if (!empty($record->cek_gorsel)) {
								$file_path = FCPATH . 'assets/uploads/cekler/' . $record->cek_gorsel;
								if (file_exists($file_path)) {
									unlink($file_path);
								}
							}
							
							// Çek kaydını sil
							$this->db->where('cek_id', $source_id)->delete('cek');
							$deleted = $this->db->affected_rows() > 0;
						}
						break;
						
					case '3': // Kasa Hareketleri
						$record = $this->db->where('kh_id', $source_id)->get('kasaHareketleri')->row();
						if ($record) {
							// Kasa hareket kaydını sil
							$this->db->where('kh_id', $source_id)->delete('kasaHareketleri');
							$deleted = $this->db->affected_rows() > 0;
						}
						break;
						
					case '4': // Senetler
						$record = $this->db->where('senet_id', $source_id)->get('senet')->row();
						if ($record) {
							// Görsel dosyasını sil
							if (!empty($record->senet_gorsel)) {
								$file_path = FCPATH . 'assets/uploads/senetler/' . $record->senet_gorsel;
								if (file_exists($file_path)) {
									unlink($file_path);
								}
							}
							
							// Senet kaydını sil
							$this->db->where('senet_id', $source_id)->delete('senet');
							$deleted = $this->db->affected_rows() > 0;
						}
						break;
						
					default:
						throw new Exception('Bilinmeyen tahsilat tipi: ' . $tip_kod);
				}
				
				if (!$deleted) {
					throw new Exception('Tahsilat kaydı silinemedi veya bulunamadı');
				}
				
				error_log("Tahsilat silindi: tip_kod=$tip_kod, source_id=$source_id");
			}
			
			$this->db->trans_complete();
			
			if ($this->db->trans_status() === FALSE) {
				throw new Exception('Veritabanı işlemi başarısız');
			}
			
			echo json_encode([
				'success' => true,
				'message' => 'Tahsilat kaydı başarıyla silindi'
			]);
			
		} catch (Exception $e) {
			$this->db->trans_rollback();
			error_log("Tahsilat Silme Hatası: " . $e->getMessage());
			
			echo json_encode([
				'success' => false,
				'message' => 'Tahsilat silinirken beklenmeyen bir hata oluştu: ' . $e->getMessage()
			]);
		}
	}

	/**
	 * Tek Veri Türü Silme İşlemi
	 */
	public function tek_veri_sil()
	{
		header('Content-Type: application/json');
		
		// Yetki kontrolü
		if (!grup_modul_yetkisi_var(530)) {
			echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.']);
			return;
		}

		// Kütüphaneleri yükle
		if (!isset($this->db)) {
			$this->load->database();
		}

		$cari_id = $_POST['cari_id'] ?? null;
		$tablo_kodu = $_POST['tablo_kodu'] ?? null;
		$tablo_adi = $_POST['tablo_adi'] ?? null;
		
		if (!$cari_id || !$tablo_kodu || !$tablo_adi) {
			echo json_encode(['status' => 'error', 'message' => 'Gerekli parametreler eksik.']);
			return;
		}

		// Transaction başlat
		$this->db->trans_begin();
		
		try {
			$silinen_kayit_sayisi = 0;
			
			switch ($tablo_kodu) {
				case 'satis_faturalari':
					// İlgili stok kayıtlarını sil
					$this->db->query("DELETE sfs FROM satisfaturasistok sfs 
									 INNER JOIN satisfaturasi sf ON sfs.satisStok_satisFaturasiID = sf.satis_id 
									 WHERE sf.satis_cariID = ?", [$cari_id]);
					
					// Satış faturalarını sil
					$this->db->where('satis_cariID', $cari_id);
					$this->db->delete('satisfaturasi');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'alis_faturalari':
					// İlgili stok kayıtlarını sil
					$this->db->query("DELETE als FROM alisfaturasistok als 
									 INNER JOIN alisfaturasi af ON als.alisStok_alisFaturasiID = af.alis_id 
									 WHERE af.alis_cariID = ?", [$cari_id]);
					
					// Alış faturalarını sil
					$this->db->where('alis_cariID', $cari_id);
					$this->db->delete('alisfaturasi');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'proforma_faturalari':
					// İlgili stok kayıtlarını sil
					$this->db->query("DELETE pfs FROM proformafaturasistok pfs 
									 INNER JOIN proformafaturasi pf ON pfs.proformaStok_proformaFaturasiID = pf.proforma_id 
									 WHERE pf.proforma_cariID = ?", [$cari_id]);
					
					// Proforma faturalarını sil
					$this->db->where('proforma_cariID', $cari_id);
					$this->db->delete('proformafaturasi');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'cari_hareketleri':
					// Cari hareketlerini sil
					$this->db->where('ch_cariID', $cari_id);
					$this->db->delete('carihareketleri');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'banka_hareketleri':
					// Banka hareketlerini sil
					$this->db->where('bh_cariID', $cari_id);
					$this->db->delete('bankahareketleri');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'kasa_hareketleri':
					// Kasa hareketlerini sil
					$this->db->where('kh_cariID', $cari_id);
					$this->db->delete('kasahareketleri');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'cekler':
					// İlgili muhasebe tahsilat durumunu sil
					$id_list = $this->db->select('cek_id')->where('cek_cariID', $cari_id)->get('cek')->result_array();
					$id_list = array_column($id_list, 'cek_id');
					if (!empty($id_list)) {
						$id_list = implode(',', $id_list);
						$this->db->query("DELETE FROM muhasebe_tahsilat_durum 
										 WHERE tahsilat_tipi = 2 AND kayit_id IN ($id_list)");
					}
					
					// Çekleri sil
					$this->db->where('cek_cariID', $cari_id);
					$this->db->delete('cek');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'senetler':
					// İlgili muhasebe tahsilat durumunu sil
					$id_list = $this->db->select('senet_id')->where('senet_cariID', $cari_id)->get('senet')->result_array();
					$id_list = array_column($id_list, 'senet_id');
					if (!empty($id_list)) {
						$id_list = implode(',', $id_list);
						$this->db->query("DELETE FROM muhasebe_tahsilat_durum 
										 WHERE tahsilat_tipi = 4 AND kayit_id IN ($id_list)");
					}
					
					// Senetleri sil
					$this->db->where('senet_cariID', $cari_id);
					$this->db->delete('senet');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				default:
					throw new Exception('Bilinmeyen tablo kodu: ' . $tablo_kodu);
			}

			// Transaction'ı tamamla
			$this->db->trans_commit();
			
			if ($this->db->trans_status() === FALSE) {
				throw new Exception('Veritabanı işlemi başarısız');
			}

			echo json_encode([
				'status' => 'success',
				'message' => $tablo_adi . ' başarıyla silindi. (' . $silinen_kayit_sayisi . ' kayıt silindi)'
			]);

		} catch (Exception $e) {
			$this->db->trans_rollback();
			echo json_encode([
				'status' => 'error',
				'message' => 'Silme işlemi sırasında hata oluştu: ' . $e->getMessage()
			]);
		}
	}
	
	/**
	 * Veri Listesi Getir - Seçmeli Silme İçin
	 */
	public function veri_listesi_getir()
	{
		// JSON header ayarla
		header('Content-Type: application/json');
		
		try {
			// Yetki kontrolü
			if (!grup_modul_yetkisi_var(530)) {
				echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.']);
				return;
			}

			$cari_id = $_POST['cari_id'] ?? null;
			$tablo_kodu = $_POST['tablo_kodu'] ?? null;
			$tablo_adi = $_POST['tablo_adi'] ?? null;
			
			if (!$cari_id || !$tablo_kodu || !$tablo_adi) {
				echo json_encode(['status' => 'error', 'message' => 'Gerekli parametreler eksik.']);
				return;
			}
			
			if (!isset($this->db)) {
				$this->load->database();
			}
			
			$data = [];
			$columns = [];
			
			switch ($tablo_kodu) {
				case 'satis_faturalari':
					$query = $this->db->select('satis_id as id, satis_id as fatura_no, satis_faturaTarihi as fatura_tarihi, satis_aciklama as aciklama')
									  ->where('satis_cariID', $cari_id)
									  ->get('satisfaturasi');
					$data = $query->result_array();
					$columns = ['Fatura No', 'Fatura Tarihi', 'Açıklama'];
					break;
					
				case 'alis_faturalari':
					$query = $this->db->select('alis_id as id, alis_faturaNo, alis_faturaTarihi, alis_genelToplam, alis_aciklama')
									  ->where('alis_cariID', $cari_id)
									  ->get('alisfaturasi');
					$data = $query->result_array();
					$columns = ['Fatura No', 'Fatura Tarihi', 'Genel Toplam', 'Açıklama'];
					break;
					
				case 'proforma_faturalari':
					$query = $this->db->select('proforma_id as id, proforma_faturaNo, proforma_faturaTarihi, proforma_genelToplam, proforma_aciklama')
									  ->where('proforma_cariID', $cari_id)
									  ->get('proformafaturasi');
					$data = $query->result_array();
					$columns = ['Fatura No', 'Fatura Tarihi', 'Genel Toplam', 'Açıklama'];
					break;
					
				case 'cari_hareketleri':
					$query = $this->db->select('ch_id as id, ch_tarih, ch_aciklama, ch_borc, ch_alacak')
									  ->where('ch_cariID', $cari_id)
									  ->get('carihareketleri');
					$data = $query->result_array();
					$columns = ['Tarih', 'Açıklama', 'Borç', 'Alacak'];
					break;
					
				case 'cekler':
					$query = $this->db->select('cek_id as id, cek_no, cek_vade, cek_tutar, cek_aciklama')
									  ->where('cek_cariID', $cari_id)
									  ->get('cek');
					$data = $query->result_array();
					$columns = ['Çek No', 'Vade', 'Tutar', 'Açıklama'];
					break;
					
				case 'senetler':
					$query = $this->db->select('senet_id as id, senet_no, senet_vade, senet_tutar, senet_aciklama')
									  ->where('senet_cariID', $cari_id)
									  ->get('senet');
					$data = $query->result_array();
					$columns = ['Senet No', 'Vade', 'Tutar', 'Açıklama'];
					break;
					
				default:
					throw new Exception('Bilinmeyen tablo kodu: ' . $tablo_kodu);
			}
			
			if (empty($data)) {
				echo json_encode([
					'status' => 'info',
					'message' => $tablo_adi . ' tablosunda kayıt bulunamadı.'
				]);
				return;
			}
			
			echo json_encode([
				'status' => 'success',
				'data' => $data,
				'columns' => $columns,
				'message' => count($data) . ' kayıt bulundu.'
			]);
			
		} catch (Exception $e) {
			error_log("Veri listesi getir hatası: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
			echo json_encode([
				'status' => 'error',
				'message' => 'Veri listesi getirilirken hata oluştu: ' . $e->getMessage()
			]);
		} catch (Error $e) {
			error_log("Veri listesi getir fatal hatası: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
			echo json_encode([
				'status' => 'error',
				'message' => 'Fatal hata: ' . $e->getMessage()
			]);
		}
	}
	
	/**
	 * Seçili Kayıtları Sil
	 */
	public function secili_kayitlari_sil()
	{
		header('Content-Type: application/json');
		
		// Yetki kontrolü
		if (!grup_modul_yetkisi_var(530)) {
			echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.']);
			return;
		}

		// Kütüphaneleri yükle
		if (!isset($this->db)) {
			$this->load->database();
		}

		$cari_id = $_POST['cari_id'] ?? null;
		$tablo_kodu = $_POST['tablo_kodu'] ?? null;
		$tablo_adi = $_POST['tablo_adi'] ?? null;
		$secilen_idler = $_POST['secilen_idler'] ?? null;
		
		if (!$cari_id || !$tablo_kodu || !$tablo_adi || !$secilen_idler || !is_array($secilen_idler)) {
			echo json_encode(['status' => 'error', 'message' => 'Gerekli parametreler eksik veya hatalı.']);
			return;
		}

		// ID'leri güvenlik için temizle
		$temiz_idler = array_map('intval', $secilen_idler);
		$temiz_idler = array_filter($temiz_idler, function($id) { return $id > 0; });
		
		if (empty($temiz_idler)) {
			echo json_encode(['status' => 'error', 'message' => 'Geçerli ID bulunamadı.']);
			return;
		}

		// Transaction başlat
		$this->db->trans_begin();
		
		try {
			$silinen_kayit_sayisi = 0;
			$id_list = implode(',', $temiz_idler);
			
			switch ($tablo_kodu) {
				case 'satis_faturalari':
					// İlgili stok kayıtlarını sil
					$this->db->query("DELETE FROM satisfaturasistok 
									 WHERE satisStok_satisFaturasiID IN ($id_list)");
					
					// Satış faturalarını sil
					$this->db->where('satis_cariID', $cari_id);
					$this->db->where_in('satis_id', $temiz_idler);
					$this->db->delete('satisfaturasi');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'alis_faturalari':
					// İlgili stok kayıtlarını sil
					$this->db->query("DELETE FROM alisfaturasistok 
									 WHERE alisStok_alisFaturasiID IN ($id_list)");
					
					// Alış faturalarını sil
					$this->db->where('alis_cariID', $cari_id);
					$this->db->where_in('alis_id', $temiz_idler);
					$this->db->delete('alisfaturasi');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'proforma_faturalari':
					// İlgili stok kayıtlarını sil
					$this->db->query("DELETE FROM proformafaturasistok 
									 WHERE proformaStok_proformaFaturasiID IN ($id_list)");
					
					// Proforma faturalarını sil
					$this->db->where('proforma_cariID', $cari_id);
					$this->db->where_in('proforma_id', $temiz_idler);
					$this->db->delete('proformafaturasi');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'cari_hareketleri':
					// Cari hareketlerini sil
					$this->db->where('ch_cariID', $cari_id);
					$this->db->where_in('ch_id', $temiz_idler);
					$this->db->delete('carihareketleri');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'banka_hareketleri':
					// Banka hareketlerini sil
					$this->db->where('bh_cariID', $cari_id);
					$this->db->where_in('bh_id', $temiz_idler);
					$this->db->delete('bankahareketleri');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'kasa_hareketleri':
					// Kasa hareketlerini sil
					$this->db->where('kh_cariID', $cari_id);
					$this->db->where_in('kh_id', $temiz_idler);
					$this->db->delete('kasahareketleri');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'cekler':
					// İlgili muhasebe tahsilat durumunu sil
					$this->db->query("DELETE FROM muhasebe_tahsilat_durum 
									 WHERE tahsilat_tipi = 2 AND kayit_id IN ($id_list)");
					
					// Çekleri sil
					$this->db->where('cek_cariID', $cari_id);
					$this->db->where_in('cek_id', $temiz_idler);
					$this->db->delete('cek');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				case 'senetler':
					// İlgili muhasebe tahsilat durumunu sil
					$this->db->query("DELETE FROM muhasebe_tahsilat_durum 
									 WHERE tahsilat_tipi = 4 AND kayit_id IN ($id_list)");
					
					// Senetleri sil
					$this->db->where('senet_cariID', $cari_id);
					$this->db->where_in('senet_id', $temiz_idler);
					$this->db->delete('senet');
					$silinen_kayit_sayisi = $this->db->affected_rows();
					break;
					
				default:
					throw new Exception('Bilinmeyen tablo kodu: ' . $tablo_kodu);
			}

			// Transaction'ı tamamla
			$this->db->trans_commit();
			
			if ($this->db->trans_status() === FALSE) {
				throw new Exception('Veritabanı işlemi başarısız');
			}

			echo json_encode([
				'status' => 'success',
				'message' => count($temiz_idler) . ' adet ' . $tablo_adi . ' kaydı başarıyla silindi.'
			]);

		} catch (Exception $e) {
			$this->db->trans_rollback();
			echo json_encode([
				'status' => 'error',
				'message' => 'Silme işlemi sırasında hata oluştu: ' . $e->getMessage()
			]);
		}
	}

	/**
	 * Cariye ait satış detaylarını getir - AJAX
	 */
	public function testSatisList()
	{
		$cari_id = $this->input->post('cari_id');
		
		if (!$cari_id) {
			echo json_encode(['success' => false, 'message' => 'Cari ID gerekli']);
			return;
		}
		
		try {
			// Satış faturası bilgilerini getir (stok bilgileri ile birlikte)
			$query = "
				SELECT 
					sf.satis_id as satis_sozlesme_id,
					sf.satis_vergiDahilToplam as satis_sozlesme_tutari,
					-- Stok adı bilgisi
					GROUP_CONCAT(
						CASE 
							WHEN s.stok_stokGrupKoduID = 1 THEN 'DİGİTÜRK'
							WHEN s.stok_id = 16 THEN 'S SPORT'
							WHEN s.stok_id = 13 THEN 'TABİİ'
							ELSE COALESCE(s.stok_ad, 'Bilinmeyen Stok')
						END
						SEPARATOR ', '
					) as satis_sozlesme_hizmet,
					-- Görsel sayısını hesapla
					CASE 
						WHEN sf.satis_dosya IS NOT NULL AND sf.satis_dosya != '' 
						THEN (LENGTH(sf.satis_dosya) - LENGTH(REPLACE(sf.satis_dosya, ',', '')) + 1)
						ELSE 0
					END as gorsel_sayisi
				FROM satisFaturasi sf
				LEFT JOIN satisFaturasiStok sfs ON sfs.satisStok_satisFaturasiID = sf.satis_id
				LEFT JOIN stok s ON sfs.satisStok_stokID = s.stok_id
				WHERE sf.satis_cariID = ? 
				GROUP BY sf.satis_id
				ORDER BY sf.satis_id DESC
			";
			
			$satislar = $this->db->query($query, [$cari_id])->result();
			
			// Verileri formatla
			foreach($satislar as $satis) {
				// Tutarı formatla
				if($satis->satis_sozlesme_tutari) {
					$satis->satis_sozlesme_tutari = number_format($satis->satis_sozlesme_tutari, 2, ',', '.') . ' TL';
				}
				// Tarih bilgisini satış ID'sine göre ayarla
				$satis->tarih = 'Satış #' . $satis->satis_sozlesme_id;
				// Eğer hizmet bilgisi boş ise varsayılan değer ver
				if(empty($satis->satis_sozlesme_hizmet)) {
					$satis->satis_sozlesme_hizmet = 'Hizmet Bilgisi Yok';
				}
			}
			
			echo json_encode([
				'success' => true, 
				'satislar' => $satislar
			]);
			
		} catch (Exception $e) {
			echo json_encode([
				'success' => false, 
				'message' => 'Satış bilgileri yüklenirken hata oluştu: ' . $e->getMessage()
			]);
		}
	}

	/**
	 * Tam Ana Rapor Excel Export - Yetki kontrolsüz
	 */
	public function tam_ana_rapor_excel()
	{
		// Memory limit artır (performans için)
		ini_set('memory_limit', '512M');
		ini_set('max_execution_time', 600);
		
		// Yetki kontrolü kaldırıldı - herkes erişebilir
		
		try {
			// PhpSpreadsheet kütüphanelerini yükle
			require_once FCPATH . 'vendor/autoload.php';
			
			// Arama parametrelerini al (tam_ana_rapor ile aynı)
			$cari_search = $this->input->get('cari_search');
			$aktivasyon_durum = $this->input->get('aktivasyon_durum');
			
			// WHERE koşulları için dizi
			$where_conditions = ['c.cari_durum = 1'];
			
			// Cari arama koşulu ekleme
			if (!empty($cari_search)) {
				$search_term = $this->db->escape_like_str($cari_search);
				$where_conditions[] = "(
					c.cari_ad LIKE '%{$search_term}%' OR 
					c.cari_soyad LIKE '%{$search_term}%' OR 
					c.cari_vergiNumarasi LIKE '%{$search_term}%' OR 
					c.cari_firmaTelefon LIKE '%{$search_term}%'
				)";
			}
			
			// WHERE koşullarını birleştir
			$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
			
			// Aktivasyon durum filtresi için HAVING koşulu
			$having_clause = '';
			if (!empty($aktivasyon_durum)) {
				$having_clause = "HAVING aktivasyon_durum = '" . $this->db->escape_str($aktivasyon_durum) . "'";
			}
			
			// Tam Ana Rapor sorgusu (tam_ana_rapor metodundan kopyalandı)
			$query = "
				SELECT DISTINCT
					c.cari_id,
					c.cari_ad,
					c.cari_soyad,
					CASE 
						WHEN c.cari_bireysel = 0 OR c.cari_soyad IS NULL OR c.cari_soyad = '' 
						THEN c.cari_ad 
						ELSE CONCAT(c.cari_ad, ' ', c.cari_soyad)
					END as cari_isletme,
					
					-- Kayıt tarihi (en yeni önce sıralama için)
					COALESCE(c.cari_olusturmaTarihi, '2000-01-01') as kayit_tarihi,
					
					-- Satış sözleşmesi bilgileri (en yeni satış)
					(SELECT sf2.satis_id FROM satisFaturasi sf2 WHERE sf2.satis_cariID = c.cari_id ORDER BY sf2.satis_id DESC LIMIT 1) as satis_sozlesme_id,
					(SELECT sf2.satis_vergiDahilToplam FROM satisFaturasi sf2 WHERE sf2.satis_cariID = c.cari_id ORDER BY sf2.satis_id DESC LIMIT 1) as satis_sozlesme_tutar,
					(SELECT sf2.satis_vergiDahilToplam FROM satisFaturasi sf2 WHERE sf2.satis_cariID = c.cari_id ORDER BY sf2.satis_id DESC LIMIT 1) as satis_sozlesme_tutar_kdv_dahil,
					-- Toplam satış sayısı
					(SELECT COUNT(*) FROM satisFaturasi sf2 WHERE sf2.satis_cariID = c.cari_id) as toplam_satis_sayisi,
					-- Stok adı bilgisi (en yeni satıştan) - birden fazla stok göstermek için GROUP_CONCAT kullan
					(SELECT GROUP_CONCAT(
						CASE 
							WHEN s.stok_stokGrupKoduID = 1 THEN 'DIGITURK'
							WHEN s.stok_id = 16 THEN 'S SPORT'
							WHEN s.stok_id = 13 THEN 'TABII'
							ELSE s.stok_ad
						END
						SEPARATOR ', '
					 )
					 FROM satisFaturasi sf2 
					 INNER JOIN satisFaturasiStok sfs ON sfs.satisStok_satisFaturasiID = sf2.satis_id
					 INNER JOIN stok s ON sfs.satisStok_stokID = s.stok_id
					 WHERE sf2.satis_cariID = c.cari_id AND sf2.satis_id = (
						 SELECT sf3.satis_id FROM satisFaturasi sf3 WHERE sf3.satis_cariID = c.cari_id ORDER BY sf3.satis_id DESC LIMIT 1
					 )
					 GROUP BY sf2.satis_id
					) as satis_sozlesme_hizmeti,
					
					-- Tahsilat tutarını farklı tablolardan toplama yap (banka, çek, kasa, senet)
					COALESCE(ch_toplam.toplam_tahsilat, 0) as tahsilat_tutar,
					
					-- Aktivasyon durumu (en yeni aktivasyondan)
					(SELECT COALESCE(ad2.aktivasyon_durum_adi, 'Tanımlanmamış') 
					 FROM aktivasyon a2 
					 LEFT JOIN aktivasyon_durum ad2 ON a2.aktivasyon_durum_id = ad2.aktivasyon_durum_id AND ad2.durum = 1
					 WHERE a2.aktivasyon_cari_id = c.cari_id 
					 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_durum,
					 
					(SELECT a2.aktivasyon_tarihi 
					 FROM aktivasyon a2 
					 WHERE a2.aktivasyon_cari_id = c.cari_id 
					 ORDER BY a2.aktivasyon_id DESC LIMIT 1) as aktivasyon_tarihi,
					
					CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) as personel
					
				FROM cari c
				
				-- Tahsilat tutarını farklı tablolardan toplama yap
				LEFT JOIN (
					SELECT 
						c2.cari_id,
						(
							-- Banka/Pos tahsilatları
							COALESCE((SELECT SUM(bh.bh_giris) FROM bankahareketleri bh WHERE bh.bh_cariID = c2.cari_id), 0) +
							-- Çek tahsilatları  
							COALESCE((SELECT SUM(ck.cek_tutar) FROM cek ck WHERE ck.cek_cariID = c2.cari_id), 0) +
							-- Kasa/Nakit tahsilatları
							COALESCE((SELECT SUM(kh.kh_giris) FROM kasahareketleri kh WHERE kh.kh_cariID = c2.cari_id), 0) +
							-- Senet tahsilatları
							COALESCE((SELECT SUM(s.senet_tutar) FROM senet s WHERE s.senet_cariID = c2.cari_id), 0)
						) as toplam_tahsilat
					FROM cari c2
				) ch_toplam ON c.cari_id = ch_toplam.cari_id
				
				LEFT JOIN kullanicilar k ON c.cari_olusturan = k.kullanici_id
				
				{$where_clause}
				GROUP BY c.cari_id
				{$having_clause}
				ORDER BY 
					CASE WHEN c.cari_olusturmaTarihi IS NULL THEN 1 ELSE 0 END,
					c.cari_olusturmaTarihi DESC, 
					c.cari_ad, c.cari_soyad
			";
			
			$rapor_verileri = $this->db->query($query)->result();
			
			// Excel dosyası oluştur
			$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			
			// Başlık satırı
			$headers = [
				'A1' => 'Cari Ad',
				'B1' => 'İşletme',
				'C1' => 'Satış Sözleşme Tutarı',
				'D1' => 'Satış Sözleşme Hizmeti',
				'E1' => 'Satış Sözleşme Tutarı (KDV Dahil)',
				'F1' => 'Tahsilat Tutar',
				'G1' => 'Aktivasyon Durum',
				'H1' => 'Aktivasyon Tarihi',
				'I1' => 'Personel'
			];
			
			// Başlıkları yaz
			foreach ($headers as $cell => $header) {
				$sheet->setCellValue($cell, $header);
			}
			
			// Başlık satırını formatla
			$sheet->getStyle('A1:I1')->getFont()->setBold(true);
			$sheet->getStyle('A1:I1')->getFill()
				->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('CCCCCC');
			
			// Verileri yaz
			$row = 2;
			foreach ($rapor_verileri as $veri) {
				$sheet->setCellValue('A' . $row, $veri->cari_ad . ' ' . $veri->cari_soyad);
				$sheet->setCellValue('B' . $row, $veri->cari_isletme);
				$sheet->setCellValue('C' . $row, $veri->satis_sozlesme_tutar ? number_format($veri->satis_sozlesme_tutar, 2, ',', '.') : '0');
				$sheet->setCellValue('D' . $row, $veri->satis_sozlesme_hizmeti ?: '-');
				$sheet->setCellValue('E' . $row, $veri->satis_sozlesme_tutar_kdv_dahil ? number_format($veri->satis_sozlesme_tutar_kdv_dahil, 2, ',', '.') : '0');
				$sheet->setCellValue('F' . $row, $veri->tahsilat_tutar ? number_format($veri->tahsilat_tutar, 2, ',', '.') : '0');
				$sheet->setCellValue('G' . $row, $veri->aktivasyon_durum ?: 'Tanımlanmamış');
				$sheet->setCellValue('H' . $row, $veri->aktivasyon_tarihi ? date('d.m.Y', strtotime($veri->aktivasyon_tarihi)) : '-');
				$sheet->setCellValue('I' . $row, $veri->personel ?: '-');
				$row++;
			}
			
			// Sütun genişliklerini ayarla
			foreach (range('A', 'I') as $column) {
				$sheet->getColumnDimension($column)->setAutoSize(true);
			}
			
			// Dosya adı ve tarih
			date_default_timezone_set('Europe/Istanbul');
			$tarih = date('d-m-Y_H-i-s');
			$filename = 'tam-ana-rapor-' . $tarih;
			
			// Excel dosyasını oluştur ve indir
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
			
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
			header('Cache-Control: max-age=0');
			
			$writer->save('php://output');
			
		} catch (Exception $e) {
			// Hata durumunda kullanıcıyı bilgilendir
			$this->session->set_flashdata('excel_hata', 'Excel export sırasında hata oluştu: ' . $e->getMessage());
			redirect('muhasebe/tam-ana-rapor');
		}
	}

	// getCariDetaylar method removed - payment details no longer shown in Stok Bilgileri modal
	
	/**
	 * Tahsilat onay durumunu güncelle
	 */
	public function updateTahsilatOnay()
	{
		// AJAX isteği kontrolü
		if (!$this->input->is_ajax_request()) {
			show_404();
			return;
		}
		
		// Kullanıcı grup bilgisini al
		$control = session("r", "login_info");
		$kullanici_grup_id = $control ? $control->grup_id : null;
		
		$tahsilat_id = $this->input->post('tahsilat_id');
		$onay_durumu = $this->input->post('onay_durumu');
		
		if (!$tahsilat_id) {
			echo json_encode(['success' => false, 'message' => 'Tahsilat ID gerekli']);
			return;
		}
		
		try {
			// Grup tabanlı güncelleme için özel kontrol
			if (strpos($tahsilat_id, '_group') !== false) {
				// Cari ID gerekli
				$cari_id = $this->input->post('cari_id');
				if (!$cari_id) {
					echo json_encode(['success' => false, 'message' => 'Cari ID gerekli']);
					return;
				}
				
				// Tip kodunu belirle
				$tip_kod = '';
				if ($tahsilat_id === 'bh_group') {
					$tip_kod = '1'; // Banka
				} elseif ($tahsilat_id === 'cek_group') {
					$tip_kod = '2'; // Çek
				} elseif ($tahsilat_id === 'kh_group') {
					$tip_kod = '3'; // Kasa
				} elseif ($tahsilat_id === 's_group') {
					$tip_kod = '4'; // Senet
				} else {
					echo json_encode(['success' => false, 'message' => 'Geçersiz grup tahsilat tipi']);
					return;
				}
				
				// Onay durumu 1 ise ve kullanıcı grup_id 1 veya 2 değilse işlemi reddet
				if ($onay_durumu == 1 && !in_array($kullanici_grup_id, [1, 2])) {
					echo json_encode(['success' => false, 'message' => 'Onaylanmış tahsilatları sadece yetkili kullanıcılar değiştirebilir']);
					return;
				}
				
				// Onay durumu 0'dan 1'e çekiliyorsa tüm kayıtlar için yetki kontrolü
				// Bu cari için bu tip tahsilatın mevcut kayıtlarını kontrol et
				$table_map = [
					'1' => 'bankahareketleri',
					'2' => 'cek',
					'3' => 'kasahareketleri',
					'4' => 'senet'
				];
				
				$id_field_map = [
					'1' => 'bh_id',
					'2' => 'cek_id',
					'3' => 'kh_id',
					'4' => 'senet_id'
				];
				
				$cari_field_map = [
					'1' => 'bh_cariID',
					'2' => 'cek_cariID',
					'3' => 'kh_cariID',
					'4' => 'senet_cariID'
				];
				
				$table = $table_map[$tip_kod];
				$id_field = $id_field_map[$tip_kod];
				$cari_field = $cari_field_map[$tip_kod];
				
				// Bu cari için bu tip tahsilatın kayıt ID'lerini al
				$source_ids_query = "SELECT {$id_field} FROM {$table} WHERE {$cari_field} = ?";
				$result = $this->db->query($source_ids_query, [$cari_id]);
				$source_ids = $result->result_array();
				
				// Eğer 0'dan 1'e çekiliyorsa, mevcut onaylı kayıtları kontrol et
				if ($onay_durumu == 1) {
					foreach ($source_ids as $row) {
						$source_id = $row[$id_field];
						
						// Mevcut kaydın onay durumunu kontrol et
						$existing = $this->db->where('tahsilat_tipi', $tip_kod)
											 ->where('kayit_id', $source_id)
											 ->get('muhasebe_tahsilat_durum')
											 ->row();
						
						// Eğer kayıt zaten onaylıysa ve kullanıcı yetkili değilse işlemi reddet
						if ($existing && $existing->onay_durumu == 1 && !in_array($kullanici_grup_id, [1, 2])) {
							echo json_encode(['success' => false, 'message' => 'Bu tahsilatlardan bazıları zaten onaylı. Sadece yetkili kullanıcılar değiştirebilir']);
							return;
						}
					}
				}
				
				$updated_count = 0;
				foreach ($source_ids as $row) {
					$source_id = $row[$id_field];
					
					// muhasebe_tahsilat_durum tablosunda ilgili kaydı bul ve güncelle
					$this->db->where('tahsilat_tipi', $tip_kod);
					$this->db->where('kayit_id', $source_id);
					
					// Eğer kayıt yoksa ekle, varsa güncelle
					$existing = $this->db->get('muhasebe_tahsilat_durum')->row();
					
					if ($existing) {
						// Güncelle
						$this->db->where('id', $existing->id);
						$update_data = array(
							'onay_durumu' => $onay_durumu ? 1 : 0,
							'guncelleme_tarihi' => date('Y-m-d H:i:s')
						);
						$this->db->update('muhasebe_tahsilat_durum', $update_data);
						if ($this->db->affected_rows() > 0) {
							$updated_count++;
						}
					} else {
						// Yeni kayıt ekle
						$insert_data = array(
							'tahsilat_tipi' => $tip_kod,
							'kayit_id' => $source_id,
							'durum' => 1, // Varsayılan durum
							'onay_durumu' => $onay_durumu ? 1 : 0,
							'olusturma_tarihi' => date('Y-m-d H:i:s')
						);
						$this->db->insert('muhasebe_tahsilat_durum', $insert_data);
						if ($this->db->affected_rows() > 0) {
							$updated_count++;
						}
					}
				}
				
				if ($updated_count > 0) {
					echo json_encode(array('success' => true, 'message' => "$updated_count kayıt güncellendi"));
				} else {
					echo json_encode(array('success' => false, 'message' => 'Güncelleme işlemi gerçekleşmedi'));
				}
				
			} else {
				// Tekil güncelleme - mevcut mantık
				$parts = explode('_', $tahsilat_id);
				if (count($parts) < 2) {
					echo json_encode(['success' => false, 'message' => 'Geçersiz tahsilat ID formatı']);
					return;
				}
				
				$tip_prefix = $parts[0];
				$source_id = $parts[1];
				
				// Tip kodunu belirle
				$tip_kod = '';
				switch($tip_prefix) {
					case 'bh':
						$tip_kod = '1';
						break;
					case 'cek':
						$tip_kod = '2';
						break;
					case 'kh':
						$tip_kod = '3';
						break;
					case 's':
						$tip_kod = '4';
						break;
					default:
						echo json_encode(['success' => false, 'message' => 'Geçersiz tahsilat tipi']);
						return;
				}
				
				// muhasebe_tahsilat_durum tablosunda ilgili kaydı bul ve güncelle
				$this->db->where('tahsilat_tipi', $tip_kod);
				$this->db->where('kayit_id', $source_id);
				
				// Eğer kayıt yoksa ekle, varsa güncelle
				$existing = $this->db->get('muhasebe_tahsilat_durum')->row();
				
				// Eğer mevcut kayıt onaylı ise ve kullanıcı yetkili değilse işlemi reddet
				if ($existing && $existing->onay_durumu == 1 && !in_array($kullanici_grup_id, [1, 2])) {
					echo json_encode(['success' => false, 'message' => 'Onaylanmış tahsilatları sadece yetkili kullanıcılar değiştirebilir']);
					return;
				}
				
				if ($existing) {
					// Güncelle
					$this->db->where('id', $existing->id);
					$update_data = array(
						'onay_durumu' => $onay_durumu ? 1 : 0,
						'guncelleme_tarihi' => date('Y-m-d H:i:s')
					);
					$this->db->update('muhasebe_tahsilat_durum', $update_data);
				} else {
					// Yeni kayıt ekle
					$insert_data = array(
						'tahsilat_tipi' => $tip_kod,
						'kayit_id' => $source_id,
						'durum' => 1, // Varsayılan durum
						'onay_durumu' => $onay_durumu ? 1 : 0,
						'olusturma_tarihi' => date('Y-m-d H:i:s')
					);
					$this->db->insert('muhasebe_tahsilat_durum', $insert_data);
				}
				
				if ($this->db->affected_rows() > 0) {
					echo json_encode(array('success' => true, 'message' => 'Onay durumu başarıyla güncellendi'));
				} else {
					echo json_encode(array('success' => false, 'message' => 'Güncelleme işlemi gerçekleşmedi'));
				}
			}
			
		} catch (Exception $e) {
			echo json_encode(array('success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()));
		}
	}
	
	/**
	 * Türkçe ay adlarını döndürür
	 */
	private function getTurkceAyAdi($ay_num) {
		$aylar = array(
			1 => 'Ocak',
			2 => 'Şubat', 
			3 => 'Mart',
			4 => 'Nisan',
			5 => 'Mayıs',
			6 => 'Haziran',
			7 => 'Temmuz',
			8 => 'Ağustos',
			9 => 'Eylül',
			10 => 'Ekim',
			11 => 'Kasım',
			12 => 'Aralık'
		);
		
		return isset($aylar[$ay_num]) ? $aylar[$ay_num] : 'Bilinmiyor';
	}

	/**
	 * Debug method for testing month data
	 */
	public function debugMonths()
	{
		// Session kontrolü atla, sadece debug için
		echo "<h2>Testing Month Generation for Tahsilat Listesi</h2>";
		echo "<p>Current Date: " . date('Y-m-d') . "</p>";
		
		// Test query from controller
		$aylar_query = "
		SELECT DISTINCT DATE_FORMAT(vade_ay, '%Y-%m') AS ay_deger,
			   YEAR(vade_ay) AS yil,
			   MONTH(vade_ay) AS ay_num
		FROM (
			SELECT cek_vadeTarih AS vade_ay FROM cek WHERE cek_vadeTarih IS NOT NULL
			UNION ALL
			SELECT senet_vadeTarih AS vade_ay FROM senet WHERE senet_vadeTarih IS NOT NULL
			UNION ALL
			SELECT bh_tarih AS vade_ay FROM bankaHareketleri WHERE bh_tarih IS NOT NULL
			UNION ALL
			SELECT kh_tarih AS vade_ay FROM kasaHareketleri WHERE kh_tarih IS NOT NULL
		) AS birlesik
		WHERE vade_ay != '0000-00-00' AND vade_ay IS NOT NULL
		ORDER BY vade_ay DESC";
		
		$mevcut_aylar = $this->db->query($aylar_query)->result();
		
		echo "<h3>Months Found in Database:</h3>";
		echo "<table border='1' style='border-collapse: collapse;'>";
		echo "<tr><th>Month Value</th><th>Year</th><th>Month Number</th><th>Display Name</th></tr>";
		
		$eklenen_aylar = array();
		foreach($mevcut_aylar as $ay) {
			if ($ay->ay_deger && !in_array($ay->ay_deger, $eklenen_aylar)) {
				$ay_adi = $this->getTurkceAyAdi($ay->ay_num);
				echo "<tr>";
				echo "<td>{$ay->ay_deger}</td>";
				echo "<td>{$ay->yil}</td>";
				echo "<td>{$ay->ay_num}</td>";
				echo "<td>{$ay_adi} {$ay->yil}</td>";
				echo "</tr>";
				$eklenen_aylar[] = $ay->ay_deger;
			}
		}
		echo "</table>";
		
		echo "<h3>Test URLs:</h3>";
		echo "<ul>";
		foreach($eklenen_aylar as $ay_deger) {
			echo "<li><a href='/muhasebe/tahsilat-listesi?tahsilat_ayi={$ay_deger}' target='_blank'>Tahsilat Listesi - {$ay_deger}</a></li>";
		}
		echo "</ul>";
		
		// Test specific October 2025
		echo "<h3>Testing October 2025 Data Details:</h3>";
		$test_query = "
		SELECT 'cek' as tip, cek_id as id, cek_vadeTarih as tarih, cek_tutar as tutar 
		FROM cek 
		WHERE YEAR(cek_vadeTarih) = 2025 AND MONTH(cek_vadeTarih) = 10
		UNION ALL
		SELECT 'senet' as tip, senet_id as id, senet_vadeTarih as tarih, senet_tutar as tutar 
		FROM senet 
		WHERE YEAR(senet_vadeTarih) = 2025 AND MONTH(senet_vadeTarih) = 10
		ORDER BY tarih DESC";
		
		$ekim_data = $this->db->query($test_query)->result();
		
		if (count($ekim_data) > 0) {
			echo "<p><strong>Found " . count($ekim_data) . " records for October 2025:</strong></p>";
			echo "<table border='1' style='border-collapse: collapse;'>";
			echo "<tr><th>Type</th><th>ID</th><th>Date</th><th>Amount</th></tr>";
			foreach($ekim_data as $record) {
				echo "<tr>";
				echo "<td>{$record->tip}</td>";
				echo "<td>{$record->id}</td>";
				echo "<td>{$record->tarih}</td>";
				echo "<td>" . number_format($record->tutar, 2) . "</td>";
				echo "</tr>";
			}
			echo "</table>";
		} else {
			echo "<p>No records found for October 2025</p>";
		}
		
		// Test muhasebe_tahsilat_durum table for October 2025
		echo "<h3>Testing muhasebe_tahsilat_durum for October 2025:</h3>";
		$mtd_query = "
		SELECT COUNT(*) as toplam, tahsilat_tipi,
			   CASE 
				   WHEN tahsilat_tipi = 1 THEN 'Banka'
				   WHEN tahsilat_tipi = 2 THEN 'Çek'
				   WHEN tahsilat_tipi = 3 THEN 'Kasa'
				   WHEN tahsilat_tipi = 4 THEN 'Senet'
			   END as tip_adi
		FROM muhasebe_tahsilat_durum mtd
		LEFT JOIN cek c ON mtd.tahsilat_tipi = 2 AND mtd.kayit_id = c.cek_id
		LEFT JOIN senet s ON mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id
		WHERE (
			(mtd.tahsilat_tipi = 2 AND YEAR(c.cek_vadeTarih) = 2025 AND MONTH(c.cek_vadeTarih) = 10) OR
			(mtd.tahsilat_tipi = 4 AND YEAR(s.senet_vadeTarih) = 2025 AND MONTH(s.senet_vadeTarih) = 10) OR
			(mtd.tahsilat_tipi IN (1,3) AND YEAR(mtd.olusturma_tarihi) = 2025 AND MONTH(mtd.olusturma_tarihi) = 10)
		)
		GROUP BY tahsilat_tipi
		ORDER BY tahsilat_tipi";
		
		$mtd_data = $this->db->query($mtd_query)->result();
		
		if (count($mtd_data) > 0) {
			echo "<table border='1' style='border-collapse: collapse;'>";
			echo "<tr><th>Tahsilat Tipi</th><th>Tip Adı</th><th>Kayıt Sayısı</th></tr>";
			$total_mtd = 0;
			foreach($mtd_data as $mtd_record) {
				echo "<tr>";
				echo "<td>{$mtd_record->tahsilat_tipi}</td>";
				echo "<td>{$mtd_record->tip_adi}</td>";
				echo "<td>{$mtd_record->toplam}</td>";
				echo "</tr>";
				$total_mtd += $mtd_record->toplam;
			}
			echo "<tr style='background-color: #f0f0f0; font-weight: bold;'>";
			echo "<td colspan='2'>TOPLAM</td>";
			echo "<td>{$total_mtd}</td>";
			echo "</tr>";
			echo "</table>";
		}
	}

	/**
	 * Dashboard için onay bekleyen tahsilat verilerini AJAX ile döndürür
	 */
	public function getOnayBekleyenTahsilat() {
		$anaHesap = anaHesapBilgisi();
		
		try {
			// Onay bekleyen tahsilatların listesini çek (aynı sorgu onayBekleyenTahsilatlar'daki gibi)
			$query = "SELECT 
				mtd.tahsilat_tutar as tutar
				FROM muhasebe_tahsilat_durum mtd
				LEFT JOIN bankaHareketleri bh ON (mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id)
				LEFT JOIN cek ck ON (mtd.tahsilat_tipi = 2 AND mtd.kayit_id = ck.cek_id)
				LEFT JOIN kasaHareketleri kh ON (mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id)
				LEFT JOIN senet s ON (mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id)
				WHERE mtd.tahsilat_olusturanAnaHesap = '$anaHesap'
				AND mtd.tahsilat_durum = 1";
			
			$result = $this->db->query($query)->result();
			
			$toplam_adet = count($result);
			$toplam_tutar = 0;
			
			foreach($result as $tahsilat) {
				if($tahsilat->tutar) {
					$toplam_tutar += $tahsilat->tutar;
				}
			}
			
			$response = [
				'success' => true,
				'adet' => number_format($toplam_adet),
				'tutar' => number_format($toplam_tutar, 2, ',', '.')
			];
			
		} catch (Exception $e) {
			$response = [
				'success' => false,
				'error' => $e->getMessage()
			];
		}
		
		header('Content-Type: application/json');
		echo json_encode($response);
	}
	
	/**
	 * Detaylı Muhasebe Raporu Sayfası
	 * 
	 * Tüm sözleşme, satış, tahsilat ve aktivasyon bilgilerini detaylı olarak görüntüler.
	 * Model: Sozlesme_tahsilat_model
	 * 
	 * @author Batuhan KAHRAMAN
	 * @version 1.0.0
	 */
	public function sozlesme_tahsilat_raporu()
	{
		// Helper yükle
		$this->load->helper(['destek_helper', 'general_helper']);
		
		// Yetki kontrolü - Admin grubu (id=1) her zaman erişebilir
		$control = session("r", "login_info");
		$is_admin = $control && isset($control->grup_id) && $control->grup_id == 1;
		
		// Geçici olarak yetki kontrolünü atla (test için)
		// if (!$is_admin && !grup_modul_yetkisi_var(999)) {
		// 	$this->session->set_flashdata('hata', 'Bu sayfaya erişim yetkiniz yok!');
		// 	redirect('yetkisiz');
		// }
		
		// Model yükle
		$this->load->model('Sozlesme_tahsilat_model');
		
		// Dropdown verileri
		$data['ulkeler'] = $this->Sozlesme_tahsilat_model->get_ulke_listesi();
		$data['iller'] = $this->Sozlesme_tahsilat_model->get_il_listesi();
		$data['ilceler'] = $this->Sozlesme_tahsilat_model->get_ilce_listesi();
		$data['bolge_sahipleri'] = $this->Sozlesme_tahsilat_model->get_bolge_sahipleri();
		$data['sezonlar'] = $this->Sozlesme_tahsilat_model->get_sezon_listesi();
		$data['stoklar'] = $this->Sozlesme_tahsilat_model->get_stok_listesi();
		$data['personeller'] = $this->Sozlesme_tahsilat_model->get_personel_listesi();
		$data['aktivasyon_hizmetler'] = $this->Sozlesme_tahsilat_model->get_aktivasyon_hizmet_listesi();
		
		// Kullanıcı bilgileri
		$data['kullanici_grup_id'] = $control ? $control->grup_id : null;
		$data['is_admin'] = $is_admin;
		
		// Yetki kontrolleri
		$yetkiler = $this->session->userdata('yetkiler') ?? [];
		$data['export_yetkisi'] = $is_admin || (isset($yetkiler[999]) && in_array(5, (array)$yetkiler[999]));
		
		// View yükle
		$this->load->view('muhasebe/sozlesme-tahsilat-raporu', $data);
	}
	
	/**
	 * Detaylı Muhasebe Raporu - Ajax Endpoint
	 * 
	 * DataTable için server-side data sağlar
	 */
	public function sozlesme_tahsilat_raporu_ajax()
	{
		// Helper yükle
		$this->load->helper(['destek_helper', 'general_helper']);
		
		// Yetki kontrolü
		$control = session("r", "login_info");
		$is_admin = $control && $control->grup_id == 1;
		
		if (!$is_admin && !grup_modul_yetkisi_var(999)) {
			header('Content-Type: application/json');
			echo json_encode(['error' => 'Yetkisiz erişim']);
			return;
		}
		
		// Model yükle
		$this->load->model('Sozlesme_tahsilat_model');
		
		// Filtreleri al
		$filters = [
			'baslangic_tarih' => $this->input->post('baslangic_tarih'),
			'bitis_tarih' => $this->input->post('bitis_tarih'),
			'ulke_id' => $this->input->post('ulke_id'),
			'il_id' => $this->input->post('il_id'),
			'ilce_id' => $this->input->post('ilce_id'),
			'bolge_sahibi' => $this->input->post('bolge_sahibi'),
			'sezon_id' => $this->input->post('sezon_id'),
			'stok_id' => $this->input->post('stok_id'),
			'personel_id' => $this->input->post('personel_id'),
			'aktivasyon_hizmet' => $this->input->post('aktivasyon_hizmet'),
			'cari_ad' => $this->input->post('cari_ad'),
		];
		
		// Boş filtreleri temizle
		$filters = array_filter($filters, function($value) {
			return !empty($value);
		});
		
		// Sorgu çalıştır
		$result = $this->Sozlesme_tahsilat_model->get_detayli_rapor($filters);
		
		// JSON olarak döndür
		header('Content-Type: application/json');
		echo json_encode([
			'data' => $result->result()
		]);
	}
	
	/**
	 * Detaylı Muhasebe Raporu - Excel Export
	 * 
	 * Filtrelenmiş verileri Excel olarak indirir
	 */
	public function sozlesme_tahsilat_raporu_excel()
	{
		// Helper yükle
		$this->load->helper(['destek_helper', 'general_helper']);
		
		// Yetki kontrolü
		$control = session("r", "login_info");
		$is_admin = $control && $control->grup_id == 1;
		
		if (!$is_admin && !grup_modul_yetkisi_var(999)) {
			$this->session->set_flashdata('hata', 'Excel export yetkiniz yok!');
			redirect('muhasebe/sozlesme_tahsilat_raporu');
		}
		
		// Model yükle
		$this->load->model('Sozlesme_tahsilat_model');
		
		// Filtreleri al
		$filters = [
			'baslangic_tarih' => $this->input->get('baslangic_tarih'),
			'bitis_tarih' => $this->input->get('bitis_tarih'),
			'ulke_id' => $this->input->get('ulke_id'),
			'il_id' => $this->input->get('il_id'),
			'ilce_id' => $this->input->get('ilce_id'),
			'bolge_sahibi' => $this->input->get('bolge_sahibi'),
			'sezon_id' => $this->input->get('sezon_id'),
			'stok_id' => $this->input->get('stok_id'),
			'personel_id' => $this->input->get('personel_id'),
			'aktivasyon_hizmet' => $this->input->get('aktivasyon_hizmet'),
			'cari_ad' => $this->input->get('cari_ad'),
		];
		
		// Boş filtreleri temizle
		$filters = array_filter($filters, function($value) {
			return !empty($value);
		});
		
		// Sorgu çalıştır
		$result = $this->Sozlesme_tahsilat_model->get_detayli_rapor($filters);
		$data = $result->result();
		
		// Excel oluştur
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Detaylı Muhasebe Raporu');
		
		// Başlık satırı
		$headers = [
			'İşletme No', 'İşletme Adı', 'Ülke', 'Şehir', 'İlçe', 'Bölge Sahibi', 'Sezon',
			'Sözleşme Hizmeti', 'Sözleşme Tutarı', 'Sözleşme Tarihi',
			'Çek Tutarı', 'Çek Vade Tarihi', 'Senet Tutarı', 'Senet Vade Tarihi',
			'Tahsilat Nakit Tutar', 'Tahsilat Nakit Tarih', 'Tahsilat Banka Tutar', 'Tahsilat Banka Tarih',
			'Tahsilat Senet Tutar', 'Tahsilat Senet Tarih', 'Tahsilat Çek Tutar', 'Tahsilat Çek Tarih',
			'Personel', 'Aktivasyon Üye No', 'Aktivasyon Hizmet'
		];
		
		$col = 'A';
		foreach ($headers as $header) {
			$sheet->setCellValue($col . '1', $header);
			$sheet->getStyle($col . '1')->getFont()->setBold(true);
			$sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setRGB('4472C4');
			$sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
			$col++;
		}
		
		// Veri satırları
		$row = 2;
		foreach ($data as $item) {
			$sheet->setCellValue('A' . $row, $item->isletme_no);
			$sheet->setCellValue('B' . $row, $item->isletme_adi);
			$sheet->setCellValue('C' . $row, $item->ulke);
			$sheet->setCellValue('D' . $row, $item->sehir);
			$sheet->setCellValue('E' . $row, $item->ilce);
			$sheet->setCellValue('F' . $row, $item->bolge_sahibi);
			$sheet->setCellValue('G' . $row, $item->sezon);
			$sheet->setCellValue('H' . $row, $item->sozlesme_hizmeti);
			$sheet->setCellValue('I' . $row, $item->sozlesme_tutari);
			$sheet->setCellValue('J' . $row, $item->sozlesme_tarihi);
			$sheet->setCellValue('K' . $row, $item->cek_tutari);
			$sheet->setCellValue('L' . $row, $item->cek_vade_tarihi);
			$sheet->setCellValue('M' . $row, $item->senet_tutari);
			$sheet->setCellValue('N' . $row, $item->senet_vade_tarihi);
			$sheet->setCellValue('O' . $row, $item->tahsilat_nakit_tutar);
			$sheet->setCellValue('P' . $row, $item->tahsilat_nakit_tarih);
			$sheet->setCellValue('Q' . $row, $item->tahsilat_banka_tutar);
			$sheet->setCellValue('R' . $row, $item->tahsilat_banka_tarih);
			$sheet->setCellValue('S' . $row, $item->tahsilat_senet_tutar);
			$sheet->setCellValue('T' . $row, $item->tahsilat_senet_tarih);
			$sheet->setCellValue('U' . $row, $item->tahsilat_cek_tutar);
			$sheet->setCellValue('V' . $row, $item->tahsilat_cek_tarih);
			$sheet->setCellValue('W' . $row, $item->personel);
			$sheet->setCellValue('X' . $row, $item->aktivasyon_uye_no);
			$sheet->setCellValue('Y' . $row, $item->aktivasyon_hizmet);
			$row++;
		}
		
		// Sütun genişlikleri
		foreach (range('A', 'Y') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}
		
		// Dosya adı
		$filename = 'detayli_muhasebe_raporu_' . date('Y-m-d_His') . '.xlsx';
		
		// Header'ları ayarla
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');
		
		// Excel dosyasını çıktı ver
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}
	
	/**
	 * İl listesi - Ajax endpoint (Ülkeye göre filtreli)
	 */
	public function get_il_listesi_ajax()
	{
		$this->load->model('Sozlesme_tahsilat_model');
		$ulke_id = $this->input->post('ulke_id');
		$result = $this->Sozlesme_tahsilat_model->get_il_listesi($ulke_id);
		
		header('Content-Type: application/json');
		echo json_encode(['data' => $result]);
	}
	
	/**
	 * İlçe listesi - Ajax endpoint (İl'e göre filtreli)
	 */
	public function get_ilce_listesi_ajax()
	{
		$this->load->model('Sozlesme_tahsilat_model');
		$il_id = $this->input->post('il_id');
		$result = $this->Sozlesme_tahsilat_model->get_ilce_listesi($il_id);
		
		header('Content-Type: application/json');
		echo json_encode(['data' => $result]);
	}
	
	/**
	 * Tahsilat Listesi Excel Export
	 */
	public function tahsilatListesiExcel()
	{
		// Memory limit artır (performans için)
		ini_set('memory_limit', '256M');
		ini_set('max_execution_time', 300);
		
		$anaHesap = anaHesapBilgisi();
		
		// Giriş yapan kullanıcı bilgisi
		$control = session("r", "login_info");
		if (!$control || !isset($control->kullanici_id)) {
			redirect(base_url('check'));
			return;
		}
		
		// Filtre parametrelerini al
		$filtre_tahsilat_tipi = $this->input->get('tahsilat_tipi');
		$filtre_durum = $this->input->get('durum');
		$filtre_personel = $this->input->get('personel');
		$filtre_baslangic_tarih = $this->input->get('baslangic_tarih');
		$filtre_bitis_tarih = $this->input->get('bitis_tarih');
		
		// Tahsilat tipi parametresini array olarak işle
		if (is_array($filtre_tahsilat_tipi)) {
			$tahsilat_tipi_array = array_map('intval', $filtre_tahsilat_tipi);
		} elseif (!empty($filtre_tahsilat_tipi)) {
			$tahsilat_tipi_array = [intval($filtre_tahsilat_tipi)];
		} else {
			$tahsilat_tipi_array = null;
		}
		
		// Ana sorgu
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
		   COALESCE(bh.bh_giris, c.cek_tutar, kh.kh_giris, s.senet_tutar) as tutar,
		   c.cek_seriNo as cek_seri_no,
		   c.cek_vadeTarih as cek_vade_tarih,
		   s.senet_seriNo as senet_seri_no,
		   s.senet_vadeTarih as senet_vade_tarih,
		   bh.bh_belgeNumarasi as banka_belge_no,
		   bh.bh_tarih as banka_tarih,
		   b.banka_bankaAd as banka_adi,
		   kh.kh_belgeNumarasi as kasa_belge_no,
		   kh.kh_tarih as kasa_tarih,
		   k.kasa_adi as kasa_adi,
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
		LEFT JOIN kullanicilar onay_k ON mtd.islemi_yapan = onay_k.kullanici_id
		LEFT JOIN bankaHareketleri bh ON mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id
		LEFT JOIN kullanicilar bh_k ON bh.bh_olusturan = bh_k.kullanici_id
		LEFT JOIN cari bh_cari ON bh.bh_cariID = bh_cari.cari_id
		LEFT JOIN banka b ON bh.bh_bankaID = b.banka_id
		LEFT JOIN cek c ON mtd.tahsilat_tipi = 2 AND mtd.kayit_id = c.cek_id
		LEFT JOIN kullanicilar c_k ON c.cek_kullaniciID = c_k.kullanici_id
		LEFT JOIN cari c_cari ON c.cek_cariID = c_cari.cari_id
		LEFT JOIN kasaHareketleri kh ON mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id
		LEFT JOIN kullanicilar kh_k ON kh.kh_olusturan = kh_k.kullanici_id
		LEFT JOIN cari kh_cari ON kh.kh_cariID = kh_cari.cari_id
		LEFT JOIN kasa k ON kh.kh_kasaID = k.kasa_id
		LEFT JOIN senet s ON mtd.tahsilat_tipi = 4 AND mtd.kayit_id = s.senet_id
		LEFT JOIN kullanicilar s_k ON s.senet_kullaniciID = s_k.kullanici_id
		LEFT JOIN cari s_cari ON s.senet_cariID = s_cari.cari_id
		WHERE mtd.tahsilat_tipi IN (1, 2, 3, 4)
		ORDER BY mtd.olusturma_tarihi DESC";
		
		// Filtre koşullarını ekle
		$where_conditions = array();
		
		if (!empty($tahsilat_tipi_array)) {
			$tahsilat_tipi_values = implode(',', $tahsilat_tipi_array);
			$where_conditions[] = "mtd.tahsilat_tipi IN ($tahsilat_tipi_values)";
		}
		
		if (!empty($filtre_durum)) {
			$where_conditions[] = "mtd.durum = " . intval($filtre_durum);
		}
		
		if (!empty($filtre_personel)) {
			$where_conditions[] = "(
				(mtd.tahsilat_tipi = 1 AND bh.bh_olusturan = " . intval($filtre_personel) . ") OR
				(mtd.tahsilat_tipi = 2 AND c.cek_kullaniciID = " . intval($filtre_personel) . ") OR
				(mtd.tahsilat_tipi = 3 AND kh.kh_olusturan = " . intval($filtre_personel) . ") OR
				(mtd.tahsilat_tipi = 4 AND s.senet_kullaniciID = " . intval($filtre_personel) . ")
			)";
		}
		
		if (!empty($filtre_baslangic_tarih) || !empty($filtre_bitis_tarih)) {
			$tarih_conditions = array();
			
			if (!empty($filtre_baslangic_tarih)) {
				$baslangic = $this->db->escape($filtre_baslangic_tarih);
				$tarih_conditions[] = "(
					(mtd.tahsilat_tipi = 1 AND bh.bh_tarih >= $baslangic) OR
					(mtd.tahsilat_tipi = 2 AND c.cek_vadeTarih >= $baslangic) OR
					(mtd.tahsilat_tipi = 3 AND kh.kh_tarih >= $baslangic) OR
					(mtd.tahsilat_tipi = 4 AND s.senet_vadeTarih >= $baslangic)
				)";
			}
			
			if (!empty($filtre_bitis_tarih)) {
				$bitis = $this->db->escape($filtre_bitis_tarih);
				$tarih_conditions[] = "(
					(mtd.tahsilat_tipi = 1 AND bh.bh_tarih <= $bitis) OR
					(mtd.tahsilat_tipi = 2 AND c.cek_vadeTarih <= $bitis) OR
					(mtd.tahsilat_tipi = 3 AND kh.kh_tarih <= $bitis) OR
					(mtd.tahsilat_tipi = 4 AND s.senet_vadeTarih <= $bitis)
				)";
			}
			
			if (!empty($tarih_conditions)) {
				$where_conditions[] = "(" . implode(' AND ', $tarih_conditions) . ")";
			}
		}
		
		if (!empty($where_conditions)) {
			$tahsilatlarQ = str_replace(
				"WHERE mtd.tahsilat_tipi IN (1, 2, 3, 4)",
				"WHERE mtd.tahsilat_tipi IN (1, 2, 3, 4) AND (" . implode(' AND ', $where_conditions) . ")",
				$tahsilatlarQ
			);
		}
		
		$tahsilatlar = $this->db->query($tahsilatlarQ)->result();
		
		// Excel oluştur
		$reader = PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
		$reader->setReadDataOnly(TRUE);
		$spreadsheet = new Spreadsheet();
		
		$sheet = $spreadsheet->getActiveSheet();
		
		date_default_timezone_set('Europe/Istanbul');
		$tarih = (new DateTime('now'))->format('d.m.Y-His');
		
		// Başlıklar
		$sheet->setCellValue('A1', 'ID');
		$sheet->setCellValue('B1', 'TİP');
		$sheet->setCellValue('C1', 'MÜŞTERİ');
		$sheet->setCellValue('D1', 'TUTAR');
		$sheet->setCellValue('E1', 'DURUM');
		$sheet->setCellValue('F1', 'İŞLEMİ YAPAN');
		$sheet->setCellValue('G1', 'ONAYLAYAN');
		$sheet->setCellValue('H1', 'OLUŞTURMA TARİHİ');
		$sheet->setCellValue('I1', 'İŞLEM TARİHİ');
		$sheet->setCellValue('J1', 'DETAY');
		
		$rows = 2;
		foreach($tahsilatlar as $t){
			// Detay bilgisi
			$detay = '';
			if ($t->tahsilat_tipi == 1) { // Banka
				$detay = ($t->banka_adi ? $t->banka_adi : '') . 
				         ($t->banka_belge_no ? ' - Belge No: ' . $t->banka_belge_no : '') .
				         ($t->banka_tarih ? ' - Tarih: ' . date('d.m.Y', strtotime($t->banka_tarih)) : '');
			} elseif ($t->tahsilat_tipi == 2) { // Çek
				$detay = ($t->cek_seri_no ? 'Seri No: ' . $t->cek_seri_no : '') .
				         ($t->cek_vade_tarih ? ' - Vade: ' . date('d.m.Y', strtotime($t->cek_vade_tarih)) : '');
			} elseif ($t->tahsilat_tipi == 3) { // Kasa
				$detay = ($t->kasa_adi ? $t->kasa_adi : '') .
				         ($t->kasa_belge_no ? ' - Belge No: ' . $t->kasa_belge_no : '') .
				         ($t->kasa_tarih ? ' - Tarih: ' . date('d.m.Y', strtotime($t->kasa_tarih)) : '');
			} elseif ($t->tahsilat_tipi == 4) { // Senet
				$detay = ($t->senet_seri_no ? 'Seri No: ' . $t->senet_seri_no : '') .
				         ($t->senet_vade_tarih ? ' - Vade: ' . date('d.m.Y', strtotime($t->senet_vade_tarih)) : '');
			}
			
			$sheet->setCellValue('A'.$rows, $t->id);
			$sheet->setCellValue('B'.$rows, $t->tahsilat_tipi_adi);
			$sheet->setCellValue('C'.$rows, $t->musteri_adi);
			$sheet->setCellValue('D'.$rows, number_format($t->tutar, 2, ',', '.') . ' ₺');
			$sheet->setCellValue('E'.$rows, $t->durum_adi);
			$sheet->setCellValue('F'.$rows, $t->islemi_yapan_personel);
			$sheet->setCellValue('G'.$rows, $t->onay_yapan_personel);
			$sheet->setCellValue('H'.$rows, $t->olusturma_tarihi ? date('d.m.Y H:i', strtotime($t->olusturma_tarihi)) : '');
			$sheet->setCellValue('I'.$rows, $t->islem_tarihi ? date('d.m.Y H:i', strtotime($t->islem_tarihi)) : '');
			$sheet->setCellValue('J'.$rows, $detay);
			
			$rows++;
		}
		
		// Kolonları otomatik boyutlandır
		foreach(range('A','J') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}
		
		$writer = new Xlsx($spreadsheet);
		
		$filename = 'tahsilat-listesi-'.$tarih;
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$writer->save('php://output');
	}

	// SMS Yönetim Sayfası
	public function smsYonetimi() {
		// Yetki kontrolü - Modül ID: 540
		if (!grup_modul_yetkisi_var(540)) {
			$this->session->set_flashdata('error', 'Bu sayfaya erişim yetkiniz yok!');
			redirect('');
			return;
		}
		
		$data["baslik"] = "Muhasebe / SMS Yönetimi";
		
		// Filtreleme parametreleri
		$durum = $this->input->get('durum');
		$tip = $this->input->get('tip');
		$tarih_baslangic = $this->input->get('tarih_baslangic');
		$tarih_bitis = $this->input->get('tarih_bitis');
		
		// SMS loglarını çek
		$this->db->select('sms_log.*, cari.cari_ad, cari.cari_soyad');
		$this->db->from('sms_log');
		$this->db->join('cari', 'cari.cari_id = sms_log.cari_id', 'left');
		
		if ($durum) {
			$this->db->where('sms_log.durum', $durum);
		}
		if ($tip) {
			$this->db->where('sms_log.tip', $tip);
		}
		if ($tarih_baslangic) {
			$this->db->where('DATE(sms_log.gonderim_tarihi) >=', $tarih_baslangic);
		}
		if ($tarih_bitis) {
			$this->db->where('DATE(sms_log.gonderim_tarihi) <=', $tarih_bitis);
		}
		
		$this->db->order_by('sms_log.gonderim_tarihi', 'DESC');
		$this->db->limit(500); // Son 500 kayıt
		
		$data['sms_logs'] = $this->db->get()->result();
		
		// İstatistikler
		$data['stats'] = [
			'toplam' => $this->db->count_all('sms_log'),
			'basarili' => $this->db->where('durum', 'basarili')->count_all_results('sms_log'),
			'basarisiz' => $this->db->where('durum', 'basarisiz')->count_all_results('sms_log'),
			'bugun' => $this->db->where('DATE(gonderim_tarihi)', date('Y-m-d'))->count_all_results('sms_log')
		];
		
		// SMS şablonunu yükle
		$data['sms_sablonu'] = $this->getSmsTemplate();
		
		$this->load->view("muhasebe/sms-yonetimi", $data);
	}
	
	// SMS Şablonunu Getir
	private function getSmsTemplate() {
		// Şablon dosyası kontrolü
		$template_file = FCPATH . 'application/config/sms_template.php';
		
		if (file_exists($template_file)) {
			include($template_file);
			return isset($sms_template) ? $sms_template : $this->getDefaultTemplate();
		}
		
		return $this->getDefaultTemplate();
	}
	
	// Varsayılan SMS Şablonu
	private function getDefaultTemplate() {
		return "Sayin Musterimiz,\n\n[ODEME_TURU] odemenizin vade tarihi [VADE_TARIHI] gunudur.\n\nKonuyla ilgili detayli bilgi ve destek icin 0552 173 10 37 numarali telefondan Burcu Hanim ile iletisime gecebilirsiniz.\n\nBilgilerinize sunar, iyi gunler dileriz.";
	}
	
	// SMS Şablonunu Güncelle
	public function smsSablonuGuncelle() {
		// Yetki kontrolü - Modül ID: 540
		if (!grup_modul_yetkisi_var(540)) {
			$this->session->set_flashdata('error', 'Bu işlem için yetkiniz yok!');
			redirect('');
			return;
		}
		
		$yeni_sablon = $this->input->post('sms_sablonu');
		
		if (empty($yeni_sablon)) {
			$this->session->set_flashdata('error', 'SMS şablonu boş olamaz!');
			redirect('muhasebe/smsYonetimi');
			return;
		}
		
		// Şablon dosyasına kaydet
		$template_file = FCPATH . 'application/config/sms_template.php';
		$content = "<?php\ndefined('BASEPATH') OR exit('No direct script access allowed');\n\n";
		$content .= "// SMS Vade Hatırlatma Şablonu\n";
		$content .= "// Değişkenler: [ODEME_TURU], [VADE_TARIHI]\n\n";
		$content .= '$sms_template = ' . var_export($yeni_sablon, true) . ";\n";
		
		if (file_put_contents($template_file, $content)) {
			$this->session->set_flashdata('success', 'SMS şablonu başarıyla güncellendi!');
		} else {
			$this->session->set_flashdata('error', 'SMS şablonu güncellenirken hata oluştu!');
		}
		
		redirect('muhasebe/smsYonetimi');
	}
	
	// SMS Log Detayı (AJAX)
	public function smsLogDetay($id) {
		// Yetki kontrolü - Modül ID: 540
		if (!grup_modul_yetkisi_var(540)) {
			header('Content-Type: application/json');
			echo json_encode([
				'success' => false,
				'message' => 'Bu işlem için yetkiniz yok!'
			]);
			return;
		}
		
		$log = $this->db->where('id', $id)->get('sms_log')->row();
		
		if ($log) {
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'data' => $log
			]);
		} else {
			header('Content-Type: application/json');
			echo json_encode([
				'success' => false,
				'message' => 'Log bulunamadı'
			]);
		}
	}
	
	// Test müşterilerini getir (AJAX)
	public function getTestCustomers() {
		// Yetki kontrolü - Modül ID: 540
		if (!grup_modul_yetkisi_var(540)) {
			header('Content-Type: application/json');
			echo json_encode([
				'success' => false,
				'message' => 'Bu işlem için yetkiniz yok!'
			]);
			return;
		}
		
		// Vade yaklaşan müşterileri çek
		$query = "
			SELECT 
				c.cari_id,
				c.cari_ad AS isletme_adi,
				c.cari_firmaTelefon AS telefon,
				CASE 
					WHEN s.senet_id IS NOT NULL THEN 'Senet'
					WHEN ck.cek_id IS NOT NULL THEN 'Çek'
					ELSE NULL
				END AS odeme_turu,
				COALESCE(s.senet_vadeTarih, ck.cek_vadeTarih) AS vade_tarihi,
				COALESCE(
					DATEDIFF(s.senet_vadeTarih, CURDATE()),
					DATEDIFF(ck.cek_vadeTarih, CURDATE())
				) AS kalan_gun,
				COALESCE(s.senet_tutar, ck.cek_tutar) AS tutar,
				COALESCE(s.senet_id, ck.cek_id) AS kayit_id,
				CASE 
					WHEN s.senet_id IS NOT NULL THEN 4
					WHEN ck.cek_id IS NOT NULL THEN 2
					ELSE NULL
				END AS tahsilat_tipi
			FROM cari c
			LEFT JOIN senet s 
				ON s.senet_cariID = c.cari_id
			LEFT JOIN muhasebe_tahsilat_durum ms 
				ON ms.kayit_id = s.senet_id 
			   AND ms.tahsilat_tipi = 4
			   AND ms.durum <> 2
			   AND ms.onay_durumu <> 1
			LEFT JOIN cek ck 
				ON ck.cek_cariID = c.cari_id
			LEFT JOIN muhasebe_tahsilat_durum mc 
				ON mc.kayit_id = ck.cek_id 
			   AND mc.tahsilat_tipi = 2
			   AND mc.durum <> 2
			   AND mc.onay_durumu <> 1
			WHERE 
				c.cari_durum = 1
				AND c.cari_firmaTelefon IS NOT NULL
				AND c.cari_firmaTelefon != ''
				AND LENGTH(REGEXP_REPLACE(c.cari_firmaTelefon, '[^0-9]', '')) >= 10
				AND (
					DATEDIFF(s.senet_vadeTarih, CURDATE()) IN (10, 3)
					OR DATEDIFF(ck.cek_vadeTarih, CURDATE()) IN (10, 3)
				)
				AND (s.senet_id IS NOT NULL OR ck.cek_id IS NOT NULL)
			ORDER BY 
				c.cari_ad
		";
		
		$result = $this->db->query($query);
		$customers = $result->result_array();
		
		header('Content-Type: application/json');
		echo json_encode([
			'success' => true,
			'data' => $customers
		]);
	}
	
	// Test SMS gönder (AJAX)
	public function sendTestSms() {
		// Yetki kontrolü - Modül ID: 540
		if (!grup_modul_yetkisi_var(540)) {
			header('Content-Type: application/json');
			echo json_encode([
				'success' => false,
				'message' => 'Bu işlem için yetkiniz yok!'
			]);
			return;
		}
		
		$customer_index = $this->input->post('customer_index');
		
		// Müşteri verilerini tekrar çek
		$query = "
			SELECT 
				c.cari_id,
				c.cari_ad AS isletme_adi,
				c.cari_firmaTelefon AS telefon,
				CASE 
					WHEN s.senet_id IS NOT NULL THEN 'Senet'
					WHEN ck.cek_id IS NOT NULL THEN 'Çek'
					ELSE NULL
				END AS odeme_turu,
				COALESCE(s.senet_vadeTarih, ck.cek_vadeTarih) AS vade_tarihi,
				COALESCE(s.senet_id, ck.cek_id) AS kayit_id,
				CASE 
					WHEN s.senet_id IS NOT NULL THEN 4
					WHEN ck.cek_id IS NOT NULL THEN 2
					ELSE NULL
				END AS tahsilat_tipi
			FROM cari c
			LEFT JOIN senet s 
				ON s.senet_cariID = c.cari_id
			LEFT JOIN muhasebe_tahsilat_durum ms 
				ON ms.kayit_id = s.senet_id 
			   AND ms.tahsilat_tipi = 4
			   AND ms.durum <> 2
			   AND ms.onay_durumu <> 1
			LEFT JOIN cek ck 
				ON ck.cek_cariID = c.cari_id
			LEFT JOIN muhasebe_tahsilat_durum mc 
				ON mc.kayit_id = ck.cek_id 
			   AND mc.tahsilat_tipi = 2
			   AND mc.durum <> 2
			   AND mc.onay_durumu <> 1
			WHERE 
				c.cari_durum = 1
				AND (
					DATEDIFF(s.senet_vadeTarih, CURDATE()) IN (10, 3)
					OR DATEDIFF(ck.cek_vadeTarih, CURDATE()) IN (10, 3)
				)
				AND (s.senet_id IS NOT NULL OR ck.cek_id IS NOT NULL)
			ORDER BY c.cari_ad
			LIMIT $customer_index, 1
		";
		
		$result = $this->db->query($query);
		$customer = $result->row_array();
		
		if (!$customer) {
			header('Content-Type: application/json');
			echo json_encode([
				'success' => false,
				'message' => 'Müşteri bulunamadı'
			]);
			return;
		}
		
		// SMS config yükle
		require_once(FCPATH . 'application/config/sms.php');
		
		// Tarihi formatla
		$aylar = ['Ocak', 'Subat', 'Mart', 'Nisan', 'Mayis', 'Haziran', 
				  'Temmuz', 'Agustos', 'Eylul', 'Ekim', 'Kasim', 'Aralik'];
		$tarih = new DateTime($customer['vade_tarihi']);
		$vade_tarihi_fmt = $tarih->format('d') . ' ' . $aylar[(int)$tarih->format('m') - 1] . ' ' . $tarih->format('Y');
		
		// Ödeme türünü büyük harfe çevir
		$odeme_turu = mb_convert_case($customer['odeme_turu'], MB_CASE_UPPER, 'UTF-8');
		$odeme_turu = str_replace(['İ', 'Ş', 'Ğ', 'Ü', 'Ö', 'Ç'], ['I', 'S', 'G', 'U', 'O', 'C'], $odeme_turu);
		
		// SMS şablonunu al
		$sms_template = $this->getSmsTemplate();
		
		// Değişkenleri değiştir
		$mesaj = str_replace('[ODEME_TURU]', $odeme_turu, $sms_template);
		$mesaj = str_replace('[VADE_TARIHI]', $vade_tarihi_fmt, $mesaj);
		
		// Telefonu formatla
		$telefon = preg_replace('/[^0-9]/', '', $customer['telefon']);
		if (substr($telefon, 0, 1) === '0') {
			$telefon = '90' . substr($telefon, 1);
		} elseif (substr($telefon, 0, 2) !== '90') {
			$telefon = '90' . $telefon;
		}
		
		// SMS gönder - Mobilisim MainmsgBody formatı (cron ile aynı)
		$xml_body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml_body .= '<MainmsgBody>' . "\n";
		$xml_body .= '    <UserName>' . $config['sms_username'] . '</UserName>' . "\n";
		$xml_body .= '    <PassWord>' . $config['sms_password'] . '</PassWord>' . "\n";
		$xml_body .= '    <Action>' . $config['sms_action'] . '</Action>' . "\n";
		$xml_body .= '    <Mesgbody>' . htmlspecialchars($mesaj) . '</Mesgbody>' . "\n";
		$xml_body .= '    <Numbers>' . $telefon . '</Numbers>' . "\n";
		$xml_body .= '    <Originator>' . $config['sms_originator'] . '</Originator>' . "\n";
		$xml_body .= '    <SDate></SDate>' . "\n";
		$xml_body .= '</MainmsgBody>';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $config['sms_api_url']);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_body);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: text/xml',
			'Content-Length: ' . strlen($xml_body)
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $config['sms_timeout']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = curl_exec($ch);
		$curl_error = curl_error($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);
		
		// Debug bilgisi - Tam log için
		$debug_info = "=== SMS GÖNDERIM DETAYI ===\n";
		$debug_info .= "URL: {$effective_url}\n";
		$debug_info .= "HTTP Code: {$http_code}\n";
		$debug_info .= "CURL Error: " . ($curl_error ?: 'YOK') . "\n";
		$debug_info .= "Telefon: {$telefon}\n";
		$debug_info .= "Mesaj Uzunluğu: " . strlen($mesaj) . " karakter\n";
		$debug_info .= "---\nGönderilen XML:\n{$xml_body}\n---\nAPI Response:\n{$response}";
		
		// SMS logunu kaydet - HTTP 200 ve CURL hatası yoksa başarılı
		$durum = ($http_code == 200 && empty($curl_error)) ? 'basarili' : 'basarisiz';
		$hata_mesaji = ($durum === 'basarisiz') ? ($curl_error ?: $response) : null;
		
		$this->db->insert('sms_log', [
			'cari_id' => $customer['cari_id'],
			'telefon' => $telefon,
			'mesaj' => $mesaj,
			'tip' => 'vade_hatirlatma',
			'durum' => $durum,
			'hata_mesaji' => $hata_mesaji,
			'odeme_turu' => $customer['odeme_turu'],
			'kayit_id' => $customer['kayit_id'],
			'tahsilat_tipi' => $customer['tahsilat_tipi'],
			'api_response' => $debug_info
		]);
		
		header('Content-Type: application/json');
		echo json_encode([
			'success' => ($durum === 'basarili'),
			'message' => ($durum === 'basarili') ? 'SMS başarıyla gönderildi' : 'SMS gönderilemedi: ' . $response
		]);
	}
	
	// Vade Hatırlatma SMS Cron (Web endpoint - Manuel test için)
	public function vadeHatirlatamaSmsCron() {
		// Güvenlik token kontrolü
		$security_token = $this->input->get('token');
		$valid_token = 'ileka_sms_cron_2025'; // Bu değeri değiştirebilirsin
		
		if ($security_token !== $valid_token) {
			header('HTTP/1.1 403 Forbidden');
			echo "Yetkisiz erişim! Token geçersiz.";
			return;
		}
		
		// Kullanıcı oturumu kontrolü - Sadece giriş yapmış kullanıcılar
		if (!$this->session->userdata('kullanici_id')) {
			header('HTTP/1.1 401 Unauthorized');
			echo "Bu işlem için giriş yapmanız gerekiyor!";
			return;
		}
		
		// Yetki kontrolü - Modül ID: 540 (SMS Yönetimi)
		if (!grup_modul_yetkisi_var(540)) {
			header('HTTP/1.1 403 Forbidden');
			echo "Bu işlem için yetkiniz yok! SMS Yönetimi modülüne erişim yetkisi gerekli.";
			return;
		}
		
		// TEST MODU kontrolü - test=1 parametresi varsa SMS gönderilmez
		$test_mode = ($this->input->get('test') == '1');
		
		// Zaman aşımı sınırını kaldır
		set_time_limit(0);
		ini_set('max_execution_time', 0);
		
		// Text output için header
		header('Content-Type: text/plain; charset=utf-8');
		
		echo "=== VADE HATIRLATMA SMS CRON JOB ===\n";
		echo "Başlangıç Zamanı: " . date('Y-m-d H:i:s') . "\n";
		
		if ($test_mode) {
			echo "⚠️  TEST MODU AKTIF - SMS GÖNDERİLMEYECEK ⚠️\n";
			echo "Gerçek SMS göndermek için URL'den '&test=1' parametresini kaldırın.\n";
		}
		
		echo "\n";
		
		// SMS config yükle
		require_once(FCPATH . 'application/config/sms.php');
		
		// SMS şablonunu al
		$sms_template = $this->getSmsTemplate();
		
		// Vade yaklaşan müşterileri çek
		$query = "
			SELECT 
				c.cari_id,
				c.cari_ad AS isletme_adi,
				c.cari_firmaTelefon AS telefon,
				CASE 
					WHEN s.senet_id IS NOT NULL THEN 'Senet'
					WHEN ck.cek_id IS NOT NULL THEN 'Çek'
					ELSE NULL
				END AS odeme_turu,
				COALESCE(s.senet_vadeTarih, ck.cek_vadeTarih) AS vade_tarihi,
				COALESCE(
					DATEDIFF(s.senet_vadeTarih, CURDATE()),
					DATEDIFF(ck.cek_vadeTarih, CURDATE())
				) AS kalan_gun,
				COALESCE(s.senet_tutar, ck.cek_tutar) AS tutar,
				COALESCE(s.senet_id, ck.cek_id) AS kayit_id,
				CASE 
					WHEN s.senet_id IS NOT NULL THEN 4
					WHEN ck.cek_id IS NOT NULL THEN 2
					ELSE NULL
				END AS tahsilat_tipi
			FROM cari c
			LEFT JOIN senet s 
				ON s.senet_cariID = c.cari_id
			LEFT JOIN muhasebe_tahsilat_durum ms 
				ON ms.kayit_id = s.senet_id 
				   AND ms.tahsilat_tipi = 4
				   AND ms.durum <> 2
				   AND ms.onay_durumu <> 1
			LEFT JOIN cek ck 
				ON ck.cek_cariID = c.cari_id
			LEFT JOIN muhasebe_tahsilat_durum mc 
				ON mc.kayit_id = ck.cek_id 
				   AND mc.tahsilat_tipi = 2
				   AND mc.durum <> 2
				   AND mc.onay_durumu <> 1
			WHERE 
				c.cari_durum = 1
				AND c.cari_firmaTelefon IS NOT NULL
				AND c.cari_firmaTelefon != ''
				AND LENGTH(REGEXP_REPLACE(c.cari_firmaTelefon, '[^0-9]', '')) >= 10
				AND (
					DATEDIFF(s.senet_vadeTarih, CURDATE()) IN (10, 3)
					OR DATEDIFF(ck.cek_vadeTarih, CURDATE()) IN (10, 3)
				)
				AND (s.senet_id IS NOT NULL OR ck.cek_id IS NOT NULL)
			ORDER BY 
				c.cari_ad
		";
		
		$result = $this->db->query($query);
		$customers = $result->result_array();
		
		echo "Toplam Bulunan Müşteri: " . count($customers) . "\n";
		echo str_repeat('-', 80) . "\n\n";
		
		if (empty($customers)) {
			echo "✓ Vade tarihi yaklaşan (10 veya 3 gün kala) müşteri bulunamadı.\n";
			echo "Cron job başarıyla tamamlandı.\n";
			return;
		}
		
		$basarili = 0;
		$basarisiz = 0;
		$tekrar = 0;
		
		foreach ($customers as $index => $customer) {
			$sira = $index + 1;
			echo "[$sira/{" . count($customers) . "}] İşleniyor: {$customer['isletme_adi']}\n";
			
			// Telefon kontrolü
			if (empty($customer['telefon']) || strlen(preg_replace('/[^0-9]/', '', $customer['telefon'])) < 10) {
				echo "  → ATLANDI: Geçerli telefon numarası yok (" . ($customer['telefon'] ?: 'BOŞ') . ")\n";
				echo "  Ödeme: {$customer['odeme_turu']} - Vade: {$customer['vade_tarihi']} ({$customer['kalan_gun']} gün kala)\n\n";
				$basarisiz++;
				continue;
			}
			
			// Bu müşteriye bu kayıt için bugün SMS gönderilmiş mi kontrol et
			$check = $this->db
				->where('cari_id', $customer['cari_id'])
				->where('kayit_id', $customer['kayit_id'])
				->where('tahsilat_tipi', $customer['tahsilat_tipi'])
				->where('DATE(gonderim_tarihi)', date('Y-m-d'))
				->get('sms_log');
			
			if ($check->num_rows() > 0) {
				echo "  → ATLANDI: Bu müşteriye bugün zaten SMS gönderilmiş\n";
				echo "  Telefon: {$customer['telefon']}\n";
				echo "  Ödeme: {$customer['odeme_turu']} - Vade: {$customer['vade_tarihi']} ({$customer['kalan_gun']} gün kala)\n\n";
				$tekrar++;
				continue;
			}
			
			// Tarihi formatla
			$aylar = ['Ocak', 'Subat', 'Mart', 'Nisan', 'Mayis', 'Haziran', 
					  'Temmuz', 'Agustos', 'Eylul', 'Ekim', 'Kasim', 'Aralik'];
			$tarih = new DateTime($customer['vade_tarihi']);
			$vade_tarihi_fmt = $tarih->format('d') . ' ' . $aylar[(int)$tarih->format('m') - 1] . ' ' . $tarih->format('Y');
			
			// Ödeme türünü büyük harfe çevir
			$odeme_turu = mb_convert_case($customer['odeme_turu'], MB_CASE_UPPER, 'UTF-8');
			$odeme_turu = str_replace(['İ', 'Ş', 'Ğ', 'Ü', 'Ö', 'Ç'], ['I', 'S', 'G', 'U', 'O', 'C'], $odeme_turu);
			
			// Mesajı hazırla
			$mesaj = str_replace('[ODEME_TURU]', $odeme_turu, $sms_template);
			$mesaj = str_replace('[VADE_TARIHI]', $vade_tarihi_fmt, $mesaj);
			
			// Telefonu formatla
			$telefon = preg_replace('/[^0-9]/', '', $customer['telefon']);
			if (substr($telefon, 0, 1) === '0') {
				$telefon = '90' . substr($telefon, 1);
			} elseif (substr($telefon, 0, 2) !== '90') {
				$telefon = '90' . $telefon;
			}
			
			// TEST MODU: SMS gönderme
			if ($test_mode) {
				// Test modunda SMS gönderilmez, sadece simülasyon
				echo "  🧪 TEST: SMS GÖNDERİLMEDİ (Simülasyon)\n";
				echo "  Telefon: {$telefon}\n";
				echo "  Ödeme: {$customer['odeme_turu']} - Vade: {$customer['vade_tarihi']} ({$customer['kalan_gun']} gün kala)\n";
				echo "  Mesaj Uzunluğu: " . strlen($mesaj) . " karakter\n";
				echo "  Mesaj Önizleme:\n";
				echo "  " . str_repeat('-', 60) . "\n";
				foreach (explode("\n", $mesaj) as $line) {
					echo "  " . $line . "\n";
				}
				echo "  " . str_repeat('-', 60) . "\n\n";
				$basarili++;
				continue;
			}
			
			// GERÇEK MOD: SMS gönder
			$xml_body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$xml_body .= '<MainmsgBody>' . "\n";
			$xml_body .= '    <UserName>' . $config['sms_username'] . '</UserName>' . "\n";
			$xml_body .= '    <PassWord>' . $config['sms_password'] . '</PassWord>' . "\n";
			$xml_body .= '    <Action>' . $config['sms_action'] . '</Action>' . "\n";
			$xml_body .= '    <Mesgbody>' . htmlspecialchars($mesaj) . '</Mesgbody>' . "\n";
			$xml_body .= '    <Numbers>' . $telefon . '</Numbers>' . "\n";
			$xml_body .= '    <Originator>' . $config['sms_originator'] . '</Originator>' . "\n";
			$xml_body .= '    <SDate></SDate>' . "\n";
			$xml_body .= '</MainmsgBody>';
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $config['sms_api_url']);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_body);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: text/xml',
				'Content-Length: ' . strlen($xml_body)
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, $config['sms_timeout']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$response = curl_exec($ch);
			$curl_error = curl_error($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			// Durum
			$durum = ($http_code == 200 && empty($curl_error)) ? 'basarili' : 'basarisiz';
			$hata_mesaji = ($durum === 'basarisiz') ? ($curl_error ?: $response) : null;
			
			// Logla
			$this->db->insert('sms_log', [
				'cari_id' => $customer['cari_id'],
				'telefon' => $telefon,
				'mesaj' => $mesaj,
				'tip' => 'vade_hatirlatma',
				'durum' => $durum,
				'hata_mesaji' => $hata_mesaji,
				'odeme_turu' => $customer['odeme_turu'],
				'kayit_id' => $customer['kayit_id'],
				'tahsilat_tipi' => $customer['tahsilat_tipi'],
				'api_response' => $response
			]);
			
			if ($durum === 'basarili') {
				echo "  ✓ BAŞARILI: SMS gönderildi\n";
				$basarili++;
			} else {
				echo "  ✗ BAŞARISIZ: " . ($curl_error ?: $response) . "\n";
				$basarisiz++;
			}
			
			echo "  Telefon: {$telefon}\n";
			echo "  Ödeme: {$customer['odeme_turu']} - Vade: {$customer['vade_tarihi']} ({$customer['kalan_gun']} gün kala)\n";
			echo "  HTTP Code: {$http_code}\n\n";
			
			// API'yi yormamak için bekleme (500ms)
			usleep(500000);
		}
		
		echo str_repeat('=', 80) . "\n";
		echo "ÖZET RAPOR:\n";
		echo "- Mod: " . ($test_mode ? 'TEST (SMS gönderilmedi)' : 'GERÇEK (SMS gönderildi)') . "\n";
		echo "- Toplam Bulunan: " . count($customers) . "\n";
		echo "- Başarılı: {$basarili}\n";
		echo "- Başarısız: {$basarisiz}\n";
		echo "- Tekrar (Bugün Gönderilmiş): {$tekrar}\n";
		echo "\nBitiş Zamanı: " . date('Y-m-d H:i:s') . "\n";
		
		if ($test_mode) {
			echo "\n⚠️  TEST MODU - GERÇEK SMS GÖNDERİLMEDİ\n";
			echo "Gerçek SMS göndermek için URL'den '&test=1' parametresini kaldırın.\n";
		}
		
		echo "=== CRON JOB TAMAMLANDI ===\n";
	}
	
	// Vade Hatırlatma SMS Cron (Plesk için - Kullanılmıyor, cron_vade_sms.php kullanılıyor)
	// Bu fonksiyon geriye dönük uyumluluk için korunuyor
	public function vadeHatirlatamaSmsCronAuto() {
		header('Content-Type: text/plain; charset=utf-8');
		echo "Bu endpoint artık kullanılmıyor.\n";
		echo "Lütfen Plesk cron ayarını şu dosyaya güncelleyin:\n";
		echo "d:\\inetpub\\ilekasoft.com\\crm.ilekasoft.com\\cron_vade_sms.php\n";
		return;
		
		// SMS config yükle
		require_once(FCPATH . 'application/config/sms.php');
		
		// SMS şablonunu al
		$this->load->config('sms_template', TRUE);
		$sms_template = $this->config->item('sms_sablonu', 'sms_template');
		
		if (!$sms_template) {
			$sms_template = $this->getDefaultTemplate();
		}
		
		// Vade yaklaşan müşterileri çek
		$query = "
			SELECT 
				c.cari_id,
				c.cari_ad AS isletme_adi,
				c.cari_firmaTelefon AS telefon,
				CASE 
					WHEN s.senet_id IS NOT NULL THEN 'Senet'
					WHEN ck.cek_id IS NOT NULL THEN 'Çek'
					ELSE NULL
				END AS odeme_turu,
				COALESCE(s.senet_vadeTarih, ck.cek_vadeTarih) AS vade_tarihi,
				COALESCE(
					DATEDIFF(s.senet_vadeTarih, CURDATE()),
					DATEDIFF(ck.cek_vadeTarih, CURDATE())
				) AS kalan_gun,
				COALESCE(s.senet_tutar, ck.cek_tutar) AS tutar,
				COALESCE(s.senet_id, ck.cek_id) AS kayit_id,
				CASE 
					WHEN s.senet_id IS NOT NULL THEN 4
					WHEN ck.cek_id IS NOT NULL THEN 2
					ELSE NULL
				END AS tahsilat_tipi
			FROM cari c
			LEFT JOIN senet s 
				ON s.senet_cariID = c.cari_id
			LEFT JOIN muhasebe_tahsilat_durum ms 
				ON ms.kayit_id = s.senet_id 
				   AND ms.tahsilat_tipi = 4
				   AND ms.durum <> 2
				   AND ms.onay_durumu <> 1
			LEFT JOIN cek ck 
				ON ck.cek_cariID = c.cari_id
			LEFT JOIN muhasebe_tahsilat_durum mc 
				ON mc.kayit_id = ck.cek_id 
				   AND mc.tahsilat_tipi = 2
				   AND mc.durum <> 2
				   AND mc.onay_durumu <> 1
			WHERE 
				c.cari_durum = 1
				AND (
					DATEDIFF(s.senet_vadeTarih, CURDATE()) IN (10, 3)
					OR DATEDIFF(ck.cek_vadeTarih, CURDATE()) IN (10, 3)
				)
				AND (s.senet_id IS NOT NULL OR ck.cek_id IS NOT NULL)
			ORDER BY 
				c.cari_ad
		";
		
		$result = $this->db->query($query);
		$customers = $result->result_array();
		
		echo "Toplam Bulunan Müşteri: " . count($customers) . "\n";
		echo str_repeat('-', 80) . "\n\n";
		
		if (empty($customers)) {
			echo "✓ Vade tarihi yaklaşan (10 veya 3 gün kala) müşteri bulunamadı.\n";
			echo "Cron job başarıyla tamamlandı.\n";
			return;
		}
		
		$basarili = 0;
		$basarisiz = 0;
		$tekrar = 0;
		
		foreach ($customers as $index => $customer) {
			$sira = $index + 1;
			echo "[$sira/{" . count($customers) . "}] İşleniyor: {$customer['isletme_adi']}\n";
			
			// Bu müşteriye bu kayıt için bugün SMS gönderilmiş mi kontrol et
			$check = $this->db
				->where('cari_id', $customer['cari_id'])
				->where('kayit_id', $customer['kayit_id'])
				->where('tahsilat_tipi', $customer['tahsilat_tipi'])
				->where('DATE(gonderim_tarihi)', date('Y-m-d'))
				->get('sms_log');
			
			if ($check->num_rows() > 0) {
				echo "  → ATLANDI: Bu müşteriye bugün zaten SMS gönderilmiş\n";
				echo "  Telefon: {$customer['telefon']}\n";
				echo "  Ödeme: {$customer['odeme_turu']} - Vade: {$customer['vade_tarihi']} ({$customer['kalan_gun']} gün kala)\n\n";
				$tekrar++;
				continue;
			}
			
			// Tarihi formatla
			$aylar = ['Ocak', 'Subat', 'Mart', 'Nisan', 'Mayis', 'Haziran', 
					  'Temmuz', 'Agustos', 'Eylul', 'Ekim', 'Kasim', 'Aralik'];
			$tarih = new DateTime($customer['vade_tarihi']);
			$vade_tarihi_fmt = $tarih->format('d') . ' ' . $aylar[(int)$tarih->format('m') - 1] . ' ' . $tarih->format('Y');
			
			// Ödeme türünü büyük harfe çevir
			$odeme_turu = mb_convert_case($customer['odeme_turu'], MB_CASE_UPPER, 'UTF-8');
			$odeme_turu = str_replace(['İ', 'Ş', 'Ğ', 'Ü', 'Ö', 'Ç'], ['I', 'S', 'G', 'U', 'O', 'C'], $odeme_turu);
			
			// Mesajı hazırla
			$mesaj = str_replace('[ODEME_TURU]', $odeme_turu, $sms_template);
			$mesaj = str_replace('[VADE_TARIHI]', $vade_tarihi_fmt, $mesaj);
			
			// Telefonu formatla
			$telefon = preg_replace('/[^0-9]/', '', $customer['telefon']);
			if (substr($telefon, 0, 1) === '0') {
				$telefon = '90' . substr($telefon, 1);
			} elseif (substr($telefon, 0, 2) !== '90') {
				$telefon = '90' . $telefon;
			}
			
			// SMS gönder
			$xml_body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$xml_body .= '<MainmsgBody>' . "\n";
			$xml_body .= '    <UserName>' . $config['sms_username'] . '</UserName>' . "\n";
			$xml_body .= '    <PassWord>' . $config['sms_password'] . '</PassWord>' . "\n";
			$xml_body .= '    <Action>' . $config['sms_action'] . '</Action>' . "\n";
			$xml_body .= '    <Mesgbody>' . htmlspecialchars($mesaj) . '</Mesgbody>' . "\n";
			$xml_body .= '    <Numbers>' . $telefon . '</Numbers>' . "\n";
			$xml_body .= '    <Originator>' . $config['sms_originator'] . '</Originator>' . "\n";
			$xml_body .= '    <SDate></SDate>' . "\n";
			$xml_body .= '</MainmsgBody>';
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $config['sms_api_url']);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_body);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: text/xml',
				'Content-Length: ' . strlen($xml_body)
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, $config['sms_timeout']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$response = curl_exec($ch);
			$curl_error = curl_error($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			// Durum
			$durum = ($http_code == 200 && empty($curl_error)) ? 'basarili' : 'basarisiz';
			$hata_mesaji = ($durum === 'basarisiz') ? ($curl_error ?: $response) : null;
			
			// Logla
			$this->db->insert('sms_log', [
				'cari_id' => $customer['cari_id'],
				'telefon' => $telefon,
				'mesaj' => $mesaj,
				'tip' => 'vade_hatirlatma',
				'durum' => $durum,
				'hata_mesaji' => $hata_mesaji,
				'odeme_turu' => $customer['odeme_turu'],
				'kayit_id' => $customer['kayit_id'],
				'tahsilat_tipi' => $customer['tahsilat_tipi'],
				'api_response' => $response
			]);
			
			if ($durum === 'basarili') {
				echo "  ✓ BAŞARILI: SMS gönderildi\n";
				$basarili++;
			} else {
				echo "  ✗ BAŞARISIZ: " . ($curl_error ?: $response) . "\n";
				$basarisiz++;
			}
			
			echo "  Telefon: {$telefon}\n";
			echo "  Ödeme: {$customer['odeme_turu']} - Vade: {$customer['vade_tarihi']} ({$customer['kalan_gun']} gün kala)\n";
			echo "  HTTP Code: {$http_code}\n\n";
			
			// API'yi yormamak için bekleme (500ms)
			usleep(500000);
		}
		
		echo str_repeat('=', 80) . "\n";
		echo "ÖZET RAPOR:\n";
		echo "- Toplam Bulunan: " . count($customers) . "\n";
		echo "- Başarılı: {$basarili}\n";
		echo "- Başarısız: {$basarisiz}\n";
		echo "- Tekrar (Bugün Gönderilmiş): {$tekrar}\n";
		echo "\nBitiş Zamanı: " . date('Y-m-d H:i:s') . "\n";
		echo "=== CRON JOB TAMAMLANDI ===\n";
	}
}