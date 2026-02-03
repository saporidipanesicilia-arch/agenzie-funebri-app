<x-guest-layout>
    <!-- Alpine Component for Dashboard Logic -->
    <div x-data="familyDashboard" x-cloak>

        <!-- Header -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-800">Area Riservata Famiglia</h1>
                    </div>
                    <div class="flex items-center">
                        <button @click="logout" class="text-sm text-gray-600 hover:text-gray-900 focus:outline-none">
                            Esci
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Loading State -->
        <div x-show="isLoading" class="flex justify-center items-center h-screen">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
        </div>

        <!-- Main Content -->
        <div x-show="!isLoading && data" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Deceased Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            <span x-text="data?.deceased?.name"></span> <span x-text="data?.deceased?.surname"></span>
                        </h2>
                        <p class="text-gray-600">
                            Nato a <span x-text="data?.deceased?.birth_city"></span> il <span
                                x-text="formatDate(data?.deceased?.birth_date)"></span>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Timeline Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Stato della Pratica</h3>
                            <ul class="space-y-4">
                                <template x-for="step in data?.timeline" :key="step.id">
                                    <li class="flex items-center">
                                        <div :class="{
                                            'bg-green-500': step.status === 'completed',
                                            'bg-yellow-500': step.status === 'in_progress',
                                            'bg-gray-300': step.status === 'pending'
                                        }" class="w-3 h-3 rounded-full mr-3"></div>
                                        <span x-text="step.name" class="text-gray-700"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- Documents Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Documenti Condivisi</h3>
                            <ul class="divide-y divide-gray-200">
                                <template x-for="doc in data?.documents" :key="doc.id">
                                    <li class="py-3 flex justify-between items-center">
                                        <div class="flex items-center">
                                            <svg class="h-8 w-8 text-funeral-400 mr-3" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span x-text="doc.name" class="text-base font-medium text-gray-900"></span>
                                        </div>
                                        <a :href="doc.url" target="_blank"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Scarica
                                        </a>
                                    </li>
                                </template>
                                <li x-show="!data?.documents?.length" class="py-3 text-gray-500 text-sm italic">
                                    Nessun documento condiviso al momento.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('familyDashboard', () => ({
                isLoading: true,
                data: null,

                init() {
                    this.fetchData();
                },

                async fetchData() {
                    const token = localStorage.getItem('family_token');
                    if (!token) {
                        window.location.href = "{{ route('family.login') }}";
                        return;
                    }

                    try {
                        const response = await axios.get('/api/family/dashboard', {
                            headers: { 'X-Family-Token': token }
                        });
                        this.data = response.data.data;
                    } catch (error) {
                        console.error('Dashboard Error', error);
                        alert('Sessione scaduta o non valida.');
                        this.logout();
                    } finally {
                        this.isLoading = false;
                    }
                },

                logout() {
                    localStorage.removeItem('family_token');
                    window.location.href = "{{ route('family.login') }}";
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    return new Date(dateString).toLocaleDateString('it-IT');
                }
            }))
        })
    </script>
</x-guest-layout>