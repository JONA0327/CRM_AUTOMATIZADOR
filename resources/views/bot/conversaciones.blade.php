<x-admin-layout title="Conversaciones del Bot">
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900">Conversaciones del Bot</h2>
        <p class="text-sm text-gray-500 mt-1">Panel para visualizar las conversaciones y logs del bot.</p>
    </div>

    {{-- Estado del Bot --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <span class="font-semibold text-gray-900 text-sm">Estado del Bot</span>
        </div>
        <div class="px-6 py-5">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full {{ $botActivo ? 'bg-green-500' : 'bg-red-400' }}"></span>
                    <span class="text-sm font-medium {{ $botActivo ? 'text-green-700' : 'text-red-600' }}">
                        {{ $botActivo ? 'Bot Activo' : 'Bot Inactivo' }}
                    </span>
                </div>
                <a href="{{ route('bot.index') }}" class="text-sm text-blue-600 hover:underline">Ir a configuración de instancias</a>
            </div>
        </div>
    </div>

    {{-- Logs del Bot --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <span class="font-semibold text-gray-900 text-sm">Logs recientes</span>
            <a href="{{ route('bot.conversaciones') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">Actualizar</a>
        </div>
        <div class="px-6 py-5 max-h-[500px] overflow-auto font-mono text-xs bg-gray-900 text-gray-100 rounded-b-xl">
            @if(isset($logs) && count($logs) > 0)
                @foreach($logs as $log)
                    <div class="py-1 border-b border-gray-800 last:border-0 whitespace-pre-wrap break-all
                        {{ str_contains($log, 'ERROR') ? 'text-red-400' : '' }}
                        {{ str_contains($log, 'INFO') ? 'text-green-400' : '' }}
                        {{ str_contains($log, 'WARNING') ? 'text-yellow-400' : '' }}
                        {{ str_contains($log, '[Bot]') ? 'text-cyan-400' : '' }}
                    ">{{ $log }}</div>
                @endforeach
            @else
                <p class="text-gray-400 italic py-8 text-center">No hay logs recientes. El archivo de logs está vacío.</p>
            @endif
        </div>
    </div>

    {{-- Info adicional --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-sm text-amber-800">
        <strong>Nota:</strong> Los logs se generan cuando el bot recibe mensajes de WhatsApp. Si no ves logs, verifica que:
        <ul class="list-disc ml-5 mt-2 space-y-1">
            <li>El bot esté <strong>activado</strong></li>
            <li>Tengas al menos una <strong>instancia conectada</strong></li>
            <li>El <strong>webhook</strong> esté configurado correctamente en la instancia</li>
            <li>Alguien haya enviado un mensaje al número conectado</li>
        </ul>
    </div>
</x-admin-layout>
