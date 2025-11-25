<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Changelog Yönetimi | İlekaSoft CRM</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <?php 
        $this->load->view("include/head-tags");
        // Yetkileri al
        $yetkiler = $this->session->userdata('yetkiler') ?? [];
        // Admin kontrolü (grup_id=1)
        $login_info = $this->session->userdata('login_info');
        $is_admin = isset($login_info->grup_id) && $login_info->grup_id == 1;
        
        // Sadece admin erişebilir
        if (!$is_admin) {
            redirect('home');
        }
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
        .badge-feature { background: #28a745; }
        .badge-bugfix { background: #dc3545; }
        .badge-improvement { background: #17a2b8; }
        .badge-security { background: #ffc107; color: #000; }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-aktif { background: #28a745; color: white; }
        .status-pasif { background: #dc3545; color: white; }
        .table th { border-top: none; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 20px;
        }
        /* Tablo başlık filtreleri */
        .filter-row input, .filter-row select {
            width: 100%;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .filter-row {
            background-color: #f8f9fa;
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
                            <h3 class="page-title">Changelog Yönetimi</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>
                                <li class="breadcrumb-item">Yönetici</li>
                                <li class="breadcrumb-item active">Changelog</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Sayfa Başlıkları -->

                <!-- Flash Mesajları -->
                <?php if($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <strong>Başarılı!</strong> <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>
                
                <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Hata!</strong> <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>
                
                <!-- Ana Kart -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0"><i class="fas fa-code-branch mr-2"></i>Versiyon Geçmişi Kayıtları</h4>
                                <button type="button" class="btn btn-light btn-sm" onclick="changelogModal()">
                                    <i class="fas fa-plus mr-1"></i> Yeni Kayıt Ekle
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="changelogTable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Versiyon</th>
                                                <th>Tarih</th>
                                                <th>Tip</th>
                                                <th>Modül</th>
                                                <th>Açıklama</th>
                                                <th>Yazar</th>
                                                <th>Durum</th>
                                                <th>İşlemler</th>
                                            </tr>
                                            <tr class="filter-row">
                                                <th></th>
                                                <th><input type="text" class="column-filter" placeholder="Filtrele" data-column="1"></th>
                                                <th><input type="text" class="column-filter" placeholder="Filtrele" data-column="2"></th>
                                                <th>
                                                    <select class="column-filter" data-column="3">
                                                        <option value="">Tümü</option>
                                                        <option value="feature">Feature</option>
                                                        <option value="bugfix">Bugfix</option>
                                                        <option value="improvement">Improvement</option>
                                                        <option value="security">Security</option>
                                                    </select>
                                                </th>
                                                <th><input type="text" class="column-filter" placeholder="Filtrele" data-column="4"></th>
                                                <th><input type="text" class="column-filter" placeholder="Filtrele" data-column="5"></th>
                                                <th><input type="text" class="column-filter" placeholder="Filtrele" data-column="6"></th>
                                                <th>
                                                    <select class="column-filter" data-column="7">
                                                        <option value="">Tümü</option>
                                                        <option value="Aktif">Aktif</option>
                                                        <option value="Pasif">Pasif</option>
                                                    </select>
                                                </th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- DataTables ile yüklenecek -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Ana Kart -->

            </div>
        </div>
        <!-- /Page Wrapper -->

    </div>
    <!-- /Main Wrapper -->

    <!-- Changelog Modal -->
    <div class="modal fade" id="changelogModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Yeni Changelog Kaydı</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="changelogForm">
                    <input type="hidden" id="changelog_id" name="changelog_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Versiyon Numarası <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="changelog_version" name="changelog_version" placeholder="örn: 1.3.4" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tarih <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="changelog_date" name="changelog_date" value="<?= date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Değişiklik Tipi <span class="text-danger">*</span></label>
                                    <select class="form-control" id="changelog_type" name="changelog_type" required>
                                        <option value="feature">Feature (Yeni Özellik)</option>
                                        <option value="bugfix">Bugfix (Hata Düzeltme)</option>
                                        <option value="improvement">Improvement (İyileştirme)</option>
                                        <option value="security">Security (Güvenlik)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Modül/Bölüm</label>
                                    <input type="text" class="form-control" id="changelog_module" name="changelog_module" placeholder="örn: Illegal - Tespit İşlemleri">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Kısa Açıklama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="changelog_description" name="changelog_description" placeholder="Kısa açıklama" required>
                        </div>
                        <div class="form-group">
                            <label>Detaylı Açıklama</label>
                            <textarea class="form-control" id="changelog_details" name="changelog_details" rows="3" placeholder="Detaylı açıklama (opsiyonel)"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Değiştirilen Dosya(lar)</label>
                            <input type="text" class="form-control" id="changelog_file" name="changelog_file" placeholder="örn: application/views/illegal/illegal_tespit_olustur.php">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Yazar</label>
                                    <input type="text" class="form-control" id="changelog_author" name="changelog_author" value="Batuhan Kahraman">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Durum</label>
                                    <select class="form-control" id="changelog_durum" name="changelog_durum">
                                        <option value="1">Aktif</option>
                                        <option value="0">Pasif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> İptal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Changelog Modal -->

    <!-- Footer JS -->
    <?php $this->load->view("include/footer-js"); ?>
    <!-- /Footer JS -->

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>

    <script>
        var table;

        $(document).ready(function() {
            // DataTable başlat
            table = $('#changelogTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '<?= base_url('yonetici/changelogListesi'); ?>',
                    type: 'POST',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'changelog_id' },
                    { data: 'changelog_version' },
                    { data: 'changelog_date' },
                    { 
                        data: 'changelog_type',
                        render: function(data) {
                            var badges = {
                                'feature': '<span class="badge badge-feature">Feature</span>',
                                'bugfix': '<span class="badge badge-bugfix">Bugfix</span>',
                                'improvement': '<span class="badge badge-improvement">Improvement</span>',
                                'security': '<span class="badge badge-security">Security</span>'
                            };
                            return badges[data] || data;
                        }
                    },
                    { data: 'changelog_module' },
                    { 
                        data: 'changelog_description',
                        render: function(data) {
                            return data.length > 50 ? data.substr(0, 50) + '...' : data;
                        }
                    },
                    { data: 'changelog_author' },
                    { 
                        data: 'changelog_durum',
                        render: function(data) {
                            return data == 1 
                                ? '<span class="status-badge status-aktif">Aktif</span>' 
                                : '<span class="status-badge status-pasif">Pasif</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group-custom">
                                    <button class="btn btn-sm btn-info" onclick="editChangelog(${row.changelog_id})" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteChangelog(${row.changelog_id})" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[2, 'desc'], [0, 'desc']], // Tarihe göre yeni->eski
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json'
                },
                dom: 'Blfrtip',
                buttons: []
            });

            // Kolon filtreleri
            $('.column-filter').on('keyup change', function() {
                var columnIndex = $(this).data('column');
                table.column(columnIndex).search(this.value).draw();
            });
        });

        // Modal aç (yeni kayıt)
        function changelogModal() {
            $('#changelogForm')[0].reset();
            $('#changelog_id').val('');
            $('#modalTitle').text('Yeni Changelog Kaydı');
            $('#changelog_date').val('<?= date('Y-m-d'); ?>');
            $('#changelog_author').val('Batuhan Kahraman');
            $('#changelogModal').modal('show');
        }

        // Düzenle
        function editChangelog(id) {
            $.ajax({
                url: '<?= base_url('yonetici/changelogDetay'); ?>',
                type: 'POST',
                data: { changelog_id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        $('#changelog_id').val(data.changelog_id);
                        $('#changelog_version').val(data.changelog_version);
                        $('#changelog_date').val(data.changelog_date);
                        $('#changelog_type').val(data.changelog_type);
                        $('#changelog_module').val(data.changelog_module);
                        $('#changelog_description').val(data.changelog_description);
                        $('#changelog_details').val(data.changelog_details);
                        $('#changelog_file').val(data.changelog_file);
                        $('#changelog_author').val(data.changelog_author);
                        $('#changelog_durum').val(data.changelog_durum);
                        $('#modalTitle').text('Changelog Kaydını Düzenle');
                        $('#changelogModal').modal('show');
                    } else {
                        toastr.error(response.message || 'Kayıt yüklenemedi');
                    }
                },
                error: function() {
                    toastr.error('Kayıt yüklenirken hata oluştu');
                }
            });
        }

        // Kaydet (ekle/güncelle)
        $('#changelogForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            var url = $('#changelog_id').val() 
                ? '<?= base_url('yonetici/changelogGuncelle'); ?>' 
                : '<?= base_url('yonetici/changelogEkle'); ?>';

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'İşlem başarılı');
                        $('#changelogModal').modal('hide');
                        table.ajax.reload();
                    } else {
                        toastr.error(response.message || 'İşlem başarısız');
                    }
                },
                error: function() {
                    toastr.error('İşlem sırasında hata oluştu');
                }
            });
        });

        // Sil
        function deleteChangelog(id) {
            if (!confirm('Bu kaydı silmek istediğinize emin misiniz?')) {
                return;
            }

            $.ajax({
                url: '<?= base_url('yonetici/changelogSil'); ?>',
                type: 'POST',
                data: { changelog_id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Kayıt silindi');
                        table.ajax.reload();
                    } else {
                        toastr.error(response.message || 'Silme işlemi başarısız');
                    }
                },
                error: function() {
                    toastr.error('Silme işlemi sırasında hata oluştu');
                }
            });
        }
    </script>

</body>
</html>
