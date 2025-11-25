<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>İllegal Ayarları | İlekaSoft CRM</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <?php $this->load->view("include/head-tags"); ?>
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .table th {
            border-top: none;
            font-weight: 600;
            color: #2c3e50;
            background-color: #f8f9fa;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }
        .btn-outline-primary:hover,
        .btn-outline-danger:hover {
            transform: translateY(-1px);
        }
        .card-header h5 {
            color: #2c3e50;
            font-weight: 600;
        }
        .nav-tabs-solid {
            border-bottom: 2px solid #dee2e6;
        }
        .nav-tabs-solid .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        .nav-tabs-solid .nav-link:hover {
            border-bottom-color: #007bff;
            color: #007bff;
        }
        .nav-tabs-solid .nav-link.active {
            border-bottom-color: #007bff;
            color: #007bff;
            background-color: transparent;
        }
        .tab-content {
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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
                            <h3 class="page-title">İllegal Ayarları</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>
                                <li class="breadcrumb-item">İllegal</li>
                                <li class="breadcrumb-item active">İllegal Ayarları</li>
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

                <!-- Tab Yapısı -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Nav Tabs -->
                                <ul class="nav nav-tabs nav-tabs-solid nav-justified" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#sezonlar-tab" role="tab">
                                            <i class="fa fa-calendar"></i> Sezonlar
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#takimlar-tab" role="tab">
                                            <i class="fa fa-users"></i> Takımlar
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#statuler-tab" role="tab">
                                            <i class="fa fa-tags"></i> Statüler
                                        </a>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content pt-4">
                                    
                                    <!-- Sezonlar Tab -->
                                    <div class="tab-pane fade show active" id="sezonlar-tab" role="tabpanel">
                                        <div class="row mb-3">
                                            <div class="col-md-12 text-right">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#sezonModal" onclick="sezonModalAc('ekle')">
                                                    <i class="fa fa-plus"></i> Yeni Sezon Ekle
                                                </button>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="sezonTable">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Sezon Adı</th>
                                                        <th>Durum</th>
                                                        <th>İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="sezonListesi">
                                                    <tr><td colspan="4" class="text-center">Yükleniyor...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Takımlar Tab -->
                                    <div class="tab-pane fade" id="takimlar-tab" role="tabpanel">
                                        <div class="row mb-3">
                                            <div class="col-md-12 text-right">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#takimModal" onclick="takimModalAc('ekle')">
                                                    <i class="fa fa-plus"></i> Yeni Takım Ekle
                                                </button>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="takimTable">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Logo</th>
                                                        <th>Takım Adı</th>
                                                        <th>Kısa Kod</th>
                                                        <th>Oluşturan</th>
                                                        <th>Oluşturma Tarihi</th>
                                                        <th>İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="takimListesi">
                                                    <tr><td colspan="6" class="text-center">Yükleniyor...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Statüler Tab -->
                                    <div class="tab-pane fade" id="statuler-tab" role="tabpanel">
                                        <div class="row mb-3">
                                            <div class="col-md-12 text-right">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#statuModal" onclick="statuModalAc('ekle')">
                                                    <i class="fa fa-plus"></i> Yeni Statü Ekle
                                                </button>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="statuTable">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Statü Adı</th>
                                                        <th>Açıklama</th>
                                                        <th>Renk</th>
                                                        <th>Sıra No</th>
                                                        <th>Durum</th>
                                                        <th>İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="statuListesi">
                                                    <tr><td colspan="7" class="text-center">Yükleniyor...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- /Page Wrapper -->
    </div>
    <!-- /Main Wrapper -->

    <!-- Sezon Modal -->
    <div class="modal fade" id="sezonModal" tabindex="-1" role="dialog" aria-labelledby="sezonModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sezonModalLabel">Sezon Ekle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="sezonForm">
                    <div class="modal-body">
                        <input type="hidden" id="sezonId" name="sezon_id">
                        <input type="hidden" id="modalTip" value="ekle">
                        <div class="form-group">
                            <label for="sezonAdi">Sezon Adı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sezonAdi" name="sezon_adi" 
                                   placeholder="Sezon adını giriniz..." required maxlength="10">
                            <small class="form-text text-muted">Maksimum 10 karakter</small>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="sezonDurum" name="sezon_durum" checked>
                                <label class="custom-control-label" for="sezonDurum">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> İptal
                        </button>
                        <button type="submit" class="btn btn-success" id="sezonKaydetBtn">
                            <i class="fa fa-save"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Takım Modal -->
    <div class="modal fade" id="takimModal" tabindex="-1" role="dialog" aria-labelledby="takimModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="takimModalLabel">Takım Ekle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="takimForm">
                        <input type="hidden" id="takimId" name="takim_id">
                        <div class="form-group">
                            <label for="takimAdi">Takım Adı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="takimAdi" name="takim_adi" 
                                   placeholder="Takım adını giriniz..." required maxlength="255">
                        </div>
                        <div class="form-group">
                            <label for="takimKisaKod">Kısa Kod</label>
                            <input type="text" class="form-control" id="takimKisaKod" name="takim_kisa_kod" 
                                   placeholder="Kısa kod (opsiyonel)..." maxlength="20">
                            <small class="form-text text-muted">Maksimum 20 karakter</small>
                        </div>
                        <div class="form-group">
                            <label for="logoSecimTipi">Logo Seçimi</label>
                            <div class="btn-group btn-group-toggle w-100 mb-2" data-toggle="buttons">
                                <label class="btn btn-outline-primary active" id="mevcutLogoBtn">
                                    <input type="radio" name="logoSecimTipi" id="mevcutLogo" value="mevcut" checked> Mevcut Logolardan Seç
                                </label>
                                <label class="btn btn-outline-primary" id="yeniLogoBtn">
                                    <input type="radio" name="logoSecimTipi" id="yeniLogo" value="yeni"> Yeni Logo Yükle
                                </label>
                            </div>
                            
                            <!-- Mevcut Logo Seçimi -->
                            <div id="mevcutLogoAlani">
                                <select class="form-control" id="mevcutLogoSelect" name="mevcut_logo">
                                    <option value="">Logo Seçiniz...</option>
                                </select>
                                <div id="mevcutLogoPreview" class="mt-2 text-center" style="display: none;">
                                    <img id="mevcutLogoPreviewImg" src="" style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                </div>
                            </div>
                            
                            <!-- Yeni Logo Yükleme -->
                            <div id="yeniLogoAlani" style="display: none;">
                                <input type="file" class="form-control" id="takimLogo" name="takim_logo" 
                                       accept="image/png,image/jpeg,image/jpg,image/gif">
                                <small class="form-text text-muted">PNG, JPG veya GIF formatında, maksimum 2MB</small>
                                <div id="logoPreview" class="mt-2 text-center" style="display: none;">
                                    <img id="logoPreviewImg" src="" style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="logoSil()">
                                        <i class="fa fa-trash"></i> Logoyu Sil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" id="takimKaydetBtn" onclick="takimKaydet()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statü Modal -->
    <div class="modal fade" id="statuModal" tabindex="-1" role="dialog" aria-labelledby="statuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statuModalLabel">Statü Ekle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="statuForm">
                        <input type="hidden" id="statuId" name="statu_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="statuAdi">Statü Adı <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="statuAdi" name="statu_adi" 
                                           placeholder="Statü adını giriniz..." required maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="statuRenk">Renk <span class="text-danger">*</span></label>
                                    <input type="color" class="form-control" id="statuRenk" name="statu_renk" 
                                           value="#007BFF" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="statuSiraNo">Sıra No</label>
                                    <input type="number" class="form-control" id="statuSiraNo" name="statu_sira_no" 
                                           placeholder="Sıra no..." min="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="statuAciklama">Açıklama</label>
                            <textarea class="form-control" id="statuAciklama" name="statu_aciklama" 
                                      rows="3" placeholder="Statü açıklaması (opsiyonel)..."></textarea>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="statuDurum" name="statu_durum" checked>
                                <label class="custom-control-label" for="statuDurum">Aktif</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> İptal
                    </button>
                    <button type="button" class="btn btn-primary" id="statuKaydetBtn" onclick="statuKaydet()">
                        <i class="fa fa-save"></i> Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view("include/footer-js"); ?>
    
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
    $(function(){
        console.log('İllegal Ayar sayfası yükleniyor...');
        
        // Feather icon hatalarını önle
        if (typeof window.feather !== 'undefined' && window.feather.replace) {
            window.feather.replace = function() {};
        }

        // SEZON İŞLEMLERİ
        function sezonlariGetir() {
            var url = '<?= base_url("illegal/sezonlar_listele") ?>';
            
            $.getJSON(url, function(resp){
                console.log('Sezon Response:', resp);
                var html = '';
                if(resp.status === 'success' && resp.data && resp.data.length > 0) {
                    $.each(resp.data, function(i, sezon){
                        // Bit değerini kontrol et - Buffer veya direkt değer olabilir
                        var durumDeger = sezon.sezon_durum;
                        if (durumDeger && durumDeger.type === 'Buffer' && durumDeger.data) {
                            durumDeger = durumDeger.data[0];
                        }
                        var durumBadge = durumDeger == 1 
                            ? '<span class="badge badge-success">Aktif</span>' 
                            : '<span class="badge badge-secondary">Pasif</span>';
                        
                        html += '<tr>'
                            + '<td>' + sezon.sezon_id + '</td>'
                            + '<td>' + sezon.sezon_adi + '</td>'
                            + '<td>' + durumBadge + '</td>'
                            + '<td>'
                            + '<button class="btn btn-sm btn-outline-primary mr-1" onclick="sezonDuzenle(' + sezon.sezon_id + ')" title="Düzenle">'
                            + '<i class="fa fa-edit"></i></button>'
                            + '<button class="btn btn-sm btn-outline-danger" onclick="sezonSil(' + sezon.sezon_id + ')" title="Sil">'
                            + '<i class="fa fa-trash"></i></button>'
                            + '</td></tr>';
                    });
                } else {
                    html = '<tr><td colspan="4" class="text-center">Veri bulunamadı</td></tr>';
                }
                $('#sezonListesi').html(html);
            }).fail(function(xhr, status, error) {
                console.error('Sezon AJAX Error:', error);
                $('#sezonListesi').html('<tr><td colspan="4" class="text-center text-danger">Veriler yüklenemedi</td></tr>');
            });
        }

        // Modal açma
        window.sezonModalAc = function(tip, id = null) {
            $('#modalTip').val(tip);
            if(tip === 'ekle') {
                $('#sezonModalLabel').text('Yeni Sezon Ekle');
                $('#sezonKaydetBtn').html('<i class="fa fa-save"></i> Kaydet');
                $('#sezonForm')[0].reset();
                $('#sezonId').val('');
                $('#sezonDurum').prop('checked', true);
            } else {
                $('#sezonModalLabel').text('Sezon Düzenle');
                $('#sezonKaydetBtn').html('<i class="fa fa-edit"></i> Güncelle');
                // Sezon bilgilerini getir
                $.getJSON('<?= base_url("illegal/sezon_detay") ?>?id=' + id, function(resp){
                    if(resp.status === 'success' && resp.data) {
                        var sezon = resp.data;
                        $('#sezonId').val(sezon.sezon_id);
                        $('#sezonAdi').val(sezon.sezon_adi);
                        
                        // Bit değerini kontrol et
                        var durumDeger = sezon.sezon_durum;
                        if (durumDeger && durumDeger.type === 'Buffer' && durumDeger.data) {
                            durumDeger = durumDeger.data[0];
                        }
                        $('#sezonDurum').prop('checked', durumDeger == 1);
                    }
                });
            }
        };

        // Düzenleme
        window.sezonDuzenle = function(id) {
            sezonModalAc('duzenle', id);
            $('#sezonModal').modal('show');
        };

        // Silme
        window.sezonSil = function(id) {
            if(confirm('Bu sezonu silmek istediğinizden emin misiniz?')){
                $.post('<?= base_url("illegal/sezon_sil") ?>', {sezon_id: id}, function(resp){
                    if(resp.status === 'success') {
                        toastr.success('Sezon başarıyla silindi!');
                        sezonlariGetir();
                    } else {
                        toastr.error('Silme işlemi başarısız: ' + (resp.msg || 'Bilinmeyen hata'));
                    }
                }, 'json').fail(function(){
                    toastr.error('Sunucu hatası!');
                });
            }
        };

        // Form gönderimi
        $('#sezonForm').on('submit', function(e){
            e.preventDefault();
            var tip = $('#modalTip').val();
            var formData = $(this).serialize();
            var url = tip === 'ekle' ? '<?= base_url("illegal/sezon_ekle") ?>' : '<?= base_url("illegal/sezon_guncelle") ?>';
            
            $.post(url, formData, function(resp){
                if(resp.status === 'success') {
                    $('#sezonModal').modal('hide');
                    toastr.success(tip === 'ekle' ? 'Sezon başarıyla eklendi!' : 'Sezon başarıyla güncellendi!');
                    sezonlariGetir();
                    $('#sezonForm')[0].reset();
                } else {
                    toastr.error('İşlem başarısız: ' + (resp.msg || 'Bilinmeyen hata'));
                }
            }, 'json').fail(function(){
                toastr.error('Sunucu hatası!');
            });
        });

        // TAKIM İŞLEMLERİ
        function takimlariGetir() {
            var url = '<?= base_url("illegal/takimlar_listele") ?>';
            
            $.getJSON(url, function(resp){
                console.log('Takım Response:', resp);
                var html = '';
                if(resp.status === 'success' && resp.data && resp.data.length > 0) {
                    $.each(resp.data, function(i, takim){
                        // Logo gösterimi
                        var logoHtml = '-';
                        if(takim.illegal_tespit_takim_logo) {
                            logoHtml = '<img src="<?= base_url() ?>' + takim.illegal_tespit_takim_logo + '" style="max-width: 50px; max-height: 50px; object-fit: contain;" alt="Logo">';
                        }
                        
                        html += '<tr>'
                            + '<td>' + takim.illegal_tespit_takim_id + '</td>'
                            + '<td>' + logoHtml + '</td>'
                            + '<td>' + takim.illegal_tespit_takim_adi + '</td>'
                            + '<td>' + (takim.illegal_tespit_takim_kisa_kod || '-') + '</td>'
                            + '<td>' + (takim.olusturan_adi || '-') + '</td>'
                            + '<td>' + (takim.illegal_tespit_takim_olusturmaTarihi || '-') + '</td>'
                            + '<td>'
                            + '<button class="btn btn-sm btn-outline-primary mr-1" onclick="takimDuzenle(' + takim.illegal_tespit_takim_id + ')" title="Düzenle">'
                            + '<i class="fa fa-edit"></i></button>'
                            + '<button class="btn btn-sm btn-outline-danger" onclick="takimSil(' + takim.illegal_tespit_takim_id + ')" title="Sil">'
                            + '<i class="fa fa-trash"></i></button>'
                            + '</td></tr>';
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center">Veri bulunamadı</td></tr>';
                }
                $('#takimListesi').html(html);
            }).fail(function(xhr, status, error) {
                console.error('Takım AJAX Error:', error);
                $('#takimListesi').html('<tr><td colspan="7" class="text-center text-danger">Veriler yüklenemedi</td></tr>');
            });
        }

        // Takım modal açma
        window.takimModalAc = function(tip, id = null) {
            // Formu resetle
            $('#takimForm')[0].reset();
            $('#logoPreview').hide();
            $('#mevcutLogoPreview').hide();
            $('#takimLogo').val('');
            $('#mevcutLogoSelect').val('');
            
            // Mevcut logo seçeneğini aktif yap
            $('#mevcutLogo').prop('checked', true);
            $('#mevcutLogoBtn').addClass('active');
            $('#yeniLogoBtn').removeClass('active');
            $('#mevcutLogoAlani').show();
            $('#yeniLogoAlani').hide();
            
            // Mevcut logoları yükle
            mevcutLogolariYukle();
            
            if(tip === 'ekle') {
                $('#takimModalLabel').text('Yeni Takım Ekle');
                $('#takimKaydetBtn').html('Kaydet');
                $('#takimId').val('');
            } else {
                $('#takimModalLabel').text('Takım Düzenle');
                $('#takimKaydetBtn').html('Güncelle');
                
                // Takım bilgilerini getir
                $.getJSON('<?= base_url("illegal/takim_detay") ?>?id=' + id, function(resp){
                    if(resp.status === 'success' && resp.data) {
                        var takim = resp.data;
                        $('#takimId').val(takim.illegal_tespit_takim_id);
                        $('#takimAdi').val(takim.illegal_tespit_takim_adi);
                        $('#takimKisaKod').val(takim.illegal_tespit_takim_kisa_kod || '');
                        
                        // Logo varsa göster
                        if(takim.illegal_tespit_takim_logo) {
                            // Mevcut logolardan mı kontrol et
                            if(takim.illegal_tespit_takim_logo.indexOf('assets/img/team-logos/') !== -1) {
                                $('#mevcutLogoSelect').val(takim.illegal_tespit_takim_logo);
                                $('#mevcutLogoPreviewImg').attr('src', '<?= base_url() ?>' + takim.illegal_tespit_takim_logo);
                                $('#mevcutLogoPreview').show();
                            } else {
                                // Yüklenmiş logo ise
                                $('#yeniLogo').prop('checked', true);
                                $('#yeniLogoBtn').addClass('active');
                                $('#mevcutLogoBtn').removeClass('active');
                                $('#mevcutLogoAlani').hide();
                                $('#yeniLogoAlani').show();
                                $('#logoPreviewImg').attr('src', '<?= base_url() ?>' + takim.illegal_tespit_takim_logo);
                                $('#logoPreview').show();
                            }
                        }
                    }
                });
            }
        };

        // Takım düzenleme
        window.takimDuzenle = function(id) {
            takimModalAc('duzenle', id);
            $('#takimModal').modal('show');
        };

        // Logo seçim tipi değiştirme
        $('input[name="logoSecimTipi"]').on('change', function() {
            if($(this).val() === 'mevcut') {
                $('#mevcutLogoAlani').show();
                $('#yeniLogoAlani').hide();
                $('#takimLogo').val('');
                $('#logoPreview').hide();
            } else {
                $('#mevcutLogoAlani').hide();
                $('#yeniLogoAlani').show();
                $('#mevcutLogoSelect').val('');
                $('#mevcutLogoPreview').hide();
            }
        });
        
        // Mevcut logoları yükle
        function mevcutLogolariYukle() {
            $.getJSON('<?= base_url("illegal/get_team_logos") ?>', function(resp) {
                if(resp.status === 'success' && resp.data) {
                    var options = '<option value="">Logo Seçiniz...</option>';
                    $.each(resp.data, function(i, logo) {
                        options += '<option value="' + logo.path + '" data-name="' + logo.name + '">' + logo.name + '</option>';
                    });
                    $('#mevcutLogoSelect').html(options);
                }
            });
        }
        
        // Mevcut logo seçildiğinde önizleme
        $('#mevcutLogoSelect').on('change', function() {
            var path = $(this).val();
            if(path) {
                $('#mevcutLogoPreviewImg').attr('src', '<?= base_url() ?>' + path);
                $('#mevcutLogoPreview').show();
            } else {
                $('#mevcutLogoPreview').hide();
            }
        });
        
        // Yeni logo önizleme
        $('#takimLogo').on('change', function(e) {
            var file = e.target.files[0];
            if(file) {
                // Dosya boyutu kontrolü (2MB)
                if(file.size > 2 * 1024 * 1024) {
                    toastr.error('Dosya boyutu maksimum 2MB olabilir!');
                    $(this).val('');
                    return;
                }
                
                // Dosya tipi kontrolü
                var validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
                if(validTypes.indexOf(file.type) === -1) {
                    toastr.error('Sadece PNG, JPG ve GIF dosyaları yüklenebilir!');
                    $(this).val('');
                    return;
                }
                
                // Önizleme göster
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#logoPreviewImg').attr('src', e.target.result);
                    $('#logoPreview').show();
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Logo silme
        window.logoSil = function() {
            if(confirm('Logoyu silmek istediğinizden emin misiniz?')) {
                $('#takimLogo').val('');
                $('#logoPreview').hide();
                $('#logoPreviewImg').attr('src', '');
                $('#mevcutLogoSelect').val('');
                $('#mevcutLogoPreview').hide();
            }
        };

        // Takım kaydetme
        window.takimKaydet = function() {
            if(!$('#takimAdi').val()) {
                toastr.error('Takım adı zorunludur!');
                return;
            }
            
            var formData = new FormData();
            formData.append('takim_id', $('#takimId').val());
            formData.append('takim_adi', $('#takimAdi').val());
            formData.append('takim_kisa_kod', $('#takimKisaKod').val());
            
            // Logo seçimi
            var logoSecimTipi = $('input[name="logoSecimTipi"]:checked').val();
            if(logoSecimTipi === 'mevcut') {
                // Mevcut logodan seçim yapıldı
                var mevcutLogo = $('#mevcutLogoSelect').val();
                if(mevcutLogo) {
                    formData.append('mevcut_logo', mevcutLogo);
                }
            } else {
                // Yeni logo yükleme
                var logoFile = $('#takimLogo')[0].files[0];
                if(logoFile) {
                    formData.append('takim_logo', logoFile);
                }
            }

            var url = $('#takimId').val() ? '<?= base_url("illegal/takim_guncelle") ?>' : '<?= base_url("illegal/takim_ekle") ?>';
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(resp) {
                    if(resp.status === 'success') {
                        toastr.success($('#takimId').val() ? 'Takım güncellendi!' : 'Takım eklendi!');
                        $('#takimModal').modal('hide');
                        takimlariGetir();
                    } else {
                        toastr.error(resp.msg || 'Bir hata oluştu!');
                    }
                },
                error: function() {
                    toastr.error('Sunucu hatası!');
                }
            });
        };

        // Takım silme
        window.takimSil = function(id) {
            if(confirm('Bu takımı silmek istediğinizden emin misiniz?')) {
                $.post('<?= base_url("illegal/takim_sil") ?>', {takim_id: id}, function(resp) {
                    if(resp.status === 'success') {
                        toastr.success('Takım silindi!');
                        takimlariGetir();
                    } else {
                        toastr.error(resp.msg || 'Bir hata oluştu!');
                    }
                }, 'json').fail(function(){
                    toastr.error('Sunucu hatası!');
                });
            }
        };

        // STATÜ İŞLEMLERİ
        function statuleriGetir() {
            var url = '<?= base_url("illegal/statuler_listele") ?>';
            
            $.getJSON(url, function(resp){
                console.log('Statü Response:', resp);
                var html = '';
                if(resp.status === 'success' && resp.data && resp.data.length > 0) {
                    $.each(resp.data, function(i, statu){
                        var durumBadge = statu.illegal_statu_durum == 1 
                            ? '<span class="badge badge-success">Aktif</span>' 
                            : '<span class="badge badge-secondary">Pasif</span>';
                        var renkOrnek = '<span style="display:inline-block;width:30px;height:20px;background-color:' + statu.illegal_statu_renk + ';border:1px solid #ddd;border-radius:3px;"></span>';
                        
                        html += '<tr>'
                            + '<td>' + statu.illegal_statu_id + '</td>'
                            + '<td>' + statu.illegal_statu_adi + '</td>'
                            + '<td>' + (statu.illegal_statu_aciklama || '-') + '</td>'
                            + '<td>' + renkOrnek + ' ' + statu.illegal_statu_renk + '</td>'
                            + '<td>' + (statu.illegal_statu_sira_no || '-') + '</td>'
                            + '<td>' + durumBadge + '</td>'
                            + '<td>'
                            + '<button class="btn btn-sm btn-outline-primary mr-1" onclick="statuDuzenle(' + statu.illegal_statu_id + ')" title="Düzenle">'
                            + '<i class="fa fa-edit"></i></button>'
                            + '<button class="btn btn-sm btn-outline-danger" onclick="statuSil(' + statu.illegal_statu_id + ')" title="Sil">'
                            + '<i class="fa fa-trash"></i></button>'
                            + '</td></tr>';
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center">Veri bulunamadı</td></tr>';
                }
                $('#statuListesi').html(html);
            }).fail(function(xhr, status, error) {
                console.error('Statü AJAX Error:', error);
                $('#statuListesi').html('<tr><td colspan="7" class="text-center text-danger">Veriler yüklenemedi</td></tr>');
            });
        }

        // Statü modal açma
        window.statuModalAc = function(tip, id = null) {
            if(tip === 'ekle') {
                $('#statuModalLabel').text('Yeni Statü Ekle');
                $('#statuKaydetBtn').html('<i class="fa fa-save"></i> Kaydet');
                $('#statuForm')[0].reset();
                $('#statuId').val('');
                $('#statuDurum').prop('checked', true);
                $('#statuRenk').val('#007BFF');
            } else {
                $('#statuModalLabel').text('Statü Düzenle');
                $('#statuKaydetBtn').html('<i class="fa fa-edit"></i> Güncelle');
                // Statü bilgilerini getir
                $.getJSON('<?= base_url("illegal/statu_detay") ?>?id=' + id, function(resp){
                    if(resp.status === 'success' && resp.data) {
                        var statu = resp.data;
                        $('#statuId').val(statu.illegal_statu_id);
                        $('#statuAdi').val(statu.illegal_statu_adi);
                        $('#statuAciklama').val(statu.illegal_statu_aciklama);
                        $('#statuRenk').val(statu.illegal_statu_renk);
                        $('#statuSiraNo').val(statu.illegal_statu_sira_no);
                        $('#statuDurum').prop('checked', statu.illegal_statu_durum == 1);
                    }
                });
            }
        };

        // Statü düzenleme
        window.statuDuzenle = function(id) {
            statuModalAc('duzenle', id);
            $('#statuModal').modal('show');
        };

        // Statü kaydetme
        window.statuKaydet = function() {
            var formData = {
                statu_id: $('#statuId').val(),
                statu_adi: $('#statuAdi').val(),
                statu_aciklama: $('#statuAciklama').val(),
                statu_renk: $('#statuRenk').val(),
                statu_sira_no: $('#statuSiraNo').val(),
                statu_durum: $('#statuDurum').is(':checked') ? 1 : 0
            };

            if(!formData.statu_adi) {
                toastr.error('Statü adı zorunludur!');
                return;
            }

            var url = formData.statu_id ? '<?= base_url("illegal/statu_guncelle") ?>' : '<?= base_url("illegal/statu_ekle") ?>';
            
            $.post(url, formData, function(resp) {
                if(resp.status === 'success') {
                    toastr.success(formData.statu_id ? 'Statü güncellendi!' : 'Statü eklendi!');
                    $('#statuModal').modal('hide');
                    statuleriGetir();
                } else {
                    toastr.error(resp.msg || 'Bir hata oluştu!');
                }
            }, 'json').fail(function(){
                toastr.error('Sunucu hatası!');
            });
        };

        // Statü silme
        window.statuSil = function(id) {
            if(confirm('Bu statüyü silmek istediğinizden emin misiniz?')) {
                $.post('<?= base_url("illegal/statu_sil") ?>', {statu_id: id}, function(resp) {
                    if(resp.status === 'success') {
                        toastr.success('Statü silindi!');
                        statuleriGetir();
                    } else {
                        toastr.error(resp.msg || 'Bir hata oluştu!');
                    }
                }, 'json').fail(function(){
                    toastr.error('Sunucu hatası!');
                });
            }
        };

        // Sayfa açılışında verileri yükle
        sezonlariGetir();
        takimlariGetir();
        statuleriGetir();
        
        console.log('İllegal Ayar sayfası yüklendi');
    });
    </script>

</body>
</html>
