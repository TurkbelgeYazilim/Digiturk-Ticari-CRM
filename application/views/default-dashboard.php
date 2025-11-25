<!DOCTYPE html>
<html lang="tr">
<head>
    <?php $this->load->view("include/head-tags"); ?>
    <title>Default Dashboard</title>
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
            
            <!-- Dashboard Header -->
            <div class="dashboard-header" style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h1 style="font-size: 24px; font-weight: 600; color: #2c3e50; margin: 0;">Default Dashboard</h1>
                <p style="color: #7f8c8d; margin-top: 5px;">Kullanıcı grubunuz için varsayılan dashboard görüntüleniyor.</p>
            </div>            <!-- Widget'lar -->
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Toplam Cari</h6>
                                    <h2 class="mb-0">127</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Toplam Stok</h6>
                                    <h2 class="mb-0">385</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-cubes fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Bu Ay Satış</h6>
                                    <h2 class="mb-0">42</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-shopping-cart fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Bu Ay Alış</h6>
                                    <h2 class="mb-0">18</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-truck fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- İkinci satır widget'ları -->
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Aktif Müşteri</h6>
                                    <h2 class="mb-0">95</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-user-circle-o fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Toplam Ürün</h6>
                                    <h2 class="mb-0">267</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-box fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Bu Hafta Teklif</h6>
                                    <h2 class="mb-0">7</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-file-text-o fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Sistem Durumu</h6>
                                    <h2 class="mb-0">Aktif</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-check-circle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>                </div>
        </div>
    </div>
    <!-- /Page Wrapper -->

</div>
<!-- /Main Wrapper -->

<!-- Footer JS -->
<?php $this->load->view("include/footer-js"); ?>

</body>
</html>