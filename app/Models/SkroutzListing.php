<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkroutzListing extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['attributes' => 'array', 'approved' => 'boolean'];
    }

    public function configuration()
    {
        return $this->belongsTo(PricingConfiguration::class, 'pricing_configuration_id');
    }
}
