@extends('layouts.app')

@section('content')
<div class="fitur-container">
    @include('keuangan.partials.tabs')
    
    <div class="main-content-box">
        <x-coming-soon 
            title="Manajemen Keuangan" 
            icon="solar:graph-up-bold-duotone" 
            description="Fitur manajemen keuangan lengkap sedang dikembangkan untuk membantu Anda memantau arus kas, laba rugi, dan kesehatan finansial bisnis."
        />
    </div>
</div>
@endsection