@extends('layouts.app')

@section('content')

<div class="relative min-h-screen py-10">

    {{-- Glow background global --}}
    <div class="pointer-events-none absolute -top-32 -left-32 w-96 h-96 bg-blue-500/20 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 right-0 w-96 h-96 bg-indigo-500/20 blur-3xl"></div>

    <div class="max-w-6xl mx-auto px-6">

        {{-- Topbar ya estándar --}}
        <x-topbar>
            <x-slot name="title">
                Transferencia de Equipos
            </x-slot>
        </x-topbar>

        {{-- Componente Livewire --}}
        <livewire:inventario.transferencias />

    </div>
</div>

@endsection