<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaProduto extends Model
{
    protected $table = 'categorias_produto';

    protected $fillable = ['empresa_id', 'nome', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function empresa(): BelongsTo { return $this->belongsTo(Empresa::class); }
    public function produtos(): HasMany  { return $this->hasMany(Produto::class, 'categoria_id'); }
}
