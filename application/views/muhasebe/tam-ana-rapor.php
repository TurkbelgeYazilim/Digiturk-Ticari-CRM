<!DOCTYPE html>
<html lang="tr">
<head>
	<?php $this->load->view("include/head-tags"); ?>
	<title>Tam Ana Rapor | İlekaSoft CRM</title>
	<link rel="icon" type="image/x-icon" href="<?= base_url('assets/favicon.ico'); ?>" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/css/dataTables.bootstrap4.min.css"/>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css"/>
	<!-- Select2 CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"/>
	<!-- SweetAlert2 CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	
	<style>
		.rapor-onay-bekliyor {
			background-color: #fff3cd;
			border-left: 4px solid #ffc107;
		}
		
		.rapor-onaylanan {
			background-color: #d4edda;
			border-left: 4px solid #28a745;
		}
		
		.rapor-reddedilen {
			background-color: #f8d7da;
			border-left: 4px solid #dc3545;
		}
		
		.rapor-aktivasyon-aktif {
			background-color: #d1ecf1;
			border-left: 4px solid #17a2b8;
		}
		
		.tutar-buyuk {
			font-size: 1.2em;
			font-weight: bold;
			color: #28a745;
		}
		
		/* Satış sözleşme tutarı link stili */
		.tutar-buyuk.text-decoration-none {
			transition: all 0.3s ease;
		}
		
		.tutar-buyuk.text-decoration-none:hover {
			color: #1e7e34 !important;
			text-decoration: none !important;
			transform: scale(1.05);
		}
		
		.tutar-buyuk .fa-external-link {
			opacity: 0.7;
			transition: opacity 0.3s ease;
		}
		
		.tutar-buyuk:hover .fa-external-link {
			opacity: 1;
		}
		
		/* İşletme link stili */
		.isletme-link {
			color: #007bff;
			text-decoration: none;
			transition: all 0.3s ease;
		}
		
		.isletme-link:hover {
			color: #0056b3;
			text-decoration: none;
		}
		
		.isletme-link .fa-external-link-alt {
			opacity: 0.6;
			transition: opacity 0.3s ease;
		}
		
		.isletme-link:hover .fa-external-link-alt {
			opacity: 1;
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
		
		/* Select2 Stok Dropdown Özelleştirmeleri */
		.select2-container--bootstrap-5 .select2-selection {
			border: 1px solid #ced4da !important;
			border-radius: 0.375rem !important;
			min-height: calc(1.5em + 0.75rem + 2px) !important;
		}
		
		.select2-container--bootstrap-5 .select2-selection--single {
			line-height: calc(1.5em + 0.75rem) !important;
		}
		
		.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
			padding-left: 0.75rem !important;
			padding-right: 2rem !important;
			color: #495057 !important;
		}
		
		.select2-container--bootstrap-5 .select2-dropdown {
			border-color: #ced4da !important;
		}
		
		.select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
			background-color: #007bff !important;
			color: #fff !important;
		}
		
		.select2-container--bootstrap-5.select2-container--focus .select2-selection {
			border-color: #80bdff !important;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
		}
		
		
		/* Satış detayları akordiyon stilleri */
		.satis-detaylar {
			background-color: #f8f9fa;
			border: 1px solid #dee2e6;
			border-radius: 0.375rem;
			padding: 0.75rem;
			margin-top: 0.5rem;
		}
		
		.satis-card {
			border: 1px solid #e3f2fd;
			background-color: #fafafa;
			transition: box-shadow 0.2s ease;
		}
		
		.satis-card:hover {
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}
		
		.satis-tutar-container,
		.satis-hizmet-container {
			position: relative;
		}
		
		.satis-detaylar-liste {
			max-height: 300px;
			overflow-y: auto;
		}
		
		/* Akordiyon butonu stilleri */
		.btn-outline-primary:focus,
		.btn-outline-info:focus {
			box-shadow: none;
		}
		
		/* Scroll bar özelleştirmesi */
		.satis-detaylar-liste::-webkit-scrollbar {
			width: 6px;
		}
		
		.satis-detaylar-liste::-webkit-scrollbar-track {
			background: #f1f1f1;
			border-radius: 3px;
		}
		
		.satis-detaylar-liste::-webkit-scrollbar-thumb {
			background: #c1c1c1;
			border-radius: 3px;
		}
		
		.satis-detaylar-liste::-webkit-scrollbar-thumb:hover {
			background: #a8a8a8;
		}
		
		/* Satış detay kartları için özel stiller */
		.satis-card {
			border: 1px solid #e3e6f0;
			border-radius: 5px;
			background: #fff;
			box-shadow: 0 1px 3px rgba(0,0,0,0.12);
		}
		
		.satis-card .card-body {
			padding: 12px !important;
		}
		
		.satis-detaylar {
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			border-radius: 5px;
			padding: 10px;
			margin-top: 8px;
		}
		
		.stats-number {
			font-size: 2rem;
			font-weight: bold;
			margin-bottom: 0.5rem;
		}
		
		.stats-label {
			font-size: 0.9rem;
			opacity: 0.9;
		}
		
		/* Görsel ve Evrak kolonları için stil */
		.gorsel-column, .evrak-column {
			width: 80px;
			text-align: center;
		}
		
		.gorsel-thumbnail {
			width: 50px;
			height: 50px;
			object-fit: cover;
			border-radius: 5px;
			cursor: pointer;
			border: 2px solid #e0e0e0;
			transition: all 0.3s ease;
		}
		
		.gorsel-thumbnail:hover {
			border-color: #007bff;
			transform: scale(1.1);
		}
		
		.badge-overlay {
			position: relative;
			display: inline-block;
		}
		
		.badge-overlay .badge {
			position: absolute;
			top: -5px;
			right: -5px;
			font-size: 10px;
			min-width: 18px;
			height: 18px;
			line-height: 18px;
			border-radius: 9px;
		}
		
		.evrak-btn {
			min-width: 50px;
		}
		
		.gorsel-thumb {
			width: 60px;
			height: 60px;
			object-fit: cover;
			border-radius: 4px;
			cursor: pointer;
			margin: 2px;
		}
		
		.gorsel-container {
			display: flex;
			flex-wrap: wrap;
			gap: 5px;
			align-items: center;
		}
		
		.btn-gorsel {
			background: none;
			border: none;
			padding: 0;
		}
		
		.btn-gorsel:hover .gorsel-thumb {
			opacity: 0.8;
			transform: scale(1.05);
			transition: all 0.3s;
		}
		
		/* Görsel yok - yükleme butonu stilleri */
		.btn-outline-secondary.gorsel-btn {
			border: 1px dashed #6c757d;
			background-color: transparent;
			transition: all 0.3s ease;
		}
		
		.btn-outline-secondary.gorsel-btn:hover {
			background-color: #6c757d;
			border-color: #6c757d;
			color: white;
			transform: scale(1.05);
		}
		
		.btn-outline-secondary.gorsel-btn:hover .text-muted {
			color: white !important;
		}
		
		/* Tahsilat görselleri için küçük butonlar */
		.btn-sm.btn-outline-success {
			min-width: 50px;
			font-size: 0.85rem;
		}
		
		.btn-sm.btn-outline-warning {
			min-width: 50px;
			font-size: 0.85rem;
		}
		
		.btn-sm.btn-outline-danger {
			min-width: 50px;
			font-size: 0.85rem;
		}
		
		/* Bootstrap margin sınıfları (eksikse) */
		.me-1 {
			margin-right: 0.25rem !important;
		}
		
		.mb-1 {
			margin-bottom: 0.25rem !important;
		}
		
		.modal-body img {
			max-width: 100%;
			height: auto;
		}
		
		.status-badge {
			font-size: 0.85em;
			padding: 5px 10px;
		}
		
		.badge-sm {
			font-size: 0.7em;
			padding: 2px 6px;
		}
		
		[data-toggle="tooltip"] {
			cursor: help;
		}
		
		.aktivasyon-durum-container {
			text-align: center;
			min-width: 100px;
		}
		
		.aktivasyon-durum-container .status-badge {
			display: inline-block;
			min-width: 80px;
			font-weight: bold;
		}
		
		.aktivasyon-durum-container small {
			opacity: 0.7;
		}
		
		/* Tutar düzenleme stilleri */
		.tutar-input {
			border: 1px solid #ced4da !important;
			transition: all 0.3s ease;
		}
		
		.tutar-input:focus {
			border-color: #007bff !important;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
		}
		
		.tutar-degisti {
			background-color: #fff3cd !important;
			border-left: 4px solid #ffc107 !important;
		}
		
		.tutar-degisti .tutar-input {
			border-color: #ffc107 !important;
			background-color: #fffbee !important;
		}
		
		.vade-tarih-degisti {
			background-color: #e7f3ff !important;
			border-left: 4px solid #007bff !important;
		}
		
		.vade-tarih-degisti .vade-tarih-input {
			border-color: #007bff !important;
			background-color: #f0f8ff !important;
		}
		
		.input-group-sm {
			max-width: 150px;
		}
		
		#kaydetTahsilat {
			animation: pulse 2s infinite;
		}
		
		@keyframes pulse {
			0% { transform: scale(1); }
			50% { transform: scale(1.05); }
			100% { transform: scale(1); }
		}
		
		/* Modal ve tablo iyileştirmeleri */
		#stokBilgisiModal .modal-xl {
			max-width: 95%;
		}
		
		#stokBilgisiModal .table th,
		#stokBilgisiModal .table td {
			padding: 0.5rem;
			vertical-align: middle;
			font-size: 0.9rem;
		}
		
		#stokBilgisiModal .select2-container {
			width: 100% !important;
		}
		
		#stokBilgisiModal .select2-selection--single {
			height: calc(1.5em + 0.75rem + 2px);
			padding: 0.375rem 0.75rem;
			font-size: 0.9rem;
		}
		
		#stokBilgisiModal .select2-selection__rendered {
			padding-left: 0;
			padding-right: 0;
		}
		
		#stokBilgisiModal .form-control {
			font-size: 0.9rem;
			padding: 0.375rem 0.5rem;
		}
		
		#stokBilgisiModal .btn-sm {
			padding: 0.25rem 0.5rem;
			font-size: 0.8rem;
		}
		
		/* Hizmet Adı ve Tahsilat Türü sütunlarının genişliklerini eşitle ve tüm sütunları hizala */
		#stokBilgisiModal #stokTable th:nth-child(1),
		#stokBilgisiModal #tahsilatDetayTable th:nth-child(1) {
			width: 5% !important;
		}
		
		#stokBilgisiModal #stokTable th:nth-child(2),
		#stokBilgisiModal #tahsilatDetayTable th:nth-child(2) {
			width: 60% !important;
		}
		
		#stokBilgisiModal #stokTable th:nth-child(4),
		#stokBilgisiModal #tahsilatDetayTable th:nth-child(3) {
			width: 15% !important;
		}
		
		#stokBilgisiModal #stokTable th:nth-child(6),
		#stokBilgisiModal #tahsilatDetayTable th:nth-child(4) {
			width: 15% !important;
		}
		
		#stokBilgisiModal #stokTable th:nth-child(7),
		#stokBilgisiModal #tahsilatDetayTable th:nth-child(5) {
			width: 5% !important;
		}
		
		/* TD'ler için de aynı kurallar */
		#stokBilgisiModal #stokTable td:nth-child(1),
		#stokBilgisiModal #tahsilatDetayTable td:nth-child(1) {
			width: 5% !important;
		}
		
		#stokBilgisiModal #stokTable td:nth-child(2),
		#stokBilgisiModal #tahsilatDetayTable td:nth-child(2) {
			width: 60% !important;
		}
		
		#stokBilgisiModal #stokTable td:nth-child(4),
		#stokBilgisiModal #tahsilatDetayTable td:nth-child(3) {
			width: 15% !important;
		}
		
		#stokBilgisiModal #stokTable td:nth-child(6),
		#stokBilgisiModal #tahsilatDetayTable td:nth-child(4) {
			width: 15% !important;
		}
		
		#stokBilgisiModal #stokTable td:nth-child(7),
		#stokBilgisiModal #tahsilatDetayTable td:nth-child(5) {
			width: 5% !important;
		}
		
		/* Satış Sözleşmesi Modal Styles */
		#satisSozlesmeModal .modal-body {
			max-height: 70vh;
			overflow-y: auto;
		}
		
		#satisSozlesmeModal .table-responsive {
			max-height: 300px;
			overflow-y: auto;
		}
		
		#satisSozlesmeModal .form-control {
			font-size: 0.875rem;
		}
		
		#satisSozlesmeModal .stok-autocomplete {
			width: 100%;
		}
		
		/* Select2 Modal içinde özel stiller */
		#satisSozlesmeModal .select2-container {
			width: 100% !important;
		}
		
		#satisSozlesmeModal .select2-selection--single {
			height: calc(1.5em + 0.75rem + 2px) !important;
			border: 1px solid #ced4da !important;
			border-radius: 0.25rem !important;
		}
		
		#satisSozlesmeModal .select2-selection__rendered {
			line-height: calc(1.5em + 0.75rem) !important;
			padding-left: 0.75rem !important;
			color: #495057 !important;
		}
		
		#satisSozlesmeModal .select2-selection__arrow {
			height: calc(1.5em + 0.75rem) !important;
		}
		
		/* Select2 dropdown z-index modalda */
		.select2-dropdown {
			z-index: 9999 !important;
		}
		
		/* KDV breakdown bilgisini gizle */
		.kdv-breakdown {
			display: none !important;
		}
		
		/* Miktar input genişliği */
		.miktar {
			max-width: 80px !important;
		}
		
		/* Birim fiyat input genişliği */
		.birimFiyat {
			max-width: 120px !important;
		}
		
		/* KDV dropdown genişliği */
		.kdvOrani {
			max-width: 80px !important;
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
						<h3 class="page-title">Tam Ana Rapor</h3>
						<ul class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>
							<li class="breadcrumb-item">Muhasebe</li>
							<li class="breadcrumb-item active">Tam Ana Rapor</li>
						</ul>
					</div>
					<div class="d-flex justify-content-end text-align-center col-sm-2">
						<a class="btn btn-outline-light" href="javascript:history.back()">
							<i class="fa fa-history"></i> <br>Önceki Sayfa
						</a>
					</div>
				</div>
			</div>
			<!-- /Page Header -->

			<!-- İstatistikler -->
			<div class="row mb-4">
				<div class="col-xl-3 col-sm-6 col-12">
					<div class="card card-stats">
						<div class="card-body">
							<div class="stats-number">
								<?= isset($toplam_kayit) ? $toplam_kayit : 0 ?>
							</div>
							<div class="stats-label">
								<i class="fa fa-list mr-2"></i>Toplam Kayıt Sayısı
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-sm-6 col-12">
					<div class="card card-stats card-warning">
						<div class="card-body">
							<div class="stats-number">
								<?= isset($onay_bekleyen_sayisi) ? $onay_bekleyen_sayisi : 0 ?>
							</div>
							<div class="stats-label">
								<i class="fa fa-clock-o mr-2"></i>Aktivasyon Bekleyen
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-sm-6 col-12">
					<div class="card card-stats card-success">
						<div class="card-body">
							<div class="stats-number">
								<?= isset($onaylanan_sayisi) ? $onaylanan_sayisi : 0 ?>
							</div>
							<div class="stats-label">
								<i class="fa fa-check mr-2"></i>Aktivasyon Tamamlanan
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 col-sm-6 col-12">
					<div class="card card-stats card-danger">
						<div class="card-body">
							<div class="stats-number">
								<?= number_format($toplam_satis_tutar ?? 0, 0, ',', '.') ?> ₺
							</div>
							<div class="stats-label">
								<i class="fa fa-money mr-2"></i>Toplam Satış
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Toplam Tutarlar -->
			<div class="row mb-4">
				<div class="col-md-6">
					<div class="card">
						<div class="card-body text-center">
							<h5 class="card-title">Toplam Satış Tutarı (KDV Dahil)</h5>
							<h3 class="text-primary">
								<i class="fa fa-shopping-cart mr-2"></i>
								<?= number_format($toplam_satis_tutar ?? 0, 0, ',', '.') ?> ₺
							</h3>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="card-body text-center">
							<h5 class="card-title">Toplam Tahsilat Tutarı</h5>
							<h3 class="text-success">
								<i class="fa fa-money mr-2"></i>
								<?= number_format($toplam_tahsilat_tutar ?? 0, 0, ',', '.') ?> ₺
							</h3>
						</div>
					</div>
				</div>
			</div>

			<!-- Rapor Listesi -->
			<div class="row">
				<div class="col-xl-12 col-md-12">
					<div class="card card-table">
						<div class="card-header">
							<div class="row">
								<div class="col">
									<h4 class="card-title">
										<i class="fa fa-chart-line text-primary mr-2"></i>
										Tam Ana Rapor
									</h4>
									<p class="text-muted mb-0">
										<small>Cari, Satış Sözleşmesi, Tahsilat ve Aktivasyon bilgileri</small>
									</p>
								</div>
								<div class="col-auto">
									<button type="button" class="btn btn-primary btn-sm" onclick="location.reload()">
										<i class="fa fa-refresh"></i> Yenile
									</button>
									<button type="button" class="btn btn-success btn-sm" onclick="exportToExcel()">
										<i class="fa fa-file-excel-o"></i> Excel
									</button>
								</div>
							</div>
						</div>

						<?php if (isset($error_message)): ?>
							<div class="alert alert-warning m-3">
								<div class="row">
									<div class="col-md-2 text-center">
										<i class="fa fa-database" style="font-size: 3rem; color: #ffc107;"></i>
									</div>
									<div class="col-md-10">
										<h5><i class="fa fa-exclamation-triangle mr-2"></i>Hata Oluştu</h5>
										<p><?= $error_message ?></p>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($this->session->flashdata('excel_hata')): ?>
							<div class="alert alert-danger m-3">
								<div class="row">
									<div class="col-md-2 text-center">
										<i class="fa fa-file-excel-o" style="font-size: 3rem; color: #dc3545;"></i>
									</div>
									<div class="col-md-10">
										<h5><i class="fa fa-exclamation-triangle mr-2"></i>Excel Export Hatası</h5>
										<p><?= $this->session->flashdata('excel_hata') ?></p>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<?php if (!empty($rapor_verileri)): ?>
							<div class="card-body">
								<!-- Arama Formu -->
								<div class="row mb-3">
									<div class="col-12">
										<div class="card bg-light">
											<div class="card-body">
												<h6 class="card-title mb-3">
													<i class="fa fa-search text-primary mr-2"></i>Cari Arama
													<small class="text-muted">(Tek arama kutusu - Tüm arama işlemleri buradan yapılır)</small>
												</h6>
												<form method="GET" action="<?= current_url() ?>" id="searchForm">
													<div class="row">
														<div class="col-md-6">
															<div class="form-group">
																<label for="cari_search">Cari Arama</label>
																<input type="text" 
																	   class="form-control" 
																	   id="cari_search" 
																	   name="cari_search" 
																	   placeholder="Cari arama: Ad, Soyad, Vergi No, Telefon veya TC Kimlik No ile arayın..."
																	   value="<?= htmlspecialchars($this->input->get('cari_search') ?? '') ?>"
																	   autocomplete="off"
																	   title="Bu tek arama kutusu tüm cari bilgilerini arar">
																<small class="form-text text-muted">
																	<i class="fa fa-info-circle text-primary"></i> <strong>Tek Arama Sistemi:</strong> 
																	Bu kutucuk tüm cari kayıtlarında arama yapar.
																	<br><strong>Arama yapılan alanlar:</strong> cari_ad, cari_soyad, cari_vergiNumarasi, cari_firmaTelefon, cari_tckn
																	<br><small class="text-success">✓ Otomatik tamamlama aktif (2+ karakter yazın)</small>
																</small>
															</div>
														</div>
														<div class="col-md-2">
															<div class="form-group">
																<label for="aktivasyon_durum">Aktivasyon Durum</label>
																<select class="form-control" id="aktivasyon_durum" name="aktivasyon_durum">
																	<option value="">Tümü</option>
																	<option value="Aktif" <?= ($this->input->get('aktivasyon_durum') == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
																	<option value="Beklemede" <?= ($this->input->get('aktivasyon_durum') == 'Beklemede') ? 'selected' : '' ?>>Beklemede</option>
																	<option value="İşlemde" <?= ($this->input->get('aktivasyon_durum') == 'İşlemde') ? 'selected' : '' ?>>İşlemde</option>
																	<option value="Tamamlandı" <?= ($this->input->get('aktivasyon_durum') == 'Tamamlandı') ? 'selected' : '' ?>>Tamamlandı</option>
																	<option value="İptal" <?= ($this->input->get('aktivasyon_durum') == 'İptal') ? 'selected' : '' ?>>İptal</option>
																	<option value="Reddedildi" <?= ($this->input->get('aktivasyon_durum') == 'Reddedildi') ? 'selected' : '' ?>>Reddedildi</option>
																</select>
															</div>
														</div>
														<div class="col-md-2">
															<div class="form-group">
																<label for="hizmet_onay_durumu">Hizmet Onay Durumu</label>
																<select class="form-control" id="hizmet_onay_durumu" name="hizmet_onay_durumu">
																	<option value="">Tümü</option>
																	<option value="onaylanan" <?= ($this->input->get('hizmet_onay_durumu') == 'onaylanan') ? 'selected' : '' ?>>Onaylananlar</option>
																	<option value="onaylanmayan" <?= ($this->input->get('hizmet_onay_durumu') == 'onaylanmayan') ? 'selected' : '' ?>>Onaylanmayanlar</option>
																</select>
															</div>
														</div>
														<div class="col-md-2">
															<div class="form-group">
																<label>&nbsp;</label>
																<div class="d-flex">
																	<button type="submit" class="btn btn-primary mr-2">
																		<i class="fa fa-search"></i> Ara
																	</button>
																	<a href="<?= current_url() ?>" class="btn btn-secondary">
																		<i class="fa fa-refresh"></i> Temizle
																	</a>
																</div>
															</div>
														</div>
													</div>
												</form>
											</div>
										</div>
									</div>
								</div>
								<!-- /Arama Formu -->
								
								<div class="table-responsive">
									<table class="table table-hover table-center mb-0" id="raporTable">
										<thead>
											<tr>
												<th>Tarih</th>
												<th>İşletme</th>
												<th class="gorsel-column">İşletme Görsel</th>
												<th class="evrak-column">Evrak</th>
												<th>Satış Sözleşme Tutarı</th>
												<th>Satış Sözleşme Hizmeti</th>
												<th class="gorsel-column">Satış Görselleri</th>
												<th>Tahsilat Tutar</th>
												<th>Tahsilat Görselleri</th>
												<th>Aktivasyon Durum</th>
												<th class="text-center">Aktivasyon Tarihi</th>
												<th>Personel</th>
												<th width="120">İşlemler</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($rapor_verileri as $veri): ?>
												<?php
												$row_class = '';
												switch($veri->aktivasyon_durum) {
													case 'Aktif': 
													case 'Tamamlandı': $row_class = 'rapor-onaylanan'; break;
													case 'Beklemede': 
													case 'İşlemde': $row_class = 'rapor-onay-bekliyor'; break;
													case 'İptal': 
													case 'Reddedildi': $row_class = 'rapor-reddedilen'; break;
													default: $row_class = 'rapor-onay-bekliyor'; break;
												}
												?>
												<tr class="<?= $row_class ?>">
													<td data-order="<?= $veri->kayit_tarihi ? strtotime($veri->kayit_tarihi) : 0 ?>"><?= $veri->kayit_tarihi ? date('d.m.Y', strtotime($veri->kayit_tarihi)) : '-' ?></td>
													<td>
														<?php if (isset($veri->cari_id) && !empty($veri->cari_id)): ?>
															<a href="<?= base_url('cari/cari-karti-duzenle/' . $veri->cari_id) ?>" 
															   target="_blank" 
															   class="isletme-link"
															   title="Cari kartı düzenle">
																<?= htmlspecialchars($veri->cari_isletme ?? '-') ?>
																<i class="fa fa-external-link-alt fa-sm ml-1"></i>
															</a>
														<?php else: ?>
															<?= htmlspecialchars($veri->cari_isletme ?? '-') ?>
														<?php endif; ?>
													</td>
													<td class="gorsel-column">
														<?php 
														// İşletme görselleri için cari_id kullanarak sayı göster
														if (isset($veri->cari_id) && !empty($veri->cari_id)): 
															// Gerçek görsel sayısını almak için bir fonksiyon çağrılabilir
															// Şimdilik varsayılan olarak 1 gösterelim, controller'dan gelmeli
															$isletme_gorsel_sayisi = isset($veri->isletme_gorsel_sayisi) ? $veri->isletme_gorsel_sayisi : 0;
															if ($isletme_gorsel_sayisi > 0):
														?>
															<button 
																class="btn btn-sm btn-outline-success gorsel-btn"
																title="<?= $isletme_gorsel_sayisi ?> görsel"
																onclick="window.open('<?= base_url("cari/gorselGoster/" . $veri->cari_id) ?>', 'gorselPencere', 'width=800,height=600,scrollbars=yes,resizable=yes')">
																<i class="fa fa-image"></i> <?= $isletme_gorsel_sayisi ?>
															</button>
														<?php else: ?>
															<button 
																class="btn btn-sm btn-outline-secondary gorsel-btn"
																title="İşletme görseli yok - Görselleri görüntülemek ve yüklemek için tıklayın"
																onclick="window.open('<?= base_url("cari/gorselGoster/" . $veri->cari_id) ?>', 'gorselPencere', 'width=1000,height=700,scrollbars=yes,resizable=yes')">
																<i class="fa fa-image text-muted mr-1"></i><i class="fa fa-plus" style="font-size: 10px; margin-left: -2px;"></i>
															</button>
														<?php 
															endif;
														else: 
														?>
															<span class="text-muted">-</span>
														<?php endif; ?>
													</td>
													<td class="evrak-column">
														<?php 
														// Evrak dosyaları için cari_id kullanarak sayı göster
														if (isset($veri->cari_id) && !empty($veri->cari_id)): 
															// Gerçek evrak sayısını almak için bir fonksiyon çağrılabilir
															// Controller'dan evrak sayısı gelmeli
															$evrak_sayisi = isset($veri->evrak_sayisi) ? $veri->evrak_sayisi : 0;
															if ($evrak_sayisi > 0):
														?>
															<button 
																class="btn btn-sm btn-outline-primary evrak-btn" 
																title="<?= $evrak_sayisi ?> evrak"
																onclick="window.open('<?= base_url("cari/evrakGoster/" . $veri->cari_id) ?>', 'evrakPencere', 'width=800,height=600,scrollbars=yes,resizable=yes')">
																<i class="fa fa-file"></i> <?= $evrak_sayisi ?>
															</button>
														<?php else: ?>
															<i class="fa fa-file text-muted" title="Evrak yok"></i>
														<?php 
															endif;
														else: 
														?>
															<span class="text-muted">-</span>
														<?php endif; ?>
													</td>
													<td data-order="<?= $veri->satis_sozlesme_tutar ? floatval($veri->satis_sozlesme_tutar) : -1 ?>">
														<?php if ($veri->satis_sozlesme_tutar): ?>
															<div class="satis-tutar-container">
																<span class="tutar-buyuk"><?= number_format($veri->satis_sozlesme_tutar, 0, ',', '.') ?> ₺</span>
																<?php if (isset($veri->toplam_satis_sayisi) && $veri->toplam_satis_sayisi > 1): ?>
																	<button class="btn btn-sm btn-outline-primary ml-2" 
																			onclick="toggleSatisDetaylar(<?= $veri->cari_id ?>)"
																			id="toggle-btn-<?= $veri->cari_id ?>"
																			title="<?= $veri->toplam_satis_sayisi ?> satış var - detayları göster/gizle">
																		<i class="fa fa-plus"></i> <?= $veri->toplam_satis_sayisi ?>
																	</button>
																	<!-- Akordiyon içerik alanı (başlangıçta gizli) -->
																	<div id="satis-detaylar-<?= $veri->cari_id ?>" class="satis-detaylar mt-2" style="display: none;">
																		<div class="text-center">
																			<i class="fa fa-spinner fa-spin text-primary"></i>
																			<span class="ml-1">Satışlar yükleniyor...</span>
																		</div>
																	</div>
																<?php endif; ?>
															</div>
														<?php else: ?>
															<span class="text-muted">-</span>
														<?php endif; ?>
													</td>
													<td>
														<?php if (isset($veri->satis_sozlesme_id) && !empty($veri->satis_sozlesme_id)): ?>
															<div class="satis-hizmet-container">
																<button class="btn btn-sm btn-success"
																		onclick="openStokBilgisiModal(<?= $veri->satis_sozlesme_id ?>, <?= $veri->cari_id ?>, '<?= htmlspecialchars($veri->cari_isletme, ENT_QUOTES) ?>')"
																		title="Stok bilgilerini görüntüle ve düzenle (ID: <?= $veri->satis_sozlesme_id ?>)">
																	<?php if (isset($veri->satis_sozlesme_hizmeti) && !empty($veri->satis_sozlesme_hizmeti)): ?>
																		<i class="fa fa-box mr-1"></i>
																		<?= htmlspecialchars($veri->satis_sozlesme_hizmeti) ?>
																	<?php else: ?>
																		<i class="fa fa-plus mr-1"></i>
																		Stok Bilgileri
																	<?php endif; ?>
																</button>
																<?php if (isset($veri->toplam_satis_sayisi) && $veri->toplam_satis_sayisi > 1): ?>
																	<br>
																	<button class="btn btn-sm btn-outline-info mt-1"
																			onclick="toggleSatisDetay2(<?= $veri->cari_id ?>)"
																			id="toggle-btn2-<?= $veri->cari_id ?>"
																			title="Bu cariye ait tüm hizmetleri göster/gizle">
																		<i class="fa fa-list"></i> Tümü
																	</button>
																	<!-- Satış detayları için div -->
																	<div id="satis-detaylar2-<?= $veri->cari_id ?>" class="satis-detaylar mt-2" style="display: none;">
																		<div class="text-center">
																			<i class="fa fa-spinner fa-spin text-primary"></i>
																			<span class="ml-1">Satışlar yükleniyor...</span>
																		</div>
																	</div>
																<?php endif; ?>
															</div>
														<?php else: ?>
															<?php if (isset($veri->satis_sozlesme_hizmeti) && !empty($veri->satis_sozlesme_hizmeti)): ?>
																<span class="text-success">
																	<i class="fa fa-box mr-1"></i>
																	<?= htmlspecialchars($veri->satis_sozlesme_hizmeti) ?>
																</span>
															<?php else: ?>
																<button class="btn btn-sm btn-success" 
																		onclick="openSatisSozlesmeModal(<?= $veri->cari_id ?>, '<?= htmlspecialchars($veri->cari_isletme, ENT_QUOTES) ?>')"
																		title="Satış sözleşmesi oluştur">
																	<i class="fa fa-plus mr-1"></i>
																	Sözleşme Oluştur
																</button>
															<?php endif; ?>
														<?php endif; ?>
													</td>
													<td class="gorsel-column">
														<?php 
														// Satış görselleri için satis_id kullanarak sayı göster
														if (isset($veri->satis_sozlesme_id) && !empty($veri->satis_sozlesme_id)): 
															// Gerçek satış görsel sayısını controller'dan al
															$satis_gorsel_sayisi = isset($veri->satis_gorsel_sayisi) ? $veri->satis_gorsel_sayisi : 0;
															if ($satis_gorsel_sayisi > 0):
														?>
															<button 
																class="btn btn-sm btn-outline-info gorsel-btn"
																title="<?= $satis_gorsel_sayisi ?> satış görseli"
																onclick="window.open('<?= base_url("cari/gorselGoster/" . $veri->cari_id . "?tip=satis&satis_id=" . $veri->satis_sozlesme_id) ?>', 'satisGorselPencere', 'width=800,height=600,scrollbars=yes,resizable=yes')">
																<i class="fa fa-image"></i> <?= $satis_gorsel_sayisi ?>
															</button>
														<?php else: ?>
															<button 
																class="btn btn-sm btn-outline-secondary gorsel-btn"
																title="Satış görseli yok - Görselleri görüntülemek ve yüklemek için tıklayın"
																onclick="window.open('<?= base_url("cari/gorselGoster/" . $veri->cari_id . "?tip=satis&satis_id=" . $veri->satis_sozlesme_id) ?>', 'satisGorselPencere', 'width=1000,height=700,scrollbars=yes,resizable=yes')">
																<i class="fa fa-image text-muted mr-1"></i><i class="fa fa-plus" style="font-size: 10px; margin-left: -2px;"></i>
															</button>
														<?php 
															endif;
														else: 
														?>
															<span class="text-muted">-</span>
														<?php endif; ?>
													</td>
													<td data-order="<?= $veri->tahsilat_tutar ? floatval($veri->tahsilat_tutar) : -1 ?>">
														<?php if ($veri->tahsilat_tutar): ?>
															<span class="tutar-buyuk text-success" style="cursor: pointer;" 
																  onclick="showTahsilatModal(<?= $veri->cari_id ?>, '<?= htmlspecialchars($veri->cari_isletme) ?>')"
																  title="Tahsilat detaylarını görmek için tıklayın">
																<?= number_format($veri->tahsilat_tutar, 0, ',', '.') ?> ₺
															</span>
														<?php else: ?>
															<span class="text-muted">-</span>
														<?php endif; ?>
													</td>
													<td>
														<?php 
														// Tahsilat görselleri için farklı türlerdeki görsel sayılarını göster
														$toplam_tahsilat_gorsel = 0;
														$banka_gorsel_sayisi = isset($veri->banka_gorsel_sayisi) ? $veri->banka_gorsel_sayisi : 0;
														$cek_gorsel_sayisi = isset($veri->cek_gorsel_sayisi) ? $veri->cek_gorsel_sayisi : 0;
														$senet_gorsel_sayisi = isset($veri->senet_gorsel_sayisi) ? $veri->senet_gorsel_sayisi : 0;
														$toplam_tahsilat_gorsel = $banka_gorsel_sayisi + $cek_gorsel_sayisi + $senet_gorsel_sayisi;
														
														if ($toplam_tahsilat_gorsel > 0): ?>
															<div class="gorsel-container">
																<?php if ($banka_gorsel_sayisi > 0): ?>
																	<button 
																		class="btn btn-sm btn-outline-success me-1 mb-1"
																		title="<?= $banka_gorsel_sayisi ?> banka görseli"
																		onclick="window.open('<?= base_url("cari/gorselGoster/" . $veri->cari_id . "?tip=banka") ?>', 'bankaGorselPencere', 'width=800,height=600,scrollbars=yes,resizable=yes')">
																		<i class="fa fa-university"></i> <?= $banka_gorsel_sayisi ?>
																	</button>
																<?php endif; ?>
																
																<?php if ($cek_gorsel_sayisi > 0): ?>
																	<button 
																		class="btn btn-sm btn-outline-warning me-1 mb-1"
																		title="<?= $cek_gorsel_sayisi ?> çek görseli"
																		onclick="window.open('<?= base_url("cari/gorselGoster/" . $veri->cari_id . "?tip=cek") ?>', 'cekGorselPencere', 'width=800,height=600,scrollbars=yes,resizable=yes')">
																		<i class="fa fa-credit-card"></i> <?= $cek_gorsel_sayisi ?>
																	</button>
																<?php endif; ?>
																
																<?php if ($senet_gorsel_sayisi > 0): ?>
																	<button 
																		class="btn btn-sm btn-outline-danger me-1 mb-1"
																		title="<?= $senet_gorsel_sayisi ?> senet görseli"
																		onclick="window.open('<?= base_url("cari/gorselGoster/" . $veri->cari_id . "?tip=senet") ?>', 'senetGorselPencere', 'width=800,height=600,scrollbars=yes,resizable=yes')">
																		<i class="fa fa-file-text"></i> <?= $senet_gorsel_sayisi ?>
																	</button>
																<?php endif; ?>
															</div>
														<?php else: ?>
															<span class="text-muted">-</span>
														<?php endif; ?>
													</td>
													<td>
														<?php
														// Aktivasyon durum ID'sine göre badge class belirle
														$badge_class = 'badge-secondary';
														$aktivasyon_durum_id = isset($veri->aktivasyon_durum_id) ? $veri->aktivasyon_durum_id : null;
														
														switch($veri->aktivasyon_durum) {
															case 'Aktif': 
															case 'Tamamlandı': $badge_class = 'badge-success'; break;
															case 'Beklemede': 
															case 'İşlemde': 
															case 'Onay Bekliyor': $badge_class = 'badge-warning'; break;
															case 'İptal': 
															case 'Reddedildi': $badge_class = 'badge-danger'; break;
															case 'Denemede': $badge_class = 'badge-info'; break;
															case 'Tanımlanmamış': $badge_class = 'badge-secondary'; break;
															default: $badge_class = 'badge-light'; break;
														}
														
														// Aktivasyon durum rengi
														$durum_renk = isset($veri->aktivasyon_durum_renk) ? $veri->aktivasyon_durum_renk : '#6c757d';
														$durum_text = $veri->aktivasyon_durum ?? 'Tanımlanmamış';
														?>
														<div class="aktivasyon-durum-container">
															<span class="badge <?= $badge_class ?> status-badge" 
																  style="background-color: <?= $durum_renk ?> !important; border-color: <?= $durum_renk ?> !important;"
																  title="Durum ID: <?= $aktivasyon_durum_id ?? 'NULL' ?>">
																<?= $durum_text ?>
															</span>
														</div>
													</td>
													<td class="text-center">
														<?php 
														// Aktivasyon tarihi varsa onu kullan, yoksa oluşturma tarihini kullan
														$display_date = $veri->aktivasyon_tarihi ?? $veri->olusturma_tarihi;
														if ($display_date): ?>
															<div 
																title="Aktivasyon ID: <?= $veri->aktivasyon_id ?? 'Yok' ?>&#10;Durum ID: <?= $veri->aktivasyon_durum_id ?? 'Yok' ?>&#10;Üye No: <?= htmlspecialchars($veri->aktivasyon_uye_no ?? 'Yok') ?>&#10;Kutu No: <?= htmlspecialchars($veri->aktivasyon_kutu_no ?? 'Yok') ?>&#10;Kart No: <?= htmlspecialchars($veri->aktivasyon_kart_no ?? 'Yok') ?>&#10;Kampanya: <?= htmlspecialchars($veri->aktivasyon_kampanya_kodu ?? 'Yok') ?>&#10;Açıklama: <?= htmlspecialchars($veri->aktivasyon_aciklama ?? 'Yok') ?>"
																data-toggle="tooltip"
																data-html="true"
																style="cursor: help;">
																<strong><?= date('d.m.Y', strtotime($display_date)) ?></strong>
																<?php if ($veri->aktivasyon_uye_no): ?>
																	<br>
																	<small class="badge badge-info badge-sm">Üye: <?= htmlspecialchars($veri->aktivasyon_uye_no) ?></small>
																<?php endif; ?>
																<?php if ($veri->aktivasyon_kampanya_kodu): ?>
																	<br>
																	<small class="badge badge-primary badge-sm">Kampanya: <?= htmlspecialchars($veri->aktivasyon_kampanya_kodu) ?></small>
																<?php endif; ?>
															</div>
														<?php else: ?>
															<span class="text-muted">-</span>
														<?php endif; ?>
													</td>
													<td>
														<?php if (isset($veri->personel) && !empty($veri->personel)): ?>
															<span 
																title="E-posta: <?= htmlspecialchars($veri->kullanici_eposta ?? 'Belirtilmemiş') ?>&#10;Kullanıcı ID: <?= htmlspecialchars($veri->kullanici_id ?? 'Yok') ?>"
																data-toggle="tooltip"
																data-html="true"
																style="cursor: help;">
																<?= htmlspecialchars($veri->personel) ?>
															</span>
															<!-- DEBUG: Veri kontrolü -->
															<?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
																<br><small class="text-danger">
																	DEBUG - kullanici_eposta: <?= isset($veri->kullanici_eposta) ? $veri->kullanici_eposta : 'YOK' ?><br>
																	DEBUG - kullanici_id: <?= isset($veri->kullanici_id) ? $veri->kullanici_id : 'YOK' ?>
																</small>
															<?php endif; ?>
														<?php else: ?>
															<span class="text-muted">-</span>
														<?php endif; ?>
													</td>
													<td>
														<?php if (isset($veri->cari_id) && !empty($veri->cari_id)): ?>
															<!-- Düzenle butonu -->
															<a href="<?= base_url('cari/cari-karti-duzenle/' . $veri->cari_id) ?>" 
															   class="btn btn-sm btn-outline-primary mr-1" 
															   title="Cari kartı düzenle"
															   target="_blank">
																<i class="fa fa-edit"></i>
															</a>
															
															<!-- Silme butonu -->
															<button type="button" 
																	class="btn btn-sm btn-outline-danger btn-cari-sil"
																	title="Cari kaydını sil"
																	data-cari-id="<?= htmlspecialchars($veri->cari_id) ?>"
																	data-cari-ad="<?= htmlspecialchars($veri->cari_isletme ?? 'Bilinmeyen Cari') ?>">
																<i class="fa fa-trash"></i>
															</button>
														<?php else: ?>
															<span class="text-muted">-</span>
														<?php endif; ?>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						<?php else: ?>
							<div class="alert alert-info m-3">
								<div class="text-center">
									<i class="fa fa-info-circle" style="font-size: 3rem; color: #17a2b8;"></i>
									<h5 class="mt-3">Henüz Veri Bulunamadı</h5>
									<p>Tam Ana Rapor için henüz uygun veri bulunmuyor.</p>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Görsel Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="imageModalLabel">Görsel</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body text-center">
				<img id="modalImage" src="" alt="Görsel" class="img-fluid">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
				<a id="downloadLink" href="" class="btn btn-primary" target="_blank">
					<i class="fa fa-download"></i> İndir
				</a>
			</div>
		</div>
	</div>
</div>

<!-- Stok Bilgisi Modal -->
<div class="modal fade" id="stokBilgisiModal" tabindex="-1" role="dialog" aria-labelledby="stokBilgisiModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="stokBilgisiModalLabel">
					<i class="fa fa-boxes mr-2"></i>Stok Bilgileri
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body p-2">
				<div id="stokBilgisiLoading" class="text-center p-4">
					<i class="fa fa-spinner fa-spin fa-2x"></i>
					<div class="mt-2">Stok bilgileri yükleniyor...</div>
				</div>
				<div id="stokBilgisiContent" style="display: none;">
					<!-- Stok listesi buraya yüklenecek -->
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
				<button type="button" class="btn btn-success" id="stokBilgisiKaydetBtn">
					<i class="fa fa-save mr-1"></i>Kaydet
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Tahsilat Detay Modal -->
<div class="modal fade" id="tahsilatModal" tabindex="-1" role="dialog" aria-labelledby="tahsilatModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="tahsilatModalLabel">
					<i class="fa fa-money mr-2"></i>Tahsilat Detayları
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div id="tahsilatLoading" class="text-center" style="display: none;">
					<i class="fa fa-spinner fa-spin fa-2x"></i>
					<p class="mt-2">Tahsilat verileri yükleniyor...</p>
				</div>
				<div id="tahsilatContent">
					<!-- Tahsilat listesi buraya yüklenecek -->
				</div>
			</div>
			<div class="modal-footer">
				<div class="d-flex justify-content-between w-100">
					<div>
						<button type="button" class="btn btn-success btn-sm" id="yeniTahsilatEkle" onclick="yeniTahsilatEkle()">
							<i class="fa fa-plus mr-1"></i>Yeni Tahsilat Ekle
						</button>
						<small class="text-muted ml-3">Tutarları değiştirdikten sonra kaydetmeyi unutmayın!</small>
					</div>
					<div>
						<button type="button" class="btn btn-primary" id="kaydetTahsilat" onclick="kaydetTutarDegisiklikleri()" style="display: none;">
							<i class="fa fa-save"></i> Kaydet
						</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Görsel Gösterme Modal -->
<div class="modal fade" id="gorselModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="gorselModalTitle">Görsel</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body text-center">
				<img id="gorselModalImage" src="" class="img-fluid" style="max-height: 500px;" alt="Görsel">
			</div>
		</div>
	</div>
</div>

<!-- Görsel Yükleme Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="uploadModalTitle">Görsel Yükle</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<form id="uploadForm" enctype="multipart/form-data">
				<div class="modal-body">
					<div class="form-group">
						<label>Görsel Dosyalarını Seçin:</label>
						<div class="custom-file">
							<input type="file" id="gorselFile" name="gorseller[]" class="custom-file-input" accept="image/*,application/pdf" multiple required>
							<label class="custom-file-label" for="gorselFile">Dosya(lar) seçin...</label>
						</div>
						<small class="form-text text-muted">
							<i class="fa fa-info-circle"></i> Desteklenen formatlar: JPG, PNG, PDF (Maksimum 10MB/dosya)
							<br><i class="fa fa-plus-circle text-success"></i> Birden fazla dosya seçebilirsiniz (Ctrl+Click)
						</small>
					</div>
					<div id="selectedFiles" class="mt-2" style="display: none;">
						<strong>Seçilen dosyalar:</strong>
						<ul id="fileList" class="list-unstyled mt-1"></ul>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
					<button type="submit" class="btn btn-primary">
						<i class="fa fa-upload"></i> Yükle
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Satış Sözleşmesi Oluşturma Modal -->
<div class="modal fade" id="satisSozlesmeModal" tabindex="-1" role="dialog" aria-labelledby="satisSozlesmeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="satisSozlesmeModalLabel">
					<i class="fa fa-file-contract mr-2"></i>Satış Sözleşmesi Oluştur
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form id="satisSozlesmeForm" action="<?= base_url("fatura/satisFaturasiOlustur"); ?>" method="POST">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6">
							<h6 class="border-bottom pb-2 mb-3">Müşteri Bilgileri</h6>
							<input type="hidden" name="satis_cariID" id="modalCariID" value="">
							<div class="form-group">
								<label>Müşteri Adı</label>
								<input type="text" class="form-control" id="modalCariAdi" readonly>
							</div>
						</div>
						<div class="col-md-6">
							<h6 class="border-bottom pb-2 mb-3">Sözleşme Bilgileri</h6>
							<div class="form-group">
								<label>Sözleşme No <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="satis_faturaNo" required>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Sözleşme Tarihi <span class="text-danger">*</span></label>
								<input type="date" class="form-control" name="satis_faturaTarihi" value="<?= date('Y-m-d') ?>" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Vade Tarihi</label>
								<input type="date" class="form-control" name="satis_vadeTarihi">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<h6 class="border-bottom pb-2 mb-3">Hizmet/Ürün Bilgileri</h6>
							<div class="table-responsive">
								<table class="table table-sm" id="modalStokTable">
									<thead>
										<tr>
											<th width="55%">Hizmet/Ürün Adı</th>
											<th width="8%">Miktar</th>
											<th width="15%">Birim Fiyat (KDV Dahil)</th>
											<th width="10%">KDV (%)</th>
											<th width="7%">Toplam (KDV Dahil)</th>
											<th width="5%">İşlem</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<select class="form-control stok-dropdown" name="stok_id[]" style="width: 100%;">
													<option value="">Hizmet/Ürün seçin...</option>
													<?php
													$stokQuery = "SELECT stok_id, stok_ad, stok_kodu, stok_satisFiyati, stok_satisKDV 
																  FROM stok 
																  WHERE stok_durum = 1 
																  ORDER BY stok_ad ASC";
													$stokResults = $this->db->query($stokQuery)->result();
													foreach ($stokResults as $stok) {
														$displayText = $stok->stok_ad;
														if (!empty($stok->stok_kodu)) {
															$displayText .= ' (' . $stok->stok_kodu . ')';
														}
														echo '<option value="' . $stok->stok_id . '" 
																data-stok-ad="' . htmlspecialchars($stok->stok_ad) . '"
																data-satis-fiyati="' . ($stok->stok_satisFiyati ?: 0) . '"
																data-satis-kdv="' . ($stok->stok_satisKDV ?: 18) . '">' . 
																htmlspecialchars($displayText) . '</option>';
													}
													?>
												</select>
												<input type="hidden" name="stok_adi[]" class="stok-adi-hidden">
											</td>
											<td>
												<input type="number" class="form-control miktar" name="miktar[]" value="1" min="1" step="0.01">
											</td>
											<td>
												<input type="number" class="form-control birim-fiyat" name="birim_fiyat[]" value="0" min="0" step="0.01" readonly>
											</td>
											<td>
												<select class="form-control kdv-orani" name="kdv_orani[]">
													<option value="0">%0</option>
													<option value="1">%1</option>
													<option value="10">%10</option>
													<option value="18" selected>%18</option>
													<option value="20">%20</option>
												</select>
											</td>
											<td>
												<input type="number" class="form-control toplam" name="toplam[]" min="0" step="0.01" placeholder="KDV dahil tutar girin">
											</td>
											<td>
												<button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeStokRow(this)">
													<i class="fa fa-trash"></i>
												</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<button type="button" class="btn btn-sm btn-info" onclick="addStokRow()">
								<i class="fa fa-plus"></i> Yeni Satır Ekle
							</button>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-8">
							<div class="form-group">
								<label>Açıklama</label>
								<textarea class="form-control" name="satis_aciklama" rows="3"></textarea>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card">
								<div class="card-body">
									<h6>Toplam Tutar</h6>
									<div class="d-flex justify-content-between">
										<span>Ara Toplam (KDV Hariç):</span>
										<span id="modalAraToplam">0.00 ₺</span>
									</div>
									<div class="d-flex justify-content-between">
										<span>KDV Tutarı:</span>
										<span id="modalKDVToplam">0.00 ₺</span>
									</div>
									<hr>
									<div class="d-flex justify-content-between font-weight-bold">
										<span>Genel Toplam (KDV Dahil):</span>
										<span id="modalGenelToplam">0.00 ₺</span>
									</div>
									<input type="hidden" name="satis_genelToplam" id="modalGenelToplamInput" value="0">
									<input type="hidden" name="satis_vergiDahilToplam" id="modalVergiDahilToplamInput" value="0">
									<input type="hidden" name="aratopHidden" id="modalAraToplamInput" value="0">
									<input type="hidden" name="kdvtopHidden" id="modalKDVToplamInput" value="0">
									<input type="hidden" name="geneltopHidden" id="modalGenelToplamInput2" value="0">
									<input type="hidden" name="vergidahilHidden" id="modalVergiDahilToplamInput2" value="0">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
					<button type="submit" class="btn btn-success">
						<i class="fa fa-save mr-1"></i>Sözleşme Oluştur
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php $this->load->view("include/footer-js"); ?>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/js/dataTables.bootstrap4.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Global fonksiyonları en başta tanımla
function showTahsilatModal(cariId, cariAd) {
	$('#tahsilatModalLabel').html('<i class="fa fa-money mr-2"></i>Tahsilat Detayları - ' + cariAd);
	
	// Cari bilgilerini modal'da sakla
	$('#tahsilatModal').data('cari-id', cariId);
	$('#tahsilatModal').data('cari-ad', cariAd);
	
	$('#tahsilatModal').modal('show');
	$('#tahsilatLoading').show();
	$('#tahsilatContent').hide();
	
	// AJAX ile tahsilat verilerini getir
	$.ajax({
		url: '<?= base_url("muhasebe/getTahsilatDetay") ?>',
		method: 'POST',
		data: { cari_id: cariId },
		dataType: 'json',
		success: function(response) {
			$('#tahsilatLoading').hide();
			$('#tahsilatContent').show();
			
			if (response.success && response.data.length > 0) {
				let html = `
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead class="thead-light">
								<tr>
									<th>Tarih</th>
									<th>Belge No</th>
									<th>Tahsilat Tipi</th>
									<th>Tutar</th>
									<th>Vade Tarihi</th>
									<th>Açıklama</th>
									<th>Görsel</th>
									<th>İşlemler</th>
								</tr>
							</thead>
							<tbody>
				`;
				
				let toplamTutar = 0;
				response.data.forEach(function(item) {
					toplamTutar += parseFloat(item.tutar || 0);
					
					html += `
						<tr data-tahsilat-id="${item.unique_id}" data-tip-kod="${item.tip_kod}" data-source-id="${item.source_id}" data-ch-id="${item.cari_hareket_id}" data-original-tutar="${item.tutar}">
							<td>${item.tarih}</td>
							<td>${item.belge_no || '-'}</td>
							<td><span class="badge badge-info">${item.tahsilat_tipi}</span></td>
							<td class="text-right">
								<div class="input-group input-group-sm">
									<input type="number" class="form-control tutar-input text-right" 
										   value="${parseFloat(item.tutar).toFixed(2)}" 
										   step="0.01" min="0"
										   data-original="${parseFloat(item.tutar).toFixed(2)}"
										   onchange="tutarDegisti(this)">
									<div class="input-group-append">
										<span class="input-group-text">₺</span>
									</div>
								</div>
							</td>
							<td class="text-center">
								${(item.tip_kod == '2' || item.tip_kod == '4') ? 
									`<div class="input-group input-group-sm">
										<input type="date" class="form-control vade-tarih-input text-center" 
											   value="${item.vade_tarih ? item.vade_tarih : ''}" 
											   data-original="${item.vade_tarih ? item.vade_tarih : ''}"
											   onchange="vadeTarihiDegisti(this)">
									</div>` : 
									'<span class="text-muted">-</span>'}
							</td>
							<td style="max-width: 200px; word-wrap: break-word; white-space: normal; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="${item.aciklama || '-'}">${(item.aciklama && item.aciklama.length > 50) ? item.aciklama.substring(0, 50) + '...' : (item.aciklama || '-')}</td>
							<td>
								${generateGorselButton(item)}
							</td>
							<td class="text-center">
								<button class="btn btn-danger btn-sm" onclick="tahsilatSil('${item.unique_id}', '${item.tip_kod}', '${item.source_id}')" title="Tahsilatı Sil">
									<i class="fa fa-trash"></i>
								</button>
							</td>
						</tr>
					`;
				});
				
				html += `
							</tbody>
							<tfoot class="thead-dark">
								<tr>
									<th colspan="3">TOPLAM</th>
									<th class="text-right" id="tahsilat-toplam">${toplamTutar.toLocaleString('tr-TR', {minimumFractionDigits: 0, maximumFractionDigits: 0})} ₺</th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
							</tfoot>
						</table>
					</div>
				`;
				
				$('#tahsilatContent').html(html);
			} else {
				$('#tahsilatContent').html(`
					<div class="alert alert-info text-center">
						<i class="fa fa-info-circle fa-2x mb-2"></i>
						<h5>Tahsilat Bulunamadı</h5>
						<p>Bu cari için henüz tahsilat kaydı bulunmuyor.</p>
					</div>
				`);
			}
		},
		error: function(xhr, status, error) {
			$('#tahsilatLoading').hide();
			$('#tahsilatContent').show().html(`
				<div class="alert alert-danger text-center">
					<i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
					<h5>Hata Oluştu</h5>
					<p>Tahsilat verileri yüklenirken bir hata oluştu.</p>
					<details>
						<summary>Hata Detayı</summary>
						<small>Status: ${status}</small><br>
						<small>Error: ${error}</small><br>
						<small>Response: ${xhr.responseText}</small><br>
						<small>Status Code: ${xhr.status}</small>
					</details>
				</div>
			`);
		}
	});
}

// Vade tarihi değiştiğinde çağrılan fonksiyon
function vadeTarihiDegisti(input) {
	var $input = $(input);
	var originalValue = $input.data('original');
	var currentValue = $input.val();
	
	// Değişiklik var mı kontrol et
	if (originalValue !== currentValue) {
		$input.closest('tr').addClass('vade-tarih-degisti');
		$('#kaydetTahsilat').show();
	} else {
		$input.closest('tr').removeClass('vade-tarih-degisti');
		
		// Hiç değişiklik kalmadıysa kaydet butonunu gizle
		if ($('.tutar-degisti, .vade-tarih-degisti').length === 0) {
			$('#kaydetTahsilat').hide();
		}
	}
}

// Diğer gerekli fonksiyonlar
function tutarDegisti(input) {
	var $input = $(input);
	var originalValue = parseFloat($input.data('original'));
	var currentValue = parseFloat($input.val()) || 0;
	
	// Değişiklik var mı kontrol et
	if (originalValue !== currentValue) {
		$input.closest('tr').addClass('tutar-degisti');
		$('#kaydetTahsilat').show();
	} else {
		$input.closest('tr').removeClass('tutar-degisti');
		
		// Hiç değişiklik kalmadıysa kaydet butonunu gizle
		if ($('.tutar-degisti, .vade-tarih-degisti').length === 0) {
			$('#kaydetTahsilat').hide();
		}
	}
	
	// Toplamı güncelle
	updateTahsilatToplam();
}

function updateTahsilatToplam() {
	let toplam = 0;
	$('#tahsilatContent tbody tr').each(function() {
		let tutarInput = $(this).find('.tutar-input');
		let tutar = parseFloat(tutarInput.val()) || 0;
		toplam += tutar;
	});
	
	// Footer'daki toplamı güncelle
	$('#tahsilat-toplam').html(toplam.toLocaleString('tr-TR', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + ' ₺');
}

function tahsilatSil(uniqueId, tipKod, sourceId) {
	Swal.fire({
		title: "Emin misiniz?",
		text: "Bu tahsilat kaydını silmek istediğinizden emin misiniz?",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: "Evet, sil!",
		cancelButtonText: "İptal"
	}).then((result) => {
		if (result.isConfirmed) {
			// AJAX ile sil
			$.ajax({
				url: '<?= base_url("muhasebe/tahsilatSil") ?>',
				method: 'POST',
				data: {
					unique_id: uniqueId,
					tip_kod: tipKod,
					source_id: sourceId
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						Swal.fire("Silindi!", "Tahsilat kaydı başarıyla silindi.", "success").then(() => {
							// Modal'ı yenile
							const cariId = $('#tahsilatModal').data('cari-id');
							const cariAd = $('#tahsilatModal').data('cari-ad');
							showTahsilatModal(cariId, cariAd);
						});
					} else {
						Swal.fire("Hata!", response.message || "Silme işlemi başarısız oldu.", "error");
					}
				},
				error: function(xhr, status, error) {
					Swal.fire("Hata!", "Silme işlemi sırasında bir hata oluştu: " + error, "error");
				}
			});
		}
	});
}

function generateGorselButton(item) {
	// Kasa hareketlerinde görsel alanı yok
	if (item.tip_kod == '3') {
		return '<span class="badge badge-secondary">Görsel Yok</span>';
	}
	
	let html = '';
	
	// Mevcut görseller varsa göster
	if (item.gorseller && item.gorseller.length > 0) {
		if (item.gorseller.length === 1) {
			// Tek görsel - PDF kontrolü yap
			const isPdf = item.gorseller[0].dosya_adi && item.gorseller[0].dosya_adi.toLowerCase().endsWith('.pdf');
			const icon = isPdf ? 'fa-file-pdf' : 'fa-image';
			const text = isPdf ? 'PDF' : 'Görsel';
			const btnClass = isPdf ? 'btn-danger' : 'btn-info';
			
			html += `<button class="btn btn-sm ${btnClass}" onclick="showGorsel('${item.gorseller[0].url}', '${item.tahsilat_tipi}')">
				<i class="fa ${icon}"></i> ${text}
			</button>`;
		} else {
			// Çoklu görsel
			html += `<button class="btn btn-sm btn-info" onclick="showGorselGaleri('${item.unique_id}', '${item.tahsilat_tipi}', ${JSON.stringify(item.gorseller).replace(/"/g, '&quot;')})">
				<i class="fa fa-images"></i> ${item.gorseller.length} Görsel
			</button>`;
		}
	}
	
	return html || '<span class="text-muted">-</span>';
}

$(document).ready(function() {
	// DataTable başlat
	$('#raporTable').DataTable({
		"language": {
			"url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json"
		},
		"order": [[ 4, "desc" ]],  // Satış Sözleşme Tutarı sütunu (index 4) azalan sıralama
		"pageLength": 25,
		"responsive": true,
		"scrollX": true,
		"searching": false,  // DataTable'ın kendi arama kutusunu gizle
		"info": true,  // "X kayıt gösteriliyor" bilgisini göster
		"lengthChange": true,  // Sayfa başına kayıt sayısı değiştirme seçeneği
		"dom": 'lrtip'  // Search kutusunu (f) DOM'dan çıkar: l(ength), (f)ilter, (t)able, (i)nfo, (p)agination
	});

	// Cari silme işlemi
	$(document).on('click', '.btn-cari-sil', function() {
		var cariId = $(this).data('cari-id');
		var cariAd = $(this).data('cari-ad');
		
		if (!cariId) {
			Swal.fire({
				icon: 'error',
				title: 'Hata',
				text: 'Cari ID bulunamadı!'
			});
			return;
		}
		
		// İlk kontrol: Bağımlı verileri kontrol et
		$.ajax({
			url: '<?= base_url("muhasebe/cari_sil_kontrol") ?>',
			type: 'POST',
			data: {
				cari_id: cariId
			},
			dataType: 'json',
			success: function(response) {
				if (response.status === 'error') {
					Swal.fire({
						icon: 'error',
						title: 'Hata',
						text: response.message
					});
				} else if (response.status === 'warning') {
					// Bağımlı veriler var, detaylı uyarı göster
					var bagimliVeriHtml = '<div class="alert alert-warning">';
					bagimliVeriHtml += '<strong>Uyarı:</strong> Bu işlem geri alınamaz!<br><br>';
					bagimliVeriHtml += '<strong>Silinecek veriler:</strong><br>';
					bagimliVeriHtml += '<ul class="list-unstyled">';
					
					response.data.forEach(function(veri) {
						bagimliVeriHtml += '<li class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">';
						bagimliVeriHtml += '<div>';
						bagimliVeriHtml += '<i class="fa fa-exclamation-triangle text-warning"></i> ';
						bagimliVeriHtml += '<strong>' + veri.tablo + ':</strong> ' + veri.adet + ' adet';
						bagimliVeriHtml += '<br><small class="text-muted">' + veri.aciklama + '</small>';
						bagimliVeriHtml += '</div>';
						bagimliVeriHtml += '<div class="btn-group">';
						// Seçmeli silme butonu
						bagimliVeriHtml += '<button class="btn btn-sm btn-outline-info" onclick="secmeliVeriSil(\'' + veri.tablo_kodu + '\', ' + cariId + ', \'' + veri.tablo + '\', ' + veri.adet + ')" title="Seçerek sil">';
						bagimliVeriHtml += '<i class="fa fa-list-ul"></i>';
						bagimliVeriHtml += '</button>';
						// Tamamen silme butonu  
						bagimliVeriHtml += '<button class="btn btn-sm btn-outline-danger" onclick="tekVeriSil(\'' + veri.tablo_kodu + '\', ' + cariId + ', \'' + veri.tablo + '\')" title="Tümünü sil">';
						bagimliVeriHtml += '<i class="fa fa-trash"></i>';
						bagimliVeriHtml += '</button>';
						bagimliVeriHtml += '</div>';
						bagimliVeriHtml += '</li>';
					});
					
					bagimliVeriHtml += '</ul></div>';
					
					Swal.fire({
						title: 'Cari Kaydı Silinecek',
						html: '<div class="text-left">' + 
							  '<p><strong>Cari:</strong> ' + response.cari_bilgi.ad + '</p>' +
							  '<p><strong>ID:</strong> ' + response.cari_bilgi.id + '</p>' +
							  (response.cari_bilgi.telefon ? '<p><strong>Telefon:</strong> ' + response.cari_bilgi.telefon + '</p>' : '') +
							  bagimliVeriHtml + 
							  '</div>',
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#dc3545',
						cancelButtonColor: '#6c757d',
						confirmButtonText: '<i class="fa fa-trash"></i> Sil ve Tüm Verileri Temizle',
						cancelButtonText: '<i class="fa fa-times"></i> İptal',
						customClass: {
							confirmButton: 'btn btn-danger',
							cancelButton: 'btn btn-secondary'
						},
						buttonsStyling: false
					}).then((result) => {
						if (result.isConfirmed) {
							cariSil(cariId);
						}
					});
				} else if (response.status === 'confirm') {
					// Bağımlı veri yok, basit onay
					Swal.fire({
						title: 'Cari Kaydı Silinecek',
						html: '<div class="text-left">' + 
							  '<p><strong>Cari:</strong> ' + response.cari_bilgi.ad + '</p>' +
							  '<p><strong>ID:</strong> ' + response.cari_bilgi.id + '</p>' +
							  (response.cari_bilgi.telefon ? '<p><strong>Telefon:</strong> ' + response.cari_bilgi.telefon + '</p>' : '') +
							  '<div class="alert alert-info">' +
							  '<i class="fa fa-info-circle"></i> Bu cari kaydına ait başka veri bulunmamaktadır.' +
							  '</div>' +
							  '</div>',
						text: response.message,
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#dc3545',
						cancelButtonColor: '#6c757d',
						confirmButtonText: '<i class="fa fa-trash"></i> Sil',
						cancelButtonText: '<i class="fa fa-times"></i> İptal',
						customClass: {
							confirmButton: 'btn btn-danger',
							cancelButton: 'btn btn-secondary'
						},
						buttonsStyling: false
					}).then((result) => {
						if (result.isConfirmed) {
							cariSil(cariId);
						}
					});
				}
			},
			error: function(xhr, status, error) {
				console.error('AJAX Error:', error);
				Swal.fire({
					icon: 'error',
					title: 'Hata',
					text: 'Kontrol işlemi sırasında bir hata oluştu: ' + error
				});
			}
		});
	});
});

// Cari silme fonksiyonu
function cariSil(cariId) {
	// Debug için log
	console.log('Cari silme işlemi başlatılıyor. Cari ID:', cariId);
	
	if (!cariId || cariId === '' || cariId === null || cariId === undefined) {
		Swal.fire({
			icon: 'error',
			title: 'Hata',
			text: 'Geçersiz Cari ID! ID: ' + cariId
		});
		return;
	}
	
	// Loading göster
	Swal.fire({
		title: 'Siliniyor...',
		text: 'Lütfen bekleyin, işlem gerçekleştiriliyor. Cari ID: ' + cariId,
		icon: 'info',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		didOpen: () => {
			Swal.showLoading();
		}
	});
	
	$.ajax({
		url: '<?= base_url("muhasebe/cari_sil") ?>',
		type: 'POST',
		data: {
			cari_id: cariId,
			onay: 'evet'
		},
		dataType: 'json',
		beforeSend: function() {
			console.log('AJAX Request Data:', {
				cari_id: cariId,
				onay: 'evet',
				url: '<?= base_url("muhasebe/cari_sil") ?>'
			});
		},
		success: function(response) {
			console.log('AJAX Success Response:', response);
			if (response.status === 'success') {
				Swal.fire({
					icon: 'success',
					title: 'Başarılı',
					text: response.message,
					timer: 2000,
					showConfirmButton: false
				}).then(() => {
					// Sayfayı yenile
					location.reload();
				});
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Hata',
					text: response.message
				});
			}
		},
		error: function(xhr, status, error) {
			console.error('AJAX Error Details:', {
				xhr: xhr,
				status: status,
				error: error,
				responseText: xhr.responseText,
				statusCode: xhr.status
			});
			
			var errorMessage = 'Silme işlemi sırasında bir hata oluştu.';
			
			// Detaylı hata mesajı oluştur
			if (xhr.status === 500) {
				errorMessage += ' (Server Error - 500)';
				if (xhr.responseText) {
					try {
						var responseJson = JSON.parse(xhr.responseText);
						if (responseJson.message) {
							errorMessage = responseJson.message;
						}
					} catch(e) {
						// JSON parse hatası
						console.error('Response text:', xhr.responseText);
						errorMessage += '\nDetay: ' + xhr.responseText.substring(0, 200);
					}
				}
			} else if (xhr.status === 0) {
				errorMessage = 'Sunucuya bağlanılamadı.';
			} else {
				errorMessage += ' (HTTP ' + xhr.status + ')';
			}
			
			Swal.fire({
				icon: 'error',
				title: 'Hata',
				text: errorMessage,
				footer: 'Lütfen sistem yöneticinizle iletişime geçin.'
			});
		}
	});
}

// Görsel modal fonksiyonu
function showImageModal(imageSrc, title) {
	$('#imageModalLabel').text(title);
	$('#modalImage').attr('src', imageSrc);
	$('#downloadLink').attr('href', imageSrc);
	$('#imageModal').modal('show');
}

// Satış Sözleşmesi Modal fonksiyonu
window.openSatisSozlesmeModal = function(cariId, cariIsletme) {
	console.log('openSatisSozlesmeModal called with:', { cariId, cariIsletme });
	
	// Yeni Hizmet Bilgileri modalını aç (satis_id = 0 yeni kayıt için)
	openStokBilgisiModal(0, cariId, cariIsletme);
}

// Modal tablo hesaplamaları
function updateModalTotals() {
	let araToplam = 0;
	let kdvToplam = 0;
	let genelToplam = 0;
	
	$('#modalStokTable tbody tr').each(function() {
		const toplamKdvDahil = parseFloat($(this).find('.toplam').val()) || 0; // KDV dahil tutar
		const kdvOrani = parseFloat($(this).find('.kdv-orani').val()) || 0;
		
		// KDV dahil tutardan KDV hariç tutarı hesapla
		const kdvCarpani = 1 + (kdvOrani / 100);
		const toplamKdvHaric = kdvCarpani > 0 ? toplamKdvDahil / kdvCarpani : toplamKdvDahil;
		const satirKdv = toplamKdvDahil - toplamKdvHaric;
		
		araToplam += toplamKdvHaric;
		kdvToplam += satirKdv;
		genelToplam += toplamKdvDahil;
	});
	
	$('#modalAraToplam').text(araToplam.toFixed(2) + ' ₺');
	$('#modalKDVToplam').text(kdvToplam.toFixed(2) + ' ₺');
	$('#modalGenelToplam').text(genelToplam.toFixed(2) + ' ₺');
	$('#modalGenelToplamInput').val(genelToplam.toFixed(2));
	$('#modalVergiDahilToplamInput').val(genelToplam.toFixed(2));
	
	// Backend için gerekli hidden alanları güncelle
	$('#modalAraToplamInput').val(araToplam.toFixed(2));
	$('#modalKDVToplamInput').val(kdvToplam.toFixed(2));
	$('#modalGenelToplamInput2').val(genelToplam.toFixed(2));
	$('#modalVergiDahilToplamInput2').val(genelToplam.toFixed(2));
}

// Yeni stok satırı ekleme
window.addStokRow = function() {
	// Mevcut ilk satırdaki select'in option'larını kopyala
	const firstStokSelect = $('#modalStokTable tbody tr:first-child .stok-dropdown');
	const stokOptions = firstStokSelect.html();
	
	const newRow = `
		<tr>
			<td>
				<select class="form-control stok-dropdown" name="stok_id[]" style="width: 100%;">
					${stokOptions}
				</select>
				<input type="hidden" name="stok_adi[]" class="stok-adi-hidden">
			</td>
			<td>
				<input type="number" class="form-control miktar" name="miktar[]" value="1" min="1" step="0.01">
			</td>
			<td>
				<input type="number" class="form-control birim-fiyat" name="birim_fiyat[]" value="0" min="0" step="0.01" readonly>
			</td>
			<td>
				<select class="form-control kdv-orani" name="kdv_orani[]">
					<option value="0">%0</option>
					<option value="1">%1</option>
					<option value="10">%10</option>
					<option value="18" selected>%18</option>
					<option value="20">%20</option>
				</select>
			</td>
			<td>
				<input type="number" class="form-control toplam" name="toplam[]" min="0" step="0.01" placeholder="KDV dahil tutar girin">
			</td>
			<td>
				<button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeStokRow(this)">
					<i class="fa fa-trash"></i>
				</button>
			</td>
		</tr>
	`;
	$('#modalStokTable tbody').append(newRow);
	initializeModalEvents();
	initializeStokDropdownEvents();
}

// Stok satırını silme
window.removeStokRow = function(button) {
	if ($('#modalStokTable tbody tr').length > 1) {
		$(button).closest('tr').remove();
		updateModalTotals();
	}
}

// Modal event'leri başlatma
function initializeModalEvents() {
	// Miktar ve toplam değiştiğinde birim fiyat hesapla (KDV dahil)
	$(document).off('input', '#modalStokTable .miktar, #modalStokTable .toplam')
		.on('input', '#modalStokTable .miktar, #modalStokTable .toplam', function() {
			const row = $(this).closest('tr');
			const miktar = parseFloat(row.find('.miktar').val()) || 0;
			const toplamKdvDahil = parseFloat(row.find('.toplam').val()) || 0; // KDV dahil tutar
			
			// Birim fiyatı hesapla (KDV dahil toplam / miktar)
			if (miktar > 0) {
				const birimFiyatKdvDahil = toplamKdvDahil / miktar;
				row.find('.birim-fiyat').val(birimFiyatKdvDahil.toFixed(2));
			} else {
				row.find('.birim-fiyat').val('0.00');
			}
			
			updateModalTotals();
		});
	
	$(document).off('change', '#modalStokTable .kdv-orani')
		.on('change', '#modalStokTable .kdv-orani', function() {
			updateModalTotals();
		});
	
	// Stok dropdown değişimi
	initializeStokDropdownEvents();
}

// Stok dropdown event'leri
function initializeStokDropdownEvents() {
	$(document).off('change', '.stok-dropdown')
		.on('change', '.stok-dropdown', function() {
			const selectedOption = $(this).find('option:selected');
			const row = $(this).closest('tr');
			
			if (selectedOption.val()) {
				// Seçili stok bilgilerini al
				const stokAd = selectedOption.data('stok-ad');
				const satisFiyatiKdvHaric = parseFloat(selectedOption.data('satis-fiyati')) || 0;
				const satisKDV = parseFloat(selectedOption.data('satis-kdv')) || 18;
				
				console.log('Selected stok:', {stokAd, satisFiyatiKdvHaric, satisKDV});
				
				// KDV dahil birim fiyatı hesapla
				const kdvCarpani = 1 + (satisKDV / 100);
				const birimFiyatKdvDahil = satisFiyatiKdvHaric * kdvCarpani;
				
				// Form alanlarını doldur
				row.find('.stok-adi-hidden').val(stokAd);
				row.find('.birim-fiyat').val(birimFiyatKdvDahil.toFixed(2)); // KDV dahil birim fiyat göster
				row.find('.kdv-orani').val(satisKDV);
				
				// Toplam alanını temizle, kullanıcı KDV dahil tutarı kendisi girecek
				row.find('.toplam').val('');
			} else {
				// Temizle
				row.find('.stok-adi-hidden').val('');
				row.find('.birim-fiyat').val('0');
				row.find('.kdv-orani').val('18');
				row.find('.toplam').val('');
			}
			
			updateModalTotals();
		});
}

// Select2 dropdown'ları için initialize fonksiyonu
function initializeSelect2ForModal() {
	$('#satisSozlesmeModal .stok-select2').each(function() {
		if (!$(this).hasClass('select2-hidden-accessible')) {
			initializeStokSelect2($(this));
		}
	});
}

// Yeni satırlar için Select2 initialize
function initializeSelect2ForNewRows() {
	$('#modalStokTable tbody tr:last-child .stok-select2').each(function() {
		if (!$(this).hasClass('select2-hidden-accessible')) {
			initializeStokSelect2($(this));
		}
	});
}

// Tek bir Select2 element için initialize
function initializeStokSelect2(element) {
	element.select2({
		dropdownParent: $('#satisSozlesmeModal'),
		placeholder: 'Hizmet/Ürün seçin veya arayın...',
		allowClear: true,
		minimumInputLength: 2,
		language: {
			inputTooShort: function() {
				return 'En az 2 karakter yazın...';
			},
			searching: function() {
				return 'Aranıyor...';
			},
			noResults: function() {
				return 'Sonuç bulunamadı';
			},
			errorLoading: function() {
				return 'Sonuçlar yüklenirken hata oluştu.';
			}
		},
		ajax: {
			url: '<?= base_url("fatura/stokArama") ?>',
			dataType: 'json',
			delay: 300,
			data: function(params) {
				console.log('Select2 AJAX Request - params:', params);
				return {
					term: params.term || '',
					page: params.page || 1
				};
			},
			processResults: function(data, params) {
				console.log('Select2 AJAX Response - data:', data);
				console.log('Select2 AJAX Response - params:', params);
				
				// Hata kontrolü
				if (data && data.error) {
					console.error('Stok arama hatası:', data.message);
					return { results: [] };
				}
				
				// Eğer data bir array değilse, boş array döndür
				if (!Array.isArray(data)) {
					console.error('Beklenmeyen veri formatı:', data);
					console.error('Data type:', typeof data);
					return { results: [] };
				}
				
				const results = data.map(function(item) {
					console.log('Mapping item:', item);
					return {
						id: item.stok_id,
						text: item.stok_ad + (item.stok_kodu ? ' (' + item.stok_kodu + ')' : ''),
						stok_data: {
							stok_id: item.stok_id,
							stok_ad: item.stok_ad,
							stok_kodu: item.stok_kodu,
							satis_fiyati: item.stok_satisFiyati || 0,
							satis_kdv: item.stok_satisKDV || 18
						}
					};
				});
				
				console.log('Final results for Select2:', results);
				return { results: results };
			},
			cache: true,
			// Hata yakalama
			transport: function(params, success, failure) {
				console.log('Select2 transport - URL:', params.url);
				console.log('Select2 transport - data:', params.data);
				
				var $request = $.ajax(params);
				
				$request.then(function(data) {
					console.log('Transport success - data:', data);
					console.log('Transport success - data type:', typeof data);
					success(data);
				});
				
				$request.fail(function(xhr, status, error) {
					console.error('Transport failed - xhr status:', xhr.status);
					console.error('Transport failed - xhr readyState:', xhr.readyState);
					console.error('Transport failed - status:', status);
					console.error('Transport failed - error:', error);
					console.error('Transport failed - responseText (first 500 chars):', xhr.responseText ? xhr.responseText.substring(0, 500) : 'No response text');
					
					// HTML response kontrolü
					if (xhr.responseText && xhr.responseText.trim().startsWith('<!DOCTYPE')) {
						console.error('HTML response detected - likely authentication error or page not found');
					}
					
					failure();
				});
				
				return $request;
			}
		}
	});
	
	// Seçim değiştiğinde
	element.on('select2:select', function(e) {
		const data = e.params.data;
		console.log('Select2 selected data:', data);
		const row = $(this).closest('tr');
		
		if (data && data.stok_id) {
			// Fiyat ve KDV bilgilerini doldur (endpoint'ten gelen format)
			const satisFiyati = parseFloat(data.stok_satisFiyati) || 0;
			const satisKDV = parseFloat(data.stok_satisKDV) || 18;
			
			console.log('Setting prices - satisFiyati:', satisFiyati, 'satisKDV:', satisKDV);
			
			row.find('.stok-adi-hidden').val(data.stok_ad);
			row.find('.birim-fiyat').val(satisFiyati.toFixed(2));
			row.find('.kdv-orani').val(satisKDV);
			
			// Stok ID'yi gizli inputa kaydet
			row.find('.stok-id').val(data.stok_id);
			
			// Toplamları güncelle
			updateModalTotals();
		}
	});
	
	// Temizlendiğinde
	element.on('select2:clear', function() {
		const row = $(this).closest('tr');
		row.find('.stok-adi-hidden').val('');
		row.find('.birim-fiyat').val('0');
		updateModalTotals();
	});
}

// Form gönderme işlemi
$('#satisSozlesmeForm').on('submit', function(e) {
	e.preventDefault();
	
	// Form validasyonu
	if (!$(this).find('input[name="satis_faturaNo"]').val().trim()) {
		alert('Sözleşme numarası zorunludur!');
		return;
	}
	
	// En az bir stok kontrolü
	let hasValidStok = false;
	$('#modalStokTable tbody tr').each(function() {
		const stokId = $(this).find('.stok-dropdown').val();
		const miktar = $(this).find('.miktar').val();
		if (stokId && miktar > 0) {
			hasValidStok = true;
		}
	});
	
	if (!hasValidStok) {
		alert('En az bir hizmet/ürün seçmelisiniz!');
		return;
	}
	
	// Loading göster
	const submitBtn = $(this).find('button[type="submit"]');
	const originalText = submitBtn.html();
	submitBtn.html('<i class="fa fa-spinner fa-spin mr-1"></i>Oluşturuluyor...').prop('disabled', true);
	
	// Form verilerini manuel olarak hazırla
	let formData = new FormData();
	
	// Temel form verileri
	formData.append('satis_cariID', $('#modalCariID').val());
	formData.append('satis_faturaNo', $('input[name="satis_faturaNo"]').val());
	formData.append('satis_faturaTarihi', $('input[name="satis_faturaTarihi"]').val());
	formData.append('satis_vadeTarihi', $('input[name="satis_vadeTarihi"]').val());
	formData.append('satis_aciklama', $('textarea[name="satis_aciklama"]').val());
	
	// Toplam değerleri
	const araToplam = parseFloat($('#modalAraToplam').text().replace(' ₺', '')) || 0;
	const kdvToplam = parseFloat($('#modalKDVToplam').text().replace(' ₺', '')) || 0;
	const genelToplam = parseFloat($('#modalGenelToplam').text().replace(' ₺', '')) || 0;
	
	formData.append('aratopHidden', araToplam.toFixed(2));
	formData.append('kdvtopHidden', kdvToplam.toFixed(2));
	formData.append('geneltopHidden', genelToplam.toFixed(2));
	formData.append('vergidahilHidden', genelToplam.toFixed(2));
	formData.append('netTutarInput', genelToplam.toFixed(2));
	formData.append('satis_genelToplam', genelToplam.toFixed(2));
	formData.append('satis_vergiDahilToplam', genelToplam.toFixed(2));
	
	// Varsayılan değerler
	formData.append('satis_paraBirimi', 1); // TL
	formData.append('indirimtopHidden', 0);
	formData.append('tevkifattopHidden', 0);
	
	// Stok verilerini ekle
	let stokIndex = 0;
	$('#modalStokTable tbody tr').each(function() {
		const stokId = $(this).find('.stok-dropdown').val();
		const stokAdi = $(this).find('.stok-adi-hidden').val();
		const miktar = $(this).find('.miktar').val();
		const birimFiyat = $(this).find('.birim-fiyat').val();
		const kdvOrani = $(this).find('.kdv-orani').val();
		const toplam = $(this).find('.toplam').val();
		
		if (stokId && miktar > 0 && toplam > 0) {
			// Controller'ın beklediği format
			formData.append(`stokid[${stokIndex}]`, stokId);
			formData.append(`stokadi[${stokIndex}]`, stokAdi);
			formData.append(`miktar[${stokIndex}]`, miktar);
			formData.append(`birimfiyat[${stokIndex}]`, birimFiyat);
			formData.append(`kdv[${stokIndex}]`, kdvOrani);
			formData.append(`toplamHidden[${stokIndex}]`, toplam);
			
			// Varsayılan değerler
			formData.append(`stokkodu[${stokIndex}]`, '');
			formData.append(`barkod[${stokIndex}]`, '');
			formData.append(`birim[${stokIndex}]`, 1); // Adet
			formData.append(`indirimyuzde[${stokIndex}]`, 0);
			formData.append(`tevkifat[${stokIndex}]`, "0|0");
			
			stokIndex++;
		}
	});
	
	// AJAX ile gönder
	$.ajax({
		url: $(this).attr('action'),
		type: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		dataType: 'json',
		success: function(response) {
			if (response.success) {
				alert('Satış sözleşmesi başarıyla oluşturuldu!');
				$('#satisSozlesmeModal').modal('hide');
				location.reload(); // Sayfayı yenile
			} else {
				alert('Hata: ' + (response.message || 'Bilinmeyen bir hata oluştu.'));
			}
		},
		error: function(xhr, status, error) {
			console.error('AJAX Error:', {xhr, status, error});
			let errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
			
			if (xhr.responseText) {
				try {
					const response = JSON.parse(xhr.responseText);
					if (response.message) {
						errorMessage = response.message;
					}
				} catch(e) {
					// JSON parse hatası
				}
			}
			
			alert(errorMessage);
		},
		complete: function() {
			submitBtn.html(originalText).prop('disabled', false);
		}
	});
});

// Stok Bilgisi modal fonksiyonu
window.openStokBilgisiModal = function(satisId, cariId, cariIsletme) {
	console.log('openStokBilgisiModal called with:', { satisId, cariId, cariIsletme });
	
	$('#stokBilgisiModalLabel').html('<i class="fa fa-boxes mr-2"></i>Hizmet Bilgileri - ' + cariIsletme + ' <small class="text-muted">(Cari ID: ' + cariId + ')</small>');
	
	// Modal'da satis_id ve cari_id'yi sakla
	$('#stokBilgisiModal').data('satisId', satisId);
	$('#stokBilgisiModal').data('cariId', cariId);
	
	$('#stokBilgisiModal').modal('show');
	$('#stokBilgisiLoading').show();
	$('#stokBilgisiContent').hide();
	
	// AJAX ile stok verilerini getir
	$.ajax({
		url: '<?= base_url("stok_bilgileri_endpoint.php") ?>',
		method: 'POST',
		data: { 
			satis_id: satisId,
			cari_id: cariId
		},
		dataType: 'json',
		success: function(response) {
			console.log('AJAX Success:', response);
			$('#stokBilgisiLoading').hide();
			$('#stokBilgisiContent').show();
			
			if (response && response.success) {
				let html = `
					<!-- Sözleşme Bilgileri -->
						<div class="card-body">
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label>Sözleşme Tarihi <span class="text-danger">*</span></label>
										<input type="date" class="form-control" id="sozlesmeTarihi" 
											   value="${response.satis_info?.satis_faturaTarihi || ''}" required>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Sözleşme No</label>
										<input type="text" class="form-control" id="sozlesmeNo" 
											   value="${response.satis_info?.satis_faturaNo || ''}" 
											   placeholder="Sözleşme numarası">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Açıklama</label>
										<textarea class="form-control" id="sozlesmeAciklama" rows="1" 
												  placeholder="Sözleşme açıklaması">${response.satis_info?.satis_aciklama || ''}</textarea>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Satış Sözleşmesi Görsel</label>
										<div class="d-flex gap-2">
											<button type="button" class="btn btn-sm btn-primary" onclick="satirGorselYukle(${satisId})">
												<i class="fa fa-upload"></i> Yükle
											</button>
											<button type="button" class="btn btn-sm btn-info" onclick="satirGorselleriGoruntule('${response.satis_info?.satis_dosya || ''}')">
												<i class="fa fa-eye"></i> Görüntüle
											</button>
										</div>
										<input type="hidden" id="satisDosya" value="${response.satis_info?.satis_dosya || ''}">
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="row mb-3">
						<div class="col-md-12">
							<button type="button" class="btn btn-primary btn-sm" onclick="stokSatiriEkle()">
								<i class="fa fa-plus mr-1"></i>Yeni Satır Ekle
							</button>
						</div>
					</div>
					<div class="w-100">
						<table class="table table-bordered table-hover mb-0" id="stokTable">
							<thead class="thead-light">
								<tr>
									<th width="5%">ID</th>
									<th width="60%">Hizmet Adı</th>
									<th width="10%" style="display: none;">Miktar</th>
									<th width="15%">Birim Fiyat (KDV Dahil)</th>
									<th width="10%" style="display: none;">KDV (%)</th>
									<th width="15%">Abonelik Bitiş Tarihi</th>
									<th width="5%">İşlem</th>
								</tr>
							</thead>
							<tbody id="stokTableBody">
				`;
				
				if (response.data && response.data.length > 0) {
					response.data.forEach(function(item, index) {
						html += createStokRow(index, item);
					});
				} else {
					html += createStokRow(0, {});
				}
				
				// Toplam satırı ekle
				html += `
							<tr class="toplam-satir">
								<td class="text-center">—</td>
								<td>
									<strong>TOPLAM TUTAR (KDV DAHİL)</strong>
									<br><small class="text-muted">
										<i class="fa fa-info-circle"></i> Birim fiyatlar KDV dahil olarak girilmelidir
									</small>
								</td>
								<td class="text-center">
									<input type="text" class="form-control text-center" id="toplamTutar" value="0.00 ₺" readonly style="background-color: #f8f9fa; font-weight: bold; width: 120px;">
								</td>
								<td class="text-center">—</td>
								<td class="text-center">—</td>
							</tr>
						</tbody>
					</table>
				</div>
					
					<!-- Tahsilat Detayları Tablosu -->
					<div class="w-100 mt-4">
						<table class="table table-bordered table-sm table-hover" id="tahsilatDetayTable">
							<thead class="thead-light">
								<tr>
									<th width="5%" class="text-center">ID</th>
									<th width="60%">Tahsilat Türü / Detay</th>
									<th width="15%" class="text-center">Toplam</th>
									<th width="15%" class="text-center">—</th>
									<th width="5%" class="text-center">Onay</th>
								</tr>
							</thead>
							<tbody id="tahsilatDetayTableBody">
				`;
				
				// Tahsilat verilerini göster
				let tahsilatToplam = 0;
				let tahsilatIndex = 1;
				if (response.tahsilat_data && response.tahsilat_data.length > 0) {
					response.tahsilat_data.forEach(function(item) {
						// Eğer detay uzunsa kısalt
						let detayText = item.Detay;
						let showMoreBtn = '';
						if (detayText.length > 100 && detayText !== '—') {
							showMoreBtn = `<br><small><a href="javascript:void(0)" onclick="toggleDetay(this)" class="text-primary">Daha fazla göster</a></small>`;
							detayText = `<span class="detay-short">${detayText.substring(0, 100)}...</span><span class="detay-full" style="display:none;">${item.Detay}</span>`;
						}
						
						// Toplam hesapla (sayı formatını düzelt)
						let tutarStr = item.Toplam.toString();
						// Binlik ayırıcıları (virgül, nokta) kaldır ve sadece son noktayı ondalık ayırıcı olarak bırak
						tutarStr = tutarStr.replace(/[^\d.,]/g, ''); // Sadece rakam, virgül ve nokta bırak
						
						// Türkçe format (123.456,78) veya İngilizce format (123,456.78) kontrolü
						if (tutarStr.includes(',')) {
							// Eğer virgülden sonra 2 hane varsa ondalık ayırıcı, yoksa binlik ayırıcı
							const parts = tutarStr.split(',');
							if (parts.length === 2 && parts[1].length <= 2) {
								// Türkçe format: 123.456,78
								tutarStr = tutarStr.replace(/\./g, '').replace(',', '.');
							} else {
								// İngilizce format: 123,456.78
								tutarStr = tutarStr.replace(/,/g, '');
							}
						}
						
						const tutarValue = parseFloat(tutarStr) || 0;
						console.log('Item:', item.Kalem, 'Toplam:', item.Toplam, 'Parsed:', tutarValue);
						tahsilatToplam += tutarValue;
						
						// Veri kontrolü - daha esnek kontrol
						// Görseldeğinde Banka & Pos için: item.Kalem="Banka & Pos", item.Toplam="2,200,000.00 ₺", item.Detay="2025-08-08 / 2,200,000.00"
						const tutarCheck = item.Toplam && item.Toplam !== '0.00 ₺' && item.Toplam !== '0 ₺' && parseFloat(item.Toplam.toString().replace(/[^\d.,]/g, '').replace(',', '.')) > 0;
						const kalemCheck = item.Kalem && item.Kalem !== '—' && item.Kalem.trim().length > 0;
						
						// Geçerli tahsilat türleri
						const validTahsilatTypes = ['Banka & Pos', 'Çek', 'Senet', 'Nakit (Kasa)', 'Banka/Pos', 'Kasa/Nakit'];
						const isValidType = validTahsilatTypes.includes(item.Kalem);
						
						const hasValidData = tutarCheck && kalemCheck && isValidType;
						
						console.log('Tahsilat kontrol:', {
							kalem: item.Kalem,
							toplam: item.Toplam,
							detay: item.Detay,
							tutarCheck: tutarCheck,
							kalemCheck: kalemCheck,
							isValidType: isValidType,
							hasValidData: hasValidData
						});
						
						html += `
							<tr data-tahsilat-id="${item.unique_id || ''}" data-tip-kod="${item.tip_kod || ''}">
								<td class="text-center">${tahsilatIndex}</td>
								<td>
									<strong>${item.Kalem}</strong><br>
									<small class="text-muted" style="word-break: break-all;">${detayText}</small>
									${showMoreBtn}
								</td>
								<td class="text-center">
									<input type="text" class="form-control text-center" value="${item.Toplam} ₺" readonly style="background-color: #f8f9fa; width: 120px;">
								</td>
								<td class="text-center">—</td>
								<td class="text-center">
									${hasValidData ? `
										<div class="custom-control custom-checkbox">
											<input type="checkbox" class="custom-control-input tahsilat-onay-checkbox" 
												   id="onay_${item.unique_id || tahsilatIndex}" 
												   data-tahsilat-id="${item.unique_id || ''}" 
												   ${item.onay_durumu == 1 ? 'checked' : ''}>
											<label class="custom-control-label" for="onay_${item.unique_id || tahsilatIndex}"></label>
										</div>
									` : '—'}
								</td>
							</tr>
						`;
						tahsilatIndex++;
					});
				} else {
					html += `
						<tr>
							<td colspan="5" class="text-center text-muted">
								<i class="fa fa-info-circle mr-1"></i>Bu cari için henüz tahsilat kaydı bulunmuyor.
							</td>
						</tr>
					`;
				}
				
				// Tahsilat toplam satırını ekle - Stok tablosuyla aynı format
				html += `
						<tr class="tahsilat-toplam-satir">
							<td class="text-center">—</td>
							<td>
								<strong>TOPLAM TAHSİLAT</strong>
							</td>
							<td class="text-center">
								<input type="text" class="form-control text-center" id="toplamTahsilat" value="${tahsilatToplam.toLocaleString('tr-TR', {minimumFractionDigits: 0, maximumFractionDigits: 0})} ₺" readonly style="background-color: #f8f9fa; font-weight: bold; width: 120px;">
							</td>
							<td class="text-center">—</td>
							<td class="text-center">—</td>
						</tr>
						<tr class="kalan-satir" style="background-color: #fff3cd; border: 2px solid #ffc107;">
							<td class="text-center">—</td>
							<td>
								<strong style="color: #856404;">KALAN TUTAR</strong>
							</td>
							<td class="text-center">
								<input type="text" class="form-control text-center" id="kalanTutar" value="0 ₺" readonly style="background-color: #fff3cd; font-weight: bold; width: 120px; color: #856404; border: 1px solid #ffc107;">
							</td>
							<td class="text-center">—</td>
							<td class="text-center">
								<div class="custom-control custom-checkbox">
									<input type="checkbox" class="custom-control-input" id="tumunuOnaylaCheckbox" title="Tümünü Onayla/İptal Et">
									<label class="custom-control-label" for="tumunuOnaylaCheckbox" style="color: #856404; font-weight: bold;"></label>
								</div>
								<small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Tümünü Onayla</small>
							</td>
						</tr>
							</tbody>
						</table>
					</div>
				`;
				
				$('#stokBilgisiContent').html(html);
				
				// Stok arama autocomplete'i başlat
				initStokAutocomplete();
				
				// Toplam tutarı hesapla
				hesaplaToplamTutar();
				
				// Mevcut veriler için KDV ayrıştırmasını göster
				document.querySelectorAll('.birimFiyat').forEach(function(input) {
					updateKdvBreakdown(input);
				});
				
				// Tümünü onayla checkbox durumunu güncelle
				setTimeout(function() {
					updateTumunuOnaylaCheckbox();
				}, 100);
			} else {
				console.log('AJAX Response not success:', response);
				$('#stokBilgisiContent').html(`
					<div class="alert alert-danger text-center">
						<i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
						<h5>Hata Oluştu</h5>
						<p>${response && response.message ? response.message : 'Stok verileri yüklenirken bir hata oluştu.'}</p>
						<details>
							<summary>Debug Bilgisi</summary>
							<pre>${JSON.stringify(response, null, 2)}</pre>
						</details>
					</div>
				`);
			}
		},
		error: function(xhr, status, error) {
			console.log('AJAX Error:', { xhr, status, error });
			console.log('Response Text:', xhr.responseText);
			
			$('#stokBilgisiLoading').hide();
			$('#stokBilgisiContent').show().html(`
				<div class="alert alert-danger text-center">
					<i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
					<h5>Hata Oluştu</h5>
					<p>Stok verileri yüklenirken bir hata oluştu.</p>
					<small class="d-block mt-2">Detay: ${status} - ${error}</small>
					<small class="d-block">URL: <?= base_url("muhasebe/getStokBilgileri") ?></small>
				</div>
			`);
		}
	});
}

// Stok satırı oluştur
function createStokRow(index, data = {}) {
	console.log('createStokRow called with:', data);
	return `
		<tr data-index="${index}">
			<td class="text-center">
				<small class="text-muted">${data.satisStok_id || '-'}</small>
			</td>
			<td>
				<input type="hidden" name="stokId[]" value="${data.stok_id || ''}" class="stokId">
				<input type="hidden" name="satisStokId[]" value="${data.satisStok_id || ''}" class="satisStokId">
				<select class="form-control stokDropdown" name="stokAdi[]" style="width: 100%;">
					${data.stok_adi ? `<option value="${data.stok_id}" selected>${data.stok_adi}${data.stok_kodu ? ' (Kod: ' + data.stok_kodu + ')' : ''}</option>` : '<option value="">Stok seçiniz...</option>'}
				</select>
			</td>
			<td style="display: none;">
				<input type="number" class="form-control miktar" name="miktar[]" 
					   value="${data.satisStok_miktar || 1}" min="1" step="0.01" 
					   onchange="hesaplaToplamTutar()">
			</td>
			<td>
				<input type="text" class="form-control birimFiyat" name="birimFiyat[]" 
					   value="${formatTurkishNumber(data.satisStok_fiyatMiktar || 0)}" 
					   placeholder="KDV dahil fiyat"
					   title="KDV dahil birim fiyat giriniz (123.456,78 formatında)"
					   onchange="hesaplaToplamTutar(); updateKdvBreakdown(this);"
					   onkeyup="formatBirimFiyatInput(this); updateKdvBreakdown(this);"
					   onblur="validateAndFormatBirimFiyat(this);">
				<small class="text-muted kdv-breakdown" style="font-size: 0.75em;"></small>
			</td>
			<td style="display: none;">
				<select class="form-control kdvOrani" name="kdvOrani[]" onchange="hesaplaToplamTutar(); updateKdvBreakdown(this.closest('tr').querySelector('.birimFiyat'));">
					<option value="0" ${(data.satisStok_kdv || 0) == 0 ? 'selected' : ''}>%0</option>
					<option value="1" ${(data.satisStok_kdv || 0) == 1 ? 'selected' : ''}>%1</option>
					<option value="8" ${(data.satisStok_kdv || 0) == 8 ? 'selected' : ''}>%8</option>
					<option value="18" ${(data.satisStok_kdv || 0) == 18 ? 'selected' : ''}>%18</option>
					<option value="20" ${(data.satisStok_kdv || 0) == 20 ? 'selected' : ''}>%20</option>
				</select>
			</td>
			<td>
				<input type="date" class="form-control abonelikBitisTarihi" name="abonelikBitisTarihi[]" 
					   value="${data.satisStok_abonelikBitisTarihi || ''}" 
					   placeholder="Abonelik Bitiş Tarihi">
			</td>
			<td>
				<button type="button" class="btn btn-danger btn-sm" onclick="stokSatiriSil(this)" 
						title="Satırı sil">
					<i class="fa fa-trash"></i>
				</button>
			</td>
		</tr>
	`;
}

// Yeni stok satırı ekle
window.stokSatiriEkle = function() {
	const tbody = document.getElementById('stokTableBody');
	const newIndex = tbody.children.length;
	const newRow = createStokRow(newIndex, {});
	tbody.insertAdjacentHTML('beforeend', newRow);
	
	// Yeni satır için dropdown'u başlat
	initStokAutocomplete();
	
	// Yeni satırdaki dropdown'u aç
	const newRowElement = tbody.lastElementChild;
	const stokDropdown = newRowElement.querySelector('.stokDropdown');
	if (stokDropdown) {
		$(stokDropdown).select2('open');
	}
};

// Stok satırı sil
function stokSatiriSil(button) {
	if (document.getElementById('stokTableBody').children.length > 1) {
		button.closest('tr').remove();
		hesaplaToplamTutar();
	} else {
		alert('En az bir stok satırı olması gereklidir.');
	}
}

// Toplam tutarı hesapla
function hesaplaToplamTutar() {
	let toplam = 0;
	
	console.log('hesaplaToplamTutar çağrıldı');
	
	// Sadece normal stok satırlarını hesapla, toplam satırını hariç tut
	const rows = document.querySelectorAll('#stokTableBody tr:not(.toplam-satir)');
	console.log('Bulunan satır sayısı:', rows.length);
	
	rows.forEach(function(row, index) {
		const miktarInput = row.querySelector('.miktar');
		const birimFiyatInput = row.querySelector('.birimFiyat');
		const kdvOraniInput = row.querySelector('.kdvOrani');
		
		if (miktarInput && birimFiyatInput && kdvOraniInput) {
			const miktar = parseFloat(miktarInput.value) || 0;
			// Birim fiyatı Türkçe formatdan parse et
			const birimFiyatKdvDahil = parseTurkishNumber(birimFiyatInput.value) || 0;
			
			console.log(`Satır ${index + 1}: miktar=${miktar}, birimFiyat=${birimFiyatKdvDahil}`);
			
			// Birim fiyat KDV dahil olduğu için direkt çarpım yapıyoruz
			const toplamSatir = miktar * birimFiyatKdvDahil;
			
			toplam += toplamSatir;
		}
	});
	
	console.log('Hesaplanan toplam:', toplam);
	
	const toplamTutarElement = document.getElementById('toplamTutar');
	if (toplamTutarElement) {
		const formattedToplam = toplam.toLocaleString('tr-TR', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + ' ₺';
		console.log('Formatlanan toplam:', formattedToplam);
		toplamTutarElement.value = formattedToplam;
	} else {
		console.log('toplamTutar elementi bulunamadı');
	}
	
	// Kalan tutarı hesapla
	hesaplaKalanTutar(toplam);
}

// Kalan tutarı hesapla (Stok Toplam - Tahsilat Toplam)
function hesaplaKalanTutar(stokToplam) {
	const toplamTahsilatElement = document.getElementById('toplamTahsilat');
	const kalanTutarElement = document.getElementById('kalanTutar');
	
	if (toplamTahsilatElement && kalanTutarElement) {
		// Tahsilat toplamını al (sadece sayıları)
		const tahsilatToplamStr = toplamTahsilatElement.value.replace(/[^\d]/g, '');
		const tahsilatToplam = parseFloat(tahsilatToplamStr) || 0;
		
		console.log('Stok Toplam:', stokToplam, 'Tahsilat Toplam:', tahsilatToplam);
		
		// Kalan tutarı hesapla
		const kalanTutar = stokToplam - tahsilatToplam;
		
		// Kalan tutara göre renklendirme
		let backgroundColor = '#d4edda'; // Yeşil (ödendi)
		let textColor = '#155724';
		let borderColor = '#c3e6cb';
		
		if (kalanTutar > 0) {
			backgroundColor = '#fff3cd'; // Sarı (kalan var)
			textColor = '#856404';
			borderColor = '#ffc107';
		} else if (kalanTutar < 0) {
			backgroundColor = '#f8d7da'; // Kırmızı (fazla ödeme)
			textColor = '#721c24';
			borderColor = '#f5c6cb';
		}
		
		// Kalan tutarı input'a yaz
		const formattedKalan = Math.abs(kalanTutar).toLocaleString('tr-TR', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + ' ₺';
		kalanTutarElement.value = formattedKalan;
		
		// Renklendirmeyi uygula
		kalanTutarElement.style.backgroundColor = backgroundColor;
		kalanTutarElement.style.color = textColor;
		kalanTutarElement.style.borderColor = borderColor;
		
		// Satırı da renklendir
		const kalanSatir = kalanTutarElement.closest('tr');
		if (kalanSatir) {
			kalanSatir.style.backgroundColor = backgroundColor;
			kalanSatir.style.borderColor = borderColor;
		}
	}
}

// Türkçe sayı formatından sayıya çevirme (123.456,78 -> 123456.78)
function parseTurkishNumber(str) {
	if (!str) return 0;
	
	// String'e çevir ve temizle
	str = str.toString().trim();
	
	// Sadece rakam, nokta ve virgül bırak
	str = str.replace(/[^\d.,]/g, '');
	
	if (str === '') return 0;
	
	// Eğer virgül varsa, Türkçe format kabul et
	if (str.includes(',')) {
		// Son virgülden sonra en fazla 2 karakter varsa, ondalık ayırıcı
		const parts = str.split(',');
		if (parts.length === 2 && parts[1].length <= 2) {
			// Türkçe format: 123.456,78
			str = str.replace(/\./g, '').replace(',', '.');
		} else {
			// Birden fazla virgül varsa sadece sayıları al
			str = str.replace(/[^0-9]/g, '');
		}
	}
	
	return parseFloat(str) || 0;
}

// Sayıyı Türkçe formata çevirme (123456.78 -> 123.456,78)
function formatTurkishNumber(num) {
	if (!num || num == 0) return '0,00';
	
	num = parseFloat(num);
	if (isNaN(num)) return '0,00';
	
	// 2 ondalık basamakla formatla
	return num.toLocaleString('tr-TR', {
		minimumFractionDigits: 2,
		maximumFractionDigits: 2
	});
}

// Birim fiyat input formatla (kullanıcı yazarken)
function formatBirimFiyatInput(input) {
	// Sadece rakam, nokta ve virgüle izin ver
	let value = input.value.replace(/[^\d.,]/g, '');
	input.value = value;
}

// Birim fiyat validasyon ve format (kullanıcı alanı terk ettiğinde)
function validateAndFormatBirimFiyat(input) {
	const parsed = parseTurkishNumber(input.value);
	input.value = formatTurkishNumber(parsed);
	
	// Hesaplamayı tetikle
	hesaplaToplamTutar();
	updateKdvBreakdown(input);
}

// KDV ayrıştırmasını göster
function updateKdvBreakdown(input) {
	const row = input.closest('tr');
	const kdvDahilFiyat = parseTurkishNumber(input.value) || 0;
	const kdvOrani = parseFloat(row.querySelector('.kdvOrani').value) || 0;
	const breakdownElement = row.querySelector('.kdv-breakdown');
	
	if (kdvDahilFiyat > 0 && kdvOrani > 0) {
		const kdvHaricFiyat = kdvDahilFiyat / (1 + (kdvOrani / 100));
		const kdvTutari = kdvDahilFiyat - kdvHaricFiyat;
		
		breakdownElement.innerHTML = 
			`KDV Hariç: ${formatTurkishNumber(kdvHaricFiyat)} ₺ + KDV(${kdvOrani}%): ${formatTurkishNumber(kdvTutari)} ₺`;
	} else {
		breakdownElement.innerHTML = '';
	}
}

// Tahsilat detaylarını toggle etmek için fonksiyon
function toggleDetay(element) {
	const parent = element.closest('td');
	const shortSpan = parent.querySelector('.detay-short');
	const fullSpan = parent.querySelector('.detay-full');
	
	if (shortSpan.style.display === 'none') {
		// Şu an tam metin gösteriliyor, kısa versiyonu göster
		shortSpan.style.display = 'inline';
		fullSpan.style.display = 'none';
		element.textContent = 'Daha fazla göster';
	} else {
		// Şu an kısa metin gösteriliyor, tam versiyonu göster
		shortSpan.style.display = 'none';
		fullSpan.style.display = 'inline';
		element.textContent = 'Daha az göster';
	}
}

// Stok dropdown başlat
function initStokAutocomplete() {
	console.log('initStokAutocomplete çağrıldı');
	
	
	$('.stokDropdown').each(function() {
		console.log('Dropdown işleniyor:', this);
		
		// Eğer zaten Select2 uygulanmışsa, yeniden uygulama
		if ($(this).data('select2')) {
			console.log('Mevcut Select2 yok ediliyor');
			$(this).select2('destroy');
		}
		
		$(this).select2({
			theme: 'bootstrap-5',
			width: '100%',
			placeholder: 'Stok seçiniz...',
			allowClear: true,
			ajax: {
				url: '<?= base_url('stok_dropdown_endpoint.php') ?>', // Real database endpoint
				dataType: 'json',
				delay: 250,
				data: function (params) {
					console.log('AJAX İstek parametreleri:', params);
					return {
						q: params.term, // arama terimi
						page: params.page || 1
					};
				},
				processResults: function (data, params) {
					console.log('AJAX Yanıtı alındı:', data);
					params.page = params.page || 1;
					
					if (data.error) {
						console.error('API Hatası:', data.error);
					}
					
					return {
						results: data.results || [],
						pagination: {
							more: data.pagination && data.pagination.more
						}
					};
				},
				cache: true,
				error: function(xhr, status, error) {
					console.error('AJAX Hatası:', {xhr, status, error});
					console.error('Yanıt Metni:', xhr.responseText);
				}
			},
			minimumInputLength: 0, // Dropdown açıldığında tüm stokları göster
			escapeMarkup: function (markup) { 
				return markup; 
			},
			templateResult: function(data) {
				if (data.loading) return data.text;
				return data.text;
			},
			templateSelection: function(data) {
				return data.text || data.id;
			}
		}).on('select2:select', function (e) {
			const selectedData = e.params.data;
			console.log('Stok seçildi:', selectedData);
			
			if (selectedData.stok_data) {
				const row = $(this).closest('tr');
				const stokData = selectedData.stok_data;
				
				// Stok ID'yi gizli alana kaydet
				row.find('.stokId').val(stokData.stok_id);
				console.log('Stok ID ayarlandı:', stokData.stok_id);
				
				// KDV oranını önce ayarla
				const kdvSelect = row.find('.kdvOrani');
				const kdvOrani = stokData.stok_satisKDV || 20;
				kdvSelect.val(kdvOrani);
				console.log('KDV ayarlandı:', kdvOrani);
				
				// Satış fiyatını KDV dahil olarak hesapla
				const satisFiyatiKdvHaric = parseFloat(stokData.stok_satisFiyati) || 0;
				const kdvCarpani = 1 + (kdvOrani / 100);
				const satisFiyatiKdvDahil = satisFiyatiKdvHaric * kdvCarpani;
				
				row.find('.birimFiyat').val(satisFiyatiKdvDahil.toFixed(2));
				console.log('Birim fiyat ayarlandı:', satisFiyatiKdvDahil.toFixed(2));
				
				// KDV ayrıştırmasını güncelle
				updateKdvBreakdown(row.find('.birimFiyat')[0]);
				
				// Toplam tutarı yeniden hesapla
				hesaplaToplamTutar();
			}
		}).on('select2:clear', function (e) {
			console.log('Select2 temizlendi');
			// Temizlendiğinde ilgili alanları da temizle
			const row = $(this).closest('tr');
			row.find('.stokId').val('');
			row.find('.birimFiyat').val('0');
			row.find('.kdvOrani').val('20');
			
			// KDV ayrıştırmasını temizle
			row.find('.kdv-breakdown').html('');
			
			// Toplam tutarı yeniden hesapla
			hesaplaToplamTutar();
		}).on('select2:open', function (e) {
			console.log('Select2 açıldı');
		}).on('select2:opening', function (e) {
			console.log('Select2 açılıyor...');
		});
		
		console.log('Dropdown için Select2 başlatıldı');
	});
	
	console.log('initStokAutocomplete tamamlandı');
}

// Stok bilgilerini kaydet
$('#stokBilgisiKaydetBtn').click(function() {
	const formData = [];
	const satisId = $('#stokBilgisiModal').data('satisId');
	const cariId = $('#stokBilgisiModal').data('cariId');
	
	console.log('Kaydet clicked with:', { satisId, cariId });
	
	document.querySelectorAll('#stokTableBody tr').forEach(function(row) {
		// Toplam satırını atla
		if (row.classList.contains('toplam-satir')) {
			return;
		}
		
		const stokDropdown = row.querySelector('.stokDropdown');
		const selectedOption = stokDropdown ? stokDropdown.options[stokDropdown.selectedIndex] : null;
		
		// Element kontrolü ekle
		const satisStokIdEl = row.querySelector('.satisStokId');
		const stokIdEl = row.querySelector('.stokId');
		const miktarEl = row.querySelector('.miktar');
		const birimFiyatEl = row.querySelector('.birimFiyat');
		const kdvOraniEl = row.querySelector('.kdvOrani');
		const abonelikBitisTarihiEl = row.querySelector('.abonelikBitisTarihi');
		
		if (!satisStokIdEl || !stokIdEl || !miktarEl || !birimFiyatEl || !kdvOraniEl || !abonelikBitisTarihiEl) {
			console.warn('Bazı form elementleri bulunamadı:', {
				satisStokId: !!satisStokIdEl,
				stokId: !!stokIdEl,
				miktar: !!miktarEl,
				birimFiyat: !!birimFiyatEl,
				kdvOrani: !!kdvOraniEl,
				abonelikBitisTarihi: !!abonelikBitisTarihiEl
			});
			return;
		}
		
		// Türkçe sayı formatını parse et
		function parseTurkishNumber(value) {
			if (!value) return 0;
			
			// String'e çevir
			let numStr = value.toString().trim();
			
			// Sadece rakam, nokta ve virgül bırak
			numStr = numStr.replace(/[^\d.,]/g, '');
			
			// Eğer boşsa 0 döndür
			if (!numStr) return 0;
			
			// Türkçe format kontrolü (414.000,50 formatında)
			if (numStr.includes('.') && numStr.includes(',')) {
				// Hem nokta hem virgül var - Türkçe format
				// Noktaları kaldır (binlik ayırıcı), virgülü ondalık ayırıcı yap
				numStr = numStr.replace(/\./g, '').replace(',', '.');
			} else if (numStr.includes(',')) {
				// Sadece virgül var
				const parts = numStr.split(',');
				if (parts.length === 2 && parts[1].length <= 2) {
					// Ondalık ayırıcı olarak virgül kullanılmış
					numStr = numStr.replace(',', '.');
				} else {
					// Binlik ayırıcı olarak virgül kullanılmış
					numStr = numStr.replace(/,/g, '');
				}
			} else if (numStr.includes('.')) {
				// Sadece nokta var
				const parts = numStr.split('.');
				if (parts.length === 2 && parts[1].length <= 2) {
					// Ondalık ayırıcı olarak nokta kullanılmış - değiştirme
				} else {
					// Binlik ayırıcı olarak nokta kullanılmış
					numStr = numStr.replace(/\./g, '');
				}
			}
			
			return parseFloat(numStr) || 0;
		}
		
		const stokData = {
			satisStok_id: satisStokIdEl.value,
			stok_id: stokIdEl.value,
			stok_adi: selectedOption ? selectedOption.text.split(' (')[0] : '', // Sadece stok adını al, kodu çıkar
			miktar: parseTurkishNumber(miktarEl.value),
			birim_fiyat: parseTurkishNumber(birimFiyatEl.value),
			kdv_orani: kdvOraniEl.value,
			abonelik_bitis_tarihi: abonelikBitisTarihiEl.value
		};
		
		console.log('Stok data prepared:', {
			original_birim_fiyat: birimFiyatEl.value,
			parsed_birim_fiyat: stokData.birim_fiyat,
			miktar: stokData.miktar
		});
		
		if (stokData.stok_id && stokData.miktar && stokData.birim_fiyat) {
			formData.push(stokData);
		}
	});
	
	if (formData.length === 0) {
		alert('En az bir stok seçip, miktar ve birim fiyat girmeniz gereklidir.');
		return;
	}
	
	// Sözleşme bilgilerini al
	const sozlesmeTarihi = document.getElementById('sozlesmeTarihi').value;
	const sozlesmeNo = document.getElementById('sozlesmeNo').value;
	const sozlesmeAciklama = document.getElementById('sozlesmeAciklama').value;
	
	// Sözleşme tarihi zorunlu
	if (!sozlesmeTarihi) {
		alert('Sözleşme tarihi zorunludur.');
		document.getElementById('sozlesmeTarihi').focus();
		return;
	}
	
	// AJAX ile kaydet
	$.ajax({
		url: '<?= base_url("stok_kaydet_endpoint.php") ?>',
		method: 'POST',
		data: {
			satis_id: satisId,
			cari_id: cariId,
			stok_bilgileri: formData,
			sozlesme_tarihi: sozlesmeTarihi,
			sozlesme_no: sozlesmeNo,
			sozlesme_aciklama: sozlesmeAciklama
		},
		dataType: 'json',
		beforeSend: function() {
			$('#stokBilgisiKaydetBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Kaydediliyor...');
		},
		success: function(response) {
			if (response.success) {
				alert('Stok bilgileri başarıyla kaydedildi.');
				$('#stokBilgisiModal').modal('hide');
				// Sayfayı yenile
				location.reload();
			} else {
				alert('Hata: ' + (response.message || 'Kayıt sırasında bir hata oluştu.'));
			}
		},
		error: function() {
			alert('Kayıt sırasında bir hata oluştu.');
		},
		complete: function() {
			$('#stokBilgisiKaydetBtn').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Kaydet');
		}
	});
});

// Modal açılırken satis_id ve cari_id'yi data attribute olarak sakla
$('#stokBilgisiModal').on('show.bs.modal', function (e) {
	const satisId = $(e.relatedTarget).data('satis-id');
	const cariId = $(e.relatedTarget).data('cari-id');
	$(this).data('satisId', satisId);
	$(this).data('cariId', cariId);
});

// Stok görsel butonu oluşturma fonksiyonu
function generateStokGorselButton(data) {
	if (!data || !data.satisStok_id) {
		return '<span class="badge badge-secondary">Henüz kaydedilmemiş</span>';
	}
	
	let html = '';
	let gorselSayisi = 0;
	
	// Stok görsellerini kontrol et (varsa)
	if (data.stok_gorselleri && data.stok_gorselleri.length > 0) {
		gorselSayisi = data.stok_gorselleri.length;
		
		// Görsel galerisi butonu
		html += `
			<button class="btn btn-sm btn-success" onclick="showStokGorselGaleri('${data.satisStok_id}', '${data.stok_adi}', ${JSON.stringify(data.stok_gorselleri).replace(/"/g, '&quot;')})">
				<i class="fa fa-images"></i> (${gorselSayisi})
			</button>
		`;
	}
	
	// Her zaman yükleme butonu göster
	html += `
		<button class="btn btn-sm btn-primary ${gorselSayisi > 0 ? 'ml-1' : ''}" onclick="uploadStokGorsel('${data.satisStok_id}', '${data.stok_adi || 'Stok'}')">
			<i class="fa fa-upload"></i> ${gorselSayisi > 0 ? 'Ekle' : 'Yükle'}
		</button>
	`;
	
	return html;
}

// Görsel gösterme fonksiyonu
function showGorsel(gorselUrl, tahsilatTipi) {
	$('#gorselModalTitle').text(tahsilatTipi + ' Görseli');
	
	// Dosya uzantısını kontrol et
	const fileExtension = gorselUrl.split('.').pop().toLowerCase();
	const modalBody = $('#gorselModalImage').closest('.modal-body');
	
	if (fileExtension === 'pdf') {
		// PDF için özel görünüm
		modalBody.html(`
			<div class="text-center">
				<div class="pdf-preview-container" style="min-height: 400px; border: 2px dashed #ddd; border-radius: 8px; padding: 20px;">
					<i class="fa fa-file-pdf fa-5x text-danger mb-3"></i>
					<h5>PDF Dosyası</h5>
					<p class="text-muted mb-3">PDF dosyasını görüntülemek için aşağıdaki butonları kullanın:</p>
					<div class="btn-group" role="group">
						<button type="button" class="btn btn-primary" onclick="window.open('${gorselUrl}', '_blank')">
							<i class="fa fa-external-link"></i> Yeni Sekmede Aç
						</button>
						<button type="button" class="btn btn-info" onclick="showPdfInModal('${gorselUrl}')">
							<i class="fa fa-eye"></i> Modal İçinde Görüntüle
						</button>
					</div>
				</div>
			</div>
		`);
	} else {
		// Resim dosyaları için normal görünüm
		modalBody.html('<img id="gorselModalImage" src="' + gorselUrl + '" class="img-fluid" style="max-height: 500px;" alt="Görsel">');
	}
	
	$('#gorselModal').modal('show');
	
	// Modal kapandığında orijinal yapıyı geri getir
	$('#gorselModal').one('hidden.bs.modal', function() {
		$(this).find('.modal-body').html('<img id="gorselModalImage" src="" class="img-fluid" style="max-height: 500px;" alt="Görsel">');
	});
}

// PDF'yi modal içinde gösterme fonksiyonu
function showPdfInModal(pdfUrl) {
	const modalBody = $('#gorselModalImage').closest('.modal-body');
	modalBody.html(`
		<div class="text-center mb-3">
			<button type="button" class="btn btn-secondary btn-sm" onclick="showGorsel('${pdfUrl}', 'PDF')">
				<i class="fa fa-arrow-left"></i> Geri
			</button>
			<button type="button" class="btn btn-primary btn-sm ml-2" onclick="window.open('${pdfUrl}', '_blank')">
				<i class="fa fa-external-link"></i> Yeni Sekmede Aç
			</button>
		</div>
		<div style="width: 100%; height: 600px; border: 1px solid #ddd;">
			<iframe src="${pdfUrl}" style="width: 100%; height: 100%; border: none;" type="application/pdf">
				<p>PDF görüntülenemiyor. <a href="${pdfUrl}" target="_blank">Buraya tıklayarak açabilirsiniz.</a></p>
			</iframe>
		</div>
	`);
}

// Görsel galeri fonksiyonu
function showGorselGaleri(uniqueId, tahsilatTipi, gorseller) {
	let html = `
		<div class="container-fluid">
			<div class="row">
	`;
	
	gorseller.forEach(function(gorsel, index) {
		html += `
			<div class="col-md-4 col-sm-6 mb-3">
				<div class="card">
					<div class="card-img-container" style="height: 200px; overflow: hidden; position: relative;">
						${gorsel.dosya_adi.toLowerCase().endsWith('.pdf') ? 
							`<div class="d-flex align-items-center justify-content-center h-100 bg-light">
								<i class="fa fa-file-pdf fa-3x text-danger"></i>
								<div class="ml-2">
									<small class="d-block">PDF Dosyası</small>
									<small class="text-muted">${gorsel.dosya_adi}</small>
								</div>
							</div>` :
							`<img src="${gorsel.url}" class="card-img-top" style="height: 100%; object-fit: cover; cursor: pointer;" 
								 onclick="showGorsel('${gorsel.url}', '${tahsilatTipi}')" alt="Görsel ${index + 1}">`
						}
					</div>
					<div class="card-footer p-2">
						<div class="d-flex justify-content-between align-items-center">
							<small class="text-muted">${gorsel.tip === 'ana' ? 'Ana Görsel' : 'Ek Görsel'}</small>
							<div>
								<button class="btn btn-sm btn-primary" onclick="window.open('${gorsel.url}', '_blank')" title="Tam Boyut">
									<i class="fa fa-external-link"></i>
								</button>
								<button class="btn btn-sm btn-danger ml-1" onclick="deleteGorselFromGaleri('${uniqueId}', '${gorsel.id}', '${gorsel.dosya_adi}')" title="Sil">
									<i class="fa fa-trash"></i>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		`;
	});
	
	html += `
			</div>
		</div>
	`;
	
	$('#gorselModalTitle').text(tahsilatTipi + ' Görselleri (' + gorseller.length + ')');
	$('#gorselModalImage').closest('.modal-body').html(html);
	$('#gorselModal').modal('show');
	
	// Modal kapandığında orijinal yapıyı geri getir
	$('#gorselModal').one('hidden.bs.modal', function() {
		$(this).find('.modal-body').html('<img id="gorselModalImage" src="" class="img-fluid" style="max-height: 500px;" alt="Görsel">');
	});
}

// Görsel yükleme fonksiyonu
function uploadGorsel(uniqueId, tipKod, sourceId, gorselAlan) {
	$('#uploadModalTitle').text('Görsel Yükle');
	$('#uploadForm').data('unique-id', uniqueId);
	$('#uploadForm').data('tip-kod', tipKod);
	$('#uploadForm').data('source-id', sourceId);
	$('#uploadForm').data('gorsel-alan', gorselAlan);
	
	// Modal'ı temizle
	$('#gorselFile').val('');
	$('.custom-file-label').text('Dosya(lar) seçin...');
	$('#selectedFiles').hide();
	$('#fileList').empty();
	
	$('#uploadModal').modal('show');
}

// Upload form submit event handler
$('#uploadForm').on('submit', function(e) {
	e.preventDefault();
	
	const formData = new FormData();
	const fileInput = document.getElementById('gorselFile');
	const files = fileInput.files;
	
	if (files.length === 0) {
		Swal.fire('Uyarı', 'Lütfen en az bir dosya seçin!', 'warning');
		return;
	}
	
	// Form verilerini hazırla
	const tipKod = $(this).data('tip-kod');
	const sourceId = $(this).data('source-id');
	
	// Tip koduna göre tip parametresini belirle
	let tipParam = '';
	switch(tipKod) {
		case '1': // Banka
			tipParam = 'bh';
			break;
		case '2': // Çek  
			tipParam = 'cek';
			break;
		case '4': // Senet
			tipParam = 'senet';
			break;
	}
	
	// Çoklu dosyaları FormData'ya ekle
	for (let i = 0; i < files.length; i++) {
		formData.append('gorseller[]', files[i]);
	}
	formData.append('tip', tipParam);
	formData.append('id', sourceId);
	
	// AJAX ile yükle
	$.ajax({
		url: '<?= base_url("muhasebe/uploadTahsilatGorsel") ?>',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		beforeSend: function() {
			$('#uploadModal .btn[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Yükleniyor...');
		},
		success: function(response) {
			console.log('Upload response:', response);
			
			// Response'un string olup olmadığını kontrol et
			if (typeof response === 'string') {
				try {
					response = JSON.parse(response);
				} catch(e) {
					console.error('JSON parse error:', e);
					Swal.fire("Hata!", "Sunucu yanıtı işlenemedi: " + response, "error");
					return;
				}
			}
			
			if (response.success) {
				let message = response.message || "Görseller başarıyla yüklendi.";
				if (response.errors && response.errors.length > 0) {
					message += "\n\nUyarılar:\n" + response.errors.join("\n");
				}
				
				Swal.fire({
					title: "Başarılı!",
					text: message,
					icon: "success",
					confirmButtonText: "Tamam"
				}).then(() => {
					$('#uploadModal').modal('hide');
					// Tahsilat modalını yenile
					const cariId = $('#tahsilatModal').data('cari-id');
					const cariAd = $('#tahsilatModal').data('cari-ad');
					showTahsilatModal(cariId, cariAd);
				});
			} else {
				Swal.fire("Hata!", response.message || "Yükleme işlemi başarısız oldu.", "error");
			}
		},
		error: function(xhr, status, error) {
			console.error('Upload error:', {xhr, status, error});
			Swal.fire("Hata!", "Yükleme işlemi sırasında bir hata oluştu: " + error, "error");
		},
		complete: function() {
			$('#uploadModal .btn[type="submit"]').prop('disabled', false).html('<i class="fa fa-upload"></i> Yükle');
			$('#gorselFile').val(''); // Dosya inputunu temizle
			$('.custom-file-label').text('Dosya(lar) seçin...');
			$('#selectedFiles').hide();
			$('#fileList').empty();
		}
	});
});

// Dosya seçimi değiştiğinde
$(document).on('change', '#gorselFile', function() {
	const files = this.files;
	const label = $(this).next('.custom-file-label');
	const selectedFiles = $('#selectedFiles');
	const fileList = $('#fileList');
	
	if (files.length === 0) {
		label.text('Dosya(lar) seçin...');
		selectedFiles.hide();
		return;
	}
	
	if (files.length === 1) {
		label.text(files[0].name);
	} else {
		label.text(files.length + ' dosya seçildi');
	}
	
	// Seçilen dosyaları listele
	fileList.empty();
	for (let i = 0; i < files.length; i++) {
		const file = files[i];
		const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
		const fileType = file.type.startsWith('image/') ? 'Resim' : (file.type === 'application/pdf' ? 'PDF' : 'Dosya');
		
		fileList.append(`
			<li class="border rounded p-2 mb-1 bg-light">
				<i class="fa fa-${file.type.startsWith('image/') ? 'image' : 'file-pdf'} text-${file.type.startsWith('image/') ? 'success' : 'danger'} mr-2"></i>
				<strong>${file.name}</strong>
				<small class="text-muted ml-2">(${fileType}, ${fileSize} MB)</small>
			</li>
		`);
	}
	
	selectedFiles.show();
});

// Görsel silme fonksiyonu
function deleteGorsel(uniqueId, tipKod, sourceId) {
	Swal.fire({
		title: "Emin misiniz?",
		text: "Bu görseli silmek istediğinize emin misiniz?",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#dc3545",
		confirmButtonText: "Evet, Sil",
		cancelButtonText: "İptal"
	}).then((result) => {
		if (result.isConfirmed) {
			// Tip koduna göre tip parametresini belirle
			let tipParam = '';
			switch(tipKod) {
				case '1': // Banka
					tipParam = 'bh';
					break;
				case '2': // Çek  
					tipParam = 'cek';
					break;
				case '4': // Senet
					tipParam = 'senet';
					break;
			}
			
			$.ajax({
				url: '<?= base_url("muhasebe/deleteTahsilatGorsel") ?>',
				method: 'POST',
				data: {
					tip: tipParam,
					id: sourceId
				},
				dataType: 'json',
				beforeSend: function() {
					Swal.fire({
						title: "Siliniyor",
						text: "Görsel siliniyor...",
						icon: "info",
						showConfirmButton: false,
						allowOutsideClick: false
					});
				},
				success: function(response) {
					console.log('Delete response:', response);
					if (response.success) {
						Swal.fire({
							title: "Başarılı!",
							text: response.message || "Görsel başarıyla silindi.",
							icon: "success",
							confirmButtonText: "Tamam"
						}).then(() => {
							// Tahsilat modalını yenile
							const cariId = $('#tahsilatModal').data('cari-id');
							const cariAd = $('#tahsilatModal').data('cari-ad');
							showTahsilatModal(cariId, cariAd);
						});
					} else {
						Swal.fire("Hata!", response.message || "Silme işlemi başarısız oldu.", "error");
					}
				},
				error: function(xhr, status, error) {
					console.error('Delete error:', {xhr, status, error});
					Swal.fire("Hata!", "Silme işlemi sırasında bir hata oluştu: " + error, "error");
				}
			});
		}
	});
}

// Galeriden görsel silme fonksiyonu
function deleteGorselFromGaleri(uniqueId, gorselId, dosyaAdi) {
	Swal.fire({
		title: "Emin misiniz?",
		text: "Bu görseli silmek istediğinize emin misiniz?\n\nDosya: " + dosyaAdi,
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#dc3545",
		confirmButtonText: "Evet, Sil",
		cancelButtonText: "İptal"
	}).then((result) => {
		if (result.isConfirmed) {
			// uniqueId'den tip kodunu ve source_id'yi çıkar
			const parts = uniqueId.split('_');
			let tipKod = '';
			let sourceId = parts[1];
			
			switch(parts[0]) {
				case 'bh':
					tipKod = '1';
					break;
				case 'cek':
					tipKod = '2';
					break;
				case 's':
					tipKod = '4';
					break;
			}
			
			deleteGorsel(uniqueId, tipKod, sourceId);
		}
	});
}

// Excel export fonksiyonu
window.exportToExcel = function() {
	// Excel export işlemi için AJAX çağrısı veya form submit
	window.open('<?= base_url("muhasebe/tam-ana-rapor-excel") ?>', '_blank');
}

// Tooltip'leri aktifleştir
$(function () {
	$('[data-toggle="tooltip"]').tooltip()
});

// Tahsilat Düzenle Modal Aç
function tahsilatDuzenleModalAc(ch_id) {
	// Burada modal açılacak ve ilgili tahsilat verisi yüklenecek
	alert('Tahsilat düzenleme için modal açılacak. ID: ' + ch_id);
	// TODO: Düzenleme modalı ve AJAX ile veri çekme işlemi eklenecek
}

// Tahsilat düzenleme fonksiyonu
function editTahsilat(uniqueId, tipKod, sourceId, tahsilatTipi) {
	console.log('Düzenle:', {uniqueId, tipKod, sourceId, tahsilatTipi});
	
	// Tahsilat tipine göre yönlendirme yap
	let editUrl = '';
	switch(tipKod) {
		case '1': // Banka/Pos
			editUrl = '<?= base_url("banka/banka-hareketi-duzenle/") ?>' + sourceId;
			break;
		case '2': // Çek
			editUrl = '<?= base_url("cek/cek-duzenle/") ?>' + sourceId;
			break;
		case '3': // Kasa/Nakit
			editUrl = '<?= base_url("kasa/kasa-hareketi-duzenle/") ?>' + sourceId;
			break;
		case '4': // Senet
			editUrl = '<?= base_url("senet/senet-duzenle/") ?>' + sourceId;
			break;
		default:
			Swal.fire('Uyarı', 'Bilinmeyen tahsilat tipi!', 'warning');
			return;
	}
	
	// Yeni sekmede düzenleme sayfasını aç
	window.open(editUrl, '_blank');
}

// Tahsilat silme fonksiyonu
function deleteTahsilat(cariHareketId, tahsilatTipi, tutar) {
	console.log('Sil:', {cariHareketId, tahsilatTipi, tutar});
	
	// Eğer cari hareket ID'si yoksa işlemi durdur
	if (!cariHareketId || cariHareketId == 'null' || cariHareketId == '') {
		Swal.fire('Uyarı', 'Bu tahsilat kaydı için cari hareket ID\'si bulunamadı. Silme işlemi yapılamaz.', 'warning');
		return;
	}
	
	// Onay penceresi göster
	Swal.fire({
		title: "Emin misiniz?",
		text: `${tahsilatTipi} kaydını silmek istediğinize emin misiniz?\n\nTutar: ${parseFloat(tutar).toLocaleString('tr-TR', {minimumFractionDigits: 0, maximumFractionDigits: 0})} ₺\n\nBu işlem geri alınamaz!`,
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#dc3545",
		confirmButtonText: "Evet, Sil",
		cancelButtonText: "İptal"
	}).then((result) => {
		if (result.isConfirmed) {
			// AJAX ile silme işlemi - Mevcut sistem kullanılır
			$.ajax({
				url: '<?= base_url("muhasebe/tahsilatSil") ?>',
				method: 'POST',
				data: { 
					ch_id: cariHareketId  // Mevcut sistem parametresi
				},
				dataType: 'json',
				beforeSend: function() {
					Swal.fire({
						title: "İşlem Devam Ediyor",
						text: "Tahsilat kaydı siliniyor...",
						icon: "info",
						showConfirmButton: false,
						allowOutsideClick: false
					});
				},
				success: function(response) {
					console.log('Silme Response:', response);
					if (response.success) {
						Swal.fire({
							title: "Başarılı!",
							text: response.message || "Tahsilat kaydı başarıyla silindi.",
							icon: "success",
							confirmButtonText: "Tamam"
						}).then(() => {
							// Satırı tablodan kaldır
							$('[data-ch-id="' + cariHareketId + '"]').fadeOut(300, function() {
								$(this).remove();
								// Tabloyu yeniden hesapla
								updateTahsilatToplam();
							});
						});
					} else {
						Swal.fire("Hata!", response.message || "Tahsilat silinirken beklenmeyen bir hata oluştu.", "error");
					}
				},
				error: function(xhr, status, error) {
					console.error('Silme Hatası:', {xhr, status, error});
					let errorMessage = "Tahsilat silme işlemi sırasında bir hata oluştu.";
					
					// Daha detaylı hata mesajı
					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMessage = xhr.responseJSON.message;
					} else if (xhr.responseText) {
						try {
							const response = JSON.parse(xhr.responseText);
							errorMessage = response.message || errorMessage;
						} catch (e) {
							errorMessage += " (Sunucu hatası: " + xhr.status + ")";
						}
					}
					
					Swal.fire("Hata!", errorMessage, "error");
				}
			});
		}
	});
}

// Tutar ve vade tarihi değişikliklerini kaydet
window.kaydetTutarDegisiklikleri = function() {
	var degisiklikler = [];
	
	// Tutar değişikliklerini topla
	$('.tutar-degisti').each(function() {
		var $row = $(this);
		var uniqueId = $row.data('tahsilat-id');
		var tipKod = $row.data('tip-kod');
		var sourceId = $row.data('source-id');
		var chId = $row.data('ch-id');
		var yeniTutar = parseFloat($row.find('.tutar-input').val());
		var eskiTutar = parseFloat($row.data('original-tutar'));
		
		degisiklikler.push({
			unique_id: uniqueId,
			tip_kod: tipKod,
			source_id: sourceId,
			ch_id: chId,
			yeni_tutar: yeniTutar,
			eski_tutar: eskiTutar,
			degisiklik_tipi: 'tutar'
		});
	});
	
	// Vade tarihi değişikliklerini topla
	$('.vade-tarih-degisti').each(function() {
		var $row = $(this);
		var uniqueId = $row.data('tahsilat-id');
		var tipKod = $row.data('tip-kod');
		var sourceId = $row.data('source-id');
		var chId = $row.data('ch-id');
		var yeniVadeTarihi = $row.find('.vade-tarih-input').val();
		var eskiVadeTarihi = $row.find('.vade-tarih-input').data('original');
		
		degisiklikler.push({
			unique_id: uniqueId,
			tip_kod: tipKod,
			source_id: sourceId,
			ch_id: chId,
			yeni_vade_tarihi: yeniVadeTarihi,
			eski_vade_tarihi: eskiVadeTarihi,
			degisiklik_tipi: 'vade_tarihi'
		});
	});
	
	if (degisiklikler.length === 0) {
		Swal.fire('Bilgi', 'Değişiklik yapılmadı.', 'info');
		return;
	}
	
	// Onay penceresi göster
	Swal.fire({
		title: "Değişiklikleri Kaydet",
		text: `${degisiklikler.length} adet tahsilat bilgisi güncellenmek üzere. Devam etmek istiyor musunuz?`,
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#28a745",
		confirmButtonText: "Evet, Kaydet",
		cancelButtonText: "İptal"
	}).then((result) => {
		if (result.isConfirmed) {
			// AJAX ile kaydet
			$.ajax({
				url: '<?= base_url("muhasebe/tahsilatGuncelle") ?>',
				method: 'POST',
				data: { 
					degisiklikler: degisiklikler
				},
				dataType: 'json',
				beforeSend: function() {
					Swal.fire({
						title: "Kaydediliyor",
						text: "Tahsilat bilgileri kaydediliyor...",
						icon: "info",
						showConfirmButton: false,
						allowOutsideClick: false
					});
				},
				success: function(response) {
					console.log('Kaydet Response:', response);
					if (response.success) {
						Swal.fire({
							title: "Başarılı!",
							text: response.message || "Tahsilat bilgileri başarıyla kaydedildi.",
							icon: "success",
							confirmButtonText: "Tamam"
						}).then(() => {
							// Değişiklik işaretlerini kaldır
							$('.tutar-degisti, .vade-tarih-degisti').removeClass('tutar-degisti vade-tarih-degisti');
							
							// Orijinal değerleri güncelle
							$('.tutar-input').each(function() {
								$(this).data('original', $(this).val());
							});
							$('.vade-tarih-input').each(function() {
								$(this).data('original', $(this).val());
							});
							
							// Kaydet butonunu gizle
							$('#kaydetTahsilat').hide();
							$('.tutar-input').each(function() {
								$(this).data('original', $(this).val());
							});
							
							// Kaydet butonunu gizle
							$('#kaydetTahsilat').hide();
							
							// Ana sayfayı yenile (tutarlar değişti)
							location.reload();
						});
					} else {
						Swal.fire("Hata!", response.message || "Kaydetme işlemi başarısız oldu.", "error");
					}
				},
				error: function(xhr, status, error) {
					console.error('Kaydet Hatası:', {xhr, status, error});
					Swal.fire("Hata!", "Kaydetme işlemi sırasında bir hata oluştu: " + error, "error");
				}
			});
		}
	});
};

// Arama formu geliştirmeleri
$(document).ready(function() {
	// Sadece bizim cari_search inputumuz için autocomplete'i başlat
	$('#cari_search').autocomplete({
		source: function(request, response) {
			$.ajax({
				url: '<?= base_url("cari_arama_endpoint.php") ?>',
				type: 'GET',
				dataType: 'json',
				data: {
					term: request.term
				},
				beforeSend: function() {
					$('.ui-autocomplete').html('<li class="ui-menu-item"><div class="ui-menu-item-wrapper"><i class="fa fa-spinner fa-spin"></i> Aranıyor...</div></li>');
				},
				success: function(data) {
					response(data);
				},
				error: function() {
					console.log('Cari arama hatası');
					response([]);
				}
			});
		},
		minLength: 2,
		delay: 300,
		select: function(event, ui) {
			// Seçilen değeri sadece bizim input'a yaz
			$(this).val(ui.item.value);
			// Form otomatik olarak submit olmasın
			event.preventDefault();
			return false;
		},
		focus: function(event, ui) {
			$(this).val(ui.item.value);
			return false;
		}
	}).on('focus', function() {
		// Focus'ta minimum karakter sayısını karşılıyorsa arama yap
		if ($(this).val().length >= 2) {
			$(this).autocomplete('search', $(this).val());
		}
	});
	
	// Sadece bizim arama inputumuzda Enter tuşu ile arama
	$('#cari_search').on('keypress', function(e) {
		if (e.which == 13) { // Enter tuşu
			$('#searchForm').submit();
		}
	});
	
	// Arama formunu temizle
	$('#searchForm').on('reset', function() {
		window.location.href = '<?= base_url('muhasebe/tam-ana-rapor') ?>';
	});
	
	// Arama sonuçları sayısını göster
	<?php if (!empty($this->input->get('cari_search')) || !empty($this->input->get('aktivasyon_durum'))): ?>
		<?php 
		$active_filters = [];
		if (!empty($this->input->get('cari_search'))) {
			$active_filters[] = 'Cari: "' . htmlspecialchars($this->input->get('cari_search')) . '"';
		}
		if (!empty($this->input->get('aktivasyon_durum'))) {
			$active_filters[] = 'Durum: "' . htmlspecialchars($this->input->get('aktivasyon_durum')) . '"';
		}
		?>
		
		// Arama sonuçları bilgi çubuğunu ekle
		$('#raporTable_wrapper').prepend(
			'<div class="alert alert-info alert-dismissible fade show" role="alert">' +
			'<i class="fa fa-info-circle mr-2"></i>' +
			'<strong>Arama Sonuçları:</strong> <?= implode(', ', $active_filters) ?> filtreleri ile <?= isset($rapor_verileri) ? count($rapor_verileri) : 0 ?> kayıt bulundu.' +
			'<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
			'<span aria-hidden="true">&times;</span>' +
			'</button>' +
			'</div>'
		);
	<?php endif; ?>
	
// Tahsilat silme fonksiyonu
window.tahsilatSil = function(uniqueId, tipKod, sourceId) {
	Swal.fire({
		title: 'Tahsilat Silme Onayı',
		text: 'Bu tahsilat kaydını silmek istediğinizden emin misiniz?',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: 'Evet, Sil',
		cancelButtonText: 'İptal'
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				url: '<?= base_url("muhasebe/tahsilatSil") ?>',
				method: 'POST',
				data: {
					unique_id: uniqueId,
					tip_kod: tipKod,
					source_id: sourceId
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						Swal.fire('Silindi!', 'Tahsilat kaydı başarıyla silindi.', 'success');
						// Tahsilat listesini yenile
						const cariId = $('#tahsilatModal').data('cari-id');
						const cariAd = $('#tahsilatModal').data('cari-ad');
						showTahsilatModal(cariId, cariAd);
					} else {
						Swal.fire('Hata!', response.message || 'Tahsilat silinirken hata oluştu.', 'error');
					}
				},
				error: function(xhr, status, error) {
					console.error('Tahsilat silme hatası:', {xhr, status, error});
					let errorMessage = "Tahsilat silinirken beklenmeyen bir hata oluştu.";
					
					// Daha detaylı hata mesajı
					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMessage = xhr.responseJSON.message;
					} else if (xhr.responseText) {
						try {
							const response = JSON.parse(xhr.responseText);
							errorMessage = response.message || errorMessage;
						} catch (e) {
							errorMessage += " (HTTP " + xhr.status + ")";
						}
					}
					
					Swal.fire('Hata!', errorMessage, 'error');
				}
			});
		}
	});
};

// Yeni tahsilat ekleme fonksiyonu
window.yeniTahsilatEkle = function() {
	console.log('yeniTahsilatEkle fonksiyonu çağrıldı');
	const cariId = $('#tahsilatModal').data('cari-id');
	const cariAd = $('#tahsilatModal').data('cari-ad');
	
	Swal.fire({
		title: 'Yeni Tahsilat Ekle',
		html: `
			<div class="form-group text-left">
				<label for="tahsilatTipi">Tahsilat Tipi:</label>
				<select id="tahsilatTipi" class="form-control">
					<option value="1">Banka/Pos</option>
					<option value="2">Çek</option>
					<option value="3">Kasa/Nakit</option>
					<option value="4">Senet</option>
				</select>
			</div>
			<div class="form-group text-left">
				<label for="tahsilatTutar">Tutar:</label>
				<input type="number" id="tahsilatTutar" class="form-control" step="0.01" min="0" placeholder="0.00">
			</div>
			<div class="form-group text-left">
				<label for="tahsilatTarih">Tarih:</label>
				<input type="date" id="tahsilatTarih" class="form-control" value="${new Date().toISOString().split('T')[0]}">
			</div>
			<div class="form-group text-left">
				<label for="tahsilatBelgeNo">Belge No:</label>
				<input type="text" id="tahsilatBelgeNo" class="form-control" placeholder="Belge numarası (opsiyonel)">
			</div>
			<div class="form-group text-left">
				<label for="tahsilatAciklama">Açıklama:</label>
				<textarea id="tahsilatAciklama" class="form-control" rows="2" placeholder="Açıklama (opsiyonel)"></textarea>
			</div>
		`,
		width: 500,
		confirmButtonText: 'Ekle',
		showCancelButton: true,
		cancelButtonText: 'İptal',
		preConfirm: () => {
			const tipi = document.getElementById('tahsilatTipi').value;
			const tutar = document.getElementById('tahsilatTutar').value;
			const tarih = document.getElementById('tahsilatTarih').value;
			const belgeNo = document.getElementById('tahsilatBelgeNo').value;
			const aciklama = document.getElementById('tahsilatAciklama').value;
			
			if (!tutar || parseFloat(tutar) <= 0) {
				Swal.showValidationMessage('Lütfen geçerli bir tutar girin');
				return false;
			}
			if (!tarih) {
				Swal.showValidationMessage('Lütfen tarih girin');
				return false;
			}
			
			return {
				tip_kod: tipi,
				tutar: tutar,
				tarih: tarih,
				belge_no: belgeNo,
				aciklama: aciklama,
				cari_id: cariId
			};
		}
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				url: '<?= base_url("muhasebe/yeniTahsilatEkle") ?>',
				method: 'POST',
				data: result.value,
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						Swal.fire('Başarılı!', 'Yeni tahsilat kaydı başarıyla eklendi.', 'success');
						// Tahsilat listesini yenile
						showTahsilatModal(cariId, cariAd);
					} else {
						Swal.fire('Hata!', response.message || 'Tahsilat eklenirken hata oluştu.', 'error');
					}
				},
				error: function() {
					Swal.fire('Hata!', 'Tahsilat eklenirken beklenmeyen bir hata oluştu.', 'error');
				}
			});
		}
	});
};

// Stok görsel galeri fonksiyonu
window.showStokGorselGaleri = function(satisStokId, stokAdi, gorseller) {
	let html = `
		<div class="container-fluid">
			<div class="row">
	`;
	
	gorseller.forEach(function(gorsel, index) {
		html += `
			<div class="col-md-4 col-sm-6 mb-3">
				<div class="card">
					<div class="card-img-container" style="height: 200px; overflow: hidden; position: relative;">
						${gorsel.dosya_adi.toLowerCase().endsWith('.pdf') ? 
							`<div class="d-flex align-items-center justify-content-center h-100 bg-light">
								<i class="fa fa-file-pdf fa-3x text-danger"></i>
								<div class="ml-2">
									<small class="d-block">PDF Dosyası</small>
									<small class="text-muted">${gorsel.dosya_adi}</small>
								</div>
							</div>` :
							`<img src="${gorsel.url}" class="card-img-top" style="height: 100%; object-fit: cover; cursor: pointer;" 
								 onclick="showGorsel('${gorsel.url}', '${stokAdi}')" alt="Stok Görseli ${index + 1}">`
						}
					</div>
					<div class="card-footer p-2">
						<div class="d-flex justify-content-between align-items-center">
							<small class="text-muted">${gorsel.dosya_adi}</small>
							<button class="btn btn-sm btn-danger" onclick="stokGorselSil('${gorsel.id}', '${satisStokId}')">
								<i class="fa fa-trash"></i>
							</button>
						</div>
					</div>
				</div>
			</div>
		`;
	});
	
	html += `
			</div>
			<div class="row mt-3">
				<div class="col-md-12 text-center">
					<button class="btn btn-primary" onclick="uploadStokGorsel('${satisStokId}', '${stokAdi}')">
						<i class="fa fa-plus"></i> Yeni Görsel Ekle
					</button>
				</div>
			</div>
		</div>
	`;
	
	Swal.fire({
		title: `${stokAdi} - Stok Görselleri`,
		html: html,
		width: '90%',
		showCloseButton: true,
		showConfirmButton: false
	});
};

// Stok görsel yükleme fonksiyonu
window.uploadStokGorsel = function(satisStokId, stokAdi) {
	if (!satisStokId) {
		Swal.fire('Hata!', 'Önce stok kaydını kaydetmelisiniz.', 'error');
		return;
	}
	
	Swal.fire({
		title: `${stokAdi} - Görsel Yükle`,
		html: `
			<div class="form-group">
				<label>Görsel Dosyaları Seçin:</label>
				<div class="custom-file">
					<input type="file" class="custom-file-input" id="stokGorselFile" multiple accept="image/*,.pdf">
					<label class="custom-file-label">Dosya(lar) seçin...</label>
				</div>
				<small class="text-muted">JPG, PNG veya PDF formatında dosyalar yükleyebilirsiniz.</small>
			</div>
			<div id="stokSelectedFiles" style="display: none;">
				<h6>Seçilen Dosyalar:</h6>
				<ul id="stokFileList" class="list-unstyled"></ul>
			</div>
		`,
		showCancelButton: true,
		confirmButtonText: 'Yükle',
		cancelButtonText: 'İptal',
		preConfirm: () => {
			const files = document.getElementById('stokGorselFile').files;
			if (files.length === 0) {
				Swal.showValidationMessage('Lütfen en az bir dosya seçin');
				return false;
			}
			return files;
		}
	}).then((result) => {
		if (result.isConfirmed) {
			const files = result.value;
			const formData = new FormData();
			
			formData.append('satisStok_id', satisStokId);
			formData.append('stok_adi', stokAdi);
			
			for (let i = 0; i < files.length; i++) {
				formData.append('stok_gorseller[]', files[i]);
			}
			
			$.ajax({
				url: '<?= base_url("stok_gorsel_yukle_endpoint.php") ?>',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if (response.success) {
						Swal.fire('Başarılı!', 'Stok görselleri başarıyla yüklendi.', 'success');
						// Stok bilgilerini yenile
						const satisId = $('#stokBilgisiModal').data('satisId');
						const cariId = $('#stokBilgisiModal').data('cariId');
						// Modal'ı yeniden yükle
						$('#stokBilgisiModal').modal('hide');
						setTimeout(() => {
							const cariIsletme = $('#stokBilgisiModalLabel').text().split(' - ')[1] || 'Stok Bilgileri';
							openStokBilgisiModal(satisId, cariId, cariIsletme);
						}, 500);
					} else {
						Swal.fire('Hata!', response.message || 'Görsel yüklenirken hata oluştu.', 'error');
					}
				},
				error: function() {
					Swal.fire('Hata!', 'Görsel yüklenirken beklenmeyen bir hata oluştu.', 'error');
				}
			});
		}
	});
	
	// Dosya seçimi değiştiğinde
	$(document).off('change', '#stokGorselFile').on('change', '#stokGorselFile', function() {
		const files = this.files;
		const label = $(this).next('.custom-file-label');
		const selectedFiles = $('#stokSelectedFiles');
		const fileList = $('#stokFileList');
		
		if (files.length === 0) {
			label.text('Dosya(lar) seçin...');
			selectedFiles.hide();
			return;
		}
		
		if (files.length === 1) {
			label.text(files[0].name);
		} else {
			label.text(files.length + ' dosya seçildi');
		}
		
		// Seçilen dosyaları listele
		fileList.empty();
		for (let i = 0; i < files.length; i++) {
			const file = files[i];
			const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
			const fileType = file.type.startsWith('image/') ? 'Resim' : (file.type === 'application/pdf' ? 'PDF' : 'Dosya');
			
			fileList.append(`
				<li class="border rounded p-2 mb-1 bg-light">
					<i class="fa fa-${file.type.startsWith('image/') ? 'image' : 'file-pdf'} text-${file.type.startsWith('image/') ? 'success' : 'danger'} mr-2"></i>
					<strong>${file.name}</strong>
					<small class="text-muted ml-2">(${fileType}, ${fileSize} MB)</small>
				</li>
			`);
		}
		
		selectedFiles.show();
	});
};

// Stok görsel silme fonksiyonu
// Stok görsel silme fonksiyonu
window.stokGorselSil = function(gorselId, satisStokId) {
	Swal.fire({
		title: 'Görsel Silme Onayı',
		text: 'Bu görseli silmek istediğinizden emin misiniz?',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: 'Evet, Sil',
		cancelButtonText: 'İptal'
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				url: '<?= base_url("muhasebe/stokGorselSil") ?>',
				method: 'POST',
				data: {
					gorsel_id: gorselId,
					satisStok_id: satisStokId
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						Swal.fire('Silindi!', 'Görsel başarıyla silindi.', 'success');
						// Modal'ı kapat ve stok bilgilerini yenile
						Swal.close();
						const satisId = $('#stokBilgisiModal').data('satisId');
						const cariId = $('#stokBilgisiModal').data('cariId');
						// Modal'ı yeniden yükle
						const cariIsletme = $('#stokBilgisiModalLabel').text().split(' - ')[1] || 'Stok Bilgileri';
						openStokBilgisiModal(satisId, cariId, cariIsletme);
					} else {
						Swal.fire('Hata!', response.message || 'Görsel silinirken hata oluştu.', 'error');
					}
				},
				error: function() {
					Swal.fire('Hata!', 'Görsel silinirken beklenmeyen bir hata oluştu.', 'error');
				}
			});
		}
	});
};

});

// Satış Sözleşmesi Görsel Yükleme Fonksiyonu
window.satirGorselYukle = function(satisId) {
	if (!satisId) {
		Swal.fire('Hata!', 'Satış ID bulunamadı.', 'error');
		return;
	}
	
	Swal.fire({
		title: 'Satış Sözleşmesi Görsel Yükle',
		html: `
			<div class="form-group">
				<label>Görsel/Dosya Seçin:</label>
				<div class="custom-file">
					<input type="file" class="custom-file-input" id="satirGorselFile" multiple accept="image/*,.pdf">
					<label class="custom-file-label">Dosya(lar) seçin...</label>
				</div>
				<small class="text-muted">JPG, PNG veya PDF formatında dosyalar yükleyebilirsiniz.</small>
			</div>
			<div id="satirSelectedFiles" style="display: none;">
				<h6>Seçilen Dosyalar:</h6>
				<ul id="satirFileList" class="list-unstyled"></ul>
			</div>
		`,
		showCancelButton: true,
		confirmButtonText: 'Yükle',
		cancelButtonText: 'İptal',
		preConfirm: () => {
			const files = document.getElementById('satirGorselFile').files;
			if (files.length === 0) {
				Swal.showValidationMessage('Lütfen en az bir dosya seçin');
				return false;
			}
			return files;
		}
	}).then((result) => {
		if (result.isConfirmed) {
			const files = result.value;
			const formData = new FormData();
			
			formData.append('satis_id', satisId);
			
			for (let i = 0; i < files.length; i++) {
				formData.append('satis_gorseller[]', files[i]);
			}
			
			$.ajax({
				url: '<?= base_url("satis_gorsel_yukle_endpoint.php") ?>',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				beforeSend: function() {
					Swal.fire({
						title: 'Yükleniyor...',
						html: 'Dosyalar yükleniyor, lütfen bekleyin...',
						allowOutsideClick: false,
						didOpen: () => {
							Swal.showLoading();
						}
					});
				},
				success: function(response) {
					if (response.success) {
						Swal.fire('Başarılı!', 'Görseller başarıyla yüklendi.', 'success');
						$('#satisDosya').val(response.satis_dosya);
					} else {
						Swal.fire('Hata!', response.message || 'Görsel yüklenirken hata oluştu.', 'error');
					}
				},
				error: function() {
					Swal.fire('Hata!', 'Görsel yüklenirken beklenmeyen bir hata oluştu.', 'error');
				}
			});
		}
	});
	
	$(document).off('change', '#satirGorselFile').on('change', '#satirGorselFile', function() {
		const files = this.files;
		const label = $(this).next('.custom-file-label');
		const selectedFiles = $('#satirSelectedFiles');
		const fileList = $('#satirFileList');
		
		if (files.length === 0) {
			selectedFiles.hide();
			label.text('Dosya(lar) seçin...');
			return;
		}
		
		if (files.length === 1) {
			label.text(files[0].name);
		} else {
			label.text(files.length + ' dosya seçildi');
		}
		
		fileList.empty();
		for (let i = 0; i < files.length; i++) {
			const file = files[i];
			const fileSize = (file.size / 1024 / 1024).toFixed(2);
			const fileType = file.type.startsWith('image/') ? 'Resim' : (file.type === 'application/pdf' ? 'PDF' : 'Dosya');
			
			fileList.append(`
				<li class="border rounded p-2 mb-1 bg-light">
					<i class="fa fa-${file.type.startsWith('image/') ? 'image' : 'file-pdf'} text-${file.type.startsWith('image/') ? 'success' : 'danger'} mr-2"></i>
					<strong>${file.name}</strong>
					<small class="text-muted ml-2">(${fileType}, ${fileSize} MB)</small>
				</li>
			`);
		}
		
		selectedFiles.show();
	});
};

// Satış Sözleşmesi Görsellerini Görüntüleme Fonksiyonu
window.satirGorselleriGoruntule = function(satisDosya) {
	if (!satisDosya || satisDosya.trim() === '') {
		Swal.fire('Bilgi', 'Henüz yüklenmiş görsel bulunmuyor.', 'info');
		return;
	}
	
	const dosyalar = satisDosya.split(',').map(d => d.trim()).filter(d => d);
	
	if (dosyalar.length === 0) {
		Swal.fire('Bilgi', 'Henüz yüklenmiş görsel bulunmuyor.', 'info');
		return;
	}
	
	const baseUrl = '<?= base_url("assets/uploads/") ?>';
	
	let gorsellerHTML = '<div class="row">';
	dosyalar.forEach((dosya, index) => {
		const dosyaUrl = baseUrl + dosya;
		const isPDF = dosya.toLowerCase().endsWith('.pdf');
		
		gorsellerHTML += `
			<div class="col-md-4 mb-3">
				<div class="card">
					<div class="card-body text-center">
						${isPDF ? 
							`<i class="fa fa-file-pdf fa-5x text-danger mb-2"></i>
							 <p class="mb-2"><small>${dosya.split('/').pop()}</small></p>` :
							`<img src="${dosyaUrl}" class="img-fluid mb-2" style="max-height: 200px; object-fit: contain;">`
						}
						<a href="${dosyaUrl}" target="_blank" class="btn btn-sm btn-primary btn-block">
							<i class="fa fa-external-link-alt"></i> Yeni Sekmede Aç
						</a>
					</div>
				</div>
			</div>
		`;
	});
	gorsellerHTML += '</div>';
	
	Swal.fire({
		title: 'Satış Sözleşmesi Görselleri',
		html: gorsellerHTML,
		width: '80%',
		showCloseButton: true,
		showConfirmButton: false
	});
};

// Tek veri türü silme fonksiyonu
window.tekVeriSil = function(tabloKodu, cariId, tabloAdi) {
	Swal.fire({
		title: 'Veri Silme Onayı',
		html: `<div class="text-left">
			<p><strong>Tablo:</strong> ${tabloAdi}</p>
			<p><strong>Cari ID:</strong> ${cariId}</p>
			<div class="alert alert-warning">
				<i class="fa fa-exclamation-triangle"></i>
				Bu işlem sadece <strong>${tabloAdi}</strong> verilerini silecektir.
				Diğer veriler etkilenmeyecektir.
			</div>
		</div>`,
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#dc3545',
		cancelButtonColor: '#6c757d',
		confirmButtonText: '<i class="fa fa-trash"></i> Sil',
		cancelButtonText: '<i class="fa fa-times"></i> İptal',
		customClass: {
			confirmButton: 'btn btn-danger',
			cancelButton: 'btn btn-secondary'
		},
		buttonsStyling: false
	}).then((result) => {
		if (result.isConfirmed) {
			// Loading göster
			Swal.fire({
				title: 'Siliniyor...',
				text: `${tabloAdi} verileri siliniyor...`,
				icon: 'info',
				allowOutsideClick: false,
				allowEscapeKey: false,
				showConfirmButton: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});
			
			// AJAX ile silme işlemi
			$.ajax({
				url: '<?= base_url("muhasebe/tek_veri_sil") ?>',
				method: 'POST',
				data: {
					cari_id: cariId,
					tablo_kodu: tabloKodu,
					tablo_adi: tabloAdi
				},
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success') {
						Swal.fire({
							icon: 'success',
							title: 'Başarılı',
							text: response.message,
							confirmButtonText: 'Tamam'
						}).then(() => {
							// Sayfayı yenile
							location.reload();
						});
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Hata',
							text: response.message || 'Silme işlemi başarısız oldu'
						});
					}
				},
				error: function(xhr, status, error) {
					console.error('Tek veri silme hatası:', {xhr, status, error});
					Swal.fire({
						icon: 'error',
						title: 'Hata',
						text: 'Silme işlemi sırasında bir hata oluştu: ' + error
					});
				}
			});
		}
	});
}

// Seçmeli veri silme fonksiyonu
window.secmeliVeriSil = function(tabloKodu, cariId, tabloAdi, toplamAdet) {
	console.log('Seçmeli veri sil - Cari ID:', cariId, 'Tablo:', tabloKodu);
	
	// Loading göster
	Swal.fire({
		title: 'Yükleniyor...',
		text: `${tabloAdi} verileri getiriliyor...`,
		icon: 'info',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		didOpen: () => {
			Swal.showLoading();
		}
	});
	
	// AJAX ile veri listesini getir
	$.ajax({
		url: '<?= base_url("muhasebe/veri_listesi_getir") ?>',
		method: 'POST',
		data: {
			cari_id: cariId,
			tablo_kodu: tabloKodu,
			tablo_adi: tabloAdi
		},
		dataType: 'json',
		success: function(response) {
			if (response.status === 'success' && response.data && response.data.length > 0) {
				// Seçim modalı oluştur
				let secimHtml = '<div class="text-left">';
				secimHtml += '<p><strong>Silmek istediğiniz kayıtları seçin:</strong></p>';
				secimHtml += '<div class="mb-3">';
				secimHtml += '<button type="button" class="btn btn-sm btn-outline-primary" onclick="tumunuSec(true)">Tümünü Seç</button> ';
				secimHtml += '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="tumunuSec(false)">Tümünü Kaldır</button>';
				secimHtml += '</div>';
				secimHtml += '<div style="max-height: 400px; overflow-y: auto;">';
				secimHtml += '<table class="table table-sm table-hover">';
				secimHtml += '<thead><tr>';
				
				// Tablo başlıklarını oluştur
				if (response.columns && response.columns.length > 0) {
					secimHtml += '<th width="50"><input type="checkbox" id="selectAll" onchange="tumunuSec(this.checked)"></th>';
					response.columns.forEach(function(column) {
						secimHtml += '<th>' + column + '</th>';
					});
				}
				
				secimHtml += '</tr></thead><tbody>';
				
				// Veri satırlarını oluştur
				response.data.forEach(function(row, index) {
					secimHtml += '<tr>';
					secimHtml += '<td><input type="checkbox" class="kayit-checkbox" value="' + row.id + '" data-index="' + index + '"></td>';
					
					// Sütun verilerini göster
					if (response.columns && response.columns.length > 0) {
						response.columns.forEach(function(column) {
							// Sütun başlığına göre doğru field'ı bul
							let fieldName = '';
							if (column === 'Fatura No') {
								fieldName = 'fatura_no';
							} else if (column === 'Fatura Tarihi') {
								fieldName = 'fatura_tarihi';
							} else if (column === 'Açıklama') {
								fieldName = 'aciklama';
							} else {
								fieldName = column.toLowerCase().replace(' ', '_');
							}
							
							let value = row[fieldName] || row[column.toLowerCase()] || row[column] || '-';
							
							// Tarih formatlaması
							if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}/)) {
								value = new Date(value).toLocaleDateString('tr-TR');
							}
							// Para formatlaması
							if (column.toLowerCase().includes('tutar') || column.toLowerCase().includes('fiyat')) {
								if (!isNaN(value) && value !== '-') {
									value = parseFloat(value).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
								}
							}
							secimHtml += '<td>' + value + '</td>';
						});
					}
					
					secimHtml += '</tr>';
				});
				
				secimHtml += '</tbody></table>';
				secimHtml += '</div>';
				secimHtml += '<div class="mt-3 text-muted">';
				secimHtml += '<small>Toplam ' + response.data.length + ' kayıt bulundu. Seçilen kayıtlar silinecektir.</small>';
				secimHtml += '</div>';
				secimHtml += '</div>';
				
				Swal.fire({
					title: tabloAdi + ' - Kayıt Seçimi',
					html: secimHtml,
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#dc3545',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fa fa-trash"></i> Seçilenleri Sil',
					cancelButtonText: '<i class="fa fa-times"></i> İptal',
					customClass: {
						confirmButton: 'btn btn-danger',
						cancelButton: 'btn btn-secondary'
					},
					buttonsStyling: false,
					width: '80%',
					preConfirm: () => {
						// Seçilen kayıtları al
						const secilenler = [];
						$('.kayit-checkbox:checked').each(function() {
							secilenler.push($(this).val());
						});
						
						if (secilenler.length === 0) {
							Swal.showValidationMessage('Lütfen en az bir kayıt seçin.');
							return false;
						}
						
						return secilenler;
					}
				}).then((result) => {
					if (result.isConfirmed && result.value) {
						// Seçilen kayıtları sil
						seciliKayitlariSil(tabloKodu, cariId, tabloAdi, result.value);
					}
				});
				
			} else {
				Swal.fire({
					icon: 'info',
					title: 'Bilgi',
					text: response.message || 'Bu veri türünde kayıt bulunamadı.'
				});
			}
		},
		error: function(xhr, status, error) {
			console.error('Veri listesi getirme hatası:', {xhr, status, error});
			Swal.fire({
				icon: 'error',
				title: 'Hata',
				text: 'Veri listesi getirilirken bir hata oluştu: ' + error
			});
		}
	});
}

// Tümünü seç/kaldır fonksiyonu
window.tumunuSec = function(secilsin) {
	$('.kayit-checkbox').prop('checked', secilsin);
	$('#selectAll').prop('checked', secilsin);
}

// Seçili kayıtları sil
window.seciliKayitlariSil = function(tabloKodu, cariId, tabloAdi, secilenIdler) {
	console.log('Seçili kayıtları sil - Cari ID:', cariId, 'Tablo:', tabloKodu, 'Seçilen IDler:', secilenIdler);
	
	Swal.fire({
		title: 'Silme Onayı',
		text: `${secilenIdler.length} adet ${tabloAdi} kaydı silinecek. Emin misiniz?`,
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#dc3545',
		cancelButtonColor: '#6c757d',
		confirmButtonText: '<i class="fa fa-trash"></i> Sil',
		cancelButtonText: '<i class="fa fa-times"></i> İptal',
		customClass: {
			confirmButton: 'btn btn-danger',
			cancelButton: 'btn btn-secondary'
		},
		buttonsStyling: false
	}).then((result) => {
		if (result.isConfirmed) {
			// Loading göster
			Swal.fire({
				title: 'Siliniyor...',
				text: `${secilenIdler.length} kayıt siliniyor...`,
				icon: 'info',
				allowOutsideClick: false,
				allowEscapeKey: false,
				showConfirmButton: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});
			
			// AJAX ile seçili kayıtları sil
			$.ajax({
				url: '<?= base_url("muhasebe/secili_kayitlari_sil") ?>',
				method: 'POST',
				data: {
					cari_id: cariId,
					tablo_kodu: tabloKodu,
					tablo_adi: tabloAdi,
					secilen_idler: secilenIdler
				},
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success') {
						Swal.fire({
							icon: 'success',
							title: 'Başarılı',
							text: response.message,
							confirmButtonText: 'Tamam'
						}).then(() => {
							// Sayfayı yenile
							location.reload();
						});
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Hata',
							text: response.message || 'Silme işlemi başarısız oldu'
						});
					}
				},
				error: function(xhr, status, error) {
					console.error('Seçili kayıtları silme hatası:', {xhr, status, error});
					Swal.fire({
						icon: 'error',
						title: 'Hata',
						text: 'Silme işlemi sırasında bir hata oluştu: ' + error
					});
				}
			});
		}
	});
}

// Satış detaylarını akordiyon şeklinde açma/kapama (Global scope)
window.toggleSatisDetaylar = function(cari_id) {
	const detayDiv = document.getElementById('satis-detaylar-' + cari_id);
	const toggleBtn = document.getElementById('toggle-btn-' + cari_id);
	const icon = toggleBtn.querySelector('i');
	
	if (detayDiv.style.display === 'none' || detayDiv.style.display === '') {
		// Açılıyor
		if (!detayDiv.dataset.loaded) {
			// İlk kez yükleniyorsa AJAX ile veri getir
			window.loadSatisDetaylar(cari_id);
		}
		detayDiv.style.display = 'block';
		icon.className = 'fa fa-minus';
		toggleBtn.title = 'Satış detaylarını gizle';
		
		// Smooth açılış efekti
		$(detayDiv).slideDown(300);
	} else {
		// Kapanıyor
		$(detayDiv).slideUp(300, function() {
			detayDiv.style.display = 'none';
		});
		icon.className = 'fa fa-plus';
		toggleBtn.title = 'Satış detaylarını göster';
	}
}

// İkinci toggle sistemi için (Tümü butonu)
window.toggleSatisDetay2 = function(cari_id) {
	const detayDiv = document.getElementById('satis-detaylar2-' + cari_id);
	const toggleBtn = document.getElementById('toggle-btn2-' + cari_id);
	const icon = toggleBtn.querySelector('i');
	
	if (detayDiv.style.display === 'none' || detayDiv.style.display === '') {
		// Açılıyor
		if (!detayDiv.dataset.loaded) {
			// İlk kez yükleniyorsa AJAX ile veri getir
			window.loadSatisDetaylar2(cari_id);
		}
		detayDiv.style.display = 'block';
		icon.className = 'fa fa-minus';
		toggleBtn.title = 'Satış detaylarını gizle';
		
		// Smooth açılış efekti
		$(detayDiv).slideDown(300);
	} else {
		// Kapanıyor
		$(detayDiv).slideUp(300, function() {
			detayDiv.style.display = 'none';
		});
		icon.className = 'fa fa-list';
		toggleBtn.title = 'Bu cariye ait tüm hizmetleri göster/gizle';
	}
}

// AJAX ile satış detaylarını yükle (İkinci sistem için)
window.loadSatisDetaylar2 = function(cari_id) {
	const detayDiv = document.getElementById('satis-detaylar2-' + cari_id);
	
	console.log('loadSatisDetaylar2 çağrıldı, cari_id:', cari_id);
	
	$.ajax({
		url: '<?= base_url("muhasebe/testSatisList") ?>',
		type: 'POST',
		data: { cari_id: cari_id },
		dataType: 'json',
		beforeSend: function() {
			console.log('AJAX isteği gönderiliyor...');
			detayDiv.innerHTML = '<div class="alert alert-info alert-sm"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>';
		},
		success: function(response) {
			console.log('AJAX başarılı, response:', response);
			if (response.success) {
				let html = '<div class="satis-detaylar-liste">';
				
				response.satislar.forEach(function(satis, index) {
					html += `
						<div class="card mb-2 satis-card">
							<div class="card-body p-2">
								<div class="row align-items-center">
									<div class="col-md-3">
										<small class="text-muted">Satış ${index + 1}</small><br>
										<strong class="text-success">${satis.satis_sozlesme_tutari || '0'}</strong>
									</div>
									<div class="col-md-4">
										<small class="text-muted">Hizmet</small><br>
										<span class="text-info">${satis.satis_sozlesme_hizmet || 'Belirtilmemiş'}</span>
									</div>
									<div class="col-md-3">
										<small class="text-muted">Tarih</small><br>
										<span>${satis.tarih || 'Bilinmeyen'}</span>
									</div>
									<div class="col-md-2 text-right">
										${satis.gorsel_sayisi > 0 ? `
											<button class="btn btn-sm btn-outline-info" 
													onclick="window.open('<?= base_url("cari/gorselGoster/") ?>${cari_id}?tip=satis&satis_id=${satis.satis_sozlesme_id}', 'satisGorsel', 'width=800,height=600,scrollbars=yes,resizable=yes')"
													title="${satis.gorsel_sayisi} görsel">
												<i class="fa fa-image"></i> ${satis.gorsel_sayisi}
											</button>
										` : '<span class="text-muted small">Görsel yok</span>'}
									</div>
								</div>
							</div>
						</div>
					`;
				});
				
				html += '</div>';
				detayDiv.innerHTML = html;
				detayDiv.dataset.loaded = 'true';
			} else {
				detayDiv.innerHTML = `
					<div class="alert alert-warning alert-sm">
						<i class="fa fa-exclamation-triangle"></i> 
						${response.message || 'Satışlar yüklenirken bir hata oluştu.'}
					</div>
				`;
			}
		},
		error: function(xhr, status, error) {
			console.log('AJAX hatası:', xhr, status, error);
			console.log('Response text:', xhr.responseText);
			detayDiv.innerHTML = `
				<div class="alert alert-danger alert-sm">
					<i class="fa fa-exclamation-circle"></i> 
					Satışlar yüklenirken bir hata oluştu. Lütfen tekrar deneyin.
					<br><small>Hata: ${error} (Status: ${status})</small>
				</div>
			`;
		}
	});
}

// AJAX ile satış detaylarını yükle (Global scope)
window.loadSatisDetaylar = function(cari_id) {
	const detayDiv = document.getElementById('satis-detaylar-' + cari_id);
	
	console.log('loadSatisDetaylar çağrıldı, cari_id:', cari_id);
	
	$.ajax({
		url: '<?= base_url("muhasebe/testSatisList") ?>',
		type: 'POST',
		data: { cari_id: cari_id },
		dataType: 'json',
		beforeSend: function() {
			console.log('AJAX isteği gönderiliyor...');
			detayDiv.innerHTML = '<div class="alert alert-info alert-sm"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>';
		},
		success: function(response) {
			console.log('AJAX başarılı, response:', response);
			if (response.success && response.satislar.length > 0) {
				let html = '<div style="padding: 8px; background: #f8f9fa; border-radius: 5px; max-height: 200px; overflow-y: auto;">';
				html += '<strong style="color: #007bff; font-size: 12px; display: block; margin-bottom: 8px;"><i class="fa fa-list"></i> Satış Detayları (' + response.satislar.length + ' adet)</strong>';
				
				response.satislar.forEach(function(satis, index) {
					html += `
						<div style="background: white; margin-bottom: 5px; padding: 8px; border-radius: 3px; border-left: 3px solid #007bff; font-size: 12px;">
							<span style="color: #28a745; font-weight: bold;">${satis.satis_sozlesme_tutari || '0 TL'}</span>
							<span style="color: #6c757d; margin-left: 10px;">-</span>
							<span style="color: #007bff; margin-left: 5px;">${satis.satis_sozlesme_hizmet || 'Hizmet Yok'}</span>
							${satis.gorsel_sayisi > 0 ? `<span style="color: #17a2b8; margin-left: 10px;"><i class="fa fa-image"></i> ${satis.gorsel_sayisi}</span>` : ''}
						</div>
					`;
				});
				
				html += '</div>';
				detayDiv.innerHTML = html;
				detayDiv.dataset.loaded = 'true';
			} else {
				detayDiv.innerHTML = `
					<div style="padding: 10px; background: #fff3cd; border-radius: 5px; border: 1px solid #ffeaa7; color: #856404; font-size: 12px;">
						<i class="fa fa-info-circle"></i> Satış kaydı bulunamadı.
					</div>
				`;
			}
		},
		error: function(xhr, status, error) {
			console.log('AJAX hatası:', xhr, status, error);
			console.log('Response text:', xhr.responseText);
			detayDiv.innerHTML = `
				<div class="alert alert-danger alert-sm">
					<i class="fa fa-exclamation-circle"></i> 
					Satışlar yüklenirken bir hata oluştu. Lütfen tekrar deneyin.
					<br><small>Hata: ${error} (Status: ${status})</small>
				</div>
			`;
		}
	});
}

// Belge hazır olduğunda modal event'lerini başlat
$(document).ready(function() {
	initializeModalEvents();
	
	// Modal gösterildiğinde Select2'yi başlat
	$('#satisSozlesmeModal').on('shown.bs.modal', function() {
		initializeSelect2ForModal();
	});
	
	// Tahsilat onay checkbox değişiklik event'i
	$(document).on('change', '.tahsilat-onay-checkbox', function() {
		const checkbox = $(this);
		const tahsilatId = checkbox.data('tahsilat-id');
		const isChecked = checkbox.is(':checked');
		
		if (!tahsilatId) {
			console.warn('Tahsilat ID bulunamadı');
			return;
		}
		
		// AJAX ile onay durumunu güncelle
		$.ajax({
			url: '<?= base_url("muhasebe/updateTahsilatOnay") ?>',
			type: 'POST',
			data: {
				tahsilat_id: tahsilatId,
				onay_durumu: isChecked ? 1 : 0,
				cari_id: $('#stokBilgisiModal').data('cariId')
			},
			success: function(response) {
				if (response.success) {
					// Başarılı mesaj göster
					toastr.success('Onay durumu güncellendi');
					
					// Tümünü onayla checkbox durumunu kontrol et
					updateTumunuOnaylaCheckbox();
				} else {
					// Hata durumunda checkbox'ı eski haline döndür
					checkbox.prop('checked', !isChecked);
					toastr.error(response.message || 'Güncelleme sırasında hata oluştu');
				}
			},
			error: function(xhr, status, error) {
				// Hata durumunda checkbox'ı eski haline döndür
				checkbox.prop('checked', !isChecked);
				toastr.error('İşlem sırasında hata oluştu');
				console.error('AJAX Error:', error);
			}
		});
	});
	
	// Tümünü Onayla checkbox event'i
	$(document).on('change', '#tumunuOnaylaCheckbox', function() {
		const isChecked = $(this).is(':checked');
		const tahsilatCheckboxes = $('.tahsilat-onay-checkbox');
		
		// Tüm tahsilat checkbox'larını aynı duruma getir
		tahsilatCheckboxes.each(function() {
			const checkbox = $(this);
			const tahsilatId = checkbox.data('tahsilat-id');
			
			if (tahsilatId && checkbox.is(':checked') !== isChecked) {
				// Checkbox durumunu değiştir
				checkbox.prop('checked', isChecked);
				
				// AJAX ile güncelle
				$.ajax({
					url: '<?= base_url("muhasebe/updateTahsilatOnay") ?>',
					type: 'POST',
					data: {
						tahsilat_id: tahsilatId,
						onay_durumu: isChecked ? 1 : 0,
						cari_id: $('#stokBilgisiModal').data('cariId')
					},
					success: function(response) {
						if (!response.success) {
							// Hata durumunda checkbox'ı eski haline döndür
							checkbox.prop('checked', !isChecked);
							toastr.error(response.message || 'Güncelleme sırasında hata oluştu');
						}
					},
					error: function() {
						// Hata durumunda checkbox'ı eski haline döndür
						checkbox.prop('checked', !isChecked);
						toastr.error('İşlem sırasında hata oluştu');
					}
				});
			}
		});
		
		// Başarı mesajı göster
		toastr.success(isChecked ? 'Tüm tahsilatlar onaylandı' : 'Tüm tahsilat onayları kaldırıldı');
	});
});

// Tümünü onayla checkbox durumunu güncelle
function updateTumunuOnaylaCheckbox() {
	const tahsilatCheckboxes = $('.tahsilat-onay-checkbox');
	const tumunuOnaylaCheckbox = $('#tumunuOnaylaCheckbox');
	
	if (tahsilatCheckboxes.length === 0) {
		// Hiç tahsilat yoksa tümünü onayla checkbox'ını gizle
		tumunuOnaylaCheckbox.prop('checked', false).prop('disabled', true);
		return;
	}
	
	// Aktif checkbox'ları kontrol et
	const checkedCount = tahsilatCheckboxes.filter(':checked').length;
	const totalCount = tahsilatCheckboxes.length;
	
	// Tümü işaretliyse tümünü onayla checkbox'ını işaretle
	if (checkedCount === totalCount) {
		tumunuOnaylaCheckbox.prop('checked', true);
	} else {
		tumunuOnaylaCheckbox.prop('checked', false);
	}
	
	// Checkbox'ı aktif et
	tumunuOnaylaCheckbox.prop('disabled', false);
}

// Cari detay fonksiyonları kaldırıldı - sadece stok bilgileri gösterilecek

</script>

</body>
</html>
