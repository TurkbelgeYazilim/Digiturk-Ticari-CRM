<?php

defined('BASEPATH') OR exit('No direct script access allowed');



$route['default_controller'] = 'home';



// Login/Check routes - Önemli: Route sırası önemlidir

$route['giris'] = 'check';

$route['check'] = 'check';

$route['check/login'] = 'check/login';

$route['login'] = 'check/login';

$route['logintest'] = 'logintest';

$route['checktest'] = 'checktest';

$route['checktest/login'] = 'checktest/login';



//rapor

$route['rapor/gunluk-rapor'] = 'rapor/gunluk_rapor';

$route['rapor/gunluk_rapor'] = 'rapor/gunluk_rapor';  // Alt çizgi ile de erişim sağla

$route['rapor/test'] = 'rapor/test';

$route['rapor/debug'] = 'rapor/debug';



//cari

$route['cari/cari-arama'] = 'cari/cariArama';

$route['cari/cariBakiyeGetir'] = 'cari/cariBakiyeGetir';

$route['cari/cari-listesi'] = 'cari/cariListesi';

$route['cari/cari-listesi/(:num)'] = 'cari/cariListesi';

$route['cari/cari-hareketleri'] = 'cari/cariHareketleri';

$route['cari/cari-gruplari'] = 'cari/cariGruplari';

// FIXED: Correct routing for cari edit form

$route['cari/cari-karti-olustur'] = 'cari/cariKartiOlustur';

$route['cari/cari-karti-duzenle/(:num)'] = 'cari/cariKartiDuzenle/$1';

$route['cari/getPersonelList'] = 'cari/getPersonelList';

$route['cari/updateCariOlusturan'] = 'cari/updateCariOlusturan';

$route['cari/cari-dekont'] = 'cari/cariDekont';

$route['cari/cari-dekont-duzenle/(:num)'] = 'cari/cariDekontDuzenle/$1';



//stok

$route['stok/stok-listesi'] = 'stok/stokListesi';

$route['stok/stok-listesi/(:num)'] = 'stok/stokListesi';

$route['stok/stok-hareketleri'] = 'stok/stokHareketleri';

$route['stok/stok-karti-olustur'] = 'stok/stokKartiOlustur';

$route['stok/stok-karti-duzenle/(:num)'] = 'stok/stokKartiDuzenle/$1';

$route['stok/stok-gruplari'] = 'stok/stokGruplari';

$route['stok/stok-birimleri'] = 'stok/stokBirimleri';

$route['stok/stokArama'] = 'stok/stokArama';



//fatura

$route['fatura/satis-faturasi'] = 'fatura/satisFaturasi';

$route['fatura/satis-faturasi/(:num)'] = 'fatura/satisFaturasi/$1';

$route['fatura/satis-faturasi/(:num)/(:num)'] = 'fatura/satisFaturasiCari/$1';

$route['fatura/satis-faturasi-duzenle/(:num)'] = 'fatura/satisFaturasiDuzenle/$1';

$route['fatura/satis-faturasi-listesi'] = 'fatura/satisFaturasiListesi';

$route['fatura/satis-faturasi-listesi/(:num)'] = 'fatura/satisFaturasiListesi';

$route['fatura/stok-bilgi-duzenle/(:num)'] = 'fatura/stokBilgiDuzenle/$1';

$route['fatura/stok-bilgi-guncelle'] = 'fatura/stokBilgiGuncelle';

$route['fatura/alis-faturasi'] = 'fatura/alisFaturasi';

$route['fatura/alis-faturasi/(:num)'] = 'fatura/alisFaturasi/$1';

$route['fatura/alis-faturasi-duzenle/(:num)'] = 'fatura/alisFaturasiDuzenle/$1';

$route['fatura/alis-faturasi/(:num)/(:num)'] = 'fatura/alisFaturasiCari/$1';

$route['fatura/alis-faturasi-listesi'] = 'fatura/alisFaturasiListesi';

$route['fatura/alis-faturasi-listesi/(:num)'] = 'fatura/alisFaturasiListesi';

$route['fatura/goruntule'] = 'fatura/goruntule';





$route['fatura/proforma-faturasi'] = 'fatura/proformaFaturasi';

$route['fatura/proforma-faturasi/(:num)'] = 'fatura/proformaFaturasi/$1';

$route['fatura/proforma-faturasi-duzenle/(:num)'] = 'fatura/proformaFaturasiDuzenle/$1';

$route['fatura/proforma-faturasi/(:num)/(:num)'] = 'fatura/proformaFaturasiCari/$1';

$route['fatura/proforma-listesi'] = 'fatura/proformaListesi';

$route['fatura/proforma-listesi/(:num)'] = 'fatura/proformaListesi';

$route['fatura/proforma-faturalastir'] = 'fatura/proformaFaturalastir';





$route['fatura/seri-yonetimi'] = 'fatura/seriYonetimi';

$route['fatura/efatura-olustur'] = 'fatura/eFaturaOlustur';

$route['fatura/efatura-olustur/(:num)'] = 'fatura/eFaturaOlustur/$1';

$route['fatura/efatura-olustur/(:num)/(:num)'] = 'fatura/eFaturaOlusturCari/$1';

$route['fatura/efatura-giden'] = 'fatura/efaturaGiden';

$route['fatura/efatura-gelen'] = 'fatura/efaturaGelen';

$route['fatura/earsiv-olustur'] = 'fatura/eArsivOlustur';

$route['fatura/earsiv-olustur/(:num)'] = 'fatura/eArsivOlustur/$1';

$route['fatura/earsiv-olustur/(:num)/(:num)'] = 'fatura/eArsivOlusturCari/$1';

$route['fatura/earsiv-faturalari'] = 'fatura/eArsivFaturalari';

$route['fatura/eirsaliye-olustur'] = 'fatura/eirsaliyeolustur';

$route['fatura/eirsaliye-olustur/(:num)'] = 'fatura/eirsaliyeolustur/$1';

$route['fatura/eirsaliye-giden'] = 'fatura/eirsaliyeGiden';

$route['fatura/eirsaliye-gelen'] = 'fatura/eirsaliyeGelen';



$route['gib/gib-bilgi'] = 'gib/gibBilgi';

$route['gib/fatura-olustur'] = 'gib/faturaOlustur';

$route['gib/duzenlenen-belgeler'] = 'gib/duzenlenenBelgeler';

$route['gib/adima-duzenlenen-belgeler'] = 'gib/adimaDuzenlenenBelgeler';



$route["ocr/dosya-yukle"]="ocr/dosyaYukle";

$route["ocr/yuklenen-dosyalar"]="ocr/yuklenenDosyalar";





$route['fatura/taslak-fatura-yukle'] = 'fatura/taslakFaturaYukle';



//kasa

$route['kasa/kasa-acilis-karti'] = 'kasa/kasaAcilisKarti';

$route['kasa/kasa-acilis-karti-duzenle/(:num)'] = 'kasa/kasaAcilisKartiDuzenle/$1';

$route['kasa/kasa-listesi'] = 'kasa/kasaListesi';

$route['kasa/kasa-hareket-kayitlari'] = 'kasa/kasaHareketKayitlari';

$route['kasa/kasa-listesi/(:num)'] = 'kasa/kasaListesi';

$route['kasa/kasa-devir-karti'] = 'kasa/kasaDevirKarti';

$route['kasa/kasa-devir-karti-duzenle/(:num)'] = 'kasa/kasaDevirKartiDuzenle/$1';

$route['kasa/kasa-tahsilat'] = 'kasa/kasaTahsilat';

$route['kasa/kasa-tahsilat-duzenle/(:num)'] = 'kasa/kasaTahsilatDuzenle/$1';

$route['kasa/kasa-odeme-islemi'] = 'kasa/kasaOdeme';

$route['kasa/kasa-odeme-islemi-duzenle/(:num)'] = 'kasa/kasaOdemeDuzenle/$1';

$route['kasa/kasa-virman'] = 'kasa/kasaVirman';

$route['kasa/kasa-virman-duzenle/(:any)'] = 'kasa/kasaVirmanDuzenle/$1';

$route['kasa/kasadan-bankaya-transfer'] = 'kasa/kasadanBankayaTransfer';

$route['kasa/kasadan-bankaya-transfer-duzenle/(:any)'] = 'kasa/kasadanBankayaTransferDuzenle/$1';



//banka

$route['banka/banka-acilis-karti'] = 'banka/bankaAcilisKarti';

$route['banka/banka-acilis-karti-duzenle/(:num)'] = 'banka/bankaAcilisKartiDuzenle/$1';

$route['banka/banka-listesi'] = 'banka/bankaListesi';

$route['banka/banka-listesi/(:num)'] = 'banka/bankaListesi';

$route['banka/banka-hareket-kayitlari'] = 'banka/bankaHareketKayitlari';

$route['banka/banka-havale'] = 'banka/bankaHavale';

$route['banka/banka-havale-duzenle/(:num)'] = 'banka/bankaHavaleDuzenle/$1';

$route['banka/banka-eft'] = 'banka/bankaEFT';

$route['banka/banka-eft-duzenle/(:num)'] = 'banka/bankaEFTDuzenle/$1';

$route['banka/banka-islemi'] = 'banka/bankaIslemi';

$route['banka/banka-islemi-duzenle/(:num)'] = 'banka/bankaIslemiDuzenle/$1';

$route['banka/banka-virman'] = 'banka/bankaVirman';

$route['banka/banka-virman-duzenle/(:any)'] = 'banka/bankaVirmanDuzenle/$1';

$route['banka/bankadan-kasaya-transfer'] = 'banka/bankadanKasayaTransfer';

$route['banka/bankadan-kasaya-transfer-duzenle/(:any)'] = 'banka/bankadanKasayaTransferDuzenle/$1';



//giderler

$route['giderler/gider-ekle'] = 'giderler/giderEkle';

$route['giderler/gider-duzenle/(:num)'] = 'giderler/giderDuzenle/$1';

$route['giderler/gider-listesi'] = 'giderler/giderListesi';

$route['giderler/gider-turleri'] = 'giderler/giderKategorileri';

$route['giderler/fis-yukle'] = 'giderler/fisYukle';



//çek

$route["cek/cek-portfoyu"] = 'cek/cekPortfoyu';

$route["cek/yeni-cek"] = 'cek/yeniCek';

$route["cek/cek-detaylari/(:num)"] = 'cek/cekDetaylari/$1';



//senet

$route["senet/senet-portfoyu"] = 'senet/senetPortfoyu';

$route["senet/yeni-senet"] = 'senet/yeniSenet';

$route["senet/senet-detaylari/(:num)"] = 'senet/senetDetaylari/$1';

// Eski route kaldırıldı: $route["senet/detay/(:num)"] = 'senet/detay/$1';



//raporlar

$route["raporlar"] = 'raporlar/raporlar';

$route["raporlar/stok-raporlari"] = 'raporlar/stokRaporlari';

$route["raporlar/kasa-raporlari"] = 'raporlar/kasaRaporlari';

$route["raporlar/cari-stok-hareket-raporlari"] = 'raporlar/cariStokHareketRaporlari';

$route["raporlar/gider-raporlari"] = 'raporlar/giderRaporlari';

$route["raporlar/kritik-stok-raporu"] = 'raporlar/kritikStokRaporu';

$route["raporlar/biten-stoklar-raporu"] = 'raporlar/bitenStoklarRaporu';

$route["raporlar/en-cok-satanlar-raporu"] = 'raporlar/enCokSatanlarRaporu';

$route["raporlar/digiturk-personel-ciro-adet"] = 'raporlar/digiturk_personel_ciro_adet';

$route["raporlar/digiturk-bolge-muduru-ciro-adet"] = 'raporlar/digiturk_bolge_muduru_ciro_adet';

$route["raporlar/digiturk-sehir-ciro-adet"] = 'raporlar/digiturk_sehir_ciro_adet';

$route["raporlar/digiturk_sehir_cari_detay"] = 'raporlar/digiturk_sehir_cari_detay';



//edönüşüm

//$route["edonusum"] = 'edonusum';



//modul

$route["modul/ayarlar/(:any)"] = 'modul/modulAyar/$1';

$route["modul/moduller"] = 'modul/moduller';



//yönetici

$route["yonetici/kullanici-loglari"] = 'yonetici/kullaniciLoglari';

$route["yonetici/kullanici-loglari/(:num)"] = 'yonetici/kullaniciLoglari';

$route["yonetici/yeni-kullanici-ekle"] = 'yonetici/yeniKullaniciEkle';

$route["yonetici/kullanici-yetkileri-duzenle"] = 'yonetici/kullaniciYetkileriDuzenle';

$route["yonetici/mevcut-kullanici-duzenle/(:num)"] = 'yonetici/mevcutKullaniciDuzenle/$1';

$route["yonetici/kullanici-listesi"] = 'yonetici/kullaniciListesi';

$route["yonetici/kullanici-listesi/(:num)"] = 'yonetici/kullaniciListesi';

$route["yonetici/sorumluluk-bolgesi"] = 'yonetici/sorumlulukBolgesi';

$route["yonetici/ayarlar"] = 'yonetici/ayarlar';



$route["yonetici/yukle/(:num)"] = 'yonetici/yukle/$1';



//admin

$route["admin/firma-yonetimi"] = 'admin/firmaYonetimi';

$route["admin/genel-log-kayitlari"] = 'admin/genelLogKayitlari';

$route["admin/moduller"] = 'admin/moduller';

$route["admin/destek"] = 'admin/desteksistemi';

$route["admin/destek/detay/(:num)"] = 'admin/destekdetay/$1';

$route["admin/duyurular"] = 'admin/duyurular';



//kullanıcı

$route["kullanici/sifre-degistir"] = 'kullanici/sifreDegistir';



$route["satin-al"] = 'odeme/index2';



//home

$route["destek"] = 'home/destek';

$route["destek/detay/(:num)"] = 'home/destekdetay/$1';



$route['404_override'] = 'home/hata';

$route['translate_uri_dashes'] = FALSE;



$route['tahsilat/tahsilat_kaydet'] = 'tahsilat/tahsilat_kaydet';

$route['tahsilat/tahsilat-olustur'] = 'tahsilat/tahsilat_olustur';

$route['tahsilat/tahsilat-listesi'] = 'tahsilat/tahsilat_listesi';

$route['tahsilat/tahsilat_listesi_ajax'] = 'tahsilat/tahsilat_listesi_ajax';

$route['tahsilat/tahsilat-duzenle/(:num)'] = 'tahsilat/tahsilat_duzenle/$1';

$route['tahsilat/tahsilat-detay/(:num)'] = 'tahsilat/tahsilat_detay/$1';

$route['tahsilat/tahsilat_sil/(:num)'] = 'tahsilat/tahsilat_sil/$1';

$route['tahsilat/tahsilat_excel_export'] = 'tahsilat/tahsilat_excel_export';

$route['tahsilat/tahsilat_guncelle'] = 'tahsilat/tahsilat_guncelle';



// Teklif modülü

$route['teklif/teklif-hazirla'] = 'teklif/teklif_hazirla';

$route['teklif/teklif-listele'] = 'teklif/teklif_listele';

$route['teklif/teklif-ayar'] = 'teklif/teklif_ayar';

$route['teklif/verilen-teklifler'] = 'teklif/verilen_teklifler';

$route['teklif/tum-teklifler'] = 'teklif/tum_teklifler';

$route['teklif/potansiyel-cari-filtrele'] = 'teklif/potansiyelCariFiltrele';

$route['teklif/telefon-cari-search'] = 'teklif/telefon_cari_search';

// Teklif Ayarları AJAX endpointleri

$route['teklif/sezonlar-listele'] = 'teklif/sezonlar_listele';

$route['teklif/sezon-ekle'] = 'teklif/sezon_ekle';

$route['teklif/sezon-guncelle'] = 'teklif/sezon_guncelle';

$route['teklif/sezon-sil'] = 'teklif/sezon_sil';

$route['teklif/cari-gruplar-listele'] = 'teklif/cari_gruplar_listele';

$route['teklif/cari-grup-ekle'] = 'teklif/cari_grup_ekle';

$route['teklif/cari-grup-guncelle'] = 'teklif/cari_grup_guncelle';

$route['teklif/cari-grup-sil'] = 'teklif/cari_grup_sil';

$route['teklif/sektorler-listele'] = 'teklif/sektorler_listele';

$route['teklif/sektor-ekle'] = 'teklif/sektor_ekle';

$route['teklif/sektor-guncelle'] = 'teklif/sektor_guncelle';

$route['teklif/sektor-sil'] = 'teklif/sektor_sil';

$route['teklif/telefon_search'] = 'teklif/telefon_search';



// Prim modülü

$route['prim/prim-hazirla'] = 'prim/prim_hazirla';

$route['prim/prim-listele'] = 'prim/prim_listele';

$route['prim/prim-ayar'] = 'prim/prim_ayar';

$route['prim/potansiyel-cari-filtrele'] = 'prim/potansiyelCariFiltrele';

$route['prim/potansiyel_cari_autocomplete'] = 'prim/potansiyel_cari_autocomplete';

$route['prim/potansiyel_satis_ekle'] = 'prim/potansiyel_satis_ekle';

$route['prim/get_cari_primler'] = 'prim/get_cari_primler';

$route['prim/get_potansiyel_mahalleler'] = 'prim/get_potansiyel_mahalleler';

// Prim Ayarları AJAX endpointleri

$route['prim/sezonlar-listele'] = 'prim/sezonlar_listele';

$route['prim/sezon-ekle'] = 'prim/sezon_ekle';

$route['prim/sezon-guncelle'] = 'prim/sezon_guncelle';

$route['prim/sezon-sil'] = 'prim/sezon_sil';

$route['prim/cari-gruplar-listele'] = 'prim/cari_gruplar_listele';

$route['prim/cari-grup-ekle'] = 'prim/cari_grup_ekle';

$route['prim/cari-grup-guncelle'] = 'prim/cari_grup_guncelle';

$route['prim/cari-grup-sil'] = 'prim/cari_grup_sil';

$route['prim/sektorler-listele'] = 'prim/sektorler_listele';

$route['prim/sektor-ekle'] = 'prim/sektor_ekle';

$route['prim/sektor-guncelle'] = 'prim/sektor_guncelle';

$route['prim/sektor-sil'] = 'prim/sektor_sil';

$route['prim/telefon_search'] = 'prim/telefon_search';



// İllegal modülü

$route['illegal/illegal-tespit-olustur'] = 'illegal/illegal_tespit_olustur';

$route['illegal/illegal-tespit-excel-yukle'] = 'illegal/illegal_tespit_excel_yukle';

$route['illegal/illegal_cari_kaydet'] = 'illegal/illegal_cari_kaydet';

$route['illegal/illegal-listele'] = 'illegal/illegal_listele';

$route['illegal/illegal-islemler'] = 'illegal/illegal_islemler';

$route['illegal/illegal-ayar'] = 'illegal/illegal_ayar';

$route['illegal/potansiyel-cari-filtrele'] = 'illegal/potansiyelCariFiltrele';

$route['illegal/potansiyel_cari_autocomplete'] = 'illegal/potansiyel_cari_autocomplete';

$route['illegal/potansiyel_satis_ekle'] = 'illegal/potansiyel_satis_ekle';

// İllegal Ayarları AJAX endpointleri

$route['illegal/sezonlar-listele'] = 'illegal/sezonlar_listele';

$route['illegal/sezon-detay'] = 'illegal/sezon_detay';

$route['illegal/sezon-ekle'] = 'illegal/sezon_ekle';

$route['illegal/sezon-guncelle'] = 'illegal/sezon_guncelle';

$route['illegal/sezon-sil'] = 'illegal/sezon_sil';

$route['illegal/takimlar-listele'] = 'illegal/takimlar_listele';

$route['illegal/takim-ekle'] = 'illegal/takim_ekle';

$route['illegal/takim-guncelle'] = 'illegal/takim_guncelle';

$route['illegal/takim-sil'] = 'illegal/takim_sil';

$route['illegal/statuler-listele'] = 'illegal/statuler_listele';

$route['illegal/get_statuler_for_select'] = 'illegal/get_statuler_for_select';

$route['illegal/statu-detay'] = 'illegal/statu_detay';

$route['illegal/statu-ekle'] = 'illegal/statu_ekle';

$route['illegal/statu-guncelle'] = 'illegal/statu_guncelle';

$route['illegal/statu-sil'] = 'illegal/statu_sil';

$route['illegal/cari-gruplar-listele'] = 'illegal/cari_gruplar_listele';

$route['illegal/cari-grup-ekle'] = 'illegal/cari_grup_ekle';

$route['illegal/cari-grup-guncelle'] = 'illegal/cari_grup_guncelle';

$route['illegal/cari-grup-sil'] = 'illegal/cari_grup_sil';

$route['illegal/sektorler-listele'] = 'illegal/sektorler_listele';

$route['illegal/sektor-ekle'] = 'illegal/sektor_ekle';

$route['illegal/sektor-guncelle'] = 'illegal/sektor_guncelle';

$route['illegal/sektor-sil'] = 'illegal/sektor_sil';

$route['illegal/telefon_search'] = 'illegal/telefon_search';



// Muhasebe modülü

$route['muhasebe/onay-bekleyen-tahsilatlar'] = 'muhasebe/onayBekleyenTahsilatlar';

$route['muhasebe/tahsilat-listesi'] = 'muhasebe/tahsilatListesi';
$route['muhasebe/tahsilatListesiExcel'] = 'muhasebe/tahsilatListesiExcel';

$route['muhasebe/tahsilat-onay/(:num)'] = 'muhasebe/tahsilatOnay/$1';

$route['muhasebe/tahsilat-red/(:num)'] = 'muhasebe/tahsilatRed/$1';
$route['muhasebe/tahsilat-geri-al/(:num)'] = 'muhasebe/tahsilatGeriAl/$1';

$route['muhasebe/tahsilat-detay/(:num)'] = 'muhasebe/tahsilatDetay/$1';

$route['muhasebe/senet-yonetim'] = 'muhasebe/senet_yonetim';
$route['muhasebe/senet-yonetim-excel'] = 'muhasebe/senet_yonetim_excel';

$route['muhasebe/senet-detay/(:num)'] = 'muhasebe/senet_detay/$1';

$route['muhasebe/senet-konum-degistir'] = 'muhasebe/senet_konum_degistir';

$route['muhasebe/musteri-listesi'] = 'muhasebe/musteriListesi';
$route['muhasebe/tam-ana-rapor'] = 'muhasebe/tam_ana_rapor';
$route['muhasebe/tam-ana-rapor-excel'] = 'muhasebe/tam_ana_rapor_excel';
$route['muhasebe/getStokBilgileri'] = 'muhasebe/getStokBilgileri';
$route['muhasebe/stokBilgileriKaydet'] = 'muhasebe/stokBilgileriKaydet';
$route['muhasebe/getTahsilatDetay'] = 'muhasebe/getTahsilatDetay';
$route['muhasebe/updateTahsilatOnay'] = 'muhasebe/updateTahsilatOnay';



// Talepler modülü

$route['talepler/talepler-listesi'] = 'talepler/index';

$route['talepler-listesi'] = 'talepler/index'; // Redirect to keep old URL working

$route['talepler'] = 'talepler/index'; // Redirect to keep old URL working

$route['talepler/talep-olustur'] = 'talepler/talep_olustur';

$route['talepler-listesi/talep-olustur'] = 'talepler/talep_olustur'; // Old URL compatibility

$route['talepler/talep-duzenle/(:num)'] = 'talepler/talep_duzenle/$1';

$route['talepler-listesi/talep-duzenle/(:num)'] = 'talepler/talep_duzenle/$1'; // Old URL compatibility

$route['talepler/talep_duzenle/(:num)'] = 'talepler/talep_duzenle/$1'; // Old URL compatibility

$route['talepler/talep-kaydet'] = 'talepler/talep_kaydet';

$route['talepler-listesi/talep-kaydet'] = 'talepler/talep_kaydet'; // Old URL compatibility

$route['talepler/talep_kaydet'] = 'talepler/talep_kaydet'; // Old URL compatibility

$route['talepler/talep-detay/(:num)'] = 'talepler/talep_detay/$1';

$route['talepler-listesi/talep-detay/(:num)'] = 'talepler/talep_detay/$1'; // Old URL compatibility

$route['talepler/talep_detay/(:num)'] = 'talepler/talep_detay/$1'; // Old URL compatibility

$route['talepler/get-ilceler/(:num)'] = 'talepler/get_ilceler/$1';

$route['talepler-listesi/get-ilceler/(:num)'] = 'talepler/get_ilceler/$1'; // Old URL compatibility

$route['talepler/get_ilceler/(:num)'] = 'talepler/get_ilceler/$1'; // Old URL compatibility

$route['talepler/durum-guncelle'] = 'talepler/durum_guncelle';

$route['talepler/otomatik-talep-olustur'] = 'talepler/otomatik_talep_olustur';

$route['talepler/auto-support'] = 'talepler/otomatik_talep_olustur'; // Alternative URL

$route['talepler-listesi/durum-guncelle'] = 'talepler/durum_guncelle'; // Old URL compatibility

$route['talepler/debug-iller'] = 'talepler/debug_iller';

$route['talepler-listesi/debug-iller'] = 'talepler/debug_iller'; // Old URL compatibility

$route['talepler/debug-ilceler'] = 'talepler/debug_ilceler';

$route['talepler-listesi/debug-ilceler'] = 'talepler/debug_ilceler'; // Old URL compatibility

$route['talepler/debug-ilceler/(:num)'] = 'talepler/debug_ilceler/$1';

$route['talepler-listesi/debug-ilceler/(:num)'] = 'talepler/debug_ilceler/$1'; // Old URL compatibility



// Aktivasyon routes

$route['aktivasyon'] = 'aktivasyon/index';

$route['aktivasyon/olustur'] = 'aktivasyon/olustur';

$route['aktivasyon/olustur/(:num)'] = 'aktivasyon/olustur/$1';

$route['aktivasyon/ssport-olustur'] = 'aktivasyon/ssport_olustur';

$route['aktivasyon/ssport-olustur/(:num)'] = 'aktivasyon/ssport_olustur/$1';

$route['aktivasyon/ssport-kaydet'] = 'aktivasyon/ssport_kaydet';

$route['aktivasyon/tabii-olustur'] = 'aktivasyon/tabii_olustur';

$route['aktivasyon/tabii-olustur/(:num)'] = 'aktivasyon/tabii_olustur/$1';

$route['aktivasyon/tabii-kaydet'] = 'aktivasyon/tabii_kaydet';

$route['aktivasyon/listele'] = 'aktivasyon/listele';

$route['aktivasyon/kaydet'] = 'aktivasyon/kaydet';

$route['aktivasyon/sil/(:num)'] = 'aktivasyon/sil/$1';

$route['aktivasyon/cari-ara'] = 'aktivasyon/cari_ara';

$route['aktivasyon/cari-detay-getir'] = 'aktivasyon/cari_detay_getir';

$route['aktivasyon/tahsilat-gorsel-yukle'] = 'aktivasyon/tahsilat_gorsel_yukle';



// Voip & Hakediş routes

$route['voip/operator-tanimla'] = 'voip/operator_tanimla';

$route['voip-hakedis/operator-tanimla'] = 'voip/operator_tanimla'; // Alternative URL

$route['voip/numara-tanimla'] = 'voip/numara_tanimla';

$route['voip-hakedis/numara-tanimla'] = 'voip/numara_tanimla'; // Alternative URL

$route['voip/numara-teslim'] = 'voip/numara_teslim';

$route['voip-hakedis/numara-teslim'] = 'voip/numara_teslim'; // Alternative URL

$route['voip/gunluk-harcama'] = 'voip/gunluk_harcama';

$route['voip-hakedis/gunluk-harcama'] = 'voip/gunluk_harcama'; // Alternative URL

$route['voip/hakedis-tanimlama'] = 'voip/hakedis_tanimlama';

$route['voip-hakedis/hakedis-tanimlama'] = 'voip/hakedis_tanimlama'; // Alternative URL

// Hakediş İris Rapor Routes (ID:1906)
$route['voip/hakedis-iris-rapor'] = 'voip/hakedis_iris_rapor';
$route['voip-hakedis/hakedis-iris-rapor'] = 'voip/hakedis_iris_rapor'; // Alternative URL
$route['voip/iris-rapor-yukle'] = 'voip/iris_rapor_yukle';
$route['voip-hakedis/iris-rapor-yukle'] = 'voip/iris_rapor_yukle'; // Alternative URL

// Hakediş Bayi Hesapla Routes (ID:1907)
$route['voip/hakedis-bayi-hesapla'] = 'voip/hakedis_bayi_hesapla';
$route['voip-hakedis/hakedis-bayi-hesapla'] = 'voip/hakedis_bayi_hesapla'; // Alternative URL

// Voip Hakediş Bayi Ödeme/Gider Routes (ID:1908)
$route['voip/bayi-odeme-gider'] = 'voip/bayi_odeme_gider';
$route['voip/bayi-odeme-gider/(:num)'] = 'voip/bayi_odeme_gider/$1';
$route['voip/bayi-odeme-kaydet'] = 'voip/bayi_odeme_kaydet';
$route['voip/bayi-odeme-sil/(:num)'] = 'voip/bayi_odeme_sil/$1';

// Debug test route (geliştirme amaçlı)
$route['voip/debug-test'] = 'voip/debug_test';

// Stok Bilgi Düzenle Routes
$route['fatura/stok-bilgi-duzenle/(:num)'] = 'fatura/stokBilgiDuzenle/$1';
$route['fatura/stok-bilgi-guncelle'] = 'fatura/stokBilgiGuncelle';
