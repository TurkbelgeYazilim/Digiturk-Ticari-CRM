<!DOCTYPE html>

<html lang="tr">

<head>

	<?php $this->load->view("include/head-tags"); ?>

	<style>

	.card {

		margin-bottom: 20px;

		border: 1px solid #e9ecef;

		border-radius: 0.375rem;

		box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);

	}

	

	.card-header {

		background-color: #f8f9fa;

		border-bottom: 1px solid #e9ecef;

		padding: 0.75rem 1.25rem;

		font-weight: 600;

	}

	

	.card-body {

		padding: 1.25rem;

	}

	

	.table th {

		background-color: #f8f9fa;

		border-top: none;

		font-weight: 600;

		font-size: 0.875rem;

		text-transform: uppercase;

		letter-spacing: 0.05em;

	}

	

	.btn-sm {

		padding: 0.25rem 0.5rem;

		font-size: 0.875rem;

		line-height: 1.5;

		border-radius: 0.2rem;

	}

	

	.form-group {

		margin-bottom: 1rem;

	}

	

	.modal-header {

		background-color: #007bff;

		color: white;

		border-bottom: none;

	}

	

	.modal-header .close {

		color: white;

		opacity: 0.8;

	}

	

	.modal-header .close:hover {

		opacity: 1;

	}

	

	.alert {

		margin-top: 15px;

		border: none;

		border-radius: 0.375rem;

	}

	

	.alert-success {

		background-color: #d4edda;

		color: #155724;

	}

	

	.alert-danger {

		background-color: #f8d7da;

		color: #721c24;

	}

	

	.badge {

		font-size: 0.75em;

		padding: 0.25em 0.4em;

	}

	

	.badge-success {

		background-color: #28a745;

	}

	

	.badge-warning {

		background-color: #ffc107;

		color: #212529;

	}

	

	.text-muted {

		color: #6c757d !important;

	}

	

	.empty-state {

		text-align: center;

		padding: 3rem 1rem;

		color: #6c757d;

	}

	

	.empty-state i {

		font-size: 3rem;

		margin-bottom: 1rem;

		opacity: 0.5;

	}

	

	/* Select2 Özelleştirmeleri */

	.select2-container--default .select2-selection--single {

		height: 38px;

		border: 1px solid #ced4da;

		border-radius: 0.375rem;

	}

	

	.select2-container--default .select2-selection--single .select2-selection__rendered {

		line-height: 36px;

		padding-left: 12px;

		color: #495057;

	}

	

	.select2-container--default .select2-selection--single .select2-selection__arrow {

		height: 36px;

		right: 10px;

	}

	

	.select2-dropdown {

		border: 1px solid #ced4da;

		border-radius: 0.375rem;

	}

	

	.select2-container--default .select2-search--dropdown .select2-search__field {

		border: 1px solid #ced4da;

		border-radius: 0.375rem;

		padding: 8px;

	}

	

	.select2-container--default .select2-results__option[aria-selected=true] {

		background-color: #007bff;

	}

	

	.select2-container--default .select2-results__option--highlighted[aria-selected] {

		background-color: #0056b3;

		color: white;

	}

	

	/* Filtre Stilleri */

	.filter-card {

		background: #f8f9fa;

		border: 1px solid #e9ecef;

		border-radius: 8px;

		margin-bottom: 20px;

	}

	

	.filter-card .card-body {

		padding: 15px;

	}

	

	.filter-toggle {

		cursor: pointer;

		transition: all 0.3s ease;

	}

	

	.filter-toggle:hover {

		color: #007bff;

	}

	

	.filter-content {

		display: none;

	}

	

	.filter-content.show {

		display: block;

	}

	

	.filter-btn-group {

		text-align: right;

		margin-top: 15px;

	}

	

	.clear-filters {

		color: #dc3545;

		text-decoration: none;

	}

	

	.clear-filters:hover {

		color: #c82333;

		text-decoration: underline;

	}

	</style>

</head>



<body>

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

						<h3 class="page-title">Numara Teslim</h3>

						<ul class="breadcrumb">

							<li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>

							<li class="breadcrumb-item"><a href="#">Voip & Hakediş</a></li>

							<li class="breadcrumb-item active">Numara Teslim</li>

						</ul>

					</div>

				</div>

			</div>

			<!-- /Page Header -->

			

			<div class="row">

				<div class="col-12">

						<!-- Numara Teslim Formu -->

						<div class="card">

							<div class="card-header">

								<i class="fas fa-user-check mr-2"></i> Yeni Numara Teslim

							</div>

							<div class="card-body">

								<form id="teslimForm">

									<div class="row">

										<div class="col-md-6">

											<div class="form-group">

												<label for="numara_id">Voip Numara <span class="text-danger">*</span></label>

												<select class="form-control select2-search" id="numara_id" name="numara_id" required>

													<option value="">Numara Seçin</option>

													<?php foreach($numaralar as $numara): ?>

														<option value="<?= $numara->numara_id ?>">

															<?= $numara->voip_numara ?> - <?= $numara->operator_adi ?>

														</option>

													<?php endforeach; ?>

												</select>

											</div>

										</div>

										<div class="col-md-6">

											<div class="form-group">

												<label for="kullanici_id">Kullanıcı <span class="text-danger">*</span></label>

												<select class="form-control select2-search" id="kullanici_id" name="kullanici_id" required>

													<option value="">Kullanıcı Seçin</option>

													<?php foreach($kullanicilar as $kullanici): ?>

														<option value="<?= $kullanici->kullanici_id ?>">

															<?= $kullanici->kullanici_ad ?> <?= $kullanici->kullanici_soyad ?>

														</option>

													<?php endforeach; ?>

												</select>

											</div>

										</div>

									</div>

									<div class="row">

										<div class="col-12">

											<div class="form-group">

												<label for="aciklama">Açıklama</label>

												<textarea class="form-control" id="aciklama" name="aciklama" rows="3" placeholder="Teslim açıklaması..."></textarea>

											</div>

										</div>

									</div>

									<div class="row">

										<div class="col-12">

											<button type="submit" class="btn btn-primary">

												<i class="fas fa-plus mr-1"></i> Teslim Et

											</button>

											<button type="reset" class="btn btn-secondary ml-2">

												<i class="fas fa-undo mr-1"></i> Temizle

											</button>

										</div>

									</div>

								</form>

								

								<!-- Alert alanı -->

								<div id="alertContainer"></div>

							</div>

						</div>



						<!-- Teslim Edilen Numaralar Listesi -->

						<div class="card">

							<div class="card-header">

								<i class="fas fa-list mr-2"></i> Teslim Edilen Numaralar

								<span class="badge badge-primary ml-2"><?= count($teslimler) ?></span>

							</div>

							<div class="card-body">

								<!-- Filtreleme Formu -->

								<div class="card mb-3">

									<div class="card-header">

										<i class="fas fa-filter mr-2"></i> Filtreleme

									</div>

									<div class="card-body">

										<form method="post" action="<?= base_url('voip/numara-teslim') ?>">

											<div class="row">

												<div class="col-md-3">

													<div class="form-group">

														<label for="filtre_numara">Numara</label>

														<input type="text" class="form-control" id="filtre_numara" name="filtre_numara" 

															   placeholder="Numara ara..." value="<?= htmlspecialchars($filtre_numara ?? '') ?>">

													</div>

												</div>

												<div class="col-md-3">

													<div class="form-group">

														<label for="filtre_operator">Operatör</label>

														<select class="form-control" id="filtre_operator" name="filtre_operator">

															<option value="">Tüm Operatörler</option>

															<?php foreach($operatorler as $operator): ?>

																<option value="<?= $operator->operator_id ?>" 

																		<?= ($filtre_operator == $operator->operator_id) ? 'selected' : '' ?>>

																	<?= $operator->operator_adi ?>

																</option>

															<?php endforeach; ?>

														</select>

													</div>

												</div>

												<div class="col-md-3">

													<div class="form-group">

														<label for="filtre_kullanici">Kullanıcı</label>

														<select class="form-control" id="filtre_kullanici" name="filtre_kullanici">

															<option value="">Tüm Kullanıcılar</option>

															<?php foreach($kullanicilar as $kullanici): ?>

																<option value="<?= $kullanici->kullanici_id ?>" 

																		<?= ($filtre_kullanici == $kullanici->kullanici_id) ? 'selected' : '' ?>>

																	<?= $kullanici->kullanici_ad ?> <?= $kullanici->kullanici_soyad ?>

																</option>

															<?php endforeach; ?>

														</select>

													</div>

												</div>

												<div class="col-md-3">

													<div class="form-group">

														<label for="filtre_durum">Durum</label>

														<select class="form-control" id="filtre_durum" name="filtre_durum">

															<option value="">Tüm Durumlar</option>

															<option value="1" <?= ($filtre_durum === '1') ? 'selected' : '' ?>>Aktif</option>

															<option value="2" <?= ($filtre_durum === '2') ? 'selected' : '' ?>>Teslim Edildi</option>

															<option value="0" <?= ($filtre_durum === '0') ? 'selected' : '' ?>>İade Edildi</option>

														</select>

													</div>

												</div>

											</div>

											<div class="row">

												<div class="col-md-3">

													<div class="form-group">

														<label for="filtre_tarih_baslangic">Başlangıç Tarihi</label>

														<input type="date" class="form-control" id="filtre_tarih_baslangic" name="filtre_tarih_baslangic" 

															   value="<?= htmlspecialchars($filtre_tarih_baslangic ?? '') ?>">

													</div>

												</div>

												<div class="col-md-3">

													<div class="form-group">

														<label for="filtre_tarih_bitis">Bitiş Tarihi</label>

														<input type="date" class="form-control" id="filtre_tarih_bitis" name="filtre_tarih_bitis" 

															   value="<?= htmlspecialchars($filtre_tarih_bitis ?? '') ?>">

													</div>

												</div>

												<div class="col-md-6 d-flex align-items-end">

													<div class="form-group mb-0">

														<button type="submit" class="btn btn-primary mr-2">

															<i class="fas fa-search mr-1"></i> Filtrele

														</button>

														<a href="<?= base_url('voip/numara-teslim') ?>" class="btn btn-secondary">

															<i class="fas fa-times mr-1"></i> Temizle

														</a>

													</div>

												</div>

											</div>

										</form>

									</div>

								</div>

								

								<?php if(!empty($teslimler)): ?>

									<div class="table-responsive">

										<table class="table table-striped table-hover">

											<thead>

												<tr>

													<th>ID</th>

													<th>Voip Numara</th>

													<th>Operatör</th>

													<th>Kullanıcı</th>

													<th>Teslim Tarihi</th>

													<th>İade Tarihi</th>

													<th>Durum</th>

													<th>Açıklama</th>

													<th>İşlemler</th>

												</tr>

											</thead>

											<tbody>

												<?php foreach($teslimler as $teslim): ?>

													<tr>

														<td><?= $teslim->teslim_id ?></td>

														<td>

															<span class="badge badge-success"><?= $teslim->voip_numara ?></span>

														</td>

														<td><?= $teslim->operator_adi ?: '<span class="text-muted">-</span>' ?></td>

														<td><?= $teslim->kullanici_ad ?> <?= $teslim->kullanici_soyad ?></td>

														<td><?= date('d.m.Y H:i', strtotime($teslim->teslim_tarihi)) ?></td>

														<td>

															<?php if($teslim->iade_tarihi): ?>

																<?= date('d.m.Y H:i', strtotime($teslim->iade_tarihi)) ?>

															<?php else: ?>

																<span class="text-muted">-</span>

															<?php endif; ?>

														</td>

														<td>

															<?php if($teslim->iade_tarihi): ?>

																<span class="badge badge-warning">İade Edildi</span>

															<?php else: ?>

																<span class="badge badge-success">Aktif</span>

															<?php endif; ?>

														</td>

														<td>

															<?= $teslim->aciklama ? mb_substr($teslim->aciklama, 0, 50) . (mb_strlen($teslim->aciklama) > 50 ? '...' : '') : '<span class="text-muted">-</span>' ?>

														</td>

														<td>

															<?php if(!$teslim->iade_tarihi): ?>

																<button type="button" class="btn btn-info btn-sm" onclick="iadeTeslim(<?= $teslim->teslim_id ?>)" title="İade Et">

																	<i class="fas fa-undo"></i>

																</button>

															<?php endif; ?>

															<button type="button" class="btn btn-warning btn-sm ml-1" onclick="editTeslim(<?= $teslim->teslim_id ?>)" title="Düzenle">

																<i class="fas fa-edit"></i>

															</button>

															<button type="button" class="btn btn-danger btn-sm ml-1" onclick="deleteTeslim(<?= $teslim->teslim_id ?>)" title="Sil">

																<i class="fas fa-trash"></i>

															</button>

														</td>

													</tr>

												<?php endforeach; ?>

											</tbody>

										</table>

									</div>

								<?php else: ?>

									<div class="empty-state">

										<i class="fas fa-user-times"></i>

										<h5>Henüz teslim edilen numara yok</h5>

										<p class="text-muted">Yukarıdaki formu kullanarak yeni numara teslimi yapabilirsiniz.</p>

									</div>

								<?php endif; ?>

							</div>

						</div>

				</div>

			</div>

			

			</div>

		</div>

		<!-- /Page Wrapper -->

	</div>

	<!-- /Main Wrapper -->



	<!-- Teslim Düzenleme Modal -->

	<div class="modal fade" id="editTeslimModal" tabindex="-1" role="dialog">

		<div class="modal-dialog" role="document">

			<div class="modal-content">

				<div class="modal-header">

					<h5 class="modal-title">

						<i class="fas fa-edit mr-2"></i> Teslim Kaydını Düzenle

					</h5>

					<button type="button" class="close" data-dismiss="modal">

						<span>&times;</span>

					</button>

				</div>

				<div class="modal-body">

					<form id="editTeslimForm">

						<input type="hidden" id="edit_teslim_id" name="teslim_id">

						<div class="form-group">

							<label for="edit_numara_id">Voip Numara <span class="text-danger">*</span></label>

							<select class="form-control select2-search-modal" id="edit_numara_id" name="numara_id" required>

								<option value="">Numara Seçin</option>

								<?php foreach($numaralar as $numara): ?>

									<option value="<?= $numara->numara_id ?>">

										<?= $numara->voip_numara ?> - <?= $numara->operator_adi ?>

									</option>

								<?php endforeach; ?>

							</select>

						</div>

						<div class="form-group">

							<label for="edit_kullanici_id">Kullanıcı <span class="text-danger">*</span></label>

							<select class="form-control select2-search-modal" id="edit_kullanici_id" name="kullanici_id" required>

								<option value="">Kullanıcı Seçin</option>

								<?php foreach($kullanicilar as $kullanici): ?>

									<option value="<?= $kullanici->kullanici_id ?>">

										<?= $kullanici->kullanici_ad ?> <?= $kullanici->kullanici_soyad ?>

									</option>

								<?php endforeach; ?>

							</select>

						</div>

						<div class="form-group">

							<label for="edit_aciklama">Açıklama</label>

							<textarea class="form-control" id="edit_aciklama" name="aciklama" rows="3"></textarea>

						</div>

					</form>

				</div>

				<div class="modal-footer">

					<button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>

					<button type="button" class="btn btn-primary" onclick="updateTeslim()">

						<i class="fas fa-save mr-1"></i> Güncelle

					</button>

				</div>

			</div>

		</div>

	</div>



	<!-- Footer -->

	<?php $this->load->view("include/footer-js"); ?>

	<!-- /Footer -->



	<!-- JavaScript -->

	<script>

		$(document).ready(function() {

			// Select2 başlatma

			$('.select2-search').select2({

				placeholder: "Arama yapın veya seçin...",

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

			

			// Modal içindeki select2'ler

			$('.select2-search-modal').select2({

				placeholder: "Arama yapın veya seçin...",

				allowClear: true,

				dropdownParent: $('#editTeslimModal'),

				language: {

					noResults: function() {

						return "Sonuç bulunamadı";

					},

					searching: function() {

						return "Aranıyor...";

					}

				}

			});

			

			// Teslim formu submit

			$('#teslimForm').on('submit', function(e) {

				e.preventDefault();

				

				var formData = $(this).serialize();

				

				$.ajax({

					url: '<?= base_url("voip/teslim_ekle") ?>',

					type: 'POST',

					data: formData,

					dataType: 'json',

					success: function(response) {

						if(response.success) {

							showAlert('success', response.message);

							$('#teslimForm')[0].reset();

							$('.select2-search').val(null).trigger('change'); // Select2 temizle

							// Sayfayı yenile

							setTimeout(function() {

								location.reload();

							}, 1500);

						} else {

							showAlert('danger', response.message);

						}

					},

					error: function() {

						showAlert('danger', 'Bir hata oluştu. Lütfen tekrar deneyin.');

					}

				});

			});

		});



		// Alert gösterme fonksiyonu

		function showAlert(type, message) {

			var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

			var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +

				message +

				'<button type="button" class="close" data-dismiss="alert">' +

				'<span>&times;</span>' +

				'</button>' +

				'</div>';

			

			$('#alertContainer').html(alertHtml);

			

			// 5 saniye sonra alert'i kaldır

			setTimeout(function() {

				$('.alert').alert('close');

			}, 5000);

		}



		// Teslim düzenleme

		function editTeslim(teslimId) {

			$.ajax({

				url: '<?= base_url("voip/teslim_getir") ?>',

				type: 'POST',

				data: { teslim_id: teslimId },

				dataType: 'json',

				success: function(response) {

					if(response.success) {

						var data = response.data;

						$('#edit_teslim_id').val(data.teslim_id);

						$('#edit_numara_id').val(data.numara_id).trigger('change'); // Select2 için trigger

						$('#edit_kullanici_id').val(data.kullanici_id).trigger('change'); // Select2 için trigger

						$('#edit_aciklama').val(data.aciklama);

						$('#editTeslimModal').modal('show');

					} else {

						showAlert('danger', response.message);

					}

				},

				error: function() {

					showAlert('danger', 'Teslim bilgileri alınırken bir hata oluştu.');

				}

			});

		}



		// Teslim güncelleme

		function updateTeslim() {

			var formData = $('#editTeslimForm').serialize();

			

			$.ajax({

				url: '<?= base_url("voip/teslim_guncelle") ?>',

				type: 'POST',

				data: formData,

				dataType: 'json',

				success: function(response) {

					if(response.success) {

						showAlert('success', response.message);

						$('#editTeslimModal').modal('hide');

						// Sayfayı yenile

						setTimeout(function() {

							location.reload();

						}, 1500);

					} else {

						showAlert('danger', response.message);

					}

				},

				error: function() {

					showAlert('danger', 'Güncelleme sırasında bir hata oluştu.');

				}

			});

		}



		// Teslim silme

		function deleteTeslim(teslimId) {

			if(confirm('Bu teslim kaydını silmek istediğinizden emin misiniz?')) {

				$.ajax({

					url: '<?= base_url("voip/teslim_sil") ?>',

					type: 'POST',

					data: { teslim_id: teslimId },

					dataType: 'json',

					success: function(response) {

						if(response.success) {

							showAlert('success', response.message);

							// Sayfayı yenile

							setTimeout(function() {

								location.reload();

							}, 1500);

						} else {

							showAlert('danger', response.message);

						}

					},

					error: function() {

						showAlert('danger', 'Silme işlemi sırasında bir hata oluştu.');

					}

				});

			}

		}



		// Numara iade etme

		function iadeTeslim(teslimId) {

			if(confirm('Bu numarayı iade etmek istediğinizden emin misiniz?')) {

				$.ajax({

					url: '<?= base_url("voip/numara_iade") ?>',

					type: 'POST',

					data: { teslim_id: teslimId },

					dataType: 'json',

					success: function(response) {

						if(response.success) {

							showAlert('success', response.message);

							// Sayfayı yenile

							setTimeout(function() {

								location.reload();

							}, 1500);

						} else {

							showAlert('danger', response.message);

						}

					},

					error: function() {

						showAlert('danger', 'İade işlemi sırasında bir hata oluştu.');

					}

				});

			}

		}

	</script>

</body>

</html>

