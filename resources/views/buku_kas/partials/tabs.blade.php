<div class="tab-navigation">
    <a href="{{ route('keuangan.pengeluaran') }}" class="tab-pill {{ request()->routeIs('keuangan.pengeluaran') || request()->routeIs('keuangan.transaksi') ? 'active' : '' }}">
        <iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon>
        <span>Pengeluaran</span>
    </a>
    <a href="{{ route('keuangan.pemasukan') }}" class="tab-pill {{ request()->routeIs('keuangan.pemasukan') ? 'active' : '' }}">
        <iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon>
        <span>Pemasukan Lainnya</span>
    </a>
    <a href="{{ route('keuangan.hutang') }}" class="tab-pill {{ request()->routeIs('keuangan.hutang') ? 'active' : '' }}">
        <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
        <span>Hutang</span>
    </a>
    <a href="{{ route('keuangan.piutang') }}" class="tab-pill {{ request()->routeIs('keuangan.piutang') ? 'active' : '' }}">
        <iconify-icon icon="solar:hand-money-bold-duotone"></iconify-icon>
        <span>Piutang</span>
    </a>
</div>
