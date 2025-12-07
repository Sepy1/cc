<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id','ticket_id','type','meta','seen_at'];

    protected $casts = [
        'meta' => 'array',
        'seen_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function ticket() { return $this->belongsTo(Ticket::class); }

    public function scopeUnseen($q) { return $q->whereNull('seen_at'); }
}
