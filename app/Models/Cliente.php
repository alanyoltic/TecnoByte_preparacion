<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'tipo_persona',
        'nombres',
        'apellidos',
        'razon_social',
        'rfc',
        'regimen_fiscal',
        'uso_cfdi',
        'vendedor_id',
        'como_se_entero',
        'correo',
        'telefono',
        'pais',
        'estado',
        'municipio',
        'localidad',
        'colonia',
        'calle',
        'no_ext',
        'no_int',
        'codigo_postal',
        'codigo_colonia',
        'codigo_localidad',
        'notas',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────────

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    // ─── Atributos de presentación ─────────────────────────────────────────────

    /**
     * Nombre para mostrar según tipo de persona.
     */
    public function getNombreCompletoAttribute(): string
    {
        if ($this->tipo_persona === 'MORAL') {
            return $this->razon_social ?? 'Sin razón social';
        }

        return trim(($this->nombres ?? '') . ' ' . ($this->apellidos ?? '')) ?: 'Sin nombre';
    }

    /**
     * Inicial o icono de avatar.
     */
    public function getInicialAttribute(): string
    {
        return strtoupper(mb_substr($this->nombre_completo, 0, 1));
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeFisicas($query)
    {
        return $query->where('tipo_persona', 'FISICA');
    }

    public function scopeMorales($query)
    {
        return $query->where('tipo_persona', 'MORAL');
    }

    // ─── Catálogos estáticos ───────────────────────────────────────────────────

    public static function opcionesComoSeEntero(): array
    {
        return [
            'REFERIDO'         => 'Referido / Recomendación',
            'REDES_SOCIALES'   => 'Redes Sociales',
            'BUSQUEDA_WEB'     => 'Búsqueda en Internet',
            'PUBLICIDAD'       => 'Publicidad (volante / espectacular)',
            'TIENDA_FISICA'    => 'Pasó por la tienda',
            'MARKETPLACE'      => 'Marketplace (FB, MercadoLibre)',
            'OTRO'             => 'Otro',
        ];
    }

    public static function opcionesRegimenFiscal(): array
    {
        return [
            '601' => '601 – General de Ley Personas Morales',
            '603' => '603 – Personas Morales con Fines no Lucrativos',
            '605' => '605 – Sueldos y Salarios e Ingresos Asimilados',
            '606' => '606 – Arrendamiento',
            '607' => '607 – Enajenación o Adquisición de Bienes',
            '608' => '608 – Demás Ingresos',
            '610' => '610 – Residentes en el Extranjero sin Establecimiento',
            '611' => '611 – Ingresos por Dividendos',
            '612' => '612 – Personas Físicas con Actividades Empresariales',
            '614' => '614 – Ingresos por Intereses',
            '615' => '615 – Régimen de los ingresos por obtención de premios',
            '616' => '616 – Sin Obligaciones Fiscales',
            '620' => '620 – Sociedades Cooperativas de Producción',
            '621' => '621 – Incorporación Fiscal',
            '622' => '622 – Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
            '623' => '623 – Opcional para Grupos de Sociedades',
            '624' => '624 – Coordinados',
            '625' => '625 – Régimen de las Actividades Empresariales con ingresos',
            '626' => '626 – Régimen Simplificado de Confianza (RESICO)',
        ];
    }

    public static function opcionesUsoCfdi(): array
    {
        return [
            'G01' => 'G01 – Adquisición de mercancias',
            'G02' => 'G02 – Devoluciones, descuentos o bonificaciones',
            'G03' => 'G03 – Gastos en general',
            'I01' => 'I01 – Construcciones',
            'I02' => 'I02 – Mobilario y equipo de oficina por inversiones',
            'I04' => 'I04 – Equipo de computo y accesorios',
            'I08' => 'I08 – Otra maquinaria y equipo',
            'S01' => 'S01 – Sin efectos fiscales',
            'CP01' => 'CP01 – Pagos',
        ];
    }
}
