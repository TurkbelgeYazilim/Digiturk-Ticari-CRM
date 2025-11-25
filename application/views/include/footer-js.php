<a href="<?= base_url("destek"); ?>?ref=footer" class="destek_btn"> <i data-feather="life-buoy" class="mr-1"></i> Destek Talep Et</a>



<?php

// MySQL bağlantısını yapalım

$mysqli = new mysqli('localhost', 'ilekasoft_crmuser', 'KaleW356!', 'ilekasoft_crmdb');



// Bağlantıyı kontrol et

if ($mysqli->connect_error) {

    die('Bağlantı hatası: ' . $mysqli->connect_error);

}



// En son destek_id değerini al

$result = $mysqli->query("SELECT MAX(destek_id) as son_destek_id FROM destek");

$row = $result->fetch_assoc();

$son_destek_id = $row['son_destek_id'] ?? 'Bilinmiyor';



// En son versiyon numarasını al (veritabanından)
$version_result = $mysqli->query("SELECT changelog_version FROM changelog ORDER BY changelog_id DESC LIMIT 1");
$app_version = '1.0.0'; // Varsayılan versiyon
if ($version_result && $version_result->num_rows > 0) {
    $version_row = $version_result->fetch_assoc();
    $app_version = $version_row['changelog_version'];
}

$mysqli->close();
?>

<div class="footer">
	<p>&copy; ilekaSoft CRM | 
		<a href="#" onclick="showChangelog()" style="color:#d92637; cursor: pointer;" title="Versiyon değişikliklerini görüntüle">
			<i class="fa fa-code-branch"></i> v<?= $app_version; ?>
		</a> | 
		<a href="<?= base_url("destek"); ?>?ref=footer" style="color:#d92637;">Destek Talep Et</a> | 
		<a href="mailto:destek@ilekasoft.com" style="color:#d92637;">destek@ilekasoft.com</a>
	</p>
</div>

<!-- Changelog Modal -->
<div class="modal fade" id="changelogModal" tabindex="-1" role="dialog" aria-labelledby="changelogModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title" id="changelogModalLabel">
					<i class="fa fa-code-branch mr-2"></i>Versiyon Geçmişi
				</h5>
				<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
				<div id="changelogLoading" class="text-center p-4">
					<i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
					<div class="mt-2">Versiyon bilgileri yükleniyor...</div>
				</div>
				<div id="changelogContent" style="display: none;">
					<!-- Changelog içeriği buraya yüklenecek -->
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
			</div>
		</div>
	</div>
</div>

<style>
.version-toggle-icon {
    transition: transform 0.2s ease;
}

.card-header[aria-expanded="true"] .version-toggle-icon {
    transform: rotate(90deg);
}

.pagination .page-link {
    color: #d92637;
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #d92637;
    border-color: #d92637;
}

.pagination .page-link:hover {
    color: #a91e2b;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
}

#changelogContent .card-header {
    transition: background-color 0.2s ease;
}

#changelogContent .card-header:hover {
    opacity: 0.9;
}
</style>


<!-- jQuery -->

<script src="<?= base_url(); ?>assets/js/jquery-3.5.1.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>



<!-- Bootstrap Core JS -->

<script src="<?= base_url(); ?>assets/js/popper.min.js"></script>

<script src="<?= base_url(); ?>assets/js/bootstrap.min.js"></script>



<script src="<?= base_url(); ?>assets/plugins/bootstrap-datepicker-1.9.0-dist/js/bootstrap-datepicker.min.js"></script>

<script src="<?= base_url(); ?>assets/plugins/bootstrap-datepicker-1.9.0-dist/locales/bootstrap-datepicker.tr.min.js"></script>



<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.js" type="text/javascript" ></script>

<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment-with-locales.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

<script src="<?= base_url(); ?>assets/js/daterangepicker.js"></script>



<!-- Feather Icon JS -->

<script src="<?= base_url(); ?>assets/js/feather.min.js"></script>



<!-- Slimscroll JS -->

<script src="<?= base_url(); ?>assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>



<!-- Select2 JS -->

<script src="<?= base_url(); ?>assets/plugins/select2/js/select2.min.js"></script>





<!-- Session timeout script geçici olarak devre dışı bırakıldı -->
<!-- <script src="<?= base_url(); ?>assets/js/bootstrap-session-timeout.js"></script> -->



<!-- Custom JS -->

<script src="<?= base_url(); ?>assets/js/script.js"></script>





<!-- 

<script>

	setInterval(function(){

		var base_url = "<?php echo base_url();?>";

		$.post(base_url + 'home/sessionKontrol', function (result) {

			if(result=="1")

				window.location.href = base_url+"home/logout";

		});

	},5000);

</script>

-->



<script>

	// Slideshow functionality wrapped in IIFE to avoid global scope pollution

	(function() {

		let slideIndex = 1;

		

		function showSlides(n) {

			let i;

			let slides = document.getElementsByClassName("mySlides");

			let dots = document.getElementsByClassName("dot");

			

			if (slides.length === 0) return; // No slides found

			

			if (n > slides.length) {slideIndex = 1}

			if (n < 1) {slideIndex = slides.length}

			for (i = 0; i < slides.length; i++) {

				slides[i].style.display = "none";

			}

			for (i = 0; i < dots.length; i++) {

				dots[i].className = dots[i].className.replace(" active", "");

			}

			if (slides[slideIndex-1]) {

				slides[slideIndex-1].style.display = "block";

			}

			if (dots[slideIndex-1]) {

				dots[slideIndex-1].className += " active";

			}

		}

		

		// Global functions for slideshow control

		window.plusSlides = function(n) {

			showSlides(slideIndex += n);

		};



		window.currentSlide = function(n) {

			showSlides(slideIndex = n);

		};

		

		// Initialize slideshow if slides exist

		if (document.getElementsByClassName("mySlides").length > 0) {

			showSlides(slideIndex);

			

			// Auto-advance slides

			window.onload = function () {

				setInterval(function(){

					window.plusSlides(1);

				}, 5000);

			};

		}

	})();



	// Session timeout geçici olarak devre dışı bırakıldı
	/*
	$.sessionTimeout({

		logoutUrl: '<?= base_url('home/logout'); ?>',

		redirUrl: '<?= base_url('home/logout'); ?>',

		warnAfter: 600000, //600000    10 dakika

		redirAfter: 620000, //620000 10dakika20saniye

		ignoreUserActivity:true,

		countdownMessage: '{timer} saniye sonunda çıkışa yönlendirileceksiniz.'

	});
	*/

</script>



<!-- Bildirim Sistemi JavaScript -->

<script>

let notificationInterval;



function bildirimleriniYukle() {

    $.ajax({

        url: '<?= base_url("home/bildirim_listesi"); ?>',

        type: 'GET',

        dataType: 'json',

        success: function(response) {

            if(response.success) {

                const count = response.toplam;

                const bildirimler = response.bildirimler;

                

                // Bildirim sayısını güncelle

                if(count > 0) {

                    $('#notification-count').text(count).show();

                } else {

                    $('#notification-count').hide();

                }

                

                // Bildirim içeriğini güncelle

                let content = '';

                if(bildirimler.length > 0) {

                    bildirimler.forEach(function(bildirim) {

                        const tarih = new Date(bildirim.bildirim_tarih);

                        const tarihStr = tarih.toLocaleString('tr-TR', {

                            day: '2-digit',

                            month: '2-digit',

                            hour: '2-digit',

                            minute: '2-digit'

                        });

                        

                        content += `

                            <div class="notification-item ${bildirim.bildirim_okundu == 0 ? 'unread' : ''}" 

                                 data-id="${bildirim.bildirim_id}" 

                                 data-link="${bildirim.bildirim_link || '#'}"

                                 onclick="bildirimTikla(${bildirim.bildirim_id}, '${bildirim.bildirim_link || '#'}')">

                                <div class="noti-title">${bildirim.bildirim_baslik}</div>

                                <div class="noti-message">${bildirim.bildirim_mesaj}</div>

                                <div class="noti-time">${tarihStr}</div>

                            </div>

                        `;

                    });

                } else {

                    content = `

                        <div class="notification-message text-center">

                            <i data-feather="bell" class="text-muted"></i>

                            <p>Yeni bildirim bulunmuyor</p>

                        </div>

                    `;

                }

                

                $('#notification-content').html(content);

                

                // Feather icons'u yeniden başlat (güvenli şekilde)

                try {

                    if(typeof feather !== 'undefined' && feather && feather.replace) {

                        feather.replace();

                    }

                } catch(e) {

                    console.warn('Feather icons could not be replaced:', e);

                }

            }

        },

        error: function() {

            console.error('Bildirimler yüklenemedi');

        }

    });

}



function bildirimTikla(bildirimId, link) {

    // Bildirimi okundu olarak işaretle

    $.ajax({

        url: '<?= base_url("home/bildirim_okundu"); ?>',

        type: 'POST',

        data: {

            bildirim_id: bildirimId

        },

        success: function(response) {

            if(response.success) {

                // Bildirimi okundu olarak görsel güncelle

                $(`[data-id="${bildirimId}"]`).removeClass('unread');

                

                // Bildirim sayısını güncelle

                setTimeout(function() {

                    bildirimleriniYukle();

                }, 500);

            }

        }

    });

    

    // Linke yönlendir

    if(link && link !== '#') {

        window.location.href = link;

    }

}



function tumBildirimleriTemizle() {

    swal({

        title: "Emin misiniz?",

        text: "Tüm bildirimler silinecektir!",

        type: "warning",

        showCancelButton: true,

        confirmButtonText: "Evet, sil!",

        cancelButtonText: "İptal",

        closeOnConfirm: false

    }, function(isConfirm) {

        if (isConfirm) {

            // Tüm bildirimleri temizle (bu endpoint'i ekleyeceğiz)

            $.ajax({

                url: '<?= base_url("home/bildirim_temizle"); ?>',

                type: 'POST',

                success: function(response) {

                    swal("Tamamlandı!", "Tüm bildirimler temizlendi.", "success");

                    bildirimleriniYukle();

                },

                error: function() {

                    swal("Hata!", "Bildirimler temizlenemedi.", "error");

                }

            });

        }

    });

}



// Sayfa yüklendiğinde bildirimler yükle

$(document).ready(function() {

    bildirimleriniYukle();

    

    // Her 30 saniyede bir bildirimler güncelle

    notificationInterval = setInterval(bildirimleriniYukle, 30000);

});



// Sayfa kapanırken interval'i temizle

$(window).on('beforeunload', function() {

    if(notificationInterval) {

        clearInterval(notificationInterval);

    }

});



// Destek detay sayfasında yanıtları okundu olarak işaretle

if(window.location.pathname.includes('/destek/detay/')) {

    // Sayfa yüklendiğinde tüm yanıtları okundu olarak işaretle

    setTimeout(function() {

        $('.new-reply').each(function() {

            const replyId = $(this).data('reply-id');

            if(replyId) {

                $.ajax({

                    url: '<?= base_url("home/yanit_okundu"); ?>',

                    type: 'POST',

                    data: {

                        yanit_id: replyId

                    },

                    success: function(response) {

                        if(response.success) {

                            $(this).removeClass('new-reply');

                        }

                    }

                });

            }

        });

    }, 2000);

}

</script>

<!-- Changelog Sistemi JavaScript -->
<script>
let changelogData = null;
let currentPage = 1;
const itemsPerPage = 10;

// DOM hazır olduğunda modal kontrolü yap
$(document).ready(function() {
    console.log('DOM hazır');
    console.log('changelogModal var mı?', $('#changelogModal').length);
    console.log('Bootstrap modal fonksiyonu var mı?', typeof $.fn.modal);
});

// Debug fonksiyonu
function debugModal() {
    console.log('=== MODAL DEBUG ===');
    console.log('changelogModal elementi:', $('#changelogModal')[0]);
    console.log('Modal uzunluğu:', $('#changelogModal').length);
    console.log('Bootstrap yüklenmiş mi?', typeof $.fn.modal);
    console.log('jQuery versiyonu:', $.fn.jquery);
    
    if ($('#changelogModal').length > 0) {
        try {
            $('#changelogModal').modal('show');
            console.log('Modal açma denemesi başarılı');
        } catch (e) {
            console.error('Modal açma hatası:', e);
        }
    } else {
        console.error('Modal elementi bulunamadı!');
    }
}

// Changelog modal'ını göster
function showChangelog() {
    console.log('showChangelog() çağrıldı');
    
    try {
        $('#changelogModal').modal('show');
        console.log('Modal show komutu gönderildi');
        $('#changelogLoading').show();
        $('#changelogContent').hide();
        
        // AJAX ile changelog verilerini getir
        $.ajax({
            url: '<?= base_url("home/getChangelog") ?>',
            method: 'GET',
            dataType: 'json',
            timeout: 10000,
            cache: false,
            success: function(response) {
                console.log('AJAX başarılı:', response);
                console.log('Response type:', typeof response);
                console.log('Response.success:', response.success);
                console.log('Response.data:', response.data);
                
                $('#changelogLoading').hide();
                $('#changelogContent').show();
                
                if (response.success && response.data) {
                    changelogData = response.data;
                    currentPage = 1;
                    renderChangelog(changelogData);
                } else {
                    $('#changelogContent').html(`
                        <div class="alert alert-warning text-center">
                            <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
                            <h5>Versiyon bilgileri yüklenemedi</h5>
                            <p>${response.message || 'Lütfen daha sonra tekrar deneyin.'}</p>
                            ${response.error ? '<small>Detay: ' + response.error + '</small>' : ''}
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX hatası detayları:');
                console.error('- Status:', status);
                console.error('- Error:', error);
                console.error('- XHR Status:', xhr.status);
                console.error('- XHR Response:', xhr.responseText);
                
                $('#changelogLoading').hide();
                $('#changelogContent').show().html(`
                    <div class="alert alert-danger text-center">
                        <i class="fa fa-exclamation-circle fa-2x mb-2"></i>
                        <h5>Hata Oluştu</h5>
                        <p>Versiyon bilgileri yüklenirken bir hata oluştu.</p>
                        <small>Status: ${status}<br>Hata: ${error}</small>
                    </div>
                `);
            }
        });
    } catch (e) {
        console.error('showChangelog hatası:', e);
        alert('Modal açılırken hata oluştu: ' + e.message);
    }
}

// Changelog'u render et
function renderChangelog(data) {
    if (!data.versions || data.versions.length === 0) {
        $('#changelogContent').html(`
            <div class="text-center text-muted">
                <i class="fa fa-info-circle fa-2x mb-2"></i>
                <p>Henüz versiyon geçmişi bulunmuyor.</p>
            </div>
        `);
        return;
    }

    const totalPages = Math.ceil(data.versions.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const currentVersions = data.versions.slice(startIndex, endIndex);

    let html = `
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">Güncel Versiyon: </h6>
                    <span class="badge badge-primary ml-2">${data.app_version}</span>
                    <small class="text-muted ml-3">Son güncelleme: ${formatDate(data.last_updated)}</small>
                </div>
                <div class="text-muted small">
                    Sayfa ${currentPage} / ${totalPages} (Toplam ${data.versions.length} versiyon)
                </div>
            </div>
        </div>
        <hr>
    `;
    
    currentVersions.forEach(function(version, index) {
        const globalIndex = startIndex + index;
        const isLatest = globalIndex === 0;
        const versionId = `version-${version.version.replace(/\./g, '-')}`;
        
        html += `
            <div class="card mb-3 ${isLatest ? 'border-primary' : ''}">
                <div class="card-header ${isLatest ? 'bg-primary text-white' : 'bg-light'}" 
                     style="cursor: pointer;" 
                     onclick="toggleVersionDetails('${versionId}')" 
                     data-toggle="collapse" 
                     data-target="#${versionId}" 
                     aria-expanded="false">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fa fa-chevron-right mr-2 version-toggle-icon" id="icon-${versionId}"></i>
                            <i class="fa fa-tag mr-2"></i>
                            v${version.version}
                            ${isLatest ? '<span class="badge badge-light text-primary ml-2">Güncel</span>' : ''}
                        </h6>
                        <small>${formatDate(version.date)}</small>
                    </div>
                </div>
                <div class="collapse" id="${versionId}">
                    <div class="card-body">
                        <h6>Değişiklikler:</h6>
                        <ul class="list-unstyled">
        `;
        
        if (version.changes && version.changes.length > 0) {
            version.changes.forEach(function(change) {
                // Tip bazlı renk ve ikon belirleme
                let badgeColor = 'secondary';
                let badgeIcon = 'fa-circle';
                let badgeLabel = change.type;
                
                if (change.type === 'feature') {
                    badgeColor = 'success';
                    badgeIcon = 'fa-plus-circle';
                    badgeLabel = 'Özellik';
                } else if (change.type === 'bugfix') {
                    badgeColor = 'danger';
                    badgeIcon = 'fa-bug';
                    badgeLabel = 'Hata Düzeltme';
                } else if (change.type === 'improvement') {
                    badgeColor = 'info';
                    badgeIcon = 'fa-arrow-up';
                    badgeLabel = 'İyileştirme';
                }
                
                html += `
                    <li class="mb-3">
                        <div>
                            <span class="badge badge-${badgeColor} mr-2">
                                <i class="fa ${badgeIcon} mr-1"></i>
                                ${badgeLabel}
                            </span>
                            ${change.module ? `<strong>${change.module}</strong>` : ''}
                        </div>
                        ${change.description ? `<div class="mt-1">${change.description}</div>` : ''}
                        ${change.details ? `<div class="text-muted small mt-1">${change.details}</div>` : ''}
                        ${change.file ? `<div class="text-muted small mt-1"><code>${change.file}</code></div>` : ''}
                        ${change.author ? `<div class="text-muted small mt-1"><i class="fa fa-user"></i> ${change.author}</div>` : ''}
                    </li>
                `;
            });
        }
        
        html += `
                        </ul>
                    </div>
                </div>
            </div>
        `;
    });

    // Pagination kontrollerini ekle
    if (totalPages > 1) {
        html += `
            <nav aria-label="Versiyon sayfalama">
                <ul class="pagination justify-content-center">
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
                            <i class="fa fa-chevron-left"></i> Önceki
                        </a>
                    </li>
        `;

        // Sayfa numaralarını göster
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a></li>`;
        }

        html += `
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
                            Sonraki <i class="fa fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        `;
    }
    
    $('#changelogContent').html(html);
}

// Versiyon detaylarını aç/kapat
function toggleVersionDetails(versionId) {
    const icon = $('#icon-' + versionId);
    const isCollapsed = $('#' + versionId).hasClass('show');
    
    if (isCollapsed) {
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
}

// Sayfa değiştir
function changePage(page) {
    if (!changelogData || page < 1 || page > Math.ceil(changelogData.versions.length / itemsPerPage)) {
        return;
    }
    
    currentPage = page;
    renderChangelog(changelogData);
    
    // Modal içeriğini en üste kaydır
    $('#changelogContent').scrollTop(0);
}

// Tarih formatla
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
</script>

<!-- Konum Takibi Sistemi -->
<script src="<?= base_url(); ?>assets/js/konum-takibi.js"></script>