<?php
if (!defined('ABSPATH')) exit;

class AP_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'settings'));
    }

    public function menu() {
        add_menu_page(
            'Asesor Previsional', 
            'Asesor Previsional', 
            'manage_options', 
            'asesor-previsional', 
            array($this, 'leads_page'), 
            'dashicons-format-chat', 
            30
        );
        add_submenu_page('asesor-previsional', 'Leads', 'Leads', 'manage_options', 'asesor-previsional');
        add_submenu_page('asesor-previsional', 'Configuración', 'Configuración', 'manage_options', 'asesor-previsional-settings', array($this, 'settings_page'));
    }

    public function settings() {
        register_setting('ap_settings', 'ap_chat_url');
        register_setting('ap_settings', 'ap_notification_email');
        register_setting('ap_settings', 'ap_whatsapp');
        register_setting('ap_settings', 'ap_advisor_name');
        register_setting('ap_settings', 'ap_advisor_code');

        add_settings_section('ap_main', 'Configuración General', null, 'ap_settings');

        add_settings_field('ap_chat_url', 'URL del Chat API', function() {
            $value = get_option('ap_chat_url', 'https://asesor-chat.vercel.app');
            echo '<input type="url" name="ap_chat_url" value="' . esc_attr($value) . '" class="regular-text" placeholder="https://tu-app.vercel.app">';
            echo '<p class="description">URL de tu chat desplegado en Vercel (ya configurado con Gemini AI)</p>';
        }, 'ap_settings', 'ap_main');

        add_settings_field('ap_advisor_name', 'Nombre del Asesor', function() {
            $value = get_option('ap_advisor_name', 'Luis Barría Chodil');
            echo '<input type="text" name="ap_advisor_name" value="' . esc_attr($value) . '" class="regular-text">';
        }, 'ap_settings', 'ap_main');

        add_settings_field('ap_advisor_code', 'Código del Asesor', function() {
            $value = get_option('ap_advisor_code', 'Cod. 1360 SPensiones');
            echo '<input type="text" name="ap_advisor_code" value="' . esc_attr($value) . '" class="regular-text">';
        }, 'ap_settings', 'ap_main');

        add_settings_field('ap_whatsapp', 'WhatsApp', function() {
            $value = get_option('ap_whatsapp', '+56934460825');
            echo '<input type="text" name="ap_whatsapp" value="' . esc_attr($value) . '" class="regular-text" placeholder="+56912345678">';
        }, 'ap_settings', 'ap_main');

        add_settings_field('ap_notification_email', 'Email de notificaciones', function() {
            $value = get_option('ap_notification_email', get_option('admin_email'));
            echo '<input type="email" name="ap_notification_email" value="' . esc_attr($value) . '" class="regular-text">';
            echo '<p class="description">Recibirás emails cuando alguien solicite asesoría</p>';
        }, 'ap_settings', 'ap_main');
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>⚙️ Configuración del Asesor Previsional</h1>
            
            <div style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="margin-top:0;">Estado del Sistema</h2>
                <p>
                    <span style="color:#10b981;font-weight:bold;">✓ Chat API:</span> 
                    <?php echo esc_html(get_option('ap_chat_url', 'https://asesor-chat.vercel.app')); ?>
                </p>
                <p>
                    <span style="color:#10b981;font-weight:bold;">✓ IA:</span> 
                    Google Gemini 2.5 Flash
                </p>
                <p>
                    <span style="color:#10b981;font-weight:bold;">✓ Leads capturados:</span> 
                    <?php echo AP_Leads::get_count(); ?>
                </p>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('ap_settings');
                do_settings_sections('ap_settings');
                submit_button();
                ?>
            </form>

            <hr>

            <h2>📖 Instrucciones de Uso</h2>
            <div style="background:#f8fafc;padding:20px;border-radius:8px;">
                <ol style="margin:0;padding-left:20px;">
                    <li><strong>El plugin ya está configurado</strong> - El chat funciona con Google Gemini AI</li>
                    <li><strong>Widget automático</strong> - El botón flotante aparece en todas las páginas</li>
                    <li><strong>Captura de leads</strong> - Los datos se guardan y te llegan por email</li>
                    <li><strong>Personaliza</strong> - Cambia el nombre y código del asesor arriba</li>
                </ol>
            </div>

            <h2>🔗 Shortcodes Disponibles</h2>
            <p>Actualmente el widget se muestra automáticamente. Si quieres usarlo manualmente:</p>
            <code style="background:#f1f5f9;padding:8px;display:inline-block;border-radius:4px;">
                [asesor_previsional]
            </code>
        </div>
        <?php
    }

    public function leads_page() {
        // Eliminar lead
        if (isset($_GET['delete']) && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'ap_delete_lead')) {
            AP_Leads::delete(intval($_GET['delete']));
            echo '<div class="notice notice-success"><p>Lead eliminado correctamente.</p></div>';
        }

        // Exportar CSV
        if (isset($_GET['export'])) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="asesor-previsional-leads-' . date('Y-m-d') . '.csv"');
            echo AP_Leads::export_csv();
            exit;
        }

        $leads = AP_Leads::get_all(50);
        $total = AP_Leads::get_count();

        ?>
        <div class="wrap">
            <h1>
                👥 Leads 
                <a href="?page=asesor-previsional&export=1" class="page-title-action">📥 Exportar CSV</a>
            </h1>

            <?php if ($total > 0): ?>
                <p>Total de leads: <strong><?php echo $total; ?></strong></p>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width:140px;">Fecha</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th style="width:100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td><?php echo esc_html($lead->created_at); ?></td>
                            <td><strong><?php echo esc_html($lead->name); ?></strong></td>
                            <td>
                                <a href="mailto:<?php echo esc_attr($lead->email); ?>">
                                    <?php echo esc_html($lead->email); ?>
                                </a>
                            </td>
                            <td>
                                <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $lead->phone)); ?>" target="_blank">
                                    <?php echo esc_html($lead->phone); ?> 📱
                                </a>
                            </td>
                            <td>
                                <?php 
                                $delete_url = wp_nonce_url(
                                    '?page=asesor-previsional&delete=' . $lead->id, 
                                    'ap_delete_lead'
                                );
                                ?>
                                <a href="<?php echo $delete_url; ?>" 
                                   class="button button-small" 
                                   onclick="return confirm('¿Eliminar este lead?')"
                                   style="color:#b91c1c;">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="background:#fff;padding:40px;text-align:center;border-radius:8px;margin-top:20px;">
                    <p style="font-size:16px;color:#64748b;">
                        📭 Aún no hay leads capturados.<br>
                        <span style="font-size:14px;">Los leads aparecerán aquí cuando los usuarios soliciten asesoría a través del chat.</span>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

new AP_Admin();
