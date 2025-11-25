<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Detaylı Muhasebe Raporu | İlekaSoft CRM</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <?php 
        $this->load->view("include/head-tags");
        
        // Yetkileri al
        $yetkiler = $this->session->userdata('yetkiler') ?? [];
    ?>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
    
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
        .table th { 
            border-top: none; 
            white-space: nowrap;
            font-size: 13px;
        }
        .table td {
            font-size: 13px;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 20px;
        }
        .filter-collapse-header {
            cursor: pointer;
            user-select: none;
        }
        .filter-collapse-header:hover {
            opacity: 0.9;
        }
        .select2-container {
            width: 100% !important;
        }
        .filter-row input,
        .filter-row select {
            font-size: 12px;
            padding: 5px 10px;
            height: 32px;
        }
        .stat-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        #dataTable_wrapper .row:first-child {
            margin-bottom: 15px;
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
                        <div class="col">
                            <h3 class="page-title">📊 Detaylı Muhasebe Raporu</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>
                                <li class="breadcrumb-item">Raporlar</li>
                                <li class="breadcrumb-item active">Detaylı Muhasebe Raporu</li>
                            </ul>
                        </div>
                        <div class="col-auto">
                            <?php if($export_yetkisi): ?>
                            <button type="button" class="btn btn-success" id="exportExcelBtn">
                                <i class="fa fa-file-excel-o"></i> Excel Export
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- /Sayfa Başlıkları -->

                <!-- İstatistik Kartları -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-primary border-primary">
                                        <i class="fa fa-file-text"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="toplam-sozlesme">0</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">Toplam Sözleşme</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-success border-success">
                                        <i class="fa fa-money"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="toplam-tutar">0 ₺</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">Toplam Sözleşme Tutarı</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-warning border-warning">
                                        <i class="fa fa-check-circle"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="tahsilat-tutar">0 ₺</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">Toplam Tahsilat</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-info border-info">
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
                </div>
                <!-- /İstatistik Kartları -->

                <!-- Filtreler -->
                <div class="card mb-4">
                    <div class="card-header filter-collapse-header" data-toggle="collapse" data-target="#filterCollapse">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-filter"></i> Filtreler
                            <i class="fa fa-chevron-down float-right"></i>
                        </h5>
                    </div>
                    <div class="card-body collapse show" id="filterCollapse">
                        <div class="row filter-row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar"></i> Başlangıç Tarihi</label>
                                    <input type="date" class="form-control" id="baslangic_tarih">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar"></i> Bitiş Tarihi</label>
                                    <input type="date" class="form-control" id="bitis_tarih">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-globe"></i> Ülke</label>
                                    <select class="form-control select2-multiple" id="ulke_id" multiple>
                                        <?php foreach($ulkeler as $ulke): ?>
                                        <option value="<?= $ulke->ulke_id ?>"><?= $ulke->ulke_adi ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-map-marker"></i> Şehir</label>
                                    <select class="form-control select2-multiple" id="il_id" multiple>
                                        <option value="">Önce ülke seçin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row filter-row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-map-pin"></i> İlçe</label>
                                    <select class="form-control select2-multiple" id="ilce_id" multiple>
                                        <option value="">Önce şehir seçin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-user-tie"></i> Bölge Sahibi</label>
                                    <select class="form-control select2" id="bolge_sahibi">
                                        <option value="">Tümü</option>
                                        <?php foreach($bolge_sahipleri as $bs): ?>
                                        <option value="<?= $bs->yetki_bolgeleri_bolge_sahibi ?>"><?= $bs->yetki_bolgeleri_bolge_sahibi ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar-check"></i> Sezon</label>
                                    <select class="form-control select2-multiple" id="sezon_id" multiple>
                                        <?php foreach($sezonlar as $sezon): ?>
                                        <option value="<?= $sezon->sezon_id ?>"><?= $sezon->sezon_adi ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-box"></i> Sözleşme Hizmeti</label>
                                    <select class="form-control select2" id="stok_id">
                                        <option value="">Tümü</option>
                                        <?php foreach($stoklar as $stok): ?>
                                        <option value="<?= $stok->stok_id ?>"><?= $stok->stok_ad ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row filter-row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-user"></i> Personel</label>
                                    <select class="form-control select2" id="personel_id">
                                        <option value="">Tümü</option>
                                        <?php foreach($personeller as $personel): ?>
                                        <option value="<?= $personel->kullanici_id ?>"><?= $personel->personel ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-cog"></i> Aktivasyon Hizmet</label>
                                    <select class="form-control select2" id="aktivasyon_hizmet">
                                        <option value="">Tümü</option>
                                        <?php foreach($aktivasyon_hizmetler as $hizmet): ?>
                                        <option value="<?= $hizmet->stokGrup_id ?>"><?= $hizmet->stokGrup_ad ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><i class="fa fa-file-invoice"></i> Fatura Durumu</label>
                                    <select class="form-control" id="fatura_kesildi">
                                        <option value="">Tümü</option>
                                        <option value="1">Kesildi</option>
                                        <option value="0">Kesilmedi</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-search"></i> İşletme Adı</label>
                                    <input type="text" class="form-control" id="cari_ad" placeholder="İşletme adı...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="btn-group-custom d-block">
                                        <button type="button" class="btn btn-primary btn-block" id="filterBtn">
                                            <i class="fa fa-search"></i> Filtrele
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-secondary btn-sm" id="clearFiltersBtn">
                                    <i class="fa fa-eraser"></i> Filtreleri Temizle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Filtreler -->

                <!-- Tablo -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-table"></i> Detaylı Rapor Verileri
                            <span class="badge badge-primary ml-2" id="recordCount">0 kayıt</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="dataTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>İşletme No</th>
                                        <th>İşletme Adı</th>
                                        <th>Ülke</th>
                                        <th>Şehir</th>
                                        <th>İlçe</th>
                                        <th>Bölge Sahibi</th>
                                        <th>Sezon</th>
                                        <th>Sözleşme Hizmeti</th>
                                        <th>Sözleşme Tutarı</th>
                                        <th>Sözleşme Tarihi</th>
                                        <th>Fatura Durumu</th>
                                        <th>Çek Tutarı</th>
                                        <th>Çek Vade</th>
                                        <th>Senet Tutarı</th>
                                        <th>Senet Vade</th>
                                        <th>Nakit Tutar</th>
                                        <th>Nakit Tarih</th>
                                        <th>Banka Tutar</th>
                                        <th>Banka Tarih</th>
                                        <th>Personel</th>
                                        <th>Aktivasyon Üye</th>
                                        <th>Aktivasyon Hizmet</th>
                                    </tr>
                                    <tr class="filter-row">
                                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrele" data-column="0"></th>
                                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrele" data-column="1"></th>
                                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrele" data-column="2"></th>
                                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrele" data-column="3"></th>
                                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrele" data-column="4"></th>
                                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrele" data-column="5"></th>
                                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrele" data-column="6"></th>
                                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrele" data-column="7"></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrele" data-column="19"></th>
                                        <th></th>
                                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrele" data-column="21"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- AJAX ile doldurulacak -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /Tablo -->

            </div>
        </div>
        <!-- /Page Wrapper -->
    </div>
    <!-- /Main Wrapper -->

    <!-- Excel Export Progress Modal -->
    <div class="modal fade" id="excelProgressModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-file-excel-o"></i> Excel Dosyası Hazırlanıyor
                    </h5>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Yükleniyor...</span>
                    </div>
                    <h5 id="excelProgressText">Veriler toplanıyor...</h5>
                    <p class="text-muted mb-0">Bu işlem birkaç saniye sürebilir.</p>
                    <div class="progress mt-3" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             id="excelProgressBar" 
                             role="progressbar" 
                             style="width: 0%">
                            <span id="excelProgressPercent">0%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view("include/footer-js"); ?>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
    var dataTable;
    var is_admin = <?= json_encode($is_admin) ?>;
    var export_yetkisi = <?= json_encode($export_yetkisi) ?>;
    
    $(document).ready(function() {
        // Select2 başlat
        $('.select2').select2({
            theme: 'bootstrap',
            placeholder: 'Seçiniz...',
            allowClear: true
        });
        
        $('.select2-multiple').select2({
            theme: 'bootstrap',
            placeholder: 'Seçiniz...',
            allowClear: true
        });
        
        // DataTable başlat
        initDataTable();
        
        // Filtre butonu
        $('#filterBtn').click(function() {
            dataTable.ajax.reload();
            updateStatistics();
        });
        
        // Temizle butonu
        $('#clearFiltersBtn').click(function() {
            $('#baslangic_tarih, #bitis_tarih, #cari_ad, #fatura_kesildi').val('');
            $('#ulke_id, #il_id, #ilce_id, #bolge_sahibi, #sezon_id, #stok_id, #personel_id, #aktivasyon_hizmet').val(null).trigger('change');
            dataTable.ajax.reload();
            updateStatistics();
        });
        
        // Excel export
        $('#exportExcelBtn').click(function() {
            var filters = getFilters();
            var queryString = $.param(filters);
            
            // Progress modal'ı göster
            $('#excelProgressModal').modal('show');
            
            // Progress animasyonu
            var progress = 0;
            var progressInterval = setInterval(function() {
                progress += 5;
                if (progress >= 90) {
                    progress = 90;
                    clearInterval(progressInterval);
                }
                updateProgress(progress);
            }, 200);
            
            // Excel dosyasını indir
            setTimeout(function() {
                updateProgress(100, 'Excel dosyanız indiriliyor...');
                window.location.href = '<?= base_url("raporlar/detayli_muhasebe_raporu_excel") ?>?' + queryString;
                
                // Modal'ı kapat
                setTimeout(function() {
                    $('#excelProgressModal').modal('hide');
                    clearInterval(progressInterval);
                    updateProgress(0);
                }, 2000);
            }, 1000);
        });
        
        // Ülke değişince illeri yükle
        $('#ulke_id').on('change', function() {
            var ulke_id = $(this).val();
            if (ulke_id && ulke_id.length > 0) {
                loadIller(ulke_id[0]); // İlk seçili ülkeyi al
            } else {
                $('#il_id').html('<option value="">Önce ülke seçin</option>').trigger('change');
            }
        });
        
        // İl değişince ilçeleri yükle
        $('#il_id').on('change', function() {
            var il_id = $(this).val();
            if (il_id && il_id.length > 0) {
                loadIlceler(il_id[0]); // İlk seçili ili al
            } else {
                $('#ilce_id').html('<option value="">Önce şehir seçin</option>').trigger('change');
            }
        });
        
        // İlk istatistikleri yükle
        updateStatistics();
    });
    
    function initDataTable() {
        dataTable = $('#dataTable').DataTable({
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "<?= base_url('raporlar/detayli_muhasebe_raporu_ajax') ?>",
                "type": "POST",
                "data": function(d) {
                    return getFilters();
                },
                "dataSrc": function(json) {
                    updateRecordCount(json.data.length);
                    return json.data;
                }
            },
            "columns": [
                { "data": "isletme_no" },
                { "data": "isletme_adi" },
                { "data": "ulke" },
                { "data": "sehir" },
                { "data": "ilce" },
                { "data": "bolge_sahibi" },
                { "data": "sezon" },
                { "data": "sozlesme_hizmeti" },
                { "data": "sozlesme_tutari" },
                { "data": "sozlesme_tarihi" },
                { 
                    "data": "fatura_durumu",
                    "render": function(data, type, row) {
                        if (row.fatura_kesildi == 1) {
                            return '<span class="badge badge-success">Kesildi</span>';
                        } else {
                            return '<span class="badge badge-warning">Kesilmedi</span>';
                        }
                    }
                },
                { "data": "cek_tutari" },
                { "data": "cek_vade_tarihi" },
                { "data": "senet_tutari" },
                { "data": "senet_vade_tarihi" },
                { "data": "tahsilat_nakit_tutar" },
                { "data": "tahsilat_nakit_tarih" },
                { "data": "tahsilat_banka_tutar" },
                { "data": "tahsilat_banka_tarih" },
                { "data": "personel" },
                { "data": "aktivasyon_uye_no" },
                { "data": "aktivasyon_hizmet" }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json"
            },
            "order": [[ 0, "desc" ]],
            "pageLength": 25,
            "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
            "responsive": true,
            "scrollX": true,
            "dom": 'Blfrtip',
            "buttons": []
        });
        
        // Kolon bazlı filtreleme
        $('.column-filter').on('keyup change', function() {
            var columnIndex = $(this).data('column');
            dataTable.column(columnIndex).search(this.value).draw();
        });
    }
    
    function getFilters() {
        return {
            baslangic_tarih: $('#baslangic_tarih').val(),
            bitis_tarih: $('#bitis_tarih').val(),
            ulke_id: $('#ulke_id').val(),
            il_id: $('#il_id').val(),
            ilce_id: $('#ilce_id').val(),
            bolge_sahibi: $('#bolge_sahibi').val(),
            sezon_id: $('#sezon_id').val(),
            stok_id: $('#stok_id').val(),
            personel_id: $('#personel_id').val(),
            aktivasyon_hizmet: $('#aktivasyon_hizmet').val(),
            cari_ad: $('#cari_ad').val(),
            fatura_kesildi: $('#fatura_kesildi').val()
        };
    }
    
    function loadIller(ulke_id) {
        $.ajax({
            url: '<?= base_url("raporlar/get_il_listesi_ajax") ?>',
            method: 'POST',
            data: { ulke_id: ulke_id },
            success: function(response) {
                var html = '<option value="">Tümü</option>';
                $.each(response.data, function(i, il) {
                    html += '<option value="' + il.id + '">' + il.il + '</option>';
                });
                $('#il_id').html(html).trigger('change');
            }
        });
    }
    
    function loadIlceler(il_id) {
        $.ajax({
            url: '<?= base_url("raporlar/get_ilce_listesi_ajax") ?>',
            method: 'POST',
            data: { il_id: il_id },
            success: function(response) {
                var html = '<option value="">Tümü</option>';
                $.each(response.data, function(i, ilce) {
                    html += '<option value="' + ilce.id + '">' + ilce.ilce + '</option>';
                });
                $('#ilce_id').html(html).trigger('change');
            }
        });
    }
    
    function updateRecordCount(count) {
        $('#recordCount').text(count + ' kayıt');
    }
    
    function updateStatistics() {
        // İstatistikleri hesapla (DataTable'dan)
        if (dataTable && dataTable.data().any()) {
            var data = dataTable.data();
            var toplamSozlesme = data.count();
            var toplamTutar = 0;
            var tahsilatTutar = 0;
            var cariSet = new Set();
            
            data.each(function(row) {
                if (row.sozlesme_tutari_raw) {
                    toplamTutar += parseFloat(row.sozlesme_tutari_raw) || 0;
                }
                if (row.tahsilat_banka_tutar_raw) {
                    tahsilatTutar += parseFloat(row.tahsilat_banka_tutar_raw) || 0;
                }
                if (row.tahsilat_nakit_tutar_raw) {
                    tahsilatTutar += parseFloat(row.tahsilat_nakit_tutar_raw) || 0;
                }
                if (row.isletme_no) {
                    cariSet.add(row.isletme_no);
                }
            });
            
            $('#toplam-sozlesme').text(toplamSozlesme.toLocaleString('tr-TR'));
            $('#toplam-tutar').text(toplamTutar.toLocaleString('tr-TR') + ' ₺');
            $('#tahsilat-tutar').text(tahsilatTutar.toLocaleString('tr-TR') + ' ₺');
            $('#toplam-cari').text(cariSet.size.toLocaleString('tr-TR'));
        }
    }
    
    function updateProgress(percent, text) {
        $('#excelProgressBar').css('width', percent + '%');
        $('#excelProgressPercent').text(percent + '%');
        if (text) {
            $('#excelProgressText').text(text);
        }
    }
    </script>
</body>
</html>
