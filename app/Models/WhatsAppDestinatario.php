<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppDestinatario extends Model
{
    protected $table = 'whatsapp_destinatarios';

    protected $fillable = [
        'regra_id',
        'nome',
        'telefone',
    ];

    public function regra(): BelongsTo
    {
        return $this->belongsTo(WhatsAppRegra::class, 'regra_id');
    }
}
