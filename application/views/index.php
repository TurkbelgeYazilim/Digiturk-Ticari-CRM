<!DOCTYPE html>

<html lang="tr" xmlns="http://www.w3.org/1999/html">

<head>

	<?php $this->load->view("include/head-tags"); ?>

	<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/c3-chart/c3.min.css">

	<script src="https://www.amcharts.com/lib/4/core.js"></script>

	<script src="https://www.amcharts.com/lib/4/charts.js"></script>

	<script src="https://www.amcharts.com/lib/4/themes/animated.js"></script>

	<!-- Enhanced Dashboard Widget Styles -->

	<style>

		/* Temel layout düzeltmeleri */

		html, body {

			overflow-x: hidden !important;

			position: relative !important;

		}

		

		.main-wrapper {

			overflow-x: hidden !important;

			position: relative !important;

		}

		

		/* Ana widget container düzenleri */

		.widget-container {

			display: block !important;

			width: 100% !important;

		}

		

		.widget-container .row {

			display: flex !important;

			flex-wrap: wrap !important;

			margin: 0 -10px !important;

		}

		

		.widget-container .col-md-3,

		.widget-container .col-lg-3,

		.widget-container .col-sm-6 {

			flex: 0 0 25% !important;

			max-width: 25% !important;

			padding: 0 10px !important;

			margin-bottom: 20px !important;

			box-sizing: border-box !important;

		}

		

		/* Ana widget stilleri */

		.widget-container .widget {

			width: 100% !important;

			height: 140px !important;

			display: flex !important;

			align-items: center !important;

			padding: 20px !important;

			border-radius: 12px !important;

			box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;

			transition: all 0.3s ease !important;

			background: white !important;

			border: 1px solid #e9ecef !important;

			position: relative !important;

			overflow: hidden !important;

		}

		

		.widget-container .widget:hover {

			transform: translateY(-5px) !important;

			box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;

		}

		

		.widget-container .widget .stats-icon,

		.widget-container .widget .stats-icon-lg {

			flex-shrink: 0 !important;

			margin-right: 15px !important;

			font-size: 2.5rem !important;

			width: 65px !important;

			text-align: center !important;

			z-index: 2 !important;

			position: relative !important;

			opacity: 0.8 !important;

		}

		

		.widget-container .widget .stats-content {

			flex: 1 !important;

			z-index: 2 !important;

			position: relative !important;

		}

		

		.widget-container .widget .stats-title {

			font-size: 0.9rem !important;

			font-weight: 600 !important;

			margin-bottom: 8px !important;

			color: #495057 !important;

			line-height: 1.3 !important;

		}

		

		.widget-container .widget .stats-number {

			font-size: 1.8rem !important;

			font-weight: 700 !important;

			margin-bottom: 4px !important;

			color: #212529 !important;

			line-height: 1.1 !important;

		}

		

		.widget-container .widget .stats-desc {

			font-size: 0.75rem !important;

			opacity: 0.7 !important;

			color: #6c757d !important;

			margin-top: 4px !important;

		}

		

		/* Modern gradient backgrounds */

		.widget-container .widget.bg-light-warning {

			background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%) !important;

			border-color: #fcb69f !important;

		}

		

		.widget-container .widget.bg-light-primary {

			background: linear-gradient(135deg, #a8edea 0%, #74b9ff 100%) !important;

			border-color: #74b9ff !important;

		}

		

		.widget-container .widget.bg-light-danger {

			background: linear-gradient(135deg, #ffecd2 0%, #ff7675 100%) !important;

			border-color: #ff7675 !important;

		}

		

		.widget-container .widget.bg-light-success {

			background: linear-gradient(135deg, #d299c2 0%, #00b894 100%) !important;

			border-color: #00b894 !important;

		}

		

		/* Progress bar düzenlemeleri */

		.widget-container .widget .stats-progress {

			height: 6px !important;

			margin: 8px 0 4px 0 !important;

			background-color: rgba(255,255,255,0.4) !important;

			border-radius: 3px !important;

			overflow: hidden !important;

		}

		

		.widget-container .widget .progress-bar {

			background-color: rgba(255,255,255,0.9) !important;

			border-radius: 3px !important;

			transition: width 0.6s ease !important;

		}

		

		/* İkinci satır widget'ları - Ana widget'larla aynı stil */

		.mobile-widgets-first .widget {

			width: 100% !important;

			height: 140px !important;

			display: flex !important;

			align-items: center !important;

			padding: 20px !important;

			border-radius: 12px !important;

			box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;

			transition: all 0.3s ease !important;

			background: white !important;

			border: 1px solid #e9ecef !important;

			position: relative !important;

			overflow: hidden !important;

		}

		

		.mobile-widgets-first .widget:hover {

			transform: translateY(-5px) !important;

			box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;

		}

		

		.mobile-widgets-first .widget .stats-icon,

		.mobile-widgets-first .widget .stats-icon-lg {

			flex-shrink: 0 !important;

			margin-right: 15px !important;

			font-size: 2.5rem !important;

			width: 65px !important;

			text-align: center !important;

			z-index: 2 !important;

			position: relative !important;

			opacity: 0.8 !important;

		}

		

		.mobile-widgets-first .widget .stats-content {

			flex: 1 !important;

			z-index: 2 !important;

			position: relative !important;

		}

		

		.mobile-widgets-first .widget .stats-title {

			font-size: 0.9rem !important;

			font-weight: 600 !important;

			margin-bottom: 8px !important;

			color: #495057 !important;

			line-height: 1.3 !important;

		}

		

		.mobile-widgets-first .widget .stats-number {

			font-size: 1.8rem !important;

			font-weight: 700 !important;

			margin-bottom: 4px !important;

			color: #212529 !important;

			line-height: 1.1 !important;

		}

		

		.mobile-widgets-first .widget .stats-desc {

			font-size: 0.75rem !important;

			opacity: 0.7 !important;

			color: #6c757d !important;

			margin-top: 4px !important;

		}

		

		/* İkinci satır widget'ları için gradient backgrounds */

		.mobile-widgets-first .widget.bg-light-warning {

			background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%) !important;

			border-color: #fcb69f !important;

		}

		

		.mobile-widgets-first .widget.bg-light-primary {

			background: linear-gradient(135deg, #a8edea 0%, #74b9ff 100%) !important;

			border-color: #74b9ff !important;

		}

		

		.mobile-widgets-first .widget.bg-light-danger {

			background: linear-gradient(135deg, #ffecd2 0%, #ff7675 100%) !important;

			border-color: #ff7675 !important;

		}

		

		.mobile-widgets-first .widget.bg-light-success {

			background: linear-gradient(135deg, #d299c2 0%, #00b894 100%) !important;

			border-color: #00b894 !important;

		}

		

		/* İkinci satır widget'ları için progress bar */

		.mobile-widgets-first .widget .stats-progress {

			height: 6px !important;

			margin: 8px 0 4px 0 !important;

			background-color: rgba(255,255,255,0.4) !important;

			border-radius: 3px !important;

			overflow: hidden !important;

		}

		

		.mobile-widgets-first .widget .progress-bar {

			background-color: rgba(255,255,255,0.9) !important;

			border-radius: 3px !important;

			transition: width 0.6s ease !important;

		}

		

		/* Son Satışlarım card düzenlemeleri */

		.mobile-sales-last .card {

			border: none !important;

			box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;

			border-radius: 12px !important;

			position: relative !important;

			z-index: 5 !important;

		}

		

		.mobile-sales-last .card-header {

			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;

			color: white !important;

			border-radius: 12px 12px 0 0 !important;

			border: none !important;

			position: relative !important;

			z-index: 6 !important;

		}

		

		.mobile-sales-last .card-body {

			padding: 20px !important;

			position: relative !important;

			z-index: 6 !important;

		}

		

		/* Responsive tasarım */

		@media (max-width: 1200px) {

			.widget-container .col-md-3,

			.widget-container .col-lg-3,

			.widget-container .col-sm-6 {

				flex: 0 0 50% !important;

				max-width: 50% !important;

			}

		}

		

		@media (max-width: 768px) {

			.widget-container .col-md-3,

			.widget-container .col-lg-3,

			.widget-container .col-sm-6 {

				flex: 0 0 100% !important;

				max-width: 100% !important;

			}

			

			.widget-container .widget,

			.mobile-widgets-first .widget {

				height: auto !important;

				min-height: 110px !important;

				padding: 15px !important;

				position: relative !important;

				z-index: 5 !important;

			}

			

			.widget-container .widget .stats-icon,

			.widget-container .widget .stats-icon-lg,

			.mobile-widgets-first .widget .stats-icon,

			.mobile-widgets-first .widget .stats-icon-lg {

				font-size: 2rem !important;

				width: 50px !important;

				margin-right: 12px !important;

			}

			

			.widget-container .widget .stats-number,

			.mobile-widgets-first .widget .stats-number {

				font-size: 1.4rem !important;

			}

			

			.widget-container .widget .stats-title,

			.mobile-widgets-first .widget .stats-title {

				font-size: 0.8rem !important;

			}

			

			/* Mobil düzen yeniden sıralama */

			.mobile-reorder {

				flex-direction: column-reverse !important;

				position: relative !important;

				z-index: 3 !important;

			}

			

			.mobile-widgets-first {

				order: 1 !important;

				margin-bottom: 20px !important;

				position: relative !important;

				z-index: 4 !important;

			}

			

			.mobile-sales-last {

				order: 2 !important;

				position: relative !important;

				z-index: 4 !important;

			}

			

			.mobile-widgets-first .col-md-6 {

				flex: 0 0 50% !important;

				max-width: 50% !important;

			}

			

			/* Mobil header/footer spacing */

			.main-wrapper .page-wrapper .content {

				padding-top: 20px !important;

				padding-bottom: 20px !important;

			}

		}

		

		@media (max-width: 576px) {

			.mobile-widgets-first .col-md-6 {

				flex: 0 0 100% !important;

				max-width: 100% !important;

			}

			

			.widget-container .row {

				margin: 0 -5px !important;

			}

			

			.widget-container .col-md-3,

			.widget-container .col-lg-3,

			.widget-container .col-sm-6,

			.mobile-widgets-first .col-md-6 {

				padding: 0 5px !important;

			}

		}

		

		/* Basit link stilleri */

		.widget-container a,

		.mobile-widgets-first a {

			text-decoration: none !important;

			color: inherit !important;

		}

		

		.widget-container a:hover,

		.mobile-widgets-first a:hover {

			text-decoration: none !important;

			color: inherit !important;

		}

	</style>



</head>

<body>



<?php $ses = session("r", "auth");

echo $ses; ?>





<!-- Main Wrapper -->

<div class="main-wrapper">



	<!-- Header -->

	<?php $this->load->view("include/header"); ?>

	<!-- /Header -->



	<!-- Sidebar -->

	<?php $this->load->view("include/sidebar"); ?>

	<!-- /Sidebar -->

	<?php

	if (!empty(alacakHesapla2(30))) {

		$alacakyuzde = (alacakHesapla(30) / alacakHesapla2(30) - 1) * 100;

	}



	if (!empty(musteriHesapla2(30))) {

		$musteriyuzde = (musteriHesapla(30) / musteriHesapla2(30) - 1) * 100;

	}



	if (!empty(faturaHesapla2(30))) {

		$faturayuzde = (faturaHesapla(30) / faturaHesapla2(30) - 1) * 100;

	}

	if (!empty(stokHesapla2(30))) {

		$stokyuzde = (stokHesapla(30) / stokHesapla2(30) - 1) * 100;

	}

	?>



	<!-- Page Wrapper -->

	<div class="page-wrapper">

		<div class="content container-fluid " style="padding-top:10px;">

			<div class="row">

				<div class="col-md-12">

					<div class="card">						<div class="card-body">
							<?php
							// Login olan kullanıcıyı al
							$login_info = session("r", "login_info");
							$user_id = $login_info && isset($login_info->kullanici_id) ? $login_info->kullanici_id : 0;

							// Kendisine bağlı olan kullanıcıları bul
							$allowed_user_ids = array($user_id);
							if ($user_id > 0) {
								$this->db->reset_query();
								$this->db->select('kullanici_id');
								$this->db->from('kullanicilar');
								$this->db->where('kullanici_sorumluMudur', $user_id);
								$bağlı_kullanicilar = $this->db->get()->result();
								
								foreach ($bağlı_kullanicilar as $user) {
									$allowed_user_ids[] = $user->kullanici_id;
								}
							}
							?>
							
							<div class="row mt-4">
								<!-- Sol taraf: Son Satışlarım -->
								<div class="col-md-6">
									<div class="card pb-3" style="height: 100%;">
										<div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; color: white !important; border-radius: 12px 12px 0 0 !important; border: none !important;">
											<h5 class="card-title" style="margin: 0;">Son Satışlarım</h5>
										</div>
										<div class="card-body" style="min-height:400px; max-height: 500px; overflow-y: auto;">
											<?php
											$this->db->reset_query();
											$this->db->select('sf.*, c.cari_ad, c.cari_soyad, k.kullanici_ad, k.kullanici_soyad');
											$this->db->from('satisFaturasi sf');
											$this->db->join('cari c', 'sf.satis_CariID = c.cari_id', 'left');
											$this->db->join('kullanicilar k', 'sf.satis_olusturan = k.kullanici_id', 'left');
											$this->db->where_in('sf.satis_olusturan', $allowed_user_ids);
											$this->db->order_by('sf.satis_olusturmaTarihi', 'DESC');
											$this->db->limit(10);
											$satislar = $this->db->get()->result();
											
											if(empty($satislar)) {
												echo '<div class="text-center text-muted py-4">Henüz satış kaydı bulunmuyor.</div>';
											} else {
												foreach ($satislar as $satis) {
													// Cari adını oluştur
													$cari_ad = '';
													if (!empty($satis->cari_ad)) {
														$cari_ad = $satis->cari_ad;
														if (!empty($satis->cari_soyad)) {
															$cari_ad .= ' ' . $satis->cari_soyad;
														}
													} else {
														$cari_ad = 'Müşteri Bulunamadı';
													}
													
													// Satışı yapan kullanıcı adı
													$yapan_kullanici = '';
													if (!empty($satis->kullanici_ad)) {
														$yapan_kullanici = $satis->kullanici_ad;
														if (!empty($satis->kullanici_soyad)) {
															$yapan_kullanici .= ' ' . $satis->kullanici_soyad;
														}
													}
												?>
													<div><strong>Cari Adı:</strong> <?= htmlspecialchars($cari_ad) ?></div>
													<div><strong>Tarih:</strong> <?= date('d.m.Y', strtotime($satis->satis_olusturmaTarihi)) ?></div>
													<div><strong>Tutar:</strong> <?= number_format($satis->satis_genelToplam, 2) ?> TL</div>
													<?php if (!empty($yapan_kullanici) && $user_id != $satis->satis_olusturan): ?>
														<div><strong>Satış Yapan:</strong> <?= htmlspecialchars($yapan_kullanici) ?></div>
													<?php endif; ?>
													<hr style="margin:8px 0;">
												<?php } 
											} ?>
										</div>
									</div>
								</div>

								<!-- Sağ taraf: 4 kart 2'şer 2'şer -->
								<div class="col-md-6">
									<div class="row">
										<!-- Üst satır -->
										<div class="col-md-6 mb-3">
											<div class="widget widget-stats bg-light-warning mb-7px" style="height: 140px;">
												<div class="stats-icon stats-icon-lg"><i class="fas fa-industry fa-fw"></i></div>
												<div class="stats-content">
													<div class="stats-title"><?= stokGrupAd(1); ?></div>
													<div class="stats-number">0,00 ₺</div>
													<div class="stats-desc">Bekleyen Tahsilat</div>
												</div>
											</div>
										</div>
										<div class="col-md-6 mb-3">
											<div class="widget widget-stats bg-light-primary mb-7px" style="height: 140px;">
												<div class="stats-icon stats-icon-lg"><i class="fas fa-users fa-fw"></i></div>
												<div class="stats-content">
													<div class="stats-title"><?= stokGrupAd(2); ?></div>
													<div class="stats-number">0,00 ₺</div>
													<div class="stats-desc">Bekleyen Tahsilat</div>
												</div>
											</div>
										</div>
										
										<!-- Alt satır -->
										<div class="col-md-6 mb-3">
											<div class="widget widget-stats bg-light-danger mb-7px" style="height: 140px;">
												<div class="stats-icon stats-icon-lg"><i class="fas fa-file-invoice fa-fw"></i></div>
												<div class="stats-content">
													<div class="stats-title"><?= stokGrupAd(4); ?></div>
													<div class="stats-number">0,00 ₺</div>
													<div class="stats-desc">Bekleyen Tahsilat</div>
												</div>
											</div>
										</div>
										<div class="col-md-6 mb-3">
											<div class="widget widget-stats bg-light-success mb-7px" style="height: 140px;">
												<div class="stats-icon stats-icon-lg"><i class="fa fa-money-bill fa-fw"></i></div>
												<div class="stats-content">
													<div class="stats-title">Hukuk Tutarı</div>
													<div class="stats-number"><?= number_format(alacakHesapla(30), 2); ?></div>
													<div class="stats-progress progress">
														<div class="progress-bar"
															 style="width: <?= number_format($alacakyuzde, 2); ?>%;"></div>
													</div>
													<div class="stats-desc">Son 30 Gün
														(%<?= number_format($alacakyuzde, 2); ?>)
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>			</div>





		</div>

	</div>

	<!-- /Page Wrapper -->



</div>

<!-- /Main Wrapper -->



<?php $this->load->view("include/footer-js"); ?>



<script src="<?= base_url() ?>assets/plugins/c3-chart/d3.v5.min.js"></script>

<script src="<?= base_url() ?>assets/plugins/c3-chart/c3.min.js"></script>

<script src="<?= base_url() ?>assets/plugins/c3-chart/chart-data.js"></script>

<!-- Chart JS

<script src="<?= base_url(); ?>assets/plugins/apexchart/apexcharts.min.js"></script>

<script src="<?= base_url(); ?>assets/plugins/apexchart/chart-data.js"></script>

-->



<script>

	// Dashboard için herhangi bir özel sidebar kodu yok

	// Diğer sayfalarla aynı şekilde çalışır

</script>



</body>



</html>

