# MySavings - Aplikasi Manajemen Keuangan Pribadi

## Deskripsi Proyek

**MySavings** adalah aplikasi web berbasis PHP yang dirancang untuk membantu pengguna mengelola keuangan pribadi mereka dengan mudah dan efisien. Aplikasi ini menyediakan fitur untuk mencatat transaksi, melihat riwayat keuangan, dan menganalisis pola pengeluaran/pemasukan.

---

## Fitur yang Sudah Ada

### 1. **Authentication System** ✅
- ✓ Login dengan email & password
- ✓ Register user baru
- ✓ Forgot Password (reset password)
- ✓ Logout
- ✓ Session management & protection
- ✓ Password hashing dengan PHP password_hash()

### 2. **Dashboard** ✅
- ✓ Ringkasan keuangan dengan 3 kartu utama:
  - Total Saldo
  - Pemasukan Bulan Ini
  - Pengeluaran Bulan Ini
- ✓ Section "Arus Kas Real-time" (placeholder untuk chart)
- ✓ Transaksi Terakhir (5 transaksi terbaru)
- ✓ User profile dengan avatar dinamis
- ✓ Navigasi sidebar yang user-friendly

### 3. **Input Transaksi** ✅
- ✓ Catat transaksi baru (Pemasukan & Pengeluaran)
- ✓ Tab filter untuk jenis transaksi
- ✓ Form dengan field:
  - Judul Transaksi
  - Jumlah (auto format currency Rp)
  - Kategori (9 pilihan)
  - Tanggal
  - Catatan (optional)
- ✓ Right sidebar dengan:
  - Saldo Estimasi real-time
  - Transaksi Terakhir
- ✓ Validasi input lengkap

### 4. **Riwayat Transaksi** ✅
- ✓ Daftar semua transaksi dengan tabel terstruktur
- ✓ Filter berdasarkan periode:
  - Hari Ini
  - Minggu Ini
  - Bulan Ini
- ✓ Summary cards:
  - Total Pemasukan
  - Total Pengeluaran
  - Saldo Bersih
- ✓ Pagination (10 transaksi per halaman)
- ✓ Kolom: Tanggal, Kategori (+ icon), Keterangan, Jumlah
- ✓ Action buttons (Filter & Download placeholder)

### 5. **Laporan Keuangan** ✅
- ✓ Analisis trend 6 bulan terakhir (placeholder chart)
- ✓ Rincian per kategori dengan:
  - Icon kategori
  - Total transaksi
  - Tipe (Pemasukan/Pengeluaran)
  - Jumlah transaksi
- ✓ Card layout yang menarik

### 6. **UI/UX** ✅
- ✓ Responsive design (mobile, tablet, desktop)
- ✓ Gradient color scheme (Blue → Purple)
- ✓ Sidebar navigation yang konsisten
- ✓ Font yang readable (Segoe UI)
- ✓ Icon emoji untuk kategori
- ✓ Color coding:
  - Green untuk pemasukan
  - Red untuk pengeluaran
  - Blue untuk saldo

### 7. **Database Integration** ✅
- ✓ MySQL dengan prepared statements (SQL injection safe)
- ✓ Table users dengan hashing password
- ✓ Table transaksi dengan foreign key
- ✓ Query teroptimasi

### 8. **Navigation** ✅
- ✓ Menu sidebar yang konsisten di semua halaman
- ✓ Link navigasi yang bekerja:
  - Login → Register/Forgot Password
  - Dashboard ↔ Transaksi ↔ Reports
  - Logout di semua halaman
- ✓ Active menu indicator

---

## Fitur yang Akan Ditambah

### Phase 2 (Enhancement)
- [ ] **Settings/Profile Management**
  - Edit profil user
  - Ganti password
  - Preferensi aplikasi (tema, bahasa)
  - Hapus akun

- [ ] **Edit & Delete Transaksi**
  - Ubah data transaksi yang sudah tercatat
  - Hapus transaksi dengan konfirmasi
  - History log perubahan
  
- [ ] **Chart & Analytics** (Upgrade dari placeholder)
  - Chart.js atau Apex Charts integration
  - Pie chart pengeluaran per kategori
  - Line chart trend bulanan
  - Bar chart perbandingan income vs expense
  - Statistik detail dengan insights

- [ ] **Recurring Transactions**
  - Transaksi berulang (daily, weekly, monthly)
  - Auto-generate transaksi otomatis
  - Reminder untuk recurring transaksi

## Tech Stack

### Backend
- **PHP 7.4+** dengan OOP & Prepared Statements
- **MySQL 5.7+** untuk database
- **Session Management** untuk authentication

### Frontend
- **HTML5** untuk structure
- **CSS3** dengan Gradient & Flexbox/Grid
- **Vanilla JavaScript** untuk interactivity
- **Responsive Design** (Mobile-first approach)

### Tools & Libraries (untuk phase selanjutnya)
- Chart.js atau Apex Charts untuk charts
- PHPMailer untuk email
- TCPDF atau Dompdf untuk PDF export
- PhpSpreadsheet untuk Excel export

---

## Struktur Folder

```
MySavings/
├── config/
│   └── koneksi.php              # Database connection
├── src/
│   ├── login.php                # Login page
│   ├── register.php             # Registration page
│   ├── forgot_password.php      # Password reset
│   ├── logout.php               # Logout action
│   ├── dashboard.php            # Main dashboard
│   ├── transaksi.php            # Transaction input
│   ├── riwayat_transaksi.php    # Transaction history
│   └── laporan_keuangan.php     # Financial reports
└── README.md                    # Project documentation
```

---

## Database Schema

### Table: users
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Table: transaksi
```sql
CREATE TABLE transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jenis ENUM('pemasukan', 'pengeluaran') NOT NULL,
    jumlah DECIMAL(15, 2) NOT NULL,
    keterangan VARCHAR(255),
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    kategori VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## Fitur Keamanan

 **Yang sudah diimplementasi:**
- Password hashing dengan `password_hash()`
- SQL injection prevention dengan Prepared Statements
- Session validation di setiap halaman
- XSS protection dengan `htmlspecialchars()`
- CSRF token ready untuk diimplementasi

 **Yang akan ditambah:**
- Two-Factor Authentication (2FA)
- Rate limiting pada login
- Password strength validation
- Audit logging
- Encryption untuk data sensitif

---

## Setup & Installation

### Prerequisites
- XAMPP atau Web Server dengan PHP 7.4+
- MySQL 5.7+
- Browser modern (Chrome, Firefox, Safari, Edge)

### Langkah Instalasi

1. **Setup Database**
   ```bash
   # Buat database MySQL
   CREATE DATABASE mysavings;
   USE mysavings;
   
   # Jalankan schema SQL di atas
   ```

2. **Copy Files**
   ```bash
   # Copy ke folder web server
   cp -r MySavings/ /path/to/htdocs/
   ```

3. **Konfigurasi Database**
   ```php
   # Edit config/koneksi.php
   $host = "localhost";
   $username = "root";
   $password = "";
   $database = "mysavings";
   ```

4. **Jalankan Aplikasi**
   ```
   http://localhost/MySavings/src/login.php
   ```

---

## 📊 Progress Status

| Fitur | Status | Version |
|-------|--------|---------|
| Authentication | ✅ Complete | v1.0 |
| Dashboard | ✅ Complete | v1.0 |
| Input Transaksi | ✅ Complete | v1.0 |
| Riwayat Transaksi | ✅ Complete | v1.0 |
| Laporan Keuangan | ✅ Complete | v1.0 |
| UI/UX Responsive | ✅ Complete | v1.0 |
| Navigation | ✅ Complete | v1.0 |
| Database Integration | ✅ Complete | v1.0 |
| Settings/Profile | 🔜 Planned | v2.0 |
| Edit/Delete Transaksi | 🔜 Planned | v2.0 |
| Export PDF/Excel | 🔜 Planned | v2.0 |
| Budget Management | 🔜 Planned | v2.0 |
| Charts & Analytics | 🔜 Planned | v2.0 |

---

## Kategori Transaksi yang Tersedia

**Pengeluaran:**
- Makanan & Minuman
- Transportasi
- Belanja Bulanan
- Hiburan
- Tagihan & Cicilan
- Investasi
- Kesehatan
- Pendidikan
- Lainnya

**Pemasukan:**
- Gaji Pokok
- Bonus
- (Custom categories dapat ditambah di phase 2)

---

## Kontribusi

Fitur-fitur baru dapat diminta melalui:
- Create issue di repository
- Submit feature request

---

## Lisensi

Project ini dibuat untuk keperluan edukasi

---

## Author
**Nama:** [Ali]
**Nama:** [Rayyan]
**Nama:** [Umam]
**Nama:** [Riko]
**Created with ❤️ for better financial management**

---

## Support

Untuk bantuan atau pertanyaan:
- Hubungi developer
- Baca dokumentasi di README ini
- Check troubleshooting section

---

## Roadmap

### Q3 2026 (v1.0 - Current)
- ✅ Core features (Auth, Dashboard, Transaction, Reports)

### Q3 2026 (v2.0)
- 🔄 Settings & Profile
- 🔄 Edit/Delete functionality
- 🔄 Export features (PDF/Excel)
- 🔄 Real charts integration
---

**Last Updated:** June 9, 2026  
**Version:** 1.0.0  
**Status:** ✅ Production Ready
