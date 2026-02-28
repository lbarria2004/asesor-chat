<?php
/**
 * Plugin Name: Asesor Previsional Virtual
 * Plugin URI: https://asesoriapensiones.cl
 * Description: Chat widget inteligente con Google Gemini AI para asesoría previsional. Captura leads y responde consultas sobre pensiones en Chile.
 * Version: 1.1.0
 * Author: Luis Barría Chodil
 * Author URI: https://asesoriapensiones.cl
 * License: GPL v2 or later
 * Text Domain: asesor-previsional
 */

if (!defined('ABSPATH')) exit;

define('AP_VERSION', '1.1.0');
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
        wp_localize_script('asesor-previsional-js', 'asesorPrevisional', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('asesor_previsional_nonce'),
            'chatUrl' => get_option('ap_chat_url', ''),
        ));
    }

    public function render_widget() {
        if (!is_admin()) {
            include AP_PLUGIN_DIR . 'templates/widget-html.php';
        }
    }

    public function ajax_chat() {
        check_ajax_referer('asesor_previsional_nonce', 'nonce');
        
        $chat_url = get_option('ap_chat_url', '');
        $messages = isset($_POST['messages']) ? json_decode(stripslashes($_POST['messages']), true) : array();
        $isNew = isset($_POST['isNew']) && $_POST['isNew'] === 'true';

        if (!empty($chat_url)) {
            $response = wp_remote_post(rtrim($chat_url, '/') . '/api/chat', array(
                'timeout' => 30,
                'headers' => array('Content-Type' => 'application/json'),
                'body' => json_encode(array('messages' => $messages, 'isNewConversation' => $isNew)),
            ));
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (isset($data['message'])) {
                    wp_send_json_success($data);
                }
            }
        }

        // Fallback
        wp_send_json_success(array('message' => $this->get_fallback($messages, $isNew)));
    }

    private function get_fallback($messages, $isNew) {
        if ($isNew) return '¡Hola! 👋 Soy el Asesor Previsional Virtual. ¿En qué puedo ayudarte?';
        $last = '';
        foreach (array_reverse($messages ?? array()) as $m) {
            if (($m['role'] ?? '') === 'user') { $last = strtolower($m['content'] ?? ''); break; }
        }
        $r = array(
            'renta vitalicia' => 'La Renta Vitalicia da pensión fija de por vida. ¿Te interesa evaluar si es tu mejor opción?',
            'retiro programado' => 'El Retiro Programado mantiene tus fondos en AFP con pensión variable. ¿Prefieres estabilidad?',
            'pgu' => 'La PGU es un beneficio estatal para mayores de 65 años. ¿Cumples los requisitos?',
            'afp' => 'La mejor AFP depende de tu perfil. ¿Quieres una evaluación personalizada?',
            'jubilar' => 'Edad legal: 65 años hombres, 60 mujeres. ¿Estás próximo a jubilarte?',
        );
        foreach ($r as $k => $v) { if (strpos($last, $k) !== false) return $v; }
        return 'Gracias por tu consulta. ¿Te gustaría agendar una asesoría gratuita con Luis Barría (Cod. 1360)?';
    }

    public function ajax_save_lead() {
        check_ajax_referer('asesor_previsional_nonce', 'nonce');
        $name = sanitize_text_field($_POST['name'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        if (empty($name) || empty($phone) || empty($email)) {
            wp_send_json_error(array('message' => 'Todos los campos son obligatorios.'));
        }
        $lead_id = AP_Leads::save(compact('name', 'phone', 'email') + array('source' => 'chat_widget'));
        if ($lead_id) {
            wp_mail(get_option('ap_notification_email', get_option('admin_email')), 'Nuevo lead', "Nombre: $name\nTeléfono: $phone\nEmail: $email");
            wp_send_json_success(array('message' => '¡Gracias! Te contactaremos pronto.'));
        }
        wp_send_json_error(array('message' => 'Error al guardar.'));
    }
}

add_action('plugins_loaded', array('Asesor_Previsional', 'get_instance'));
register_activation_hook(__FILE__, function() {
    AP_Leads::create_table();
    add_option('ap_notification_email', get_option('admin_email'));
});
