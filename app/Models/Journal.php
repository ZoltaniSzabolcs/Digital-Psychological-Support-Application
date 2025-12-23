<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',      // 'text' or 'audio'
        'content',   // Text body or S3 Key
        'visibility' // 'private' or 'shared'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper: Journal::sharedWithPsychologist()->get()
    public function scopeSharedWithPsychologist($query)
    {
        return $query->where('visibility', 'shared');
    }
}
