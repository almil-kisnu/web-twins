@extends('layouts.app')

@section('content')
<div class="fitur-container">
    @include('keuangan.partials.tabs')
    
    <div class="main-content-box">
        <x-coming-soon 
            title="Kas Box" 
            icon="solar:wallet-bold-duotone" 
            description="Fitur Kas Box sedang dikembangkan untuk memudahkan pengelolaan kas harian Anda secara terpusat."
        />
    </div>
</div>
@endsection
