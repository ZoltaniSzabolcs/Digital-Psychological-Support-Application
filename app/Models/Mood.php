<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mood extends Model
{
    use HasFactory;

    protected $table = 'moods'; // Explicitly link to table

    protected $fillable = [
        'user_id',
        'score',
        'emoji',
        'tags',
        'notes',
        'suicidal_thought_flag'
    ];

    protected $casts = [
        'tags' => 'array', // Automatically converts JSON to PHP Array
        'suicidal_thought_flag' => 'boolean',
        'score' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
