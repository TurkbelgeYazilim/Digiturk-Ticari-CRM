<!DOCTYPE html>
<html lang="tr" xmlns="http://www.w3.org/1999/html">
<head>
	<?php $this->load->view("include/head-tags"); ?>
	<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/c3-chart/c3.min.css">
	<script src="https://www.amcharts.com/lib/4/core.js"></script>
	<script src="https://www.amcharts.com/lib/4/charts.js"></script>
	<script src="https://www.amcharts.com/lib/4/themes/animated.js"></script>
	<!-- Custom Widget Styles for Layout Consistency -->
	<style>
		.widget-container .widget {
			min-width: 300px !important;
			max-width: 360px !important;
			width: 100% !important;
			min-height: 140px !important;
			height: 140px !important;
			display: flex !important;
			align-items: center !important;
			padding: 20px !important;
			margin-bottom: 20px !important;
			border-radius: 8px !important;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
			transition: transform 0.2s ease !important;
		}
		
		.widget-container .widget:hover {
			transform: translateY(-2px) !important;
		}
				.widget-container .widget .stats-icon {
			flex-shrink: 0 !important;
			margin-right: 15px !important;
			font-size: 2.5rem !important;
		}
		
		.widget-container .widget .stats-icon-lg {
			flex-shrink: 0 !important;
			margin-right: 15px !important;
			font-size: 3rem !important;
			width: 60px !important;
			text-align: center !important;
		}
		
		.widget-container .widget .stats-content {
			flex: 1 !important;
		}
		
		.widget-container .widget .stats-title {
			font-size: 0.875rem !important;
			font-weight: 600 !important;
			margin-bottom: 8px !important;
		}
		
		.widget-container .widget .stats-number {
			font-size: 1.75rem !important;
			font-weight: bold !important;
			margin-bottom: 4px !important;
		}
		
		.widget-container .widget .stats-desc {
			font-size: 0.75rem !important;
			opacity: 0.8 !important;
		}
		
		/* Responsive adjustments for mobile */
		@media (max-width: 768px) {
			.widget-container .widget {
				min-width: 280px !important;
				max-width: 100% !important;
				height: auto !important;
				min-height: 120px !important;
				padding: 15px !important;
			}
					.widget-container .widget .stats-icon {
				font-size: 2rem !important;
				margin-right: 10px !important;
			}
			
			.widget-container .widget .stats-icon-lg {
				font-size: 2.5rem !important;
				margin-right: 10px !important;
				width: 50px !important;
			}
			
			.widget-container .widget .stats-number {
				font-size: 1.5rem !important;
			}
		}
		
		/* Ensure container rows have proper spacing */
		.widget-container .row {
			margin: 0 -10px !important;
		}
		
		.widget-container .col-md-3,
		.widget-container .col-md-6,
		.widget-container .col-sm-6,
		.widget-container .col-12 {
			padding: 0 10px !important;
			margin-bottom: 20px !important;
		}
		
		/* Progress bar consistency */
		.widget-container .widget .stats-progress {
			height: 4px !important;
			margin: 8px 0 !important;
			background-color: rgba(255,255,255,0.3) !important;
		}
		
		.widget-container .widget .progress-bar {
			background-color: rgba(255,255,255,0.8) !important;
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
							<!-- Widget Container for Layout Consistency -->
							<div class="widget-container">
								<div class="row mt-4">
									<div class="col-md-3 col-lg-3 col-sm-6">									<?php
									// Header'daki gibi kullanıcı bilgisini al
									$login_info = session("r", "login_info");
									$user_id = null;
									$toplam_satis_faturasi = 0;
									if ($login_info && isset($login_info->kullanici_id)) {
										$user_id = $login_info->kullanici_id;
										
										// Son 30 gün için tarih filtresi
										$start_date = date('Y-m-d', strtotime('-30 days'));
										
										// Kendisine bağlı olan kullanıcıları bul
										$allowed_user_ids_sales = array($user_id);
										$this->db->reset_query();
										$this->db->select('kullanici_id');
										$this->db->from('kullanicilar');
										$this->db->where('kullanici_sorumluMudur', $user_id);
										$bağlı_kullanicilar_sales = $this->db->get()->result();
										
										foreach ($bağlı_kullanicilar_sales as $user) {
											$allowed_user_ids_sales[] = $user->kullanici_id;
										}
										
										// Kullanıcının ve bağlı kullanıcıların oluşturduğu satış faturalarını say (son 30 gün)
										$this->db->reset_query();
										$this->db->where_in('satis_olusturan', $allowed_user_ids_sales);
										$this->db->where('DATE(satis_olusturmaTarihi) >=', $start_date);
										$toplam_satis_faturasi = $this->db->from('satisFaturasi')->count_all_results();
									}
									
									$bugun = date('d.m.Y');
									$bugun_url = date('Y-m-d');
									$satis_listesi_url = base_url("fatura/satis-faturasi-listesi?tarihAraligi=" . $bugun . " - " . $bugun);
									?><a href="<?= $satis_listesi_url ?>"
									   style="text-decoration: none; color: inherit;">
										<div class="widget widget-stats bg-light-warning mb-7px" style="cursor: pointer;">
											<div class="stats-icon stats-icon-lg"><i class="fas fa-industry fa-fw"></i></div>
											<div class="stats-content">
												<div class="stats-title">Yapım Aşamasında</div>
												<div class="stats-number" style="display: none;"><?= number_format($toplam_satis_faturasi); ?></div>
												<div class="stats-desc" style="display: none;">Son 30 Gün</div>
											</div>
										</div>
									</a>
								</div>								<div class="col-md-3 col-lg-3 col-sm-6">
									<div class="widget widget-stats bg-light-primary mb-7px">
										<div class="stats-icon stats-icon-lg"><i class="fas fa-users fa-fw"></i></div>
										<div class="stats-content">
											<div class="stats-title">Yapım Aşamasında</div>
											<div class="stats-number" style="display: none;"><?= number_format(musteriHesapla(30)); ?></div>
											<div class="stats-progress progress" style="display: none;">
												<div class="progress-bar"
													 style="width: <?= number_format($musteriyuzde, 2) ?>%;"></div>
											</div>
											<div class="stats-desc" style="display: none;">Son 30 Gün
												(%<?= number_format($musteriyuzde, 2) ?>)
											</div>
										</div>
									</div>
								</div>								<div class="col-md-3 col-lg-3 col-sm-6">
									<div class="widget widget-stats bg-light-danger mb-7px">
										<div class="stats-icon stats-icon-lg"><i class="fas fa-file-invoice fa-fw"></i>
										</div>
										<div class="stats-content">
											<div class="stats-title">Yapım Aşamasında</div>
											<div class="stats-number" style="display: none;"><?= faturaHesapla(30); ?></div>
											<div class="stats-progress progress" style="display: none;">
												<div class="progress-bar"
													 style="width: <?= number_format($faturayuzde, 2) ?>%;"></div>
											</div>
											<div class="stats-desc" style="display: none;">Son 30 Gün
												(%<?= number_format($faturayuzde, 2) %>)
											</div>
										</div>
									</div>
								</div>								<div class="col-md-3 col-lg-3 col-sm-6">
									<div class="widget widget-stats bg-light-success mb-7px">
										<div class="stats-icon stats-icon-lg"><i class="fa fa-money-bill fa-fw"></i>
										</div>
										<div class="stats-content">
											<div class="stats-title">Yapım Aşamasında</div>
											<div class="stats-number" style="display: none;"><?= number_format(alacakHesapla(30), 2); ?></div>
											<div class="stats-progress progress" style="display: none;">
												<div class="progress-bar"
													 style="width: <?= number_format($alacakyuzde, 2); ?>%;"></div>
											</div>
											<div class="stats-desc" style="display: none;">Son 30 Gün
												(%<?= number_format($alacakyuzde, 2); %>)
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- 4 widget daha çoğaltıldı -->
							<!-- Yeni düzen: Sağ üstte, üstten aşağı 2x2 olacak şekilde, boyutlar değişmeden -->
						</div> <!-- .row mt-4 bitişi -->						<!-- İkinci satır: Son Satışlarım ve diğer widget'lar yan yana -->
						<div class="row mt-4 mobile-reorder">							<?php
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

							// Teklif Verildi: durum_id = 4
							$this->db->reset_query();
							$this->db->where('durum_id', 4);
							$this->db->where_in('islemi_yapan', $allowed_user_ids);
							$teklif_verildi_count = $this->db->from('potansiyel_satis')->count_all_results();

							// İptal: durum_id <= 3
							$this->db->reset_query();
							$this->db->where('durum_id <=', 3);
							$this->db->where_in('islemi_yapan', $allowed_user_ids);
							$iptal_count = $this->db->from('potansiyel_satis')->count_all_results();

							// Satıldı: durum_id = 5
							$this->db->reset_query();
							$this->db->where('durum_id', 5);
							$this->db->where_in('islemi_yapan', $allowed_user_ids);
							$satildi_count = $this->db->from('potansiyel_satis')->count_all_results();
							?>
									<!-- Son Satışlarım -->
							<div class="col-md-6 mobile-sales-last">
								<div class="card pb-3">
									<div class="card-header">
										<h5 class="card-title">Son Satışlarım</h5>
									</div>									<div class="card-body card-body-height" style="min-height:200px; max-height: 400px; overflow-y: auto;">
										<?php
										$this->db->reset_query();
										$this->db->select('sf.*, c.cari_ad, c.cari_soyad, k.kullanici_ad, k.kullanici_soyad');
										$this->db->from('satisFaturasi sf');
										$this->db->join('cari c', 'sf.satis_CariID = c.cari_id', 'left');
										$this->db->join('kullanicilar k', 'sf.satis_olusturan = k.kullanici_id', 'left');
										$this->db->where_in('sf.satis_olusturan', $allowed_user_ids);
										$this->db->order_by('sf.satis_olusturmaTarihi', 'DESC');$this->db->limit(10);
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
							</div>							<!-- Sağ taraftaki widget'lar -->
							<div class="col-md-6 mobile-widgets-first">
								<div class="row">									<div class="col-md-6 mb-3">
										<a href="<?= base_url('teklif/teklif-listele?durum_id=4'); ?>" class="text-decoration-none">
											<div class="widget widget-stats bg-light-primary mb-7px">
												<div class="stats-icon stats-icon-lg"><i class="fas fa-users fa-fw"></i></div>
												<div class="stats-content">
													<div class="stats-title">Teklif Verildi</div>
													<div class="stats-number"><?= $teklif_verildi_count ?></div>
													<div class="stats-desc">Son 30 Gün</div>
												</div>
											</div>
										</a>
									</div>									<div class="col-md-6 mb-3">
										<a href="<?= base_url('teklif/teklif-listele?durum_id=5'); ?>" class="text-decoration-none">
											<div class="widget widget-stats bg-light-danger mb-7px">
												<div class="stats-icon stats-icon-lg"><i class="fas fa-file-invoice fa-fw"></i></div>
												<div class="stats-content">
													<div class="stats-title">Satıldı</div>
													<div class="stats-number"><?= $satildi_count ?></div>
													<div class="stats-desc">Son 30 Gün</div>
												</div>
											</div>
										</a>
									</div>									<div class="col-md-6 mb-3">
										<a href="<?= base_url('teklif/teklif-listele?durum_id=3'); ?>" class="text-decoration-none">
											<div class="widget widget-stats bg-light-success mb-7px">
												<div class="stats-icon stats-icon-lg"><i class="fa fa-money-bill fa-fw"></i></div>
												<div class="stats-content">
													<div class="stats-title">İptal</div>
													<div class="stats-number"><?= $iptal_count ?></div>
													<div class="stats-desc">Son 30 Gün</div>
												</div>
											</div>
										</a>
									</div>
									<div class="col-md-6 mb-3">
										<!-- Boş alan veya gelecekte başka bir widget eklenebilir -->
									</div>								</div>
							</div>
							<!-- End Widget Container -->
						</div>
						<!-- Eski alt widget satırları kaldırıldı -->
					</div>
				</div>			</div>
			<div class="row mb-4">
</div>


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
	$(document).ready(function () {

		$(window).resize(function () {
			resize();
		});
		resize();

		function resize() {
			let width = screen.width;
			var element1 = document.getElementById("div1");
			var element2 = document.getElementById("div2");
			if (width <= 1366) {

				element1.classList.remove("col-md-4");
				element1.classList.add("col-md-6");
				element2.classList.remove("col-md-4");
				element2.classList.add("col-md-6");
			} else if (width >= 1920) {

				element1.classList.remove("col-md-6");
				element1.classList.add("col-md-4");
				element2.classList.remove("col-md-6");
				element2.classList.add("col-md-4");
			}
		}

	});
</script>

<style>
/* Ana sayfa widget'ları için özel CSS */
.page-wrapper .widget {
    min-width: 300px !important;
    max-width: 360px !important;
    width: 100% !important;
    min-height: 140px !important;
    height: 140px !important;
    display: flex !important;
    align-items: center !important;
    margin-bottom: 7px !important;
    padding: 15px !important;
}

.widget-stats {
    background-color: #f8f9fa;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stats-icon {
    font-size: 2.5rem !important;
    margin-right: 15px !important;
}

.stats-icon-lg {
    font-size: 3rem !important;
    margin-right: 15px !important;
    width: 60px !important;
    text-align: center !important;
}

.stats-content {
    flex-grow: 1;
}

.stats-title {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 5px;
}

.stats-number {
    font-size: 20px;
    font-weight: bold;
    color: #333;
}

.stats-desc {
    font-size: 14px;
    color: #666;
}

@media (max-width: 768px) {
    .widget-container {
        position: static;
        width: 100%;
        margin: 0 auto;
    }
    .widget {
        margin-bottom: 15px !important;
        width: 100% !important;
        min-width: 280px !important;
        max-width: 100% !important;
    }
    .card {
        margin-bottom: 20px;
    }
    
    /* Mobil'de Son Satışlarım'ı en alta taşı */
    .mobile-reorder {
        display: flex;
        flex-direction: column;
    }
    
    .mobile-sales-last {
        order: 2;
    }
      .mobile-widgets-first {
        order: 1;
    }
}

/* Dashboard Widget Hover Effects */
.widget.widget-stats {
    transition: all 0.3s ease;
}

.widget.widget-stats:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

a.text-decoration-none:hover {
    text-decoration: none !important;
}

/* Widget link styling */
a.text-decoration-none .stats-title,
a.text-decoration-none .stats-number,
a.text-decoration-none .stats-desc {
    color: inherit;
}
</style>

</body>

</html>
