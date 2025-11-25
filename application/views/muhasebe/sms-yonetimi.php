<!DOCTYPE html>
<html lang="tr">
<head>
	<?php $this->load->view("include/head-tags"); ?>
	<title>SMS Yönetimi | İlekaSoft CRM</title>
	<link rel="icon" type="image/x-icon" href="<?= base_url('assets/favicon.ico'); ?>" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/css/dataTables.bootstrap4.min.css"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
<div class="main-wrapper">
	<?php $this->load->view("include/header"); ?>
	<?php $this->load->view("include/sidebar"); ?>
		<div class="page-wrapper">
			<div class="content container-fluid">
				<!-- Page Header -->
				<div class="page-header">
					<div class="row">
						<div class="col-sm-10">
							<h3 class="page-title">📱 SMS Yönetimi</h3>
							<ul class="breadcrumb">
								<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Anasayfa</a></li>
								<li class="breadcrumb-item">Muhasebe</li>
								<li class="breadcrumb-item active">SMS Yönetimi</li>
							</ul>
						</div>
						<div class="d-flex justify-content-end text-align-center col-sm-2">
							<a class="btn btn-outline-light btn-sm" href="javascript:history.back()">
								<i class="fa fa-history"></i> <br>Önceki Sayfa
							</a>
						</div>
					</div>
				</div>
				<!-- /Page Header -->

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
                    
                    <form method="post" action="<?php echo base_url('muhasebe/smsSablonuGuncelle'); ?>">
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

    <!-- SMS Test ve Önizleme -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">🧪 SMS Test ve Önizleme</h4>
                    <p class="text-muted">Vade tarihi yaklaşan müşteri seçerek SMS şablonunu önizleyebilir ve test SMS gönderebilirsiniz.</p>
                    
                    <div id="testSmsArea">
                        <div class="alert alert-info">
                            <i class="fa fa-spinner fa-spin"></i> Vade tarihi yaklaşan müşteriler yükleniyor...
                        </div>
                    </div>
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
                                            <td>
                                                <a href="<?php echo base_url('cari/cari-karti-duzenle/' . $log->cari_id); ?>" target="_blank" class="text-primary">
                                                    <?php echo $log->cari_ad . ' ' . $log->cari_soyad; ?>
                                                </a>
                                            </td>
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
        "pageLength": 25,
        "columnDefs": [
            { "orderable": false, "targets": [6] } // İşlem sütunu sıralanamaz
        ]
    });
    
    // SMS Test verilerini yükle
    loadTestCustomers();
});

// Clipboard kopyalama
function copyToClipboard() {
    const cronUrl = document.getElementById('cronUrl');
    cronUrl.select();
    cronUrl.setSelectionRange(0, 99999); // Mobil için
    
    try {
        document.execCommand('copy');
        alert('✓ URL kopyalandı! Plesk Cron Job ayarlarına yapıştırabilirsiniz.');
    } catch (err) {
        alert('Kopyalama başarısız. URL\'i manuel olarak seçip kopyalayın.');
    }
}

// Test müşterilerini yükle
function loadTestCustomers() {
    $.ajax({
        url: '<?php echo base_url("muhasebe/getTestCustomers"); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                renderTestArea(response.data);
            } else {
                $('#testSmsArea').html(`
                    <div class="alert alert-warning">
                        <strong>⚠️ Uyarı:</strong> Şu anda vade tarihi yaklaşan (10 veya 3 gün kala) müşteri bulunamadı.
                    </div>
                `);
            }
        },
        error: function() {
            $('#testSmsArea').html(`
                <div class="alert alert-danger">
                    <strong>✗ Hata:</strong> Müşteri verileri yüklenirken bir hata oluştu.
                </div>
            `);
        }
    });
}

// Test alanını oluştur
function renderTestArea(customers) {
    let html = `
        <div class="form-group">
            <label for="testCustomerSelect"><strong>Test için bir işletme seçin:</strong> <span class="text-primary">(Toplam ${customers.length} müşteri)</span></label>
            <select id="testCustomerSelect" class="form-control" onchange="updateTestSmsPreview()">
                <option value="">-- İşletme seçin --</option>
    `;
    
    customers.forEach((customer, index) => {
        html += `<option value="${index}">${customer.isletme_adi} - ${customer.odeme_turu} (${customer.kalan_gun} gün kaldı)</option>`;
    });
    
    html += `
            </select>
        </div>
        
        <div id="testSmsPreview" style="display: none;">
            <div style="background: #fff; border: 2px solid #667eea; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.1);">
                <h5 style="color: #667eea; margin: 0 0 15px 0; font-size: 18px; display: flex; align-items: center;">
                    <span style="font-size: 24px; margin-right: 10px;">📱</span>
                    Gönderilecek SMS İçeriği
                </h5>
                <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; position: relative;">
                    <div style="position: absolute; top: 10px; right: 10px; background: #667eea; color: white; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                        SMS ÖNİZLEME
                    </div>
                    <pre id="testSmsContent" style="margin: 0; padding: 0; background: transparent; border: none; color: #212529; font-size: 15px; line-height: 1.8; font-family: 'Segoe UI', Arial, sans-serif; white-space: pre-wrap; word-wrap: break-word;">Önizleme yükleniyor...</pre>
                </div>
                <div style="margin-top: 12px; text-align: right; color: #6c757d; font-size: 12px;">
                    <span id="testSmsLength">0</span> karakter
                </div>
            </div>
            
            <div style="margin-top: 15px; padding: 20px; background: linear-gradient(135deg, #e7f3ff 0%, #f0f7ff 100%); border-radius: 12px; border: 1px solid #b3d9ff;">
                <strong style="color: #0c5460; font-size: 16px; display: block; margin-bottom: 12px;">📋 Müşteri Bilgileri</strong>
                <div id="testCustomerInfo" style="font-size: 14px; line-height: 2; color: #2c3e50;"></div>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                <strong style="color: #856404;">⚠️ Dikkat:</strong>
                <p style="margin: 10px 0 0 0; color: #856404;">Bu butona tıkladığınızda seçili müşteriye GERÇEK SMS gönderilecektir!</p>
            </div>
            
            <button class="btn btn-success btn-lg btn-block mt-3" onclick="sendTestSms()">
                <i class="fa fa-paper-plane"></i> Seçili Müşteriye SMS Gönder
            </button>
        </div>
    `;
    
    $('#testSmsArea').html(html);
    
    // Global değişkene kaydet
    window.testCustomers = customers;
}

// SMS önizlemesini güncelle
function updateTestSmsPreview() {
    const selectBox = document.getElementById('testCustomerSelect');
    const selectedIndex = selectBox.value;
    const previewDiv = document.getElementById('testSmsPreview');
    
    if (!selectedIndex || selectedIndex === '') {
        previewDiv.style.display = 'none';
        return;
    }
    
    const customer = window.testCustomers[selectedIndex];
    
    // Tarih formatlama
    const aylar = ['Ocak', 'Subat', 'Mart', 'Nisan', 'Mayis', 'Haziran', 
                  'Temmuz', 'Agustos', 'Eylul', 'Ekim', 'Kasim', 'Aralik'];
    const tarih = new Date(customer.vade_tarihi);
    const vadeTarihi = tarih.getDate() + ' ' + aylar[tarih.getMonth()] + ' ' + tarih.getFullYear();
    
    // Ödeme türünü büyük harfe çevir
    const odemeTuru = customer.odeme_turu.toUpperCase().replace('İ', 'I').replace('Ş', 'S').replace('Ğ', 'G').replace('Ü', 'U').replace('Ö', 'O').replace('Ç', 'C');
    
    // SMS şablonunu al ve değişkenleri değiştir
    const smsTemplate = document.querySelector('textarea[name="sms_sablonu"]').value;
    const smsMetni = smsTemplate
        .replace(/\[ODEME_TURU\]/g, odemeTuru)
        .replace(/\[VADE_TARIHI\]/g, vadeTarihi);
    
    document.getElementById('testSmsContent').textContent = smsMetni;
    document.getElementById('testSmsLength').textContent = smsMetni.length;
    
    // Müşteri bilgilerini göster
    const kalanGunRenk = customer.kalan_gun <= 3 ? '#dc3545' : '#ffc107';
    const tutarFormatli = parseFloat(customer.tutar).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    document.getElementById('testCustomerInfo').innerHTML = `
        <div style="display: grid; grid-template-columns: 140px 1fr; gap: 8px;">
            <strong style="color: #495057;">İşletme:</strong> 
            <span>${customer.isletme_adi}</span>
            
            <strong style="color: #495057;">Telefon:</strong> 
            <span>${customer.telefon}</span>
            
            <strong style="color: #495057;">Ödeme Türü:</strong> 
            <span style="background: #667eea; color: white; padding: 2px 10px; border-radius: 4px; display: inline-block;">${customer.odeme_turu}</span>
            
            <strong style="color: #495057;">Vade Tarihi:</strong> 
            <span style="font-weight: 600; color: #dc3545;">${customer.vade_tarihi}</span>
            
            <strong style="color: #495057;">Kalan Süre:</strong> 
            <span style="background: ${kalanGunRenk}; color: white; padding: 2px 10px; border-radius: 4px; display: inline-block; font-weight: bold;">${customer.kalan_gun} gün kaldı</span>
            
            <strong style="color: #495057;">Tutar:</strong> 
            <span style="font-size: 16px; font-weight: bold; color: #28a745;">${tutarFormatli} TL</span>
        </div>
    `;
    
    previewDiv.style.display = 'block';
    previewDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Test SMS gönder
function sendTestSms() {
    const selectBox = document.getElementById('testCustomerSelect');
    const selectedIndex = selectBox.value;
    
    if (!selectedIndex || selectedIndex === '') {
        alert('Lütfen önce bir işletme seçin!');
        return;
    }
    
    const customer = window.testCustomers[selectedIndex];
    
    if (!confirm(`${customer.isletme_adi} isimli müşteriye (${customer.telefon}) SMS gönderilecek. Onaylıyor musunuz?`)) {
        return;
    }
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Gönderiliyor...';
    
    $.ajax({
        url: '<?php echo base_url("muhasebe/sendTestSms"); ?>',
        type: 'POST',
        data: {
            customer_index: selectedIndex
        },
        dataType: 'json',
        success: function(response) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Seçili Müşteriye SMS Gönder';
            
            if (response.success) {
                alert(`✓ SMS başarıyla gönderildi!\n\nMüşteri: ${customer.isletme_adi}\nTelefon: ${customer.telefon}\nDurum: ${response.message}`);
                // Sayfayı yenile (logları güncellemek için)
                setTimeout(() => location.reload(), 2000);
            } else {
                alert(`✗ SMS gönderilemedi!\n\nHata: ${response.message}`);
            }
        },
        error: function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Seçili Müşteriye SMS Gönder';
            alert('Bir hata oluştu!');
        }
    });
}

// Log detayını göster
function showLogDetail(id) {
    $('#logDetailModal').modal('show');
    $('#logDetailContent').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');
    
    $.ajax({
        url: '<?php echo base_url("muhasebe/smsLogDetay/"); ?>' + id,
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
                
                if (log.hata_mesaji && log.hata_mesaji !== 'null' && log.hata_mesaji.trim() !== '') {
                    html += `
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="text-danger">❌ Hata Mesajı:</h6>
                                <pre style="background: #ffebee; padding: 15px; border-radius: 5px; color: #c62828;">${log.hata_mesaji}</pre>
                            </div>
                        </div>
                    `;
                }
                
                if (log.api_response && log.api_response !== 'null' && log.api_response.trim() !== '') {
                    html += `
                        <div class="row mt-3">
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
	</div>
</div>

<?php $this->load->view("include/footer-js"); ?>
	
	<!-- DataTables JS -->
	<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
</body>
</html>
