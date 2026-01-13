<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'file_path',
        'external_url',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
