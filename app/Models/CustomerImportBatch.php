<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerImportBatch extends Model
{
    protected $fillable = [
        'struttura_id',
        'proprietario_id',
        'user_id',
        'original_name',
        'stored_path',
        'status',
        'total_rows',
        'valid_rows',
        'duplicate_hotel_rows',
        'duplicate_chain_rows',
        'duplicate_file_rows',
        'needs_review_rows',
        'imported_rows',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function rows()
    {
        return $this->hasMany(CustomerImportRow::class, 'batch_id')->orderBy('row_number');
    }

    public function struttura()
    {
        return $this->belongsTo(Struttura::class, 'struttura_id');
    }

    public function proprietario()
    {
        return $this->belongsTo(Proprietario::class, 'proprietario_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
