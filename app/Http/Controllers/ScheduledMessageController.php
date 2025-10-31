<?php

namespace App\Http\Controllers;

use App\Models\ScheduledMessage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduledMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ScheduledMessage::query();

        // Filtrar por categoría si se especifica
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filtrar por período de tiempo si se especifica
        if ($request->filled('time_period')) {
            $query->where('time_period', $request->time_period);
        }

        // Filtrar por estado si se especifica
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(10);

        // Para AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'messages' => $messages,
                'categories' => ScheduledMessage::getCategories(),
                'time_periods' => ScheduledMessage::getTimePeriods()
            ]);
        }

        return view('scheduled-messages.index', [
            'messages' => $messages,
            'categories' => ScheduledMessage::getCategories(),
            'time_periods' => ScheduledMessage::getTimePeriods(),
            'currentCategory' => $request->category,
            'currentTimePeriod' => $request->time_period,
            'currentStatus' => $request->status
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'success' => true,
            'categories' => ScheduledMessage::getCategories(),
            'time_periods' => ScheduledMessage::getTimePeriods()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message_text' => 'nullable|string',
            'audio_data' => 'nullable|string',
            'category' => 'required|in:bienvenida,seguimiento,contestar_preguntas,informacion_productos',
            'associated_question' => 'nullable|string|max:500',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean'
        ]);

        // Validar que al menos mensaje de texto o audio esté presente
        if (!$request->filled('message_text') && !$request->filled('audio_data')) {
            return response()->json([
                'success' => false,
                'errors' => ['message' => 'Debe proporcionar al menos un mensaje de texto o audio.']
            ], 422);
        }

        // Determinar el período de tiempo automáticamente si se proporcionan horarios
        $timePeriod = null;
        if ($request->filled('start_time')) {
            $hour = (int) explode(':', $request->start_time)[0];
            $timePeriod = ScheduledMessage::determineTimePeriod($hour);
        }

        $message = ScheduledMessage::create([
            'title' => $request->title,
            'message_text' => $request->message_text,
            'audio_data' => $request->audio_data,
            'category' => $request->category,
            'associated_question' => $request->associated_question,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'time_period' => $timePeriod,
            'is_active' => $request->boolean('is_active', true),
            'metadata' => []
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mensaje programado creado exitosamente',
            'data' => $message
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ScheduledMessage $scheduledMessage)
    {
        return response()->json([
            'success' => true,
            'message' => $scheduledMessage,
            'audio_url' => $scheduledMessage->audio_url,
            'category_name' => $scheduledMessage->category_name,
            'time_period_name' => $scheduledMessage->time_period_name
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScheduledMessage $scheduledMessage)
    {
        return response()->json([
            'success' => true,
            'message' => $scheduledMessage,
            'categories' => ScheduledMessage::getCategories(),
            'time_periods' => ScheduledMessage::getTimePeriods(),
            'audio_url' => $scheduledMessage->audio_url
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ScheduledMessage $scheduledMessage)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message_text' => 'nullable|string',
            'audio_data' => 'nullable|string',
            'category' => 'required|in:bienvenida,seguimiento,contestar_preguntas,informacion_productos',
            'associated_question' => 'nullable|string|max:500',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean'
        ]);

        // Validar que al menos mensaje de texto o audio esté presente
        if (!$request->filled('message_text') && !$request->filled('audio_data')) {
            return response()->json([
                'success' => false,
                'errors' => ['message' => 'Debe proporcionar al menos un mensaje de texto o audio.']
            ], 422);
        }

        // Determinar el período de tiempo automáticamente si se proporcionan horarios
        $timePeriod = null;
        if ($request->filled('start_time')) {
            $hour = (int) explode(':', $request->start_time)[0];
            $timePeriod = ScheduledMessage::determineTimePeriod($hour);
        }

        $scheduledMessage->update([
            'title' => $request->title,
            'message_text' => $request->message_text,
            'audio_data' => $request->audio_data,
            'category' => $request->category,
            'associated_question' => $request->associated_question,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'time_period' => $timePeriod,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mensaje programado actualizado exitosamente',
            'data' => $scheduledMessage->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScheduledMessage $scheduledMessage)
    {
        $scheduledMessage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mensaje programado eliminado exitosamente'
        ]);
    }

    /**
     * Toggle the active status of a message
     */
    public function toggleStatus(ScheduledMessage $scheduledMessage)
    {
        $scheduledMessage->update(['is_active' => !$scheduledMessage->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Estado del mensaje actualizado exitosamente',
            'is_active' => $scheduledMessage->is_active
        ]);
    }

    /**
     * Get messages by category
     */
    public function getByCategory(Request $request, $category)
    {
        $messages = ScheduledMessage::getActiveByCategory($category);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'category' => $category,
            'category_name' => ScheduledMessage::getCategories()[$category] ?? $category
        ]);
    }

    /**
     * Get current messages based on Mexico time
     */
    public function getCurrentMessages()
    {
        $mexicoTime = Carbon::now('America/Mexico_City');
        $currentHour = $mexicoTime->hour;
        $timePeriod = ScheduledMessage::determineTimePeriod($currentHour);

        // Obtener mensajes por período actual
        $messagesByPeriod = ScheduledMessage::getByCurrentTimePeriod();

        // Obtener mensajes por rango horario específico
        $messagesByTimeRange = ScheduledMessage::getByTimeRange();

        return response()->json([
            'success' => true,
            'current_time' => $mexicoTime->format('Y-m-d H:i:s'),
            'time_period' => $timePeriod,
            'messages_by_period' => $messagesByPeriod,
            'messages_by_time_range' => $messagesByTimeRange,
            'time_periods' => ScheduledMessage::getTimePeriods()
        ]);
    }
}
