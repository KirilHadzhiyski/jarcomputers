<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalDocument extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'total' => 'decimal:2', 'document_date' => 'date', 'synced_at' => 'datetime'];
    }
}
