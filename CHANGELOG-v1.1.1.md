# MM Web Monitoring v1.1.1

## Tanggal Rilis: 26 Juni 2024

### Perbaikan Bug

- **Silent Monitoring**: Implementasi silent monitoring saat plugin diaktifkan kembali atau server up kembali. Plugin akan secara otomatis memeriksa semua website yang aktif dengan penundaan progresif untuk menghindari beban server.

- **Bulk Actions**: Menghapus tombol bulk check now, bulk start, bulk pause pada halaman all websites yang tidak berfungsi dengan baik. Sebagai gantinya, fitur ini diimplementasikan melalui dropdown bulk actions WordPress standar.

- **UI Fix**: Memperbaiki bug pada dropdown bulk actions di halaman all websites yang terpotong atau tidak terlihat dengan benar.

### Perubahan Teknis

- Menambahkan hook `mmwm_silent_check` untuk menangani pemeriksaan silent saat plugin diaktifkan kembali.
- Menambahkan fungsi `run_silent_monitoring` di class activator untuk menjalankan pemeriksaan silent.
- Menambahkan fungsi `handle_silent_check` di class cron untuk menangani pemeriksaan silent.
- Memperbaiki styling CSS untuk dropdown bulk actions.
- Mengimplementasikan opsi bulk actions (Bulk Check Now, Bulk Stop, Bulk Pause, Bulk Start) melalui dropdown WordPress standar.

### Catatan Pengembang

Versi ini fokus pada perbaikan bug dan peningkatan stabilitas plugin. Fitur bulk actions yang sebelumnya menggunakan tombol kustom telah diimplementasikan ulang menggunakan dropdown bulk actions WordPress standar untuk meningkatkan kompatibilitas dan keandalan.