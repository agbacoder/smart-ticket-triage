<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory, HasUlids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'subject',
        'body',
        'status',
        'category',
        'note',
        'explanation',
        'confidence',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
