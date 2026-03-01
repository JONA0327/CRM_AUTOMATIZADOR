<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal de conversaciones del bot — solo usuarios del mismo tenant pueden escuchar
Broadcast::channel('bot-tenant.{tenantId}', function ($user, $tenantId) {
    return $user->tenant_id === $tenantId;
});
