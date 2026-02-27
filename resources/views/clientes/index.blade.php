<x-admin-layout title="Clientes">

    {{-- Flash success --}}
    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3.5 text-sm">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3.5 text-sm">
            <p class="font-semibold mb-1">Corrige los siguientes errores:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div x-data="clientesApp()" x-init="init()">

        {{-- ── HEADER ── --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Clientes</h2>
                <p class="text-sm text-gray-400 mt-0.5">
                    {{ $clientes->total() }} cliente{{ $clientes->total() !== 1 ? 's' : '' }} registrado{{ $clientes->total() !== 1 ? 's' : '' }}
                </p>
            </div>
            <button @click="abrirCrear()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Añadir Cliente
            </button>
        </div>

        {{-- ── BÚSQUEDA Y FILTROS ── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-6 overflow-hidden">
            <form method="GET" action="{{ route('clientes.index') }}"
                  class="flex items-stretch divide-x divide-gray-100">

                <label class="flex items-center gap-3 flex-1 px-4 py-3 cursor-text focus-within:bg-blue-50/40 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           placeholder="Buscar por nombre, teléfono o folio..."
                           class="flex-1 text-sm text-gray-700 placeholder-gray-400 bg-transparent border-0 outline-none focus:ring-0 p-0"/>
                    @if (request('buscar'))
                        <a href="{{ route('clientes.index', array_merge(request()->except('buscar'), ['estado' => request('estado')])) }}"
                           class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </label>

                <div class="flex items-center px-4 py-3">
                    <select name="estado"
                            class="text-sm text-gray-600 bg-transparent border-0 outline-none focus:ring-0 cursor-pointer pr-6 appearance-none">
                        <option value="">Todos los estados</option>
                        <option value="Prospecto"  @selected(request('estado') === 'Prospecto')>Prospecto</option>
                        <option value="Activo"     @selected(request('estado') === 'Activo')>Activo</option>
                        <option value="Inactivo"   @selected(request('estado') === 'Inactivo')>Inactivo</option>
                        <option value="Recurrente" @selected(request('estado') === 'Recurrente')>Recurrente</option>
                    </select>
                </div>

                <button type="submit"
                        class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
            </form>

            @if (request()->hasAny(['buscar', 'estado']))
                <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 border-t border-blue-100 text-xs text-blue-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filtros activos:</span>
                    @if (request('buscar'))
                        <span class="px-2 py-0.5 bg-blue-200/60 rounded-full font-medium">"{{ request('buscar') }}"</span>
                    @endif
                    @if (request('estado'))
                        <span class="px-2 py-0.5 bg-blue-200/60 rounded-full font-medium">{{ request('estado') }}</span>
                    @endif
                    <a href="{{ route('clientes.index') }}"
                       class="ml-auto font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                        Limpiar filtros
                    </a>
                </div>
            @endif
        </div>

        {{-- ── TABLA / VACÍO ── --}}
        @if ($clientes->isEmpty())
            <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 py-24 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center mb-5 shadow-sm">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-gray-800 font-semibold text-lg mb-1.5">
                    {{ request()->hasAny(['buscar','estado']) ? 'Sin resultados' : 'Sin clientes aún' }}
                </p>
                <p class="text-gray-400 text-sm mb-7 max-w-xs">
                    {{ request()->hasAny(['buscar','estado'])
                        ? 'Ningún cliente coincide con tu búsqueda.'
                        : 'Comienza añadiendo clientes para llevar el seguimiento.' }}
                </p>
                @unless (request()->hasAny(['buscar','estado']))
                    <button @click="abrirCrear()"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir primer cliente
                    </button>
                @endunless
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/60">
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Folio</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nombre</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Teléfono</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                                <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Observaciones</th>
                                <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($clientes as $cliente)
                                <tr class="hover:bg-gray-50/50 transition-colors">

                                    {{-- Folio --}}
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-mono font-bold rounded-lg border border-blue-100">
                                            {{ $cliente->folio ?? '—' }}
                                        </span>
                                    </td>

                                    {{-- Nombre --}}
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs font-bold text-blue-600">
                                                    {{ mb_strtoupper(mb_substr($cliente->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="font-semibold text-gray-900">{{ $cliente->name }}</span>
                                        </div>
                                    </td>

                                    {{-- Teléfono --}}
                                    <td class="px-5 py-4 text-gray-600">
                                        @if ($cliente->phone)
                                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $cliente->phone) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-1.5 text-green-600 hover:text-green-700 font-medium transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.524 5.847L0 24l6.335-1.509A11.933 11.933 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.8 9.8 0 01-5.003-1.368l-.36-.214-3.723.887.916-3.619-.234-.373A9.77 9.77 0 012.182 12C2.182 6.58 6.58 2.182 12 2.182S21.818 6.58 21.818 12 17.42 21.818 12 21.818z"/>
                                                </svg>
                                                {{ $cliente->phone }}
                                            </a>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>

                                    {{-- Fecha --}}
                                    <td class="px-5 py-4 text-gray-500 whitespace-nowrap">
                                        @if ($cliente->date)
                                            {{ $cliente->date->format('d/m/Y') }}
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>

                                    {{-- Estado --}}
                                    <td class="px-5 py-4">
                                        @if ($cliente->status)
                                            @php
                                                $statusColors = [
                                                    'Activo'     => 'bg-green-100 text-green-700 border-green-200',
                                                    'Inactivo'   => 'bg-gray-100 text-gray-500 border-gray-200',
                                                    'Prospecto'  => 'bg-amber-100 text-amber-700 border-amber-200',
                                                    'Recurrente' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                ];
                                                $color = $statusColors[$cliente->status] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                                            @endphp
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $color }}">
                                                {{ $cliente->status }}
                                            </span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>

                                    {{-- Observaciones count --}}
                                    <td class="px-5 py-4 text-center">
                                        <button @click="abrirObservaciones({{ json_encode([
                                            'id'    => $cliente->id,
                                            'name'  => $cliente->name,
                                            'folio' => $cliente->folio,
                                        ]) }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors
                                                       {{ $cliente->observations_count > 0
                                                            ? 'text-purple-700 bg-purple-50 hover:bg-purple-100'
                                                            : 'text-gray-400 bg-gray-50 hover:bg-gray-100' }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            {{ $cliente->observations_count }}
                                            {{ $cliente->observations_count === 1 ? 'registro' : 'registros' }}
                                        </button>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('clientes.show', $cliente) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Ver historial
                                            </a>
                                            <button @click="abrirEditar({{ json_encode([
                                                'id'         => $cliente->id,
                                                'name'       => $cliente->name,
                                                'phone'      => $cliente->phone,
                                                'date'       => $cliente->date?->format('Y-m-d'),
                                                'status'     => $cliente->status,
                                                'update_url' => route('clientes.update', $cliente),
                                            ]) }})"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Editar
                                            </button>
                                            <form method="POST" action="{{ route('clientes.destroy', $cliente) }}"
                                                  onsubmit="return confirm('¿Eliminar a «{{ addslashes($cliente->name) }}»?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($clientes->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $clientes->links() }}
                    </div>
                @endif
            </div>
        @endif

        {{-- ══ MODAL CREAR / EDITAR CLIENTE ══ --}}
        <template x-teleport="body">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display:none">

                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cerrar()"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.stop>

                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-gray-900"
                                x-text="modo === 'crear' ? 'Añadir Cliente' : 'Editar Cliente'"></h3>
                        </div>
                        <button @click="cerrar()"
                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form :action="formAction" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="modo === 'editar' ? 'PUT' : 'POST'">

                        <div class="px-6 py-5 space-y-4">

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Nombre <span class="text-red-500 normal-case font-normal">*</span>
                                </label>
                                <input type="text" name="name" x-model="form.name" required
                                       placeholder="Nombre completo del cliente..."
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"/>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Teléfono</label>
                                    <input type="text" name="phone" x-model="form.phone"
                                           placeholder="+52 123 456 7890"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Fecha</label>
                                    <input type="date" name="date" x-model="form.date"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white"/>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Estado</label>
                                <select name="status" x-model="form.status"
                                        class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white">
                                    <option value="">— Sin estado —</option>
                                    <option value="Prospecto">Prospecto</option>
                                    <option value="Activo">Activo</option>
                                    <option value="Recurrente">Recurrente</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>

                        </div>

                        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3 bg-gray-50/50 rounded-b-2xl">
                            <button type="button" @click="cerrar()"
                                    class="px-5 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                <span x-text="modo === 'crear' ? 'Guardar cliente' : 'Actualizar cliente'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ══ MODAL OBSERVACIONES ══ --}}
        <template x-teleport="body">
            <div x-show="obsModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display:none">

                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cerrarObs()"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.stop>

                    {{-- Header del modal --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900"
                                    x-text="'Historial de ' + (clienteActual?.name ?? '')"></h3>
                                <p class="text-xs text-gray-400 mt-0.5"
                                   x-text="clienteActual?.folio ?? ''"></p>
                            </div>
                        </div>
                        <button @click="cerrarObs()"
                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Cuerpo scrollable --}}
                    <div class="flex-1 overflow-y-auto">

                        {{-- Tabla de observaciones existentes --}}
                        <div class="px-6 pt-5">
                            <div x-show="loadingObs" class="flex items-center justify-center py-10 text-gray-400 gap-3">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span class="text-sm">Cargando registros...</span>
                            </div>

                            <template x-if="!loadingObs && observaciones.length === 0">
                                <div class="flex flex-col items-center py-10 text-center">
                                    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 mb-1">Sin registros aún</p>
                                    <p class="text-xs text-gray-400">Añade el primer registro de este cliente.</p>
                                </div>
                            </template>

                            <template x-if="!loadingObs && observaciones.length > 0">
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                                        Historial (<span x-text="observaciones.length"></span> registros)
                                    </h4>
                                    <div class="rounded-xl border border-gray-100 overflow-hidden mb-2">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="bg-gray-50/80 border-b border-gray-100">
                                                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fecha</th>
                                                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Peso</th>
                                                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Edad</th>
                                                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Observación</th>
                                                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Productos sugeridos</th>
                                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50">
                                                <template x-for="obs in observaciones" :key="obs.id">
                                                    <tr class="hover:bg-gray-50/60 transition-colors">
                                                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs" x-text="formatFecha(obs.created_at)"></td>
                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            <template x-if="obs.weight">
                                                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">
                                                                    <span x-text="obs.weight"></span> kg
                                                                </span>
                                                            </template>
                                                            <template x-if="!obs.weight">
                                                                <span class="text-gray-300 text-xs">—</span>
                                                            </template>
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            <template x-if="obs.age">
                                                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full">
                                                                    <span x-text="obs.age"></span> años
                                                                </span>
                                                            </template>
                                                            <template x-if="!obs.age">
                                                                <span class="text-gray-300 text-xs">—</span>
                                                            </template>
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-600 text-xs max-w-xs">
                                                            <span x-text="obs.observation || '—'" class="line-clamp-2"></span>
                                                        </td>
                                                        <td class="px-4 py-3 text-xs max-w-[200px]">
                                                            <template x-if="obs.suggested_products">
                                                                <div class="flex flex-wrap gap-1.5">
                                                                    <template x-for="nombre in splitProds(obs.suggested_products)" :key="nombre">
                                                                        <div class="flex items-center gap-1.5 px-2 py-1 bg-white border border-gray-100 rounded-lg shadow-sm max-w-[150px]">
                                                                            {{-- Imagen del producto --}}
                                                                            <div class="w-7 h-7 rounded-md overflow-hidden bg-gray-100 flex-shrink-0 flex items-center justify-center">
                                                                                <template x-if="getProducto(nombre)?.image_url">
                                                                                    <img :src="getProducto(nombre).image_url"
                                                                                         :alt="nombre"
                                                                                         class="w-full h-full object-cover"/>
                                                                                </template>
                                                                                <template x-if="!getProducto(nombre)?.image_url">
                                                                                    <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                                    </svg>
                                                                                </template>
                                                                            </div>
                                                                            <span x-text="nombre" class="text-xs font-medium text-gray-700 truncate"></span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="!obs.suggested_products">
                                                                <span class="text-gray-300">—</span>
                                                            </template>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <div class="flex items-center justify-end gap-1.5">
                                                                <button @click="editarObs(obs)"
                                                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                    </svg>
                                                                    Editar
                                                                </button>
                                                                <button @click="eliminarObs(obs.id)"
                                                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                    </svg>
                                                                    Eliminar
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Formulario nueva / editar observación --}}
                        <div class="px-6 pb-6 pt-4">
                            <div class="bg-gray-50/70 rounded-xl border border-gray-100 p-5">
                                <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span x-text="modoObs === 'crear' ? 'Nuevo registro' : 'Editar registro'"></span>
                                </h4>

                                {{-- Peso + Edad --}}
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>
                                                Peso (kg)
                                            </span>
                                        </label>
                                        <input type="number" x-model="formObs.weight"
                                               step="0.01" min="0" max="999"
                                               placeholder="Ej. 75.5"
                                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white"/>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-indigo-400 inline-block"></span>
                                                Edad (años)
                                            </span>
                                        </label>
                                        <input type="number" x-model="formObs.age"
                                               min="0" max="120"
                                               placeholder="Ej. 35"
                                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white"/>
                                    </div>
                                </div>

                                {{-- Observación --}}
                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Observaciones</label>
                                    <textarea x-model="formObs.observation" rows="3"
                                              placeholder="Notas clínicas, seguimiento, síntomas, historial..."
                                              class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition resize-none bg-white"></textarea>
                                </div>

                                {{-- Productos sugeridos (selector visual) --}}
                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                                            Productos sugeridos
                                        </span>
                                    </label>

                                    {{-- Buscador --}}
                                    <div class="relative mb-2">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <input type="text" x-model="obsProductSearch"
                                               placeholder="Buscar producto..."
                                               class="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition bg-white"/>
                                    </div>

                                    {{-- Grid de productos --}}
                                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-44 overflow-y-auto p-1 rounded-lg border border-gray-100 bg-gray-50/50">
                                        <template x-for="prod in filtrarProductos()" :key="prod.id">
                                            <button type="button"
                                                    @click="toggleProducto(prod.name)"
                                                    :class="esProdSeleccionado(prod.name)
                                                        ? 'ring-2 ring-purple-500 bg-purple-50 border-purple-200'
                                                        : 'bg-white border-gray-100 hover:border-gray-200 hover:bg-gray-50'"
                                                    class="relative flex flex-col items-center p-2 rounded-xl border transition-all text-center cursor-pointer">

                                                {{-- Checkmark si está seleccionado --}}
                                                <template x-if="esProdSeleccionado(prod.name)">
                                                    <div class="absolute top-1 right-1 w-4 h-4 bg-purple-500 rounded-full flex items-center justify-center">
                                                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </div>
                                                </template>

                                                {{-- Imagen --}}
                                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 mb-1.5 flex items-center justify-center flex-shrink-0">
                                                    <template x-if="prod.image_url">
                                                        <img :src="prod.image_url" :alt="prod.name"
                                                             class="w-full h-full object-cover"/>
                                                    </template>
                                                    <template x-if="!prod.image_url">
                                                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    </template>
                                                </div>

                                                {{-- Nombre --}}
                                                <span x-text="prod.name"
                                                      :class="esProdSeleccionado(prod.name) ? 'text-purple-700 font-semibold' : 'text-gray-600'"
                                                      class="text-[10px] leading-tight line-clamp-2 w-full"></span>
                                            </button>
                                        </template>

                                        {{-- Sin resultados en búsqueda --}}
                                        <template x-if="filtrarProductos().length === 0">
                                            <div class="col-span-4 py-6 text-center text-xs text-gray-400">
                                                Sin productos que coincidan
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Productos seleccionados (chips con ×) --}}
                                    <div x-show="selectedProdNames().length > 0" class="flex flex-wrap gap-1.5 mt-2.5">
                                        <template x-for="nombre in selectedProdNames()" :key="nombre">
                                            <div class="flex items-center gap-1.5 pl-1 pr-2 py-1 bg-white border border-purple-200 rounded-full shadow-sm">
                                                <div class="w-5 h-5 rounded-full overflow-hidden bg-gray-100 flex-shrink-0 flex items-center justify-center">
                                                    <template x-if="getProducto(nombre)?.image_url">
                                                        <img :src="getProducto(nombre).image_url" class="w-full h-full object-cover"/>
                                                    </template>
                                                    <template x-if="!getProducto(nombre)?.image_url">
                                                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                                                        </svg>
                                                    </template>
                                                </div>
                                                <span x-text="nombre" class="text-xs font-semibold text-purple-700"></span>
                                                <button type="button" @click="toggleProducto(nombre)"
                                                        class="text-purple-400 hover:text-purple-700 transition-colors leading-none">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Botones --}}
                                <div class="flex items-center justify-end gap-3">
                                    <template x-if="modoObs === 'editar'">
                                        <button type="button" @click="resetFormObs()"
                                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-200 rounded-lg transition-colors">
                                            Cancelar edición
                                        </button>
                                    </template>
                                    <button type="button" @click="guardarObs()"
                                            :disabled="savingObs"
                                            class="inline-flex items-center gap-2 px-5 py-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-60 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                                        <svg x-show="!savingObs" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                        </svg>
                                        <svg x-show="savingObs" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span x-text="modoObs === 'crear' ? 'Guardar registro' : 'Actualizar registro'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>

    <script>
    function clientesApp() {
        return {
            // ── Modal cliente ──
            open: false,
            modo: 'crear',
            formAction: '{{ route('clientes.store') }}',
            form: { name: '', phone: '', date: '', status: '' },

            // ── Modal observaciones ──
            obsModal: false,
            clienteActual: null,
            observaciones: [],
            loadingObs: false,
            savingObs: false,
            modoObs: 'crear',
            obsEditId: null,
            formObs: { weight: '', age: '', observation: '', suggested_products: '' },
            todosProductos: @json($productos),
            obsProductSearch: '',

            init() {
                @if ($errors->any())
                    this.abrirCrear();
                    this.form.name   = @json(old('name', ''));
                    this.form.phone  = @json(old('phone', ''));
                    this.form.date   = @json(old('date', ''));
                    this.form.status = @json(old('status', ''));
                @endif
            },

            // ── Clientes ──
            abrirCrear() {
                this.modo = 'crear';
                this.formAction = '{{ route('clientes.store') }}';
                this.form = { name: '', phone: '', date: '', status: '' };
                this.open = true;
                document.body.style.overflow = 'hidden';
            },

            abrirEditar(c) {
                this.modo = 'editar';
                this.formAction = c.update_url;
                this.form = {
                    name:   c.name   ?? '',
                    phone:  c.phone  ?? '',
                    date:   c.date   ?? '',
                    status: c.status ?? '',
                };
                this.open = true;
                document.body.style.overflow = 'hidden';
            },

            cerrar() {
                this.open = false;
                document.body.style.overflow = '';
            },

            // ── Observaciones ──
            async abrirObservaciones(cliente) {
                this.clienteActual = cliente;
                this.observaciones = [];
                this.resetFormObs();
                this.obsModal = true;
                document.body.style.overflow = 'hidden';
                await this.cargarObservaciones();
            },

            cerrarObs() {
                this.obsModal = false;
                this.clienteActual = null;
                this.observaciones = [];
                document.body.style.overflow = '';
            },

            async cargarObservaciones() {
                this.loadingObs = true;
                try {
                    const res = await axios.get(`/clientes/${this.clienteActual.id}/observaciones`);
                    this.observaciones = res.data;
                } catch (e) {
                    console.error('Error al cargar observaciones:', e);
                } finally {
                    this.loadingObs = false;
                }
            },

            async guardarObs() {
                this.savingObs = true;
                try {
                    if (this.modoObs === 'crear') {
                        const res = await axios.post(
                            `/clientes/${this.clienteActual.id}/observaciones`,
                            this.formObs
                        );
                        this.observaciones.unshift(res.data);
                    } else {
                        const res = await axios.put(
                            `/clientes/${this.clienteActual.id}/observaciones/${this.obsEditId}`,
                            this.formObs
                        );
                        const idx = this.observaciones.findIndex(o => o.id === this.obsEditId);
                        if (idx !== -1) this.observaciones.splice(idx, 1, res.data);
                    }
                    this.resetFormObs();
                } catch (e) {
                    console.error('Error al guardar observación:', e);
                    alert('Ocurrió un error al guardar. Intenta de nuevo.');
                } finally {
                    this.savingObs = false;
                }
            },

            async eliminarObs(id) {
                if (!confirm('¿Eliminar este registro de observación?')) return;
                try {
                    await axios.delete(`/clientes/${this.clienteActual.id}/observaciones/${id}`);
                    this.observaciones = this.observaciones.filter(o => o.id !== id);
                } catch (e) {
                    console.error('Error al eliminar:', e);
                    alert('No se pudo eliminar el registro.');
                }
            },

            editarObs(obs) {
                this.modoObs  = 'editar';
                this.obsEditId = obs.id;
                this.formObs  = {
                    weight:             obs.weight             ?? '',
                    age:                obs.age                ?? '',
                    observation:        obs.observation        ?? '',
                    suggested_products: obs.suggested_products ?? '',
                };
            },

            resetFormObs() {
                this.modoObs   = 'crear';
                this.obsEditId = null;
                this.formObs   = { weight: '', age: '', observation: '', suggested_products: '' };
            },

            // ── Helpers ──
            formatFecha(isoStr) {
                if (!isoStr) return '—';
                const d = new Date(isoStr);
                return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
            },

            splitProds(str) {
                if (!str) return [];
                return str.split(',').map(s => s.trim()).filter(Boolean);
            },

            // ── Productos helpers ──
            getProducto(nombre) {
                return this.todosProductos.find(p => p.name === nombre) ?? null;
            },

            filtrarProductos() {
                if (!this.obsProductSearch) return this.todosProductos;
                const q = this.obsProductSearch.toLowerCase();
                return this.todosProductos.filter(p => p.name.toLowerCase().includes(q));
            },

            selectedProdNames() {
                return this.splitProds(this.formObs.suggested_products);
            },

            esProdSeleccionado(nombre) {
                return this.selectedProdNames().includes(nombre);
            },

            toggleProducto(nombre) {
                const seleccionados = this.selectedProdNames();
                const idx = seleccionados.indexOf(nombre);
                if (idx === -1) {
                    seleccionados.push(nombre);
                } else {
                    seleccionados.splice(idx, 1);
                }
                this.formObs.suggested_products = seleccionados.join(', ');
            },
        };
    }
    </script>

</x-admin-layout>
