<div class="flex flex-col h-full bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
    x-data="{
        init() {
                this.scrollToBottom();
                this.$nextTick(() => this.scrollToBottom());
            },
            scrollToBottom() {
                const container = this.$refs.conversationContainer;
                if (container) {
                    container.scrollTo({
                        top: container.scrollHeight,
                        behavior: 'smooth'
                    });
                }
            }
    }" x-on:comment-added.window="scrollToBottom()">
    <!-- Estilos CSS dentro del componente -->
    <style>
        /* Scrollbar personalizado */
        #conversation-container {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }

        #conversation-container::-webkit-scrollbar {
            width: 6px;
        }

        #conversation-container::-webkit-scrollbar-track {
            background: #f7fafc;
            border-radius: 3px;
        }

        #conversation-container::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        #conversation-container::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .dark #conversation-container {
            scrollbar-color: #4a5568 #2d3748;
        }

        .dark #conversation-container::-webkit-scrollbar-track {
            background: #2d3748;
        }

        .dark #conversation-container::-webkit-scrollbar-thumb {
            background: #4a5568;
        }

        .dark #conversation-container::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }
    </style>
    <!-- Header de la conversación mejorado -->
    <div class="flex-shrink-0 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-t-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                    <div class="absolute inset-0 w-3 h-3 bg-green-300 rounded-full animate-ping"></div>
                </div>
                <div>
                    <h3 class="font-semibold text-sm">Chat en Vivo</h3>
                    <p class="text-xs text-blue-100">{{ $totalComments }}
                        {{ $totalComments === 1 ? 'mensaje' : 'mensajes' }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <button x-on:click="scrollToBottom()"
                    class="p-1.5 rounded-md bg-white/20 hover:bg-white/30 transition-colors" title="Ir al final">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Área de mensajes con scroll mejorado -->
    <div x-ref="conversationContainer" id="conversation-container"
        class="flex-1 overflow-y-auto p-4 space-y-4 min-h-0 scroll-smooth" style="max-height: calc(100vh - 18rem);">
        @if ($comments->count() > 0)
            @foreach ($comments as $index => $comment)
                @php
                    $isCurrentUser = $comment->user_id === auth()->id();
                    $isAgent = $comment->author->hasRole(['agent', 'admin', 'super_admin']);

                    // Determinar el color del avatar y mensaje - Mejorado para tema claro
                    $avatarClass = $isAgent
                        ? 'bg-blue-600 dark:bg-blue-500'
                        : ($isCurrentUser
                            ? 'bg-green-600 dark:bg-green-500'
                            : 'bg-gray-600 dark:bg-gray-500');

                    $messageClass = $isCurrentUser
                        ? 'bg-blue-500 text-white ml-auto'
                        : ($isAgent
                            ? 'bg-indigo-50 dark:bg-indigo-900/30 text-gray-900 dark:text-gray-100 border border-indigo-200 dark:border-indigo-700'
                            : 'bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-600');
                @endphp

                <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }} group">
                    <div
                        class="flex {{ $isCurrentUser ? 'flex-row-reverse' : 'flex-row' }} items-end max-w-[85%] space-x-2 {{ $isCurrentUser ? 'space-x-reverse' : '' }}">
                        <!-- Avatar (siempre visible) -->
                        <div class="flex-shrink-0 relative mb-1">
                            <div
                                class="w-8 h-8 rounded-full {{ $avatarClass }} flex items-center justify-center text-white text-sm font-bold shadow-lg border-2 border-white dark:border-primary-500">
                                {{ substr($comment->author->name, 0, 1) }}
                            </div>
                        </div>

                        <!-- Contenido del mensaje -->
                        <div class="flex flex-col {{ $isCurrentUser ? 'items-end' : 'items-start' }}">
                            <!-- Header del mensaje (siempre visible: nombre, rol, tiempo) -->
                            <div
                                class="flex items-center space-x-2 mb-1.5 {{ $isCurrentUser ? 'flex-row-reverse space-x-reverse' : '' }}">
                                <span class="text-xs font-semibold text-gray-900 dark:text-gray-200">
                                    {{ $comment->author->name }}
                                </span>

                                @if ($isAgent)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        {{ ucfirst($comment->author->getRoleNames()->first()) }}
                                    </span>
                                @endif

                                <span class="text-xs text-gray-700 dark:text-gray-400">
                                    {{ $comment->created_at->format('H:i') }}
                                </span>
                            </div>

                            <!-- Burbuja del mensaje -->
                            <div class="relative group/message">
                                <div
                                    class="
                                    px-4 py-3 rounded-2xl shadow-sm {{ $messageClass }}
                                    break-words text-sm leading-relaxed min-w-0
                                    transition-all duration-200 hover:shadow-md
                                ">
                                    {!! nl2br(e($comment->body)) !!}
                                </div>
                            </div>

                            <!-- Adjuntos -->
                            @if ($comment->attachments->count() > 0)
                                <div class="mt-3 space-y-2 w-full max-w-xs">
                                    @foreach ($comment->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment->path) }}" target="_blank"
                                            class="
                                                flex items-center p-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 
                                                rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm 
                                                transition-all duration-200 hover:shadow-md hover:scale-[1.02]
                                                group/attachment
                                            ">
                                            @php
                                                $isImage = str_starts_with($attachment->mime_type, 'image/');
                                                $isPdf = $attachment->mime_type === 'application/pdf';
                                                $isDoc = in_array($attachment->mime_type, [
                                                    'application/msword',
                                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                ]);
                                            @endphp

                                            <div class="flex-shrink-0 mr-3">
                                                @if ($isImage)
                                                    <div
                                                        class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @elseif($isPdf)
                                                    <div
                                                        class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div
                                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <p
                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $attachment->name }}
                                                </p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ number_format($attachment->size / 1024, 1) }}KB
                                                </p>
                                            </div>

                                            <svg class="w-4 h-4 text-gray-400 group-hover/attachment:text-gray-600 dark:group-hover/attachment:text-gray-300 transition-colors"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Divisor entre comentarios (no mostrar en el último) -->
                @if ($index < $comments->count() - 1)
                    <div class="flex justify-center my-4">
                        <div class="w-full max-w-xs border-t border-gray-200 dark:border-gray-600 opacity-50"></div>
                    </div>
                @endif
            @endforeach
        @else
            <!-- Estado vacío mejorado -->
            <div class="flex flex-col items-center justify-center h-full text-center py-12">
                <div
                    class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900 dark:to-indigo-900 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">¡Comienza la conversación!</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 max-w-sm">
                    No hay mensajes aún. Escribe tu primer mensaje para iniciar el intercambio con el equipo de soporte.
                </p>
            </div>
        @endif
    </div>

    <!-- Formulario de nuevo comentario mejorado -->
    <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
        <form wire:submit.prevent="addComment" class="p-4 space-y-3">
            <!-- Área de escritura mejorada -->
            <div class="relative">
                <textarea wire:model.live="comment" rows="3" placeholder="Escribe tu mensaje aquí..."
                    class="
                        w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                        shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-none text-sm 
                        placeholder-gray-400 dark:placeholder-gray-500
                        transition-all duration-200
                    "
                    {{ $isTyping ? 'ring-2 ring-blue-500 border-blue-500' : '' }}></textarea>

                @error('comment')
                    <p class="mt-2 text-xs text-red-600 dark:text-red-400 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Barra de herramientas -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <!-- Botón de adjuntar archivos -->
                    <label class="group cursor-pointer">
                        <input type="file" multiple wire:model="attachments" class="hidden"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt,.zip">
                        <div
                            class="
                            inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 
                            bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg 
                            hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 
                            group-hover:border-blue-400 group-hover:shadow-sm
                        ">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            Adjuntar
                        </div>
                    </label>

                    <!-- Indicador de archivos seleccionados -->
                    @if (count($attachments) > 0)
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            {{ count($attachments) }} archivo{{ count($attachments) > 1 ? 's' : '' }}
                        </span>
                    @endif
                </div>

                <!-- Botón enviar mejorado -->
                <button type="submit" @disabled(!$this->canSubmit())
                    class="
                        inline-flex items-center px-6 py-2 text-sm font-medium text-white 
                        bg-primary-600 hover:bg-primary-700 border border-transparent rounded-lg 
                        shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 
                        disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary-600
                        transition-all duration-200 hover:shadow-md
                    ">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Enviar mensaje
                </button>
            </div>

            <!-- Preview de archivos adjuntos -->
            @if (count($attachments) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach ($attachments as $index => $attachment)
                        @if ($attachment)
                            <div
                                class="flex items-center text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg px-3 py-2 border border-blue-200 dark:border-blue-800">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span
                                    class="truncate max-w-32 font-medium">{{ $attachment->getClientOriginalName() }}</span>
                                <button type="button" wire:click="removeAttachment({{ $index }})"
                                    class="ml-2 text-blue-400 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @endforeach
                </div>
                @error('attachments.*')
                    <p class="text-xs text-red-600 dark:text-red-400 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            @endif
        </form>
    </div>
</div>
