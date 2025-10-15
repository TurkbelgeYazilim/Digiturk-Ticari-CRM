<?php

defined('BASEPATH') or exit('No direct script access allowed');



use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Helper\Sample;

use PhpOffice\PhpSpreadsheet\IOFactory;



class Fatura extends CI_Controller

{



	public function __construct()

	{

		parent::__construct();

		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

		$this->load->model('vt');
		$this->load->database();
		$this->load->library('session');
		$this->load->helper('general');
		$this->load->helper('url');



		$control = session("r", "login");





		if(gibYetki()==1)

			redirect("home/hata");



		if (!$control) {

			redirect("check");

		}



		//sessionKontrolHelper();

	}



	public function satisFaturasi($id = 0)

	{

		$data["baslik"] = "Fatura / Sat�� S�zle�mesi Olu�tur";

		//logekle(11,1);

		$anaHesap = anaHesapBilgisi();



		$tevkifatQ = "SELECT * FROM tevkifatKodlari";

		$tevkifatExe = $this->db->query($tevkifatQ)->result();

		$tevkifat = "";

		foreach ($tevkifatExe as $item) {

			$tevkifat .= "<option value='" . $item->tevkifat_orani . "|" . $item->tevkifat_id . "'>" . $item->tevkifat_kodu . "-" . $item->tevkifat_adi . "</option>";

		}

		$data["tevkifat"] = $tevkifat;



		$istisnaQ = "SELECT * FROM istisnaKodlari";

		$istisnaExe = $this->db->query($istisnaQ)->result();

		$istisna = "";

		foreach ($istisnaExe as $item) {

			$istisna .= "<option value='" . $item->istisna_id . "'>" . $item->istisna_kodu . " " . $item->istisna_adi . "</option>";

		}

		$data["istisna"] = $istisna;

		$sqlBanka = "select * from ayarlar_banka where ayarlarbanka_olusturanAnaHesap=" . $anaHesap . " ";

		$data["faturabanka"] = $this->db->query($sqlBanka)->result();

//d�zenleme i�in veritaban�ndan verilerin �ekilmesi

		if ($id != 0) {

			$data["baslik"] = "Fatura / Sat�� Fatura D�zenle";

			$sql = "select * from satisFaturasi where satis_id=" . $id;

			$faturaDetay = $this->db->query($sql)->row();

			$data["faturaDetay"]=$faturaDetay ;



			$sqlEtiket="select * from etiketler where etiket_id=".$faturaDetay->satis_faturaEtiketID;

			$exeEtiket=$this->db->query($sqlEtiket)->row();

			$data["etiket"]=$exeEtiket;



			$sqlStok = "select * from satisFaturasiStok inner join stok on satisStok_stokID=stok_id inner join stokBirimleri on stokBirim_id=stok_birimID where satisStok_satisFaturasiID=" . $id;

			$data["stok"] = $this->db->query($sqlStok)->result();

			$sqlGenelIskonto = "select * from genel_iskonto inner join satisFaturasi on iskonto_satis_id=satis_id where satis_id=" . $id;

			$data["genelIskonto"] = $this->db->query($sqlGenelIskonto)->result();

			$sqlIade = "select * from iade_fatura_numaralari inner join satisFaturasi on iade_fatura_numaralari.satis_id=satisFaturasi.satis_id where satisFaturasi.satis_id=" . $id;

			$data["iadeFaturalari"] = $this->db->query($sqlIade)->result();

			$sqlCari = "select * from satisFaturasi inner join cari on satis_cariID=cari_id where satis_cariID=" . $data["faturaDetay"]->satis_cariID;

			$data["cari"] = $this->db->query($sqlCari)->row();

			if ($data["cari"]->cari_vergiNumarasi != null)

				$numara = $data["cari"]->cari_vergiNumarasi;

			else if ($data["cari"]->cari_tckn != null)

				$numara = $data["cari"]->cari_tckn;

			$data["cari_value"] = $numara . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;

			$sqlInternet = "select * from earsiv_internet_bilgileri where satis_id=" . $id;

			$data["earsivInternet"] = $this->db->query($sqlInternet)->row();

			$sqlBanka = "select * from faturabanka_bilgileri inner join ayarlar_banka on faturabanka_ayarbankaID=ayarlarbanka_id where faturabanka_faturaID=" . $id . " ";



			$data["faturabankaDuzenle"] = $this->db->query($sqlBanka)->result();



		}

		$iller = $this->db->get('iller');



		if ($iller->num_rows() > 0) {

			$data['_iller'] = $iller->result();

		} else {

			$data['_iller'] = false;

		}



		$stokBirimleriQ = "SELECT * FROM stokBirimleri";

		$birimler = $this->db->query($stokBirimleriQ)->result();

		$option ="";

		foreach ($birimler as $birim)

		{

			$option.="<option value='$birim->stokBirim_id'>$birim->stokBirim_ad</option>";

		}

		$data["birimler"]=$option;

		$this->load->view('fatura/satis-faturasi', $data);

	}





	public function satisFaturasiCari($id = 0)

	{

		$data["baslik"] = "Fatura / Sat�� S�zle�mesi Olu�tur";

		//logekle(11,1);

		$anaHesap = anaHesapBilgisi();



		$tevkifatQ = "SELECT * FROM tevkifatKodlari";

		$tevkifatExe = $this->db->query($tevkifatQ)->result();

		$tevkifat = "";

		foreach ($tevkifatExe as $item) {

			$tevkifat .= "<option value='" . $item->tevkifat_orani . "|" . $item->tevkifat_id . "'>" . $item->tevkifat_kodu . "-" . $item->tevkifat_adi . "</option>";

		}

		$data["tevkifat"] = $tevkifat;



		$istisnaQ = "SELECT * FROM istisnaKodlari";

		$istisnaExe = $this->db->query($istisnaQ)->result();

		$istisna = "";

		foreach ($istisnaExe as $item) {

			$istisna .= "<option value='" . $item->istisna_id . "'>" . $item->istisna_kodu . " " . $item->istisna_adi . "</option>";

		}

		$data["istisna"] = $istisna;

		$sqlBanka = "select * from ayarlar_banka where ayarlarbanka_olusturanAnaHesap=" . $anaHesap . " ";

		$data["faturabanka"] = $this->db->query($sqlBanka)->result();

//cari kaydını getir

		if ($id != 0) {



			$sqlCari = "select * from cari where  cari_id=$id";

			$data["cari"] = $this->db->query($sqlCari)->row();

			if ($data["cari"]->cari_vergiNumarasi != null)

				$numara = $data["cari"]->cari_vergiNumarasi;

			else if ($data["cari"]->cari_tckn != null)

				$numara = $data["cari"]->cari_tckn;

			$data["cari_value"] = $numara . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;





		}

		$iller = $this->db->get('iller');



		if ($iller->num_rows() > 0) {

			$data['_iller'] = $iller->result();

		} else {

			$data['_iller'] = false;

		}



		$stokBirimleriQ = "SELECT * FROM stokBirimleri";

		$birimler = $this->db->query($stokBirimleriQ)->result();

		$option ="";

		foreach ($birimler as $birim)

		{

			$option.="<option value='$birim->stokBirim_id'>$birim->stokBirim_ad</option>";

		}

		$data["birimler"]=$option;

		$this->load->view('fatura/satis-faturasi', $data);

	}



	public function eFaturaOlustur($id = 0)

	{



		$data["baslik"] = "Fatura / E-Fatura Olu�tur";

		//logekle(11,1);

		$anaHesap = anaHesapBilgisi();



		$tevkifatQ = "SELECT * FROM tevkifatKodlari";

		$tevkifatExe = $this->db->query($tevkifatQ)->result();

		$tevkifat = "";

		foreach ($tevkifatExe as $item) {

			$tevkifat .= "<option value='" . $item->tevkifat_orani . "|" . $item->tevkifat_id . "'>" . $item->tevkifat_kodu . "-" . $item->tevkifat_adi . "</option>";

		}

		$data["tevkifat"] = $tevkifat;



		$istisnaQ = "SELECT * FROM istisnaKodlari";

		$istisnaExe = $this->db->query($istisnaQ)->result();

		$istisna = "";



		foreach ($istisnaExe as $item) {



			$istisna .= "<option value='" . $item->istisna_id . "'>" . $item->istisna_kodu . " " . $item->istisna_adi . "</option>";

		}

		$data["istisna"] = $istisna;



		$sqlBanka = "select * from ayarlar_banka where ayarlarbanka_olusturanAnaHesap=" . $anaHesap . " ";

		$data["faturabanka"] = $this->db->query($sqlBanka)->result();

		//d�zenleme i�in verilerin veritaban�ndan �ekilmesi

		if ($id != 0) {

			$data["baslik"] = "Fatura / E-Fatura D�zenle";

			$sql = "select * from satisFaturasi where satis_id=" . $id;

			$faturaDetay = $this->db->query($sql)->row();

			$data["faturaDetay"]=$faturaDetay ;



			$sqlEtiket="select * from etiketler where etiket_id=".$faturaDetay->satis_faturaEtiketID;

			$exeEtiket=$this->db->query($sqlEtiket)->row();

			$data["etiket"]=$exeEtiket;





			$sqlStok = "select * from satisFaturasiStok inner join stok on satisStok_stokID=stok_id inner join stokBirimleri on stokBirim_id=stok_birimID where satisStok_satisFaturasiID=".$id;

			$data["stok"] = $this->db->query($sqlStok)->result();

			$sqlGenelIskonto = "select * from genel_iskonto inner join satisFaturasi on iskonto_satis_id=satis_id where satis_id=" . $id;

			$data["genelIskonto"] = $this->db->query($sqlGenelIskonto)->result();

			$sqlIade = "select * from iade_fatura_numaralari inner join satisFaturasi on iade_fatura_numaralari.satis_id=satisFaturasi.satis_id where satisFaturasi.satis_id=" . $id;

			$data["iadeFaturalari"] = $this->db->query($sqlIade)->result();

			$sqlCari = "select * from satisFaturasi inner join cari on satis_cariID=cari_id where satis_cariID=" . $data["faturaDetay"]->satis_cariID;

			$data["cari"] = $this->db->query($sqlCari)->row();

			if ($data["cari"]->cari_vergiNumarasi != null)

				$numara = $data["cari"]->cari_vergiNumarasi;

			else if ($data["cari"]->cari_tckn != null)

				$numara = $data["cari"]->cari_tckn;

			$data["cari_value"] = $numara . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;

			$sqlBanka = "select * from faturabanka_bilgileri inner join ayarlar_banka on faturabanka_ayarbankaID=ayarlarbanka_id where faturabanka_faturaID=" . $id . " ";



			$data["faturabankaDuzenle"] = $this->db->query($sqlBanka)->result();

		}

		$iller = $this->db->get('iller');



		if ($iller->num_rows() > 0) {

			$data['_iller'] = $iller->result();

		} else {

			$data['_iller'] = false;

		}



		$stokBirimleriQ = "SELECT * FROM stokBirimleri";

		$birimler = $this->db->query($stokBirimleriQ)->result();

		$option ="";

		foreach ($birimler as $birim)

		{

			$option.="<option value='$birim->stokBirim_id'>$birim->stokBirim_ad</option>";

		}

		$data["birimler"]=$option;

		$this->load->view('fatura/efatura-olustur', $data);

	}



	public function eFaturaOlusturCari($id = 0)

	{



		$data["baslik"] = "Fatura / E-Fatura Olu�tur";

		//logekle(11,1);

		$anaHesap = anaHesapBilgisi();



		$tevkifatQ = "SELECT * FROM tevkifatKodlari";

		$tevkifatExe = $this->db->query($tevkifatQ)->result();

		$tevkifat = "";

		foreach ($tevkifatExe as $item) {

			$tevkifat .= "<option value='" . $item->tevkifat_orani . "|" . $item->tevkifat_id . "'>" . $item->tevkifat_kodu . "-" . $item->tevkifat_adi . "</option>";

		}

		$data["tevkifat"] = $tevkifat;



		$istisnaQ = "SELECT * FROM istisnaKodlari";

		$istisnaExe = $this->db->query($istisnaQ)->result();

		$istisna = "";



		foreach ($istisnaExe as $item) {



			$istisna .= "<option value='" . $item->istisna_id . "'>" . $item->istisna_kodu . " " . $item->istisna_adi . "</option>";

		}

		$data["istisna"] = $istisna;



		$sqlBanka = "select * from ayarlar_banka where ayarlarbanka_olusturanAnaHesap=" . $anaHesap . " ";

		$data["faturabanka"] = $this->db->query($sqlBanka)->result();

		//düzenleme için verilerin veritabanından çekilmesi

		if ($id != 0) {



			$sqlCari = "select * from cari where  cari_id=$id";

			$data["cari"] = $this->db->query($sqlCari)->row();

			if ($data["cari"]->cari_vergiNumarasi != null)

				$numara = $data["cari"]->cari_vergiNumarasi;

			else if ($data["cari"]->cari_tckn != null)

				$numara = $data["cari"]->cari_tckn;

			$data["cari_value"] = $numara . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;



		}

		$iller = $this->db->get('iller');



		if ($iller->num_rows() > 0) {

			$data['_iller'] = $iller->result();

		} else {

			$data['_iller'] = false;

		}



		$stokBirimleriQ = "SELECT * FROM stokBirimleri";

		$birimler = $this->db->query($stokBirimleriQ)->result();

		$option ="";

		foreach ($birimler as $birim)

		{

			$option.="<option value='$birim->stokBirim_id'>$birim->stokBirim_ad</option>";

		}

		$data["birimler"]=$option;

		$this->load->view('fatura/efatura-olustur', $data);

	}



	public function eArsivOlustur($id = 0)

	{

		$data["baslik"] = "Fatura / E-Ar�iv Olu�tur";

		//logekle(11,1);

		$anaHesap = anaHesapBilgisi();



		$tevkifatQ = "SELECT * FROM tevkifatKodlari";

		$tevkifatExe = $this->db->query($tevkifatQ)->result();

		$tevkifat = "";

		foreach ($tevkifatExe as $item) {

			$tevkifat .= "<option value='" . $item->tevkifat_orani . "|" . $item->tevkifat_id . "'>" . $item->tevkifat_kodu . "-" . $item->tevkifat_adi . "</option>";

		}

		$data["tevkifat"] = $tevkifat;



		$istisnaQ = "SELECT * FROM istisnaKodlari";

		$istisnaExe = $this->db->query($istisnaQ)->result();

		$istisna = "";

		foreach ($istisnaExe as $item) {

			$istisna .= "<option value='" . $item->istisna_id . "'>" . $item->istisna_kodu . " " . $item->istisna_adi . "</option>";

		}

		$data["istisna"] = $istisna;

		$sqlBanka = "select * from ayarlar_banka where ayarlarbanka_olusturanAnaHesap=" . $anaHesap . " ";

		$data["faturabanka"] = $this->db->query($sqlBanka)->result();

		//d�zenleme i�in veritaban�ndan verilerin �ekilmesi

		if ($id != 0) {

			$data["baslik"] = "Fatura / E-Ar�iv D�zenle";

			$sql = "select * from satisFaturasi where satis_id=" . $id;

			$faturaDetay = $this->db->query($sql)->row();

			$data["faturaDetay"]=$faturaDetay ;



			$sqlEtiket="select * from etiketler where etiket_id=".$faturaDetay->satis_faturaEtiketID;

			$exeEtiket=$this->db->query($sqlEtiket)->row();

			$data["etiket"]=$exeEtiket;





			$sqlStok = "select * from satisFaturasiStok inner join stok on satisStok_stokID=stok_id inner join stokBirimleri on stokBirim_id=stok_birimID where satisStok_satisFaturasiID=" . $id;

			$data["stok"] = $this->db->query($sqlStok)->result();

			$sqlGenelIskonto = "select * from genel_iskonto inner join satisFaturasi on iskonto_satis_id=satis_id where satis_id=" . $id;

			$data["genelIskonto"] = $this->db->query($sqlGenelIskonto)->result();

			$sqlIade = "select * from iade_fatura_numaralari inner join satisFaturasi on iade_fatura_numaralari.satis_id=satisFaturasi.satis_id where satisFaturasi.satis_id=" . $id;

			$data["iadeFaturalari"] = $this->db->query($sqlIade)->result();

			$sqlCari = "select * from satisFaturasi inner join cari on satis_cariID=cari_id where satis_cariID=" . $data["faturaDetay"]->satis_cariID;

			$data["cari"] = $this->db->query($sqlCari)->row();

			if ($data["cari"]->cari_vergiNumarasi != null)

				$numara = $data["cari"]->cari_vergiNumarasi;

			else if ($data["cari"]->cari_tckn != null)

				$numara = $data["cari"]->cari_tckn;

			$data["cari_value"] = $numara . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;



			$sqlInternet = "select * from earsiv_internet_bilgileri where satis_id=" . $id;

			$data["earsivInternet"] = $this->db->query($sqlInternet)->row();



			$sqlBanka = "select * from faturabanka_bilgileri inner join ayarlar_banka on faturabanka_ayarbankaID=ayarlarbanka_id where faturabanka_faturaID=" . $id . " ";

			$data["faturabankaDuzenle"] = $this->db->query($sqlBanka)->result();

		}

		$iller = $this->db->get('iller');



		if ($iller->num_rows() > 0) {

			$data['_iller'] = $iller->result();

		} else {

			$data['_iller'] = false;

		}



		$stokBirimleriQ = "SELECT * FROM stokBirimleri";

		$birimler = $this->db->query($stokBirimleriQ)->result();

		$option ="";

		foreach ($birimler as $birim)

		{

			$option.="<option value='$birim->stokBirim_id'>$birim->stokBirim_ad</option>";

		}

		$data["birimler"]=$option;

		$this->load->view('fatura/earsiv-olustur', $data);

	}



	public function eArsivOlusturCari($id = 0)

	{

		$data["baslik"] = "Fatura / E-Arşiv Oluştur";

		//logekle(11,1);

		$anaHesap = anaHesapBilgisi();



		$tevkifatQ = "SELECT * FROM tevkifatKodlari";

		$tevkifatExe = $this->db->query($tevkifatQ)->result();

		$tevkifat = "";

		foreach ($tevkifatExe as $item) {

			$tevkifat .= "<option value='" . $item->tevkifat_orani . "|" . $item->tevkifat_id . "'>" . $item->tevkifat_kodu . "-" . $item->tevkifat_adi . "</option>";

		}

		$data["tevkifat"] = $tevkifat;



		$istisnaQ = "SELECT * FROM istisnaKodlari";

		$istisnaExe = $this->db->query($istisnaQ)->result();

		$istisna = "";

		foreach ($istisnaExe as $item) {

			$istisna .= "<option value='" . $item->istisna_id . "'>" . $item->istisna_kodu . " " . $item->istisna_adi . "</option>";

		}

		$data["istisna"] = $istisna;

		$sqlBanka = "select * from ayarlar_banka where ayarlarbanka_olusturanAnaHesap=" . $anaHesap . " ";

		$data["faturabanka"] = $this->db->query($sqlBanka)->result();

		//düzenleme için veritabanından verilerin çekilmesi

		if ($id != 0) {

			$sqlCari = "select * from cari where  cari_id=$id";

			$data["cari"] = $this->db->query($sqlCari)->row();

			if ($data["cari"]->cari_vergiNumarasi != null)

				$numara = $data["cari"]->cari_vergiNumarasi;

			else if ($data["cari"]->cari_tckn != null)

				$numara = $data["cari"]->cari_tckn;

			$data["cari_value"] = $numara . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;

		}

		$iller = $this->db->get('iller');



		if ($iller->num_rows() > 0) {

			$data['_iller'] = $iller->result();

		} else {

			$data['_iller'] = false;

		}



		$stokBirimleriQ = "SELECT * FROM stokBirimleri";

		$birimler = $this->db->query($stokBirimleriQ)->result();

		$option ="";

		foreach ($birimler as $birim)

		{

			$option.="<option value='$birim->stokBirim_id'>$birim->stokBirim_ad</option>";

		}

		$data["birimler"]=$option;

		$this->load->view('fatura/earsiv-olustur', $data);

	}



	public function eirsaliyeolustur($id = 0){

		$data['baslik'] = 'E-İrsaliye Oluştur';



		if ($id != 0) {

			$data["baslik"] = "Fatura / E-Arşiv Düzenle";

			$sql = "select * from satisFaturasi where satis_id=" . $id;

			$faturaDetay = $this->db->query($sql)->row();

			$data["faturaDetay"]=$faturaDetay ;



			$irsaliyeDetay = $this->db->query("select * from irsaliye_bilgileri where irs_faturaID = '$id' ")->row();

			$data["irsaliyeDetay"] = $irsaliyeDetay;



 			$sqlStok = "select * from satisFaturasiStok inner join stok on satisStok_stokID=stok_id inner join stokBirimleri on stokBirim_id=stok_birimID where satisStok_satisFaturasiID=" . $id;

			$data["stok"] = $this->db->query($sqlStok)->result();

			$sqlGenelIskonto = "select * from genel_iskonto inner join satisFaturasi on iskonto_satis_id=satis_id where satis_id=" . $id;

			$data["genelIskonto"] = $this->db->query($sqlGenelIskonto)->result();

			$sqlIade = "select * from iade_fatura_numaralari inner join satisFaturasi on iade_fatura_numaralari.satis_id=satisFaturasi.satis_id where satisFaturasi.satis_id=" . $id;

			$data["iadeFaturalari"] = $this->db->query($sqlIade)->result();

			$sqlCari = "select * from satisFaturasi inner join cari on satis_cariID=cari_id where satis_cariID=" . $data["faturaDetay"]->satis_cariID;

			$data["cari"] = $this->db->query($sqlCari)->row();

			if ($data["cari"]->cari_vergiNumarasi != null)

				$numara = $data["cari"]->cari_vergiNumarasi;

			else if ($data["cari"]->cari_tckn != null)

				$numara = $data["cari"]->cari_tckn;

			$data["cari_value"] = $numara . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;



			$sqlInternet = "select * from earsiv_internet_bilgileri where satis_id=" . $id;

			$data["earsivInternet"] = $this->db->query($sqlInternet)->row();



			$sqlBanka = "select * from faturabanka_bilgileri inner join ayarlar_banka on faturabanka_ayarbankaID=ayarlarbanka_id where faturabanka_faturaID=" . $id . " ";

			$data["faturabankaDuzenle"] = $this->db->query($sqlBanka)->result();

		}



		$stokBirimleriQ = "SELECT * FROM stokBirimleri";

		$birimler = $this->db->query($stokBirimleriQ)->result();

		$option ="";

		foreach ($birimler as $birim)

		{

			$option.="<option value='$birim->stokBirim_id'>$birim->stokBirim_ad</option>";

		}

		$data["birimler"]=$option;



		$this->load->view('fatura/eirsaliye-olustur', $data);

	}



	public function irsaliyeOlustur(){

		echo "123";

	}



	public function irsaliyeGuncelle(){

		echo "123";

	}



	public function autocompleteDataByNameEArsiv()

	{

		// Ana hesap kısıtlaması kaldırıldı - tüm kullanıcılar tüm carileri görebilir

		$returnData = array();

		$conditions['searchTerm'] = $this->input->get('term');

		$getData = $this->vt->getRows($conditions, null); // Ana hesap null yapıldı



		if (!empty($getData)) {

			foreach ($getData as $row) {

				$cariGrubu = $row['cari_cariGrupKoduID'];

				$cariGrubuQ = "SELECT cariGrup_ad FROM cariGruplari WHERE cariGrup_id = '$cariGrubu'";

				$cariGrubuE = $this->db->query($cariGrubuQ)->row();

				if ($row['cari_tckn'] != null)

					$tcknvkn = $row['cari_tckn'];

				else if ($row['cari_vergiNumarasi'] != null)

					$tcknvkn = $row['cari_vergiNumarasi'];

				else $tcknvkn = "";

				if ($row["cari_soyad"] != null)

					$cariSoyad = " " . $row["cari_soyad"];

				else $cariSoyad = "";



				$data['id'] = $row['cari_id'];

				$data['value'] = $tcknvkn . " - " . $row['cari_ad'] . $cariSoyad;

				$data['cari_kodu'] = $row['cari_kodu'];

				$data['cari_grubu'] = $cariGrubuE->cariGrup_ad;

				$data['cari_vergiDairesi'] = $row['cari_vergiDairesi'];

				$data['cari_vergiNumarasi'] = $row['cari_vergiNumarasi'];

				$data['cari_tckn'] = $row['cari_tckn'];

				$data['cari_adres'] = $row['cari_adres'] ? $row['cari_adres'] : ''; // Boş ise boş string döndür

				$data['cari_firmaEPosta'] = $row['cari_firmaEPosta'];

				$data['cari_aliasPK'] = $row['cari_alias_pk'];

				array_push($returnData, $data);

			}

		}

		echo json_encode($returnData);

		die;

	}



	public function autocompleteDataByTcknVknEarsiv()

	{

		// Ana hesap kısıtlaması kaldırıldı - tüm kullanıcılar tüm carileri görebilir

		$returnData = array();

		$conditions['searchTerm'] = $this->input->get('term');

		$getData = $this->vt->getRows($conditions, null); // Ana hesap null yapıldı



		if (!empty($getData)) {

			foreach ($getData as $row) {

				$cariGrubu = $row['cari_cariGrupKoduID'];

				$cariGrubuQ = "SELECT cariGrup_ad FROM cariGruplari WHERE cariGrup_id = '$cariGrubu'";

				$cariGrubuE = $this->db->query($cariGrubuQ)->row();



				if ($row['cari_tckn'] != null)

					$tcknvkn = $row['cari_tckn'];

				else if ($row['cari_vergiNumarasi'] != null)

					$tcknvkn = $row['cari_vergiNumarasi'];

				else $tcknvkn = "";

				if ($row["cari_soyad"] != null)

					$cariSoyad = " " . $row["cari_soyad"];

				else $cariSoyad = "";

				$data['id'] = $row['cari_id'];

				$data['value'] = $tcknvkn . " - " . $row['cari_ad'] . $cariSoyad;

				$data['cari_kodu'] = $row['cari_kodu'];

				$data['cari_grubu'] = $cariGrubuE->cariGrup_ad;

				$data['cari_vergiDairesi'] = $row['cari_vergiDairesi'];

				$data['cari_vergiNumarasi'] = $row['cari_vergiNumarasi'];

				$data['cari_tckn'] = $row['cari_tckn'];

				$data['cari_adres'] = $row['cari_adres'];

				$data['cari_firmaEPosta'] = $row['cari_firmaEPosta'];

				$data['cari_aliasPK'] = $row['cari_alias_pk'];

				array_push($returnData, $data);

			}

		}

		echo json_encode($returnData);

		die;

	}





	public function autocompleteDataByCariGib()

	{

		// Ana hesap kısıtlaması kaldırıldı - tüm kullanıcılar tüm carileri görebilir

		$returnData = array();

		$conditions['searchTerm'] = $this->input->get('term');

		$getData = $this->vt->getRowsTCKNVKN($conditions, null); // Ana hesap null yapıldı



		if (!empty($getData)) {

			foreach ($getData as $row) {



				if ($row['cari_tckn'] != null)

					$tcknvkn = $row['cari_tckn'];

				else if ($row['cari_vergiNumarasi'] != null)

					$tcknvkn = $row['cari_vergiNumarasi'];

				else $tcknvkn = "";



				$data['id'] = $row['cari_id'];

				$data['value'] = $tcknvkn ;

				$data['cari_ad']= $row['cari_ad'] ;

				$data['cari_soyad']=$row["cari_soyad"];

				array_push($returnData, $data);

			}

		}

		echo json_encode($returnData);

		die;

	}







	public function autocompleteDataByName()

	{

		// Ana hesap kısıtlaması kaldırıldı - tüm kullanıcılar tüm carileri görebilir

		$returnData = array();

		$conditions['searchTerm'] = $this->input->get('term');

		$getData = $this->vt->getRows($conditions, null); // Ana hesap null yapıldı



		if (!empty($getData)) {

			foreach ($getData as $row) {

				$cariGrubu = $row['cari_cariGrupKoduID'];

				$cariGrubuQ = "SELECT cariGrup_ad FROM cariGruplari WHERE cariGrup_id = '$cariGrubu'";

				$cariGrubuE = $this->db->query($cariGrubuQ)->row();



				$data['id'] = $row['cari_id'];

				$data['value'] = $row['cari_ad'];

				$data['cari_kodu'] = $row['cari_kodu'];

				$data['cari_grubu'] = $cariGrubuE->cariGrup_ad;

				$data['cari_vergiDairesi'] = $row['cari_vergiDairesi'];

				$data['cari_vergiNumarasi'] = $row['cari_vergiNumarasi'];

				$data['cari_adres'] = $row['cari_adres'];

				array_push($returnData, $data);

			}

		}

		echo json_encode($returnData);

		die;

	}



	public function autocompleteDataByCode()

	{

		// Ana hesap kısıtlaması kaldırıldı - tüm kullanıcılar tüm carileri görebilir

		$returnData = array();

		$conditions['searchTerm'] = $this->input->get('term');

		$getData = $this->vt->getRows2($conditions, null); // Ana hesap null yapıldı



		if (!empty($getData)) {

			foreach ($getData as $row) {

				$cariGrubu = $row['cari_cariGrupKoduID'];

				$cariGrubuQ = "SELECT cariGrup_ad FROM cariGruplari WHERE cariGrup_id = '$cariGrubu'";

				$cariGrubuE = $this->db->query($cariGrubuQ)->row();



				$data['id'] = $row['cari_id'];

				$data['value'] = $row['cari_kodu'];

				$data['cari_ad'] = $row['cari_ad'];

				$data['cari_grubu'] = $cariGrubuE->cariGrup_ad;

				$data['cari_vergiDairesi'] = $row['cari_vergiDairesi'];

				$data['cari_vergiNumarasi'] = $row['cari_vergiNumarasi'];

				$data['cari_adres'] = $row['cari_adres'];

				$data['cari_firmaEPosta'] = $row['cari_firmaEPosta'];

				$data['cari_aliasPK'] = $row['cari_alias_pk'];

				array_push($returnData, $data);

			}

		}

		echo json_encode($returnData);

		die;

	}



	public function autocompleteDataByStockCode()

	{

		$anaHesap = anaHesapBilgisi();



		$returnData = array();

		$conditions['searchTerm'] = $this->input->get('term');

		$getData = $this->vt->getRows3($conditions, $anaHesap);



		if (!empty($getData)) {

			foreach ($getData as $row) {



				$data['id'] = $row['stok_id'];

				$data['value'] = $row['stok_kodu'];

				$data['stok_barkod'] = $row['stok_barkod'];

				$data['stok_ad'] = $row['stok_ad'];

				$data['stok_birimi'] = $row['stok_birimID'];

				$data['stok_satisFiyati'] = $row['stok_satisFiyati'];

				$data['stok_satisKDV'] = $row['stok_satisKDV'];

				$data['stok_alisFiyati'] = $row['stok_alisFiyati'];

				$data['stok_alisKDV'] = $row['stok_alisKDV'];

				array_push($returnData, $data);

			}

		}

		echo json_encode($returnData);

		die;

	}



	public function autocompleteDataByStockName()

	{

		// Ana hesap kısıtlaması tamamen kaldırıldı - tüm kullanıcılar tüm stokları görebilir

		$returnData = array();

		$conditions['searchTerm'] = $this->input->get('term');

		$getData = $this->vt->getRows4($conditions, null, true); // Ana hesap null, admin true



		if (!empty($getData)) {

			foreach ($getData as $row) {



				$data['id'] = $row['stok_id'];

				$data['value'] = $row['stok_ad'];

				$data['stok_barkod'] = $row['stok_barkod'];

				$data['stok_kodu'] = $row['stok_kodu'];

				$data['stok_birimi'] = $row['stok_birimID'];

				$data['stok_satisFiyati'] = $row['stok_satisFiyati'];

				$data['stok_satisKDV'] = $row['stok_satisKDV'];

				$data['stok_alisFiyati'] = $row['stok_alisFiyati'];

				$data['stok_alisKDV'] = $row['stok_alisKDV'];

				array_push($returnData, $data);

			}

		}

		echo json_encode($returnData);

		die;

	}





	public function autocompleteDataByEtiket()

	{

		$anaHesap = anaHesapBilgisi();



		$returnData = array();

		$conditions['searchTerm'] = $this->input->get('term');

		$getData = $this->vt->getRowsEtiket($conditions, $anaHesap);



		if (!empty($getData)) {

			foreach ($getData as $row) {



				$data['id'] = $row['etiket_id'];

				$data['value'] = $row['etiket_adi'];

				array_push($returnData, $data);

			}

		}

		echo json_encode($returnData);

		die;

	}



	public function satisFaturasiOlustur()

	{
		// DÜZELTME: KDV dahil tutar problemini çözmek için

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$ch_turu=2;

		$satis_InvoiceType = postval("satis_InvoiceType");



		$satis_etiketID=postval("etiket_id");



		if(empty($satis_etiketID))

		{

			$satis_faturaEtiketID=postval("satis_faturaEtiketID");

			if(!empty($satis_faturaEtiketID))

			{

				$dataEtiket["etiket_adi"]=$satis_faturaEtiketID;

				$dataEtiket["etiket_olusturan"] = $u_id;

				$dataEtiket["etiket_olusturanAnaHesap"] = $anaHesap;

				$dataEtiket["etiket_olusturmaTarihi"] = $tarihi;

				$dataEtiket["etiket_olusturmaSaati"] = $saati;

				$this->db->insert("etiketler",$dataEtiket);

				$satis_etiketID=$this->db->insert_id();

			}

		}



		$data["satis_faturaEtiketID"]=$satis_etiketID;



		if($satis_InvoiceType==1)

			$ch_turu=12;//e-fatura

		else if($satis_InvoiceType==2)

			$ch_turu=13;//e-arşiv

		else if($satis_InvoiceType==3)

			$ch_turu=14;//e-irsaliye



		$satis_irsaliyeTarihi = postval("satis_irsaliyeTarihi");

		$satis_faturaTarihi = postval("satis_faturaTarihi");



		$yeni_satis_irsaliyeTarihi = date("Y-m-d", strtotime($satis_irsaliyeTarihi));

		$yeni_satis_faturaTarihi = date("Y-m-d", strtotime($satis_faturaTarihi));



		$irsaliyeNo = postval("satis_irsaliyeNo");

		$faturaNo = postval("satis_faturaNo");



		$cariKodu = postval("cari_kodu");

		$cariAdi = postval("cari_adi");



		$genelToplamHidden = postval("geneltopHidden");

		$indirimToplamHidden = postval("indirimtopHidden");

		$tevkifatToplamHidden = postval("tevkifattopHidden");

		$vergidahilHidden = postval("vergidahilHidden");





		$istisna_sebebi = postval("istisna_sebebi");

		$vergiMuafiyet_sebep = postval("vergiMuafiyet_sebep");



		if (postval("satis_paraBirimi") == 1) {

			$guncelKur = NULL;

		} else {

			$guncelKur = postval("satis_guncelKur");

			$dovizCevrilmis = $genelToplamHidden * $guncelKur;

		}





		$data["satis_InvoiceType"] = $satis_InvoiceType;



		if ($satis_InvoiceType == 2) {//arşiv

			$data["satis_faturaDurum"] = 1;

			$data["satis_earsivfaturaTip"] = postval("satis_earsivfaturaTip");

			$data["satis_earsivTipi"] = postval("satis_earsivTipi");

			$data["satis_earsivGonderimSekli"] = postval("satis_earsivGonderimSekli");

		} else if ($satis_InvoiceType == 1)//e-fatura

		{

			$data["satis_faturaDurum"] = 1;

			$data["satis_faturaSenaryo"] = postval("satis_faturaSenaryo");

			$data["satis_earsivfaturaTip"] = postval("satis_efaturaTip");



		}else if ($satis_InvoiceType == 3)//e-fatura

		{

			$data["satis_faturaDurum"] = 1;



		} else //Satış Sözleşmesi

		{

			$data["satis_faturaDurum"] = 2;

			$data["satis_earsivfaturaTip"] = postval("satis_faturaTip");

		}



		$paraBirimi=postval("satis_paraBirimi");



		$data["satis_cariID"] = postval("satis_cariID");

		$data["satis_paraBirimi"] = $paraBirimi;

		$data["satis_guncelKur"] = $guncelKur;

		$data["satis_dovizCevrilmis"] = $dovizCevrilmis;

		$data["satis_irsaliyeNo"] = $irsaliyeNo != '' ? $irsaliyeNo : NULL;

		$data["satis_irsaliyeTarihi"] = $satis_irsaliyeTarihi != '' ? $yeni_satis_irsaliyeTarihi : NULL;

		$data["satis_faturaNo"] = $faturaNo;

		$data["satis_faturaTarihi"] = $yeni_satis_faturaTarihi;

		$data["satis_faturaSaati"] = postval("satis_faturaSaati");

		$data["satis_aciklama"] = postval("satis_aciklama");

		$data["satis_araToplam"] = postval("aratopHidden");

		$data["satis_kdvToplam"] = postval("kdvtopHidden");

		$data["satis_genelToplam"] = $genelToplamHidden;

		$data["satis_vergiDahilToplam"] = $vergidahilHidden;

		$data["satis_indirimToplam"] = $indirimToplamHidden;

		$data["satis_netTutar"] = postval("netTutarInput");



		if ($tevkifatToplamHidden != null)

			$data["satis_tevkifatToplam"] = $tevkifatToplamHidden;

		if ($istisna_sebebi != null)

			$data["satis_istisna_id"] = $istisna_sebebi;



		if ($vergiMuafiyet_sebep != null)

			$data["satis_vergiMuafiyetSebep"] = $vergiMuafiyet_sebep;



		$data["satis_olusturan"] = $u_id;

		$data["satis_olusturanAnaHesap"] = $anaHesap;

		$data["satis_olusturmaTarihi"] = $tarihi;

		$data["satis_olusturmaSaati"] = $saati;



		$cariKontrolQ = "SELECT * FROM cari WHERE (cari_kodu = '$cariKodu') AND cari_olusturanAnaHesap = '$anaHesap' ";

		$cariKontrol = $this->db->query($cariKontrolQ)->row();



		if (!$cariKontrol) {

			$this->session->set_flashdata('cari_hatali', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		$faturaVarmiQ = "SELECT * FROM satisFaturasi WHERE  (satis_irsaliyeNo = '$irsaliyeNo' OR satis_faturaNo = '$faturaNo') AND satis_olusturanAnaHesap = '$anaHesap'";

		$faturaVarmiExe = $this->db->query($faturaVarmiQ)->result();



		if ($faturaVarmiExe) {

			$this->session->set_flashdata('fatura_mukerrer', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		$this->vt->insert("satisFaturasi", $data);

		$satis_id = $this->db->insert_id();



		if ($satis_InvoiceType == 3){ //eirsaliye ise gerekli kayıt işlemleri yapmak

			$datairs["irs_faturaID"] = $satis_id;

			$datairs["irs_eirsaliyeTip"] = postval("satis_eirsaliyeTip");

			$datairs["irs_irsaliyeTarihi"] = postval("satis_irsaliyeTarihi");

			$datairs["irs_irsaliyeSaati"] = postval("satis_irsaliyeSaati");

			$datairs["irs_irsaliyeSevkTarihi"] = postval("satis_irsaliyeSevkTarihi");

			$datairs["irs_irsaliyeSevkSaati"] = postval("satis_irsaliyeSevkSaati");

			$datairs["irs_irsaliyeSiparisNo"] = postval("satis_irsaliyeSiparisNo");

			$datairs["irs_irsaliyeSoforAd"] = postval("satis_irsaliyeSoforAd");

			$datairs["irs_irsaliyeDorsePlaka"] = postval("satis_irsaliyeDorsePlaka");

			$datairs["irs_irsaliyeSiparisTarihi"] = postval("satis_irsaliyeSiparisTarihi");

			$datairs["irs_irsaliyeSoforTCKN"] = postval("satis_irsaliyeSoforTCKN");

			$datairs["irs_irsaliyeTasiyiciUnvan"] = postval("satis_irsaliyeTasiyiciUnvan");

			$datairs["irs_irsaliyeTasiyiciVKNTCKN"] = postval("satis_irsaliyeTasiyiciVKNTCKN");

			$datairs["irs_irsaliyePlaka"] = postval("satis_irsaliyePlaka");

			$datairs["sb_ulke"] = postval("sb_ulke");

			$datairs["sb_sehir"] = postval("sb_sehir");

			$datairs["sb_ilce"] = postval("sb_ilce");

			$datairs["sb_pk"] = postval("sb_pk");

			$datairs["sb_adres"] = postval("sb_adres");

			$datairs["asb_vkntckn"] = postval("asb_vkntckn");

			$datairs["asb_unvan"] = postval("asb_unvan");

			$datairs["asb_mno"] = postval("asb_mno");

			$datairs["asb_tel"] = postval("asb_tel");

			$datairs["asb_eposta"] = postval("asb_eposta");

			$datairs["asb_ulke"] = postval("asb_ulke");

			$datairs["asb_il"] = postval("asb_il");

			$datairs["asb_ilce"] = postval("asb_ilce");

			$datairs["asb_adres"] = postval("asb_adres");

			$datairs["irs_olusturan"] = $u_id;

			$datairs["irs_olusturanAnaHesap"] = $anaHesap;

			$datairs["irs_olusturmaTarihi"] = $tarihi;

			$datairs["irs_olusturmaSaati"] = $saati;



			$this->vt->insert("irsaliye_bilgileri", $datairs);

		}



		$bankaCheck = postval("bankaCheck");

		if ($bankaCheck == 1) {

			$bankaCount = postval("bankaCount");

			echo $bankaCount;

			if ($bankaCount != 0) {//banka bilgileri

				$ayarlarbanka_id = postval("ayarlarbanka_id");

				$dataBanka["faturabanka_faturaID"] = $satis_id;

				for ($i = 0; $i < $bankaCount; $i++) {

					$dataBanka["faturabanka_ayarbankaID"] = $ayarlarbanka_id[$i];

					$this->vt->insert("faturabanka_bilgileri", $dataBanka);

				}

			}

		}



		$iade_count = postval("iade_count");

		if ($iade_count != 0) {//arşiv iade



			$iadenumara = postval("iadenumara");

			$iadetarih = postval("iadetarih");

			$dataIade["satis_id"] = $satis_id;

			//iade_fatura_numaralari (e-arşiv iade fatura numaraları)

			for ($i = 0; $i < $iade_count; $i++) {

				$dataIade["fatura_numarasi"] = $iadenumara[$i];

				$dataIade["tarih"] = $iadetarih[$i];



				$this->vt->insert("iade_fatura_numaralari", $dataIade);



			}

		}



		$indirim_count = postval("indirim_count");

		if ($indirim_count != 0) {//genel iskonto



			$indirimsebep = postval("indirimsebep");

			$indirimtutar = postval("indirimtutar");

			$dataIndirim["iskonto_satis_id"] = $satis_id;

			//genel_iskonto (faturaya yapılan genel indirimler)

			for ($i = 0; $i < $indirim_count; $i++) {

				if ($indirimsebep[$i] == 0)

					$dataIndirim["iskonto_sebep"] = "İskonto";

				else if ($indirimsebep[$i] == 1)

					$dataIndirim["iskonto_sebep"] = "Nakit İskontosu";

				else if ($indirimsebep[$i] == 2)

					$dataIndirim["iskonto_sebep"] = "Diğer";



				$dataIndirim["iskonto_tutar"] = $indirimtutar[$i];



				$this->vt->insert("genel_iskonto", $dataIndirim);



			}

		}



		$satis_earsivGonderimSekli = postval("satis_earsivGonderimSekli");

		$satis_earsivTipi = postval("satis_earsivTipi");

		if (($satis_earsivTipi == 1 && $satis_earsivGonderimSekli == 2) || postval("epostaCheck") == "1") {

			$dataInternet["satis_id"] = $satis_id;

			$dataInternet["earsiv_eposta"] = postval("eposta_bilgi");

			$this->vt->insert("earsiv_internet_bilgileri", $dataInternet);

		} else if ($satis_earsivTipi == 2) {

			$dataInternet["satis_id"] = $satis_id;

			$dataInternet["earsiv_eposta"] = postval("eposta_bilgi");

			$dataInternet["earsiv_odemesekli"] = postval("satis_odemesekli");

			$dataInternet["earsiv_odemearaci"] = postval("satis_odemearaci");

			$dataInternet["earsiv_odemetarihi"] = postval("satis_odemetarihi");

			$dataInternet["earsiv_internetsatisbilgisi"] = postval("satis_internetsatisbilgisi");

			$dataInternet["earsiv_tasiyan"] = postval("satis_tasiyan");

			$dataInternet["earsiv_tasiyanvkntckn"] = postval("satis_tasiyanvkntckn");

			$dataInternet["earsiv_tasiyanunvan"] = postval("satis_tasiyanunvan");

			$dataInternet["earsiv_tasiyantarih"] = postval("satis_tasiyantarih");

			$dataInternet["earsiv_olusturma_tarih"] = $tarihi;

			$dataInternet["earsiv_olusturma_saat"] = $saati;

			$this->vt->insert("earsiv_internet_bilgileri", $dataInternet);

		}



		//Satış Sözleşmesi stok

		$dataStok["satisStok_satisFaturasiID"] = $satis_id;

		$stokid = postval("stokid");

		$stokadi=postval("stokadi");

		$stokkodu=postval("stokkodu");

		$barkod=postval("barkod");

		$birim=postval("birim");



		$kacAdetStok = count($stokid);



		$miktar = postval("miktar");

		$birimfiyat = postval("birimfiyat");

		$kdv = postval("kdv");

		$toplam = postval("toplamHidden");



		$indirimtutari = postval("indirimyuzde");

		$kullanici_sayisi = postval("kullanici_sayisi");

		$tevkifatInput = postval("tevkifat");

		for ($i = 0; $i < $kacAdetStok; $i++) {



			$stokidsi=$stokid[$i];

			if(empty($stokadi[$i]) || trim($stokadi[$i]) == ""){
				$this->session->set_flashdata('fatura_stok_bos', 'OK');
				redirect($_SERVER['HTTP_REFERER']);
			}
			
			// E�er stokid bo�sa, yeni stok olu�tur
			if(empty($stokidsi)) {
				$dataStokX["stok_kodu"] = $stokkodu[$i];
				$dataStokX["stok_ad"] = $stokadi[$i];
				$dataStokX["stok_barkod"] = isset($barkod[$i]) ? $barkod[$i] : '';
				$dataStokX["stok_satisFiyati"] = $birimfiyat[$i];
				$dataStokX["stok_alisFiyati"] = 0;
				$dataStokX["stok_satisKDV"] = $kdv[$i];
				$dataStokX["stok_alisKDV"] = 0;
				$dataStokX["stok_birimID"] = $birim[$i];
				$dataStokX["stok_olusturan"] = $u_id;
				$dataStokX["stok_olusturanAnaHesap"] = $anaHesap;
				$dataStokX["stok_olusturmaTarihi"] = $tarihi;
				$dataStokX["stok_olusturmaSaati"] = $saati;

				$this->vt->insert("stok", $dataStokX);
				$stokidsi = $this->db->insert_id();
			}



			if ($kdv[$i] == 0) {

				$yeniKDV = 0;

			} else if ($kdv[$i] == 1) {

				$yeniKDV = 0.01;

			} else if ($kdv[$i] == 8) {

				$yeniKDV = 0.08;

			} else if ($kdv[$i] == 18) {

				$yeniKDV = 0.18;

			}



			$yeniKDVHesap = $toplam[$i] * $yeniKDV;

			$yeniKDVHesap2 = $toplam[$i] + $yeniKDVHesap;





		$tevkifat = explode("|", $tevkifatInput[$i]);



		$dataStok["satisStok_stokID"] = $stokidsi;

		$dataStok["satisStok_miktar"] = $miktar[$i];

		$dataStok["satisStok_birimFiyat"] = $birimfiyat[$i];

		$dataStok["satisStok_fiyatMiktar"] = $toplam[$i]; // KDV dahil toplam

		$dataStok["satisStok_kdv"] = $kdv[$i];

		$dataStok["satisStok_indirimTutari"] = $indirimtutari[$i];			$dataStok["satisStok_satirIskonto"] = $birimfiyat[$i] * $miktar[$i] * $indirimtutari[$i] / 100;
			
			// satisStok_indirimlifiyat her zaman KDV dahil olmalı
			$kdvOrani = $kdv[$i] / 100;
			$toplamKdvDahil = $toplam[$i] * (1 + $kdvOrani);
			$dataStok["satisStok_indirimlifiyat"] = $toplamKdvDahil;

		$dataStok["satisStok_kullanici_sayisi"] = isset($kullanici_sayisi[$i]) ? $kullanici_sayisi[$i] : null;



			if ($tevkifat[0] == 0)

				$dataStok["satisStok_tevkifat_id"] = 0;

			else

				$dataStok["satisStok_tevkifat_id"] = $tevkifat[1];





			$this->vt->insert("satisFaturasiStok", $dataStok);



			$paraBirimiTxt="TL";

			if($paraBirimi==2)

				$paraBirimiTxt="USD";

			else if($paraBirimi==3)

				$paraBirimiTxt="EUR";

			else if($paraBirimi==4)

				$paraBirimiTxt="GBP";



			$data_sh["sh_turu"] = 2;

			$data_sh["sh_cikis"] = $miktar[$i];

			$data_sh["sh_cariID"] = postval("satis_cariID");

			$data_sh["sh_stokID"] = $stokidsi;

			$data_sh["sh_faturaID"] = $satis_id;

			$data_sh["sh_belgeNumarasi"] = $faturaNo;

			$data_sh["sh_paraBirimi"] = $paraBirimiTxt;

			$data_sh["sh_tarih"] = $tarihi;

			$data_sh["sh_birimFiyat"] = $birimfiyat[$i];

			$data_sh["sh_kdv"] = $kdv[$i];

			$data_sh["sh_toplamKDV"] = $yeniKDVHesap;

			$data_sh["sh_toplamFiyat"] = $yeniKDVHesap2;

			$data_sh["sh_olusturan"] = $u_id;

			$data_sh["sh_olusturanAnaHesap"] = $anaHesap;

			$data_sh["sh_olusturmaTarihi"] = $tarihi;

			$data_sh["sh_olusturmaSaati"] = $saati;



			$this->vt->insert("stokHareketleri", $data_sh);

		}



		//Satış Sözleşmesi stok



		//Cari Hareketleri Ekle :begin

		$data_ch["ch_belgeNumarasi"] = $faturaNo;

		$data_ch["ch_turu"] = $ch_turu;

		$data_ch["ch_cariID"] = postval("satis_cariID");

		$data_ch["ch_faturaID"] = $satis_id;

		$data_ch["ch_paraBirimi"] = $paraBirimiTxt;

		/*if($dovizCevrilmis)

			$data_ch["ch_borc"] =$dovizCevrilmis;

		else*/

		$data_ch["ch_borc"] = postval("geneltopHidden");

		$data_ch["ch_tarih"] = $yeni_satis_faturaTarihi;



		$data_ch["ch_olusturan"] = $u_id;

		$data_ch["ch_olusturanAnaHesap"] = $anaHesap;

		$data_ch["ch_olusturmaTarihi"] = $tarihi;

		$data_ch["ch_olusturmaSaati"] = $saati;



		$this->vt->insert("cariHareketleri", $data_ch);

		$ch_id = $this->db->insert_id();

		$datachu["satis_cariHareketiID"] = $ch_id;



		$this->vt->update('satisFaturasi', array('satis_id' => $satis_id), $datachu);

		//Cari Hareketleri Ekle :end



		// Dosya yükleme işlemi - Manual upload without CodeIgniter Upload library
		if (!empty($_FILES['fatura_dosya']['name'][0])) {
			$upload_path = './assets/uploads/faturalar/';
			$allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'xlsx', 'xls');
			$max_size = 10240000; // 10MB in bytes
			
			// Upload dizinini kontrol et ve yoksa oluştur
			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0755, true);
			}
			
			$uploaded_files = array();
			$upload_errors = array();
			
			// Çoklu dosya yükleme
			$files_count = count($_FILES['fatura_dosya']['name']);
			for ($i = 0; $i < $files_count; $i++) {
				if (!empty($_FILES['fatura_dosya']['name'][$i])) {
					$file_name = $_FILES['fatura_dosya']['name'][$i];
					$file_tmp = $_FILES['fatura_dosya']['tmp_name'][$i];
					$file_size = $_FILES['fatura_dosya']['size'][$i];
					$file_error = $_FILES['fatura_dosya']['error'][$i];
					
					if ($file_error == 0 && $file_size <= $max_size) {
						$file_info = pathinfo($file_name);
						$file_ext = strtolower($file_info['extension']);
						
						// Dosya türü kontrolü
						if (in_array($file_ext, $allowed_types)) {
							// Güvenli dosya adı oluştur
							$encrypted_name = md5(uniqid(rand(), true)) . '.' . $file_ext;
							$upload_file = $upload_path . $encrypted_name;
							
							if (move_uploaded_file($file_tmp, $upload_file)) {
								$uploaded_files[] = 'faturalar/' . $encrypted_name;
							} else {
								$upload_errors[] = 'Dosya yüklenirken hata oluştu: ' . $file_name;
							}
						} else {
							$upload_errors[] = 'Geçersiz dosya türü: ' . $file_name;
						}
					} else {
						$upload_errors[] = 'Dosya boyutu çok büyük veya hata var: ' . $file_name;
					}
				}
			}
			
			if (!empty($uploaded_files)) {
				$data_update['satis_dosya'] = implode(',', $uploaded_files);
				$this->vt->update('satisFaturasi', array('satis_id' => $satis_id), $data_update);
			}
			
			// Hata varsa flash mesaj olarak kaydet
			if (!empty($upload_errors)) {
				$this->session->set_flashdata('file_upload_error', implode('<br>', $upload_errors));
			}
		}



		$this->session->set_flashdata('fatura_ok', 'OK');

		logekle(11, 2);

		
		// AJAX isteği kontrolü
		if ($this->input->is_ajax_request()) {
			// AJAX isteği ise JSON response döndür
			echo json_encode(array(
				'success' => true,
				'message' => 'Satış sözleşmesi başarıyla oluşturuldu.',
				'satis_id' => $satis_id
			));
			return;
		}
		
		//redirect("fatura/satis-faturasi-duzenle/$satis_id");

		if ($satis_InvoiceType == 2) {

			redirect("fatura/earsiv-faturalari");

		} else if ($satis_InvoiceType == 1) {

			redirect("fatura/efaturaGiden");

		} else if($satis_InvoiceType == 3){

			redirect("fatura/eirsaliyeGiden");

		}else {

			redirect("fatura/satis-faturasi-listesi");

		}

	}



	public function satisFaturasiDuzenle($id)

	{

		$data["baslik"] = "Fatura / Satış Sözleşmesi Düzenle";

		

		// Dashboard'daki "Son Satışlarım" mantığı: Kendisi + bağlı kullanıcılar

		$control = session("r", "login_info");

		$u_id = $control->kullanici_id;

		

		$allowed_user_ids = array($u_id);

		if ($u_id > 0) {

			$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";

			$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();

			

			foreach ($bağlı_kullanicilar as $user) {

				$allowed_user_ids[] = $user->kullanici_id;

			}

		}

		$allowed_user_ids_str = implode(',', $allowed_user_ids);



		$satisFaturasiQ = "SELECT * FROM satisFaturasi WHERE satis_id = '$id' AND satis_olusturan IN ($allowed_user_ids_str)";

		$satisFaturasiStokQ = "SELECT * FROM satisFaturasiStok WHERE satisStok_satisFaturasiID = '$id'";



		$data["satisFaturasi"] = $this->db->query($satisFaturasiQ)->row();



		if (!$data["satisFaturasi"]) {

			redirect('hata');

			return;

		}



		$cariID = $data["satisFaturasi"]->satis_cariID;



		$cariBilgileriQ = "SELECT * FROM cari WHERE cari_id = '$cariID' AND cari_olusturan IN ($allowed_user_ids_str)";



		$data["cariBilgileri"] = $this->db->query($cariBilgileriQ)->row();



		if ($data["cariBilgileri"]) {

			$cariGrubuID = $data["cariBilgileri"]->cari_cariGrupKoduID;

			$cariGrubuQ = "SELECT * FROM cariGruplari WHERE cariGrup_id = '$cariGrubuID'";

			$data["cariGrubuBilgileri"] = $this->db->query($cariGrubuQ)->row();

		}

		

		$data["satisFaturasiStok"] = $this->db->query($satisFaturasiStokQ)->result();



		$this->load->view("fatura/satis-faturasi-duzenle", $data);

	}



	public function satisFaturasiGuncelle()

	{



		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$satis_InvoiceType = postval("satis_InvoiceType");



		$satis_irsaliyeTarihi = postval("satis_irsaliyeTarihi");

		$satis_faturaTarihi = postval("satis_faturaTarihi");



		$yeni_satis_irsaliyeTarihi = date("Y-m-d", strtotime($satis_irsaliyeTarihi));

		$yeni_satis_faturaTarihi = date("Y-m-d", strtotime($satis_faturaTarihi));



		$satis_id = postval("satis_id");



		$irsaliyeNo = postval("satis_irsaliyeNo");

		$faturaNo = postval("satis_faturaNo");



		$cariKodu = postval("cari_kodu");

		$cariAdi = postval("cari_adi");



		$genelToplamHidden = postval("geneltopHidden");

		$indirimToplamHidden = postval("indirimtopHidden");

		$tevkifatToplamHidden = postval("tevkifattopHidden");

		$vergidahilHidden = postval("vergidahilHidden");



		$istisna_sebebi = postval("istisna_sebebi");

		$vergiMuafiyet_sebep = postval("vergiMuafiyet_sebep");



		if (postval("satis_paraBirimi") == 1) {

			$guncelKur = NULL;

		} else {

			$guncelKur = postval("satis_guncelKur");

			$dovizCevrilmis = $genelToplamHidden * $guncelKur;

		}



		$satis_etiketID=postval("etiket_id");

		if (empty($satis_etiketID)) {

			$satis_faturaEtiketID = postval("satis_faturaEtiketID");

			if (!empty($satis_faturaEtiketID)) {

				$dataEtiket["etiket_adi"] = $satis_faturaEtiketID;

				$dataEtiket["etiket_olusturan"] = $u_id;

				$dataEtiket["etiket_olusturanAnaHesap"] = $anaHesap;

				$dataEtiket["etiket_olusturmaTarihi"] = $tarihi;

				$dataEtiket["etiket_olusturmaSaati"] = $saati;

				$this->db->insert("etiketler", $dataEtiket);

				$satis_etiketID = $this->db->insert_id();

			}

		}

		$data["satis_faturaEtiketID"] = $satis_etiketID;



		$data["satis_InvoiceType"] = $satis_InvoiceType;



		if ($satis_InvoiceType == 2) {//arşiv

			$data["satis_faturaDurum"] = 1;

			$data["satis_earsivfaturaTip"] = postval("satis_earsivfaturaTip");

			$data["satis_earsivTipi"] = postval("satis_earsivTipi");

			$data["satis_earsivGonderimSekli"] = postval("satis_earsivGonderimSekli");

		} else if ($satis_InvoiceType == 1)//e-fatura

		{

			$data["satis_faturaDurum"] = 1;

			$data["satis_faturaSenaryo"] = postval("satis_faturaSenaryo");

			$data["satis_earsivfaturaTip"] = postval("satis_efaturaTip");



		} else //Satış Sözleşmesi

		{

			$data["satis_faturaDurum"] = 2;

			$data["satis_earsivfaturaTip"] = postval("satis_faturaTip");



		}



		$paraBirimi=postval("satis_paraBirimi");



		$data["satis_cariID"] = postval("satis_cariID");



		$data["satis_paraBirimi"] = $paraBirimi;

		$data["satis_guncelKur"] = $guncelKur;

		$data["satis_dovizCevrilmis"] = $dovizCevrilmis;

		$data["satis_irsaliyeNo"] = $irsaliyeNo != '' ? $irsaliyeNo : NULL;

		$data["satis_irsaliyeTarihi"] = $satis_irsaliyeTarihi != '' ? $yeni_satis_irsaliyeTarihi : NULL;

		$data["satis_faturaNo"] = $faturaNo;

		$data["satis_faturaTarihi"] = $yeni_satis_faturaTarihi;

		$data["satis_faturaSaati"] = postval("satis_faturaSaati");

		$data["satis_aciklama"] = postval("satis_aciklama");

		$data["satis_araToplam"] = postval("aratopHidden");

		$data["satis_kdvToplam"] = postval("kdvtopHidden");

		$data["satis_genelToplam"] = $genelToplamHidden;

		$data["satis_vergiDahilToplam"] = $vergidahilHidden;

		$data["satis_indirimToplam"] = $indirimToplamHidden;

		$data["satis_netTutar"] = postval("netTutarInput");



		if ($tevkifatToplamHidden != null)

			$data["satis_tevkifatToplam"] = $tevkifatToplamHidden;

		if ($istisna_sebebi != null)

			$data["satis_istisna_id"] = $istisna_sebebi;





		if ($vergiMuafiyet_sebep != null)

			$data["satis_vergiMuafiyetSebep"] = $vergiMuafiyet_sebep;



		$data["satis_olusturan"] = $u_id;

		$data["satis_olusturanAnaHesap"] = $anaHesap;

		$data["satis_olusturmaTarihi"] = $tarihi;

		$data["satis_olusturmaSaati"] = $saati;



		$cariKontrolQ = "SELECT * FROM cari WHERE (cari_kodu = '$cariKodu') AND cari_olusturanAnaHesap = '$anaHesap'";

		$cariKontrol = $this->db->query($cariKontrolQ)->row();



		if (!$cariKontrol) {

			$this->session->set_flashdata('cari_hatali', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		$faturaVarmiQ = "SELECT * FROM satisFaturasi WHERE (satis_irsaliyeNo = '$irsaliyeNo' OR satis_faturaNo = '$faturaNo') AND satis_olusturanAnaHesap = '$anaHesap' AND satis_id = $satis_id";

		$faturaVarmiExe = $this->db->query($faturaVarmiQ)->row();





		/*if ($faturaVarmiExe) {

			$this->session->set_flashdata('fatura_mukerrer', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}

*/

		//if ($faturaVarmiExe)

			$this->vt->update('satisFaturasi', array('satis_id' => $satis_id), $data);



		//iade

		$iade_count = postval("iade_count");



		$this->vt->del("iade_fatura_numaralari", "satis_id", $satis_id);

		if ($iade_count != 0) {// iade

			$iadenumara = postval("iadenumara");

			$iadetarih = postval("iadetarih");

			$dataIade["satis_id"] = $satis_id;

			//iade_fatura_numaralari (e-arşiv iade fatura numaraları)

			for ($i = 0; $i < $iade_count; $i++) {

				$dataIade["fatura_numarasi"] = $iadenumara[$i];

				$dataIade["tarih"] = $iadetarih[$i];



				$this->vt->insert("iade_fatura_numaralari", $dataIade);



			}

		}

		//indirim

		$indirim_count = postval("indirim_count");

		$this->vt->del("genel_iskonto", "iskonto_satis_id", $satis_id);

		if ($indirim_count != 0) {//genel iskonto

			$indirimsebep = postval("indirimsebep");

			$indirimtutar = postval("indirimtutar");

			$dataIndirim["iskonto_satis_id"] = $satis_id;

			//genel_iskonto (faturaya yapılan genel indirimler)

			for ($i = 0; $i < $indirim_count; $i++) {

				if ($indirimsebep[$i] == 0)

					$dataIndirim["iskonto_sebep"] = "İskonto";

				else if ($indirimsebep[$i] == 1)

					$dataIndirim["iskonto_sebep"] = "Nakit İskontosu";

				else if ($indirimsebep[$i] == 2)

					$dataIndirim["iskonto_sebep"] = "Diğer";



				$dataIndirim["iskonto_tutar"] = $indirimtutar[$i];



				$this->vt->insert("genel_iskonto", $dataIndirim);



			}

		}



		$bankaCheck = postval("bankaCheck");

		$this->vt->del("faturabanka_bilgileri", "faturabanka_faturaID", $satis_id);

		if ($bankaCheck == 1) {

			$bankaCount = postval("bankaCount");

			echo $bankaCount;

			if ($bankaCount != 0) {//banka bilgileri

				$ayarlarbanka_id = postval("ayarlarbanka_id");

				$dataBanka["faturabanka_faturaID"] = $satis_id;

				for ($i = 0; $i < $bankaCount; $i++) {

					$dataBanka["faturabanka_ayarbankaID"] = $ayarlarbanka_id[$i];

					print_r($dataBanka);

					$this->vt->insert("faturabanka_bilgileri", $dataBanka);

				}

			}

		}





		//earşiv gönderim şekli

		$satis_earsivGonderimSekli = postval("satis_earsivGonderimSekli");

		$satis_earsivTipi = postval("satis_earsivTipi");

		$earsiv_internet = postval("earsiv_internet");

		print_r($earsiv_internet);



		if ($earsiv_internet)

			$this->vt->del("earsiv_internet_bilgileri", "earsiv_id", $earsiv_internet);

		if ($satis_earsivTipi == 1 && $satis_earsivGonderimSekli == 2) {

			$dataInternet["satis_id"] = $satis_id;

			$dataInternet["earsiv_eposta"] = postval("eposta_bilgi");

			$this->vt->insert("earsiv_internet_bilgileri", $dataInternet);

		} else if ($satis_earsivTipi == 2) {

			$dataInternet["satis_id"] = $satis_id;

			$dataInternet["earsiv_eposta"] = postval("eposta_bilgi");

			echo $dataInternet["earsiv_eposta"];

			$dataInternet["earsiv_odemesekli"] = postval("satis_odemesekli");

			$dataInternet["earsiv_odemearaci"] = postval("satis_odemearaci");

			$dataInternet["earsiv_odemetarihi"] = postval("satis_odemetarihi");

			$dataInternet["earsiv_internetsatisbilgisi"] = postval("satis_internetsatisbilgisi");

			$dataInternet["earsiv_tasiyan"] = postval("satis_tasiyan");

			$dataInternet["earsiv_tasiyanvkntckn"] = postval("satis_tasiyanvkntckn");

			$dataInternet["earsiv_tasiyanunvan"] = postval("satis_tasiyanunvan");

			$dataInternet["earsiv_tasiyantarih"] = postval("satis_tasiyantarih");

			$dataInternet["earsiv_olusturma_tarih"] = $tarihi;

			$dataInternet["earsiv_olusturma_saat"] = $saati;





			$this->vt->insert("earsiv_internet_bilgileri", $dataInternet);





		}





		//Satış Sözleşmesi stok

		$dataStok["satisStok_satisFaturasiID"] = $satis_id;

		$stokid = postval("stokid");

		$kacAdetStok = count($stokid);



		$satisFaturasiStok_id = postval("satisFaturasiStok_id");



		$stoklarQ = "SELECT * FROM satisFaturasiStok WHERE satisStok_satisFaturasiID = '$satis_id'";

		$stoklarExe = $this->db->query($stoklarQ)->result();



		$newArray = [];

		foreach ($stoklarExe as $sexe) {

			$newArray[] = $sexe->satisStok_id;

		}



		$diff = array_diff($newArray, $satisFaturasiStok_id);



		if (!empty($diff)) {

			foreach ($diff as $key => $value) {

				$this->vt->del("satisFaturasiStok", "satisStok_id", $value);

			}

		}

		$miktar = postval("miktar");

		$birimfiyat = postval("birimfiyat");

		$kdv = postval("kdv");

		$stokadi = postval("stokadi");

		$stokkodu = postval("stokkodu");

		$barkod= postval("barkod");

		$birim= postval("birim");

		$toplam = postval("toplamHidden");

		$indirimtutari = postval("indirimyuzde");

		$kullanici_sayisi = postval("kullanici_sayisi");

		$tevkifatInput = postval("tevkifat");



		$this->vt->del("stokHareketleri", "sh_faturaID", $satis_id);



		for ($i = 0; $i < $kacAdetStok; $i++) {

			$stokidsi=$stokid[$i];

			if(empty($stokid[$i])) {

				$dataStokX["stok_kodu"] = $stokkodu[$i];

				$stokQ = "select * from stok where stok_kodu='" . $dataStokX["stok_kodu"] . "'";

				$stokExe = $this->db->query($stokQ)->row();

				if ($stokExe) {

					$stokidsi = $stokExe->stok_id;

				} else {

					$dataStokX["stok_ad"] = $stokadi[$i];

					$dataStokX["stok_barkod"] = $barkod[$i];

					$dataStokX["stok_satisFiyati"] = $birimfiyat[$i];

					$dataStokX["stok_alisFiyati"] = 0;

					$dataStokX["stok_satisKDV"] = $kdv[$i];

					$dataStokX["stok_alisKDV"] = 0;

					$dataStokX["stok_birimID"] = $birim[$i];

					$dataStokX["stok_olusturan"] = $u_id;

					$dataStokX["stok_olusturanAnaHesap"] = $anaHesap;

					$dataStokX["stok_olusturmaTarihi"] = $tarihi;

					$dataStokX["stok_olusturmaSaati"] = $saati;



					$this->vt->insert("stok", $dataStokX);

					$stokidsi = $this->db->insert_id();



				}

			}





			if ($kdv[$i] == 0) {

				$yeniKDV = 0;

			} else if ($kdv[$i] == 1) {

				$yeniKDV = 0.01;

			} else if ($kdv[$i] == 8) {

				$yeniKDV = 0.08;

			} else if ($kdv[$i] == 18) {

				$yeniKDV = 0.18;

			}



			$yeniKDVHesap = $toplam[$i] * $yeniKDV;

			$yeniKDVHesap2 = $toplam[$i] + $yeniKDVHesap;



		$tevkifat = explode("|", $tevkifatInput[$i]);

		$dataStok["satisStok_stokID"] = $stokidsi;

		$dataStok["satisStok_miktar"] = $miktar[$i];

		$dataStok["satisStok_birimFiyat"] = $birimfiyat[$i];

		$dataStok["satisStok_fiyatMiktar"] = $toplam[$i]; // KDV dahil toplam

		$dataStok["satisStok_kdv"] = $kdv[$i];

		$dataStok["satisStok_indirimTutari"] = $indirimtutari[$i];			$dataStok["satisStok_satirIskonto"] = $birimfiyat[$i] * $miktar[$i] * $indirimtutari[$i] / 100;
			
			// satisStok_indirimlifiyat her zaman KDV dahil olmalı
			$kdvOrani = $kdv[$i] / 100;
			$toplamKdvDahil = $toplam[$i] * (1 + $kdvOrani);
			$dataStok["satisStok_indirimlifiyat"] = $toplamKdvDahil;

		$dataStok["satisStok_kullanici_sayisi"] = isset($kullanici_sayisi[$i]) ? $kullanici_sayisi[$i] : null;



			if ($tevkifat[0] == 0)

				$dataStok["satisStok_tevkifat_id"] = 0;

			else

				$dataStok["satisStok_tevkifat_id"] = $tevkifat[1];



			if (!empty($satisFaturasiStok_id[$i])) {



				$this->vt->update('satisFaturasiStok', array('satisStok_id' => $satisFaturasiStok_id[$i]), $dataStok);

			} else {



				$this->vt->insert("satisFaturasiStok", $dataStok);

			}

			$paraBirimiTxt="TL";

			if($paraBirimi==2)

				$paraBirimiTxt="USD";

			else if($paraBirimi==3)

				$paraBirimiTxt="EUR";

			else if($paraBirimi==4)

				$paraBirimiTxt="GBP";



			//stok hareketleri güncelle

			$data_sh["sh_turu"] = 2;

			$data_sh["sh_cikis"] = $miktar[$i];

			$data_sh["sh_cariID"] = postval("satis_cariID");

			$data_sh["sh_stokID"] = $stokidsi;

			$data_sh["sh_faturaID"] = $satis_id;

			$data_sh["sh_belgeNumarasi"] = $faturaNo;

			$data_sh["sh_paraBirimi"]=$paraBirimiTxt;

			$data_sh["sh_tarih"] = $tarihi;

			$data_sh["sh_birimFiyat"] = $birimfiyat[$i];

			$data_sh["sh_kdv"] = $kdv[$i];

			$data_sh["sh_toplamKDV"] = $yeniKDVHesap;

			$data_sh["sh_toplamFiyat"] = $yeniKDVHesap2;

			$data_sh["sh_olusturan"] = $u_id;

			$data_sh["sh_olusturanAnaHesap"] = $anaHesap;

			$data_sh["sh_olusturmaTarihi"] = $tarihi;

			$data_sh["sh_olusturmaSaati"] = $saati;



			$this->vt->insert("stokHareketleri", $data_sh);

			//stok hareketleri güncelle





		}



		//Satış Sözleşmesi stok



		$kh_id = postval("kh_id");

		$bh_id = postval("bh_id");



		//Cari Hareketleri Düzenle :begin

		$ch_idsi = postval("ch_id");

		$data_ch["ch_belgeNumarasi"] = $faturaNo;

		$data_ch["ch_cariID"] = postval("satis_cariID");

		$data_ch["ch_faturaID"] = $satis_id;

		$data_ch["ch_paraBirimi"]=$paraBirimiTxt;

		/*if($dovizCevrilmis)

			$data_ch["ch_borc"] =$dovizCevrilmis;

		else*/

			$data_ch["ch_borc"] = postval("geneltopHidden");

		$data_ch["ch_tarih"] = $yeni_satis_faturaTarihi;



		$data_ch["ch_olusturan"] = $u_id;

		$data_ch["ch_olusturanAnaHesap"] = $anaHesap;

		$data_ch["ch_olusturmaTarihi"] = $tarihi;

		$data_ch["ch_olusturmaSaati"] = $saati;



		$this->vt->update('cariHareketleri', array('ch_id' => $ch_idsi), $data_ch);

		//Cari Hareketleri Düzenle :end



		// Dosya yükleme işlemi

		if (!empty($_FILES['fatura_dosya']['name'][0])) {

			// Debug log

			$debug_log = "=== DOSYA YÜKLEME DEBUG ===\n";

			$debug_log .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";

			$debug_log .= "Satis ID: " . $satis_id . "\n";

			$debug_log .= "FILES: " . print_r($_FILES['fatura_dosya'], true) . "\n";

			file_put_contents('./debug_file_upload.log', $debug_log, FILE_APPEND);

			

			$upload_path = './assets/uploads/faturalar/';

			

			// Upload dizinini kontrol et ve yoksa oluştur

			if (!is_dir($upload_path)) {

				mkdir($upload_path, 0755, true);

			}

			

			$uploaded_files = array();

			$upload_errors = array();

			

			// Mevcut dosyaları al

			$existing_files = array();

			$current_satis = $this->db->query("SELECT satis_dosya FROM satisFaturasi WHERE satis_id = '$satis_id'")->row();

			if ($current_satis && !empty($current_satis->satis_dosya)) {

				$existing_files = explode(',', $current_satis->satis_dosya);

			}

			

			$config['upload_path'] = $upload_path;

			$config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png|gif|txt|xlsx|xls';

			$config['max_size'] = 10240; // 10MB

			$config['encrypt_name'] = TRUE;

			

			$this->load->library('upload', $config);

			

			// Çoklu dosya yükleme

			$files_count = count($_FILES['fatura_dosya']['name']);

			for ($i = 0; $i < $files_count; $i++) {

				if (!empty($_FILES['fatura_dosya']['name'][$i])) {

					$_FILES['single_file']['name'] = $_FILES['fatura_dosya']['name'][$i];

					$_FILES['single_file']['type'] = $_FILES['fatura_dosya']['type'][$i];

					$_FILES['single_file']['tmp_name'] = $_FILES['fatura_dosya']['tmp_name'][$i];

					$_FILES['single_file']['error'] = $_FILES['fatura_dosya']['error'][$i];

					$_FILES['single_file']['size'] = $_FILES['fatura_dosya']['size'][$i];

					

					if ($this->upload->do_upload('single_file')) {

						$upload_data = $this->upload->data();

						$uploaded_files[] = 'faturalar/' . $upload_data['file_name'];

						file_put_contents('./debug_file_upload.log', "Dosya yüklendi: " . $upload_data['file_name'] . "\n", FILE_APPEND);

					} else {

						$upload_errors[] = $this->upload->display_errors();

						file_put_contents('./debug_file_upload.log', "Upload hatası: " . $this->upload->display_errors() . "\n", FILE_APPEND);

					}

				}

			}

			

			// Yeni dosyaları mevcut dosyalarla birleştir

			$all_files = array_merge($existing_files, $uploaded_files);

			$all_files = array_filter($all_files); // Boş değerleri temizle

			

			if (!empty($all_files)) {

				$data_update['satis_dosya'] = implode(',', $all_files);

				$this->vt->update('satisFaturasi', array('satis_id' => $satis_id), $data_update);

				file_put_contents('./debug_file_upload.log', "Veritabanı güncellendi: " . implode(',', $all_files) . "\n", FILE_APPEND);

			}

			

			// Hata varsa flash mesaj olarak kaydet

			if (!empty($upload_errors)) {

				$this->session->set_flashdata('file_upload_error', implode('<br>', $upload_errors));

			}

		} else {

			file_put_contents('./debug_file_upload.log', "Hiç dosya seçilmemiş: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

		}





		$this->session->set_flashdata('fatura_updt_ok', 'OK');

		logekle(11, 3);

		redirect($_SERVER['HTTP_REFERER']);

	}

	public function satisFaturasiListesi()

	{

		$data["baslik"] = "Fatura / Satış Sözleşmesi Listesi";



		// Dashboard'daki "Son Satışlarım" mantığı: Kendisi + bağlı kullanıcılar

		$control = session("r", "login_info");

		$u_id = $control->kullanici_id;

		

		$allowed_user_ids = array($u_id);

		if ($u_id > 0) {

			$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";

			$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();

			

			foreach ($bağlı_kullanicilar as $user) {

				$allowed_user_ids[] = $user->kullanici_id;

			}

		}

		$allowed_user_ids_str = implode(',', $allowed_user_ids);



		$faturaNo = $this->input->get('faturaNo');

		//$irsaliyeNo = $this->input->get('irsaliyeNo');

		//$cariAdi = $this->input->get('cariAdi');

		$tarihGet = $this->input->get('tarihAraligi');

		$etiket = $this->input->get('etiket');

		$tarihAraligi = explode(' - ', $tarihGet);



		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



		$siralama = $this->input->get('siralama');

		

		$ayArama = $this->input->get('ayArama');



		$urim = $this->uri->segment(2);



		$segment = 2;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if ($sayfa) {

			$pagem = ($page - 1) * $limit;

		} else {

			$pagem = 0;

		}



		if ((isset($faturaNo) && !empty($faturaNo)) || (isset($etiket) && !empty($etiket)) || (isset($tarihGet) && !empty($tarihGet)) || (isset($siralama) && !empty($siralama)) || (isset($ayArama) && !empty($ayArama))) {



			/*if(!empty($cariGrubu)){$sorgu1 = "AND cari_cariGrupKoduID = '$cariGrubu'";}

			else{$sorgu1 = "";}*/



			if (!empty($tarihGet)) {

				$sorgu1 = "AND satis_faturaTarihi BETWEEN '$tarih1' AND '$tarih2'";

			} else {

				$sorgu1 = "";

			}



			/*if (!empty($irsaliyeNo)) {

				$sorgu2 = "AND satis_irsaliyeNo LIKE '%$irsaliyeNo%'";

			} else {

				$sorgu2 = "";

			}*/

			if (!empty($etiket)) {

				$sorgu2 = "AND sf.satis_faturaEtiketID = '$etiket'";

			} else {

				$sorgu2 = "";

			}



			if (!empty($siralama)) {

				if ($siralama == 1) {//Tarih büyükten küçüğe

					$sira = "ORDER BY DATE(satis_faturaTarihi) DESC";

				} else if ($siralama == 2) {//Tarih küçükten büyüğe

					$sira = "ORDER BY DATE(satis_faturaTarihi) ASC";

				} else {

					$sira = "ORDER BY satis_id DESC";

				}

			} else {

				$sira = "ORDER BY satis_id DESC";

			}

	        if (!empty($ayArama)) {

	            $sorgu3 = "AND month(sf.satis_faturaTarihi) = '$ayArama'";

	        }else $sorgu3="";

	        

			$countq = "SELECT COUNT(*) as total FROM satisFaturasi sf INNER JOIN cari c ON sf.satis_cariID = c.cari_id WHERE sf.satis_InvoiceType IS NULL AND c.cari_olusturan IN ($allowed_user_ids_str) AND sf.satis_faturaNo LIKE '%$faturaNo%' " . $sorgu2 . " " . $sorgu1 . " ".$sorgu3." ";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;



			$sorgu = "SELECT sf.* FROM satisFaturasi sf INNER JOIN cari c ON sf.satis_cariID = c.cari_id WHERE sf.satis_InvoiceType IS NULL AND c.cari_olusturan IN ($allowed_user_ids_str) AND sf.satis_faturaNo LIKE '%$faturaNo%' " . $sorgu2 . " " . $sorgu1 ." ". $sorgu3 ." " . $sira . " LIMIT $pagem,$limit";

		} else {

			$countq = "SELECT COUNT(*) as total FROM satisFaturasi sf INNER JOIN cari c ON sf.satis_cariID = c.cari_id WHERE sf.satis_InvoiceType IS NULL AND c.cari_olusturan IN ($allowed_user_ids_str)";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;

			$sorgu = "SELECT sf.* FROM satisFaturasi sf INNER JOIN cari c ON sf.satis_cariID = c.cari_id WHERE sf.satis_InvoiceType IS NULL AND c.cari_olusturan IN ($allowed_user_ids_str) ORDER BY sf.satis_id DESC LIMIT $pagem,$limit";

		}



		$data["count_of_list"] = $count;

		

		// DEBUG: Log sorgu bilgilerini

		$debug_file = FCPATH . 'debug_satis_fatura_listesi.log';

		$timestamp = date('Y-m-d H:i:s');

		file_put_contents($debug_file, "=== SATIŞ FATURA LİSTESİ DEBUG ===\n", FILE_APPEND);

		file_put_contents($debug_file, "Timestamp: $timestamp\n", FILE_APPEND);

		file_put_contents($debug_file, "User ID: $u_id\n", FILE_APPEND);

		file_put_contents($debug_file, "Allowed User IDs: " . implode(', ', $allowed_user_ids) . "\n", FILE_APPEND);

		file_put_contents($debug_file, "Kısıtlama: Dashboard mantığı - Kendisi + bağlı kullanıcılar (cari_olusturan IN ($allowed_user_ids_str))\n", FILE_APPEND);

		file_put_contents($debug_file, "Count Result: $count\n", FILE_APPEND);

		file_put_contents($debug_file, "Main Query: $sorgu\n", FILE_APPEND);

		file_put_contents($debug_file, "=== DEBUG END ===\n\n", FILE_APPEND);



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/fatura/$urim";

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

	$data["satisFaturasi"] = $this->db->query($sorgu)->result();

	// Her fatura için stok bilgilerini al
	$faturaStoklar = array();
	foreach ($data["satisFaturasi"] as $fatura) {
		$stokQ = "SELECT s.stok_id, s.stok_ad, s.stok_kodu 
				  FROM satisFaturasiStok sfs 
				  INNER JOIN stok s ON sfs.satisStok_stokID = s.stok_id 
				  WHERE sfs.satisStok_satisFaturasiID = '{$fatura->satis_id}'
				  LIMIT 1"; // İlk stoğu al
		$stokBilgi = $this->db->query($stokQ)->row();
		$faturaStoklar[$fatura->satis_id] = $stokBilgi;
	}
	$data["faturaStoklar"] = $faturaStoklar;

	$this->load->view("fatura/satis-faturasi-listesi", $data);	}



	public function eArsivFaturalari()

	{

		$data["baslik"] = "Fatura / E-Arşiv Faturalari";



		$anaHesap = anaHesapBilgisi();



		$faturaNo = $this->input->get('faturaNo');

		$irsaliyeNo = $this->input->get('irsaliyeNo');

		//$cariAdi = $this->input->get('cariAdi');

		$tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



		$siralama = $this->input->get('siralama');



		$urim = $this->uri->segment(2);



		$segment = 2;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if ($sayfa) {

			$pagem = ($page - 1) * $limit;

		} else {

			$pagem = 0;

		}



		if ((isset($faturaNo) && !empty($faturaNo)) || (isset($irsaliyeNo) && !empty($irsaliyeNo)) || (isset($tarihGet) && !empty($tarihGet)) || (isset($siralama) && !empty($siralama))) {



			/*if(!empty($cariGrubu)){$sorgu1 = "AND cari_cariGrupKoduID = '$cariGrubu'";}

			else{$sorgu1 = "";}*/



			if (!empty($tarihGet)) {

				$sorgu1 = "AND satis_faturaTarihi BETWEEN '$tarih1' AND '$tarih2'";

			} else {

				$sorgu1 = "";

			}



			if (!empty($irsaliyeNo)) {

				$sorgu2 = "AND satis_irsaliyeNo LIKE '%$irsaliyeNo%'";

			} else {

				$sorgu2 = "";

			}



			if (!empty($siralama)) {

				if ($siralama == 1) {//Tarih büyükten küçüğe

					$sira = "ORDER BY DATE(satis_faturaTarihi) DESC";

				} else if ($siralama == 2) {//Tarih küçükten büyüğe

					$sira = "ORDER BY DATE(satis_faturaTarihi) ASC";

				} else {

					$sira = "ORDER BY satis_id DESC";

				}

			} else {

				$sira = "ORDER BY satis_id DESC";

			}



			$countq = "SELECT COUNT(*) as total FROM satisFaturasi WHERE satis_InvoiceType = 2 AND satis_olusturanAnaHesap = '$anaHesap' AND satis_faturaNo LIKE '%$faturaNo%' " . $sorgu2 . " " . $sorgu1 . " ";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;



			$sorgu = "SELECT * FROM satisFaturasi WHERE satis_InvoiceType = 2 AND satis_olusturanAnaHesap = '$anaHesap' AND satis_faturaNo LIKE '%$faturaNo%' " . $sorgu2 . " " . $sorgu1 . " " . $sira . " LIMIT $pagem,$limit";

		} else {

			$countq = "SELECT COUNT(*) as total FROM satisFaturasi WHERE  satis_InvoiceType = 2 AND satis_olusturanAnaHesap = '$anaHesap'";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;

			$sorgu = "SELECT * FROM satisFaturasi WHERE  satis_InvoiceType = 2 AND satis_olusturanAnaHesap = '$anaHesap' ORDER BY satis_id DESC LIMIT $pagem,$limit";

		}



		$data["count_of_list"] = $count;



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/fatura/$urim";

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

		$data["satisFaturasi"] = $this->db->query($sorgu)->result();



		$this->load->view("fatura/earsiv-faturalari", $data);

	}



	public function efaturaGiden()

	{

		$data["baslik"] = "Fatura / E-Fatura Giden Kutusu";



		$anaHesap = anaHesapBilgisi();



		$faturaNo = $this->input->get('faturaNo');

		$irsaliyeNo = $this->input->get('irsaliyeNo');

		//$cariAdi = $this->input->get('cariAdi');

		$tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



		$siralama = $this->input->get('siralama');



		$urim = $this->uri->segment(2);



		$segment = 2;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if ($sayfa) {

			$pagem = ($page - 1) * $limit;

		} else {

			$pagem = 0;

		}



		if ((isset($faturaNo) && !empty($faturaNo)) || (isset($irsaliyeNo) && !empty($irsaliyeNo)) || (isset($tarihGet) && !empty($tarihGet)) || (isset($siralama) && !empty($siralama))) {



			/*if(!empty($cariGrubu)){$sorgu1 = "AND cari_cariGrupKoduID = '$cariGrubu'";}

			else{$sorgu1 = "";}*/



			if (!empty($tarihGet)) {

				$sorgu1 = "AND satis_faturaTarihi BETWEEN '$tarih1' AND '$tarih2'";

			} else {

				$sorgu1 = "";

			}



			if (!empty($irsaliyeNo)) {

				$sorgu2 = "AND satis_irsaliyeNo LIKE '%$irsaliyeNo%'";

			} else {

				$sorgu2 = "";

			}



			if (!empty($siralama)) {

				if ($siralama == 1) {//Tarih büyükten küçüğe

					$sira = "ORDER BY DATE(satis_faturaTarihi) DESC";

				} else if ($siralama == 2) {//Tarih küçükten büyüğe

					$sira = "ORDER BY DATE(satis_faturaTarihi) ASC";

				} else {

					$sira = "ORDER BY satis_id DESC";

				}

			} else {

				$sira = "ORDER BY satis_id DESC";

			}



			$countq = "SELECT COUNT(*) as total FROM satisFaturasi WHERE satis_InvoiceType = 1 AND satis_olusturanAnaHesap = '$anaHesap' AND satis_faturaNo LIKE '%$faturaNo%' " . $sorgu2 . " " . $sorgu1 . " ";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;



			$sorgu = "SELECT * FROM satisFaturasi WHERE satis_InvoiceType = 2 AND satis_olusturanAnaHesap = '$anaHesap' AND satis_faturaNo LIKE '%$faturaNo%' " . $sorgu2 . " " . $sorgu1 . " " . $sira . " LIMIT $pagem,$limit";

		} else {

			$countq = "SELECT COUNT(*) as total FROM satisFaturasi WHERE  satis_InvoiceType = 1 AND satis_olusturanAnaHesap = '$anaHesap'";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;

			$sorgu = "SELECT * FROM satisFaturasi WHERE  satis_InvoiceType = 1 AND satis_olusturanAnaHesap = '$anaHesap' ORDER BY satis_id DESC LIMIT $pagem,$limit";

		}



		$data["count_of_list"] = $count;



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/fatura/$urim";

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

		$data["satisFaturasi"] = $this->db->query($sorgu)->result();

		//GidenKutusuSorgu(); //todo session-id hatası



		$this->load->view("fatura/efatura-giden", $data);

	}



	public function efaturaGelen()

	{

		$data["baslik"] = "Fatura / E-Fatura Gelen Kutusu";



		$anaHesap = anaHesapBilgisi();

		$faturaNo = $this->input->get('faturaNo');

		//$irsaliyeNo = $this->input->get('irsaliyeNo');

		$tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$startDate = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$enDate = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih





		$this->load->view("fatura/efatura-gelen", $data);

	}



	public function eirsaliyeGiden()

	{

		$data["baslik"] = "Fatura / E-İrsaliye Giden Kutusu";



		$anaHesap = anaHesapBilgisi();



		$faturaNo = $this->input->get('faturaNo');

		$irsaliyeNo = $this->input->get('irsaliyeNo');

		//$cariAdi = $this->input->get('cariAdi');

		$tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



		$siralama = $this->input->get('siralama');



		$urim = $this->uri->segment(2);



		$segment = 2;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if ($sayfa) {

			$pagem = ($page - 1) * $limit;

		} else {

			$pagem = 0;

		}



		if ((isset($faturaNo) && !empty($faturaNo)) || (isset($irsaliyeNo) && !empty($irsaliyeNo)) || (isset($tarihGet) && !empty($tarihGet)) || (isset($siralama) && !empty($siralama))) {



			/*if(!empty($cariGrubu)){$sorgu1 = "AND cari_cariGrupKoduID = '$cariGrubu'";}

			else{$sorgu1 = "";}*/



			if (!empty($tarihGet)) {

				$sorgu1 = "AND satis_faturaTarihi BETWEEN '$tarih1' AND '$tarih2'";

			} else {

				$sorgu1 = "";

			}



			if (!empty($irsaliyeNo)) {

				$sorgu2 = "AND satis_irsaliyeNo LIKE '%$irsaliyeNo%'";

			} else {

				$sorgu2 = "";

			}



			if (!empty($siralama)) {

				if ($siralama == 1) {//Tarih büyükten küçüğe

					$sira = "ORDER BY DATE(satis_faturaTarihi) DESC";

				} else if ($siralama == 2) {//Tarih küçükten büyüğe

					$sira = "ORDER BY DATE(satis_faturaTarihi) ASC";

				} else {

					$sira = "ORDER BY satis_id DESC";

				}

			} else {

				$sira = "ORDER BY satis_id DESC";

			}



			$countq = "SELECT COUNT(*) as total FROM satisFaturasi WHERE satis_InvoiceType = 3 AND satis_olusturanAnaHesap = '$anaHesap' AND satis_faturaNo LIKE '%$faturaNo%' " . $sorgu2 . " " . $sorgu1 . " ";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;



			$sorgu = "SELECT * FROM satisFaturasi WHERE satis_InvoiceType = 3 AND satis_olusturanAnaHesap = '$anaHesap' AND satis_faturaNo LIKE '%$faturaNo%' " . $sorgu2 . " " . $sorgu1 . " " . $sira . " LIMIT $pagem,$limit";

		} else {

			$countq = "SELECT COUNT(*) as total FROM satisFaturasi WHERE  satis_InvoiceType = 1 AND satis_olusturanAnaHesap = '$anaHesap'";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;

			$sorgu = "SELECT * FROM satisFaturasi WHERE  satis_InvoiceType = 3 AND satis_olusturanAnaHesap = '$anaHesap' ORDER BY satis_id DESC LIMIT $pagem,$limit";

		}



		$data["count_of_list"] = $count;



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/fatura/$urim";

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

		$data["satisFaturasi"] = $this->db->query($sorgu)->result();

		//GidenKutusuSorgu(); //todo session-id hatası



		$this->load->view("fatura/eirsaliye-giden", $data);

	}



	public function eirsaliyeGelen()

	{

		$data["baslik"] = "Fatura / E-İrsaliye Gelen Kutusu";



		$anaHesap = anaHesapBilgisi();

		$faturaNo = $this->input->get('faturaNo');

		//$irsaliyeNo = $this->input->get('irsaliyeNo');

		$tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$startDate = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$enDate = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



		$this->load->view("fatura/eirsaliye-gelen", $data);

	}



	public function alisFaturasi($id = 0)

	{

		$data["baslik"] = "Fatura / Alış Faturası Oluştur";

		//logekle(11,1);

		$anaHesap = anaHesapBilgisi();





		$tevkifatQ = "SELECT * FROM tevkifatKodlari";

		$tevkifatExe = $this->db->query($tevkifatQ)->result();

		$tevkifat = "";

		foreach ($tevkifatExe as $item) {

			$tevkifat .= "<option value='" . $item->tevkifat_orani . "|" . $item->tevkifat_id . "'>" . $item->tevkifat_kodu . "-" . $item->tevkifat_adi . "</option>";

		}

		$data["tevkifat"] = $tevkifat;



		$istisnaQ = "SELECT * FROM istisnaKodlari";

		$istisnaExe = $this->db->query($istisnaQ)->result();

		$istisna = "";

		foreach ($istisnaExe as $item) {

			$istisna .= "<option value='" . $item->istisna_id . "'>" . $item->istisna_kodu . " " . $item->istisna_adi . "</option>";

		}

		$data["istisna"] = $istisna;

		$sqlBanka = "select * from ayarlar_banka where ayarlarbanka_olusturanAnaHesap=" . $anaHesap . " ";

		$data["faturabanka"] = $this->db->query($sqlBanka)->result();

//düzenleme için veritabanından verilerin çekilmesi

		if ($id != 0) {

			$data["baslik"] = "Fatura / Alış Faturası Düzenle";

			$sql = "select * from alisFaturasi where alis_id=" . $id;

			$faturaDetay = $this->db->query($sql)->row();

			$data["faturaDetay"]=$faturaDetay ;



			$sqlEtiket="select * from etiketler where etiket_id=".$faturaDetay->alis_faturaEtiketID;

			$exeEtiket=$this->db->query($sqlEtiket)->row();

			$data["etiket"]=$exeEtiket;







			$sqlStok = "select * from alisFaturasiStok inner join stok on alisStok_stokID=stok_id inner join stokBirimleri on stokBirim_id=stok_birimID where alisStok_alisFaturasiID=" . $id;

			$data["stok"] = $this->db->query($sqlStok)->result();

			$sqlGenelIskonto = "select * from genel_iskonto inner join alisFaturasi on iskonto_satis_id=alis_id where alis_id=" . $id;

			$data["genelIskonto"] = $this->db->query($sqlGenelIskonto)->result();

			$sqlIade = "select * from iade_fatura_numaralari inner join alisFaturasi on iade_fatura_numaralari.satis_id=alisFaturasi.alis_id where alisFaturasi.alis_id=" . $id;

			$data["iadeFaturalari"] = $this->db->query($sqlIade)->result();

			$sqlCari = "select * from alisFaturasi inner join cari on alis_cariID=cari_id where alis_cariID=" . $data["faturaDetay"]->alis_cariID;

			$data["cari"] = $this->db->query($sqlCari)->row();

			if ($data["cari"]->cari_vergiNumarasi != null)

				$numara = $data["cari"]->cari_vergiNumarasi;

			else if ($data["cari"]->cari_tckn != null)

				$numara = $data["cari"]->cari_tckn;

			$data["cari_value"] = $numara . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;



			$sqlBanka = "select * from faturabanka_bilgileri inner join ayarlar_banka on faturabanka_ayarbankaID=ayarlarbanka_id where faturabanka_faturaID=" . $id . " ";



			$data["faturabankaDuzenle"] = $this->db->query($sqlBanka)->result();



		}

		$iller = $this->db->get('iller');



		if ($iller->num_rows() > 0) {

			$data['_iller'] = $iller->result();

		} else {

			$data['_iller'] = false;

		}

		$stokBirimleriQ = "SELECT * FROM stokBirimleri";

		$birimler = $this->db->query($stokBirimleriQ)->result();

		$option ="";

		foreach ($birimler as $birim)

		{

			$option.="<option value='$birim->stokBirim_id'>$birim->stokBirim_ad</option>";

		}

		$data["birimler"]=$option;

		$this->load->view('fatura/alis-faturasi', $data);

	}



	public function alisFaturasiCari($id = 0)

	{

		$data["baslik"] = "Fatura / Alış Faturası Oluştur";

		//logekle(11,1);

		$anaHesap = anaHesapBilgisi();





		$tevkifatQ = "SELECT * FROM tevkifatKodlari";

		$tevkifatExe = $this->db->query($tevkifatQ)->result();

		$tevkifat = "";

		foreach ($tevkifatExe as $item) {

			$tevkifat .= "<option value='" . $item->tevkifat_orani . "|" . $item->tevkifat_id . "'>" . $item->tevkifat_kodu . "-" . $item->tevkifat_adi . "</option>";

		}

		$data["tevkifat"] = $tevkifat;



		$istisnaQ = "SELECT * FROM istisnaKodlari";

		$istisnaExe = $this->db->query($istisnaQ)->result();

		$istisna = "";

		foreach ($istisnaExe as $item) {

			$istisna .= "<option value='" . $item->istisna_id . "'>" . $item->istisna_kodu . " " . $item->istisna_adi . "</option>";

		}

		$data["istisna"] = $istisna;

		$sqlBanka = "select * from ayarlar_banka where ayarlarbanka_olusturanAnaHesap=" . $anaHesap . " ";

		$data["faturabanka"] = $this->db->query($sqlBanka)->result();

//düzenleme için veritabanından verilerin çekilmesi

		if ($id != 0) {

			$sqlCari = "select * from cari where  cari_id=$id";

			$data["cari"] = $this->db->query($sqlCari)->row();

			if ($data["cari"]->cari_vergiNumarasi != null)

				$numara = $data["cari"]->cari_vergiNumarasi;

			else if ($data["cari"]->cari_tckn != null)

				$numara = $data["cari"]->cari_tckn;

			$data["cari_value"] = $numara . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;



		}

		$iller = $this->db->get('iller');



		if ($iller->num_rows() > 0) {

			$data['_iller'] = $iller->result();

		} else {

			$data['_iller'] = false;

		}

		$stokBirimleriQ = "SELECT * FROM stokBirimleri";

		$birimler = $this->db->query($stokBirimleriQ)->result();

		$option ="";

		foreach ($birimler as $birim)

		{

			$option.="<option value='$birim->stokBirim_id'>$birim->stokBirim_ad</option>";

		}

		$data["birimler"]=$option;

		$this->load->view('fatura/alis-faturasi', $data);

	}



	public function autocompleteDataByStockCode2()

	{

		$anaHesap = anaHesapBilgisi();



		$returnData = array();

		$conditions['searchTerm'] = $this->input->get('term');

		$getData = $this->vt->getRows3($conditions, $anaHesap);



		if (!empty($getData)) {

			foreach ($getData as $row) {

				$stokBirimi = $row['stok_birimID'];

				$stokBirimiQ = "SELECT stokBirim_ad FROM stokBirimleri WHERE stokBirim_id = '$stokBirimi'";

				$stokBirimiE = $this->db->query($stokBirimiQ)->row();



				$data['id'] = $row['stok_id'];

				$data['value'] = $row['stok_kodu'];

				$data['stok_barkod'] = $row['stok_barkod'];

				$data['stok_ad'] = $row['stok_ad'];

				$data['stok_birimi'] = $stokBirimiE->stokBirim_ad;

				$data['stok_alisFiyati'] = $row['stok_alisFiyati'];

				$data['stok_alisKDV'] = $row['stok_alisKDV'];

				array_push($returnData, $data);

			}

		}

		echo json_encode($returnData);

		die;

	}



	public function autocompleteDataByStockName2()

	{

		// Ana hesap kısıtlaması tamamen kaldırıldı - tüm kullanıcılar tüm stokları görebilir

		$returnData = array();

		$conditions['searchTerm'] = $this->input->get('term');

		$getData = $this->vt->getRows4($conditions, null, true); // Ana hesap null, admin true



		if (!empty($getData)) {

			foreach ($getData as $row) {

				$stokBirimi = $row['stok_birimID'];

				$stokBirimiQ = "SELECT stokBirim_ad FROM stokBirimleri WHERE stokBirim_id = '$stokBirimi'";

				$stokBirimiE = $this->db->query($stokBirimiQ)->row();



				$data['id'] = $row['stok_id'];

				$data['value'] = $row['stok_ad'];

				$data['stok_barkod'] = $row['stok_barkod'];

				$data['stok_kodu'] = $row['stok_kodu'];

				$data['stok_birimi'] = $stokBirimiE->stokBirim_ad;

				$data['stok_alisFiyati'] = $row['stok_alisFiyati'];

				$data['stok_alisKDV'] = $row['stok_alisKDV'];

				array_push($returnData, $data);

			}

		}

		echo json_encode($returnData);

		die;

	}



	public function alisFaturasiOlustur()

	{



		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$alis_irsaliyeTarihi = postval("alis_irsaliyeTarihi");

		$alis_faturaTarihi = postval("alis_faturaTarihi");



		$yeni_alis_irsaliyeTarihi = date("Y-m-d", strtotime($alis_irsaliyeTarihi));

		$yeni_alis_faturaTarihi = date("Y-m-d", strtotime($alis_faturaTarihi));



		$irsaliyeNo = postval("alis_irsaliyeNo");

		$faturaNo = postval("alis_faturaNo");



		$alis_etiketID=postval("etiket_id");



		if(empty($alis_etiketID))

		{

			$alis_faturaEtiketID=postval("alis_faturaEtiketID");

			if(!empty($alis_faturaEtiketID))

			{

				$dataEtiket["etiket_adi"]=$alis_faturaEtiketID;

				$dataEtiket["etiket_olusturan"] = $u_id;

				$dataEtiket["etiket_olusturanAnaHesap"] = $anaHesap;

				$dataEtiket["etiket_olusturmaTarihi"] = $tarihi;

				$dataEtiket["etiket_olusturmaSaati"] = $saati;

				$this->db->insert("etiketler",$dataEtiket);

				$alis_etiketID=$this->db->insert_id();

			}

		}



		$data["alis_faturaEtiketID"]=$alis_etiketID;



		$cariKodu = postval("cari_kodu");

		$cariAdi = postval("cari_adi");



		$genelToplamHidden = postval("geneltopHidden");

		$indirimToplamHidden = postval("indirimtopHidden");

		$tevkifatToplamHidden = postval("tevkifattopHidden");

		$vergidahilHidden = postval("vergidahilHidden");





		$istisna_sebebi = postval("istisna_sebebi");

		$vergiMuafiyet_sebep = postval("vergiMuafiyet_sebep");



		if (postval("alis_paraBirimi") == 1) {

			$guncelKur = NULL;

		} else {

			$guncelKur = postval("alis_guncelKur");

			$dovizCevrilmis = $genelToplamHidden * $guncelKur;

		}



		$paraBirimi=postval("alis_paraBirimi");



		$data["alis_faturaTipi"] = postval("alis_faturaTip");

		$data["alis_cariID"] = postval("alis_cariID");

		$data["alis_paraBirimi"] = $paraBirimi;

		$data["alis_guncelKur"] = $guncelKur;

		$data["alis_dovizCevrilmis"] = $dovizCevrilmis;

		$data["alis_irsaliyeNo"] = $irsaliyeNo != '' ? $irsaliyeNo : NULL;

		$data["alis_irsaliyeTarihi"] = $yeni_alis_irsaliyeTarihi != '' ? $yeni_alis_irsaliyeTarihi : NULL;

		$data["alis_faturaNo"] = $faturaNo;

		$data["alis_faturaTarihi"] = $yeni_alis_faturaTarihi;

		$data["alis_faturaSaati"] = postval("alis_faturaSaati");

		$data["alis_aciklama"] = postval("alis_aciklama");

		$data["alis_araToplam"] = postval("aratopHidden");

		$data["alis_kdvToplam"] = postval("kdvtopHidden");

		$data["alis_genelToplam"] = $genelToplamHidden;

		$data["alis_vergiDahilToplam"] = $vergidahilHidden;

		$data["alis_indirimToplam"] = $indirimToplamHidden;

		$data["alis_netTutar"] = postval("netTutarInput");



		if ($tevkifatToplamHidden != null)

			$data["alis_tevkifatToplam"] = $tevkifatToplamHidden;

		if ($istisna_sebebi != null)

			$data["alis_istisna_id"] = $istisna_sebebi;



		if ($vergiMuafiyet_sebep != null)

			$data["alis_vergiMuafiyetSebep"] = $vergiMuafiyet_sebep;



		$data["alis_olusturan"] = $u_id;

		$data["alis_olusturanAnaHesap"] = $anaHesap;

		$data["alis_olusturmaTarihi"] = $tarihi;

		$data["alis_olusturmaSaati"] = $saati;



		$cariKontrolQ = "SELECT * FROM cari WHERE (cari_kodu = '$cariKodu' ) AND cari_olusturanAnaHesap = '$anaHesap' ";

		$cariKontrol = $this->db->query($cariKontrolQ)->row();



		if (!$cariKontrol) {

			$this->session->set_flashdata('cari_hatali', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		$faturaVarmiQ = "SELECT * FROM alisFaturasi WHERE  (alis_irsaliyeNo = '$irsaliyeNo' OR alis_faturaNo = '$faturaNo') AND alis_olusturanAnaHesap = '$anaHesap'";

		$faturaVarmiExe = $this->db->query($faturaVarmiQ)->result();





		if ($faturaVarmiExe) {

			$this->session->set_flashdata('fatura_mukerrer', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}





		$this->vt->insert("alisFaturasi", $data);

		$alis_id = $this->db->insert_id();



		$iade_count = postval("iade_count");

		if ($iade_count != 0) {// iade



			$iadenumara = postval("iadenumara");

			$iadetarih = postval("iadetarih");

			$dataIade["satis_id"] = $alis_id;

			//iade_fatura_numaralari (e-arşiv iade fatura numaraları)

			for ($i = 0; $i < $iade_count; $i++) {

				$dataIade["fatura_numarasi"] = $iadenumara[$i];

				$dataIade["tarih"] = $iadetarih[$i];



				$this->vt->insert("iade_fatura_numaralari", $dataIade);



			}

		}



		$indirim_count = postval("indirim_count");

		if ($indirim_count != 0) {//genel iskonto



			$indirimsebep = postval("indirimsebep");

			$indirimtutar = postval("indirimtutar");

			$dataIndirim["iskonto_satis_id"] = $alis_id;

			//genel_iskonto (faturaya yapılan genel indirimler)

			for ($i = 0; $i < $indirim_count; $i++) {

				if ($indirimsebep[$i] == 0)

					$dataIndirim["iskonto_sebep"] = "İskonto";

				else if ($indirimsebep[$i] == 1)

					$dataIndirim["iskonto_sebep"] = "Nakit İskontosu";

				else if ($indirimsebep[$i] == 2)

					$dataIndirim["iskonto_sebep"] = "Diğer";



				$dataIndirim["iskonto_tutar"] = $indirimtutar[$i];



				$this->vt->insert("genel_iskonto", $dataIndirim);



			}

		}



		$bankaCheck = postval("bankaCheck");

		$this->vt->del("faturabanka_bilgileri", "faturabanka_faturaID", $alis_id);

		if ($bankaCheck == 1) {

			$bankaCount = postval("bankaCount");

			echo $bankaCount;

			if ($bankaCount != 0) {//banka bilgileri

				$ayarlarbanka_id = postval("ayarlarbanka_id");

				$dataBanka["faturabanka_faturaID"] = $alis_id;

				for ($i = 0; $i < $bankaCount; $i++) {

					$dataBanka["faturabanka_ayarbankaID"] = $ayarlarbanka_id[$i];

					print_r($dataBanka);

					$this->vt->insert("faturabanka_bilgileri", $dataBanka);

				}

			}

		}



		//alış faturası stok

		$dataStok["alisStok_alisFaturasiID"] = $alis_id;

		$stokid = postval("stokid");

		$kacAdetStok = count($stokid);



		$miktar = postval("miktar");

		$birimfiyat = postval("birimfiyat");

		$kdv = postval("kdv");

		$toplam = postval("toplamHidden");



		$indirimtutari = postval("indirimyuzde");

		$tevkifatInput = postval("tevkifat");





		$stokadi=postval("stokadi");

		$stokkodu=postval("stokkodu");

		$barkod=postval("barkod");

		$birim=postval("birim");



		for ($i = 0; $i < $kacAdetStok; $i++) {

			$stokidsi=$stokid[$i];

			if(empty($stokid[$i])) {

				$dataStokX["stok_kodu"] = $stokkodu[$i];

				$stokQ="select * from stok where stok_kodu='".$dataStokX["stok_kodu"]."'";

				$stokExe=$this->db->query($stokQ)->row();

				if($stokExe)

				{

					$stokidsi=$stokExe->stok_id;

				}

				else {

					$dataStokX["stok_ad"] = $stokadi[$i];

					$dataStokX["stok_barkod"] = $barkod[$i];

					$dataStokX["stok_satisFiyati"] = $birimfiyat[$i];

					$dataStokX["stok_alisFiyati"] = 0;

					$dataStokX["stok_satisKDV"] = $kdv[$i];

					$dataStokX["stok_alisKDV"] = 0;

					$dataStokX["stok_birimID"] = $birim[$i];

					$dataStokX["stok_olusturan"] = $u_id;

					$dataStokX["stok_olusturanAnaHesap"] = $anaHesap;

					$dataStokX["stok_olusturmaTarihi"] = $tarihi;

					$dataStokX["stok_olusturmaSaati"] = $saati;



					$this->vt->insert("stok", $dataStokX);

					$stokidsi = $this->db->insert_id();



				}

			}



			if ($kdv[$i] == 0) {

				$yeniKDV = 0;

			} else if ($kdv[$i] == 1) {

				$yeniKDV = 0.01;

			} else if ($kdv[$i] == 8) {

				$yeniKDV = 0.08;

			} else if ($kdv[$i] == 18) {

				$yeniKDV = 0.18;

			}



			$yeniKDVHesap = $toplam[$i] * $yeniKDV;

			$yeniKDVHesap2 = $toplam[$i] + $yeniKDVHesap;





			$tevkifat = explode("|", $tevkifatInput[$i]);



			$dataStok["alisStok_stokID"] = $stokidsi;

			$dataStok["alisStok_miktar"] = $miktar[$i];

			$dataStok["alisStok_birimFiyat"] = $birimfiyat[$i];

			$dataStok["alisStok_fiyatMiktar"] = $birimfiyat[$i] * $miktar[$i];

			$dataStok["alisStok_kdv"] = $kdv[$i];

			$dataStok["alisStok_indirimTutari"] = $indirimtutari[$i];

			$dataStok["alisStok_satirIskonto"] = $birimfiyat[$i] * $miktar[$i] * $indirimtutari[$i] / 100;

			$dataStok["alisStok_indirimlifiyat"] = $toplam[$i];





			if ($tevkifat[0] == 0)

				$dataStok["alisStok_tevkifat_id"] = 0;

			else

				$dataStok["alisStok_tevkifat_id"] = $tevkifat[1];





			$this->vt->insert("alisFaturasiStok", $dataStok);



			$paraBirimiTxt="TL";

			if($paraBirimi==2)

				$paraBirimiTxt="USD";

			else if($paraBirimi==3)

				$paraBirimiTxt="EUR";

			else if($paraBirimi==4)

				$paraBirimiTxt="GBP";



			$data_sh["sh_turu"] = 1;

			$data_sh["sh_giris"] = $miktar[$i];

			$data_sh["sh_cariID"] = postval("alis_cariID");

			$data_sh["sh_stokID"] = $stokidsi;

			$data_sh["sh_faturaID"] = $alis_id;

			$data_sh["sh_belgeNumarasi"] = $faturaNo;

			$data_sh["sh_paraBirimi"]=$paraBirimiTxt;

			$data_sh["sh_tarih"] = $tarihi;

			$data_sh["sh_birimFiyat"] = $birimfiyat[$i];

			$data_sh["sh_kdv"] = $kdv[$i];

			$data_sh["sh_toplamKDV"] = $yeniKDVHesap;

			$data_sh["sh_toplamFiyat"] = $yeniKDVHesap2;

			$data_sh["sh_olusturan"] = $u_id;

			$data_sh["sh_olusturanAnaHesap"] = $anaHesap;

			$data_sh["sh_olusturmaTarihi"] = $tarihi;

			$data_sh["sh_olusturmaSaati"] = $saati;



			$this->vt->insert("stokHareketleri", $data_sh);

		}



		//alış faturası stok



		//Cari Hareketleri Ekle :begin

		$data_ch["ch_belgeNumarasi"] = $faturaNo;

		$data_ch["ch_turu"] = 1;

		$data_ch["ch_cariID"] = postval("alis_cariID");

		$data_ch["ch_faturaID"] = $alis_id;

		$data_ch["ch_paraBirimi"]=$paraBirimiTxt;

/*

		if($dovizCevrilmis)

			$data_ch["ch_alacak"] =$dovizCevrilmis;

		else*/

		$data_ch["ch_alacak"] = postval("geneltopHidden");

		$data_ch["ch_tarih"] = $yeni_alis_faturaTarihi;



		$data_ch["ch_olusturan"] = $u_id;

		$data_ch["ch_olusturanAnaHesap"] = $anaHesap;

		$data_ch["ch_olusturmaTarihi"] = $tarihi;

		$data_ch["ch_olusturmaSaati"] = $saati;



		$this->vt->insert("cariHareketleri", $data_ch);

		$ch_id = $this->db->insert_id();

		$datachu["alis_cariHareketiID"] = $ch_id;



		$this->vt->update('alisFaturasi', array('alis_id' => $alis_id), $datachu);

		//Cari Hareketleri Ekle :end



		$this->session->set_flashdata('fatura_ok', 'OK');

		logekle(11, 2);

		//redirect("fatura/satis-faturasi-duzenle/$satis_id");



		redirect("fatura/alis-faturasi-listesi");





	}



	public function alisFaturasiGuncelle()

	{

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$alis_irsaliyeTarihi = postval("alis_irsaliyeTarihi");

		$alis_faturaTarihi = postval("alis_faturaTarihi");



		$yeni_alis_irsaliyeTarihi = date("Y-m-d", strtotime($alis_irsaliyeTarihi));

		$yeni_alis_faturaTarihi = date("Y-m-d", strtotime($alis_faturaTarihi));



		$alis_id = postval("alis_id");



		$irsaliyeNo = postval("alis_irsaliyeNo");

		$faturaNo = postval("alis_faturaNo");



		$cariKodu = postval("cari_kodu");

		$cariAdi = postval("cari_adi");



		$genelToplamHidden = postval("geneltopHidden");

		$indirimToplamHidden = postval("indirimtopHidden");

		$tevkifatToplamHidden = postval("tevkifattopHidden");

		$vergidahilHidden = postval("vergidahilHidden");



		$istisna_sebebi = postval("istisna_sebebi");

		$vergiMuafiyet_sebep = postval("vergiMuafiyet_sebep");



		if (postval("alis_paraBirimi") == 1) {

			$guncelKur = NULL;

		} else {

			$guncelKur = postval("alis_guncelKur");

			$dovizCevrilmis = $genelToplamHidden * $guncelKur;

		}



		$paraBirimi= postval("alis_paraBirimi");



		$alis_etiketID=postval("etiket_id");

		if (empty($alis_etiketID)) {

			$alis_faturaEtiketID = postval("alis_faturaEtiketID");

			if (!empty($alis_faturaEtiketID)) {

				$dataEtiket["etiket_adi"] = $alis_faturaEtiketID;

				$dataEtiket["etiket_olusturan"] = $u_id;

				$dataEtiket["etiket_olusturanAnaHesap"] = $anaHesap;

				$dataEtiket["etiket_olusturmaTarihi"] = $tarihi;

				$dataEtiket["etiket_olusturmaSaati"] = $saati;

				$this->db->insert("etiketler", $dataEtiket);

				$alis_etiketID = $this->db->insert_id();

			}

		}

		$data["alis_faturaEtiketID"] = $alis_etiketID;



		$data["alis_faturaTipi"] = postval("alis_faturaTip");





		$data["alis_cariID"] = postval("alis_cariID");

		$data["alis_paraBirimi"] =$paraBirimi;

		$data["alis_guncelKur"] = $guncelKur;

		$data["alis_dovizCevrilmis"] = $dovizCevrilmis;

		$data["alis_irsaliyeNo"] = $irsaliyeNo != '' ? $irsaliyeNo : NULL;

		$data["alis_irsaliyeTarihi"] = $alis_irsaliyeTarihi != '' ? $yeni_alis_irsaliyeTarihi : NULL;

		$data["alis_faturaNo"] = $faturaNo;

		$data["alis_faturaTarihi"] = $yeni_alis_faturaTarihi;

		$data["alis_faturaSaati"] = postval("alis_faturaSaati");

		$data["alis_aciklama"] = postval("alis_aciklama");

		$data["alis_araToplam"] = postval("aratopHidden");

		$data["alis_kdvToplam"] = postval("kdvtopHidden");

		$data["alis_genelToplam"] = $genelToplamHidden;

		$data["alis_vergiDahilToplam"] = $vergidahilHidden;

		$data["alis_indirimToplam"] = $indirimToplamHidden;

		$data["alis_netTutar"] = postval("netTutarInput");



		if ($tevkifatToplamHidden != null)

			$data["alis_tevkifatToplam"] = $tevkifatToplamHidden;

		if ($istisna_sebebi != null)

			$data["alis_istisna_id"] = $istisna_sebebi;





		if ($vergiMuafiyet_sebep != null)

			$data["alis_vergiMuafiyetSebep"] = $vergiMuafiyet_sebep;



		$data["alis_olusturan"] = $u_id;

		$data["alis_olusturanAnaHesap"] = $anaHesap;

		$data["alis_olusturmaTarihi"] = $tarihi;

		$data["alis_olusturmaSaati"] = $saati;



		$cariKontrolQ = "SELECT * FROM cari WHERE (cari_kodu = '$cariKodu') AND cari_olusturanAnaHesap = '$anaHesap'";

		$cariKontrol = $this->db->query($cariKontrolQ)->row();



		if (!$cariKontrol) {

			$this->session->set_flashdata('cari_hatali', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		$this->vt->update('alisFaturasi', array('alis_id' => $alis_id), $data);



		//iade

		$iade_count = postval("iade_count");



		$this->vt->del("iade_fatura_numaralari", "satis_id", $alis_id);

		if ($iade_count != 0) {// iade

			$iadenumara = postval("iadenumara");

			$iadetarih = postval("iadetarih");

			$dataIade["satis_id"] = $alis_id;

			//iade_fatura_numaralari (e-arşiv iade fatura numaraları)

			for ($i = 0; $i < $iade_count; $i++) {

				$dataIade["fatura_numarasi"] = $iadenumara[$i];

				$dataIade["tarih"] = $iadetarih[$i];



				$this->vt->insert("iade_fatura_numaralari", $dataIade);



			}

		}

		//indirim

		$indirim_count = postval("indirim_count");

		$this->vt->del("genel_iskonto", "iskonto_satis_id", $alis_id);

		if ($indirim_count != 0) {//genel iskonto

			$indirimsebep = postval("indirimsebep");

			$indirimtutar = postval("indirimtutar");

			$dataIndirim["iskonto_satis_id"] = $alis_id;

			//genel_iskonto (faturaya yapılan genel indirimler)

			for ($i = 0; $i < $indirim_count; $i++) {

				if ($indirimsebep[$i] == 0)

					$dataIndirim["iskonto_sebep"] = "İskonto";

				else if ($indirimsebep[$i] == 1)

					$dataIndirim["iskonto_sebep"] = "Nakit İskontosu";

				else if ($indirimsebep[$i] == 2)

					$dataIndirim["iskonto_sebep"] = "Diğer";



				$dataIndirim["iskonto_tutar"] = $indirimtutar[$i];



				$this->vt->insert("genel_iskonto", $dataIndirim);



			}

		}





		$bankaCheck = postval("bankaCheck");

		$this->vt->del("faturabanka_bilgileri", "faturabanka_faturaID", $alis_id);

		if ($bankaCheck == 1) {

			$bankaCount = postval("bankaCount");

			echo $bankaCount;

			if ($bankaCount != 0) {//banka bilgileri

				$ayarlarbanka_id = postval("ayarlarbanka_id");

				$dataBanka["faturabanka_faturaID"] = $alis_id;

				for ($i = 0; $i < $bankaCount; $i++) {

					$dataBanka["faturabanka_ayarbankaID"] = $ayarlarbanka_id[$i];

					print_r($dataBanka);

					$this->vt->insert("faturabanka_bilgileri", $dataBanka);

				}

			}

		}



		//alis faturası stok

		$dataStok["alisStok_alisFaturasiID"] = $alis_id;

		$stokid = postval("stokid");

		$kacAdetStok = count($stokid);



		$alisFaturasiStok_id = postval("alisFaturasiStok_id");



		$stoklarQ = "SELECT * FROM alisFaturasiStok WHERE alisStok_alisFaturasiID = '$alis_id'";

		$stoklarExe = $this->db->query($stoklarQ)->result();



		$newArray = [];

		foreach ($stoklarExe as $sexe) {

			$newArray[] = $sexe->alisStok_id;

		}



		$diff = array_diff($newArray, $alisFaturasiStok_id);



		if (!empty($diff)) {

			foreach ($diff as $key => $value) {

				$this->vt->del("alisFaturasiStok", "alisStok_id", $value);

			}

		}



		$miktar = postval("miktar");

		$birimfiyat = postval("birimfiyat");

		$kdv = postval("kdv");

		$toplam = postval("toplamHidden");

		$indirimtutari = postval("indirimyuzde");

		$tevkifatInput = postval("tevkifat");





		$stokadi=postval("stokadi");

		$stokkodu=postval("stokkodu");

		$barkod=postval("barkod");

		$birim=postval("birim");



		$this->vt->del("stokHareketleri", "sh_faturaID", $alis_id);



		for ($i = 0; $i < $kacAdetStok; $i++) {

			$stokidsi=$stokid[$i];

			if(empty($stokid[$i])) {

				$dataStokX["stok_kodu"] = $stokkodu[$i];

				$stokQ="select * from stok where stok_kodu='".$dataStokX["stok_kodu"]."'";

				$stokExe=$this->db->query($stokQ)->row();

				if($stokExe)

				{

					$stokidsi=$stokExe->stok_id;

				}

				else {

					$dataStokX["stok_ad"] = $stokadi[$i];

					$dataStokX["stok_barkod"] = $barkod[$i];

					$dataStokX["stok_satisFiyati"] = $birimfiyat[$i];

					$dataStokX["stok_alisFiyati"] = 0;

					$dataStokX["stok_satisKDV"] = $kdv[$i];

					$dataStokX["stok_alisKDV"] = 0;

					$dataStokX["stok_birimID"] = $birim[$i];

					$dataStokX["stok_olusturan"] = $u_id;

					$dataStokX["stok_olusturanAnaHesap"] = $anaHesap;

					$dataStokX["stok_olusturmaTarihi"] = $tarihi;

					$dataStokX["stok_olusturmaSaati"] = $saati;



					$this->vt->insert("stok", $dataStokX);

					$stokidsi = $this->db->insert_id();

				}

			}



			if ($kdv[$i] == 0) {

				$yeniKDV = 0;

			} else if ($kdv[$i] == 1) {

				$yeniKDV = 0.01;

			} else if ($kdv[$i] == 8) {

				$yeniKDV = 0.08;

			} else if ($kdv[$i] == 18) {

				$yeniKDV = 0.18;

			}



			$yeniKDVHesap = $toplam[$i] * $yeniKDV;

			$yeniKDVHesap2 = $toplam[$i] + $yeniKDVHesap;



			$tevkifat = explode("|", $tevkifatInput[$i]);

			$dataStok["alisStok_stokID"] = $stokidsi;

			$dataStok["alisStok_miktar"] = $miktar[$i];

			$dataStok["alisStok_birimFiyat"] = $birimfiyat[$i];

			$dataStok["alisStok_fiyatMiktar"] = $birimfiyat[$i] * $miktar[$i];

			$dataStok["alisStok_kdv"] = $kdv[$i];

			$dataStok["alisStok_indirimTutari"] = $indirimtutari[$i];

			$dataStok["alisStok_satirIskonto"] = $birimfiyat[$i] * $miktar[$i] * $indirimtutari[$i] / 100;

			$dataStok["alisStok_indirimlifiyat"] = $toplam[$i];



			if ($tevkifat[0] == 0)

				$dataStok["alisStok_tevkifat_id"] = 0;

			else

				$dataStok["alisStok_tevkifat_id"] = $tevkifat[1];



			if (!empty($alisFaturasiStok_id[$i])) {



				$this->vt->update('alisFaturasiStok', array('alisStok_id' => $alisFaturasiStok_id[$i]), $dataStok);

			} else {



				$this->vt->insert("alisFaturasiStok", $dataStok);

			}



			$paraBirimiTxt="TL";

			if($paraBirimi==2)

				$paraBirimiTxt="USD";

			else if($paraBirimi==3)

				$paraBirimiTxt="EUR";

			else if($paraBirimi==4)

				$paraBirimiTxt="GBP";



			//stok hareketleri güncelle

			$data_sh["sh_turu"] = 1;

			$data_sh["sh_giris"] = $miktar[$i];

			$data_sh["sh_cariID"] = postval("alis_cariID");

			$data_sh["sh_stokID"] = $stokidsi;

			$data_sh["sh_faturaID"] = $alis_id;

			$data_sh["sh_belgeNumarasi"] = $faturaNo;

			$data_sh["sh_paraBirimi"]=$paraBirimiTxt;

			$data_sh["sh_tarih"] = $tarihi;

			$data_sh["sh_birimFiyat"] = $birimfiyat[$i];

			$data_sh["sh_kdv"] = $kdv[$i];

			$data_sh["sh_toplamKDV"] = $yeniKDVHesap;

			$data_sh["sh_toplamFiyat"] = $yeniKDVHesap2;

			$data_sh["sh_olusturan"] = $u_id;

			$data_sh["sh_olusturanAnaHesap"] = $anaHesap;

			$data_sh["sh_olusturmaTarihi"] = $tarihi;

			$data_sh["sh_olusturmaSaati"] = $saati;



			$this->vt->insert("stokHareketleri", $data_sh);

			//stok hareketleri güncelle





		}



		//Satış Sözleşmesi stok





		//Cari Hareketleri Düzenle :begin

		$ch_idsi = postval("ch_id");

		$data_ch["ch_belgeNumarasi"] = $faturaNo;

		$data_ch["ch_cariID"] = postval("alis_cariID");

		$data_ch["ch_faturaID"] = $alis_id;

		$data_ch["ch_paraBirimi"]=$paraBirimiTxt;

/*

		if($dovizCevrilmis)

			$data_ch["ch_alacak"] =$dovizCevrilmis;

		else*/

		$data_ch["ch_alacak"] = postval("geneltopHidden");

		$data_ch["ch_tarih"] = $yeni_alis_faturaTarihi;



		$data_ch["ch_olusturan"] = $u_id;

		$data_ch["ch_olusturanAnaHesap"] = $anaHesap;

		$data_ch["ch_olusturmaTarihi"] = $tarihi;

		$data_ch["ch_olusturmaSaati"] = $saati;



		$this->vt->update('cariHareketleri', array('ch_id' => $ch_idsi), $data_ch);

		//Cari Hareketleri Düzenle :end





		$this->session->set_flashdata('fatura_updt_ok', 'OK');

		logekle(11, 3);

		redirect($_SERVER['HTTP_REFERER']);

	}



	public function alisFaturasiDuzenle($id)

	{

		$data["baslik"] = "Fatura / Alış Faturası Düzenle";

		$anaHesap = anaHesapBilgisi();



		$alisFaturasiQ = "SELECT * FROM alisFaturasi WHERE alis_id = '$id' AND alis_olusturanAnaHesap = '$anaHesap'";

		$alisFaturasiStokQ = "SELECT * FROM alisFaturasiStok WHERE alisStok_alisFaturasiID = '$id'";



		$data["alisFaturasi"] = $this->db->query($alisFaturasiQ)->row();



		$cariID = $data["alisFaturasi"]->alis_cariID;

		$olusturanHesapKim = $data["alisFaturasi"]->alis_olusturanAnaHesap;



		$cariBilgileriQ = "SELECT * FROM cari WHERE cari_id = '$cariID' AND cari_olusturanAnaHesap = '$anaHesap'";



		$data["cariBilgileri"] = $this->db->query($cariBilgileriQ)->row();



		$cariGrubuID = $data["cariBilgileri"]->cari_cariGrupKoduID;



		$cariGrubuQ = "SELECT * FROM cariGruplari WHERE cariGrup_id = '$cariGrubuID' AND cariGrup_olusturanAnaHesap = '$anaHesap'";



		$data["cariGrubuBilgileri"] = $this->db->query($cariGrubuQ)->row();

		$data["alisFaturasiStok"] = $this->db->query($alisFaturasiStokQ)->result();



		$bankaQ = "SELECT * FROM banka WHERE banka_olusturanAnaHesap = '$anaHesap'";

		$data["banka"] = $this->db->query($bankaQ)->result();



		$kasaQ = "SELECT * FROM kasa WHERE kasa_olusturanAnaHesap = '$anaHesap'";

		$data["kasa"] = $this->db->query($kasaQ)->result();



		if ($anaHesap == $olusturanHesapKim) {

			$this->load->view("fatura/alis-faturasi-duzenle", $data);

		} else {

			redirect('hata');

		}

	}



	public function alisFaturasiListesi()

	{

		$data["baslik"] = "Fatura / Alış Faturası Listesi";

		$anaHesap = anaHesapBilgisi();



		$faturaNo = $this->input->get('faturaNo');

		$etiket = $this->input->get('etiket');

		//$irsaliyeNo = $this->input->get('irsaliyeNo');

		//$cariAdi = $this->input->get('cariAdi');



		$tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



		$siralama = $this->input->get('siralama');

        $ayArama= $this->input->get('ayArama');

		

		$urim = $this->uri->segment(2);



		$segment = 2;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if ($sayfa) {

			$pagem = ($page - 1) * $limit;

		} else {

			$pagem = 0;

		}



		if ((isset($faturaNo) && !empty($faturaNo)) || (isset($etiket) && !empty($etiket)) || (isset($tarihGet) && !empty($tarihGet)) || (isset($siralama) && !empty($siralama)) || (isset($ayArama) && !empty($ayArama))) {



			/*if(!empty($cariGrubu)){$sorgu1 = "AND cari_cariGrupKoduID = '$cariGrubu'";}

			else{$sorgu1 = "";}*/



			if (!empty($tarihGet)) {

				$sorgu1 = "AND alis_faturaTarihi BETWEEN '$tarih1' AND '$tarih2'";

			} else {

				$sorgu1 = "";

			}

/*

			if (!empty($irsaliyeNo)) {

				$sorgu2 = "AND alis_irsaliyeNo LIKE '%$irsaliyeNo%'";

			} else {

				$sorgu2 = "";

			}*/

			if (!empty($etiket)) {

				$sorgu2 = "AND alis_faturaEtiketID = '$etiket'";

			} else {

				$sorgu2 = "";

			}



			if (!empty($siralama)) {

				if ($siralama == 1) {//Tarih büyükten küçüğe

					$sira = "ORDER BY DATE(alis_faturaTarihi) DESC";

				} else if ($siralama == 2) {//Tarih küçükten büyüğe

					$sira = "ORDER BY DATE(alis_faturaTarihi) ASC";

				} else {

					$sira = "ORDER BY alis_id DESC";

				}

			} else {

				$sira = "ORDER BY alis_id DESC";

			}

			

	        if (!empty($ayArama)) {

	            $sorgu3 = "AND month(alis_faturaTarihi) = '$ayArama'";

	        }else $sorgu3="";



			$countq = "SELECT COUNT(*) as total FROM alisFaturasi WHERE alis_olusturanAnaHesap = '$anaHesap' AND alis_faturaNo LIKE '%$faturaNo%' " . $sorgu2 . " " . $sorgu1 . " ". $sorgu3. " ";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;



			$sorgu = "SELECT * FROM alisFaturasi WHERE alis_olusturanAnaHesap = '$anaHesap' AND alis_faturaNo LIKE '%$faturaNo%' " . $sorgu2 . " " . $sorgu1 ." ". $sorgu3 . "  " . $sira . " LIMIT $pagem,$limit";

		} else {

			$countq = "SELECT COUNT(*) as total FROM alisFaturasi WHERE alis_olusturanAnaHesap = '$anaHesap'";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;

			$sorgu = "SELECT * FROM alisFaturasi WHERE alis_olusturanAnaHesap = '$anaHesap' ORDER BY alis_id DESC LIMIT $pagem,$limit";

		}

		$data["count_of_list"] = $count;



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/fatura/$urim";

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

		$data["alisFaturasi"] = $this->db->query($sorgu)->result();



		$this->load->view("fatura/alis-faturasi-listesi", $data);

	}



	public function satisFaturasiExcel()

	{

		$anaHesap = anaHesapBilgisi();



		$faturaNo = $this->input->get('faturaNo');

		$irsaliyeNo = $this->input->get('irsaliyeNo');

		$tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



		if ((isset($faturaNo) && !empty($faturaNo)) || (isset($irsaliyeNo) && !empty($irsaliyeNo)) || (isset($tarihGet) && !empty($tarihGet))) {



			if (!empty($tarihGet)) {

				$sorgu1 = "AND satis_faturaTarihi BETWEEN '$tarih1' AND '$tarih2'";

			} else {

				$sorgu1 = "";

			}



			$sorgu = "SELECT * FROM satisFaturasi WHERE satis_olusturanAnaHesap = '$anaHesap' AND satis_faturaNo LIKE '%$faturaNo%' AND satis_irsaliyeNo LIKE '%$irsaliyeNo%' " . $sorgu1 . " ORDER BY satis_id DESC";

		} else {

			$sorgu = "SELECT * FROM satisFaturasi WHERE satis_olusturanAnaHesap = '$anaHesap' ORDER BY satis_id DESC";

		}



		$satisFaturasi = $this->db->query($sorgu)->result();



		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');

		$reader->setReadDataOnly(TRUE);

		$spreadsheet = new Spreadsheet();



		$sheet = $spreadsheet->getActiveSheet();



		date_default_timezone_set('Europe/Istanbul');

		$tarih = (new DateTime('now'))->format('d.m.Y-His');



		$sheet->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('CCCCCC');



		$sheet->setCellValue('A1', 'Tarih');

		$sheet->setCellValue('B1', 'Sözleşme No');

		$sheet->setCellValue('C1', 'İrsaliye No');

		$sheet->setCellValue('D1', 'Cari Adı');

		$sheet->setCellValue('E1', 'Genel Toplam');



		$rows = 2;

		foreach ($satisFaturasi as $sf) {

			$tarihYeni = date('d.m.Y', strtotime($sf->satis_faturaTarihi));



			$cariID = $sf->satis_cariID;

			$cariQ = "SELECT cari_ad FROM cari WHERE cari_id = '$cariID'";

			$carie = $this->db->query($cariQ)->row();

			$cariAd = $carie->cari_ad;



			$sheet->setCellValue('A' . $rows, $tarihYeni);

			$sheet->setCellValue('B' . $rows, $sf->satis_faturaNo);

			$sheet->setCellValue('C' . $rows, $sf->satis_irsaliyeNo);

			$sheet->setCellValue('D' . $rows, $cariAd);

			$sheet->setCellValue('E' . $rows, $sf->satis_genelToplam);

			$rows++;

		}



		$sheet->getColumnDimension('A')->setAutoSize(true);

		$sheet->getColumnDimension('B')->setAutoSize(true);

		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->getColumnDimension('E')->setAutoSize(true);



		$writer = new Xlsx($spreadsheet);//instantiate Xlsx



		$filename = 'satis-faturalari-' . $tarih;//set filename for excel file to be exported



		header('Content-Type: application/vnd.ms-excel');// generate excel file

		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

		header('Cache-Control: max-age=0');



		$writer->save('php://output');//download file

	}



	public function alisFaturasiExcel()

	{

		$anaHesap = anaHesapBilgisi();



		$faturaNo = $this->input->get('faturaNo');

		$irsaliyeNo = $this->input->get('irsaliyeNo');

		//$cariAdi = $this->input->get('cariAdi');



		$tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



		if ((isset($faturaNo) && !empty($faturaNo)) || (isset($irsaliyeNo) && !empty($irsaliyeNo)) || (isset($tarihGet) && !empty($tarihGet))) {



			/*if(!empty($cariGrubu)){$sorgu1 = "AND cari_cariGrupKoduID = '$cariGrubu'";}

			else{$sorgu1 = "";}*/



			if (!empty($tarihGet)) {

				$sorgu1 = "AND alis_faturaTarihi BETWEEN '$tarih1' AND '$tarih2'";

			} else {

				$sorgu1 = "";

			}



			$sorgu = "SELECT * FROM alisFaturasi WHERE alis_olusturanAnaHesap = '$anaHesap' AND alis_faturaNo LIKE '%$faturaNo%' AND alis_irsaliyeNo LIKE '%$irsaliyeNo%' " . $sorgu1 . "  ORDER BY alis_id DESC";

		} else {

			$sorgu = "SELECT * FROM alisFaturasi WHERE alis_olusturanAnaHesap = '$anaHesap' ORDER BY alis_id DESC";

		}



		$alisFaturasi = $this->db->query($sorgu)->result();



		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');

		$reader->setReadDataOnly(TRUE);

		$spreadsheet = new Spreadsheet();



		$sheet = $spreadsheet->getActiveSheet();



		date_default_timezone_set('Europe/Istanbul');

		$tarih = (new DateTime('now'))->format('d.m.Y-His');



		$sheet->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('CCCCCC');



		$sheet->setCellValue('A1', 'Tarih');

		$sheet->setCellValue('B1', 'Sözleşme No');

		$sheet->setCellValue('C1', 'İrsaliye No');

		$sheet->setCellValue('D1', 'Cari Adı');

		$sheet->setCellValue('E1', 'Genel Toplam');



		$rows = 2;

		foreach ($alisFaturasi as $af) {

			$tarihYeni = date('d.m.Y', strtotime($af->alis_faturaTarihi));



			$cariID = $af->alis_cariID;

			$cariQ = "SELECT cari_ad FROM cari WHERE cari_id = '$cariID'";

			$carie = $this->db->query($cariQ)->row();

			$cariAd = $carie->cari_ad;



			$sheet->setCellValue('A' . $rows, $tarihYeni);

			$sheet->setCellValue('B' . $rows, $af->alis_faturaNo);

			$sheet->setCellValue('C' . $rows, $af->alis_irsaliyeNo);

			$sheet->setCellValue('D' . $rows, $cariAd);

			$sheet->setCellValue('E' . $rows, $af->alis_genelToplam);

			$rows++;

		}



		$sheet->getColumnDimension('A')->setAutoSize(true);

		$sheet->getColumnDimension('B')->setAutoSize(true);

		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->getColumnDimension('E')->setAutoSize(true);



		$writer = new Xlsx($spreadsheet);//instantiate Xlsx



		$filename = 'alis-faturalari-' . $tarih;//set filename for excel file to be exported



		header('Content-Type: application/vnd.ms-excel');// generate excel file

		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

		header('Cache-Control: max-age=0');



		$writer->save('php://output');//download file

	}



	public function satisFaturasiStokKontrol()

	{

		$anaHesap = anaHesapBilgisi();



		$data = $this->input->post();



		$stokID = $data["stokID"];

		$stokMiktar = $data["stokMiktar"];



		foreach ($stokID as $key => $value) {

			$toplamGirisQ = "SELECT SUM(sh_giris) AS toplamGiris FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' AND sh_stokID = '$value'";

			$toplamGirisExe = $this->db->query($toplamGirisQ)->row();



			$toplamCikisQ = "SELECT SUM(sh_cikis) AS toplamCikis FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' AND sh_stokID = '$value'";

			$toplamCikisExe = $this->db->query($toplamCikisQ)->row();



			$toplamGiris = $toplamGirisExe->toplamGiris;

			$toplamCikis = $toplamCikisExe->toplamCikis;

			$kalan = $toplamGiris - $toplamCikis;



			$sonuc = $kalan - $stokMiktar[$key];



			if ($sonuc < 0) {

				echo "1";

				die;

			}

		}

	}



	public function alisFaturasiStokKontrol()

	{

		$anaHesap = anaHesapBilgisi();



		$data = $this->input->post();



		$stokID = $data["stokID"];

		$stokMiktar = $data["stokMiktar"];



		foreach ($stokID as $key => $value) {

			$toplamGirisQ = "SELECT SUM(sh_giris) AS toplamGiris FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' AND sh_stokID = '$value'";

			$toplamGirisExe = $this->db->query($toplamGirisQ)->row();



			$toplamCikisQ = "SELECT SUM(sh_cikis) AS toplamCikis FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' AND sh_stokID = '$value'";

			$toplamCikisExe = $this->db->query($toplamCikisQ)->row();



			$toplamGiris = $toplamGirisExe->toplamGiris;

			$toplamCikis = $toplamCikisExe->toplamCikis;

			$kalan = $toplamGiris - $toplamCikis;



			$sonuc = $kalan + $stokMiktar[$key];



			if ($sonuc < 0) {

				echo "1";

				die;

			}

		}

	}



	public function goruntule()

	{

		$anaHesap = anaHesapBilgisi();



		$data["baslik"] = "Fatura Görüntüle";



		$faturaTipi = $this->input->get("tipi");

		$faturaID = $this->input->get("id");



		if ($faturaTipi == "satis") {
			
			// Dashboard'daki "Son Satışlarım" mantığı: Kendisi + bağlı kullanıcılar
			$control = session("r", "login_info");
			$u_id = $control->kullanici_id;
			
			$allowed_user_ids = array($u_id);
			if ($u_id > 0) {
				$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";
				$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();
				
				foreach ($bağlı_kullanicilar as $user) {
					$allowed_user_ids[] = $user->kullanici_id;
				}
			}
			$allowed_user_ids_str = implode(',', $allowed_user_ids);

			$faturaQ = "SELECT sf.* FROM satisFaturasi sf INNER JOIN cari c ON sf.satis_cariID = c.cari_id WHERE sf.satis_id = '$faturaID' AND c.cari_olusturan IN ($allowed_user_ids_str)";

			$faturaExe = $this->db->query($faturaQ)->row();

			$data["fatura"] = $faturaExe;



			$cariQ = "SELECT * FROM cari WHERE cari_id = '$faturaExe->satis_cariID' AND cari_olusturan IN ($allowed_user_ids_str)";

			$data["cari"] = $this->db->query($cariQ)->row();



			if ($faturaExe) {

				$this->load->view('fatura/fatura-goruntule', $data);

			} else {

				$this->load->view('hata');

			}

		} else if ($faturaTipi == "alis") {

			$faturaQ = "SELECT * FROM alisFaturasi WHERE alis_id = '$faturaID' AND alis_olusturanAnaHesap = '$anaHesap'";

			$faturaExe = $this->db->query($faturaQ)->row();

			$data["fatura"] = $faturaExe;



			$cariQ = "SELECT * FROM cari WHERE cari_id = '$faturaExe->alis_cariID' AND cari_olusturanAnaHesap = '$anaHesap' ";

			$data["cari"] = $this->db->query($cariQ)->row();



			if ($faturaExe) {

				$this->load->view('fatura/fatura-goruntule', $data);

			} else {

				$this->load->view('hata');

			}

		} else if ($faturaTipi == "proforma") {

			$faturaQ = "SELECT * FROM proformaFaturasi WHERE proforma_id = '$faturaID' AND proforma_olusturanAnaHesap = '$anaHesap'";

			$faturaExe = $this->db->query($faturaQ)->row();

			$data["fatura"] = $faturaExe;



			$cariQ = "SELECT * FROM cari WHERE cari_id = '$faturaExe->proforma_cariID' AND cari_olusturanAnaHesap = '$anaHesap' ";

			$data["cari"] = $this->db->query($cariQ)->row();



			if ($faturaExe) {

				$this->load->view('fatura/fatura-goruntule', $data);

			} else {

				$this->load->view('hata');

			}

		} else {

			$this->load->view('hata');

		}

	}



	public function paraBirimiKontrol()

	{

		$data = $this->input->post();



		$paraBirimi = $data["paraBirimi"];

		$paraBirimi_converted = getPriceOfCurrency_TCMB($paraBirimi);



		echo $paraBirimi_converted;

	}



	public function seriYonetimi()

	{

		$data["baslik"] = "Seri Yönetimi";



		$anaHesap = anaHesapBilgisi();



		$sorgu1 = "SELECT * FROM seriYonetimi WHERE seri_urun = 1 AND seri_olusturanAnaHesap = '$anaHesap' ORDER BY seri_id DESC";

		$sorgu2 = "SELECT * FROM seriYonetimi WHERE seri_urun = 2 AND seri_olusturanAnaHesap = '$anaHesap' ORDER BY seri_id DESC";

		$sorgu3 = "SELECT * FROM seriYonetimi WHERE seri_urun = 3 AND seri_olusturanAnaHesap = '$anaHesap' ORDER BY seri_id DESC";

		$sorgu4 = "SELECT * FROM seriYonetimi WHERE seri_urun = 4 AND seri_olusturanAnaHesap = '$anaHesap' ORDER BY seri_id DESC";

		$data["eFaturaSeri"] = $this->db->query($sorgu1)->result();

		$data["eArsivSeri"] = $this->db->query($sorgu2)->result();

		$data["eirsaliyeSeri"] = $this->db->query($sorgu3)->result();

		$data["eirsaliyeYanitSeri"] = $this->db->query($sorgu4)->result();



		$this->load->view("fatura/seri-yonetimi", $data);

	}



	public function seriEkle(){

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$seri_urun = postval("seri_urun");

		$seri_seriOnEki = postval("seri_seriOnEki");



		$seriKontrol = $this->db->query("SELECT * FROM seriYonetimi WHERE seri_olusturanAnaHesap = '$anaHesap' AND seri_seriOnEki = '$seri_seriOnEki'")->row();



		if($seriKontrol){

			$this->session->set_flashdata('seri_var', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		if (!checkSpecialChars($seri_seriOnEki)) {

			$this->session->set_flashdata('seri_ozel_karakter', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		if (strlen($seri_seriOnEki) != 3) {

			$this->session->set_flashdata('seri_karakter_siniri', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		$seri_siraNo = postval("seri_siraNo");

		$seri_seriYili = postval("seri_seriYili");

		$seri_varsayilan = postval("seri_varsayilan");

		$seri_eArsivTipi = postval("seri_eArsivTipi");



		if ($seri_varsayilan == 1) {



			if ($seri_eArsivTipi) {

					$updateQ = "UPDATE seriYonetimi SET seri_varsayilan = NULL WHERE seri_varsayilan = 1 AND seri_urun = '$seri_urun' AND seri_olusturanAnaHesap = '$anaHesap' AND seri_eArsivTipi = '$seri_eArsivTipi'";

				$this->db->query($updateQ);

			} else {

				$updateQ = "UPDATE seriYonetimi SET seri_varsayilan = NULL WHERE seri_varsayilan = 1 AND seri_urun = '$seri_urun' AND seri_olusturanAnaHesap = '$anaHesap'";

				$this->db->query($updateQ);

			}

		}



		$data["seri_durum"] = 1;

		$data["seri_urun"] = $seri_urun;

		$data["seri_seriOnEki"] = $seri_seriOnEki;

		$data["seri_siraNo"] = $seri_siraNo;

		$data["seri_seriYili"] = $seri_seriYili;

		$data["seri_aciklama"] = postval("seri_aciklama");

		$data["seri_varsayilan"] = $seri_varsayilan;

		$data["seri_eArsivTipi"] = $seri_eArsivTipi;

		$data["seri_olusturanAnaHesap"] = $anaHesap;

		$data["seri_olusturan"] = $u_id;

		$data["seri_olusturmaTarihi"] = $tarihi;

		$data["seri_olusturmaSaati"] = $saati;



		$this->session->set_flashdata('seri_fatura_basarili', 'OK');

		$this->vt->insert("seriYonetimi", $data);

		redirect($_SERVER['HTTP_REFERER']);

	}



	public function seriVarsayilan($id, $urun, $arsivTipi = NULL)

	{

		$anaHesap = anaHesapBilgisi();



		if ($arsivTipi) {

			$updateQ = "UPDATE seriYonetimi SET seri_varsayilan = NULL WHERE seri_varsayilan = 1 AND seri_urun = '$urun' AND seri_olusturanAnaHesap = '$anaHesap' AND seri_eArsivTipi = '$arsivTipi' ";

			$this->db->query($updateQ);

		} else {

			$updateQ = "UPDATE seriYonetimi SET seri_varsayilan = NULL WHERE seri_varsayilan = 1 AND seri_urun = '$urun' AND seri_olusturanAnaHesap = '$anaHesap'";

			$this->db->query($updateQ);

		}



		$updateQ2 = "UPDATE seriYonetimi SET seri_varsayilan = 1 WHERE seri_id = '$id' AND seri_olusturanAnaHesap = '$anaHesap'";

		$this->db->query($updateQ2);



		$this->session->set_flashdata('varsayilan_ok', 'OK');

		redirect($_SERVER['HTTP_REFERER']);

	}





	public function seriGuncelle(){

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$seri_urun = postval("seri_urun");



		$seri_seriOnEki = postval("seri_seriOnEki");



		$seri_id = postval("seri_id");



		$seriKontrol = $this->db->query("SELECT * FROM seriYonetimi WHERE seri_olusturanAnaHesap = '$anaHesap' AND seri_seriOnEki = '$seri_seriOnEki' AND seri_id != '$seri_id'")->row();



		if($seriKontrol){

			$this->session->set_flashdata('seri_var', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		if (!checkSpecialChars($seri_seriOnEki)) {

			$this->session->set_flashdata('seri_ozel_karakter', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		if (strlen($seri_seriOnEki) != 3) {

			$this->session->set_flashdata('seri_karakter_siniri', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		$seri_siraNo = postval("seri_siraNo");

		$seri_seriYili = postval("seri_seriYili");

		$seri_varsayilan = postval("seri_varsayilan");

		$seri_eArsivTipi = postval("seri_eArsivTipi");



		if ($seri_varsayilan == 1) {



			if ($seri_eArsivTipi) {

				$updateQ = "UPDATE seriYonetimi SET seri_varsayilan = NULL WHERE seri_varsayilan = 1 AND seri_urun = '$seri_urun' AND seri_olusturanAnaHesap = '$anaHesap' AND seri_eArsivTipi = '$seri_eArsivTipi'";

				$this->db->query($updateQ);

			} else {

				$updateQ = "UPDATE seriYonetimi SET seri_varsayilan = NULL WHERE seri_varsayilan = 1 AND seri_urun = '$seri_urun' AND seri_olusturanAnaHesap = '$anaHesap'";

				$this->db->query($updateQ);

			}

		}



		$seri_id=postval("seri_id");

		$data["seri_durum"] = 1;

		$data["seri_urun"] = $seri_urun;

		$data["seri_seriOnEki"] = $seri_seriOnEki;

		$data["seri_siraNo"] = $seri_siraNo;

		$data["seri_seriYili"] = $seri_seriYili;

		$data["seri_aciklama"] = postval("seri_aciklama");

		$data["seri_varsayilan"] = $seri_varsayilan;

		$data["seri_eArsivTipi"] = $seri_eArsivTipi;

		$data["seri_olusturanAnaHesap"] = $anaHesap;

		$data["seri_olusturan"] = $u_id;

		$data["seri_olusturmaTarihi"] = $tarihi;

		$data["seri_olusturmaSaati"] = $saati;



		$this->session->set_flashdata('seri_arsiv_guncelle_basarili', 'OK');

		$this->vt->update("seriYonetimi",array('seri_id' => $seri_id), $data);



		redirect($_SERVER['HTTP_REFERER']);

	}



	public function satis_fatura_sil()

	{

		$id = $this->input->get('fatura_id');

		$del1 = "DELETE FROM satisFaturasi WHERE satis_id=$id";

		$del2 = "DELETE FROM satisFaturasiStok WHERE satisStok_satisFaturasiID=$id";

		$del3 = "DELETE FROM stokHareketleri WHERE sh_faturaID=$id";

		$del4 = "DELETE FROM cariHareketleri WHERE ch_faturaID=$id";

		$this->db->query($del1);

		$this->db->query($del2);

		$this->db->query($del3);

		$this->db->query($del4);

		$this->session->set_flashdata('fatura_sil_ok', 'OK');

		redirect("fatura/satisFaturasiListesi");

	}



	public function alis_fatura_sil(){

		$id = $this->input->get('fatura_id');

		$del1 = "DELETE FROM alisFaturasi WHERE alis_id=$id";

		$del2 = "DELETE FROM alisFaturasiStok WHERE alisStok_alisFaturasiID=$id";

		$del3 = "DELETE FROM stokHareketleri WHERE sh_faturaID=$id";

		$del4 = "DELETE FROM cariHareketleri WHERE ch_faturaID=$id";

		$this->db->query($del1);

		$this->db->query($del2);

		$this->db->query($del3);

		$this->db->query($del4);

		$this->session->set_flashdata('fatura_sil_ok', 'OK');

		redirect("fatura/alisFaturasiListesi");

	}



	public function efaturaIceAktar($faturano){

		$anaHesap = anaHesapBilgisi();



		//faturayı oku

		$ws_archive = env(EFATURA_URL);



		$SESSION_ID = getSessionID();



		$trace = true;

		$exceptions = false;



		$xml_array->REQUEST_HEADER->SESSION_ID = $SESSION_ID;

		$xml_array->REQUEST_HEADER->APPLICATION_NAME = env(APPLICATION_NAME);

		$xml_array->REQUEST_HEADER->COMPRESSED = 'N';

		$xml_array->INVOICE_SEARCH_KEY->READ_INCLUDED = 'true';

		$xml_array->INVOICE_SEARCH_KEY->DIRECTION = "IN";

		$xml_array->INVOICE_SEARCH_KEY->ID = $faturano; //Evrensel Tekil Tanımlama Numarası (ETTN) ile fatura okumak için kullanılabilir. GUID formatında//	IN değeri gönderilecek.

		$xml_array->INVOICE_SEARCH_KEY->TYPE = "XML"; //faturayı almak istediğini formattır. PDF, HTML, XML değeri alabilir. XML değeri gönderilmezse faturanın imzalı UBL-TR XML dosyası dönülecektir.



		$xml_array->HEADER_ONLY = 'N';



		$client = new SoapClient($ws_archive, array('trace' => $trace, 'exceptions' => $exceptions));



		$response = $client->GetInvoiceWithType($xml_array);



		$response = $response->INVOICE->CONTENT->_;



		//faturayı oku



		$dosya = fopen("veri.xml", "w");

		$yaz = fputs($dosya, $response);//faturayı xml olarak kaydet



		$xml = simplexml_load_file('veri.xml');



		//xml dosyasını okuyup data array'e yaz



		$data["faturano"] = $faturano;

		$cariID = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID');

		$faturaTarih = $xml->xpath('//cbc:IssueDate');

		$data["faturaTarih"] = $faturaTarih[0];

		$faturaSaat = $xml->xpath('//cbc:IssueTime');

		$data["faturaSaat"] = $faturaSaat[0];

		$faturaTip = $xml->xpath('//cbc:InvoiceTypeCode');

		$data["faturaTip"] = $faturaTip[0];

		$senaryo = $xml->xpath('//cbc:ProfileID');

		$data["faturaSenaryo"] = $senaryo[0];

		$faturaParaBirimi = $xml->xpath('//cbc:DocumentCurrencyCode');

		$data["faturaParaBirimi"] = $faturaParaBirimi[0];



		//döviz

		$dovizKur = $xml->xpath('//cac:PricingExchangeRate/cbc:CalculationRate');



		//iade fatura bilgileri

		$iadeFatNo = $xml->xpath('//cac:BillingReference/cac:InvoiceDocumentReference/cbc:ID');

		$iadeFatTarih = $xml->xpath('//cac:BillingReference/cac:InvoiceDocumentReference/cbc:IssueDate');



		//ara toplam

		$araToplam = $xml->xpath('//cac:LegalMonetaryTotal/cbc:LineExtensionAmount');

		$data["araToplam"] = $araToplam[0][0];



		//toplam indirim

		$indirimTutar = $xml->xpath('//cac:LegalMonetaryTotal/cbc:AllowanceTotalAmount');

		$data["indirim"] = $indirimTutar[0];



		$netTutar = $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxExclusiveAmount');

		$data["netTutar"] = $netTutar[0];



		$vergiDahil = $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount');

		$data["vergiDahil"] = $vergiDahil[0];



		$genelToplam = $xml->xpath('//cac:LegalMonetaryTotal/cbc:PayableAmount');

		$data["genelToplam"] = $genelToplam[0];



		//istisna

		$istisnaKod = $xml->xpath('//cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReasonCode');



		//vergi muafiyet

		$vergiMuafiyet = $xml->xpath('//cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReason');

		$data["vergiMuafiyet"] = $vergiMuafiyet[0];//todo: test edilecek



		//cari

		if (count($cariID) > 0) {

			$cariID = $cariID[0];

			if (strlen($cariID) == 10)

				$cariQ = "SELECT * FROM cari where cari_vergiNumarasi='$cariID' AND cari_olusturanAnaHesap = '$anaHesap'";

			else

				$cariQ = "SELECT * FROM cari where cari_tckn='$cariID' AND cari_olusturanAnaHesap = '$anaHesap'";

			$data["cari"] = $this->db->query($cariQ)->row();



			if (count($data["cari"]) == 0) {

				$cariAd = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name');

				$cariAdresRoom = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:Room');

				$cariAdresStreet = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:StreetName');

				$cariAdresBuilding = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:BuildingName');

				$cariAdresBuildingNumber = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:BuildingNumber');

				$cariAdresPostal = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:PostalZone');

				$cariAdresCitySubdivisionName = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:CitySubdivisionName');

				$cariAdresCityName = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:CityName');

				$cariVergiDaire = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cac:TaxScheme/cbc:Name');

				$cariEposta = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail');

				$cariTelefon = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telephone');



				$data["cari"]->cari_ad = $cariAd[0];

				$data["cari"]->cari_adres = $cariAdresStreet[0] . " " . $cariAdresBuilding[0] . " No:" . $cariAdresBuildingNumber[0] . " " . $cariAdresPostal[0] . " " . $cariAdresCitySubdivisionName[0] . " / " . $cariAdresCityName[0];

				$data["cari"]->cari_kodu = $cariID;

				$data["cari"]->cari_vergiDairesi = $cariVergiDaire[0];



				$data["cari"]->cari_firmaEPosta = $cariEposta[0];

				$data["cari"]->cari_firmaTelefon = $cariTelefon[0];



			}

			$data["cari_value"] = $cariID . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;



		}



		//iade faturaları

		if (count($iadeFatNo) > 0) {

			$i = 0;

			foreach ($iadeFatNo as $item) {

				$iade[$i]["faturano"] = strval($item);

				$iade[$i]["faturatarih"] = explode(" ", $iadeFatTarih[$i])[0];

				$i++;

			}

			$data["iadeFaturalari"] = $iade;

		}



		//doviz kur

		if (count($dovizKur) > 0) {

			$data["doviz"] = $dovizKur[0];

		}



		//istisna

		if (count($istisnaKod) > 0) {

			$kod = strval($istisnaKod[0]);

			$istisnaQ = "SELECT * FROM istisnaKodlari where istisna_kodu='$kod'";

			$istisnaExe = $this->db->query($istisnaQ)->row();



			$data["istisna"] = "<option value='$istisnaExe->istisna_id'>$istisnaExe->istisna_kodu $istisnaExe->istisna_adi</option>";

		}



		$invoiceLine = $xml->xpath("//cac:InvoiceLine");



		$data["toplamTevkifatKdv"] = 0;



		for ($i = 0; $i < count($invoiceLine); $i++) {

			$j = $i + 1;

			$stokText = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:Item');

			$stokAd_orj = $stokText[0]->children("cbc", true)->Name;

			$stokAd = str_replace('\'', '', $stokAd_orj);

			$stokQ = "select stok_id,stok_ad,stok_kodu from stok where stok_olusturanAnaHesap = '$anaHesap' AND stok_ad like '%$stokAd%'";

			$stokExe = $this->db->query($stokQ)->row();



			if (count($stokExe) == 0) {

				$stokText = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:Item/cac:SellersItemIdentification');

				if ($stokText)

					$stokKod = $stokText[0]->children("cbc", true)->ID;

				$stok[$i]["kod"] = $stokKod;

				$stok[$i]["id"] = -1;

				$stok[$i]["ad"] = $stokAd;

			} else {

				$stok[$i]["kod"] = $stokExe->stok_kodu;

				$stok[$i]["id"] = $stokExe->stok_id;

				$stok[$i]["ad"] = $stokExe->stok_ad;

			}



			$miktar = $xml->xpath("//cac:InvoiceLine['.$j.']/cbc:InvoicedQuantity");

			$stok[$i]["miktar"] = explode(" ", $miktar[$i])[0];



			$birimFiyat = $xml->xpath("//cac:InvoiceLine['.$j.']/cac:Price/cbc:PriceAmount");

			$stok[$i]["birimFiyat"] = $birimFiyat[$i][0];



			$birim = $miktar[$i]["unitCode"];



			if($birim=="NIU")

				$birim="C62";

			$stokBirimQ = "select * from stokBirimleri where stokBirim_kodu='$birim' ";

			$stokBirimExe = $this->db->query($stokBirimQ)->row();

			$data["stokBirim"] = "<option value='$stokBirimExe->stokBirim_id'>$stokBirimExe->stokBirim_ad </option>";



			$kdvOran = $xml->xpath("//cac:InvoiceLine['.$j.']/cac:TaxTotal/cac:TaxSubtotal/cbc:Percent");

			$stok[$i]["stokKdvOran"] = $kdvOran[$i][0];



			$stokToplamFiyat = $xml->xpath('//cac:InvoiceLine/cbc:LineExtensionAmount');

			$toplamFiyat = explode(" ", $stokToplamFiyat[$i])[0];

			$stok[$i]["toplamFiyat"] = $toplamFiyat;



			$stokKdv = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:TaxTotal/cbc:TaxAmount');

			$kdv = explode(" ", $stokKdv[$i])[0];

			$data["toplamKdv"] += $kdv;



			$iskonto = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:AllowanceCharge');

			if ($iskonto)

				$stok[$i]["satirIndirim"] = explode(" ", $iskonto[0]->children("cbc", true)->MultiplierFactorNumeric)[0];

			else

				$stok[$i]["satirIndirim"] = 0;





			$tevkifat = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:WithholdingTaxTotal/cac:TaxSubtotal/cac:TaxCategory/cac:TaxScheme');

			if ($tevkifat) {

				$stok[$i]["tevkifatAd"] = $tevkifat[0]->children("cbc", true)->Name;

				$stok[$i]["tevkifatKod"] = explode(" ", $tevkifat[0]->children("cbc", true)->TaxTypeCode)[0];

				$stok[$i]["tevkifatOran"] = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:WithholdingTaxTotal/cac:TaxSubtotal/cbc:Percent')[0];



				$fiyat = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:WithholdingTaxTotal/cac:TaxSubtotal/cbc:TaxAmount')[0];



				$data["toplamTevkifatKdv"] += $fiyat;

			} else {

				$stok[$i]["tevkifatAd"] = "";

			}



		}



		$data["stok"] = $stok;

		$data["baslik"] = "E-Fatura İçe Aktar";

		$this->load->view("fatura/efaturaIceAktar", $data);

	}



	public function efaturaIceAktarKaydet()

	{

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$cari_id = postval("cari_id");

		$faturano = postval("faturaNumara");

		$paraBirimi=postval("paraBirimi");

		$paraBirimiTxt="TL";

		if($paraBirimi==2)

			$paraBirimiTxt="USD";

		else if($paraBirimi==3)

			$paraBirimiTxt="EUR";

		else if($paraBirimi==4)

			$paraBirimiTxt="GBP";



		//cari kayıtlı değil ise cari ekle

		if ($cari_id == "") {

			$cari_kod = postval("cari_kodu");

			//alias pk öğrenme (checkUser)

			$ws = env('AUTH_URL');



			$trace = true;

			$exceptions = false;



			$xml_array->REQUEST_HEADER->SESSION_ID = getSessionID();

			$xml_array->REQUEST_HEADER->APPLICATION_NAME = env('APPLICATION_NAME');

			$xml_array->USER->IDENTIFIER = $cari_kod; //Mükellefiyeti sorgulanacak firmanın vergi kimlik numarası

			$xml_array->DOCUMENT_TYPE = "INVOICE"; //	Mükelleffiyet kontrol edilecek ürün tipi. E-Fatura için INVOICE, E-İrsaliye için DESPATCHADVICE gönderilmelidir.



			$client = new SoapClient($ws, array('trace' => $trace, 'exceptions' => $exceptions));



			$response = $client->CheckUser($xml_array);



			$error_short_des = $response->ERROR_TYPE->ERROR_SHORT_DES;

			if (isSessionKilled($error_short_des) == 1) {//session koptu anlamına gelir, yeni session üretilip yazılmalı ve tekrar denenmelidir.

				generateNewSession();

				$this->efaturaIceAktarKaydet();

			} else {//session devam eder, normal prosedür devam et.

				$dataf = (array)$response;

				$alias_pk = $dataf["USER"][0]->ALIAS;

			}





			//cari ekleme

			$dataCari["cari_kodu"] = $cari_kod;

			$dataCari["cari_ad"] = postval("cari_ad");

			$dataCari["cari_vergiDairesi"] = postval("cari_vergiDairesi");

			if (strlen($cari_kod) == 10)

				$dataCari["cari_vergiNumarasi"] = $cari_kod;

			else if (strlen($cari_kod) == 11)

				$dataCari["cari_tckn"] = $cari_kod;

			$dataCari["cari_firmaEPosta"] = postval("cari_firmaEPosta");

			$dataCari["cari_firmaTelefon"] = postval("cari_firmaTelefon");

			$dataCari["cari_adres"] = postval("cari_adres");

			$dataCari["cari_olusturan"] = $u_id;

			$dataCari["cari_olusturanAnaHesap"] = $anaHesap;

			$dataCari["cari_olusturmaTarihi"] = $tarihi;

			$dataCari["cari_olusturmaSaati"] = $saati;

			$dataCari["cari_bireysel"] = 0;

			$dataCari["cari_alias_pk"] = $alias_pk;

			$this->db->insert("cari", $dataCari);

			$cari_id = $this->db->insert_id();



		}



		//cari hareketi ekle

		$dataCariHareket["ch_belgeNumarasi"] = $faturano;

		$dataCariHareket["ch_turu"] = 1;

		$dataCariHareket["ch_cariID"] = $cari_id;

		$dataCariHareket["ch_faturaID"] = -1;

		$dataCariHareket["ch_paraBirimi"]=$paraBirimiTxt;

/*

		if($dovizCevrilmis)

			$data_ch["ch_alacak"] =$dovizCevrilmis;

		else*/

		$dataCariHareket["ch_alacak"] = postval("geneltopHidden");

		$dataCariHareket["ch_tarih"] = $tarihi;



		$dataCariHareket["ch_olusturan"] = $u_id;

		$dataCariHareket["ch_olusturanAnaHesap"] = $anaHesap;

		$dataCariHareket["ch_olusturmaTarihi"] = $tarihi;

		$dataCariHareket["ch_olusturmaSaati"] = $saati;



		$this->vt->insert("cariHareketleri", $dataCariHareket);



		//stok ve stok hareketi ekle



		$stokid = postval("stokid");

		$kacAdetStok = count($stokid);





		$miktar = postval("miktar");

		$birimfiyat = postval("birimfiyat");

		$kdv = postval("kdv");

		$indirimliFiyat = postval("toplamHidden");

		$stokkodu = postval("stokkodu");

		$stokadi = postval("stokadi");

		$stokbirim = postval("stok_birimID");





		for ($i = 0; $i < $kacAdetStok; $i++) {

			if ($stokid[$i] == -1) {

				$dataStok["stok_kodu"] = $stokkodu[$i];

				$dataStok["stok_ad"] = $stokadi[$i];

				$dataStok["stok_birimID"] = $stokbirim[$i];

				$dataStok["stok_olusturan"] = $u_id;

				$dataStok["stok_olusturanAnaHesap"] = $anaHesap;

				$dataStok["stok_olusturmaTarihi"] = $tarihi;

				$dataStok["stok_olusturmaSaati"] = $saati;



				$this->vt->insert("stok", $dataStok);

				$stokid[$i] = $this->db->insert_id();

			}



			$toplamKDV = $indirimliFiyat[$i] * $kdv[$i] / 100;

			$toplamFiyat = $toplamKDV + $indirimliFiyat[$i];



			$data_sh["sh_turu"] = 1;

			$data_sh["sh_giris"] = $miktar[$i];

			$data_sh["sh_cariID"] = $cari_id;

			$data_sh["sh_stokID"] = $stokid[$i];

			$data_sh["sh_belgeNumarasi"] = $faturano;

			$data_sh["sh_faturaID"] = -1;

			$data_sh["sh_paraBirimi"]=$paraBirimiTxt;

			$data_sh["sh_tarih"] = $tarihi;

			$data_sh["sh_birimFiyat"] = $birimfiyat[$i];

			$data_sh["sh_kdv"] = $kdv[$i];

			$data_sh["sh_toplamKDV"] = $toplamKDV;

			$data_sh["sh_toplamFiyat"] = $toplamFiyat;

			$data_sh["sh_olusturan"] = $u_id;

			$data_sh["sh_olusturanAnaHesap"] = $anaHesap;

			$data_sh["sh_olusturmaTarihi"] = $tarihi;

			$data_sh["sh_olusturmaSaati"] = $saati;



			$this->vt->insert("stokHareketleri", $data_sh);

		}

		$this->session->set_flashdata('ice_aktar_ok', 'OK');

		redirect("fatura/efatura-gelen");

	}



	public function taslakFaturaYukle()

	{

		$data["baslik"] = "E-Fatura Yükle";

		$this->load->view("fatura/taslakFaturaYukle", $data);

	}



	public function taslakFaturaKaydet()

	{

		$path = 'assets/bulut/';

		if (!empty($_FILES['file']['name'])) {

			$config['upload_path'] = $path;

			$config['allowed_types'] = 'xml';

			$config['file_name'] = $_FILES['file']['name'];

			$config['encrypt_name'] = TRUE;

			$config['max_width'] = 250;

			$config['max_height'] = 250;



			$this->load->library('upload', $config);



			if ($this->upload->do_upload('file')) {

				$fileName = $this->upload->data('file_name');



				$xml = simplexml_load_file($path . "/" . $fileName);



				//xml dosyasını okuyup data array'e yaz

				$ch_turu=2;

				$sh_turu=2;



				$faturaNo = $xml->xpath('//cbc:ID');

				$faturaNo = $faturaNo[0];





				$control2 = session("r", "login_info");

				$u_id = $control2->kullanici_id;



				date_default_timezone_set('Europe/Istanbul');

				$tarihi = (new DateTime('now'))->format('Y.m.d');

				$saati = (new DateTime('now'))->format('H:i:s');



				$anaHesap = anaHesapBilgisi();



				//$dataSatis["satis_faturaNo"]=$faturaNo;



				#region cariKontrol



				//faturanın gönderileceği kişinin vkn sini veritabanında ara. yok ise cari oluştur. cari id sini al



				$cariID = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID');

				//cari

				if (count($cariID) > 0) {

					$cariID = $cariID[0];

					if (strlen($cariID) == 10)

						$cariQ = "SELECT * FROM cari where cari_vergiNumarasi='$cariID'";

					else

						$cariQ = "SELECT * FROM cari where cari_tckn='$cariID'";

					$cariExe = $this->db->query($cariQ)->row();



					if (count($cariExe) == 0) {

						$cariSoyad=null;

						$cariAd = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name');

						if(count($cariAd)==0) {

							$cariAd = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:FirstName');

							$cariSoyad = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:FamilyName');

							$cariSoyad=$cariSoyad[0];

						}

						$cariAdresRoom = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:Room');

						$cariAdresStreet = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:StreetName');

						$cariAdresBuilding = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:BuildingName');

						$cariAdresBuildingNumber = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:BuildingNumber');

						$cariAdresPostal = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:PostalZone');

						$cariAdresCitySubdivisionName = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:CitySubdivisionName');

						$cariAdresCityName = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:CityName');

						$cariVergiDaire = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cac:TaxScheme/cbc:Name');

						$cariEposta = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail');

						$cariTelefon = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telephone');



						$ws = env('AUTH_URL');



						$trace = true;

						$exceptions = false;



						$xml_array->REQUEST_HEADER->SESSION_ID = getSessionID();

						$xml_array->REQUEST_HEADER->APPLICATION_NAME = env('APPLICATION_NAME');

						$xml_array->USER->IDENTIFIER = $cariID; //Mükellefiyeti sorgulanacak firmanın vergi kimlik numarası

						$xml_array->DOCUMENT_TYPE = "INVOICE"; //	Mükelleffiyet kontrol edilecek ürün tipi. E-Fatura için INVOICE, E-İrsaliye için DESPATCHADVICE gönderilmelidir.



						$client = new SoapClient($ws, array('trace' => $trace, 'exceptions' => $exceptions));



						$response = $client->CheckUser($xml_array);



						$error_short_des = $response->ERROR_TYPE->ERROR_SHORT_DES;

						if (isSessionKilled($error_short_des) == 1) {//session koptu anlamına gelir, yeni session üretilip yazılmalı ve tekrar denenmelidir.

							generateNewSession();

							$this->efaturaIceAktarKaydet();

						} else {//session devam eder, normal prosedür devam et.

							$dataf = (array)$response;

							$alias_pk = $dataf["USER"][0]->ALIAS;

						}



						$dataCari["cari_ad"] = $cariAd[0];

						$dataCari["cari_soyad"] = $cariSoyad;

						$dataCari["cari_adres"] = $cariAdresStreet[0] . " " . $cariAdresBuilding[0] . " No:" . $cariAdresBuildingNumber[0] . " " . $cariAdresPostal[0] . " " . $cariAdresCitySubdivisionName[0] . " / " . $cariAdresCityName[0];

						$dataCari["cari_kodu"] = $cariID;

						if (strlen($cariID) == 10)

							$dataCari["cari_vergiNumarasi"] = $cariID;

						else

							$dataCari["cari_tckn"] = $cariID;

						$dataCari["cari_vergiDairesi"] = $cariVergiDaire[0];

						$dataCari["cari_firmaEPosta"] = $cariEposta[0];

						$dataCari["cari_firmaTelefon"] = $cariTelefon[0];



						$dataCari["cari_olusturan"] = $u_id;

						$dataCari["cari_olusturanAnaHesap"] = $anaHesap;

						$dataCari["cari_olusturmaTarihi"] = $tarihi;

						$dataCari["cari_olusturmaSaati"] = $saati;

						$dataCari["cari_bireysel"] = 0;

						$dataCari["cari_alias_pk"] = $alias_pk;



						$this->db->insert("cari", $dataCari);

						$cari_id = $this->db->insert_id();





					} else $cari_id = $cariExe->cari_id;

				}



				#endregion cariKontrol



				#region satisfaturasi



				$dataSatis["satis_cariID"] = $cari_id;



				$faturaTarihXml = $xml->xpath('//cbc:IssueDate');

				$dataSatis["satis_faturaTarihi"] = $faturaTarihXml[0];



				$faturaSaatXml = $xml->xpath('//cbc:IssueTime');

				$dataSatis["satis_faturaSaati"] = $faturaSaatXml[0];





				//ara toplam

				$araToplamXml = $xml->xpath('//cac:LegalMonetaryTotal/cbc:LineExtensionAmount');

				$dataSatis["satis_araToplam"] = $araToplamXml[0][0];



				//toplam indirim

				$indirimTutarXml = $xml->xpath('//cac:LegalMonetaryTotal/cbc:AllowanceTotalAmount');

				$dataSatis["satis_indirimToplam"] = $indirimTutarXml[0];



				$netTutarXml = $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxExclusiveAmount');

				$dataSatis["satis_netTutar"] = $netTutarXml[0];



				$vergiDahilXml = $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount');

				$dataSatis["satis_vergiDahilToplam"] = $vergiDahilXml[0];



				$genelToplamXml = $xml->xpath('//cac:LegalMonetaryTotal/cbc:PayableAmount');

				$dataSatis["satis_genelToplam"] = $genelToplamXml[0];





				//fatura tipi

				$faturaTipXml = $xml->xpath('//cbc:InvoiceTypeCode');

				$faturaTip = $faturaTipXml[0];

				if ($faturaTip == "SATIS")

					$dataSatis["satis_earsivfaturaTip"] = 1;

				else if ($faturaTip == "IADE")

					$dataSatis["satis_earsivfaturaTip"] = 2;

				else if ($faturaTip == "TEVKIFAT")

					$dataSatis["satis_earsivfaturaTip"] = 3;

				else if ($faturaTip == "ISTISNA")

					$dataSatis["satis_earsivfaturaTip"] = 4;



				//fatura senaryosu

				$senaryoXml = $xml->xpath('//cbc:ProfileID');

				$senaryo = $senaryoXml[0];

				$dataSatis["satis_InvoiceType"] = 1;

				if ($senaryo == "TEMELFATURA") {

					$dataSatis["satis_faturaSenaryo"] = 1;

					$ch_turu = 12;//e-fatura

					$sh_turu=3;//efatura

				}

				else if ($senaryo == "TICARIFATURA") {

					$dataSatis["satis_faturaSenaryo"] = 2;

					$ch_turu = 12;//e-fatura

					$sh_turu=3;//efatura

				}

				else if ($senaryo == "EARSIVFATURA") {

					$dataSatis["satis_InvoiceType"] = 2;

					$dataSatis["satis_faturaSenaryo"] = Null;

					$ch_turu=13;//e-arşiv

					$sh_turu=4;//earsiv

				}





				//fatura para birimi

				$faturaParaBirimiXml = $xml->xpath('//cbc:DocumentCurrencyCode');

				$faturaParaBirimi = $faturaParaBirimiXml[0];

				if ($faturaParaBirimi == "TRY")

					$dataSatis["satis_paraBirimi"] = 1;

				else {

					if ($faturaParaBirimi == "USD")

						$dataSatis["satis_paraBirimi"] = 2;

					else if ($faturaParaBirimi == "EUR")

						$dataSatis["satis_paraBirimi"] = 3;

					else if ($faturaParaBirimi == "GBP")

						$dataSatis["satis_paraBirimi"] = 4;

					$faturaKurXml = $xml->xpath('//cac:PricingExchangeRate/cbc:CalculationRate');

					$dataSatis["satis_guncelKur"] = $faturaKurXml[0];

					$dovizCevrilmis=$dataSatis["satis_genelToplam"] * $dataSatis["satis_guncelKur"];

					$dataSatis["satis_dovizCevrilmis"] = $dovizCevrilmis;

				}



				$notXML = $xml->xpath("//cbc:Note");

				if (count($notXML) != 0)

					$dataSatis["satis_aciklama"] = $notXML[0];



				//vergi muafiyet

				$vergiMuafiyetXml = $xml->xpath('//cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReason');

				if (count($vergiMuafiyetXml) != 0)

					$dataSatis["satis_vergiMuafiyetSebep"] = $vergiMuafiyetXml[0];

				else {

					//istisna

					$istisnaKodXml = $xml->xpath('//cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReasonCode');

					if (count($istisnaKodXml) > 0) {

						$kod = strval($istisnaKodXml[0]);

						$istisnaQ = "SELECT istisna_id FROM istisnaKodlari where istisna_kodu='$kod'";

						$istisnaExe = $this->db->query($istisnaQ)->row();

						$dataSatis["satis_istisna_id"] = $istisnaExe->istisna_id;

					}

				}

				//earsiv gonderim sekli(kağıt/elektronik)

				$gonderimSekliXml = "://cac:AdditionalDocumentReference/cbc:DocumentType";

				if (count($gonderimSekliXml) != 0) {

					if ($gonderimSekliXml[0] == "KAGIT")

						$dataSatis["satis_earsivGonderimSekli"] = 1;

					else if ($gonderimSekliXml[0] == "ELEKTRONIK")

						$dataSatis["satis_earsivGonderimSekli"] = 2;

				}



				//earsiv tipi(normal/internet)

				$gonderimSekliXml = "://cac:AdditionalDocumentReference/cbc:DocumentType";

				if (count($gonderimSekliXml) != 0) {

					if ($gonderimSekliXml[0] == "NORMAL")

						$dataSatis["satis_earsivTipi"] = 1;

					else if ($gonderimSekliXml[0] == "INTERNET")

						$dataSatis["satis_earsivTipi"] = 2;

				}

				$dataSatis["satis_faturaDurum"] = 1;





				$dataSatis["satis_olusturan"] = $u_id;

				$dataSatis["satis_olusturanAnaHesap"] = $anaHesap;

				$dataSatis["satis_olusturmaTarihi"] = $tarihi;

				$dataSatis["satis_olusturmaSaati"] = $saati;



				$this->db->insert("satisFaturasi", $dataSatis);

				$satis_id = $this->db->insert_id();





				#endregion



				#region satisfaturasistok



				$data["satisStok_satisFaturasiID"] = $satis_id;

				$invoiceLine = $xml->xpath("//cac:InvoiceLine");



				$toplamTevkifatKdv = 0;

				$toplamKdv = 0;

				$toplamIskonto = 0;

				for ($i = 0; $i < count($invoiceLine); $i++) {

					$j = $i + 1;

					$stokBirimText = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cbc:InvoicedQuantity ');

					$sqlbirim = "select stokBirim_id from stokBirimleri where stokBirim_kodu='" . $stokBirimText[0]["unitCode"] . "'";

					$sqlBirimExe = $this->db->query($sqlbirim)->row();





					$stokText = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:Item');

					$stokAd = $stokText[0]->children("cbc", true)->Name;

					$stokQ = "select stok_id,stok_ad,stok_kodu from stok where stok_ad like '%$stokAd%'";

					$stokExe = $this->db->query($stokQ)->row();

					if (count($stokExe) == 0) {

						$stokText = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:Item/cbc:Name');

						$dataStok["stok_ad"] = $stokText[0];



						$stokText = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:Item/cac:SellersItemIdentification');

						if (count($stokText) != 0) {

							$stokKod = $stokText[0]->children("cbc", true)->ID;



							$dataStok["stok_kodu"] = $stokKod[0];

						} else

							$dataStok["stok_kodu"] = $stokText[0];



						$dataStok["stok_birimID"] = $sqlBirimExe->stokBirim_id;

						$dataStok["stok_olusturan"] = $u_id;

						$dataStok["stok_olusturanAnaHesap"] = $anaHesap;

						$dataStok["stok_olusturmaTarihi"] = $tarihi;

						$dataStok["stok_olusturmaSaati"] = $saati;



						$this->vt->insert("stok", $dataStok);

						$data["satisStok_stokID"] = $this->db->insert_id();

					} else {

						$data["satisStok_stokID"] = $stokExe->stok_id;

					}





					$data["satisStok_miktar"] = explode(" ", $stokBirimText[$i])[0];



					$birimFiyat = $xml->xpath("//cac:InvoiceLine['.$j.']/cac:Price/cbc:PriceAmount");

					$data["satisStok_birimFiyat"] = $birimFiyat[$i][0];



					$kdvOran = $xml->xpath("//cac:InvoiceLine['.$j.']/cac:TaxTotal/cac:TaxSubtotal/cbc:Percent");

					$data["satisStok_kdv"] = $kdvOran[$i][0];



					$stokIndirimliFiyat = $xml->xpath('//cac:InvoiceLine/cbc:LineExtensionAmount');

					$indirimliFiyat = explode(" ", $stokIndirimliFiyat[$i])[0];

					$data["satisStok_indirimliFiyat"] = $indirimliFiyat;

					$data["satisStok_kullanici_sayisi"] = null; // Default value for XML import



					$stokKdv = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:TaxTotal/cbc:TaxAmount');

					$kdv = explode(" ", $stokKdv[$i])[0];

					$toplamKdv += $indirimliFiyat * $kdv / 100;





					$iskonto = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:AllowanceCharge');

					if ($iskonto) {

						$data["satisStok_satirIskonto"] = explode(" ", $iskonto[0]->children("cbc", true)->MultiplierFactorNumeric)[0];

						$toplamIskonto = 0;

					} else

						$data["satisStok_satirIskonto"] = 0;





					$tevkifat = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:WithholdingTaxTotal/cac:TaxSubtotal/cac:TaxCategory/cac:TaxScheme');

					if ($tevkifat) {



						$tevkifatKod = explode(" ", $tevkifat[0]->children("cbc", true)->TaxTypeCode)[0];

						$tevkifatsql = "select tevkifat_id from tevkifatKodlari where tevkifat_kodu='" . $tevkifatKod . "'";

						$tevkifatExe = $this->db->query($tevkifatsql)->row();

						$data["satisStok_tevkifat_id"] = $tevkifatExe->tevkifat_id;



						$fiyat = $xml->xpath('//cac:InvoiceLine[' . $j . ']/cac:WithholdingTaxTotal/cac:TaxSubtotal/cbc:TaxAmount')[0];



						$toplamTevkifatKdv += $fiyat;

					}





					$this->db->insert("satisFaturasiStok", $data);

					$satisStok_id=$this->db->insert_id();



					if($faturaParaBirimi=="TRY")

						$faturaParaBirimi="TL";



					$shKdv=$data["satisStok_indirimliFiyat"]*$kdv/100;

					$data_sh["sh_turu"] = $sh_turu;

					$data_sh["sh_cikis"] = $data["satisStok_miktar"];

					$data_sh["sh_cariID"] = $cari_id;

					$data_sh["sh_stokID"] = $data["satisStok_stokID"];

					$data_sh["sh_belgeNumarasi"] = null;

					$data_sh["sh_paraBirimi"]=$faturaParaBirimi;

					$data_sh["sh_faturaID"] = $satis_id;

					$data_sh["sh_tarih"] = $tarihi;

					$data_sh["sh_birimFiyat"] = $data["satisStok_birimFiyat"];

					$data_sh["sh_kdv"] = $kdv[$i];

					$data_sh["sh_toplamKDV"] = $shKdv;

					$data_sh["sh_toplamFiyat"] = $shKdv+$data["satisStok_indirimliFiyat"];

					$data_sh["sh_olusturan"] = $u_id;

					$data_sh["sh_olusturanAnaHesap"] = $anaHesap;

					$data_sh["sh_olusturmaTarihi"] = $tarihi;

					$data_sh["sh_olusturmaSaati"] = $saati;



					$this->vt->insert("stokHareketleri", $data_sh);



				}



				$toplamIskontoXML = $xml->xpath('//cac:AllowanceCharge/cbc:MultiplierFactorNumeric');

				foreach ($toplamIskontoXML as $x)

					$toplamIskonto += $x[0];



				$dataSatisToplam["satis_kdvToplam"] = $toplamKdv;

				$dataSatisToplam["satis_indirimToplam"] = $toplamIskonto;

				$dataSatisToplam["satis_tevkifatToplam"] = $toplamTevkifatKdv;

				$this->vt->update('satisFaturasi', array('satis_id' => $satis_id), $dataSatisToplam);



				#endregion



				#region iadefaturabilgileri

				//iade fatura bilgileri

				$iadeFatNoXml = $xml->xpath('//cac:BillingReference/cac:InvoiceDocumentReference/cbc:ID');

				$iadeFatTarihXml = $xml->xpath('//cac:BillingReference/cac:InvoiceDocumentReference/cbc:IssueDate');

				$dataIade["satis_id"] = $satis_id;

				if (count($iadeFatNoXml) > 0) {

					$i = 0;

					foreach ($iadeFatNoXml as $item) {

						$dataIade["fatura_numarasi"] = strval($item);

						$dataIade["tarih"] = explode(" ", $iadeFatTarihXml[$i])[0];

						$i++;

					}

					$this->db->insert("iade_fatura_numaralari", $dataIade);

				}

				#endregion



				#region bankaBilgileri

				$bankaIbanXml = $xml->xpath('//cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:ID');

				foreach ($bankaIbanXml as $iban) {

					$dataBanka["ayarlarbanka_ayarID"] = $anaHesap;

					$bankasql = "select * from ayarlar_banka where ayarlarbanka_ayarID=$anaHesap and ayarlarbanka_iban='$iban'";

					$bankaexe = $this->db->query($bankasql)->row();

					if (count($bankaexe) == 0) {

						$dataBanka["ayarlarbanka_iban"] = $iban;

						$bankaAdXml = $xml->xpath('//cac:PaymentMeans/cac:PayeeFinancialAccount/cac:FinancialInstitution/cbc:Name');

						$dataBanka["ayarlarbanka_ad"] = $bankaAdXml[0];

						$bankaSubeAdXml = $xml->xpath('//cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:Name');

						$dataBanka["ayarlarbanka_subeAd"] = $bankaSubeAdXml[0];

						$bankaParaBirimXml = $xml->xpath('//cac:PaymentMeans/cbc:CurrencyCode');

						$dataBanka["ayarlarbanka_paraBirim"] = $bankaParaBirimXml[0];



						$dataBanka["ayarlarbanka_olusturanAnaHesap"] = $anaHesap;

						$dataBanka["ayarlarbanka_olusturan"] = $u_id;

						$dataBanka["ayarlarbanka_olusturmaTarihi"] = $tarihi;

						$dataBanka["ayarlarbanka_olusturmaSaati"] = $saati;

						$this->db->insert("ayarlar_banka", $dataBanka);

						$ayarbankaID = $this->db->insert_id();

					} else

						$ayarbankaID = $bankaexe->ayarlarbanka_ayarID;



					$datafaturabanka["faturabanka_ayarbankaID"] = $ayarbankaID;

					$datafaturabanka["faturabanka_faturaID"] = $satis_id;

					$this->db->insert("faturabanka_bilgileri", $datafaturabanka);

				}

				#endregion



				#region earsivinternetbilgileri

				$deliveryXml = $xml->xpath('//cac:Delivery/cac:CarrierParty/cac:PartyIdentification/cbc:ID');



				if (count($deliveryXml) != 0) {

					$vkntckn = $deliveryXml[0];

					if (strlen($vkntckn) == 10)

						$dataInternet["earsiv_tasiyan"] = 2;

					else if (strlen($vkntckn) == 11)

						$dataInternet["earsiv_tasiyan"] = 1;

					$dataInternet["earsiv_tasiyanvkntckn"] = $vkntckn;



					$deliveryUnvanXml = $xml->xpath('//cac:Delivery/cac:CarrierParty/cac:PartyName/cbc:Name');

					$dataInternet["earsiv_tasiyanunvan"] = $deliveryUnvanXml[0];



					$deliveryTarihXml = $xml->xpath('//cac:Delivery/cac:Despatch/cbc:ActualDespatchDate');

					$dataInternet["earsiv_tasiyantarih"] = $deliveryTarihXml[0];



					$dataInternet["satis_id"] = $satis_id;



					$deliveryOdemeSekliXml = $xml->xpath('//cac:PaymentMeans/cbc:PaymentMeansCode');

					$dataInternet["earsiv_odemesekli"] = $deliveryOdemeSekliXml[0];



					$deliveryOdemeAraciXml = $xml->xpath('//cac:PaymentMeans/cbc:InstructionNote');

					$dataInternet["earsiv_odemearaci"] = $deliveryOdemeAraciXml[0];



					$deliveryOdemeTarihXml = $xml->xpath('//cac:PaymentMeans/cbc:PaymentDueDate');

					$dataInternet["earsiv_odemetarihi"] = $deliveryOdemeTarihXml[0];



					$dataInternet["earsiv_olusturma_tarih"] = $tarihi;

					$dataInternet["earsiv_olusturma_saat"] = $saati;

					//todo:test edilecek

					//$dataInternet["earsiv_internetsatisbilgisi"]="";

					//$dataInternet["earsiv_eposta"]="";



					$this->db->insert("earsiv_internet_bilgileri", $dataInternet);

				}

				#endregion



				#region genelIskonto

				$genelIskontoSebep = $xml->xpath('//cac:AllowanceCharge/cbc:AllowanceChargeReason');

				foreach ($genelIskontoSebep as $item) {

					$dataIskonto["iskonto_satis_id"] = $satis_id;

					$dataIskonto["iskonto_sebep"] = $item;

					$genelIskontoTutar = $xml->xpath('//cac:AllowanceCharge/cbc:MultiplierFactorNumeric');

					$dataIskonto["iskonto_tutar"] = $genelIskontoTutar[0];

					if ($dataIskonto["iskonto_tutar"] == null) {

						$this->session->set_flashdata('sablon-hata', 'OK');

						redirect("fatura/taslakFaturaYukle");

					}

					$this->db->insert("genel_iskonto", $dataIskonto);



				}

				#endregion



				//Cari Hareketleri Ekle :begin

				$data_ch["ch_belgeNumarasi"] = null;

				$data_ch["ch_turu"] = $ch_turu;

				$data_ch["ch_cariID"] = $cari_id;

				$data_ch["ch_faturaID"] = $satis_id;

				$data_ch["ch_paraBirimi"]=$faturaParaBirimi;

/*

				if($dovizCevrilmis)

					$data_ch["ch_borc"] =$dovizCevrilmis;

				else*/

				$data_ch["ch_borc"] = $genelToplamXml[0];

				$data_ch["ch_tarih"] = $tarihi;



				$data_ch["ch_olusturan"] = $u_id;

				$data_ch["ch_olusturanAnaHesap"] = $anaHesap;

				$data_ch["ch_olusturmaTarihi"] = $tarihi;

				$data_ch["ch_olusturmaSaati"] = $saati;



				$this->vt->insert("cariHareketleri", $data_ch);

				$ch_id = $this->db->insert_id();

				$datachu["satis_cariHareketiID"] = $ch_id;



				$this->vt->update('satisFaturasi', array('satis_id' => $satis_id), $datachu);



				unlink($path . "/" . $fileName);



				$this->session->set_flashdata('fatura-yuklendi', 'OK');

				if ($dataSatis["satis_InvoiceType"] == 1)

					redirect("fatura/efatura-giden");

				else if ($dataSatis["satis_InvoiceType"] == 2)

					redirect("fatura/earsiv-faturalari");



			} else

				$this->session->set_flashdata('dosya-yuklenemedi', 'OK');



		} else

			$this->session->set_flashdata('dosya-yok', 'OK');



		redirect("fatura/taslakFaturaYukle");

	}



	public function efatura_donustur()

	{

		$faturaID = $this->input->get('efatura_fatura_id');



		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$data["satis_InvoiceType"]=1;

		$data["satis_faturaNo"]=null;

		$data["satis_faturaDurum"]=1;

		$data["satis_faturaSenaryo"]=1;

		$data["satis_olusturan"] = $u_id;

		$data["satis_olusturanAnaHesap"] = $anaHesap;

		$data["satis_olusturmaTarihi"] = $tarihi;

		$data["satis_olusturmaSaati"] = $saati;



		$this->db->update("satisFaturasi", $data,array('satis_id' => $faturaID));

		$this->session->set_flashdata('fatura_donusturuldu', 'OK');

		redirect("fatura/efaturaGiden");

	}



	public function earsiv_donustur()

	{

		$faturaID = $this->input->get('earsiv_fatura_id');



		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$data["satis_InvoiceType"]=2;

		$data["satis_faturaNo"]=null;

		$data["satis_faturaDurum"]=1;

		$data["satis_earsivTipi"]=1;

		$data["satis_earsivGonderimSekli"]=1;

		$data["satis_olusturan"] = $u_id;

		$data["satis_olusturanAnaHesap"] = $anaHesap;

		$data["satis_olusturmaTarihi"] = $tarihi;

		$data["satis_olusturmaSaati"] = $saati;



		$this->db->update("satisFaturasi", $data,array('satis_id' => $faturaID));

		$this->session->set_flashdata('fatura_donusturuldu', 'OK');

		redirect("fatura/earsiv-faturalari");

	}



	public function efatura_sil()

	{

		$id = $this->input->get('fatura_id');

		$del1 = "DELETE FROM satisFaturasi WHERE satis_id=$id";

		$del2 = "DELETE FROM satisFaturasiStok WHERE satisStok_satisFaturasiID=$id";

		$del3 = "DELETE FROM stokHareketleri WHERE sh_faturaID=$id";

		$del4 = "DELETE FROM cariHareketleri WHERE ch_faturaID=$id";

		$del5 = "DELETE FROM faturabanka_bilgileri WHERE faturabanka_faturaID=$id";

		$del6 = "DELETE FROM genel_iskonto WHERE iskonto_satis_id=$id";

		$del7 = "DELETE FROM iade_fatura_numaralari WHERE satis_id=$id";

		$this->db->query($del1);

		$this->db->query($del2);

		$this->db->query($del3);

		$this->db->query($del4);

		$this->db->query($del5);

		$this->db->query($del6);

		$this->db->query($del7);

		$this->session->set_flashdata('fatura_sil_ok', 'OK');

		redirect("fatura/efaturaGiden");

	}



	public function earsiv_sil()

	{

		$id = $this->input->get('fatura_id');

		$del1 = "DELETE FROM satisFaturasi WHERE satis_id=$id";

		$del2 = "DELETE FROM satisFaturasiStok WHERE satisStok_satisFaturasiID=$id";

		$del3 = "DELETE FROM stokHareketleri WHERE sh_faturaID=$id";

		$del4 = "DELETE FROM cariHareketleri WHERE ch_faturaID=$id";

		$del5 = "DELETE FROM faturabanka_bilgileri WHERE faturabanka_faturaID=$id";

		$del6 = "DELETE FROM genel_iskonto WHERE iskonto_satis_id=$id";

		$del7 = "DELETE FROM iade_fatura_numaralari WHERE satis_id=$id";

		$del8 = "DELETE FROM earsiv_internet_bilgileri WHERE satis_id=$id";

		$this->db->query($del1);

		$this->db->query($del2);

		$this->db->query($del3);

		$this->db->query($del4);

		$this->db->query($del5);

		$this->db->query($del6);

		$this->db->query($del7);

		$this->db->query($del8);

		$this->session->set_flashdata('fatura_sil_ok', 'OK');

		redirect("fatura/earsiv-faturalari");

	}





	public function proformaFaturasi($id=0)

	{

		$data["baslik"] = "Fatura / Proforma Faturası Oluştur";



		//logekle(11,1);

		$anaHesap = anaHesapBilgisi();



		$tevkifatQ = "SELECT * FROM tevkifatKodlari";

		$tevkifatExe = $this->db->query($tevkifatQ)->result();

		$tevkifat = "";

		foreach ($tevkifatExe as $item) {

			$tevkifat .= "<option value='" . $item->tevkifat_orani . "|" . $item->tevkifat_id . "'>" . $item->tevkifat_kodu . "-" . $item->tevkifat_adi . "</option>";

		}

		$data["tevkifat"] = $tevkifat;



		$istisnaQ = "SELECT * FROM istisnaKodlari";

		$istisnaExe = $this->db->query($istisnaQ)->result();

		$istisna = "";

		foreach ($istisnaExe as $item) {

			$istisna .= "<option value='" . $item->istisna_id . "'>" . $item->istisna_kodu . " " . $item->istisna_adi . "</option>";

		}

		$data["istisna"] = $istisna;



		$sqlBanka = "select * from ayarlar_banka where ayarlarbanka_olusturanAnaHesap=" . $anaHesap . " ";

		$data["faturabanka"] = $this->db->query($sqlBanka)->result();



		//düzenleme için veritabanından verilerin çekilmesi

		if ($id != 0) {

			$data["baslik"] = "Fatura / Proforma Fatura Düzenle";

			$sql = "select * from proformaFaturasi where proforma_id=" . $id;

			$faturaDetay = $this->db->query($sql)->row();

			$data["faturaDetay"]=$faturaDetay ;



			$sqlEtiket="select * from etiketler where etiket_id=".$faturaDetay->proforma_faturaEtiketID;

			$exeEtiket=$this->db->query($sqlEtiket)->row();

			$data["etiket"]=$exeEtiket;





			$sqlStok = "select * from proformaFaturasiStok inner join stok on proformaStok_stokID=stok_id inner join stokBirimleri on stokBirim_id=stok_birimID where proformaStok_proformaFaturasiID=" . $id;

			$data["stok"] = $this->db->query($sqlStok)->result();



			$sqlGenelIskonto = "select * from genel_iskonto inner join proformaFaturasi on iskonto_satis_id=proforma_id where proforma_id=" . $id;

			$data["genelIskonto"] = $this->db->query($sqlGenelIskonto)->result();



			$sqlIade = "select * from iade_fatura_numaralari inner join proformaFaturasi on iade_fatura_numaralari.satis_id=proformaFaturasi.proforma_id where proformaFaturasi.proforma_id=" . $id;

			$data["iadeFaturalari"] = $this->db->query($sqlIade)->result();



			$sqlCari = "select * from proformaFaturasi inner join cari on proforma_cariID=cari_id where proforma_cariID=" . $data["faturaDetay"]->proforma_cariID;

			$data["cari"] = $this->db->query($sqlCari)->row();

			if ($data["cari"]->cari_vergiNumarasi != null)

				$numara = $data["cari"]->cari_vergiNumarasi;

			else if ($data["cari"]->cari_tckn != null)

				$numara = $data["cari"]->cari_tckn;

			$data["cari_value"] = $numara . " - " . $data["cari"]->cari_ad . " " . $data["cari"]->cari_soyad;



			$sqlBanka = "select * from faturabanka_bilgileri inner join ayarlar_banka on faturabanka_ayarbankaID=ayarlarbanka_id where faturabanka_faturaID=" . $id . " ";

			$data["faturabankaDuzenle"] = $this->db->query($sqlBanka)->result();



		}

		$iller = $this->db->get('iller');



		if ($iller->num_rows() > 0) {

			$data['_iller'] = $iller->result();

		} else {

			$data['_iller'] = false;

		}



		$stokBirimleriQ = "SELECT * FROM stokBirimleri";

		$birimler = $this->db->query($stokBirimleriQ)->result();

		$option ="";

		foreach ($birimler as $birim)

		{

			$option.="<option value='$birim->stokBirim_id'>$birim->stokBirim_ad</option>";

		}

		$data["birimler"]=$option;

		$this->load->view("fatura/proforma-faturasi",$data);

	}



	public function proformaFaturasiOlustur(){

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$cariKodu = postval("cari_kodu");



		$genelToplamHidden = postval("geneltopHidden");

		$indirimToplamHidden = postval("indirimtopHidden");

		$tevkifatToplamHidden = postval("tevkifattopHidden");

		$vergidahilHidden = postval("vergidahilHidden");



		$istisna_sebebi = postval("istisna_sebebi");

		$vergiMuafiyet_sebep = postval("vergiMuafiyet_sebep");



		if (postval("proforma_paraBirimi") == 1) {

			$guncelKur = NULL;

		} else {

			$guncelKur = postval("proforma_guncelKur");

			$dovizCevrilmis = $genelToplamHidden * $guncelKur;

		}



		$data["proforma_durum"] = 0;

		$data["proforma_faturaTipi"] = postval("satis_faturaTip");





		$proforma_etiketID=postval("etiket_id");



		if(empty($proforma_etiketID))

		{

			$proforma_faturaEtiketID=postval("proforma_faturaEtiketID");

			if(!empty($proforma_faturaEtiketID))

			{

				$dataEtiket["etiket_adi"]=$proforma_faturaEtiketID;

				$dataEtiket["etiket_olusturan"] = $u_id;

				$dataEtiket["etiket_olusturanAnaHesap"] = $anaHesap;

				$dataEtiket["etiket_olusturmaTarihi"] = $tarihi;

				$dataEtiket["etiket_olusturmaSaati"] = $saati;

				$this->db->insert("etiketler",$dataEtiket);

				$proforma_etiketID=$this->db->insert_id();

			}

		}



		$data["proforma_faturaEtiketID"]=$proforma_etiketID;



		$faturaNo=postval("proforma_faturaNo");

		$faturaTarihi=postval("proforma_faturaTarihi");

		$faturaSaati=postval("proforma_faturaSaati");

		$paraBirimi=postval("proforma_paraBirimi");



		$data["proforma_cariID"] = postval("proforma_cariID");

		$data["proforma_paraBirimi"] = $paraBirimi;

		$data["proforma_guncelKur"] = $guncelKur;

		$data["proforma_dovizCevrilmis"] = $dovizCevrilmis;

		$data["proforma_faturaNo"] = $faturaNo;

		$data["proforma_faturaTarihi"] = $faturaTarihi;

		$data["proforma_faturaSaati"] = $faturaSaati;

		$data["proforma_aciklama"] = postval("proforma_aciklama");

		$data["proforma_araToplam"] = postval("aratopHidden");

		$data["proforma_kdvToplam"] = postval("kdvtopHidden");

		$data["proforma_genelToplam"] = $genelToplamHidden;

		$data["proforma_vergiDahilToplam"] = $vergidahilHidden;

		$data["proforma_indirimToplam"] = $indirimToplamHidden;

		$data["proforma_netTutar"] = postval("netTutarInput");



		if ($tevkifatToplamHidden != null)

			$data["proforma_tevkifatToplam"] = $tevkifatToplamHidden;

		if ($istisna_sebebi != null)

			$data["proforma_istisna_id"] = $istisna_sebebi;



		if ($vergiMuafiyet_sebep != null)

			$data["proforma_vergiMuafiyetSebep"] = $vergiMuafiyet_sebep;



		$data["proforma_olusturan"] = $u_id;

		$data["proforma_olusturanAnaHesap"] = $anaHesap;

		$data["proforma_olusturmaTarihi"] = $tarihi;

		$data["proforma_olusturmaSaati"] = $saati;



		$cariKontrolQ = "SELECT * FROM cari WHERE (cari_kodu = '$cariKodu') AND cari_olusturanAnaHesap = '$anaHesap' ";

		$cariKontrol = $this->db->query($cariKontrolQ)->row();



		if (!$cariKontrol) {

			$this->session->set_flashdata('cari_hatali', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		$faturaVarmiQ = "SELECT * FROM proformaFaturasi WHERE  proforma_faturaNo = '$faturaNo' AND proforma_olusturanAnaHesap = '$anaHesap'";

		$faturaVarmiExe = $this->db->query($faturaVarmiQ)->result();



		if ($faturaVarmiExe) {

			$this->session->set_flashdata('fatura_mukerrer', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		$this->vt->insert("proformaFaturasi", $data);

		$proforma_id = $this->db->insert_id();



		$bankaCheck = postval("bankaCheck");

		if ($bankaCheck == 1) {

			$bankaCount = postval("bankaCount");

			echo $bankaCount;

			if ($bankaCount != 0) {//banka bilgileri

				$ayarlarbanka_id = postval("ayarlarbanka_id");

				$dataBanka["faturabanka_faturaID"] = $proforma_id;

				for ($i = 0; $i < $bankaCount; $i++) {

					$dataBanka["faturabanka_ayarbankaID"] = $ayarlarbanka_id[$i];

					$this->vt->insert("faturabanka_bilgileri", $dataBanka);

				}

			}

		}



		$iade_count = postval("iade_count");

		if ($iade_count != 0) {//arşiv iade



			$iadenumara = postval("iadenumara");

			$iadetarih = postval("iadetarih");

			$dataIade["satis_id"] = $proforma_id;

			//iade_fatura_numaralari (e-arşiv iade fatura numaraları)

			for ($i = 0; $i < $iade_count; $i++) {

				$dataIade["fatura_numarasi"] = $iadenumara[$i];

				$dataIade["tarih"] = $iadetarih[$i];



				$this->vt->insert("iade_fatura_numaralari", $dataIade);



			}

		}



		$indirim_count = postval("indirim_count");

		if ($indirim_count != 0) {//genel iskonto

			$indirimsebep = postval("indirimsebep");

			$indirimtutar = postval("indirimtutar");

			$dataIndirim["iskonto_satis_id"] = $proforma_id;

			//genel_iskonto (faturaya yapılan genel indirimler)

			for ($i = 0; $i < $indirim_count; $i++) {

				if ($indirimsebep[$i] == 0)

					$dataIndirim["iskonto_sebep"] = "İskonto";

				else if ($indirimsebep[$i] == 1)

					$dataIndirim["iskonto_sebep"] = "Nakit İskontosu";

				else if ($indirimsebep[$i] == 2)

					$dataIndirim["iskonto_sebep"] = "Diğer";



				$dataIndirim["iskonto_tutar"] = $indirimtutar[$i];



				$this->vt->insert("genel_iskonto", $dataIndirim);



			}

		}



		//proforma faturası stok

		$dataStok["proformaStok_proformaFaturasiID"] = $proforma_id;

		$stokid = postval("stokid");

		$stokadi=postval("stokadi");

		$stokkodu=postval("stokkodu");

		$barkod=postval("barkod");

		$birim=postval("birim");



		$kacAdetStok = count($stokid);



		$miktar = postval("miktar");

		$birimfiyat = postval("birimfiyat");

		$kdv = postval("kdv");

		$toplam = postval("toplamHidden");



		$indirimtutari = postval("indirimyuzde");

		$tevkifatInput = postval("tevkifat");

		for ($i = 0; $i < $kacAdetStok; $i++) {

			$stokidsi=$stokid[$i];

			if(empty($stokid[$i])) {

				$dataStokX["stok_kodu"] = $stokkodu[$i];

				$stokQ="select * from stok where stok_kodu='".$dataStokX["stok_kodu"]."' and stok_kodu!=''";

				$stokExe=$this->db->query($stokQ)->row();

				if($stokExe)

				{

					$stokidsi=$stokExe->stok_id;

				}

				else {

					$dataStokX["stok_ad"] = $stokadi[$i];

					$dataStokX["stok_barkod"] = $barkod[$i];

					$dataStokX["stok_satisFiyati"] = $birimfiyat[$i];

					$dataStokX["stok_alisFiyati"] = 0;

					$dataStokX["stok_satisKDV"] = $kdv[$i];

					$dataStokX["stok_alisKDV"] = 0;

					$dataStokX["stok_birimID"] = $birim[$i];

					$dataStokX["stok_olusturan"] = $u_id;

					$dataStokX["stok_olusturanAnaHesap"] = $anaHesap;

					$dataStokX["stok_olusturmaTarihi"] = $tarihi;

					$dataStokX["stok_olusturmaSaati"] = $saati;



					$this->vt->insert("stok", $dataStokX);

					$stokidsi = $this->db->insert_id();

				}



			}





			if ($kdv[$i] == 0) {

				$yeniKDV = 0;

			} else if ($kdv[$i] == 1) {

				$yeniKDV = 0.01;

			} else if ($kdv[$i] == 8) {

				$yeniKDV = 0.08;

			} else if ($kdv[$i] == 18) {

				$yeniKDV = 0.18;

			}



			$yeniKDVHesap = $toplam[$i] * $yeniKDV;

			$yeniKDVHesap2 = $toplam[$i] + $yeniKDVHesap;





			$tevkifat = explode("|", $tevkifatInput[$i]);



			$dataStok["proformaStok_stokID"] = $stokidsi;

			$dataStok["proformaStok_miktar"] = $miktar[$i];

			$dataStok["proformaStok_birimFiyat"] = $birimfiyat[$i];

			$dataStok["proformaStok_fiyatMiktar"] = $birimfiyat[$i] * $miktar[$i];

			$dataStok["proformaStok_kdv"] = $kdv[$i];

			$dataStok["proformaStok_indirimTutari"] = $indirimtutari[$i];

			$dataStok["proformaStok_satirIskonto"] = $birimfiyat[$i] * $miktar[$i] * $indirimtutari[$i] / 100;

			$dataStok["proformaStok_indirimlifiyat"] = $toplam[$i];





			if ($tevkifat[0] == 0)

				$dataStok["proformaStok_tevkifat_id"] = 0;

			else

				$dataStok["proformaStok_tevkifat_id"] = $tevkifat[1];





			$this->vt->insert("proformaFaturasiStok", $dataStok);



			$paraBirimiTxt="TL";

			if($paraBirimi==2)

				$paraBirimiTxt="USD";

			else if($paraBirimi==3)

				$paraBirimiTxt="EUR";

			else if($paraBirimi==4)

				$paraBirimiTxt="GBP";



		}



		//Satış Sözleşmesi stok



		$this->session->set_flashdata('fatura_ok', 'OK');

		logekle(11, 2);

		redirect("fatura/proforma-listesi");





	}



	public function proformaListesi()

	{

		$data["baslik"] = "Fatura / Proforma Fatura Listesi";

		$anaHesap = anaHesapBilgisi();



		$faturaNo = $this->input->get('faturaNo');

		$etiket = $this->input->get('etiket');



		$tarihGet = $this->input->get('tarihAraligi');



		$tarihAraligi = explode(' - ', $tarihGet);



		$tarih1 = date('Y-m-d', strtotime($tarihAraligi[0])); //küçük tarih

		$tarih2 = date('Y-m-d', strtotime($tarihAraligi[1])); //büyük tarih



		$siralama = $this->input->get('siralama');



		$urim = $this->uri->segment(2);



		$segment = 2;

		$sayfa = $this->input->get("sayfa");



		$page = $sayfa ? $sayfa : 0;

		$limit = 20;



		if ($sayfa) {

			$pagem = ($page - 1) * $limit;

		} else {

			$pagem = 0;

		}



		if ((isset($faturaNo) && !empty($faturaNo)) || (isset($etiket) && !empty($etiket)) || (isset($tarihGet) && !empty($tarihGet)) || (isset($siralama) && !empty($siralama))) {





			if (!empty($tarihGet)) {

				$sorgu1 = "AND proforma_faturaTarihi BETWEEN '$tarih1' AND '$tarih2'";

			} else {

				$sorgu1 = "";

			}



			if (!empty($etiket)) {

				$sorgu2 = "AND proforma_faturaEtiketID = '$etiket'";

			} else {

				$sorgu2 = "";

			}



			if (!empty($siralama)) {

				if ($siralama == 1) {//Tarih büyükten küçüğe

					$sira = "ORDER BY DATE(proforma_faturaTarihi) DESC";

				} else if ($siralama == 2) {//Tarih küçükten büyüğe

					$sira = "ORDER BY DATE(proforma_faturaTarihi) ASC";

				} else {

					$sira = "ORDER BY proforma_id DESC";

				}

			} else {

				$sira = "ORDER BY proforma_id DESC";

			}



			$countq = "SELECT COUNT(*) as total FROM proformaFaturasi WHERE proforma_olusturanAnaHesap = '$anaHesap' AND proforma_faturaNo LIKE '%$faturaNo%'  " . $sorgu1 . " ".$sorgu2;

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;



			$sorgu = "SELECT * FROM proformaFaturasi WHERE proforma_olusturanAnaHesap = '$anaHesap' AND proforma_faturaNo LIKE '%$faturaNo%' " . $sorgu1 . " ".$sorgu2." " . $sira . " LIMIT $pagem,$limit";

		} else {

			$countq = "SELECT COUNT(*) as total FROM proformaFaturasi WHERE proforma_olusturanAnaHesap = '$anaHesap'";

			$countexe = $this->db->query($countq)->row();

			$count = $countexe->total;

			$sorgu = "SELECT * FROM proformaFaturasi WHERE proforma_olusturanAnaHesap = '$anaHesap' ORDER BY proforma_id DESC LIMIT $pagem,$limit";

		}

		$data["count_of_list"] = $count;



		$this->load->library("pagination");



		$config = array();

		$config["base_url"] = base_url() . "/fatura/$urim";

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

		$data["proformaFaturasi"] = $this->db->query($sorgu)->result();



		$this->load->view("fatura/proforma-listesi", $data);

	}



	public  function proformaFaturasiGuncelle()

	{



		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();





		$proforma_id = postval("proforma_id");



		$faturaNo = postval("proforma_faturaNo");



		$cariKodu = postval("cari_kodu");



		$genelToplamHidden = postval("geneltopHidden");

		$indirimToplamHidden = postval("indirimtopHidden");

		$tevkifatToplamHidden = postval("tevkifattopHidden");

		$vergidahilHidden = postval("vergidahilHidden");



		$istisna_sebebi = postval("istisna_sebebi");

		$vergiMuafiyet_sebep = postval("vergiMuafiyet_sebep");



		if (postval("proforma_paraBirimi") == 1) {

			$guncelKur = NULL;

		} else {

			$guncelKur = postval("proforma_guncelKur");

			$dovizCevrilmis = $genelToplamHidden * $guncelKur;

		}



		$data["proforma_faturaTipi"] = postval("proforma_faturaTip");





		$proforma_etiketID=postval("etiket_id");

		$data["proforma_faturaEtiketID"] = $proforma_etiketID;



		$paraBirimi=postval("proforma_paraBirimi");



		$data["proforma_cariID"] = postval("proforma_cariID");



		$data["proforma_paraBirimi"] = $paraBirimi;

		$data["proforma_guncelKur"] = $guncelKur;

		$data["proforma_dovizCevrilmis"] = $dovizCevrilmis;

		$data["proforma_faturaNo"] = $faturaNo;

		$data["proforma_faturaTarihi"] = postval("proforma_faturaTarihi");

		$data["proforma_faturaSaati"] = postval("proforma_faturaSaati");

		$data["proforma_aciklama"] = postval("proforma_aciklama");

		$data["proforma_araToplam"] = postval("aratopHidden");

		$data["proforma_kdvToplam"] = postval("kdvtopHidden");

		$data["proforma_genelToplam"] = $genelToplamHidden;

		$data["proforma_vergiDahilToplam"] = $vergidahilHidden;

		$data["proforma_indirimToplam"] = $indirimToplamHidden;

		$data["proforma_netTutar"] = postval("netTutarInput");





		if ($tevkifatToplamHidden != null)

			$data["proforma_tevkifatToplam"] = $tevkifatToplamHidden;

		if ($istisna_sebebi != null)

			$data["proforma_istisna_id"] = $istisna_sebebi;





		if ($vergiMuafiyet_sebep != null)

			$data["proforma_vergiMuafiyetSebep"] = $vergiMuafiyet_sebep;



		$data["proforma_olusturan"] = $u_id;

		$data["proforma_olusturanAnaHesap"] = $anaHesap;

		$data["proforma_olusturmaTarihi"] = $tarihi;

		$data["proforma_olusturmaSaati"] = $saati;



		$cariKontrolQ = "SELECT * FROM cari WHERE (cari_kodu = '$cariKodu') AND cari_olusturanAnaHesap = '$anaHesap'";

		$cariKontrol = $this->db->query($cariKontrolQ)->row();



		if (!$cariKontrol) {

			$this->session->set_flashdata('cari_hatali', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}



		$faturaVarmiQ = "SELECT * FROM proformaFaturasi WHERE proforma_faturaNo='$' and proforma_olusturanAnaHesap = '$anaHesap' ";

		$faturaVarmiExe = $this->db->query($faturaVarmiQ)->row();



		/*if ($faturaVarmiExe) {

			$this->session->set_flashdata('fatura_mukerrer', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}

*/

		//if ($faturaVarmiExe)

			$this->vt->update('proformaFaturasi', array('proforma_id' => $proforma_id), $data);



		//iade

		$iade_count = postval("iade_count");



		$this->vt->del("iade_fatura_numaralari", "satis_id", $proforma_id);

		if ($iade_count != 0) {// iade

			$iadenumara = postval("iadenumara");

			$iadetarih = postval("iadetarih");

			$dataIade["satis_id"] = $proforma_id;

			//iade_fatura_numaralari (e-arşiv iade fatura numaraları)

			for ($i = 0; $i < $iade_count; $i++) {

				$dataIade["fatura_numarasi"] = $iadenumara[$i];

				$dataIade["tarih"] = $iadetarih[$i];



				$this->vt->insert("iade_fatura_numaralari", $dataIade);



			}

		}

		//indirim

		$indirim_count = postval("indirim_count");

		$this->vt->del("genel_iskonto", "iskonto_satis_id", $proforma_id);

		if ($indirim_count != 0) {//genel iskonto

			$indirimsebep = postval("indirimsebep");

			$indirimtutar = postval("indirimtutar");

			$dataIndirim["iskonto_satis_id"] = $proforma_id;

			//genel_iskonto (faturaya yapılan genel indirimler)

			for ($i = 0; $i < $indirim_count; $i++) {

				if ($indirimsebep[$i] == 0)

					$dataIndirim["iskonto_sebep"] = "İskonto";

				else if ($indirimsebep[$i] == 1)

					$dataIndirim["iskonto_sebep"] = "Nakit İskontosu";

				else if ($indirimsebep[$i] == 2)

					$dataIndirim["iskonto_sebep"] = "Diğer";



				$dataIndirim["iskonto_tutar"] = $indirimtutar[$i];



				$this->vt->insert("genel_iskonto", $dataIndirim);



			}

		}



		$bankaCheck = postval("bankaCheck");

		$this->vt->del("faturabanka_bilgileri", "faturabanka_faturaID", $proforma_id);

		if ($bankaCheck == 1) {

			$bankaCount = postval("bankaCount");

			echo $bankaCount;

			if ($bankaCount != 0) {//banka bilgileri

				$ayarlarbanka_id = postval("ayarlarbanka_id");

				$dataBanka["faturabanka_faturaID"] = $proforma_id;

				for ($i = 0; $i < $bankaCount; $i++) {

					$dataBanka["faturabanka_ayarbankaID"] = $ayarlarbanka_id[$i];

					print_r($dataBanka);

					$this->vt->insert("faturabanka_bilgileri", $dataBanka);

				}

			}

		}



		//Satış Sözleşmesi stok

		$dataStok["proformaStok_proformaFaturasiID"] = $proforma_id;

		$stokid = postval("stokid");

		$kacAdetStok = count($stokid);



		$proformaFaturasiStok_id = postval("proformaFaturasiStok_id");



		$stoklarQ = "SELECT * FROM proformaFaturasiStok WHERE proformaStok_proformaFaturasiID = '$proforma_id'";

		$stoklarExe = $this->db->query($stoklarQ)->result();



		$newArray = [];

		foreach ($stoklarExe as $sexe) {

			$newArray[] = $sexe->proformaStok_id;

		}



		$diff = array_diff($newArray, $proformaFaturasiStok_id);



		if (!empty($diff)) {

			foreach ($diff as $key => $value) {

				$this->vt->del("proformaFaturasiStok", "proformaStok_id", $value);

			}

		}

		$miktar = postval("miktar");

		$birimfiyat = postval("birimfiyat");

		$kdv = postval("kdv");

		$stokadi = postval("stokadi");

		$stokkodu = postval("stokkodu");

		$barkod= postval("barkod");

		$birim= postval("birim");

		$toplam = postval("toplamHidden");

		$indirimtutari = postval("indirimyuzde");

		$tevkifatInput = postval("tevkifat");





		for ($i = 0; $i < $kacAdetStok; $i++) {

			$stokidsi=$stokid[$i];

			if(empty($stokid[$i])) {

				$dataStokX["stok_kodu"] = $stokkodu[$i];

				$stokQ = "select * from stok where stok_kodu='" . $dataStokX["stok_kodu"] . "'";

				$stokExe = $this->db->query($stokQ)->row();

				if ($stokExe) {

					$stokidsi = $stokExe->stok_id;

				} else {

					$dataStokX["stok_ad"] = $stokadi[$i];

					$dataStokX["stok_barkod"] = $barkod[$i];

					$dataStokX["stok_satisFiyati"] = $birimfiyat[$i];

					$dataStokX["stok_alisFiyati"] = 0;

					$dataStokX["stok_satisKDV"] = $kdv[$i];

					$dataStokX["stok_alisKDV"] = 0;

					$dataStokX["stok_birimID"] = $birim[$i];

					$dataStokX["stok_olusturan"] = $u_id;

					$dataStokX["stok_olusturanAnaHesap"] = $anaHesap;

					$dataStokX["stok_olusturmaTarihi"] = $tarihi;

					$dataStokX["stok_olusturmaSaati"] = $saati;



					$this->vt->insert("stok", $dataStokX);

					$stokidsi = $this->db->insert_id();



				}

			}





			if ($kdv[$i] == 0) {

				$yeniKDV = 0;

			} else if ($kdv[$i] == 1) {

				$yeniKDV = 0.01;

			} else if ($kdv[$i] == 8) {

				$yeniKDV = 0.08;

			} else if ($kdv[$i] == 18) {

				$yeniKDV = 0.18;

			}



			$yeniKDVHesap = $toplam[$i] * $yeniKDV;

			$yeniKDVHesap2 = $toplam[$i] + $yeniKDVHesap;



			$tevkifat = explode("|", $tevkifatInput[$i]);

			$dataStok["proformaStok_stokID"] = $stokidsi;

			$dataStok["proformaStok_miktar"] = $miktar[$i];

			$dataStok["proformaStok_birimFiyat"] = $birimfiyat[$i];

			$dataStok["proformaStok_fiyatMiktar"] = $birimfiyat[$i] * $miktar[$i];

			$dataStok["proformaStok_kdv"] = $kdv[$i];

			$dataStok["proformaStok_indirimTutari"] = $indirimtutari[$i];

			$dataStok["proformaStok_satirIskonto"] = $birimfiyat[$i] * $miktar[$i] * $indirimtutari[$i] / 100;

			$dataStok["proformaStok_indirimlifiyat"] = $toplam[$i];



			if ($tevkifat[0] == 0)

				$dataStok["proformaStok_tevkifat_id"] = 0;

			else

				$dataStok["proformaStok_tevkifat_id"] = $tevkifat[1];



			if (!empty($proformaFaturasiStok_id[$i])) {



				$this->vt->update('proformaFaturasiStok', array('proformaStok_id' => $proformaFaturasiStok_id[$i]), $dataStok);

			} else {



				$this->vt->insert("proformaFaturasiStok", $dataStok);

			}

			$paraBirimiTxt="TL";

			if($paraBirimi==2)

				$paraBirimiTxt="USD";

			else if($paraBirimi==3)

				$paraBirimiTxt="EUR";

			else if($paraBirimi==4)

				$paraBirimiTxt="GBP";



		}



		//Satış Sözleşmesi stok





		$this->session->set_flashdata('fatura_updt_ok', 'OK');

		logekle(11, 3);

		redirect($_SERVER['HTTP_REFERER']);

	}



	public function proformaOnayRed()

	{

		$id = postval('fatura_onayRed_id');

		$secenek = postval('fatura_onayRed_secenek');

		$sql="update proformaFaturasi set proforma_durum=$secenek where proforma_id=$id";

		$this->db->query($sql);

		redirect("fatura/proformaListesi");





	}



	public function proforma_fatura_sil()

	{

		$id = $this->input->get('fatura_id');

		$del1 = "DELETE FROM proformaFaturasi WHERE proforma_id=$id";

		$del2 = "DELETE FROM proformaFaturasiStok WHERE proformaStok_proformaFaturasiID=$id";

		$this->db->query($del1);

		$this->db->query($del2);

		$this->session->set_flashdata('fatura_sil_ok', 'OK');

		redirect("fatura/proformaListesi");

	}





	public function proformaFaturalastir()

	{



		$faturaID = $this->input->get('faturalastir_id');



		$sql="select * from proformaFaturasi where proforma_id=$faturaID";

		$proformaDetay=$this->db->query($sql)->row();



		$sqlStok="select * from proformaFaturasiStok where proformaStok_proformaFaturasiID=$faturaID";

		$proformaStok=$this->db->query($sqlStok)->result();



		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$ch_turu=2;



		$satis_faturaTarihi = $proformaDetay->proforma_faturaTarihi;

		$faturaNo = $proformaDetay->proforma_faturaNo;





		$genelToplamHidden = $proformaDetay->proforma_genelToplam;

		$indirimToplamHidden =  $proformaDetay->proforma_indirimToplam;

		$tevkifatToplamHidden =  $proformaDetay->proforma_tevkifatToplam;

		$vergidahilHidden =  $proformaDetay->proforma_vergiDahilToplam;





		$istisna_sebebi =  $proformaDetay->proforma_istisna_id;

		$vergiMuafiyet_sebep =  $proformaDetay->proforma_vergiMuafiyetSebep;





		$guncelKur =  $proformaDetay->proforma_guncelKur;

		$dovizCevrilmis =  $proformaDetay->proforma_dovizCevrilmis;



		$data["satis_faturaDurum"] = 2;

		$data["satis_earsivfaturaTip"] =  $proformaDetay->proforma_faturaTipi;



		$paraBirimi= $proformaDetay->proforma_paraBirimi;



		$data["satis_cariID"] = $proformaDetay->proforma_cariID;

		$data["satis_paraBirimi"] = $paraBirimi;

		$data["satis_guncelKur"] = $guncelKur;

		$data["satis_dovizCevrilmis"] = $dovizCevrilmis;

		$data["satis_irsaliyeNo"] =  NULL;

		$data["satis_irsaliyeTarihi"] = NULL;

		$data["satis_faturaNo"] = $faturaNo;

		$data["satis_faturaTarihi"] = $satis_faturaTarihi;

		$data["satis_faturaSaati"] =  $proformaDetay->proforma_faturaSaati;

		$data["satis_aciklama"] = $proformaDetay->proforma_aciklama;

		$data["satis_araToplam"] = $proformaDetay->proforma_araToplam;

		$data["satis_kdvToplam"] = $proformaDetay->proforma_kdvToplam;

		$data["satis_genelToplam"] = $genelToplamHidden;

		$data["satis_vergiDahilToplam"] = $vergidahilHidden;

		$data["satis_indirimToplam"] = $indirimToplamHidden;

		$data["satis_netTutar"] =  $proformaDetay->proforma_netTutar;



		if ($tevkifatToplamHidden != null)

			$data["satis_tevkifatToplam"] = $tevkifatToplamHidden;

		if ($istisna_sebebi != null)

			$data["satis_istisna_id"] = $istisna_sebebi;



		if ($vergiMuafiyet_sebep != null)

			$data["satis_vergiMuafiyetSebep"] = $vergiMuafiyet_sebep;



		$data["satis_olusturan"] = $u_id;

		$data["satis_olusturanAnaHesap"] = $anaHesap;

		$data["satis_olusturmaTarihi"] = $tarihi;

		$data["satis_olusturmaSaati"] = $saati;



		$faturaVarmiQ = "SELECT * FROM satisFat

    urasi WHERE  (satis_faturaNo = '$faturaNo') AND satis_olusturanAnaHesap = '$anaHesap'";

		$faturaVarmiExe = $this->db->query($faturaVarmiQ)->result();



		if ($faturaVarmiExe) {

			$this->session->set_flashdata('fatura_mukerrer', 'OK');

			redirect($_SERVER['HTTP_REFERER']);

		}





		$this->vt->insert("satisFaturasi", $data);

		$satis_id = $this->db->insert_id();



		$sqlbanka="update faturabanka_bilgileri set faturabanka_faturaID=$satis_id where faturabanka_faturaID=$faturaID";

		$this->db->query($sqlbanka);



		$sqliade="update iade_fatura_numaralari set satis_id=$satis_id where satis_id=$faturaID";

		$this->db->query($sqliade);



		$sqlindirim="update genel_iskonto set iskonto_satis_id=$satis_id where iskonto_satis_id=$faturaID";

		$this->db->query($sqlindirim);



		//Satış Sözleşmesi stok

		$dataStok["satisStok_satisFaturasiID"] = $satis_id;



		foreach ($proformaStok as $item) {



			if ($kdv[$i] == 0) {

				$yeniKDV = 0;

			} else if ($kdv[$i] == 1) {

				$yeniKDV = 0.01;

			} else if ($kdv[$i] == 8) {

				$yeniKDV = 0.08;

			} else if ($kdv[$i] == 18) {

				$yeniKDV = 0.18;

			}



			$yeniKDVHesap = $item->proformaStok_indirimliFiyat * $yeniKDV;

			$yeniKDVHesap2 = $item->proformaStok_indirimliFiyat + $yeniKDVHesap;



			$dataStok["satisStok_stokID"] = $item->proformaStok_stokID;

			$dataStok["satisStok_miktar"] = $item->proformaStok_miktar;

			$dataStok["satisStok_birimFiyat"] = $item->proformaStok_birimFiyat;

			$dataStok["satisStok_fiyatMiktar"] = $item->proformaStok_fiyatMiktar;

			$dataStok["satisStok_kdv"] = $item->proformaStok_kdv;

			$dataStok["satisStok_indirimTutari"] = $item->proformaStok_indirimTutari;

			$dataStok["satisStok_satirIskonto"] = $item->proformaStok_satirIskonto;

			$dataStok["satisStok_indirimlifiyat"] = $item->proformaStok_indirimliFiyat;

			$dataStok["satisStok_tevkifat_id"] = $item->proformaStok_tevkifat_id;

			$dataStok["satisStok_kullanici_sayisi"] = null; // Default value for proforma conversion



			$this->vt->insert("satisFaturasiStok", $dataStok);



			$paraBirimiTxt="TL";

			if($paraBirimi==2)

				$paraBirimiTxt="USD";

			else if($paraBirimi==3)

				$paraBirimiTxt="EUR";

			else if($paraBirimi==4)

				$paraBirimiTxt="GBP";



			$data_sh["sh_turu"] = 2;

			$data_sh["sh_cikis"] = $item->proformaStok_miktar;

			$data_sh["sh_cariID"] = $proformaDetay->proforma_cariID;

			$data_sh["sh_stokID"] = $item->proformaStok_stokID;

			$data_sh["sh_faturaID"] = $satis_id;

			$data_sh["sh_belgeNumarasi"] = $faturaNo;

			$data_sh["sh_paraBirimi"] = $paraBirimiTxt;

			$data_sh["sh_tarih"] = $tarihi;

			$data_sh["sh_birimFiyat"] = $item->proformaStok_stokID;

			$data_sh["sh_kdv"] = $item->proformaStok_stokID;

			$data_sh["sh_toplamKDV"] = $yeniKDVHesap;

			$data_sh["sh_toplamFiyat"] = $yeniKDVHesap2;

			$data_sh["sh_olusturan"] = $u_id;

			$data_sh["sh_olusturanAnaHesap"] = $anaHesap;

			$data_sh["sh_olusturmaTarihi"] = $tarihi;

			$data_sh["sh_olusturmaSaati"] = $saati;



			$this->vt->insert("stokHareketleri", $data_sh);

		}



		//Satış Sözleşmesi stok



		//Cari Hareketleri Ekle :begin

		$data_ch["ch_belgeNumarasi"] = $faturaNo;

		$data_ch["ch_turu"] = $ch_turu;

		$data_ch["ch_cariID"] = $proformaDetay->proforma_cariID;

		$data_ch["ch_faturaID"] = $satis_id;

		$data_ch["ch_paraBirimi"] = $paraBirimiTxt;

/*

		if($dovizCevrilmis)

			$data_ch["ch_borc"] =$dovizCevrilmis;

		else*/

		$data_ch["ch_borc"] = $proformaDetay->proforma_genelToplam;

		$data_ch["ch_tarih"] = $tarihi;



		$data_ch["ch_olusturan"] = $u_id;

		$data_ch["ch_olusturanAnaHesap"] = $anaHesap;

		$data_ch["ch_olusturmaTarihi"] = $tarihi;

		$data_ch["ch_olusturmaSaati"] = $saati;



		$this->vt->insert("cariHareketleri", $data_ch);

		$ch_id = $this->db->insert_id();

		$datachu["satis_cariHareketiID"] = $ch_id;



		$this->vt->update('satisFaturasi', array('satis_id' => $satis_id), $datachu);

		//Cari Hareketleri Ekle :end



		$dataP["proforma_durum"]=3;

		$this->vt->update('proformaFaturasi', array('proforma_id' => $faturaID), $dataP);





		$this->session->set_flashdata('fatura_ok', 'OK');

		logekle(11, 2);

		//redirect("fatura/satis-faturasi-duzenle/$satis_id");



			redirect("fatura/satis-faturasi-listesi");



	}





	public function etiketler()

	{

		$data["baslik"]="Etiketler";

		$anahesap=anaHesapBilgisi();

		$sql="select * from etiketler where etiket_olusturanAnaHesap=$anahesap";

		$exe=$this->db->query($sql)->result();

		$data["etiketler"]=$exe;

		$this->load->view("fatura/etiketler",$data);

	}



	public function etiketEkle()

	{

		$ad=postval("etiket_adi");

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$sql="select * from etiketler where etiket_adi='$ad'";

		$exe=$this->db->query($sql)->row();

		if($exe)

			$this->session->set_flashdata('etiket_mevcut', 'OK');

		else {



			$data["etiket_adi"] = $ad;

			$data["etiket_olusturan"] = $u_id;

			$data["etiket_olusturanAnaHesap"] = $anaHesap;

			$data["etiket_olusturmaTarihi"] = $tarihi;

			$data["etiket_olusturmaSaati"] = $saati;

			$this->db->insert("etiketler", $data);

			$this->session->set_flashdata('etiket_ekle_basarili', 'OK');

		}

		redirect("fatura/etiketler");

	}

	public function etiketGuncelle()

	{

		$ad=postval("etiket_adi");

		$id=postval("etiket_id");

		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;



		date_default_timezone_set('Europe/Istanbul');

		$tarihi = (new DateTime('now'))->format('Y.m.d');

		$saati = (new DateTime('now'))->format('H:i:s');



		$anaHesap = anaHesapBilgisi();



		$data["etiket_adi"]=$ad;

		$data["etiket_olusturan"]=$u_id;

		$data["etiket_olusturanAnaHesap"]=$anaHesap;

		$data["etiket_olusturmaTarihi"]=$tarihi;

		$data["etiket_olusturmaSaati"]=$saati;

		$this->vt->update('etiketler', array('etiket_id' => $id), $data);

		$this->session->set_flashdata('etiket_guncelle_basarili', 'OK');

		redirect("fatura/etiketler");

	}



	public function etiketSil()

	{

		$id=postval("etiket_id");

		$anaHesap = anaHesapBilgisi();

		$dataSatis["satis_faturaEtiketID"]=0;

		$this->vt->update('satisFaturasi', array('satis_faturaEtiketID' => $id,'satis_olusturanAnaHesap'=>$anaHesap), $dataSatis);





		$dataAlis["alis_faturaEtiketID"]=0;

		$this->vt->update('alisFaturasi', array('alis_faturaEtiketID' => $id,'alis_olusturanAnaHesap'=>$anaHesap), $dataAlis);



		$dataProforma["proforma_faturaEtiketID"]=0;

		$this->vt->update('proformaFaturasi', array('proforma_faturaEtiketID' => $id,'proforma_olusturanAnaHesap'=>$anaHesap), $dataProforma);



		$this->vt->del('etiketler', 'etiket_id' , $id);

		$this->session->set_flashdata('etiket_silme_basarili', 'OK');

		redirect("fatura/etiketler");

	}



	public function deleteSalesContractFile()

	{

		// AJAX isteği kontrolü

		if (!$this->input->is_ajax_request()) {

			show_404();

			return;

		}



		$control2 = session("r", "login_info");

		$u_id = $control2->kullanici_id;

		$anaHesap = anaHesapBilgisi();



		// POST verilerini al

		$file_name = $this->input->post('file_name');

		$satis_id = $this->input->post('satis_id');



		// Güvenlik kontrolü - kullanıcının bu faturayı düzenleme yetkisi var mı?

		$allowed_user_ids = array($u_id);

		if ($u_id > 0) {

			$bağlı_kullanicilarQ = "SELECT kullanici_id FROM kullanicilar WHERE kullanici_sorumluMudur = '$u_id'";

			$bağlı_kullanicilar = $this->db->query($bağlı_kullanicilarQ)->result();

			

			foreach ($bağlı_kullanicilar as $user) {

				$allowed_user_ids[] = $user->kullanici_id;

			}

		}

		$allowed_user_ids_str = implode(',', $allowed_user_ids);



		// Faturayı kontrol et

		$satisFaturasiQ = "SELECT * FROM satisFaturasi WHERE satis_id = '$satis_id' AND satis_olusturan IN ($allowed_user_ids_str)";

		$satisFaturasi = $this->db->query($satisFaturasiQ)->row();



		if (!$satisFaturasi) {

			echo json_encode(array('success' => false, 'message' => 'Bu işlem için yetkiniz yok.'));

			return;

		}



		// Mevcut dosyaları al

		$existing_files = explode(',', $satisFaturasi->satis_dosya);

		$updated_files = array();



		// Dosyayı listeden çıkar ve fiziksel olarak sil

		foreach ($existing_files as $file_path) {

			$file_path = trim($file_path);

			if (!empty($file_path)) {

				$current_file_name = basename($file_path);

				if ($current_file_name !== $file_name) {

					$updated_files[] = $file_path;

				} else {

					// Fiziksel dosyayı sil

					$full_path = './assets/uploads/' . $file_path;

					if (file_exists($full_path)) {

						unlink($full_path);

					}

				}

			}

		}



		// Veritabanını güncelle

		$data_update['satis_dosya'] = implode(',', $updated_files);

		$this->vt->update('satisFaturasi', array('satis_id' => $satis_id), $data_update);



		echo json_encode(array('success' => true, 'message' => 'Dosya ba�ar�yla silindi.'));

	}

	// Stok bilgi d�zenleme sayfas�
	public function stokBilgiDuzenle($stok_id)
	{
		$data["baslik"] = "Stok Bilgi D�zenle";

		$anaHesap = anaHesapBilgisi();

		// Stok bilgilerini getir
		$stokQ = "SELECT s.*, sb.stokBirim_ad, sb.stokBirim_kisaltma FROM stok s 
			LEFT JOIN stokBirimleri sb ON s.stok_birimID = sb.stokBirim_id 
			WHERE s.stok_id = '$stok_id' AND s.stok_olusturanAnaHesap = '$anaHesap'";
		$stokBilgi = $this->db->query($stokQ)->row();

		if (!$stokBilgi) {
			redirect('fatura/satis-faturasi-listesi');
		}

		$data["stokBilgi"] = $stokBilgi;

		// Stok birimleri listesi
		$stokBirimleriQ = "SELECT * FROM stokBirimleri ORDER BY stokBirim_ad";
		$data["stokBirimleri"] = $this->db->query($stokBirimleriQ)->result();

		$this->load->view('fatura/stok-bilgi-duzenle', $data);
	}

	// Stok bilgi g�ncelleme i�lemi
	public function stokBilgiGuncelle()
	{
		$control2 = session("r", "login_info");
		$u_id = $control2->kullanici_id;

		$anaHesap = anaHesapBilgisi();
		$stok_id = postval("stok_id");

		// Form verilerini al
		$data["stok_ad"] = postval("stok_ad");
		$data["stok_kodu"] = postval("stok_kodu");
		$data["stok_birimID"] = postval("stok_birimID");
		$data["stok_satisFiyati"] = postval("stok_satisFiyati");
		$data["stok_satisKDV"] = postval("stok_satisKDV");

		// Yetki kontrol�
		$yetkiKontrolQ = "SELECT * FROM stok WHERE stok_id = '$stok_id' AND stok_olusturanAnaHesap = '$anaHesap'";
		$yetkiKontrol = $this->db->query($yetkiKontrolQ)->row();

		if (!$yetkiKontrol) {
			$this->session->set_flashdata('stok_guncelle_yetki_hatasi', 'OK');
			redirect('fatura/satis-faturasi-listesi');
		}

		// Stok kodunun benzersiz olup olmad���n� kontrol et
		$stokKoduKontrolQ = "SELECT * FROM stok WHERE stok_kodu = '".$data["stok_kodu"]."'
			AND stok_olusturanAnaHesap = '$anaHesap' AND stok_id != '$stok_id'";
		$stokKoduKontrol = $this->db->query($stokKoduKontrolQ)->row();
		
		if ($stokKoduKontrol) {
			$this->session->set_flashdata('stok_kodu_mevcut', 'OK');
			redirect('fatura/stok-bilgi-duzenle/'.$stok_id);
		}

		// G�ncelleme i�lemi
		$this->vt->update('stok', array('stok_id' => $stok_id), $data);

		$this->session->set_flashdata('stok_guncelle_basarili', 'OK');
		redirect('fatura/stok-bilgi-duzenle/'.$stok_id);
	}
}


