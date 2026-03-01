<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MensajeBot implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Conversation $conversacion) {}

    public function broadcastOn(): Channel
    {
        // Canal específico del tenant para aislar mensajes entre clientes
        $tenantId = tenancy()->tenant?->getTenantKey() ?? 'global';
        return new Channel('bot-tenant.' . $tenantId);
    }

    public function broadcastAs(): string
    {
        return 'nuevo-mensaje';
    }

    public function broadcastWith(): array
    {
        return [
            'id'           => $this->conversacion->id,
            'phone'        => $this->conversacion->phone,
            'instancia'    => $this->conversacion->instancia,
            'client_name'  => $this->conversacion->client_name,
            'user_message' => $this->conversacion->user_message,
            'bot_response' => $this->conversacion->bot_response,
            'hora'         => $this->conversacion->created_at->format('H:i'),
        ];
    }
}
