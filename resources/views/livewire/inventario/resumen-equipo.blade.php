<div class="space-y-4 text-sm text-slate-200">

    <div class="text-lg font-semibold text-orange-400">
        🔥 {{ $equipo->marca }} {{ $equipo->modelo }}
    </div>

    <ul class="space-y-1">
        <li>💻 CPU: {{ $equipo->cpu }}</li>
        <li>🧠 RAM: {{ $equipo->ram_total }} GB DDR4</li>
        <li>💾 SSD: {{ $equipo->almacenamiento_principal }}</li>
        <li>🖥️ Pantalla: {{ $equipo->monitor?->pulgadas ?? 'N/A' }}”</li>

        <li>
            🎮 GPU:
            @foreach($equipo->gpus as $gpu)
                <span class="block ml-4">
                    {{ $gpu->tipo }} - {{ $gpu->marca }} {{ $gpu->modelo }}
                </span>
            @endforeach
        </li>
    </ul>

    <hr class="border-white/10">

    <ul class="grid grid-cols-2 gap-2 text-xs">
        <li>✅ WiFi</li>
        <li>✅ Bluetooth</li>
        <li>❌ Cámara Web</li>
        <li>✅ Teclado iluminado</li>
        <li>✅ HDMI</li>
        <li>✅ USB</li>
        <li>✅ USB-C</li>
        <li>✅ Ethernet</li>
    </ul>

    <div class="text-green-400 text-sm">
        🔋 Batería funcional
    </div>

</div>
