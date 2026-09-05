<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewPhoto extends Model
{
    protected $fillable = [
        'reviewer_name', 'reviewer_photo', 'rating', 'comment', 'filenames', 'review_time', 'active',
    ];

    protected $casts = [
        'filenames'   => 'array',
        'review_time' => 'datetime',
        'active'      => 'boolean',
    ];

    public function stars(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}
