<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-funeral-900 leading-tight">
                Nuova Pratica Funeraria
            </h2>
            <x-ui.button variant="ghost" size="sm" onclick="window.history.back()">
                Annulla e Esci
            </x-ui.button>
        </div>
    </x-slot>

    <!-- Alpine.js Wizard State -->
    <div x-data="funeralWizard" class="pb-20">
        <!-- Global Error Banner -->
        <div x-show="Object.keys(errors).length > 0" x-cloak
            class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 fixed top-20 right-4 left-4 lg:left-auto lg:right-4 z-50 shadow-lg rounded-r lg:w-96"
            x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm leading-5 font-medium text-red-800">
                        Si sono verificati degli errori
                    </h3>
                    <div class="mt-2 text-sm leading-5 text-red-700">
                        <p>Controlla i campi evidenziati.</p>
                    </div>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button @click="errors = {}"
                            class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:bg-red-100 transition ease-in-out duration-150">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Stepper -->
        <x-ui.container class="mb-8">
            <div
                class="relative after:absolute after:inset-x-0 after:top-1/2 after:block after:h-0.5 after:-translate-y-1/2 after:rounded-lg after:bg-funeral-200">
                <ol class="relative z-10 flex justify-between text-sm font-medium text-funeral-500">

                    <!-- Step 1 -->
                    <li class="flex items-center gap-2 bg-funeral-base p-2">
                        <span @click="jump(1)"
                            class="cursor-pointer h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold ring-4 ring-funeral-base transition-all duration-200"
                            :class="step >= 1 ? 'bg-funeral-900 text-white' : 'bg-funeral-200 text-funeral-600 hover:bg-funeral-300'">
                            1
                        </span>
                        <span class="hidden sm:block" :class="step >= 1 ? 'text-funeral-900' : ''">Defunto</span>
                    </li>

                    <!-- Step 2 -->
                    <li class="flex items-center gap-2 bg-funeral-base p-2">
                        <span @click="jump(2)"
                            class="cursor-pointer h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold ring-4 ring-funeral-base transition-all duration-200"
                            :class="step >= 2 ? 'bg-funeral-900 text-white' : 'bg-funeral-200 text-funeral-600 hover:bg-funeral-300'">
                            2
                        </span>
                        <span class="hidden sm:block" :class="step >= 2 ? 'text-funeral-900' : ''">Cerimonia</span>
                    </li>

                    <!-- Step 3 -->
                    <li class="flex items-center gap-2 bg-funeral-base p-2">
                        <span @click="jump(3)"
                            class="cursor-pointer h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold ring-4 ring-funeral-base transition-all duration-200"
                            :class="step >= 3 ? 'bg-funeral-900 text-white' : 'bg-funeral-200 text-funeral-600 hover:bg-funeral-300'">
                            3
                        </span>
                        <span class="hidden sm:block" :class="step >= 3 ? 'text-funeral-900' : ''">Prodotti</span>
                    </li>

                    <!-- Step 4 -->
                    <li class="flex items-center gap-2 bg-funeral-base p-2">
                        <span @click="jump(4)"
                            class="cursor-pointer h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold ring-4 ring-funeral-base transition-all duration-200"
                            :class="step >= 4 ? 'bg-funeral-900 text-white' : 'bg-funeral-200 text-funeral-600 hover:bg-funeral-300'">
                            4
                        </span>
                        <span class="hidden sm:block" :class="step >= 4 ? 'text-funeral-900' : ''">Documenti</span>
                    </li>

                    <!-- Step 5 -->
                    <li class="flex items-center gap-2 bg-funeral-base p-2">
                        <span @click="jump(5)"
                            class="cursor-pointer h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold ring-4 ring-funeral-base transition-all duration-200"
                            :class="step >= 5 ? 'bg-funeral-900 text-white' : 'bg-funeral-200 text-funeral-600 hover:bg-funeral-300'">
                            5
                        </span>
                        <span class="hidden sm:block" :class="step >= 5 ? 'text-funeral-900' : ''">Riepilogo</span>
                    </li>
                </ol>
            </div>
        </x-ui.container>

        <x-ui.container>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Main Form Area (Steps) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- STEP 1: DECEASED -->
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <x-ui.card header="Dati del Defunto">
                            <p class="mb-6 text-sm text-funeral-500">Inserisci i dati anagrafici fondamentali per
                                avviare la pratica.</p>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="deceased_name"
                                        class="block text-sm font-medium text-funeral-700">Nome</label>
                                    <input type="text" id="deceased_name" x-model="formData.deceased.name"
                                        :class="errors['deceased_name'] ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-funeral-200 focus:border-funeral-500 focus:ring-funeral-500'"
                                        class="mt-1 block w-full rounded-md shadow-sm sm:text-sm h-10 px-3">
                                    <p x-show="errors['deceased_name']" x-text="errors['deceased_name']"
                                        class="mt-1 text-xs text-red-600 font-medium"></p>
                                </div>
                                <div>
                                    <label for="deceased_surname"
                                        class="block text-sm font-medium text-funeral-700">Cognome</label>
                                    <input type="text" id="deceased_surname" x-model="formData.deceased.surname"
                                        :class="errors['deceased_surname'] ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-funeral-200 focus:border-funeral-500 focus:ring-funeral-500'"
                                        class="mt-1 block w-full rounded-md shadow-sm sm:text-sm h-10 px-3">
                                    <p x-show="errors['deceased_surname']" x-text="errors['deceased_surname']"
                                        class="mt-1 text-xs text-red-600 font-medium"></p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="deceased_tax_code"
                                        class="block text-sm font-medium text-funeral-700">Codice Fiscale</label>
                                    <input type="text" id="deceased_tax_code" x-model="formData.deceased.tax_code"
                                        :class="errors['deceased_tax_code'] ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-funeral-200 focus:border-funeral-500 focus:ring-funeral-500'"
                                        class="mt-1 block w-full rounded-md shadow-sm sm:text-sm h-10 px-3 uppercase">
                                    <p x-show="errors['deceased_tax_code']" x-text="errors['deceased_tax_code']"
                                        class="mt-1 text-xs text-red-600 font-medium"></p>
                                </div>
                                <div>
                                    <label for="deceased_birth_date"
                                        class="block text-sm font-medium text-funeral-700">Data di Nascita</label>
                                    <input type="date" id="deceased_birth_date" x-model="formData.deceased.birth_date"
                                        :class="errors['deceased_birth_date'] ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-funeral-200 focus:border-funeral-500 focus:ring-funeral-500'"
                                        class="mt-1 block w-full rounded-md shadow-sm sm:text-sm h-10 px-3">
                                    <p x-show="errors['deceased_birth_date']" x-text="errors['deceased_birth_date']"
                                        class="mt-1 text-xs text-red-600 font-medium"></p>
                                </div>
                                <div>
                                    <label for="deceased_birth_city"
                                        class="block text-sm font-medium text-funeral-700">Luogo di Nascita</label>
                                    <input type="text" id="deceased_birth_city" x-model="formData.deceased.birth_city"
                                        :class="errors['deceased_birth_city'] ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-funeral-200 focus:border-funeral-500 focus:ring-funeral-500'"
                                        class="mt-1 block w-full rounded-md shadow-sm sm:text-sm h-10 px-3">
                                    <p x-show="errors['deceased_birth_city']" x-text="errors['deceased_birth_city']"
                                        class="mt-1 text-xs text-red-600 font-medium"></p>
                                </div>
                                <div>
                                    <label for="deceased_death_date"
                                        class="block text-sm font-medium text-funeral-700">Data del Decesso</label>
                                    <input type="datetime-local" id="deceased_death_date"
                                        x-model="formData.deceased.death_date"
                                        :class="errors['deceased_death_date'] ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-funeral-200 focus:border-funeral-500 focus:ring-funeral-500'"
                                        class="mt-1 block w-full rounded-md shadow-sm sm:text-sm h-10 px-3">
                                    <p x-show="errors['deceased_death_date']" x-text="errors['deceased_death_date']"
                                        class="mt-1 text-xs text-red-600 font-medium"></p>
                                </div>
                                <div>
                                    <label for="deceased_death_city"
                                        class="block text-sm font-medium text-funeral-700">Luogo del Decesso</label>
                                    <input type="text" id="deceased_death_city" x-model="formData.deceased.death_city"
                                        :class="errors['deceased_death_city'] ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-funeral-200 focus:border-funeral-500 focus:ring-funeral-500'"
                                        class="mt-1 block w-full rounded-md shadow-sm sm:text-sm h-10 px-3">
                                    <p x-show="errors['deceased_death_city']" x-text="errors['deceased_death_city']"
                                        class="mt-1 text-xs text-red-600 font-medium"></p>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- STEP 2: CEREMONY -->
                    <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <x-ui.card header="Dettagli Cerimonia">
                            <div class="space-y-6">
                                <div>
                                    <label class="text-sm font-medium text-funeral-700">Tipo di Servizio</label>
                                    <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-3 sm:gap-x-4">
                                        <!-- Card Selection -->
                                        <div @click="formData.ceremony.type = 'burial'"
                                            class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none"
                                            :class="formData.ceremony.type === 'burial' ? 'border-funeral-900 ring-2 ring-funeral-900' : 'border-funeral-200'">
                                            <span class="flex flex-1">
                                                <span class="flex flex-col">
                                                    <span class="block text-sm font-medium text-funeral-900">Inumazione
                                                        / Tumulazione</span>
                                                    <span
                                                        class="mt-1 flex items-center text-sm text-funeral-500">Sepoltura
                                                        tradizionale</span>
                                                </span>
                                            </span>
                                            <span class="pointer-events-none absolute -inset-px rounded-lg border-2"
                                                :class="formData.ceremony.type === 'burial' ? 'border-funeral-900' : 'border-transparent'"
                                                aria-hidden="true"></span>
                                        </div>

                                        <div @click="formData.ceremony.type = 'cremation'"
                                            class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none"
                                            :class="formData.ceremony.type === 'cremation' ? 'border-funeral-900 ring-2 ring-funeral-900' : 'border-funeral-200'">
                                            <span class="flex flex-1">
                                                <span class="flex flex-col">
                                                    <span
                                                        class="block text-sm font-medium text-funeral-900">Cremazione</span>
                                                    <span class="mt-1 flex items-center text-sm text-funeral-500">Con
                                                        urne cinerarie</span>
                                                </span>
                                            </span>
                                            <span class="pointer-events-none absolute -inset-px rounded-lg border-2"
                                                :class="formData.ceremony.type === 'cremation' ? 'border-funeral-900' : 'border-transparent'"
                                                aria-hidden="true"></span>
                                        </div>
                                    </div>
                                </div>
                                <div x-show="formData.ceremony.type === 'burial'" x-transition>
                                    <label class="block text-sm font-medium text-funeral-700">Cimitero di
                                        destinazione</label>
                                    <select x-model="formData.ceremony.location"
                                        :class="errors['ceremony_location'] ? 'border-red-500' : 'border-funeral-200'"
                                        class="mt-1 block w-full rounded-md shadow-sm focus:border-funeral-500 focus:ring-funeral-500 sm:text-sm h-10 px-3">
                                        <option value="">Seleziona Cimitero...</option>
                                        <template x-for="cem in cemeteries" :key="cem.id">
                                            <option :value="cem.name + ' (' + cem.city + ')'"
                                                x-text="cem.name + ' (' + cem.city + ')'"></option>
                                        </template>
                                    </select>
                                    <p x-show="errors['ceremony_location']" x-text="errors['ceremony_location']"
                                        class="mt-1 text-xs text-red-600 font-medium"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-funeral-700">Data e Ora
                                        Cerimonia</label>
                                    <input type="datetime-local" x-model="formData.ceremony.date"
                                        :class="errors['ceremony_date'] ? 'border-red-500' : 'border-funeral-200'"
                                        class="mt-1 block w-full rounded-md shadow-sm focus:border-funeral-500 focus:ring-funeral-500 sm:text-sm h-10 px-3">
                                    <p x-show="errors['ceremony_date']" x-text="errors['ceremony_date']"
                                        class="mt-1 text-xs text-red-600 font-medium"></p>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- STEP 3: MEMORIAL TABLE (PRODUCTS) -->
                    <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">

                        <!-- Product Logic (Dynamic) -->
                        <div>
                            <!-- Header / Category Filter -->
                            <div class="mb-8 overflow-x-auto pb-2">
                                <nav class="flex space-x-2" aria-label="Categories">
                                    <button @click="activeCategory = 'coffin'"
                                        :class="activeCategory === 'coffin' ? 'bg-funeral-900 text-white' : 'bg-white text-funeral-600 hover:bg-funeral-100'"
                                        class="rounded-full px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap border border-funeral-200 shadow-sm">
                                        Cofani
                                    </button>
                                    <button @click="activeCategory = 'urn'"
                                        :class="activeCategory === 'urn' ? 'bg-funeral-900 text-white' : 'bg-white text-funeral-600 hover:bg-funeral-100'"
                                        class="rounded-full px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap border border-funeral-200 shadow-sm">
                                        Urne Cinerarie
                                    </button>
                                    <button @click="activeCategory = 'flower'"
                                        :class="activeCategory === 'flower' ? 'bg-funeral-900 text-white' : 'bg-white text-funeral-600 hover:bg-funeral-100'"
                                        class="rounded-full px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap border border-funeral-200 shadow-sm">
                                        Composizioni Floreali
                                    </button>
                                </nav>
                            </div>

                            <!-- Interactive Product Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <!-- Product Card -->
                                    <div @click="toggleItem(product)"
                                        class="group relative bg-white rounded-xl shadow-sm border overflow-hidden cursor-pointer transition-all duration-200 hover:shadow-md"
                                        :class="isSelected(product) ? 'border-funeral-900 ring-2 ring-funeral-900 shadow-md' : 'border-funeral-200'">

                                        <!-- Image Container (AspectRatio 4:3) -->
                                        <div class="aspect-[4/3] bg-funeral-100 relative overflow-hidden">
                                            <img :src="product.image" :alt="product.name"
                                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">

                                            <!-- Selected Indicator -->
                                            <div x-show="isSelected(product)" x-transition
                                                class="absolute top-2 right-2 bg-funeral-900 text-white rounded-full p-1 shadow-lg">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Content -->
                                        <div class="p-4">
                                            <div class="flex justify-between items-start mb-2">
                                                <h3 class="text-base font-medium text-funeral-900"
                                                    x-text="product.name"></h3>
                                            </div>
                                            <p class="text-xs text-funeral-500 line-clamp-2 mb-3"
                                                x-text="product.description"></p>

                                            <div class="flex justify-end items-center border-t border-funeral-50 pt-3">
                                                <span class="text-lg font-semibold text-funeral-900"
                                                    x-text="'€ ' + product.price.toLocaleString()"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Live Price Widget (Only visible in this step as a floating toast on Mobile, or integrated) -->
                            <div
                                class="mt-8 border-t border-funeral-200 pt-4 flex justify-between items-center bg-funeral-50 p-4 rounded-lg lg:hidden">
                                <span class="text-sm font-medium text-funeral-600">Totale stimato articoli:</span>
                                <span class="text-xl font-bold text-funeral-900"
                                    x-text="'€ ' + currentTotal.toLocaleString()"></span>
                            </div>

                        </div>
                    </div>

                    <!-- STEP 4: DOCUMENTS CHECKLIST -->
                    <div x-show="step === 4" x-cloak x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <x-ui.card header="Checklist Documenti">
                            <p class="mb-6 text-sm text-funeral-500">
                                In base alle scelte effettuate (<em><span
                                        x-text="formData.ceremony.type === 'cremation' ? 'Cremazione' : 'Inumazione/Tumulazione'"></span></em>),
                                ecco i documenti necessari.
                            </p>

                            <div class="space-y-4">
                                <!-- Base Documents -->
                                <label
                                    class="flex items-start gap-3 p-4 border border-funeral-200 rounded-lg bg-white shadow-sm cursor-pointer hover:bg-funeral-50">
                                    <input type="checkbox" value="identity_document"
                                        x-model="formData.required_documents"
                                        class="mt-1 h-4 w-4 rounded border-funeral-300 text-funeral-900 focus:ring-funeral-900">
                                    <div>
                                        <span class="block text-sm font-medium text-funeral-900">Documento Identità
                                            Defunto</span>
                                        <span class="block text-xs text-funeral-500">Originale o copia conforme.</span>
                                    </div>
                                </label>
                                <label
                                    class="flex items-start gap-3 p-4 border border-funeral-200 rounded-lg bg-white shadow-sm cursor-pointer hover:bg-funeral-50">
                                    <input type="checkbox" disabled checked
                                        class="mt-1 h-4 w-4 rounded border-funeral-300 text-funeral-400 bg-gray-50">
                                    <div>
                                        <span class="block text-sm font-medium text-funeral-900">Codice Fiscale
                                            Defunto</span>
                                        <span class="block text-xs text-funeral-500">Tessera sanitaria
                                            (Richiesto).</span>
                                    </div>
                                </label>
                                <label
                                    class="flex items-start gap-3 p-4 border border-funeral-200 rounded-lg bg-white shadow-sm cursor-pointer hover:bg-funeral-50">
                                    <input type="checkbox" value="certificate_death"
                                        x-model="formData.required_documents"
                                        class="mt-1 h-4 w-4 rounded border-funeral-300 text-funeral-900 focus:ring-funeral-900">
                                    <div>
                                        <span class="block text-sm font-medium text-funeral-900">Certificato
                                            Necroscopico (ISTAT)</span>
                                        <span class="block text-xs text-funeral-500">Rilasciato dal medico 15h dopo il
                                            decesso.</span>
                                    </div>
                                </label>

                                <!-- Conditional Documents: Cremation -->
                                <template x-if="formData.ceremony.type === 'cremation'">
                                    <label
                                        class="flex items-start gap-3 p-4 border border-l-4 border-l-accent-gold border-y-funeral-200 border-r-funeral-200 rounded-lg bg-amber-50/50 shadow-sm cursor-pointer hover:bg-amber-50">
                                        <input type="checkbox" value="cremation_request"
                                            x-model="formData.required_documents"
                                            class="mt-1 h-4 w-4 rounded border-funeral-300 text-funeral-900 focus:ring-funeral-900">
                                        <div>
                                            <span class="block text-sm font-medium text-funeral-900">Istanza di
                                                Cremazione</span>
                                            <span class="block text-xs text-funeral-500">Firma del coniuge o parenti più
                                                prossimi.</span>
                                        </div>
                                    </label>
                                </template>

                                <template x-if="formData.ceremony.type === 'cremation'">
                                    <label
                                        class="flex items-start gap-3 p-4 border border-funeral-200 rounded-lg bg-white shadow-sm cursor-pointer hover:bg-funeral-50">
                                        <input type="checkbox"
                                            class="mt-1 h-4 w-4 rounded border-funeral-300 text-funeral-900 focus:ring-funeral-900">
                                        <div>
                                            <span class="block text-sm font-medium text-funeral-900">Destinazione
                                                Ceneri</span>
                                            <span class="block text-xs text-funeral-500">Affido, Dispersione o
                                                Tumulazione.</span>
                                        </div>
                                    </label>
                                </template>

                                <!-- Conditional Documents: Burial -->
                                <template x-if="formData.ceremony.type === 'burial'">
                                    <label
                                        class="flex items-start gap-3 p-4 border border-funeral-200 rounded-lg bg-white shadow-sm cursor-pointer hover:bg-funeral-50">
                                        <input type="checkbox" value="burial_permit"
                                            x-model="formData.required_documents"
                                            class="mt-1 h-4 w-4 rounded border-funeral-300 text-funeral-900 focus:ring-funeral-900">
                                        <div>
                                            <span class="block text-sm font-medium text-funeral-900">Concessione
                                                Cimiteriale</span>
                                            <span class="block text-xs text-funeral-500">Estremi del loculo/tomba (se
                                                già in possesso).</span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- STEP 5: SUMMARY & CONFIRM -->
                    <div x-show="step === 5" x-cloak x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <x-ui.card header="Riepilogo e Conferma">
                            <div class="space-y-8">
                                <!-- Data Section -->
                                <div>
                                    <h4
                                        class="text-sm font-semibold text-funeral-900 uppercase tracking-wider mb-4 border-b border-funeral-100 pb-2">
                                        Dati Pratica</h4>
                                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm font-medium text-funeral-500">Defunto</dt>
                                            <dd class="mt-1 text-sm text-funeral-900 font-semibold"
                                                x-text="formData.deceased.name + ' ' + formData.deceased.surname"></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-funeral-500">Codice Fiscale</dt>
                                            <dd class="mt-1 text-sm text-funeral-900 font-mono"
                                                x-text="formData.deceased.tax_code || '-'"></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-funeral-500">Tipo Rito</dt>
                                            <dd class="mt-1 text-sm text-funeral-900">
                                                <span x-show="formData.ceremony.type === 'burial'">Sepoltura
                                                    Tradizionale</span>
                                                <span x-show="formData.ceremony.type === 'cremation'">Cremazione</span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Selected Products Section -->
                                <div>
                                    <h4
                                        class="text-sm font-semibold text-funeral-900 uppercase tracking-wider mb-4 border-b border-funeral-100 pb-2">
                                        Articoli Selezionati</h4>
                                    <template x-if="selectedItems.length > 0">
                                        <ul
                                            class="divide-y divide-funeral-100 border border-funeral-200 rounded-lg bg-funeral-50">
                                            <template x-for="item in selectedItems" :key="item.id">
                                                <li class="p-4 flex justify-between items-center">
                                                    <div class="flex items-center gap-4">
                                                        <img :src="item.image" :alt="item.name"
                                                            class="h-10 w-10 object-cover rounded-md bg-funeral-200">
                                                        <div>
                                                            <p class="text-sm font-medium text-funeral-900"
                                                                x-text="item.name"></p>
                                                            <p class="text-xs text-funeral-500 capitalize"
                                                                x-text="item.type"></p>
                                                        </div>
                                                    </div>
                                                    <span class="text-sm font-semibold text-funeral-900"
                                                        x-text="'€ ' + item.price.toLocaleString()"></span>
                                                </li>
                                            </template>
                                            <li
                                                class="p-4 bg-funeral-100 flex justify-between items-center rounded-b-lg">
                                                <span class="text-sm font-bold text-funeral-900">Totale Stimato</span>
                                                <span class="text-base font-bold text-funeral-900"
                                                    x-text="'€ ' + currentTotal.toLocaleString()"></span>
                                            </li>
                                        </ul>
                                    </template>
                                    <template x-if="selectedItems.length === 0">
                                        <div
                                            class="text-sm text-funeral-500 italic p-4 bg-funeral-50 rounded-lg border border-funeral-200 border-dashed text-center">
                                            Nessun articolo selezionato.
                                        </div>
                                    </template>
                                </div>

                                <!-- Notes Section -->
                                <div>
                                    <label for="notes"
                                        class="block text-sm font-medium text-funeral-900 uppercase tracking-wider mb-2">Note
                                        Aggiuntive</label>
                                    <textarea id="notes" x-model="formData.notes" rows="4"
                                        class="block w-full rounded-md border-funeral-200 shadow-sm focus:border-funeral-500 focus:ring-funeral-500 sm:text-sm p-3"
                                        placeholder="Inserisci eventuali note o richieste particolari..."></textarea>
                                </div>

                                <!-- Disclaimer -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex gap-3">
                                    <svg class="h-5 w-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                    </svg>
                                    <p class="text-sm text-blue-800">
                                        Questa azione creerà una nuova pratica in stato <strong>"Bozza"</strong>. Potrai
                                        modificare tutti i dati successivamente.
                                    </p>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- Bottom Navigation -->
                    <div class="flex flex-wrap justify-between gap-4 pt-6">
                        <x-ui.button variant="secondary" @click="prev()" x-show="step > 1">
                            &larr; Indietro
                        </x-ui.button>
                        <div x-show="step === 1"></div> <!-- Spacer -->

                        <x-ui.button @click="next()" x-show="step < totalSteps">
                            Prosegui &rarr;
                        </x-ui.button>
                        <x-ui.button variant="primary" x-show="step === totalSteps" @click="submitFuneral()"
                            :disabled="isLoading">
                            <span x-show="!isLoading">Conferma e Crea Pratica</span>
                            <span x-show="isLoading">Creazione in corso...</span>
                        </x-ui.button>
                    </div>

                </div>

                <!-- Sticky Sidebar (Draft Summary) -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">
                        <x-ui.card header="Bozza Pratica" class="bg-funeral-50">
                            <dl class="space-y-4 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-funeral-500">Defunto</dt>
                                    <dd class="font-medium text-funeral-900"
                                        x-text="formData.deceased.name + ' ' + formData.deceased.surname || '-'">-</dd>
                                </div>
                                <div class="border-t border-funeral-200 pt-4 flex justify-between">
                                    <dt class="text-funeral-500">Tipo Rito</dt>
                                    <dd class="font-medium text-funeral-900">
                                        <span x-show="formData.ceremony.type === 'burial'">Sepoltura</span>
                                        <span x-show="formData.ceremony.type === 'cremation'">Cremazione</span>
                                    </dd>
                                </div>
                            </dl>
                            <x-slot name="footer">
                                <div class="flex items-center gap-2 text-xs text-funeral-500">
                                    <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span x-show="isSaving">Salvataggio in corso...</span>
                                    <span x-show="!isSaving && lastSavedAt">Salvato alle <span
                                            x-text="lastSavedAt"></span></span>
                                    <span x-show="!isSaving && !lastSavedAt">Salvataggio automatico attivo</span>
                                </div>
                            </x-slot>
                        </x-ui.card>

                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-800 mb-2">💡 Suggerimento</h4>
                            <p class="text-xs text-blue-600">
                                Hai bisogno del Codice Fiscale per generare automaticamente i moduli ISTAT nel passaggio
                                4.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </x-ui.container>

    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('funeralWizard', () => ({
                step: 1,
                totalSteps: 5,
                isLoading: false,
                errors: {},
                formData: {
                    deceased: {
                        name: '',
                        surname: '',
                        tax_code: '',
                        birth_date: '',
                        birth_city: '',
                        death_date: '',
                        death_city: ''
                    },
                    ceremony: {
                        type: 'burial', // 'burial' or 'cremation'
                        location: '',
                        date: ''
                    },
                    product_ids: [],
                    required_documents: [],
                    notes: ''
                },

                // Products State
                activeCategory: 'coffin', // Renamed to singular to match data
                products: [], // Loaded from API matched to parent scope
                selectedItems: [],

                // Auto-save State
                draftId: null,
                isSaving: false,
                lastSavedAt: null,

                // Data
                cemeteries: [],

                init() {
                    this.debouncedSave = () => { };
                    this.fetchProducts();
                    this.fetchCemeteries();
                    this.startAutoSave();
                    this.$watch('formData', () => {
                        this.debouncedSave();
                    });
                },

                debouncedSave: null,

                async fetchProducts() {
                    try {
                        const response = await axios.get('/api/products');
                        this.products = response.data.data.map(p => ({
                            id: p.id,
                            name: p.name,
                            category: p.category,
                            price: parseFloat(p.price),
                            image: p.image_url || 'https://via.placeholder.com/400x300?text=No+Image',
                            description: p.description
                        }));
                    } catch (error) {
                        console.error('Failed to fetch products', error);
                        // UX: Show error in UI
                        alert('Impossibile caricare il catalogo prodotti. Riprova più tardi.');
                    }
                },

                async fetchCemeteries() {
                    try {
                        const response = await axios.get('/api/cemeteries');
                        this.cemeteries = response.data.data;
                    } catch (error) {
                        console.error('Failed to fetch cemeteries', error);
                    }
                },

                startAutoSave() {
                    // Simple debounce implementation
                    let timeout;
                    this.debouncedSave = () => {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => {
                            this.saveDraft();
                        }, 2000); // Save after 2 seconds of inactivity
                    };
                    this.debouncedSave(); // Initialize
                },

                async saveDraft() {
                    if (this.isLoading) return; // Don't save if submitting

                    this.isSaving = true;
                    try {
                        const payload = {
                            draft_id: this.draftId,
                            step: this.step,
                            data: this.formData
                        };

                        const response = await axios.post('/api/funerals/drafts', payload);

                        // Store draft ID for future updates
                        if (response.data.draft_id) {
                            this.draftId = response.data.draft_id;
                        }

                        const now = new Date();
                        this.lastSavedAt = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    } catch (error) {
                        console.error('Auto-save failed', error);
                        // Silent fail for drafts
                    } finally {
                        this.isSaving = false;
                    }
                },


                // Navigation
                next() {
                    if (this.validateStep(this.step)) {
                        if (this.step < this.totalSteps) this.step++;
                    }
                },
                prev() { if (this.step > 1) this.step-- },
                jump(s) {
                    // Only allow jumping back or if current step is valid
                    if (s < this.step || this.validateStep(this.step)) {
                        this.step = s;
                    }
                },

                // Validation
                validateStep(step) {
                    // Frontend validation (basic)
                    // Backend validation happens on submit
                    let valid = true;
                    // We clear frontend-only errors here, but keep backend errors until corrected? 
                    // Better to just check required fields

                    if (step === 1) {
                        // Simple check
                        if (!this.formData.deceased.name || !this.formData.deceased.surname) valid = false;
                    }

                    return valid;
                },

                // Products Logic
                toggleItem(item) {
                    const index = this.selectedItems.findIndex(i => i.id === item.id);
                    if (index > -1) {
                        this.selectedItems.splice(index, 1);
                    } else {
                        this.selectedItems.push(item);
                    }
                    this.updateProductIds();
                },
                isSelected(item) {
                    return this.selectedItems.some(i => i.id === item.id);
                },
                get filteredProducts() {
                    return this.products.filter(p => p.category === this.activeCategory);
                },
                get currentTotal() {
                    return this.selectedItems.reduce((acc, item) => acc + item.price, 0);
                },
                updateProductIds() {
                    this.formData.product_ids = this.selectedItems.map(i => i.id);
                },

                // Submission
                async submitFuneral() {
                    this.isLoading = true;
                    this.errors = {};

                    // Prepare payload matching StoreFuneralHttpRequest
                    const payload = {
                        deceased_name: this.formData.deceased.name,
                        deceased_surname: this.formData.deceased.surname,
                        deceased_tax_code: this.formData.deceased.tax_code,
                        deceased_birth_date: this.formData.deceased.birth_date,
                        deceased_birth_city: this.formData.deceased.birth_city,
                        deceased_death_date: this.formData.deceased.death_date,
                        deceased_death_city: this.formData.deceased.death_city,

                        ceremony_type: this.formData.ceremony.type,
                        ceremony_location: this.formData.ceremony.location,
                        ceremony_date: this.formData.ceremony.date,

                        product_ids: this.formData.product_ids,
                        notes: this.formData.notes
                    };

                    try {
                        const response = await axios.post('/api/funerals', payload);

                        // Success
                        window.location.href = "{{ route('dashboard') }}"; // Redirect to dashboard

                    } catch (error) {
                        if (error.response && error.response.status === 422) {
                            // Validation errors
                            this.errors = error.response.data.errors || {};

                            // Scroll to top to show errors
                            window.scrollTo({ top: 0, behavior: 'smooth' });

                        } else {
                            alert('Si è verificato un errore imprevisto: ' + (error.response?.data?.message || error.message));
                        }
                    } finally {
                        this.isLoading = false;
                    }
                }
            }));
        });
    </script>
</x-app-layout>