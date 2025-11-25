<?php

// Debug - View dosyası yükleme kontrolü

if (!isset($tahsilatlar)) {

	echo "<div class='alert alert-danger'>HATA: Tahsilatlar verisi tanımlanmamış!</div>";

	exit;

}

?>

<!DOCTYPE html>

<html lang="tr">

<head>

	<?php $this->load->view("include/head-tags"); ?>

	<title>Onay Bekleyen Tahsilatlar | İlekaSoft CRM</title>

	<link rel="icon" type="image/x-icon" href="<?= base_url('assets/favicon.ico'); ?>" />

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/css/dataTables.bootstrap4.min.css"/>

	

	<style>

		.tahsilat-onay-bekleyen {

			background-color: #fff3cd;

			border-left: 4px solid #ffc107;

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

		

		.card-stats .card-body {

			padding: 1.5rem;

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

		

		@media (max-width: 768px) {

			.table-responsive {

				font-size: 0.875rem;

			}

			

			.btn-group .btn {

				padding: 0.25rem 0.5rem;

				font-size: 0.8rem;

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

						<h3 class="page-title">Onay Bekleyen Tahsilatlar</h3>

						<ul class="breadcrumb">

							<li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>

							<li class="breadcrumb-item">Muhasebe</li>

							<li class="breadcrumb-item active">Onay Bekleyen Tahsilatlar</li>

						</ul>

					</div>

					<div class="d-flex justify-content-end text-align-center col-sm-2">

						<a class="btn btn-outline-light" href="javascript:history.back()">

							<i class="fa fa-history"></i> <br>Önceki Sayfa

						</a>

					</div>

				</div>

			</div>

			<!-- /Page Header -->			<!-- İstatistikler -->

			<div class="row mb-4">

				<div class="col-xl-4 col-sm-6 col-12">

					<div class="card card-stats">

						<div class="card-body">

							<div class="stats-number" id="toplamAdetStat">

								<?= isset($toplam_adet) ? $toplam_adet : count($tahsilatlar) ?>

							</div>							<div class="stats-label">

								<i class="fa fa-clock-o mr-2"></i>Onay Bekleyen Tahsilat Sayısı

							</div>

						</div>

					</div>

				</div>

				<div class="col-xl-4 col-sm-6 col-12">

					<div class="card card-stats">

						<div class="card-body">

							<div class="stats-number" id="toplamTutarStat">

								<?= number_format($toplam_tutar, 2, ',', '.') ?> ₺

							</div>							<div class="stats-label">

								<i class="fa fa-money mr-2"></i>Onay Bekleyen Toplam Tutar

							</div>

						</div>

					</div>

				</div>

				<div class="col-xl-4 col-sm-6 col-12">

					<div class="card card-stats">

						<div class="card-body">							<div class="stats-number" id="bekleyenSayiStat">

								<?php 

								// Artık tüm veriler zaten onay bekleyen durumda olduğu için

								// direkt toplam sayıyı gösterebiliriz

								echo isset($toplam_adet) ? $toplam_adet : count($tahsilatlar);

								?>

							</div>

							<div class="stats-label">

								<i class="fa fa-hourglass-half mr-2"></i>Onay Bekleyen Sayısı

							</div>

						</div>

					</div>

				</div>

			</div>

			

			<!-- Filtre Alanı -->

			<div class="row mb-3">

				<div class="col-md-2">

					<div class="form-group">

						<label for="tahsilatTipiFilter">Tahsilat Tipi</label>

						<select id="tahsilatTipiFilter" class="form-control">

							<option value="">Tümü</option>

							<option value="Banka Hareketi">Banka Hareketi</option>

							<option value="Çek">Çek</option>

							<option value="Kasa Hareketi">Kasa Hareketi</option>

							<option value="Senet">Senet</option>

						</select>

					</div>

				</div>

				<div class="col-md-2">

					<div class="form-group">

						<label for="hizmetFilter">Hizmet</label>

						<select id="hizmetFilter" class="form-control">

							<option value="">Tümü</option>

							<?php

							$ci = get_instance();

							$stokGrupQ = "SELECT DISTINCT stokGrup_ad FROM stokGruplari ORDER BY stokGrup_ad ASC";

							$stokGruplar = $ci->db->query($stokGrupQ)->result();

							foreach($stokGruplar as $sg):

							?>

							<option value="<?= $sg->stokGrup_ad ?>"><?= $sg->stokGrup_ad ?></option>

							<?php endforeach; ?>

						</select>

					</div>

				</div>

				<div class="col-md-2">

					<div class="form-group">

						<label for="durumFilter">Durum</label>

						<select id="durumFilter" class="form-control">

							<option value="">Tümü</option>

							<option value="Onay Bekliyor" selected>Onay Bekliyor</option>

							<option value="Onaylandı">Onaylandı</option>

							<option value="Reddedildi">Reddedildi</option>

						</select>

					</div>

				</div>

				<div class="col-md-2">

					<div class="form-group">

						<label for="islemiYapanFilter">İşlemi Yapan</label>

						<input type="text" id="islemiYapanFilter" class="form-control" placeholder="Ara...">

					</div>

				</div>

				<div class="col-md-2">

					<div class="form-group">

						<label for="onayYapanFilter">Onay Yapan</label>

						<input type="text" id="onayYapanFilter" class="form-control" placeholder="Ara...">

					</div>

				</div>

				<div class="col-md-2">

					<div class="form-group">

						<label for="tarihFilter">İşlem Tarihi</label>

						<input type="date" id="tarihFilter" class="form-control">

					</div>

				</div>

			</div>

			<div class="row mb-3">

				<div class="col-md-12 text-right">

					<button id="clearFilters" class="btn btn-secondary">

						<i class="fa fa-eraser"></i> Filtreleri Temizle

					</button>

				</div>

			</div>

			

			<!-- Yeni Tahsilat Ekle Butonu -->

			<div class="row mb-3">

				<div class="col-12 text-center">

					<a href="<?= base_url('tahsilat/tahsilat-olustur') ?>" class="btn btn-success btn-lg">

						<i class="fa fa-plus mr-2"></i>Yeni Tahsilat Ekle

					</a>

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

										<i class="fa fa-hourglass-half text-warning mr-2"></i>

										Onay Bekleyen Tahsilatlar

									</h4>

									<p class="text-muted mb-0">

										<small>Sadece onay bekleyen durumundaki tahsilatlar listelenmektedir</small>

									</p>

								</div>

								<div class="col-auto">

									<button type="button" class="btn btn-primary btn-sm" onclick="location.reload()">

										<i class="fa fa-refresh"></i> Yenile

									</button>

								</div>

							</div>

						</div><?php if (isset($error_message)): ?>

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

											<th>Hizmet</th>

											<th>Durum</th>

											<th>İşlemi Yapan</th>

											<th>Onay Yapan</th>

											<th>İşlem Tarihi</th>

											<th>Görsel</th>

											<th>Açıklama</th>

											<th>İşlemler</th>

										</tr>

									</thead>

									<tbody>

										<?php foreach($tahsilatlar as $tahsilat): ?>

										<tr class="<?= $tahsilat->durum == 1 ? 'tahsilat-onay-bekleyen' : '' ?>">

											<td><strong>#<?= $tahsilat->id ?></strong></td>

											<td>

												<?php 

												$icon = '';

												$class = '';

												switch($tahsilat->tahsilat_tipi_adi) {

													case 'Banka Hareketi': $icon = 'fa-university'; $class = 'text-primary'; break;

													case 'Çek': $icon = 'fa-file-text-o'; $class = 'text-info'; break;

													case 'Kasa Hareketi': $icon = 'fa-money'; $class = 'text-success'; break;

													default: $icon = 'fa-question'; $class = 'text-muted'; break;

												}

												?>

												<i class="fa <?= $icon ?> <?= $class ?> mr-2"></i>

												<span class="<?= $class ?>"><?= $tahsilat->tahsilat_tipi_adi ?></span>

											</td>

											<td>

												<strong><?= $tahsilat->musteri_adi ?: 'Bilinmiyor' ?></strong>

											</td>

											<td>

												<span class="tutar-buyuk"><?= $tahsilat->tutar ? number_format($tahsilat->tutar, 2, ',', '.') . ' ₺' : '-' ?></span>

											</td>

											<td>

												<?php if($tahsilat->hizmet): ?>

													<span class="badge badge-info"><?= $tahsilat->hizmet ?></span>

												<?php else: ?>

													<span class="text-muted">-</span>

												<?php endif; ?>

											</td>

											<td>

												<?php if($tahsilat->durum == 1): ?>

													<span class="badge badge-warning">

														<i class="fa fa-clock-o mr-1"></i>Onay Bekliyor

													</span>

												<?php elseif($tahsilat->durum == 2): ?>

													<span class="badge badge-success">

														<i class="fa fa-check mr-1"></i>Onaylandı

													</span>

												<?php elseif($tahsilat->durum == 3): ?>

													<span class="badge badge-danger">

														<i class="fa fa-times mr-1"></i>Reddedildi

													</span>

												<?php else: ?>

													<span class="badge badge-secondary">Bilinmiyor</span>

												<?php endif; ?>

											</td>

											<td>

												<i class="fa fa-user text-info mr-1"></i>

												<?= $tahsilat->islemi_yapan_personel ?: 'Bilinmiyor' ?>

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

											</td>											<td>

												<?php if($tahsilat->gorsel): ?>

													<?php

													// Tahsilat tipine göre doğru klasör yolunu belirle

													$gorsel_url = '';

													if($tahsilat->tahsilat_tipi == 1) {

														$gorsel_url = base_url('assets/uploads/dekontlar/' . $tahsilat->gorsel);

													} elseif($tahsilat->tahsilat_tipi == 2) {

														$gorsel_url = base_url('assets/uploads/cekler/' . $tahsilat->gorsel);

													} elseif($tahsilat->tahsilat_tipi == 4) {

														$gorsel_url = base_url('assets/uploads/senetler/' . $tahsilat->gorsel);

													} else {

														$gorsel_url = base_url('assets/uploads/dekontlar/' . $tahsilat->gorsel);

													}

													?>

													<button type="button" class="btn btn-sm btn-outline-info" 

															onclick="window.open('<?= $gorsel_url ?>', '_blank')">

														<i class="fa fa-image"></i> Görsel

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

													<?php else: ?>

														<span class="text-muted">İşlem Tamamlandı</span>

													<?php endif; ?>

												</div>

											</td>

										</tr>

										<?php endforeach; ?>

									</tbody>

								</table>

							</div>

							<?php else: ?>

							<div class="text-center py-5">

								<i class="fa fa-check-circle text-success" style="font-size: 4rem; margin-bottom: 1rem;"></i>

								<h4>Tebrikler! Onay bekleyen tahsilat bulunmuyor.</h4>

								<p class="text-muted">Tüm tahsilatlar onaylanmış durumda.</p>

								<a href="<?= base_url('tahsilat/tahsilat-olustur') ?>" class="btn btn-primary">

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

			</div>

			<div class="modal-footer">

				<button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>

				<a href="#" id="onay-link" class="btn btn-success">

					<i class="fa fa-check mr-2"></i>Evet, Onayla

				</a>

			</div>

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

						Reddedilen tahsilatlar kayıtlardan silinir ve geri alınamaz.

					</p>

				</div>

			</div>

			<div class="modal-footer">

				<button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>

				<a href="#" id="red-link" class="btn btn-danger">

					<i class="fa fa-times mr-2"></i>Evet, Reddet

				</a>

			</div>

		</div>

	</div>

</div>



<?php $this->load->view("include/footer-js"); ?>

<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/js/dataTables.bootstrap4.min.js"></script>



<script>

$(document).ready(function() {	// DataTable başlat

	var table = $('#tahsilatlarTable').DataTable({

		"language": {

			"url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json"

		},

		"pageLength": 25,

		"order": [[ 8, "desc" ]], // İşlem Tarihi kolonuna göre azalan sıralama (index 8)

		"columnDefs": [

			{ "orderable": false, "targets": [9, 11] } // Görsel ve İşlemler kolonlarını sıralama dışı bırak

		]

	});

	

	// İstatistikleri Güncelle Fonksiyonu

	function updateStatistics() {

		var filteredData = table.rows({ search: 'applied' }).data();

		var toplamAdet = filteredData.length;

		var toplamTutar = 0;

		

		// Filtrelenmiş satırlardaki tutarları topla

		filteredData.each(function(row) {

			// Tutar kolonu index 3

			var tutarText = $(row[3]).text().trim();

			// "123.456,78 ₺" formatından sayıya çevir

			if (tutarText && tutarText !== '-') {

				var tutarStr = tutarText.replace(' ₺', '').replace(/\./g, '').replace(',', '.');

				var tutar = parseFloat(tutarStr);

				if (!isNaN(tutar)) {

					toplamTutar += tutar;

				}

			}

		});

		

		// Kartları güncelle

		$('#toplamAdetStat').text(toplamAdet);

		$('#toplamTutarStat').text(toplamTutar.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ₺');

		$('#bekleyenSayiStat').text(toplamAdet);

	}

	

	// Tablo her çizildiğinde istatistikleri güncelle

	table.on('draw', function() {

		updateStatistics();

	});

	

	// Tahsilat Tipi Filtreleme (Index 1)

	$('#tahsilatTipiFilter').on('change', function() {

		table.column(1).search($(this).val()).draw();

	});

	

	// Hizmet Filtreleme (Index 4)

	$('#hizmetFilter').on('change', function() {

		table.column(4).search($(this).val()).draw();

	});

	

	// Durum Filtreleme (Index 5)

	$('#durumFilter').on('change', function() {

		table.column(5).search($(this).val()).draw();

	});

	

	// İşlemi Yapan Filtreleme (Index 6)

	$('#islemiYapanFilter').on('keyup', function() {

		table.column(6).search($(this).val()).draw();

	});

	

	// Onay Yapan Filtreleme (Index 7)

	$('#onayYapanFilter').on('keyup', function() {

		table.column(7).search($(this).val()).draw();

	});

	

	// Tarih Filtreleme (Index 8)

	$('#tarihFilter').on('change', function() {

		var selectedDate = $(this).val();

		if (selectedDate) {

			// Tarihi DD.MM.YYYY formatına çevir

			var dateParts = selectedDate.split('-');

			var formattedDate = dateParts[2] + '.' + dateParts[1] + '.' + dateParts[0];

			table.column(8).search(formattedDate).draw();

		} else {

			table.column(8).search('').draw();

		}

	});

	

	// Filtreleri Temizle

	$('#clearFilters').on('click', function() {

		$('#tahsilatTipiFilter').val('');

		$('#hizmetFilter').val('');

		$('#durumFilter').val('');

		$('#islemiYapanFilter').val('');

		$('#onayYapanFilter').val('');

		$('#tarihFilter').val('');

		table.search('').columns().search('').draw();

	});



	// Onay butonu tıklama olayı

	$(document).on('click', '.tahsilat-onay-btn', function() {

		var tahsilat_id = $(this).data('id');

		var cari_ad = $(this).data('cari');

		var tutar = $(this).data('tutar');

		

		$('#onay-cari-ad').text(cari_ad);

		$('#onay-tutar').text(tutar);

		$('#onay-link').attr('href', '<?= base_url("muhasebe/tahsilat-onay/") ?>' + tahsilat_id);

		

		$('#onayModal').modal('show');

	});



	// Red butonu tıklama olayı

	$(document).on('click', '.tahsilat-red-btn', function() {

		var tahsilat_id = $(this).data('id');

		var cari_ad = $(this).data('cari');

		var tutar = $(this).data('tutar');

		

		$('#red-cari-ad').text(cari_ad);

		$('#red-tutar').text(tutar);

		$('#red-link').attr('href', '<?= base_url("muhasebe/tahsilat-red/") ?>' + tahsilat_id);

		

		$('#redModal').modal('show');

	});

});

</script>



<!-- Flash Messages -->

<?php if ($this->session->flashdata('tahsilat_onay_ok')): ?>

<script>

swal({

	title: "Başarılı",

	type: "success", 

	text: "Tahsilat başarıyla onaylandı.",

	confirmButtonText:'Tamam',

	button: false,

	timer: 3000,

});

</script>

<?php endif; ?>



<?php if ($this->session->flashdata('tahsilat_red_ok')): ?>

<script>

swal({

	title: "Başarılı",

	type: "success", 

	text: "Tahsilat başarıyla reddedildi.",

	confirmButtonText:'Tamam',

	button: false,

	timer: 3000,

});

</script>

<?php endif; ?>



<?php if ($this->session->flashdata('tahsilat_onay_hata')): ?>

<script>

swal({

	title: "Hata",

	type: "error", 

	text: "Tahsilat onaylanırken bir hata oluştu.",

	confirmButtonText:'Tamam',

	button: false,

	timer: 3000,

});

</script>

<?php endif; ?>



<?php if ($this->session->flashdata('tahsilat_red_hata')): ?>

<script>

swal({

	title: "Hata",

	type: "error", 

	text: "Tahsilat reddedilirken bir hata oluştu.",

	confirmButtonText:'Tamam',

	button: false,

	timer: 3000,

});

</script>

<?php endif; ?>



</body>

</html>

