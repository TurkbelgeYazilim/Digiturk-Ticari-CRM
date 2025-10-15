<!DOCTYPE html>

<html lang="tr">

<head>

	<?php $this->load->view("include/head-tags"); ?>

</head>

<body>



<div class="main-wrapper">



	<?php $this->load->view("include/header"); ?>



	<?php $this->load->view("include/sidebar"); ?>



	<?php
	// Debug: Talep verilerini kontrol et
	if(!isset($talep) || !$talep) {
		echo "<div class='alert alert-danger'>HATA: Destek talebi bulunamadı! (ID: " . ($this->uri->segment(3) ? $this->uri->segment(3) : 'bilinmiyor') . ")</div>";
		echo "<a href='" . base_url("destek") . "' class='btn btn-primary'>Destek Listesine Dön</a>";
		echo "</div></div></div>";
		return;
	}
	
	// Debug: Yanıt sayısını göster
	$yanit_sayisi = isset($yanitlar) && is_array($yanitlar) ? count($yanitlar) : 0;
	echo "<!-- DEBUG: Toplam yanıt sayısı: $yanit_sayisi -->";

	$departman = $talep->destek_department;

	if($departman == 1){

		$departman_text = "Teknik Destek";

	}else if($departman == 2){

		$departman_text = "Muhasebe";

	}else if($departman == 3){

		$departman_text = "Satış";

	}else if($departman == 4){

		$departman_text = "Şikayet / İstek / Öneri";

	}else{$departman_text="Hata...";}



	$durum = $talep->destek_status;

	if($durum == 1){

		$durum_text = "Açık";

	}else if($durum == 2){

		$durum_text = "İşlemde";

	}else if($durum == 3){

		$durum_text = "Kapalı";

	}else if($durum == 4){

		$durum_text = "Cevap bekleniyor";

	}else{$durum_text = "Hata...";}



	$olusturanID = $talep->destek_olusturan;

	$firmaID = $talep->destek_olusturanAnaHesap;

	$firmaKim = getirFirmaById($firmaID);

	$kullaniciKim = getirKullaniciById($olusturanID);
	
	// Null kontrolleri
	if(!$firmaKim) {
		$firmaKim = (object)['ayarlar_firmaAdi' => 'Bilinmeyen Firma'];
	}
	if(!$kullaniciKim) {
		$kullaniciKim = (object)['kullanici_eposta' => 'bilinmeyen@email.com'];
	}

	?>



	<div class="page-wrapper">

		<div class="content container-fluid">

			<div class="row">

				<div class="col-sm-12">

					<div class="chat-window">

						<div class="chat-cont-right">

							<div class="chat-header">

								<div class="media d-flex">

									<div class="media-body">

										<div class="user-name"><b>Konu:</b> <?= $talep->destek_title; ?></div>

										<div class="user-status">

											<?=$departman_text;?> - 

											<?php if(isset($isAdmin) && $isAdmin): ?>

												<span class="badge badge-<?= 

													$durum == 1 ? 'success' : 

													($durum == 2 ? 'warning' : 

													($durum == 3 ? 'secondary' : 'info')); 

												?>"><?=$durum_text;?></span>

											<?php else: ?>

												<?=$durum_text;?>

											<?php endif; ?>

										</div>

									</div>

								</div>

								<div class="chat-options">

									<?php if(isset($isAdmin) && $isAdmin): ?>

									<div class="dropdown mr-2">

										<button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="durumDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

											Durum Değiştir

										</button>

										<div class="dropdown-menu" aria-labelledby="durumDropdown">

											<a class="dropdown-item" href="<?= base_url("home/destek_durum_degistir/".$talep->destek_id."/1"); ?>">Açık</a>

											<a class="dropdown-item" href="<?= base_url("home/destek_durum_degistir/".$talep->destek_id."/2"); ?>">İşlemde</a>

											<a class="dropdown-item" href="<?= base_url("home/destek_durum_degistir/".$talep->destek_id."/3"); ?>">Kapalı</a>

											<a class="dropdown-item" href="<?= base_url("home/destek_durum_degistir/".$talep->destek_id."/4"); ?>">Cevap bekleniyor</a>

										</div>

									</div>

									<?php endif; ?>

									<a href="<?= base_url("destek");?>">

										<i data-feather="arrow-left" class="mr-1"></i>

									</a>

								</div>

							</div>

							<div class="chat-body">

								<div class="chat-scroll">

									<ul class="list-unstyled">



										<?php 

										// Yanıtları tarih ve kullanıcıya göre grupla

										$grouped_yanitlar = array();

										if(isset($yanitlar) && is_array($yanitlar) && count($yanitlar) > 0) {
											foreach($yanitlar as $yanit){

											$key = $yanit->dyanit_yanitlayan . '_' . date('Y-m-d H:i', strtotime($yanit->dyanit_tarih));

											if(!isset($grouped_yanitlar[$key])){

												$grouped_yanitlar[$key] = array(

													'yanit' => $yanit,

													'files' => array()

												);

											}

											if($yanit->dyanit_resim != NULL){

												$grouped_yanitlar[$key]['files'][] = $yanit->dyanit_resim;

											}

										}
										}
										
										if(count($grouped_yanitlar) > 0) {

										foreach($grouped_yanitlar as $group){ 

											$yanit = $group['yanit'];

											$files = $group['files'];

										?>

												<?php if($yanit->dyanit_isAdmin == 0){ ?>

												<li class="media sent">

													<div class="media-body">

														<div class="msg-box">

															<div>

																<?php if(!empty($files)): ?>

																	<div class="mb-2">

																		<?php foreach($files as $file): ?>

																			<a href="<?= base_url("assets/bulut/tickets/$file"); ?>" target="_blank" class="badge badge-info mr-1" style="color:#FFF!important;">

																				<i class="fas fa-paperclip"></i> <?= substr($file, 0, 15); ?>...

																			</a>

																		<?php endforeach; ?>

																	</div>

																<?php endif; ?>

																<?php if(!empty($yanit->dyanit_mesaj)): ?>

																	<p><?= nl2br(htmlspecialchars($yanit->dyanit_mesaj)); ?></p>

																<?php endif; ?>

																<ul class="chat-msg-info">

																	<li>

																		<div class="chat-time">

																			<span><?php echo $kullaniciKim->kullanici_eposta; ?></span><br>

																			<span><?= date('d.m.Y H:i', strtotime($yanit->dyanit_tarih)); ?></span>

																		</div>

																	</li>

																</ul>

															</div>

														</div>

													</div>

												</li>

												<?php }else{?>

												<li class="media d-flex received">

													<div class="media-body">

														<div class="msg-box">

															<div>

																<?php if(!empty($files)): ?>

																	<div class="mb-2">

																		<?php foreach($files as $file): ?>

																			<a href="<?= base_url("assets/bulut/tickets/$file"); ?>" target="_blank" class="badge badge-secondary mr-1" style="color:#FFF!important;">

																				<i class="fas fa-paperclip"></i> <?= substr($file, 0, 15); ?>...

																			</a>

																		<?php endforeach; ?>

																	</div>

																<?php endif; ?>

																<?php if(!empty($yanit->dyanit_mesaj)): ?>

																	<p><?= nl2br(htmlspecialchars($yanit->dyanit_mesaj)); ?></p>

																<?php endif; ?>

																<ul class="chat-msg-info">

																	<li>

																		<div class="chat-time">

																			<span>destek@ilekasoft.com</span>

																			<br>

																			<span><?= date('d.m.Y H:i', strtotime($yanit->dyanit_tarih)); ?></span>

																		</div>

																	</li>

																</ul>

															</div>

														</div>

													</div>

												</li>

												<?php }?>

										<?php }?>
										<?php } else { ?>
											<li class="text-center text-muted py-4">
												<em>Henüz yanıt bulunmuyor.</em>
											</li>
										<?php } ?>

									</ul>

								</div>

							</div>

							<form action="<?= base_url("home/dyanit_gonder"); ?>" method="post">

								<input type="hidden" name="dyanit_destekID" value="<?= $this->uri->segment(3);?>">

							<div class="chat-footer">

								<div class="input-group">

									<!-- <div class="input-group-prepend">

										<div class="btn-file btn">

											<i class="fas fa-paperclip"></i>

											<input type="file">

										</div>

									</div> -->

									<textarea class="input-msg-send form-control" name="dyanit_mesaj"></textarea>

									<div class="input-group-append">

										<button type="submit" class="btn msg-send-btn"><i class="fas fa-paper-plane"></i></button>

									</div>

								</div>

							</div>

							</form>

						</div>

					</div>

				</div>

			</div>



		</div>

	</div>



</div>



<?php if ($this->session->flashdata('destekyanit_ok')): ?>

	<script>

		swal({

			title: "Bilgilendirme",

			type: "success",

			text: "Yanıtınız gönderildi.",

			confirmButtonText:'Tamam',

			button: false,

			timer: 5000,

		});

	</script>

<?php endif; ?>



<?php if ($this->session->flashdata('destek_durum_ok')): ?>

	<script>

		swal({

			title: "Bilgilendirme",

			type: "success",

			text: "<?= $this->session->flashdata('destek_durum_ok'); ?>",

			confirmButtonText:'Tamam',

			button: false,

			timer: 5000,

		});

	</script>

<?php endif; ?>



<?php if ($this->session->flashdata('destek_durum_hata')): ?>

	<script>

		swal({

			title: "Hata",

			type: "error",

			text: "<?= $this->session->flashdata('destek_durum_hata'); ?>",

			confirmButtonText:'Tamam',

			button: false,

			timer: 5000,

		});

	</script>

<?php endif; ?>



<?php $this->load->view("include/footer-js"); ?>

</body>

</html>

