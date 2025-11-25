<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Home extends CI_Controller

{



	public function __construct()

	{

		parent::__construct();

		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

		$this->load->model('vt');



		$control = session("r", "login");



		/*$control2 = session("r", "login_info");

		if(isDemoActive() == 1){

			if(demoDateExpireControl($control2->kullanici_id) <= -1){

				redirect('hata/hata2');

			}

		}*/



		if(!$control){

			redirect("check");

		}

	}



	public function index(){

	
		$data["baslik"] = "Anasayfa";
		$anaHesap = anaHesapBilgisi();

		// Kullanıcı grup dashboard kontrolü
		$control2 = session("r", "login_info");
		if ($control2 && isset($control2->grup_id) && $control2->grup_id) {
			// Kullanıcının grup dashboard ayarını kontrol et
			$grupDashboard = $this->db->query("SELECT dashboard FROM kullanici_grubu WHERE kg_id = ? AND kg_olusturanAnaHesap = ?", 
				array($control2->grup_id, $anaHesap))->row();
			
			if ($grupDashboard && !empty($grupDashboard->dashboard)) {
				// Grup için özel dashboard varsa kontrol et
				$dashboardPath = $grupDashboard->dashboard;
				
				// Eğer default-dashboard ise default dashboard göster
				if ($dashboardPath === 'default-dashboard') {
					$this->load->view('default-dashboard', $data);
					return;
				}
				
				// Eğer index ise ana dashboard göster  
				if ($dashboardPath === 'index') {
					// Normal işleme devam et (altta ana dashboard kodu var)
				} else {
					// Diğer özel dashboard'lar için dosya var mı kontrol et
					if (file_exists(APPPATH . 'views/' . $dashboardPath . '.php')) {
						$this->load->view($dashboardPath, $data);
						return;
					} else {
						// Dosya yoksa default dashboard göster
						$this->load->view('default-dashboard', $data);
						return;
					}
				}
			} else {
				// Grup dashboard ayarı yoksa default dashboard göster
				$this->load->view('default-dashboard', $data);
				return;
			}
		} else {
			// Kullanıcı hiç gruba atanmamışsa default dashboard göster
			$this->load->view('default-dashboard', $data);
			return;
		}

		$modulGibSorgula = modulSorgula($anaHesap, 2);
		$modulOcrSorgula = modulSorgula($anaHesap, 4);

		if ($modulGibSorgula) {

			redirect("gib/index");

		}else if ($modulOcrSorgula) {

			redirect("ocr/index");

		} 	 else {			// Get logged-in user ID to filter only their sales invoices

			$control2 = session("r", "login_info");

			$logged_in_user_id = $control2->kullanici_id;

			

			$satisFaturasiQ = "SELECT satis_faturaTarihi as faturaTarihi,satis_faturaNo as faturaNo,satis_genelToplam as genelToplam,satis_paraBirimi as paraBirimi,'satis' as tip,satis_id as id,cari.cari_id,concat(cari.cari_ad,' ',cari.cari_soyad) as cari_ad FROM satisFaturasi inner join cari on satisFaturasi.satis_cariID=cari.cari_id  WHERE satis_olusturanAnaHesap = '$anaHesap' and satis_InvoiceType is null and satis_olusturan = '$logged_in_user_id' ORDER BY satis_id DESC LIMIT 6";

			$alisFaturasiQ = "SELECT alis_faturaTarihi as faturaTarihi,alis_faturaNo as faturaNo,alis_genelToplam as genelToplam,alis_paraBirimi as paraBirimi,'alis' as tip,alis_id as id,cari.cari_id,concat(cari.cari_ad,' ',cari.cari_soyad) as cari_ad FROM alisFaturasi inner join cari on alisFaturasi.alis_cariID=cari.cari_id  WHERE alis_olusturanAnaHesap = '$anaHesap' and alis_olusturan = '$logged_in_user_id' ORDER BY alis_id DESC LIMIT 6";

    		

    		$af = $this->db->query($alisFaturasiQ)->result();

            $sf = $this->db->query($satisFaturasiQ)->result();

    		

    	

    	$sf=seciliAlanlarSorgu($satisFaturasiQ);

      	$af=seciliAlanlarSorgu($alisFaturasiQ);

         

    		$fatura = array_merge($sf, $af);

    		rsort($fatura);

    		$data["fatura"] = array_chunk($fatura, 6)[0];  		// Calculate sales count and total amount for the logged-in user for today only

		$control2 = session("r", "login_info");

		$satis_adedi_bugun = 0;

		$satis_toplam_bugun = 0;

		

		if ($control2 && isset($control2->kullanici_id)) {

			$logged_in_user_id = $control2->kullanici_id;

			$bugun = date('Y-m-d');

					$satis_adedi_query = "SELECT COUNT(*) as toplam_satis_faturasi 

								  FROM satisFaturasi 

								  WHERE satis_olusturan = '$logged_in_user_id' 

									AND satis_faturaTarihi = '$bugun'

									AND satis_olusturanAnaHesap = '$anaHesap'

									AND satis_InvoiceType IS NULL";

			$satis_adedi_result = $this->db->query($satis_adedi_query)->row();

			

			if ($satis_adedi_result && isset($satis_adedi_result->toplam_satis_faturasi)) {

				$satis_adedi_bugun = $satis_adedi_result->toplam_satis_faturasi;

			}

			

			// Calculate total sales amount for today

			$satis_toplam_query = "SELECT SUM(satis_genelToplam) as toplam_satis_tutari 

								   FROM satisFaturasi 

								   WHERE satis_olusturan = '$logged_in_user_id' 

									 AND satis_faturaTarihi = '$bugun'

									 AND satis_olusturanAnaHesap = '$anaHesap'

									 AND satis_InvoiceType IS NULL";

			$satis_toplam_result = $this->db->query($satis_toplam_query)->row();

			

			if ($satis_toplam_result && isset($satis_toplam_result->toplam_satis_tutari)) {

				$satis_toplam_bugun = $satis_toplam_result->toplam_satis_tutari ?: 0;

			}

		}

		

		$data["satis_adedi_bugun"] = $satis_adedi_bugun;
		$data["satis_toplam_bugun"] = $satis_toplam_bugun;
         
			$this->load->view('index', $data);

		}

		



	}





	public function istatistikDonut()

	{

		$anaHesap = anaHesapBilgisi();



		$satisFaturasiQ = "SELECT sum(satis_genelToplam) as toplam,'satis' as tip,satis_id as id FROM satisFaturasi WHERE satis_olusturanAnaHesap = '$anaHesap' and satis_InvoiceType is null ORDER BY satis_id DESC LIMIT 6";

		$sf = $this->db->query($satisFaturasiQ)->row();



		$alisFaturasiQ = "SELECT sum(alis_genelToplam) as toplam,'alis' as tip,alis_id as id FROM alisFaturasi WHERE alis_olusturanAnaHesap = '$anaHesap' ORDER BY alis_id DESC LIMIT 6";

		$af = $this->db->query($alisFaturasiQ)->row();

		$data = "$sf->toplam,$af->toplam";

		

		print ($data);

	}





	public function istatistikBar()

	{

		$anaHesap = anaHesapBilgisi();



		$bugun = date("Y-m-d");

		$once = strtotime('-6 days', strtotime($bugun));

		$dateOnce = date("Y-m-d", $once);



		$satisFaturasiQ = "SELECT satis_faturaTarihi,sum(satis_genelToplam) as satis_genelToplam FROM satisFaturasi where satis_InvoiceType is null and satis_olusturanAnaHesap=$anaHesap and satis_faturaTarihi BETWEEN '$dateOnce' and '$bugun'  GROUP by satis_faturaTarihi order by satis_faturaTarihi ASC ";

		$sf = $this->db->query($satisFaturasiQ)->result();

		$alisFaturasiQ = "SELECT alis_faturaTarihi,sum(alis_genelToplam) as alis_genelToplam FROM alisFaturasi where alis_olusturanAnaHesap=$anaHesap and alis_faturaTarihi BETWEEN '$dateOnce' and '$bugun' GROUP by alis_faturaTarihi order by alis_faturaTarihi ASC";



		$af = $this->db->query($alisFaturasiQ)->result();

		$dataSf = "";

		$dataAf = "";

		$dataGun = "";



		for ($i = $once; $i <= strtotime($bugun); $i = $i + 86400) {

			$date = date("Y-m-d", $i);



			$dataGun .= gunler(date('w', $i)) . ",";

			$j = 0;

			$k = 0;

			foreach ($sf as $satis) {

				if ($satis->satis_faturaTarihi == $date) {

					$dataSf .= "" . round($satis->satis_genelToplam, 2) . ",";

					$j = 1;

					break;

				}

			}



			if ($j == 0)

				$dataSf .= "0,";



			foreach ($af as $alis) {

				if ($alis->alis_faturaTarihi == $date) {

					$dataAf .= "" . round($alis->alis_genelToplam, 2) . ",";

					$k = 1;

					break;

				}

			}

			if ($k == 0)

				$dataAf .= "0,";

		}





		$data = trim($dataSf, ",") . "-" . trim($dataAf, ",") . "-" . trim($dataGun, ",");



		print ($data);



	}



	public function get_ilceler()

	{

		//ajaxta post ile gelen il id'si

		$il_id = $this->input->post('il_id');

		if (empty($il_id)) {

			$data = array('status' => 'error', 'message' => 'Ä°l ID Bilgisi AlÄ±namadÄ±..!');

		} else {

			//ile gÃ¶re ilÃ§eler Ã§ekiliyor...

			$ilceler = $this->db->get_where('ilceler', array('il_id' => $il_id));

			if ($ilceler->num_rows() > 0) {

				$ilceList = array();

				foreach ($ilceler->result() as $item) {

					$ilceList[] = array('id' => $item->id, 'ilce' => $item->ilce);

				}

				//var olan iller data keyine aktarÄ±lÄ±yor...

				$data = array('status' => 'ok', 'message' => '', 'data' => $ilceList);

			} else {

				$data = array('status' => 'error', 'message' => 'Ä°lÃ§e BulunamadÄ±..!');

			}

		}

		//Ã§Ä±ktÄ±yÄ± jsona uygun bir yapÄ±da set ediyoruz...

		$this->output->set_content_type('application/json')->set_output(json_encode($data));

	}



	/**
	 * İlleri getir (ülke bazında)
	 */
	public function get_iller()
	{
		$ulke_id = $this->input->post('ulke_id');
		if (empty($ulke_id)) {
			// Eğer ulke_id yoksa, tüm illeri getir (Türkiye için)
			$iller = $this->db->order_by('il', 'ASC')->get('iller');
		} else {
			// Belirtilen ülkeye ait illeri getir
			$iller = $this->db->where('ulke_id', $ulke_id)->order_by('il', 'ASC')->get('iller');
		}
		
		if ($iller->num_rows() > 0) {
			$ilList = array();
			foreach ($iller->result() as $item) {
				$ilList[] = array('id' => $item->id, 'il' => $item->il);
			}
			$data = array('status' => 'ok', 'message' => '', 'data' => $ilList);
		} else {
			$data = array('status' => 'error', 'message' => 'İl Bulunamadı..!');
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	/**
	 * Ülkeleri getir
	 */
	public function get_ulkeler()
	{
		$ulkeler = $this->db->order_by('ulke_adi', 'ASC')->get('ulkeler');
		
		if ($ulkeler->num_rows() > 0) {
			$ulkeList = array();
			foreach ($ulkeler->result() as $item) {
				$ulkeList[] = array(
					'id' => $item->id, 
					'ulke_adi' => $item->ulke_adi,
					'ulke_kodu' => $item->ulke_kodu
				);
			}
			$data = array('status' => 'ok', 'message' => '', 'data' => $ulkeList);
		} else {
			$data = array('status' => 'error', 'message' => 'Ülke Bulunamadı..!');
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	/**
	 * Test endpoint for countries - remove after testing
	 */
	public function test_ulkeler()
	{
		$ulkeler = $this->db->order_by('ulke_adi', 'ASC')->get('ulkeler');
		echo "<h3>Ülkeler Tablosu Test</h3>";
		echo "<p>Toplam kayıt: " . $ulkeler->num_rows() . "</p>";
		if ($ulkeler->num_rows() > 0) {
			echo "<table border='1'>";
			echo "<tr><th>ID</th><th>Ülke Adı</th><th>Ülke Kodu</th></tr>";
			foreach ($ulkeler->result() as $ulke) {
				echo "<tr><td>" . $ulke->id . "</td><td>" . $ulke->ulke_adi . "</td><td>" . $ulke->ulke_kodu . "</td></tr>";
			}
			echo "</table>";
		}
	}

	public function hata()

	{

		//logekle(54,7);

		$this->load->view('hata');

	}



	public function hata2($id)

	{

		if($id == 1){//gib modÃ¼l yok

			$data["hataBaslik"] = "ModÃ¼le sahip deÄŸilsiniz!";

			$data["hataMesaji"] = "GIB/IVD'ye gelen ilekaSoft Ã¼zerinde gÃ¶rÃ¼ntÃ¼leyebilmek, muhasebe iÅŸlemlerinize dahil edebilmek iÃ§in modÃ¼le sahip olmanÄ±z gerekmektedir! <hr>DetaylÄ± bilgi ve modÃ¼lÃ¼ talep etmek iÃ§in lÃ¼tfen arayÄ±nÄ±z: <a href='tel:0850 333 0 353' style='text-decoration: underline;color:#000!important;'>0850 333 0 353</a>";

		}else if($id == 2){//admin deÄŸil

			$data["hataBaslik"] = "Yetkiniz yok!";

			$data["hataMesaji"] = "Yetkiniz olmayan bir sayfaya girmek istediÄŸiniz iÃ§in bu sayfa sizlerle paylaÅŸÄ±lamÄ±yor.";

		}else if($id == 3){//demo sÃ¼resi bitti

			$data["hataBaslik"] = "Demo sÃ¼reniz sona erdi.";

			$data["hataMesaji"] = "Ãœcretsiz kullanÄ±m sÃ¼reniz sona ermiÅŸ, bilgileriniz bir sÃ¼re daha sunucularÄ±mÄ±zda saklanmaya devam edecektir, Ã¼yeliÄŸinizi devam ettirmek iÃ§in <a href='#'>satÄ±n alma</a> sayfasÄ±nÄ± ziyaret ediniz.";

		}

		$this->load->view('hata_d',$data);

	}



	public function logout()

	{

		logekle(54, 6);

		$this->session->sess_destroy();

		redirect(base_url('giris'));

	}



	public function tests2()

	{

		$ws = 'https://apiportal.turkbelge.com.tr/TurkBelgeWS.asmx?wsdl';



		$trace = true;

		$exceptions = false;



		$xml_array->request = '';



		$client = new SoapClient($ws, array('trace' => $trace, 'exceptions' => $exceptions));



		//var_dump($client->__getFunctions());die;



		$response = $client->GetGibUserListAdded($xml_array);

		print_r($response);

	}



	public function ubltest(){

		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Invoice/>');

		$xml->addAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');

		$xml->addAttribute('xmlns:qdt', 'urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2');

		$xml->addAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

		$xml->addAttribute('xmlns:udt', 'urn:un:unece:uncefact:data:draft:UnqualifiedDataTypesSchemaModule:2');

		$xml->addAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

		$xml->addAttribute('xmlns:ccts', 'urn:un:unece:uncefact:data:draft:UnqualifiedDataTypesSchemaModule:2');

		$xml->addAttribute('xmlns:udt', 'urn:oasis:names:specification:ubl:schema:xsd:CoreComponentParameters-2');

		$xml->addAttribute('xmlns:stat', 'urn:oasis:names:specification:ubl:schema:xsd:DocumentStatusCode-1.0');



		$xml->addChild('cbc:UBLVersionID', '2.1');



		echo $xml->asXml();

	}



	public function sessionKontrol(){

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;

		$sql="select * from kullanicilar where kullanici_id=$u_id";

		$exe=$this->db->query($sql)->row();

			if ($_SESSION["kullanici_oturumDurum"] == $exe->kullanici_oturumDurum)

				$sonuc = "0";

			else {

				$sonuc = "1";

			}

		$this->output->set_content_type('application/json')->set_output(json_encode($sonuc));

	}



	public function destek(){

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		$data["baslik"] = "Destek Sistemi";



		$urim = $this->uri->segment(1);



		$segment = 1;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if($sayfa){$pagem = ($page-1)*$limit;}

		else{$pagem = 0;}



		// Filtreler

		$durum_filtre = $this->input->get("durum");

		$kullanici_filtre = $this->input->get("kullanici");



		// grup_id = 1 olan kullanıcılar tüm talepleri görebilir, diğerleri sadece kendi taleplerini

		$this->load->helper('destek');

		$user_group_id = $control2->grup_id; $isAdminGroup = ($user_group_id == 1);

		

		// WHERE koÅŸullarÄ±nÄ± hazÄ±rla

		$where_conditions = array();

		

		if($isAdminGroup){

			// Admin kullanÄ±cÄ± - tÃ¼m talepleri gÃ¶ster (kullanÄ±cÄ± bilgileri ile birlikte)

			$base_query = "SELECT d.*, d.destek_unreadReplies, CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) as kullanici_adsoyad, k.kullanici_eposta

					  FROM destek d 

					  LEFT JOIN kullanicilar k ON d.destek_olusturan = k.kullanici_id";

			

			// Filtre koÅŸullarÄ±nÄ± ekle

			if (!empty($durum_filtre)) {

				$where_conditions[] = "d.destek_status = " . intval($durum_filtre);

			}

			if (!empty($kullanici_filtre)) {

				$where_conditions[] = "d.destek_olusturan = " . intval($kullanici_filtre);

			}

		} else {

			// Normal kullanÄ±cÄ± - sadece kendi taleplerini gÃ¶ster

			$base_query = "SELECT d.*, d.destek_unreadReplies, CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) as kullanici_adsoyad, k.kullanici_eposta,

					  (SELECT COUNT(*) FROM destek_yanit dy WHERE dy.dyanit_destekID = d.destek_id AND dy.dyanit_yanitlayan != $u_id AND (dy.dyanit_isRead = 0 OR dy.dyanit_isRead IS NULL)) as destek_unreadReplies 

					  FROM destek d 

					  LEFT JOIN kullanicilar k ON d.destek_olusturan = k.kullanici_id";

			

			$where_conditions[] = "d.destek_olusturan = $u_id";

			

			// Filtre koÅŸullarÄ±nÄ± ekle

			if (!empty($durum_filtre)) {

				$where_conditions[] = "d.destek_status = " . intval($durum_filtre);

			}

		}

		

		// WHERE koÅŸullarÄ±nÄ± birleÅŸtir

		$where_clause = "";

		if (!empty($where_conditions)) {

			$where_clause = " WHERE " . implode(" AND ", $where_conditions);

		}

		

		// Count sorgusu

		$countq = "SELECT COUNT(*) as total FROM destek d" . 

				  ($isAdminGroup ? "" : " WHERE d.destek_olusturan = $u_id") .

				  (!empty($durum_filtre) ? ($isAdminGroup ? " WHERE" : " AND") . " d.destek_status = " . intval($durum_filtre) : "") .

				  (!empty($kullanici_filtre) && $isAdminGroup ? (!empty($durum_filtre) ? " AND" : " WHERE") . " d.destek_olusturan = " . intval($kullanici_filtre) : "");

		

		$countexe = $this->db->query($countq)->row();

		$count = $countexe->total;

		

		// Ana sorgu

		$sorgu = $base_query . $where_clause . " ORDER BY d.destek_id DESC LIMIT $pagem,$limit";



		$data["count_of_list"] = $count;



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/$urim";

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



		$config['num_tag_open'] = '<span class="page-link"><li class="page-item">';

		$config['num_tag_close'] = '</li></span>';



		$config['cur_tag_open'] = '<span class="page-link"><li class="page-item active">';

		$config['cur_tag_close'] = '</li></span>';



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

		$data["destekler"] = $this->db->query($sorgu)->result();

		$data["isAdmin"] = $isAdminGroup;

		

		// Filtre verileri

		$data["durum_filtre"] = $durum_filtre;

		$data["kullanici_filtre"] = $kullanici_filtre;

		

		// Destek durumlarÄ±

		$data["destek_durumlari"] = array(

			1 => 'AÃ§Ä±k',

			2 => 'Beklemede', 

			3 => 'KapalÄ±'

		);

		

		// KullanÄ±cÄ± listesi (sadece admin iÃ§in)

		if ($isAdminGroup) {

			$data["kullanicilar"] = $this->db->query("

				SELECT DISTINCT k.kullanici_id, CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) as kullanici_adsoyad

				FROM kullanicilar k

				INNER JOIN destek d ON k.kullanici_id = d.destek_olusturan

				ORDER BY k.kullanici_ad, k.kullanici_soyad

			")->result();

		}



		$this->load->view("destek",$data);

	}



	public function yeniTicket(){
		// Upload library'yi yükle
		$this->load->library('upload');
		
		// Session kontrolü
		$control2 = session("r", "login_info");
		if(!$control2 || !isset($control2->kullanici_id)) {
			// Session yoksa login sayfasına yönlendir
			redirect(base_url("check"));
			return;
		}

		$u_id = $control2->kullanici_id;

		// POST veri kontrolü - sadece form gönderilmişse işlem yap
		if(!$_POST || empty(postval("destek_title"))) {
			// POST verisi yoksa destek sayfasına yönlendir
			redirect(base_url('destek'));
			return;
		}

		$anaHesap = anaHesapBilgisi();

		date_default_timezone_set('Europe/Istanbul');
		$tarih = (new DateTime('now'))->format('Y.m.d H:i:s');

		$data["destek_title"] = postval("destek_title");
		$data["destek_department"] = postval("destek_department");
		$data["destek_priority"] = postval("destek_priority");

		$data["destek_status"] = 1;

		$data["destek_isRead"] = 1;

		$data["destek_date"] = $tarih;

		$data["destek_ip"] = ipadresial();

		$data["destek_olusturan"] = $u_id;

		$data["destek_olusturanAnaHesap"] = $anaHesap;



		$this->vt->insert("destek", $data);



		$destek_id = $this->db->insert_id();



		$this->load->library('upload');

		$config['upload_path'] = './assets/bulut/tickets/';

		$config['encrypt_name'] = TRUE;

		$config['allowed_types']="jpg|jpeg|png|xml|pdf";

		$config['max_size']="1024";

		$config['remove_space']=true;



		$this->upload->initialize($config);



		// Ã‡oklu dosya yÃ¼kleme

		$uploaded_files = array();

		$upload_errors = array();

		

		if(isset($_FILES['destek_files']) && !empty($_FILES['destek_files']['name'][0])){

			$file_count = count($_FILES['destek_files']['name']);

			

			for($i = 0; $i < $file_count; $i++){

				if(!empty($_FILES['destek_files']['name'][$i])){

					$_FILES['single_file']['name'] = $_FILES['destek_files']['name'][$i];

					$_FILES['single_file']['type'] = $_FILES['destek_files']['type'][$i];

					$_FILES['single_file']['tmp_name'] = $_FILES['destek_files']['tmp_name'][$i];

					$_FILES['single_file']['error'] = $_FILES['destek_files']['error'][$i];

					$_FILES['single_file']['size'] = $_FILES['destek_files']['size'][$i];

					

					if ($this->upload->do_upload('single_file')){

						$datam = $this->upload->data();

						$uploaded_files[] = $datam['file_name'];

					} else {

						$upload_errors[] = $this->upload->display_errors();

					}

				}

			}

			

			// EÄŸer hata varsa ve hiÃ§ dosya yÃ¼klenmediyse talebi sil

			if(!empty($upload_errors) && empty($uploaded_files)){

				$this->session->set_flashdata('resim_hata','OK');

				$this->vt->del("destek","destek_id",$destek_id);

				redirect($_SERVER['HTTP_REFERER']);

			}

		}

		

		// Ana mesajÄ± destek_yanit tablosuna ekle

		$data_d["dyanit_destekID"] = $destek_id;

		$data_d["dyanit_yanitlayan"] = $u_id;

		$data_d["dyanit_mesaj"] = postval("destek_msg");

		$data_d["dyanit_resim"] = !empty($uploaded_files) ? $uploaded_files[0] : NULL; // Ä°lk dosyayÄ± ana resim olarak kaydet

		$data_d["dyanit_tarih"] = $tarih;

		$data_d["dyanit_isAdmin"] = 0;

		$data_d["dyanit_yanitlayanAnaHesap"] = $anaHesap;



		$this->vt->insert("destek_yanit", $data_d);

		

		// EÄŸer birden fazla dosya varsa, diÄŸerlerini ayrÄ± yanÄ±tlar olarak kaydet

		if(count($uploaded_files) > 1){

			for($i = 1; $i < count($uploaded_files); $i++){

				$data_extra["dyanit_destekID"] = $destek_id;

				$data_extra["dyanit_yanitlayan"] = $u_id;

				$data_extra["dyanit_mesaj"] = ""; // Ek dosya iÃ§in mesaj boÅŸ

				$data_extra["dyanit_resim"] = $uploaded_files[$i];

				$data_extra["dyanit_tarih"] = $tarih;

				$data_extra["dyanit_isAdmin"] = 0;

				$data_extra["dyanit_yanitlayanAnaHesap"] = $anaHesap;

				

				$this->vt->insert("destek_yanit", $data_extra);

			}

		}

		$mail_config = Array(
			'protocol' => 'smtp',
			'smtp_crypto' => 'tls',
			'smtp_host' => 'smtp.gmail.com',
			'smtp_port' => 587,
			'smtp_user' => 'bilgi@ileka.com.tr',
			'smtp_pass' => 'qcel gfig rgao qefo',
			'charset'   => 'utf-8',
			'mailtype'  => 'html',
			'wordwrap' => FALSE,
			'newline' => "\r\n",
			'crlf' => "\r\n",
			'dsn' => FALSE,
			'multipart' => 'mixed'
		);



		// Talep bilgilerini al

		$son_talep = $this->db->query("SELECT * FROM destek WHERE destek_olusturan = $u_id ORDER BY destek_id DESC LIMIT 1")->row();

		$kullanici = session("r", "login_info");

		$mesaj_icerigi = postval("destek_msg"); // GerÃ§ek mesaj iÃ§eriÄŸini al

		

		// Mail içeriğini zenginleştir

		$mail_baslik = 'İlekaSoft CRM - Yeni Destek Talebi #' . ($son_talep ? $son_talep->destek_id : 'N/A');

		$mail_mesaj = '
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <style>

        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }

        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }

        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }

        .content { padding: 30px; }

        .info-box { background-color: #f8f9fa; border-left: 4px solid #007bff; padding: 15px; margin: 15px 0; }

        .btn { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 15px 0; }

        .footer { background-color: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px; }

    </style>

</head>

<body>

    <div class="container">

        <div class="header">

            <h2>ğŸ« Yeni Destek Talebi</h2>

        </div>

        <div class="content">

            <p>Merhaba,</p>

            <p>Sistemde yeni bir destek talebi oluÅŸturuldu. Detaylar aÅŸaÄŸÄ±daki gibidir:</p>

            

            <div class="info-box">

                <h4>ğŸ“‹ Talep Bilgileri</h4>

                <p><strong>Talep ID:</strong> #' . ($son_talep ? $son_talep->destek_id : 'N/A') . '</p>

                <p><strong>KullanÄ±cÄ±:</strong> ' . ($kullanici->kullanici_ad ?? 'Bilinmiyor') . ' ' . ($kullanici->kullanici_soyad ?? '') . '</p>

                <p><strong>E-posta:</strong> ' . ($kullanici->kullanici_eposta ?? 'Bilinmiyor') . '</p>

                <p><strong>BaÅŸlÄ±k:</strong> ' . ($son_talep ? htmlspecialchars($son_talep->destek_title) : 'Bilinmiyor') . '</p>

                <p><strong>OluÅŸturma Tarihi:</strong> ' . date('d.m.Y H:i:s') . '</p>

            </div>

            

            <div class="info-box">

                <h4>ğŸ’¬ Mesaj Ä°Ã§eriÄŸi</h4>

                <p>' . (!empty($mesaj_icerigi) ? nl2br(htmlspecialchars(substr($mesaj_icerigi, 0, 300))) : 'Mesaj iÃ§eriÄŸi bulunamadÄ±') . 

                (!empty($mesaj_icerigi) && strlen($mesaj_icerigi) > 300 ? '...' : '') . '</p>

            </div>

            

            <p>LÃ¼tfen en kÄ±sa sÃ¼rede talebi inceleyin ve gerekli iÅŸlemleri yapÄ±n.</p>

            

            <a href="https://crm.ilekasoft.com/destek/detay/' . ($son_talep ? $son_talep->destek_id : '') . '" class="btn">ğŸ”— Talebi GÃ¶rÃ¼ntÃ¼le</a>

        </div>

        <div class="footer">

            <p>Bu e-posta Ä°lekaSoft CRM sistemi tarafÄ±ndan otomatik olarak gÃ¶nderilmiÅŸtir.</p>

            <p>' . date('Y') . ' Â© Ä°lekaSoft - TÃ¼m haklarÄ± saklÄ±dÄ±r.</p>

        </div>

    </div>

</body>

</html>';



		$this->load->library('email', $mail_config);
		$this->email->clear(TRUE);
		$this->email->set_newline("\r\n");
		$this->email->set_crlf("\r\n");
		$this->email->set_header('Content-Type', 'text/html; charset=UTF-8');
		$this->email->set_header('Content-Transfer-Encoding', '8bit');
		$this->email->to("talep@turkbelge.com.tr");
		$this->email->from("bilgi@ileka.com.tr","İleka CRM Sistemi");
		$this->email->subject($mail_baslik);
		$this->email->message($mail_mesaj);

		// E-posta göndermeyi dene ama hata olursa kullanıcıyı bilgilendir
		try {
			// Debug: E-posta gönderme öncesi log
			file_put_contents('debug_email.log', "E-posta gönderiliyor: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND | LOCK_EX);
			file_put_contents('debug_email.log', "Alıcı: talep@turkbelge.com.tr\n", FILE_APPEND | LOCK_EX);
			file_put_contents('debug_email.log', "Konu: " . $mail_baslik . "\n", FILE_APPEND | LOCK_EX);
			
			if (!$this->email->send()) {
				// E-posta gönderilemedi ama talep oluşturuldu
				$debug_info = $this->email->print_debugger();
				file_put_contents('debug_email.log', "E-posta hatası:\n" . $debug_info . "\n", FILE_APPEND | LOCK_EX);
				$this->session->set_flashdata('email_uyari', 'Destek talebiniz başarıyla oluşturuldu ancak bildirim e-postası gönderilemedi.');
			} else {
				// E-posta başarıyla gönderildi
				file_put_contents('debug_email.log', "E-posta başarıyla gönderildi!\n", FILE_APPEND | LOCK_EX);
				$this->session->set_flashdata('destek_ok', 'OK');
			}
		} catch (Exception $e) {
			// E-posta hatası - talep yine de oluşturuldu
			file_put_contents('debug_email.log', "E-posta exception: " . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
			$this->session->set_flashdata('email_uyari', 'Destek talebiniz başarıyla oluşturuldu ancak bildirim e-postası gönderilemedi.');
		}

		// Her durumda success flash message'ı set et
		$this->session->set_flashdata('destek_ok','OK');
		
		// Destek sayfasına yönlendir
		redirect(base_url('destek'));

		redirect($_SERVER['HTTP_REFERER']);

	}



	public function destekdetay($id){
		// Emergency debug - sayfanın başında çıktı
		echo "<!-- DEBUG: Controller destekdetay çalıştı, ID: $id -->";
		
		// Debug: Controller başlangıcı
		file_put_contents('debug_controller.log', "Controller başlatıldı: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND | LOCK_EX);

		$control2 = session("r", "login_info");
		
		// Session kontrolü
		if(!$control2 || !isset($control2->kullanici_id)) {
			file_put_contents('debug_controller.log', "Session hatası!\n", FILE_APPEND | LOCK_EX);
			echo "<div class='alert alert-danger'>Giriş yapmanız gerekiyor!</div>";
			echo "<a href='" . base_url('check') . "'>Giriş Yap</a>";
			return;
		}

		$u_id = $control2->kullanici_id;
		file_put_contents('debug_controller.log', "User ID: $u_id\n", FILE_APPEND | LOCK_EX);



		$data["baslik"] = "Destek Sistemi - Detay";

		$data["talep"] = $this->db->query("SELECT * FROM destek WHERE destek_id = $id")->row();

		$data["yanitlar"] = $this->db->query("SELECT * FROM destek_yanit WHERE dyanit_destekID = $id")->result();

		// Debug log
		$yanit_sayisi = is_array($data["yanitlar"]) ? count($data["yanitlar"]) : 0;
		error_log("DEBUG: Destek ID: $id, Talep bulundu: " . ($data["talep"] ? 'Evet' : 'Hayır') . ", Yanıt sayısı: $yanit_sayisi");
		
		// Debug dosyasına yaz
		$debug_text = "DEBUG " . date('Y-m-d H:i:s') . ": ID=$id, Talep=" . ($data["talep"] ? 'VAR' : 'YOK') . ", Yanıt=$yanit_sayisi\n";
		file_put_contents('debug_controller.log', $debug_text, FILE_APPEND | LOCK_EX);
		
		// Talep bulunamadıysa hata sayfasına yönlendir
		if(!$data["talep"]) {
			redirect("home/hata2/3");
			return;
		}

		$isRead = $data["talep"]->destek_isRead;

		$kim = $data["talep"]->destek_olusturan;



		// grup_id = 1 olan kullanıcılar tüm talepleri görebilir, diğerleri sadece kendi taleplerini

		$this->load->helper('destek');

		$user_group_id = $control2->grup_id; $isAdminGroup = ($user_group_id == 1);

		$data["isAdmin"] = $isAdminGroup;



		if($kim == $u_id || $isAdminGroup){

			if($isRead == 0){

				$data_a["destek_isRead"] = 1;



				$this->vt->update('destek', array('destek_id'=>$id), $data_a);

			}



			$this->load->view("destek-detay",$data);

		}else{

			redirect("home/hata2/2");

		}

	}



    public function dovizKur(){

        $this->load->view("doviz-kur");

    }



	public function dyanit_gonder(){

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		$anaHesap = anaHesapBilgisi();



		date_default_timezone_set('Europe/Istanbul');

		$tarih = (new DateTime('now'))->format('Y.m.d H:i:s');



		$destek_id = postval("dyanit_destekID");

		

		// Talebin sahibini kontrol et

		$talep = $this->db->query("SELECT destek_olusturan, destek_title FROM destek WHERE destek_id = $destek_id")->row();

		

		// grup_id = 1 olan kullanıcılar tüm taleplere yanıt gönderebilir, diğerleri sadece kendi taleplerini

		$this->load->helper('destek');

		$this->load->helper('bildirim');

		$user_group_id = $control2->grup_id; $isAdminGroup = ($user_group_id == 1);

		

		if($talep && ($talep->destek_olusturan == $u_id || $isAdminGroup)){

			$data["dyanit_destekID"] = $destek_id;

			$data["dyanit_mesaj"] = postval("dyanit_mesaj");

			$data["dyanit_yanitlayan"] = $u_id;

			$data["dyanit_yanitlayanAnaHesap"] = $anaHesap;

			$data["dyanit_tarih"] = $tarih;

			$data["dyanit_isAdmin"] = $isAdminGroup ? 1 : 0;

			$data["dyanit_isRead"] = 0; // Yeni yanÄ±t okunmamÄ±ÅŸ olarak iÅŸaretle



			$this->vt->insert("destek_yanit", $data);



			// Destek tablosunu gÃ¼ncelle

			$data_u["destek_lastUpdateDate"] = $tarih;

			$data_u["destek_lastReplyDate"] = $tarih;

			$data_u["destek_lastReplyBy"] = $u_id;

			

			// Cevap verildiÄŸinde durumu otomatik olarak "KapalÄ±" yap

			$data_u["destek_status"] = 3; // 3 = KapalÄ±



			$this->vt->update('destek', array('destek_id'=>$destek_id), $data_u);



			// OkunmamÄ±ÅŸ yanÄ±t sayÄ±sÄ±nÄ± gÃ¼ncelle

			destek_okunmamis_yanit_sayisi_guncelle($destek_id);



			// Bildirim gÃ¶nder (yanÄ±tlayan kiÅŸi talep sahibi deÄŸilse)

			if($talep->destek_olusturan != $u_id) {

				$yanitlayan_bilgi = $this->db->query("SELECT CONCAT(kullanici_ad, ' ', kullanici_soyad) as kullanici_adsoyad FROM kullanicilar WHERE kullanici_id = $u_id")->row();

				$yanitlayan_ad = $yanitlayan_bilgi ? $yanitlayan_bilgi->kullanici_adsoyad : 'Destek Ekibi';

				

				bildirim_gonder(

					$talep->destek_olusturan,

					'destek_yanit',

					'Destek Talebinize YanÄ±t Geldi',

					$yanitlayan_ad . ' tarafÄ±ndan "' . $talep->destek_title . '" konulu destek talebinize yanÄ±t verildi.',

					base_url("destek/detay/$destek_id"),

					$destek_id

				);



				// E-posta bildirimi gÃ¶nder

				destek_eposta_bildirim_gonder($destek_id, 'yeni_yanit', $yanitlayan_ad . ' tarafÄ±ndan yanÄ±tlandÄ±.');

			}



			// Admin bildirimi (talep sahibi yanÄ±t verdiyse)

			if($talep->destek_olusturan == $u_id) {

				$mail_config = Array(
					'protocol' => 'smtp',
					'smtp_crypto' => 'tls',
					'smtp_host' => 'smtp.gmail.com',
					'smtp_port' => 587,
					'smtp_user' => 'bilgi@ileka.com.tr',
					'smtp_pass' => 'qcel gfig rgao qefo',
					'charset'   => 'utf-8',
					'mailtype'  => 'html',
					'wordwrap' => FALSE,
					'newline' => "\r\n",
					'crlf' => "\r\n",
					'dsn' => FALSE,
					'multipart' => 'mixed'
				);



				// YanÄ±tlayan kullanÄ±cÄ± bilgilerini al

				$yanitlayan_bilgi = $this->db->query("SELECT CONCAT(kullanici_ad, ' ', kullanici_soyad) as kullanici_adsoyad, kullanici_eposta FROM kullanicilar WHERE kullanici_id = $u_id")->row();

				$yanit_mesaji = postval("dyanit_mesaj");

				

				// Mail içeriğini zenginleştir

				$mail_baslik = '[TALEP] İlekaSoft CRM - Destek Talebine Yanıt #' . $destek_id;

				$admin_mail_mesaj = '
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <style>

        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }

        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }

        .header { background-color: #17a2b8; color: white; padding: 20px; text-align: center; }

        .content { padding: 30px; }

        .info-box { background-color: #f8f9fa; border-left: 4px solid #17a2b8; padding: 15px; margin: 15px 0; }

        .response-box { background-color: #e8f4fd; border-left: 4px solid #007bff; padding: 15px; margin: 15px 0; }

        .btn { display: inline-block; padding: 12px 24px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 4px; margin: 15px 0; }

        .footer { background-color: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px; }

    </style>

</head>

<body>

    <div class="container">

        <div class="header">

            <h2>ğŸ’¬ Destek Talebine Yeni YanÄ±t</h2>

        </div>

        <div class="content">

            <p>Merhaba,</p>

            <p>Bir destek talebine kullanÄ±cÄ± tarafÄ±ndan yanÄ±t verildi. LÃ¼tfen inceleyin:</p>

            

            <div class="info-box">

                <h4>ğŸ“‹ Talep Bilgileri</h4>

                <p><strong>Talep ID:</strong> #' . $destek_id . '</p>

                <p><strong>Talep BaÅŸlÄ±ÄŸÄ±:</strong> ' . htmlspecialchars($talep->destek_title) . '</p>';

                

                if($yanitlayan_bilgi) {

                    $admin_mail_mesaj .= '

                    <p><strong>YanÄ±tlayan KullanÄ±cÄ±:</strong> ' . htmlspecialchars($yanitlayan_bilgi->kullanici_adsoyad) . '</p>

                    <p><strong>E-posta:</strong> ' . htmlspecialchars($yanitlayan_bilgi->kullanici_eposta) . '</p>';

                }

                

                $admin_mail_mesaj .= '

                <p><strong>YanÄ±t Tarihi:</strong> ' . date('d.m.Y H:i:s') . '</p>

            </div>

            

            <div class="response-box">

                <h4>ğŸ’¬ KullanÄ±cÄ± YanÄ±tÄ±</h4>

                <p>' . (!empty($yanit_mesaji) ? nl2br(htmlspecialchars(substr($yanit_mesaji, 0, 300))) : 'YanÄ±t iÃ§eriÄŸi bulunamadÄ±') . 

                (!empty($yanit_mesaji) && strlen($yanit_mesaji) > 300 ? '...' : '') . '</p>

            </div>

            

            <p>Bu yanÄ±t kullanÄ±cÄ±nÄ±n talebine karÅŸÄ± verdiÄŸi geri bildirimdir. LÃ¼tfen gerekli durumlarda tekrar yanÄ±t verin.</p>

            

            <a href="https://crm.ilekasoft.com/destek/detay/' . $destek_id . '" class="btn">ğŸ”— Talebi GÃ¶rÃ¼ntÃ¼le ve YanÄ±tla</a>

        </div>

        <div class="footer">

            <p>Bu e-posta Ä°lekaSoft CRM sistemi tarafÄ±ndan otomatik olarak gÃ¶nderilmiÅŸtir.</p>

            <p>' . date('Y') . ' Â© Ä°lekaSoft - TÃ¼m haklarÄ± saklÄ±dÄ±r.</p>

        </div>

    </div>

</body>

</html>';



				$this->load->library('email', $mail_config);
				$this->email->clear(TRUE);
				$this->email->set_newline("\r\n");
				$this->email->set_crlf("\r\n");
				$this->email->set_header('Content-Type', 'text/html; charset=UTF-8');
				$this->email->set_header('Content-Transfer-Encoding', '8bit');
				$this->email->to("talep@turkbelge.com.tr");
				$this->email->from("bilgi@ileka.com.tr","İleka CRM");
				$this->email->subject($mail_baslik);
				$this->email->message($admin_mail_mesaj);

				if (!$this->email->send()){

					echo $this->email->print_debugger();

				}

			}



			$this->session->set_flashdata('destekyanit_ok','OK');

			redirect('destek'); // Destek sayfasÄ±na geri dÃ¶n

		} else {

			// Yetkisiz eriÅŸim

			redirect("home/hata2/2");

		}

	}



	public function destek_durum_degistir($destek_id, $yeni_durum){

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		// Admin yetkisi kontrolÃ¼

		$this->load->helper('destek');

		$this->load->helper('bildirim');

		$user_group_id = $control2->grup_id; $isAdminGroup = ($user_group_id == 1);

		

		if(!$isAdminGroup){

			redirect("home/hata2/2");

		}



		// GeÃ§erli durumlar: 1=AÃ§Ä±k, 2=Ä°ÅŸlemde, 3=KapalÄ±, 4=Cevap bekleniyor

		$gecerli_durumlar = array(1, 2, 3, 4);

		if(!in_array($yeni_durum, $gecerli_durumlar)){

			$this->session->set_flashdata('destek_durum_hata','GeÃ§ersiz durum!');

			redirect($_SERVER['HTTP_REFERER']);

		}



		// Destek talebi var mÄ± kontrol et

		$talep = $this->db->query("SELECT destek_olusturan, destek_title FROM destek WHERE destek_id = $destek_id")->row();

		if(!$talep){

			$this->session->set_flashdata('destek_durum_hata','Destek talebi bulunamadÄ±!');

			redirect($_SERVER['HTTP_REFERER']);

		}



		// Durumu gÃ¼ncelle

		date_default_timezone_set('Europe/Istanbul');

		$tarih = (new DateTime('now'))->format('Y.m.d H:i:s');

		

		$data["destek_status"] = $yeni_durum;

		$data["destek_lastUpdateDate"] = $tarih;



		$this->vt->update('destek', array('destek_id'=>$destek_id), $data);



		// Durum metinlerini belirle

		$durum_metinleri = array(

			1 => 'AÃ§Ä±k',

			2 => 'Ä°ÅŸlemde', 

			3 => 'KapalÄ±',

			4 => 'Cevap bekleniyor'

		);

		

		$yeni_durum_text = $durum_metinleri[$yeni_durum];



		// KullanÄ±cÄ±ya bildirim gÃ¶nder

		bildirim_gonder(

			$talep->destek_olusturan,

			'destek_durum',

			'Destek Talebinizin Durumu DeÄŸiÅŸti',

			'"' . $talep->destek_title . '" konulu destek talebinizin durumu "' . $yeni_durum_text . '" olarak deÄŸiÅŸtirildi.',

			base_url("destek/detay/$destek_id"),

			$destek_id

		);



		// E-posta bildirimi gÃ¶nder

		destek_eposta_bildirim_gonder($destek_id, 'durum_degisti', $yeni_durum_text);



		$this->session->set_flashdata('destek_durum_ok', 'Destek durumu "'.$yeni_durum_text.'" olarak gÃ¼ncellendi.');

		redirect($_SERVER['HTTP_REFERER']);

	}



	// Bildirim sistemi iÃ§in API endpoint'leri

	public function bildirim_listesi(){

		$control2 = session("r", "login_info");

		if (!$control2) {

			echo json_encode(array('success' => false, 'message' => 'Oturum bulunamadÄ±'));

			return;

		}



		$u_id = $control2->kullanici_id;

		$this->load->helper('bildirim');

		

		$limit = $this->input->get('limit') ? (int)$this->input->get('limit') : 10;

		$bildirimler = okunmamis_bildirimler($u_id, $limit);

		

		$response = array(

			'success' => true,

			'bildirimler' => $bildirimler,

			'toplam' => okunmamis_bildirim_sayisi($u_id)

		);

		

		echo json_encode($response);

	}



	public function bildirim_okundu(){

		$control2 = session("r", "login_info");

		if (!$control2) {

			echo json_encode(array('success' => false, 'message' => 'Oturum bulunamadÄ±'));

			return;

		}



		$u_id = $control2->kullanici_id;

		$bildirim_id = $this->input->post('bildirim_id');

		

		if($bildirim_id){

			$this->load->helper('bildirim');

			$result = bildirim_okundu_isaretle($bildirim_id, $u_id);

			

			if($result){

				echo json_encode(array('success' => true, 'message' => 'Bildirim okundu olarak iÅŸaretlendi'));

			} else {

				echo json_encode(array('success' => false, 'message' => 'Bildirim gÃ¼ncellenemedi'));

			}

		} else {

			echo json_encode(array('success' => false, 'message' => 'Bildirim ID bulunamadÄ±'));

		}

	}



	public function yanit_okundu(){

		$control2 = session("r", "login_info");

		if (!$control2) {

			echo json_encode(array('success' => false, 'message' => 'Oturum bulunamadÄ±'));

			return;

		}



		$u_id = $control2->kullanici_id;

		$yanit_id = $this->input->post('yanit_id');

		

		if($yanit_id){

			$this->load->helper('bildirim');

			$result = destek_yanit_okundu_isaretle($yanit_id, $u_id);

			

			if($result){

				echo json_encode(array('success' => true, 'message' => 'YanÄ±t okundu olarak iÅŸaretlendi'));

			} else {

				echo json_encode(array('success' => false, 'message' => 'YanÄ±t gÃ¼ncellenemedi'));

			}

		} else {

			echo json_encode(array('success' => false, 'message' => 'YanÄ±t ID bulunamadÄ±'));

		}

	}



	public function bildirim_temizle(){

		$control2 = session("r", "login_info");

		if (!$control2) {

			echo json_encode(array('success' => false, 'message' => 'Oturum bulunamadÄ±'));

			return;

		}



		$u_id = $control2->kullanici_id;

		

		// KullanÄ±cÄ±nÄ±n tÃ¼m bildirimlerini okundu olarak iÅŸaretle

		$data = array('bildirim_okundu' => 1);

		$where = array('bildirim_kullanici_id' => $u_id, 'bildirim_okundu' => 0);

		

		$result = $this->vt->update('kullanici_bildirim', $where, $data);

		

		if($result){

		echo json_encode(array('success' => true, 'message' => 'TÃ¼m bildirimler temizlendi'));

	} else {

		echo json_encode(array('success' => false, 'message' => 'Bildirimler temizlenemedi'));

	}

}

	/**
	 * Changelog verilerini getir (AJAX endpoint) - Veritabanından
	 */
	public function getChangelog()
	{
		// JSON header ayarla
		header('Content-Type: application/json; charset=utf-8');
		
		// Session kontrolü
		$control = session("r", "login_info");
		if (!$control || !isset($control->kullanici_id)) {
			echo json_encode(['success' => false, 'message' => 'Oturum süresi dolmuş']);
			exit();
		}

		try {
			// Veritabanından changelog kayıtlarını çek (aktif olanlar, yeni->eski sıralı)
			$this->db->select('changelog_version as version, changelog_date as date, changelog_type as type, changelog_module as module, changelog_description as description, changelog_details as details, changelog_file as file, changelog_author as author');
			$this->db->from('changelog');
			$this->db->where('changelog_durum', 1);
			$this->db->order_by('changelog_date', 'DESC');
			$this->db->order_by('changelog_id', 'DESC');
			
			$query = $this->db->get();
			
			if (!$query) {
				echo json_encode(['success' => false, 'message' => 'Veritabanı sorgusu başarısız']);
				exit();
			}

			$changelog_records = $query->result_array();

			if (empty($changelog_records)) {
				echo json_encode(['success' => false, 'message' => 'Henüz changelog kaydı bulunmuyor']);
				exit();
			}

			// Versiyonları grupla (aynı versiyon numarasına sahip değişiklikleri birleştir)
			$versions = [];
			foreach ($changelog_records as $record) {
				$version = $record['version'];
				
				if (!isset($versions[$version])) {
					$versions[$version] = [
						'version' => $version,
						'date' => $record['date'],
						'changes' => []
					];
				}
				
				$versions[$version]['changes'][] = [
					'type' => $record['type'],
					'module' => $record['module'],
					'description' => $record['description'],
					'details' => $record['details'],
					'file' => $record['file'],
					'author' => $record['author']
				];
			}

			// Array'i yeniden indeksle
			$versions = array_values($versions);

			// Response formatı
			$changelog_data = [
				'app_version' => isset($versions[0]['version']) ? $versions[0]['version'] : '1.0.0',
				'last_updated' => isset($versions[0]['date']) ? $versions[0]['date'] : date('Y-m-d'),
				'versions' => $versions
			];

			// Response döndür
			echo json_encode([
				'success' => true,
				'data' => $changelog_data,
				'record_count' => count($changelog_records)
			]);
			exit();

		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => 'Changelog verileri yüklenirken hata oluştu: ' . $e->getMessage()]);
			exit();
		}
	}

	public function testMethod() {
		echo "Test method çalışıyor!<br>";
		echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
		echo "Bu sayfa çalışıyorsa CodeIgniter routing düzgün çalışıyor.";
	}

	/**
	 * Default dashboard göster (kullanıcı grubunda dashboard ayarı yoksa)
	 */
	public function defaultDashboard()
	{
		$data["baslik"] = "Default Dashboard";
		$this->load->view('default-dashboard', $data);
	}

}