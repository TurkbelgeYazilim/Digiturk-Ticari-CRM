<?php



function session($type,$name,$data=null){

	$ci = get_instance();

	

	if($type=="r"){

		return $ci->session->userdata($name);

	}

	

	if($type=="w"){

		return $ci->session->set_userdata($name,$data);

	}

	

	if($type=="d"){

		return $ci->session->unset_userdata($name);

	}

	

	return false;

 }



function seciliAlanlarSorgu($query="")

{

    	$ci=get_instance();

    

    	$result= $ci->db->query($query)->result();

    	return $result;

}





function flash($type,$msg){

	$flash_data = 

				  '<div class="alert alert-'.$type.' alert-dismissable">

				  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

				  '.$msg.'

				  </div>';

	$ci=get_instance();

	return $ci->session->set_flashdata('message',$flash_data);

}



function flashreader(){

	$ci = get_instance();

	return $ci->session->flashdata('message');

}



function uploader($config,$filename){

	$ci=get_instance();

	$ci->upload->initialize($config);

	if($ci->upload->do_upload($filename)){

		$resim=$ci->upload->data();

		$resimadi=$resim['file_name'];

		#$thumbnail = $resim['raw_name'].'_thumb'.$resim['file_ext'];

		#$time = time();

		#$kayityolu=base_url().$config['upload_path'].$resimadi;

		$kayityolu=$resimadi;

		

		#$thumbvekayit = $kayityolu.",".$thumbnail;

		return $kayityolu;

	}else{

			//echo $ci->upload->display_errors();die();

			return "bos.png";

			

		}

}



function postval($name){

	$ci=get_instance();

	return $ci->input->post($name);

}



function tarih($date){

	date_default_timezone_set("Europe/Istanbul");

	$date = strtotime($date);

	$zaman_farki = time() - $date;

	$saniye = $zaman_farki;

	$dakika = round($zaman_farki/60);

	$saat = round($zaman_farki/3600);

	$gun = round($zaman_farki/86400);

	$hafta = round($zaman_farki/604800);

	$ay = round($zaman_farki/2419200);

	$yil = round($zaman_farki/29030400);

	

	if($saniye < 60){

	if($saniye == 0){

		return "az önce";

	}else{

		return $saniye .' saniye önce';

	}

	}else if($dakika < 60){

		return $dakika.' dakika önce';

	}else if($saat < 24){

		return $saat.' saat önce';

	}else if($gun < 7){

		return $gun.' gün önce';

	}else if($hafta < 4){

		return $hafta.' hafta önce';

	}else if($ay<12){

		return $yil.' yil önce';

	}

}



function sef($str, $options = array()){

 

    $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

 

 

    $defaults = array(

        'delimiter' => '-',

        'limit' => null,

        'lowercase' => true,

        'transliterate' => true,

    );

 

    $options = array_merge($defaults, $options);

    $dmr = $defaults["delimiter"];

    $char_map = array(

        // Latin

        'À' => 'A', 'Á' => 'A', ' ' => $dmr, 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 

        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 

        'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 

        'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 

        'ß' => 'ss', 

        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 

        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 

        'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 

        'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 

        'ÿ' => 'y',

 

        // Latin symbols

        '©' => '(c)',

 

        // Greek

        'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',

        'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',

        'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',

        'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',

        'Ϋ' => 'Y',

        'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',

        'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',

        'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',

        'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',

        'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

 

        // Turkish

        'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',

        'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 

 

        // Russian

        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',

        'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',

        'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',

        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',

        'Я' => 'Ya',

        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',

        'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',

        'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',

        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',

        'я' => 'ya',

 

        // Ukrainian

        'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',

        'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

 

        // Czech

        'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 

        'Ž' => 'Z', 

        'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',

        'ž' => 'z', 

 

        // Polish

        'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 

        'Ż' => 'Z', 

        'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',

        'ż' => 'z',

 

        // Latvian

        'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 

        'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',

        'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',

        'š' => 's', 'ū' => 'u', 'ž' => 'z'

    );

 

 

    if ($options['transliterate']) {

        $str = str_replace(array_keys($char_map), $char_map, $str);

    }

    $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

    $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

    $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

    $str = trim($str, $options['delimiter']);

    return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;

}



function counter($from,$where=array()){

	$ci=get_instance();

	$result=$ci->db->from($from)->where($where)->count_all_results();

	return $result;

}



function ipadresial(){

	if(getenv("HTTP_CLIENT_IP")) {

 		$ip = getenv("HTTP_CLIENT_IP");

 	} elseif(getenv("HTTP_X_FORWARDED_FOR")) {

 		$ip = getenv("HTTP_X_FORWARDED_FOR");

 		if (strstr($ip, ',')) {

 			$tmp = explode (',', $ip);

 			$ip = trim($tmp[0]);

 		}

 	} else {

 	$ip = getenv("REMOTE_ADDR");

 	}

	return $ip;

}



function generateUniqID(){

    //$key = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));

    $key = implode(str_split(substr(strtolower(md5(microtime().rand(1000, 99999))), 0, 12)));

    return $key;

}



function generateUniqID2(){

	$key = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));

	return $key;

}



function anaHesapBilgisi(){

    $control2 = session("r", "login_info");

    if (!$control2 || !isset($control2->kullanici_id)) {

        return null;

    }

    $u_id = $control2->kullanici_id;

    $ci = get_instance();

    $sorgu = "SELECT * FROM kullanicilar WHERE kullanici_id = '$u_id'";

    $result = $ci->db->query($sorgu)->row();

    if (!$result) {

        return null;    }

    

    // DEBUG: Log kullanıcı bilgilerini yazdır

    $debug_info = "USER DEBUG - ID: $u_id, kullanici_sorumluMudur: " . 

                  (isset($result->kullanici_sorumluMudur) ? $result->kullanici_sorumluMudur : 'NULL');

    error_log($debug_info);

    

    // If user has a responsible manager (kullanici_sorumluMudur is set and not 0)

    if (isset($result->kullanici_sorumluMudur) && $result->kullanici_sorumluMudur != 0) {

        $ana_hesap = $result->kullanici_sorumluMudur;

        error_log("USER DEBUG - Returning sorumluMudur: $ana_hesap");

        return $ana_hesap;

    }

    

    // If user doesn't have a responsible manager, use their own ID

    $ana_hesap = $result->kullanici_id;

    error_log("USER DEBUG - Returning own ID: $ana_hesap");

    return $ana_hesap;

}





function logekle($islemID, $islemTipi){

    //$islemTipi = 1-Görüntüleme 2-Ekleme 3-Düzenleme 4-Silme 5-Giriş 6-Çıkış 7-Hata

    $control2 = session("r", "login_info");

    $u_id = $control2->kullanici_id;



    date_default_timezone_set('Europe/Istanbul');

    $tarihi = (new DateTime('now'))->format('Y.m.d');

    $saati = (new DateTime('now'))->format('H:i:s');



    $ip = ipadresial();



    $log_anaHesap = anaHesapBilgisi();



    $ci = get_instance();



    $data["log_userID"] = $u_id;

    $data["log_islemID"] = $islemID;

    $data["log_anaHesap"] = $log_anaHesap;

    $data["log_islemTipi"] = $islemTipi;

    $data["log_islemTarih"] = $tarihi;

    $data["log_islemSaat"] = $saati;

    $data["log_ip"] = $ip;



    $ci->db->insert("loglar", $data);

}



function truncateString($str, $chars, $to_space, $replacement="...") {

   if($chars > strlen($str)) return $str;



   $str = substr($str, 0, $chars);

   $space_pos = strrpos($str, " ");

   if($to_space && $space_pos >= 0) 

       $str = substr($str, 0, strrpos($str, " "));



   return($str . $replacement);

}



function cutText($str, $limit = 25){

	$str = trim(strip_tags(htmlspecialchars_decode($str)));

	$length = strlen($str);

	if($length > $limit){

		$str = mb_substr($str, 0, $limit, 'UTF-8') . '...';

	}

	return $str;

}



// KULLANICI YETKİLERİ TABLOSU ARTIK KULLANILMIYOR

// function yetkiSorgula($kullanici, $modulID, $yetkiID){

//      $ci = get_instance();

//

//      $sorgu = "SELECT * FROM kullaniciYetkileri WHERE ky_kullaniciID = '$kullanici' AND ky_modul = '$modulID' AND ky_yetki = '$yetkiID' ";

//      $result = $ci->db->query($sorgu)->row();

//

//      return $result;

// }



function dateFormatConvert($date,$type){

    $old_date_timestamp = strtotime($date);

    if($type == 1){

        $new_date = date('d.m.Y', $old_date_timestamp);

    }

    return $new_date;

}



function getirBugun(){

    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('d.m.Y');



    return $bugun;

}



function getirBugunGib(){

	date_default_timezone_set('Europe/Istanbul');

	$bugun = (new DateTime('now'))->format('d/m/Y');



	return $bugun;

}

function getirSaat(){

    date_default_timezone_set('Europe/Istanbul');

    $saat = (new DateTime('now'))->format('H:i');



    return $saat;

}



function getirSaatGib(){

	date_default_timezone_set('Europe/Istanbul');

	$saat = (new DateTime('now'))->format('H:i:s');



	return $saat;

}



function getirFirma(){

    $anaHesap = anaHesapBilgisi();

    

    if (!$anaHesap) {

        return null;

    }



    $ci = get_instance();



    $sorgu = "SELECT * FROM ayarlar WHERE ayarlar_id = '$anaHesap'";

    $result = $ci->db->query($sorgu)->row();



    return $result;

}



function getirFirmaById($id){

	$ci = get_instance();



	$sorgu = "SELECT * FROM ayarlar WHERE ayarlar_id = '$id'";

	$result = $ci->db->query($sorgu)->row();



	return $result;

}



function getirKullaniciById($id){

	$ci = get_instance();



	$sorgu = "SELECT * FROM kullanicilar WHERE kullanici_id = '$id'";

	$result = $ci->db->query($sorgu)->row();



	return $result;

}



function getirActiveTickets(){

	$ci = get_instance();



	$sorgu = "SELECT COUNT(*) as total FROM destek WHERE destek_status = 1";

	$result = $ci->db->query($sorgu)->row();



	return $result;

}



function stokBirimleriOlustur($olusturanAnaHesap){

    date_default_timezone_set('Europe/Istanbul');

    $tarih = (new DateTime('now'))->format('Y-m-d H:i:s');



    $ci = get_instance();



    $data = array(

        array("stokBirim_ad" => "ADET", "stokBirim_kisaltma" => "ADET", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "KİLOGRAM", "stokBirim_kisaltma" => "KG", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "GRAM", "stokBirim_kisaltma" => "GR", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "METRE", "stokBirim_kisaltma" => "M", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "LİTRE", "stokBirim_kisaltma" => "LİTRE", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "PAKET (Packet)", "stokBirim_kisaltma" => "PA", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "PAKET (Pack)", "stokBirim_kisaltma" => "PK", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "KUTU", "stokBirim_kisaltma" => "KUTU", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "SANTİMETRE", "stokBirim_kisaltma" => "CM", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "METREKÜP", "stokBirim_kisaltma" => "M3", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "METREKARE", "stokBirim_kisaltma" => "M2", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "RULO", "stokBirim_kisaltma" => "RULO", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "SET", "stokBirim_kisaltma" => "SET", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih),

        array("stokBirim_ad" => "SANTİMETREKÜP", "stokBirim_kisaltma" => "CM3", "stokBirim_olusturan" => NULL, "stokBirim_olusturanAnaHesap" => $olusturanAnaHesap, "stokBirim_olusturmaTarihi"=> $tarih)

    );



    $result = $ci->db->insert_batch("stokBirimleri", $data);

    return $result;

}



function cariGruplariOlustur($olusturanAnaHesap){

    date_default_timezone_set('Europe/Istanbul');

    $tarih = (new DateTime('now'))->format('Y-m-d H:i:s');



    $ci = get_instance();

    $data = array(

        array("cariGrup_kodu" => "C001", "cariGrup_ad" => "Kurumsal Müşteri", "cariGrup_olusturan" => NULL, "cariGrup_olusturanAnaHesap" => $olusturanAnaHesap, "cariGrup_olusturmaTarihi" => $tarih),

        array("cariGrup_kodu" => "C002", "cariGrup_ad" => "Bireysel Müşteri", "cariGrup_olusturan" => NULL, "cariGrup_olusturanAnaHesap" => $olusturanAnaHesap, "cariGrup_olusturmaTarihi" => $tarih)

    );



    $result = $ci->db->insert_batch("cariGruplari", $data);

    return $result;

}



function giderKategorileriOlustur($olusturanAnaHesap){

    date_default_timezone_set('Europe/Istanbul');

    $tarih = (new DateTime('now'))->format('Y-m-d H:i:s');



    $ci=get_instance();



    $data = array(

        array('gk_adi' => 'Sabit Giderler', 'gk_mainID' => NULL, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Genel Giderler', 'gk_mainID' => NULL, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Ulaşım Giderleri', 'gk_mainID' => NULL, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Personel Giderleri', 'gk_mainID' => NULL, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Vergi, Sgk Vb. Giderler', 'gk_mainID' => NULL, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'İletişim Giderleri', 'gk_mainID' => NULL, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih));



    $result = $ci->db->insert_batch("giderKategorileri", $data);



    $sabitGiderler = "SELECT gk_id FROM giderKategorileri WHERE gk_olusturanAnaHesap = '$olusturanAnaHesap' AND gk_adi = 'Sabit Giderler' ";

    $sabitGiderlerExe = $ci->db->query($sabitGiderler)->row();



    $genelGiderler = "SELECT gk_id FROM giderKategorileri WHERE gk_olusturanAnaHesap = '$olusturanAnaHesap' AND gk_adi = 'Genel Giderler' ";

    $genelGiderlerExe = $ci->db->query($genelGiderler)->row();



    $ulasimGiderleri = "SELECT gk_id FROM giderKategorileri WHERE gk_olusturanAnaHesap = '$olusturanAnaHesap' AND gk_adi = 'Ulaşım Giderleri' ";

    $ulasimGiderleriExe = $ci->db->query($ulasimGiderleri)->row();



    $personelGiderleri = "SELECT gk_id FROM giderKategorileri WHERE gk_olusturanAnaHesap = '$olusturanAnaHesap' AND gk_adi = 'Personel Giderleri' ";

    $personelGiderleriExe = $ci->db->query($personelGiderleri)->row();



    $vergiGiderleri = "SELECT gk_id FROM giderKategorileri WHERE gk_olusturanAnaHesap = '$olusturanAnaHesap' AND gk_adi = 'Vergi, Sgk Vb. Giderler' ";

    $vergiGiderleriExe = $ci->db->query($vergiGiderleri)->row();



    $iletisimGiderleri = "SELECT gk_id FROM giderKategorileri WHERE gk_olusturanAnaHesap = '$olusturanAnaHesap' AND gk_adi = 'İletişim Giderleri' ";

    $iletisimGiderleriExe = $ci->db->query($iletisimGiderleri)->row();



    $data2 = array(

        array('gk_adi' => 'Aidat', 'gk_mainID' => $sabitGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Kira', 'gk_mainID' => $sabitGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Elekrik', 'gk_mainID' => $sabitGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Su', 'gk_mainID' => $sabitGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Doğalgaz', 'gk_mainID' => $sabitGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Avukat', 'gk_mainID' => $sabitGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'SMM / YMM', 'gk_mainID' => $sabitGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),



        array('gk_adi' => 'Yiyecek / İçecek', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Kırtasiye', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Reklam / Tanıtım', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Kargo / Kurye', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Temizlik', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Teknik İşler', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Noter', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'İşyeri Sigorta', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Temsili Ağırlama', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Bilg. Sarf Malzemeleri', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Küçük Demirbaşlar', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Gazete / Dergi', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Aylık Abonelikler', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Büro Bakım Onarım', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Matbaa', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Banka / Kart Komisyonları', 'gk_mainID' => $genelGiderlerExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),



        array('gk_adi' => 'Araç Kiralama', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Araç Sigorta', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Araç Yakıt', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Araç Otopark', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Araç Vergi', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Araç Ceza', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'HGS / OGS', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Şehir İçi Ulaşım', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Araç Tamir Bakım', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Yurtiçi Ulaşım / Konaklama', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Yurtdışı Ulaşım / Konaklama', 'gk_mainID' => $ulasimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),



        array('gk_adi' => 'Maaş', 'gk_mainID' => $personelGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Prim', 'gk_mainID' => $personelGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Kıdem / İhbar', 'gk_mainID' => $personelGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Personel Sağlık', 'gk_mainID' => $personelGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),



        array('gk_adi' => 'Vergi Ödemeleri', 'gk_mainID' => $vergiGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Bağkur Ödemeleri', 'gk_mainID' => $vergiGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'SGK Ödemeleri', 'gk_mainID' => $vergiGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'Resmi Harçlar', 'gk_mainID' => $vergiGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),



        array('gk_adi' => 'Sabit Telefon', 'gk_mainID' => $iletisimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'VOIP', 'gk_mainID' => $iletisimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'GSM', 'gk_mainID' => $iletisimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih),

        array('gk_adi' => 'İnternet', 'gk_mainID' => $iletisimGiderleriExe->gk_id, 'gk_olusturan' => NULL, 'gk_olusturanAnaHesap' => $olusturanAnaHesap, 'gk_olusturmaTarihi' => $tarih)

    );



    $result = $ci->db->insert_batch("giderKategorileri", $data2);

    return $result;

}



function alacakHesapla($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));



    $toplamBorcQ = "SELECT SUM(ch_borc) AS toplamBorc FROM cariHareketleri WHERE ch_olusturanAnaHesap = '$anaHesap' AND ch_tarih BETWEEN '$hesaplananTarih' AND '$bugun'";

    $toplamBorcExe = $ci->db->query($toplamBorcQ)->row();



    $toplamAlacakQ = "SELECT SUM(ch_alacak) AS toplamAlacak FROM cariHareketleri WHERE ch_olusturanAnaHesap = '$anaHesap' AND ch_tarih BETWEEN '$hesaplananTarih' AND '$bugun'";

    $toplamAlacakExe = $ci->db->query($toplamAlacakQ)->row();



    $data["toplamBorc"] = $toplamBorcExe->toplamBorc;

    $data["toplamAlacak"] = $toplamAlacakExe->toplamAlacak;

    $result = $data["toplamBorc"] - $data["toplamAlacak"];



    return $result;

}



function alacakHesapla2($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));

    $hesaplananTarih2 = date('Y-m-d', strtotime($hesaplananTarih.'-30 days'));



    $toplamBorcQ = "SELECT SUM(ch_borc) AS toplamBorc FROM cariHareketleri WHERE ch_olusturanAnaHesap = '$anaHesap' AND ch_tarih BETWEEN '$hesaplananTarih2' AND '$hesaplananTarih'";

    $toplamBorcExe = $ci->db->query($toplamBorcQ)->row();



    $toplamAlacakQ = "SELECT SUM(ch_alacak) AS toplamAlacak FROM cariHareketleri WHERE ch_olusturanAnaHesap = '$anaHesap' AND ch_tarih BETWEEN '$hesaplananTarih2' AND '$hesaplananTarih'";

    $toplamAlacakExe = $ci->db->query($toplamAlacakQ)->row();



    $data["toplamBorc"] = $toplamBorcExe->toplamBorc;

    $data["toplamAlacak"] = $toplamAlacakExe->toplamAlacak;

    $result = $data["toplamBorc"] - $data["toplamAlacak"];



    return $result;

}



function musteriHesapla($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));



    $toplamMusteriQ = "SELECT COUNT(*) AS toplamMusteri FROM cari WHERE cari_olusturanAnaHesap = '$anaHesap' AND cari_olusturmaTarihi BETWEEN '$hesaplananTarih' AND '$bugun'";

    $toplamMusteriExe = $ci->db->query($toplamMusteriQ)->row();



    $result = $toplamMusteriExe->toplamMusteri;



    return $result;

}



function musteriHesapla2($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));

    $hesaplananTarih2 = date('Y-m-d', strtotime($hesaplananTarih.'-30 days'));



    $toplamMusteriQ = "SELECT COUNT(*) AS toplamMusteri FROM cari WHERE cari_olusturanAnaHesap = '$anaHesap' AND cari_olusturmaTarihi BETWEEN '$hesaplananTarih2' AND '$hesaplananTarih'";

    $toplamMusteriExe = $ci->db->query($toplamMusteriQ)->row();



    $result = $toplamMusteriExe->toplamMusteri;



    return $result;

}



function faturaHesapla($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));



    $alisFaturasiQ = "SELECT COUNT(*) AS toplamFatura FROM alisFaturasi WHERE alis_olusturanAnaHesap = '$anaHesap' AND alis_olusturmaTarihi BETWEEN '$hesaplananTarih' AND '$bugun'";

    $alisFaturasiExe = $ci->db->query($alisFaturasiQ)->row();



    $satisFaturasiQ = "SELECT COUNT(*) AS toplamFatura FROM satisFaturasi WHERE satis_olusturanAnaHesap = '$anaHesap' AND satis_olusturmaTarihi BETWEEN '$hesaplananTarih' AND '$bugun'";

    $satisFaturasiExe = $ci->db->query($satisFaturasiQ)->row();



    $satisFaturasi = $alisFaturasiExe->toplamFatura;

    $alisFaturasi = $satisFaturasiExe->toplamFatura;



    $result = $satisFaturasi + $alisFaturasi;



    return $result;

}



function faturaHesapla2($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));

    $hesaplananTarih2 = date('Y-m-d', strtotime($hesaplananTarih.'-30 days'));



    $alisFaturasiQ = "SELECT COUNT(*) AS toplamFatura FROM alisFaturasi WHERE alis_olusturanAnaHesap = '$anaHesap' AND alis_olusturmaTarihi BETWEEN '$hesaplananTarih2' AND '$hesaplananTarih'";

    $alisFaturasiExe = $ci->db->query($alisFaturasiQ)->row();



    $satisFaturasiQ = "SELECT COUNT(*) AS toplamFatura FROM satisFaturasi WHERE satis_olusturanAnaHesap = '$anaHesap' AND satis_olusturmaTarihi BETWEEN '$hesaplananTarih2' AND '$hesaplananTarih'";

    $satisFaturasiExe = $ci->db->query($satisFaturasiQ)->row();



    $satisFaturasi = $alisFaturasiExe->toplamFatura;

    $alisFaturasi = $satisFaturasiExe->toplamFatura;



    $result = $satisFaturasi + $alisFaturasi;



    return $result;

}



function stokHesapla($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));



    $toplamGirisQ = "SELECT SUM(sh_giris) AS toplamGiris FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' AND sh_tarih BETWEEN '$hesaplananTarih' AND '$bugun'";

    $toplamGirisExe = $ci->db->query($toplamGirisQ)->row();



    $toplamCikisQ = "SELECT SUM(sh_cikis) AS toplamCikis FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' AND sh_tarih BETWEEN '$hesaplananTarih' AND '$bugun'";

    $toplamCikisExe = $ci->db->query($toplamCikisQ)->row();



    $toplamGiris = $toplamGirisExe->toplamGiris;

    $toplamCikis = $toplamCikisExe->toplamCikis;

    $result = $toplamGiris - $toplamCikis;



    return $result;

}



function stokHesapla2($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));

    $hesaplananTarih2 = date('Y-m-d', strtotime($hesaplananTarih.'-30 days'));



    $toplamGirisQ = "SELECT SUM(sh_giris) AS toplamGiris FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' AND sh_tarih BETWEEN '$hesaplananTarih2' AND '$hesaplananTarih'";

    $toplamGirisExe = $ci->db->query($toplamGirisQ)->row();



    $toplamCikisQ = "SELECT SUM(sh_cikis) AS toplamCikis FROM stokHareketleri WHERE sh_olusturanAnaHesap = '$anaHesap' AND sh_tarih BETWEEN '$hesaplananTarih2' AND '$hesaplananTarih'";

    $toplamCikisExe = $ci->db->query($toplamCikisQ)->row();



    $toplamGiris = $toplamGirisExe->toplamGiris;

    $toplamCikis = $toplamCikisExe->toplamCikis;

    $result = $toplamGiris - $toplamCikis;



    return $result;

}



function satisFaturasiHesapla($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));



    $toplamSFq = "SELECT SUM(satis_genelToplam) AS toplamFatura FROM satisFaturasi WHERE satis_olusturanAnaHesap = '$anaHesap' AND satis_olusturmaTarihi BETWEEN '$hesaplananTarih' AND '$bugun'";

    $toplamSFexe = $ci->db->query($toplamSFq)->row();



    $result = $toplamSFexe->toplamFatura;



    return $result;

}



function alisFaturasiHesapla($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));



    $toplamAFq = "SELECT SUM(alis_genelToplam) AS toplamFatura FROM alisFaturasi WHERE alis_olusturanAnaHesap = '$anaHesap' AND alis_olusturmaTarihi BETWEEN '$hesaplananTarih' AND '$bugun'";

    $toplamAFexe = $ci->db->query($toplamAFq)->row();



    $result = $toplamAFexe->toplamFatura;



    return $result;

}



function giderHesapla($gun=NULL){

    $ci = get_instance();



    $anaHesap = anaHesapBilgisi();



    date_default_timezone_set('Europe/Istanbul');

    $bugun = (new DateTime('now'))->format('Y-m-d');

    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));



    $toplamGiderQ = "SELECT SUM(gider_tutar) AS gider FROM giderler WHERE gider_olusturanAnaHesap = '$anaHesap' AND gider_olusturmaTarihi BETWEEN '$hesaplananTarih' AND '$bugun'";

    $toplamGiderExe = $ci->db->query($toplamGiderQ)->row();



    $result = $toplamGiderExe->gider;



    return $result;

}



function getVaultsofCompany(){

	$ci = get_instance();



	$anaHesap = anaHesapBilgisi();



	$kasalarQ = "SELECT * FROM kasa WHERE kasa_olusturanAnaHesap = '$anaHesap'";

	$data = $ci->db->query($kasalarQ)->result();



	return $data;

}



function getUserResponsibleVaults($kullanici_id = null){

	$ci = get_instance();

	

	// Eğer kullanıcı ID verilmemişse, oturumdaki kullanıcıyı al

	if($kullanici_id === null) {

		$control2 = session("r", "login_info");

		$kullanici_id = $control2->kullanici_id;

	}



	// Önce kasa_sorumlusu alanının var olup olmadığını kontrol et

	$columns = $ci->db->list_fields('kasa');

	$has_kasa_sorumlusu = in_array('kasa_sorumlusu', $columns);

	

	if($has_kasa_sorumlusu) {

		// Ana hesap kısıtlaması olmadan direkt sorgu yap

		$kasalarQ = "SELECT * FROM kasa WHERE kasa_sorumlusu = '$kullanici_id'";

		$data = $ci->db->query($kasalarQ)->result();

	} else {

		// Ana hesap kısıtlaması olmadan direkt sorgu yap

		$kasalarQ = "SELECT * FROM kasa WHERE kasa_olusturan = '$kullanici_id'";

		$data = $ci->db->query($kasalarQ)->result();

	}



	return $data;

}



function getBanksofCompany(){

	$ci = get_instance();



	$anaHesap = anaHesapBilgisi();



	// Hem kendi bankalarını hem de diğer tüm bankaları getir

	$bankalarQ = "SELECT * FROM banka ORDER BY banka_bankaAd ASC";

	$data = $ci->db->query($bankalarQ)->result();



	return $data;

}



function getPriceOfCurrency($id){

    $JSON = json_decode(file_get_contents('https://api.genelpara.com/embed/para-birimleri.json'), true);



    if($id == 1){

        return $JSON['XU100']['satis'];

    }

    if($id == 2){

        return $JSON['USD']['satis'];   

    }

    if($id == 3){

        return $JSON['EUR']['satis'];

    }

    if($id == 4){

        return $JSON['BTC']['satis'];

    }

    if($id == 5){

        return  $JSON['GA']['satis'];

    } 

}



function getPriceOfCurrency_TCMB($id){

	$connect_web = simplexml_load_file('http://www.tcmb.gov.tr/kurlar/today.xml');



	if($id == 2){//usd

		return $connect_web->Currency[0]->BanknoteSelling;

	}



	if($id == 3){//eur

		return $connect_web->Currency[3]->BanknoteSelling;

	}



	if($id == 4){//gbp

		return $connect_web->Currency[4]->BanknoteSelling;

	}

}



function modulSorgula($firmaID, $modulID){

	$ci = get_instance();



	$sorgu = "SELECT * FROM firmaModulleri WHERE firmaModul_moduleID = '$modulID' AND firmaModul_firmaID = '$firmaID'";

	$result = $ci->db->query($sorgu)->row();



	return $result;

}



function checkSpecialChars($string)

{

	if(!preg_match("/^[A-Za-z0-9]+$/", $string)){

		return false;

	} else {

		return true;

	}

}



function generateGUID(){

	if (function_exists('com_create_guid') === true)

	{

		return trim(com_create_guid(), '{}');

	}



	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

}



function getGUID(){

	if (function_exists('com_create_guid')){

		return com_create_guid();

	}else{

		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.

		$charid = md5(uniqid(rand(), true));

		$hyphen = chr(45);// "-"

		$uuid = substr($charid, 0, 8).$hyphen

			.substr($charid, 8, 4).$hyphen

			.substr($charid,12, 4).$hyphen

			.substr($charid,16, 4).$hyphen

			.substr($charid,20,12);

		return $uuid;

	}

}

function gunler($gun=0)

{

	$gunler=array(



		0 => "Paz",



		1 => "Pzt",



		2 => "Sal",



		3 => "Çar",



		4 => "Per",



		5 => "Cum",



		6 => "Cmt"



	);

	return $gunler[$gun];

}

function sessionKontrolHelper()

{

	$ci=get_instance();

	$control2 = session("r", "login_info");

	$u_id = $control2->kullanici_id;

	$sql="select * from kullanicilar where kullanici_id=$u_id";

	$exe=$ci->db->query($sql)->row();

		if ($_SESSION["kullanici_oturumDurum"] != $exe->kullanici_oturumDurum) {

			session_destroy();

			redirect("check");



	}

}



function adminYetki()

{

	$ci=get_instance();

	$control2 = session("r", "login_info");

	$u_id = $control2->kullanici_id;



	// Admin kullanıcı ID'leri

	$admin_ids = array(1);

	

	if(in_array($u_id, $admin_ids)){

		return 1;

	}else{

		return 0;

	}

}



function randomPassword() {

	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

	$pass = array(); //remember to declare $pass as an array

	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache

	for ($i = 0; $i < 8; $i++) {

		$n = rand(0, $alphaLength);

		$pass[] = $alphabet[$n];

	}

	return implode($pass); //turn the array into a string

}



function isDemoActive(){

	$ci=get_instance();

	$control2 = session("r", "login_info");

	$u_id = $control2->kullanici_id;



	$sql="select * from kullanicilar where kullanici_id=$u_id";

	$exe=$ci->db->query($sql)->row();



	$demo = $exe->kullanici_demo;



	if($demo == 1){

		return 1;

	}else{

		return 0;

	}

}



function demoDateExpireControl($userID){

	$ci=get_instance();



	//$control = session("r", "login");

	//$control2 = session("r", "login_info");



	$sql="select * from kullanicilar where kullanici_id=$userID";

	$exe=$ci->db->query($sql)->row();



	$kullanici_demoSonTarihi = $exe->kullanici_demoSonTarihi;



	$bugunTarih = strtotime((new DateTime('now'))->format('Y-m-d H:i:s'));



	$your_date = strtotime($kullanici_demoSonTarihi);

	$datediff = $your_date - $bugunTarih;

	$kalan_gun = round($datediff / (60 * 60 * 24));



	return $kalan_gun;

}

// Stok grup adını getirir
function stokGrupAd($stokGrup_id) {
    $ci = get_instance();
    
    $query = "SELECT stokGrup_ad FROM stokGruplari WHERE stokGrup_id = ?";
    $result = $ci->db->query($query, [$stokGrup_id])->row();
    
    return $result ? $result->stokGrup_ad : '-';
}

// Stok grubuna göre satış sözleşmesi sayısını hesaplar
function satisStokGrupHesapla($stokGrup_id, $gun = 30) {
    $ci = get_instance();
    $anaHesap = anaHesapBilgisi();
    
    date_default_timezone_set('Europe/Istanbul');
    $bugun = (new DateTime('now'))->format('Y-m-d');
    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));
    
    $query = "SELECT COUNT(DISTINCT sf.satis_id) AS toplam
              FROM satisFaturasi sf
              JOIN satisFaturasiStok sfs ON sf.satis_id = sfs.satisStok_satisFaturasiID
              JOIN stok st ON sfs.satisStok_stokID = st.stok_id
              WHERE st.stok_stokGrupKoduID = ?
              AND sf.satis_olusturanAnaHesap = ?
              AND DATE(sf.satis_olusturmaTarihi) BETWEEN ? AND ?";
    
    $result = $ci->db->query($query, [$stokGrup_id, $anaHesap, $hesaplananTarih, $bugun])->row();
    
    return $result ? $result->toplam : 0;
}

// Stok grubuna göre tahsilat tutarını hesaplar
function tahsilatStokGrupHesapla($stokGrup_id, $gun = 30) {
    $ci = get_instance();
    $anaHesap = anaHesapBilgisi();
    
    date_default_timezone_set('Europe/Istanbul');
    $bugun = (new DateTime('now'))->format('Y-m-d');
    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));
    
    // Banka hareketleri tahsilatı
    $queryBanka = "SELECT SUM(bh.bh_giris) AS toplam
                   FROM bankaHareketleri bh
                   JOIN cari c ON bh.bh_cariID = c.cari_id
                   JOIN satisFaturasi sf ON sf.satis_cariID = c.cari_id
                   JOIN satisFaturasiStok sfs ON sf.satis_id = sfs.satisStok_satisFaturasiID
                   JOIN stok st ON sfs.satisStok_stokID = st.stok_id
                   WHERE st.stok_stokGrupKoduID = ?
                   AND bh.bh_olusturanAnaHesap = ?
                   AND DATE(bh.bh_olusturmaTarihi) BETWEEN ? AND ?
                   AND bh.bh_giris > 0";
    
    // Kasa hareketleri tahsilatı
    $queryKasa = "SELECT SUM(kh.kh_giris) AS toplam
                  FROM kasaHareketleri kh
                  JOIN cari c ON kh.kh_cariID = c.cari_id
                  JOIN satisFaturasi sf ON sf.satis_cariID = c.cari_id
                  JOIN satisFaturasiStok sfs ON sf.satis_id = sfs.satisStok_satisFaturasiID
                  JOIN stok st ON sfs.satisStok_stokID = st.stok_id
                  WHERE st.stok_stokGrupKoduID = ?
                  AND kh.kh_olusturanAnaHesap = ?
                  AND DATE(kh.kh_olusturmaTarihi) BETWEEN ? AND ?
                  AND kh.kh_giris > 0";
    
    $bankaResult = $ci->db->query($queryBanka, [$stokGrup_id, $anaHesap, $hesaplananTarih, $bugun])->row();
    $kasaResult = $ci->db->query($queryKasa, [$stokGrup_id, $anaHesap, $hesaplananTarih, $bugun])->row();
    
    $toplam = ($bankaResult->toplam ?? 0) + ($kasaResult->toplam ?? 0);
    
    return number_format($toplam, 2, ',', '.');
}

// Stok grubuna göre alacak tutarını hesaplar
function alacakStokGrupHesapla($stokGrup_id, $gun = 30) {
    $ci = get_instance();
    $anaHesap = anaHesapBilgisi();
    
    date_default_timezone_set('Europe/Istanbul');
    $bugun = (new DateTime('now'))->format('Y-m-d');
    $hesaplananTarih = date('Y-m-d', strtotime($bugun.'-'.$gun.' days'));
    
    // Satış faturaları toplamı
    $querySatis = "SELECT SUM(sfs.satisStok_fiyatMiktar) AS toplam
                   FROM satisFaturasi sf
                   JOIN satisFaturasiStok sfs ON sf.satis_id = sfs.satisStok_satisFaturasiID
                   JOIN stok st ON sfs.satisStok_stokID = st.stok_id
                   WHERE st.stok_stokGrupKoduID = ?
                   AND sf.satis_olusturanAnaHesap = ?
                   AND DATE(sf.satis_olusturmaTarihi) BETWEEN ? AND ?";
    
    // Tahsilatlar toplamı (banka + kasa)
    $queryTahsilat = "SELECT 
                      (SELECT COALESCE(SUM(bh.bh_giris), 0)
                       FROM bankaHareketleri bh
                       JOIN cari c ON bh.bh_cariID = c.cari_id
                       JOIN satisFaturasi sf ON sf.satis_cariID = c.cari_id
                       JOIN satisFaturasiStok sfs ON sf.satis_id = sfs.satisStok_satisFaturasiID
                       JOIN stok st ON sfs.satisStok_stokID = st.stok_id
                       WHERE st.stok_stokGrupKoduID = ?
                       AND bh.bh_olusturanAnaHesap = ?
                       AND DATE(bh.bh_olusturmaTarihi) BETWEEN ? AND ?
                       AND bh.bh_giris > 0) +
                      (SELECT COALESCE(SUM(kh.kh_giris), 0)
                       FROM kasaHareketleri kh
                       JOIN cari c ON kh.kh_cariID = c.cari_id
                       JOIN satisFaturasi sf ON sf.satis_cariID = c.cari_id
                       JOIN satisFaturasiStok sfs ON sf.satis_id = sfs.satisStok_satisFaturasiID
                       JOIN stok st ON sfs.satisStok_stokID = st.stok_id
                       WHERE st.stok_stokGrupKoduID = ?
                       AND kh.kh_olusturanAnaHesap = ?
                       AND DATE(kh.kh_olusturmaTarihi) BETWEEN ? AND ?
                       AND kh.kh_giris > 0) AS toplam";
    
    $satisResult = $ci->db->query($querySatis, [$stokGrup_id, $anaHesap, $hesaplananTarih, $bugun])->row();
    $tahsilatResult = $ci->db->query($queryTahsilat, [
        $stokGrup_id, $anaHesap, $hesaplananTarih, $bugun,
        $stokGrup_id, $anaHesap, $hesaplananTarih, $bugun
    ])->row();
    
    $alacak = ($satisResult->toplam ?? 0) - ($tahsilatResult->toplam ?? 0);
    
    return number_format($alacak, 2, ',', '.');
}

// Stok grubuna göre bekleyen tahsilat tutarını hesaplar (onay_bekleyen)
function bekleyenTahsilatStokGrupHesapla($stokGrup_id) {
    $ci = get_instance();
    
    // muhasebe_tahsilat_durum tablosundan onay bekleyen tahsilatları getir
    $query = "SELECT SUM(
                  CASE 
                      WHEN mtd.tahsilat_tipi = 1 THEN COALESCE(bh.bh_giris, bh.bh_cikis, 0)
                      WHEN mtd.tahsilat_tipi = 2 THEN COALESCE(ck.cek_tutar, 0)
                      WHEN mtd.tahsilat_tipi = 3 THEN COALESCE(kh.kh_giris, kh.kh_cikis, 0)
                      WHEN mtd.tahsilat_tipi = 4 THEN COALESCE(s.senet_tutar, 0)
                      ELSE 0
                  END
              ) AS toplam
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
              WHERE mtd.durum = 1
              AND EXISTS (
                  SELECT 1 
                  FROM satisFaturasi sf
                  JOIN satisFaturasiStok sfs ON sf.satis_id = sfs.satisStok_satisFaturasiID
                  JOIN stok st ON sfs.satisStok_stokID = st.stok_id
                  WHERE sf.satis_cariID = c.cari_id
                  AND st.stok_stokGrupKoduID = ?
              )";
    
    $result = $ci->db->query($query, [$stokGrup_id])->row();
    
    $toplam = $result->toplam ?? 0;
    
    return number_format($toplam, 2, ',', '.');
}

