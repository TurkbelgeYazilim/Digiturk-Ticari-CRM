<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use furkankadioglu\eFatura\InvoiceManager;

class Cari extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->model('vt');

		// AJAX endpoint'ler için session kontrolünü bypass et
		$current_url = $_SERVER['REQUEST_URI'] ?? '';
		$ajax_endpoints = [
			'/cari/getPersonelList',
			'/cari/updateCariOlusturan'
		];
		
		$is_ajax_endpoint = false;
		foreach ($ajax_endpoints as $endpoint) {
			if (strpos($current_url, $endpoint) !== false) {
				$is_ajax_endpoint = true;
				break;
			}
		}

		if (!$is_ajax_endpoint) {
			$control = session("r", "login");

			if (gibYetki() == 1)
				redirect("home/hata");

			if (!$control) {
				redirect("check");
			}
		}

		//sessionKontrolHelper();
	}

	public function cariGruplari()
	{
		$data["baslik"] = "Cari / Cari Grupları";
		$anaHesap = anaHesapBilgisi();

		$cariGruplariQ = "SELECT * FROM cariGruplari WHERE cariGrup_olusturanAnaHesap = '$anaHesap'";
		$data["cariGruplari"] = $this->db->query($cariGruplariQ)->result();

		$countq = "SELECT COUNT(*) as total FROM cariGruplari WHERE cariGrup_olusturanAnaHesap = '$anaHesap'";
		$countexe = $this->db->query($countq)->row();
		$count = $countexe->total;
		$data["count_of_list"] = $count;

		//logekle(4,1);
		$this->load->view('cari/cari-gruplari', $data);
	}

	public function yeniCariGrubuEkle()
	{
		$control2 = session("r", "login_info");
		$u_id = $control2->kullanici_id;

		date_default_timezone_set('Europe/Istanbul');
		$tarihi = (new DateTime('now'))->format('Y.m.d H:i:s');

		$anaHesap = anaHesapBilgisi();

		$cariGrup_kodu = postval("cariGrup_kodu");

		$data["cariGrup_kodu"] = $cariGrup_kodu;
		$data["cariGrup_ad"] = postval("cariGrup_ad");
		$data["cariGrup_olusturan"] = $u_id;
		$data["cariGrup_olusturanAnaHesap"] = $anaHesap;
		$data["cariGrup_olusturmaTarihi"] = $tarihi;

		$cariGrupKoduVarmiQ = "SELECT * FROM cariGruplari WHERE cariGrup_kodu = '$cariGrup_kodu' AND cariGrup_olusturanAnaHesap = '$anaHesap'";
		$cariGrupKoduVarmi = $this->db->query($cariGrupKoduVarmiQ)->row();

		if ($cariGrupKoduVarmi) {
			$this->session->set_flashdata('cari_grup_kodvar', 'OK');
			redirect($_SERVER['HTTP_REFERER']);
		} else {
			$this->vt->insert("cariGruplari", $data);
			$this->session->set_flashdata('cari_grup_ok', 'OK');
			logekle(4, 2);
			redirect($_SERVER['HTTP_REFERER']);
		}
	}

	public function mevcutCariGrubuDuzenle()
	{
		$id = postval("cariGrup_id");
		$cariGrup_kodu = postval("cariGrup_kodu");

		$anaHesap = anaHesapBilgisi();

		$data["cariGrup_kodu"] = $cariGrup_kodu;
		$data["cariGrup_ad"] = postval("cariGrup_ad");

		$cariGrupKoduVarmiQ = "SELECT * FROM cariGruplari WHERE cariGrup_kodu = '$cariGrup_kodu' AND cariGrup_olusturanAnaHesap = '$anaHesap' AND cariGrup_id != $id";
		$cariGrupKoduVarmi = $this->db->query($cariGrupKoduVarmiQ)->row();

		if ($cariGrupKoduVarmi) {
			$this->session->set_flashdata('cari_grup_kodvar', 'OK');
			redirect($_SERVER['HTTP_REFERER']);
		} else {
			$this->vt->update('cariGruplari', array('cariGrup_id' => $id), $data);
			$this->session->set_flashdata('cari_grup_updt_ok', 'OK');
			logekle(4, 3);
			redirect($_SERVER['HTTP_REFERER']);
		}
	}

	public function cariKartiOlustur()
	{
		$data["baslik"] = "Cari / Cari Kartı Oluştur";
		$iller = $this->db->get('iller');

		if ($iller->num_rows() > 0) {
			$data['_iller'] = $iller->result();
		} else {
			$data['_iller'] = false;
		}

		// Ülkeleri yükle
		$ulkeler = $this->db->order_by('ulke_adi', 'ASC')->get('ulkeler');
		if ($ulkeler->num_rows() > 0) {
			$data['_ulkeler'] = $ulkeler->result();
		} else {
			$data['_ulkeler'] = false;
		}

		//logekle(1,1);
		$this->load->view("cari/cari-karti-olustur", $data);
	}	public function yeniCariKartiOlustur()
	{
		// DEBUGGING: Log all POST data to understand mobile submission issues
		$debug_log_file = FCPATH . 'debug_cari_listesi.log';
		$timestamp = date('Y-m-d H:i:s');
		
		// Get user agent to identify mobile devices
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
		$is_mobile = preg_match('/(android|iphone|ipad|mobile)/i', $user_agent);
		
		// Detailed debug information
		$debug_info = array(
			'timestamp' => $timestamp,
			'user_agent' => $user_agent,
			'is_mobile' => $is_mobile,
			'post_data_count' => count($_POST),
			'post_data' => $_POST,
			'files_data' => $_FILES,
			'request_method' => $_SERVER['REQUEST_METHOD'],
			'content_type' => isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'Not set',
			'content_length' => isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 'Not set'
		);
		
		// Log to file
		$log_entry = $timestamp . " [CARI_CREATION_DEBUG] " . json_encode($debug_info, JSON_PRETTY_PRINT) . "\n\n";
		file_put_contents($debug_log_file, $log_entry, FILE_APPEND | LOCK_EX);
		
		$control2 = session("r", "login_info");
		$u_id = $control2->kullanici_id;

		date_default_timezone_set('Europe/Istanbul');
		$tarihi = (new DateTime('now'))->format('Y.m.d');
		$saati = (new DateTime('now'))->format('H:i:s');

		$anaHesap = anaHesapBilgisi();

		$cariTipi = postval("cari_bireysel");
		$cariKodu = postval("cari_kodu");
		$cariVergiNo = postval("cari_vergiNumarasi");
		//	$tckn = postval("cari_tckn");
		$alias_pk = postval("cari_alias_pk");
		
		// ENHANCED DEBUG: Log specific form values for mobile debugging
		$debug_values = array(
			'cari_bireysel' => $cariTipi,
			'cari_kodu' => $cariKodu,
			'cari_vergiNumarasi' => $cariVergiNo,
			'cari_ad' => postval("cari_ad"),
			'cari_soyad' => postval("cari_soyad"),
			'cari_il' => postval("cari_il"),
			'cari_ilce' => postval("cari_ilce"),
			'cari_firmaTelefon' => postval("cari_firmaTelefon"),
			'cari_firmaEPosta' => postval("cari_firmaEPosta"),
			'files_count' => count($_FILES),
			'all_post_keys' => array_keys($_POST)
		);
		
		$log_entry2 = $timestamp . " [CARI_VALUES_DEBUG] " . json_encode($debug_values, JSON_PRETTY_PRINT) . "\n\n";
		file_put_contents($debug_log_file, $log_entry2, FILE_APPEND | LOCK_EX);
		
		// Check if we have critical empty values that shouldn't be empty (mobile issue detection)
		$empty_critical_fields = array();
		if (empty($cariTipi)) $empty_critical_fields[] = 'cari_bireysel';
		if (empty(postval("cari_ad"))) $empty_critical_fields[] = 'cari_ad';
		if (empty(postval("cari_il"))) $empty_critical_fields[] = 'cari_il';
		
		if (!empty($empty_critical_fields)) {
			$log_entry3 = $timestamp . " [MOBILE_ISSUE_DETECTED] Empty critical fields: " . implode(', ', $empty_critical_fields) . "\n";
			file_put_contents($debug_log_file, $log_entry3, FILE_APPEND | LOCK_EX);
			
			// For mobile devices with empty data, redirect back with error
			if ($is_mobile && count($empty_critical_fields) >= 2) {
				$this->session->set_flashdata('mobile_form_error', 'OK');
				redirect("cari/cari-karti-olustur");
				return;
			}
		}

		if ($alias_pk === "") {//earşiv
			$cariEInvoiceType = 2;
		} else {
			$cariEInvoiceType = 1;
		}
		$cariKoduLen = strlen($cariKodu);
		$cariVergiNoLen = strlen($cariVergiNo);
		
		// Cari Kodu boşsa otomatik oluştur
		if (empty($cariKodu) || $cariKoduLen < 3) {
			// Son cari kodunu bul ve +1 artırarak yeni kod oluştur
			$sonCariKoduQ = "SELECT cari_kodu FROM cari WHERE cari_olusturanAnaHesap = '$anaHesap' AND cari_kodu REGEXP '^[0-9]+$' ORDER BY CAST(cari_kodu AS UNSIGNED) DESC LIMIT 1";
			$sonCariKodu = $this->db->query($sonCariKoduQ)->row();
			
			if ($sonCariKodu) {
				$yeniKodNumara = intval($sonCariKodu->cari_kodu) + 1;
			} else {
				$yeniKodNumara = 1;
			}
			
			$cariKodu = str_pad($yeniKodNumara, 3, '0', STR_PAD_LEFT);
		}
		/*
				if($cariTipi == 0) {
					if ($cariVergiNoLen < 10) {
						$this->session->set_flashdata('cari_kodu_vkn_karakter', 'OK');
						redirect("cari/cari-karti-olustur");
						die;
					}
				}

				if($cariTipi == 1) {
					if ($cariVergiNoLen < 11) {
						$this->session->set_flashdata('cari_kodu_tckn_karakter', 'OK');
						redirect("cari/cari-karti-olustur");
						die;
					}
				}*/

		// TC Kimlik/Vergi numarası kontrolü kaldırıldı - aynı numara ile kayıt olmasına izin verildi
		// Önceki kontroller: cari_tckn ve cari_vergiNumarasi tekrar kontrolleri devre dışı bırakıldı

		if ($cariVergiNoLen == 10)
			$data["cari_vergiNumarasi"] = $cariVergiNo;
		else if ($cariVergiNoLen == 11)
			$data["cari_tckn"] = $cariVergiNo;


		$data["cari_kodu"] = mb_strtoupper($cariKodu, "UTF-8");;
		$data["cari_ad"] = mb_strtoupper(postval("cari_ad"), "UTF-8");
		$data["cari_soyad"] = mb_strtoupper(postval("cari_soyad"), "UTF-8");
		$data["cari_vergiDairesi"] = mb_strtoupper(postval("cari_vergiDairesi"), "UTF-8");		/*		$data["cari_vergiNumarasi"] = $cariVergiNo;
				$data["cari_tckn"] = $tckn;*/
		$data["cari_ulke"] = postval("cari_ulke");
		$data["cari_il"] = postval("cari_il");
		$data["cari_ilce"] = postval("cari_ilce");
		$data["cari_websitesi"] = postval("cari_websitesi");
		$data["cari_firmaEPosta"] = postval("cari_firmaEPosta");
		$data["cari_firmaTelefon"] = postval("cari_firmaTelefon");
		$data["cari_adres"] = mb_strtoupper(postval("cari_adres"), "UTF-8");
		$data["cari_postaKodu"] = postval("cari_postaKodu");
		$data["cari_olusturan"] = $u_id;
		$data["cari_olusturanAnaHesap"] = $anaHesap;
		$data["cari_olusturmaTarihi"] = $tarihi;
		$data["cari_olusturmaSaati"] = $saati;		$data["cari_bireysel"] = $cariTipi;
		$data["cari_EInvoiceType"] = $cariEInvoiceType;
		$data["cari_alias_pk"] = $alias_pk;		// Handle file upload for photos
		$fotograf_dosyalar = [];
		if (!empty($_FILES['cari_fotograflar']['name'][0])) {
			$count = count($_FILES['cari_fotograflar']['name']);
			$gorsel_dir = FCPATH . 'assets/uploads/cariler/gorseller/';
			if (!is_dir($gorsel_dir)) { 
				mkdir($gorsel_dir, 0777, true); 
			}
			for ($i = 0; $i < $count; $i++) {
				if ($_FILES['cari_fotograflar']['error'][$i] == 0) {
					$tmp_name = $_FILES['cari_fotograflar']['tmp_name'][$i];
					$original_name = $_FILES['cari_fotograflar']['name'][$i];
					$name = uniqid('foto_') . '_' . basename($original_name);
					$target_path = $gorsel_dir . $name;
					if (move_uploaded_file($tmp_name, $target_path)) {
						$fotograf_dosyalar[] = 'cariler/gorseller/' . $name;
					}
				}
			}
		}
		if (count($fotograf_dosyalar) > 0) {
			$data['fotograf_dosya'] = implode(',', $fotograf_dosyalar);
		}		// Handle file upload for documents
		$evrak_dosyalar = [];
		if (!empty($_FILES['cari_dosya']['name'][0])) {
			$count = count($_FILES['cari_dosya']['name']);
			$evrak_dir = FCPATH . 'assets/uploads/cariler/evraklar/';
			if (!is_dir($evrak_dir)) { 
				mkdir($evrak_dir, 0777, true); 
			}
			for ($i = 0; $i < $count; $i++) {
				if ($_FILES['cari_dosya']['error'][$i] == 0) {
					$tmp_name = $_FILES['cari_dosya']['tmp_name'][$i];
					$original_name = $_FILES['cari_dosya']['name'][$i];
					$name = uniqid('evrak_') . '_' . basename($original_name);
					$target_path = $evrak_dir . $name;
					if (move_uploaded_file($tmp_name, $target_path)) {
						$evrak_dosyalar[] = 'cariler/evraklar/' . $name;
					}
				}
			}
		}
		if (count($evrak_dosyalar) > 0) {
			$data['evrak_dosya'] = implode(',', $evrak_dosyalar);
		}		$cariKoduVarmiQ = "SELECT * FROM cari WHERE cari_kodu = '$cariKodu' AND cari_olusturanAnaHesap = '$anaHesap' AND cari_durum=true";
		$cariKoduVarmi = $this->db->query($cariKoduVarmiQ)->row();

		$page = postval("page");
		if ($page == "satisfatura") {
			if ($cariKoduVarmi) {
				$this->session->set_flashdata('cari_kodu_mevcut', 'OK');
			} else {
				$this->vt->insert("cari", $data);
				$cari_id = $this->db->insert_id();
				$this->session->set_flashdata('cari_ok', 'OK');
				logekle(1, 2);
			}
			redirect("fatura/satis-faturasi/$cari_id/1");
		} else if ($page == "alisfatura") {
			if ($cariKoduVarmi) {
				$this->session->set_flashdata('cari_kodu_mevcut', 'OK');
			} else {
				$this->vt->insert("cari", $data);
				$cari_id = $this->db->insert_id();
				$this->session->set_flashdata('cari_ok', 'OK');
				logekle(1, 2);
			}
			redirect("fatura/alis-faturasi/$cari_id/1");
		} else if ($page == "earsivfatura") {
			if ($cariKoduVarmi) {
				$this->session->set_flashdata('cari_kodu_mevcut', 'OK');
			} else {
				$this->vt->insert("cari", $data);
				$cari_id = $this->db->insert_id();
				$this->session->set_flashdata('cari_ok', 'OK');
				logekle(1, 2);
			}
			redirect("fatura/earsiv-olustur/$cari_id/1");
		} else if ($page == "efatura") {
			if ($cariKoduVarmi) {
				$this->session->set_flashdata('cari_kodu_mevcut', 'OK');
			} else {
				$this->vt->insert("cari", $data);
				$cari_id = $this->db->insert_id();
				$this->session->set_flashdata('cari_ok', 'OK');
				logekle(1, 2);
			}
			redirect("fatura/efatura-olustur/$cari_id/1");
		} else {
			if ($cariKoduVarmi) {
				$this->session->set_flashdata('cari_kodu_mevcut', 'OK');
				redirect("cari/cari-karti-olustur");
			} else {
				$this->vt->insert("cari", $data);
				$cari_id = $this->db->insert_id();
				$this->session->set_flashdata('cari_ok', 'OK');
				logekle(1, 2);
				redirect("cari/cari-karti-duzenle/$cari_id");
			}
		}
	}	public function cariKartiDuzenle($id)
	{
		$data["baslik"] = "Cari / Cari Kartı Düzenle";
		
		// Dashboard mantığı: Kendisi + bağlı kullanıcılar
		$control = session("r", "login_info");
		$u_id = $control->kullanici_id;
		$user_group_id = $control->grup_id;
		$admin_groups = [1, 2, 5, 7];
		$is_admin = in_array($user_group_id, $admin_groups);
		
		// Allowed user IDs (kendisi + bağlı kullanıcılar)
		$allowed_user_ids = array($u_id);
		if ($u_id > 0) {
			$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";
			$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();
			
			foreach ($bağlı_kullanicilar as $user) {
				$allowed_user_ids[] = $user->kullanici_id;
			}
		}
		$allowed_user_ids_str = implode(',', $allowed_user_ids);
		
		// Müşteri bilgilerini getir
		if ($is_admin) {
			// Admin gruplar için hiçbir kısıtlama yok
			$cariQ = "SELECT c.*, i.il as il_adi, il.ilce as ilce_adi 
					  FROM cari c 
					  LEFT JOIN iller i ON c.cari_il = i.id 
					  LEFT JOIN ilceler il ON c.cari_ilce = il.id 
					  WHERE c.cari_id = '$id' AND c.cari_durum = true";
		} else {
			// Dashboard mantığı: Kendisi + bağlı kullanıcılar (anaHesap kısıtlaması kaldırıldı)
			$cariQ = "SELECT c.*, i.il as il_adi, il.ilce as ilce_adi 
					  FROM cari c 
					  LEFT JOIN iller i ON c.cari_il = i.id 
					  LEFT JOIN ilceler il ON c.cari_ilce = il.id 
					  WHERE c.cari_id = '$id' AND c.cari_durum = true AND c.cari_olusturan IN ($allowed_user_ids_str)";
		}
		
		$cari = $this->db->query($cariQ)->row();
		
		if (!$cari) {
			show_error('Müşteri bulunamadı!');
			return;
		}
		
		$data["cari"] = $cari;
		
		// İller listesi
		$iller = $this->db->get('iller');
		if ($iller->num_rows() > 0) {
			$data['_iller'] = $iller->result();
		} else {
			$data['_iller'] = false;
		}
		
		// Ülkeler listesi
		$ulkeler = $this->db->order_by('ulke_adi', 'ASC')->get('ulkeler');
		if ($ulkeler->num_rows() > 0) {
			$data['_ulkeler'] = $ulkeler->result();
		} else {
			$data['_ulkeler'] = false;
		}
		
		// Müşteri grupları
		if ($is_admin) {
			// Admin gruplar için tüm grupları göster
			$cariGruplariQ = "SELECT * FROM cariGruplari";
		} else {
			// Normal kullanıcılar için kısıtlama kaldırıldı (Dashboard mantığı)
			$cariGruplariQ = "SELECT * FROM cariGruplari";
		}
		$data["cariGruplari"] = $this->db->query($cariGruplariQ)->result();
		
		// Detaylı iletişim bilgileri
		$cariDetayliQ = "SELECT * FROM cariDetayliIletisim WHERE cdetay_cariID = '$id'";
		$data["detaylıIletisim"] = $this->db->query($cariDetayliQ)->result();
		
		// Detaylı banka bilgileri
		$cariDetayliBankaQ = "SELECT * FROM cariDetayliBanka WHERE cdetayBanka_cariID = '$id'";
		$data["detayliBanka"] = $this->db->query($cariDetayliBankaQ)->result();
		
		// View'ı yükle
		$this->load->view('cari/cari-karti-olustur', $data);
	}

	public function mevcutCariKartiDuzenle()
	{
		// Debug logging to track form submissions
		$debug_file = FCPATH . 'debug_form_update.log';
		$timestamp = date('Y-m-d H:i:s');
		file_put_contents($debug_file, "=== FORM DEBUG START ===\n", FILE_APPEND);
		file_put_contents($debug_file, "Timestamp: $timestamp\n", FILE_APPEND);
		file_put_contents($debug_file, "Method: mevcutCariKartiDuzenle()\n", FILE_APPEND);
		file_put_contents($debug_file, "POST Data: " . print_r($_POST, true) . "\n", FILE_APPEND);
		
		$control2 = session("r", "login_info");
		$u_id = $control2->kullanici_id;

		date_default_timezone_set('Europe/Istanbul');
		$tarihi = (new DateTime('now'))->format('Y.m.d');
		$saati = (new DateTime('now'))->format('H:i:s');

		$anaHesap = anaHesapBilgisi();
		
		// Get the customer ID from POST data
		$cari_id = postval("cari_id");
		
		if (!$cari_id) {
			file_put_contents($debug_file, "ERROR: No cari_id found in POST data\n", FILE_APPEND);
			$this->session->set_flashdata('cari_update_error', 'OK');
			redirect($_SERVER['HTTP_REFERER']);
			return;
		}
		
		file_put_contents($debug_file, "Customer ID: $cari_id\n", FILE_APPEND);
		
		// Prepare update data
		$cariTipi = postval("cari_bireysel");
		$cariVergiNo = postval("cari_vergiNumarasi");
		$alias_pk = postval("cari_alias_pk");

		if ($alias_pk === "") {
			$cariEInvoiceType = 2;
		} else {
			$cariEInvoiceType = 1;
		}
		
		$cariVergiNoLen = strlen($cariVergiNo);
		
		// Build update data array
		$data = array();
		
		if ($cariVergiNoLen == 10) {
			$data["cari_vergiNumarasi"] = $cariVergiNo;
			$data["cari_tckn"] = null;
		} else if ($cariVergiNoLen == 11) {
			$data["cari_tckn"] = $cariVergiNo;
			$data["cari_vergiNumarasi"] = null;
		}

		$data["cari_kodu"] = mb_strtoupper(postval("cari_kodu"), "UTF-8");
		$data["cari_ad"] = mb_strtoupper(postval("cari_ad"), "UTF-8");
		$data["cari_soyad"] = mb_strtoupper(postval("cari_soyad"), "UTF-8");
		$data["cari_vergiDairesi"] = mb_strtoupper(postval("cari_vergiDairesi"), "UTF-8");
		$data["cari_ulke"] = postval("cari_ulke");
		$data["cari_il"] = postval("cari_il");
		$data["cari_ilce"] = postval("cari_ilce");
		$data["cari_websitesi"] = postval("cari_websitesi");
		$data["cari_firmaEPosta"] = postval("cari_firmaEPosta");
		$data["cari_firmaTelefon"] = postval("cari_firmaTelefon");
		$data["cari_adres"] = mb_strtoupper(postval("cari_adres"), "UTF-8");
		$data["cari_bireysel"] = $cariTipi;
		$data["cari_EInvoiceType"] = $cariEInvoiceType;
		$data["cari_alias_pk"] = $alias_pk;
		
		// Get current customer data to merge with existing files
		$current_cari = $this->db->where('cari_id', $cari_id)->get('cari')->row();
		
		// Handle file upload for photos
		$fotograf_dosyalar = [];
		if ($current_cari && !empty($current_cari->fotograf_dosya)) {
			$fotograf_dosyalar = explode(',', $current_cari->fotograf_dosya);
		}
		
		if (!empty($_FILES['cari_fotograflar']['name'][0])) {
			$count = count($_FILES['cari_fotograflar']['name']);
			$gorsel_dir = FCPATH . 'assets/uploads/cariler/gorseller/';
			if (!is_dir($gorsel_dir)) { 
				mkdir($gorsel_dir, 0777, true); 
			}
			for ($i = 0; $i < $count; $i++) {
				if ($_FILES['cari_fotograflar']['error'][$i] == 0) {
					$tmp_name = $_FILES['cari_fotograflar']['tmp_name'][$i];
					$original_name = $_FILES['cari_fotograflar']['name'][$i];
					$name = uniqid('foto_') . '_' . basename($original_name);
					$target_path = $gorsel_dir . $name;
					if (move_uploaded_file($tmp_name, $target_path)) {
						$fotograf_dosyalar[] = 'cariler/gorseller/' . $name;
						file_put_contents($debug_file, "Photo uploaded: " . $name . "\n", FILE_APPEND);
					}
				}
			}
		}
		if (count($fotograf_dosyalar) > 0) {
			$data['fotograf_dosya'] = implode(',', $fotograf_dosyalar);
		}
		
		// Handle file upload for documents
		$evrak_dosyalar = [];
		if ($current_cari && !empty($current_cari->evrak_dosya)) {
			$evrak_dosyalar = explode(',', $current_cari->evrak_dosya);
		}
		
		if (!empty($_FILES['cari_dosya']['name'][0])) {
			$count = count($_FILES['cari_dosya']['name']);
			$evrak_dir = FCPATH . 'assets/uploads/cariler/evraklar/';
			if (!is_dir($evrak_dir)) { 
				mkdir($evrak_dir, 0777, true); 
			}
			for ($i = 0; $i < $count; $i++) {
				if ($_FILES['cari_dosya']['error'][$i] == 0) {
					$tmp_name = $_FILES['cari_dosya']['tmp_name'][$i];
					$original_name = $_FILES['cari_dosya']['name'][$i];
					$name = uniqid('evrak_') . '_' . basename($original_name);
					$target_path = $evrak_dir . $name;
					if (move_uploaded_file($tmp_name, $target_path)) {
						$evrak_dosyalar[] = 'cariler/evraklar/' . $name;
						file_put_contents($debug_file, "Document uploaded: " . $name . "\n", FILE_APPEND);
					}
				}
			}
		}
		if (count($evrak_dosyalar) > 0) {
			$data['evrak_dosya'] = implode(',', $evrak_dosyalar);
		}
		
		file_put_contents($debug_file, "Update data prepared: " . print_r($data, true) . "\n", FILE_APPEND);
		file_put_contents($debug_file, "FILES data: " . print_r($_FILES, true) . "\n", FILE_APPEND);
		
		try {
			// Update the customer record
			$this->vt->update('cari', array('cari_id' => $cari_id), $data);
			
			file_put_contents($debug_file, "Database update successful\n", FILE_APPEND);
			
			// Set success message
			$this->session->set_flashdata('cari_update_ok', 'OK');
			logekle(1, 3); // Log the update action
			
			file_put_contents($debug_file, "=== FORM DEBUG END - SUCCESS ===\n\n", FILE_APPEND);
			
			// Redirect back to the edit form
			redirect("cari/cari-karti-duzenle/$cari_id");
			
		} catch (Exception $e) {
			file_put_contents($debug_file, "Database update failed: " . $e->getMessage() . "\n", FILE_APPEND);
			file_put_contents($debug_file, "=== FORM DEBUG END - ERROR ===\n\n", FILE_APPEND);
					$this->session->set_flashdata('cari_update_error', 'OK');
			redirect($_SERVER['HTTP_REFERER']);
		}
	}

	public function cariListesi()
	{
		$data["baslik"] = "Cari / Cari Listesi";
		
		// Giriş yapan kullanıcı bilgisi
		$control = session("r", "login_info");
		$u_id = $control->kullanici_id;
		
		// Dashboard'daki "Son Satışlarım" mantığı: Kendisi + bağlı kullanıcılar
		$allowed_user_ids = array($u_id);
		if ($u_id > 0) {
			$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";
			$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();
			
			foreach ($bağlı_kullanicilar as $user) {
				$allowed_user_ids[] = $user->kullanici_id;
			}
		}
		$allowed_user_ids_str = implode(',', $allowed_user_ids);
		
		$cariKodu = $this->input->get('cariKodu');
		$cariAdi = $this->input->get('cariAdi');
		$cariTipi = $this->input->get('bireysel');
		if ($cariTipi == "true") {
			$cariTipi2 = 1;
		} else if ($cariTipi == "false") {
			$cariTipi2 = 0;
		} else {
			$cariTipi2 = 2;
		}		$islemYapan = $this->input->get('islemYapan');
		$bulunduguIl = $this->input->get('bulunduguIl');
		$tarihGet = $this->input->get('tarihAraligi');

		$tarihAraligi = explode(' - ', $tarihGet);
		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0]));
		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1]));

		$urim = $this->uri->segment(2);
		$segment = 2;
		$sayfa = $this->input->get("sayfa");
		$page = $sayfa ? $sayfa : 0;
		$limit = 20;
		$pagem = $sayfa ? ($page - 1) * $limit : 0;
		if ((isset($cariKodu) && !empty($cariKodu)) || (isset($cariAdi) && !empty($cariAdi)) || (isset($islemYapan) && !empty($islemYapan)) || (isset($bulunduguIl) && !empty($bulunduguIl)) || (isset($tarihGet) && !empty($tarihGet)) || ($cariTipi != 2)) {
			// Dashboard mantığı: Kendisi + bağlı kullanıcılar
			$sorgu1 = !empty($islemYapan) ? "AND cari_olusturan = '$islemYapan'" : "";
			$sorgu2 = !empty($bulunduguIl) ? "AND cari_il = '$bulunduguIl'" : "";
			$sorgu3 = !empty($tarihGet) ? "AND cari_olusturmaTarihi BETWEEN '$tarih1' AND '$tarih2'" : "";
			$sorgu4 = !empty($cariTipi) ? "AND cari_bireysel = '$cariTipi2'" : "";
			
			$countq = "SELECT COUNT(*) as total FROM cari WHERE cari_durum=true AND cari_olusturan IN ($allowed_user_ids_str) AND cari_kodu LIKE '%$cariKodu%' AND cari_ad LIKE '%$cariAdi%' $sorgu1 $sorgu2 $sorgu3 $sorgu4";
			$sorgu = "SELECT * FROM cari WHERE cari_durum=true AND cari_olusturan IN ($allowed_user_ids_str) AND cari_kodu LIKE '%$cariKodu%' AND cari_ad LIKE '%$cariAdi%' $sorgu1 $sorgu2 $sorgu3 $sorgu4 ORDER BY cari_id DESC LIMIT $pagem,$limit";
			
			$countexe = $this->db->query($countq)->row();
			$count = $countexe->total;
		} else {
			// Dashboard mantığı: Kendisi + bağlı kullanıcılar
			$countq = "SELECT COUNT(*) as total FROM cari WHERE cari_durum=true AND cari_olusturan IN ($allowed_user_ids_str)";
			$sorgu = "SELECT * FROM cari WHERE cari_durum=true AND cari_olusturan IN ($allowed_user_ids_str) ORDER BY cari_id DESC LIMIT $pagem,$limit";
			
			$countexe = $this->db->query($countq)->row();
			$count = $countexe->total;
		}
		
		// DEBUG: Log sorgu bilgilerini
		$debug_file = FCPATH . 'debug_cari_listesi.log';
		$timestamp = date('Y-m-d H:i:s');
		file_put_contents($debug_file, "=== CARI LİSTESİ DEBUG ===\n", FILE_APPEND);
		file_put_contents($debug_file, "Timestamp: $timestamp\n", FILE_APPEND);
		file_put_contents($debug_file, "User ID: $u_id\n", FILE_APPEND);
		file_put_contents($debug_file, "Allowed User IDs: " . implode(', ', $allowed_user_ids) . "\n", FILE_APPEND);
		file_put_contents($debug_file, "Kısıtlama: Dashboard mantığı - Kendisi + bağlı kullanıcılar (cari_olusturan IN ($allowed_user_ids_str))\n", FILE_APPEND);
		file_put_contents($debug_file, "Count Query: $countq\n", FILE_APPEND);
		file_put_contents($debug_file, "Main Query: $sorgu\n", FILE_APPEND);
		file_put_contents($debug_file, "Count Result: $count\n", FILE_APPEND);
		
		// Toplam kayıt kontrolü
		$totalCariQ = "SELECT COUNT(*) as total FROM cari WHERE cari_durum=true";
		$totalCariResult = $this->db->query($totalCariQ)->row();
		file_put_contents($debug_file, "Total active cari (no filter): " . $totalCariResult->total . "\n", FILE_APPEND);
		
		$kullaniciCariQ = "SELECT COUNT(*) as total FROM cari WHERE cari_olusturan IN ($allowed_user_ids_str)";
		$kullaniciCariResult = $this->db->query($kullaniciCariQ)->row();
		file_put_contents($debug_file, "Total cari with allowed users filter: " . $kullaniciCariResult->total . "\n", FILE_APPEND);
		
		// Örnek kayıtları göster
		$ornekKayitlarQ = "SELECT cari_id, cari_kodu, cari_ad, cari_olusturan, cari_durum FROM cari LIMIT 5";
		$ornekKayitlar = $this->db->query($ornekKayitlarQ)->result();
		file_put_contents($debug_file, "Sample records:\n", FILE_APPEND);
		foreach($ornekKayitlar as $kayit) {
			$is_allowed = in_array($kayit->cari_olusturan, $allowed_user_ids) ? 'YES' : 'NO';
			file_put_contents($debug_file, "ID: {$kayit->cari_id}, Kod: {$kayit->cari_kodu}, Ad: {$kayit->cari_ad}, Oluşturan: {$kayit->cari_olusturan}, Durum: {$kayit->cari_durum}, Allowed: $is_allowed\n", FILE_APPEND);
		}
		file_put_contents($debug_file, "=== DEBUG END ===\n\n", FILE_APPEND);
		
		$data["count_of_list"] = $count;
		$this->load->library("pagination");
		$config = array();
		$config["base_url"] = base_url() . "/cari/$urim";
		$config["total_rows"] = $count;
		$config["per_page"] = $limit;
		$config["uri_segment"] = $segment;
		$config['use_page_numbers'] = TRUE;
		$config['enable_query_strings'] = TRUE;
		$config['page_query_string'] = TRUE;
		$config['reuse_query_string'] = TRUE;
		$config['query_string_segment'] = 'sayfa';
		$config['num_links'] = 9;
		$config['full_tag_open'] = '<div class="d-flex justify-content-center"><ul class="pagination">';
		$config['full_tag_close'] = '</ul></div>';
		$config['first_link'] = '&laquo;&laquo;';
		$config['first_tag_open'] = '<span class="page-link"><li class="page-item">';
		$config['first_tag_close'] = '</li></span>';
		$config['last_link'] = '&raquo;&raquo;';
		$config['last_tag_open'] = '<span class="page-link"><li class="page-item">';
		$config['last_tag_close'] = '</li></span>';
		$config['prev_link'] = '&laquo;';
		$config['prev_tag_open'] = '<span class="page-link"><li class="page-item">';
		$config['prev_tag_close'] = '</li></span>';
		$config['next_link'] = '&raquo;';
		$config['next_tag_open'] = '<span class="page-link"><li class="page-item">';
		$config['next_tag_close'] = '</li></span>';
		$this->pagination->initialize($config);
		$data["links"] = $this->pagination->create_links();
		$data["cari"] = $this->db->query($sorgu)->result();
		
		// Toplam satış faturası tutarını hesapla
		$toplamSatisTutarQ = "SELECT COALESCE(SUM(sf.satis_genelToplam), 0) as toplam_satis_tutar 
							  FROM satisfaturasi sf 
							  INNER JOIN cari c ON sf.satis_cariID = c.cari_id 
							  WHERE c.cari_durum=true AND c.cari_olusturan IN ($allowed_user_ids_str)";
		
		if ((isset($cariKodu) && !empty($cariKodu)) || (isset($cariAdi) && !empty($cariAdi)) || (isset($islemYapan) && !empty($islemYapan)) || (isset($bulunduguIl) && !empty($bulunduguIl)) || (isset($tarihGet) && !empty($tarihGet)) || ($cariTipi != 2)) {
			$toplamSatisTutarQ .= " AND c.cari_kodu LIKE '%$cariKodu%' AND c.cari_ad LIKE '%$cariAdi%' $sorgu1 $sorgu2 $sorgu3 $sorgu4";
		}
		
		$toplamSatisTutarResult = $this->db->query($toplamSatisTutarQ)->row();
		$data["toplamSatisTutar"] = $toplamSatisTutarResult->toplam_satis_tutar;
		
		$this->load->view("cari/cari-listesi", $data);
	}

	public function cariHareketleri()
	{
		$data["baslik"] = "Cari / Cari Hareketleri";
		$anaHesap = anaHesapBilgisi();
		
		$getCari = $this->input->get('cari');
		$hareketTuru = $this->input->get('hareketTuru');
		$belgeNo = $this->input->get('belgeNo');
		$tarihAraligi = $this->input->get('tarihAraligi');
		$siralama = $this->input->get('siralama');

		if (isset($getCari) && !empty($getCari)) {
			// Get customer information - NO RESTRICTION by account (allows any logged-in user to view any customer)
			$cariQ = "SELECT * FROM cari WHERE cari_id = '$getCari'";
			$cariExe = $this->db->query($cariQ)->row();
			$data["cariExe"] = $cariExe;
			
			if ($cariExe) {
				// Yeni UNION sorgusu ile tüm hareketleri getir
				$cariHareketleriQ = "
					SELECT 
						bh_tarih AS Tarih,
						'Banka' AS Tahsilat_Tipi,
						NULL    AS Borc,
						COALESCE(bh_giris, bh_cikis) AS Alacak,
						bh_aciklama AS Aciklama,
						bh_gorsel AS Gorsel,
						bh_id AS kayit_id,
						'banka' AS tablo
					FROM bankahareketleri
					WHERE bh_cariID = '$getCari'

					UNION ALL

					SELECT 
						cek_kayitTarihi AS Tarih,
						'Çek' AS Tahsilat_Tipi,
						NULL           AS Borc,
						cek_tutar      AS Alacak,
						cek_notAciklama AS Aciklama,
						cek_gorsel     AS Gorsel,
						cek_id AS kayit_id,
						'cek' AS tablo
					FROM cek
					WHERE cek_cariID = '$getCari'

					UNION ALL

					SELECT 
						kh_tarih AS Tarih,
						'Kasa'  AS Tahsilat_Tipi,
						NULL    AS Borc,
						COALESCE(kh_giris, kh_cikis) AS Alacak,
						kh_aciklama AS Aciklama,
						NULL AS Gorsel,
						kh_id AS kayit_id,
						'kasa' AS tablo
					FROM kasahareketleri
					WHERE kh_cariID = '$getCari'

					UNION ALL

					SELECT 
						senet_kayitTarihi AS Tarih,
						'Senet' AS Tahsilat_Tipi,
						NULL     AS Borc,
						senet_tutar AS Alacak,
						senet_notAciklama AS Aciklama,
						senet_gorsel AS Gorsel,
						senet_id AS kayit_id,
						'senet' AS tablo
					FROM senet
					WHERE senet_cariID = '$getCari'

					UNION ALL

					SELECT
						satis_faturaTarihi AS Tarih,
						'Satış Faturası'   AS Tahsilat_Tipi,
						satis_genelToplam  AS Borc,
						NULL               AS Alacak,
						satis_aciklama     AS Aciklama,
						satis_dosya        AS Gorsel,
						satis_id AS kayit_id,
						'satis' AS tablo
					FROM satisfaturasi
					WHERE satis_cariID = '$getCari'

					ORDER BY Tarih DESC
				";
				
				$data["cariHareketleri"] = $this->db->query($cariHareketleriQ)->result();
				
				// Bakiye hesaplama
				$toplamBorc = 0;
				$toplamAlacak = 0;
				foreach($data["cariHareketleri"] as $hareket) {
					if($hareket->Borc) $toplamBorc += $hareket->Borc;
					if($hareket->Alacak) $toplamAlacak += $hareket->Alacak;
				}
				$kalan = $toplamBorc - $toplamAlacak;
				$data["kalan"] = $kalan;
			} else {
				$data["cariHareketleri"] = array();
				$data["kalan"] = 0;
			}
		} else {
			$data["cariHareketleri"] = array();
			$data["kalan"] = 0;
		}
		
		$this->load->view("cari/cari-hareketleri", $data);
	}	public function gorselSil()
	{
		$response = array('success' => false, 'message' => '');
		
		try {
			$cari_id = $this->input->post('cari_id');
			$dosya_adi = $this->input->post('dosya_adi');
			
			if (!$cari_id || !$dosya_adi) {
				$response['message'] = 'Gerekli parametreler eksik - Cari ID: ' . $cari_id . ', Dosya: ' . $dosya_adi;
				echo json_encode($response);
				return;
			}
			
			// Dashboard mantığı: Kendisi + bağlı kullanıcılar
			$control = session("r", "login_info");
			$u_id = $control->kullanici_id;
			$user_group_id = $control->grup_id;
			$admin_groups = [1, 2, 5, 7];
			$is_admin = in_array($user_group_id, $admin_groups);
			
			// Allowed user IDs (kendisi + bağlı kullanıcılar)
			$allowed_user_ids = array($u_id);
			if ($u_id > 0) {
				$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";
				$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();
				
				foreach ($bağlı_kullanicilar as $user) {
					$allowed_user_ids[] = $user->kullanici_id;
				}
			}
			$allowed_user_ids_str = implode(',', $allowed_user_ids);
			
			// Cari kaydını getir
			if ($is_admin) {
				// Admin gruplar için hiçbir kısıtlama yok
				$cariQ = "SELECT fotograf_dosya FROM cari WHERE cari_id = '$cari_id'";
			} else {
				// Dashboard mantığı: Kendisi + bağlı kullanıcılar (anaHesap kısıtlaması kaldırıldı)
				$cariQ = "SELECT fotograf_dosya FROM cari WHERE cari_id = '$cari_id' AND cari_olusturan IN ($allowed_user_ids_str)";
			}
			
			$cariData = $this->db->query($cariQ)->row();
			
			if (!$cariData) {
				$response['message'] = 'Cari kaydı bulunamadı - ID: ' . $cari_id;
				echo json_encode($response);
				return;
			}
			
			// Mevcut görselleri al
			$mevcutGorseller = $cariData->fotograf_dosya;
			$gorselArray = explode(',', $mevcutGorseller);
			
			// Silinecek dosyayı listeden çıkar
			$yeniGorselArray = array();
			foreach ($gorselArray as $gorsel) {
				if (trim($gorsel) !== trim($dosya_adi)) {
					$yeniGorselArray[] = trim($gorsel);
				}
			}
			
			// Yeni listeyi oluştur
			$yeniGorselListesi = implode(',', $yeniGorselArray);
			
			// Veritabanını güncelle
			$updateData = array('fotograf_dosya' => $yeniGorselListesi);
			$this->vt->update('cari', array('cari_id' => $cari_id), $updateData);
			
			$response['success'] = true;
			$response['message'] = 'Görsel başarıyla silindi';
			
		} catch (Exception $e) {
			$response['message'] = 'Hata oluştu: ' . $e->getMessage();
		}
		
		echo json_encode($response);
	}
	public function evrakSil()
	{
		$response = array('success' => false, 'message' => '');
		
		try {
			$cari_id = $this->input->post('cari_id');
			$dosya_adi = $this->input->post('dosya_adi');
			
			if (!$cari_id || !$dosya_adi) {
				$response['message'] = 'Gerekli parametreler eksik - Cari ID: ' . $cari_id . ', Dosya: ' . $dosya_adi;
				echo json_encode($response);
				return;
			}
			
			// Dashboard mantığı: Kendisi + bağlı kullanıcılar
			$control = session("r", "login_info");
			$u_id = $control->kullanici_id;
			$user_group_id = $control->grup_id;
			$admin_groups = [1, 2, 5, 7];
			$is_admin = in_array($user_group_id, $admin_groups);
			
			// Allowed user IDs (kendisi + bağlı kullanıcılar)
			$allowed_user_ids = array($u_id);
			if ($u_id > 0) {
				$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";
				$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();
				
				foreach ($bağlı_kullanicilar as $user) {
					$allowed_user_ids[] = $user->kullanici_id;
				}
			}
			$allowed_user_ids_str = implode(',', $allowed_user_ids);
			
			// Cari kaydını getir
			if ($is_admin) {
				// Admin gruplar için hiçbir kısıtlama yok
				$cariQ = "SELECT evrak_dosya FROM cari WHERE cari_id = '$cari_id'";
			} else {
				// Dashboard mantığı: Kendisi + bağlı kullanıcılar (anaHesap kısıtlaması kaldırıldı)
				$cariQ = "SELECT evrak_dosya FROM cari WHERE cari_id = '$cari_id' AND cari_olusturan IN ($allowed_user_ids_str)";
			}
			
			$cariData = $this->db->query($cariQ)->row();
			
			if (!$cariData) {
				$response['message'] = 'Cari kaydı bulunamadı - ID: ' . $cari_id;
				echo json_encode($response);
				return;
			}
			
			// Mevcut evrakları al
			$mevcutEvraklar = $cariData->evrak_dosya;
			$evrakArray = explode(',', $mevcutEvraklar);
			
			// Silinecek dosyayı listeden çıkar
			$yeniEvrakArray = array();
			foreach ($evrakArray as $evrak) {
				if (trim($evrak) !== trim($dosya_adi)) {
					$yeniEvrakArray[] = trim($evrak);
				}
			}
			
			// Yeni listeyi oluştur
			$yeniEvrakListesi = implode(',', $yeniEvrakArray);
			
			// Veritabanını güncelle
			$updateData = array('evrak_dosya' => $yeniEvrakListesi);
			$this->vt->update('cari', array('cari_id' => $cari_id), $updateData);
			
			$response['success'] = true;
			$response['message'] = 'Evrak başarıyla silindi';
			
		} catch (Exception $e) {
			$response['message'] = 'Hata oluştu: ' . $e->getMessage();
		}
		
		echo json_encode($response);
	}
	public function cariBakiyeGetir()
	{
		$response = array('success' => false, 'bakiye' => '');
		
		try {
			$cari_id = $this->input->post('cari_id');
			
			if (empty($cari_id)) {
				$response['message'] = 'Cari ID boş';
				echo json_encode($response);
				return;
			}
			
			// Dashboard mantığı: Kendisi + bağlı kullanıcılar
			$control = session("r", "login_info");
			$u_id = $control->kullanici_id;
			$user_group_id = $control->grup_id;
			$admin_groups = [1, 2, 5, 7];
			$is_admin = in_array($user_group_id, $admin_groups);
			
			if (!$is_admin) {
				// Dashboard mantığı: Kendisi + bağlı kullanıcılar
				$allowed_user_ids = array($u_id);
				if ($u_id > 0) {
					$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";
					$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();
					
					foreach ($bağlı_kullanicilar as $user) {
						$allowed_user_ids[] = $user->kullanici_id;
					}
				}
				$allowed_user_ids_str = implode(',', $allowed_user_ids);
				
				$cariQ = "SELECT cari_ad, cari_soyad FROM cari WHERE cari_id = ? AND cari_olusturan IN ($allowed_user_ids_str)";
				$cari = $this->db->query($cariQ, array($cari_id))->row();
				
				// Cari hareketleri sayfasındaki UNION sorgusu ile tutarlı hesaplama
				$cariHareketleriQ = "
					SELECT 
						NULL    AS Borc,
						COALESCE(bh_giris, bh_cikis) AS Alacak
					FROM bankahareketleri
					WHERE bh_cariID = ?

					UNION ALL

					SELECT 
						NULL           AS Borc,
						cek_tutar      AS Alacak
					FROM cek
					WHERE cek_cariID = ?

					UNION ALL

					SELECT 
						NULL    AS Borc,
						COALESCE(kh_giris, kh_cikis) AS Alacak
					FROM kasahareketleri
					WHERE kh_cariID = ?

					UNION ALL

					SELECT 
						NULL     AS Borc,
						senet_tutar AS Alacak
					FROM senet
					WHERE senet_cariID = ?

					UNION ALL

					SELECT
						satis_genelToplam  AS Borc,
						NULL               AS Alacak
					FROM satisfaturasi
					WHERE satis_cariID = ?
				";
				
				$hareketler = $this->db->query($cariHareketleriQ, array($cari_id, $cari_id, $cari_id, $cari_id, $cari_id))->result();
				
				// Bakiye hesaplama
				$toplamBorc = 0;
				$toplamAlacak = 0;
				foreach($hareketler as $hareket) {
					if($hareket->Borc) $toplamBorc += $hareket->Borc;
					if($hareket->Alacak) $toplamAlacak += $hareket->Alacak;
				}
			} else {
				// Admin gruplar için hiçbir kısıtlama yok
				$cariQ = "SELECT cari_ad, cari_soyad FROM cari WHERE cari_id = ?";
				$cari = $this->db->query($cariQ, array($cari_id))->row();
				
				// Cari hareketleri sayfasındaki UNION sorgusu ile tutarlı hesaplama
				$cariHareketleriQ = "
					SELECT 
						NULL    AS Borc,
						COALESCE(bh_giris, bh_cikis) AS Alacak
					FROM bankahareketleri
					WHERE bh_cariID = ?

					UNION ALL

					SELECT 
						NULL           AS Borc,
						cek_tutar      AS Alacak
					FROM cek
					WHERE cek_cariID = ?

					UNION ALL

					SELECT 
						NULL    AS Borc,
						COALESCE(kh_giris, kh_cikis) AS Alacak
					FROM kasahareketleri
					WHERE kh_cariID = ?

					UNION ALL

					SELECT 
						NULL     AS Borc,
						senet_tutar AS Alacak
					FROM senet
					WHERE senet_cariID = ?

					UNION ALL

					SELECT
						satis_genelToplam  AS Borc,
						NULL               AS Alacak
					FROM satisfaturasi
					WHERE satis_cariID = ?
				";
				
				$hareketler = $this->db->query($cariHareketleriQ, array($cari_id, $cari_id, $cari_id, $cari_id, $cari_id))->result();
				
				// Bakiye hesaplama
				$toplamBorc = 0;
				$toplamAlacak = 0;
				foreach($hareketler as $hareket) {
					if($hareket->Borc) $toplamBorc += $hareket->Borc;
					if($hareket->Alacak) $toplamAlacak += $hareket->Alacak;
				}
			}
			
			if (!$cari) {
				$response['message'] = 'Cari bulunamadı';
				echo json_encode($response);
				return;
			}
			
			// Net bakiye = Borç - Alacak 
			// Pozitif: Müşterinin bizden alacağı var
			// Negatif: Müşterinin bize borcu var
			$bakiye = $toplamBorc - $toplamAlacak;
			
			// Formatla
			$bakiye_formatted = number_format(abs($bakiye), 2, ',', '.') . ' ₺';
			if ($bakiye > 0) {
				$bakiye_formatted = '<span class="text-danger">-' . $bakiye_formatted . '</span> <small>(Müşteri alacaklı)</small>';
			} elseif ($bakiye < 0) {
				$bakiye_formatted = '<span class="text-success">+' . $bakiye_formatted . '</span> <small>(Müşteri borçlu)</small>';
			} else {
				$bakiye_formatted = '<span class="text-muted">' . $bakiye_formatted . '</span> <small>(Bakiye sıfır)</small>';
			}
			
			$response['success'] = true;
			$response['bakiye'] = $bakiye_formatted;
			$response['bakiye_raw'] = $bakiye;
			
		} catch (Exception $e) {
			$response['message'] = 'Hata oluştu: ' . $e->getMessage();
		}
		
		echo json_encode($response);
	}
	public function gorselGoster($cari_id)
	{
		// Dashboard mantığı: Kendisi + bağlı kullanıcılar
		$control = session("r", "login_info");
		$u_id = $control->kullanici_id;
		$user_group_id = $control->grup_id;
		$admin_groups = [1, 2, 5, 7];
		$is_admin = in_array($user_group_id, $admin_groups);
		
		// URL parametrelerini kontrol et
		$tip = $this->input->get('tip'); // 'satis', 'banka', 'cek', 'senet' olabilir
		$satis_id = $this->input->get('satis_id');
		
		// Allowed user IDs (kendisi + bağlı kullanıcılar)
		$allowed_user_ids = array($u_id);
		if ($u_id > 0) {
			$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";
			$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();
			
			foreach ($bağlı_kullanicilar as $user) {
				$allowed_user_ids[] = $user->kullanici_id;
			}
		}
		$allowed_user_ids_str = implode(',', $allowed_user_ids);
		
		if ($tip == 'satis' && $satis_id) {
			// Satış görsellerini getir
			if ($is_admin) {
				$satisQ = "SELECT sf.satis_dosya, c.cari_ad, c.cari_soyad 
						   FROM satisfaturasi sf 
						   INNER JOIN cari c ON sf.satis_cariID = c.cari_id 
						   WHERE sf.satis_id = '$satis_id' AND c.cari_id = '$cari_id'";
			} else {
				$satisQ = "SELECT sf.satis_dosya, c.cari_ad, c.cari_soyad 
						   FROM satisfaturasi sf 
						   INNER JOIN cari c ON sf.satis_cariID = c.cari_id 
						   WHERE sf.satis_id = '$satis_id' AND c.cari_id = '$cari_id' 
						   AND c.cari_olusturan IN ($allowed_user_ids_str)";
			}
			
			$satis = $this->db->query($satisQ)->row();
			
			if (!$satis) {
				show_error('Satış kaydı bulunamadı!');
				return;
			}
			
			$data['cari'] = $satis;
			$data['cari_id'] = $cari_id;
			$data['satis_id'] = $satis_id;
			$data['is_admin'] = $is_admin;
			$data['tip'] = 'satis';
			$data['dosya_field'] = $satis->satis_dosya;
			
		} else if ($tip == 'banka') {
			// Banka hareketleri görsellerini getir
			if ($is_admin) {
				$bankaQ = "SELECT GROUP_CONCAT(bh_gorsel SEPARATOR ',') as banka_gorselleri, c.cari_ad, c.cari_soyad 
						   FROM bankahareketleri bh 
						   INNER JOIN cari c ON bh.bh_cariID = c.cari_id 
						   WHERE bh.bh_cariID = '$cari_id' AND bh.bh_gorsel IS NOT NULL AND bh.bh_gorsel != ''
						   GROUP BY c.cari_id";
			} else {
				$bankaQ = "SELECT GROUP_CONCAT(bh_gorsel SEPARATOR ',') as banka_gorselleri, c.cari_ad, c.cari_soyad 
						   FROM bankahareketleri bh 
						   INNER JOIN cari c ON bh.bh_cariID = c.cari_id 
						   WHERE bh.bh_cariID = '$cari_id' AND bh.bh_gorsel IS NOT NULL AND bh.bh_gorsel != ''
						   AND c.cari_olusturan IN ($allowed_user_ids_str)
						   GROUP BY c.cari_id";
			}
			
			$banka = $this->db->query($bankaQ)->row();
			
			if (!$banka) {
				show_error('Banka hareketi bulunamadı!');
				return;
			}
			
			$data['cari'] = $banka;
			$data['cari_id'] = $cari_id;
			$data['is_admin'] = $is_admin;
			$data['tip'] = 'banka';
			$data['dosya_field'] = $banka->banka_gorselleri;
			
		} else if ($tip == 'cek') {
			// Çek görsellerini getir
			if ($is_admin) {
				$cekQ = "SELECT GROUP_CONCAT(cek_gorsel SEPARATOR ',') as cek_gorselleri, c.cari_ad, c.cari_soyad 
						 FROM cek 
						 INNER JOIN cari c ON cek.cek_cariID = c.cari_id 
						 WHERE cek.cek_cariID = '$cari_id' AND cek.cek_gorsel IS NOT NULL AND cek.cek_gorsel != ''
						 GROUP BY c.cari_id";
			} else {
				$cekQ = "SELECT GROUP_CONCAT(cek_gorsel SEPARATOR ',') as cek_gorselleri, c.cari_ad, c.cari_soyad 
						 FROM cek 
						 INNER JOIN cari c ON cek.cek_cariID = c.cari_id 
						 WHERE cek.cek_cariID = '$cari_id' AND cek.cek_gorsel IS NOT NULL AND cek.cek_gorsel != ''
						 AND c.cari_olusturan IN ($allowed_user_ids_str)
						 GROUP BY c.cari_id";
			}
			
			$cek = $this->db->query($cekQ)->row();
			
			if (!$cek) {
				show_error('Çek kaydı bulunamadı!');
				return;
			}
			
			$data['cari'] = $cek;
			$data['cari_id'] = $cari_id;
			$data['is_admin'] = $is_admin;
			$data['tip'] = 'cek';
			$data['dosya_field'] = $cek->cek_gorselleri;
			
		} else if ($tip == 'senet') {
			// Senet görsellerini getir
			if ($is_admin) {
				$senetQ = "SELECT GROUP_CONCAT(senet_gorsel SEPARATOR ',') as senet_gorselleri, c.cari_ad, c.cari_soyad 
						   FROM senet 
						   INNER JOIN cari c ON senet.senet_cariID = c.cari_id 
						   WHERE senet.senet_cariID = '$cari_id' AND senet.senet_gorsel IS NOT NULL AND senet.senet_gorsel != ''
						   GROUP BY c.cari_id";
			} else {
				$senetQ = "SELECT GROUP_CONCAT(senet_gorsel SEPARATOR ',') as senet_gorselleri, c.cari_ad, c.cari_soyad 
						   FROM senet 
						   INNER JOIN cari c ON senet.senet_cariID = c.cari_id 
						   WHERE senet.senet_cariID = '$cari_id' AND senet.senet_gorsel IS NOT NULL AND senet.senet_gorsel != ''
						   AND c.cari_olusturan IN ($allowed_user_ids_str)
						   GROUP BY c.cari_id";
			}
			
			$senet = $this->db->query($senetQ)->row();
			
			if (!$senet) {
				show_error('Senet kaydı bulunamadı!');
				return;
			}
			
			$data['cari'] = $senet;
			$data['cari_id'] = $cari_id;
			$data['is_admin'] = $is_admin;
			$data['tip'] = 'senet';
			$data['dosya_field'] = $senet->senet_gorselleri;
			
		} else {
			// Cari görsellerini getir (eski davranış)
			if ($is_admin) {
				// Admin gruplar için hiçbir kısıtlama yok
				$cariQ = "SELECT cari_ad, cari_soyad, fotograf_dosya FROM cari WHERE cari_id = '$cari_id' AND cari_durum = true";
			} else {
				// Dashboard mantığı: Kendisi + bağlı kullanıcılar (anaHesap kısıtlaması kaldırıldı)
				$cariQ = "SELECT cari_ad, cari_soyad, fotograf_dosya FROM cari WHERE cari_id = '$cari_id' AND cari_durum = true AND cari_olusturan IN ($allowed_user_ids_str)";
			}
			
			$cari = $this->db->query($cariQ)->row();
			
			if (!$cari) {
				show_error('Cari kaydı bulunamadı!');
				return;
			}
			
			$data['cari'] = $cari;
			$data['cari_id'] = $cari_id;
			$data['is_admin'] = $is_admin;
			$data['tip'] = 'cari';
			$data['dosya_field'] = $cari->fotograf_dosya;
		}
		
		$this->load->view('cari/gorsel-goster', $data);
	}
	
	public function evrakGoster($cari_id)
	{
		// Dashboard mantığı: Kendisi + bağlı kullanıcılar
		$control = session("r", "login_info");
		$u_id = $control->kullanici_id;
		$user_group_id = $control->grup_id;
		$admin_groups = [1, 2, 5, 7];
		$is_admin = in_array($user_group_id, $admin_groups);
		
		// Allowed user IDs (kendisi + bağlı kullanıcılar)
		$allowed_user_ids = array($u_id);
		if ($u_id > 0) {
			$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";
			$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();
			
			foreach ($bağlı_kullanicilar as $user) {
				$allowed_user_ids[] = $user->kullanici_id;
			}
		}
		$allowed_user_ids_str = implode(',', $allowed_user_ids);
		
		// Cari kaydını getir
		if ($is_admin) {
			// Admin gruplar için hiçbir kısıtlama yok
			$cariQ = "SELECT cari_ad, cari_soyad, evrak_dosya FROM cari WHERE cari_id = '$cari_id' AND cari_durum = true";
		} else {
			// Dashboard mantığı: Kendisi + bağlı kullanıcılar (anaHesap kısıtlaması kaldırıldı)
			$cariQ = "SELECT cari_ad, cari_soyad, evrak_dosya FROM cari WHERE cari_id = '$cari_id' AND cari_durum = true AND cari_olusturan IN ($allowed_user_ids_str)";
		}
		
		$cari = $this->db->query($cariQ)->row();
		
		if (!$cari) {
			show_error('Cari kaydı bulunamadı!');
			return;
		}
		
		$data['cari'] = $cari;
		$data['cari_id'] = $cari_id;
		$data['is_admin'] = $is_admin;
		
		$this->load->view('cari/evrak-goster', $data);
	}
	
	/**
	 * Mobile-friendly method to get districts based on city ID
	 * Used for AJAX calls from customer creation form
	 */
	public function getIlceler()
	{
		// Get city ID from POST request
		$il_id = $this->input->post('il_id');
		
		// Debug logging for mobile issues
		$debug_log_file = FCPATH . 'debug_cari_listesi.log';
		$timestamp = date('Y-m-d H:i:s');
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
		$is_mobile = preg_match('/(android|iphone|ipad|mobile)/i', $user_agent);
		
		$debug_info = array(
			'timestamp' => $timestamp,
			'method' => 'getIlceler',
			'il_id' => $il_id,
			'is_mobile' => $is_mobile,
			'user_agent' => $user_agent
		);
		
		$log_entry = $timestamp . " [ILCE_REQUEST] " . json_encode($debug_info) . "\n";
		file_put_contents($debug_log_file, $log_entry, FILE_APPEND | LOCK_EX);
		
		if (empty($il_id)) {
			$data = array('status' => 'error', 'message' => 'İl ID Bilgisi Alınamadı..!');
		} else {
			// Get districts for the given city ID
			$ilceler = $this->db->get_where('ilceler', array('il_id' => $il_id));
			
			if ($ilceler->num_rows() > 0) {
				$ilceList = array();
				foreach ($ilceler->result() as $item) {
					$ilceList[] = array('id' => $item->id, 'ilce' => $item->ilce);
				}
				$data = array('status' => 'ok', 'message' => '', 'data' => $ilceList);
			} else {
				$data = array('status' => 'error', 'message' => 'İlçe Bulunamadı..!');
			}
		}
		
		// Return JSON response
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function gorselYukle() {
		// Dashboard mantığı: Kendisi + bağlı kullanıcılar
		$control = session("r", "login_info");
		$u_id = $control->kullanici_id;
		$user_group_id = $control->grup_id;
		$admin_groups = [1, 2, 5, 7];
		$is_admin = in_array($user_group_id, $admin_groups);

		if (!$is_admin) {
			echo json_encode(['success' => false, 'message' => 'Yetkiniz yok!']);
			return;
		}

		$cari_id = postval('cari_id');
		$tip = postval('tip');
		$satis_id = postval('satis_id');

		if (empty($_FILES['gorsel_dosyalar']['name'][0])) {
			echo json_encode(['success' => false, 'message' => 'Dosya seçilmedi!']);
			return;
		}

		// Upload path'ini tip'e göre belirle
		$upload_path = './assets/uploads/';
		switch($tip) {
			case 'satis':
				$upload_path = './assets/uploads/faturalar/';
				break;
			case 'banka':
				$upload_path = './assets/uploads/dekontlar/';
				break;
			case 'cek':
				$upload_path = './assets/uploads/cekler/';
				break;
			case 'senet':
				$upload_path = './assets/uploads/senetler/';
				break;
			default:
				$upload_path = './assets/uploads/';
				break;
		}

		if (!file_exists($upload_path)) {
			mkdir($upload_path, 0777, true);
		}

		$uploaded_files = array();

		foreach ($_FILES['gorsel_dosyalar']['name'] as $key => $name) {
			if (!empty($name)) {
				$tmp_name = $_FILES['gorsel_dosyalar']['tmp_name'][$key];
				$size = $_FILES['gorsel_dosyalar']['size'][$key];
				$error = $_FILES['gorsel_dosyalar']['error'][$key];

				if ($error === 0) {
					$allowed_types = array('jpg', 'jpeg', 'png', 'pdf');
					$file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

					if (in_array($file_ext, $allowed_types) && $size <= 10485760) { // 10MB limit
						$encrypted_name = md5(uniqid(rand(), true)) . '.' . $file_ext;
						$destination = $upload_path . $encrypted_name;

						if (move_uploaded_file($tmp_name, $destination)) {
							// Tip'e göre veritabanına kaydedilecek yolu belirle
							$db_path = '';
							switch($tip) {
								case 'satis':
									$db_path = 'faturalar/' . $encrypted_name;
									break;
								case 'banka':
									$db_path = 'dekontlar/' . $encrypted_name;
									break;
								case 'cek':
									$db_path = 'cekler/' . $encrypted_name;
									break;
								case 'senet':
									$db_path = 'senetler/' . $encrypted_name;
									break;
								default:
									$db_path = $encrypted_name;
									break;
							}
							$uploaded_files[] = $db_path;
						}
					}
				}
			}
		}

		if (!empty($uploaded_files)) {
			$file_list = implode(',', $uploaded_files);
			
			// Tip'e göre veritabanını güncelle
			if ($tip == 'satis' && $satis_id) {
				// Mevcut dosyaları al
				$existing = $this->db->query("SELECT satis_dosya FROM satisFaturasi WHERE satis_id = '$satis_id'")->row();
				$current_files = $existing && !empty($existing->satis_dosya) ? $existing->satis_dosya . ',' : '';
				$new_files = $current_files . $file_list;
				
				$dataUpdate = array('satis_dosya' => $new_files);
				$this->vt->update('satisFaturasi', array('satis_id' => $satis_id), $dataUpdate);
			} else {
				// Cari fotoğrafları için
				$existing = $this->db->query("SELECT fotograf_dosya FROM cari WHERE cari_id = '$cari_id'")->row();
				$current_files = $existing && !empty($existing->fotograf_dosya) ? $existing->fotograf_dosya . ',' : '';
				$new_files = $current_files . $file_list;
				
				$dataUpdate = array('fotograf_dosya' => $new_files);
				$this->vt->update('cari', array('cari_id' => $cari_id), $dataUpdate);
			}

			echo json_encode(['success' => true, 'message' => count($uploaded_files) . ' dosya başarıyla yüklendi.']);
		} else {
			echo json_encode(['success' => false, 'message' => 'Dosya yüklenemedi!']);
		}
	}
	
	/**
	 * Personel listesini getir
	 */
	public function getPersonelList()
	{
		try {
			$personelQ = "SELECT kullanici_id, kullanici_ad, kullanici_soyad 
						  FROM kullanicilar 
						  ORDER BY kullanici_ad ASC, kullanici_soyad ASC";
			
			$personeller = $this->db->query($personelQ)->result();
			
			echo json_encode([
				'success' => true,
				'data' => $personeller
			], JSON_UNESCAPED_UNICODE);
			
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'message' => 'Personel listesi alınırken hata oluştu: ' . $e->getMessage()
			], JSON_UNESCAPED_UNICODE);
		}
	}
	
	/**
	 * Cari oluşturan personeli güncelle
	 */
	public function updateCariOlusturan()
	{
		try {
			$cari_id = $this->input->post('cari_id');
			$cari_olusturan = $this->input->post('cari_olusturan');
			
			if (empty($cari_id) || empty($cari_olusturan)) {
				echo json_encode([
					'success' => false,
					'message' => 'Gerekli parametreler eksik!'
				], JSON_UNESCAPED_UNICODE);
				return;
			}
			
			// Cari kaydının varlığını kontrol et
			$this->db->where('cari_id', $cari_id);
			$cari_exists = $this->db->get('cari')->num_rows();
			
			if ($cari_exists == 0) {
				echo json_encode([
					'success' => false,
					'message' => 'Cari kaydı bulunamadı!'
				], JSON_UNESCAPED_UNICODE);
				return;
			}
			
			// Personelin varlığını kontrol et
			$this->db->where('kullanici_id', $cari_olusturan);
			$personel_exists = $this->db->get('kullanicilar')->num_rows();
			
			if ($personel_exists == 0) {
				echo json_encode([
					'success' => false,
					'message' => 'Personel bulunamadı!'
				], JSON_UNESCAPED_UNICODE);
				return;
			}
			
			// Güncelleme işlemi
			$update_data = [
				'cari_olusturan' => $cari_olusturan
			];
			
			$this->db->where('cari_id', $cari_id);
			$result = $this->db->update('cari', $update_data);
			
			if ($result) {
				echo json_encode([
					'success' => true,
					'message' => 'Sorumlu personel başarıyla güncellendi!'
				], JSON_UNESCAPED_UNICODE);
			} else {
				echo json_encode([
					'success' => false,
					'message' => 'Güncelleme işlemi başarısız!'
				], JSON_UNESCAPED_UNICODE);
			}
			
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'message' => 'Güncelleme sırasında hata oluştu: ' . $e->getMessage()
			], JSON_UNESCAPED_UNICODE);
		}
	}

	/**
	 * Ülkeye göre illeri getir
	 */
	public function getIllerByUlke()
	{
		$ulke_id = $this->input->post('ulke_id');
		if (empty($ulke_id)) {
			$data = array('status' => 'error', 'message' => 'Ülke ID Bilgisi Alınamadı..!');
		} else {
			// Ülkeye göre iller çekiliyor...
			$iller = $this->db->get_where('iller', array('ulke_id' => $ulke_id));
			if ($iller->num_rows() > 0) {
				$data = array('status' => 'success', 'data' => $iller->result());
			} else {
				$data = array('status' => 'error', 'message' => 'Bu ülkeye ait il bilgisi bulunamadı..!');
			}
		}
		header('Content-Type: application/json');
		echo json_encode($data);
	}
}