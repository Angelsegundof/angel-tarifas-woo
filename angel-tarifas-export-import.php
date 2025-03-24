<?php
/*
Plugin Name: Ángel Tarifas Export/Import
Description: Exporta e importa las zonas de envío de WooCommerce entre sitios como todo un crack 😎.
Author: Angel Ferrer
web: wwww.angelferrer.site
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', function() {
    add_menu_page('Ángel Tarifas', 'Ángel Tarifas', 'manage_woocommerce', 'angel-tarifas', 'angel_render_admin_page');
});

function angel_render_admin_page() {
    ?>
    <div class="wrap">
        <h1>Exportar / Importar Tarifas de WooCommerce</h1>
        <form method="post">
            <button name="angel_export" class="button button-primary">Exportar Tarifas</button>
        </form>

        <form method="post" enctype="multipart/form-data" style="margin-top: 20px;">
            <input type="file" name="angel_import_file" accept=".json" required />
            <button name="angel_import" class="button button-secondary">Importar Tarifas</button>
        </form>
    </div>
    <?php
}

add_action('admin_init', function() {
    if (isset($_POST['angel_export'])) {
        angel_export_tarifas();
    }

    if (isset($_POST['angel_import'])) {
        if (!empty($_FILES['angel_import_file']['tmp_name'])) {
            $data = file_get_contents($_FILES['angel_import_file']['tmp_name']);
            $json = json_decode($data, true);
            angel_import_tarifas($json);
        }
    }
});

function angel_export_tarifas() {
    global $wpdb;

    $tables = [
        'woocommerce_shipping_zones',
        'woocommerce_shipping_zone_locations',
        'woocommerce_shipping_zone_methods',
        'woocommerce_shipping_zone_methodmeta',
    ];

    $export_data = [];

    foreach ($tables as $table) {
        $export_data[$table] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$table}", ARRAY_A);
    }

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="angel-tarifas-export-' . date('Ymd-His') . '.json"');
    echo json_encode($export_data);
    exit;
}

function angel_import_tarifas($data) {
    global $wpdb;

    $tables = [
        'woocommerce_shipping_zones',
        'woocommerce_shipping_zone_locations',
        'woocommerce_shipping_zone_methods',
        'woocommerce_shipping_zone_methodmeta',
    ];

    foreach ($tables as $table) {
        $wpdb->query("DELETE FROM {$wpdb->prefix}{$table}");
        foreach ($data[$table] as $row) {
            $wpdb->insert("{$wpdb->prefix}{$table}", $row);
        }
    }

    echo '<div class="notice notice-success"><p>¡Importación completada con éxito, Ángel! Tus tarifas ya están listas para despegar 🚀💼</p></div>';
}
