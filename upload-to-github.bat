@echo off
echo ========================================
echo    TUTORIAL UPLOAD KE GITHUB
echo ========================================
echo.

echo 1. Konfigurasi Git untuk Windows...
git config --global core.autocrlf true
echo.

echo 2. Cek status Git...
git status
echo.

echo 3. Reset staging area (jika ada warning LF/CRLF)...
git reset
echo.

echo 4. Tambahkan semua file...
git add .
echo.

echo 5. Commit perubahan...
git commit -m "Initial commit: Upload project sneat01"
echo.

echo 6. Cek remote repository...
git remote -v
echo.

echo ========================================
echo    LANGKAH SELANJUTNYA:
echo ========================================
echo.
echo 1. Buka https://github.com
echo 2. Buat repository baru
echo 3. Jalankan perintah berikut:
echo    git remote add origin https://github.com/farzcode/pos-restoran
echo    git push -u origin main
echo.
echo Ganti USERNAME dan REPOSITORY_NAME sesuai akun GitHub Anda
echo.
pause 