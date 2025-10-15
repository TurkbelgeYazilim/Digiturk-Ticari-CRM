# İleka CRM Kurulum Talimatları

## 🔧 Konfigürasyon Dosyaları

Güvenlik nedeniyle hassas konfigürasyon dosyaları repository'de bulunmamaktadır. Aşağıdaki dosyaları kopyalayıp düzenlemeniz gerekmektedir:

### 1. Veritabanı Ayarları
```bash
cp application/config/database.example.php application/config/database.php
```
`database.php` dosyasında şu bilgileri güncelleyin:
- `hostname`: Veritabanı sunucu adresi
- `username`: Veritabanı kullanıcı adı
- `password`: Veritabanı şifresi
- `database`: Veritabanı adı

### 2. SMS Ayarları
```bash
cp application/config/sms.example.php application/config/sms.php
```
`sms.php` dosyasında şu bilgileri güncelleyin:
- `sms_username`: SMS servis kullanıcı adı
- `sms_password`: SMS servis şifresi
- `sms_originator`: Gönderici adı

### 3. Email Ayarları
```bash
cp application/config/email.example.php application/config/email.php
```
`email.php` dosyasında şu bilgileri güncelleyin:
- `email_smtp_host`: SMTP sunucu adresi
- `email_smtp_user`: Email adresi
- `email_smtp_pass`: Email şifresi

## 🚀 İlk Kurulum

1. Repository'i klonlayın
2. Config dosyalarını yukarıdaki talimatlar doğrultusunda oluşturun
3. Composer bağımlılıklarını yükleyin:
   ```bash
   composer install
   ```
4. Veritabanı tablolarını oluşturun (SQL dosyası `1OrnekData/` klasöründe)

## 🔒 Güvenlik

- **Asla** gerçek şifreleri repository'e commit etmeyin
- Config dosyaları `.gitignore` ile korunmaktadır
- Production ortamında environment variables kullanın

## 👨‍💻 Geliştirici

**Batuhan Kahraman**
- 📧 batuhan.kahraman@ileka.com.tr
- 📱 +90 501 357 10 85
- 🔗 [GitHub](https://github.com/Batuhan-Kahraman/)