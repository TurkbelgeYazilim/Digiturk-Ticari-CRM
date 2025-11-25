<!DOCTYPE html>
<html lang="tr">
<head>
    <title>İllegal Tespit Oluştur | İlekaSoft CRM</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <?php $this->load->view("include/head-tags"); ?>
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/favicon.ico'); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"
          integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/ui-lightness/jquery-ui.min.css">
    <style>
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-title {
            color: #2c3e50;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 15px;
        }
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #e74c3c;
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }
        .alert-info {
            background-color: #d5f2ff;
            border-color: #bee5eb;
            color: #0c5460;
            border-radius: 10px;
        }
        .country-select {
            background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"><path fill="%23666" d="M2 0L0 2h4zm0 5L0 3h4z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 12px;
        }
        .success-message {
            display: none;
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>
<?php
$firmaID = getirFirma();
$firma_ID = $firmaID->ayarlar_id;
?>

<div class="main-wrapper">
    <?php $this->load->view("include/header"); ?>
    <?php $this->load->view("include/sidebar"); ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-10">
                        <h3 class="page-title">
                            <?php if(isset($edit_data)): ?>
                                İllegal Tespit Düzenle
                            <?php else: ?>
                                İllegal Tespit Oluştur
                            <?php endif; ?>
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>
                            <li class="breadcrumb-item">İllegal</li>
                            <li class="breadcrumb-item active">
                                <?php if(isset($edit_data)): ?>
                                    İllegal Tespit Düzenle
                                <?php else: ?>
                                    İllegal Tespit Oluştur
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                    <div class="d-flex justify-content-end text-align-center col-sm-2">
                        <a class="btn btn-outline-light" href="javascript:history.back()"><i class="fa fa-history"></i> <br>Önceki Sayfa</a>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <div class="row">
                <div class="col-12">
                    <div class="form-container">
                        <form id="illegalTespitForm" method="post" enctype="multipart/form-data">
                            <?php if(isset($edit_data)): ?>
                                <input type="hidden" name="illegal_tespit_id" value="<?= $edit_data->illegal_tespit_id ?>">
                            <?php endif; ?>
                            <!-- Cari Bilgisi ve Telefon -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_cari_bilgi">
                                            <i class="fa fa-user"></i> Cari Bilgisi <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="illegal_cari_bilgi" 
                                               name="illegal_cari_bilgi" placeholder="Cari adı veya firma adını giriniz..." required>
                                        <input type="hidden" id="illegal_cari_id" name="illegal_cari_id">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_cari_telefon">
                                            <i class="fa fa-phone"></i> Telefon
                                        </label>
                                        <input type="tel" class="form-control" id="illegal_cari_telefon" 
                                               name="illegal_cari_telefon" placeholder="Telefon numarasını giriniz...">
                                    </div>
                                </div>
                            </div>

            <!-- Ülke, İl, İlçe -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="illegal_cari_ulke">
                            <i class="fa fa-globe"></i> Ülke <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="illegal_cari_ulke" name="illegal_cari_ulke" required>
                            <option value="">Ülke Seçiniz</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="illegal_cari_il">
                            <i class="fa fa-map-marker"></i> İl <span class="text-danger">*</span>
                        </label>
                        <select class="form-control select2-search" id="illegal_cari_il" name="illegal_cari_il" required>
                            <option value="">İl Seçiniz</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="illegal_cari_ilce">
                            <i class="fa fa-map-marker"></i> İlçe <span class="text-danger">*</span>
                        </label>
                        <select class="form-control select2-search" id="illegal_cari_ilce" name="illegal_cari_ilce" disabled required>
                            <option value="">Önce il seçiniz</option>
                        </select>
                    </div>
                </div>
            </div>                            <!-- Adres -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="illegal_cari_adres">
                                            <i class="fa fa-map-marker"></i> Adres
                                        </label>
                                        <textarea class="form-control" id="illegal_cari_adres" name="illegal_cari_adres" 
                                                  rows="3" placeholder="Adres bilgisini giriniz..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Tespit Tarihi ve Saati -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_tespit_tarih">
                                            <i class="fa fa-calendar"></i> Tespit Tarihi <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="illegal_tespit_tarih" 
                                               name="illegal_tespit_tarih" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_tespit_saat">
                                            <i class="fa fa-clock-o"></i> Tespit Saati <span class="text-danger">*</span>
                                        </label>
                                        <input type="time" class="form-control" id="illegal_tespit_saat" 
                                               name="illegal_tespit_saat" value="<?= date('H:i') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Açıklama -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="illegal_tespit_aciklama">
                                            <i class="fa fa-comment"></i> Açıklama
                                        </label>
                                        <textarea class="form-control" id="illegal_tespit_aciklama" name="illegal_tespit_aciklama" 
                                                  rows="4" placeholder="Tespit açıklamasını giriniz..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Takımlar -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_tespit_takim_id">
                                            <i class="fa fa-users"></i> Takım <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2-search" id="illegal_tespit_takim_id" name="illegal_tespit_takim_id" required>
                                            <option value="">Takım Seçiniz</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_tespit_rakip_takim_id">
                                            <i class="fa fa-users"></i> Rakip Takım <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2-search" id="illegal_tespit_rakip_takim_id" name="illegal_tespit_rakip_takim_id" required>
                                            <option value="">Rakip Takım Seçiniz</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Tespiti Yapan Personel ve Avukat Personel -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_tespit_personel_id">
                                            <i class="fa fa-user-circle"></i> Tespiti Yapan Personel <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control select2-search" id="illegal_tespit_personel_id" name="illegal_tespit_personel_id" required>
                                            <option value="">Personel Seçiniz</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_tespit_avukat_id">
                                            <i class="fa fa-balance-scale"></i> Tespiti Yapan Avukat
                                        </label>
                                        <select class="form-control select2-search" id="illegal_tespit_avukat_id" name="illegal_tespit_avukat_id">
                                            <option value="">Avukat Seçiniz</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Hizmet ve İmzalı Tespit -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_tespit_stokGrup_id">
                                            <i class="fa fa-th-list"></i> Hizmet <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="illegal_tespit_stokGrup_id" name="illegal_tespit_stokGrup_id" required>
                                            <option value="">Hizmet Seçiniz</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_tespit_imzali">
                                            <i class="fa fa-signature"></i> İmzalı Tespit
                                        </label>
                                        <div class="form-check" style="margin-top: 10px;">
                                            <input type="checkbox" class="form-check-input" id="illegal_tespit_imzali" name="illegal_tespit_imzali" value="1">
                                            <label class="form-check-label" for="illegal_tespit_imzali">
                                                İmzalı tespit olarak işaretle
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Görsel Yüklemeleri -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_tespit_gorsel">
                                            <i class="fa fa-image"></i> Tespit Görselleri
                                        </label>
                                        <input type="file" class="form-control-file" id="illegal_tespit_gorsel" 
                                               name="illegal_tespit_gorsel[]" multiple accept="image/*">
                                        <small class="form-text text-muted">Birden fazla görsel seçebilirsiniz.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="illegal_tespit_tutanak_gorsel">
                                            <i class="fa fa-file-image-o"></i> Tutanak Görselleri
                                        </label>
                                        <input type="file" class="form-control-file" id="illegal_tespit_tutanak_gorsel" 
                                               name="illegal_tespit_tutanak_gorsel[]" multiple accept="image/*">
                                        <small class="form-text text-muted">Birden fazla görsel seçebilirsiniz.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <?php if(isset($edit_data)): ?>
                                            <i class="fa fa-save"></i> Güncelle
                                        <?php else: ?>
                                            <i class="fa fa-save"></i> Kaydet
                                        <?php endif; ?>
                                    </button>
                                    <button type="reset" class="btn btn-secondary ml-3">
                                        <i class="fa fa-refresh"></i> Temizle
                                    </button>
                                </div>
                            </div>

                            <div class="success-message" id="successMessage">
                                <i class="fa fa-check-circle fa-2x"></i>
                                <div class="mt-2">İllegal tespit başarıyla kaydedildi!</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view("include/footer-js"); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" 
        integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" 
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

<script>
$(document).ready(function() {
    // Edit verileri kontrol et
    <?php if(isset($edit_data)): ?>
    var editData = <?= json_encode([
        'illegal_tespit_id' => isset($edit_data->illegal_tespit_id) ? $edit_data->illegal_tespit_id : null,
        'illegal_cari_id' => isset($edit_data->illegal_cari_id) ? $edit_data->illegal_cari_id : null,
        'illegal_cari_bilgi' => isset($edit_data->illegal_cari_isletme_adi) ? $edit_data->illegal_cari_isletme_adi : '',
        'illegal_cari_telefon' => isset($edit_data->illegal_cari_firmaTelefon) ? $edit_data->illegal_cari_firmaTelefon : '',
        'illegal_cari_ulke' => isset($edit_data->illegal_cari_ulke) ? $edit_data->illegal_cari_ulke : '',
        'illegal_cari_il' => isset($edit_data->illegal_cari_il) ? $edit_data->illegal_cari_il : '',
        'illegal_cari_ilce' => isset($edit_data->illegal_cari_ilce) ? $edit_data->illegal_cari_ilce : '',
        'illegal_cari_adres' => isset($edit_data->illegal_cari_adres) ? $edit_data->illegal_cari_adres : '',
        'illegal_tespit_tarih' => isset($edit_data->illegal_tespit_tarih) ? $edit_data->illegal_tespit_tarih : '',
        'illegal_tespit_saat' => isset($edit_data->illegal_tespit_saat) ? $edit_data->illegal_tespit_saat : '',
        'illegal_tespit_aciklama' => isset($edit_data->illegal_tespit_aciklama) ? $edit_data->illegal_tespit_aciklama : '',
        'illegal_tespit_takim_id' => isset($edit_data->illegal_tespit_takim_id) ? $edit_data->illegal_tespit_takim_id : '',
        'illegal_tespit_rakip_takim_id' => isset($edit_data->illegal_tespit_rakip_takim_id) ? $edit_data->illegal_tespit_rakip_takim_id : '',
        'illegal_tespit_personel_id' => isset($edit_data->illegal_tespit_personel_id) ? $edit_data->illegal_tespit_personel_id : '',
        'illegal_tespit_avukat_id' => isset($edit_data->illegal_tespit_avukat_id) ? $edit_data->illegal_tespit_avukat_id : '',
        'illegal_tespit_stokGrup_id' => isset($edit_data->illegal_tespit_stokGrup_id) ? $edit_data->illegal_tespit_stokGrup_id : '',
        'illegal_tespit_imzali' => isset($edit_data->illegal_tespit_imzali) ? $edit_data->illegal_tespit_imzali : ''
    ]) ?>;
    console.log('Edit Data:', editData); // Debug için
    <?php else: ?>
    var editData = null;
    console.log('No edit data found'); // Debug için
    <?php endif; ?>
    
    // Login olan kullanıcı bilgisi
    <?php 
    $login_info = $this->session->userdata('login_info');
    $current_user_id = isset($login_info->kullanici_id) ? $login_info->kullanici_id : null;
    $current_user_grup_id = isset($login_info->grup_id) ? $login_info->grup_id : null;
    ?>
    var currentUserId = <?= json_encode($current_user_id) ?>;
    var currentUserGrupId = <?= json_encode($current_user_grup_id) ?>;
    console.log('Current User ID:', currentUserId, 'Grup ID:', currentUserGrupId);
    
    <?php if(isset($error_message)): ?>
    console.error('Error: <?= $error_message ?>');
    alert('Hata: <?= $error_message ?>');
    <?php endif; ?>
    
    // Veriler yükleme
    loadUlkeler();
    setupCariAutocomplete();
    loadIller();
    loadTakimlar();
    loadPersoneller();
    loadAvukatlar();
    loadStokGruplari();
    
    // Select2 başlat
    $('.select2-search').select2({
        placeholder: "Ara ve seç...",
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
    
    // Edit verilerini dolduran fonksiyon
    function populateEditData() {
        if (editData) {
            // Temel bilgileri doldur
            $('#illegal_cari_id').val(editData.illegal_cari_id);
            $('#illegal_cari_bilgi').val(editData.illegal_cari_bilgi);
            $('#illegal_cari_telefon').val(editData.illegal_cari_telefon);
            $('#illegal_cari_adres').val(editData.illegal_cari_adres);
            $('#illegal_tespit_tarih').val(editData.illegal_tespit_tarih);
            $('#illegal_tespit_saat').val(editData.illegal_tespit_saat);
            $('#illegal_tespit_aciklama').val(editData.illegal_tespit_aciklama);
            
            // İmzalı checkbox
            if (editData.illegal_tespit_imzali == '1') {
                $('#illegal_tespit_imzali').prop('checked', true);
            }
            
            // Ülke seçimi
            if (editData.illegal_cari_ulke) {
                $('#illegal_cari_ulke').val(editData.illegal_cari_ulke);
            }
            
            // İl seçimi ve sonrasında ilçe yükleme
            if (editData.illegal_cari_il) {
                $('#illegal_cari_il').val(editData.illegal_cari_il);
                // İlçeleri yükle
                loadIlcelerForEdit(editData.illegal_cari_il, editData.illegal_cari_ilce);
            }
            
            // Takım, personel, avukat ve hizmet seçimlerini ayarla
            setTimeout(function() {
                if (editData.illegal_tespit_takim_id) {
                    $('#illegal_tespit_takim_id').val(editData.illegal_tespit_takim_id).trigger('change');
                }
                if (editData.illegal_tespit_rakip_takim_id) {
                    $('#illegal_tespit_rakip_takim_id').val(editData.illegal_tespit_rakip_takim_id).trigger('change');
                }
                if (editData.illegal_tespit_personel_id) {
                    $('#illegal_tespit_personel_id').val(editData.illegal_tespit_personel_id).trigger('change');
                }
                if (editData.illegal_tespit_avukat_id) {
                    $('#illegal_tespit_avukat_id').val(editData.illegal_tespit_avukat_id).trigger('change');
                }
                if (editData.illegal_tespit_stokGrup_id) {
                    $('#illegal_tespit_stokGrup_id').val(editData.illegal_tespit_stokGrup_id);
                }
            }, 1000);
        }
    }
    
    // Edit için ilçeleri yükleme
    function loadIlcelerForEdit(il_id, ilce_id) {
        if(il_id) {
            $('#illegal_cari_ilce').prop('disabled', false);
            $('#illegal_cari_ilce').html('<option>Yükleniyor...</option>');
            $.ajax({
                url: '<?= base_url("home/get_ilceler") ?>',
                type: 'POST',
                data: {il_id: il_id},
                dataType: 'json',
                success: function(result) {
                    if(result && result.status !== 'error') {
                        var options = '<option value="">İlçe Seçiniz</option>';
                        $.each(result.data, function(i, ilce) {
                            var selected = (ilce.id == ilce_id) ? ' selected' : '';
                            options += '<option value="'+ilce.id+'"'+selected+'>'+ilce.ilce+'</option>';
                        });
                        $('#illegal_cari_ilce').html(options);
                    } else {
                        $('#illegal_cari_ilce').html('<option value="">İlçe bulunamadı</option>');
                    }
                },
                error: function() {
                    $('#illegal_cari_ilce').html('<option value="">Hata oluştu</option>');
                }
            });
        }
    }
    
    // Cari autocomplete sistemi
    function setupCariAutocomplete() {
        $('#illegal_cari_bilgi').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '<?= base_url("illegal/cari_autocomplete") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        term: request.term
                    },
                    success: function(data) {
                        response(data);
                    },
                    error: function() {
                        response([]);
                    }
                });
            },
            minLength: 3,
            select: function(event, ui) {
                $('#illegal_cari_id').val(ui.item.id);
                $('#illegal_cari_bilgi').val(ui.item.label);
                return false;
            },
            focus: function(event, ui) {
                $('#illegal_cari_bilgi').val(ui.item.label);
                return false;
            }
        });
        
        // Textbox temizlendiğinde hidden field'ı da temizle
        $('#illegal_cari_bilgi').on('input', function() {
            if ($(this).val() === '') {
                $('#illegal_cari_id').val('');
            }
        });
    }
    
    // Ülkeleri yükleme
    function loadUlkeler() {
        $.ajax({
            url: '<?= base_url("illegal/get_ulkeler") ?>',
            type: 'GET',
            dataType: 'json',
            success: function(result) {
                if(result && Array.isArray(result)) {
                    var options = '<option value="">Ülke Seçiniz</option>';
                    $.each(result, function(i, ulke) {
                        var selected = ulke.id == 3 ? 'selected' : ''; // Türkiye default
                        options += '<option value="'+ulke.id+'" '+selected+'>'+ulke.ulke_adi+'</option>';
                    });
                    $('#illegal_cari_ulke').html(options);
                    checkAndPopulateEditData();
                } else {
                    $('#illegal_cari_ulke').html('<option value="">Ülke yüklenemedi</option>');
                }
            },
            error: function() {
                $('#illegal_cari_ulke').html('<option value="">Hata oluştu</option>');
            }
        });
    }

    // İlleri yükleme
    function loadIller() {
        $.ajax({
            url: '<?= base_url("home/get_iller") ?>',
            type: 'POST',
            dataType: 'json',
            success: function(result) {
                if(result && result.status !== 'error') {
                    var options = '<option value="">İl Seçiniz</option>';
                    $.each(result.data, function(i, il) {
                        options += '<option value="'+il.id+'">'+il.il+'</option>';
                    });
                    $('#illegal_cari_il').html(options);
                    checkAndPopulateEditData();
                } else {
                    $('#illegal_cari_il').html('<option value="">İl yüklenemedi</option>');
                }
            },
            error: function() {
                $('#illegal_cari_il').html('<option value="">Hata oluştu</option>');
            }
        });
    }
    
    // İl değiştiğinde ilçeleri yükle
    $('#illegal_cari_il').on('change', function() {
        var il_id = $(this).val();
        if(il_id) {
            $('#illegal_cari_ilce').prop('disabled', false);
            $('#illegal_cari_ilce').html('<option>Yükleniyor...</option>');
            $.ajax({
                url: '<?= base_url("home/get_ilceler") ?>',
                type: 'POST',
                data: {il_id: il_id},
                dataType: 'json',
                success: function(result) {
                    if(result && result.status !== 'error') {
                        var options = '<option value="">İlçe Seçiniz</option>';
                        $.each(result.data, function(i, ilce) {
                            options += '<option value="'+ilce.id+'">'+ilce.ilce+'</option>';
                        });
                        $('#illegal_cari_ilce').html(options);
                    } else {
                        $('#illegal_cari_ilce').html('<option value="">İlçe bulunamadı</option>');
                    }
                },
                error: function() {
                    $('#illegal_cari_ilce').html('<option value="">Hata oluştu</option>');
                }
            });
        } else {
            $('#illegal_cari_ilce').prop('disabled', true).html('<option value="">Önce il seçiniz</option>');
        }
    });
    
    // Takımlar yükleme
    function loadTakimlar() {
        $.ajax({
            url: '<?= base_url("illegal/takimlar_listele") ?>',
            type: 'POST',
            dataType: 'json',
            success: function(result) {
                console.log('Takimlar response:', result); // Debug için
                if(result && result.status === 'success') {
                    var options1 = '<option value="">Takım Seçiniz</option>';
                    var options2 = '<option value="">Rakip Takım Seçiniz</option>';
                    $.each(result.data, function(i, takim) {
                        options1 += '<option value="'+takim.illegal_tespit_takim_id+'">'+takim.illegal_tespit_takim_adi+'</option>';
                        options2 += '<option value="'+takim.illegal_tespit_takim_id+'">'+takim.illegal_tespit_takim_adi+'</option>';
                    });
                    $('#illegal_tespit_takim_id').html(options1);
                    $('#illegal_tespit_rakip_takim_id').html(options2);
                    checkAndPopulateEditData();
                } else {
                    console.error('Takimlar error:', result);
                    var errorMsg = result.message || 'Takım yüklenemedi';
                    $('#illegal_tespit_takim_id').html('<option value="">'+errorMsg+'</option>');
                    $('#illegal_tespit_rakip_takim_id').html('<option value="">'+errorMsg+'</option>');
                    toastr.error('Takımlar yüklenirken hata: ' + errorMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('Takimlar ajax error:', xhr.responseText);
                $('#illegal_tespit_takim_id').html('<option value="">AJAX Hatası</option>');
                $('#illegal_tespit_rakip_takim_id').html('<option value="">AJAX Hatası</option>');
                toastr.error('Takımlar AJAX hatası: ' + error);
            }
        });
        
        // Takım dropdownlarına select2 arama özelliği ekle
        $('#illegal_tespit_takim_id').select2({
            placeholder: "Takım ara ve seç...",
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
        
        $('#illegal_tespit_rakip_takim_id').select2({
            placeholder: "Rakip takım ara ve seç...",
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
    }
    
    // Personeller yükleme
    function loadPersoneller() {
        $.ajax({
            url: '<?= base_url("illegal/get_personeller") ?>',
            type: 'POST',
            dataType: 'json',
            timeout: 10000, // 10 saniye timeout
            success: function(result) {
                console.log('Personeller response:', result); // Debug için
                if(result && result.status === 'success' && result.data) {
                    var options = '<option value="">Personel Seçiniz</option>';
                    $.each(result.data, function(i, personel) {
                        if(personel.kullanici_id && personel.kullanici_ad) {
                            var personelAdi = personel.kullanici_ad;
                            if(personel.kullanici_soyad) {
                                personelAdi += ' ' + personel.kullanici_soyad;
                            }
                            options += '<option value="'+personel.kullanici_id+'">'+personelAdi+'</option>';
                        }
                    });
                    $('#illegal_tespit_personel_id').html(options);
                    
                    // Yeni kayıt ise ve edit data yoksa, login olan kullanıcıyı seç
                    if (!editData && currentUserId) {
                        $('#illegal_tespit_personel_id').val(currentUserId).trigger('change');
                        console.log('Personel otomatik seçildi:', currentUserId);
                    }
                    
                    checkAndPopulateEditData();
                } else {
                    console.error('Personeller error:', result);
                    var errorMsg = (result && result.message) ? result.message : 'Personel yüklenemedi';
                    $('#illegal_tespit_personel_id').html('<option value="">'+errorMsg+'</option>');
                    toastr.warning('Personeller yüklenirken sorun: ' + errorMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('Personeller ajax error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    readyState: xhr.readyState,
                    statusText: xhr.statusText
                });
                
                // Fallback olarak manuel personel listesi
                var fallbackOptions = `
                    <option value="">Personel Seçiniz</option>
                    <option value="1">Admin User</option>
                    <option value="2">Test Personel</option>
                    <option value="3">Demo Kullanıcı</option>
                `;
                $('#illegal_tespit_personel_id').html(fallbackOptions);
                
                if (status === 'timeout') {
                    toastr.error('Personel listesi yükleme zaman aşımı');
                } else {
                    toastr.error('Personel listesi AJAX hatası: ' + error);
                }
            }
        });
        
        // Personel dropdown'a select2 arama özelliği ekle
        $('#illegal_tespit_personel_id').select2({
            placeholder: "Personel ara ve seç...",
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
    }
    
    // Avukatlar yükleme (Sadece Hukuk grubu - grup_id=4)
    function loadAvukatlar() {
        $.ajax({
            url: '<?= base_url("illegal/get_personeller") ?>',
            type: 'POST',
            data: { grup_id: 4 }, // Sadece Hukuk grubu
            dataType: 'json',
            timeout: 10000,
            success: function(result) {
                console.log('Avukatlar response:', result);
                if(result && result.status === 'success' && result.data) {
                    var options = '<option value="">Avukat Seçiniz</option>';
                    $.each(result.data, function(i, personel) {
                        if(personel.kullanici_id && personel.kullanici_ad) {
                            var personelAdi = personel.kullanici_ad;
                            if(personel.kullanici_soyad) {
                                personelAdi += ' ' + personel.kullanici_soyad;
                            }
                            options += '<option value="'+personel.kullanici_id+'">'+personelAdi+'</option>';
                        }
                    });
                    $('#illegal_tespit_avukat_id').html(options);
                    
                    // Yeni kayıt ise ve edit data yoksa, login olan kullanıcı Hukuk grubundaysa seç
                    if (!editData && currentUserId && currentUserGrupId == 4) {
                        $('#illegal_tespit_avukat_id').val(currentUserId).trigger('change');
                        console.log('Avukat otomatik seçildi:', currentUserId);
                    }
                    
                    checkAndPopulateEditData();
                } else {
                    console.error('Avukatlar error:', result);
                    var errorMsg = (result && result.message) ? result.message : 'Avukat yüklenemedi';
                    $('#illegal_tespit_avukat_id').html('<option value="">'+errorMsg+'</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Avukatlar ajax error:', error);
                $('#illegal_tespit_avukat_id').html('<option value="">Avukat Seçiniz</option>');
            }
        });
        
        // Avukat dropdown'a select2 arama özelliği ekle
        $('#illegal_tespit_avukat_id').select2({
            placeholder: "Avukat ara ve seç...",
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
    }
    
    // Stok Grupları yükleme
    function loadStokGruplari() {
        $.ajax({
            url: '<?= base_url("illegal/get_stok_gruplari") ?>',
            type: 'POST',
            dataType: 'json',
            success: function(result) {
                console.log('Stok gruplari response:', result); // Debug için
                if(result && result.status === 'success') {
                    var options = '<option value="">Hizmet Seçiniz</option>';
                    $.each(result.data, function(i, grup) {
                        var selected = (grup.stokGrup_id == '1') ? ' selected' : '';
                        options += '<option value="'+grup.stokGrup_id+'"'+selected+'>'+grup.stokGrup_ad+'</option>';
                    });
                    $('#illegal_tespit_stokGrup_id').html(options);
                    
                    // Stok grupları yüklendikten sonra edit verilerini doldur
                    checkAndPopulateEditData();
                } else {
                    console.error('Stok gruplari error:', result);
                    var errorMsg = result.message || 'Hizmet yüklenemedi';
                    $('#illegal_tespit_stokGrup_id').html('<option value="">'+errorMsg+'</option>');
                    toastr.error('Hizmetler yüklenirken hata: ' + errorMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('Stok gruplari ajax error:', xhr.responseText);
                $('#illegal_tespit_stokGrup_id').html('<option value="">AJAX Hatası</option>');
                toastr.error('Hizmetler AJAX hatası: ' + error);
            }
        });
    }
    
    // Tüm veriler yüklendikten sonra edit verilerini doldur
    var loadedCount = 0;
    var totalLoads = 5; // iller, takimlar, personeller, avukatlar, stokgruplari
    
    function checkAndPopulateEditData() {
        loadedCount++;
        console.log('checkAndPopulateEditData called. loadedCount:', loadedCount, 'totalLoads:', totalLoads, 'editData:', editData);
        if (loadedCount >= totalLoads && editData) {
            console.log('Populating edit data...');
            populateEditData();
        }
    }

    // Form gönderimi
    $('#illegalTespitForm').on('submit', function(e) {
        e.preventDefault();
        
        // Gerekli alanları kontrol et
        var cari_bilgi = $('#illegal_cari_bilgi').val().trim();
        var cari_id = $('#illegal_cari_id').val();
        var tespit_tarih = $('#illegal_tespit_tarih').val();
        var tespit_saat = $('#illegal_tespit_saat').val();
        var takim_id = $('#illegal_tespit_takim_id').val();
        var rakip_takim_id = $('#illegal_tespit_rakip_takim_id').val();
        var personel_id = $('#illegal_tespit_personel_id').val();
        
        if (!cari_bilgi) {
            toastr.error('Cari bilgisi zorunludur!');
            return;
        }
        
        if (!tespit_tarih) {
            toastr.error('Tespit tarihi zorunludur!');
            return;
        }
        
        if (!tespit_saat) {
            toastr.error('Tespit saati zorunludur!');
            return;
        }
        
        if (!takim_id) {
            toastr.error('Takım seçimi zorunludur!');
            return;
        }
        
        if (!rakip_takim_id) {
            toastr.error('Rakip takım seçimi zorunludur!');
            return;
        }
        
        if (!personel_id) {
            toastr.error('Tespiti yapan personel seçimi zorunludur!');
            return;
        }
        
        var hizmet_id = $('#illegal_tespit_stokGrup_id').val();
        if (!hizmet_id) {
            toastr.error('Hizmet seçimi zorunludur!');
            return;
        }
        
        var il_id = $('#illegal_cari_il').val();
        if (!il_id) {
            toastr.error('İl seçimi zorunludur!');
            return;
        }
        
        var ilce_id = $('#illegal_cari_ilce').val();
        if (!ilce_id) {
            toastr.error('İlçe seçimi zorunludur!');
            return;
        }

        // Submit butonunu devre dışı bırak
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...');

        // FormData ile dosyaları da gönder
        var formData = new FormData(this);

        // AJAX ile form gönder
        $.ajax({
            url: '<?= base_url("illegal/illegal_tespit_kaydet") ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    $('#successMessage').fadeIn();
                    toastr.success('İllegal tespit başarıyla kaydedildi!');
                    $('#illegalTespitForm')[0].reset();
                    
                    // Dropdown'ları yeniden yükle
                    setupCariAutocomplete();
                    loadTakimlar();
                    loadPersoneller();
                    loadAvukatlar();
                    loadStokGruplari();
                    
                    // Tarih ve saat alanlarını bugün olarak ayarla
                    $('#illegal_tespit_tarih').val('<?= date('Y-m-d') ?>');
                    $('#illegal_tespit_saat').val('<?= date('H:i') ?>');
                    
                    setTimeout(function() {
                        $('#successMessage').fadeOut();
                    }, 3000);
                } else {
                    toastr.error(response.message || 'Kayıt sırasında hata oluştu!');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html(originalText);
                toastr.error('Sunucu hatası oluştu!');
            }
        });
    });

    // Form temizleme
    $('button[type="reset"]').on('click', function() {
        $('#successMessage').hide();
        $('#illegal_cari_id').val('');
        $('#illegal_tespit_tarih').val('<?= date('Y-m-d') ?>');
        $('#illegal_tespit_saat').val('<?= date('H:i') ?>');
        toastr.info('Form temizlendi');
    });
});
</script>

</body>
</html>