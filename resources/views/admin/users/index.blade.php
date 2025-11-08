@extends('layouts.app')

@section('content')
<div class="module-shell" data-module="admin-users">
    <header class="module-header">
        <div class="module-header__headline">
            <span class="stat-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
            </span>
            <div>
                <h2 class="module-title">Gestión de Usuarios</h2>
                <p class="module-subtitle">Administrar y monitorear las cuentas del sistema</p>
            </div>
        </div>
        <div class="module-actions">
            <a href="{{ route('admin.dashboard') }}" class="module-btn module-btn--ghost">← Volver</a>
            <button type="button" class="module-btn module-btn--primary">+ Nuevo usuario</button>
        </div>
    </header>

    <section class="module-section">
        <div class="module-stats">
            <div class="module-stats__item">
                <span class="module-stats__label">Total</span>
                <span class="module-stats__value">{{ $users->count() }}</span>
            </div>
            <div class="module-stats__item">
                <span class="module-stats__label">Administradores</span>
                <span class="module-stats__value">{{ $users->where('role.name', 'admin')->count() }}</span>
            </div>
            <div class="module-stats__item">
                <span class="module-stats__label">Usuarios regulares</span>
                <span class="module-stats__value">{{ $users->where('role.name', 'user')->count() }}</span>
            </div>
            <div class="module-stats__item">
                <span class="module-stats__label">Activos hoy</span>
                <span class="module-stats__value">{{ $users->where('updated_at', '>=', now()->startOfDay())->count() }}</span>
            </div>
        </div>

        <div class="module-panel">
            <div class="module-panel__controls">
                <div class="module-panel__fields">
                    <div class="module-field">
                        <label for="user-search">Buscar</label>
                        <input type="text" id="user-search" placeholder="Buscar usuarios..." class="module-input">
                    </div>
                    <div class="module-field">
                        <label for="role-filter">Rol</label>
                        <select id="role-filter" class="module-input">
                            <option value="">Todos los roles</option>
                            <option value="admin">Administrador</option>
                            <option value="user">Usuario</option>
                            <option value="manager">Gerente</option>
                            <option value="sales">Vendedor</option>
                        </select>
                    </div>
                </div>
                <div class="module-meta">
                    <span>Mostrando {{ $users->count() }} usuarios</span>
                    <span>Total registrados: {{ $users->count() }}</span>
                </div>
            </div>
        </div>
        <div class="module-table">
            <div class="module-table__head">
                <h3 class="text-lg font-semibold text-blue-900">Listado de usuarios</h3>
            </div>
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Email verificado</th>
                            <th>Registrado</th>
                            <th>Última actividad</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-700 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-blue-900">{{ $user->name }}</div>
                                        <div class="text-sm text-soft">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                @if($user->role)
                                    @php
                                        $role = $user->role->name;
                                        $roleColors = [
                                            'admin' => 'bg-red-100 text-red-700',
                                            'manager' => 'bg-purple-100 text-purple-700',
                                            'sales' => 'bg-green-100 text-green-700',
                                            'user' => 'bg-blue-100 text-blue-700',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$role] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ ucfirst($role) }}
                                    </span>
                                @else
                                    <span class="text-soft">Sin rol</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center text-green-600 text-sm">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Verificado
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-red-600 text-sm">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-sm text-soft">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap text-sm text-soft">{{ $user->updated_at->diffForHumans() }}</td>
                            <td class="whitespace-nowrap">
                                <div class="flex justify-end gap-2">
                                    <button class="action-btn" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <button class="action-btn" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="module-table__footer">
                <span class="text-sm text-soft">Total: {{ $users->count() }} registros</span>
                <span class="text-sm text-soft">Actualizado {{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </section>
</div>
@endsection
