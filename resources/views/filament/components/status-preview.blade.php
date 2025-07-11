@props(['name', 'color'])

<div
    class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                <x-heroicon-o-eye class="w-4 h-4 text-gray-600 dark:text-gray-400" />
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Vista Previa</span>
                <p class="text-xs text-gray-500 dark:text-gray-400">Así se verá en los tickets</p>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <x-filament::badge :color="$color" class="text-sm font-medium px-3 py-1">
            {{ $name }}
        </x-filament::badge>

        <div class="text-right">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400 block">Color:</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($color) }}</span>
        </div>
    </div>
</div>
