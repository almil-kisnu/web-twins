# Panduan Pembuatan Menu Jadwal & Absensi (Laravel Backoffice)

Panduan ini disusun untuk AI Agent atau Developer yang akan membuat menu **Manajemen Karyawan (Jadwal & Absensi)** di sistem Backoffice berbasis **Laravel**. Sistem ini terhubung ke database PostgreSQL yang sama dengan aplikasi POS Kasir (Flutter + PowerSync).

Menu ini **hanya dapat diakses oleh Owner** (Pemilik Bisnis).

---

## 1. Konteks Arsitektur & Aturan Dasar
- **Framework**: Laravel (versi 10/11) sebagai antarmuka Web Admin / Backoffice.
- **Database**: PostgreSQL (Supabase). Laravel langsung terkoneksi ke database utama.
- **Primary Key**: Menggunakan `UUID` (`gen_random_uuid()`). Semua Eloquent Model di Laravel **wajib** menggunakan konfigurasi UUID:
  ```php
  protected $keyType = 'string';
  public $incrementing = false;
  
  protected static function boot() {
      parent::boot();
      static::creating(function ($model) {
          if (empty($model->{$model->getKeyName()})) {
              $model->{$model->getKeyName()} = (string) Str::uuid();
          }
      });
  }
  ```
- **Hak Akses (Role)**: Hanya untuk **Owner**. Dalam skema ini, Owner adalah user yang tidak terikat pada satu toko tertentu secara eksklusif (misal: tabel `users` dimana `store_id IS NULL` atau divalidasi melalui Role System yang sudah ada).
- **Pemilihan Toko**: Karena Owner memiliki akses global, setiap halaman harus menyediakan **Dropdown Filter Toko (Store Picker)** agar Owner dapat melihat jadwal dan absensi spesifik per cabang (`store_id`).

---

## 2. Struktur Tabel Terkait (Sesuai `panduan.md`)
AI Agent harus membuat/menyesuaikan Model Laravel untuk tabel-tabel berikut:
1. `shifts` (`uuid`, `nama`, `waktu_mulai`, `waktu_selesai`)
2. `jadwal` (`uuid`, `user_id`, `shift_id`, `store_id`, `hari_dalam_minggu`)
3. `absensi` (`uuid`, `jadwal_id`, `store_id`, `tanggal_absensi`, `waktu_check_in`, `status_kehadiran`)
4. `users` (`uuid`, `username`, `store_id`, `status_aktif`)
5. `store` (`uuid`, `nama`)

---

## 3. Rincian 4 Fitur Utama (Menu)

### A. Master Shift (`/admin/shifts`)
*Merujuk pada UI `shift_screen.dart` di Flutter.*
- **Fungsi**: Mengelola jam kerja (Pagi, Siang, Malam).
- **Aksi**: CRUD (Create, Read, Update, Delete).
- **Validasi**: Format waktu `HH:MM:SS` untuk `waktu_mulai` dan `waktu_selesai`. Pastikan shift tidak bisa dihapus jika masih berelasi dengan tabel `jadwal` (Restrict on Delete).

### B. Jadwal Karyawan (`/admin/jadwal`)
*Merujuk pada UI `jadwal_karyawan_screen.dart` di Flutter.*
- **Fungsi**: Memetakan karyawan ke shift tertentu pada hari tertentu.
- **Aksi**: CRUD Jadwal.
- **Form Input**:
  - Pilih Toko (`store_id`)
  - Pilih Karyawan (`user_id` di mana `users.store_id` = toko yang dipilih)
  - Pilih Shift (`shift_id`)
  - Pilih Hari (1 = Senin ... 7 = Minggu). *Gunakan multiple select agar bisa set jadwal beberapa hari sekaligus.*
- **Validasi**: Mencegah duplikasi jadwal (Satu karyawan tidak boleh di-assign ke lebih dari 1 shift pada hari yang sama di toko yang sama).

### C. Riwayat Absensi (`/admin/absensi/riwayat`)
*Merujuk pada UI `riwayat_absensi_screen.dart` di Flutter.*
- **Fungsi**: Memantau log kehadiran harian karyawan.
- **Aksi**: Read (Daftar Absensi), Update (Ubah Status).
- **Fitur Khusus**:
  - **Filter**: Berdasarkan Bulan/Tanggal, Toko, dan Nama Karyawan.
  - **Edit Status Kehadiran**: Owner dapat mengubah status `hadir`, `izin`, atau `alpha` secara manual apabila terjadi kesalahan dari sisi kasir/aplikasi.
- **High Performance Requirements**: 
  - Gunakan **Server-Side Pagination** (misal: Yajra DataTables atau Laravel built-in `paginate()`). Dilarang meload seluruh data ke View.
  - Gunakan **Eager Loading**: `Absensi::with(['jadwal.user', 'jadwal.shift', 'store'])`.

### D. Rekap Absensi (`/admin/absensi/rekap`)
*Merujuk pada UI `rekap_absensi_screen.dart` di Flutter.*
- **Fungsi**: Laporan agregasi bulanan untuk penggajian atau evaluasi karyawan.
- **Aksi**: Read (View Report), Export (opsional: Excel/PDF).
- **Fitur Khusus**: Menampilkan Total Hadir, Total Izin, dan Total Alpha per karyawan dalam satu bulan.
- **High Performance Requirements (PENTING)**:
  Jangan menarik semua baris absensi ke PHP (koleksi) lalu menghitung loop manual karena akan memakan memori jika data jutaan baris (Out of Memory). Gunakan Query Aggregation di level database. 
  
  *Contoh Query Builder (Laravel):*
  ```php
  $rekap = DB::table('users as u')
      ->leftJoin('jadwal as j', function ($join) use ($store_id) {
          $join->on('j.user_id', '=', 'u.uuid')
               ->where('j.store_id', '=', $store_id);
      })
      ->leftJoin('absensi as a', function ($join) use ($bulan_terpilih) {
          $join->on('a.jadwal_id', '=', 'j.uuid')
               ->where('a.tanggal_absensi', 'like', $bulan_terpilih . '%');
      })
      ->where('u.store_id', $store_id)
      ->where('u.status_aktif', true)
      ->select(
          'u.uuid',
          'u.username',
          DB::raw("SUM(CASE WHEN a.status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as total_hadir"),
          DB::raw("SUM(CASE WHEN a.status_kehadiran = 'izin' THEN 1 ELSE 0 END) as total_izin"),
          DB::raw("SUM(CASE WHEN a.status_kehadiran = 'alpha' THEN 1 ELSE 0 END) as total_alpha")
      )
      ->groupBy('u.uuid', 'u.username')
      ->orderBy('u.username', 'asc')
      ->paginate(50);
  ```

---

## 4. Keamanan dan Performa Ekstra (High Performance Notes)
1. **Database Indexing**: Instruksikan AI untuk membuat migration tambahan guna menambahkan `INDEX` pada kolom pencarian yang sering digunakan:
   - `CREATE INDEX idx_absensi_tanggal ON absensi(tanggal_absensi);`
   - `CREATE INDEX idx_jadwal_user_store ON jadwal(user_id, store_id);`
2. **Tanpa Sinkronisasi Ganda**: Karena Laravel mengubah langsung ke PostgreSQL, data akan otomatis ter-sinkronisasi ke aplikasi mobile melalui **PowerSync cloud** (sebagaimana tercatat di `panduan.md`). Tidak perlu membuat API khusus untuk push ke mobile.
3. **Caching**: Pada halaman **Rekap Absensi**, jika data periode sebelumnya (bulan lalu) diakses, sangat disarankan menambahkan layer cache (Redis / File) selama 1-24 jam karena data bulan lalu bersifat statis (tidak berubah).
4. **UX**: Desain tampilan Web Admin harus informatif. Gunakan label warna untuk membedakan status (Hijau = Hadir, Kuning/Oranye = Izin, Merah = Alpha) sesuai gaya estetika pada Flutter.

Dengan panduan ini, AI Agent akan dapat men-generate Controller, Model, View (Blade/Inertia), dan Routing Laravel yang solid, efisien, seragam dengan Frontend Mobile, serta mampu menangani beban data yang sangat besar.
