<!DOCTYPE html>
<html lang="tr">
<head>
    <?php $this->load->view("include/head-tags"); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">
    <style>
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
        .table th {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        .badge {
            font-size: 0.875em;
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
                        <h3 class="page-title">Aktivasyon Listesi</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('aktivasyon'); ?>">Aktivasyon</a></li>
                            <li class="breadcrumb-item active">Liste</li>
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

            <!-- Flash Messages -->
            <?php if($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title d-inline-block">Aktivasyon Listesi</h4>
                            <div class="float-right">
                                <button type="button" class="btn btn-success mr-2" onclick="exportToExcel()">
                                    <i class="fa fa-file-excel"></i> Excel'e Aktar
                                </button>
                                <?php if(grup_modul_yetkisi_var(901)): ?>
                                    <a href="<?= base_url('aktivasyon/olustur') ?>" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> Yeni Aktivasyon
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="aktivasyonTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Müşteri</th>
                                            <th>Hizmet</th>
                                            <th>Üye No</th>
                                            <th>Kutu No</th>
                                            <th>Kart No</th>
                                            <th>Kampanya Kodu</th>
                                            <th>Aktivasyon Tarihi</th>
                                            <th>Durum</th>
                                            <th>Açıklama</th>
                                            <th>Oluşturan</th>
                                            <th>Oluşturma Tarihi</th>
                                            <th>Güncelleme Tarihi</th>
                                            <?php if(grup_modul_yetkisi_var(901)): ?>
                                            <th>İşlemler</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(isset($aktivasyonlar) && !empty($aktivasyonlar)): ?>
                                            <?php foreach($aktivasyonlar as $aktivasyon): ?>
                                            <tr>
                                                <td><?= $aktivasyon->aktivasyon_id ?></td>
                                                <td><?= $aktivasyon->cari_ad ?></td>
                                                <td>
                                                    <?php 
                                                        $hizmet_adi = '';
                                                        $hizmet_renk = '';
                                                        switch($aktivasyon->aktivasyon_stok) {
                                                            case 1:
                                                                $hizmet_adi = 'DIGITURK';
                                                                $hizmet_renk = '#007bff'; // Mavi
                                                                break;
                                                            case 2:
                                                                $hizmet_adi = 'S SPORT';
                                                                $hizmet_renk = '#28a745'; // Yeşil
                                                                break;
                                                            case 3:
                                                                $hizmet_adi = 'TABII';
                                                                $hizmet_renk = '#fd7e14'; // Turuncu
                                                                break;
                                                            case 4:
                                                                $hizmet_adi = 'EXXEN';
                                                                $hizmet_renk = '#6f42c1'; // Mor
                                                                break;
                                                            default:
                                                                $hizmet_adi = 'Bilinmiyor';
                                                                $hizmet_renk = '#6c757d'; // Gri
                                                        }
                                                    ?>
                                                    <span class="badge" style="background-color: <?= $hizmet_renk ?>; color: white;">
                                                        <?= $hizmet_adi ?>
                                                    </span>
                                                </td>
                                                <td><?= $aktivasyon->aktivasyon_uye_no ?: '-' ?></td>
                                                <td><?= $aktivasyon->aktivasyon_kutu_no ?: '-' ?></td>
                                                <td><?= $aktivasyon->aktivasyon_kart_no ?: '-' ?></td>
                                                <td><?= $aktivasyon->aktivasyon_kampanya_kodu ?: '-' ?></td>
                                                <td>
                                                    <?= $aktivasyon->aktivasyon_tarihi ? 
                                                        date('d.m.Y H:i', strtotime($aktivasyon->aktivasyon_tarihi)) : '-' 
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background-color: <?= $aktivasyon->aktivasyon_durum_renk ?>; color: white;">
                                                        <?= $aktivasyon->aktivasyon_durum_adi ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($aktivasyon->aktivasyon_aciklama): ?>
                                                        <span title="<?= htmlspecialchars($aktivasyon->aktivasyon_aciklama) ?>">
                                                            <?= strlen($aktivasyon->aktivasyon_aciklama) > 50 ? 
                                                                substr($aktivasyon->aktivasyon_aciklama, 0, 50) . '...' : 
                                                                $aktivasyon->aktivasyon_aciklama 
                                                            ?>
                                                        </span>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $aktivasyon->kullanici_ad ?: '-' ?></td>
                                                <td><?= date('d.m.Y H:i', strtotime($aktivasyon->olusturma_tarihi)) ?></td>
                                                <td><?= date('d.m.Y H:i', strtotime($aktivasyon->guncelleme_tarihi)) ?></td>
                                                <?php if(grup_modul_yetkisi_var(901)): ?>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="<?= base_url('aktivasyon/olustur/'.$aktivasyon->aktivasyon_id) ?>" 
                                                           class="btn btn-sm btn-warning" title="Düzenle">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <a href="<?= base_url('aktivasyon/sil/'.$aktivasyon->aktivasyon_id) ?>" 
                                                           class="btn btn-sm btn-danger" title="Sil"
                                                           onclick="return confirm('Bu aktivasyonu silmek istediğinizden emin misiniz?')">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                                <?php endif; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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

<!-- SheetJS için Excel Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<?php $this->load->view("include/footer-js"); ?>

<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $('#aktivasyonTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/tr.json"
        },
        "order": [[ 11, "desc" ]], // Oluşturma tarihine göre sırala (sütun indeksi 11 oldu)
        "pageLength": 25,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": <?= grup_modul_yetkisi_var(901) ? "[-1]" : "[]" ?> } // İşlemler sütunu sıralanamaz
        ]
    });
});

// Excel'e aktarma fonksiyonu
function exportToExcel() {
    var table = $('#aktivasyonTable').DataTable();
    var data = [];
    
    // Başlık satırı
    var headers = [];
    $('#aktivasyonTable thead th').each(function() {
        headers.push($(this).text());
    });
    data.push(headers);
    
    // Veri satırları - DataTable'dan tüm veriyi al (filtrelenmiş veya sıralanmış haliyle)
    table.rows({ search: 'applied' }).every(function() {
        var rowData = [];
        $(this.node()).find('td').each(function(index) {
            var cellText = $(this).text().trim();
            
            // Badge içindeki metni temizle
            if($(this).find('.badge').length > 0) {
                cellText = $(this).find('.badge').text().trim();
            }
            
            // Para birimi sembollerini temizle
            cellText = cellText.replace('₺', '').replace('TL', '').trim();
            
            rowData.push(cellText);
        });
        data.push(rowData);
    });
    
    // Excel dosyası oluştur
    var wb = XLSX.utils.book_new();
    var ws = XLSX.utils.aoa_to_sheet(data);
    
    // Kolon genişliklerini ayarla
    var wscols = [];
    for(var i = 0; i < headers.length; i++) {
        wscols.push({wch: 15});
    }
    ws['!cols'] = wscols;
    
    XLSX.utils.book_append_sheet(wb, ws, "Aktivasyon Listesi");
    
    // Dosyayı indir
    var tarih = new Date().toISOString().slice(0,10);
    XLSX.writeFile(wb, "aktivasyon_listesi_" + tarih + ".xlsx");
}
</script>
</body>
</html>
