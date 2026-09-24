<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierProduct extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['retail_price' => 'decimal:4', 'dealer_price' => 'decimal:4', 'synced_at' => 'datetime'];
    }
}
