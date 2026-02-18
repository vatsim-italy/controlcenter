<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingActivityLog extends Model
{
    use HasFactory;

    protected $table = 'training_activity_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'training_id',
        'user_id',
        'month',
        'year',
        'hours',
        'requirement_met',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'requirement_met' => 'boolean',
        'hours' => 'decimal:2',
    ];

    /**
     * The training this activity log belongs to.
     */
    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    /**
     * The user this activity log belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
