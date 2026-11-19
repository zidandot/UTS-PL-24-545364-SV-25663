# Pemantauan Aplikasi Perpustakaan

## Menjalankan aplikasi dan pemantauan

Jika `.env` belum ada, salin `.env.example` menjadi `.env`. Isi variabel basis
data sesuai konfigurasi aplikasi dan tetapkan `GRAFANA_ADMIN_PASSWORD` dengan
password unik yang kuat. Jangan menimpa `.env` yang sudah ada, commit file itu,
atau membagikan password tersebut.

Jalankan semua layanan dari root repository:

```powershell
docker compose up -d --build
docker compose ps
```

Tunggu sampai proses scrape berjalan, lalu buka:

- Prometheus targets: <http://localhost:9090/targets>
- Grafana: <http://localhost:3000> (user `admin`, password dari `.env`)
- Dashboard: **Application → Pemantauan Aplikasi Perpustakaan**
- Prometheus alerts: <http://localhost:9090/alerts>

Dashboard diprovisikan otomatis. CPU dan memori berasal dari cAdvisor;
permintaan per detik berasal dari Nginx exporter; status backend diuji melalui
endpoint `/api/health` oleh Blackbox exporter. Endpoint status Nginx hanya
listen di port internal 8081 dan tidak dipublikasikan ke host.

## Membuktikan alert

Pastikan semua target Prometheus berstatus **UP** terlebih dahulu. Lalu hentikan
backend dengan sengaja:

```powershell
docker compose stop backend
```

Tunggu sekitar 90 detik (scrape interval 15 detik ditambah `for: 1m`) sampai
alert `ApplicationServiceUnavailable` berstatus **FIRING** pada halaman Alerts
Prometheus. Setelah mengambil screenshot, pulihkan layanan:

```powershell
docker compose start backend
```

## Screenshot laporan

Simpan screenshot asli berikut, tampilkan URL/halaman yang relevan dan jangan
memotong status atau judulnya:

1. `/targets` Prometheus ketika seluruh target berstatus **UP**.
2. Dashboard Grafana penuh dengan keempat panel, judul berbentuk pertanyaan,
   legenda, dan satuan sumbu terlihat.
3. Halaman `/alerts` Prometheus setelah alert **FIRING**, dengan nama alert
   serta ringkasan/annotation tindakan pertama terlihat.

Cantumkan keterangan singkat di bawah setiap gambar pada PDF laporan.
