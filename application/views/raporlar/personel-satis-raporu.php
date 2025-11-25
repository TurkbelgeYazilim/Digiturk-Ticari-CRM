<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Personel Satış Raporu | İlekaSoft CRM</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <?php $this->load->view("include/head-tags"); ?>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .table th { 
            border-top: none; 
            white-space: nowrap;
            font-size: 13px;
        }
        .table td {
            font-size: 13px;
        }
        .select2-container {
            width: 100% !important;
        }
        .stat-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .avatar-lg {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-primary-light {
            background-color: rgba(102, 126, 234, 0.1);
        }
        .bg-success-light {
            background-color: rgba(40, 167, 69, 0.1);
        }
        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.1);
        }
        .stat-card h3 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }
        .stat-card h6 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
                            <h3 class="page-title">👤 Personel Satış Raporu</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>
                                <li class="breadcrumb-item">Raporlar</li>
                                <li class="breadcrumb-item active">Personel Satış Raporu</li>
                            </ul>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-success" id="exportExcelBtn">
                                <i class="fa fa-file-excel-o"></i> Excel Export
                            </button>
                        </div>
                    </div>
                </div>
                <!-- /Sayfa Başlıkları -->

                <!-- Filtre Kartı -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-filter"></i> Filtreler</h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Başlangıç Tarihi</label>
                                        <input type="date" class="form-control" name="baslangic_tarih" id="baslangic_tarih">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Bitiş Tarihi</label>
                                        <input type="date" class="form-control" name="bitis_tarih" id="bitis_tarih">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Personel</label>
                                        <select class="form-control select2" name="personel_id" id="personel_id">
                                            <option value="">Tümü</option>
                                            <?php foreach($personel_listesi as $personel): ?>
                                                <option value="<?= $personel->kullanici_id ?>">
                                                    <?= $personel->kullanici_ad . ' ' . $personel->kullanici_soyad ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-block" id="filterBtn">
                                                <i class="fa fa-search"></i> Filtrele
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button type="button" class="btn btn-secondary" id="clearBtn">
                                        <i class="fa fa-times"></i> Temizle
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /Filtre Kartı -->

                <!-- İstatistik Kartları -->
                <div class="row mb-3">
                    <div class="col-xl-4 col-sm-6 col-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Toplam Satış Adedi</h6>
                                        <h3 class="mb-0" id="toplamAdet">0</h3>
                                    </div>
                                    <div class="avatar avatar-lg bg-primary-light rounded">
                                        <i class="fa fa-shopping-cart fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-6 col-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Toplam Satış Tutarı</h6>
                                        <h3 class="mb-0" id="toplamTutar">0 ₺</h3>
                                    </div>
                                    <div class="avatar avatar-lg bg-success-light rounded">
                                        <i class="fa fa-money fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-6 col-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Ortalama Satış</h6>
                                        <h3 class="mb-0" id="ortalamaTutar">0 ₺</h3>
                                    </div>
                                    <div class="avatar avatar-lg bg-warning-light rounded">
                                        <i class="fa fa-bar-chart fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /İstatistik Kartları -->

                <!-- Rapor Tablosu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-table"></i> Personel Bazlı Satış Özet Raporu</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>Personel</th>
                                        <th>Sözleşme Hizmeti</th>
                                        <th>Adet</th>
                                        <th>Toplam Tutar</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables ile doldurulacak -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /Rapor Tablosu -->

            </div>
        </div>
        <!-- /Page Wrapper -->

    </div>
    <!-- /Main Wrapper -->

    <!-- Detay Modal -->
    <div class="modal fade" id="detayModal" tabindex="-1" role="dialog" aria-labelledby="detayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="detayModalLabel">📊 Detaylı Muhasebe Raporu</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="span">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="detayIframe" style="width:100%; height:70vh; border:none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Detay Modal -->

    <?php $this->load->view("include/footer-js"); ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {
        // Select2 başlat
        $('.select2').select2({
            theme: 'bootstrap'
        });

        // İstatistik kartlarını güncelle
        function updateStats(data) {
            var toplamAdet = 0;
            var toplamTutar = 0;
            
            data.forEach(function(row) {
                toplamAdet += parseInt(row.adet) || 0;
                var tutar = row.toplam_tutar_raw || 0;
                toplamTutar += parseFloat(tutar) || 0;
            });
            
            var ortalama = toplamAdet > 0 ? toplamTutar / toplamAdet : 0;
            
            // Kartları güncelle
            $('#toplamAdet').text(toplamAdet.toLocaleString('tr-TR'));
            $('#toplamTutar').text(toplamTutar.toLocaleString('tr-TR', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + ' ₺');
            $('#ortalamaTutar').text(ortalama.toLocaleString('tr-TR', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + ' ₺');
        }

        // DataTable başlat
        var table = $('#dataTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= base_url("raporlar/personel_satis_raporu_ajax") ?>',
                type: 'POST',
                data: function(d) {
                    d.baslangic_tarih = $('#baslangic_tarih').val();
                    d.bitis_tarih = $('#bitis_tarih').val();
                    d.personel_id = $('#personel_id').val();
                },
                dataSrc: function(json) {
                    // İstatistik kartlarını güncelle
                    updateStats(json.data);
                    return json.data;
                }
            },
            columns: [
                { data: 'personel' },
                { data: 'sozlesme_hizmeti' },
                { data: 'adet' },
                { data: 'toplam_tutar' },
                { data: 'detay_btn', orderable: false, searchable: false }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json'
            },
            pageLength: 25,
            order: [[0, 'asc']]
        });

        // Filtrele butonu
        $('#filterBtn').on('click', function() {
            table.ajax.reload();
        });

        // Temizle butonu
        $('#clearBtn').on('click', function() {
            $('#filterForm')[0].reset();
            $('.select2').val(null).trigger('change');
            table.ajax.reload();
        });

        // Detay butonu tıklanınca
        $(document).on('click', '.detay-goster', function() {
            var personelId = $(this).data('personel-id');
            var stokId = $(this).data('stok-id');
            
            // Detaylı rapora yönlendir (filtrelerle)
            var url = '<?= base_url("raporlar/detayli_muhasebe_raporu") ?>';
            url += '?personel_id=' + personelId;
            url += '&stok_id=' + stokId;
            
            if ($('#baslangic_tarih').val()) {
                url += '&baslangic_tarih=' + $('#baslangic_tarih').val();
            }
            if ($('#bitis_tarih').val()) {
                url += '&bitis_tarih=' + $('#bitis_tarih').val();
            }
            
            // Modal içinde göster
            $('#detayIframe').attr('src', url);
            $('#detayModal').modal('show');
        });

        // Excel Export
        $('#exportExcelBtn').on('click', function() {
            var url = '<?= base_url("raporlar/personel_satis_raporu_excel") ?>';
            url += '?baslangic_tarih=' + $('#baslangic_tarih').val();
            url += '&bitis_tarih=' + $('#bitis_tarih').val();
            url += '&personel_id=' + $('#personel_id').val();
            
            window.location.href = url;
        });
    });
    </script>
</body>
</html>
