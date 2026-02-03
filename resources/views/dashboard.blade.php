<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-funeral-900 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <!-- Stats Grid using Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">

        <!-- Stat 1 -->
        <x-ui.card class="border-l-4 border-l-status-info-text">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-status-info-bg rounded-md p-3">
                    <svg class="h-6 w-6 text-status-info-text" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-funeral-500 truncate">Funerali in corso</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-funeral-900">3</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </x-ui.card>

        <!-- Stat 2 -->
        <x-ui.card class="border-l-4 border-l-status-warning-text">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-status-warning-bg rounded-md p-3">
                    <svg class="h-6 w-6 text-status-warning-text" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-funeral-500 truncate">Scadenze Cimitero</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-funeral-900">12</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </x-ui.card>

        <!-- Stat 3 -->
        <x-ui.card class="border-l-4 border-l-status-success-text">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-status-success-bg rounded-md p-3">
                    <svg class="h-6 w-6 text-status-success-text" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-funeral-500 truncate">Fatturato Mese</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-funeral-900">€ 12.450</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </x-ui.card>

        <!-- Stat 4 -->
        <x-ui.card class="border-l-4 border-l-accent-gold">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-amber-50 rounded-md p-3">
                    <svg class="h-6 w-6 text-accent-gold" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-funeral-500 truncate">Documenti da Firmare</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-funeral-900">5</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- Active Funerals Table -->
    <x-ui.card noPadding>
        <div class="border-b border-funeral-100 px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-base font-semibold leading-6 text-funeral-900">Funerali Recenti</h3>
                <p class="mt-1 text-sm text-funeral-500">Stato avanzamento pratiche in corso.</p>
            </div>
            <x-ui.button size="sm">Nuovo Funerale</x-ui.button>
        </div>
        <x-ui.table :headers="['Defunto', 'Data Cerimonia', 'Stato', 'Cimitero']">
            <tr>
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-funeral-900 sm:pl-6">
                    Rossi Mario
                    <span class="block text-xs font-normal text-funeral-500">Decesso: 01/02/2026</span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-funeral-500 hidden sm:table-cell">04/02/2026 10:30
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <x-ui.badge status="info" label="In Corso" />
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-funeral-500 hidden lg:table-cell">Monumentale (MI)
                </td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <a href="#" class="text-funeral-600 hover:text-funeral-900">Gestisci</a>
                </td>
            </tr>
            <tr>
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-funeral-900 sm:pl-6">
                    Bianchi Giuseppe
                    <span class="block text-xs font-normal text-funeral-500">Decesso: 31/01/2026</span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-funeral-500 hidden sm:table-cell">03/02/2026 15:00
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <x-ui.badge status="neutral" label="Pianificazione" />
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-funeral-500 hidden lg:table-cell">Maggiore (MI)</td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <a href="#" class="text-funeral-600 hover:text-funeral-900">Gestisci</a>
                </td>
            </tr>
        </x-ui.table>
    </x-ui.card>
</x-app-layout>