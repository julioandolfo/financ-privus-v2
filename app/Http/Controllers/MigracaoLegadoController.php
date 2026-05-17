<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigracaoLegadoController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    public function index()
    {
        return view('migracao.index');
    }

    public function testar(Request $request): JsonResponse
    {
        $request->validate([
            'host'     => ['required', 'string'],
            'database' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['nullable', 'string'],
            'port'     => ['nullable', 'integer'],
        ]);

        try {
            $this->configurarConexao($request);
            DB::connection('legado')->statement('SELECT 1');

            $tabelas = DB::connection('legado')->select('SHOW TABLES');

            return response()->json([
                'ok'       => true,
                'tabelas'  => count($tabelas),
                'mensagem' => 'Conexão estabelecida! ' . count($tabelas) . ' tabelas encontradas.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'mensagem' => $e->getMessage()], 422);
        } finally {
            DB::purge('legado');
        }
    }

    public function executarPasso(Request $request): JsonResponse
    {
        $request->validate([
            'host'     => ['required', 'string'],
            'database' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['nullable', 'string'],
            'port'     => ['nullable', 'integer'],
            'etapa'    => ['required', 'string'],
        ]);

        try {
            $this->configurarConexao($request);

            Artisan::call('migrate:legado', ['--only' => [$request->etapa]]);
            $output = trim(Artisan::output());

            $ok = !str_contains(strtolower($output), 'erro') && !str_contains(strtolower($output), 'error');

            return response()->json(['ok' => $ok, 'output' => $output ?: '(sem saída)']);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'output' => $e->getMessage()], 500);
        } finally {
            DB::purge('legado');
        }
    }

    private function configurarConexao(Request $request): void
    {
        DB::purge('legado');

        config(['database.connections.legado' => [
            'driver'    => 'mysql',
            'host'      => $request->host,
            'port'      => (int) ($request->port ?? 3306),
            'database'  => $request->database,
            'username'  => $request->username,
            'password'  => $request->password ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ]]);
    }
}
