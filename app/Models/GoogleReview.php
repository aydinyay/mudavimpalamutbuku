<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleReview extends Model
{
    protected $fillable = [
        'google_review_id', 'author_name', 'author_photo',
        'rating', 'comment', 'review_time',
        'display_status', 'admin_read', 'admin_note',
    ];

    protected $casts = [
        'review_time' => 'datetime',
        'admin_read'  => 'boolean',
    ];

    public function stars(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    public function isNegative(): bool
    {
        return $this->rating <= 3;
    }
}
