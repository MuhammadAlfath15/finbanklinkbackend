---
config:
  theme: neutral
---
erDiagram
    ADMIN ||--|| PENGATURAN_ADMIN : memiliki
    ADMIN ||--o{ PINJOL : mengelola
    ADMIN ||--o{ LAPORAN : memvalidasi
    ADMIN ||--o{ REGULASI_FILTER : membuat
    ADMIN ||--o{ ARTIKEL_EDUKASI : menulis
    USER ||--o{ LAPORAN : melaporkan
    USER ||--o{ ULASAN : menulis
    USER ||--o{ SIMULASI_PINJAMAN : menyimpan
    PINJOL ||--o{ LAPORAN : dilaporkan_pada
    PINJOL ||--o{ ULASAN : menerima
    LAPORAN ||--o{ LAPORAN_REGULASI : memiliki
    LAPORAN ||--o{ LAMPIRAN_LAPORAN : memiliki
    REGULASI_FILTER ||--o{ LAPORAN_REGULASI : mengelompokkan

    ADMIN {
        BIGINT id_admin PK
        VARCHAR nama
        VARCHAR email
        VARCHAR username
        VARCHAR password_hash
        VARCHAR role
        VARCHAR no_hp
        BOOLEAN is_active
        DATETIME created_at
        DATETIME updated_at
    }

    USER {
        BIGINT id_user PK
        VARCHAR nama
        VARCHAR email
        VARCHAR no_hp
        VARCHAR password_hash
        DATETIME created_at
        DATETIME updated_at
    }

    PENGATURAN_ADMIN {
        BIGINT id_pengaturan PK
        BIGINT id_admin FK
        BOOLEAN email_alert_darurat
        BOOLEAN ringkasan_laporan
        BOOLEAN two_factor_enabled
        DATETIME last_password_changed_at
        DATETIME updated_at
    }

    PINJOL {
        BIGINT id_pinjol PK
        VARCHAR nama_pinjol
        YEAR tahun_berdiri
        TEXT alamat
        VARCHAR website
        VARCHAR status_pinjol
        BIGINT created_by FK
        DATETIME created_at
        DATETIME updated_at
    }

    LAPORAN {
        BIGINT id_laporan PK
        BIGINT id_user FK
        VARCHAR kode_laporan
        VARCHAR judul_laporan
        TEXT isi_laporan
        VARCHAR nama_pelapor
        VARCHAR kontak_pelapor
        VARCHAR email_pelapor
        VARCHAR tautan_aplikasi
        VARCHAR foto_bukti
        VARCHAR status_laporan
        DATETIME tanggal_lapor
        BIGINT id_pinjol FK
        BIGINT id_admin_penanggung_jawab FK
        DATETIME created_at
        DATETIME updated_at
    }

    REGULASI_FILTER {
        BIGINT id_regulasi PK
        VARCHAR nama_kriteria
        VARCHAR deskripsi
        BOOLEAN is_active
        BIGINT created_by FK
        DATETIME created_at
        DATETIME updated_at
    }

    LAPORAN_REGULASI {
        BIGINT id_laporan_regulasi PK
        BIGINT id_laporan FK
        BIGINT id_regulasi FK
        VARCHAR catatan
    }

    LAMPIRAN_LAPORAN {
        BIGINT id_lampiran PK
        BIGINT id_laporan FK
        VARCHAR nama_file
        VARCHAR file_path
        VARCHAR tipe_file
        INT ukuran_file
        DATETIME uploaded_at
    }

    ULASAN {
        BIGINT id_ulasan PK
        BIGINT id_user FK
        BIGINT id_pinjol FK
        VARCHAR nama_pengulas
        INT rating
        TEXT komentar
        VARCHAR screenshot
        DATETIME created_at
    }

    ARTIKEL_EDUKASI {
        BIGINT id_artikel PK
        BIGINT id_admin FK
        VARCHAR judul
        VARCHAR kategori
        TEXT isi_artikel
        VARCHAR gambar
        DATETIME created_at
        DATETIME updated_at
    }

    SIMULASI_PINJAMAN {
        BIGINT id_simulasi PK
        BIGINT id_user FK
        DECIMAL jumlah_pinjaman
        INT tenor_hari
        DECIMAL bunga_per_hari
        DECIMAL biaya_admin
        DECIMAL cicilan_per_bulan
        DECIMAL total_bayar
        DECIMAL apr_tahunan
        DATETIME created_at
    }