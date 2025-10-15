<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Check extends CI_Controller {

	

	public function __construct(){

		parent::__construct();

		$this->load->model('Vt', 'vt');
		$this->load->library('session');
		$this->load->database();
		$this->load->helper('url');

		// Geçici olarak session kontrol devre dışı
		// $control = session("r","login");
		// if($control){
		//     redirect("");
		// }

	}



	public function index(){
		$this->load->view('giris');
	}

	

	public function login(){
		
		// POST kontrolü
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) {
			
			$email = isset($_POST['u_email']) ? trim($_POST['u_email']) : '';
			$password = isset($_POST['u_password']) ? trim($_POST['u_password']) : '';
			
		if(empty($email) || empty($password)) {
			$this->session->set_flashdata('login_fail','ok');
			redirect('check');
			return;
		}

		$query1 = $this->vt->single("kullanicilar",array('kullanici_eposta'=>$email,'kullanici_sifre'=>md5($password)));		if($query1){

			$kullaniciSorumluMudur = $query1->kullanici_sorumluMudur;
			$kullaniciAnaHesapID = $query1->kullanici_anaHesapID;



			$status = $query1->kullanici_durum;

			$kullanici_demo = $query1->kullanici_demo;



			if($kullanici_demo == 1){

				$today = strtotime(date('Y-m-d H:i:s'));


                // Debug: log session data after setting session vars to help troubleshooting session persistence
                $ci_session_data = '';
                if (isset($this->session) && method_exists($this->session, 'all_userdata')) {
                    $ci_session_data = print_r($this->session->all_userdata(), true);
                } else {
                    $ci_session_data = isset($_SESSION) ? print_r($_SESSION, true) : '$_SESSION not available';
                }
                file_put_contents('login_debug.txt', "Post-login session data:\n" . $ci_session_data . "\n", FILE_APPEND);

                redirect('');
				$demoSonTarih = strtotime($query1->kullanici_demoSonTarihi);



				if($today >= $demoSonTarih){//demo süresi bitti ödeme sayfasına yönlendir

					$this->session->set_flashdata('anaHesap',$kullaniciAnaHesapID);

					redirect('satin-al');

				}

			}



			if($status == 1){

				$kullaniciID = $query1->kullanici_id;



				$kullaniciSession = $query1->kullanici_belgeSessionID;

				$modulQ = "SELECT * FROM firmaModulleri WHERE firmaModul_firmaID = '$kullaniciAnaHesapID'";

				$modulExe = $this->db->query($modulQ)->result();



				foreach($modulExe as $modExe){

					if($modExe->firmaModul_moduleID==1){//true ise ana hesabın efatura modülü aktif durumdadır, bu yüzden servisten session id alip öyle giriş yapmalıyız.

						echo "1";

						$faturaGirisSorgulaQ = "SELECT * FROM modulAyarlari WHERE ma_olusturanAnaHesap = '$kullaniciAnaHesapID' AND ma_modulID = 1";

						$faturaGirisSorgula = $this->db->query($faturaGirisSorgulaQ)->row();

						// $auth = auth($faturaGirisSorgula->ma_efaturaUsername, $faturaGirisSorgula->ma_efaturaSifre);
						$auth = ""; // E-fatura kullanılmadığı için boş değer

						

						session("w", "auth", $auth);

					

						$ses = session("r","auth");

						$datak["kullanici_belgeSessionID"] = $ses;

						if($ses != $kullaniciSession){

							$this->vt->update('kullanicilar', array('kullanici_id'=>$kullaniciID), $datak);

						}

						

					}//efatura modulu

					if($modExe->firmaModul_moduleID==2){

						echo "2";

						$faturaGirisSorgulaQ = "SELECT * FROM modulAyarlari WHERE ma_olusturanAnaHesap = '$kullaniciAnaHesapID' AND ma_modulID = 2 and ma_gibUsername is not null and ma_gibSifre is not null";

						$faturaGirisSorgula = $this->db->query($faturaGirisSorgulaQ)->row();



						if($faturaGirisSorgula)

						{

							$datak["kullanici_belgeSessionID"] = 1;

							$this->vt->update('kullanicilar', array('kullanici_id'=>$kullaniciID), $datak);

						}

					}//gib modülü

					if($modExe->firmaModul_moduleID==5){

						echo "5";

						$faturaGirisSorgulaQ = "SELECT * FROM modulAyarlari WHERE ma_olusturanAnaHesap = '$kullaniciAnaHesapID' AND ma_modulID = 5";

						$faturaGirisSorgula = $this->db->query($faturaGirisSorgulaQ)->row();



						// $auth = authizi($faturaGirisSorgula->ma_eirsaliyeUsername, $faturaGirisSorgula->ma_eirsaliyeSifre);
						$auth = ""; // E-irsaliye kullanılmadığı için boş değer



						session("w", "auth", $auth);

						$ses = session("r","auth");

						$datak["kullanici_belgeSessionIDizi"] = $ses;



						if($ses != $kullaniciSession){

							$this->vt->update('kullanicilar', array('kullanici_id'=>$kullaniciID), $datak);

						}

					}//eirsaliye

				}



				session("w","login",true);
				session("w","login_info",$query1);
				logekle(54,5);

				$uniqID=uniqid();
				$_SESSION["kullanici_oturumDurum"]=$uniqID;
				$dataOturum["kullanici_oturumDurum"] = $uniqID;
				$this->vt->update('kullanicilar', array('kullanici_id'=>$kullaniciID), $dataOturum);
				
				redirect('');

			}else{

				$this->session->set_flashdata('login_fail_inactive','ok');

				redirect('check');

			}

		}else{
			$this->session->set_flashdata('login_fail','ok');
			redirect('check');
		}
		
		} else {
			// GET request ise giriş sayfasına yönlendir
			redirect('check');
		}
	}



	public function basarili(){

		$this->load->view('basarili');

	}

}

