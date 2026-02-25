<x-admin-layout title="Configurar Bot">

    {{-- Flash success --}}
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-4">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900">Configurar Bot</h2>
        <p class="text-sm text-gray-500 mt-1">
            Las API Keys se almacenan <strong>cifradas</strong> en la base de datos.
            Deja un campo vacío para mantener el valor actual.
        </p>
    </div>

    <form method="POST" action="{{ route('configuracion.update') }}">
        @csrf

        <div class="space-y-6">

            {{-- ── PROMPT DEL SISTEMA ── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                 x-data="{ chars: {{ strlen($systemPrompt ?? '') }}, max: 8000 }">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">Prompt del sistema</p>
                            <p class="text-xs text-gray-400">Instrucciones base que el bot usará en cada conversación</p>
                        </div>
                    </div>
                    @if($systemPrompt)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Configurado
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Pendiente
                        </span>
                    @endif
                </div>
                <div class="px-6 py-5">
                    <label for="system_prompt" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Instrucciones para el bot
                        <span class="ml-1 text-xs text-gray-400 font-normal">
                            (el usuario nunca verá este texto)
                        </span>
                    </label>
                    <textarea
                        id="system_prompt"
                        name="system_prompt"
                        rows="10"
                        maxlength="8000"
                        placeholder="Eres un asistente de ventas especializado en productos agrícolas. Tu objetivo es ayudar a los clientes a encontrar el producto adecuado para sus cultivos y enfermedades. Responde siempre en español, de forma clara y amable..."
                        @input="chars = $event.target.value.length"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm font-mono leading-relaxed
                               focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition resize-y
                               {{ $systemPrompt ? 'border-green-300 bg-green-50/40' : '' }}"
                    >{{ $systemPrompt }}</textarea>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-xs text-gray-400">
                            Puedes usar saltos de línea para separar secciones del prompt.
                        </p>
                        <span class="text-xs" :class="chars >= max * 0.9 ? 'text-red-500 font-semibold' : 'text-gray-400'">
                            <span x-text="chars"></span> / <span x-text="max"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── EVOLUTION API ── --}}
            @php $g = $grupos['evolution']; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.523 0-10-4.477-10-10S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $g['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $g['descripcion'] }}</p>
                        </div>
                    </div>
                    <x-config-badge :configured="$estado['evolution_url'] && $estado['evolution_key']"/>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-config-field name="evolution_url" label="URL del servidor" tipo="text"
                        placeholder="https://tu-evolution-api.com"
                        :configured="$estado['evolution_url']"/>
                    <x-config-field name="evolution_key" label="API Key global" tipo="password"
                        placeholder="Tu API Key"
                        :configured="$estado['evolution_key']"/>
                </div>
            </div>

            {{-- ── CHATGPT / OPENAI ── --}}
            @php $g = $grupos['openai']; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-teal-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-teal-700" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0L4.01 14.2A4.501 4.501 0 0 1 2.34 7.896zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.808 2.768a4.504 4.504 0 0 1-.689 8.122V12.57a.79.79 0 0 0-.412-.719zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.806-2.767a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $g['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $g['descripcion'] }}</p>
                        </div>
                    </div>
                    <x-config-badge :configured="$estado['openai_key']"/>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-config-field name="openai_key" label="API Key" tipo="password"
                        placeholder="sk-..."
                        :configured="$estado['openai_key']"/>
                    <x-config-field name="openai_model" label="Modelo por defecto" tipo="text"
                        placeholder="gpt-4o"
                        :configured="$estado['openai_model']"/>
                </div>
            </div>

            {{-- ── DEEPSEEK ── --}}
            @php $g = $grupos['deepseek']; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $g['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $g['descripcion'] }}</p>
                        </div>
                    </div>
                    <x-config-badge :configured="$estado['deepseek_key']"/>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-config-field name="deepseek_key" label="API Key" tipo="password"
                        placeholder="sk-..."
                        :configured="$estado['deepseek_key']"/>
                    <x-config-field name="deepseek_model" label="Modelo por defecto" tipo="text"
                        placeholder="deepseek-chat"
                        :configured="$estado['deepseek_model']"/>
                </div>
            </div>

            {{-- ── GEMINI ── --}}
            @php $g = $grupos['gemini']; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-orange-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $g['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $g['descripcion'] }}</p>
                        </div>
                    </div>
                    <x-config-badge :configured="$estado['gemini_key']"/>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-config-field name="gemini_key" label="API Key" tipo="password"
                        placeholder="AIza..."
                        :configured="$estado['gemini_key']"/>
                    <x-config-field name="gemini_model" label="Modelo por defecto" tipo="text"
                        placeholder="gemini-1.5-pro"
                        :configured="$estado['gemini_model']"/>
                </div>
            </div>

            {{-- ── GOOGLE ── --}}
            @php $g = $grupos['google']; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $g['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $g['descripcion'] }}</p>
                        </div>
                    </div>
                    <x-config-badge :configured="$estado['google_client_id'] && $estado['google_client_secret']"/>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-config-field name="google_client_id" label="Client ID" tipo="password"
                        placeholder="12345-abc.apps.googleusercontent.com"
                        :configured="$estado['google_client_id']"/>
                    <x-config-field name="google_client_secret" label="Client Secret" tipo="password"
                        placeholder="GOCSPX-..."
                        :configured="$estado['google_client_secret']"/>
                </div>
            </div>

            {{-- ── ASSEMBLYAI ── --}}
            @php $g = $grupos['assembly']; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-rose-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $g['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $g['descripcion'] }}</p>
                        </div>
                    </div>
                    <x-config-badge :configured="$estado['assembly_key']"/>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-config-field name="assembly_key" label="API Key" tipo="password"
                        placeholder="Tu API Key de AssemblyAI"
                        :configured="$estado['assembly_key']"/>
                </div>
            </div>

            {{-- ── ZOOM ── --}}
            @php $g = $grupos['zoom']; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-sky-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-sky-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12c0 6.627-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0s12 5.373 12 12zM6.5 8.5A1.5 1.5 0 005 10v4.5A2.5 2.5 0 007.5 17h7A1.5 1.5 0 0016 15.5V11a2.5 2.5 0 00-2.5-2.5H6.5zm10.25 1.45l-2.5 1.786V12.3l2.5 1.786c.32.228.75.005.75-.393v-3.35c0-.398-.43-.62-.75-.393z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $g['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $g['descripcion'] }}</p>
                        </div>
                    </div>
                    <x-config-badge :configured="$estado['zoom_account_id'] && $estado['zoom_client_id'] && $estado['zoom_client_secret']"/>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-config-field name="zoom_account_id" label="Account ID" tipo="password"
                        placeholder="Tu Account ID de Zoom"
                        :configured="$estado['zoom_account_id']"/>
                    <x-config-field name="zoom_client_id" label="Client ID" tipo="password"
                        placeholder="Tu Client ID de Zoom"
                        :configured="$estado['zoom_client_id']"/>
                    <x-config-field name="zoom_client_secret" label="Client Secret" tipo="password"
                        placeholder="Tu Client Secret de Zoom"
                        :configured="$estado['zoom_client_secret']"/>
                </div>
            </div>

        </div>

        {{-- Botón guardar --}}
        <div class="mt-6 flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Guardar configuración
            </button>
        </div>

    </form>

</x-admin-layout>
