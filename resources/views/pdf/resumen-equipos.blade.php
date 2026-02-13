<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Resumen de equipos</title>
    <style>

        
            body {
                font-family: "Noto Color Emoji", "Segoe UI Emoji", "Apple Color Emoji", sans-serif;
            }
        

        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color:#111; }
        .h1 { font-size: 18px; font-weight: 700; margin: 0 0 6px 0; }
        .sub { font-size: 11px; font-weight: 700; margin: 0 0 12px 0; }
        .muted { color:#666; font-style: italic; }
        table { width: 100%; border-collapse: collapse; }
        td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: top; }
        .icon { width: 34px; text-align: center; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

@foreach($items as $i => $it)

    <h1 class="title">{{ $it['titulo'] ?? 'Equipo' }}</h1>
    <p class="sub">Serie: {{ $it['serie'] ?? '—' }}</p>

    @if(!empty($it['lineas']) && count($it['lineas']) > 0)
        <table>
            @foreach($it['lineas'] as $l)
                <tr>
                    <td class="icon">{{ $l['icon'] ?? '•' }}</td>
                    <td>{{ $l['text'] ?? '' }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <p class="muted">Sin información para este equipo.</p>
    @endif

    @if($i < count($items) - 1)
        <div class="page-break"></div>
    @endif


    

@endforeach


</body>
</html>
