<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-funeral-900 leading-tight">
                    {{ $funeral->deceased->full_name ?? 'Dettaglio Pratica' }}
                </h2>
                <p class="text-sm text-funeral-500 mt-1">
                    Pratica #{{ $funeral->funeral_code }} &bull; {{ ucfirst($funeral->service_type) }} &bull;
                    <span
                        class="text-funeral-700 font-medium">{{ $funeral->ceremony_location ?? 'Luogo da definire' }}</span>
                </p>
            </div>

            <div class="flex gap-2">
                <x-ui.button variant="secondary" size="sm" icon="printer">
                    Stampa
                </x-ui.button>
                <x-ui.button size="sm">
                    Salva modifiche
                </x-ui.button>
            </div>
        </div>
    </x-slot>

    <x-ui.container x-data="{ activeTab: 'overview' }">

        <!-- Tabs Navigation -->
        <div class="border-b border-funeral-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'overview'"
                    :class="activeTab === 'overview' ? 'border-funeral-900 text-funeral-900' : 'border-transparent text-funeral-500 hover:border-funeral-300 hover:text-funeral-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                    Panoramica
                </button>
                <button @click="activeTab = 'docs'"
                    :class="activeTab === 'docs' ? 'border-funeral-900 text-funeral-900' : 'border-transparent text-funeral-500 hover:border-funeral-300 hover:text-funeral-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors flex items-center gap-2">
                    Documenti
                    <span
                        class="bg-funeral-100 text-funeral-600 py-0.5 px-2 rounded-full text-xs hover:bg-funeral-200 transition-colors">3</span>
                </button>
                <button @click="activeTab = 'finance'"
                    :class="activeTab === 'finance' ? 'border-funeral-900 text-funeral-900' : 'border-transparent text-funeral-500 hover:border-funeral-300 hover:text-funeral-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                    Contabilità
                </button>
                <button @click="activeTab = 'family'"
                    :class="activeTab === 'family' ? 'border-funeral-900 text-funeral-900' : 'border-transparent text-funeral-500 hover:border-funeral-300 hover:text-funeral-700'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                    Accesso Famiglia
                </button>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- LEFT COLUMN (Main Content) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- TAB: OVERVIEW -->
                <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0">

                    <!-- Deceased Card -->
                    <x-ui.card header="Dati Anagrafici" class="mb-6">
                        <x-slot name="actions">
                            <button class="text-sm text-accent-gold hover:text-yellow-600 font-medium">Modifica</button>
                        </x-slot>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-funeral-500">Nome Completo</dt>
                                <dd class="mt-1 text-sm text-funeral-900 font-medium">
                                    {{ $funeral->deceased->full_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-funeral-500">Codice Fiscale</dt>
                                <dd class="mt-1 text-sm text-funeral-900 font-mono">{{ $funeral->deceased->tax_code }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-funeral-500">Nascita</dt>
                                <dd class="mt-1 text-sm text-funeral-900">
                                    {{ $funeral->deceased->birth_date?->format('d/m/Y') }}
                                    ({{ $funeral->deceased->birth_city }})</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-funeral-500">Decesso</dt>
                                <dd class="mt-1 text-sm text-funeral-900">
                                    {{ $funeral->death_date?->format('d/m/Y H:i') }} ({{ $funeral->death_city }})</dd>
                            </div>
                        </dl>
                    </x-ui.card>

                    <!-- Ceremony Card -->
                    <x-ui.card header="Dettagli Cerimonia">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-funeral-500">Note Interne</dt>
                                <dd
                                    class="mt-1 text-sm text-funeral-900 bg-funeral-50 p-3 rounded-md border border-funeral-100 italic">
                                    {{ $funeral->notes ?: 'Nessuna nota operativa inserita.' }}
                                </dd>
                            </div>
                        </dl>
                    </x-ui.card>
                </div>

                <!-- TAB: DOCUMENTS -->
                <div x-show="activeTab === 'docs'" x-cloak>
                    <x-ui.card header="Lista Documenti">
                        <div class="space-y-4">
                            <!-- Helper -->
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex gap-3">
                                <svg class="h-5 w-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-blue-800">Stato Documentazione</h4>
                                    <p class="text-xs text-blue-600 mt-1">
                                        Per procedere con la sepoltura sono necessari il "Permesso di Seppellimento" e
                                        l'atto di morte originale.
                                    </p>
                                </div>
                            </div>

                            <!-- List -->
                            <ul class="divide-y divide-funeral-100">
                                <li class="py-3 flex justify-between items-center">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-8 w-8 rounded-full bg-status-success-bg flex items-center justify-center text-status-success-text">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-funeral-900">Certificato di Morte (ISTAT)
                                            </p>
                                            <p class="text-xs text-funeral-500">Caricato il 01/02/2026</p>
                                        </div>
                                    </div>
                                    <x-ui.button variant="ghost" size="sm">Vedi</x-ui.button>
                                </li>
                                <li class="py-3 flex justify-between items-center">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-8 w-8 rounded-full bg-status-warning-bg flex items-center justify-center text-status-warning-text">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-funeral-900">Permesso di Seppellimento
                                            </p>
                                            <p class="text-xs text-funeral-500">In attesa di firma Comune</p>
                                        </div>
                                    </div>
                                    <x-ui.button variant="secondary" size="sm">Sollecita</x-ui.button>
                                </li>
                            </ul>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Other tabs placeholders -->
                <div x-show="activeTab === 'finance'" x-cloak>
                    <x-ui.card header="Contabilità">
                        <p class="text-funeral-500 py-4">Dettagli preventivi e fatture qui.</p>
                    </x-ui.card>
                </div>
                <div x-show="activeTab === 'family'" x-cloak>
                    <x-ui.card header="Accesso Famiglia">
                        <p class="text-funeral-500 py-4">Gestione token e QR code.</p>
                    </x-ui.card>
                </div>

            </div>

            <!-- RIGHT COLUMN (Timeline & Context) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Timeline Widget -->
                <x-ui.card header="Avanzamento">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($funeral->timeline as $step)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-funeral-200"
                                                aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span
                                                    class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ $step->status === 'completed' ? 'bg-status-success-bg text-status-success-text' : ($step->status === 'in_progress' ? 'bg-status-info-bg text-status-info-text' : 'bg-funeral-100 text-funeral-400') }}">
                                                    @if($step->status === 'completed')
                                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                                                            aria-hidden="true">
                                                            <path fill-rule="evenodd"
                                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    @else
                                                        <span class="h-2.5 w-2.5 rounded-full bg-current"
                                                            aria-hidden="true"></span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-funeral-900 font-medium">{{ $step->step_name }}
                                                    </p>
                                                </div>
                                                <div class="whitespace-nowrap text-right text-xs text-funeral-500">
                                                    <time>{{ $step->updated_at->format('d/m') }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </x-ui.card>
            </div>

        </div>
    </x-ui.container>
</x-app-layout>