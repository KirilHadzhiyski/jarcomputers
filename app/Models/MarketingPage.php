<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingPage extends Model
{
    public const TYPE_LABELS = [
        'landing' => 'Landing page',
        'city' => 'City page',
        'service' => 'Service page',
        'campaign' => 'Campaign',
        'legal' => 'Legal',
    ];

    protected $fillable = [
        'title',
        'slug',
        'type',
        'meta_title',
        'meta_description',
        'headline',
        'body',
        'cta_label',
        'cta_url',
        'is_published',
        'published_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
