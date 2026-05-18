<?php

namespace App\Services;

use App\Models\Notificacao;
use App\Models\User;
use Carbon\Carbon;

/**
 * Typed helpers for creating system notifications.
 *
 * Each method encapsulates the correct tipo / icone / cor combination
 * so callers never have to remember magic strings.
 */
class NotificacaoService
{
    // ─── Vencimento próximo ───────────────────────────────────────────────────

    /**
     * A bill or receivable is approaching its due date.
     *
     * @param  User    $user
     * @param  string  $descricao   Human-readable description (e.g. "Aluguel Janeiro/2026")
     * @param  float   $valor       Amount
     * @param  Carbon  $vencimento  Due date
     * @param  string  $link        URL to the record (e.g. route('contas-pagar.edit', $id))
     */
    public static function vencimentoProximo(
        User $user,
        string $descricao,
        float $valor,
        Carbon $vencimento,
        string $link
    ): void {
        $valorFormatado     = 'R$ ' . number_format($valor, 2, ',', '.');
        $vencimentoFormatado = $vencimento->format('d/m/Y');
        $diasRestantes       = now()->startOfDay()->diffInDays($vencimento->startOfDay(), false);

        if ($diasRestantes === 0) {
            $prazoTexto = 'vence hoje';
        } elseif ($diasRestantes === 1) {
            $prazoTexto = 'vence amanhã';
        } else {
            $prazoTexto = "vence em {$diasRestantes} dias ({$vencimentoFormatado})";
        }

        Notificacao::criar(
            userId:    $user->id,
            empresaId: $user->empresa_id,
            tipo:      'vencimento',
            titulo:    "Vencimento próximo: {$descricao}",
            mensagem:  "{$descricao} — {$valorFormatado} — {$prazoTexto}.",
            link:      $link,
            icone:     'clock',
            cor:       'yellow',
        );
    }

    // ─── Conta vencida ────────────────────────────────────────────────────────

    /**
     * A bill or receivable is overdue.
     *
     * @param  User    $user
     * @param  string  $descricao  Human-readable description
     * @param  float   $valor      Amount
     * @param  string  $link       URL to the record
     */
    public static function contaVencida(
        User $user,
        string $descricao,
        float $valor,
        string $link
    ): void {
        $valorFormatado = 'R$ ' . number_format($valor, 2, ',', '.');

        Notificacao::criar(
            userId:    $user->id,
            empresaId: $user->empresa_id,
            tipo:      'vencimento',
            titulo:    "Conta vencida: {$descricao}",
            mensagem:  "{$descricao} — {$valorFormatado} — está vencida e aguarda regularização.",
            link:      $link,
            icone:     'exclamation-triangle',
            cor:       'red',
        );
    }

    // ─── Recorrência gerada ───────────────────────────────────────────────────

    /**
     * A recurring entry was automatically generated.
     *
     * @param  User    $user
     * @param  string  $descricao  Template description (e.g. "Salários")
     * @param  string  $link       URL to the generated record
     */
    public static function recorrenciaGerada(
        User $user,
        string $descricao,
        string $link
    ): void {
        Notificacao::criar(
            userId:    $user->id,
            empresaId: $user->empresa_id,
            tipo:      'recorrencia',
            titulo:    "Recorrência gerada: {$descricao}",
            mensagem:  "O lançamento recorrente \"{$descricao}\" foi gerado automaticamente.",
            link:      $link,
            icone:     'arrow-path',
            cor:       'blue',
        );
    }

    // ─── Mensagem de sistema ──────────────────────────────────────────────────

    /**
     * A generic system notification (informational message).
     *
     * @param  User    $user
     * @param  string  $titulo    Short subject line
     * @param  string  $mensagem  Detailed body text
     */
    public static function sistema(
        User $user,
        string $titulo,
        string $mensagem
    ): void {
        Notificacao::criar(
            userId:    $user->id,
            empresaId: $user->empresa_id,
            tipo:      'sistema',
            titulo:    $titulo,
            mensagem:  $mensagem,
            link:      null,
            icone:     'information-circle',
            cor:       'blue',
        );
    }

    // ─── Pagamento registrado ─────────────────────────────────────────────────

    /**
     * A payment was successfully recorded.
     *
     * @param  User    $user
     * @param  string  $descricao  Human-readable description
     * @param  float   $valor      Amount paid
     * @param  string  $link       URL to the payment record
     */
    public static function pagamentoRegistrado(
        User $user,
        string $descricao,
        float $valor,
        string $link
    ): void {
        $valorFormatado = 'R$ ' . number_format($valor, 2, ',', '.');

        Notificacao::criar(
            userId:    $user->id,
            empresaId: $user->empresa_id,
            tipo:      'pagamento',
            titulo:    "Pagamento registrado: {$descricao}",
            mensagem:  "{$descricao} — {$valorFormatado} — foi baixado com sucesso.",
            link:      $link,
            icone:     'check-circle',
            cor:       'green',
        );
    }

    // ─── Alerta genérico ─────────────────────────────────────────────────────

    /**
     * A generic alert that requires user attention.
     *
     * @param  User         $user
     * @param  string       $titulo    Short subject line
     * @param  string       $mensagem  Detailed body text
     * @param  string|null  $link      Optional URL for more details
     */
    public static function alerta(
        User $user,
        string $titulo,
        string $mensagem,
        ?string $link = null
    ): void {
        Notificacao::criar(
            userId:    $user->id,
            empresaId: $user->empresa_id,
            tipo:      'alerta',
            titulo:    $titulo,
            mensagem:  $mensagem,
            link:      $link,
            icone:     'exclamation-triangle',
            cor:       'orange',
        );
    }
}
