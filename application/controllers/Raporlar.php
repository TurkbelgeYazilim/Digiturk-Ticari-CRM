<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Raporlar extends CI_Controller {



  public function __construct(){

		parent::__construct();

		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

		$this->load->model('vt');

		

		$control = session("r", "login");





	  if(gibYetki()==1)

		  redirect("home/hata");



	  if(!$control){

			redirect("check");

		}

	  //sessionKontrolHelper();

	}



	public function index(){

		$data["baslik"] = "Raporlar";

		$this->load->view("raporlar/raporlar",$data);

	}



	public function raporlar(){

		$data["baslik"] = "Raporlar";

		$this->load->view("raporlar/raporlar",$data);

	}



	public function stokRaporlari(){

		$data["baslik"] = "Raporlar / Stok Raporları";

		$anaHesap = anaHesapBilgisi();



		$stokKodu = $this->input->get('stokKodu');

		$stokAdi = $this->input->get('stokAdi');

		$stokGrubu = $this->input->get('stokGrubu');



		$tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



		$urim = $this->uri->segment(2);



		$segment = 2;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if($sayfa){$pagem = ($page-1)*$limit;}

		else{$pagem = 0;/*logekle(6,1);*/}





		if((isset($stokKodu) && !empty($stokKodu)) || (isset($stokAdi) && !empty($stokAdi)) || (isset($stokGrubu) && !empty($stokGrubu)) || (isset($tarihGet) && !empty($tarihGet))){



			if(!empty($stokGrubu)){$sorgu1 = "AND stok_stokGrupKoduID = '$stokGrubu'";}

			else{$sorgu1 = "";}



			if(!empty($tarihGet)){$sorgu2 = "AND stok_olusturmaTarihi BETWEEN '$tarih1' AND '$tarih2'";}

			else{$sorgu2 = "";}



			$countq = "SELECT COUNT(*) as total FROM stok WHERE stok_olusturanAnaHesap = '$anaHesap' AND stok_kodu LIKE '%$stokKodu%' AND stok_ad LIKE '%$stokAdi%' ".$sorgu1." ".$sorgu2." ";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;



			$sorgu = "SELECT * FROM stok WHERE stok_olusturanAnaHesap = '$anaHesap' AND stok_kodu LIKE '%$stokKodu%' AND stok_ad LIKE '%$stokAdi%' ".$sorgu1." ".$sorgu2." ORDER BY stok_id DESC LIMIT $pagem,$limit";

		}else{

			$countq = "SELECT COUNT(*) as total FROM stok WHERE stok_olusturanAnaHesap = '$anaHesap'";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;

			$sorgu = "SELECT * FROM stok WHERE stok_olusturanAnaHesap = '$anaHesap' ORDER BY stok_id DESC LIMIT $pagem,$limit";

		}

		$data["count_of_list"] = $count;



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/raporlar/$urim";

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

		$data["stok"] = $this->db->query($sorgu)->result();



		$this->load->view('raporlar/stok-raporlari',$data);

	}



	public function kasaRaporlari(){

		$data["baslik"] = "Raporlar / Kasa Raporları";

		$anaHesap = anaHesapBilgisi();



        $kasaKodu = $this->input->get('kasaKodu');

        $kasaAdi = $this->input->get('kasaAdi');

        //$cariAdi = $this->input->get('cariAdi');



        $urim = $this->uri->segment(2);

        

        $segment = 2;

        $sayfa = $this->input->get("sayfa");



        $page = $sayfa ? $sayfa : 0;

        $limit = 20;



        if($sayfa){$pagem = ($page-1)*$limit;}

        else{$pagem = 0;}



        if((isset($kasaKodu) && !empty($kasaKodu)) || (isset($kasaAdi) && !empty($kasaAdi))){



            /*if(!empty($cariGrubu)){$sorgu1 = "AND cari_cariGrupKoduID = '$cariGrubu'";}

            else{$sorgu1 = "";}*/



            $countq = "SELECT COUNT(*) as total FROM kasa WHERE kasa_olusturanAnaHesap = '$anaHesap' AND kasa_kodu LIKE '%$kasaKodu%' AND kasa_adi LIKE '%$kasaAdi%'";

            $countexe = $this->db->query($countq)->row();

            $count = $countexe->total;



            $sorgu = "SELECT * FROM kasa WHERE kasa_olusturanAnaHesap = '$anaHesap' AND kasa_kodu LIKE '%$kasaKodu%' AND kasa_adi LIKE '%$kasaAdi%' ORDER BY kasa_id DESC LIMIT $pagem,$limit";

        }else{

            $countq = "SELECT COUNT(*) as total FROM kasa WHERE kasa_olusturanAnaHesap = '$anaHesap'";

            $countexe = $this->db->query($countq)->row();

            $count = $countexe->total;

            $sorgu = "SELECT * FROM kasa WHERE kasa_olusturanAnaHesap = '$anaHesap' ORDER BY kasa_id DESC LIMIT $pagem,$limit";

        }

        $data["count_of_list"] = $count;



        $this->load->library("pagination");



        $config = array();

        $config["base_url"] = base_url() . "/raporlar/$urim";

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

        $data["kasa"] = $this->db->query($sorgu)->result();



        $this->load->view("raporlar/kasa-raporlari", $data);

	}



	public function cariStokHareketRaporlari(){

		$data["baslik"] = "Raporlar / Cari Stok Hareket Raporları";

		$anaHesap = anaHesapBilgisi();



		$cariBilgisi = $this->input->get('cari');



	    $tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



	    $urim = $this->uri->segment(2);

	    

	    $segment = 2;

	    $sayfa = $this->input->get("sayfa");



	    $page = $sayfa ? $sayfa : 0;

	    $limit = 20;



	    if($sayfa){$pagem = ($page-1)*$limit;}

	    else{$pagem = 0;}



	    if((isset($tarihGet) && !empty($tarihGet))){



	        if(!empty($cariBilgisi) && $cariBilgisi != "tumu"){

	    		$kasaQuery = "AND sh_cariID = '$cariBilgisi'";

	    	}



	    	if(!empty($tarihGet)){$sorgu1 = "AND sh_tarih BETWEEN '$tarih1' AND '$tarih2'";}

			else{$sorgu1 = "";}



	        $countq = "SELECT COUNT(*) as total FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' ".$kasaQuery."  ".$sorgu1." ";

	        $countexe = $this->db->query($countq)->row();

	        $count = $countexe->total;



	        $sorgu = "SELECT * FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' ".$kasaQuery." ".$sorgu1." ORDER BY sh_id DESC LIMIT $pagem,$limit";

	    }else{



	    	if(!empty($cariBilgisi) && $cariBilgisi != "tumu"){

	    		$kasaQuery = "AND sh_cariID = '$cariBilgisi'";

	    	} 



	        $countq = "SELECT COUNT(*) as total FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' ".$kasaQuery."";

	        $countexe = $this->db->query($countq)->row();

	        $count = $countexe->total;

	        $sorgu = "SELECT * FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' ".$kasaQuery." ORDER BY sh_id DESC LIMIT $pagem,$limit";

	    }



	    $data["count_of_list"] = $count;



	    $this->load->library("pagination");



	    $config = array();

	    $config["base_url"] = base_url() . "/raporlar/$urim";

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

	    $data["cariStokHareketleri"] = $this->db->query($sorgu)->result();

	    $data["toplamHareket"] = $count;



	    $cariQ = "SELECT * FROM cari WHERE cari_id = '$cariBilgisi'";

	    $cariExe = $this->db->query($cariQ)->row();

	    $cariOlusturanAnaHesap = $cariExe->cari_olusturanAnaHesap;



			if($cariExe){

		    if($anaHesap == $cariOlusturanAnaHesap){

		$this->load->view("raporlar/cari-stok-hareket-raporlari",$data);

		}else{

		    	redirect('hata');

		    }

	  	}else{

	  			$this->load->view("cari/cari-hareketleri", $data);

	  	}

	}



	public function giderRaporlari(){

		$data["baslik"] = "Raporlar / Gider Raporları";

		$anaHesap = anaHesapBilgisi();



		$giderTuru = $this->input->get("giderTuru");

		

		$giderKategorileriQ = "SELECT * FROM giderKategorileri WHERE gk_olusturanAnaHesap = '$anaHesap' AND gk_mainID IS NULL";

		$data["giderKategorileri"] = $this->db->query($giderKategorileriQ)->result();



		//$olusturanHesapKim = $data["giderKategorileri"]->gk_olusturanAnaHesap;



		$giderTuruQ = "SELECT * FROM giderKategorileri WHERE gk_id = '$giderTuru' AND gk_olusturanAnaHesap = '$anaHesap' ";

		$data["giderKat"] = $this->db->query($giderTuruQ)->row();



		$olusturanHesapKim = $data["giderKat"]->gk_olusturanAnaHesap;



		if($giderTuru){

			if($anaHesap == $olusturanHesapKim){

				$this->load->view("raporlar/gider-raporlari",$data);

			}else{

				redirect('hata');

			}

		}else{

			$this->load->view("raporlar/gider-raporlari",$data);

		}

	}



	public function kritikStokRaporu(){

		$data["baslik"] = "Azalan Ürünler (Kritik Stok)";



		$anaHesap = anaHesapBilgisi();



		$urim = $this->uri->segment(2);



		$segment = 2;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if($sayfa){$pagem = ($page-1)*$limit;}

		else{$pagem = 0;/*logekle(6,1);*/}



		$countq = "SELECT COUNT(*) as total FROM stok WHERE stok_olusturanAnaHesap = '$anaHesap' AND stok_kritikSeviyesi IS NOT NULL";

		$countexe = $this->db->query($countq)->row();

		$count = $countexe->total;

		$sorgu = "SELECT * FROM stok WHERE stok_olusturanAnaHesap = '$anaHesap' AND stok_kritikSeviyesi IS NOT NULL ORDER BY stok_id DESC LIMIT $pagem,$limit";

	

		$data["count_of_list"] = $count;



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/raporlar/$urim";

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

		$data["stok"] = $this->db->query($sorgu)->result();



		$this->load->view("raporlar/kritik-stok-raporu",$data);

	}



	public function bitenStoklarRaporu(){

		$data["baslik"] = "Biten Stoklar";



		$anaHesap = anaHesapBilgisi();



		$urim = $this->uri->segment(2);



		$segment = 2;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if($sayfa){$pagem = ($page-1)*$limit;}

		else{$pagem = 0;/*logekle(6,1);*/}



		$countq = "SELECT COUNT(*) as total FROM stok WHERE stok_olusturanAnaHesap = '$anaHesap'";

		$countexe = $this->db->query($countq)->row();

		$count = $countexe->total;

		$sorgu = "SELECT * FROM stok WHERE stok_olusturanAnaHesap = '$anaHesap' ORDER BY stok_id DESC LIMIT $pagem,$limit";

	

		$data["count_of_list"] = $count;



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/raporlar/$urim";

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

		$data["stok"] = $this->db->query($sorgu)->result();



		$this->load->view("raporlar/biten-stoklar-raporu",$data);

	}



	public function enCokSatanlarRaporu(){

		$data["baslik"] = "En Çok Satanlar";



		$anaHesap = anaHesapBilgisi();



		$urim = $this->uri->segment(2);



		$segment = 2;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if($sayfa){$pagem = ($page-1)*$limit;}

		else{$pagem = 0;/*logekle(6,1);*/}



		$countq = "SELECT COUNT(*) as total FROM stok WHERE stok_olusturanAnaHesap = '$anaHesap'";

		$countexe = $this->db->query($countq)->row();

		$count = $countexe->total;

		$sorgu = "SELECT * FROM stok WHERE stok_olusturanAnaHesap = '$anaHesap' ORDER BY stok_id DESC LIMIT $pagem,$limit";

	

		$data["count_of_list"] = $count;



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/raporlar/$urim";

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

		$data["stok"] = $this->db->query($sorgu)->result();



		$this->load->view("raporlar/en-cok-satanlar-raporu",$data);

	}



	public function digiturk_personel_ciro_adet(){

		// Yetki kontrolü

		if(!grup_modul_yetkisi_var(1401)){

			redirect("home/hata");

		}



		$data["baslik"] = "Digiturk Personel Ciro/Adet Raporu";

		

		// Tarih filtreleri - Daha geniş varsayılan tarih aralığı

		$baslangic_tarihi = $this->input->get('baslangic_tarihi') ? $this->input->get('baslangic_tarihi') : date('Y-01-01'); // Yılbaşından itibaren

		$bitis_tarihi = $this->input->get('bitis_tarihi') ? $this->input->get('bitis_tarihi') : date('Y-m-d');

		$personel_id = $this->input->get('personel_id') ? $this->input->get('personel_id') : '';

		

		$data['baslangic_tarihi'] = $baslangic_tarihi;

		$data['bitis_tarihi'] = $bitis_tarihi;

		$data['personel_id'] = $personel_id;

		

		// Personel listesi

		$personel_query = "SELECT kullanici_id, CONCAT(kullanici_ad, ' ', kullanici_soyad) AS personel_adi 

						   FROM kullanicilar 

						   WHERE kullanici_durum = 1 

						   ORDER BY kullanici_ad";

		$data['personel_listesi'] = $this->db->query($personel_query)->result();

		

		// Ana rapor sorgusu - Aktivasyon tablosu olmadan

		$sql = "SELECT 

				kg.kg_adi AS kullanici_grubu,

				CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) AS personel_adi,

				COUNT(sf.satis_id) AS toplam_satis_adedi,

				SUM(sf.satis_genelToplam) AS toplam_ciro

			FROM satisFaturasi sf

			JOIN kullanicilar k ON sf.satis_olusturan = k.kullanici_id

			LEFT JOIN kullanici_grubu kg ON k.grup_id = kg.kg_id

			WHERE sf.satis_olusturmaTarihi BETWEEN ? AND ?";

		

		$params = array($baslangic_tarihi, $bitis_tarihi);

		

		if (!empty($personel_id)) {

			$sql .= " AND k.kullanici_id = ?";

			$params[] = $personel_id;

		}

		

		$sql .= " GROUP BY kg.kg_adi, k.kullanici_ad, k.kullanici_soyad

				  ORDER BY toplam_ciro DESC";

		

		$rapor_verileri = $this->db->query($sql, $params)->result();

		

		$data['rapor_verileri'] = $rapor_verileri;

		

		$this->load->view("raporlar/digiturk-personel-ciro-adet", $data);

	}



	public function digiturk_bolge_muduru_ciro_adet(){

		// Yetki kontrolü

		if(!grup_modul_yetkisi_var(1402)){

			redirect("home/hata");

		}



		$data["baslik"] = "Digiturk Bölge Müdürü ve Personel Ciro/Adet Raporu";

		

		// Tarih filtreleri

		$baslangic_tarihi = $this->input->get('baslangic_tarihi') ? $this->input->get('baslangic_tarihi') : date('Y-m-01');

		$bitis_tarihi = $this->input->get('bitis_tarihi') ? $this->input->get('bitis_tarihi') : date('Y-m-d');

		$personel_id = $this->input->get('personel_id') ? $this->input->get('personel_id') : '';

		

		$data['baslangic_tarihi'] = $baslangic_tarihi;

		$data['bitis_tarihi'] = $bitis_tarihi;

		$data['personel_id'] = $personel_id;

		

		// Personel listesi - sadece grup_id = 6 olanlar (Bölge müdürleri)

		$personel_query = "SELECT kullanici_id, CONCAT(kullanici_ad, ' ', kullanici_soyad) AS personel_adi 

						   FROM kullanicilar 

						   WHERE kullanici_durum = 1 AND grup_id = 6

						   ORDER BY kullanici_ad";

		$data['personel_listesi'] = $this->db->query($personel_query)->result();

		

		// Ana rapor sorgusu - Sadece Bölge müdürleri, kendi + bağlı personellerin toplamı

		$sql = "SELECT 

				bm.kullanici_id,

				kg.kg_adi AS kullanici_grubu,

				CONCAT(bm.kullanici_ad, ' ', bm.kullanici_soyad) AS personel_adi,

				-- Bölge müdürünün kendi satışları

				COALESCE(bm_sales.kendi_satis_adedi, 0) AS kendi_satis_adedi,

				COALESCE(bm_sales.kendi_ciro, 0) AS kendi_ciro,

				-- Bağlı personellerin satışları

				COALESCE(team_sales.personel_satis_adedi, 0) AS personel_satis_adedi,

				COALESCE(team_sales.personel_ciro, 0) AS personel_ciro,

				-- Toplam (kendi + personeller)

				(COALESCE(bm_sales.kendi_satis_adedi, 0) + COALESCE(team_sales.personel_satis_adedi, 0)) AS toplam_satis_adedi,

				(COALESCE(bm_sales.kendi_ciro, 0) + COALESCE(team_sales.personel_ciro, 0)) AS toplam_ciro

			FROM kullanicilar bm

			LEFT JOIN kullanici_grubu kg ON bm.grup_id = kg.kg_id

			-- Bölge müdürünün kendi satışları

			LEFT JOIN (

				SELECT 

					k.kullanici_id,

					COUNT(sf.satis_id) AS kendi_satis_adedi,

					SUM(sf.satis_genelToplam) AS kendi_ciro

				FROM aktivasyon a

				JOIN satisFaturasi sf ON a.aktivasyon_cari_id = sf.satis_cariID

				JOIN kullanicilar k ON sf.satis_olusturan = k.kullanici_id

				WHERE sf.satis_olusturmaTarihi BETWEEN ? AND ?

				AND k.grup_id = 6

				GROUP BY k.kullanici_id

			) bm_sales ON bm.kullanici_id = bm_sales.kullanici_id

			-- Bağlı personellerin satışları

			LEFT JOIN (

				SELECT 

					k.kullanici_sorumluMudur,

					COUNT(sf.satis_id) AS personel_satis_adedi,

					SUM(sf.satis_genelToplam) AS personel_ciro

				FROM aktivasyon a

				JOIN satisFaturasi sf ON a.aktivasyon_cari_id = sf.satis_cariID

				JOIN kullanicilar k ON sf.satis_olusturan = k.kullanici_id

				WHERE sf.satis_olusturmaTarihi BETWEEN ? AND ?

				AND k.kullanici_sorumluMudur IS NOT NULL

				AND k.kullanici_sorumluMudur IN (SELECT kullanici_id FROM kullanicilar WHERE grup_id = 6)

				GROUP BY k.kullanici_sorumluMudur

			) team_sales ON bm.kullanici_id = team_sales.kullanici_sorumluMudur

			WHERE bm.grup_id = 6 AND bm.kullanici_durum = 1";

		

		$params = array($baslangic_tarihi, $bitis_tarihi, $baslangic_tarihi, $bitis_tarihi);

		

		if (!empty($personel_id)) {

			$sql .= " AND bm.kullanici_id = ?";

			$params[] = $personel_id;

		}

		

		$sql .= " ORDER BY toplam_ciro DESC";

		

		$data['rapor_verileri'] = $this->db->query($sql, $params)->result();

		

		$this->load->view("raporlar/digiturk-bolge-muduru-ciro-adet", $data);

	}



	public function digiturk_sehir_ciro_adet(){

		// Yetki kontrolü

		if(!grup_modul_yetkisi_var(1403)){

			redirect("home/hata");

		}



		$data["baslik"] = "Digiturk Şehir Ciro/Adet Raporu";

		

		// Tarih filtreleri - Daha geniş varsayılan tarih aralığı

		$baslangic_tarihi = $this->input->get('baslangic_tarihi') ? $this->input->get('baslangic_tarihi') : date('Y-01-01'); // Yılbaşından itibaren

		$bitis_tarihi = $this->input->get('bitis_tarihi') ? $this->input->get('bitis_tarihi') : date('Y-m-d');

		$sehir_id = $this->input->get('sehir_id') ? $this->input->get('sehir_id') : '';

		

		$data['baslangic_tarihi'] = $baslangic_tarihi;

		$data['bitis_tarihi'] = $bitis_tarihi;

		$data['sehir_id'] = $sehir_id;

		

		// Şehir listesi

		$sehir_query = "SELECT id, il AS sehir_adi 

						FROM iller 

						ORDER BY il";

		$data['sehir_listesi'] = $this->db->query($sehir_query)->result();

		

		// Ana rapor sorgusu - Şehirlere göre satış raporu (Digiturk, S Sport ve TABII ayrı ayrı) - DÜZELTME

		$sql = "SELECT 
			COALESCE(i.il, '(Bilinmiyor)') AS sehir_adi,
			i.id AS cari_il,
			-- Digiturk satış adedi (stok_stokGrupKoduID = 1) - DÜZELTME: COUNT(*) kullan
			COUNT(CASE 
				WHEN st.stok_stokGrupKoduID = 1
				THEN 1 
				ELSE NULL 
			END) AS digiturk_satis_adedi,
			-- Digiturk ciro (stok_stokGrupKoduID = 1)
			COALESCE(SUM(CASE 
				WHEN st.stok_stokGrupKoduID = 1
				THEN sfs.satisStok_fiyatMiktar 
				ELSE 0 
			END), 0) AS digiturk_ciro,
			-- S Sport satış adedi (stok_id = 16)
			COUNT(CASE 
				WHEN st.stok_id = 16
				THEN 1 
				ELSE NULL 
			END) AS ssport_satis_adedi,
			-- S Sport ciro (stok_id = 16)
			COALESCE(SUM(CASE 
				WHEN st.stok_id = 16
				THEN sfs.satisStok_fiyatMiktar 
				ELSE 0 
			END), 0) AS ssport_ciro,
			-- TABII satış adedi (stok_id = 13)
			COUNT(CASE 
				WHEN st.stok_id = 13
				THEN 1 
				ELSE NULL 
			END) AS tabii_satis_adedi,
			-- TABII ciro (stok_id = 13)
			COALESCE(SUM(CASE 
				WHEN st.stok_id = 13
				THEN sfs.satisStok_fiyatMiktar 
				ELSE 0 
			END), 0) AS tabii_ciro,
			-- Toplam satış adedi (Digiturk + S Sport + TABII)
			COUNT(CASE 
				WHEN st.stok_stokGrupKoduID = 1 OR st.stok_id IN (16, 13)
				THEN 1 
				ELSE NULL 
			END) AS toplam_satis_adedi,
			-- Toplam ciro (Digiturk + S Sport + TABII)
			COALESCE(SUM(CASE 
				WHEN st.stok_stokGrupKoduID = 1 OR st.stok_id IN (16, 13)
				THEN sfs.satisStok_fiyatMiktar 
				ELSE 0 
			END), 0) AS toplam_ciro,
			-- Ortak satış adedi - hesaplanacak
			0 AS ortak_satis_adedi
		FROM satisfaturasistok sfs
		JOIN stok st ON st.stok_id = sfs.satisStok_stokID
		JOIN satisfaturasi sf ON sf.satis_id = sfs.satisStok_satisFaturasiID
		JOIN cari c ON c.cari_id = sf.satis_cariID
		LEFT JOIN iller i ON i.id = c.cari_il
		WHERE sf.satis_faturaTarihi BETWEEN ? AND ?
		AND (st.stok_stokGrupKoduID = 1 OR st.stok_id IN (16, 13))";

		

		$params = array($baslangic_tarihi, $bitis_tarihi);

		

		if (!empty($sehir_id)) {
			$sql .= " AND i.id = ?";
			$params[] = $sehir_id;
		}

		$sql .= " GROUP BY COALESCE(i.id, 0), COALESCE(i.il, '(Bilinmiyor)')
				  ORDER BY toplam_ciro DESC, sehir_adi";

		

		$rapor_verileri = $this->db->query($sql, $params)->result();

		

		$data['rapor_verileri'] = $rapor_verileri;

		

		$this->load->view("raporlar/digiturk-sehir-ciro-adet", $data);

	}

	public function digiturk_sehir_cari_detay(){
		// AJAX isteği kontrolü - CodeIgniter 3'te $_SERVER kullanılır
		if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			show_404();
		}

		// Yetki kontrolü
		if(!grup_modul_yetkisi_var(1403)){
			echo json_encode(['error' => 'Yetkiniz yok']);
			return;
		}

		$sehir_id = $this->input->post('sehir_id');
		$baslangic_tarihi = $this->input->post('baslangic_tarihi');
		$bitis_tarihi = $this->input->post('bitis_tarihi');
		$product_type = $this->input->post('product_type'); // Yeni parametre

		if (!$baslangic_tarihi || !$bitis_tarihi) {
			echo json_encode(['error' => 'Eksik parametre - baslangic: ' . $baslangic_tarihi . ', bitis: ' . $bitis_tarihi]);
			return;
		}

		try {
			// Eskişehir örneğine göre düzeltilmiş SQL query - DÜZELTME: satis_faturaTarihi kullan
			$sql = "SELECT 
					c.cari_id,
					c.cari_ad,
					CASE 
						WHEN LENGTH(c.cari_firmaTelefon) >= 10 THEN
							CONCAT(
								SUBSTRING(c.cari_firmaTelefon, 1, 3), ' ',
								SUBSTRING(c.cari_firmaTelefon, 4, 3), ' ',
								SUBSTRING(c.cari_firmaTelefon, 7, 2), ' ',
								SUBSTRING(c.cari_firmaTelefon, 9, 2)
							)
						ELSE c.cari_firmaTelefon
					END AS cari_firmaTelefon,
					i.il AS il_adi,
					ic.ilce AS ilce_adi,
					st.stok_ad AS urun_tipi,
					CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) AS personel_adsoyad,
					sfs.satisStok_fiyatMiktar AS toplam_tutar,
					sf.satis_faturaTarihi AS fatura_tarihi
				FROM satisfaturasistok sfs
				JOIN satisfaturasi sf ON sf.satis_id = sfs.satisStok_satisFaturasiID
				JOIN stok st ON st.stok_id = sfs.satisStok_stokID
				JOIN cari c ON c.cari_id = sf.satis_cariID
				JOIN iller i ON i.id = c.cari_il
				LEFT JOIN ilceler ic ON ic.id = c.cari_ilce
				LEFT JOIN kullanicilar k ON k.kullanici_id = c.cari_olusturan
				WHERE sf.satis_faturaTarihi BETWEEN ? AND ?";
			
			$params = array($baslangic_tarihi, $bitis_tarihi);
			
			// Şehir filtresi varsa ekle
			if (!empty($sehir_id)) {
				$sql .= " AND i.id = ?";
				$params[] = $sehir_id;
			}
			
			// Product type filtrelemesi ekle
			if ($product_type == 'digiturk') {
				$sql .= " AND st.stok_stokGrupKoduID = 1";
			} elseif ($product_type == 'ssport') {
				$sql .= " AND st.stok_id = 16";
			} elseif ($product_type == 'tabii') {
				$sql .= " AND st.stok_id = 13";
			} elseif ($product_type == 'all') {
				$sql .= " AND (st.stok_stokGrupKoduID = 1 OR st.stok_id IN (16, 13))";
			}
			
			$sql .= " ORDER BY sf.satis_faturaTarihi DESC, c.cari_id, sfs.satisStok_fiyatMiktar DESC";

			$result = $this->db->query($sql, $params);
			
			if (!$result) {
				echo json_encode(['error' => 'Veritabanı hatası: ' . $this->db->error()['message']]);
				return;
			}
			
			$data = $result->result();

			echo json_encode(['success' => true, 'data' => $data, 'count' => count($data), 'product_type' => $product_type]);
			
		} catch (Exception $e) {
			echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
		}
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
	public function detayli_muhasebe_raporu()
	{
		// Helper yükle
		$this->load->helper(['destek_helper', 'general_helper']);
		
		// Yetki kontrolü - Admin grubu (id=1) her zaman erişebilir
		$control = session("r", "login_info");
		$is_admin = $control && isset($control->grup_id) && $control->grup_id == 1;
		
		if (!$is_admin && !grup_modul_yetkisi_var(999)) {
			$this->session->set_flashdata('hata', 'Bu sayfaya erişim yetkiniz yok!');
			redirect('yetkisiz');
		}
		
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
		$this->load->view('raporlar/sozlesme-tahsilat-raporu', $data);
	}
	
	/**
	 * Detaylı Muhasebe Raporu - Ajax Endpoint
	 * 
	 * DataTable için server-side data sağlar
	 */
	public function detayli_muhasebe_raporu_ajax()
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
			'fatura_kesildi' => $this->input->post('fatura_kesildi'),
		];
		
		// Boş filtreleri temizle (0 değerini korumak için özel kontrol)
		$filters = array_filter($filters, function($value) {
			return $value !== '' && $value !== null;
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
	public function detayli_muhasebe_raporu_excel()
	{
		// Timeout ve memory limitlerini artır
		set_time_limit(300); // 5 dakika
		ini_set('memory_limit', '512M');
		
		// Helper yükle
		$this->load->helper(['destek_helper', 'general_helper']);
		
		// Yetki kontrolü
		$control = session("r", "login_info");
		$is_admin = $control && $control->grup_id == 1;
		
		if (!$is_admin && !grup_modul_yetkisi_var(999)) {
			$this->session->set_flashdata('hata', 'Excel export yetkiniz yok!');
			redirect('raporlar/detayli_muhasebe_raporu');
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
			'fatura_kesildi' => $this->input->get('fatura_kesildi'),
		];
		
		// Boş filtreleri temizle
		$filters = array_filter($filters, function($value) {
			return $value !== '' && $value !== null;
		});
		
		// Sorgu çalıştır
		$result = $this->Sozlesme_tahsilat_model->get_detayli_rapor($filters);
		
		// Hata kontrolü
		if ($result === false) {
			$this->session->set_flashdata('hata', 'Veri çekilirken bir hata oluştu! Lütfen tekrar deneyin.');
			redirect('raporlar/detayli_muhasebe_raporu');
			return;
		}
		
		$data = $result->result();
		
		// Veri kontrolü
		if (empty($data)) {
			$this->session->set_flashdata('uyari', 'Seçili filtrelere göre veri bulunamadı!');
			redirect('raporlar/detayli_muhasebe_raporu');
			return;
		}
		
		// Excel oluştur - Memory optimized
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Detaylı Muhasebe Raporu');
		
		// Başlık satırı
		$headers = [
			'İşletme No', 'İşletme Adı', 'Ülke', 'Şehir', 'İlçe', 'Bölge Sahibi', 'Sezon',
			'Sözleşme Hizmeti', 'Sözleşme Tutarı', 'Sözleşme Tarihi', 'Fatura Durumu',
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
		
		// Veri satırları - Basitleştirilmiş (memory optimizasyonu)
		$row = 2;
		foreach ($data as $item) {
			// fromArray kullanarak toplu veri ekleme - daha hızlı ve az memory kullanır
			// Tutar alanları için _raw değerlerini kullan (Excel'de düzgün gösterim için)
			$rowData = [
				$item->isletme_no ?? '',
				$item->isletme_adi ?? '',
				$item->ulke ?? '',
				$item->sehir ?? '',
				$item->ilce ?? '',
				$item->bolge_sahibi ?? '',
				$item->sezon ?? '',
				$item->sozlesme_hizmeti ?? '',
				$item->sozlesme_tutari_raw ?? '',
				$item->sozlesme_tarihi ?? '',
				$item->fatura_durumu ?? '',
				$item->cek_tutari_raw ?? '',
				$item->cek_vade_tarihi ?? '',
				$item->senet_tutari_raw ?? '',
				$item->senet_vade_tarihi ?? '',
				$item->tahsilat_nakit_tutar_raw ?? '',
				$item->tahsilat_nakit_tarih ?? '',
				$item->tahsilat_banka_tutar_raw ?? '',
				$item->tahsilat_banka_tarih ?? '',
				$item->senet_tutari_raw ?? '', // Tahsilat senet için raw
				$item->tahsilat_senet_tarih ?? '',
				$item->cek_tutari_raw ?? '', // Tahsilat çek için raw
				$item->tahsilat_cek_tarih ?? '',
				$item->personel ?? '',
				$item->aktivasyon_uye_no ?? '',
				$item->aktivasyon_hizmet ?? ''
			];
			
			$sheet->fromArray($rowData, null, 'A' . $row);
			$row++;
			
			// Her 100 satırda bir memory'yi temizle
			if ($row % 100 == 0) {
				gc_collect_cycles();
			}
		}
		
		// Tutar sütunlarına sayı formatı uygula (Türk Lirası formatı)
		// Sütunlar: I, L, N, P, R, T, V (Sözleşme, Çek, Senet, Tahsilatlar)
		$tutarSutunlari = ['I', 'L', 'N', 'P', 'R', 'T', 'V'];
		$sonSatir = $row - 1;
		
		if ($sonSatir >= 2) {
			foreach ($tutarSutunlari as $col) {
				// Range olarak format uygula (performans için)
				$range = $col . '2:' . $col . $sonSatir;
				$sheet->getStyle($range)->getNumberFormat()
					->setFormatCode('#,##0.00 ₺');
			}
		}
		
		// Sütun genişlikleri - Sadece kullanılan sütunlar için
		foreach (range('A', 'Z') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}
		
		// Dosya adı
		$filename = 'detayli_muhasebe_raporu_' . date('Y-m-d_His') . '.xlsx';
		
		// Header'ları ayarla
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');
		header('Pragma: public');
		
		// Excel dosyasını çıktı ver
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		
		// Memory temizliği
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);
		
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
	 * Eski URL'lerden yeni URL'lere yönlendirme (Geriye dönük uyumluluk)
	 */
	public function sozlesme_tahsilat_raporu()
	{
		redirect('raporlar/detayli_muhasebe_raporu');
	}
	
	public function sozlesme_tahsilat_raporu_ajax()
	{
		$this->detayli_muhasebe_raporu_ajax();
	}
	
	public function sozlesme_tahsilat_raporu_excel()
	{
		$this->detayli_muhasebe_raporu_excel();
	}

	/**
	 * SENARYO 1: Konum Satış Raporu
	 * 
	 * Ülke/İl/İlçe ve Sözleşme Hizmeti bazında özet satış raporu
	 * Detay için modal ile ana raporu açar
	 */
	public function konum_satis_raporu()
	{
		// Yetki kontrolü - Module ID: 999
		if (!grup_modul_yetkisi_var(999)) {
			redirect('home/yetkisiz');
		}

		$this->load->model('Sozlesme_tahsilat_model');
		
		$data['baslik'] = 'Konum Satış Raporu';
		$data['ulke_listesi'] = $this->Sozlesme_tahsilat_model->get_ulke_listesi();
		$data['il_listesi'] = $this->Sozlesme_tahsilat_model->get_il_listesi();
		$data['ilce_listesi'] = $this->Sozlesme_tahsilat_model->get_ilce_listesi();
		
		$this->load->view('raporlar/konum-satis-raporu', $data);
	}

	/**
	 * AJAX: Konum Satış Raporu Verilerini Getir
	 */
	public function konum_satis_raporu_ajax()
	{
		// Yetki kontrolü
		if (!grup_modul_yetkisi_var(999)) {
			echo json_encode(['error' => 'Yetkiniz yok']);
			return;
		}

		$this->load->model('Sozlesme_tahsilat_model');
		
		// Filtreleri al
		$filters = [
			'baslangic_tarih' => $this->input->post('baslangic_tarih'),
			'bitis_tarih' => $this->input->post('bitis_tarih'),
			'ulke_id' => $this->input->post('ulke_id'),
			'il_id' => $this->input->post('il_id'),
			'ilce_id' => $this->input->post('ilce_id')
		];
		
		// Boş filtreleri temizle
		$filters = array_filter($filters);
		
		// Veriyi çek
		$result = $this->Sozlesme_tahsilat_model->get_konum_satis_ozet($filters);
		
		// DataTables formatında döndür
		$data = [];
		foreach ($result->result() as $row) {
			$data[] = [
				'ulke' => $row->ulke,
				'sehir' => $row->sehir,
				'ilce' => $row->ilce,
				'sozlesme_hizmeti' => $row->sozlesme_hizmeti,
				'adet' => $row->adet,
				'toplam_tutar' => $row->toplam_tutar,
				'toplam_tutar_raw' => $row->toplam_tutar_raw, // İstatistik kartları için
				'detay_btn' => '<button class="btn btn-sm btn-info detay-goster" 
								data-ulke-id="'.$row->ulke_id.'" 
								data-il-id="'.$row->il_id.'" 
								data-ilce-id="'.$row->ilce_id.'" 
								data-stok-id="'.$row->stok_id.'">
								<i class="fas fa-eye"></i> Detay
							</button>'
			];
		}
		
		echo json_encode(['data' => $data]);
	}

	/**
	 * EXCEL: Konum Satış Raporu Excel Export
	 */
	public function konum_satis_raporu_excel()
	{
		// Yetki kontrolü
		if (!grup_modul_yetkisi_var(999)) {
			redirect('home/yetkisiz');
		}

		$this->load->model('Sozlesme_tahsilat_model');
		$this->load->library('excel');
		
		// Filtreleri al
		$filters = [
			'baslangic_tarih' => $this->input->get('baslangic_tarih'),
			'bitis_tarih' => $this->input->get('bitis_tarih'),
			'ulke_id' => $this->input->get('ulke_id'),
			'il_id' => $this->input->get('il_id'),
			'ilce_id' => $this->input->get('ilce_id')
		];
		
		$filters = array_filter($filters);
		
		// Veriyi çek
		$result = $this->Sozlesme_tahsilat_model->get_konum_satis_ozet($filters);
		
		// Excel oluştur
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Konum Satış Raporu');
		
		// Başlıklar
		$headers = ['Ülke', 'Şehir', 'İlçe', 'Sözleşme Hizmeti', 'Adet', 'Toplam Tutar'];
		$col = 'A';
		foreach ($headers as $header) {
			$sheet->setCellValue($col.'1', $header);
			$sheet->getStyle($col.'1')->getFont()->setBold(true);
			$sheet->getColumnDimension($col)->setAutoSize(true);
			$col++;
		}
		
		// Veriler
		$row = 2;
		foreach ($result->result() as $data) {
			$sheet->setCellValue('A'.$row, $data->ulke);
			$sheet->setCellValue('B'.$row, $data->sehir);
			$sheet->setCellValue('C'.$row, $data->ilce);
			$sheet->setCellValue('D'.$row, $data->sozlesme_hizmeti);
			$sheet->setCellValue('E'.$row, $data->adet);
			$sheet->setCellValue('F'.$row, $data->toplam_tutar);
			$row++;
		}
		
		// Dosya adı ve indirme
		$filename = 'konum_satis_raporu_' . date('Y-m-d_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');
		
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}

	// ============================================================================
	// SENARYO 2: PERSONEL SATIŞ RAPORU
	// ============================================================================

	/**
	 * Personel Satış Raporu Ana Sayfa
	 */
	public function personel_satis_raporu()
	{
		// Yetki kontrolü - Module ID: 999
		if (!grup_modul_yetkisi_var(999)) {
			redirect('home/yetkisiz');
		}

		$this->load->model('Sozlesme_tahsilat_model');
		
		$data['baslik'] = 'Personel Satış Raporu';
		$data['personel_listesi'] = $this->Sozlesme_tahsilat_model->get_personel_listesi();
		
		$this->load->view('raporlar/personel-satis-raporu', $data);
	}

	/**
	 * AJAX: Personel Satış Raporu Verilerini Getir
	 */
	public function personel_satis_raporu_ajax()
	{
		// Yetki kontrolü
		if (!grup_modul_yetkisi_var(999)) {
			echo json_encode(['error' => 'Yetkiniz yok']);
			return;
		}

		$this->load->model('Sozlesme_tahsilat_model');
		
		// Filtreleri al
		$filters = [
			'baslangic_tarih' => $this->input->post('baslangic_tarih'),
			'bitis_tarih' => $this->input->post('bitis_tarih'),
			'personel_id' => $this->input->post('personel_id')
		];
		
		// Boş filtreleri temizle
		$filters = array_filter($filters);
		
		// Veriyi çek
		$result = $this->Sozlesme_tahsilat_model->get_personel_satis_ozet($filters);
		
		// DataTables formatında döndür
		$data = [];
		foreach ($result->result() as $row) {
			$data[] = [
				'personel' => $row->personel,
				'personel_id' => $row->personel_id,
				'sozlesme_hizmeti' => $row->sozlesme_hizmeti,
				'adet' => $row->adet,
				'toplam_tutar' => $row->toplam_tutar,
				'toplam_tutar_raw' => $row->toplam_tutar_raw,
				'detay_btn' => '<button class="btn btn-sm btn-info detay-goster" 
								data-personel-id="'.$row->personel_id.'" 
								data-stok-id="'.$row->stok_id.'">
								<i class="fas fa-eye"></i> Detay
							</button>'
			];
		}
		
		echo json_encode(['data' => $data]);
	}

	/**
	 * EXCEL: Personel Satış Raporu Excel Export
	 */
	public function personel_satis_raporu_excel()
	{
		// Yetki kontrolü
		if (!grup_modul_yetkisi_var(999)) {
			redirect('home/yetkisiz');
		}

		$this->load->model('Sozlesme_tahsilat_model');
		$this->load->library('excel');
		
		// Filtreleri al
		$filters = [
			'baslangic_tarih' => $this->input->get('baslangic_tarih'),
			'bitis_tarih' => $this->input->get('bitis_tarih'),
			'personel_id' => $this->input->get('personel_id')
		];
		
		$filters = array_filter($filters);
		
		// Veriyi çek
		$result = $this->Sozlesme_tahsilat_model->get_personel_satis_ozet($filters);
		
		// Excel oluştur
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Personel Satış Raporu');
		
		// Başlıklar
		$headers = ['Personel', 'Sözleşme Hizmeti', 'Adet', 'Toplam Tutar'];
		$col = 'A';
		foreach ($headers as $header) {
			$sheet->setCellValue($col . '1', $header);
			$sheet->getStyle($col . '1')->getFont()->setBold(true);
			$sheet->getStyle($col . '1')->getFill()
				->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setRGB('4CAF50');
			$sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
			$col++;
		}
		
		// Veriler
		$row = 2;
		foreach ($result->result() as $data_row) {
			$sheet->setCellValue('A' . $row, $data_row->personel);
			$sheet->setCellValue('B' . $row, $data_row->sozlesme_hizmeti);
			$sheet->setCellValue('C' . $row, $data_row->adet);
			$sheet->setCellValue('D' . $row, $data_row->toplam_tutar);
			$row++;
		}
		
		// Kolon genişlikleri
		$sheet->getColumnDimension('A')->setWidth(30);
		$sheet->getColumnDimension('B')->setWidth(40);
		$sheet->getColumnDimension('C')->setWidth(15);
		$sheet->getColumnDimension('D')->setWidth(20);
		
		// Download
		$filename = 'Personel_Satis_Raporu_' . date('Y-m-d_H-i-s') . '.xlsx';
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');
		
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}

	// ============================================================================
	// SENARYO 3: PERSONEL TAHSİLAT RAPORU
	// ============================================================================

	/**
	 * Personel Tahsilat Raporu Ana Sayfa
	 */
	public function personel_tahsilat_raporu()
	{
		// Yetki kontrolü - Module ID: 999
		if (!grup_modul_yetkisi_var(999)) {
			redirect('home/yetkisiz');
		}

		$this->load->model('Sozlesme_tahsilat_model');
		
		$data['baslik'] = 'Personel Tahsilat Raporu';
		$data['personel_listesi'] = $this->Sozlesme_tahsilat_model->get_personel_listesi();
		
		$this->load->view('raporlar/personel-tahsilat-raporu', $data);
	}

	/**
	 * AJAX: Personel Tahsilat Raporu Verilerini Getir
	 */
	public function personel_tahsilat_raporu_ajax()
	{
		// Yetki kontrolü
		if (!grup_modul_yetkisi_var(999)) {
			echo json_encode(['error' => 'Yetkiniz yok']);
			return;
		}

		$this->load->model('Sozlesme_tahsilat_model');
		
		// Filtreleri al
		$filters = [
			'baslangic_tarih' => $this->input->post('baslangic_tarih'),
			'bitis_tarih' => $this->input->post('bitis_tarih'),
			'personel_id' => $this->input->post('personel_id')
		];
		
		// Boş filtreleri temizle
		$filters = array_filter($filters);
		
		// Veriyi çek
		$result = $this->Sozlesme_tahsilat_model->get_personel_tahsilat_ozet($filters);
		
		// DataTables formatında döndür
		$data = [];
		foreach ($result->result() as $row) {
			$data[] = [
				'personel' => $row->personel,
				'personel_id' => $row->personel_id,
				'tahsilat_tipi' => $row->tahsilat_tipi,
				'adet' => $row->adet,
				'toplam_tutar' => $row->toplam_tutar,
				'toplam_tutar_raw' => $row->toplam_tutar_raw,
				'detay_btn' => '<button class="btn btn-sm btn-info detay-goster" 
								data-personel-id="'.$row->personel_id.'" 
								data-tahsilat-tipi="'.$row->tahsilat_tipi_id.'">
								<i class="fas fa-eye"></i> Detay
							</button>'
			];
		}
		
		echo json_encode(['data' => $data]);
	}

	/**
	 * EXCEL: Personel Tahsilat Raporu Excel Export
	 */
	public function personel_tahsilat_raporu_excel()
	{
		// Yetki kontrolü
		if (!grup_modul_yetkisi_var(999)) {
			redirect('home/yetkisiz');
		}

		$this->load->model('Sozlesme_tahsilat_model');
		$this->load->library('excel');
		
		// Filtreleri al
		$filters = [
			'baslangic_tarih' => $this->input->get('baslangic_tarih'),
			'bitis_tarih' => $this->input->get('bitis_tarih'),
			'personel_id' => $this->input->get('personel_id')
		];
		
		$filters = array_filter($filters);
		
		// Veriyi çek
		$result = $this->Sozlesme_tahsilat_model->get_personel_tahsilat_ozet($filters);
		
		// Excel oluştur
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Personel Tahsilat Raporu');
		
		// Başlıklar
		$headers = ['Personel', 'Tahsilat Tipi', 'Adet', 'Toplam Tutar'];
		$col = 'A';
		foreach ($headers as $header) {
			$sheet->setCellValue($col . '1', $header);
			$sheet->getStyle($col . '1')->getFont()->setBold(true);
			$sheet->getStyle($col . '1')->getFill()
				->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setRGB('4CAF50');
			$sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
			$col++;
		}
		
		// Veriler
		$row = 2;
		foreach ($result->result() as $data_row) {
			$sheet->setCellValue('A' . $row, $data_row->personel);
			$sheet->setCellValue('B' . $row, $data_row->tahsilat_tipi);
			$sheet->setCellValue('C' . $row, $data_row->adet);
			$sheet->setCellValue('D' . $row, $data_row->toplam_tutar);
			$row++;
		}
		
		// Kolon genişlikleri
		$sheet->getColumnDimension('A')->setWidth(30);
		$sheet->getColumnDimension('B')->setWidth(20);
		$sheet->getColumnDimension('C')->setWidth(15);
		$sheet->getColumnDimension('D')->setWidth(20);
		
		// Download
		$filename = 'Personel_Tahsilat_Raporu_' . date('Y-m-d_H-i-s') . '.xlsx';
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');
		
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}

}