---
applyTo: '**'
---
## Versiyon Yönetimi

Changelog sistemi artık **veritabanı tabanlı** çalışmaktadır. `changelog` tablosu kullanılır.

### Kullanıcı "Versiyon Gönder" Dediğinde:

**1. En Son Versiyon Tespit:**
- `sql/latest_version.sql` dosyasını oku
- Dosyadaki `-- SON VERSIYON: X.X.X` satırından versiyon numarasını tespit et
- Yeni versiyon numarasını belirle (bir sonraki patch versiyonu)
- Örnek: En son 1.3.7 ise → Yeni versiyon 1.3.8 olacak
- Not: Dosya okunamazsa kullanıcıya "en son versiyon ne?" diye sor

**2. Analiz Yap:**
- Konuşma geçmişini tarayarak yapılan değişiklikleri tespit et
- Değiştirilen dosyaları listele
- Değişiklik tiplerini belirle (feature/bugfix/improvement/security)
- Etkilenen modülleri tespit et

**3. Versiyon Artırımı:**
- Tespit edilen son versiyondan bir artır
- Değişiklik tipine göre artırım yap:
  - **Major** (X.0.0): Büyük yenilikler - Manuel belirtilirse
  - **Minor** (x.X.0): Yeni özellikler - Feature varsa
  - **Patch** (x.x.X): Düzeltmeler/İyileştirmeler - Sadece bugfix/improvement

**4. Changelog Taslağı Hazırla:**
```
📦 Versiyon: [versiyon_no]
📅 Tarih: [bugün]

[Değişiklik Tipi Badge]:
- [Açıklama satırları]

📁 Değişen Dosyalar:
- [dosya listesi]

👤 Yazar: Batuhan Kahraman
```

**5. Kullanıcı Onayı Al:**
- Taslağı kullanıcıya göster
- Onay alındıktan sonra `changelog` tablosuna kaydet
- Her değişiklik için ayrı satır oluştur (aynı versiyon, farklı type/module/description)

**6. SQL Dosyası Oluştur:**
- `temp/changelog_insert.sql` dosyası oluştur
- Her değişiklik için ayrı INSERT komutu yaz
- Dosya formatı:
```sql
-- Versiyon X.X.X - [Başlık]
-- Tarih: YYYY-MM-DD

INSERT INTO changelog 
(changelog_version, changelog_date, changelog_type, changelog_module, 
 changelog_description, changelog_details, changelog_file, 
 changelog_author, changelog_durum, changelog_olusturan)
VALUES 
('X.X.X', 'YYYY-MM-DD', 'type', 'module', 
 'Kısa açıklama', 
 'Detaylı açıklama', 
 'dosya/yolu.php', 
 'Batuhan KAHRAMAN', 1, 187);
```

**7. Latest Version Dosyasını Güncelle:**
- `sql/latest_version.sql` dosyasını güncelle
- Yeni versiyon numarası, tarih ve yazar bilgilerini yaz
- Dosya formatı:
```sql
-- SON VERSIYON BILGISI
-- Bu dosya otomatik olarak güncellenir
-- AI tarafından versiyon tespiti için kullanılır

-- SON VERSIYON: X.X.X
-- TARIH: YYYY-MM-DD
-- YAZAR: Batuhan KAHRAMAN

-- NOT: Bu dosya sadece versiyon bilgisi içerir
-- Gerçek changelog kayıtları changelog tablosunda tutulur
```

**8. Otomatik Import Linki Ver:**
```
https://crm.ilekasoft.com/yonetici/changelogListesi?import_sql=changelog_insert.sql
```

### Otomatik Import Sistemi:
- SQL dosyası `temp/` klasörüne kaydedilir
- URL parametresi ile otomatik import: `?import_sql=dosya_adi.sql`
- Sistem SQL'i okur ve INSERT komutlarını çalıştırır
- Başarılı import sonrası dosya otomatik silinir
- Sadece admin kullanıcılar erişebilir
- Flash mesaj ile sonuç gösterilir

### Not:
- Kullanıcı ID: 187 (Batuhan KAHRAMAN)
- Github: https://github.com/TurkbelgeYazilim/Digiturk-Ticari-CRM
- changelog.json artık kullanılmıyor, veritabanı kullanılıyor
- Yönetim sayfası: https://crm.ilekasoft.com/yonetici/changelogListesi
- Import Controller: `Yonetici.php -> importChangelogSql()`

---

## Genel Kurallar
- Syntax hatalarını her zaman kontrol edin.
- Admin kullanıcılarına (kullanici_grubu id=1) her zaman sınırsız yetki verilmelidir. Sayfalarda yetki kontrolü yaparken mutlaka admin grubuna ait kullanıcılar için yetki kısıtlaması uygulanmamalıdır.
- Yeni sayfa oluştururken mutlaka sayfaya ve butonlara yetki kontrolü eklenmeli ve bunu da https://crm.ilekasoft.com/yonetici/kullaniciGrubuDuzenle sayfasına eklenmeli veya güncellenmelidir.
- Yeni eklenen modüller için mutlaka yetki kontrolü ekleyin.
- DataTable olan bir sayfa varsa mutlaka arama, sıralama, sayfalama ve filtreleme özelliklerini ekleyin.
- Yeni eklenen sayfaların responsive olmasına dikkat edin.
- Yeni sayfa yapıldığında mutlaka https://crm.ilekasoft.com/illegal/illegal-listele sayfasını şablon olarak kullanın.
- Yeni eklenen sayfaların tasarımının mevcut tasarımla uyumlu olmasına dikkat edin.
- Yeni eklenen sayfaların performanslı çalışmasına dikkat edin.
---

## CSS ve Stil Kuralları
- **Sayfa içinde CSS tanımı yapmayın:** Tüm stiller `assets/css/` klasöründeki dosyalarda tanımlanmalıdır
- **Mevcut CSS sınıflarını kullanın:** Yeni sayfa oluştururken veya düzenlerken önce `assets/css/style.css` dosyasındaki mevcut sınıfları kontrol edin
- **Inline style kullanmayın:** `style="..."` şeklinde inline stil tanımları yapmayın (Örn: `style='font-family: Arial'` gibi)
- **Standart buton sınıflarını kullanın:**
  - `.btn-excel` - Excel export butonları için
  - `.btn-primary` - Ana aksiyon butonları için (kırmızı #d92637)
  - `.btn-success` - Başarı/onay butonları için (yeşil #22cc62)
  - `.btn-outline-success` - İkincil yeşil butonlar için
  - `.btn-info` - Bilgi butonları için (mavi #009efb)
  - `.btn-warning` - Uyarı butonları için (sarı #ffbc34)
  - `.btn-danger` - Tehlike/silme butonları için (kırmızı #ef3737)
- **CSS klasörü yapısı:**
  - `assets/css/style.css` - Ana stil dosyası (Bootstrap override ve özel stiller)
  - `assets/css/bootstrap.min.css` - Bootstrap framework
  - `assets/css/muhasebe.css` - Muhasebe modülü özel stilleri
- **Yeni stil gerekiyorsa:** `assets/css/style.css` dosyasına anlamlı sınıf adlarıyla ekleyin

---

## Veritabanı Kuralları
- Veritabanı ile ilgili herhangi bir işlem yapılacağında (tablo yapısı, kolonlar, ilişkiler vb.) önce `sql\ilekasoft_crmdb.sql` dosyasına bakılmalıdır.
- Bu dosyadan mevcut tablo yapıları, kolon isimleri ve tipleri, ilişkiler, foreign key'ler, indexler ve varsayılan değerler kontrol edilmelidir.