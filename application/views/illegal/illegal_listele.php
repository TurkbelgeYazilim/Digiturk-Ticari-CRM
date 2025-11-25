<!DOCTYPE html>
<html lang="tr">
<head>
    <title>İllegal Tespit Listesi | İlekaSoft CRM</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <?php 
        $this->load->view("include/head-tags");
        // Yetkileri al
        $yetkiler = $this->session->userdata('yetkiler') ?? [];
        // Admin kontrolü (grup_id=1)
        $login_info = $this->session->userdata('login_info');
        $is_admin = isset($login_info->grup_id) && $login_info->grup_id == 1;
        
        // Yetki kontrolleri
        $ekle_yetkisi = $is_admin || (isset($yetkiler[1620]) && in_array(2, (array)$yetkiler[1620]));
        $duzenle_yetkisi = $is_admin || (isset($yetkiler[1620]) && in_array(3, (array)$yetkiler[1620]));
    ?>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    
    <style>
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .btn-group-custom .btn {
            margin: 2px;
            border-radius: 5px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-aktif { background: #28a745; color: white; }
        .status-pasif { background: #dc3545; color: white; }
        .imzali-yes { color: #28a745; }
        .imzali-no { color: #6c757d; }
        .table th { border-top: none; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 20px;
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
                
                <!-- Sayfa Başlıkları -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-sm-12">
                            <h3 class="page-title">İllegal Tespit Listesi</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>
                                <li class="breadcrumb-item">İllegal</li>
                                <li class="breadcrumb-item active">İllegal Tespit Listesi</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Sayfa Başlıkları -->

                <!-- İstatistik Kartları -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-primary border-primary">
                                        <i class="fa fa-users"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="toplam-cari">0</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">Toplam Cari</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-success border-success">
                                        <i class="fa fa-clipboard-check"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="toplam-tespit">0</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">Toplam Tespit</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-warning border-warning">
                                        <i class="fa fa-signature"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="imzali-tespit">0</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">İmzalı Tespit</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-info border-info">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="bugun-tespit">0</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">Bugün Yapılan</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /İstatistik Kartları -->

                <!-- Filtreler -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-filter"></i> Filtreler
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Cari Durum</label>
                                    <select class="form-control" id="filter_cari_durum">
                                        <option value="">Tümü</option>
                                        <option value="1">Aktif</option>
                                        <option value="0">Pasif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>İmzalı Durum</label>
                                    <select class="form-control" id="filter_imzali">
                                        <option value="">Tümü</option>
                                        <option value="1">İmzalı</option>
                                        <option value="0">İmzasız</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Ülke</label>
                                    <select class="form-control" id="filter_ulke">
                                        <option value="">Tümü</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>İl</label>
                                    <select class="form-control" id="filter_il">
                                        <option value="">Tümü</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tarih Aralığı</label>
                                    <input type="date" class="form-control" id="filter_tarih_baslangic" placeholder="Başlangıç">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="date" class="form-control" id="filter_tarih_bitis" placeholder="Bitiş">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-group-custom">
                                    <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                        <i class="fa fa-search"></i> Filtrele
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                        <i class="fa fa-eraser"></i> Temizle
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 text-right mt-2">
                            <div class="btn-group float-right">
                                    <?php if($ekle_yetkisi): ?>
                                    <a href="<?= base_url('illegal/illegal-tespit-olustur') ?>" class="btn btn-success">
                                        <i class="fa fa-plus"></i> Yeni
                                    </a>
                                    <?php else: ?>
                                    <button class="btn btn-success" disabled title="Yeni Tespit Yetkiniz Yok">
                                        <i class="fa fa-plus"></i> Yeni
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Filtreler -->

                <!-- İllegal Tespit Tablosu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-list"></i> İllegal Tespit Kayıtları
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="illegal-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cari</th>
                                        <th>Telefon</th>
                                        <th>İl/İlçe</th>
                                        <th>Tarih/Saat</th>
                                        <th>Takımlar</th>
                                        <th>Hizmet</th>
                                        <th>Personel</th>
                                        <th>İmzalı</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- AJAX ile doldurulacak -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /İllegal Tespit Tablosu -->

            </div>
        </div>
        <!-- /Page Wrapper -->
    </div>
    <!-- /Main Wrapper -->

    <?php $this->load->view("include/footer-js"); ?>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    
    <script>
    // Yetkilendirme değişkenleri
    var is_admin = <?= json_encode($is_admin) ?>;
    var yetki_1620 = <?= json_encode(isset($yetkiler[1620]) ? $yetkiler[1620] : []) ?>;
    
    // Admin ise tüm yetkileri aç
    var duzenle_yetkisi = is_admin || yetki_1620.includes(3);
    var sil_yetkisi = is_admin || yetki_1620.includes(4);
    
    $(document).ready(function() {
        // DataTable başlat
        var table = $('#illegal-table').DataTable({
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "<?= base_url('illegal/get_illegal_listesi') ?>",
                "type": "POST",
                "error": function(xhr, status, error) {
                    console.error('DataTable Ajax Hatası:', error);
                    toastr.error('Veriler yüklenirken hata oluştu: ' + error);
                }
            },
            "columns": [
                { "data": "illegal_tespit_id" },
                { "data": "cari_bilgi" },
                { "data": "telefon" },
                { "data": "il_ilce" },
                { "data": "tarih_saat" },
                { "data": "takimlar" },
                { "data": "hizmet_adi" },
                { "data": "personel_adi" },
                { "data": "imzali_durum" },
                { "data": "islemler" }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json"
            },
            "order": [[ 0, "desc" ]],
            "pageLength": 25,
            "responsive": true,
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    className: 'btn btn-danger btn-sm'
                }
            ]
        });

        // İstatistikleri yükle
        loadStatistics();

        // Ülke ve İl listelerini yükle
        loadUlkeler();
        loadIller();

        // Ülke değiştiğinde illeri filtrele
        $('#filter_ulke').on('change', function() {
            var ulke_id = $(this).val();
            if (ulke_id) {
                loadIller(ulke_id);
            } else {
                loadIller();
            }
        });

        // Ülke listesini yükle (illegal_cari tablosundaki kayıtlardan)
        function loadUlkeler() {
            $.ajax({
                url: '<?= base_url("illegal/get_filter_ulkeler") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">Tümü</option>';
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        $.each(response.data, function(i, ulke) {
                            options += '<option value="' + ulke.id + '">' + ulke.ulke_adi + '</option>';
                        });
                    }
                    $('#filter_ulke').html(options);
                },
                error: function() {
                    console.error('Ülkeler yüklenemedi');
                }
            });
        }

        // İl listesini yükle (illegal_cari tablosundaki kayıtlardan)
        function loadIller(ulke_id) {
            $.ajax({
                url: '<?= base_url("illegal/get_filter_iller") ?>',
                type: 'GET',
                data: ulke_id ? { ulke_id: ulke_id } : {},
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">Tümü</option>';
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        $.each(response.data, function(i, il) {
                            options += '<option value="' + il.id + '">' + il.il + '</option>';
                        });
                    }
                    $('#filter_il').html(options);
                },
                error: function() {
                    console.error('İller yüklenemedi');
                }
            });
        }

        // Filtre fonksiyonları
        window.applyFilters = function() {
            var cari_durum = $('#filter_cari_durum').val();
            var imzali = $('#filter_imzali').val();
            var ulke = $('#filter_ulke').val();
            var il = $('#filter_il').val();
            var tarih_baslangic = $('#filter_tarih_baslangic').val();
            var tarih_bitis = $('#filter_tarih_bitis').val();

            table.ajax.url("<?= base_url('illegal/get_illegal_listesi') ?>?" + 
                "cari_durum=" + cari_durum + 
                "&imzali=" + imzali + 
                "&ulke=" + ulke + 
                "&il=" + il + 
                "&tarih_baslangic=" + tarih_baslangic + 
                "&tarih_bitis=" + tarih_bitis
            ).load();
        };

        window.clearFilters = function() {
            $('#filter_cari_durum').val('');
            $('#filter_imzali').val('');
            $('#filter_ulke').val('');
            $('#filter_il').val('');
            $('#filter_tarih_baslangic').val('');
            $('#filter_tarih_bitis').val('');
            table.ajax.url("<?= base_url('illegal/get_illegal_listesi') ?>").load();
        };

        // İstatistikleri yükle
        function loadStatistics() {
            $.ajax({
                url: '<?= base_url("illegal/get_statistics") ?>',
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    if(data.status === 'success') {
                        $('#toplam-cari').text(data.toplam_cari || 0);
                        $('#toplam-tespit').text(data.toplam_tespit || 0);
                        $('#imzali-tespit').text(data.imzali_tespit || 0);
                        $('#bugun-tespit').text(data.bugun_tespit || 0);
                    }
                },
                error: function() {
                    console.error('İstatistikler yüklenemedi');
                }
            });
        }

        // Silme fonksiyonu
        window.deleteRecord = function(id, type) {
            if (!sil_yetkisi) {
                toastr.error('Bu işlemi silme yetkiniz yok!');
                return;
            }
            
            if(confirm('Bu kayıt silinecek. Emin misiniz?')) {
                $.ajax({
                    url: '<?= base_url("illegal/delete_record") ?>',
                    type: 'POST',
                    data: { id: id, type: type },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success') {
                            toastr.success(response.message);
                            table.ajax.reload();
                            loadStatistics();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Silme işlemi başarısız');
                    }
                });
            }
        };
    });
    </script>
</body>
</html>

