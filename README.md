<<<<<<< HEAD
Realtime Collaborative Document Editor

Deskripsi:
Aplikasi ini merupakan sistem realtime collaborative document editor berbasis web yang dibuat menggunakan Laravel 12.
Aplikasi ini memungkinkan banyak user mengedit dokumen secara bersamaan secara realtime dengan fitur live cursor, autosave, online users, dan version history.

Teknologi yang digunakan:
Laravel 12
PHP
MySQL
JavaScript
Tailwind CSS
Laravel Echo
Pusher
Bootstrap
Laragon

Fitur Aplikasi:
Register User
Login & Logout
Create Document
Edit Document Realtime
Delete Document
Live Collaborative Editing
Live Cursor Tracking
Online Users Detection
Autosave Document
Conflict Resolution
Version History
Realtime Synchronization

Cara Menjalankan Project
1. Clone Project
git clone <repository-url>
2. Masuk ke Folder Project
cd collaborative-editor
3. Install Dependency Laravel
composer install
4. Install Dependency Frontend
npm install
5. Copy File Environment
cp .env.example .env
6. Generate APP KEY
php artisan key:generate
7. Atur Database di File .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=collab_doc
DB_USERNAME=root
DB_PASSWORD=
8. Jalankan Migration
php artisan migrate
9. Jalankan Laravel Reverb
php artisan reverb:start
10. Jalankan Vite
npm run dev
11. Jalankan Server Laravel
php artisan serve
12. Buka Project di Browser
http://127.0.0.1:8000