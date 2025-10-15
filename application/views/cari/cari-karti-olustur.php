<!DOCTYPE html>

<html lang="tr">

<head>

	<?php $this->load->view("include/head-tags"); ?>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA==" crossorigin="anonymous" referrerpolicy="no-referrer" />	<style>

		/* Select2 dropdown hover rengini mor yap */

		.select2-container--default .select2-results__option--highlighted[aria-selected] {

			background-color: #5c2d91 !important;

			color: #fff !important;

		}		.form-group {

    margin-bottom: 0.56rem !important; /* 0.75rem'in %25'i kadar daha azaltıldı */

}

		}

		#modalImage {

			border-radius: 8px;

			box-shadow: 0 4px 12px rgba(0,0,0,0.15);

		}

	</style>

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

		<div class="content container-fluid">			<!-- Page Header -->

			<div class="page-header">

				<div class="row">



					<div class="col-sm-10">

						<?php

						// Edit modu kontrolü için $cari varlığını kontrol et

						$isPageEditMode = isset($cari) && !empty($cari);

						?>

						<h3 class="page-title"><?= $isPageEditMode ? 'Müşteri Düzenle' : 'Müşteri Oluştur' ?></h3>

						<ul class="breadcrumb">

							<li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>

							<li class="breadcrumb-item">Müşteri</li>

							<li class="breadcrumb-item active"><?= $isPageEditMode ? 'Müşteri Düzenle' : 'Müşteri Oluştur' ?></li>

						</ul>

					</div>

					<div class="d-flex justify-content-end text-align-center col-sm-2">

						<a class="btn btn-outline-light" href="javascript:history.back()"><i class="fa fa-history"></i>

							<br>Önceki Sayfa</a>

					</div>

				</div>

			</div>

			<!-- /Page Header -->			<?php

			// Edit modu kontrolü

			$isEditMode = isset($cari) && !empty($cari);

			$pageTitle = $isEditMode ? "Cari Kartı Düzenle" : "Cari Kartı";

			$formAction = $isEditMode ? base_url("cari/mevcutCariKartiDuzenle") : base_url("cari/yeniCariKartiOlustur");

			

			// Edit modunda cari vergi numarası belirleme

			if($isEditMode) {

				if($cari->cari_vergiNumarasi != null)

					$cari_vergiNumarasi = $cari->cari_vergiNumarasi;

				else

					$cari_vergiNumarasi = $cari->cari_tckn;

				

				// Bireysel müşteri kontrolü

				$bireysel = $cari->cari_bireysel;

			}

			?>



			<div class="row">

				<div class="col-md-12">

					<!-- Mobile Form Error Alert -->
					<?php if($this->session->flashdata('mobile_form_error')): ?>
						<div class="alert alert-danger alert-dismissible">
							<button type="button" class="close" data-dismiss="alert">&times;</button>
							<strong>Mobil Cihaz Uyarısı!</strong> Form verileriniz düzgün gönderilmedi. Lütfen tüm zorunlu alanları doldurup tekrar deneyin. 
							Sorun devam ederse lütfen masaüstü bilgisayar kullanın.
						</div>
					<?php endif; ?>

					<!-- TC Kimlik/Vergi Numarası kontrolleri kaldırıldı - aynı numara ile kayıt izni verildi -->

					<div class="card">

						<div class="card-body">							<h4 class="card-title">Temel Bilgiler<span style="float:right;<?php if($isEditMode && $cari->cari_alias_pk){echo "display:block;";}else{echo "display:none;";} ?>" id="mukellef_mark">e-Fatura Mükellefidir <i

											class="fa fa-check-circle text-success"></i></span></h4>

							<form action="<?= $formAction ?>" method="POST" id="myForm" enctype="multipart/form-data">

								<!-- Debug: Form Action = <?= $formAction ?> -->

								<!-- Debug: Edit Mode = <?= $isEditMode ? 'true' : 'false' ?> -->

								<?php if($isEditMode): ?>

									<input type="hidden" value="<?= $cari->cari_id ?>" name="cari_id">

								<?php endif; ?>								<div class="row">

									<div class="form-group col-md-6">

										<label>Cari Tipi <span style="color: red;">*</span></label>

										<select class="select" name="cari_bireysel" required="" id="cariBireysel">

											<option value="">Seçiniz...</option>

											<option value="1" <?php if($isEditMode && $bireysel == 1){echo "selected";} ?>>Bireysel Müşteri</option>

											<option value="0" <?php if($isEditMode && $bireysel != 1){echo "selected";}elseif(!$isEditMode){echo "selected";} ?>>Kurumsal Müşteri</option>

											<option value="2" <?php if($isEditMode && $bireysel == 2){echo "selected";} ?>>Diğer</option>

										</select>



										<input type="hidden" name="cari_alias_pk" id="alias_pk" value="<?= $isEditMode ? $cari->cari_alias_pk : '' ?>">

									</div>
									
									<!-- Sorumlu Personel -->
									<div class="form-group col-md-6">
										<label>Sorumlu Personel</label>
										<select id="cari_olusturan" name="cari_olusturan" class="select2" style="width: 100%">
											<option value="">Personel Seçiniz...</option>
											<!-- Personel listesi AJAX ile yüklenecek -->
										</select>
									</div>

								</div><div class="row" style="display: <?= $isEditMode ? 'block' : 'none' ?>;" id="cari_input">

									<div class="row p-3">										<div class="col-md-6">

											<div class="form-group">

												<label>TC Kimlik Numarası / Vergi Numarası <span style="color: red;">*</span></label>

												<input type="number" class="form-control" required

													   name="cari_vergiNumarasi"

													   oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"

													   maxlength="11" id="vergiNumarasi" autocomplete="off" 

													   value="<?= $isEditMode ? $cari_vergiNumarasi : '' ?>"

													   <?php if($isEditMode && $bireysel == 2) echo "readonly";?>>

											</div>

											<div class="form-group">

												<label>Vergi Dairesi <span style="color: red;">*</span></label>

												<input type="text" class="form-control" name="cari_vergiDairesi"

													   id="vergiDairesi" autocomplete="off" required=""

													   value="<?= $isEditMode ? $cari->cari_vergiDairesi : '' ?>">

											</div>


										</div>



										<div class="col-md-6">
								
											<div class="form-group">

												<label>İşletme Adı <span style="color: red;">*</span></label>

												<input type="text" class="form-control" name="cari_ad" required

													   autocomplete="off" id="cariAd"

													   value="<?= $isEditMode ? $cari->cari_ad : '' ?>">

											</div>


												<label>Yetkili Adı Soyadı <span style="color: red;">*</span></label>

												<input type="text" class="form-control" name="cari_soyad" id="cariSoyad" 

													   value="<?= $isEditMode ? $cari->cari_soyad : '' ?>" autocomplete="off">

											</div>

										</div>

									</div>

								</div>								<h4 class="card-title mt-4">Müşteri İletişim Bilgileri</h4>

								<div class="row">

									<div class="col-md-6">

										<?php
										// Ülke dropdown
										if ($_ulkeler != false):
											echo '<div class="form-group">
												<label>Ülke <span style="color: red;">*</span></label>
												<select id="ulke" name="cari_ulke" data-plugin-selectTwo class="select ajaxUlkeler" required>
												<option value="">Seçiniz</option>';
											foreach ($_ulkeler as $item) {
												$selected = ($isEditMode && $cari->cari_ulke == $item->id) ? 'selected' : '';
												// Türkiye'yi varsayılan seç (id 216 genellikle Türkiye)
												if (!$isEditMode && $item->ulke_adi == 'Turkey') {
													$selected = 'selected';
												}
												echo '<option value="' . $item->id . '" ' . $selected . '>' . $item->ulke_adi . '</option>';
											}
											echo '</select></div>';
										else:
											echo 'Kayıtlı Ülke Bulunamadı..!';
										endif;
										?>

										<?php
										if ($_iller != false):
											echo '<div class="form-group">
												<label>İl <span style="color: red;">*</span></label>
												<select id="il" name="cari_il" data-plugin-selectTwo class="select ajaxIller" required disabled>
												<option value="">Önce ülke seçiniz</option>';
											foreach ($_iller as $item) {
												$selected = ($isEditMode && $cari->cari_il == $item->id) ? 'selected' : '';
												echo '<option value="' . $item->id . '" ' . $selected . '>' . $item->il . '</option>';
											}
											echo '</select></div>';
										else:
											echo '<div class="form-group">
												<label>İl <span style="color: red;">*</span></label>
												<select id="il" name="cari_il" data-plugin-selectTwo class="select ajaxIller" required disabled>
													<option value="">Önce ülke seçiniz</option>
												</select>
											</div>';
										endif;
										?>										<div class="form-group">

											<label>Instagram Hesabı</label>

											<input type="text" class="form-control" name="cari_websitesi"

												   autocomplete="off" value="<?= $isEditMode ? $cari->cari_websitesi : '' ?>">

										</div>



										<div class="form-group">

											<label>Mahalle</label>

											<input type="text" class="form-control" name="cari_mahalle"

												   autocomplete="off" placeholder="Mahalle bilgisi giriniz..."

												   value="<?= $isEditMode ? $cari->cari_mahalle : '' ?>">

										</div><div class="form-group">

											<label>Firma e-Posta <?php if($modulSorgula) echo '<span style="color: red;">*</span>';?></label>

											<input type="email" class="form-control" name="cari_firmaEPosta"

												   autocomplete="off" <?php if($modulSorgula) echo "required";?> id="cari_eposta"

												   value="<?= $isEditMode ? $cari->cari_firmaEPosta : '' ?>">

										</div>										<div class="form-group">

											<label>Firma Telefon <?php if($modulSorgula) echo '<span style="color: red;">*</span>';?></label>

											<input type="text" class="form-control" name="cari_firmaTelefon"

												   autocomplete="off"

												   oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"

												   maxlength="13"  <?php if($modulSorgula) echo "required";?>

												   value="<?= $isEditMode ? $cari->cari_firmaTelefon : '' ?>">

										</div>

										<!-- Fotoğraflar Yükleme Alanı -->

										<div class="form-group">

											<label>İşletme Görselleri

												<?php if(!$isEditMode): ?>

													<span style="color: red;">*</span>

												<?php endif; ?>

											</label>

				

											<?php if($isEditMode && !empty($cari->fotograf_dosya)): ?>

												<!-- Mevcut Görseller -->

												<div class="mb-3">

													<label class="d-block">Mevcut Görseller:</label>

													<div class="row">

														<?php 

														$fotograflar = explode(',', $cari->fotograf_dosya);

														foreach($fotograflar as $index => $fotograf): 

															if(trim($fotograf) != ''):

														?>

															<div class="col-md-3 mb-2" id="gorsel_<?= $index ?>">

																<div class="card">

																	<img src="<?= base_url('assets/uploads/' . trim($fotograf)) ?>" 

																		 class="card-img-top" style="height: 100px; object-fit: cover; cursor: pointer;"

																		 onclick="showImageModal('<?= base_url('assets/uploads/' . trim($fotograf)) ?>')">

																	<div class="card-body p-2">

																		<button type="button" class="btn btn-danger btn-sm w-100" 

																				onclick="deleteImage(<?= $cari->cari_id ?>, '<?= trim($fotograf) ?>', <?= $index ?>)">

																			<i class="fa fa-trash"></i> Sil

																		</button>

																	</div>

																</div>

															</div>

														<?php 

															endif;

														endforeach; 

														?>

													</div>

												</div>

											<?php endif; ?>

											

											<!-- Yeni Görsel Yükleme -->

											<label class="d-block">

												<?= $isEditMode ? 'Yeni Görsel Ekle (İsteğe bağlı):' : 'İşletmeye ait en az 3 fotoğraf yükleyiniz:' ?>

											</label>

											<input type="file" class="form-control" name="cari_fotograflar[]" multiple accept="image/*" 

												   <?= !$isEditMode ? 'required' : '' ?>>

											<small class="text-muted">JPG, PNG, GIF formatları kabul edilir. Maksimum 5MB.</small>

										</div>

										<!-- /Fotoğraflar Yükleme Alanı -->

									</div>									<div class="col-md-6">										<div class="ilceler">

											<div class="form-group">

												<label>İlçe <span style="color: red;">*</span></label>

												<select id="ilce" name="cari_ilce" class="select" required>

													<option value="">İl Seçiniz</option>

													<?php if($isEditMode && $cari->cari_ilce): ?>

														<option value="<?= $cari->cari_ilce ?>" selected><?= $cari->ilce_adi ?></option>

													<?php endif; ?>

												</select>

											</div>
												<div class="form-group">
													<label>Posta Kodu</label>
													<input type="text" class="form-control" name="cari_postaKodu" maxlength="10" placeholder="Posta kodu giriniz" value="<?= $isEditMode ? $cari->cari_postaKodu : '' ?>">
												</div>

										</div>

										<div class="form-group">

											<label>Adres</label>

											<textarea rows="9" cols="5" class="form-control"

													  placeholder="Adres bilgisi giriniz..."

													  name="cari_adres"><?= $isEditMode ? $cari->cari_adres : '' ?></textarea>

										</div>										<!-- Dosya Yükleme Alanı -->

										<div class="form-group">

											<label>Evraklar (Vergi levhası, imza sirküsü, kimlik fotokopisi)

												<?php if(!$isEditMode): ?>

													<span style="color: red;">*</span>

												<?php endif; ?>

											</label>

				

											<?php if($isEditMode && !empty($cari->evrak_dosya)): ?>

												<!-- Mevcut Evraklar -->

												<div class="mb-3">

													<label class="d-block">Mevcut Evraklar:</label>

													<div class="list-group">

														<?php 

														$evraklar = explode(',', $cari->evrak_dosya);

														foreach($evraklar as $index => $evrak): 

															if(trim($evrak) != ''):

																$dosya_adi = basename(trim($evrak));

														?>

															<div class="list-group-item d-flex justify-content-between align-items-center" id="evrak_<?= $index ?>">

																<div>

																	<i class="fa fa-file-o mr-2"></i>

																	<a href="<?= base_url('assets/uploads/' . trim($evrak)) ?>" target="_blank">

																		<?= $dosya_adi ?>

																	</a>

																</div>

																<button type="button" class="btn btn-danger btn-sm" 

																		onclick="deleteDocument(<?= $cari->cari_id ?>, '<?= trim($evrak) ?>', <?= $index ?>)">

																	<i class="fa fa-trash"></i> Sil

																</button>

															</div>

														<?php 

															endif;

														endforeach; 

														?>

													</div>

												</div>

											<?php endif; ?>

											

											<!-- Yeni Evrak Yükleme -->

											<label class="d-block">

												<?= $isEditMode ? 'Yeni Evrak Ekle (İsteğe bağlı):' : 'Vergi levhası, imza sirküsü, kimlik fotokopisi yükleyiniz:' ?>

											</label>

											<input type="file" class="form-control" name="cari_dosya[]" multiple accept="*/*" 

												   <?= !$isEditMode ? 'required' : '' ?>>

											<small class="text-muted">PDF, DOC, DOCX, JPG, PNG ve diğer dosya formatları kabul edilir. Maksimum 10MB.</small>

										</div>

										<!-- /Dosya Yükleme Alanı -->

									</div>

								</div>								<button type="submit" class="btn btn-danger" style="width:100%;" id="kydtBTN">

									<?= $isEditMode ? 'Güncelle' : 'Kaydet' ?>

								</button>

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



















<?php $this->load->view("include/footer-js"); ?>



<script>

	$(document).ready(function () {
		// Personel listesini yükle
		loadPersonelList();
		
		// Ülke değiştiğinde o ülkeye ait illeri yükle
		$('.ajaxUlkeler').on('change', function (e) {
			var base_url = "<?php echo base_url();?>";
			var ulke_id = $(this).val();
			
				if (ulke_id) {
					// Seçilen ülkeye göre illeri yükle
					$('#il').html('<option value="">İller yükleniyor...</option>').prop('disabled', true);
					$('#ilce').html('<option value="">Önce il seçiniz</option>');
					$('div.ilceler').html('<div class="form-group"><label>İlçe <span style="color: red;">*</span></label><select id="ilce" name="cari_ilce" class="form-control select" required><option value="">Önce il seçiniz</option></select></div><div class="form-group"><label>Posta Kodu</label><input type="text" class="form-control" name="cari_postaKodu" maxlength="10" placeholder="Posta kodu giriniz" value="<?= $isEditMode ? $cari->cari_postaKodu : '' ?>"></div>');				$.post(base_url + 'cari/getIllerByUlke', {ulke_id: ulke_id}, function (result) {
					if (result && result.status != 'error') {
						var iller = result.data;
						var select_options = '<option value="">İl Seçiniz</option>';
						
						for (var i = 0; i < iller.length; i++) {
							select_options += '<option value="' + iller[i].id + '">' + iller[i].il + '</option>';
						}
						
						$('#il').html(select_options).prop('disabled', false);
						
						// Edit modunda seçili ili tekrar seç
						<?php if($isEditMode && $cari->cari_il): ?>
							$('#il').val('<?= $cari->cari_il ?>');
						<?php endif; ?>
					} else {
						$('#il').html('<option value="">Bu ülkeye ait il bulunamadı</option>').prop('disabled', true);
						alert('Hata: ' + result.message);
					}
				}).fail(function() {
					$('#il').html('<option value="">İl yüklenemedi</option>').prop('disabled', true);
					alert('İller yüklenirken bir hata oluştu.');
				});
			} else {
				// Ülke seçilmemişse illeri temizle
				$('#il').html('<option value="">Önce ülke seçiniz</option>').prop('disabled', true);
				$('#ilce').html('<option value="">Önce ülke seçiniz</option>');
			}
		});
		
		$('.ajaxIller').on('change', function (e) {

			var base_url = "<?php echo base_url();?>";

			var il_id = $(this).val();

			$.post(base_url + 'cari/getIlceler', {il_id: il_id}, function (result) {				if (result && result.status != 'error') {

					var ilceler = result.data;

				var select = '<div class="form-group"><label>İlçe <span style="color: red;">*</span></label><select id="ilce" name="cari_ilce" class="form-control select" required>';

				select += '<option value="">İlçe Seçiniz</option>';

				for (var i = 0; i < ilceler.length; i++) {

					select += '<option value="' + ilceler[i].id + '">' + ilceler[i].ilce + '</option>';

				}

				select += '</select></div><div class="form-group"><label>Posta Kodu</label><input type="text" class="form-control" name="cari_postaKodu" maxlength="10" placeholder="Posta kodu giriniz" value="<?= $isEditMode ? $cari->cari_postaKodu : '' ?>"></div>';

				$('div.ilceler').empty().html(select);

				} else {

					alert('Hata : ' + result.message);

				}

			});

		});

	});

</script>



<script>

	$("#cariBireysel").on('change', function () {

		var status = $("#cariBireysel option:selected").text();



		$("#cari_input").css("display", "block");

		$("#c_soyad").css("display", "none");
		if (status == "Bireysel Müşteri") {



			$("#vergiDairesi").prop('required', false);

			$("#cariSoyad").prop('required', false);

			$("#vergiNumarasi").prop('readonly', false);



			$("#vergiDairesi").val("");

			$("#vergiNumarasi").val("");

			$("#alias_pk").val("");

			$("#cariAd").val("");



		} else if (status == "Kurumsal Müşteri") {



			$("#vergiDairesi").prop('required', false);



			$("#vergiNumarasi").prop('readonly', false);

			$("#vergiDairesi").val("");

			$("#vergiNumarasi").val("");

			$("#alias_pk").val("");

			$("#cariAd").val("");



		} else if (status == "Diğer") {



			$("#vergiDairesi").prop('required', false);



			$("#vergiNumarasi").prop('readonly', true);

			$("#vergiDairesi").val("");

			$("#vergiNumarasi").val("11111111111");

			$("#alias_pk").val("");

			$("#cariAd").val("");



		}

	});

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
	// Personel listesini yükle
	function loadPersonelList() {
		var base_url = "<?php echo base_url();?>";
		
		$.ajax({
			url: base_url + 'cari/getPersonelList',
			type: 'GET',
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					var options = '<option value="">Personel Seçiniz...</option>';
					$.each(response.data, function(index, personel) {
						var selected = '';
						<?php if(isset($cari) && $cari->cari_olusturan): ?>
							if (personel.kullanici_id == '<?= $cari->cari_olusturan ?>') {
								selected = 'selected';
							}
						<?php endif; ?>
						options += '<option value="' + personel.kullanici_id + '" ' + selected + '>' + personel.kullanici_ad + ' ' + personel.kullanici_soyad + '</option>';
					});
					$('#cari_olusturan').html(options);
					
					// Select2 initialize et
					$('#cari_olusturan').select2({
						placeholder: "Personel seçiniz...",
						allowClear: true,
						language: {
							noResults: function() {
								return "Sonuç bulunamadı";
							},
							searching: function() {
								return "Aranıyor...";
							}
						}
					});
				} else {
					console.error('Personel listesi yüklenemedi:', response.message);
				}
			},
			error: function(xhr, status, error) {
				console.error('AJAX Hatası:', error);
			}
		});
	}
	
	// Personel değiştiğinde cari_olusturan alanını güncelle
	$(document).on('change', '#cari_olusturan', function() {
		var selectedPersonel = $(this).val();
		var selectedText = $(this).find('option:selected').text();
		
		if (selectedPersonel) {
			// AJAX ile cari_olusturan alanını güncelle
			var cari_id = $('input[name="cari_id"]').val();
			if (cari_id) {
				updateCariOlusturan(cari_id, selectedPersonel, selectedText);
			}
		}
	});
	
	// Cari oluşturan alanını güncelle
	function updateCariOlusturan(cari_id, personel_id, personel_name) {
		var base_url = "<?php echo base_url();?>";
		
		$.ajax({
			url: base_url + 'cari/updateCariOlusturan',
			type: 'POST',
			data: {
				cari_id: cari_id,
				cari_olusturan: personel_id
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					toastr.success('Sorumlu personel güncellendi: ' + personel_name);
				} else {
					toastr.error('Güncelleme hatası: ' + response.message);
				}
			},
			error: function(xhr, status, error) {
				toastr.error('AJAX Hatası: ' + error);
			}
		});
	}
</script>

<script>

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

	/*$("#vergiNumarasi").on('blur', function () {

		let str = $("#vergiNumarasi").val();

		if (str.length == 11) {

			$("#c_soyad").css("display", "block");

			$("#cariSoyad").prop('required', true);

			$("#vergiDairesi").prop('required', false);

		} else {



			$("#vergiDairesi").prop('required', true);

			$("#c_soyad").css("display", "none");

			$("#cariSoyad").prop('required', false);

		}



		if (str.length == 10 || str.length == 11) {

			$.ajax({

				url: "<?= env('BASE_URL'); ?>/Edonusum/Auth/CheckUser",

				method: "POST",

				data: {vergiNumarasi: str},

				dataType: 'json',

				beforeSend: function () {

					$("#loading-image").show();

					$(':input[type="submit"]').prop('disabled', true);

				},

				success: function (result) {

					console.log(result);

					let alias = result.alias;

					//let identifier = result.identifier;

					let title = result.title;

					if (alias != null) {

						if(str.length == 11)

						{

							$("#c_soyad").css("display", "block");

							var titleSplit=title.trim().split(' ');

							$("#cariSoyad").val(titleSplit[titleSplit.length-1]);

							var ad="";

							for(var i=0;i<titleSplit.length-1;i++)

							{

								ad+=titleSplit[i]+ " ";

							}

							$("#cariAd").val(ad.trim());

							$('#cariSoyad').prop('disabled', true);

						}

						else

							$("#cariAd").val(title);

						$("#alias_pk").val(alias);

						$("#mukellef_mark").css("display", "block");

						$('#cariAd').prop('disabled', true);



					} else {

						$("#cariAd").val("");

						$("#alias_pk").val("");

						//$('#myForm').trigger("reset");

						$("#mukellef_mark").css("display", "none");

						$('#cariAd').prop('disabled', false);

					}

					$(':input[type="submit"]').prop('disabled', false);

					$("#loading-image").hide();

				}

			});

		}



	});*/



	// TC Kimlik/Vergi numarası uzunluğuna göre alan kontrolü (AJAX sorguları kaldırıldı)

	$("#vergiNumarasi").on('input', function () {

		let str = $("#vergiNumarasi").val();

		

		if (str.length == 11) {

			// TC Kimlik numarası - soyad alanını göster

			$("#c_soyad").css("display", "block");

			$("#cariSoyad").prop('required', true);

			$("#vergiDairesi").prop('required', false);

		} else {

			// Vergi numarası - soyad alanını gizle

			$("#vergiDairesi").prop('required', true);

			$("#c_soyad").css("display", "none");

			$("#cariSoyad").prop('required', false);

		}

	});



	/*$("#vergiNumarasi").on('blur', function () {



		let str = $("#vergiNumarasi").val();



		$("#cariSoyad").val("");

		$("#cariAd").val("")

		if (str.length == 11) {

			$("#c_soyad").css("display", "block");

			$("#cariSoyad").prop('required', true);

			$("#vergiDairesi").prop('required', false);

		} else {



			$("#vergiDairesi").prop('required', true);

			$("#c_soyad").css("display", "none");

			$("#cariSoyad").prop('required', false);

		}



		if (str.length == 10 || str.length == 11) {

			$.ajax({

				url: "<?= env('BASE_URL'); ?>/Cari/gibVknTcknSorgula",

				method: "POST",

				data: {vergiNumarasi: str},

				dataType: 'json',

				beforeSend: function () {

					$("#loading-image").show();

					$(':input[type="submit"]').prop('disabled', true);

				},

				success: function (result) {

;

					if (str.length == 11) {

						let ad = result.ad;

						let soyad = result.soyad;

						$("#c_soyad").css("display", "block");

						$("#cariAd").val(ad);

						$("#cariSoyad").val(soyad);

					} else {

						$("#c_soyad").css("display", "none");

						$("#cariAd").val(result.unvan);

					}



					$(':input[type="submit"]').prop('disabled', false);

					$("#loading-image").hide();

				}

			});



			<?php if($modulSorgula) { ?>

			$.ajax({

				url: "<?= env('BASE_URL'); ?>/Edonusum/Auth/CheckUser",

				method: "POST",

				data: {vergiNumarasi: str},

				dataType: 'json',

				beforeSend: function () {

					$("#loading-image").show();

					$(':input[type="submit"]').prop('disabled', true);

				},

				success: function (result) {

					let alias = result.alias;

					if (alias != null) {

						$("#alias_pk").val(alias);

						$("#mukellef_mark").css("display", "block");



					} else {

						$("#alias_pk").val("");

						$("#mukellef_mark").css("display", "none");

					}

					$(':input[type="submit"]').prop('disabled', false);

					$("#loading-image").hide();

				}

			});

			<?php } ?>







		}

	});*/





	$("#cari_eposta").on('blur', function () {

		var eposta= $("#cari_eposta").val();

		var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

		if(!regex.test(eposta)) {

			toastr.warning("Geçerli bir e-posta giriniz.");

			if(document.getElementById("cari_eposta").required == true)

				$("#kydtBTN").prop("disabled", true);

		}

		else {

			$("#kydtBTN").prop('disabled', false);

		}

	});

</script>



<script type="text/javascript">

	$(document).ready(function () {

		$("#ulke").select2({
			width: '100%'
		});

		$("#il").select2({

			width: '100%'

		});



		// Form submit blocking kodu kaldırıldı - güncelleme çalışması için

	});

</script>



<script>

$(document).ready(function () {

	// Submit butonunu başlangıçta etkinleştir

	$("#kydtBTN").prop('disabled', false);

	

	<?php if($isEditMode): ?>

		// Edit modunda - mevcut değerleri kullan

		$('#cari_input').show();

		// Ülke dropdown için trigger (o ülkeye ait illeri yükler)
		<?php if($cari->cari_ulke): ?>
			setTimeout(function() {
				$('#ulke').val('<?= $cari->cari_ulke ?>').trigger('change');
			}, 100);
		<?php endif; ?>

		<?php if($cari->cari_ilce): ?>

			// İl seçildikten sonra ilçeleri yükle

			setTimeout(function() {

				$('#il').trigger('change');

				setTimeout(function() {

					$('#ilce').val('<?= $cari->cari_ilce ?>');

				}, 1000);

			}, 500);

		<?php endif; ?>

	<?php else: ?>

	// Create modunda - varsayılan değerler

		$('#cariBireysel').val('0').trigger('change');

	$('#cari_input').show();

	<?php endif; ?>

});





</script>



<!-- Görsel Modal -->

<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">

	<div class="modal-dialog modal-lg" role="document">

		<div class="modal-content">

			<div class="modal-header">

				<h5 class="modal-title" id="imageModalLabel">Görsel Önizleme</h5>

				<button type="button" class="close" data-dismiss="modal" aria-label="Close">

					<span aria-hidden="true">&times;</span>

				</button>

			</div>

			<div class="modal-body text-center">

				<img id="modalImage" src="" class="img-fluid" style="max-width: 100%; height: auto;">

			</div>

		</div>

	</div>

</div>



<script>

// Görsel modal fonksiyonu

function showImageModal(imageSrc) {

	$('#modalImage').attr('src', imageSrc);

	$('#imageModal').modal('show');

}



// Görsel silme fonksiyonu

function deleteImage(cariId, dosyaAdi, index) {

	if(confirm('Bu görseli silmek istediğinizden emin misiniz?')) {

		$.ajax({

			url: '<?= base_url("cari/gorselSil") ?>',

			type: 'POST',

			data: {

				cari_id: cariId,

				dosya_adi: dosyaAdi

			},

			dataType: 'json',

			success: function(response) {

				if(response.success) {

					$('#gorsel_' + index).fadeOut(function() {

						$(this).remove();

					});

					toastr.success('Görsel başarıyla silindi.');

				} else {

					toastr.error('Hata: ' + response.message);

				}

			},

			error: function() {

				toastr.error('Bir hata oluştu. Lütfen tekrar deneyin.');

			}

		});

	}

}



// Evrak silme fonksiyonu

function deleteDocument(cariId, dosyaAdi, index) {

	if(confirm('Bu evrakı silmek istediğinizden emin misiniz?')) {

		$.ajax({

			url: '<?= base_url("cari/evrakSil") ?>',

			type: 'POST',

			data: {

				cari_id: cariId,

				dosya_adi: dosyaAdi

			},

			dataType: 'json',

			success: function(response) {

				if(response.success) {

					$('#evrak_' + index).fadeOut(function() {

						$(this).remove();

					});

					toastr.success('Evrak başarıyla silindi.');

				} else {

					toastr.error('Hata: ' + response.message);

				}

			},

			error: function() {

				toastr.error('Bir hata oluştu. Lütfen tekrar deneyin.');

			}

		});

	}

}

</script>

<!-- Form Debug ve Validation -->
<script>
$(document).ready(function() {
    // Form submit olayında tüm değerleri debug et
    $('#myForm').on('submit', function(e) {
        console.log('Form submit triggered - checking all values:');
        
        // İlçe değerini özellikle kontrol et
        var ilceValue = $('#ilce').val();
        var ilceName = $('#ilce option:selected').text();
        
        console.log('İlçe dropdown value:', ilceValue);
        console.log('İlçe dropdown text:', ilceName);
        console.log('İlçe element exists:', $('#ilce').length > 0);
        console.log('İlçe element disabled:', $('#ilce').prop('disabled'));
        
        // Eğer ilçe değeri boşsa uyarı ver
        if (!ilceValue || ilceValue === '') {
            console.warn('İlçe değeri boş! Form submit edilmeden önce ilçe seçimi yapılmalı.');
        }
        
        // Tüm form elemanlarını logla
        $('#myForm input, #myForm select, #myForm textarea').each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            if (name) {
                console.log('Form field:', name, '=', value);
            }
        });
    });
    
    // İlçe dropdown değişikliklerini takip et
    $(document).on('change', '#ilce', function() {
        var selectedValue = $(this).val();
        var selectedText = $(this).find('option:selected').text();
        console.log('İlçe seçimi değişti:', selectedValue, '-', selectedText);
    });
});
</script>

<!-- Mobile Form Fix for Customer Creation -->
<script>
// Detect if user is on mobile device
function isMobileDevice() {
    return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1) || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Mobile-specific form submission fix
if (isMobileDevice()) {
    console.log('Mobile device detected - applying mobile form fixes');
    
    $(document).ready(function() {
        // Remove submit button disabling on mobile to prevent stuck forms
        $(':input[type="submit"]').prop('disabled', false);
        
        // Disable select2 on mobile devices for better compatibility
        if (isMobileDevice()) {
            $('.select').removeClass('select').addClass('form-control mobile-select');
            
            // Destroy any existing select2 instances
            if ($.fn.select2) {
                $('.select2-hidden-accessible').select2('destroy');
            }
            
            // Ensure dropdowns work properly on mobile
            $('.mobile-select').each(function() {
                $(this).css({
                    'display': 'block',
                    'width': '100%',
                    'padding': '12px',
                    'font-size': '16px',
                    'border': '1px solid #ddd',
                    'border-radius': '4px',
                    'background-color': 'white'
                });
            });
        }
        
        // Add mobile-specific form submission handler
        $('#myForm').on('submit', function(e) {
            console.log('Mobile form submission started');
            
            // Check required fields manually for mobile
            var isValid = true;
            var missingFields = [];
            
            // Check required text inputs
            $('input[required], select[required]').each(function() {
                var value = $(this).val();
                if (!value || (typeof value === 'string' && value.trim() === '')) {
                    isValid = false;
                    var fieldName = $(this).attr('name') || $(this).attr('id') || 'unnamed field';
                    missingFields.push(fieldName);
                    $(this).css('border', '2px solid red');
                } else {
                    $(this).css('border', '');
                }
            });
            
            // Check file requirement for new customers only
            if (!$('input[name="cari_id"]').length) { // New customer
                var fileInput = $('input[name="cari_dosya[]"]');
                if (fileInput.length && fileInput.prop('required') && (!fileInput[0].files || fileInput[0].files.length === 0)) {
                    isValid = false;
                    missingFields.push('evrak dosyası');
                    fileInput.css('border', '2px solid red');
                } else if (fileInput.length) {
                    fileInput.css('border', '');
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Lütfen şu zorunlu alanları doldurun: ' + missingFields.join(', '));
                return false;
            }
            
            // Add loading indicator
            var submitBtn = $('#kydtBTN');
            submitBtn.prop('disabled', true);
            submitBtn.text('Kaydediliyor...');
            
            // Log form data for debugging
            try {
                var formData = new FormData(this);
                console.log('Mobile form data being submitted:');
                for (var pair of formData.entries()) {
                    console.log(pair[0] + ': ' + (typeof pair[1] === 'object' ? 'File: ' + pair[1].name : pair[1]));
                }
            } catch(e) {
                console.log('Could not log form data:', e);
            }
            
            return true;
        });
        
        // Override problematic AJAX validations on mobile
        if (isMobileDevice()) {
            // Mobile-specific district loading
            $('.ajaxIller, #il').off('change.ajax').on('change', function() {
                var selectedIl = $(this).val();
                console.log('Mobile: İl seçildi:', selectedIl);
                
                if (selectedIl) {
                    // Show loading indicator
                    $('#ilce').html('<option>İlçeler yükleniyor...</option>').prop('disabled', true);
                    
                    // Mobile-friendly AJAX request with longer timeout
                    $.ajax({
                        url: '<?= base_url("cari/getIlceler") ?>',
                        type: 'POST',
                        data: { il_id: selectedIl },
                        timeout: 15000, // 15 second timeout for mobile
                        dataType: 'json',
                        success: function(result) {
                            console.log('Mobile: İlçe verisi alındı:', result);
                            if (result && result.status !== 'error' && result.data) {
                                var options = '<option value="">İlçe Seçiniz</option>';
                                for (var i = 0; i < result.data.length; i++) {
                                    options += '<option value="' + result.data[i].id + '">' + result.data[i].ilce + '</option>';
                                }
                                $('#ilce').html(options).prop('disabled', false);
                                
                                // Force refresh for mobile browsers
                                setTimeout(function() {
                                    $('#ilce').trigger('change');
                                }, 100);
                                
                                // Re-enable form submission
                                $(':input[type="submit"]').prop('disabled', false);
                            } else {
                                $('#ilce').html('<option value="">İlçe bulunamadı</option>').prop('disabled', false);
                                console.error('Mobile: İlçe verisi alınamadı:', result);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Mobile: İlçe AJAX hatası:', status, error);
                            $('#ilce').html('<option value="">İlçe yüklenemedi - tekrar deneyin</option>').prop('disabled', false);
                            
                            // Fallback: Still allow form submission
                            $(':input[type="submit"]').prop('disabled', false);
                        }
                    });
                } else {
                    $('#ilce').html('<option value="">Önce il seçiniz</option>').prop('disabled', false);
                }
            });
        } else {
            // Keep original desktop functionality
            $('.ajaxIller').off('change.ajax').on('change', function() {
                var selectedIl = $(this).val();
                if (selectedIl) {
                    $.ajax({
                        url: '<?= base_url("home/get_ilceler") ?>',
                        type: 'POST',
                        data: { il_id: selectedIl },
                        success: function(result) {
                            if (result && result.status !== 'error' && result.data) {
                                var options = '<option value="">İlçe Seçiniz</option>';
                                for (var i = 0; i < result.data.length; i++) {
                                    options += '<option value="' + result.data[i].id + '">' + result.data[i].ilce + '</option>';
                                }
                                $('#ilce').html(options);
                            }
                        },
                        error: function() {
                            console.log('District loading failed');
                        }
                    });
                }
                $(':input[type="submit"]').prop('disabled', false);
            });
        }
        
        // Disable problematic client-side validations on mobile
        $('#cariBireysel').off('change.validation').on('change', function() {
            $(':input[type="submit"]').prop('disabled', false);
            // Allow form submission even if validation hasn't completed
        });
        
        // Force enable submit button after any form interaction
        $('#myForm input, #myForm select, #myForm textarea').on('change keyup', function() {
            setTimeout(function() {
                $(':input[type="submit"]').prop('disabled', false);
            }, 100);
        });
    });
    
    // Add mobile-specific CSS
    var mobileCSS = `
        @media (max-width: 768px) {
            .form-control {
                font-size: 16px !important; /* Prevents zoom on iOS */
                -webkit-appearance: none;
                border-radius: 4px;
            }
            
            input[type="file"] {
                padding: 10px;
                border: 1px solid #ccc;
                font-size: 16px;
            }
            
            #kydtBTN {
                padding: 15px;
                font-size: 18px;
                width: 100% !important;
                margin-top: 20px;
                -webkit-appearance: none;
                border-radius: 4px;
            }
            
            /* Disable select2 on mobile for better compatibility */
            .select2-container {
                display: none !important;
            }
            
            .select {
                display: block !important;
                width: 100% !important;
                padding: 12px !important;
                font-size: 16px !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
                background-color: white !important;
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                appearance: none !important;
            }
            
            /* Special styling for district dropdown */
            #ilce {
                min-height: 45px !important;
                background-image: url('data:image/svg+xml;utf8,<svg fill="black" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>') !important;
                background-repeat: no-repeat !important;
                background-position: right 10px center !important;
                background-size: 20px !important;
                padding-right: 40px !important;
            }
            
            /* Special styling for country dropdown */
            #ulke {
                min-height: 45px !important;
                background-image: url('data:image/svg+xml;utf8,<svg fill="black" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>') !important;
                background-repeat: no-repeat !important;
                background-position: right 10px center !important;
                background-size: 20px !important;
                padding-right: 40px !important;
            }
            
            /* Ensure proper mobile form layout */
            .form-group {
                margin-bottom: 20px !important;
            }
            
            .form-group label {
                display: block !important;
                margin-bottom: 8px !important;
                font-weight: bold !important;
            }
        }
    `;
    
    var style = document.createElement('style');
    style.innerHTML = mobileCSS;
    document.head.appendChild(style);
    
    // Add mobile debug panel if needed (can be enabled for testing)
    if (window.location.search.includes('debug=1')) {
        var debugPanel = document.createElement('div');
        debugPanel.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; background: black; color: white; padding: 10px; z-index: 9999; font-size: 12px; max-height: 200px; overflow-y: scroll;';
        debugPanel.innerHTML = '<strong>Mobil Debug Panel (debug=1 parametresi ile aktif)</strong><br>';
        document.body.appendChild(debugPanel);
        
        // Override console.log for mobile debugging
        var originalLog = console.log;
        console.log = function() {
            originalLog.apply(console, arguments);
            debugPanel.innerHTML += Array.from(arguments).join(' ') + '<br>';
            debugPanel.scrollTop = debugPanel.scrollHeight;
        };
        
        console.log('Mobil debug panel aktif');
        console.log('User Agent: ' + navigator.userAgent);
        console.log('Screen Size: ' + screen.width + 'x' + screen.height);
        console.log('Window Size: ' + window.innerWidth + 'x' + window.innerHeight);
    }
}
</script>

</body>

</html>

