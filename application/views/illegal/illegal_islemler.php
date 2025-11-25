<!DOCTYPE html>
<html lang="tr">
<head>
    <title>İllegal Tespit İşlemleri | İlekaSoft CRM</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <?php 
        $this->load->view("include/head-tags");
        // Yetkileri al
        $yetkiler = $this->session->userdata('yetkiler') ?? [];
        // Admin kontrolü (grup_id=1)
        $login_info = $this->session->userdata('login_info');
        $is_admin = isset($login_info->grup_id) && $login_info->grup_id == 1;
        
        // Yetki kontrolleri
        $ekle_yetkisi = $is_admin || (isset($yetkiler[1625]) && in_array(2, (array)$yetkiler[1625]));
        $duzenle_yetkisi = $is_admin || (isset($yetkiler[1625]) && in_array(3, (array)$yetkiler[1625]));
    ?>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
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
        .status-ihtar { background: #ffc107; color: #333; }
        .status-telefon { background: #17a2b8; color: white; }
        .status-suç { background: #dc3545; color: white; }
        .status-satildi { background: #28a745; color: white; }
        .table th { border-top: none; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 20px;
        }
        
        /* Select2 modal içinde düzgün görünsün */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 4px;
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
                            <h3 class="page-title">
                                <?php if (isset($statu_adi) && !empty($statu_adi)): ?>
                                    İllegal İşlemler - <?= htmlspecialchars($statu_adi); ?>
                                <?php else: ?>
                                    İllegal Tespit İşlemleri
                                <?php endif; ?>
                            </h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>
                                <li class="breadcrumb-item">İllegal</li>
                                <?php if (isset($statu_adi) && !empty($statu_adi)): ?>
                                    <li class="breadcrumb-item"><a href="<?= base_url('illegal/illegal-islemler'); ?>">İllegal İşlemler</a></li>
                                    <li class="breadcrumb-item active"><?= htmlspecialchars($statu_adi); ?></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active">İllegal Tespit İşlemleri</li>
                                <?php endif; ?>
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
                                        <i class="fa fa-tasks"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="toplam-islem">0</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">Toplam İşlem</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-success border-success">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="bugun-islem">0</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">Bugün Yapılan</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-warning border-warning">
                                        <i class="fa fa-image"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="gorsel-islem">0</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">Görsel Ekli</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon text-info border-info">
                                        <i class="fa fa-list"></i>
                                    </span>
                                    <div class="dash-count">
                                        <h3 id="aktif-tespit">0</h3>
                                    </div>
                                </div>
                                <div class="dash-widget-info">
                                    <h6 class="text-muted">Aktif Tespit</h6>
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
                                    <label>Ülke</label>
                                    <select class="form-control" id="filter_ulke">
                                        <option value="">Tümü</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>İllegal Tespit</label>
                                    <select class="form-control" id="filter_tespit_id">
                                        <option value="">Tümü</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>İşlem Statüsü</label>
                                    <select class="form-control" id="filter_statu">
                                        <option value="">Tümü</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Personel</label>
                                    <select class="form-control" id="filter_personel">
                                        <option value="">Tümü</option>
                                    </select>
                                </div>
                            </div>
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
                                    <button type="button" class="btn btn-success" onclick="openNewModal()">
                                        <i class="fa fa-plus"></i> Yeni
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-success" disabled title="Yeni İşlem Yetkiniz Yok">
                                        <i class="fa fa-plus"></i> Yeni
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Filtreler -->

                <!-- İllegal İşlemleri Tablosu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-list"></i> İllegal Tespit İşlemleri
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="islemler-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tespit Bilgisi</th>
                                        <th>Statüsü</th>
                                        <th>Açıklama</th>
                                        <th>Personel</th>
                                        <th>Tarih</th>
                                        <th>Görsel</th>
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
                <!-- /İllegal İşlemleri Tablosu -->

            </div>
        </div>
        <!-- /Page Wrapper -->
    </div>
    <!-- /Main Wrapper -->

    <!-- Modal -->
    <div class="modal fade" id="islemModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Yeni İşlem Ekle</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="islemForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="islem_id" name="islem_id">
                        
                        <div class="form-group">
                            <label>İllegal Tespit <span class="text-danger">*</span></label>
                            <select class="form-control" id="tespit_id" name="illegal_tespit_id" required>
                                <option value="">Tespit Seçiniz</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>İşlem Statüsü <span class="text-danger">*</span></label>
                            <select class="form-control" id="statu" name="illegal_tespit_islemler_statu" required>
                                <option value="">Statü Seçiniz</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>İşlem Tarihi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="islem_tarihi" name="illegal_tespit_islemler_tarih" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Açıklama</label>
                            <textarea class="form-control" id="aciklama" name="illegal_tespit_islemler_aciklama" rows="4"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Görsel/Dosya</label>
                            <input type="file" class="form-control" id="gorsel" name="illegal_tespit_islemler_gorsel" accept="image/*">
                            <small class="form-text text-muted">Kabul edilen formatlar: JPG, PNG, GIF</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">
                            <span id="submitText">Kaydet</span>
                        </button>
                    </div>
                </form>
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
    // Yetkilendirme değişkenleri
    var is_admin = <?= json_encode($is_admin) ?>;
    var yetki_1625 = <?= json_encode(isset($yetkiler[1625]) ? $yetkiler[1625] : []) ?>;
    
    // Admin ise tüm yetkileri aç
    var ekle_yetkisi = is_admin || yetki_1625.includes(2);
    var duzenle_yetkisi = is_admin || yetki_1625.includes(3);
    var sil_yetkisi = is_admin || yetki_1625.includes(4);
    
    // URL'den gelen statü parametresi
    var url_statu_id = <?= json_encode(isset($statu_id) ? $statu_id : null) ?>;
    
    var table;
    
    $(document).ready(function() {
        // URL parametrelerini oku ve filtrelere uygula
        function loadFiltersFromUrl() {
            var urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('ulke')) {
                $('#filter_ulke').val(urlParams.get('ulke'));
            }
            if (urlParams.has('tespit_id')) {
                $('#filter_tespit_id').val(urlParams.get('tespit_id'));
            }
            // statu parametresi loadStatuler() içinde set ediliyor
            if (urlParams.has('personel')) {
                $('#filter_personel').val(urlParams.get('personel'));
            }
            if (urlParams.has('tarih_baslangic')) {
                $('#filter_tarih_baslangic').val(urlParams.get('tarih_baslangic'));
            }
            if (urlParams.has('tarih_bitis')) {
                $('#filter_tarih_bitis').val(urlParams.get('tarih_bitis'));
            }
        }
        
        // URL'den filtreleri yükle
        loadFiltersFromUrl();
        
        // DataTable başlatma fonksiyonu
        function initDataTable() {
            table = $('#islemler-table').DataTable({
                "processing": true,
                "serverSide": false,
                "ajax": {
                    "url": "<?= base_url('illegal/get_islemler_datatable') ?>",
                    "type": "POST",
                    "data": function(d) {
                        // URL'den gelen statü parametresini öncelikli kullan
                        var statuValue = url_statu_id || $('#filter_statu').val();
                        
                        d.ulke = $('#filter_ulke').val();
                        d.tespit_id = $('#filter_tespit_id').val();
                        d.statu = statuValue;
                        d.personel_id = $('#filter_personel').val();
                        d.tarih_baslangic = $('#filter_tarih_baslangic').val();
                        d.tarih_bitis = $('#filter_tarih_bitis').val();
                        
                        // Debug için konsola yazdır
                        console.log('DataTable Filtreler:', {
                            ulke: d.ulke,
                            tespit_id: d.tespit_id,
                            statu: d.statu,
                            statu_kaynak: url_statu_id ? 'URL' : 'Filter',
                            personel_id: d.personel_id,
                            tarih_baslangic: d.tarih_baslangic,
                            tarih_bitis: d.tarih_bitis
                        });
                    },
                    "error": function(xhr, status, error) {
                        console.error('DataTable Ajax Hatası:', error);
                        alert('Veriler yüklenirken hata oluştu: ' + error);
                    }
                },
                "columns": [
                    { "data": "illegal_tespit_islemler_id" },
                    { "data": "tespit_bilgi" },
                    { "data": "statu" },
                    { "data": "aciklama" },
                    { "data": "personel_ad" },
                    { "data": "tarih" },
                    { "data": "gorsel" },
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
        }

        // İstatistikleri yükle
        loadStatistics();
        loadUlkeler();
        loadTestiler();
        loadPersoneller();
        
        // Statüler yüklendikten SONRA DataTable başlat
        loadStatuler().then(function() {
            initDataTable();
        });

        // Select2 başlat
        $('#tespit_id').select2({
            dropdownParent: $('#islemModal'),
            placeholder: 'Tespit Seçiniz',
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

        // Filtre fonksiyonları
        window.applyFilters = function() {
            // URL parametrelerini oluştur
            var params = [];
            
            var ulke = $('#filter_ulke').val();
            var tespit_id = $('#filter_tespit_id').val();
            var statu = $('#filter_statu').val();
            var personel = $('#filter_personel').val();
            var tarih_baslangic = $('#filter_tarih_baslangic').val();
            var tarih_bitis = $('#filter_tarih_bitis').val();
            
            if (ulke) params.push('ulke=' + ulke);
            if (tespit_id) params.push('tespit_id=' + tespit_id);
            if (statu) params.push('statu=' + statu);
            if (personel) params.push('personel=' + personel);
            if (tarih_baslangic) params.push('tarih_baslangic=' + tarih_baslangic);
            if (tarih_bitis) params.push('tarih_bitis=' + tarih_bitis);
            
            // URL'yi güncelle
            var newUrl = window.location.pathname;
            if (params.length > 0) {
                newUrl += '?' + params.join('&');
            }
            window.history.pushState({}, '', newUrl);
            
            table.ajax.reload();
        };

        window.clearFilters = function() {
            $('#filter_ulke').val('');
            $('#filter_tespit_id').val('');
            $('#filter_statu').val('');
            $('#filter_personel').val('');
            $('#filter_tarih_baslangic').val('');
            $('#filter_tarih_bitis').val('');
            
            // URL parametrelerini temizle
            window.history.pushState({}, '', window.location.pathname);
            
            table.ajax.reload();
        };

        // Form submit
        $('#islemForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            var isEdit = $('#islem_id').val() !== '';
            
            // Yetki kontrolü
            if (isEdit && !duzenle_yetkisi) {
                alert('Bu işlemi düzenleme yetkiniz yok!');
                return;
            }
            
            if (!isEdit && !ekle_yetkisi) {
                alert('Yeni işlem ekleme yetkiniz yok!');
                return;
            }
            
            var url = isEdit ? '<?= base_url("illegal/update_islem") ?>' : '<?= base_url("illegal/create_islem") ?>';
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        alert(isEdit ? 'İşlem başarıyla güncellendi!' : 'İşlem başarıyla eklendi!');
                        $('#islemModal').modal('hide');
                        table.ajax.reload();
                        loadStatistics();
                    } else {
                        alert('Hata: ' + (response.message || 'Bilinmeyen hata'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Form submit hatası:', error);
                    alert('İşlem kaydedilirken hata oluştu!');
                }
            });
        });

        // İstatistikleri yükle
        function loadStatistics() {
            $.ajax({
                url: '<?= base_url("illegal/get_islem_statistics") ?>',
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    if(data.status === 'success') {
                        $('#toplam-islem').text(data.data.toplam_islem || 0);
                        $('#bugun-islem').text(data.data.bugun_islem || 0);
                        $('#gorsel-islem').text(data.data.gorsel_islem || 0);
                        $('#aktif-tespit').text(data.data.aktif_tespit || 0);
                    }
                },
                error: function() {
                    console.error('İstatistikler yüklenemedi');
                }
            });
        }

        // Tespitleri yükle
        // Ülkeleri yükle (Yetki kontrolü ile)
        function loadUlkeler() {
            $.ajax({
                url: '<?= base_url("illegal/get_filter_ulkeler_islemler") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success' && response.data) {
                        var options = '<option value="">Tümü</option>';
                        $.each(response.data, function(i, ulke) {
                            options += '<option value="' + ulke.id + '">' + ulke.ulke_adi + '</option>';
                        });
                        $('#filter_ulke').html(options);
                    }
                },
                error: function() {
                    console.error('Ülkeler yüklenirken hata oluştu');
                }
            });
        }

        function loadTestiler() {
            $.ajax({
                url: '<?= base_url("illegal/get_tespitler_for_select") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success' && response.data) {
                        var options = '<option value="">Tümü</option>';
                        $.each(response.data, function(i, tespit) {
                            var displayText = tespit.illegal_cari_isletme_adi + ' (' + tespit.illegal_tespit_tarih + ')';
                            options += '<option value="' + tespit.illegal_tespit_id + '">' + displayText + '</option>';
                        });
                        $('#filter_tespit_id').html(options);
                        
                        // Modal select'i de doldur
                        var modal_options = '<option value="">Tespit Seçiniz</option>';
                        $.each(response.data, function(i, tespit) {
                            var displayText = tespit.illegal_cari_isletme_adi + ' (' + tespit.illegal_tespit_tarih + ')';
                            modal_options += '<option value="' + tespit.illegal_tespit_id + '">' + displayText + '</option>';
                        });
                        $('#tespit_id').html(modal_options);
                        
                        // Select2'yi yeniden başlat
                        $('#tespit_id').trigger('change');
                    }
                }
            });
        }

        // Personelleri yükle
        function loadPersoneller() {
            $.ajax({
                url: '<?= base_url("illegal/get_personeller_for_select") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success' && response.data) {
                        var options = '<option value="">Tümü</option>';
                        $.each(response.data, function(i, personel) {
                            var displayText = personel.kullanici_ad + ' ' + (personel.kullanici_soyad || '');
                            options += '<option value="' + personel.kullanici_id + '">' + displayText + '</option>';
                        });
                        $('#filter_personel').html(options);
                    }
                }
            });
        }

        // Statüleri yükle (Promise döndürür)
        function loadStatuler() {
            return $.ajax({
                url: '<?= base_url("illegal/get_statuler_for_select") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success' && response.data) {
                        // Filtre select'i doldur
                        var filter_options = '<option value="">Tümü</option>';
                        $.each(response.data, function(i, statu) {
                            filter_options += '<option value="' + statu.illegal_statu_id + '">' + statu.illegal_statu_adi + '</option>';
                        });
                        $('#filter_statu').html(filter_options);
                        
                        // URL'den gelen statü parametresini set et
                        if (url_statu_id) {
                            $('#filter_statu').val(url_statu_id);
                            console.log('Statü filtresi set edildi:', url_statu_id);
                        }
                        
                        // Modal select'i doldur
                        var modal_options = '<option value="">Statü Seçiniz</option>';
                        $.each(response.data, function(i, statu) {
                            modal_options += '<option value="' + statu.illegal_statu_id + '">' + statu.illegal_statu_adi + '</option>';
                        });
                        $('#statu').html(modal_options);
                    }
                }
            });
        }

        // Silme fonksiyonu
        window.deleteRecord = function(id) {
            if (!sil_yetkisi) {
                alert('Bu işlemi silme yetkiniz yok!');
                return;
            }
            
            if(confirm('Bu işlemi silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: '<?= base_url("illegal/delete_islem") ?>',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success') {
                            alert('İşlem başarıyla silindi!');
                            table.ajax.reload();
                            loadStatistics();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Silme işlemi başarısız');
                    }
                });
            }
        };

        // Modal açma
        window.openNewModal = function() {
            if (!ekle_yetkisi) {
                alert('Yeni işlem ekleme yetkiniz yok!');
                return;
            }
            
            $('#islemForm')[0].reset();
            $('#islem_id').val('');
            $('#modalTitle').text('Yeni İşlem Ekle');
            $('#submitText').text('Kaydet');
            
            // Select2'yi sıfırla
            $('#tespit_id').val('').trigger('change');
            
            $('#islemModal').modal('show');
        };

        // Modal düzenleme
        window.editRecord = function(id) {
            if (!duzenle_yetkisi) {
                alert('Bu işlemi düzenleme yetkiniz yok!');
                return;
            }
            
            $.ajax({
                url: '<?= base_url("illegal/get_islem_by_id") ?>',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success' && response.data) {
                        var data = response.data;
                        
                        $('#islem_id').val(data.illegal_tespit_islemler_id);
                        $('#tespit_id').val(data.illegal_tespit_id).trigger('change');
                        $('#statu').val(data.illegal_tespit_islemler_statu);
                        $('#islem_tarihi').val(data.illegal_tespit_islemler_tarih);
                        $('#aciklama').val(data.illegal_tespit_islemler_aciklama);
                        
                        $('#modalTitle').text('İşlem Düzenle');
                        $('#submitText').text('Güncelle');
                        $('#islemModal').modal('show');
                    } else {
                        alert('İşlem bilgileri yüklenemedi');
                    }
                },
                error: function() {
                    alert('İşlem bilgileri yüklenirken hata oluştu!');
                }
            });
        };
    });
    </script>
</body>
</html>