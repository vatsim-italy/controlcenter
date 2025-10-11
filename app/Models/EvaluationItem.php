<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationItem extends Model
{
    use HasFactory;

    protected $table = 'evaluation_items';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'level',
        'category',
        'key_name',
        'description',
    ];

    // Optional: relation to results
    public function results()
    {
        return $this->hasMany(EvaluationResult::class, 'item_id', 'item_id');
    }

    public function evaluationResults()
    {
        return $this->hasMany(EvaluationResult::class, 'eval_id', 'id');
    }

}
