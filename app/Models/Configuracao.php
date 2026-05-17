<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = ['empresa_id', 'chave', 'valor', 'tipo'];

    public static function get(string $chave, mixed $default = null, ?int $empresaId = null): mixed
    {
        $empresaId = $empresaId ?? auth()->user()?->empresa_id;

        $row = static::where('empresa_id', $empresaId)->where('chave', $chave)->first()
            ?? static::whereNull('empresa_id')->where('chave', $chave)->first();

        if (! $row) {
            return $default;
        }

        return match ($row->tipo) {
            'integer' => (int) $row->valor,
            'boolean' => filter_var($row->valor, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($row->valor, true),
            default   => $row->valor,
        };
    }

    public static function set(string $chave, mixed $valor, string $tipo = 'string', ?int $empresaId = null): void
    {
        $empresaId = $empresaId ?? auth()->user()?->empresa_id;

        if ($tipo === 'json' && is_array($valor)) {
            $valor = json_encode($valor);
        } elseif ($tipo === 'boolean') {
            $valor = $valor ? '1' : '0';
        }

        static::updateOrCreate(
            ['empresa_id' => $empresaId, 'chave' => $chave],
            ['valor' => $valor, 'tipo' => $tipo]
        );
    }

    public static function setMany(array $dados, ?int $empresaId = null): void
    {
        foreach ($dados as $chave => $valor) {
            static::set($chave, $valor, 'string', $empresaId);
        }
    }
}
