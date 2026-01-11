<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'statement',
        'is_completed',
        'task_date',
        'sort_order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'task_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
