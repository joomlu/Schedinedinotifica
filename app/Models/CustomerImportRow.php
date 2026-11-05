<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerImportRow extends Model
{
    protected $fillable = [
        'batch_id',
        'row_number',
        'status',
        'raw_payload',
        'normalized_payload',
        'notes',
        'duplicate_customer_id',
        'duplicate_scope',
        'imported_customer_id',
        'imported_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'normalized_payload' => 'array',
        'notes' => 'array',
        'imported_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(CustomerImportBatch::class, 'batch_id');
    }

    public function duplicateCustomer()
    {
        return $this->belongsTo(Customers::class, 'duplicate_customer_id');
    }

    public function importedCustomer()
    {
        return $this->belongsTo(Customers::class, 'imported_customer_id');
    }
}
