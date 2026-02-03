<div {{ $attributes->merge(['class' => 'ui-card']) }}>
    @if (isset($header))
        <div class="px-4 py-4 sm:px-6 border-b border-funeral-100 bg-white">
            <h3 class="text-base font-medium text-funeral-900 flex items-center justify-between">
                {{ $header }}
                @if (isset($actions))
                    <div class="ml-4 flex-shrink-0">
                        {{ $actions }}
                    </div>
                @endif
            </h3>
        </div>
    @endif

    <div class="px-4 py-5 sm:p-6 {{ isset($noPadding) ? '!p-0' : '' }}">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="px-4 py-4 sm:px-6 bg-funeral-50 border-t border-funeral-100 text-sm text-funeral-500">
            {{ $footer }}
        </div>
    @endif
</div>