<!DOCTYPE html>
<html lang="tr">
<head>
    <?php $this->load->view("include/head-tags"); ?>
    <title>Muhasebe Dashboard</title>
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
            <div class="dashboard-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <h1 style="font-size: 28px; font-weight: 700; margin: 0;">
                            <i class="fa fa-calculator mr-3"></i>Muhasebe Dashboard
                        </h1>
                    </div>
                </div>
            </div>

            <!-- Finansal Özet Widget'ları -->
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 15px; cursor: pointer;" onclick="window.location.href='<?= base_url('muhasebe/tahsilat-listesi?tahsilat_tipi[]=1&durum=1') ?>'">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-2" style="font-weight: 600;" id="vadesiGecenBaslik">Tahsilat Yapılmayan: Banka</h6>
                                    <h2 class="mb-0" style="font-weight: 700;" id="vadesiGecenTutar">
                                        <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                    </h2>
                                    <small style="opacity: 0.8;" id="vadesiGecenAdet">Banka Adet/Tutar verisi yükleniyor...</small>
                                </div>
                                <div class="text-right">
                                    <i class="fa fa-university fa-3x" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%); border-radius: 15px; cursor: pointer;" onclick="window.location.href='<?= base_url('muhasebe/tahsilat-listesi?tahsilat_tipi[]=2&durum=1') ?>'">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-2" style="font-weight: 600;" id="onayBekleyenBaslik">Tahsilat Yapılmayan: Çek</h6>
                                    <h2 class="mb-0" style="font-weight: 700;" id="onayBekleyenTutar">
                                        <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                    </h2>
                                    <small style="opacity: 0.8;" id="onayBekleyenAdet">Veriler yükleniyor...</small>
                                </div>
                                <div class="text-right">
                                    <i class="fa fa-credit-card fa-3x" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 15px; cursor: pointer;" onclick="window.location.href='<?= base_url('muhasebe/tahsilat-listesi?tahsilat_tipi[]=3&durum=1') ?>'">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-2" style="font-weight: 600;" id="netKarBaslik">Tahsilat Yapılmayan: Kasa</h6>
                                    <h2 class="mb-0" style="font-weight: 700;" id="netKarTutar">
                                        <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                    </h2>
                                    <small style="opacity: 0.8;" id="karMarji">Veriler yükleniyor...</small>
                                </div>
                                <div class="text-right">
                                    <i class="fa fa-money fa-3x" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; cursor: pointer;" onclick="window.location.href='<?= base_url('muhasebe/tahsilat-listesi?tahsilat_tipi[]=4&durum=1') ?>'">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-2" style="font-weight: 600;" id="kasaBaslik">Tahsilat Yapılmayan: Senet</h6>
                                    <h2 class="mb-0" style="font-weight: 700;" id="kasaBakiye">
                                        <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                    </h2>
                                    <small style="opacity: 0.8;" id="kasaAciklama">Veriler yükleniyor...</small>
                                </div>
                                <div class="text-right">
                                    <i class="fa fa-file-text-o fa-3x" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bekleyen İşlemler ve Ödemeler -->
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 15px; cursor: pointer;" onclick="window.location.href='<?= base_url('muhasebe/tahsilat-listesi?tahsilat_tipi[]=1&durum=2') ?>'">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-2" style="font-weight: 600;" id="bekleyenTahsilatBaslik">Tahsilat Yapıldı: Banka</h6>
                                    <h2 class="mb-0" style="font-weight: 700;" id="bekleyenTahsilatTutar">
                                        <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                    </h2>
                                    <small style="opacity: 0.8;" id="bekleyenTahsilatAdet">Veriler yükleniyor...</small>
                                </div>
                                <div class="text-right">
                                    <i class="fa fa-university fa-3x" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 15px; cursor: pointer;" onclick="window.location.href='<?= base_url('muhasebe/tahsilat-listesi?tahsilat_tipi[]=2&durum=2') ?>'">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-2" style="font-weight: 600;" id="bekleyenOdemeBaslik">Tahsilat Yapıldı: Çek</h6>
                                    <h2 class="mb-0" style="font-weight: 700;" id="bekleyenOdemeTutar">
                                        <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                    </h2>
                                    <small style="opacity: 0.8;" id="bekleyenOdemeAdet">Veriler yükleniyor...</small>
                                </div>
                                <div class="text-right">
                                    <i class="fa fa-credit-card fa-3x" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); border-radius: 15px; cursor: pointer;" onclick="window.location.href='<?= base_url('muhasebe/tahsilat-listesi?tahsilat_tipi[]=3&durum=2') ?>'">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-2" style="font-weight: 600;" id="kdvBaslik">Tahsilat Yapıldı: Kasa</h6>
                                    <h2 class="mb-0" style="font-weight: 700;" id="kdvTutar">
                                        <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                    </h2>
                                    <small style="opacity: 0.8;" id="kdvAciklama">Veriler yükleniyor...</small>
                                </div>
                                <div class="text-right">
                                    <i class="fa fa-money fa-3x" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%); border-radius: 15px; cursor: pointer;" onclick="window.location.href='<?= base_url('muhasebe/tahsilat-listesi?tahsilat_tipi[]=4&durum=2') ?>'">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-2" style="font-weight: 600;" id="bordroBaslik">Tahsilat Yapıldı: Senet</h6>
                                    <h2 class="mb-0" style="font-weight: 700;" id="bordroTutar">
                                        <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                    </h2>
                                    <small style="opacity: 0.8;" id="bordroAciklama">Veriler yükleniyor...</small>
                                </div>
                                <div class="text-right">
                                    <i class="fa fa-file-text-o fa-3x" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personel Bazında Tahsilat Kartları -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px;">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="card-title mb-0" style="font-weight: 600;">
                                        <i class="fa fa-trophy mr-2"></i>Top 3 - Personel Başına Tahsilat Bekleyen
                                    </h5>
                                </div>
                                <div class="text-right">
                                    <i class="fa fa-users fa-3x" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                            
                            <div class="row" id="personelListesi">
                                <div class="col-md-4 mb-2">
                                    <div class="d-flex justify-content-between align-items-center p-3" style="background: rgba(255,255,255,0.1); border-radius: 10px; cursor: pointer;" onclick="personelTahsilatGit(1)" id="personel1Kart">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-warning mr-2" style="font-size: 14px;">1</span>
                                                <strong id="personel1Kullanici">
                                                    <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                                </strong>
                                            </div>
                                            <div class="mt-1">
                                                <span id="personel1Tutar" style="font-size: 18px; font-weight: bold;">—</span>
                                            </div>
                                            <small style="opacity: 0.8;" id="personel1Adet">—</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-2">
                                    <div class="d-flex justify-content-between align-items-center p-3" style="background: rgba(255,255,255,0.1); border-radius: 10px; cursor: pointer;" onclick="personelTahsilatGit(2)" id="personel2Kart">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-light mr-2" style="font-size: 14px; color: #333;">2</span>
                                                <strong id="personel2Kullanici">
                                                    <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                                </strong>
                                            </div>
                                            <div class="mt-1">
                                                <span id="personel2Tutar" style="font-size: 18px; font-weight: bold;">—</span>
                                            </div>
                                            <small style="opacity: 0.8;" id="personel2Adet">—</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-2">
                                    <div class="d-flex justify-content-between align-items-center p-3" style="background: rgba(255,255,255,0.1); border-radius: 10px; cursor: pointer;" onclick="personelTahsilatGit(3)" id="personel3Kart">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-secondary mr-2" style="font-size: 14px;">3</span>
                                                <strong id="personel3Kullanici">
                                                    <i class="fa fa-spinner fa-spin"></i> Yükleniyor...
                                                </strong>
                                            </div>
                                            <div class="mt-1">
                                                <span id="personel3Tutar" style="font-size: 18px; font-weight: bold;">—</span>
                                            </div>
                                            <small style="opacity: 0.8;" id="personel3Adet">—</small>
                                        </div>
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

<!-- Footer JS -->
<?php $this->load->view("include/footer-js"); ?>

<script>
$(document).ready(function() {
    // Sayfa yüklendiğinde tüm verileri paralel olarak çek
    loadDashboardData();
    
    // 30 saniyede bir verileri yenile
    setInterval(loadDashboardData, 30000);
});

function loadDashboardData() {
    // Tek sorgu ile tüm tahsilat özeti (8 satır) çekilecek ve tüm kartlar güncellenecek
    loadTahsilatOzeti();
    // Personel bazında tahsilat verilerini çek
    loadPersonelTahsilatOzeti();
}

// Tek sorgu ile 8 kombinasyonu (tahsilat_tipi x durum) çeken ajax fonksiyonu
function loadTahsilatOzeti() {
    console.log('loadTahsilatOzeti fonksiyonu çağrıldı'); // Debug
    $.ajax({
        url: '<?= base_url('muhasebeapi') ?>',
        type: 'POST',
        data: {
            action: 'getTahsilatOzeti'
        },
        dataType: 'json',
        success: function(response) {
            console.log('AJAX Response:', response); // Debug için eklendi
            if(response.success && Array.isArray(response.rows)) {
                console.log('Response rows:', response.rows); // Debug için eklendi
                // Create lookup by key "tip|durum" (e.g. "Banka|Vadesi Geçen Tahsilat")
                var lookup = {};
                response.rows.forEach(function(r){
                    var key = r.tahsilat_tipi + '|' + r.durum;
                    lookup[key] = r;
                });

                // Mapping (ORDER BY m.tahsilat_tipi, m.durum)
                // Üst 4 kart: Tahsilat Yapılmayan
                // 1,1 -> Banka - Vadesi Geçen
                var r11 = lookup['Banka|Vadesi Geçen Tahsilat'] || {toplam_adet:0, toplam_tutar:0};
                $('#vadesiGecenTutar').html(formatMoney(r11.toplam_tutar) + ' ₺');
                $('#vadesiGecenAdet').html(r11.toplam_adet + ' adet');

                // 2,1 -> Çek - Vadesi Geçen
                var r21 = lookup['Çek|Vadesi Geçen Tahsilat'] || {toplam_adet:0, toplam_tutar:0};
                $('#onayBekleyenTutar').html(formatMoney(r21.toplam_tutar) + ' ₺');
                $('#onayBekleyenAdet').html(r21.toplam_adet + ' adet');

                // 3,1 -> Kasa - Vadesi Geçen
                var r31 = lookup['Kasa|Vadesi Geçen Tahsilat'] || {toplam_adet:0, toplam_tutar:0};
                $('#netKarTutar').html(formatMoney(r31.toplam_tutar) + ' ₺');
                $('#karMarji').html(r31.toplam_adet + ' adet');

                // 4,1 -> Senet - Vadesi Geçen
                var r41 = lookup['Senet|Vadesi Geçen Tahsilat'] || {toplam_adet:0, toplam_tutar:0};
                $('#kasaBakiye').html(formatMoney(r41.toplam_tutar) + ' ₺');
                $('#kasaAciklama').html(r41.toplam_adet + ' adet');

                // Alt 4 kart: Tahsilat Yapıldı
                // 1,2 -> Banka - Tahsilat Yapıldı
                var r12 = lookup['Banka|Tahsilat Yapıldı'] || {toplam_adet:0, toplam_tutar:0};
                $('#bekleyenTahsilatTutar').html(formatMoney(r12.toplam_tutar) + ' ₺');
                $('#bekleyenTahsilatAdet').html(r12.toplam_adet + ' adet');

                // 2,2 -> Çek - Tahsilat Yapıldı
                var r22 = lookup['Çek|Tahsilat Yapıldı'] || {toplam_adet:0, toplam_tutar:0};
                $('#bekleyenOdemeTutar').html(formatMoney(r22.toplam_tutar) + ' ₺');
                $('#bekleyenOdemeAdet').html(r22.toplam_adet + ' adet');

                // 3,2 -> Kasa - Tahsilat Yapıldı
                var r32 = lookup['Kasa|Tahsilat Yapıldı'] || {toplam_adet:0, toplam_tutar:0};
                $('#kdvTutar').html(formatMoney(r32.toplam_tutar) + ' ₺');
                $('#kdvAciklama').html(r32.toplam_adet + ' adet');

                // 4,2 -> Senet - Tahsilat Yapıldı
                var r42 = lookup['Senet|Tahsilat Yapıldı'] || {toplam_adet:0, toplam_tutar:0};
                $('#bordroTutar').html(formatMoney(r42.toplam_tutar) + ' ₺');
                $('#bordroAciklama').html(r42.toplam_adet + ' adet');

            } else {
                // fallback: sıfırlama - tüm kartları sıfır değerlerle güncelle
                $('#vadesiGecenTutar').html('0 ₺');
                $('#vadesiGecenAdet').html('0 adet');

                $('#onayBekleyenTutar').html('0 ₺');
                $('#onayBekleyenAdet').html('0 adet');

                $('#netKarTutar').html('0 ₺');
                $('#karMarji').html('0 adet');

                $('#kasaBakiye').html('0 ₺');
                $('#kasaAciklama').html('0 adet');

                $('#bekleyenTahsilatTutar').html('0 ₺');
                $('#bekleyenTahsilatAdet').html('0 adet');

                $('#bekleyenOdemeTutar').html('0 ₺');
                $('#bekleyenOdemeAdet').html('0 adet');

                $('#kdvTutar').html('0 ₺');
                $('#kdvAciklama').html('0 adet');

                $('#bordroTutar').html('0 ₺');
                $('#bordroAciklama').html('0 adet');
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', status, error); // Debug için eklendi
            console.log('Response Text:', xhr.responseText); // Debug için eklendi
            // error durumunda da aynı sıfırlama
            $('#vadesiGecenTutar').html('0 ₺');
            $('#vadesiGecenAdet').html('0 adet');

            $('#onayBekleyenTutar').html('0 ₺');
            $('#onayBekleyenAdet').html('0 adet');

            $('#netKarTutar').html('0 ₺');
            $('#karMarji').html('0 adet');

            $('#kasaBakiye').html('0 ₺');
            $('#kasaAciklama').html('0 adet');

            $('#bekleyenTahsilatTutar').html('0 ₺');
            $('#bekleyenTahsilatAdet').html('0 adet');

            $('#bekleyenOdemeTutar').html('0 ₺');
            $('#bekleyenOdemeAdet').html('0 adet');

            $('#kdvTutar').html('0 ₺');
            $('#kdvAciklama').html('0 adet');

            $('#bordroTutar').html('0 ₺');
            $('#bordroAciklama').html('0 adet');
        }
    });
}

// Personel kartları için kullanıcı ID'lerini tut
var personelIdleri = [null, null, null];

// Personel bazında tahsilat özetini çeken fonksiyon
function loadPersonelTahsilatOzeti() {
    $.ajax({
        url: '<?= base_url('muhasebeapi') ?>',
        type: 'POST',
        data: {
            action: 'getPersonelTahsilatOzeti'
        },
        dataType: 'json',
        success: function(response) {
            console.log('Personel AJAX Response:', response);
            if(response.success && Array.isArray(response.rows)) {
                // İlk 3 personeli kartlara doldur
                for(let i = 0; i < 3; i++) {
                    const personel = response.rows[i] || null;
                    const kartNo = i + 1;
                    
                    if(personel) {
                        $(`#personel${kartNo}Kullanici`).html(personel.kullanici);
                        $(`#personel${kartNo}Tutar`).html(formatMoney(personel.toplam_tutar) + ' ₺');
                        $(`#personel${kartNo}Adet`).html(personel.toplam_adet + ' adet');
                        // Kullanıcı ID'sini sakla
                        personelIdleri[i] = personel.cari_olusturan_id || personel.kullanici_id;
                        // Kartı tıklanabilir yap
                        $(`#personel${kartNo}Kart`).css('opacity', '1').css('pointer-events', 'auto');
                    } else {
                        $(`#personel${kartNo}Kullanici`).html('—');
                        $(`#personel${kartNo}Tutar`).html('0 ₺');
                        $(`#personel${kartNo}Adet`).html('0 adet');
                        // Kullanıcı ID'sini temizle
                        personelIdleri[i] = null;
                        // Kartı tıklanamaz yap
                        $(`#personel${kartNo}Kart`).css('opacity', '0.5').css('pointer-events', 'none');
                    }
                }
            } else {
                // Hata durumunda tüm kartları sıfırla
                for(let i = 1; i <= 3; i++) {
                    $(`#personel${i}Kullanici`).html('—');
                    $(`#personel${i}Tutar`).html('0 ₺');
                    $(`#personel${i}Adet`).html('0 adet');
                    // Kullanıcı ID'lerini temizle
                    personelIdleri[i-1] = null;
                    // Kartları tıklanamaz yap
                    $(`#personel${i}Kart`).css('opacity', '0.5').css('pointer-events', 'none');
                }
            }
        },
        error: function(xhr, status, error) {
            console.log('Personel AJAX Error:', status, error);
            // Hata durumunda tüm kartları sıfırla
            for(let i = 1; i <= 3; i++) {
                $(`#personel${i}Kullanici`).html('—');
                $(`#personel${i}Tutar`).html('0 ₺');
                $(`#personel${i}Adet`).html('0 adet');
                // Kullanıcı ID'lerini temizle
                personelIdleri[i-1] = null;
                // Kartları tıklanamaz yap
                $(`#personel${i}Kart`).css('opacity', '0.5').css('pointer-events', 'none');
            }
        }
    });
}

// Personel tahsilat sayfasına git
function personelTahsilatGit(kartNo) {
    const personelId = personelIdleri[kartNo - 1];
    if (personelId) {
        const url = '<?= base_url('muhasebe/tahsilat-listesi') ?>?durum=1&personel=' + personelId + '&tahsilat_ayi=';
        window.location.href = url;
    } else {
        console.log('Personel ID bulunamadı:', kartNo);
    }
}

// Para formatı fonksiyonu
function formatMoney(amount) {
    if (!amount) return '0';
    return parseFloat(amount).toLocaleString('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
</script>

</body>
</html>