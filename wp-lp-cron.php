<?php
/**
 * Plugin Name: WP Landing Page Cronômetro
 * Description: Exibe um cronômetro em contagem regressiva no topo de páginas específicas do WordPress e Produtos do WooCommerce.
 * Version: 0.1
 * Author: Marco Floriano
 */

if (!defined('ABSPATH')) exit;

// Enfileira os scripts e estilos
function wplp_enqueue_assets() {
    wp_enqueue_style('wplp-style', plugin_dir_url(__FILE__) . 'public/style.css');
    wp_enqueue_script('wplp-script', plugin_dir_url(__FILE__) . 'public/countdown.js', [], false, true);
}
add_action('wp_enqueue_scripts', 'wplp_enqueue_assets');

// Insere o banner no topo das páginas
function wplp_injetar_banner() {
    $options = get_option('wplp_cron_settings');
    if (!$options) return;

    $pagina_atual = get_the_ID();
    $ativadas = $options['ids'] ?? [];
    $end_time_str = $options['datetime'] ?? '';

    if (!$end_time_str || !in_array($pagina_atual, $ativadas)) return;

    $now = current_time('timestamp'); // horário do WordPress
    $end_timestamp = strtotime($end_time_str);

    if ($end_timestamp < $now) {
        // Expirou: limpar páginas selecionadas para que não exiba mais
        $options['ids'] = [];
        update_option('wplp_cron_settings', $options);
        return;
    }

    $titulo = esc_html($options['title'] ?? '');
    $descricao = esc_html($options['description'] ?? '');

    echo '<div id="wplp-banner" data-endtime="' . esc_attr($end_time_str) . '">
        <strong>' . $titulo . '</strong> ' . $descricao . '
        <span id="wplp-countdown"></span>
    </div>';
}
if (!has_action('wp_body_open', 'wplp_injetar_banner')) {
    add_action('wp_body_open', 'wplp_injetar_banner');
}

// Cria o submenu do plugin
function wplp_cron_add_admin_menu() {
    add_menu_page(
        'WP Lp Cron',
        'WP Lp Cron',
        'manage_options',
        'wp-lp-cron',
        'wplp_cron_admin_page',
        'dashicons-clock',
        25
    );
}
add_action('admin_menu', 'wplp_cron_add_admin_menu');

// Página de configuração
function wplp_cron_admin_page() {
    $options = get_option('wplp_cron_settings');
    $title = $options['title'] ?? '';
    $description = $options['description'] ?? '';
    $datetime = $options['datetime'] ?? '';
    $selected_ids = $options['ids'] ?? [];

    $pages = get_pages(['sort_column' => 'post_title']);
    $products = get_posts(['post_type' => 'product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
    ?>
    <div class="wrap">
        <h1>Configuração do Cronômetro (WP Lp Cron)</h1>
        <form method="post" action="options.php">
            <?php settings_fields('wplp_cron_group'); ?>
            <?php do_settings_sections('wplp_cron'); ?>

            <table class="form-table">
                <tr>
                    <th><label for="wplp-title">Título*</label></th>
                    <td><input type="text" id="wplp-title" name="wplp_cron_settings[title]" value="<?php echo esc_attr($title); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="wplp-desc">Descrição</label></th>
                    <td><input type="text" id="wplp-desc" name="wplp_cron_settings[description]" value="<?php echo esc_attr($description); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="wplp-datetime">Data e Hora Final*</label></th>
                    <td><input type="datetime-local" id="wplp-datetime" name="wplp_cron_settings[datetime]" value="<?php echo esc_attr($datetime); ?>" required></td>
                </tr>
                <tr>
                    <th>Selecionar Páginas</th>
                    <td>
                        <fieldset style="max-height:200px; overflow:auto; border:1px solid #ccc; padding:10px;">
                        <?php foreach ($pages as $p): ?>
                            <label><input type="checkbox" name="wplp_cron_settings[ids][]" value="<?php echo $p->ID; ?>" <?php checked(in_array($p->ID, $selected_ids)); ?>> <?php echo esc_html($p->post_title); ?></label><br>
                        <?php endforeach; ?>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th>Selecionar Produtos (WooCommerce)</th>
                    <td>
                        <fieldset style="max-height:200px; overflow:auto; border:1px solid #ccc; padding:10px;">
                        <?php foreach ($products as $p): ?>
                            <label><input type="checkbox" name="wplp_cron_settings[ids][]" value="<?php echo $p->ID; ?>" <?php checked(in_array($p->ID, $selected_ids)); ?>> <?php echo esc_html($p->post_title); ?></label><br>
                        <?php endforeach; ?>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <?php submit_button('Salvar Configurações'); ?>
        </form>
    </div>
    <?php
}


// Registra a opção no banco
function wplp_cron_register_settings() {
    register_setting('wplp_cron_group', 'wplp_cron_settings');
}
add_action('admin_init', 'wplp_cron_register_settings');

