@extends('layouts.app')

@section('content')
<div class="fitur-container">
    @include('keuangan.partials.tabs')
    
    <div class="main-content-box">
        <x-coming-soon 
            title="Pemindahan Saldo" 
            icon="solar:card-transfer-bold-duotone" 
            description="Fitur Pemindahan Saldo sedang dikembangkan untuk memudahkan transfer antar akun kas atau outlet Anda."
        />
    </div>
</div>
@endsection
