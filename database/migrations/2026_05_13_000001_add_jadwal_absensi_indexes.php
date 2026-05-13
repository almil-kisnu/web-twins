<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Index untuk pencarian absensi berdasarkan tanggal (paling sering difilter)
        DB::statement('CREATE INDEX IF NOT EXISTS idx_absensi_tanggal ON absensi(tanggal_absensi)');
        
        // Index untuk pencarian absensi berdasarkan store_id
        DB::statement('CREATE INDEX IF NOT EXISTS idx_absensi_store ON absensi(store_id)');
        
        // Composite index untuk pencarian jadwal berdasarkan user + store
        DB::statement('CREATE INDEX IF NOT EXISTS idx_jadwal_user_store ON jadwal(user_id, store_id)');
        
        // Index untuk pencarian jadwal berdasarkan shift_id
        DB::statement('CREATE INDEX IF NOT EXISTS idx_jadwal_shift ON jadwal(shift_id)');
        
        // Composite index untuk absensi: jadwal_id + tanggal (untuk rekap bulanan)
        DB::statement('CREATE INDEX IF NOT EXISTS idx_absensi_jadwal_tanggal ON absensi(jadwal_id, tanggal_absensi)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_absensi_tanggal');
        DB::statement('DROP INDEX IF EXISTS idx_absensi_store');
        DB::statement('DROP INDEX IF EXISTS idx_jadwal_user_store');
        DB::statement('DROP INDEX IF EXISTS idx_jadwal_shift');
        DB::statement('DROP INDEX IF EXISTS idx_absensi_jadwal_tanggal');
    }
};
