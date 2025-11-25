<!DOCTYPE html>
<html lang="tr">
<head>
	<?php $this->load->view("include/head-tags"); ?>
	<title>SMS Yönetimi | İlekaSoft CRM</title>
	<link rel="icon" type="image/x-icon" href="<?= base_url('assets/favicon.ico'); ?>" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/css/dataTables.bootstrap4.min.css"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="fix-header fix-sidebar card-no-border">
	<div id="main-wrapper">
		<?php $this->load->view("include/header"); ?>
		<?php $this->load->view("include/sidebar"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-5 align-self-center">
						<h3 class="text-primary">📱 SMS Yönetimi</h3>
					</div>
					<div class="col-md-7 align-self-center">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Anasayfa</a></li>
							<li class="breadcrumb-item"><a href="<?php echo base_url('yonetici/smsYonetimi'); ?>">SMS Yönetimi</a></li>
						</ol>
					</div>
				</div>

    <!-- Flash Mesajları -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>✓ Başarılı!</strong> <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>✗ Hata!</strong> <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- İstatistikler -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid #667eea;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Toplam SMS</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['toplam']); ?></h2>
                        </div>
                        <div class="text-primary" style="font-size: 48px;">📊</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid #28a745;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Başarılı</h6>
                            <h2 class="mb-0 text-success"><?php echo number_format($stats['basarili']); ?></h2>
                        </div>
                        <div class="text-success" style="font-size: 48px;">✓</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid #dc3545;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Başarısız</h6>
                            <h2 class="mb-0 text-danger"><?php echo number_format($stats['basarisiz']); ?></h2>
                        </div>
                        <div class="text-danger" style="font-size: 48px;">✗</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid #ffc107;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Bugün</h6>
                            <h2 class="mb-0 text-warning"><?php echo number_format($stats['bugun']); ?></h2>
                        </div>
                        <div class="text-warning" style="font-size: 48px;">📅</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Şablonu Düzenleme -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">📝 SMS Şablonu Düzenleme</h4>
                    <p class="text-muted">Vade hatırlatma SMS şablonunu buradan düzenleyebilirsiniz.</p>
                    
                    <form method="post" action="<?php echo base_url('yonetici/smsSablonuGuncelle'); ?>">
                        <div class="form-group">
                            <label><strong>SMS Şablonu</strong></label>
                            <textarea class="form-control" name="sms_sablonu" rows="8" required><?php echo htmlspecialchars($sms_sablonu); ?></textarea>
                            <small class="form-text text-muted">
                                <strong>Kullanılabilir Değişkenler:</strong><br>
                                <span class="badge badge-info">[ODEME_TURU]</span> - Ödeme türü (Çek/Senet)<br>
                                <span class="badge badge-info">[VADE_TARIHI]</span> - Vade tarihi (25 Ocak 2025 formatında)
                            </small>
                        </div>
                        <div class="form-group">
                            <label><strong>Karakter Sayısı:</strong> <span id="charCount">0</span> / 160 (1 SMS)</label>
                            <div class="progress" style="height: 5px;">
                                <div id="charProgress" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Şablonu Güncelle
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtreleme ve SMS Logları -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">📋 SMS Gönderim Logları</h4>
                    
                    <!-- Filtre Formu -->
                    <form method="get" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Durum</label>
                                    <select name="durum" class="form-control">
                                        <option value="">Tümü</option>
                                        <option value="basarili" <?php echo ($this->input->get('durum') == 'basarili') ? 'selected' : ''; ?>>Başarılı</option>
                                        <option value="basarisiz" <?php echo ($this->input->get('durum') == 'basarisiz') ? 'selected' : ''; ?>>Başarısız</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tip</label>
                                    <select name="tip" class="form-control">
                                        <option value="">Tümü</option>
                                        <option value="vade_hatirlatma" <?php echo ($this->input->get('tip') == 'vade_hatirlatma') ? 'selected' : ''; ?>>Vade Hatırlatma</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Başlangıç</label>
                                    <input type="date" name="tarih_baslangic" class="form-control" value="<?php echo $this->input->get('tarih_baslangic'); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Bitiş</label>
                                    <input type="date" name="tarih_bitis" class="form-control" value="<?php echo $this->input->get('tarih_bitis'); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-info btn-block">
                                        <i class="fa fa-filter"></i> Filtrele
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tablo -->
                    <div class="table-responsive">
                        <table id="smsLogsTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Müşteri</th>
                                    <th>Telefon</th>
                                    <th>Tip</th>
                                    <th>Durum</th>
                                    <th>Gönderim Tarihi</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($sms_logs)): ?>
                                    <?php foreach($sms_logs as $log): ?>
                                        <tr>
                                            <td><?php echo $log->id; ?></td>
                                            <td><?php echo $log->cari_ad . ' ' . $log->cari_soyad; ?></td>
                                            <td><?php echo $log->telefon; ?></td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?php echo str_replace('_', ' ', ucfirst($log->tip)); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($log->durum == 'basarili'): ?>
                                                    <span class="badge badge-success">✓ Başarılı</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">✗ Başarısız</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($log->gonderim_tarihi)); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="showLogDetail(<?php echo $log->id; ?>)">
                                                    <i class="fa fa-eye"></i> Detay
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Kayıt bulunamadı</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Detay Modal -->
<div class="modal fade" id="logDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📋 SMS Log Detayı</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="logDetailContent">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Karakter sayacı
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="sms_sablonu"]');
    const charCount = document.getElementById('charCount');
    const charProgress = document.getElementById('charProgress');
    
    function updateCharCount() {
        const length = textarea.value.length;
        charCount.textContent = length;
        
        const percentage = (length / 160) * 100;
        charProgress.style.width = Math.min(percentage, 100) + '%';
        
        if (length > 160) {
            charProgress.classList.remove('bg-success', 'bg-warning');
            charProgress.classList.add('bg-danger');
        } else if (length > 140) {
            charProgress.classList.remove('bg-success', 'bg-danger');
            charProgress.classList.add('bg-warning');
        } else {
            charProgress.classList.remove('bg-warning', 'bg-danger');
            charProgress.classList.add('bg-success');
        }
    }
    
    textarea.addEventListener('input', updateCharCount);
    updateCharCount();
    
    // DataTable
    $('#smsLogsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json"
        },
        "order": [[0, "desc"]],
        "pageLength": 25
    });
});

// Log detayını göster
function showLogDetail(id) {
    $('#logDetailModal').modal('show');
    $('#logDetailContent').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');
    
    $.ajax({
        url: '<?php echo base_url("yonetici/smsLogDetay/"); ?>' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const log = response.data;
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> ${log.id}</p>
                            <p><strong>Müşteri ID:</strong> ${log.cari_id}</p>
                            <p><strong>Telefon:</strong> ${log.telefon}</p>
                            <p><strong>Tip:</strong> <span class="badge badge-secondary">${log.tip}</span></p>
                            <p><strong>Durum:</strong> <span class="badge badge-${log.durum == 'basarili' ? 'success' : 'danger'}">${log.durum}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Ödeme Türü:</strong> ${log.odeme_turu || '-'}</p>
                            <p><strong>Kayıt ID:</strong> ${log.kayit_id || '-'}</p>
                            <p><strong>Tahsilat Tipi:</strong> ${log.tahsilat_tipi || '-'}</p>
                            <p><strong>Gönderim Tarihi:</strong> ${log.gonderim_tarihi}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6>📱 SMS Mesajı:</h6>
                            <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px;">${log.mesaj}</pre>
                        </div>
                    </div>
                `;
                
                if (log.hata_mesaji) {
                    html += `
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-danger">❌ Hata Mesajı:</h6>
                                <pre style="background: #ffebee; padding: 15px; border-radius: 5px; color: #c62828;">${log.hata_mesaji}</pre>
                            </div>
                        </div>
                    `;
                }
                
                if (log.api_response) {
                    html += `
                        <div class="row">
                            <div class="col-md-12">
                                <h6>🔧 API Response:</h6>
                                <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 200px; overflow-y: auto;">${log.api_response}</pre>
                            </div>
                        </div>
                    `;
                }
                
                $('#logDetailContent').html(html);
            } else {
                $('#logDetailContent').html('<div class="alert alert-danger">Log detayı yüklenemedi!</div>');
            }
        },
        error: function() {
            $('#logDetailContent').html('<div class="alert alert-danger">Bir hata oluştu!</div>');
        }
    });
}
</script>

			</div>
			<!-- Container-fluid -->
		</div>
		<!-- Page-wrapper -->
	</div>
	<!-- Main-wrapper -->
	<?php $this->load->view("include/footer-js"); ?>
</body>
</html>
