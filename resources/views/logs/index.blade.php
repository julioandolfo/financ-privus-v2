<x-layouts.app title="Logs de Auditoria">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Logs de Auditoria</h1>
            <p class="text-sm text-surface-500 mt-0.5">Histórico de atividades dos usuários da empresa</p>
        </div>
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-44">
                <x-ui.select name="event" label="Ação">
                    <option value="">Todas as ações</option>
                    @foreach($eventos as $evento)
                    <option value="{{ $evento }}" @selected(request('event') === $evento)>
                        {{ ucfirst($evento) }}
                    </option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="w-44">
                <x-ui.input name="date_from" type="date" label="Data inicial" value="{{ request('date_from') }}" />
            </div>
            <div class="w-44">
                <x-ui.input name="date_to" type="date" label="Data final" value="{{ request('date_to') }}" />
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            @if(request()->hasAny(['event', 'date_from', 'date_to']))
            <x-ui.button href="{{ route('logs.index') }}" variant="ghost">Limpar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    {{-- Table --}}
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider whitespace-nowrap">Data / Hora</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Usuário</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Ação</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Entidade</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Detalhes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($logs as $log)
                    @php
                        $eventBadge = match($log->event) {
                            'created' => ['success', 'Criado'],
                            'updated' => ['info',    'Atualizado'],
                            'deleted' => ['danger',  'Excluído'],
                            default   => ['default', ucfirst($log->event ?? 'N/A')],
                        };

                        // Simplify fully-qualified class name to readable label
                        $subjectType = $log->subject_type
                            ? class_basename($log->subject_type)
                            : '—';

                        $propriedades = $log->properties?->toArray() ?? [];
                        $temDetalhes  = !empty($propriedades);
                    @endphp
                    <tr
                        x-data="{ open: false }"
                        class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors align-top"
                    >
                        <td class="px-5 py-4 text-xs text-surface-500 whitespace-nowrap">
                            {{ $log->created_at->format('d/m/Y') }}<br>
                            <span class="text-surface-400">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @if($log->causer)
                            <span class="text-sm font-medium text-surface-900 dark:text-white">{{ $log->causer->name }}</span>
                            @else
                            <span class="text-sm text-surface-400 italic">Sistema</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <x-ui.badge :variant="$eventBadge[0]">{{ $eventBadge[1] }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-600 dark:text-surface-400">
                            {{ $subjectType }}
                            @if($log->subject_id)
                            <span class="text-xs text-surface-400">#{{ $log->subject_id }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-700 dark:text-surface-300 max-w-xs">
                            @if($log->log_name && $log->log_name !== 'default')
                            <span class="text-xs text-surface-400 mr-1">[{{ $log->log_name }}]</span>
                            @endif
                            {{ $log->description ?? '—' }}
                        </td>
                        <td class="px-5 py-4">
                            @if($temDetalhes)
                            <button
                                type="button"
                                @click="open = !open"
                                class="inline-flex items-center gap-1 text-xs text-primary-600 dark:text-primary-400 hover:underline"
                            >
                                <span x-text="open ? 'Ocultar' : 'Ver'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''"
                                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div
                                x-show="open"
                                x-cloak
                                x-transition
                                class="mt-2 max-w-sm"
                            >
                                <pre class="text-xs bg-surface-50 dark:bg-surface-900 border border-surface-200 dark:border-surface-700 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap text-surface-600 dark:text-surface-400">{{ json_encode($propriedades, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            @else
                            <span class="text-xs text-surface-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhum registro encontrado para os filtros selecionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $logs->links() }}
        </div>
        @endif
    </x-ui.card>

</x-layouts.app>
