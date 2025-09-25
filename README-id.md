# PHP WebSocket Chat

Ini adalah aplikasi obrolan (chat) real-time sederhana yang dibangun menggunakan WebSocket dengan server backend yang ditulis sepenuhnya dalam PHP menggunakan fungsi socket tingkat rendah. Proyek ini berfungsi sebagai contoh dasar bagaimana protokol WebSocket dapat diimplementasikan dari awal di PHP tanpa pustaka eksternal seperti Ratchet.

## Fitur

- Obrolan real-time antar beberapa klien.
- Server WebSocket kustom yang ditulis dengan PHP murni.
- Antarmuka pengguna yang bersih dan responsif menggunakan Bootstrap 4.
- Sistem login sederhana berbasis sesi PHP.
- Tidak ada ketergantungan pada pustaka JavaScript eksternal (menggunakan Vanilla JS).

## Teknologi yang Digunakan

- **Backend**: PHP 7.x atau lebih baru
- **Frontend**: HTML5, Bootstrap 4, JavaScript (ES6)
- **Protokol**: WebSocket (RFC 6455)

## Cara Menjalankan

1.  **Prasyarat**: Pastikan Anda memiliki PHP yang terinstal dan dapat diakses dari baris perintah (command line). Anda juga memerlukan server web seperti Apache (misalnya, dari XAMPP) untuk menyajikan file frontend.

2.  **Tempatkan File**: Letakkan semua file proyek di dalam direktori root server web Anda (misalnya, `d:\xampp\htdocs\php-websocket`).

3.  **Jalankan Server WebSocket**: Buka terminal atau command prompt, navigasikan ke direktori proyek, dan jalankan perintah berikut:
    ```sh
    php server.php
    ```
    Server sekarang akan berjalan dan mendengarkan koneksi di `ws://localhost:8889`.

4.  **Akses Aplikasi**: Buka browser web Anda dan navigasikan ke halaman login:
    `http://localhost/php-websocket/login.php`

5.  **Login dan Mulai Mengobrol**: Masukkan nama pengguna apa pun dan klik "Login". Anda akan diarahkan ke ruang obrolan. Buka beberapa tab atau browser untuk mensimulasikan banyak pengguna.

## Struktur File

- `server.php`: Skrip untuk memulai server WebSocket.
- `index.php`: Halaman antarmuka obrolan utama.
- `login.php`: Halaman login untuk mengatur sesi pengguna.
- `classes/ChatServer.php`: Logika inti untuk menangani koneksi WebSocket, handshake, dan penyiaran pesan.
- `classes/ChatClient.php`: Kelas yang merepresentasikan setiap klien yang terhubung.
- `classes/Utility.php`: Kumpulan fungsi bantuan untuk mem-parsing header, cookie, dan data sesi.
- `js/script.js`: Kode JavaScript sisi klien untuk menangani koneksi WebSocket dan memanipulasi DOM.
