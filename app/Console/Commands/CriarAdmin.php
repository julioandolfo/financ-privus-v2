<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CriarAdmin extends Command
{
    protected $signature = 'admin:criar
                            {--name=     : Nome do usuário}
                            {--email=    : E-mail do usuário}
                            {--password= : Senha (mín. 8 caracteres)}
                            {--empresa=  : ID da empresa (usa a primeira se omitido)}';

    protected $description = 'Cria ou atualiza um usuário administrador';

    public function handle(): int
    {
        $name     = $this->option('name')     ?: $this->ask('Nome');
        $email    = $this->option('email')    ?: $this->ask('E-mail');
        $password = $this->option('password') ?: $this->secret('Senha (mín. 8 caracteres)');

        if (strlen($password) < 8) {
            $this->error('A senha deve ter pelo menos 8 caracteres.');
            return self::FAILURE;
        }

        // Resolve empresa
        $empresaId = $this->option('empresa');

        if ($empresaId) {
            $empresa = Empresa::find($empresaId);
            if (!$empresa) {
                $this->error("Empresa ID {$empresaId} não encontrada.");
                return self::FAILURE;
            }
        } else {
            $empresa = Empresa::first();

            if (!$empresa) {
                $razao = $this->ask('Nenhuma empresa encontrada. Informe a Razão Social');
                $empresa = Empresa::create([
                    'razao_social' => $razao,
                    'codigo'       => strtoupper(substr(Str::slug($razao, ''), 0, 15)) ?: Str::upper(Str::random(8)),
                    'ativo'        => true,
                ]);
                $this->info("Empresa criada: ID {$empresa->id}");
            }
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'               => $name,
                'password'           => Hash::make($password),
                'empresa_id'         => $empresa->id,
                'role'               => 'admin',
                'ativo'              => true,
                'email_verified_at'  => now(),
            ]
        );

        $acao = $user->wasRecentlyCreated ? 'criado' : 'atualizado';

        $this->newLine();
        $this->info("✓ Usuário {$acao} com sucesso!");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID',      $user->id],
                ['Nome',    $user->name],
                ['E-mail',  $user->email],
                ['Role',    $user->role],
                ['Empresa', "{$empresa->razao_social} (ID {$empresa->id})"],
            ]
        );

        return self::SUCCESS;
    }
}
