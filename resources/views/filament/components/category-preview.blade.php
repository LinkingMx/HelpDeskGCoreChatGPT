@props(['icon', 'name'])

<div
    class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                <x-heroicon-o-eye class="w-4 h-4 text-gray-600 dark:text-gray-400" />
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Vista Previa</span>
                <p class="text-xs text-gray-500 dark:text-gray-400">Así se verá la categoría</p>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <div
            class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
            @if ($icon && str_starts_with($icon, 'heroicon-'))
                <div class="w-5 h-5 text-primary-600 dark:text-primary-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
            @else
                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-gray-400" />
            @endif

            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $name }}</span>
        </div>

        <div class="text-right">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400 block">Icono:</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                @if ($icon && str_starts_with($icon, 'heroicon-'))
                    <span class="text-green-600 dark:text-green-400">✓</span> {{ $icon }}
                @elseif($icon)
                    <span class="text-red-600 dark:text-red-400">✗</span> {{ $icon }}
                @else
                    Por defecto
                @endif
            </span>
        </div>
    </div>
</div>
