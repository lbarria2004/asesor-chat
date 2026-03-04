<?php
if (!defined('ABSPATH')) exit;

class AP_Leads {
    public static function create_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_leads';
        $sql = "CREATE TABLE $table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(30) NOT NULL,
            source varchar(50) DEFAULT 'chat_widget',
            status varchar(20) DEFAULT 'new',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) " . $wpdb->get_charset_collate() . ";";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function save($data) {
        global $wpdb;
        return $wpdb->insert($wpdb->prefix . 'ap_leads', array(
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'source' => $data['source'] ?? 'chat_widget',
            'created_at' => current_time('mysql'),
        )) ? $wpdb->insert_id : false;
    }

    public static function get_all($per_page = 20, $page = 1) {
        global $wpdb;
        $offset = ($page - 1) * $per_page;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ap_leads ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ));
    }

    public static function get_count() {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ap_leads");
    }

    public static function delete($id) {
        global $wpdb;
        return $wpdb->delete($wpdb->prefix . 'ap_leads', array('id' => $id));
    }

    public static function export_csv() {
        global $wpdb;
        $leads = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ap_leads ORDER BY created_at DESC");
        $csv = "ID,Nombre,Email,Teléfono,Fecha\n";
        foreach ($leads as $l) {
            $csv .= "$l->id,$l->name,$l->email,$l->phone,$l->created_at\n";
        }
        return $csv;
    }
}
