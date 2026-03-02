<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'contacts';

    protected $fillable = [
        'nom',
        'email',
        'telephone',
        'sujet',
        'message',
        'lu',
        'archive',
    ];

    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
            'archive' => 'boolean',
        ];
    }

    public function scopeNonLus($query)
    {
        return $query->where('lu', false)->where('archive', false);
    }

    public function scopeNonArchives($query)
    {
        return $query->where('archive', false);
    }
}