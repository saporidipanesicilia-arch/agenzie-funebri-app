<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-funeral-900 leading-tight">
                    Elenco Funerali
                </h2>
                <p class="text-sm text-funeral-500 mt-1">Gestione delle pratiche attive e storico.</p>
            </div>

            <x-ui.button href="{{ route('funerals.create-wizard') }}" icon="+">
                Nuova Pratica
            </x-ui.button>
        </div>
    </x-slot>

    <x-ui.container>

        <!-- Search & Filter Bar -->
        <x-ui.card class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">Cerca</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-funeral-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" name="search" id="search"
                            class="block w-full rounded-md border-0 py-1.5 pl-10 text-funeral-900 ring-1 ring-inset ring-funeral-200 placeholder:text-funeral-400 focus:ring-2 focus:ring-inset focus:ring-funeral-900 sm:text-sm sm:leading-6"
                            placeholder="Cerca per nome defunto, codice fiscale...">
                    </div>
                </div>
                <div class="flex gap-2">
                    <select
                        class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-funeral-900 ring-1 ring-inset ring-funeral-200 focus:ring-2 focus:ring-inset focus:ring-funeral-900 sm:text-sm sm:leading-6">
                        <option>Tutti gli stati</option>
                        <option>In corso</option>
                        <option>Pianificazione</option>
                        <option>Concluso</option>
                    </select>
                </div>
            </div>
        </x-ui.card>

        <!-- Funerals List -->
        <x-ui.card noPadding>
            <x-ui.table :headers="['Codice', 'Defunto', 'Data Cerimonia', 'Stato', 'Step']">
                @forelse($funerals as $funeral)
                    <tr class="hover:bg-funeral-50 transition-colors">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-funeral-500 sm:pl-6">
                            #{{ $funeral->funeral_code }}
                        </td>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-funeral-900">
                            {{ $funeral->deceased->full_name ?? 'N/D' }}
                            <span class="block text-xs font-normal text-funeral-500">Decesso:
                                {{ $funeral->death_date?->format('d/m/Y') ?? '-' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-funeral-600">
                            {{ $funeral->ceremony_date?->format('d/m/Y H:i') ?? 'Da definire' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @php
                                $statusMap = [
                                    'active' => 'success',
                                    'planned' => 'info',
                                    'completed' => 'neutral',
                                    'suspended' => 'error'
                                ];
                                $statusLabel = [
                                    'active' => 'In Corso',
                                    'planned' => 'Pianificazione',
                                    'completed' => 'Concluso',
                                    'suspended' => 'Sospeso'
                                ];
                            @endphp
                            <x-ui.badge :status="$statusMap[$funeral->status] ?? 'neutral'"
                                :label="$statusLabel[$funeral->status] ?? ucfirst($funeral->status)" />
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-funeral-500">
                            {{ $funeral->timeline->where('status', 'in_progress')->first()?->step_name ?? '-' }}
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <a href="{{ route('funerals.show', $funeral->id) }}"
                                class="text-funeral-600 hover:text-funeral-900 font-semibold">Gestisci</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-12 text-center text-sm text-funeral-500">
                            <svg class="mx-auto h-12 w-12 text-funeral-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2">Nessun funerale trovato.</p>
                            <p class="mt-1">
                                <a href="{{ route('funerals.create-wizard') }}"
                                    class="text-funeral-900 underline hover:text-funeral-700">Crea la prima pratica</a>
                            </p>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <div class="mt-4">
            {{ $funerals->links() }}
        </div>

    </x-ui.container>
</x-app-layout>