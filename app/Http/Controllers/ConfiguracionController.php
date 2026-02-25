<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    /**
     * Definición de todos los campos gestionados, agrupados por servicio.
     * Solo se almacena metadata — los valores reales se leen de la BD cifrados.
     */
    private array $campos = [
        'evolution' => [
            'label'       => 'Evolution API',
            'descripcion' => 'API para gestionar instancias de WhatsApp',
            'claves'      => [
                'evolution_url' => ['label' => 'URL del servidor', 'tipo' => 'text',     'placeholder' => 'https://tu-evolution-api.com'],
                'evolution_key' => ['label' => 'API Key global',   'tipo' => 'password', 'placeholder' => 'Tu API Key de Evolution'],
            ],
        ],
        'openai' => [
            'label'       => 'ChatGPT (OpenAI)',
            'descripcion' => 'Modelos GPT para generación de respuestas',
            'claves'      => [
                'openai_key'   => ['label' => 'API Key',        'tipo' => 'password', 'placeholder' => 'sk-...'],
                'openai_model' => ['label' => 'Modelo por defecto', 'tipo' => 'text', 'placeholder' => 'gpt-4o'],
            ],
        ],
        'deepseek' => [
            'label'       => 'DeepSeek',
            'descripcion' => 'Modelos DeepSeek para inferencia de lenguaje',
            'claves'      => [
                'deepseek_key'   => ['label' => 'API Key',           'tipo' => 'password', 'placeholder' => 'sk-...'],
                'deepseek_model' => ['label' => 'Modelo por defecto', 'tipo' => 'text',    'placeholder' => 'deepseek-chat'],
            ],
        ],
        'gemini' => [
            'label'       => 'Google Gemini',
            'descripcion' => 'Modelos Gemini de Google para IA generativa',
            'claves'      => [
                'gemini_key'   => ['label' => 'API Key',           'tipo' => 'password', 'placeholder' => 'AIza...'],
                'gemini_model' => ['label' => 'Modelo por defecto', 'tipo' => 'text',    'placeholder' => 'gemini-1.5-pro'],
            ],
        ],
        'google' => [
            'label'       => 'Google (Calendar & Drive)',
            'descripcion' => 'Acceso a Google Calendar y Google Drive mediante OAuth2',
            'claves'      => [
                'google_client_id'     => ['label' => 'Client ID',     'tipo' => 'password', 'placeholder' => '12345-abc.apps.googleusercontent.com'],
                'google_client_secret' => ['label' => 'Client Secret', 'tipo' => 'password', 'placeholder' => 'GOCSPX-...'],
            ],
        ],
        'assembly' => [
            'label'       => 'AssemblyAI',
            'descripcion' => 'Transcripción de audio/voz a texto',
            'claves'      => [
                'assembly_key' => ['label' => 'API Key', 'tipo' => 'password', 'placeholder' => 'Tu API Key de AssemblyAI'],
            ],
        ],
        'zoom' => [
            'label'       => 'Zoom',
            'descripcion' => 'Integración con reuniones y webinars de Zoom',
            'claves'      => [
                'zoom_account_id'     => ['label' => 'Account ID',     'tipo' => 'password', 'placeholder' => 'Tu Account ID de Zoom'],
                'zoom_client_id'      => ['label' => 'Client ID',      'tipo' => 'password', 'placeholder' => 'Tu Client ID de Zoom'],
                'zoom_client_secret'  => ['label' => 'Client Secret',  'tipo' => 'password', 'placeholder' => 'Tu Client Secret de Zoom'],
            ],
        ],
    ];

    /**
     * Muestra la página de configuración.
     * Las API Keys solo pasan como booleano (configurada/no).
     * El system_prompt se pasa descifrado para que sea editable.
     */
    public function index()
    {
        $estado = [];
        foreach ($this->campos as $grupo => $info) {
            foreach ($info['claves'] as $clave => $meta) {
                $estado[$clave] = Configuracion::isConfigured($clave);
            }
        }

        $systemPrompt = Configuracion::get('system_prompt', '');

        return view('configuracion.index', [
            'grupos'       => $this->campos,
            'estado'       => $estado,
            'systemPrompt' => $systemPrompt,
        ]);
    }

    /**
     * Guarda las claves enviadas. Solo actualiza si el campo no está vacío.
     * Un campo vacío significa "mantener el valor actual" (excepto system_prompt
     * que sí puede guardarse vacío para borrarlo).
     */
    public function update(Request $request)
    {
        $guardados = 0;

        // Guardar system_prompt (texto plano visible, se cifra igual en BD)
        if ($request->has('system_prompt')) {
            $prompt = $request->input('system_prompt', '');
            if (trim($prompt) !== '') {
                Configuracion::set(
                    clave:       'system_prompt',
                    valor:       $prompt,
                    grupo:       'bot',
                    descripcion: 'Prompt del sistema para el bot',
                );
            } else {
                Configuracion::clear('system_prompt');
            }
            $guardados++;
        }

        foreach ($this->campos as $grupo => $info) {
            foreach ($info['claves'] as $clave => $meta) {
                $valor = $request->input($clave);

                if ($valor !== null && trim($valor) !== '') {
                    Configuracion::set(
                        clave:       $clave,
                        valor:       trim($valor),
                        grupo:       $grupo,
                        descripcion: $meta['label'],
                    );
                    $guardados++;
                }
            }
        }

        $msg = $guardados > 0
            ? "Se guardaron {$guardados} campo(s) correctamente."
            : 'No se realizaron cambios (todos los campos estaban vacíos).';

        return redirect()->route('configuracion.index')->with('success', $msg);
    }

    /**
     * Limpia (borra) el valor de una clave específica.
     */
    public function limpiar(string $clave)
    {
        $clavesValidas = collect($this->campos)
            ->flatMap(fn($g) => array_keys($g['claves']))
            ->push('system_prompt')
            ->toArray();

        if (in_array($clave, $clavesValidas)) {
            Configuracion::clear($clave);
        }

        return redirect()->route('configuracion.index')
            ->with('success', "Clave «{$clave}» eliminada.");
    }
}
