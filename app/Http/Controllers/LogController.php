<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = auth()->user()->empresa_id;

        $logs = Activity::with('causer')
            ->whereHasMorph(
                'causer',
                [User::class],
                fn ($q) => $q->where('empresa_id', $empresaId)
            )
            ->when($request->filled('event'), fn ($q, $v) => $q->where('event', $v))
            ->when($request->filled('date_from'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->filled('date_to'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $eventos = Activity::whereHasMorph(
            'causer',
            [User::class],
            fn ($q) => $q->where('empresa_id', $empresaId)
        )
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->filter()
            ->values();

        return view('logs.index', compact('logs', 'eventos'));
    }
}
