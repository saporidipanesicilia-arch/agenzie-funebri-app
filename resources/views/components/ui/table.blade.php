@props([
    'headers' => [], // Array of strings
    'rows' => [], // Array of objects/arrays (if used raw)
])

<div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-funeral-200">
        <thead class="bg-funeral-50">
            <tr>
                @foreach($headers as $header)
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-funeral-500 sm:pl-6">
                        {{ $header }}
                    </th>
                @endforeach
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                    <span class="sr-only">Azioni</span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-funeral-200 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
