<div>
    <div class="space-y-6">
        @if($comments->count() > 0)
            <div class="space-y-6">
                @foreach($comments as $comment)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-100 dark:border-gray-700 p-4 hover:shadow-lg transition-shadow duration-200">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                <!-- Avatar con iniciales -->
                                <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-800 flex items-center justify-center text-primary-700 dark:text-primary-200 font-bold text-lg">
                                    {{ substr($comment->author->name, 0, 1) }}
                                </div>
                                
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white text-base">
                                        {{ $comment->author->name }}
                                        
                                        <!-- Rol del usuario -->
                                        <span class="inline-flex items-center ml-2 text-xs bg-primary-50 dark:bg-primary-900 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-full">
                                            {{ $comment->author->getRoleNames()->first() }}
                                        </span>
                                    </div>
                                    
                                    <!-- Tiempo con mejor formato y separación -->
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $comment->created_at->format('d M Y, H:i') }} 
                                        <span class="text-xs ml-1">({{ $comment->created_at->diffForHumans() }})</span>
                                    </span>
                                </div>
                            </div>
                            
                            @if($comment->user_id === auth()->id())
                                <div class="px-2 py-1 rounded-md bg-green-50 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-medium">
                                    Tú
                                </div>
                            @endif
                        </div>
                        
                        <!-- Contenido del comentario con mejor contraste -->
                        <div class="mt-3 text-gray-800 dark:text-gray-200 p-3 bg-gray-50 dark:bg-gray-700 rounded-md border-l-4 border-primary-400 dark:border-primary-600">
                            {!! nl2br(e($comment->body)) !!}
                        </div>
                        
                        <!-- Mejora de la visualización de adjuntos -->
                        @if($comment->attachments->count() > 0)
                            <div class="mt-4 border-t border-gray-100 dark:border-gray-700 pt-3">
                                <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    Adjuntos ({{ $comment->attachments->count() }})
                                </div>
                                
                                <div class="flex flex-wrap gap-2">
                                    @foreach($comment->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment->path) }}" target="_blank" 
                                           class="flex items-center text-xs bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md px-3 py-1.5 transition-colors border border-gray-200 dark:border-gray-600">
                                            <!-- Icono según tipo de archivo -->
                                            @php
                                                $icon = 'document';
                                                if (strpos($attachment->mime_type, 'image') !== false) {
                                                    $icon = 'photograph';
                                                } elseif (strpos($attachment->mime_type, 'pdf') !== false) {
                                                    $icon = 'document-text';
                                                }
                                            @endphp
                                            
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                @if($icon === 'document')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                @elseif($icon === 'photograph')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                @elseif($icon === 'document-text')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                @endif
                                            </svg>
                                            
                                            <span class="truncate max-w-xs">{{ $attachment->name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="mt-2 font-medium">No hay comentarios todavía.</p>
                <p class="text-sm mt-1">Sé el primero en comentar sobre este ticket.</p>
            </div>
        @endif
    </div>
    
    <!-- Formulario de comentarios mejorado -->
    <div class="mt-8">
        <form wire:submit.prevent="addComment" class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Agregar un comentario</h3>
            
            <div class="space-y-4">
                <div>
                    <textarea id="comment" rows="4" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                              wire:model="comment" placeholder="Escribe tu comentario aquí..."></textarea>
                    @error('comment') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="border rounded-md p-4 bg-gray-50 dark:bg-gray-700">
                    <label for="attachments" class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        Adjuntar archivos (opcional)
                    </label>
                    
                    <input type="file" id="attachments" multiple
                           class="mt-2 block w-full text-sm text-gray-900 file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-medium
                                  file:bg-primary-50 file:text-primary-700
                                  hover:file:bg-primary-100
                                  dark:file:bg-primary-900 dark:file:text-primary-300
                                  dark:text-gray-400"
                           wire:model="attachments">
                    @error('attachments.*') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    
                    @if(count($attachments) > 0)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($attachments as $index => $attachment)
                                <div class="text-xs bg-gray-100 dark:bg-gray-600 rounded-md px-3 py-1.5 flex items-center border border-gray-200 dark:border-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span class="truncate max-w-xs">{{ $attachment->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="$set('attachments.{{ $index }}', null)" 
                                            class="ml-2 text-gray-500 hover:text-red-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        Publicar comentario
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
