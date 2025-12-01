@php
    // Armamos título y serie desde tu tabla equipos
    $titulo = strtoupper(trim(($equipo->marca ?? '') . ' ' . ($equipo->modelo ?? '')));
    $serie  = $equipo->numero_serie ?? (string) $equipo->id;
@endphp

SIZE 77 mm,50 mm
GAP 2 mm,0
CLS
DENSITY 8
SPEED 4
DIRECTION 0
REFERENCE 0,0

REM ======= TITULO (MARCA + MODELO) =======
TEXT 40,60,"0",0,2,2,"{{ $titulo }}"

REM ======= SERIE (TEXTO) =======
TEXT 40,120,"0",0,1,1,"SERIE: {{ $serie }}"

REM ======= CODIGO DE BARRAS =======
BARCODE 140,200,"128",60,1,0,2,2,"{{ $serie }}"
TEXT 170,270,"0",0,1,1,"*{{ $serie }}*"

PRINT 1,1
