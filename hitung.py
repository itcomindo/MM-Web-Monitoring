import os

# --- KONFIGURASI ---
NAMA_FILE_OUTPUT = "all-files.txt"
BATAS_BARIS_TIDAK_EFISIEN = 300

# Daftar ekstensi file yang akan DIABAIKAN (case-insensitive)
# Termasuk file media, log, dan teks biasa.
EKSTENSI_DIABAIKAN = {
    # Media
    '.jpg', '.jpeg', '.png', '.gif', '.bmp', '.svg', '.webp', '.ico',
    '.mp3', '.wav', '.ogg', '.flac',
    '.mp4', '.mov', '.avi', '.mkv',
    # Dokumen & Log
    '.log', '.md', '.txt', '.pdf', '.doc', '.docx', '.xls', '.xlsx',
    # Lain-lain
    '.zip', '.rar', '.exe', '.dll', '.db',
}

# Daftar file yang akan DIABAIKAN berdasarkan nama
NAMA_FILE_DIABAIKAN = {
    'hitung.py',       # Abaikan script ini sendiri
    NAMA_FILE_OUTPUT,  # Abaikan file outputnya sendiri
}
# --- AKHIR KONFIGURASI ---

def hitung_baris_file(path_file):
    """Membuka file dan menghitung jumlah barisnya."""
    try:
        with open(path_file, 'r', encoding='utf-8', errors='ignore') as f:
            return sum(1 for _ in f)
    except Exception as e:
        # Jika terjadi error saat membaca file, kembalikan pesan error
        return f"[Error Baca File: {e}]"

def analisis_direktori(path_awal, file_output):
    """Berjalan melalui semua direktori dan subdirektori untuk menganalisis file."""
    for root, dirs, files in os.walk(path_awal):
        # Mengabaikan folder yang umum tidak berisi kode (seperti .git, .vscode, node_modules)
        # Ini akan mempercepat proses secara signifikan pada proyek besar
        dirs[:] = [d for d in dirs if d not in ['.git', '.vscode', 'node_modules', '__pycache__']]

        # Menghitung kedalaman folder untuk indentasi
        # os.path.relpath digunakan agar path dimulai dari direktori awal
        path_relatif = os.path.relpath(root, path_awal)
        if path_relatif == ".":
            depth = 0
        else:
            depth = path_relatif.count(os.sep) + 1

        indentasi = '  ' * depth

        # Menulis nama direktori (kecuali direktori root/awal)
        if depth > 0:
            file_output.write(f"{'  ' * (depth - 1)}{os.path.basename(root)}/\n")

        # Urutkan file agar hasilnya konsisten
        files.sort()
        for nama_file in files:
            # 1. Cek apakah nama file harus diabaikan
            if nama_file in NAMA_FILE_DIABAIKAN:
                continue

            # 2. Cek apakah ekstensi file harus diabaikan
            _, ekstensi = os.path.splitext(nama_file)
            if ekstensi.lower() in EKSTENSI_DIABAIKAN:
                continue

            # Jika lolos semua filter, proses file tersebut
            path_lengkap_file = os.path.join(root, nama_file)
            jumlah_baris = hitung_baris_file(path_lengkap_file)

            # Siapkan string output
            if isinstance(jumlah_baris, int):
                # Tentukan tag 'tidak efisien' jika baris melebihi batas
                tag_efisiensi = f" [tidak efisien]" if jumlah_baris > BATAS_BARIS_TIDAK_EFISIEN else ""
                info_baris = f"[{jumlah_baris} baris kode]"
                file_output.write(f"{indentasi}-- {nama_file} {info_baris}{tag_efisiensi}\n")
            else:
                # Tulis pesan error jika gagal menghitung baris
                file_output.write(f"{indentasi}-- {nama_file} {jumlah_baris}\n")

def main():
    """Fungsi utama untuk menjalankan script."""
    # Direktori tempat script ini dijalankan
    direktori_awal = '.'
    print(f"Memulai analisis direktori dari: {os.path.abspath(direktori_awal)}")
    print(f"Hasil akan disimpan di file: {NAMA_FILE_OUTPUT}")

    try:
        with open(NAMA_FILE_OUTPUT, 'w', encoding='utf-8') as f_out:
            analisis_direktori(direktori_awal, f_out)
        print("\nAnalisis Selesai!")
        print(f"Hasil telah berhasil disimpan di '{NAMA_FILE_OUTPUT}'.")
    except IOError as e:
        print(f"\nError: Gagal menulis ke file output. {e}")

if __name__ == "__main__":
    main()