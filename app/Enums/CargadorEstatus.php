<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CargadorEstatus: string implements HasColor, HasLabel
{
    case DISPONIBLE = 'DISPONIBLE';
    case ASIGNADO = 'ASIGNADO';
    case VENDIDO = 'VENDIDO';
    case EN_GARANTIA = 'EN_GARANTIA';
    case SCRAP = 'SCRAP';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DISPONIBLE => 'Disponible',
            self::ASIGNADO => 'Asignado a Equipo',
            self::VENDIDO => 'Vendido',
            self::EN_GARANTIA => 'En Garantía',
            self::SCRAP => 'Baja / Scrap',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DISPONIBLE => 'success',
            self::ASIGNADO => 'info',
            self::VENDIDO => 'gray',
            self::EN_GARANTIA => 'warning',
            self::SCRAP => 'danger',
        };
    }
}
