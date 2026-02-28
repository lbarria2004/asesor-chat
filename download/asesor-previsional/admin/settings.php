<?php
if (!defined('ABSPATH')) exit;

class AP_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'settings'));
    }

    public function menu() {
        add_menu_page('Asesor Previsional', 'Asesor Previsional', 'manage_options', 'asesor-previsional', array($this, 'leads_page'), 'dashicons-format-chat', 30);
        add_submenu_page('asesor-previsional', 'Leads', 'Leads', 'manage_options', 'asesor-previsional');
        add_submenu_page('asesor-previsional', 'Configuración', 'Configuración', 'manage_options', 'asesor-previsional-settings', array($this, 'settings_page'));
    }

    public function settings() {
        register_setting('ap_settings', 'ap_chat_url');
        register_setting('ap_settings', 'ap_notification_email');
        add_settings_section('ap_main', 'Configuración General', null, 'ap_settings');
        add_settings_field('ap_chat_url', 'URL del Chat API', function() {
            echo '<input type="url" name="ap_chat_url" value="' . esc_attr(get_option('ap_chat_url', '')) . '" class="regular-text" placeholder="https://tu-app.vercel.app">';
            echo '<p class="description">URL de tu app desplegada en Vercel</p>';
        }, 'ap_settings', 'ap_main');
        add_settings_field('ap_notification_email', 'Email de notificaciones', function() {
            echo '<input type="email" name="ap_notification_email" value="' . esc_attr(get_option('ap_notification_email', get_option('admin_email'))) . '" class="regular-text">';
        }, 'ap_settings', 'ap_main');
    }

    public function settings_page() {
        echo '<div class="wrap"><h1>Configuración</h1><form method="post" action="options.php">';
        settings_fields('ap_settings');
        do_settings_sections('ap_settings');
        submit_button();
        echo '</form><hr><h2>Instrucciones</h2><ol><li>Despliega el chat en Vercel</li><li>Copia la URL aquí</li><li>El widget aparecerá automáticamente</li></ol></div>';
    }

    public function leads_page() {
        if (isset($_GET['delete'])) {
            AP_Leads::delete(intval($_GET['delete']));
            echo '<div class="notice notice-success"><p>Lead eliminado</p></div>';
        }
        if (isset($_GET['export'])) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="leads-' . date('Y-m-d') . '.csv"');
            echo AP_Leads::export_csv();
            exit;
        }
        $leads = AP_Leads::get_all();
        echo '<div class="wrap"><h1>Leads <a href="?page=asesor-previsional&export=1" class="page-title-action">Exportar CSV</a></h1>';
        if ($leads) {
            echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Fecha</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Acciones</th></tr></thead><tbody>';
            foreach ($leads as $l) {
                echo "<tr><td>$l->created_at</td><td><strong>$l->name</strong></td><td><a href='mailto:$l->email'>$l->email</a></td><td><a href='tel:$l->phone'>$l->phone</a></td><td><a href='?page=asesor-previsional&delete=$l->id' class='button' onclick='return confirm(\"¿Eliminar?\")'>Eliminar</a></td></tr>";
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No hay leads todavía.</p>';
        }
        echo '</div>';
    }
}

new AP_Admin();
