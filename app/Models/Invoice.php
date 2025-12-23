<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'cif_cui',
        'series_number',
        'status',
        'pdf_path'
    ];

    protected $casts = [
        'amount' => 'decimal:2', // Ensures 2 decimal places
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
