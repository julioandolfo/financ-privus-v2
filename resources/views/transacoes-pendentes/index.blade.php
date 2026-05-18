<x-layouts.app title="Transações Pendentes">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Transações Pendentes</h1>
            <p class="text-sm text-surface-500 mt-0.5">Revise e aprove transações importadas do banco</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card
            label="Pendentes"
            :value="number_format($stats->total_pendentes)"
            color="yellow"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>'
        />
        <x-ui.stat-card
            label="Aprovadas"
            :value="number_format($stats->total_aprovadas)"
            color="green"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>'
        />
        <x-ui.stat-card
            label="Ignoradas"
            :value="number_format($stats->total_ignoradas)"
            color="primary"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>'
        />
        <x-ui.stat-card
            label="Valor Pendente"
            :value="'R$ ' . number_format($stats->valor_pendente, 2, ',', '.')"
            color="red"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>'
        />
    </div>

    {{-- Filter bar + status tabs --}}
    <x-ui.card class="mb-4">
        {{-- Status tabs --}}
        <div class="flex gap-1 mb-4 border-b border-surface-100 dark:border-surface-700 pb-4">
            @foreach(['' => 'Todas', 'pendente' => 'Pendentes', 'aprovada' => 'Aprovadas', 'ignorada' => 'Ignoradas'] as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $val, 'page' => null]) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                      {{ request('status', '') === $val
                         ? 'bg-primary-600 text-white shadow-sm'
                         : 'text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-700' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <div class="w-44">
                <x-ui.select name="tipo" label="Tipo">
                    <option value="">Todos</option>
                    <option value="debito"  @selected(request('tipo') === 'debito')>Débito</option>
                    <option value="credito" @selected(request('tipo') === 'credito')>Crédito</option>
                </x-ui.select>
            </div>
            <div class="w-52">
                <x-ui.select name="conta_bancaria_id" label="Conta Bancária">
                    <option value="">Todas</option>
                    @foreach($contasBancarias as $cb)
                    <option value="{{ $cb->id }}" @selected(request('conta_bancaria_id') == $cb->id)>{{ $cb->nome }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="w-40">
                <x-ui.input name="data_inicio" type="date" label="Data início" value="{{ request('data_inicio') }}" />
            </div>
            <div class="w-40">
                <x-ui.input name="data_fim" type="date" label="Data fim" value="{{ request('data_fim') }}" />
            </div>
            <x-ui.button type="submit" variant="secondary">Filtrar</x-ui.button>
            @if(request()->hasAny(['tipo','conta_bancaria_id','data_inicio','data_fim']))
            <x-ui.button href="{{ route('transacoes-pendentes.index', ['status' => request('status')]) }}" variant="ghost">Limpar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    {{-- Table with Alpine state for checkboxes and modals --}}
    <x-ui.card :padding="false"
        x-data="{
            selected: [],
            all: false,
            modal: { open: false, id: null, descricao: '', data_vencimento: '', categoria_id: '', loading: false, error: '' },
            openModal(id, descricao, data_vencimento, categoria_id) {
                this.modal = { open: true, id, descricao, data_vencimento, categoria_id: categoria_id ?? '', loading: false, error: '' };
            },
            closeModal() { this.modal.open = false; },
            async aprovar() {
                this.modal.loading = true;
                this.modal.error = '';
                try {
                    const resp = await fetch('/transacoes-pendentes/' + this.modal.id + '/aprovar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            categoria_id: this.modal.categoria_id || null,
                            descricao: this.modal.descricao || null,
                            data_vencimento: this.modal.data_vencimento || null,
                        }),
                    });
                    const json = await resp.json();
                    if (json.success) {
                        this.closeModal();
                        window.location.reload();
                    } else {
                        this.modal.error = json.message ?? 'Erro ao aprovar.';
                    }
                } catch(e) {
                    this.modal.error = 'Erro de comunicação com o servidor.';
                } finally {
                    this.modal.loading = false;
                }
            },
            async ignorar(id) {
                if (!confirm('Ignorar esta transação?')) return;
                const resp = await fetch('/transacoes-pendentes/' + id + '/ignorar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                const json = await resp.json();
                if (json.success) window.location.reload();
            },
            async aprovarLote() {
                if (this.selected.length === 0) return;
                if (!confirm('Aprovar ' + this.selected.length + ' transação(ões) selecionada(s) usando a categoria sugerida?')) return;
                const resp = await fetch('/transacoes-pendentes/aprovar-lote', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids: this.selected }),
                });
                const json = await resp.json();
                alert(json.message);
                if (json.success) window.location.reload();
            },
        }"
    >

        {{-- Bulk action bar --}}
        <div x-show="selected.length > 0" x-cloak
             class="flex items-center gap-3 px-5 py-3 bg-primary-50 dark:bg-primary-900/20 border-b border-primary-100 dark:border-primary-800">
            <span class="text-sm font-medium text-primary-700 dark:text-primary-300" x-text="selected.length + ' selecionada(s)'"></span>
            <x-ui.button size="sm" @click="aprovarLote()">Aprovar Selecionadas</x-ui.button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-800/50">
                        <th class="w-10 px-5 py-3">
                            <input type="checkbox" class="rounded border-surface-300 text-primary-600"
                                x-model="all"
                                @change="selected = all ? Array.from(document.querySelectorAll('[data-id]')).map(el => el.dataset.id) : []">
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Data</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Conta Bancária</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Categoria Sugerida</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Origem</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @forelse($transacoes as $transacao)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                        <td class="px-5 py-4">
                            @if($transacao->status === 'pendente')
                            <input type="checkbox" class="rounded border-surface-300 text-primary-600"
                                data-id="{{ $transacao->id }}"
                                :value="'{{ $transacao->id }}'"
                                x-model="selected">
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500 whitespace-nowrap">
                            {{ $transacao->data_transacao->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-surface-900 dark:text-white max-w-xs truncate">
                                {{ $transacao->descricao_normalizada ?? $transacao->descricao_original }}
                            </div>
                            @if($transacao->descricao_normalizada && $transacao->descricao_normalizada !== $transacao->descricao_original)
                            <div class="text-xs text-surface-400 max-w-xs truncate">{{ $transacao->descricao_original }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">
                            {{ $transacao->contaBancaria?->nome ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-right whitespace-nowrap
                            {{ $transacao->tipo === 'credito' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $transacao->tipo === 'credito' ? '+' : '-' }} R$ {{ number_format($transacao->valor, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-500">
                            {{ $transacao->categoriaSugerida?->nome ?? '—' }}
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $statusMap = [
                                    'pendente'  => ['warning', 'Pendente'],
                                    'aprovada'  => ['success', 'Aprovada'],
                                    'ignorada'  => ['default', 'Ignorada'],
                                ];
                                [$variant, $label] = $statusMap[$transacao->status] ?? ['default', $transacao->status];
                            @endphp
                            <x-ui.badge :variant="$variant">{{ $label }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-sm text-surface-400 capitalize">
                            {{ str_replace('_', ' ', $transacao->origem) }}
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            @if($transacao->status === 'pendente')
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    type="button"
                                    @click="openModal(
                                        {{ $transacao->id }},
                                        '{{ addslashes($transacao->descricao_normalizada ?? $transacao->descricao_original) }}',
                                        '{{ $transacao->data_transacao->format('Y-m-d') }}',
                                        {{ $transacao->categoria_sugerida_id ? $transacao->categoria_sugerida_id : 'null' }}
                                    )"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-300 transition-colors"
                                >
                                    Aprovar
                                </button>
                                <button
                                    type="button"
                                    @click="ignorar({{ $transacao->id }})"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors"
                                >
                                    Ignorar
                                </button>
                            </div>
                            @elseif($transacao->status === 'aprovada')
                            <div class="text-xs text-surface-400">
                                @if($transacao->aprovada_em)
                                {{ $transacao->aprovada_em->format('d/m/Y') }}
                                @endif
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-sm text-surface-400">
                            Nenhuma transação encontrada para os filtros selecionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transacoes->hasPages())
        <div class="px-5 py-4 border-t border-surface-100 dark:border-surface-700">
            {{ $transacoes->links() }}
        </div>
        @endif

        {{-- Aprovar Modal --}}
        <div x-show="modal.open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="closeModal()">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50" @click="closeModal()"></div>

            {{-- Panel --}}
            <div class="relative bg-white dark:bg-surface-800 rounded-2xl shadow-xl w-full max-w-md p-6"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-surface-900 dark:text-white">Aprovar Transação</h2>
                    <button type="button" @click="closeModal()"
                            class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Error --}}
                <div x-show="modal.error" x-cloak
                     class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-600 dark:text-red-400"
                     x-text="modal.error">
                </div>

                <div class="space-y-4">
                    {{-- Descrição --}}
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Descrição</label>
                        <input type="text"
                               x-model="modal.descricao"
                               class="w-full rounded-xl border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-700 text-surface-900 dark:text-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="Descrição da conta..."
                        />
                    </div>

                    {{-- Categoria --}}
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Categoria</label>
                        <select x-model="modal.categoria_id"
                                class="w-full rounded-xl border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-700 text-surface-900 dark:text-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">— Sem categoria —</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Data de vencimento --}}
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Data de Vencimento</label>
                        <input type="date"
                               x-model="modal.data_vencimento"
                               class="w-full rounded-xl border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-700 text-surface-900 dark:text-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <x-ui.button variant="ghost" @click="closeModal()" :disabled="modal.loading">Cancelar</x-ui.button>
                    <x-ui.button @click="aprovar()" :loading="modal.loading" :disabled="modal.loading">
                        <span x-show="!modal.loading">Confirmar Aprovação</span>
                        <span x-show="modal.loading" x-cloak>Aprovando...</span>
                    </x-ui.button>
                </div>
            </div>
        </div>

    </x-ui.card>

</x-layouts.app>
