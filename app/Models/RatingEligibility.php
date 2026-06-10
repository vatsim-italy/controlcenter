<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingEligibility extends Model
{
    use HasFactory;

    protected $table = 'rating_eligibilities';

    protected $fillable = [
        'user_id',
        'rating_id',
        'eligible',
        'reason',
    ];

    protected $casts = [
        'eligible' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rating()
    {
        return $this->belongsTo(Rating::class);
    }
}