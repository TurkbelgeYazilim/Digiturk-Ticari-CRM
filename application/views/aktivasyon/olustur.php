<!DOCTYPE html>

<html lang="tr">

<head>

    <?php $this->load->view("include/head-tags"); ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">

    <style>

        .select2-container--default .select2-results__option--highlighted[aria-selected] {

            background-color: #5c2d91 !important;

            color: #fff !important;

        }

        .form-group {

            margin-bottom: 1rem !important;

        }

        .card {

            border-radius: 10px;

            box-shadow: 0 4px 8px rgba(0,0,0,0.1);

        }

        .btn-primary {

            background-color: #5c2d91;

            border-color: #5c2d91;

        }

        .btn-primary:hover {

            background-color: #4a2373;

            border-color: #4a2373;

        }

        .required {

            color: red;

        }

        #musteriDetayAlani .card {

            border: 1px solid #e3e6f0;

            border-radius: 8px;

        }

        #musteriDetayAlani .card-header {

            background-color: #f8f9fc;

            border-bottom: 1px solid #e3e6f0;

        }

        #evrakGorselAlani .btn {

            margin-right: 5px;

            margin-bottom: 5px;

        }

        #evrakGorselAlani {

            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 10px;

        }

        #personelBilgi {

            background-color: #f8f9fa;

            border-color: #dee2e6;

        }

        .tahsilat-item {

            border: 1px solid #e3e6f0;

            border-radius: 6px;

            padding: 10px;

            margin-bottom: 10px;

            background-color: #f8f9fc;

        }

        .tahsilat-item .badge {

            font-size: 0.75rem;

            margin-right: 5px;

        }

        .tahsilat-durum-onay {

            background-color: #28a745;

            color: white;

        }

        .tahsilat-durum-bekliyor {

            background-color: #ffc107;

            color: black;

        }

        .tahsilat-durum-red {

            background-color: #dc3545;

            color: white;

        }

        .tahsilat-gorsel-btn {

            font-size: 0.75rem;

            padding: 2px 8px;

        }

        .tutar-buyuk {

            font-size: 1.1em;

            font-weight: bold;

            color: #28a745;

        }

        .table th {

            background-color: #f8f9fc;

            border-color: #e3e6f0;

            font-weight: 600;

            font-size: 0.85rem;

        }

        .table td {

            vertical-align: middle;

            border-color: #e3e6f0;

        }

        #sozlesmeTutar {

            background-color: #f8f9fa;

            border-color: #dee2e6;

            font-weight: 600;

            color: #155724;

        }

        #sozlesmeAciklama {

            background-color: #f8f9fa;

            border-color: #dee2e6;

            resize: none;

        }

        #sozlesmeGorselAlani {

            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 10px;

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

                        <?php

                        $isEditMode = isset($aktivasyon) && !empty($aktivasyon);

                        ?>

                        <h3 class="page-title"><?= $isEditMode ? 'Digiturk Aktivasyon Düzenle' : 'Digiturk Aktivasyon Oluştur' ?></h3>

                        <ul class="breadcrumb">

                            <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>

                            <li class="breadcrumb-item"><a href="<?= base_url('aktivasyon'); ?>">Aktivasyon</a></li>

                            <li class="breadcrumb-item active"><?= $isEditMode ? 'Digiturk Düzenle' : 'Digiturk Oluştur' ?></li>

                        </ul>

                    </div>

                    <div class="d-flex justify-content-end text-align-center col-sm-2">

                        <a class="btn btn-outline-light" href="javascript:history.back()">

                            <i class="fa fa-history"></i><br>Önceki Sayfa

                        </a>

                    </div>

                </div>

            </div>

            <!-- /Page Header -->



            <div class="row">

                <div class="col-md-12">

                    <div class="card">

                        <div class="card-body">

                            <h4 class="card-title"><?= $isEditMode ? 'Digiturk Aktivasyon Düzenle' : 'Yeni Digiturk Aktivasyon Oluştur' ?></h4>

                            

                            <form action="<?= base_url('aktivasyon/kaydet') ?>" method="POST" id="aktivasyonForm">

                                <?php if($isEditMode): ?>

                                    <input type="hidden" name="aktivasyon_id" value="<?= $aktivasyon->aktivasyon_id ?>">

                                <?php endif; ?>



                                <div class="row">

                                    <!-- Müşteri Seçimi -->

                                    <div class="form-group col-md-6">

                                        <label for="cari_id">Müşteri <span class="required">*</span></label>

                                        <select class="form-control select2" name="cari_id" id="cari_id" required>

                                            <option value="">Müşteri Seçiniz...</option>

                                            <?php if(isset($cari_listesi) && !empty($cari_listesi)): ?>

                                                <?php foreach($cari_listesi as $cari): ?>

                                                    <?php 

                                                        $displayText = $cari->cari_ad;

                                                        $extraInfo = array();

                                                        if (!empty($cari->il_adi)) {

                                                            $extraInfo[] = $cari->il_adi;

                                                        }

                                                        if (!empty($cari->ilce_adi)) {

                                                            $extraInfo[] = $cari->ilce_adi;

                                                        }

                                                        if (!empty($cari->cari_firmaTelefon)) {

                                                            $extraInfo[] = $cari->cari_firmaTelefon;

                                                        }

                                                        

                                                        if (!empty($extraInfo)) {

                                                            $displayText .= ' (' . implode(', ', $extraInfo) . ')';

                                                        }

                                                    ?>

                                                    <option value="<?= $cari->cari_id ?>" 

                                                        <?= ($isEditMode && $aktivasyon->aktivasyon_cari_id == $cari->cari_id) ? 'selected' : '' ?>>

                                                        <?= $displayText ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            <?php else: ?>

                                                <option value="">Cari bulunamadı</option>

                                            <?php endif; ?>

                                        </select>

                                    </div>



                                    <!-- Durum -->

                                    <div class="form-group col-md-6">

                                        <label for="durum_id">Durum <span class="required">*</span></label>

                                        <select class="form-control" name="durum_id" id="durum_id" required>

                                            <option value="">Durum Seçiniz...</option>

                                            <?php if(isset($durum_listesi) && !empty($durum_listesi)): ?>

                                                <?php foreach($durum_listesi as $durum): ?>

                                                    <option value="<?= $durum->aktivasyon_durum_id ?>"

                                                        <?= ($isEditMode && $aktivasyon->aktivasyon_durum_id == $durum->aktivasyon_durum_id) ? 'selected' : '' ?>>

                                                        <?= $durum->aktivasyon_durum_adi ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            <?php endif; ?>

                                        </select>

                                    </div>

                                </div>



                                <div class="row">

                                    <!-- Üye No -->

                                    <div class="form-group col-md-4">

                                        <label for="uye_no">Üye No</label>

                                        <input type="text" class="form-control" name="uye_no" id="uye_no" 

                                               value="<?= $isEditMode ? $aktivasyon->aktivasyon_uye_no : '' ?>" 

                                               placeholder="Üye numarasını giriniz">

                                    </div>



                                    <!-- Kutu No -->

                                    <div class="form-group col-md-4">

                                        <label for="kutu_no">Kutu No</label>

                                        <input type="text" class="form-control" name="kutu_no" id="kutu_no" 

                                               value="<?= $isEditMode ? $aktivasyon->aktivasyon_kutu_no : '' ?>" 

                                               placeholder="Kutu numarasını giriniz">

                                    </div>



                                    <!-- Kart No -->

                                    <div class="form-group col-md-4">

                                        <label for="kart_no">Kart No</label>

                                        <input type="text" class="form-control" name="kart_no" id="kart_no" 

                                               value="<?= $isEditMode ? $aktivasyon->aktivasyon_kart_no : '' ?>" 

                                               placeholder="Kart numarasını giriniz">

                                    </div>

                                </div>



                                <div class="row">

                                    <!-- Açıklama -->

                                    <div class="form-group col-md-12">

                                        <label for="aciklama">Açıklama</label>

                                        <textarea class="form-control" name="aciklama" id="aciklama" rows="3" 

                                                  placeholder="Digiturk aktivasyon ile ilgili açıklama giriniz"><?= $isEditMode ? $aktivasyon->aktivasyon_aciklama : '' ?></textarea>

                                    </div>

                                </div>



                                <!-- Müşteri Bilgileri Bölümü -->

                                <div id="musteriDetayAlani" style="display: none;">

                                    <div class="card mt-3">

                                        <div class="card-header">

                                            <h5 class="card-title mb-0">Müşteri Bilgileri</h5>

                                        </div>

                                        <div class="card-body">

                                            <div class="row">

                                                <!-- Personel -->

                                                <div class="form-group col-md-3">

                                                    <label>Personel</label>

                                                    <input type="text" class="form-control" id="personelBilgi" readonly 

                                                           placeholder="Müşteri seçildiğinde otomatik doldurulur">

                                                </div>



                                                <!-- Sözleşme Tutar -->

                                                <div class="form-group col-md-3">

                                                    <label>Sözleşme Tutar</label>

                                                    <input type="text" class="form-control" id="sozlesmeTutar" readonly 

                                                           placeholder="Tutar bilgisi">

                                                </div>



                                                <!-- Müşteri Evrak/Görsel -->

                                                <div class="form-group col-md-4">

                                                    <label>Müşteri Evrak/Görsel</label>

                                                    <div id="evrakGorselAlani">

                                                        <p class="text-muted mb-0">Müşteri seçildiğinde evrak ve görsel linkleri burada gösterilecek</p>

                                                    </div>

                                                </div>



                                                <!-- Düzenle Butonu -->

                                                <div class="form-group col-md-2">

                                                    <label>&nbsp;</label>

                                                    <div>

                                                        <a href="#" id="musteriDuzenleBtn" class="btn btn-warning btn-sm" style="display: none;" target="_blank">

                                                            <i class="fa fa-edit"></i> Düzenle

                                                        </a>

                                                    </div>

                                                </div>

                                            </div>

                                            

                                            <div class="row">

                                                <!-- Sözleşme Açıklama -->

                                                <div class="form-group col-md-5">

                                                    <label>Sözleşme Açıklama</label>

                                                    <textarea class="form-control" id="sozlesmeAciklama" rows="2" readonly 

                                                              placeholder="Sözleşme açıklama bilgisi"></textarea>

                                                </div>



                                                <!-- Sözleşme Görsel -->

                                                <div class="form-group col-md-5">

                                                    <label>Sözleşme Görsel</label>

                                                    <div id="sozlesmeGorselAlani">

                                                        <p class="text-muted mb-0">Sözleşme görselleri burada gösterilecek</p>

                                                    </div>

                                                </div>



                                                <!-- Sözleşme Düzenle Butonu -->

                                                <div class="form-group col-md-2">

                                                    <label>&nbsp;</label>

                                                    <div>

                                                        <a href="#" id="sozlesmeDuzenleBtn" class="btn btn-info btn-sm" style="display: none;" target="_blank">

                                                            <i class="fa fa-file-invoice"></i> Sözleşme Düzenle

                                                        </a>

                                                    </div>

                                                </div>

                                            </div>



                                            <!-- Tahsilat Bilgileri Bölümü -->

                                            <div class="row mt-3">

                                                <div class="col-md-12">

                                                    <h6 class="text-primary mb-3">

                                                        <i class="fa fa-money"></i> Tahsilat Bilgileri

                                                    </h6>

                                                    <div id="tahsilatBilgiAlani">

                                                        <p class="text-muted mb-0">Müşteri seçildiğinde tahsilat bilgileri burada gösterilecek</p>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>



                                <div class="row">

                                    <div class="col-md-12">

                                        <div class="form-group">

                                            <button type="submit" class="btn btn-primary">

                                                <i class="fa fa-save"></i> 

                                                <?= $isEditMode ? 'Digiturk Güncelle' : 'Digiturk Kaydet' ?>

                                            </button>

                                            <a href="<?= base_url('aktivasyon') ?>" class="btn btn-secondary ml-2">

                                                <i class="fa fa-times"></i> İptal

                                            </a>

                                        </div>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



<?php $this->load->view("include/footer-js"); ?>



<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>

$(document).ready(function() {

    // Select2 için müşteri dropdown'ını başlat

    $('#cari_id').select2({

        placeholder: 'Müşteri ara ve seç...',

        allowClear: true,

        ajax: {

            url: '<?= base_url("aktivasyon/cari_ara") ?>',

            dataType: 'json',

            delay: 250,

            data: function (params) {

                return {

                    q: params.term, // search term

                    stok_grup_id: '1,279' // Digiturk için birden fazla stok grup desteği (1 ve 279)

                };

            },

            processResults: function (data) {

                return {

                    results: data

                };

            },

            cache: true

        },

        minimumInputLength: 2

    });



    // Müşteri seçildiğinde detayları getir

    $('#cari_id').on('select2:select', function (e) {

        var cariId = e.params.data.id;

        if (cariId) {

            musteriDetayGetir(cariId);

        }

    });



    // Müşteri seçimi temizlendiğinde detay alanını gizle

    $('#cari_id').on('select2:clear', function (e) {

        $('#musteriDetayAlani').hide();

        $('#personelBilgi').val('');

        $('#sozlesmeTutar').val('');

        $('#sozlesmeAciklama').val('');

        $('#evrakGorselAlani').html('<p class="text-muted mb-0">Müşteri seçildiğinde evrak ve görsel linkleri burada gösterilecek</p>');

        $('#sozlesmeGorselAlani').html('<p class="text-muted mb-0">Sözleşme görselleri burada gösterilecek</p>');

        $('#tahsilatBilgiAlani').html('<p class="text-muted mb-0">Müşteri seçildiğinde tahsilat bilgileri burada gösterilecek</p>');

        $('#musteriDuzenleBtn').hide();

        $('#sozlesmeDuzenleBtn').hide();

    });



    // AJAX ile müşteri detaylarını getir

    function musteriDetayGetir(cariId) {

        $.ajax({

            url: '<?= base_url("aktivasyon/cari_detay_getir") ?>',

            type: 'GET',

            data: { cari_id: cariId },

            dataType: 'json',

            success: function(response) {

                if (response.error) {

                    console.error('Hata:', response.error);

                    return;

                }



                // Personel bilgisini doldur

                $('#personelBilgi').val(response.personel || 'Belirtilmemiş');



                // Sözleşme bilgilerini doldur

                $('#sozlesmeTutar').val(response.sozlesme_tutar || 'Belirtilmemiş');

                $('#sozlesmeAciklama').val(response.sozlesme_aciklama || 'Açıklama bulunmuyor');



                // Sözleşme görsel linklerini oluştur

                var sozlesmeGorselHtml = '';

                if (response.sozlesme_dosyalar && response.sozlesme_dosyalar.length > 0) {

                    sozlesmeGorselHtml += '<strong>Sözleşme:</strong> ';

                    response.sozlesme_dosyalar.forEach(function(sozlesme, index) {

                        sozlesmeGorselHtml += '<button type="button" class="btn btn-sm btn-outline-info mr-1 mb-1" ';

                        sozlesmeGorselHtml += 'onclick="window.open(\'' + sozlesme.link + '\', \'sozlesmeGorsel' + index + '\', \'width=800,height=600,scrollbars=yes,resizable=yes\')">';

                        sozlesmeGorselHtml += '<i class="fa fa-file-image"></i> Görsel ' + (index + 1);

                        sozlesmeGorselHtml += '</button>';

                    });

                } else {

                    sozlesmeGorselHtml = '<p class="text-muted mb-0">Sözleşme görseli bulunmuyor</p>';

                }

                $('#sozlesmeGorselAlani').html(sozlesmeGorselHtml);



                // Evrak ve görsel linklerini oluştur

                var evrakGorselHtml = '';

                

                // Evrak dosyaları

                if (response.evrak_dosyalar && response.evrak_dosyalar.length > 0) {

                    evrakGorselHtml += '<strong>Evraklar:</strong> ';

                    evrakGorselHtml += '<button type="button" class="btn btn-sm btn-outline-primary mr-2 mb-1" ';

                    evrakGorselHtml += 'onclick="window.open(\'' + '<?= base_url("cari/evrakGoster/") ?>' + cariId + '\', \'evrakPencere\', \'width=800,height=600,scrollbars=yes,resizable=yes\')">';

                    evrakGorselHtml += '<i class="fa fa-file"></i> ' + response.evrak_dosyalar.length + ' Evrak';

                    evrakGorselHtml += '</button>';

                }



                // Görsel dosyaları

                if (response.fotograf_dosyalar && response.fotograf_dosyalar.length > 0) {

                    if (response.evrak_dosyalar && response.evrak_dosyalar.length > 0) {

                        evrakGorselHtml += ' '; // Boşluk ekle

                    }

                    evrakGorselHtml += '<strong>Görseller:</strong> ';

                    evrakGorselHtml += '<button type="button" class="btn btn-sm btn-outline-success mr-1 mb-1" ';

                    evrakGorselHtml += 'onclick="window.open(\'' + '<?= base_url("cari/gorselGoster/") ?>' + cariId + '\', \'gorselPencere\', \'width=800,height=600,scrollbars=yes,resizable=yes\')">';

                    evrakGorselHtml += '<i class="fa fa-image"></i> ' + response.fotograf_dosyalar.length + ' Görsel';

                    evrakGorselHtml += '</button>';

                }



                if (!evrakGorselHtml) {

                    evrakGorselHtml = '<p class="text-muted mb-0">Bu müşteri için evrak veya görsel bulunmamaktadır.</p>';

                }



                $('#evrakGorselAlani').html(evrakGorselHtml);



                // Düzenle butonu linkini ayarla

                $('#musteriDuzenleBtn').attr('href', response.duzenle_link).show();



                // Sözleşme düzenle butonu linkini ayarla

                if (response.sozlesme_id) {

                    var sozlesmeDuzenleLink = '<?= base_url("fatura/satis-faturasi/") ?>' + response.sozlesme_id;

                    $('#sozlesmeDuzenleBtn').attr('href', sozlesmeDuzenleLink).show();

                } else {

                    $('#sozlesmeDuzenleBtn').hide();

                }



                // Tahsilat bilgilerini işle

                var tahsilatHtml = '';

                if (response.tahsilatlar && response.tahsilatlar.length > 0) {

                    tahsilatHtml += '<div class="table-responsive">';

                    tahsilatHtml += '<table class="table table-sm table-bordered">';

                    tahsilatHtml += '<thead class="thead-light">';

                    tahsilatHtml += '<tr>';

                    tahsilatHtml += '<th>Tahsilat Tipi</th>';

                    tahsilatHtml += '<th>Tutar</th>';

                    tahsilatHtml += '<th>Durum</th>';

                    tahsilatHtml += '<th>Görsel</th>';

                    tahsilatHtml += '<th>Tarih</th>';

                    tahsilatHtml += '<th>İşlemler</th>';

                    tahsilatHtml += '</tr>';

                    tahsilatHtml += '</thead>';

                    tahsilatHtml += '<tbody>';

                    

                    response.tahsilatlar.forEach(function(tahsilat, index) {

                        // Tahsilat tipi iconları ve renkleri

                        var tipIcon = '';

                        var tipClass = '';

                        switch(tahsilat.tahsilat_tipi) {

                            case 'Banka': tipIcon = 'fa fa-university'; tipClass = 'text-primary'; break;

                            case 'Çek': tipIcon = 'fa fa-file-text-o'; tipClass = 'text-info'; break;

                            case 'Kasa': tipIcon = 'fa fa-money'; tipClass = 'text-success'; break;

                            case 'Senet': tipIcon = 'fa fa-file-o'; tipClass = 'text-warning'; break;

                            default: tipIcon = 'fa fa-question'; tipClass = 'text-muted'; break;

                        }

                        

                        // Durum iconları ve renkleri  

                        var durumIcon = '';

                        var durumBadgeClass = '';

                        switch(tahsilat.durum_code) {

                            case 1: 

                                durumIcon = 'fa fa-clock-o'; 

                                durumBadgeClass = 'badge-warning'; 

                                break;

                            case 2: 

                                durumIcon = 'fa fa-check'; 

                                durumBadgeClass = 'badge-success'; 

                                break;

                            case 3: 

                                durumIcon = 'fa fa-times'; 

                                durumBadgeClass = 'badge-danger'; 

                                break;

                            default: 

                                durumIcon = 'fa fa-question'; 

                                durumBadgeClass = 'badge-secondary'; 

                                break;

                        }

                        

                        tahsilatHtml += '<tr>';

                        

                        // Tahsilat Tipi sütunu

                        tahsilatHtml += '<td>';

                        tahsilatHtml += '<i class="' + tipIcon + ' ' + tipClass + ' mr-2"></i>';

                        tahsilatHtml += '<span class="' + tipClass + '">' + tahsilat.tahsilat_tipi + '</span>';

                        tahsilatHtml += '</td>';

                        

                        // Tutar sütunu

                        tahsilatHtml += '<td><strong class="tutar-buyuk">' + tahsilat.tutar + '</strong></td>';

                        

                        // Durum sütunu

                        tahsilatHtml += '<td>';

                        tahsilatHtml += '<span class="badge ' + durumBadgeClass + '">';

                        tahsilatHtml += '<i class="' + durumIcon + ' mr-1"></i>' + tahsilat.durum;

                        tahsilatHtml += '</span>';

                        tahsilatHtml += '</td>';

                        

                        // Görsel sütunu

                        tahsilatHtml += '<td>';

                        if (tahsilat.gorsel && tahsilat.gorsel_link) {

                            tahsilatHtml += '<button type="button" class="btn btn-sm btn-outline-info tahsilat-gorsel-btn" ';

                            tahsilatHtml += 'onclick="window.open(\'' + tahsilat.gorsel_link + '\', \'_blank\')">';

                            tahsilatHtml += '<i class="fa fa-image"></i> Görsel</button>';

                        } else {

                            tahsilatHtml += '<span class="text-muted">-</span>';

                        }

                        tahsilatHtml += '</td>';

                        

                        // Tarih sütunu

                        tahsilatHtml += '<td>';

                        if (tahsilat.olusturma_tarihi) {

                            var tarih = new Date(tahsilat.olusturma_tarihi);

                            tahsilatHtml += '<small>' + tarih.toLocaleDateString('tr-TR') + '</small>';

                        }

                        tahsilatHtml += '</td>';

                        

                        // İşlemler sütunu - Görsel Yükleme

                        tahsilatHtml += '<td>';

                        tahsilatHtml += '<div class="tahsilat-gorsel-upload-wrapper" data-tahsilat-id="' + tahsilat.kayit_id + '" data-tahsilat-tipi="' + tahsilat.tahsilat_tipi_kodu + '">';

                        tahsilatHtml += '<input type="file" class="d-none tahsilat-gorsel-input" id="gorselInput_' + index + '" accept="image/*" onchange="tahsilatGorselYukle(this, ' + tahsilat.kayit_id + ', ' + tahsilat.tahsilat_tipi_kodu + ')">';

                        tahsilatHtml += '<button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById(\'gorselInput_' + index + '\').click()">';

                        tahsilatHtml += '<i class="fa fa-upload"></i> Görsel Yükle';

                        tahsilatHtml += '</button>';

                        tahsilatHtml += '</div>';

                        tahsilatHtml += '</td>';

                        

                        tahsilatHtml += '</tr>';

                    });

                    tahsilatHtml += '</tbody>';

                    tahsilatHtml += '</table>';

                    tahsilatHtml += '</div>';

                } else {

                    tahsilatHtml = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> Bu müşteri için henüz tahsilat kaydı bulunmamaktadır.</div>';

                }

                $('#tahsilatBilgiAlani').html(tahsilatHtml);



                // Detay alanını göster

                $('#musteriDetayAlani').show();

            },

            error: function(xhr, status, error) {

                console.error('AJAX Hatası:', error);

            }

        });

    }



    // Sayfa yüklendiğinde düzenleme modunda müşteri seçiliyse detayları getir

    <?php if($isEditMode && !empty($aktivasyon->aktivasyon_cari_id)): ?>

        musteriDetayGetir(<?= $aktivasyon->aktivasyon_cari_id ?>);

    <?php endif; ?>



    // Form validasyonu

    $('#aktivasyonForm').on('submit', function(e) {

        var cariId = $('#cari_id').val();

        var durumId = $('#durum_id').val();



        if (!cariId) {

            alert('Lütfen bir müşteri seçiniz.');

            e.preventDefault();

            return false;

        }



        if (!durumId) {

            alert('Lütfen bir durum seçiniz.');

            e.preventDefault();

            return false;

        }



        return true;

    });



    // Sadece rakam girişi için input'ları kontrol et

    $('#uye_no, #kutu_no, #kart_no').on('input', function() {

        this.value = this.value.replace(/[^0-9]/g, '');

    });



    // Tahsilat görsel yükleme fonksiyonu

    function tahsilatGorselYukle(input, tahsilatId, tahsilatTipi) {

        if (input.files && input.files[0]) {

            var file = input.files[0];

            

            // Dosya boyutu kontrolü (5MB)

            if (file.size > 5 * 1024 * 1024) {

                alert('Dosya boyutu 5MB\'dan küçük olmalıdır.');

                input.value = '';

                return;

            }

            

            // Dosya türü kontrolü

            if (!file.type.match('image.*')) {

                alert('Lütfen sadece resim dosyası seçiniz.');

                input.value = '';

                return;

            }

            

            var formData = new FormData();

            formData.append('gorsel', file);

            formData.append('tahsilat_id', tahsilatId);

            formData.append('tahsilat_tipi', tahsilatTipi);

            

            // Loading göster

            var wrapper = input.closest('.tahsilat-gorsel-upload-wrapper');

            var button = wrapper.querySelector('button');

            var originalText = button.innerHTML;

            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Yükleniyor...';

            button.disabled = true;

            

            $.ajax({

                url: '<?= base_url("aktivasyon/tahsilat_gorsel_yukle") ?>',

                type: 'POST',

                data: formData,

                contentType: false,

                processData: false,

                success: function(response) {

                    if (response.success) {

                        alert('Görsel başarıyla yüklendi.');

                        // Müşteri detaylarını yenile

                        var cariId = $('#cari_id').val();

                        if (cariId) {

                            musteriDetayGetir(cariId);

                        }

                    } else {

                        alert('Görsel yüklenirken hata oluştu: ' + (response.message || 'Bilinmeyen hata'));

                    }

                },

                error: function(xhr, status, error) {

                    alert('Görsel yüklenirken hata oluştu. Lütfen tekrar deneyiniz.');

                    console.error('Görsel yükleme hatası:', error);

                },

                complete: function() {

                    // Loading'i gizle

                    button.innerHTML = originalText;

                    button.disabled = false;

                    input.value = ''; // Input'u temizle

                }

            });

        }

    }

});

</script>

</body>

</html>

