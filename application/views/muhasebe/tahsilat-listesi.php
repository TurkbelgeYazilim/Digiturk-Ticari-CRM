<!DOCTYPE html>

<html lang="tr">

<head>

	<?php $this->load->view("include/head-tags"); ?>

	<title>Tahsilat Listesi | İlekaSoft CRM</title>

	<link rel="icon" type="image/x-icon" href="<?= base_url('assets/favicon.ico'); ?>" />

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/css/dataTables.bootstrap4.min.css"/>

	

	<style>

		.tahsilat-onay-bekleyen {

			background-color: #fff3cd;

			border-left: 4px solid #ffc107;

		}

		

		.tahsilat-onaylanan {

			background-color: #d4edda;

			border-left: 4px solid #28a745;

		}

		

		.tahsilat-reddedilen {

			background-color: #f8d7da;

			border-left: 4px solid #dc3545;

		}

		

		.tutar-buyuk {

			font-size: 1.2em;

			font-weight: bold;

			color: #28a745;

		}

		

		.btn-onay {

			background-color: #28a745;

			border-color: #28a745;

			color: white;

		}

		

		.btn-onay:hover {

			background-color: #218838;

			border-color: #1e7e34;

			color: white;

		}

		

		.btn-red {

			background-color: #dc3545;

			border-color: #dc3545;

			color: white;

		}

		

		.btn-red:hover {

			background-color: #c82333;

			border-color: #bd2130;

			color: white;

		}

		

		.card-stats {

			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

			color: white;

		}

		

		.card-stats.card-warning {

			background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);

		}

		

		.card-stats.card-success {

			background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);

		}

		

		.card-stats.card-danger {

			background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);

		}

		

		.card-stats .card-body {

			padding: 1.5rem;

		}

		

		.stats-number {

			font-size: 1.4rem;

			font-weight: bold;

			margin-bottom: 0.5rem;

			white-space: nowrap;

			overflow: hidden;

			text-overflow: ellipsis;

		}

		

		.stats-label {

			font-size: 0.8rem;

			opacity: 0.9;

			line-height: 1.2;

		}

		

		.cek-detaylari {

			background-color: #f8f9fa;

			border: 1px solid #e9ecef;

			border-radius: 4px;

			padding: 8px;

			margin: 2px 0;

		}

		

		.cek-detaylari small {

			margin-bottom: 2px;

			color: #495057;

		}

		

		.cek-detaylari .badge {

			font-size: 0.75em;

		}

		

		.banka-detaylari {

			background-color: #e8f4fd;

			border: 1px solid #bee5eb;

			border-radius: 4px;

			padding: 8px;

			margin: 2px 0;

		}

		

		.banka-detaylari small {

			margin-bottom: 2px;

			color: #055160;

		}

		

		.banka-detaylari .badge {

			font-size: 0.75em;

		}

		

		.kasa-detaylari {

			background-color: #fff3cd;

			border: 1px solid #ffeaa7;

			border-radius: 4px;

			padding: 8px;

			margin: 2px 0;

		}

		

		.kasa-detaylari small {

			margin-bottom: 2px;

			color: #856404;

		}

		

		.kasa-detaylari .badge {

			font-size: 0.75em;

		}

		

		.filtre-card {

			border-left: 4px solid #17a2b8;

		}

		

		.filtre-card .card-header {

			background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);

			color: white;

		}

		

		.filtre-card .card-header .card-title {

			color: white !important;

		}

		

		/* Tahsilat Tipi Dropdown Checkbox Stilleri */

		.tahsilat-tipi-dropdown .dropdown-menu {

			max-height: 300px;

			overflow-y: auto;

		}

		

		.tahsilat-tipi-dropdown .dropdown-menu .custom-control {

			cursor: pointer;

		}

		

		.tahsilat-tipi-dropdown .dropdown-menu .custom-control:hover {

			background-color: #f8f9fa;

			border-radius: 4px;

		}

		

		.tahsilat-tipi-dropdown .dropdown-menu .custom-control-label {

			width: 100%;

			cursor: pointer;

			padding: 2px 0;

		}

		

		.tahsilat-tipi-dropdown .dropdown-toggle {

			text-align: left;

			overflow: hidden;

			text-overflow: ellipsis;

			white-space: nowrap;

		}

		

		.tahsilat-tipi-dropdown .dropdown-toggle .badge {

			font-size: 0.75em;

			margin-left: 5px;

		}

		

		/* Checkbox stilleri */

		.custom-control-input:checked ~ .custom-control-label::before {

			background-color: #007bff;

			border-color: #007bff;

		}

		

		/* Dropdown menu click olayında kapanmaması için */

		.tahsilat-tipi-dropdown .dropdown-menu {

			padding: 8px;

		}

		

		@media (max-width: 768px) {

			.table-responsive {

				font-size: 0.875rem;

			}

			

			.btn-group .btn {

				padding: 0.25rem 0.5rem;

				font-size: 0.8rem;

			}

			

			.cek-detaylari {

				padding: 4px;

				font-size: 0.8rem;

			}

			

			.banka-detaylari {

				padding: 4px;

				font-size: 0.8rem;

			}

			

			.kasa-detaylari {

				padding: 4px;

				font-size: 0.8rem;

			}

			

			.stats-number {

				font-size: 1rem;

			}

			

			.stats-label {

				font-size: 0.7rem;

			}

			

			.card-stats .card-body {

				padding: 1rem;

			}

		}

	</style>

</head>

<body>

<div class="main-wrapper">

	<?php $this->load->view("include/header"); ?>

	<?php $this->load->view("include/sidebar"); ?>

	<div class="page-wrapper">

		<div class="content container-fluid">

			<!-- Page Header -->

			<div class="page-header">

				<div class="row">

					<div class="col-sm-10">

						<h3 class="page-title">

							<?php if(isset($ozel_sayfa) && $ozel_sayfa): ?>

								Çek ve Senet Ödeme Beklenen Tahsilatlar

							<?php else: ?>

								Tahsilat Listesi

							<?php endif; ?>

						</h3>

						<ul class="breadcrumb">

							<li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>

							<li class="breadcrumb-item">Muhasebe</li>

							<li class="breadcrumb-item active">

								<?php if(isset($ozel_sayfa) && $ozel_sayfa): ?>

									Çek ve Senet Ödeme Beklenen Tahsilatlar

								<?php else: ?>

									Tahsilat Listesi

								<?php endif; ?>

							</li>

					</ul>

				</div>

				<div class="d-flex justify-content-end text-align-center col-sm-2">

					<a class="btn btn-outline-light btn-sm" href="javascript:history.back()">

						<i class="fa fa-history"></i> <br>Önceki Sayfa

					</a>

				</div>

			</div>			</div>

			<!-- /Page Header -->



			<!-- İstatistikler -->

			<div class="row mb-4">

				<!-- Durum İstatistikleri -->

				<div class="col-xl-2 col-sm-6 col-12">

					<div class="card card-stats">

						<div class="card-body">

							<div class="stats-number">

								<?= isset($durum_stats) ? $durum_stats['bekleyen'] . '/' . $durum_stats['tamamlanan'] . '/' . $durum_stats['reddedilen'] : '0/0/0' ?>

							</div>

							<div class="stats-label">

								<i class="fa fa-list mr-2"></i>Bekleyen/Tamamlanan/Reddedilen

							</div>

						</div>

					</div>

				</div>

				

				<!-- Senet İstatistikleri -->

				<div class="col-xl-2 col-sm-6 col-12">

					<div class="card card-stats card-warning">

						<div class="card-body">

							<div class="stats-number">

								<?= isset($tip_stats) ? $tip_stats['senet']['adet'] . '/' . number_format($tip_stats['senet']['tutar'], 0, ',', '.') : '0/0' ?>

							</div>

							<div class="stats-label">

								<i class="fa fa-file-o mr-2"></i>Senet Adet/Tutar

							</div>

						</div>

					</div>

				</div>

				

				<!-- Çek İstatistikleri -->

				<div class="col-xl-2 col-sm-6 col-12">

					<div class="card card-stats card-success">

						<div class="card-body">

							<div class="stats-number">

								<?= isset($tip_stats) ? $tip_stats['cek']['adet'] . '/' . number_format($tip_stats['cek']['tutar'], 0, ',', '.') : '0/0' ?>

							</div>

							<div class="stats-label">

								<i class="fa fa-file-text-o mr-2"></i>Çek Adet/Tutar

							</div>

						</div>

					</div>

				</div>

				

				<!-- Banka İstatistikleri -->

				<div class="col-xl-2 col-sm-6 col-12">

					<div class="card card-stats card-danger">

						<div class="card-body">

							<div class="stats-number">

								<?= isset($tip_stats) ? $tip_stats['banka']['adet'] . '/' . number_format($tip_stats['banka']['tutar'], 0, ',', '.') : '0/0' ?>

							</div>

							<div class="stats-label">

								<i class="fa fa-university mr-2"></i>Banka Adet/Tutar

							</div>

						</div>

					</div>

				</div>

				

				<!-- Kasa İstatistikleri -->

				<div class="col-xl-2 col-sm-6 col-12">

					<div class="card card-stats">

						<div class="card-body">

							<div class="stats-number">

								<?= isset($tip_stats) ? $tip_stats['kasa']['adet'] . '/' . number_format($tip_stats['kasa']['tutar'], 0, ',', '.') : '0/0' ?>

							</div>

							<div class="stats-label">

								<i class="fa fa-money mr-2"></i>Kasa Adet/Tutar

							</div>

						</div>

					</div>

				</div>

				

				<!-- Vadesi Geçen İstatistikleri -->

				<div class="col-xl-2 col-sm-6 col-12">

					<div class="card card-stats" style="background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);">

						<div class="card-body">

							<div class="stats-number">

								<?= isset($vadesi_gecen) ? $vadesi_gecen['adet'] . '/' . number_format($vadesi_gecen['tutar'], 0, ',', '.') : '0/0' ?>

							</div>

							<div class="stats-label">

								<i class="fa fa-exclamation-triangle mr-2"></i>Vadesi Geçen Adet/Tutar

							</div>

						</div>

					</div>

				</div>

			</div>



			<!-- Toplam Tutar -->

			<div class="row mb-4">

				<div class="col-12">

					<div class="card">

						<div class="card-body text-center">

							<h4 class="card-title">Toplam Tahsilat Tutarı</h4>

							<h2 class="text-success">

								<i class="fa fa-money mr-2"></i>

								<?= number_format($toplam_tutar, 2, ',', '.') ?>

							</h2>

						</div>

					</div>

				</div>

			</div>



			<!-- Filtre Formu -->

			<div class="row mb-4">

				<div class="col-12">

					<div class="card filtre-card">

						<div class="card-header">

							<h5 class="card-title mb-0">

								<i class="fa fa-filter text-info mr-2"></i>Filtreler

								<button type="button" class="btn btn-sm btn-outline-secondary float-right" onclick="filtreleriTemizle()">

									<i class="fa fa-eraser"></i> Filtreleri Temizle

								</button>

							</h5>

						</div>

						<div class="card-body">

							<form method="GET" id="filtreForm">

								<div class="row">

									<!-- Tahsilat Tipi -->

									<div class="col-md-3 col-sm-6 mb-3">

										<label for="tahsilat_tipi">Tahsilat Tipi</label>

										<div class="dropdown tahsilat-tipi-dropdown">

											<button class="btn btn-outline-secondary dropdown-toggle w-100 text-left" type="button" id="tahsilatTipiDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

												<span id="tahsilatTipiLabel">Tahsilat tipini seçiniz...</span>

											</button>

											<div class="dropdown-menu w-100 p-2" aria-labelledby="tahsilatTipiDropdown" style="min-width: 250px;">

												<div class="custom-control custom-checkbox mb-2">

													<input type="checkbox" class="custom-control-input tahsilat-tipi-checkbox" name="tahsilat_tipi[]" value="1" id="tahsilat_tipi_1" <?= (is_array($filtre_tahsilat_tipi) && in_array('1', $filtre_tahsilat_tipi)) || (!is_array($filtre_tahsilat_tipi) && $filtre_tahsilat_tipi == '1') ? 'checked' : '' ?>>

													<label class="custom-control-label" for="tahsilat_tipi_1">

														<i class="fa fa-university text-primary mr-2"></i>Banka

													</label>

												</div>

												<div class="custom-control custom-checkbox mb-2">

													<input type="checkbox" class="custom-control-input tahsilat-tipi-checkbox" name="tahsilat_tipi[]" value="2" id="tahsilat_tipi_2" <?= (is_array($filtre_tahsilat_tipi) && in_array('2', $filtre_tahsilat_tipi)) || (!is_array($filtre_tahsilat_tipi) && $filtre_tahsilat_tipi == '2') ? 'checked' : '' ?>>

													<label class="custom-control-label" for="tahsilat_tipi_2">

														<i class="fa fa-file-text-o text-success mr-2"></i>Çek

													</label>

												</div>

												<div class="custom-control custom-checkbox mb-2">

													<input type="checkbox" class="custom-control-input tahsilat-tipi-checkbox" name="tahsilat_tipi[]" value="3" id="tahsilat_tipi_3" <?= (is_array($filtre_tahsilat_tipi) && in_array('3', $filtre_tahsilat_tipi)) || (!is_array($filtre_tahsilat_tipi) && $filtre_tahsilat_tipi == '3') ? 'checked' : '' ?>>

													<label class="custom-control-label" for="tahsilat_tipi_3">

														<i class="fa fa-money text-warning mr-2"></i>Kasa

													</label>

												</div>

												<div class="custom-control custom-checkbox mb-2">

													<input type="checkbox" class="custom-control-input tahsilat-tipi-checkbox" name="tahsilat_tipi[]" value="4" id="tahsilat_tipi_4" <?= (is_array($filtre_tahsilat_tipi) && in_array('4', $filtre_tahsilat_tipi)) || (!is_array($filtre_tahsilat_tipi) && $filtre_tahsilat_tipi == '4') ? 'checked' : '' ?>>

													<label class="custom-control-label" for="tahsilat_tipi_4">

														<i class="fa fa-file-o text-info mr-2"></i>Senet

													</label>

												</div>

												<div class="dropdown-divider"></div>

												<div class="text-center">

													<button type="button" class="btn btn-sm btn-outline-primary" onclick="tahsilatTipiTumunuSec()">

														<i class="fa fa-check-square-o"></i> Tümünü Seç

													</button>

													<button type="button" class="btn btn-sm btn-outline-secondary ml-1" onclick="tahsilatTipiTumunuTemizle()">

														<i class="fa fa-square-o"></i> Temizle

													</button>

												</div>

											</div>

										</div>

									</div>

									

									<!-- Durum -->

									<div class="col-md-3 col-sm-6 mb-3">

										<label for="durum">Durum</label>

										<select class="form-control" name="durum" id="durum">

											<option value="">Tümü</option>

											<option value="1" <?= ($filtre_durum == '1') ? 'selected' : '' ?>>Ödeme Bekleniyor</option>

											<option value="2" <?= ($filtre_durum == '2') ? 'selected' : '' ?>>Ödeme Alındı</option>

											<option value="3" <?= ($filtre_durum == '3') ? 'selected' : '' ?>>İşlem Başarısız</option>

										</select>

									</div>

									

									<!-- İşlemi Yapan Personel -->

									<div class="col-md-3 col-sm-6 mb-3">

										<label for="personel">İşlemi Yapan Personel</label>

										<select class="form-control" name="personel" id="personel">

											<option value="">Tümü</option>

											<?php if (isset($personel_listesi) && !empty($personel_listesi)): ?>

												<?php foreach($personel_listesi as $personel): ?>

													<option value="<?= $personel->kullanici_id ?>" <?= ($filtre_personel == $personel->kullanici_id) ? 'selected' : '' ?>>

														<?= $personel->kullanici_ad ?> <?= $personel->kullanici_soyad ?>

													</option>

												<?php endforeach; ?>

											<?php endif; ?>

										</select>

									</div>

								</div>

								

								<div class="row">

									<!-- Başlangıç Tarihi -->

									<div class="col-md-3 col-sm-6 mb-3">

										<label for="baslangic_tarih">Başlangıç Tarihi</label>

										<input type="date" class="form-control" name="baslangic_tarih" id="baslangic_tarih" value="<?= isset($filtre_baslangic_tarih) ? $filtre_baslangic_tarih : '' ?>">

									</div>

									

									<!-- Bitiş Tarihi -->

									<div class="col-md-3 col-sm-6 mb-3">

										<label for="bitis_tarih">Bitiş Tarihi</label>

										<input type="date" class="form-control" name="bitis_tarih" id="bitis_tarih" value="<?= isset($filtre_bitis_tarih) ? $filtre_bitis_tarih : '' ?>">

									</div>

								</div>

								

								<div class="row">

									<div class="col-12">

										<button type="submit" class="btn btn-primary">

											<i class="fa fa-search"></i> Filtrele

										</button>

										

										<?php if (!empty($filtre_tahsilat_tipi) || !empty($filtre_durum) || !empty($filtre_personel) || !empty($filtre_baslangic_tarih) || !empty($filtre_bitis_tarih)): ?>

											<span class="badge badge-info ml-2">

												<i class="fa fa-filter"></i> Filtre aktif

											</span>

										<?php endif; ?>

									</div>

								</div>

							</form>

						</div>

					</div>

				</div>

			</div>



			<!-- Tahsilatlar Listesi -->

			<div class="row">

				<div class="col-xl-12 col-md-12">

					<div class="card card-table">

						<div class="card-header">

							<div class="row">

								<div class="col">

									<h4 class="card-title">

										<i class="fa fa-list text-primary mr-2"></i>

										<?php if(isset($ozel_sayfa) && $ozel_sayfa): ?>

											Çek ve Senet Ödeme Beklenen Tahsilatlar

										<?php else: ?>

											Tüm Tahsilatlar

										<?php endif; ?>

									</h4>

									<p class="text-muted mb-0">

										<?php if(isset($ozel_sayfa) && $ozel_sayfa): ?>

											<small>Bugün ve sonrası vade tarihli, ödeme beklenen çek ve senet tahsilatları listelenmektedir</small>

										<?php else: ?>

											<small>Tüm tahsilatlar durum farkı gözetmeksizin listelenmektedir</small>

										<?php endif; ?>

									</p>

							</div>

							<div class="col-auto">

								<?php 
									// Excel export için query string oluştur
									$qs = '?';
									if(!empty($_GET['tahsilat_tipi'])) {
										if(is_array($_GET['tahsilat_tipi'])) {
											foreach($_GET['tahsilat_tipi'] as $tip) {
												$qs .= 'tahsilat_tipi[]=' . urlencode($tip) . '&';
											}
										} else {
											$qs .= 'tahsilat_tipi=' . urlencode($_GET['tahsilat_tipi']) . '&';
										}
									}
									if(!empty($_GET['durum'])) $qs .= 'durum=' . urlencode($_GET['durum']) . '&';
									if(!empty($_GET['personel'])) $qs .= 'personel=' . urlencode($_GET['personel']) . '&';
									if(!empty($_GET['baslangic_tarih'])) $qs .= 'baslangic_tarih=' . urlencode($_GET['baslangic_tarih']) . '&';
									if(!empty($_GET['bitis_tarih'])) $qs .= 'bitis_tarih=' . urlencode($_GET['bitis_tarih']) . '&';
									$qs = rtrim($qs, '&');
									if($qs == '?') $qs = '';
								?>
								<a href="<?= base_url("muhasebe/tahsilatListesiExcel$qs"); ?>" class="btn btn-outline-success btn-sm mr-2">
									<i class="fa fa-file-excel"></i> Excel
								</a>

								<button type="button" class="btn btn-primary btn-sm" onclick="location.reload()">

									<i class="fa fa-refresh"></i> Yenile

								</button>

							</div>

						</div>

					</div>

						<?php if (isset($error_message)): ?>

							<div class="alert alert-warning">

								<div class="row">

									<div class="col-md-2 text-center">

										<i class="fa fa-database" style="font-size: 3rem; color: #ffc107;"></i>

									</div>

									<div class="col-md-10">

										<h5><i class="fa fa-exclamation-triangle mr-2"></i>Veritabanı Kurulum Gerekli</h5>

										<p><?= $error_message ?></p>

										<div class="mt-3">

											<p><strong>Kurulum Adımları:</strong></p>

											<ol>

												<li>MySQL/phpMyAdmin'e giriş yapın</li>

												<li><code>muhase_database_setup.sql</code> dosyasını çalıştırın</li>

												<li>Bu sayfayı yenileyin</li>

											</ol>

										</div>

									</div>

								</div>

							</div>

						<?php elseif (count($tahsilatlar) > 0): ?>

							<div class="table-responsive">

								<table class="table table-striped custom-table mb-0" id="tahsilatlarTable">

									<thead>

										<tr>

											<th>ID</th>

											<th>Tahsilat Tipi</th>

											<th>Müşteri Adı</th>

											<th>Tutar</th>

											<th>Detaylar</th>

											<th>Durum</th>

											<th>Vade Tarihi</th>

											<th>Onay Yapan</th>

											<th>İşlem Tarihi</th>

											<th>Görsel</th>

											<th>Açıklama</th>

											<th>İşlemler</th>

										</tr>

									</thead>

									<tbody>

										<?php foreach($tahsilatlar as $tahsilat): ?>

										<tr class="<?php 

											if($tahsilat->durum == 1) echo 'tahsilat-onay-bekleyen';

											elseif($tahsilat->durum == 2) echo 'tahsilat-onaylanan';

											elseif($tahsilat->durum == 3) echo 'tahsilat-reddedilen';

										?>">

											<td><strong>#<?= $tahsilat->id ?></strong></td>

											<td>

												<?php 

												$icon = '';

												$class = '';

												switch($tahsilat->tahsilat_tipi_adi) {

													case 'Banka': $icon = 'fa-university'; $class = 'text-primary'; break;

													case 'Çek': $icon = 'fa-file-text-o'; $class = 'text-info'; break;

													case 'Kasa': $icon = 'fa-money'; $class = 'text-success'; break;

													case 'Senet': $icon = 'fa-file-o'; $class = 'text-warning'; break;

													default: $icon = 'fa-question'; $class = 'text-muted'; break;

												}

												?>

												<i class="fa <?= $icon ?> <?= $class ?> mr-2"></i>

												<span class="<?= $class ?>"><?= $tahsilat->tahsilat_tipi_adi ?></span>

											</td>

											<td>

												<strong title="<?= htmlspecialchars($tahsilat->musteri_adi ?: 'Bilinmiyor') ?>">
													<?= $tahsilat->musteri_adi ? (mb_strlen($tahsilat->musteri_adi) > 30 ? mb_substr($tahsilat->musteri_adi, 0, 30) . '...' : $tahsilat->musteri_adi) : 'Bilinmiyor' ?>
												</strong>

											</td>

											<td>

												<span class="tutar-buyuk"><?= $tahsilat->tutar ? number_format($tahsilat->tutar, 2, ',', '.') . ' ₺' : '-' ?></span>

											</td>

											<td>

												<?php if($tahsilat->tahsilat_tipi_adi == 'Banka' && isset($tahsilat->banka_belge_no)): ?>

													<div class="banka-detaylari">

														<?php if($tahsilat->banka_adi): ?>

															<small class="d-block"><strong>Banka:</strong> <?= $tahsilat->banka_adi ?></small>

														<?php endif; ?>

														<?php if($tahsilat->banka_hesap_no): ?>

															<small class="d-block"><strong>Hesap No:</strong> <?= $tahsilat->banka_hesap_no ?></small>

														<?php endif; ?>

														<?php if($tahsilat->banka_belge_no): ?>

															<small class="d-block"><strong>Belge No:</strong> <?= $tahsilat->banka_belge_no ?></small>

														<?php endif; ?>

														<?php if($tahsilat->banka_tarih): ?>

															<small class="d-block"><strong>İşlem Tarihi:</strong> <?= date('d.m.Y', strtotime($tahsilat->banka_tarih)) ?></small>

														<?php endif; ?>

														<?php if($tahsilat->banka_turu): ?>

															<small class="d-block">

																<strong>Hareket Tipi:</strong> 

																<span class="badge badge-<?= $tahsilat->banka_turu == 1 ? 'success' : 'info' ?>">

																	<?= $tahsilat->banka_turu == 1 ? 'Gelen Havale' : 'Diğer' ?>

																</span>

															</small>

														<?php endif; ?>

														<?php if($tahsilat->banka_aciklama): ?>

															<small class="d-block"><strong>Açıklama:</strong> <?= substr($tahsilat->banka_aciklama, 0, 50) ?><?= strlen($tahsilat->banka_aciklama) > 50 ? '...' : '' ?></small>

														<?php endif; ?>

													</div>

												<?php elseif($tahsilat->tahsilat_tipi_adi == 'Kasa' && isset($tahsilat->kasa_belge_no)): ?>

													<div class="kasa-detaylari">

														<?php if($tahsilat->kasa_adi): ?>

															<small class="d-block"><strong>Kasa:</strong> <?= $tahsilat->kasa_adi ?></small>

														<?php endif; ?>

														<?php if($tahsilat->kasa_kodu): ?>

															<small class="d-block"><strong>Kasa Kodu:</strong> <?= $tahsilat->kasa_kodu ?></small>

														<?php endif; ?>

														<?php if($tahsilat->kasa_belge_no): ?>

															<small class="d-block"><strong>Belge No:</strong> <?= $tahsilat->kasa_belge_no ?></small>

														<?php endif; ?>

														<?php if($tahsilat->kasa_tarih): ?>

															<small class="d-block"><strong>İşlem Tarihi:</strong> <?= date('d.m.Y', strtotime($tahsilat->kasa_tarih)) ?></small>

														<?php endif; ?>

														<?php if($tahsilat->kasa_turu): ?>

															<small class="d-block">

																<strong>Hareket Tipi:</strong> 

																<span class="badge badge-<?= $tahsilat->kasa_turu == 1 ? 'success' : 'primary' ?>">

																	<?= $tahsilat->kasa_turu == 1 ? 'Nakit Tahsilat' : 'Diğer' ?>

																</span>

															</small>

														<?php endif; ?>

														<?php if($tahsilat->kasa_aciklama && !strpos($tahsilat->kasa_aciklama, 'Açılış Bakiyesi')): ?>

															<small class="d-block"><strong>Açıklama:</strong> <?= substr($tahsilat->kasa_aciklama, 0, 50) ?><?= strlen($tahsilat->kasa_aciklama) > 50 ? '...' : '' ?></small>

														<?php endif; ?>

													</div>

												<?php elseif($tahsilat->tahsilat_tipi_adi == 'Çek' && isset($tahsilat->cek_seri_no)): ?>

													<div class="cek-detaylari">

														<?php if($tahsilat->cek_seri_no): ?>

															<small class="d-block"><strong>Seri No:</strong> <?= $tahsilat->cek_seri_no ?></small>

														<?php endif; ?>

														<?php if($tahsilat->cek_vade_tarih): ?>

															<small class="d-block"><strong>Vade:</strong> <?= date('d.m.Y', strtotime($tahsilat->cek_vade_tarih)) ?></small>

														<?php endif; ?>

														<?php if($tahsilat->cek_portfoy_no): ?>

															<small class="d-block"><strong>Portföy:</strong> <?= $tahsilat->cek_portfoy_no ?></small>

														<?php endif; ?>

														<?php if(isset($tahsilat->cek_durum)): ?>

															<small class="d-block">

																<strong>Çek Durumu:</strong> 

																<span class="badge badge-<?= $tahsilat->cek_durum == 1 ? 'success' : ($tahsilat->cek_durum == 0 ? 'warning' : 'danger') ?>">

																	<?= $tahsilat->cek_durum == 1 ? 'Tahsil Edildi' : ($tahsilat->cek_durum == 0 ? 'Beklemede' : 'İade') ?>

																</span>

															</small>

														<?php endif; ?>

													</div>

												<?php else: ?>

													<span class="text-muted">-</span>

												<?php endif; ?>

											</td>

											<td>

												<?php if($tahsilat->durum == 1): ?>

													<span class="badge badge-warning">

														<i class="fa fa-clock-o mr-1"></i>Ödeme Bekleniyor

													</span>

												<?php elseif($tahsilat->durum == 2): ?>

													<span class="badge badge-success">

														<i class="fa fa-check mr-1"></i>Ödeme Alındı

													</span>

												<?php elseif($tahsilat->durum == 3): ?>

													<span class="badge badge-danger">

														<i class="fa fa-times mr-1"></i>İşlem Başarısız

													</span>

												<?php else: ?>

													<span class="badge badge-secondary">Bilinmiyor</span>

												<?php endif; ?>

											</td>

											<td>

												<?php 

												// Vade tarihini tahsilat tipine göre göster

												$vade_tarihi = null;

												if($tahsilat->tahsilat_tipi == 2 && isset($tahsilat->cek_vade_tarih) && $tahsilat->cek_vade_tarih) {

													// Çek vade tarihi

													$vade_tarihi = $tahsilat->cek_vade_tarih;

												} elseif($tahsilat->tahsilat_tipi == 4 && isset($tahsilat->senet_vade_tarih) && $tahsilat->senet_vade_tarih) {

													// Senet vade tarihi

													$vade_tarihi = $tahsilat->senet_vade_tarih;

												}

												

												if($vade_tarihi): ?>

													<strong><?= date('d.m.Y', strtotime($vade_tarihi)) ?></strong>

												<?php else: ?>

													<span class="text-muted">-</span>

												<?php endif; ?>

											</td>

											<td>

												<?php if($tahsilat->onay_yapan_personel): ?>

													<i class="fa fa-user-check text-success mr-1"></i>

													<?= $tahsilat->onay_yapan_personel ?>

												<?php else: ?>

													<span class="text-muted">-</span>

												<?php endif; ?>

											</td>

											<td>

												<?php if($tahsilat->islem_tarihi): ?>

													<strong><?= date('d.m.Y', strtotime($tahsilat->islem_tarihi)) ?></strong>

													<br><small class="text-muted"><?= date('H:i', strtotime($tahsilat->islem_tarihi)) ?></small>

												<?php else: ?>

													<span class="text-muted">-</span>

												<?php endif; ?>

											</td>

											<td>

												<?php 

												// Önce muhasebe_tahsilat_durum tablosundaki görsel alanını kontrol et

												$muhasebe_gorsel = null;

												if(isset($tahsilat->onay_gorsel) && !empty($tahsilat->onay_gorsel)) {

													$muhasebe_gorsel = $tahsilat->onay_gorsel;

												}

												

												// Ana tahsilat tablosundaki görseli de kontrol et

												$tahsilat_gorsel = null;

												if(isset($tahsilat->tahsilat_gorsel) && !empty($tahsilat->tahsilat_gorsel)) {

													$tahsilat_gorsel = $tahsilat->tahsilat_gorsel;

												}

												

												// Eğer muhasebe tablosunda görsel varsa onu kullan

												if($muhasebe_gorsel): ?>

													<button type="button" class="btn btn-sm btn-outline-success" 

															onclick="window.open('<?= base_url('assets/uploads/' . $muhasebe_gorsel) ?>', '_blank')"

															title="Onay Görseli">

														<i class="fa fa-image"></i> Onay Görseli

													</button>

												<?php elseif($tahsilat_gorsel): ?>

													<?php

													// Tahsilat tipine göre doğru klasör yolunu belirle

													$gorsel_url = '';

													if($tahsilat->tahsilat_tipi == 1) {

														$gorsel_url = base_url('assets/uploads/dekontlar/' . $tahsilat_gorsel);

													} elseif($tahsilat->tahsilat_tipi == 2) {

														$gorsel_url = base_url('assets/uploads/cekler/' . $tahsilat_gorsel);

													} elseif($tahsilat->tahsilat_tipi == 4) {

														$gorsel_url = base_url('assets/uploads/senetler/' . $tahsilat_gorsel);

													} else {

														$gorsel_url = base_url('assets/uploads/dekontlar/' . $tahsilat_gorsel);

													}

													?>

													<button type="button" class="btn btn-sm btn-outline-info" 

															onclick="window.open('<?= $gorsel_url ?>', '_blank')"

															title="Tahsilat Görseli">

														<i class="fa fa-image"></i> Tahsilat Görseli

													</button>

												<?php else: ?>

													<span class="text-muted">-</span>

												<?php endif; ?>

											</td>

											<td>

												<?php if($tahsilat->aciklama): ?>

													<?= substr($tahsilat->aciklama, 0, 30) ?>

													<?= strlen($tahsilat->aciklama) > 30 ? '...' : '' ?>

												<?php else: ?>

													<span class="text-muted">-</span>

												<?php endif; ?>

											</td>

											<td>

												<div class="btn-group" role="group">

													<?php if($tahsilat->durum == 1): // Sadece onay bekleyenler için ?>

													<button type="button" 

															class="btn btn-sm btn-onay tahsilat-onay-btn" 

															data-id="<?= $tahsilat->id ?>"

															data-cari="<?= $tahsilat->musteri_adi ?>"

															data-tutar="<?= $tahsilat->tutar ? number_format($tahsilat->tutar, 2, ',', '.') : '0,00' ?>"

															title="Onayla">

														<i class="fa fa-check"></i>

													</button>

													<button type="button" 

															class="btn btn-sm btn-red tahsilat-red-btn" 

															data-id="<?= $tahsilat->id ?>"

															data-cari="<?= $tahsilat->musteri_adi ?>"

															data-tutar="<?= $tahsilat->tutar ? number_format($tahsilat->tutar, 2, ',', '.') : '0,00' ?>"

															title="Reddet">

														<i class="fa fa-times"></i>

													</button>

													<?php elseif($tahsilat->durum == 2): // Onaylanmış tahsilatlar için geri alma butonu ?>
														<button type="button" 
																class="btn btn-sm btn-warning tahsilat-geri-al-btn" 
																data-id="<?= $tahsilat->id ?>"
																data-cari="<?= $tahsilat->musteri_adi ?>"
																data-tutar="<?= $tahsilat->tutar ? number_format($tahsilat->tutar, 2, ',', '.') : '0,00' ?>"
																title="Geri Al">
															<i class="fa fa-undo"></i> Geri Al
														</button>
													<?php elseif($tahsilat->durum == 3): // Reddedilmiş tahsilatlar ?>
														<span class="text-muted">İşlem Reddedildi</span>
													<?php else: ?>
														<span class="text-muted">İşlem Tamamlandı</span>
													<?php endif; ?>

													

													<a href="<?= base_url('muhasebe/tahsilat-detay/' . $tahsilat->id) ?>" 

													   class="btn btn-sm btn-outline-primary" 

													   title="Detay Görüntüle">

														<i class="fa fa-eye"></i>

													</a>

												</div>

											</td>

										</tr>

										<?php endforeach; ?>

									</tbody>

								</table>

							</div>

						<?php else: ?>

							<div class="text-center py-5">

								<i class="fa fa-list text-muted" style="font-size: 4rem; margin-bottom: 1rem;"></i>

								<h4>Henüz tahsilat kaydı bulunmuyor.</h4>

								<p class="text-muted">İlk tahsilat kaydını oluşturmak için aşağıdaki butonu kullanabilirsiniz.</p>

								<a href="<?= base_url('tahsilat') ?>" class="btn btn-primary">

									<i class="fa fa-plus"></i> Yeni Tahsilat Ekle

								</a>

							</div>

						<?php endif; ?>

					</div>

				</div>

			</div>

		</div>

	</div>

</div>



<!-- Onay Modalı -->

<div class="modal fade" id="onayModal" tabindex="-1" role="dialog">

	<div class="modal-dialog" role="document">

		<div class="modal-content">

			<div class="modal-header bg-success text-white">

				<h5 class="modal-title">

					<i class="fa fa-check-circle mr-2"></i>Tahsilat Onayı

				</h5>

				<button type="button" class="close text-white" data-dismiss="modal">

					<span>&times;</span>

				</button>

			</div>

			<form id="onayForm" method="post" enctype="multipart/form-data">

				<div class="modal-body">

					<div class="text-center">

						<i class="fa fa-question-circle text-warning" style="font-size: 3rem; margin-bottom: 1rem;"></i>

						<h5>Bu tahsilatı onaylamak istediğinizden emin misiniz?</h5>

						<div class="alert alert-info mt-3">

							<strong>Cari:</strong> <span id="onay-cari-ad"></span><br>

							<strong>Tutar:</strong> <span id="onay-tutar"></span> ₺

						</div>

						<p class="text-muted">

							Onaylanan tahsilatlar geri alınamaz ve muhasebe kayıtlarına işlenir.

						</p>

					</div>

					<!-- Görsel Yükleme Alanı -->

					<div class="form-group mt-4">

						<label for="tahsilat_gorsel">Görsel Yükle (İsteğe bağlı)</label>

						<input type="file" class="form-control-file" id="tahsilat_gorsel" name="tahsilat_gorsel" accept="image/*">

						<small class="form-text text-muted">Maksimum dosya boyutu: 5MB. Desteklenen formatlar: JPG, PNG, GIF</small>

					</div>

					

					<!-- Açıklama Alanı -->

					<div class="form-group">

						<label for="onay_aciklama">Açıklama (İsteğe bağlı)</label>

						<textarea class="form-control" id="onay_aciklama" name="aciklama" rows="3" placeholder="Onay açıklaması..."></textarea>

					</div>

				</div>

				<div class="modal-footer">

					<button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>

					<button type="submit" class="btn btn-success">

						<i class="fa fa-check mr-1"></i>Onayla

					</button>

				</div>

			</form>

		</div>

	</div>

</div>



<!-- Red Modalı -->

<div class="modal fade" id="redModal" tabindex="-1" role="dialog">

	<div class="modal-dialog" role="document">

		<div class="modal-content">

			<div class="modal-header bg-danger text-white">

				<h5 class="modal-title">

					<i class="fa fa-times-circle mr-2"></i>Tahsilat Reddi

				</h5>

				<button type="button" class="close text-white" data-dismiss="modal">

					<span>&times;</span>

				</button>

			</div>

			<div class="modal-body">

				<div class="text-center">

					<i class="fa fa-exclamation-triangle text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>

					<h5>Bu tahsilatı reddetmek istediğinizden emin misiniz?</h5>

					<div class="alert alert-warning mt-3">

						<strong>Cari:</strong> <span id="red-cari-ad"></span><br>

						<strong>Tutar:</strong> <span id="red-tutar"></span> ₺

					</div>

					<p class="text-muted">

						Reddedilen tahsilatlar muhasebe kayıtlarına işlenmez.

					</p>

				</div>

			</div>

			<div class="modal-footer">

				<button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>

				<a href="#" id="red-link" class="btn btn-danger">

					<i class="fa fa-times mr-1"></i>Reddet

				</a>

			</div>

		</div>

	</div>

</div>



<!-- Geri Alma Modalı -->
<div class="modal fade" id="geriAlModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-warning text-white">
				<h5 class="modal-title">
					<i class="fa fa-undo mr-2"></i>Tahsilat Geri Alma
				</h5>
				<button type="button" class="close text-white" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="text-center">
					<i class="fa fa-question-circle text-warning" style="font-size: 3rem; margin-bottom: 1rem;"></i>
					<h5>Bu tahsilat onayını geri almak istediğinizden emin misiniz?</h5>
					<div class="alert alert-info mt-3">
						<strong>Cari:</strong> <span id="geri-al-cari-ad"></span><br>
						<strong>Tutar:</strong> <span id="geri-al-tutar"></span> ₺
					</div>
					<div class="alert alert-warning">
						<strong><i class="fa fa-exclamation-triangle mr-2"></i>Dikkat!</strong><br>
						Bu işlem tahsilatı tekrar <strong>"Ödeme Bekleniyor"</strong> durumuna getirecektir.<br>
						Onay görseli ve onay bilgileri silinecektir.
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
				<a href="#" id="geri-al-link" class="btn btn-warning">
					<i class="fa fa-undo mr-1"></i>Geri Al
				</a>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view("include/footer-js"); ?>



<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/js/dataTables.bootstrap4.min.js"></script>



<script>

$(document).ready(function() {

	// DataTable başlat

	$('#tahsilatlarTable').DataTable({

		"language": {

			"url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json"

		},

		"pageLength": 25,

		"order": [[ 0, "desc" ]],

		"columnDefs": [

			{ "orderable": false, "targets": [4, 9, 11] } // Detaylar, Görsel ve İşlemler sütunları sıralanamaz

		]

	});

	

	// Onay butonları

	$('.tahsilat-onay-btn').click(function() {

		var tahsilat_id = $(this).data('id');

		var cari_ad = $(this).data('cari');

		var tutar = $(this).data('tutar');

		

		$('#onay-cari-ad').text(cari_ad);

		$('#onay-tutar').text(tutar);

		$('#onayForm').attr('action', '<?= base_url("muhasebe/tahsilat-onay/") ?>' + tahsilat_id);

		

		$('#onayModal').modal('show');

	});

	

	// Red butonları

	$('.tahsilat-red-btn').click(function() {

		var tahsilat_id = $(this).data('id');

		var cari_ad = $(this).data('cari');

		var tutar = $(this).data('tutar');

		

		$('#red-cari-ad').text(cari_ad);

		$('#red-tutar').text(tutar);

		$('#red-link').attr('href', '<?= base_url("muhasebe/tahsilat-red/") ?>' + tahsilat_id);

		

		$('#redModal').modal('show');

	});

	

	// Geri alma butonları

	$('.tahsilat-geri-al-btn').click(function() {

		var tahsilat_id = $(this).data('id');

		var cari_ad = $(this).data('cari');

		var tutar = $(this).data('tutar');

		

		$('#geri-al-cari-ad').text(cari_ad);

		$('#geri-al-tutar').text(tutar);

		$('#geri-al-link').attr('href', '<?= base_url("muhasebe/tahsilat-geri-al/") ?>' + tahsilat_id);

		

		$('#geriAlModal').modal('show');

	});

});



// Filtreleri temizleme fonksiyonu

function filtreleriTemizle() {

	// Tüm select ve input elementlerini sıfırla

	$('#tahsilat_tipi').val('');

	$('#durum').val('');

	$('#personel').val('');

	$('#baslangic_tarih').val('');

	$('#bitis_tarih').val('');

	

	// Formu gönder

	$('#filtreForm').submit();

}

</script>



<?php if($this->session->flashdata('tahsilat_onay_ok')): ?>

<script>

toastr.success('Tahsilat başarıyla onaylandı!', 'Başarılı');

</script>

<?php endif; ?>



<?php if($this->session->flashdata('tahsilat_red_ok')): ?>

<script>

toastr.success('Tahsilat başarıyla reddedildi!', 'Başarılı');

</script>

<?php endif; ?>



<?php if($this->session->flashdata('tahsilat_onay_hata')): ?>

<script>

toastr.error('Tahsilat onaylama işlemi sırasında hata oluştu!', 'Hata');

</script>

<?php endif; ?>



<?php if($this->session->flashdata('tahsilat_red_hata')): ?>

<script>

toastr.error('Tahsilat reddetme işlemi sırasında hata oluştu!', 'Hata');

</script>

<?php endif; ?>



<?php if($this->session->flashdata('tahsilat_geri_al_ok')): ?>

<script>

toastr.success('<?= $this->session->flashdata('tahsilat_geri_al_ok') ?>', 'Başarılı');

</script>

<?php endif; ?>



<?php if($this->session->flashdata('tahsilat_geri_al_hata')): ?>

<script>

toastr.error('<?= $this->session->flashdata('tahsilat_geri_al_hata') ?>', 'Hata');

</script>

<?php endif; ?>

<script>
$(document).ready(function() {
    // Tahsilat Tipi Dropdown + Checkbox İşlemleri
    
    // Dropdown menüsünün checkbox'a tıklandığında kapanmaması için
    $('.tahsilat-tipi-dropdown .dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });
    
    // Checkbox değişikliklerini dinle ve label'ı güncelle
    $('.tahsilat-tipi-checkbox').on('change', function() {
        updateTahsilatTipiLabel();
    });
    
    // Sayfa yüklendiğinde label'ı güncelle
    updateTahsilatTipiLabel();
    
    // Filtreleri Temizle fonksiyonu güncelleme
    window.filtreleriTemizle = function() {
        $('.tahsilat-tipi-checkbox').prop('checked', false);
        $('#durum').val('');
        $('#personel').val('');
        $('#tahsilat_ayi').val('');
        $('#filtreForm')[0].reset();
        updateTahsilatTipiLabel();
        
        // URL'yi temizle ve sayfayı yenile
        window.location.href = window.location.pathname;
    };
});

// Tahsilat Tipi Label'ını Güncelle
function updateTahsilatTipiLabel() {
    var checkedBoxes = $('.tahsilat-tipi-checkbox:checked');
    var labelText = '';
    var labelElement = $('#tahsilatTipiLabel');
    
    if (checkedBoxes.length === 0) {
        labelText = 'Tahsilat tipini seçiniz...';
        labelElement.removeClass('text-primary font-weight-bold');
    } else {
        var selectedTypes = [];
        checkedBoxes.each(function() {
            var value = $(this).val();
            var text = $(this).next('label').text().trim();
            selectedTypes.push(text);
        });
        
        if (checkedBoxes.length === 1) {
            labelText = selectedTypes[0];
        } else if (checkedBoxes.length <= 2) {
            labelText = selectedTypes.join(', ');
        } else {
            labelText = selectedTypes[0] + ' ve ' + (checkedBoxes.length - 1) + ' diğer';
        }
        
        labelElement.addClass('text-primary font-weight-bold');
    }
    
    labelElement.text(labelText);
}

// Tümünü Seç
function tahsilatTipiTumunuSec() {
    $('.tahsilat-tipi-checkbox').prop('checked', true);
    updateTahsilatTipiLabel();
}

// Tümünü Temizle
function tahsilatTipiTumunuTemizle() {
    $('.tahsilat-tipi-checkbox').prop('checked', false);
    updateTahsilatTipiLabel();
}
</script>

</body>

</html>

