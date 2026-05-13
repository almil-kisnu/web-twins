<!-- TAB: REKAP ABSENSI (DB Aggregation) -->
<div id="view-rekap" style="display: none;">
    <form method="GET" action="{{ route('absensi.index') }}" class="filter-bar">
        <input type="hidden" name="active_tab" value="rekap">
        @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
            <select name="store_id" class="inline-select" style="min-width:150px; height:42px;">
                @if(Auth::user()->role === 'owner')
                    <option value="all" {{ $store_id === 'all' ? 'selected' : '' }}>Semua Outlet</option>
                @endif
                @foreach($outlets as $o)
                    <option value="{{ $o->uuid }}" {{ $store_id == $o->uuid ? 'selected' : '' }}>{{ $o->nama }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="store_id" value="{{ $store_id }}">
        @endif
        <label style="font-weight:600;font-size:14px;color:#333;">Periode:</label>
        <input type="month" name="rekap_bulan" value="{{ $rekapBulan }}" style="min-width:160px;">
        <button type="submit" class="btn-action" style="padding:8px 16px;font-size:13px;">
            <iconify-icon icon="solar:magnifer-linear"></iconify-icon> Tampilkan
        </button>
    </form>

    @php
        $totH = $rekap->sum('total_hadir');
        $totI = $rekap->sum('total_izin');
        $totA = $rekap->sum('total_alpha');
        $totR = $rekap->sum('total_record');
    @endphp

    <div class="rekap-card">
        <div class="rekap-stat">
            <div class="number" style="color: #2E7D32;">{{ $totH }}</div>
            <div class="label">Total Hadir</div>
        </div>
        <div class="rekap-stat">
            <div class="number" style="color: #E65100;">{{ $totI }}</div>
            <div class="label">Total Izin</div>
        </div>
        <div class="rekap-stat">
            <div class="number" style="color: #C62828;">{{ $totA }}</div>
            <div class="label">Total Alpha</div>
        </div>
        <div class="rekap-stat">
            <div class="number" style="color: #0081C9;">{{ $totR }}</div>
            <div class="label">Total Record</div>
        </div>
    </div>

    <table class="fitur-table">
        <thead>
            <tr>
                <th>NO</th>
                <th>KARYAWAN</th>
                <th class="text-center" style="color:#2E7D32;">HADIR</th>
                <th class="text-center" style="color:#E65100;">IZIN</th>
                <th class="text-center" style="color:#C62828;">ALPHA</th>
                <th class="text-center">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap as $idx => $r)
                <tr class="searchable-row">
                    <td>{{ $rekap->firstItem() + $idx }}</td>
                    <td style="font-weight: 600;">{{ $r->username }}</td>
                    <td class="text-center">
                        <span class="status-badge status-hadir">{{ $r->total_hadir }}</span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge status-izin">{{ $r->total_izin }}</span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge status-alpha">{{ $r->total_alpha }}</span>
                    </td>
                    <td class="text-center" style="font-weight:700;">{{ $r->total_record }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">Belum ada data rekap untuk periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($rekap->hasPages())
        <div class="pagination-container">
            {{ $rekap->appends(request()->except('page'))->links() }}
        </div>
    @endif
</div>
