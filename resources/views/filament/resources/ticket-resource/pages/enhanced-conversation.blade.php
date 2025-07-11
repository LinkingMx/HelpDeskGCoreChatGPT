<div class="flex flex-col h-full">
    <!-- Header de la conversación -->
    <div
        class="flex-shrink-0 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-3 mb-4 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Chat en vivo</span>
            </div>
            <div class="text-xs text-blue-600 dark:text-blue-300">
                {{ $this->getRecord()->comments()->count() }} mensajes
            </div>
        </div>
    </div>

    <!-- Área de conversación scrolleable -->
    <div class="flex-1 overflow-hidden">
        <div class="h-full overflow-y-auto space-y-3 pr-2 conversation-scroll" style="max-height: calc(100vh - 20rem);">
            <livewire:enhanced-ticket-conversation :ticket="$this->getRecord()" />
        </div>
    </div>

    <!-- Form de nuevo comentario fijo en la parte inferior -->
    <div class="flex-shrink-0 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <livewire:quick-comment-form :ticket="$this->getRecord()" />
    </div>
</div>

<style>
    .conversation-scroll {
        scrollbar-width: thin;
        scrollbar-color: #e5e7eb #f3f4f6;
    }

    .conversation-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .conversation-scroll::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 3px;
    }

    .conversation-scroll::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 3px;
    }

    .conversation-scroll::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    .dark .conversation-scroll {
        scrollbar-color: #4b5563 #374151;
    }

    .dark .conversation-scroll::-webkit-scrollbar-track {
        background: #374151;
    }

    .dark .conversation-scroll::-webkit-scrollbar-thumb {
        background: #4b5563;
    }

    .dark .conversation-scroll::-webkit-scrollbar-thumb:hover {
        background: #6b7280;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-scroll al final cuando se carga la página
        const container = document.querySelector('.conversation-scroll');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }

        // Auto-scroll cuando se añade un nuevo comentario
        window.addEventListener('comment-added', () => {
            setTimeout(() => {
                if (container) {
                    container.scrollTo({
                        top: container.scrollHeight,
                        behavior: 'smooth'
                    });
                }
            }, 100);
        });
    });
</script>
