@php
    $ticket = $ticket();
    $attachments = $ticket->attachments; // Usa la relación hasManyThrough
@endphp

@if ($attachments->count() > 0)
    <div class="space-y-3">
        @foreach ($attachments as $attachment)
            <a href="{{ route('attachments.download', $attachment) }}"
                class="flex items-center p-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 
                      rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm 
                      transition-all duration-200 hover:shadow-md hover:scale-[1.02]
                      group"
                title="Descargar {{ $attachment->original_name }}">

                @php
                    $isImage = str_starts_with($attachment->mime, 'image/');
                    $isPdf = $attachment->mime === 'application/pdf';
                    $isDoc = in_array($attachment->mime, [
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ]);
                @endphp

                <!-- Icono del archivo -->
                <div class="flex-shrink-0 mr-3">
                    @if ($isImage)
                        <div
                            class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @elseif($isPdf)
                        <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @else
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Información del archivo -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                        {{ $attachment->original_name }}
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        {{ number_format($attachment->size / 1024, 1) }}KB
                        • Adjuntado {{ $attachment->created_at->diffForHumans() }}
                    </p>
                </div>

                <!-- Icono de descarga -->
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </a>
        @endforeach
    </div>
@else
    <div class="text-center py-6">
        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a2 2 0 00-2.828-2.828z" />
            </svg>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Este ticket no tiene archivos adjuntos
        </p>
    </div>
@endif
