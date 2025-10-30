<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para gestionar las estructuras hoteleras con sus autorizaciones
 * 
 * @property int $id
 * @property int|null $cliente_id
 * @property string $name
 * @property string|null $description
 * @property string|null $fecha_registro
 * @property string|null $fecha_vencimiento
 * @property bool $online
 * @property bool $activo
 * @property string|null $username_hotel
 * @property string|null $password_hotel
 * @property string|null $schedina_url
 * @property string|null $city
 * @property string|null $address
 * @property string|null $cp
 * @property string|null $phone
 * @property string|null $fax
 * @property string|null $email
 * @property string|null $web
 * @property string|null $cf
 * @property string|null $piva
 * @property string|null $typology
 * @property string|null $clasification
 * @property int|null $roomdisp
 * @property int|null $beddisp
 * @property string|null $logo
 * @property \Carbon\CarbonInterface $created_at
 * @property \Carbon\CarbonInterface $updated_at
 */
class HotelStructure extends Model
{
    protected $fillable = [
        'cliente_id',
        'name',
        'description',
        'fecha_registro',
        'fecha_vencimiento',
        'online',
        'activo',
        'username_hotel',
        'password_hotel',
        'schedina_url',
        'city',
        'address',
        'cp',
        'phone',
        'fax',
        'email',
        'web',
        'cf',
        'piva',
        'typology',
        'clasification',
        'roomdisp',
        'beddisp',
        'logo',
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'fecha_vencimiento' => 'date',
        'online' => 'boolean',
        'activo' => 'boolean',
        'roomdisp' => 'integer',
        'beddisp' => 'integer',
    ];

    /**
     * Relación con el cliente (administrador de cadena)
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Scope para filtrar solo hoteles online
     */
    public function scopeOnline($query)
    {
        return $query->where('online', true);
    }

    /**
     * Scope para filtrar solo hoteles activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por cliente
     */
    public function scopeForCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }
}
