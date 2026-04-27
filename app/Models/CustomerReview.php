<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReview extends Model
{
    public const SOURCE_LABELS = [
        'google' => 'Google',
        'facebook' => 'Facebook',
        'website' => 'Website',
        'manual' => 'Manual',
    ];

    protected $fillable = [
        'customer_name',
        'source',
        'rating',
        'review_text',
        'is_featured',
        'is_published',
        'reviewed_at',
        'public_reply',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'reviewed_at' => 'date',
        ];
    }
}
