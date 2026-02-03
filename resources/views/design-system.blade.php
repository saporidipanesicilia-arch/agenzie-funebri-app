<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-funeral-900 leading-tight">
            Design System Verification
        </h2>
    </x-slot>

    <div class="py-12 bg-funeral-base min-h-screen">
        <x-ui.container>

            <div class="space-y-12">

                <!-- COLORS SECTION -->
                <section>
                    <h3 class="ui-heading mb-4">1. Color Palette (Semantic)</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <x-ui.card>
                            <div class="h-20 bg-funeral-900 rounded-md mb-2"></div>
                            <span class="text-xs font-medium">Funeral 900</span>
                        </x-ui.card>
                        <x-ui.card>
                            <div class="h-20 bg-funeral-600 rounded-md mb-2"></div>
                            <span class="text-xs font-medium">Funeral 600</span>
                        </x-ui.card>
                        <x-ui.card>
                            <div class="h-20 bg-accent-gold rounded-md mb-2"></div>
                            <span class="text-xs font-medium">Accent Gold</span>
                        </x-ui.card>
                        <x-ui.card>
                            <div class="h-20 bg-funeral-100 rounded-md mb-2"></div>
                            <span class="text-xs font-medium">Funeral 100</span>
                        </x-ui.card>
                    </div>
                </section>

                <!-- BUTTONS SECTION -->
                <section>
                    <h3 class="ui-heading mb-4">2. Buttons</h3>
                    <div class="flex flex-wrap gap-4 items-center">
                        <x-ui.button>Primary Action</x-ui.button>
                        <x-ui.button variant="secondary">Secondary Action</x-ui.button>
                        <x-ui.button variant="ghost">Cancel</x-ui.button>
                        <x-ui.button variant="danger">Delete Record</x-ui.button>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-4 items-center">
                        <x-ui.button size="sm">Small</x-ui.button>
                        <x-ui.button size="md">Medium</x-ui.button>
                        <x-ui.button size="lg">Large</x-ui.button>
                    </div>
                </section>

                <!-- BADGES SECTION -->
                <section>
                    <h3 class="ui-heading mb-4">3. Status Indicators (Semaforo)</h3>
                    <div class="flex gap-4">
                        <x-ui.badge status="success" label="Active / Complete" />
                        <x-ui.badge status="warning" label="Pending / Expiring" />
                        <x-ui.badge status="error" label="Error / Expired" />
                        <x-ui.badge status="info" label="In Progress" />
                        <x-ui.badge status="neutral" label="Draft" />
                    </div>
                </section>

                <!-- CARDS SECTION -->
                <section>
                    <h3 class="ui-heading mb-4">4. Card Component</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <x-ui.card header="Standard Card">
                            <p class="ui-body">This is a standard card content with padding. It uses the white
                                background and subtle borders.</p>
                            <x-slot name="footer">
                                Optional footer content
                            </x-slot>
                        </x-ui.card>

                        <x-ui.card header="Action Card">
                            <x-slot name="actions">
                                <x-ui.button size="sm" variant="secondary">Edit</x-ui.button>
                            </x-slot>
                            <p class="ui-body">This card has an action button in the header.</p>
                        </x-ui.card>
                    </div>
                </section>

            </div>
        </x-ui.container>
    </div>
</x-app-layout>