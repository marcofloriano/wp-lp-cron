<?php
/**
 * Plugin Name: WP Lp Cron
 * Description: Display a customizable countdown timer on specific pages and WooCommerce products, with full control via the WordPress admin panel.
 * Version: 0.2
 * Author: Marco Floriano
 * Author URI: http://setor9.com.br/
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

    if (empty($options['enable_banner'])) return; // banner desativado manualmente

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
        'manage_woocommerce', // Agora visível para Administradores e Gerentes da loja
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
                <tr>
                    <th>Exibir banner fixo no topo?</th>
                    <td>
                        <label>
                            <input type="checkbox" name="wplp_cron_settings[enable_banner]" value="1" <?php checked(!empty($options['enable_banner'])); ?>>
                            Sim, exibir o cronômetro automaticamente no topo das páginas selecionadas.
                        </label>
                    </td>
                </tr>
                <!-- ESTILOS DO TÍTULO -->
                <tr><th colspan="2"><h3>Estilo do Título</h3></th></tr>
                <tr>
                    <th>Cor</th>
                    <td><input type="color" name="wplp_cron_settings[title_style][color]" value="<?php echo esc_attr($options['title_style']['color'] ?? '#000000'); ?>"></td>
                </tr>
                <tr>
                    <th>Tamanho da fonte</th>
                    <td><input type="text" name="wplp_cron_settings[title_style][size]" value="<?php echo esc_attr($options['title_style']['size'] ?? '20px'); ?>"></td>
                </tr>
                <tr>
                    <th>Disposição</th>
                    <td>
                        <select name="wplp_cron_settings[title_style][display]">
                            <option value="block" <?php selected($options['title_style']['display'] ?? '', 'block'); ?>>Em linha separada</option>
                            <option value="inline" <?php selected($options['title_style']['display'] ?? '', 'inline'); ?>>Na mesma linha</option>
                        </select>
                    </td>
                </tr>

                <!-- ESTILOS DA DESCRIÇÃO -->
                <tr><th colspan="2"><h3>Estilo da Descrição</h3></th></tr>
                <tr>
                    <th>Cor</th>
                    <td><input type="color" name="wplp_cron_settings[desc_style][color]" value="<?php echo esc_attr($options['desc_style']['color'] ?? '#333333'); ?>"></td>
                </tr>
                <tr>
                    <th>Tamanho da fonte</th>
                    <td><input type="text" name="wplp_cron_settings[desc_style][size]" value="<?php echo esc_attr($options['desc_style']['size'] ?? '16px'); ?>"></td>
                </tr>
                <tr>
                    <th>Disposição</th>
                    <td>
                        <select name="wplp_cron_settings[desc_style][display]">
                            <option value="block" <?php selected($options['desc_style']['display'] ?? '', 'block'); ?>>Em linha separada</option>
                            <option value="inline" <?php selected($options['desc_style']['display'] ?? '', 'inline'); ?>>Na mesma linha</option>
                        </select>
                    </td>
                </tr>

                <!-- ESTILOS DO CRONÔMETRO -->
                <tr><th colspan="2"><h3>Estilo do Cronômetro</h3></th></tr>
                <tr>
                    <th>Cor</th>
                    <td><input type="color" name="wplp_cron_settings[time_style][color]" value="<?php echo esc_attr($options['time_style']['color'] ?? '#ff0000'); ?>"></td>
                </tr>
                <tr>
                    <th>Tamanho da fonte</th>
                    <td><input type="text" name="wplp_cron_settings[time_style][size]" value="<?php echo esc_attr($options['time_style']['size'] ?? '18px'); ?>"></td>
                </tr>
                <tr>
                    <th>Disposição</th>
                    <td>
                        <select name="wplp_cron_settings[time_style][display]">
                            <option value="block" <?php selected($options['time_style']['display'] ?? '', 'block'); ?>>Em linha separada</option>
                            <option value="inline" <?php selected($options['time_style']['display'] ?? '', 'inline'); ?>>Na mesma linha</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Visual do Cronômetro</th>
                    <td>
                        <select name="wplp_cron_settings[style_template]">
                            <option value="default" <?php selected($options['style_template'] ?? '', 'default'); ?>>Simples</option>
                            <option value="neon" <?php selected($options['style_template'] ?? '', 'neon'); ?>>Neon Digital</option>
                            <option value="box" <?php selected($options['style_template'] ?? '', 'box'); ?>>Caixas Promocionais</option>
                        </select>
                        <p class="description">Escolha o estilo visual do cronômetro para o shortcode.</p>
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

function wplp_style_inline($s) {
    return sprintf(
        'color:%s; font-size:%s; display:%s;',
        esc_attr($s['color'] ?? '#000'),
        esc_attr($s['size'] ?? '16px'),
        esc_attr($s['display'] ?? 'block')
    );
}

function wplp_cron_shortcode() {
    $options = get_option('wplp_cron_settings');
    if (!$options) return '';

    // Verifica se a página ou produto atual está autorizada
    $pagina_atual = get_the_ID();
    $ativadas = $options['ids'] ?? [];
    if (!in_array($pagina_atual, $ativadas)) return '';

    // Verifica validade da data/hora
    $end_time_str = $options['datetime'] ?? '';
    $now = current_time('timestamp');
    $end_timestamp = strtotime($end_time_str);

    if (!$end_time_str || $end_timestamp < $now) return '';

    // Converte para formato ISO 8601 (compatível com JS)
    $end_time_iso = date('Y-m-d\TH:i:s', $end_timestamp);

    // Dados do conteúdo
    $titulo    = esc_html($options['title'] ?? '');
    $descricao = esc_html($options['description'] ?? '');
    $template  = $options['style_template'] ?? 'default';

    // Estilos salvos no painel
    $title_style = $options['title_style'] ?? [];
    $desc_style  = $options['desc_style'] ?? [];
    $time_style  = $options['time_style'] ?? [];

    ob_start(); ?>
    <div class="wplp-shortcode template-<?php echo esc_attr($template); ?>" data-endtime="<?php echo esc_attr($end_time_iso); ?>">
        <span class="wplp-title" style="<?php echo wplp_style_inline($title_style); ?>"><?php echo $titulo; ?></span>
        <span class="wplp-description" style="<?php echo wplp_style_inline($desc_style); ?>"><?php echo $descricao; ?></span>
        <span class="wplp-countdown" style="<?php echo wplp_style_inline($time_style); ?>"></span>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('wp_lp_cron', 'wplp_cron_shortcode');



