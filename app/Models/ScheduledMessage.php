<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ScheduledMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message_text',
        'audio_data',
        'category',
        'associated_question',
        'start_time',
        'end_time',
        'time_period',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // Categorías disponibles
    public static function getCategories()
    {
        return [
            'bienvenida' => 'Mensaje de Bienvenida',
            'seguimiento' => 'Seguimiento',
            'contestar_preguntas' => 'Contestar Preguntas',
            'informacion_productos' => 'Información Adicional de Productos'
        ];
    }

    // Períodos de tiempo
    public static function getTimePeriods()
    {
        return [
            'mañana' => 'Mañana (06:00 - 11:59)',
            'tarde' => 'Tarde (12:00 - 17:59)',
            'noche' => 'Noche (18:00 - 05:59)'
        ];
    }

    // Accessor para obtener audio URL si existe
    public function getAudioUrlAttribute()
    {
        if ($this->audio_data) {
            return 'data:audio/mpeg;base64,' . $this->audio_data;
        }
        return null;
    }

    // Determinar período del día basado en horario
    public static function determineTimePeriod($hour)
    {
        if ($hour >= 6 && $hour < 12) {
            return 'mañana';
        } elseif ($hour >= 12 && $hour < 18) {
            return 'tarde';
        } else {
            return 'noche';
        }
    }

    // Obtener mensajes activos por categoría
    public static function getActiveByCategory($category)
    {
        return self::where('category', $category)
                   ->where('is_active', true)
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    // Obtener mensajes por período de tiempo actual
    public static function getByCurrentTimePeriod()
    {
        $mexicoTime = Carbon::now('America/Mexico_City');
        $currentHour = $mexicoTime->hour;
        $timePeriod = self::determineTimePeriod($currentHour);

        return self::where('time_period', $timePeriod)
                   ->where('is_active', true)
                   ->get();
    }

    // Obtener mensajes dentro del rango horario actual
    public static function getByTimeRange()
    {
        $mexicoTime = Carbon::now('America/Mexico_City');
        $currentTime = $mexicoTime->format('H:i:s');

        return self::where('is_active', true)
                   ->whereNotNull('start_time')
                   ->whereNotNull('end_time')
                   ->where(function($query) use ($currentTime) {
                       $query->where(function($q) use ($currentTime) {
                           // Rango normal (ej: 09:00 - 17:00)
                           $q->whereRaw('start_time <= end_time')
                             ->whereTime('start_time', '<=', $currentTime)
                             ->whereTime('end_time', '>=', $currentTime);
                       })->orWhere(function($q) use ($currentTime) {
                           // Rango que cruza medianoche (ej: 22:00 - 06:00)
                           $q->whereRaw('start_time > end_time')
                             ->where(function($subq) use ($currentTime) {
                                 $subq->whereTime('start_time', '<=', $currentTime)
                                      ->orWhereTime('end_time', '>=', $currentTime);
                             });
                       });
                   })
                   ->get();
    }

    // Scope para mensajes activos
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope por categoría
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Obtener el nombre legible de la categoría
    public function getCategoryNameAttribute()
    {
        $categories = self::getCategories();
        return $categories[$this->category] ?? $this->category;
    }

    // Obtener el nombre legible del período
    public function getTimePeriodNameAttribute()
    {
        $periods = self::getTimePeriods();
        return $periods[$this->time_period] ?? $this->time_period;
    }
}
