<!DOCTYPE html>

<html lang="tr">

<head>

	<?php $this->load->view("include/head-tags"); ?>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"

		  integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA=="

		  crossorigin="anonymous" referrerpolicy="no-referrer"/>

	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/ui-lightness/jquery-ui.css">

	<style>

		.ui-autocomplete {

			z-index: 999999;

			max-height: 200px;

			overflow-y: auto;

			/* prevent horizontal scrollbar */

			overflow-x: hidden;

			/* add padding to account for vertical scrollbar */

			padding-right: 20px;

		}



		.form-group {

			margin-bottom: 10px !important;

		}

		.card {

			margin-bottom: 0px !important;

		}

				/* Hide Stok Kodu (Stock Code) and Birim (Unit) columns */

		#my_table th:nth-child(2), /* Stok Kodu header */

		#my_table td:nth-child(2), /* Stok Kodu data cells */

		#my_table th:nth-child(3), /* Birim header */

		#my_table td:nth-child(3) { /* Birim data cells */

			display: none !important;

		}

		

		/* Mobile specific styles - Miktar kutucuğunu küçült */

		@media (max-width: 768px) {

			/* Mobil cihazlarda footer altında kalan kaydet butonu sorunu için minimal padding */

			body {

				padding-bottom: 25px !important;

			}

			

			.main-wrapper {

				padding-bottom: 25px !important;

			}

			

			.page-wrapper {

				padding-bottom: 35px !important;

			}

			

			/* Form container için minimal padding */

			.content.container-fluid {

				padding-bottom: 15px !important;

			}

			

			/* Kaydet butonu container'ına minimal padding */

			.col-md-12.p-0 {

				padding-bottom: 5px !important;

				margin-bottom: 15px !important;

			}

			

			/* Kaydet butonu için minimal margin */

			#submitBtn {

				margin-bottom: 10px !important;

			}

			

			/* Tablo container için horizontal scroll */

			.table-responsive {

				overflow-x: auto !important;

				-webkit-overflow-scrolling: touch;

			}

			

			/* Tablo minimum genişliği */

			#my_table {

				min-width: 600px !important;

				width: auto !important;

			}

			

			/* Tablo hücrelerinin vertical alignment'ını düzenle */

			#my_table td,

			#my_table th {

				vertical-align: middle !important;

				padding: 8px 4px !important;

			}

			

			/* Stok Adı sütunu genişliği - 10 karakter için */

			#my_table th:nth-child(1),

			#my_table td:nth-child(1) {

				min-width: 12px !important;

				max-width: 18px !important;

				width: 18px !important;

				vertical-align: middle !important;

				overflow: hidden !important;

				text-overflow: ellipsis !important;

				white-space: nowrap !important;

			}

			

			/* Miktar sütunu */

			#my_table th:nth-child(4),

			#my_table td:nth-child(4) {

				min-width: 12px !important;

				max-width: 18px !important;

				width: 18px !important;

				vertical-align: middle !important;

				overflow: hidden !important;

				text-overflow: ellipsis !important;

				white-space: nowrap !important;

			}

			

			input[name^="miktar"] {

				width: 45px !important;

				min-width: 45px !important;

				margin: 0 !important;

				height: 32px !important;

			}

			

			/* KDV (%) sütununu mobilde gizle */

			#my_table th:nth-child(6), /* KDV (%) header */

			#my_table td:nth-child(6) { /* KDV (%) data cells */

				display: none !important;

			}

			

			/* Toplam sütununu mobilde gizle */

			#my_table th:nth-child(7), /* Toplam header */

			#my_table td:nth-child(7) { /* Toplam data cells */

				display: none !important;

			}

			

			/* Birim Fiyat sütununu genişlet - 8 karakter için */

			#my_table th:nth-child(5),

			#my_table td:nth-child(5) {

				min-width: 65px !important;

				max-width: 65px !important;

				width: 65px !important;

				vertical-align: middle !important;

				overflow: hidden !important;

				text-overflow: ellipsis !important;

				white-space: nowrap !important;

			}

			

			
			/* Kullanıcı Sayısı sütunu - 3 karakter için */
			#my_table th:nth-child(8),
			#my_table td:nth-child(8) {
				min-width: 25px !important;
				max-width: 25px !important;
				width: 25px !important;
				vertical-align: middle !important;
				overflow: hidden !important;
				text-overflow: ellipsis !important;
				white-space: nowrap !important;
			}
			
			/* İşlem sütunu - operations moved to column 9 */			#my_table th:nth-child(9),

			#my_table td:nth-child(9) {

				min-width: 80px !important;

				width: 80px !important;

				vertical-align: middle !important;

			}

			

			/* Birim fiyat input'unu düzenle */

			input[name^="birimfiyat"] {

				position: relative;

				margin: 0 !important;

				width: 100% !important;

				height: 32px !important;

			}

			

			/* Mobilde tüm input alanlarının tutarlı olması için */

			#my_table input[type="text"],

			#my_table input[type="number"],

			#my_table select {

				height: 32px !important;

				margin: 0 !important;

				padding: 4px 8px !important;

				font-size: 12px !important;

				border: 1px solid #ddd !important;

				border-radius: 4px !important;

			}

			

			/* Buton boyutlarını küçült */

			#my_table .btn {

				font-size: 11px !important;

				padding: 2px 4px !important;

			}

		}

	</style>



	<script>

		var formatter = new Intl.NumberFormat('tr-TR');

		var items = 0;

		var counter = [];

		var paraBirimiMetin = "TL";

		var items_iade = 0;

		var counter_iade = [];

		var items_banka = 0;

		var counter_banka = [];

		var checkboxbanka = 0;

	</script>

</head>

<body>



<?php



$firmaID = getirFirma();

$firma_ID = $firmaID->ayarlar_id;



$modulSorgula = modulSorgula($firma_ID, 1);

?>

<!-- Main Wrapper -->

<div class="main-wrapper">

	<!-- Header -->

	<?php $this->load->view("include/header"); ?>

	<!-- /Header -->



	<!-- Sidebar -->

	<?php $this->load->view("include/sidebar"); ?>

	<!-- /Sidebar -->



	<!-- Page Wrapper -->

	<div class="page-wrapper">

		<div class="content container-fluid">



			<!-- Page Header -->

			<div class="page-header">

				<div class="row">

					<div class="col-sm-10">

						<h3 class="page-title">Satış Sözleşmesi Oluştur</h3>

						<ul class="breadcrumb">

							<li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>

							<li class="breadcrumb-item">Sözleşme</li>

							<li class="breadcrumb-item active">Satış Sözleşmesi Oluştur</li>

						</ul>

					</div>

					<div class="d-flex justify-content-end text-align-center col-sm-2">

						<a class="btn btn-outline-light" href="javascript:history.back()"><i class="fa fa-history"></i>

							<br>Önceki Sayfa</a>

					</div>

				</div>

			</div>

			<!-- /Page Header -->



			<div id="newRow"></div>



			<?php

			$anaHesap = anaHesapBilgisi();

			$ayarlarQ = "SELECT * FROM ayarlar WHERE ayarlar_id = '$anaHesap' ";

			$ayarlarExe = $this->db->query($ayarlarQ)->row();

			?>



			<div class="row">

				<div class="col-md-12">

					<div class="card">

						<div class="card-body">

							<?php

							if ($faturaDetay) {//düzenle?>

							<form action="<?= base_url("fatura/satisFaturasiGuncelle/"); ?>" method="POST" id="myForm" enctype="multipart/form-data">

								<?php }

								else{//ekle

								?>

								<form action="<?= base_url("fatura/satisFaturasiOlustur"); ?>" method="POST"

									  id="myForm" enctype="multipart/form-data">

									<?php

									}

									?>





									<input type="hidden" name="satis_id" value="<?= $faturaDetay->satis_id ?>">

									<input type="hidden" name="satis_cariID" id="cari_id" value="<?= $cari->cari_id ?>">

									<input type="hidden" name="ch_id" id="ch_id"

										   value="<?= $faturaDetay->satis_cariHareketiID; ?>">



									<input type="hidden" class="form-control" name="cari_kodu" id="kod_input"

										   required="" value="<?= $cari->cari_kodu ?>">



									<div class="row">

										<div class="col-md-7">

											<div class="col-md-12">

												<div class="form-group row">

													<label class="col-sm-3 text-sm-left pt-2">Cari TCKN/VKN Veya cari

														Adı</label>

													<div class="col-sm-8">

														<input type="text" class="form-control" name="cari_tcknvkn"

															   id="tcknvkn_input" required=""

															   placeholder="Cari tckn/vkn veya cari adı yazmaya başlayıp, listeden seçiniz."

															   value="<?= $cari_value ?>" onblur="cariKontrol();">

													</div>

													<div class="col-sm-1 p-0 mt-2" id="cariEkleBtn"

														 style="display: none;">

														<a href="javascript(0);" class="col-8 border-0 p-2 text-center"

														   style="background-color: forestgreen;color:#fff;font-size:14px;border-radius: 5px;"

														   data-toggle='modal' data-target='#cariEkle'><i

																	class="fa fa-user-plus"></i></a>

													</div>

												</div>

											</div>



											<div class="col-md-12" style="display: none;">

												<div class="form-group row">

													<label class="col-sm-3 text-sm-left pt-2">Adres</label>

													<div class="col-sm-8">

														<!-- <input type="text" class="form-control" name="" id="cari_adres_input" readonly> -->

														<textarea class="form-control" name="" id="cari_adres_input"

																  readonly=""

																  style="resize: none; background-color: #f8f9fa;"><?= $cari->cari_adres ?></textarea>

													</div>

												</div>

											</div>

										</div>

										<div class="col-md-5">



											<div class="card">

												<div class="row no-gutters">

													<div class="d-flex col-sm-5 justify-content-center align-items-center">

														<?php if ($ayarlarExe->ayarlar_logoName) { ?>

															<img class="card-img" alt="logo"

																 src="<?= base_url(); ?>assets/bulut/logo/<?= $ayarlarExe->ayarlar_logoName; ?>">

														<?php } ?>

													</div>

													<div class="col-sm-7">

														<div class="card-body">

															<h5 class="card-title"

																style="cursor:pointer;"><?= $ayarlarExe->ayarlar_firmaAd; ?></h5>

															<p class="card-text"><?= $ayarlarExe->ayarlar_adres; ?></p>

															<p class="card-text" style="display: none;"><?= $ayarlarExe->ayarlar_vergiDairesi; ?>

																V.D. <?= $ayarlarExe->ayarlar_vergiNo; ?></p>

														</div>

													</div>

												</div>

											</div>

										</div>

									</div>



									<div class="row col-md-12 border-top pt-4"

										 id="faturaBilgisiDiv" <?php if (!$faturaDetay) echo "style='display:none;'"; ?>>

										<div class="col-12">

											<div class="row">

												<div class="col-md-7">													<div class="col-md-12">

														<div class="form-group row">

															<label class="col-sm-3 text-sm-left pt-2">Sözleşme No <span style="color: red;">*</span></label>

															<div class="col-sm-9">

																<input type="text" class="form-control"

																	   name="satis_faturaNo"

																	   required="" autocomplete="off"

																	   value="<?= $faturaDetay->satis_faturaNo ?>">

															</div>



														</div>

													</div><div class="col-md-12" style="display: none;">

														<div class="form-group row">

															<label class="col-sm-3 text-sm-left pt-2">Tip</label>

															<div class="col-sm-9" id="satis_faturaTip_div">

																<select class="form-control" name="satis_faturaTip"

																		id="satis_faturaTip" required="">

																	<option value="1" <?php if ($faturaDetay->satis_earsivfaturaTip == 1) echo "selected"; ?>>

																		SATIŞ

																	</option>

																	<option value="2" <?php if ($faturaDetay->satis_earsivfaturaTip == 2) echo "selected"; ?>>

																		İADE

																	</option>

																	<option value="3" <?php if ($faturaDetay->satis_earsivfaturaTip == 3) echo "selected"; ?>>

																		TEVKİFAT

																	</option>

																	<option value="4" <?php if ($faturaDetay->satis_earsivfaturaTip == 4) echo "selected"; ?>>

																		İSTİSNA

																	</option>

																	<option value="5" <?php if ($faturaDetay->satis_earsivfaturaTip == 5) echo "selected"; ?>>

																		İADE-İSTİSNA

																	</option>

																	<option value="6" <?php if ($faturaDetay->satis_earsivfaturaTip == 6) echo "selected"; ?>>

																		ÖZEL MATRAH

																	</option>

																</select>

															</div>



														</div>

													</div>

													<div class="col-md-12" style="display: none;">

														<div class="form-group row">

															<label class="col-sm-3 text-sm-left pt-2">Para

																Birimi</label>

															<div class="col-sm-5">

																<select class="form control select"

																		name="satis_paraBirimi"

																		id="satis_paraBirimi">

																	<option value="1" <?php if ($faturaDetay->satis_paraBirimi == 1) {

																		echo "selected";

																		$paraBirimi = "TL";

																	} ?>>

																		Türk Lirası

																	</option>

																	<option value="2" <?php if ($faturaDetay->satis_paraBirimi == 2) {

																		echo "selected";

																		$paraBirimi = "USD";

																	} ?>>

																		Amerikan Doları

																	</option>

																	<option value="3" <?php if ($faturaDetay->satis_paraBirimi == 3) {

																		echo "selected";

																		$paraBirimi = "EUR";

																	} ?>>

																		Euro

																	</option>

																	<option value="4" <?php if ($faturaDetay->satis_paraBirimi == 4) {

																		echo "selected";

																		$paraBirimi = "GBP";

																	} ?>>

																		İngiliz Sterlini

																	</option>

																</select>

															</div>

															<div class="col-sm-4">

																<input type="text" name="satis_guncelKur"

																	   id="satis_guncelKur"

																	   class="form-control" placeholder="Döviz kuru"

																	<?php if ($faturaDetay->satis_guncelKur == null) echo 'style="display:none;"'; ?>

																	   value="<?= $faturaDetay->satis_guncelKur ?>"/>

															</div>

														</div>

													</div>

												</div>



												<div class="col-md-5">



													<div class="col-md-12">

														<div class="form-group row">

															<label class="col-sm-3 text-sm-left pt-2"><!--Düzenleme-->

																Tarihi</label>

															<div class="col-sm-9">

																<input type="text" class="datepicker form-control"

																	   name="satis_faturaTarihi" autocomplete="off"

																	   required=""

																	   value="<?= getirBugun(); ?>"/>

															</div>

														</div>

													</div>



													<div class="col-md-12">

														<div class="form-group row">

															<label class="col-sm-3 text-sm-left pt-2"><!--Düzenleme-->

																Saati</label>

															<div class="col-sm-9">

																<input type="text" class="form-control"

																	   name="satis_faturaSaati"

																	   autocomplete="off" required=""

																	   value="<?= getirSaat(); ?>"

																	   id="datetimepicker"/>

															</div>

														</div>

													</div>													<div class="col-md-12" style="display: none;">

														<div class="form-group row">

															<label class="col-sm-3 text-sm-left pt-2">Etiket</label>

															<div class="col-sm-9">



																<input type="text" class="form-control"  id="satis_faturaEtiketID"

																	   name="satis_faturaEtiketID"

																	   autocomplete="off"     placeholder="Etiket adını yazmaya başlayıp, listeden seçiniz."

																	   value="<?= $etiket->etiket_adi ?>" onblur="etiketKontrol();"

																	   />



																<input type="hidden" class="form-control" name="etiket_id" id="etiket_id"

																	    value="<?= $etiket->etiket_id ?>">

															</div>

														</div>

													</div>

												</div>

											</div>

										</div>

									</div>

									<div class="row mt-4 mb-4 p-4 border-top border-bottom"

										 id="checkboxDiv"<?php if (!$faturaDetay) echo "style='display:none;'"; ?>>

										<div class="row pl-4">



											<div class="col-md-4" style="display: none;">												<input class="form-check-input" type="checkbox" value="1"

													   id="kdvCheck" onchange="kdvCheckFunc()"

													   name="kdvCheck" style="width:30px;height:20px;" checked>

												<label class="form-check-label ml-3" for="kdvCheck">

													KDV Dahil

												</label>



											</div>





										</div>

									</div>



									<div class="row"

										 id="iade_fatura_bilgi" <?php if (empty($iadeFaturalari)) echo 'style="display: none;"'; ?>>

										<div class="col-md-12">

											<h4 class="card-title mt-4">İade Fatura Bilgisi </h4>

											<div class="row">

												<div class="table-responsive">

													<input type="hidden" name="iade_count" id="iade_count">

													<table class="table table-bordered custom-table mb-0" id="my_table">

														<tbody id="tbody_iade">

														<?php

														$itemsIade = 0;

														foreach ($iadeFaturalari as $itemIade) {

															$itemsIade++; ?>

															<tr>

																<td>Numarası</td>

																<td>

																	<input type='text' class='form-control'

																		   name='iadenumara[]'

																		   id='iadenumara<?= $itemsIade ?>'

																		   value="<?= $itemIade->fatura_numarasi ?>">

																</td>

																<td>Tarihi</td>

																<td>

																	<input type='date' class='form-control'

																		   name='iadetarih[]'

																		   id='iadetarih<?= $itemsIade ?>'

																		   value="<?= $itemIade->tarih ?>">

																</td>

																<td>

																	<button type='button' class='btn btn-info btn-sm'

																			onclick='addItemIade()'>

																		<i class='fa fa-plus'></i> Ekle

																	</button>

																	<button type='button' onclick='deleteRowIade(this);'

																			class='btn btn-danger btn-sm'

																			id='<?= $itemsIade ?>'>

																		<i class='fa fa-trash'></i> Sil

																	</button>

																</td>

															</tr>

															<script>



																items_iade++;

																counter_iade.push(items_iade);

																document.getElementById("iade_count").value = items_iade;

															</script>

															<?

														}

														?>





														</tbody>

													</table>

												</div>

											</div>

										</div>



									</div>





									<div class="row"

										 id="stokBilgisiDiv" <?php if (!$faturaDetay) echo "style='display:none;'"; ?>>

										<div class="col-md-12">

											<h4 class="card-title mt-4">Stok Bilgisi <i

														class="fa fa-info-circle fa-xs text-info" data-toggle="tooltip"

														data-placement="right"

														title="Tablonun istediğiniz kenarından tutup büyütme ve küçültme işlemi yapabilirsiniz."></i>



												<a href="javascript:void(0)" class="btn btn-success btn-sm"

												   onclick="addItem();" style="float:right;"><i class="fa fa-plus"></i>

													Yeni

													Satır Ekle</a>

											</h4>



											<div class="row">

												<div class="table-responsive">

													<table class="table table-bordered custom-table mb-0" id="my_table">

														<thead>

														<tr>

															<th>Stok Adı</th>

															<th>Stok Kodu</th>

															<!-- <th>Barkod</th> Barkod başlığı kaldırıldı -->

															<th>Birim</th>

															<th>Miktar</th>

															<th>Birim Fiyat <span id="birimFiyatParaBirimi"></span>

															</th>

															<th>KDV (%)</th>

															<th>Toplam Fiyat</th>

															<th>Kullanıcı Sayısı</th>

															<th></th>

														</tr>

														</thead>

														<tbody id="tbody">

														<?php

														$items = 0;



														foreach ($stok as $item) {

															$items++;

															$tevkifatVal = 0;

															$tevkifatOran = 0;

															if ($item->satisStok_tevkifat_id != 0) {

																$sqlTevkifat = "select * from tevkifatKodlari inner join satisFaturasiStok on tevkifat_id=satisStok_tevkifat_id where satisStok_id=" . $item->satisStok_id;

																$tevkifatExe = $this->db->query($sqlTevkifat)->row();

																$tevkifatVal = $tevkifatExe->tevkifat_orani . '|' . $tevkifatExe->tevkifat_id;

																$tevkifatOran = $tevkifatExe->tevkifat_orani;

															}

															?>

															<tr>

																<td>

																	<input type="hidden" name="satisFaturasiStok_id[]"

																		   value="<?= $item->satisStok_id; ?>">

																	<input type='text' class='form-control'

																		   name='stokadi[]'

																		   id='stokadi<?= $items ?>' readonly

																		   value="<?= $item->stok_ad ?>">

																</td>

																<td>

																	<input type='text' class='form-control'

																		   name='stokkodu[]' id='stokkodu<?= $items ?>'

																		   readonly value="<?= $item->stok_kodu ?>">

																</td>

																<!-- Barkod inputları kaldırıldı -->

																<td>

																	<select class='form-control' name='birim[]'

																			id='birim<?= $items ?>'>

																		<option value='0'>Birim</option><?= $birimler ?>

																	</select>



																</td>

																<td>

																	<input type='number' step='0.1' class='form-control'

																		   name='miktar[]'

																		   id='miktar<?= $items ?>' required=''

																		   style='width:175px;'

																		   value="<?= $item->satisStok_miktar ?>">

																</td>



																<td>

																	<input type='text' class='form-control'

																		   name='birimfiyat[]'

																		   id='birimfiyat<?= $items ?>' required=''

																		   inputmode='decimal' pattern='[0-9.,]*'

																		   value="<?= $item->satisStok_birimFiyat ?>">

																</td>



																<td>

																	<select class='form-control' name='kdv[]'

																			id='kdv<?= $items ?>'>

																		<option value='0' <?php if ($item->satisStok_kdv == 0) echo "selected"; ?>>

																			0

																		</option>

																		<option value='1' <?php if ($item->satisStok_kdv == 1) echo "selected"; ?>>

																			1

																		</option>

																		<option value='8' <?php if ($item->satisStok_kdv == 8) echo "selected"; ?>>

																			8

																		</option>

																		<option value='10' <?php if ($item->satisStok_kdv == 10) echo "selected"; ?>>

																			10

																		</option>

																		<option value='18' <?php if ($item->satisStok_kdv == 18) echo "selected"; ?>>

																			18

																		</option>

																		<option value='20' <?php if ($item->satisStok_kdv == 20) echo "selected"; ?>>

																			20

																		</option>

																	</select>

																</td>

																<td>

																	<input type='hidden' name='indirimtutari[]'

																		   id='indirimtutari<?= $items ?>'>

																	<input type='text' class='form-control'

																		   name='toplam[]'

																		   id='toplam<?= $items ?>' readonly

																		   value="<?= $item->satisStok_indirimlifiyat ?>">

																	<input type='hidden' name='toplamHidden[]'

																		   id='toplamHidden<?= $items ?>'

																		   value="<?= $item->satisStok_indirimlifiyat ?>">

																</td>

																<td>

																	<input type='number' class='form-control'

																		   name='kullanici_sayisi[]'

																		   id='kullanici_sayisi<?= $items ?>'

																		   value="<?= $item->satisStok_kullanici_sayisi ?>"

																		   min="0" style="width:100px;">

																</td>

																<td>

																	<button type='button' class='btn btn-info btn-sm'

																			data-toggle='modal'

																			data-target='#add_category'

																			data-idsi='<?= $items ?>'

																			id='eklebuton<?= $items ?>'>

																		<i class='fa fa-plus'></i> Ekle

																	</button>

																	<button type='button' onclick='deleteRow(this);'

																			class='btn btn-danger btn-sm'

																			id='<?= $items ?>'>

																		<i class='fa fa-trash'></i> Sil

																	</button>

																</td>

															</tr>

															<td id='indirimytd<?= $items ?>' <?php if ($item->satisStok_satirIskonto == 0) echo "style='display: none;'"; ?>>

																<label>İndirim (%)</label>

																<input type='number' step='0.1' class='form-control'

																	   placeholder='İndirim (%)' name='indirimyuzde[]'

																	   id='indirimyuzde<?= $items ?>'

																	<?php if ($item->satisStok_indirimTutari != 0) echo ' value="' . $item->satisStok_indirimTutari . '"'; ?>>

															</td>

															<td id='tevkifatselecttd<?= $items ?>' <?php if ($item->satisStok_tevkifat_id == 0) echo "style='display: none;'"; ?>>

																<label>Tevkifat</label>

																<select name='tevkifat[]' id='tevkifat<?= $items ?>'

																		class='form-control'

																		onchange='tevkifat(this,<?= $items ?>)'>

																	<option value='0'>Seçiniz

																	</option><?php echo $tevkifat; ?>

																</select>

																<script>

																	document.getElementById('tevkifat<?= $items ?>').value = '<?= $tevkifatVal ?>';

																</script>

															</td>

															<td id='tevkifattexttd<?= $items ?>' <?php if ($item->satisStok_tevkifat_id == 0) echo "style='display: none;'"; ?>>

																<label>Tevkifat Oranı(%)</label>

																<input type='text' class='form-control'

																	   name='tevkifat_oran[]'

																	   id='tevkifat_oran<?= $items ?>' readonly

																	   placeholder='Tevkifat Oranı(%)'

																	   value="<?= $tevkifatOran * 100 ?>">

															</td>

															<script>

																items++;

																counter.push(items);

																document.getElementById("birim" + items).value = <?= $item->stok_birimID ?>;

																document.getElementById("birim" + items).style.pointerEvents = "none";

																document.getElementById("birim" + items).style.backgroundColor = "#e9ecef";





															</script>



															<?

														}



														?>



														</tbody>



													</table>

												</div>

											</div>



											<div class="row"

												 id="vergiMuafiyet" <?php if ($faturaDetay->satis_vergiMuafiyetSebep == "") echo 'style="display: none;"'; ?>>

												<div class="col-md-12">

													<h4 class="card-title mt-4">VERGİ MUAFİYET </h4>

													<table class="table custom-table mb-0">

														<tr>

															<td style="width:5%;"><label>Vergi Muafiyet Sebebi</label>

															</td>

															<td>

																<input type="text" name="vergiMuafiyet_sebep"

																	   id="vergiMuafiyet_sebep" class="form-control"

																	   value="<?= $faturaDetay->satis_vergiMuafiyetSebep ?>">

															</td>

														</tr>

													</table>

												</div>

											</div>



											<div class="row"

												 id="istisna_table" <?php if ($faturaDetay->satis_istisna_id == 0) echo 'style="display: none;"'; ?>>

												<div class="col-md-12">

													<h4 class="card-title mt-4">VERGİ MUAFİYET </h4>

													<p style="font-style:italic;color:red;">

														! İhracat faturasında 301 nolu kodu kullanız . .<br>

														! İhracat kayıtlı faturalarda 701 , 702 veya 703 nolu kodlar kullanız . .

													</p>

													<table class="table custom-table mb-0">

														<tr>

															<td style="width:5%;"><label>İstisna Sebebi</label></td>

															<td>

																<select class="form-control" name='istisna_sebebi'

																		id='istisna_sebebi'>

																	<option value="0">Seçiniz</option>

																	<?= $istisna; ?>

																</select>

															</td>

														</tr>

													</table>

												</div>

											</div>



											<div class="row"

												 id="bankabilgileri" style="display: none;">

												<div class="col-md-12">

													<h4 class="card-title mt-4">Banka Bilgileri</h4>

													<input type="hidden" name="bankaCount" id="bankaCount">

													<table class="table table-bordered custom-table mb-0" id="my_table">

														<tbody id="tbody_ayarlarbanka">

														</tbody>

													</table>

												</div>

											</div>





											<div class="row">

												<div class="table-responsive">



													<table class="table table-stripped table-center table-hover">

														<thead></thead>

														<tbody>

														<tr style="display: none;">

															<td></td>

															<td></td>

															<td></td>

															<td class="text-right">Ara Toplam</td>

															<td class="text-right"

																id="aratop"><?= $faturaDetay->satis_araToplam . " " . $paraBirimi ?></td>

														</tr>

														<tr style="display: none;">

															<td></td>

															<td></td>

															<td></td>

															<td class="text-right">Toplam İskonto(İndirim)</td>

															<td class="text-right"

																id="indirimtop"><?= $faturaDetay->satis_indirimToplam . " " . $paraBirimi ?></td>

														</tr>

														<tr style="display: none;">

															<td></td>

															<td></td>

															<td></td>

															<td class="text-right">Net Tutar(Matrah)</td>

															<td class="text-right"

																id="nettutartop"><?= $faturaDetay->satis_netTutar . " " . $paraBirimi ?></td>

														</tr>

														<tr style="display: none;">

															<td></td>

															<td></td>

															<td></td>

															<td class="text-right">Hesaplanan KDV</td>

															<td class="text-right"

																id="kdvtop"><?= $faturaDetay->satis_kdvToplam . " " . $paraBirimi ?></td>

														</tr>

														<tr style="display: none;">

															<td></td>

															<td></td>

															<td></td>

															<td class="text-right">Hesaplanan KDV Tevkifat</td>

															<td class="text-right"

																id="kdvtevkifat"><?= $faturaDetay->satis_tevkifatToplam . " " . $paraBirimi ?></td>

														</tr>

														<tr style="display: none;">

															<td></td>

															<td></td>

															<td></td>

															<td class="text-right">Vergiler Dahil Ödenecek Tutar</td>

															<td class="text-right"

																id="vergidahil"><?= $faturaDetay->satis_vergiDahilToplam . " " . $paraBirimi ?></td>

														</tr>

														<tr>

															<td></td>

															<td></td>

															<td></td>

															<td class="text-right">Genel Toplam</td>

															<td class="text-right"

																id="geneltop"><?= $faturaDetay->satis_genelToplam . " " . $paraBirimi ?></td>

														</tr>

														</tbody>

													</table>



													<!-- <div class="float-right">

														Ara Toplam: <span id="aratop"	style="font-weight: bold;"></span>

														<span style="margin-right:20px;"></span>

														KDV: <span id="kdvtop" style="font-weight: bold;"></span>

														<span style="margin-right:20px;"></span>

														Genel Toplam: <span id="geneltop" style="font-weight: bold;"></span>

														<span style="margin-right:10px;"></span>

													</div> -->



												</div>

											</div>

										</div>

									</div>



									<div class="row"

										 id="faturaAltBilgisiDiv" <?php if (!$faturaDetay) echo "style='display:none;'"; ?>>

										<div class="col-md-12">

											<h4 class="card-title mt-4">Ek Bilgiler</h4>

											<div class="row">

												<div class="col-md-12">

													<div class="form-group">

														<label>Açıklama</label>

														<textarea class="form-control"

																  name="satis_aciklama"><?= $faturaDetay->satis_aciklama ?></textarea>

													</div>

												</div>

											</div>											<!-- Dosya Yükleme Alanı -->

											<div class="row">

												<div class="col-md-12">

													<div class="form-group">

														<label>Satış sözleşmesini lütfen buraya yükleyiniz <span style="color: red;">*</span></label>

														<?php if (isset($faturaDetay) && !empty($faturaDetay->satis_dosya)): ?>

															<!-- Mevcut Dosyalar -->

															<div class="alert alert-info mb-3">

																<h6><i class="fa fa-info-circle"></i> Yüklü Dosyalar:</h6>

																<?php 

																$dosyalar = explode(',', $faturaDetay->satis_dosya);

																$dosya_sayisi = 0;

																foreach ($dosyalar as $dosya): 

																	$dosya = trim($dosya);

																	if (!empty($dosya)):

																		$dosya_sayisi++;

																		$dosya_uzantisi = pathinfo($dosya, PATHINFO_EXTENSION);

																		$dosya_adi = pathinfo($dosya, PATHINFO_BASENAME);

																?>

																	<div class="d-flex justify-content-between align-items-center border-bottom py-2">

																		<div class="d-flex align-items-center">

																			<?php if (in_array(strtolower($dosya_uzantisi), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>

																				<i class="fa fa-image text-success mr-2"></i>

																			<?php elseif (strtolower($dosya_uzantisi) == 'pdf'): ?>

																				<i class="fa fa-file-pdf text-danger mr-2"></i>

																			<?php else: ?>

																				<i class="fa fa-file text-info mr-2"></i>

																			<?php endif; ?>

																			<span><?= $dosya_adi; ?></span>

																		</div>

																		<div>
																			<?php 
																			// Dosya yolunu düzelt - eğer zaten "faturalar/" ile başlıyorsa tekrarlanmasın
																			$dosya_yolu = (strpos($dosya, 'faturalar/') === 0) ? 
																				base_url('assets/uploads/' . $dosya) : 
																				base_url('assets/uploads/faturalar/' . $dosya);
																			?>
																			<a href="<?= $dosya_yolu; ?>" 

																			   target="_blank" class="btn btn-sm btn-primary mr-1">

																				<i class="fa fa-eye"></i> Görüntüle

																			</a>

																			<?php if (in_array(strtolower($dosya_uzantisi), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>

																				<button type="button" class="btn btn-sm btn-info" 

																				        onclick="previewImage('<?= $dosya_yolu; ?>', '<?= $dosya_adi; ?>')">

																					<i class="fa fa-search-plus"></i> Önizleme

																				</button>

																			<?php endif; ?>

																		</div>

																	</div>

																<?php 

																	endif;

																endforeach; 

																?>

																<small class="text-muted">Toplam <?= $dosya_sayisi; ?> dosya yüklenmiş</small>

															</div>

														<?php endif; ?>

														

														<input type="file" class="form-control" name="fatura_dosya[]" multiple accept="*/*" <?= (!isset($faturaDetay) || empty($faturaDetay->satis_dosya)) ? 'required' : ''; ?>>

														<?php if (isset($faturaDetay) && !empty($faturaDetay->satis_dosya)): ?>

															<small class="text-muted">Yeni dosya seçerseniz, mevcut dosyalar değiştirilecektir.</small>

														<?php endif; ?>

													</div>

												</div>

											</div>

											<!-- /Dosya Yükleme Alanı -->

										</div>



										<div class="col-md-12 p-0">

											<div class="row">

												<div class="col-md-12">

													<button type="submit" class="btn btn-danger" style="width:100%;"

															id="submitBtn">

														Kaydet

													</button>

												</div>

											</div>

										</div>

									</div>





									<input type="hidden" id="aratopHidden" name="aratopHidden"

										   value="<?= $faturaDetay->satis_araToplam ?>">

									<input type="hidden" id="kdvtopHidden" name="kdvtopHidden"

										   value="<?= $faturaDetay->satis_kdvToplam ?>">

									<input type="hidden" id="geneltopHidden" name="geneltopHidden"

										   value="<?= $faturaDetay->satis_genelToplam ?>">

									<input type="hidden" id="vergidahilHidden" name="vergidahilHidden"

										   value="<?= $faturaDetay->satis_vergiDahilToplam ?>">

									<input type="hidden" id="indirimtopHidden" name="indirimtopHidden"

										   value="<?= $faturaDetay->satis_indirimToplam ?>">

									<input type="hidden" id="tevkifattopHidden" name="tevkifattopHidden"

										   value="<?= $faturaDetay->satis_tevkifatToplam ?>">

									<input type="hidden" id="netTutarInput" name="netTutarInput"

										   value="<?= $faturaDetay->satis_netTutar ?>">

								</form>

						</div>

					</div>

				</div>

			</div>

		</div>

	</div>

	<!-- /Page Wrapper -->



</div>

<!-- /Main Wrapper -->



<div id="edit_category" class="modal custom-modal fade" role="dialog">

	<div class="modal-dialog modal-dialog-centered" role="document">

		<div class="modal-content">

			<div class="modal-header">

				<h5 class="modal-title">Stok Ekle</h5>

				<button type="button" class="close" data-dismiss="modal" aria-label="Close">

					<span aria-hidden="true">&times;</span>

				</button>

			</div>

			<div class="modal-body">

				<form action="#" method="POST">

					<input class="form-control" type="hidden" id="idsi" name="id">

					<input class="form-control" type="hidden" id="stokID" name="stokID">

					<div class="form-group">

						<label>Stok Adına Göre <span class="text-danger">*</span></label>

						<input class="form-control" type="text" id="stokAdi" name="stokAdi" required=""/>

					</div>



					<div class="form-group">

						<label>Stok Koduna Göre <span class="text-danger">*</span></label>

						<input class="form-control" type="text" id="stokKodu" name="stokKodu" required=""/>

					</div>



					<div class="submit-section">

						<!-- <button class="btn btn-danger submit-btn">Seç</button> -->

						<button type="button" class="btn btn-danger" data-dismiss="modal">Kapat</button>

					</div>

				</form>

			</div>

		</div>

	</div>

</div>





<div id="cariEkle" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"

	 aria-labelledby="myLargeModalLabel" aria-hidden="true">

	<div class="modal-dialog modal-lg">

		<div class="modal-content">

			<div class="modal-header">

				<h5 class="modal-title">Cari Ekle</h5>

				<button type="button" class="close" data-dismiss="modal" aria-label="Close">

					<span aria-hidden="true">&times;</span>

				</button>

			</div>

			<div class="modal-body">

				<form action="<?= base_url("cari/yeniCariKartiOlustur"); ?>" method="POST" id="myForm">

					<div class="row">

						<div class="form-group col-md-12">

							<input type="hidden" name="page" value="satisfatura">

							<label>Cari Tipi</label>

							<select class="select" name="cari_bireysel" required="" id="cariBireysel">

								<option value="">Seçiniz...</option>

								<option value="1">Bireysel Müşteri</option>

								<option value="0">Kurumsal Müşteri</option>

								<option value="2">Diğer</option>

							</select>



							<input type="hidden" name="cari_alias_pk" id="alias_pk">

						</div>

					</div>

					<div class="row" style="display: none;" id="cari_input">

						<div class="row p-3">

							<div class="col-md-4">

								<div class="form-group">

									<label>TCKN / VKN</label>

									<input type="number" class="form-control" required

										   name="cari_vergiNumarasi"

										   oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"

										   maxlength="11" id="vergiNumarasi" autocomplete="off"><img

											src="/assets/img/Eclipse-1s-200px.gif"

											style="position: absolute; right:15px; bottom: 95px; width:40px; display:none;"

											id="loading-image"/>

								</div>

								<div class="form-group">

									<label>Vergi Dairesi</label>

									<input type="text" class="form-control" name="cari_vergiDairesi"

										   id="vergiDairesi" autocomplete="off" required=""/>

								</div>

							</div>



							<div class="col-md-4">

								<div class="form-group">

									<label>Cari Kodu</label>

									<input type="text" class="form-control" name="cari_kodu" id="cari_kodu"

										   required=""

										   autocomplete="off" required minlength="3">

								</div>

								<div class="form-group">

									<label>Cari Adı</label>

									<input type="text" class="form-control" name="cari_ad" required

										   autocomplete="off" id="cariAd">

								</div>



							</div>





							<div class="col-md-4">

								<div class="form-group">

									<label>Cari Grup</label>

									<select class="select" name="cari_cariGrupKoduID" id="cariGrupKodu">

										<option value="0">Seçiniz...</option>

										<?php foreach ($cariGruplari as $cg) { ?>

											<option value="<?= $cg->cariGrup_id ?>"><?= $cg->cariGrup_kodu; ?>

												(<?= $cg->cariGrup_ad; ?>)

											</option>

										<?php } ?>

									</select>

								</div>

								<div class="form-group" id="c_soyad" style="display: none;">

									<label>Cari Soyadı</label>

									<input type="text" class="form-control" name="cari_soyad" id="cariSoyad"

										   autocomplete="off">

								</div>

							</div>

						</div>

					</div>



					<h4 class="card-title mt-4">Cari İletişim Bilgileri</h4>

					<div class="row">

						<div class="col-md-6">



							<?php

							if ($_iller != false):

								echo '<div class="form-group">

												<label>İl</label>

												<select id="il" name="cari_il" data-plugin-selectTwo class="select ajaxIller" required>

												<option value="">Seçiniz</option>';

								foreach ($_iller as $item) {

									echo '<option value="' . $item->id . '">' . $item->il . '</option>';

								}

								echo '</select></div>';

							else:

								echo 'Kayıtlı İl Bulunamadı..!';

							endif;

							?>



							<div class="form-group">

								<label>Web sitesi</label>

								<input type="text" class="form-control" name="cari_websitesi"

									   autocomplete="off">

							</div>



							<div class="form-group">

								<label>Firma e-Posta</label>

								<input type="email" class="form-control" name="cari_firmaEPosta"

									   autocomplete="off" id="cari_eposta">

							</div>



							<div class="form-group">

								<label>Firma Telefon</label>

								<input type="text" class="form-control" name="cari_firmaTelefon"

									   autocomplete="off" id="cari_telefon"

									   oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"

									   maxlength="10">

							</div>



						</div>

						<div class="col-md-6">

							<div class="ilceler">

								<div class="form-group">

									<label>İlçe</label>

									<select id="ilce" name="cari_ilce" class="select" required>

										<option value="">İl Seçiniz</option>

									</select>

								</div>

							</div>

							<div class="form-group">

								<label>Adres</label>

								<textarea rows="9" cols="5" class="form-control"

										  placeholder="Adres bilgisi giriniz..."

										  name="cari_adres"></textarea>

							</div>

						</div>

					</div>

					<button type="submit" class="btn btn-danger" style="width:100%;" id="kydtBTN">Kaydet

					</button>

				</form>

			</div>

		</div>

	</div>

</div>





<div id="add_category" class="modal custom-modal fade" role="dialog">

	<div class="modal-dialog modal-dialog-centered" role="document">

		<div class="modal-content">

			<div class="modal-header">

				<h5 class="modal-title">İşlem Seçiniz</h5>

				<button type="button" class="close" data-dismiss="modal" aria-label="Close">

					<span aria-hidden="true">&times;</span>

				</button>

			</div>

			<div class="modal-body">

				<form action="#" method="POST">

					<input class="form-control" type="hidden" id="add_idsi" name="id">



					<select name="add_secim[]" multiple id="add_secim" class="form-control select"

							placeholder="Seçiniz...">

						<option value="1">İndirim (%)</option>

					</select>



					<div class="submit-section">

						<a class="btn btn-danger" id="ekle_kaydet">Ekle</a>

						<button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>

					</div>

				</form>

			</div>

		</div>

	</div>

</div>



<?php if ($this->session->flashdata('fatura_mukerrer')): ?>

	<script>

		swal({

			title: "Bilgilendirme",

			type: "error",

			text: "Aynı fatura veya irsaliye numarasına ait kayıt mevcuttur, tekrar deneyiniz.",

			confirmButtonText: 'Tamam',

			button: false,

			timer: 5000,

		});

	</script>

<?php endif; ?>



<?php if ($this->session->flashdata('cari_hatali')): ?>

	<script>

		swal({

			title: "Bilgilendirme",

		 type: "error",

		 text: "Cari kodu veya cari adı kayıtlı değil, lütfen listeden seçerek devam ediniz.",

		 confirmButtonText: 'Tamam',

		 button: false,

		 timer: 5000,

		});

	</script>

<?php endif; ?>



<?php $this->load->view("include/footer-js"); ?>



<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"

		integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="

		crossorigin="anonymous" referrerpolicy="no-referrer"></script>



<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script>

	$(function () {

		$('.datepicker').datepicker({

			dateFormat: 'dd.mm.yy',

			language: 'tr'/*,

			autoclose: true,

			todayHighlight: true,

			format: 'mm/dd/yyyy',

			startDate: new Date(new Date().setDate(new Date().getDate() -7)),

			endDate: new Date(new Date())*/

		});

	});



	$(function () {

		$('#datetimepicker').datetimepicker({

			format: 'HH:mm'

		});

	});

</script>





<script>





	<?php

	if (!$faturaDetay) {

		echo "addItem();";

	} else {

		echo "document.getElementById('istisna_sebebi').value='" . $faturaDetay->satis_istisna_id . "';";

	}



	if ($faturaDetay->satis_earsivfaturaTip == 3)

		echo 'var x = document.getElementById("add_secim");

			var option = document.createElement("option");

			option.text = "Tevkifat";

			option.value = "3";

			x.add(option);';





	if ($faturabankaDuzenle) {

		echo "checkboxbanka=1;";

		echo 'document.getElementById("bankaCheck").checked = true;var row="";$("#bankabilgileri").css("display", "block");';

		$items_banka = 0;

		foreach ($faturabankaDuzenle as $item) {

			$items_banka++;

			$html = "<tr>";

			$html .= '<td><label>Banka Adı</label><input type="hidden" name="ayarlarbanka_id[]" id="ayarlarbanka_id' . $items_banka . '" value="' . $item->ayarlarbanka_id . '"><input type="text" class="form-control" name="ayarlar_bankaadi[]" id="ayarlar_bankaadi' . $items_banka . '" value="' . $item->ayarlar_banka_ad . '"></td>';

			$html .= '<td><label>Şube</label><input type="text" class="form-control" name="ayarlar_sube[]" id="ayarlar_sube' . $items_banka . '" value="' . $item->ayarlarbanka_subeAd . '"></td>';



			$html .= '<td><label>Para Birimi</label><select class="form-control" name="ayarlar_parabirimi" id="ayarlar_parabirimi' . $items_banka . '"><option value="1"';

			if ($item->ayarlarbanka_paraBirim == 1) $html .= " selected ";

			$html .= '>Türk Lirası</option><option value="2"';

			if ($item->ayarlarbanka_paraBirim == 2) $html .= " selected ";

			$html .= '>Amerikan Doları </option><option value="3"';

			if ($item->ayarlarbanka_paraBirim == 3) $html .= " selected ";

			$html .= '>Euro</option><option value="4"';

			if ($item->ayarlarbanka_paraBirim == 4) $html .= " selected ";

			$html .= '> İngiliz Sterlini </option> </select> </td>"';



			$html .= '<td><label>Hesap No</label><input type="text" class="form-control" name="ayarlar_hesapno[]" id="ayarlar_hesapno' . $items_banka . '" value="' . $item->ayarlarbanka_hesapNo . '"></td>"';

			$html .= '<td><label>IBAN</label><input type="text" class="form-control" name="ayarlar_iban[]" id="ayarlar_iban' . $items_banka . '" value="' . $item->ayarlarbanka_iban . '"></td>';

			$html .= '<td><button type="button" onclick="deleteRowBanka(this);" class="btn btn-danger btn-sm" id="' . $items_banka . '"><i class="fa fa-trash"></i> Sil</button></td>';

			$html .= "</tr>";

			echo 'row=document.getElementById("tbody_ayarlarbanka").insertRow();row.innerHTML=\'' . $html . '\';';

			echo "items_banka=" . $items_banka . "; counter_banka.push(" . $items_banka . ");document.getElementById('bankaCount').value=" . $items_banka . ";";

		}

	}





	?>



	function kdvCheckFunc() {



		if (document.getElementById("kdvCheck").checked == true) {

			// Desktop davranışı

			if (window.innerWidth > 768) {

				$('input[name^="birimfiyat"]').prop('readonly', true);

				$('input[name^="toplam"]').prop('readonly', false);

			} else {

				// Mobile davranışı - birim fiyat aktif kalır ama toplam alanını temsil eder

				$('input[name^="birimfiyat"]').prop('readonly', false);

				$('input[name^="birimfiyat"]').attr('placeholder', 'Toplam (KDV Dahil)');

				$('input[name^="toplam"]').prop('readonly', true);

			}

		} else {

			// Her iki durumda da birim fiyat aktif, toplam readonly

			$('input[name^="birimfiyat"]').prop('readonly', false);

			if (window.innerWidth > 768) {

				$('input[name^="birimfiyat"]').attr('placeholder', 'Birim Fiyat');

			} else {

				$('input[name^="birimfiyat"]').attr('placeholder', 'Birim Fiyat (TL)');

			}

			$('input[name^="toplam"]').prop('readonly', true);

		}

		hesapla();



	}



	$(document).on('change', 'input[id="bankaCheck"]', function () {

		//function bankaCheckFunc(){

		if (document.getElementById("bankaCheck").checked == true) {

			$("#bankabilgileri").css("display", "block");



			var row = document.getElementById("tbody_ayarlarbanka");

			row.innerHTML = "";

			if (checkboxbanka == 1) {

				<?php  $items_banka = 0;foreach ($faturabankaDuzenle as $item) {

				$items_banka++;

				/*echo "items_banka++; counter_banka.push(items_banka); document.getElementById('bankaCount').value =items_banka;";*/

				$html = "<tr>";

				$html .= '<td><label>Banka Adı</label><input type="hidden" name="ayarlarbanka_id[]" id="ayarlarbanka_id' . $items_banka . '" value="' . $item->ayarlarbanka_id . '"><input type="text" class="form-control" name="ayarlar_bankaadi[]" id="ayarlar_bankaadi' . $items_banka . '" value="' . $item->ayarlar_banka_ad . '"></td>';

				$html .= '<td><label>Şube</label><input type="text" class="form-control" name="ayarlar_sube[]" id="ayarlar_sube' . $items_banka . '" value="' . $item->ayarlarbanka_subeAd . '"></td>';



				$html .= '<td><label>Para Birimi</label><select class="form-control" name="ayarlar_parabirimi" id="ayarlar_parabirimi' . $items_banka . '"><option value="1"';

				if ($item->ayarlarbanka_paraBirim == 1) $html .= " selected ";

				$html .= '>Türk Lirası</option><option value="2"';

				if ($item->ayarlarbanka_paraBirim == 2) $html .= " selected ";

				$html .= '>Amerikan Doları </option><option value="3"';

				if ($item->ayarlarbanka_paraBirim == 3) $html .= " selected ";

				$html .= '>Euro</option><option value="4"';

				if ($item->ayarlarbanka_paraBirim == 4) $html .= " selected ";

				$html .= '> İngiliz Sterlini </option> </select> </td>"';



				$html .= '<td><label>Hesap No</label><input type="text" class="form-control" name="ayarlar_hesapno[]" id="ayarlar_hesapno' . $items_banka . '"  value="' . $item->ayarlarbanka_hesapNo . '"></td>';

				$html .= '<td><label>IBAN</label><input type="text" class="form-control" name="ayarlar_iban[]" id="ayarlar_iban' . $items_banka . '"  value="' . $item->ayarlarbanka_iban . '"></td>';

				$html .= '<td><button type="button" onclick="deleteRowBanka(this);" class="btn btn-danger btn-sm" id="' . $items_banka . '"><i class="fa fa-trash"></i> Sil</button></td>';

				$html .= "</tr>";

				echo 'row=document.getElementById("tbody_ayarlarbanka").insertRow();row.innerHTML=\'' . $html . '\';';

				echo "items_banka=" . $items_banka . "; counter_banka.push(" . $items_banka . ");document.getElementById('bankaCount').value=" . $items_banka . ";";

			}?>

			} else {

				<?php  foreach ($faturabanka as $itemx) {

				$items_banka++;

				$html = "<tr>";

				$html .= '<td><label>Banka Adı</label><input type="hidden" name="ayarlarbanka_id[]" id="ayarlarbanka_id' . $items_banka . '" value="' . $itemx->ayarlarbanka_id . '"><input type="text" class="form-control" name="ayarlar_bankaadi[]" id="ayarlar_bankaadi' . $items_banka . '" value="' . $itemx->ayarlar_banka_ad . '"></td>';

				$html .= '<td><label>Şube</label><input type="text" class="form-control" name="ayarlar_sube[]" id="ayarlar_sube' . $items_banka . '" value="' . $itemx->ayarlarbanka_subeAd . '"></td>';

				$html .= '<td><label>Para Birimi</label><select class="form-control" name="ayarlar_parabirimi" id="ayarlar_parabirimi' . $items_banka . '"><option value="1"';

				if ($itemx->ayarlarbanka_paraBirim == 1) $html .= " selected ";

				$html .= '>Türk Lirası</option><option value="2"';

				if ($itemx->ayarlarbanka_paraBirim == 2) $html .= " selected ";

				$html .= '>Amerikan Doları </option><option value="3"';

				if ($itemx->ayarlarbanka_paraBirim == 3) $html .= " selected ";

				$html .= '>Euro</option><option value="4"';

				if ($itemx->ayarlarbanka_paraBirim == 4) $html .= " selected ";

				$html .= '> İngiliz Sterlini </option> </select> </td>"';



				$html .= '<td><label>Hesap No</label><input type="text" class="form-control" name="ayarlar_hesapno[]" id="ayarlar_hesapno' . $items_banka . '"  value="' . $itemx->ayarlarbanka_hesapNo . '"></td>';

				$html .= '<td><label>IBAN</label><input type="text" class="form-control" name="ayarlar_iban[]" id="ayarlar_iban' . $items_banka . '"  value="' . $itemx->ayarlarbanka_iban . '"></td>';

				$html .= '<td><button type="button" onclick="deleteRowBanka(this);" class="btn btn-danger btn-sm" id="' . $items_banka . '"><i class="fa fa-trash"></i> Sil</button></td>';

				$html .= "</tr>";

				echo 'row=document.getElementById("tbody_ayarlarbanka").insertRow();row.innerHTML=\'' . $html . '\';';

			}



				echo "items_banka=" . $items_banka . "; counter_banka.push(" . $items_banka . ");document.getElementById('bankaCount').value=" . $items_banka . ";";

			 ?>





			}

		} else {

			$("#bankabilgileri").css("display", "none");

			checkboxbanka = 0;

		}





	});

	function cariKontrol() {
		// Cari hesap kontrolü fonksiyonu
		var cariSelect = document.getElementById('cari_id');
		if (cariSelect && cariSelect.value == '') {
			alert('Lütfen cari hesap seçiniz.');
			return false;
		}
		return true;
	}

	function stokKontrol(stok) {

		var id = stok.match(/\d/g);

		var stokID = $("#stokid" + id).val();

		if (stokID == "") {

			$("#stokadi" + id).css("background-color", "#dff1e7");

			$("#stokkodu" + id).css("background-color", "#dff1e7");

			$("#barkod" + id).css("background-color", "#dff1e7");

			$("#birim" + id).css("background-color", "#dff1e7");

		} else {

			$("#stokadi" + id).css("background-color", "#e9ecef");

			$("#stokkodu" + id).css("background-color", "#e9ecef");

			$("#barkod" + id).css("background-color", "#e9ecef");

			$("#birim" + id).css("background-color", "#e9ecef");

		}

	}

	function addItem() {



	items++;

	counter.push(items);

	var html = "<tr>";

	html += "<td><input type='hidden' name='stokid[]' id='stokid" + items + "'><input type='text' class='form-control' name='stokadi[]' id='stokadi" + items + "' onblur='stokKontrol(this.id)'> </td>";

	html += "<td style='display:none'><input type='text' class='form-control' name='stokkodu[]' id='stokkodu" + items + "'  onblur='stokKontrol(this.id)'></td>";

	// Barkod inputu kaldırıldı

	html += "<td style='display:none'><select class='form-control' name='birim[]' id='birim" + items + "'><?= $birimler ?></select></td>";

	html += "<td><input type='number' step='0.1' class='form-control' autocomplete='off' name='miktar[]' id='miktar" + items + "' style='width:175px;'></td>";

	html += "<td><input type='text' class='form-control' autocomplete='off' name='birimfiyat[]' id='birimfiyat" + items + "' inputmode='decimal' pattern='[0-9.,]*'></td>";		/*html += "<td><input type='text' class='form-control' name='kdv[]' id='kdv"+items+"' readonly></td>";*/

		html += "<td><select class='form-control' name='kdv[]' id='kdv" + items + "' ><option value='0' >0</option><option value='1'>1</option><option value='8' selected>8</option><option value='10' selected>10</option><option value='18'>18</option><option value='20'>20</option></select></td>";

		html += "<td><input type='hidden' name='indirimtutari[]' id='indirimtutari" + items + "'><input type='text' class='form-control' name='toplam[]' id='toplam" + items + "' readonly><input type='hidden' name='toplamHidden[]' id='toplamHidden" + items + "'></td>";

		html += "<td><input type='number' class='form-control' name='kullanici_sayisi[]' id='kullanici_sayisi" + items + "' min='0' style='width:100px;'></td>";

		html += "<td><button type='button' class='btn btn-info btn-sm' data-toggle='modal' data-target='#add_category' data-idsi='" + items + "' id='eklebuton" + items + "' ><i class='fa fa-plus'></i> Ekle</button> <button type='button' onclick='deleteRow(this);' class='btn btn-danger btn-sm' id='" + items + "'><i class='fa fa-trash'></i> Sil</button> </td>";

		html += "</tr>";

		var row = document.getElementById("tbody").insertRow();

		row.innerHTML = html;

		var secenekler = "<td id='indirimytd" + items + "' style='display: none;'><label>İndirim (%)</label><input type='number' step='0.1' class='form-control' placeholder='İndirim (%)' name='indirimyuzde[]'   id='indirimyuzde" + items + "'></td>";

		secenekler += "<td id='tevkifatselecttd" + items + "' style='display: none;' ><label>Tevkifat</label><select  name='tevkifat[]'  id='tevkifat" + items + "'class='form-control' onchange='tevkifat(this," + items + ")'><option value='0'>Seçiniz</option><?php echo $tevkifat; ?></select></td>";

		secenekler += "<td id='tevkifattexttd" + items + "' style='display: none;' ><label>Tevkifat Oranı(%)</label><input type='text' class='form-control' name='tevkifat_oran[]' id='tevkifat_oran" + items + "' readonly placeholder='Tevkifat Oranı(%)'>";





		var row = document.getElementById("tbody").insertRow();

		row.innerHTML = secenekler;

		$("#stokadi" + items).autocomplete({

			source: '<?= env('BASE_URL'); ?>/fatura/autocompleteDataByStockName',

			minLength: 2,

			search: function(event, ui) {

				console.log('Stok arama başlatıldı:', $(this).val());

			},

			response: function(event, ui) {

				console.log('Stok arama yanıtı:', ui.content.length + ' sonuç bulundu');

			},

			select: function (event, ui) {

				event.preventDefault();



				$('.ui-autocomplete').modal('hide');



				toastr.success(ui.item.value + " stok isimli stok bilgileri getirildi.");

				let id = event.target.id;

				var idsi = id.match(/\d/g);

				idsi = idsi.join("");



				$("#stokID").val(ui.item.id);

				$("#stokid" + idsi + "").val(ui.item.id);

				$("#stokadi" + idsi + "").val(ui.item.value);

				$("#stokkodu" + idsi + "").val(ui.item.stok_kodu);

				$("#birim" + idsi + "").val(ui.item.stok_birimi);

				// Barkod alanı kaldırıldı

				$("#birimfiyat" + idsi + "").val(ui.item.stok_satisFiyati);

				$("#kdv" + idsi + "").val(ui.item.stok_satisKDV);

				$("#miktar" + idsi + "").val("1");

				$("#toplam" + idsi + "").val("1");

				document.getElementById("stokadi" + idsi).readOnly = true;

				document.getElementById("stokkodu" + idsi).readOnly = true;

				//document.getElementById("birim" + idsi).disabled = true;

				// Barkod alanı kaldırıldı

				$("#birim" + idsi).css("pointer-events", "none");

				$("#birim" + idsi).css("background-color", "#e9ecef");

				$("#stokadi" + idsi).css("background-color", "#e9ecef");

				$("#stokkodu" + idsi).css("background-color", "#e9ecef");

				// Barkod alanı kaldırıldı

				$("#birim" + idsi).css("background-color", "#e9ecef");



				var tip = $("#satis_faturaTip").val();

				if (tip != 4 && tip != 5 && tip != 6)

					vergimuafiyet();

				if (document.getElementById("kdvCheck").checked == false)

					hesapla();

			}

		});

		$("#stokkodu" + items).autocomplete({

			source: '<?= env('BASE_URL'); ?>/fatura/autocompleteDataByStockCode',

			minLength: 2,

			select: function (event, ui) {

				event.preventDefault();



				$('.ui-autocomplete').modal('hide');

				toastr.success(ui.item.value + " stok kodlu stok bilgileri getirildi.");

				let id = event.target.id;

				var idsi = id.match(/\d/g);

				idsi = idsi.join("");

				$("#stokID").val(ui.item.id);

				$("#stokid" + idsi + "").val(ui.item.id);

				$("#stokkodu" + idsi + "").val(ui.item.value);

				$("#stokadi" + idsi + "").val(ui.item.stok_ad);

				$("#birim" + idsi + "").val(ui.item.stok_birimi);

				// Barkod alanı kaldırıldı

				$("#birimfiyat" + idsi + "").val(ui.item.stok_satisFiyati);

				$("#kdv" + idsi + "").val(ui.item.stok_satisKDV);

				$("#miktar" + idsi + "").val("1");

				$("#toplam" + idsi + "").val("1");



				document.getElementById("stokadi" + idsi).readOnly = true;

				document.getElementById("stokkodu" + idsi).readOnly = true;

				document.getElementById("birim" + idsi).readOnly = true;

				// Barkod alanı kaldırıldı



				$("#birim" + idsi).css("pointer-events", "none");

				$("#birim" + idsi).css("background-color", "#e9ecef");

				$("#stokadi" + idsi).css("background-color", "#e9ecef");

				$("#stokkodu" + idsi).css("background-color", "#e9ecef");

				// Barkod alanı kaldırıldı



				$("#birim" + idsi).css("background-color", "#e9ecef");



				var tip = $("#satis_faturaTip").val();

				if (tip != 4 && tip != 5 && tip != 6)

					vergimuafiyet();

				if (document.getElementById("kdvCheck").checked == false)

					hesapla();



			}

		});



		if (document.getElementById("kdvCheck").checked == true) {

			// Desktop davranışı

			if (window.innerWidth > 768) {

				$('input[name^="birimfiyat"]').prop('readonly', true);

				$('input[name^="toplam"]').prop('readonly', false);

			} else {

				// Mobile davranışı - birim fiyat aktif kalır

				$('input[name^="birimfiyat"]').prop('readonly', false);

				$('input[name^="birimfiyat"]').attr('placeholder', 'Toplam (KDV Dahil)');

				$('input[name^="toplam"]').prop('readonly', true);

			}

		} else {

			$('input[name^="birimfiyat"]').prop('readonly', false);

			if (window.innerWidth > 768) {

				$('input[name^="birimfiyat"]').attr('placeholder', 'Birim Fiyat');

			} else {

				$('input[name^="birimfiyat"]').attr('placeholder', 'Birim Fiyat (TL)');

			}

			$('input[name^="toplam"]').prop('readonly', true);

		}

		

		// Yeni eklenen satır için mobilde placeholder ayarı

		if (window.innerWidth <= 768) {

			if (document.getElementById("kdvCheck").checked) {

				$('#birimfiyat' + items).attr('placeholder', 'Toplam (KDV Dahil)');

			} else {

				$('#birimfiyat' + items).attr('placeholder', 'Birim Fiyat (TL)');

			}

			

			// Mobil optimizasyonları da uygula

			$('#birimfiyat' + items).attr('inputmode', 'decimal');

			$('#birimfiyat' + items).attr('pattern', '[0-9.]*');

			$('#birimfiyat' + items).attr('maxlength', '15'); // 7 rakam + 2 nokta + küsurat için yeterli

		}

	}



	function tevkifat(object, items) {

		var val = object.value;

		val = (val.split("|"))[0];

		val = val * 100;

		$("#tevkifat_oran" + items).val(val);

		hesapla();

	}



	function round(value, decimals) {

		return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);

		/*debugger;

		if(value!=0){

		if (value != "NaN") {

			//return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);/*

			var x = (value);

			var array = x.toString().split(".");

			x = array[1];

			var ondalik = x.toString().substr(0, 2);

			if (parseInt(x[1]) % 2 == 0) {

				return Number(array[0] + "." + ondalik);

			} else {

				return Number(Math.ceil(value + 'e' + decimals) + 'e-' + decimals);

			}

		} else return 0;

	} else return 0;*/

	}



	function hesapla() {

		var aratop = 0;

		var kdvtop = 0;

		var geneltop = 0;

		var nettop = 0;

		var fiyat = 0;

		var indirimtop = 0;

		var tevkifattop = 0;

		var kdvHesap = 0;

		var i = 1;

		var isMobile = window.innerWidth <= 768;

		var kdvDahil = document.getElementById("kdvCheck").checked;

		

		for (i = 1; i <= items; i++) {

			var satir = $("#stokadi" + i);

			if (satir.length >= 1) {

				fiyat = 0;

				kdvHesap = 0;

				var miktar = $("#miktar" + i + "").val();

				var birimfiyat = $("#birimfiyat" + i + "").val();

				// Birim fiyattan noktaları kaldır

				birimfiyat = birimfiyat.toString().replace(/\./g, '');

				var toplam = $("#toplam" + i + "").val();

				var kdvoran = $("#kdv" + i).val();

				var indirimyuzde = $("#indirimyuzde" + i).val();

				var tevkifat = $("#tevkifat" + i).val();

				

				if (kdvDahil) {

					if (isMobile) {

						// Mobilde birim fiyat kutucuğu toplam değerini temsil eder

						var toplamTutar = parseFloat(birimfiyat) * parseFloat(miktar);

						var netTutar = toplamTutar / (1 + (parseFloat(kdvoran) / 100));

						var gercekBirimFiyat = netTutar / parseFloat(miktar);

						

						// Görünmeyen toplam alanını güncelle

						$("#toplam" + i + "").val(toplamTutar.toFixed(2));

						$("#toplamHidden" + i + "").val(toplamTutar.toFixed(2));

						

						// Hesaplama için gerçek birim fiyatı kullan

						fiyat = netTutar;

					} else {

						// Desktop davranışı (eski)

						birimfiyat = miktar * kdvoran / 100;

						birimfiyat += parseFloat(miktar);

						birimfiyat = parseFloat(toplam) / birimfiyat;

						$("#birimfiyat" + i + "").val(birimfiyat);

						fiyat = miktar * birimfiyat;
						
						// Desktop KDV dahil modda da toplamHidden KDV dahil olmalı
						var kdvTutari = fiyat * parseFloat(kdvoran) / 100;
						var fiyatKdvDahilDesktop = fiyat + kdvTutari;
						$("#toplamHidden" + i + "").val(fiyatKdvDahilDesktop.toFixed(2));
					}

				} else {

					// KDV dahil değilse normal hesaplama

					fiyat = miktar * birimfiyat;

				}

				

				aratop += fiyat;//ara toplam

				if (indirimyuzde != "") {

					var indirimtutari = fiyat * indirimyuzde / 100;

					indirimtop += parseFloat(indirimtutari); //toplam indirim

					fiyat = fiyat - parseFloat(indirimtutari);

				}

				

				if (!kdvDahil) {

					$("#toplam" + i + "").val(fiyat);
				}
				
				// toplamHidden her zaman KDV dahil olmalı
				kdvHesap = fiyat * parseFloat(kdvoran) / 100;
				var fiyatKdvDahil = fiyat + kdvHesap;
				$("#toplamHidden" + i + "").val(fiyatKdvDahil.toFixed(2));

				kdvtop += kdvHesap;



				if ( tevkifat != 0) {

					var tevkifatOran = (tevkifat.split("|"));

					var tevkifatHesap = kdvHesap * tevkifatOran[0];

					tevkifattop = tevkifattop + tevkifatHesap;

				}



				nettop = aratop - indirimtop;

				geneltop = nettop + kdvtop;



				$("#vergidahil").html(formatter.format(round((geneltop).toFixed(3), 2)) + " " + paraBirimiMetin);

				$("#vergidahilHidden").val(round((geneltop).toFixed(3), 2));





				geneltop = round(geneltop.toFixed(3), 2) - round((tevkifattop).toFixed(3), 2);



				$("#aratop").html(formatter.format(round((aratop).toFixed(3), 2)) + " " + paraBirimiMetin);

				$("#aratopHidden").val(round((aratop).toFixed(3), 2));

				$("#kdvtop").html(formatter.format(round(kdvtop, 2)) + " " + paraBirimiMetin);

				$("#kdvtopHidden").val(round(kdvtop, 2));

				$("#geneltop").html(formatter.format(round((geneltop).toFixed(3), 2)) + " " + paraBirimiMetin);

				$("#geneltopHidden").val(round((geneltop).toFixed(3), 2));



				$("#netTutarInput").val(round((nettop).toFixed(3), 2));



				$("#nettutartop").html(formatter.format(round((nettop).toFixed(3), 2)) + " " + paraBirimiMetin);



				$("#indirimtop").html(formatter.format(round((indirimtop).toFixed(3), 2)) + " " + paraBirimiMetin);

				$("#indirimtopHidden").val(round((indirimtop).toFixed(3), 2));





				$("#kdvtevkifat").html(formatter.format(round((tevkifattop).toFixed(3), 2)) + " " + paraBirimiMetin);

				$("#tevkifattopHidden").val(round((tevkifattop).toFixed(3), 2));



			}

		}



	}



	function deleteRow(button) {



		if (counter.length >= 1) {

			counter.pop();

		}
		if (counter.length == 0) {

			counter.push(1);

			toastr.warning("En az bir kayıt olması zorunludur, bu kayıt silinemez.");

		} else {



			var btnID = button.id;



			//items--;





			var btnID = button.id;



			button.parentElement.parentElement.remove();

			$('#bos' + btnID + '').remove();

			$('#indirimytd' + btnID + '').remove();

			$('#indirimttd' + btnID + '').remove();

			$('#tevkifattd' + btnID + '').remove();

			$('#istisnatd' + btnID + '').remove();

			$('#tevkifatselecttd' + btnID + '').remove();

			$('#tevkifattexttd' + btnID + '').remove();

			hesapla();



		}



	}



	toastr.options = {

		"closeButton": true,

		"debug": false,

		"newestOnTop": false,

		"progressBar": true,

		"positionClass": "toast-top-right",

		"preventDuplicates": false,

		"onclick": null,

		"showDuration": "300",

		"hideDuration": "1000",

		"timeOut": "5000",

		"extendedTimeOut": "1000",

		"showEasing": "swing",

		"hideEasing": "linear",

		"showMethod": "fadeIn",

		"hideMethod": "fadeOut"

	};



	$('#edit_category').on('show.bs.modal', function (event) {

		let idsi = $(event.relatedTarget).data('id')

		$(this).find('.modal-body #idsi').val(idsi)

	})



	$('#add_category').on('show.bs.modal', function (event) {

		let a_idsi = $(event.relatedTarget).data('idsi')

		$(this).find('.modal-body #add_idsi').val(a_idsi)

	})



	$(function () {

		$("#name_input").autocomplete({

			source: "<?= env('BASE_URL'); ?>/fatura/autocompleteDataByNameEArsiv",

			minLength: 2,

			select: function (event, ui) {

				event.preventDefault();

				console.log("Cari seçildi:", ui.item);

				console.log("Cari adresi:", ui.item.cari_adres);

				$("#cari_id").val(ui.item.id);

				$("#tcknvkn_input").val(ui.item.value);

				$("#kod_input").val(ui.item.cari_kodu);

				$("#cari_grubu_input").val(ui.item.cari_grubu);

				$("#cari_vergiDairesi_input").val(ui.item.cari_vergiDairesi);

				$("#cari_adres_input").val(ui.item.cari_adres);

				$("#cari_adres_input").prop('readonly', false); // Readonly özelliğini kaldır

				$("#cari_adres_input").prop('readonly', true); // Tekrar readonly yap

				$("#eposta_bilgi_input").val(ui.item.cari_firmaEPosta);

				console.log("Adres input değeri:", $("#cari_adres_input").val());
	


				toastr.success(ui.item.value + " cari adlı müşteriye ait bilgiler getirildi.");
				$("#faturaBilgisiDiv").css("display", "block");
				$("#faturaAltBilgisiDiv").css("display", "block");
				$("#stokBilgisiDiv").css("display", "block");
				$("#checkboxDiv").css("display", "block");
				$("#submitBtn").css("display", "block");
				var tables = document.getElementsByTagName('table');
				for (var i = 0; i < tables.length; i++) {
					resizableGrid(tables[i]);
				}
				
				// Mobilde cari seçimi sonrası birim fiyat alanlarına özel davranış
				if (window.innerWidth <= 768) {
					// Birim fiyat alanlarını mobil için optimize et
					setTimeout(function() {
						$('input[name^="birimfiyat"]').each(function() {
							// Input type'ını decimal yaparak mobilde sayısal klavyeyi etkinleştir
							$(this).attr('inputmode', 'decimal');
							$(this).attr('pattern', '[0-9.]*');
							// Maksimum uzunluk sınırı koy (noktalı hali için)
							$(this).attr('maxlength', '15'); // 7 rakam + 2 nokta + küsurat için yeterli
						});
					}, 100);
				}

			}

		});

	});
	


	$(function () {

		$("#tcknvkn_input").autocomplete({

			source: "<?= env('BASE_URL'); ?>/fatura/autocompleteDataByTcknVknEarsiv",

			minLength: 2,

			select: function (event, ui) {

				event.preventDefault();

				console.log("TCKN/VKN ile cari seçildi:", ui.item);

				console.log("Cari adresi:", ui.item.cari_adres);

				$("#cari_id").val(ui.item.id);

				$("#tcknvkn_input").val(ui.item.value);

				$("#kod_input").val(ui.item.cari_kodu);

				$("#cari_grubu_input").val(ui.item.cari_grubu);

				$("#cari_vergiDairesi_input").val(ui.item.cari_vergiDairesi);

				$("#cari_adres_input").val(ui.item.cari_adres);

				$("#cari_adres_input").prop('readonly', false); // Readonly özelliğini kaldır

				$("#cari_adres_input").prop('readonly', true); // Tekrar readonly yap

				$("#eposta_bilgi_input").val(ui.item.cari_firmaEPosta);

				console.log("Adres input değeri:", $("#cari_adres_input").val());
	


				toastr.success(ui.item.value + " cari adlı müşteriye ait bilgiler getirildi.");
				$("#faturaBilgisiDiv").css("display", "block");
				$("#faturaAltBilgisiDiv").css("display", "block");
				$("#stokBilgisiDiv").css("display", "block");
				$("#checkboxDiv").css("display", "block");
				$("#submitBtn").css("display", "block");
				var tables = document.getElementsByTagName('table');
				for (var i = 0; i < tables.length; i++) {
					resizableGrid(tables[i]);
				}
				
				// Mobilde cari seçimi sonrası birim fiyat alanlarına özel davranış
				if (window.innerWidth <= 768) {
					// Birim fiyat alanlarını mobil için optimize et
					setTimeout(function() {
						$('input[name^="birimfiyat"]').each(function() {
							// Input type'ını decimal yaparak mobilde sayısal klavyeyi etkinleştir
							$(this).attr('inputmode', 'decimal');
							$(this).attr('pattern', '[0-9.]*');
							// Maksimum uzunluk sınırı koy (noktalı hali için)
							$(this).attr('maxlength', '15'); // 7 rakam + 2 nokta + küsurat için yeterli
						});
					}, 100);
				}

			}

		});

	});
	


	$(function () {

		$("#satis_faturaEtiketID").autocomplete({

			source: "<?= env('BASE_URL'); ?>/fatura/autocompleteDataByEtiket",

			minLength: 2,

			select: function (event, ui) {

				event.preventDefault();

				$("#etiket_id").val(ui.item.id);

				$("#satis_faturaEtiketID").val(ui.item.value);

			}

		});

	});
	


	<?php if (!$faturaDetay) {

		echo '$("#tcknvkn_input").on("keyup", function () {

		var input=$("#tcknvkn_input").val();



		if(input=="") {

			$("#name_input").val("");

			$("#tcknvkn_input").val("");

			$("#kod_input").val("");

			$("#cari_grubu_input").val("");

			$("#cari_vergiDairesi_input").val("");

			$("#cari_adres_input").val("");

			$("#eposta_bilgi_input").val("");

			$("#faturaBilgisiDiv").css("display", "none");

			$("#faturaAltBilgisiDiv").css("display", "none");

			$("#stokBilgisiDiv").css("display", "none");

			$("#checkboxDiv").css("display", "none");

			$("#submitBtn").css("display", "none");

		}

	});';

	} else {



		echo 'var element = document.getElementById("faturaBilgisiDiv");

				element.classList.remove("d-none");';

	}

	if ($cari) {

		echo 'var element = document.getElementById("faturaBilgisiDiv");

				element.classList.remove("d-none");$("#faturaAltBilgisiDiv").css("display", "block");

				$("#stokBilgisiDiv").css("display", "block");

				$("#checkboxDiv").css("display", "block");

				$("#submitBtn").css("display", "block");';

	}

	?>





	function vergimuafiyet() {

		$("#vergiMuafiyet").css("display", "none");

		document.getElementById("vergiMuafiyet_sebep").required = false;

		var sonuc = 0;

		for (i = 1; i <= items; i++) {

			var val = $("#kdv" + i).val();

			if (val == 0) {

				sonuc = 1;

				break;

			}

		}

		if (sonuc == 1) {

			$("#vergiMuafiyet").css("display", "block");

			document.getElementById("vergiMuafiyet_sebep").required = true;

		}

	}



	$(document).on('change', 'select[name^="kdv"]', function () {

		var tip = $("#satis_faturaTip").val();

		if (tip != 4 && tip != 5 && tip != 6)

			vergimuafiyet();

		hesapla();

	});
	


	$(document).on('change', 'input[name^="miktar"]', function () {

		hesapla();



	});
	


	$(document).on('change', 'input[name^="birimfiyat"]', function () {

		hesapla();



	});
	


	$(document).on('change', 'input[name^="toplam"]', function () {

		hesapla();

	});

	$(document).on('change', 'input[name^="indirimyuzde"]', function () {

		/*

				let indirimtop = 0;



				let id = event.target.id;

				var numb = id.match(/\d/g);

				numb = numb.join("");
	


				var indirimyuzde = $("#indirimyuzde" + numb + "").val();

				var toplam = $("#toplam" + numb + "").val();



				var indirimtutari = toplam * indirimyuzde / 100;



				$("#indirimtutari" + numb + "").val(indirimtutari);
	


				for (i = 1; i <= items; i++) {

					var tutar = $("#indirimtutari" + i + "").val();

					var hesap = $.trim(tutar).length;

					if (hesap != 0)

						indirimtop = indirimtop + parseFloat($("#indirimtutari" + i + "").val());

				}



				$("#indirimtop").html(formatter.format(indirimtop) + " " + paraBirimiMetin);
	


				$("#indirimtopHidden").val(round((indirimtop).toFixed(3), 2));*/

		let id = event.target.id;

		var numb = id.match(/\d/g);

		numb = numb.join("");
	


		var indirimyuzde = $("#indirimyuzde" + numb + "").val();

		if (indirimyuzde == "100") {



			$("#vergiMuafiyet").css("display", "block");

			document.getElementById("vergiMuafiyet_sebep").required = true;

			vergimuafiyet();

		} else {



			$("#vergiMuafiyet").css("display", "none");

			document.getElementById("vergiMuafiyet_sebep").required = false;

			vergimuafiyet();



		}

		hesapla();
		




	});
	


	// Mobilde birim fiyat altında toplam gösterimi

	$(document).ready(function() {

		// Mobile birim fiyat input change event'i

		$(document).on('input change', 'input[name^="birimfiyat"]', function() {

			// Hesaplama otomatik olarak hesapla() fonksiyonunda yapılıyor

			hesapla();

			

			// Mobilde toplam gösterimini güncelle kaldırıldı

		});
		

		// KDV checkbox değiştiğinde mobil toplamları güncelle kaldırıldı

		$(document).on('change', '#kdvCheck', function() {

			// Mobil toplam gösterimi kaldırıldı

		});
		

		// Miktar değiştiğinde de toplam hesaplaması yap

		$(document).on('input change', 'input[name^="miktar"]', function() {

			hesapla();

			// Mobil toplam gösterimi kaldırıldı

		});
		

		// Birim fiyat formatlaması - binlik ayırıcı nokta ekleme
		function formatNumberWithDots(value) {
			// Sadece rakamları al
			let numericValue = value.toString().replace(/[^\d]/g, '');
			
			// Boşsa boş döndür
			if (numericValue === '') return '';
			
			// Çok uzun sayıları sınırla (maksimum 10 hane)
			if (numericValue.length > 10) {
				numericValue = numericValue.substring(0, 10);
			}
			
			// Binlik ayırıcı nokta ekle
			return numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
		}

		

		function removeDotsFromNumber(value) {
			// Noktaları kaldır ve sayısal değer döndür
			return value.toString().replace(/\./g, '');
		}
		
		// Birim fiyat input'larına formatla event'i ekle - İyileştirilmiş cursor pozisyonu
		$(document).on('input', 'input[name^="birimfiyat"]', function() {
			let cursorPosition = this.selectionStart;
			let originalValue = this.value;
			
			// Formatla
			let formattedValue = formatNumberWithDots(originalValue);
			
			// Değer değişmediyse cursor pozisyonunu değiştirme
			if (originalValue === formattedValue) {
				return;
			}
			
			this.value = formattedValue;
			
			// Cursor pozisyonunu daha akıllıca ayarla
			// Cursor'dan önceki kısmın orijinal ve formatlanmış halini karşılaştır
			let originalPrefix = originalValue.substring(0, cursorPosition);
			let numericPrefix = originalPrefix.replace(/[^\d]/g, '');
			
			// Formatlanmış değerde bu rakamların nerede bittiğini bul
			let numericCount = 0;
			let newCursorPosition = 0;
			
			for (let i = 0; i < formattedValue.length && numericCount < numericPrefix.length; i++) {
				if (/\d/.test(formattedValue[i])) {
					numericCount++;
				}
				newCursorPosition = i + 1;
			}
			
			// Cursor pozisyonunu sınırlar içinde tut
			newCursorPosition = Math.min(newCursorPosition, formattedValue.length);
			newCursorPosition = Math.max(newCursorPosition, 0);
			
			// setSelectionRange sadece text input'larda çalışır, number input'larda çalışmaz
			try {
				if (this.type !== 'number') {
					this.setSelectionRange(newCursorPosition, newCursorPosition);
				}
			} catch (e) {
				// Hata durumunda cursor pozisyonunu ayarlama
				console.warn('Cursor pozisyonu ayarlanamadı:', e);
			}
			
			hesapla();
		});

	// Form submit edildiğinde noktaları kaldır ve stok seçim kontrolü yap

	$('form').on('submit', function(e) {

		// Stok seçim kontrolü - her satırda stokid dolu mu kontrol et

		var stokSecimHatasi = false;

		var hataliSatirlar = [];

		var bosAlanlar = [];

		

		$('input[name^="stokadi"]').each(function() {

			var id = this.id.match(/\d+/);

			if (id && id[0]) {

				var stokId = $('#stokid' + id[0]).val();

				var stokAdi = $(this).val();

				var miktar = $('#miktar' + id[0]).val();

				var birimFiyat = $('#birimfiyat' + id[0]).val();

				

				// Eğer stok adı dolu ama stokid boşsa, listeden seçim yapılmamış demektir

				if (stokAdi && stokAdi.trim() !== '' && (!stokId || stokId.trim() === '')) {

					stokSecimHatasi = true;

					hataliSatirlar.push('Satır ' + id[0]);

					$(this).css('border', '2px solid red');

				} else {

					$(this).css('border', '');

				}

				

				// Zorunlu alanların dolu olup olmadığını kontrol et

				if (stokAdi && stokAdi.trim() !== '') {

					if (!miktar || miktar.trim() === '' || parseFloat(miktar) <= 0) {

						bosAlanlar.push('Satır ' + id[0] + ' - Miktar');

						$('#miktar' + id[0]).css('border', '2px solid red');

					} else {

						$('#miktar' + id[0]).css('border', '');

					}

					

					if (!birimFiyat || birimFiyat.trim() === '') {

						bosAlanlar.push('Satır ' + id[0] + ' - Birim Fiyat');

						$('#birimfiyat' + id[0]).css('border', '2px solid red');

					} else {

						$('#birimfiyat' + id[0]).css('border', '');

					}

				}

			}

		});

		

		if (stokSecimHatasi) {

			e.preventDefault();

			toastr.error('Lütfen stok adı alanında listeden seçim yapınız. Hatalı satırlar: ' + hataliSatirlar.join(', '));

			return false;

		}

		

		if (bosAlanlar.length > 0) {

			e.preventDefault();

			toastr.error('Lütfen zorunlu alanları doldurunuz: ' + bosAlanlar.join(', '));

			return false;

		}

		

		// Birim fiyat formatını düzelt

		$('input[name^="birimfiyat"]').each(function() {

			// Noktalı binlik ayırıcıları kaldır, sadece sayısal değeri bırak

			let cleanValue = removeDotsFromNumber(this.value);

			

			// Boş değilse ve geçerli bir sayıysa ayarla

			if (cleanValue && !isNaN(cleanValue)) {

				this.value = cleanValue;

			}

		});

	});
		// Sayfa yüklendiğinde mevcut değerleri formatla

		$(document).ready(function() {

			$('input[name^="birimfiyat"]').each(function() {

				// Input type'ını text'e çevir (eğer number ise)

				if (this.type === 'number') {

					$(this).attr('type', 'text');

					$(this).attr('inputmode', 'decimal');

					$(this).attr('pattern', '[0-9.,]*');

				}

				

				if (this.value && this.value !== '0') {

					this.value = formatNumberWithDots(this.value);

				}

			});

		});

	});

	// Görsel önizleme fonksiyonu
	function previewImage(imageSrc, imageName) {
		$('#previewModalLabel').text(imageName);
		$('#previewImage').attr('src', imageSrc);
		$('#downloadLink').attr('href', imageSrc);
		$('#previewModal').modal('show');
	}

	// Mobil cihaz kontrolü
	function isMobile() {
		var userAgent = navigator.userAgent.toLowerCase();
		var isMobileDevice = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile/i.test(userAgent);
		var isSmallScreen = window.innerWidth <= 768;
		var isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
		
		return isMobileDevice || (isSmallScreen && isTouchDevice);
	}

	// Mobil cihazlar için minimal padding uygulama
	$(document).ready(function() {
		if (isMobile()) {
			console.log('Mobil cihaz tespit edildi - satis-faturasi sayfası için minimal düzenlemeler uygulanıyor');
			
			// Minimal padding değerleri - sadece footer altında kalmaması için
			$('body').css({
				'padding-bottom': '25px !important'
			});
			
			$('.main-wrapper').css({
				'padding-bottom': '25px !important'
			});
			
			$('.page-wrapper').css({
				'padding-bottom': '35px !important'
			});
			
			// Form container için minimal padding
			$('.content.container-fluid').css({
				'padding-bottom': '15px !important'
			});
			
			// Kaydet butonu container'ına minimal margin
			$('#submitBtn').closest('.col-md-12').css({
				'margin-bottom': '15px !important',
				'padding-bottom': '5px !important'
			});
			
			// Viewport değiştiğinde yeniden kontrol et
			$(window).on('resize orientationchange', function() {
				setTimeout(function() {
					if (isMobile()) {
						$('body').css('padding-bottom', '25px');
						$('.page-wrapper').css('padding-bottom', '35px');
					}
				}, 300);
			});
		}
	});

	// Feather icons güvenli başlatma
	$(document).ready(function() {
		try {
			if(typeof feather !== 'undefined' && feather && feather.replace) {
				feather.replace();
			}
		} catch(e) {
			console.warn('Feather icons could not be initialized:', e);
		}
	});

</script>



<!-- Görsel Önizleme Modalı -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="previewModalLabel">Görsel Önizleme</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body text-center">
				<img id="previewImage" src="" alt="Önizleme" class="img-fluid" style="max-height: 500px;">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
				<a id="downloadLink" href="#" target="_blank" class="btn btn-primary">
					<i class="fa fa-download"></i> İndir
				</a>
			</div>
		</div>
	</div>
</div>

</body>

</html>

