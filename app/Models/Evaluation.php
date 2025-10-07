<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory;

    protected $table = 'evaluations'; // table name
    protected $primaryKey = 'eval_id';      // primary key column
    public $incrementing = true;         // Auto-incrementing

    public $timestamps = true;         // set to true if you have created_at/updated_at

    protected $fillable = [
        'student_id',
        'training_id',
        'examiner_id',
        'level',
        'date',
        'position',
    ];

    // Relationships

    /**
     * The Training this evaluation belongs to
     */
    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id', 'id');
    }

    /**
     * The examiner (user) who performed the evaluation
     */
    public function examiner()
    {
        return $this->belongsTo(User::class, 'examiner_id', 'id');
    }

    /**
     * The results (votes/comments) of this evaluation
     */
    public function results()
    {
        return $this->hasMany(EvaluationResult::class, 'eval_id', 'eval_id');
    }

    /**
     * Group results by category for easier Blade rendering
     */
    public function resultsByCategory()
    {
        return $this->results()->with('item')->get()->groupBy(function($result) {
            return $result->item->category;
        });
    }
}
