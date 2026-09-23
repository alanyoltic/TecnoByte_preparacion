{{-- resources/views/layouts/ventas.blade.php --}}
{{-- Layout de Ventas: usa el mismo app.blade.php con el sidebar unificado --}}
@extends('layouts.app')

@section('content')
    {{ $slot }}
@endsection
