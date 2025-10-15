# CRM Database Migration - kullanici_sorumluluk_bolgesi Table Enhancement

## Durum Özeti

✅ **TAMAMLANAN İŞLEMLER:**
- Database migration dosyaları oluşturuldu
- Turkish column name uyumlu migration script hazırlandı
- Foreign key ilişkileri yapılandırıldı
- Verification scripts oluşturuldu
- PHP migration runner script hazırlandı

⏳ **BEKLEYEN İŞLEMLER:**
- Database migration'ının çalıştırılması
- Tablo yapısının doğrulanması
- Foreign key ilişkilerinin test edilmesi

## Migration Dosyaları

### 1. Ana Migration Dosyası
```
database_migrations/fix_kullanici_sorumluluk_bolgesi.sql
```
Bu dosya şu alanları ekler:
- `baslangic_tarihi` (DATE) - Sorumluluk başlangıç tarihi
- `bitis_tarihi` (DATE) - Sorumluluk bitiş tarihi  
- `ulke_id` (VARCHAR(2), default: 'TR') - Ülke kodu
- `aciklama` (TEXT) - Ek açıklama

### 2. Verification Script
```
database_migrations/verify_migration.sql
```
Migration sonrası kontrol için kullanılır.

### 3. PHP Migration Runner
```
run_migration.php
```
PHP üzerinden migration çalıştırmak için hazırlandı.

## Migration'ı Çalıştırma

### Seçenek 1: MySQL Command Line (Önerilen)
```bash
mysql -u ilekasoft_crmuser -p'KaleW356!' ilekasoft_crmdb < database_migrations/fix_kullanici_sorumluluk_bolgesi.sql
```

### Seçenek 2: phpMyAdmin
1. phpMyAdmin'e giriş yapın
2. `ilekasoft_crmdb` veritabanını seçin
3. SQL sekmesine gidin
4. `database_migrations/fix_kullanici_sorumluluk_bolgesi.sql` dosyasının içeriğini kopyalayın
5. Çalıştır butonuna tıklayın

### Seçenek 3: PHP Script (Web üzerinden)
1. `run_migration.php` dosyasını web sunucusunda çalıştırın
2. Browser'da dosyaya erişin: `http://yoursite.com/run_migration.php`

## Doğrulama

Migration tamamlandıktan sonra şu kontrolleri yapın:

### 1. Tablo Yapısını Kontrol Edin
```sql
DESCRIBE kullanici_sorumluluk_bolgesi;
```

### 2. Yeni Alanları Kontrol Edin
```sql
SELECT * FROM kullanici_sorumluluk_bolgesi LIMIT 1;
```

### 3. Verification Script'i Çalıştırın
```bash
mysql -u ilekasoft_crmuser -p'KaleW356!' ilekasoft_crmdb < database_migrations/verify_migration.sql
```

## Beklenen Sonuç

Migration başarılı olduğunda `kullanici_sorumluluk_bolgesi` tablosu şu alanları içerecek:

| Alan Adı | Tür | Null | Default | Açıklama |
|----------|-----|------|---------|----------|
| id | INT | NO | AUTO_INCREMENT | Primary key |
| kullanici_id | INT | NO | - | Kullanıcı ID |
| bolge_id | INT | NO | - | Bölge ID |
| baslangic_tarihi | DATE | YES | NULL | Başlangıç tarihi |
| bitis_tarihi | DATE | YES | NULL | Bitiş tarihi |
| ulke_id | VARCHAR(2) | YES | 'TR' | Ülke kodu |
| aciklama | TEXT | YES | NULL | Açıklama |

## Foreign Key İlişkileri

- `ulke_id` → `ulkeler.ulke_kodu` (Ülke bilgisi için)
- Mevcut foreign key'ler korunur

## İndeksler

- `idx_kullanici_sorumluluk_ulke` - ulke_id için performans
- `idx_kullanici_sorumluluk_tarih` - tarih aralığı sorguları için

## Güvenlik

- Migration conditional logic kullanır (alanlar zaten varsa hata vermez)
- Mevcut veriler korunur
- Foreign key constraints veri bütünlüğünü sağlar

## Sonraki Adımlar

1. ✅ Migration'ı çalıştırın
2. ✅ Verification script ile kontrol edin
3. ✅ Uygulama kodunda yeni alanları kullanmaya başlayın
4. ✅ Kullanıcı arayüzünde yeni alanlar için form elemanları ekleyin

## Sorun Giderme

Eğer migration sırasında hata alırsanız:

1. **"Table doesn't exist" hatası:**
   - `kullanici_sorumluluk_bolgesi` tablosunun var olduğundan emin olun
   - `SHOW TABLES LIKE 'kullanici_sorumluluk_bolgesi';`

2. **"Column already exists" hatası:**
   - Normal bir durumdur, migration conditional logic kullanır
   - İkinci kez çalıştırılsa bile hata vermez

3. **Foreign key hatası:**
   - `ulkeler` tablosunun var olduğundan emin olun
   - `ulke_kodu` alanının var olduğundan emin olun

4. **Permission hatası:**
   - Database kullanıcısının ALTER TABLE yetkisi olduğundan emin olun

## Backup Önerisi

Migration'dan önce veritabanı backup'ı alın:
```bash
mysqldump -u ilekasoft_crmuser -p'KaleW356!' ilekasoft_crmdb > backup_$(date +%Y%m%d_%H%M%S).sql
```
