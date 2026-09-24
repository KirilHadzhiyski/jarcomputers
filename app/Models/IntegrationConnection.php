<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationConnection extends Model
{
    protected $fillable = ['provider', 'enabled', 'credentials', 'settings', 'state', 'last_success_at', 'next_run_at'];

    protected $hidden = ['credentials'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'credentials' => 'encrypted:array', 'settings' => 'array', 'state' => 'array',
            'last_success_at' => 'datetime', 'next_run_at' => 'datetime'];
    }

    public function runs()
    {
        return $this->hasMany(IntegrationRun::class)->latest('id');
    }
}
