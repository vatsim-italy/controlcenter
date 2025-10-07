<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationResult extends Model
{
    protected $table = 'evaluation_results';
    protected $primaryKey = 'results_id';
    public $timestamps = false;

    protected $fillable = [
        'eval_id',
        'item_id',
        'vote',
        'comment',
    ];

    // Relations
    public function item()
    {
        return $this->belongsTo(EvaluationItem::class, 'item_id', 'item_id');
    }

    public function report()
    {
        return $this->belongsTo(TrainingReport::class, 'eval_id', 'id');
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'eval_id', 'id');
    }

}
