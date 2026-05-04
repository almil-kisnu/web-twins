@extends('layouts.app')

@section('content')
<div class="fitur-container">
    @include('keuangan.partials.tabs')
    
    <div class="main-content-box">
        <x-coming-soon 
            title="Arus Uang" 
            icon="solar:round-transfer-horizontal-bold-duotone" 
            description="Fitur Arus Uang sedang dikembangkan untuk memberikan visualisasi aliran masuk dan keluar dana secara real-time."
        />
    </div>
</div>
@endsection
