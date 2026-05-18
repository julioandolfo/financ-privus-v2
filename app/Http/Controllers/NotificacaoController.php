<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacaoController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    /**
     * GET /notificacoes
     * Paginated list with optional filters: tipo, lida
     */
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $empresaId = $user->empresa_id;

        $notificacoes = Notificacao::where('empresa_id', $empresaId)
            ->where('user_id', $user->id)
            ->when($request->tipo, fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->lida === 'nao_lidas', fn ($q) => $q->where('lida', false))
            ->when($request->lida === 'lidas',     fn ($q) => $q->where('lida', true))
            ->orderByRaw('lida ASC, created_at DESC')
            ->paginate(25)
            ->withQueryString();

        $totalNaoLidas = Notificacao::where('empresa_id', $empresaId)
            ->where('user_id', $user->id)
            ->naoLidas()
            ->count();

        return view('notificacoes.index', compact('notificacoes', 'totalNaoLidas'));
    }

    // ─── Dropdown (API) ───────────────────────────────────────────────────────

    /**
     * GET /api/notificacoes/dropdown
     * Returns JSON for the bell-icon dropdown: last 10 items + unread count.
     */
    public function dropdown(): JsonResponse
    {
        $user      = auth()->user();
        $empresaId = $user->empresa_id;

        $items = Notificacao::where('empresa_id', $empresaId)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn (Notificacao $n) => [
                'id'             => $n->id,
                'tipo'           => $n->tipo,
                'titulo'         => $n->titulo,
                'mensagem'       => $n->mensagem,
                'link'           => $n->link,
                'icone'          => $n->icone,
                'cor'            => $n->cor,
                'lida'           => $n->lida,
                'tempo_relativo' => $this->tempoRelativo($n->created_at),
                'created_at'     => $n->created_at->toISOString(),
            ]);

        $naoLidas = Notificacao::where('empresa_id', $empresaId)
            ->where('user_id', $user->id)
            ->naoLidas()
            ->count();

        return response()->json([
            'notificacoes' => $items,
            'nao_lidas'    => $naoLidas,
        ]);
    }

    // ─── Marcar lida ──────────────────────────────────────────────────────────

    /**
     * POST /notificacoes/{id}/marcar-lida
     */
    public function marcarLida(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user          = auth()->user();
        $notificacao   = Notificacao::findOrFail($id);

        abort_if($notificacao->empresa_id !== $user->empresa_id, 403);
        abort_if($notificacao->user_id    !== $user->id,         403);

        if (! $notificacao->lida) {
            $notificacao->update([
                'lida'    => true,
                'lida_em' => now(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        $redirect = $notificacao->link ?? route('notificacoes.index');

        return redirect($redirect);
    }

    // ─── Marcar todas lidas ───────────────────────────────────────────────────

    /**
     * POST /notificacoes/marcar-todas-lidas
     */
    public function marcarTodasLidas(Request $request): RedirectResponse|JsonResponse
    {
        $user      = auth()->user();
        $empresaId = $user->empresa_id;

        Notificacao::where('empresa_id', $empresaId)
            ->where('user_id', $user->id)
            ->where('lida', false)
            ->update([
                'lida'    => true,
                'lida_em' => now(),
            ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('notificacoes.index')
            ->with('success', 'Todas as notificações foram marcadas como lidas.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    /**
     * DELETE /notificacoes/{id}
     */
    public function destroy(int $id): RedirectResponse
    {
        $user        = auth()->user();
        $notificacao = Notificacao::findOrFail($id);

        abort_if($notificacao->empresa_id !== $user->empresa_id, 403);
        abort_if($notificacao->user_id    !== $user->id,         403);

        $notificacao->delete();

        return redirect()->route('notificacoes.index')
            ->with('success', 'Notificação removida.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function tempoRelativo(\Illuminate\Support\Carbon $date): string
    {
        $diff = $date->diffInSeconds(now());

        if ($diff < 60) {
            return 'agora mesmo';
        }

        if ($diff < 3600) {
            $m = (int) floor($diff / 60);
            return "{$m}min atrás";
        }

        if ($diff < 86400) {
            $h = (int) floor($diff / 3600);
            return "{$h}h atrás";
        }

        if ($diff < 2592000) {
            $d = (int) floor($diff / 86400);
            return "{$d}d atrás";
        }

        return $date->format('d/m/Y');
    }
}
