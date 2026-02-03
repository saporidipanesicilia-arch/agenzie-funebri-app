<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tavolo delle Memorie') }}
        </h2>
    </x-slot>

    <!-- Alpine.js Product Catalog Component -->
    <div x-data="productCatalog" class="py-12">
        <x-ui.container>

            <!-- Filter & Search Bar -->
            <div class="mb-6 flex space-x-4">
                <input type="text" x-model="search" placeholder="Cerca prodotto..."
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                <select x-model="category" @change="fetchProducts()"
                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Tutte le categorie</option>
                    <option value="coffins">Cofani</option>
                    <option value="urns">Urne</option>
                    <option value="flowers">Fiori</option>
                </select>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center py-10">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-500"></div>
            </div>

            <!-- Error State -->
            <div x-show="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                <strong class="font-bold">Errore!</strong>
                <span class="block sm:inline" x-text="error"></span>
            </div>

            <!-- Product Grid -->
            <div x-show="!isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="product in filteredProducts" :key="product.id">
                    <x-ui.card class="hover:shadow-lg transition-shadow duration-200">
                        <x-slot name="header">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-bold text-gray-900" x-text="product.name"></h3>
                                <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded"
                                    x-text="product.category"></span>
                            </div>
                        </x-slot>

                        <div class="p-4">
                            <div class="aspect-w-16 aspect-h-9 mb-4 bg-gray-200 rounded-lg overflow-hidden">
                                <!-- Placeholder Image Logic -->
                                <img :src="product.image_url || 'https://via.placeholder.com/400x300'"
                                    alt="Product Image" class="object-cover w-full h-48">
                            </div>
                            <p class="text-gray-600 mb-4"
                                x-text="product.description || 'Nessuna descrizione disponibile.'"></p>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold text-gray-900" x-text="'€ ' + product.price"></span>
                                <x-ui.button variant="secondary">
                                    Dettagli
                                </x-ui.button>
                            </div>
                        </div>
                    </x-ui.card>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="!isLoading && filteredProducts.length === 0" class="text-center py-10">
                <p class="text-gray-500 text-lg">Nessun prodotto trovato.</p>
            </div>

        </x-ui.container>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productCatalog', () => ({
                products: [],
                isLoading: true,
                error: null,
                category: '',
                search: '',

                init() {
                    this.fetchProducts();
                },

                async fetchProducts() {
                    this.isLoading = true;
                    this.error = null;
                    try {
                        // Call Backend API
                        const params = {};
                        if (this.category) params.category = this.category;

                        const response = await axios.get('/api/products', { params });
                        this.products = response.data.data;
                    } catch (err) {
                        console.error(err);
                        this.error = 'Impossibile caricare i prodotti. Riprova più tardi.';
                    } finally {
                        this.isLoading = false;
                    }
                },

                get filteredProducts() {
                    if (!this.search) return this.products;
                    const lowerSearch = this.search.toLowerCase();
                    return this.products.filter(p =>
                        p.name.toLowerCase().includes(lowerSearch) ||
                        (p.description && p.description.toLowerCase().includes(lowerSearch))
                    );
                }
            }));
        });
    </script>
</x-app-layout>