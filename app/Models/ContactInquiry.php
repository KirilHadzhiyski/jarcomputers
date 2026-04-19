<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'message',
        'source_page',
        'is_read',
        'created_at',
    ];
}
