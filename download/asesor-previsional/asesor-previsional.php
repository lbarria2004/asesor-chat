<?php
/**
 * Plugin Name: Asesor Previsional Virtual
 * Plugin URI: https://asesoriapensiones.cl
 * Description: Chat widget inteligente con Google Gemini AI para asesoría previsional. Captura leads y responde consultas sobre pensiones en Chile.
 * Version: 2.0.0
 * Author: Luis Barría Chodil
 * Author URI: https://asesoriapensiones.cl
 * License: GPL v2 or later
 * Text Domain: asesor-previsional
 */

if (!defined('ABSPATH')) exit;

define('AP_VERSION', '2.0.0');
define('AP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AP_PLUGIN_URL', plugin_dir_url(__FILE__));

class Asesor_Previsional {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        require_once AP_PLUGIN_DIR . 'includes/class-leads.php';
        if (is_admin()) {
            require_once AP_PLUGIN_DIR . 'admin/settings.php';
        }
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_widget'), 999);
        add_action('wp_ajax_ap_chat', array($this, 'ajax_chat'));
        add_action('wp_ajax_nopriv_ap_chat', array($this, 'ajax_chat'));
        add_action('wp_ajax_ap_save_lead', array($this, 'ajax_save_lead'));
        add_action('wp_ajax_nopriv_ap_save_lead', array($this, 'ajax_save_lead'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('asesor-previsional-css', AP_PLUGIN_URL . 'assets/css/widget.css', array(), AP_VERSION);
        wp_enqueue_script('asesor-previsional-js', AP_PLUGIN_URL . 'assets/js/widget.js', array('jquery'), AP_VERSION, true);
        
        // URL del chat con Gemini - puede cambiarse en configuración
        $chat_url = get_option('ap_chat_url', 'https://asesor-chat.vercel.app');
        
        wp_localize_script('asesor-previsional-js', 'asesorPrevisional', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('asesor_previsional_nonce'),
            'chatUrl' => $chat_url,
            'whatsapp' => get_option('ap_whatsapp', '+56934460825'),
            'advisorName' => get_option('ap_advisor_name', 'Luis Barría Chodil'),
            'advisorCode' => get_option('ap_advisor_code', 'Cod. 1360 SPensiones'),
        ));
    }

    public function render_widget() {
        if (!is_admin()) {
            include AP_PLUGIN_DIR . 'templates/widget-html.php';
        }
    }

    public function ajax_chat() {
        check_ajax_referer('asesor_previsional_nonce', 'nonce');
        
        $chat_url = get_option('ap_chat_url', 'https://asesor-chat.vercel.app');
        $messages = isset($_POST['messages']) ? json_decode(stripslashes($_POST['messages']), true) : array();
        $isNew = isset($_POST['isNew']) && $_POST['isNew'] === 'true';

        // Intentar conectar con el chat de Vercel + Gemini
        if (!empty($chat_url)) {
            $api_url = rtrim($chat_url, '/') . '/api/chat';
            
            $response = wp_remote_post($api_url, array(
                'timeout' => 45,
                'headers' => array('Content-Type' => 'application/json'),
                'body' => json_encode(array(
                    'messages' => $messages,
                    'isNewConversation' => $isNew
                )),
            ));
            
            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                
                if (isset($data['message']) && !empty($data['message'])) {
                    wp_send_json_success($data);
                }
            }
        }

        // Fallback inteligente si no hay conexión
        wp_send_json_success(array('message' => $this->get_fallback($messages, $isNew)));
    }

    private function get_fallback($messages, $isNew) {
        if ($isNew) {
            return '¡Hola! 👋 Soy el Asesor Previsional Virtual de Luis Barría Chodil (Cod. 1360 SPensiones). Estoy aquí para ayudarte con tus consultas sobre pensiones en Chile. ¿En qué puedo ayudarte hoy?';
        }
        
        $last = '';
        foreach (array_reverse($messages ?? array()) as $m) {
            if (($m['role'] ?? '') === 'user') {
                $last = strtolower($m['content'] ?? '');
                break;
            }
        }
        
        // Respuestas de respaldo más completas
        $responses = array(
            'renta vitalicia' => 'La Renta Vitalicia es una modalidad donde transfieres tus fondos de la AFP a una aseguradora y recibes una pensión fija mensual de por vida. Es ideal si buscas estabilidad sin preocuparte de las fluctuaciones del mercado. ¿Te gustaría que Luis Barría te explique si esta opción es la mejor para tu caso?',
            'retiro programado' => 'El Retiro Programado te permite mantener tus fondos en la AFP y recibir una pensión variable según la rentabilidad de tus inversiones. Tienes flexibilidad pero también estás expuesto a las variaciones del mercado. ¿Quieres que evaluemos cuál modalidad se adapta mejor a tu perfil?',
            'pgu' => 'La Pensión Garantizada Universal (PGU) es un beneficio estatal para personas de 65 años o más que cumplan ciertos requisitos de residencia y cotizaciones. El monto máximo es de aproximadamente $200.000 mensuales. ¿Te gustaría saber si calificas para este beneficio?',
            'pensión garantizada' => 'La Pensión Garantizada Universal (PGU) es un beneficio estatal para personas de 65 años o más. Puede complementar tu pensión o ser tu única fuente de ingresos si no tienes pensión. ¿Cumples con los 20 años de cotizaciones requeridos?',
            'afp' => 'No existe una AFP que sea "la mejor" para todos. Depende de tu perfil de riesgo, edad y horizonte de jubilación. Algunas ofrecen mejor rentabilidad pero con mayor comisión, otras son más estables. ¿Quieres que analicemos cuál se ajusta mejor a tu perfil?',
            'mejor afp' => 'La elección de AFP depende de tu edad, tolerancia al riesgo y objetivos. Las AFP tienen diferentes fondos (A, B, C, D, E) con distintos perfiles de riesgo. ¿Te gustaría una evaluación personalizada con Luis Barría?',
            'jubilar' => 'La edad legal de jubilación en Chile es 65 años para hombres y 60 años para mujeres. Para jubilarte necesitas tener al menos 10 años de cotizaciones. ¿Estás próximo a cumplir la edad legal o quieres planificar tu jubilación con anticipación?',
            'jubilación' => 'Planificar tu jubilación es una decisión importante. El monto de tu pensión dependerá de tu saldo acumulado, edad, densidad de cotizaciones y la modalidad que elijas. ¿Te gustaría una evaluación gratuita de tu pensión estimada?',
            'jubilarme' => '¡Excelente que estés pensando en tu jubilación! El proceso puede ser complejo y las decisiones que tomes ahora impactarán tu pensión de por vida. ¿Te gustaría que Luis Barría (Cod. 1360 SPensiones) te guíe en este proceso?',
            'invalidez' => 'La pensión de invalidez requiere un dictamen médico de la COMPIN. El proceso puede ser complejo y es importante estar bien asesorado para maximizar tus posibilidades. ¿Te gustaría que Luis Barría te oriente sobre los requisitos y pasos a seguir?',
            'apv' => 'El Ahorro Previsional Voluntario (APV) te permite incrementar tu pensión futura con beneficios tributarios. Puedes rebajar tus depósitos de la base imponible o diferir el impuesto hasta el retiro. ¿Te interesa saber cómo puedes aprovecharlo?',
            'ahorro previsional' => 'El ahorro previsional voluntario es una excelente estrategia para mejorar tu pensión futura. Tienes opciones como APV, Cuenta 2 y Depósitos Convenidos, cada una con diferentes beneficios tributarios. ¿Quieres que evaluemos cuál es la mejor opción para ti?',
            'bono por hijo' => 'El Bono por Hijo Nacido Vivo es un beneficio para mujeres que tengan hijos. Se acredita como cotización en su cuenta de AFP y puede incrementar significativamente su pensión. ¿Tienes hijos y quieres saber cuánto podrías recibir?',
            'excedente' => 'Los excedentes de libre disposición son los fondos que te sobran al momento de jubilarte después de obtener una pensión suficiente. Puedes retirarlos libremente o usarlos para mejorar tu pensión. ¿Estás próximo a jubilarte y quieres evaluar tus opciones?',
            'contacto' => '¡Perfecto! Puedes contactar directamente a Luis Barría Chodil (Cod. 1360 SPensiones) al WhatsApp +56 9 3446 0825 o al email luis.barria@asesoriapensiones.cl. ¿Te gustaría que te llamemos?',
            'whatsapp' => 'Puedes contactar a Luis Barría directamente al WhatsApp +56 9 3446 0825 para una asesoría personalizada sin compromiso. También puedes dejarme tus datos y te contactamos a la brevedad.',
            'asesor' => 'Luis Barría Chodil es asesor previsional registrado con código 1360 en SPensiones. Ofrece asesoría personalizada para jubilación, renta vitalicia, retiro programado y más. ¿Te gustaría agendar una consulta gratuita?',
            'precio' => 'La asesoría inicial es completamente gratuita. Nuestro objetivo es ayudarte a tomar las mejores decisiones sobre tu pensión. ¿Te gustaría agendar una consulta sin compromiso con Luis Barría?',
            'costo' => '¡Buenas noticias! La primera consulta es gratuita. Luis Barría evaluará tu caso y te orientará sobre las mejores opciones para tu pensión. ¿Te gustaría agendar?',
            'valor' => 'La asesoría inicial no tiene costo. Queremos que tomes las mejores decisiones para tu futuro. ¿Te gustaría una evaluación gratuita de tu pensión estimada?',
            'hola' => '¡Hola! 👋 Soy el Asesor Previsional Virtual de Luis Barría Chodil. Estoy aquí para ayudarte con tus consultas sobre pensiones, AFP, jubilación y más. ¿En qué puedo ayudarte?',
            'gracias' => '¡De nada! 😊 Si tienes más consultas sobre pensiones, estoy aquí para ayudarte. También puedes contactar directamente a Luis Barría al WhatsApp +56 9 3446 0825 para una asesoría personalizada.',
        );
        
        foreach ($responses as $keyword => $response) {
            if (strpos($last, $keyword) !== false) {
                return $response;
            }
        }
        
        return 'Entiendo tu consulta sobre el sistema de pensiones. Cada situación es única y requiere análisis personalizado. ¿Te gustaría que Luis Barría (Cod. 1360 SPensiones) te brinde una asesoría gratuita para resolver tus dudas? Puedes contactarlo al WhatsApp +56 9 3446 0825.';
    }

    public function ajax_save_lead() {
        check_ajax_referer('asesor_previsional_nonce', 'nonce');
        
        $name = sanitize_text_field($_POST['name'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        
        if (empty($name) || empty($phone) || empty($email)) {
            wp_send_json_error(array('message' => 'Todos los campos son obligatorios.'));
        }
        
        $lead_id = AP_Leads::save(array(
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'source' => 'chat_widget'
        ));
        
        if ($lead_id) {
            // Enviar notificación por email
            $to = get_option('ap_notification_email', get_option('admin_email'));
            $subject = '🆕 Nuevo lead - Asesor Previsional Virtual';
            $message = "Has recibido una nueva solicitud de asesoría:\n\n";
            $message .= "Nombre: $name\n";
            $message .= "Teléfono: $phone\n";
            $message .= "Email: $email\n\n";
            $message .= "Contactar a la brevedad.";
            
            wp_mail($to, $subject, $message);
            
            wp_send_json_success(array('message' => '¡Gracias! Un asesor te contactará pronto.'));
        }
        
        wp_send_json_error(array('message' => 'Error al guardar. Intenta de nuevo.'));
    }
}

add_action('plugins_loaded', array('Asesor_Previsional', 'get_instance'));

register_activation_hook(__FILE__, function() {
    AP_Leads::create_table();
    add_option('ap_chat_url', 'https://asesor-chat.vercel.app');
    add_option('ap_whatsapp', '+56934460825');
    add_option('ap_advisor_name', 'Luis Barría Chodil');
    add_option('ap_advisor_code', 'Cod. 1360 SPensiones');
    add_option('ap_notification_email', get_option('admin_email'));
});
