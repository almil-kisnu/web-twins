<table>
    <tr><th colspan="5" style="font-size: 18px; font-weight: bold; text-align: center;">LAPORAN BUKU KAS - TWINS</th></tr>
    <tr><th colspan="5" style="text-align: center; color: #666;">Outlet: {{ $outlet_name }}</th></tr>
    <tr><th colspan="5" style="text-align: center; color: #666;">Periode: {{ $start_date ? \Carbon\Carbon::parse($start_date)->format('d M Y') : 'Awal' }} - {{ $end_date ? \Carbon\Carbon::parse($end_date)->format('d M Y') : 'Sekarang' }}</th></tr>
    <tr><th colspan="5"></th></tr>
    
    @if(in_array('Pemasukan', $kategoriList) || in_array('Pengeluaran', $kategoriList))
        <tr><th colspan="2" style="background: #D1E9FF; font-weight: bold; border: 1px solid #000;">RINGKASAN CASHFLOW</th><th colspan="3"></th></tr>
        <tr><td style="border: 1px solid #000;">Total Pemasukan</td><td style="border: 1px solid #000; font-weight: bold; color: #2E7D32;">Rp {{ number_format($total_pemasukan ?? 0, 0, ',', '.') }}</td><td colspan="3"></td></tr>
        <tr><td style="border: 1px solid #000;">Total Pengeluaran</td><td style="border: 1px solid #000; font-weight: bold; color: #C62828;">Rp {{ number_format($total_pengeluaran ?? 0, 0, ',', '.') }}</td><td colspan="3"></td></tr>
        @php $net = ($total_pemasukan ?? 0) - ($total_pengeluaran ?? 0); @endphp
        <tr><td style="border: 1px solid #000; background: #f8fafc; font-weight: bold;">SALDO BERSIH</td><td style="border: 1px solid #000; background: #f8fafc; font-weight: bold; color: {{ $net < 0 ? '#C62828' : '#2E7D32' }}">Rp {{ number_format($net, 0, ',', '.') }}</td><td colspan="3"></td></tr>
        <tr><th colspan="5"></th></tr>

        @if(in_array('Pemasukan', $kategoriList))
            <tr><th colspan="5" style="background: #E8F5E9; font-weight: bold; border: 1px solid #000;">DAFTAR PEMASUKAN</th></tr>
            <tr style="background: #f1f5f9;">
                <th style="border: 1px solid #000; font-weight: bold;">No</th>
                <th style="border: 1px solid #000; font-weight: bold;">Tanggal</th>
                <th style="border: 1px solid #000; font-weight: bold;">Keterangan</th>
                <th style="border: 1px solid #000; font-weight: bold;">Outlet</th>
                <th style="border: 1px solid #000; font-weight: bold;">Nominal (Rp)</th>
            </tr>
            @foreach($pemasukan ?? [] as $i => $p)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                <td style="border: 1px solid #000;">{{ preg_replace('/\(Trx: [a-f0-9-]{36}\)/i', '(Otomatis)', $p->keterangan) }}</td>
                <td style="border: 1px solid #000;">{{ $p->outlet->nama ?? '-' }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($p->nominal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr><th colspan="5"></th></tr>
        @endif

        @if(in_array('Pengeluaran', $kategoriList))
            <tr><th colspan="5" style="background: #FFEBEE; font-weight: bold; border: 1px solid #000;">DAFTAR PENGELUARAN</th></tr>
            <tr style="background: #f1f5f9;">
                <th style="border: 1px solid #000; font-weight: bold;">No</th>
                <th style="border: 1px solid #000; font-weight: bold;">Tanggal</th>
                <th style="border: 1px solid #000; font-weight: bold;">Keterangan</th>
                <th style="border: 1px solid #000; font-weight: bold;">Outlet</th>
                <th style="border: 1px solid #000; font-weight: bold;">Nominal (Rp)</th>
            </tr>
            @foreach($pengeluaran ?? [] as $i => $p)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                <td style="border: 1px solid #000;">{{ preg_replace('/\(Trx: [a-f0-9-]{36}\)/i', '(Otomatis)', $p->keterangan) }}</td>
                <td style="border: 1px solid #000;">{{ $p->outlet->nama ?? '-' }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($p->nominal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr><th colspan="5"></th></tr>
        @endif
    @endif

    @if(in_array('Hutang', $kategoriList) || in_array('Piutang', $kategoriList))
        <tr><th colspan="2" style="background: #FFF9C4; font-weight: bold; border: 1px solid #000;">RINGKASAN TAGIHAN</th><th colspan="3"></th></tr>
        <tr><td style="border: 1px solid #000;">Total Hutang</td><td style="border: 1px solid #000; font-weight: bold; color: #E65100;">Rp {{ number_format($total_sisa_hutang ?? 0, 0, ',', '.') }}</td><td colspan="3"></td></tr>
        <tr><td style="border: 1px solid #000;">Total Piutang</td><td style="border: 1px solid #000; font-weight: bold; color: #0284C7;">Rp {{ number_format($total_sisa_piutang ?? 0, 0, ',', '.') }}</td><td colspan="3"></td></tr>
        <tr><th colspan="5"></th></tr>

        @if(in_array('Hutang', $kategoriList))
            <tr><th colspan="6" style="background: #FFF3E0; font-weight: bold; border: 1px solid #000;">DAFTAR HUTANG SUPPLIER</th></tr>
            <tr style="background: #f1f5f9;">
                <th style="border: 1px solid #000; font-weight: bold;">No</th>
                <th style="border: 1px solid #000; font-weight: bold;">Supplier</th>
                <th style="border: 1px solid #000; font-weight: bold;">Jatuh Tempo</th>
                <th style="border: 1px solid #000; font-weight: bold;">Status</th>
                <th style="border: 1px solid #000; font-weight: bold;">Total Hutang (Rp)</th>
                <th style="border: 1px solid #000; font-weight: bold;">Sisa Tagihan (Rp)</th>
            </tr>
            @foreach($hutang ?? [] as $i => $h)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
                <td style="border: 1px solid #000;">{{ $h->contact->nama ?? '-' }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ \Carbon\Carbon::parse($h->jatuh_tempo)->format('d/m/Y') }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $h->sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($h->nominal, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ number_format($h->sisa, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr><th colspan="6"></th></tr>
        @endif

        @if(in_array('Piutang', $kategoriList))
            <tr><th colspan="6" style="background: #E1F5FE; font-weight: bold; border: 1px solid #000;">DAFTAR PIUTANG CUSTOMER</th></tr>
            <tr style="background: #f1f5f9;">
                <th style="border: 1px solid #000; font-weight: bold;">No</th>
                <th style="border: 1px solid #000; font-weight: bold;">Customer</th>
                <th style="border: 1px solid #000; font-weight: bold;">Jatuh Tempo</th>
                <th style="border: 1px solid #000; font-weight: bold;">Status</th>
                <th style="border: 1px solid #000; font-weight: bold;">Total Piutang (Rp)</th>
                <th style="border: 1px solid #000; font-weight: bold;">Sisa Tagihan (Rp)</th>
            </tr>
            @foreach($piutang ?? [] as $i => $p)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
                <td style="border: 1px solid #000;">{{ $p->contact->nama ?? '-' }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ \Carbon\Carbon::parse($p->jatuh_tempo)->format('d/m/Y') }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $p->sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($p->nominal, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ number_format($p->sisa, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        @endif
    @endif
</table>
