# Tutorial Upload Project ke GitHub

## Langkah 1: Persiapan Awal

### 1.1 Pastikan Git sudah terinstall
```bash
git --version
```
Jika belum terinstall, download dari: https://git-scm.com/

### 1.2 Konfigurasi Git (jika belum)
```bash
git config --global user.name "farzcode"
git config --global user.email "faridkurniawan204@gmail.com"
```

### 1.3 Konfigurasi Line Endings untuk Windows (PENTING!)
Untuk menghindari warning LF/CRLF, jalankan:
```bash
git config --global core.autocrlf true
```

## Langkah 2: Cek Status Repository

### 2.1 Cek status Git saat ini
```bash
git status
```

### 2.2 Cek remote repository (jika ada)
```bash
git remote -v
```

## Langkah 3: Buat Repository di GitHub

### 3.1 Buka GitHub.com dan login
- Kunjungi https://github.com
- Login ke akun Anda

### 3.2 Buat repository baru
- Klik tombol "New" atau "+" di pojok kanan atas
- Pilih "New repository"
- Isi nama repository (misal: "sneat01")
- Pilih "Public" atau "Private"
- **JANGAN** centang "Initialize this repository with a README"
- Klik "Create repository"

## Langkah 4: Upload Project ke GitHub

### 4.1 Tambahkan semua file ke staging area
```bash
git add .
```

### 4.2 Commit perubahan
```bash
git commit -m "Initial commit: Upload project sneat01"
```

### 4.3 Tambahkan remote repository
```bash
git remote add origin https://github.com/USERNAME/REPOSITORY_NAME.git
```
Ganti `USERNAME` dengan username GitHub Anda dan `REPOSITORY_NAME` dengan nama repository yang Anda buat.

### 4.4 Push ke GitHub
```bash
git push -u origin main
```
atau jika menggunakan branch master:
```bash
git push -u origin master
```

## Langkah 5: Verifikasi Upload

### 5.1 Cek di GitHub
- Buka repository Anda di GitHub
- Pastikan semua file sudah terupload

### 5.2 Cek status lokal
```bash
git status
```
Seharusnya menampilkan "working tree clean"

## Langkah 6: Update Project Selanjutnya

### 6.1 Setelah melakukan perubahan
```bash
git add .
git commit -m "Deskripsi perubahan"
git push
```

## Troubleshooting

### Warning LF/CRLF (Line Endings)
Jika muncul warning seperti:
```
warning: in the working copy of 'file.php', LF will be replaced by CRLF the next time Git touches it
```

**Solusi:**
```bash
# Konfigurasi Git untuk Windows
git config --global core.autocrlf true

# Reset semua file yang sudah di-staging
git reset

# Tambahkan ulang file
git add .

# Commit
git commit -m "Initial commit: Upload project sneat01"
```

### Jika ada error "fatal: remote origin already exists"
```bash
git remote remove origin
git remote add origin https://github.com/USERNAME/REPOSITORY_NAME.git
```

### Jika ada error authentication
- Gunakan Personal Access Token
- Atau setup SSH key

### Jika ada conflict
```bash
git pull origin main
# Resolve conflict manual
git add .
git commit -m "Resolve conflict"
git push
```

## Tips Tambahan

### 1. Buat README.md
```bash
echo "# Sneat01 Project" > README.md
echo "Deskripsi project Anda di sini" >> README.md
git add README.md
git commit -m "Add README"
git push
```

### 2. Cek branch yang aktif
```bash
git branch
```

### 3. Buat branch baru
```bash
git checkout -b nama-branch
git push -u origin nama-branch
```

## File .gitignore

Project Anda sudah memiliki file `.gitignore` yang baik. Pastikan file sensitif seperti:
- `config.php` (jika berisi password database)
- File log
- Cache files
- Environment files

Tidak teruploaded ke GitHub.

## Selesai!

Project Anda sekarang sudah tersimpan di GitHub dan bisa diakses oleh siapa saja (jika public) atau hanya Anda (jika private).

Untuk update selanjutnya, cukup jalankan:
```bash
git add .
git commit -m "Update message"
git push
``` 