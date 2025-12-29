<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Journal extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'type',      // 'text' or 'audio'
        'content',   // Text body or S3 Key
        'visibility' // 'private' or 'shared'
    ];

    // Define a collection to ensure only audio files are accepted
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('voice_entries')
            ->acceptsMimeTypes([
                'audio/mpeg',
                'audio/mp3',
                'audio/wav',
                'audio/webm',
                'audio/ogg',
                'video/webm',
            ])
            ->singleFile();
    }

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
