<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationRun extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    public function connection()
    {
        return $this->belongsTo(IntegrationConnection::class, 'integration_connection_id');
    }
}
