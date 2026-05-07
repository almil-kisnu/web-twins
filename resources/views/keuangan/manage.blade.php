@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
<style>
    .view-section { display: none; }
    .view-section.active { display: block; }
</style>
@endpush

@section('content')
<div class="fitur-container" id="keuangan-app">
    {{-- PILL TABS --}}
    <div class="tab-navigation">
        <a href="javascript:void(0)" class="tab-pill" onclick="switchTab('kas-box')" id="pill-kas-box">
            <iconify-icon icon="solar:wallet-bold-duotone"></iconify-icon>
            <span>Kas Box</span>
        </a>
        <a href="javascript:void(0)" class="tab-pill" onclick="switchTab('arus-uang')" id="pill-arus-uang">
            <iconify-icon icon="solar:round-transfer-horizontal-bold-duotone"></iconify-icon>
            <span>Arus Uang</span>
        </a>
        <a href="javascript:void(0)" class="tab-pill" onclick="switchTab('pemindahan-saldo')" id="pill-pemindahan-saldo">
            <iconify-icon icon="solar:card-transfer-bold-duotone"></iconify-icon>
            <span>Pemindahan Saldo</span>
        </a>
    </div>

    {{-- MAIN CONTENT BOX --}}
    <div class="main-content-box">
        
        <!-- SECTION KAS BOX -->
        <div id="view-kas-box" class="view-section">
            <x-coming-soon 
                title="Kas Box" 
                icon="solar:wallet-bold-duotone" 
                description="Fitur Kas Box sedang dikembangkan untuk memudahkan pengelolaan kas harian Anda secara terpusat."
            />
        </div>

        <!-- SECTION ARUS UANG -->
        <div id="view-arus-uang" class="view-section">
            <x-coming-soon 
                title="Arus Uang" 
                icon="solar:round-transfer-horizontal-bold-duotone" 
                description="Fitur Arus Uang sedang dikembangkan untuk memantau aliran masuk dan keluar dana secara otomatis."
            />
        </div>

        <!-- SECTION PEMINDAHAN SALDO -->
        <div id="view-pemindahan-saldo" class="view-section">
            <x-coming-soon 
                title="Pemindahan Saldo" 
                icon="solar:card-transfer-bold-duotone" 
                description="Fitur Pemindahan Saldo sedang dikembangkan untuk memudahkan transfer dana antar outlet atau akun bank."
            />
        </div>

    </div>
</div>

<script>
    function switchTab(tabId) {
        // Update Active Pill
        document.querySelectorAll('.tab-pill').forEach(pill => pill.classList.remove('active'));
        const activePill = document.getElementById('pill-' + tabId);
        if (activePill) activePill.classList.add('active');

        // Update Visibility
        document.querySelectorAll('.view-section').forEach(section => section.classList.remove('active'));
        const activeSection = document.getElementById('view-' + tabId);
        if (activeSection) activeSection.classList.add('active');

        // Update URL without reload
        window.history.replaceState(null, null, `?tab=${tabId}`);
    }

    // Auto switch to tab from URL on load
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'kas-box';
        switchTab(tab);
    });
</script>
@endsection
