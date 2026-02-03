<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"
        x-data="familyLogin">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">

            <div class="mb-5 text-center">
                <h2 class="text-2xl font-bold text-gray-800">Accesso Famiglia</h2>
                <p class="text-sm text-gray-600 mt-2">Inserisci il codice di accesso fornito dall'agenzia.</p>
            </div>

            <!-- Error Message -->
            <div x-show="errorMessage"
                class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline" x-text="errorMessage"></span>
            </div>

            <form @submit.prevent="login">
                <!-- Access Token Input -->
                <div>
                    <x-input-label for="token" :value="__('Codice Accesso')" />
                    <x-text-input id="token" class="block mt-1 w-full" type="text" name="token" x-model="token" required
                        autofocus />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-primary-button class="ml-3" :disabled="isLoading">
                        <span x-show="!isLoading">{{ __('Accedi') }}</span>
                        <span x-show="isLoading">{{ __('Verifica in corso...') }}</span>
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('familyLogin', () => ({
                token: '',
                isLoading: false,
                errorMessage: '',

                async login() {
                    this.isLoading = true;
                    this.errorMessage = '';

                    try {
                        const response = await axios.post('/api/family/login', {
                            token: this.token
                        });

                        // Store token in localStorage for dashboard access
                        localStorage.setItem('family_token', this.token);

                        // Redirect
                        window.location.href = response.data.redirect_url;

                    } catch (error) {
                        this.errorMessage = error.response?.data?.message || 'Errore durante l\'accesso. Verifica il codice.';
                    } finally {
                        this.isLoading = false;
                    }
                }
            }))
        })
    </script>
</x-guest-layout>