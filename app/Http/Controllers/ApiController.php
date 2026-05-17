<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function buscarCnpj(string $cnpj): JsonResponse
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return response()->json(['error' => 'CNPJ inválido'], 422);
        }

        try {
            $response = Http::timeout(8)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

            if ($response->failed()) {
                return response()->json(['error' => 'CNPJ não encontrado'], 404);
            }

            $data = $response->json();

            return response()->json([
                'razao_social'   => $data['razao_social'] ?? '',
                'nome_fantasia'  => $data['nome_fantasia'] ?? '',
                'email'          => $data['email'] ?? '',
                'telefone'       => $data['ddd_telefone_1'] ?? '',
                'cep'            => $data['cep'] ?? '',
                'logradouro'     => $data['logradouro'] ?? '',
                'numero'         => $data['numero'] ?? '',
                'complemento'    => $data['complemento'] ?? '',
                'bairro'         => $data['bairro'] ?? '',
                'municipio'      => $data['municipio'] ?? '',
                'uf'             => $data['uf'] ?? '',
                'situacao'       => $data['descricao_situacao_cadastral'] ?? '',
            ]);
        } catch (\Throwable) {
            return response()->json(['error' => 'Serviço indisponível'], 503);
        }
    }
}
