<!-- TAB: JADWAL KARYAWAN -->
<div id="view-jadwal" style="display: none;">
    <table class="fitur-table">
        <thead>
            <tr>
                <th>KARYAWAN</th>
                <th>OUTLET</th>
                <th>SHIFT</th>
                <th>HARI</th>
                <th style="width: 100px; text-align: center;">AKSI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $j)
                <tr class="searchable-row" data-shift="{{ $j->shift_id }}">
                    <td style="font-weight: 600;">
                        {{ $j->user->name ?? '-' }}<br>
                        <small style="font-weight:normal;color:#666;">{{ $j->user->operator->nama ?? 'Karyawan' }}</small>
                    </td>
                    <td>{{ $j->store->nama ?? '-' }}</td>
                    <td>
                        <span style="font-weight:600;color:#0081C9;">{{ $j->shift->nama ?? '-' }}</span><br>
                        @if($j->shift)
                            <small style="color:#666;">{{ \Carbon\Carbon::parse($j->shift->waktu_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($j->shift->waktu_selesai)->format('H:i') }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="hari-chip">{{ \App\Models\Jadwal::hariName($j->hari_dalam_minggu) }}</span>
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <button class="btn-filter"
                                style="width:32px;height:32px;border-radius:8px;color:#FBC02D;border-color:#FFF9C4;"
                                onclick="openEditJadwal('{{ $j->uuid }}', '{{ $j->shift_id }}', {{ $j->hari_dalam_minggu }})"
                                title="Edit">
                                <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                            </button>
                            <button class="btn-filter"
                                style="width:32px;height:32px;border-radius:8px;color:#D9534F;border-color:#ffcccc;"
                                onclick="globalDelete('/absensi/jadwal/{{ $j->uuid }}', '{{ $j->user->name ?? '' }}')"
                                title="Hapus">
                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state">Belum ada jadwal karyawan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>