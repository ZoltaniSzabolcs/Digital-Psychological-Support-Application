<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['sender_id', 'receiver_id', 'content', 'type', 'read_at'];

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver() {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Define collection for audio
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('audio_messages')
            ->acceptsMimeTypes(['audio/mpeg', 'audio/mp3', 'audio/webm', 'audio/ogg', 'video/webm'])
            ->singleFile();
    }
}
