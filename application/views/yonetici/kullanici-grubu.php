<!DOCTYPE html>

<html lang="tr">

<head>

    <?php $this->load->view("include/head-tags"); ?>

</head>

<body>

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

                                <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>

                                <li class="breadcrumb-item">Yönetici</li>

                                <li class="breadcrumb-item active">Kullanıcı Grupları</li>

                            </ul>

                        </div>

                        <div class="d-flex justify-content-end text-align-center col-sm-2">

                            <a class="btn btn-outline-light" href="javascript:history.back()"><i class="fa fa-history"></i> <br>Önceki Sayfa</a>

                        </div>

                    </div>

                </div>

                <!-- /Page Header -->

                <div class="row">

                    <div class="col-xl-12 col-md-12">

                        <div class="card card-table">

                            <div class="card-header">

                                <div class="row">

                                    <div class="col">

                                        <h5 class="card-title">Kullanıcı Grupları</h5>

                                    </div>

                                </div>

                            </div>                            <div class="card-body">

                                <?php if($this->session->flashdata('kullanici_grubu_ok')): ?>

                                    <div class="alert alert-success alert-dismissible fade show" role="alert">

                                        <?= $this->session->flashdata('kullanici_grubu_ok') ?>

                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">

                                            <span aria-hidden="true">&times;</span>

                                        </button>

                                    </div>

                                <?php endif; ?>

                                

                                <?php if($this->session->flashdata('kullanici_grubu_hata')): ?>

                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">

                                        <?= $this->session->flashdata('kullanici_grubu_hata') ?>

                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">

                                            <span aria-hidden="true">&times;</span>

                                        </button>

                                    </div>

                                <?php endif; ?>

                                

                                <div class="table-responsive">

                                    <table class="table table-striped custom-table mb-0">

                                        <thead>

                                            <tr>

                                                <th>#</th>

                                                <th>Grup Adı</th>

                                                <th>Açıklama</th>

                                                <th>İşlem</th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            <?php if (!empty($gruplar)): $i=1; foreach($gruplar as $grup): ?>

                                            <tr>

                                                <td><?= $i++ ?></td>

                                                <td><?= htmlspecialchars($grup->kg_adi) ?></td>

                                                <td><?= htmlspecialchars($grup->kg_aciklama) ?></td>

                                                <td class="text-right">

                                                    <a href="<?= base_url('yonetici/kullaniciGrubuDuzenle/'.$grup->kg_id) ?>" class="btn btn-sm btn-white text-success mr-2">

                                                        <i class="far fa-edit mr-1"></i> Düzenle

                                                    </a>

                                                </td>

                                            </tr>

                                            <?php endforeach; else: ?>

                                            <tr><td colspan="4">Hiç grup yok.</td></tr>

                                            <?php endif; ?>

                                        </tbody>

                                    </table>

                                </div>

                                <hr>
                                <h4>Yeni Grup Ekle</h4>
                                <form method="post" action="<?= base_url('yonetici/kullaniciGrubuEkle') ?>">
                                    <div class="mb-3">
                                        <label for="kg_adi" class="form-label">Grup Adı</label>
                                        <input type="text" class="form-control" id="kg_adi" name="kg_adi" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="kg_aciklama" class="form-label">Açıklama</label>
                                        <input type="text" class="form-control" id="kg_aciklama" name="kg_aciklama">
                                    </div>
                                    <div class="mb-3">
                                        <label for="dashboard" class="form-label">Dashboard Türü</label>
                                        <select class="form-control" id="dashboard" name="dashboard">
                                            <option value="">Dashboard Seçiniz</option>
                                            <?php foreach($dashboards as $dashboard): ?>
                                                <option value="<?= $dashboard['value'] ?>" <?= $dashboard['value'] == 'default-dashboard' ? 'selected' : '' ?>>
                                                    <?= $dashboard['text'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted">
                                            Bu gruba atanan kullanıcılar giriş yaptıklarında seçilen dashboard'u görecekler.
                                        </small>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Ekle</button>
                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- /Page Wrapper -->    </div>

    <?php $this->load->view("include/footer-js"); ?>

    

    <script>

    // Form debug için

    $('form').on('submit', function(e) {

        console.log('Form gönderiliyor...');

        console.log('Action:', $(this).attr('action'));

        console.log('Method:', $(this).attr('method'));

        console.log('Grup Adı:', $('#kg_adi').val());

        console.log('Açıklama:', $('#kg_aciklama').val());

    });

    </script>

</body>

</html>

