<!-- TAB: MASTER SHIFT -->
<div id="view-shift" style="display: none;">
    <table class="fitur-table">
        <thead>
            <tr>
                <th>NAMA SHIFT</th>
                <th>WAKTU MULAI</th>
                <th>WAKTU SELESAI</th>
                <th style="width: 100px; text-align: center;">AKSI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shifts as $s)
                <tr class="searchable-row">
                    <td style="font-weight: 600; color: #333;">{{ $s->nama }}</td>
                    <td>{{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') }}</td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <button class="btn-filter" style="width:32px;height:32px;border-radius:8px;color:#FBC02D;border-color:#FFF9C4;"
                                onclick="openEditShift({{ json_encode($s) }})" title="Edit">
                                <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                            </button>
                            <button class="btn-filter" style="width:32px;height:32px;border-radius:8px;color:#D9534F;border-color:#ffcccc;"
                                onclick="globalDelete('/absensi/shift/{{ $s->uuid }}', '{{ $s->nama }}')" title="Hapus">
                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty-state">Belum ada data shift.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
