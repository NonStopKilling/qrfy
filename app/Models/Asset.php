<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'qr_code',
        'name',
        'serial_number',
        'model',
        'status',
        'public_token',
        'manual_pdf_path',
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }
}
