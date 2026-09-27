# 🟢 Canlı Kur'an-ı Kerim Radyosu & Tilavet Portalı

![PHP 8.x Compatible](https://img.shields.io/badge/PHP-8.x_Compatible-777BB4?style=for-the-badge&logo=php)
![PWA Ready](https://img.shields.io/badge/PWA-Ready-10B981?style=for-the-badge&logo=pwa)
![License: MIT](https://img.shields.io/badge/License-MIT-D4AF37?style=for-the-badge)
![Database](https://img.shields.io/badge/Database-JSON_(No_MySQL)-blue?style=for-the-badge)

7/24 kesintisiz canlı Kur'an-ı Kerim yayınları, mealli ve tecvidli tilavet radyoları, PWA mobil uygulama desteği ve gelişmiş yönetim paneline sahip **%100 Ücretsiz ve Açık Kaynak** PHP scripti.

---

### 🌐 CANLI DEMO & ÖRNEK SİTE
- 🔗 **Canlı Önizleme:** [https://kurandinle.akasasozluk.com/](https://kurandinle.akasasozluk.com/)
- 🔐 **Demo Yönetim Paneli:** [https://kurandinle.akasasozluk.com/admin/index.php](https://kurandinle.akasasozluk.com/admin/index.php)
  - **Kullanıcı Adı:** `demo`
  - **Şifre:** `123456`

---

### 📦 DOĞRUDAN İNDİR
👉 **[kuran-radyo-script.zip (Doğrudan İndir)](https://github.com/user-attachments/files/32700672/kuran-radyo-script.zip)**

---

### 📸 EKRAN GÖRÜNTÜLERİ

| Masaüstü Görünümü | Mobil Görünüm |
| :---: | :---: |
| ![Masaüstü Görünümü](https://github.com/user-attachments/assets/6b64bbc6-1382-4f5a-ad6a-ceae85d4c19a) | ![Mobil Görünüm](https://github.com/user-attachments/assets/084b1fe7-74f4-455d-b540-eb7fa1ddff78) |

---

## 🌟 ÖNE ÇIKAN ÖZELLİKLER

- 📱 **Mobil Uygulama Desteği:** iOS (Safari), Android (Chrome) ve Masaüstü cihazlara uygulama olarak kurulabilir.
- 🔄 **Kesintisiz Yayın & Otomatik Retry:** Yayın kopmalarında 5 kez otomatik denenir, açılmazsa yedek yayına (Backup Stream) geçer.
- 🔌 **Sitene Ekle (Embed Widget):** Diğer web sitelerinde yayınlanabilen iFrame widget oluşturucu.
- 🌙 **Gece & Gündüz Teması:** Gece ve gündüz modları arası tek tıkla geçiş yapabilen İslami renk paleti.
- 📊 **Canlı İstatistikler:** Anlık dinleyici sayısı, toplam dinleme ve ülke bazlı ziyaretçi analizleri.
- 🚀 **Sıfır Veritabanı (JSON Mimarisi):** MySQL veritabanı gerektirmez, ultra hızlı ve hafiftir.

---

## 📖 KURULUM KILAVUZU

### 📂 Adım 1: Dosyaları Sunucuya Yükleme
Arşivindeki `kuran-radyo` klasörü içerisindeki tüm dosyaları cPanel / Hosting panelinize girip **Dosya Yöneticisi (File Manager)** alanına yükleyin.

---

### 🔓 Adım 2: Hosting Panelinde `data/` İzni Verme
*Çoğu hostingde sistem bu izni otomatik verir. Ancak hata alırsanız 5 saniyede şöyle izin verebilirsiniz:*

#### 🔹 cPanel Kullanıyorsanız:
1. cPanel Dosya Yöneticisi (File Manager)'a girin.
2. `data` klasörünün üzerine sağ tıklayıp **"Change Permissions"** *(İzinleri Değiştir)* seçin.
3. Açılan küçük penceredeki sayısal kutuya **775** veya **777** yazıp *"Change Permissions"* butonuna basın.

#### 🔹 DirectAdmin Kullanıyorsanız:
1. DirectAdmin Dosya Yöneticisi (File Manager)'a girin.
2. `data` klasörünün sağındaki kutucuğu işaretleyin.
3. Üst menüden **"Set Permissions"** *(İzin Ayarla)* deyip **777** yapın.

#### 🔹 FileZilla (FTP) Kullanıyorsanız:
1. FileZilla ile sitenize bağlanın.
2. `data` klasörüne sağ tıklayıp **"Dosya İzinleri..."** *(File Permissions)* seçeneğine tıklayın.
3. Sayısal Değer kutusuna **777** yazıp *"Alt dizinlere de uygula"* kutusunu işaretleyip Tamam'a basın.

#### 🔹 VDS / VPS Sunucusu Kullanıyorsanız:
Terminalinizde (SSH) şu 2 komutu çalıştırın:
```bash
chown -R www-data:www-data /var/www/kuran-radyo/data
chmod -R 775 /var/www/kuran-radyo/data
```
---

### 🔐 Adım 3: Yönetim Paneline İlk Giriş & Şifre Değiştirme

1. Tarayıcınızdan yönetim paneline bağlanın:  
   👉 https://siteniz.com/admin/login.php
2. Varsayılan Giriş Bilgileri:
   - Kullanıcı Adı: admin
   - Şifre: admin123
3. Paneller menüsünden "Güvenlik" sekmesine girin.
4. Mevcut Şifreniz: admin123 yazın, Yeni Şifrenizi belirleyip kaydet butonuna basın.

---

### 📋 ÖZGÜR LİSANS VE KULLANIM NOTU

Bu script %100 açık kaynaklıdır. Scripti istediğiniz gibi sitelerinizde ücretsiz kullanabilir, kodlarını dilediğiniz gibi geliştirebilir, özelleştirebilir ve başkalarıyla paylaşabilirsiniz.

Hayırlı kullanımlar dileriz. 🤲
