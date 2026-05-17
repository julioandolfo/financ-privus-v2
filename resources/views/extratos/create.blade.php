<x-layouts.app title="Importar Extrato">

    <div class="max-w-xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('extratos.index') }}" class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">Importar Extrato</h1>
                <p class="text-sm text-surface-500 mt-0.5">Suporta arquivos OFX e CSV</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('extratos.store') }}" enctype="multipart/form-data">
            @csrf

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Configuração</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
                        Conta Bancária <span class="text-red-500">*</span>
                    </label>
                    <select name="conta_bancaria_id" required
                        class="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm bg-white dark:bg-surface-800 text-surface-900 dark:text-white ring-1 ring-inset ring-surface-200 dark:ring-surface-700 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        <option value="">Selecione a conta</option>
                        @foreach($contas as $conta)
                        <option value="{{ $conta->id }}" @selected(old('conta_bancaria_id') == $conta->id)>
                            {{ $conta->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div x-data="{ dragging: false }" class="relative">
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
                        Arquivo <span class="text-red-500">*</span>
                    </label>
                    <label
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileName.textContent = $event.dataTransfer.files[0]?.name ?? 'Nenhum arquivo'"
                        :class="dragging ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'border-surface-200 dark:border-surface-700 hover:border-primary-300'"
                        class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-xl cursor-pointer transition-colors">
                        <svg class="w-8 h-8 text-surface-400 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        <p class="text-sm text-surface-500">Arraste o arquivo aqui ou <span class="text-primary-600 font-medium">clique para selecionar</span></p>
                        <p x-ref="fileName" class="text-xs text-surface-400 mt-1">Nenhum arquivo selecionado</p>
                        <input x-ref="fileInput" type="file" name="arquivo" accept=".ofx,.csv,.txt" class="hidden"
                            @change="$refs.fileName.textContent = $event.target.files[0]?.name ?? 'Nenhum arquivo'">
                    </label>
                </div>
            </x-ui.card>

            {{-- Formatos suportados --}}
            <x-ui.card class="mb-6">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-3">Formatos suportados</h2>
                <div class="space-y-3">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-12 text-center">
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400">OFX</span>
                        </div>
                        <p class="text-xs text-surface-500">Formato padrão exportado pelos principais bancos brasileiros (Itaú, Bradesco, Santander, BB, Caixa). Detecção automática de créditos e débitos.</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-12 text-center">
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-surface-100 dark:bg-surface-700 text-surface-700 dark:text-surface-300">CSV</span>
                        </div>
                        <div>
                            <p class="text-xs text-surface-500 mb-1">Dois formatos aceitos:</p>
                            <div class="text-xs font-mono bg-surface-50 dark:bg-surface-800 rounded-lg p-2 space-y-1">
                                <p class="text-surface-500">3 colunas: <span class="text-surface-700 dark:text-surface-300">Data, Descrição, Valor</span></p>
                                <p class="text-surface-400">01/05/2026, Pagamento fornecedor, -1500.00</p>
                                <p class="text-surface-400 mt-1">4 colunas: <span class="text-surface-700 dark:text-surface-300">Data, Descrição, Débito, Crédito</span></p>
                                <p class="text-surface-400">01/05/2026, Transferência recebida, , 5000.00</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('extratos.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                    Importar e Processar
                </x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
