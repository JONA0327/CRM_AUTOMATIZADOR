<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ScheduledMessage;

class ScheduledMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mensajes de Bienvenida
        ScheduledMessage::create([
            'title' => 'Bienvenida Matutina',
            'message_text' => '¡Buenos días! Esperamos que tengas un excelente día. Estamos aquí para ayudarte con todos tus productos de salud y bienestar de 4Life. ¿En qué podemos asistirte hoy?',
            'category' => 'bienvenida',
            'start_time' => '06:00',
            'end_time' => '11:59',
            'time_period' => 'mañana',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'priority' => 'high'
            ]
        ]);

        ScheduledMessage::create([
            'title' => 'Bienvenida Vespertina',
            'message_text' => '¡Buenas tardes! Bienvenido a 4Life. Nuestros productos están diseñados para apoyar tu sistema inmunológico y mejorar tu calidad de vida. ¿Te gustaría conocer más sobre nuestros productos estrella?',
            'category' => 'bienvenida',
            'start_time' => '12:00',
            'end_time' => '17:59',
            'time_period' => 'tarde',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'priority' => 'high'
            ]
        ]);

        ScheduledMessage::create([
            'title' => 'Bienvenida Nocturna',
            'message_text' => '¡Buenas noches! Aunque es tarde, estamos disponibles para ti. 4Life ofrece soluciones de salud para toda la familia. ¿Hay algún producto específico que te interese conocer?',
            'category' => 'bienvenida',
            'start_time' => '18:00',
            'end_time' => '05:59',
            'time_period' => 'noche',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'priority' => 'medium'
            ]
        ]);

        // Mensajes de Seguimiento
        ScheduledMessage::create([
            'title' => 'Seguimiento Post-Compra',
            'message_text' => 'Hola, ¿cómo ha sido tu experiencia con los productos 4Life que adquiriste? Nos encantaría conocer tu opinión y apoyarte en tu proceso de bienestar. ¿Has notado algún beneficio?',
            'category' => 'seguimiento',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'trigger' => 'post_purchase',
                'days_after' => 7
            ]
        ]);

        ScheduledMessage::create([
            'title' => 'Recordatorio de Consulta',
            'message_text' => 'Te recordamos que tienes una consulta pendiente con nuestro equipo de expertos en 4Life. Estamos listos para resolver todas tus dudas y ayudarte a elegir los mejores productos para ti.',
            'category' => 'seguimiento',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'trigger' => 'appointment_reminder'
            ]
        ]);

        ScheduledMessage::create([
            'title' => 'Seguimiento de Interés',
            'message_text' => 'Notamos que has estado explorando nuestros productos de Transfer Factor. ¿Te gustaría recibir más información personalizada o agendar una consulta gratuita con nuestros especialistas?',
            'category' => 'seguimiento',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'trigger' => 'product_interest',
                'category_interest' => 'transfer_factor'
            ]
        ]);

        // Mensajes para Contestar Preguntas
        ScheduledMessage::create([
            'title' => 'Respuesta sobre Transfer Factor',
            'message_text' => 'Transfer Factor es nuestra tecnología patentada que ayuda a educar y fortalecer el sistema inmunológico. Está disponible en varios productos como Transfer Factor Plus, RioVida y PRO-TF. Cada uno tiene beneficios específicos según tus necesidades.',
            'category' => 'contestar_preguntas',
            'associated_question' => '¿Qué es Transfer Factor?',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'faq_id' => 1,
                'keywords' => ['transfer factor', 'inmunológico', 'patentada']
            ]
        ]);

        ScheduledMessage::create([
            'title' => 'Respuesta sobre Precios',
            'message_text' => 'Nuestros precios varían según el producto y la presentación. Transfer Factor Plus tiene un costo de $89 USD, RioVida $45 USD, y PRO-TF $69 USD. Todos incluyen envío gratuito y garantía de satisfacción. ¿Te interesa algún producto en particular?',
            'category' => 'contestar_preguntas',
            'associated_question' => '¿Cuáles son los precios de los productos?',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'faq_id' => 2,
                'keywords' => ['precio', 'costo', 'cuánto']
            ]
        ]);

        ScheduledMessage::create([
            'title' => 'Respuesta sobre Efectos Secundarios',
            'message_text' => 'Los productos 4Life están formulados con ingredientes naturales y son generalmente bien tolerados. En casos raros, algunas personas pueden experimentar reacciones leves como malestar estomacal si se toman con el estómago vacío. Recomendamos tomarlos con alimentos.',
            'category' => 'contestar_preguntas',
            'associated_question' => '¿Los productos tienen efectos secundarios?',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'faq_id' => 3,
                'keywords' => ['efectos secundarios', 'reacciones', 'seguridad']
            ]
        ]);

        ScheduledMessage::create([
            'title' => 'Respuesta sobre Resultados',
            'message_text' => 'Los resultados pueden variar entre personas, pero muchos clientes reportan sentirse más energizados y con mejor bienestar general en las primeras 2-4 semanas. Para resultados óptimos, recomendamos uso consistente durante al menos 90 días.',
            'category' => 'contestar_preguntas',
            'associated_question' => '¿Cuándo veré resultados?',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'faq_id' => 4,
                'keywords' => ['resultados', 'tiempo', 'efectividad']
            ]
        ]);

        // Mensajes de Información Adicional de Productos
        ScheduledMessage::create([
            'title' => 'Información Transfer Factor Plus',
            'message_text' => 'Transfer Factor Plus combina Transfer Factor con hongos shiitake, maitake y cordyceps para un apoyo inmunológico superior. Ideal para personas activas o en temporadas de cambios climáticos. Cada frasco contiene 90 cápsulas para 30 días.',
            'category' => 'informacion_productos',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'product_name' => 'Transfer Factor Plus',
                'product_category' => 'immune_support',
                'benefits' => ['apoyo inmunológico', 'energía', 'resistencia']
            ]
        ]);

        ScheduledMessage::create([
            'title' => 'Información RioVida',
            'message_text' => 'RioVida es nuestro delicioso jugo antioxidante con Transfer Factor y frutas del amazonas como açaí, granada y arándano. Perfecto para toda la familia, proporciona antioxidantes y apoyo inmunológico en un sabor irresistible.',
            'category' => 'informacion_productos',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'product_name' => 'RioVida',
                'product_category' => 'antioxidants',
                'benefits' => ['antioxidantes', 'sabor', 'familia']
            ]
        ]);

        ScheduledMessage::create([
            'title' => 'Información PRO-TF',
            'message_text' => 'PRO-TF es nuestra fórmula profesional concentrada de Transfer Factor. Diseñada para personas que buscan el máximo apoyo inmunológico. Contiene 4 veces más Transfer Factor que nuestro producto básico.',
            'category' => 'informacion_productos',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'product_name' => 'PRO-TF',
                'product_category' => 'professional',
                'benefits' => ['concentrado', 'profesional', 'máximo apoyo']
            ]
        ]);

        ScheduledMessage::create([
            'title' => 'Información Energy & Fitness',
            'message_text' => 'Nuestra línea Energy & Fitness está diseñada para deportistas y personas activas. Incluye productos pre-entreno, recuperación muscular y suplementos para mantener niveles óptimos de energía durante el ejercicio.',
            'category' => 'informacion_productos',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'system',
                'product_name' => 'Energy & Fitness',
                'product_category' => 'sports',
                'benefits' => ['energía', 'rendimiento', 'recuperación']
            ]
        ]);
    }
}
