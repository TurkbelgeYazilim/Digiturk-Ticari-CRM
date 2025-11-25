<!DOCTYPE html>

<html lang="tr">

<head>

		<?php $this->load->view("include/head-tags"); ?>

		<style>

		.text-purple {

			color: #6f42c1 !important;

		}

		.btn-outline-purple {

			color: #6f42c1;

			border-color: #6f42c1;

		}

		.btn-outline-purple:hover {

			color: #fff;

			background-color: #6f42c1;

			border-color: #6f42c1;

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

								<h3 class="page-title">Yönetici</h3>

								<ul class="breadcrumb">

									<li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a>

									</li>

									<li class="breadcrumb-item">Yönetici

									</li>

									<li class="breadcrumb-item active">Kullanici Listesi</li>

								</ul>

							</div>

							<div class="d-flex justify-content-end text-align-center col-sm-2">

		<a class="btn btn-outline-light" href="javascript:history.back()"><i class="fa fa-history"></i> <br>Önceki Sayfa</a>

	</div>

						</div>

					</div>

					<!-- /Page Header -->



					<?php 

						$anaHesap = anaHesapBilgisi();



						$control2 = session("r", "login_info");

						$u_id = $control2->kullanici_id;

					?>

				

					<div class="row">

						

						<div class="col-xl-12 col-md-12">

							<div class="card card-table">

								<div class="card-header">

									<div class="row">

										<div class="col">

											<h5 class="card-title">Kullanıcı Listesi</h5>

										</div>										<div class="col-auto">

											<a href="<?= base_url('yonetici/yeniKullaniciEkle'); ?>" class="btn btn-outline-purple btn-sm">

        <i class="fa fa-plus"></i> Yeni Kullanıcı Ekle

    </a>

    <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#add_category"><i class="fa fa-search"></i> Arama</a>

    <?php 

		$qs = '?';

		if(!empty($_GET)) {

			$params = array();

			foreach($_GET as $key => $val) {

				if($key != 'sayfa') {

					$params[] = $key . '=' . urlencode($val);

				}

			}

			if(!empty($params)) {

				$qs .= implode('&', $params);

			}

		}

		if($qs == '?') $qs = '';

	?>

    <a href="<?= base_url("yonetici/kullaniciListesiExcel$qs"); ?>" class="btn btn-outline-success btn-sm" style='font-family: Arial, Helvetica, sans-serif;'><i class="fa fa-file-excel"></i> Dışa aktar</a>

										</div>

									</div>

								</div>

								<div class="card-body">

									<div class="table-responsive">

										<table class="table table-striped custom-table mb-0">											<thead>

    <tr>

        <th><a href="?sort=id&order=<?= ($this->input->get('sort')=='id' && $this->input->get('order')=='asc') ? 'desc' : 'asc' ?>">ID</a></th>

        <th><a href="?sort=kullanici_eposta&order=<?= ($this->input->get('sort')=='kullanici_eposta' && $this->input->get('order')=='asc') ? 'desc' : 'asc' ?>">e-Posta</a></th>

        <th><a href="?sort=sorumlu_mudur_ad&order=<?= ($this->input->get('sort')=='sorumlu_mudur_ad' && $this->input->get('order')=='asc') ? 'desc' : 'asc' ?>">Sorumlu Müdür</a></th>

        <th><a href="?sort=kullanici_ad&order=<?= ($this->input->get('sort')=='kullanici_ad' && $this->input->get('order')=='asc') ? 'desc' : 'asc' ?>">Ad Soyad</a></th>

        <th><a href="?sort=kg_adi&order=<?= ($this->input->get('sort')=='kg_adi' && $this->input->get('order')=='asc') ? 'desc' : 'asc' ?>">Yetki</a></th>

        <th><a href="?sort=kullanici_durum&order=<?= ($this->input->get('sort')=='kullanici_durum' && $this->input->get('order')=='asc') ? 'desc' : 'asc' ?>">Durum</a></th>

        <th class="text-right">İşlem</th>

    </tr>

</thead>

<tbody>

<?php foreach($kullanici as $kul){

    $durum = $kul->kullanici_durum;

    $yetkiTxt = !empty($kul->kg_adi) ? $kul->kg_adi : "Grup Atanmamış";

    $btnClass = $durum == 1 ? 'btn-success' : 'btn-danger';

    $btnText = $durum == 1 ? 'Aktif' : 'Pasif';

    $newDurum = $durum == 1 ? 2 : 1;

    $durumBtn = '<a href="'.base_url('yonetici/kullaniciDurumDegistir/'.$kul->kullanici_id.'/'.$newDurum).'" class="btn btn-sm '.$btnClass.'">'.$btnText.'</a>';

    

    // Sorumlu müdür bilgisi

    $sorumluMudur = '';

    if (!empty($kul->sorumlu_mudur_ad) && !empty($kul->sorumlu_mudur_soyad)) {

        $sorumluMudur = $kul->sorumlu_mudur_ad . ' ' . $kul->sorumlu_mudur_soyad;

    } else {

        $sorumluMudur = '<span class="text-muted">Atanmamış</span>';

    }

?>

<tr>

    <td><?= $kul->kullanici_id; ?></td>

    <td><?= $kul->kullanici_eposta; ?></td>

    <td><?= $sorumluMudur; ?></td>

    <td><?= $kul->kullanici_ad; ?> <?= $kul->kullanici_soyad;?></td>

    <td><?= $yetkiTxt; ?></td>

    <td><?= $durumBtn; ?></td>    <td class="text-right">

        <a href="<?= base_url('yonetici/mevcutKullaniciDuzenle/'.$kul->kullanici_id); ?>"  class="btn btn-sm btn-white text-purple mr-2"><i class="far fa-edit mr-1"></i> Düzenle</a>

        <!--

        <a href="#" data-toggle="modal" data-target="#edit_category" class="btn btn-sm btn-white text-purple mr-2"><i class="far fa-edit mr-1"></i> Düzenle</a> 

        <a href="#" data-toggle="modal" data-target="#delete_category" class="btn btn-sm btn-white text-danger mr-2"><i class="far fa-trash-alt mr-1"></i>Sil</a>

        -->

    </td>

</tr>

<?php } ?>

</tbody>

										</table>

										<hr>

<span style="margin-left:15px;">Toplam kayıt sayısı:</span> <b><?= $count_of_list; ?></b>

<br><br>

									</div>

								</div>

							</div>

							<?php echo $links; ?>

							<br><br>

						</div>

					</div>

				</div>

			</div>

			<!-- /Page Wrapper -->

			<?php 

				$kullaniciEposta = $this->input->get('kullaniciEposta');

				$kullaniciAdi = $this->input->get('kullaniciAdi');

			?>

			<?php
				// Ek filtre değerleri
				$grupID = $this->input->get('grupID');
				$kullaniciDurum = $this->input->get('durum');
				$sorumluMudur = $this->input->get('sorumluMudur');
			?>

			<!-- Add Category Modal -->

					<div id="add_category" class="modal custom-modal fade" role="dialog">

						<div class="modal-dialog modal-dialog-centered" role="document">

							<div class="modal-content">

								<div class="modal-header">

									<h5 class="modal-title">Kullanıcı Arama</h5>

									<button type="button" class="close" data-dismiss="modal" aria-label="Close">

										<span aria-hidden="true">&times;</span>

									</button>

								</div>

								<div class="modal-body">

									<form action="<?= base_url("yonetici/kullanici-listesi"); ?>" method="GET">

										<div class="form-group">

											<label>e-Posta'ya Göre </label>

											<input class="form-control" type="text" name="kullaniciEposta" value="<?=$kullaniciEposta;?>" autocomplete="off">

										</div>

										<div class="form-group">

											<label>Kullanıcı Adına Göre </label>

											<input class="form-control" type="text" name="kullaniciAdi" value="<?=$kullaniciAdi;?>" autocomplete="off">

										</div>

										<div class="form-group">
											<label>Gruba Göre</label>
											<select name="grupID" class="form-control">
												<option value="">Tümü</option>
												<?php if(!empty($kullaniciGruplari)) foreach($kullaniciGruplari as $g){ ?>
													<option value="<?= $g->kg_id ?>" <?= (isset($grupID) && $grupID == $g->kg_id) ? 'selected' : '' ?>><?= $g->kg_adi ?></option>
												<?php } ?>
											</select>
										</div>

										<div class="form-group">
											<label>Durum</label>
											<select name="durum" class="form-control">
												<option value="">Tümü</option>
												<option value="1" <?= (isset($kullaniciDurum) && $kullaniciDurum == '1') ? 'selected' : '' ?>>Aktif</option>
												<option value="2" <?= (isset($kullaniciDurum) && $kullaniciDurum == '2') ? 'selected' : '' ?>>Pasif</option>
											</select>
										</div>

										<div class="form-group">
											<label>Sorumlu Müdür</label>
											<select name="sorumluMudur" class="form-control">
												<option value="">Tümü</option>
												<?php if(!empty($tumKullanicilar)) foreach($tumKullanicilar as $u){ ?>
													<option value="<?= $u->kullanici_id ?>" <?= (isset($sorumluMudur) && $sorumluMudur == $u->kullanici_id) ? 'selected' : '' ?>><?= $u->kullanici_ad ?> <?= $u->kullanici_soyad ?></option>
												<?php } ?>
											</select>
										</div>

										<div class="submit-section">

											<button class="btn btn-danger submit-btn">Ara</button>

										</div>

									</form>

								</div>

							</div>

						</div>

					</div>

					<!-- /Add Category Modal -->

		</div>

		<!-- /Main Wrapper -->



		

<?php $this->load->view("include/footer-js"); ?>

<!-- Select2 JS -->

<script src="<?= base_url(); ?>assets/plugins/select2/js/select2.min.js"></script>



</body>

</html>

