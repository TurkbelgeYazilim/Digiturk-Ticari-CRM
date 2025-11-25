<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Excel ile İllegal Tespit Yükleme | İlekaSoft CRM</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <?php 
        $this->load->view("include/head-tags");
    ?>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    
    <style>
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .upload-area {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        .upload-area:hover {
            background: #e9ecef;
            border-color: #764ba2;
        }
        .upload-icon {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 20px;
        }
        .preview-table {
            display: none;
            margin-top: 30px;
        }
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        .status-warning {
            color: #ffc107;
            font-weight: bold;
        }
        .error-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .step-card {
            margin-bottom: 20px;
        }
        .step-number {
            background: #667eea;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
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
                            <h3 class="page-title">Excel ile İllegal Tespit Yükleme</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Anasayfa</a></li>
                                <li class="breadcrumb-item"><a href="<?= base_url('illegal/illegal-listele'); ?>">İllegal Tespit</a></li>
                                <li class="breadcrumb-item active">Excel Yükleme</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Sayfa Başlıkları -->

                <!-- Yönlendirme Kartları -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card step-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <span class="step-number">1</span>
                                    <div>
                                        <h6 class="mb-1">Excel Şablonunu İndirin</h6>
                                        <p class="text-muted mb-0">Örnek dosya formatını kullanın</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card step-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <span class="step-number">2</span>
                                    <div>
                                        <h6 class="mb-1">Excel Dosyasını Doldurun</h6>
                                        <p class="text-muted mb-0">Tüm zorunlu alanları eksiksiz doldurun</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card step-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <span class="step-number">3</span>
                                    <div>
                                        <h6 class="mb-1">Dosyayı Yükleyin</h6>
                                        <p class="text-muted mb-0">Kontrol edin ve kaydedin</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Yönlendirme Kartları -->

                <!-- Excel Yükleme Kartı -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-file-excel-o"></i> Excel Dosyası Yükleme
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Şablon İndirme -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> 
                                    <strong>Dikkat:</strong> Excel dosyanızı yüklemeden önce örnek şablonu indirip formatı kontrol etmeniz önerilir.
                                    <div class="mt-2">
                                        <a href="<?= base_url('illegal/excel_sablon_indir'); ?>" class="btn btn-sm btn-primary">
                                            <i class="fa fa-download"></i> Excel Şablonunu İndir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Alanı -->
                        <form id="excelUploadForm" enctype="multipart/form-data">
                            <div class="upload-area" id="uploadArea">
                                <div class="upload-icon">
                                    <i class="fa fa-cloud-upload"></i>
                                </div>
                                <h5>Excel Dosyasını Sürükleyip Bırakın</h5>
                                <p class="text-muted">veya</p>
                                <input type="file" id="excelFile" name="excel_file" accept=".xlsx,.xls" style="display:none;">
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('excelFile').click();">
                                    <i class="fa fa-folder-open"></i> Dosya Seç
                                </button>
                                <p class="text-muted mt-3 mb-0">
                                    <small>Desteklenen formatlar: .xlsx, .xls (Maksimum: 10MB)</small>
                                </p>
                            </div>
                            
                            <div id="fileInfo" class="mt-3" style="display:none;">
                                <div class="alert alert-success">
                                    <i class="fa fa-check-circle"></i> 
                                    <strong id="fileName"></strong> dosyası seçildi.
                                    <button type="button" class="btn btn-sm btn-danger float-right" onclick="clearFile()">
                                        <i class="fa fa-times"></i> Temizle
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <button type="button" class="btn btn-success btn-lg" id="btnPreview" onclick="previewExcel()" disabled>
                                    <i class="fa fa-search"></i> Önizleme ve Kontrol Et
                                </button>
                            </div>
                        </form>

                        <!-- Önizleme Tablosu -->
                        <div id="previewSection" class="preview-table">
                            <hr class="my-4">
                            <h5 class="mb-3">
                                <i class="fa fa-table"></i> Veri Önizleme ve Doğrulama
                            </h5>

                            <!-- Özet Bilgiler -->
                            <div class="row mb-4" id="summaryInfo">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3 id="totalCount">0</h3>
                                            <p class="mb-0">Toplam Kayıt</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3 id="validCount">0</h3>
                                            <p class="mb-0">Geçerli Kayıt</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <h3 id="errorCount">0</h3>
                                            <p class="mb-0">Hatalı Kayıt</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h3 id="warningCount">0</h3>
                                            <p class="mb-0">Uyarı</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hata Listesi -->
                            <div id="errorSection" class="alert alert-danger error-list" style="display:none;">
                                <h6><i class="fa fa-exclamation-triangle"></i> Hatalar</h6>
                                <ul id="errorList"></ul>
                            </div>

                            <!-- Veri Tablosu -->
                            <div class="table-responsive">
                                <table id="previewTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Satır</th>
                                            <th>Durum</th>
                                            <th>Cari İşletme Adı</th>
                                            <th>Telefon</th>
                                            <th>Ülke</th>
                                            <th>İl</th>
                                            <th>İlçe</th>
                                            <th>Tespit Tarihi</th>
                                            <th>Tespit Saati</th>
                                            <th>Takım</th>
                                            <th>Rakip Takım</th>
                                            <th>Personel ID</th>
                                            <th>Hatalar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewTableBody">
                                    </tbody>
                                </table>
                            </div>

                            <!-- Kaydet Butonu -->
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-success btn-lg" id="btnSave" onclick="saveExcelData()" disabled>
                                    <i class="fa fa-save"></i> Geçerli Kayıtları Kaydet
                                </button>
                                <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="resetForm()">
                                    <i class="fa fa-refresh"></i> Yeni Yükleme
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- /Excel Yükleme Kartı -->

            </div>
        </div>
        <!-- /Page Wrapper -->
    </div>
    <!-- /Main Wrapper -->

    <?php $this->load->view("include/footer-js"); ?>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

    <script>
        let previewData = [];
        let dataTable = null;

        // Dosya seçimi
        $('#excelFile').on('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                const fileSize = file.size / 1024 / 1024; // MB cinsinden
                
                if (fileSize > 10) {
                    Swal.fire('Hata', 'Dosya boyutu 10MB\'dan büyük olamaz!', 'error');
                    this.value = '';
                    return;
                }
                
                $('#fileName').text(file.name);
                $('#fileInfo').show();
                $('#btnPreview').prop('disabled', false);
                $('#uploadArea').css('border-color', '#28a745');
            }
        });

        // Drag & Drop
        const uploadArea = document.getElementById('uploadArea');
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#764ba2';
            uploadArea.style.background = '#e9ecef';
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '#667eea';
            uploadArea.style.background = '#f8f9fa';
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#667eea';
            uploadArea.style.background = '#f8f9fa';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('excelFile').files = files;
                $('#excelFile').trigger('change');
            }
        });

        // Dosya temizle
        function clearFile() {
            document.getElementById('excelFile').value = '';
            $('#fileInfo').hide();
            $('#btnPreview').prop('disabled', true);
            $('#uploadArea').css('border-color', '#667eea');
            $('#previewSection').hide();
        }

        // Excel önizleme
        function previewExcel() {
            const fileInput = document.getElementById('excelFile');
            if (!fileInput.files.length) {
                Swal.fire('Uyarı', 'Lütfen bir Excel dosyası seçin!', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('excel_file', fileInput.files[0]);

            // Loading göster
            Swal.fire({
                title: 'Excel dosyası kontrol ediliyor...',
                text: 'Lütfen bekleyin',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '<?= base_url("illegal/excel_kontrol_et"); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    
                    if (response.status === 'success') {
                        previewData = response.data;
                        displayPreview(response);
                    } else {
                        Swal.fire('Hata', response.message || 'Excel dosyası işlenirken hata oluştu!', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.error('AJAX Error:', error);
                    Swal.fire('Hata', 'Excel dosyası yüklenirken bir hata oluştu!', 'error');
                }
            });
        }

        // Önizleme göster
        function displayPreview(response) {
            const data = response.data;
            const summary = response.summary;

            // Özet bilgileri güncelle
            $('#totalCount').text(summary.total);
            $('#validCount').text(summary.valid);
            $('#errorCount').text(summary.errors);
            $('#warningCount').text(summary.warnings);

            // Hataları göster
            if (summary.errors > 0) {
                let errorHtml = '';
                data.forEach(row => {
                    if (row.errors && row.errors.length > 0) {
                        errorHtml += `<li><strong>Satır ${row.row_number}:</strong> ${row.errors.join(', ')}</li>`;
                    }
                });
                $('#errorList').html(errorHtml);
                $('#errorSection').show();
                $('#btnSave').prop('disabled', true);
            } else {
                $('#errorSection').hide();
                $('#btnSave').prop('disabled', false);
            }

            // Tabloyu oluştur
            if (dataTable) {
                dataTable.destroy();
            }

            let tableHtml = '';
            data.forEach(row => {
                const statusClass = row.is_valid ? 'status-ok' : 'status-error';
                const statusIcon = row.is_valid ? '<i class="fa fa-check-circle"></i>' : '<i class="fa fa-times-circle"></i>';
                const statusText = row.is_valid ? 'Geçerli' : 'Hatalı';
                const errorText = row.errors ? row.errors.join('<br>') : '-';

                tableHtml += `
                    <tr>
                        <td>${row.row_number}</td>
                        <td class="${statusClass}">${statusIcon} ${statusText}</td>
                        <td>${row.cari_isletme_adi || ''}</td>
                        <td>${row.cari_telefon || ''}</td>
                        <td>${row.cari_ulke || ''}</td>
                        <td>${row.cari_il || ''}</td>
                        <td>${row.cari_ilce || ''}</td>
                        <td>${row.tespit_tarih || ''}</td>
                        <td>${row.tespit_saat || ''}</td>
                        <td>${row.takim || ''}</td>
                        <td>${row.rakip_takim || ''}</td>
                        <td>${row.personel_id || ''}</td>
                        <td class="status-error">${errorText}</td>
                    </tr>
                `;
            });

            $('#previewTableBody').html(tableHtml);
            $('#previewSection').show();

            // DataTable initialize
            dataTable = $('#previewTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json"
                },
                "pageLength": 25,
                "order": [[0, 'asc']]
            });

            // Sayfayı önizleme bölümüne kaydır
            $('html, body').animate({
                scrollTop: $('#previewSection').offset().top - 100
            }, 500);
        }

        // Verileri kaydet
        function saveExcelData() {
            if (!previewData || previewData.length === 0) {
                Swal.fire('Uyarı', 'Kaydedilecek veri bulunamadı!', 'warning');
                return;
            }

            // Sadece geçerli kayıtları filtrele
            const validData = previewData.filter(row => row.is_valid);
            
            if (validData.length === 0) {
                Swal.fire('Uyarı', 'Kaydedilecek geçerli veri bulunamadı!', 'warning');
                return;
            }

            Swal.fire({
                title: 'Emin misiniz?',
                text: `${validData.length} adet geçerli kayıt sisteme eklenecek.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Evet, Kaydet',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Loading göster
                    Swal.fire({
                        title: 'Kayıtlar ekleniyor...',
                        text: 'Lütfen bekleyin',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '<?= base_url("illegal/excel_kaydet"); ?>',
                        type: 'POST',
                        data: JSON.stringify({ data: validData }),
                        contentType: 'application/json',
                        success: function(response) {
                            Swal.close();
                            
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Başarılı!',
                                    text: response.message,
                                    showConfirmButton: true
                                }).then(() => {
                                    window.location.href = '<?= base_url("illegal/illegal-listele"); ?>';
                                });
                            } else {
                                Swal.fire('Hata', response.message || 'Kayıt sırasında hata oluştu!', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.close();
                            console.error('AJAX Error:', error);
                            Swal.fire('Hata', 'Kayıt sırasında bir hata oluştu!', 'error');
                        }
                    });
                }
            });
        }

        // Formu sıfırla
        function resetForm() {
            clearFile();
            $('#previewSection').hide();
            previewData = [];
            if (dataTable) {
                dataTable.destroy();
                dataTable = null;
            }
            $('#previewTableBody').html('');
        }
    </script>
</body>
</html>
https://crm.ilekasoft.com/illegal/illegal-tespit-excel-yukle