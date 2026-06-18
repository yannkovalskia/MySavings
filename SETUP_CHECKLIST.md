# 📝 Setup Checklist - MySavings v2.0

## ✅ Tahap 1: Persiapan Database

### Step 1: Backup Database
```bash
# Backup database Anda terlebih dahulu!
# di phpMyAdmin atau MySQL CLI:
```

### Step 2: Tambahkan Column Catatan
Jalankan query berikut di phpMyAdmin (pada database `mysavings`):

```sql
ALTER TABLE `transaksi` ADD COLUMN `catatan` TEXT NULL DEFAULT NULL AFTER `tanggal`;
```

**Verifikasi dengan**:
```sql
DESCRIBE transaksi;
```

Pastikan output menunjukkan column `catatan` dengan type `TEXT`.

---

## ✅ Tahap 2: Verifikasi File

Pastikan file-file berikut sudah ada:

### ✓ File Baru:
- [ ] `src/settings.php` - ✅ SUDAH DIBUAT

### ✓ File Dimodifikasi:
- [ ] `src/transaksi.php` - ✅ SUDAH DIUBAH
- [ ] `src/riwayat_transaksi.php` - ✅ SUDAH DIUBAH
- [ ] `src/login.php` - ✅ SUDAH LENGKAP

---

## ✅ Tahap 3: Testing

### Test Login & Settings
1. Buka http://localhost/MySavings/src/login.php
2. Login dengan akun Anda
3. Klik "Settings" di sidebar
4. Test edit profil (ubah nama/email)
5. Test ubah password (jika ada)

### Test Edit Transaksi
1. Buka http://localhost/MySavings/src/transaksi.php
2. Atau http://localhost/MySavings/src/riwayat_transaksi.php
3. Cari tombol "Edit" (berwarna biru)
4. Klik Edit untuk test
5. Ubah data transaksi
6. Klik "Update Transaksi"
7. Verifikasi data tersimpan

### Test Hapus Transaksi
1. Di halaman riwayat atau transaksi
2. Cari tombol "Hapus" (berwarna merah)
3. Klik Hapus
4. Konfirmasi dialog muncul?
5. Klik "OK" untuk hapus
6. Verifikasi transaksi hilang
7. Cek saldo berubah (jika ada perubahan)

---

## 🔍 Troubleshooting

### ❌ Error: "Unknown column 'catatan' in 'field list'"
**Solusi**: 
- Jalankan SQL migration untuk tambahkan column
- Atau edit data dan abaikan field catatan (optional)

### ❌ Error: "Transaksi tidak ditemukan saat edit"
**Solusi**: 
- Pastikan Anda login dengan user yang membuat transaksi
- ID transaksi di URL harus benar
- Transaksi belum dihapus

### ❌ Form edit kosong saat klik Edit
**Solusi**: 
- Refresh browser
- Check console browser untuk error message
- Verifikasi database connection

### ❌ Tombol Edit/Hapus tidak muncul
**Solusi**: 
- Refresh browser dengan Ctrl+Shift+R (clear cache)
- Check file `transaksi.php` dan `riwayat_transaksi.php`
- Pastikan update sudah tersimpan

---

## 📞 Quick Support

### Jika ada error database saat test:
1. Buka `config/koneksi.php`
2. Verifikasi host, username, password, database name
3. Test koneksi dengan: `$koneksi->ping();`

### Jika button tidak bekerja:
1. Check JavaScript console (F12)
2. Verify link URL benar
3. Check file permissions (755 untuk folder, 644 untuk file)

### Jika saldo tidak update setelah hapus:
1. Refresh halaman
2. Verifikasi database sudah terupdate
3. Check calculation logic di dashboard.php

---

## 📊 Database Schema Verification

Jalankan query ini untuk verify struktur:

```sql
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'transaksi' AND TABLE_SCHEMA = 'mysavings'
ORDER BY ORDINAL_POSITION;
```

Expected output:
```
id              | int(11)         | NO
user_id         | int(11)         | NO
jenis           | enum(...)       | NO
jumlah          | decimal(12,2)   | NO
keterangan      | varchar(200)    | NO
kategori        | varchar(50)     | NO
tanggal         | date            | NO
catatan         | text            | YES ← HARUS ADA
created_at      | timestamp       | NO
```

---

## 🎉 Selesai!

Setelah semua test berhasil, aplikasi siap untuk production!

**Fitur yang sudah siap**:
- ✅ Login & Register
- ✅ Dashboard
- ✅ Catat Transaksi (tambah)
- ✅ **Edit Transaksi (NEW)**
- ✅ **Hapus Transaksi (NEW)**
- ✅ Riwayat Transaksi
- ✅ Laporan Keuangan
- ✅ Profil User
- ✅ **Settings (NEW)**
- ✅ Lupa Password

---

**Last Updated**: 2026-06-18
**Version**: 2.0.0
