<?php

/**
 *
 * @link              https://www.wysylajtaniej.pl
 * @since             1.0.0
 * @package           wysylajtaniej
 *
 * @wordpress-plugin
 * Plugin Name:       WysylajTaniej.pl — przesyłki kurierskie
 * Plugin URI:        https://www.wysylajtaniej.pl/narzedzia/wtyczka-do-integracji-z-firma-kurierska-woocommerce
 * Description:       Wysylajtaniej.pl — Integracja przesyłek kurierskich z woocommerce. Nadaj paczkę, przesyłkę bezpośrednio z Twojego sklepu kurierem Inpost, DPD, Paczkomat lub Orlen Paczka. Tanie przesyłki i usługi kurierskie. Masowe wysyłanie przesylek z listy zamówień.
 * Version:           1.3.2
 * Author:            WysylajTaniej.pl
 * Author URI:        https://www.wysylajtaniej.pl/narzedzia/wtyczka-do-integracji-z-firma-kurierska-woocommerce
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Developer:         WysylajTaniej.pl
 * Developer URI:     https://www.wysylajtaniej.pl
 * Text Domain:       wysylajtaniej.pl
 * Domain Path:       /languages
 * WC requires at least: 3.4.7
 * WC tested up to: 8.6.1
 * Tested up to: 6.4.4
 */
$author = 'WysylajTaniej.pl';
$author_uri = 'https://www.wysylajtaniej.pl/narzedzia/wtyczka-do-integracji-z-firma-kurierska-woocommerce';

/**
 * Dodaje dodatkowy link do opisu wtyczki na liście wtyczek
 *
 * @param array  $plugin_meta Aktualna lista linków meta wtyczki.
 * @param string $plugin_file Ścieżka do pliku wtyczki.
 * @return array Zaktualizowana lista linków meta wtyczki.
 */
function add_wysylajtaniej_plugin_meta($plugin_meta, $plugin_file)
{
    // Sprawdź, czy to jest nasza wtyczka.
    if (strpos($plugin_file, 'wysylajtaniej.php') !== false) {
        // Dodaj link do opisu wtyczki.
        $plugin_meta[] = '<a href="https://www.wysylajtaniej.pl/narzedzia/wtyczka-do-integracji-z-firma-kurierska-woocommerce" target="_blank">Instrukcja</a>';
        $plugin_meta[] = '<a href="https://www.wysylajtaniej.pl/blog/faq-wtyczka-woocommerce-pytania-i-odpowiedzi/" target="_blank">Faq</a>';
    }

    return $plugin_meta;
}
add_filter('plugin_row_meta', 'add_wysylajtaniej_plugin_meta', 10, 2);


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('wysylajtaniej_VERSION', '1.3.2');
define('wysylajtaniej_PLUGIN_NAME', 'wysylajtaniej');
define('send_url', 'https://www.wysylajtaniej.pl/api/v1/order/add');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wysylajtaniej-activator.php
 */
function activate_wysylajtaniej()
{
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wysylajtaniej-activator.php';
        wysylajtaniej_Activator::activate();
    }
}

function my_custom_text()
{
    // Pobierz wartość 
    $custom_link_text = wysylajtaniej_Admin::getDUrl();
    $custom_link_text = preg_replace("/[^\p{L}\p{N}\s.]/u", "", $custom_link_text);

    $disable_dUrl = wysylajtaniej_Admin::getdisable_dUrl();

    // Jeśli pole checkbox jest zaznaczone, nie wyświetlaj tekstu		
    if ($disable_dUrl == '1') {
        // Nie wyświetlaj niczego
    } else {
        // Jeśli pole checkbox nie jest zaznaczone, wyświetl tekst
        if (!empty($custom_link_text)) {
            echo '<div class="my-custom-text">' . $custom_link_text . ' <a href="https://www.wysylajtaniej.pl/" target="_blank">WysylajTaniej.pl</a></div>';
        } else {
            echo '<div class="my-custom-text"><p>Nasze przesyłki wysyłamy przez <a href="https://www.wysylajtaniej.pl/" target="_blank">WysylajTaniej.pl</a></p></div>';
        }
    }
}
add_action('woocommerce_after_single_product_summary', 'my_custom_text', 50);

function load_wysylajtaniej_textdomain()
{
    load_plugin_textdomain('wysylajtaniej', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'load_wysylajtaniej_textdomain');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wysylajtaniej-deactivator.php
 */
function deactivate_wysylajtaniej()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-wysylajtaniej-deactivator.php';
    wysylajtaniej_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wysylajtaniej');
register_deactivation_hook(__FILE__, 'deactivate_wysylajtaniej');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wysylajtaniej.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wysylajtaniej()
{
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        $plugin = new wysylajtaniej();
        $plugin->run();
    }
}
add_action('plugins_loaded', 'run_wysylajtaniej');

function wysylajtaniej_settings_link($links)
{
    // Pobierz link względny do strony ustawień wtyczki
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        admin_url('admin.php?page=wysylajtaniej'),
        __('Settings', 'wysylajtaniej')
    );
    // Dodaj link do tablicy
    array_push($links, $settings_link);
    // Zwróć tablicę z linkami
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wysylajtaniej_settings_link');
function wysylajtaniej_help_link($links)
{
    $help_link = sprintf(
        '<a href="%s" target="_blank">%s</a>',
        'https://www.wysylajtaniej.pl/narzedzia/wtyczka-do-integracji-z-firma-kurierska-woocommerce',
        __('Pomoc techniczna', 'wysylajtaniej')
    );
    array_push($links, $help_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wysylajtaniej_help_link');



/////// generowanie masowe 
