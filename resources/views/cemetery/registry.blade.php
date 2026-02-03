<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registro Cimiteriale') }}
        </h2>
    </x-slot>

    <!-- Alpine.js Cemetery Registry Component -->
    <div x-data="cemeteryRegistry" class="py-12">
        <x-ui.container>

            <!-- Search Bar -->
            <div class="mb-8">
                <div class="max-w-xl mx-auto">
                    <div class="relative">
                        <input type="text" x-model.debounce.500ms="searchQuery" @input="searchAttributes"
                            placeholder="Cerca cimitero per nome o città..."
                            class="w-full border-gray-300 rounded-full py-3 px-6 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-lg">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center py-10">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-500"></div>
            </div>

            <!-- Results Grid -->
            <div x-show="!selectedCemetery && !isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="cemetery in cemeteries" :key="cemetery.id">
                    <div @click="selectCemetery(cemetery.id)"
                        class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-200 cursor-pointer p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-indigo-100 p-3 rounded-full">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-500" x-text="cemetery.city"></span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="cemetery.name"></h3>
                        <p class="text-gray-600 text-sm mb-4" x-text="cemetery.address"></p>
                        <div class="mt-4 pt-4 border-t border-funeral-50 flex justify-end">
                            <span class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Visualizza Mappa &rarr;
                            </span>
                        </div>
                    </div>
                </template>

                <div x-show="cemeteries.length === 0" class="col-span-full text-center py-10 text-gray-500">
                    Nessun cimitero trovato.
                </div>
            </div>

            <!-- Selected Cemetery Details & Map -->
            <div x-show="selectedCemetery" x-transition class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <button @click="selectedCemetery = null"
                            class="text-gray-500 hover:text-gray-700 text-sm flex items-center mb-1">
                            &larr; Torna alla ricerca
                        </button>
                        <h2 class="text-2xl font-bold text-gray-900" x-text="selectedCemetery?.name"></h2>
                        <p class="text-gray-600 text-sm"
                            x-text="selectedCemetery?.city + ' - ' + selectedCemetery?.address"></p>
                    </div>
                    <div class="flex space-x-2">
                        <template x-for="map in selectedCemetery?.maps" :key="map.name">
                            <a :href="map.url" target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                <span x-text="'Mappa: ' + map.name"></span>
                            </a>
                        </template>
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Aree e Settori</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <template x-for="area in selectedCemetery?.areas" :key="area.id">
                            <div
                                class="border border-gray-200 rounded-lg p-4 text-center hover:bg-gray-50 transition-colors">
                                <span class="block text-2xl font-bold text-gray-800" x-text="area.name"></span>
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Settore</span>
                            </div>
                        </template>
                    </div>

                    <div x-show="!selectedCemetery?.areas?.length" class="text-center py-6 text-gray-500 italic">
                        Nessuna informazione sui settori disponibile.
                    </div>
                </div>
            </div>

        </x-ui.container>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cemeteryRegistry', () => ({
                searchQuery: '',
                cemeteries: [],
                selectedCemetery: null,
                isLoading: false,

                init() {
                    this.searchAttributes(); // Load initial data
                },

                async searchAttributes() {
                    this.isLoading = true;
                    this.selectedCemetery = null; // Reset selection on search
                    try {
                        const response = await axios.get('/api/cemeteries', {
                            params: { q: this.searchQuery }
                        });
                        this.cemeteries = response.data.data;
                    } catch (error) {
                        console.error('Search error', error);
                        // Handle error
                    } finally {
                        this.isLoading = false;
                    }
                },

                async selectCemetery(id) {
                    this.isLoading = true;
                    try {
                        const response = await axios.get('/api/cemeteries/' + id + '/map');
                        this.selectedCemetery = response.data.data;
                    } catch (error) {
                        console.error('Detail error', error);
                        alert('Impossibile caricare i dettagli del cimitero.');
                    } finally {
                        this.isLoading = false;
                    }
                }
            }))
        })
    </script>
</x-app-layout>