# Naskah Presentasi Demo: AlnovStore Game Top Up Microservices

**Durasi Estimasi:** 10-15 Menit
**Pembagian Peran:**
- **Speaker 1 (Frontend & UI Demo):** Fokus mendemokan antarmuka website (UI/UX) dan simulasi transaksi sebagai pembeli (user).
- **Speaker 2 (Postman - Service Dasar):** Fokus menjelaskan arsitektur Microservices dan mendemokan API Player Service & Game Item Service via Postman.
- **Speaker 3 (Postman - Order & Integrasi):** Fokus mendemokan Order Service, integrasi antar service (End-to-End), fitur tracking, dan penutup.

---

## [Speaker 1] - Pembukaan & Demo Website (Frontend)

**(Menampilkan layar halaman utama web `index.html`)**

**Speaker 1:**
"Halo semuanya, perkenalkan kami dari Kelompok 2. Hari ini kami akan mendemokan project UTS Sistem Terintegrasi Antar Layanan kami, yaitu **AlnovStore**. 

AlnovStore adalah platform top-up game premium yang dibangun menggunakan arsitektur *Microservices*. Dari sisi Frontend, kami merancang antarmuka yang modern, dinamis, dan responsif. Seperti yang bisa dilihat di halaman utama ini, terdapat *slider banner* promo, dan katalog game yang bisa difilter, seperti Mobile Legends, Free Fire, Valorant, PUBG, dan League of Legends.

Sekarang, saya akan memposisikan diri saya sebagai seorang **Pembeli** yang ingin melakukan top-up. 

1. Pertama, saya akan memilih game **Mobile Legends**. *(Klik card Mobile Legends)*
2. Di halaman top-up ini, sistem secara dinamis menyesuaikan tampilan. Anda bisa melihat judulnya Mobile Legends, instruksinya meminta **User ID dan Zone ID**, dan nominalnya menampilkan mata uang **Diamond** beserta icon-nya. Nominal ini juga kami buat serealistis mungkin sesuai harga in-game aslinya.
3. Saya masukkan User ID `12345678` dan Zone ID `1234`. Lalu klik **Cek ID**. 
4. *(Tunggu loading)* Nah, ID berhasil divalidasi dan muncul nama player 'NOVAL_GAMING'. Validasi ini sebenarnya terintegrasi dengan backend API Player Service.
5. Selanjutnya saya pilih nominal **28 Diamond**. Di bawah langsung tertera total harganya, yaitu Rp 7.500.
6. Saya klik **Beli Sekarang**. *(Tunggu loading)*
7. Transaksi berhasil! Muncul struk digital (modal success) yang mencatat detail pembelian. Saldo dompet saya di pojok kanan atas juga otomatis berkurang dari Rp 500.000 menjadi Rp 492.500.

Sebagai perbandingan, jika saya kembali ke halaman utama dan memilih game **Valorant** *(Buka halaman Valorant)*. Sistem akan cerdas menyesuaikan:
- Kolom Zone ID **hilang** (karena Valorant hanya butuh Riot ID).
- Nominal berubah menjadi **VP (Valorant Points)** dengan harga dan icon yang sesuai.

Semua interaksi di website ini tidak berdiri sendiri, melainkan berkomunikasi dengan 3 service mandiri di backend. Untuk penjelasan bagaimana API backend ini bekerja, akan dilanjutkan oleh rekan saya, [Nama Speaker 2]."

---

## [Speaker 2] - Arsitektur & Postman Demo (Player & Item Service)

**(Berpindah ke layar aplikasi Postman, menampilkan Collection "AlnovStore - Game Top Up Microservices")**

**Speaker 2:**
"Terima kasih, [Nama Speaker 1]. Di bagian backend, project kami menggunakan arsitektur Microservices yang terdiri dari 3 service terpisah yang berjalan di port yang berbeda:
1. **Player Service (Port 8001)**
2. **Game Item Service (Port 8002)**
3. **Order Service (Port 8003)**

Untuk mendemokan API ini, kami telah membuat Postman Collection terstruktur yang bisa dilihat di layar. Semua URL sudah dikonfigurasi agar mudah di-testing.

Mari kita lihat **Player Service (Port 8001)** terlebih dahulu. Service ini khusus mengelola data akun pengguna.
- Saya akan jalankan request **'Get Player Profile'** (`GET /players/1/profile`). *(Klik Send)* 
- Di sini kita bisa lihat responsenya berupa JSON berisi data diri player, seperti nama 'NOVAL_GAMING' dan saldo *wallet*-nya. Endpoint inilah yang tadi dipanggil oleh web saat [Nama Speaker 1] menekan tombol 'Cek ID'.
- Kami juga memiliki endpoint **'Check Player Balance'** untuk memastikan apakah saldo dompet player mencukupi sebelum transaksi dilakukan.

Selanjutnya, **Game Item Service (Port 8002)**. Service ini bertanggung jawab atas katalog produk (nominal game) dan stok.
- Jika saya jalankan request **'Get Trending Items'** (`GET /items/trending`). *(Klik Send)*
- API akan merespon dengan daftar item (nominal top-up) beserta harganya. Data dari response inilah yang digunakan oleh Frontend untuk menampilkan pilihan nominal secara dinamis.
- Service ini juga memiliki endpoint **'Validate Stock'** (`GET /items/1/validate-stock`) yang fungsinya mengecek ketersediaan item di database sebelum pesanan diproses.

Lalu, bagaimana pesanan sebenarnya dibuat dan bagaimana ketiga service ini saling berkomunikasi? Bagian integrasi antar layanan ini akan dijelaskan oleh rekan saya, [Nama Speaker 3]."

---

## [Speaker 3] - Order Service, Integrasi (End-to-End), & Penutup

**(Masih di layar Postman, fokus ke folder "3. Order Service" dan "4. Alur Top Up (End-to-End)")**

**Speaker 3:**
"Terima kasih, [Nama Speaker 2]. Sekarang kita masuk ke **Order Service (Port 8003)**. Ini adalah inti dari sistem transaksi top-up kami.

Saat user di website mengklik tombol 'Beli Sekarang', Frontend akan mengirimkan *request* ke endpoint **'Create Order'** (`POST /orders`).
*(Buka request Create Order, perlihatkan body JSON)*

Di request body ini, kita mengirimkan `player_id`, `game_item_id`, dan `quantity`. Mari kita send request-nya. *(Klik Send)*
Status *201 Created*! Pesanan berhasil dibuat.

**Apa yang terjadi di balik layar saat endpoint ini dipanggil?** Di sinilah letak 'Sistem Terintegrasi Antar Layanan' terjadi secara *real-time*:
1. Order Service bertindak sebagai **Consumer**. Ia pertama kali melakukan *HTTP request* secara internal ke **Player Service (Port 8001)** untuk mengecek ketersediaan saldo.
2. Kemudian, ia me-request ke **Game Item Service (Port 8002)** untuk memvalidasi stok barang.
3. Jika saldo dan stok aman, Order Service akan mengeksekusi pemotongan saldo di Player Service, mengurangi stok di Item Service, lalu menyimpan status pesanan menjadi 'success'.
Semua proses komunikasi antar microservice ini terjadi di *backend* secara *seamless* tanpa disadari oleh user.

Terakhir, mari kita demokan fitur pelacakan pesanan baik untuk user maupun Admin. 
Di Postman, kita punya endpoint **'Get Player Orders'** (`GET /orders/player/1`). *(Klik Send)*
Di sini akan muncul riwayat transaksi khusus dari Player tertentu.
*(Opsional: Pindah sebentar ke layar web browser)*
Di website, fitur ini bisa diakses melalui menu navigasi **'Lacak Pesanan'**. Tinggal masukkan Player ID dan riwayat akan muncul dengan rapi.

Sementara itu, untuk keperluan rekapitulasi, Admin bisa menembak endpoint **'Get Sales Recap'** (`GET /orders/recap`) *(Kembali ke Postman dan klik Send)* untuk melihat total pendapatan, total order, dan jumlah transaksi yang sukses atau gagal secara keseluruhan.

**Penutup:**
Sebagai kesimpulan, penggunaan arsitektur microservices pada AlnovStore memungkinkan pengembangan yang jauh lebih modular. Service memiliki database dan logic-nya masing-masing. Jika suatu saat kami ingin menambahkan fitur sistem *Point Reward* atau integrasi ke *Payment Gateway* pihak ketiga, kami cukup membuat satu service baru tanpa mengganggu service Player, Item, atau Order yang sudah stabil berjalan.

Sekian demo dari Kelompok 2 untuk project AlnovStore Microservices. Terima kasih atas perhatiannya, kami kembalikan ke Bapak/Ibu Dosen/Asisten."
