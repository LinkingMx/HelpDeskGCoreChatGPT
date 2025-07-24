<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa - {{ $attachment->original_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-2xl w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            @php
                                $isDoc = in_array($attachment->mime, [
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                ]);
                                $isExcel = in_array($attachment->mime, [
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                ]);
                                $isZip =
                                    str_contains($attachment->mime, 'zip') ||
                                    str_contains($attachment->mime, 'archive');
                            @endphp

                            @if ($isDoc)
                                <i class="fas fa-file-word text-2xl"></i>
                            @elseif($isExcel)
                                <i class="fas fa-file-excel text-2xl"></i>
                            @elseif($isZip)
                                <i class="fas fa-file-archive text-2xl"></i>
                            @else
                                <i class="fas fa-file text-2xl"></i>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-xl font-bold">Vista Previa del Archivo</h1>
                            <p class="text-blue-100">Ticket #{{ $ticket->id }} - {{ $ticket->subject }}</p>
                        </div>
                    </div>
                    <button onclick="window.close()" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Información del archivo -->
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                        {{ $attachment->original_name }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Este tipo de archivo no se puede visualizar directamente en el navegador.
                    </p>
                </div>

                <!-- Detalles del archivo -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">Tipo de archivo</span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">{{ $attachment->mime }}</p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <i class="fas fa-weight-hanging text-green-500"></i>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">Tamaño</span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">{{ number_format($attachment->size / 1024, 1) }} KB
                        </p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <i class="fas fa-calendar text-purple-500"></i>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">Subido</span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">{{ $attachment->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <i class="fas fa-ticket text-orange-500"></i>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">Ticket</span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">#{{ $ticket->id }}</p>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('attachments.download', $attachment) }}"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="fas fa-download"></i>
                        <span>Descargar Archivo</span>
                    </a>

                    <a href="/admin/tickets/{{ $ticket->id }}"
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="fas fa-arrow-left"></i>
                        <span>Volver al Ticket</span>
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4">
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Archivo verificado y protegido por el sistema de tickets
                </p>
            </div>
        </div>
    </div>
</body>

</html>
