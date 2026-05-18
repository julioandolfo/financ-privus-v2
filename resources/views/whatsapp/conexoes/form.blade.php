@php
    $isEdit  = isset($config) && $config->exists;
    $title   = $isEdit ? 'Editar Conexão' : 'Nova Conexão';
    $action  = $isEdit ? route('whatsapp.conexoes.update', $config) : route('whatsapp.conexoes.store');
    $method  = $isEdit ? 'PUT' : 'POST';
@endphp

<x-layouts.app :title="$title">

    <div class="max-w-2xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('whatsapp.conexoes.index') }}"
               class="text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-surface-900 dark:text-white">{{ $title }}</h1>
                <p class="text-sm text-surface-500 mt-0.5">Configuração da instância Evolution API</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ $action }}">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Identificação</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-ui.input
                            name="nome"
                            label="Nome da Conexão"
                            required
                            placeholder="Ex: WhatsApp Principal"
                            value="{{ old('nome', $config->nome ?? '') }}"
                            :error="$errors->first('nome')"
                        />
                    </div>
                    <x-ui.select name="provider" label="Provider" :error="$errors->first('provider')">
                        <option value="evolution" @selected(old('provider', $config->provider ?? 'evolution') === 'evolution')>Evolution API</option>
                        <option value="baileys"   @selected(old('provider', $config->provider ?? '') === 'baileys')>Baileys</option>
                        <option value="cloud"     @selected(old('provider', $config->provider ?? '') === 'cloud')>Cloud API</option>
                    </x-ui.select>
                    <x-ui.input
                        name="numero_remetente"
                        label="Número Remetente"
                        placeholder="5511999999999"
                        value="{{ old('numero_remetente', $config->numero_remetente ?? '') }}"
                        :error="$errors->first('numero_remetente')"
                        hint="Formato: DDI+DDD+número"
                    />
                </div>
            </x-ui.card>

            <x-ui.card class="mb-4">
                <h2 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-4">Conexão API</h2>
                <div class="grid grid-cols-1 gap-4">
                    <x-ui.input
                        name="base_url"
                        label="Base URL"
                        required
                        type="url"
                        placeholder="https://evolution.seudominio.com"
                        value="{{ old('base_url', $config->base_url ?? '') }}"
                        :error="$errors->first('base_url')"
                        hint="URL base da sua instância Evolution API"
                    />
                    <x-ui.input
                        name="instance_name"
                        label="Nome da Instância"
                        placeholder="default"
                        value="{{ old('instance_name', $config->instance_name ?? '') }}"
                        :error="$errors->first('instance_name')"
                        hint="Nome da instância configurada no Evolution API"
                    />
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
                            API Key {{ $isEdit ? '(deixe vazio para manter a atual)' : '' }}
                            @if(!$isEdit) <span class="text-red-500">*</span> @endif
                        </label>
                        <input
                            type="password"
                            name="api_key"
                            {{ !$isEdit ? 'required' : '' }}
                            autocomplete="new-password"
                            placeholder="{{ $isEdit ? '••••••••••••••••' : 'Sua API Key' }}"
                            value="{{ old('api_key') }}"
                            class="block w-full rounded-xl border border-surface-200 dark:border-surface-600 bg-white dark:bg-surface-800 px-3.5 py-2.5 text-sm text-surface-900 dark:text-white placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors {{ $errors->has('api_key') ? 'border-red-300 focus:ring-red-500' : '' }}"
                        >
                        @error('api_key')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-surface-700 dark:text-surface-300">Conexão ativa</p>
                        <p class="text-xs text-surface-400 mt-0.5">Desative para pausar envios sem excluir a conexão</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" name="ativo" value="1"
                               {{ old('ativo', $config->ativo ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-surface-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-surface-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-surface-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('whatsapp.conexoes.index') }}" variant="ghost">Cancelar</x-ui.button>
                <x-ui.button type="submit">
                    {{ $isEdit ? 'Atualizar Conexão' : 'Salvar Conexão' }}
                </x-ui.button>
            </div>
        </form>
    </div>

</x-layouts.app>
