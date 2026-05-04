<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">

<div class="tab-navigation">
    <a href="{{ route('keuangan.kas-box') }}" class="tab-pill {{ (request()->routeIs('keuangan.kas-box') || request()->routeIs('keuangan.index')) ? 'active' : '' }}">
        <iconify-icon icon="solar:wallet-bold-duotone"></iconify-icon>
        <span>Kas Box</span>
    </a>
    <a href="{{ route('keuangan.arus-uang') }}" class="tab-pill {{ request()->routeIs('keuangan.arus-uang') ? 'active' : '' }}">
        <iconify-icon icon="solar:round-transfer-horizontal-bold-duotone"></iconify-icon>
        <span>Arus Uang</span>
    </a>
    <a href="{{ route('keuangan.pemindahan-saldo') }}" class="tab-pill {{ request()->routeIs('keuangan.pemindahan-saldo') ? 'active' : '' }}">
        <iconify-icon icon="solar:card-transfer-bold-duotone"></iconify-icon>
        <span>Pemindahan Saldo</span>
    </a>
</div>
