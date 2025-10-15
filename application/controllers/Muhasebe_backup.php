<?php

defined('BASEPATH') OR exit('No direct script access allowed');



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

				   COALESCE(CONCAT(islem_personel.kullanici_ad, ' ', islem_personel.kullanici_soyad), islem_personel.kullanici_ad, 'Bilinmiyor') as islemi_yapan_personel

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
			// Geri alma işlemi - durumu onay bekliyor'a çevir
			$data = array(
				'durum' => 1, // 1 = Onay bekliyor
				'islem_tarihi' => null, // İşlem tarihini sıfırla
				'islemi_yapan' => null, // Onay yapan kişiyi sıfırla
				'gorsel' => null, // Onay görselini temizle
			);
			
			// Mevcut açıklama varsa geri alma notunu ekle
			$mevcut_aciklama = $tahsilat->aciklama ? $tahsilat->aciklama . "\n\n" : "";
			$data['aciklama'] = $mevcut_aciklama . "Geri Alma İşlemi: " . date('d.m.Y H:i:s') . " tarihinde " . $control->kullanici_ad . " " . $control->kullanici_soyad . " tarafından geri alındı.";
			
			$this->db->where('id', $tahsilat_id);
			$this->db->update('muhasebe_tahsilat_durum', $data);
			
			if ($this->db->affected_rows() > 0) {
				$this->session->set_flashdata('tahsilat_geri_al_ok', 'Tahsilat başarıyla geri alındı ve onay bekleyen duruma getirildi.');
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

				WHERE s.senet_vadeTarih < CURDATE()

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

				WHERE s.senet_vadeTarih < CURDATE()

			";

		} else {

			$countQ = "

				SELECT COUNT(*) as total

				FROM senet s

				LEFT JOIN (

					SELECT sh1.sh_senet_id, MAX(sh1.sh_id) as max_id

					FROM senet_hareketleri sh1

					GROUP BY sh1.sh_senet_id

				) son_hareket ON s.senet_id = son_hareket.sh_senet_id

				LEFT JOIN senet_hareketleri sh_son ON son_hareket.max_id = sh_son.sh_id

				WHERE (sh_son.sh_konum_tip_id = '$durum_id' OR (sh_son.sh_konum_tip_id IS NULL AND '$durum_id' = '1'))

			";

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

		$config['base_url'] = base_url("muhasebe/senet-yonetim?durum=$durum_filter");

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
		
		// Yetki kontrolü
		$control2 = session("r", "login");
		if (!grup_modul_yetkisi_var(531)) {
			redirect(base_url("illegal"));
		}
		
		$data = [];
		
		// Arama parametrelerini al
		$cari_search = $this->input->get('cari_search');
		$aktivasyon_durum = $this->input->get('aktivasyon_durum');
		
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
				c.cari_firmaTelefon LIKE '%{$search_term}%' OR 
				c.cari_tckn LIKE '%{$search_term}%'
			)";
		}
		
		// WHERE koşullarını birleştir
		$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
		
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
			
		} catch (Exception $e) {
			$data["error_message"] = "Veri yuklenirken bir hata olustu: " . $e->getMessage();
			$data["rapor_verileri"] = [];
		}
		
		// View yukle
		$this->load->view("muhasebe/tam-ana-rapor", $data);
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
			// Tahsilat detaylarını farklı tablolardan getir - cari hareket ID'si ve görsel alanları ile birlikte
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
					NULL as vade_tarih
				FROM bankahareketleri bh
				LEFT JOIN banka b ON bh.bh_bankaID = b.banka_id
				LEFT JOIN carihareketleri ch ON ch.ch_bankaID = bh.bh_id AND ch.ch_cariID = bh.bh_cariID
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
					c.cek_vadeTarih as vade_tarih
				FROM cek c
				LEFT JOIN carihareketleri ch ON ch.ch_cekID = c.cek_id AND ch.ch_cariID = c.cek_cariID
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
					NULL as vade_tarih
				FROM kasahareketleri kh
				LEFT JOIN kasa k ON kh.kh_kasaID = k.kasa_id
				LEFT JOIN carihareketleri ch ON ch.ch_kasaID = kh.kh_id AND ch.ch_cariID = kh.kh_cariID
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
					s.senet_vadeTarih as vade_tarih
				FROM senet s
				LEFT JOIN carihareketleri ch ON ch.ch_senetID = s.senet_id AND ch.ch_cariID = s.senet_cariID
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
}