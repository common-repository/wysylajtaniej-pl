<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wysylajtaniej.pl
 * @since      1.0.0
 *
 * @package    wysylajtaniej
 * @subpackage wysylajtaniej/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks
 *
 * @package    wysylajtaniej
 * @subpackage wysylajtaniej/admin
 * @author     wysylajtaniej.pl <woocommerce@wysylajtaniej.pl>
 */
class wysylajtaniej_Admin
{

    const ERRORS_URL = "https://www.wysylajtaniej.pl/api/v1/service/errors";
    const COURIERS_URL = "https://www.wysylajtaniej.pl/api/v1/service/couriers";
    const SERVICES_URL = "https://www.wysylajtaniej.pl/api/v1/service/services";

    const ORDER_VALIDATE_URL = "https://www.wysylajtaniej.pl/api/v1/order/validate";
    const ORDER_ADD_URL = "https://www.wysylajtaniej.pl/api/v1/order/add";

    const PARCEL_SEARCH_URL = "https://www.wysylajtaniej.pl/api/v1/parcel/search";

    const SHAPES = [
        "BOX",
        "ROUND",
        "LETTER",
        "WHEELS",
    ];

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Error array.
     *
     * @since    1.0.0
     * @access   public
     * @var      array $errors Array of error messages.
     */
    public $errors;
    /**
     * Messages array.
     *
     * @since    1.0.0
     * @access   public
     * @var      array $messages Array of messages.
     */
    public $messages;

    /**
     * Courier array.
     *
     * @since    1.0.0
     * @access   public
     * @var      array $couriers Array of couriers.
     */
    public static $couriers = [];

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('some_hook', array($this, 'wysylajtaniej_register_bulk_action'));
        //$screen = 'edit-shop_order';

        add_filter('handle_bulk_actions-edit-shop_order', array($this, 'wysylajtaniej_handle_bulk_action'), 10, 3);
        add_filter('bulk_actions-edit-shop_order', array($this, 'wysylajtaniej_register_bulk_action'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wysylajtaniej-admin.css', array(), $this->version, 'all');
    }

    /**
     * Add relevant links to plugins page.
     *
     * @@since    1.0.0
     *
     * @param array $links Plugin action links
     *
     * @return array Plugin action links
     */

    public function plugin_action_links($links)
    {

        $plugin_links = array();


        $plugin_links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=' . $this->plugin_name)) . '">' . __('Settings', 'wysylajtaniej') . '</a>';

        $plugin_links[] = '<a href="mailto:woo@wysylajtaniej.pl">' . esc_html__('Contact', 'wysylajtaniej') . '</a>';


        return array_merge($plugin_links, $links);
    }

    /**
     * Add column to order list.
     *
     * @@since    1.0.0
     *
     * @param array $columns current columns
     *
     * @return array columns
     */

    public function extra_order_column($columns)
    {

        $columns[$this->plugin_name] = __('wysylajtaniej.pl', 'wysylajtaniej');
        return $columns;
    }

    /**
     * Adds 'wysylajtaniej' column content to 'Orders' page
     *
     * @param string[] $column name of column being displayed
     */
    function extra_order_column_content($column)
    {
        global $post;

        if ($this->plugin_name === $column) {
            $this->other_package_link($post);
        }
    }

    /**
     * Add wysylajtaniej Page to woocommerce menu
     *
     * @@since    1.0.0
     */
    public function wysylajtaniej_menu()
    {
        add_submenu_page('woocommerce', __('wysylajtaniej', 'wysylajtaniej'), __('wysylajtaniej', 'wysylajtaniej'), 'manage_woocommerce', $this->plugin_name, array($this, 'wysylajtaniej_options'));
    }

    /**
     * Add wysylajtaniej meta box for order
     *
     * @@since    1.0.0
     */
    public function wysylajtaniej_meta_boxes()
    {
        add_meta_box($this->plugin_name . '_delivery', __('wysylajtaniej.pl', 'wysylajtaniej'), array($this, 'delivery_form_callback'), 'shop_order', 'normal', 'core');
    }


    /**
     * Generate option line
     *
     * @@since  1.0.0
     */
    public function generate_input_TR($id, $label, $value, $name, $type='text', $placeholder='', $description='', $return=false, $extraClasses='')

    {


        if (!$type) $type = 'text';
        $tr = sprintf(
            '
        <tr class="inputRow %8$s">
            <th scope="row">
                <label for="%1$s">%2$s</label>
            </th>
            <td>
                <input type="%3$s" value="%4$s" id="%1$s" name="%5$s" placeholder="%6$s">
                <br>
                <span class="description">%7$s</span>
            </td>
        </tr>',
            $id,
            $label,
            $type,
            $value,
            $name,
            $placeholder,
            $description,
            $extraClasses
        );
        if ($return) return $tr;

        echo $tr;
    }


    /** Generate service line
     *
     * @@since  1.0.0
     */
    public function generate_service_TR($service, $return = false, $order_array = null, $order_data = null)
    {
        $extra_table = '';


        if (isset($service['extra_fields']) && $service['extra_fields']) {


            foreach ($service['extra_fields'] as $key => $extra_field) {
                $value = '';
                $description = '';
                if (
                    isset($order_array['Order']) &&
                    isset($order_array['Order']['services']) &&
                    is_array($order_array['Order']['services'])
                ) {
                    foreach ($order_array['Order']['services'] as $order_service) {
                        if (
                            $order_service['slug'] == $service['slug'] &&
                            isset($order_service['values']) &&
                            isset($order_service['values'][$extra_field]) &&
                            $order_service['values'][$extra_field]
                        ) {
                            $value = $order_service['values'][$extra_field];
                        }
                    }
                }

                if ($extra_field == 'COD_VALUE' && !$value && isset($order_data)) {
                    $value = str_replace('.', ',', $order_data->get_total());
                }
                if ($extra_field == 'ACCOUNT_NUMBER' && !$value) {
                    $value = wysylajtaniej_Admin::getAccount();
                }
                if ($extra_field == 'UB_VALUE' && !$value) {
                    if (wysylajtaniej_Admin::getInsurance() > $order_data->get_total()) {
                        $value = wysylajtaniej_Admin::getInsurance();
                    } else {
                        $value = str_replace('.', ',', $order_data->get_total());
                    }
                }
                if ($extra_field == 'DESCRIPTION' && !$value) {
                    global $post;
                    $email_odbiorcy = self::getValueFromOrder($order_array, 'receiver', 'email', $order_data->get_billing_email());
                    $search  = array('{email}', '{id_order}');
                    $replace = array($email_odbiorcy, $post->ID);
                    $subject = wysylajtaniej_Admin::getDOpis();
                    $value = str_replace($search, $replace, $subject);
                }
                if ($extra_field == 'DATE_RANGE') {
                    $description = 'przykład:<br>' . date('Y-m-d', strtotime('+1 day')) . ' 08:00 - 12:00';
                    $value = date('Y-m-d', strtotime('+1 day')) . ' 08:00 - 12:00';
                }
                $tr =
                    [
                        'id' => $extra_field,
                        'name' => $extra_field,
                        'label' => $this->getNameOfService($extra_field),
                        'value' => $value,
                        'description' => $description
                    ];
                $extra_table .= $this->generate_input_TR($tr['id'],  $tr['label'],  $tr['value'],  'wt_service_field[' . $service['slug'] . '][' . $tr['name'] . ']',  'text',  '',  $tr['description'],  true);
            }
        }
        $checked = false;
        if (
            isset($order_array['Order']) &&
            isset($order_array['Order']['services']) &&
            is_array($order_array['Order']['services'])
        ) {
            foreach ($order_array['Order']['services'] as $order_service) {
                if (
                    $order_service['slug'] == $service['slug']
                ) {
                    $checked = true;
                }
            }
        }
        if ($service['slug'] == 'DESCRIPTION' && wysylajtaniej_Admin::getDOpis()) {
            $checked = true;
        }
        if ($service['slug'] == 'COD' && $order_data->get_payment_method() == 'cod') {
            $checked = true;
        }
        if ($service['slug'] == 'UB' && $order_data->get_payment_method() == 'cod') {
            $checked = true;
        }
        $tr = sprintf(
            '
        <tr class="inputRow extra_service %8$s">
                            <th scope="row">
                                <label for="%1$s">%2$s</label>
                            </th>

                            <td>
                                <input type="%3$s" value="%4$s" id="%1$s" name="%5$s" placeholder="%5$s" %7$s>
                                <br>
                                <span class="description">%6$s</span>
                                <table>%9$s</table>
                            </td>
                        </tr>',
            $service['slug'],
            $service['name'],
            'checkbox',
            1,
            'wt_service[' . $service['slug'] . ']',
            '',
            ($checked) ? 'checked' : '',
            implode(' ', array_keys($service['couriers'])),
            $extra_table
        );
        if ($return) return $tr;

        echo $tr;
    }


    /**
     * Delivery form
     *
     * @@since    1.0.0
     */
    public function delivery_form_callback()
    {


        $apiKey = wysylajtaniej_Admin::getApiKey();

        if (!$apiKey) {
            wysylajtaniej_Admin::printMessages([__('No API KEY for wysylajtaniej', 'wysylajtaniej')], 'error');
            return;
        }
        global $post;
        $order_array = get_post_meta($post->ID, '_wysylajtaniejObject', true);
        $order_data = new WC_Order($post->ID);
        $sender_info = self::getSender($order_array);
        $receiver_info = self::getReceiver($order_data, $order_array);


        $package_info = [
            [
                'id' => 'width',
                'name' => 'width',
                'label' => __('width', 'wysylajtaniej') . ' (cm)',
                'value' => wysylajtaniej_Admin::getPackageSize('width', $order_data, $order_array),
            ],
            [
                'id' => 'height',
                'name' => 'height',
                'label' => __('height', 'wysylajtaniej') . ' (cm)',
                'value' => wysylajtaniej_Admin::getPackageSize('height', $order_data, $order_array),
            ],
            [
                'id' => 'length',
                'name' => 'length',
                'label' => __('length', 'wysylajtaniej') . ' (cm)',
                'value' => wysylajtaniej_Admin::getPackageSize('length', $order_data, $order_array),
            ],
            [
                'id' => 'weight',
                'name' => 'weight',
                'label' => __('weight', 'wysylajtaniej') . ' (kg)',
                'value' => self::getOrderWeight($order_data, $order_array),
            ]
        ];
        if (!session_id()) {
            session_start();
        }
        if (isset($_SESSION) && is_array($_SESSION) && array_key_exists('wysylajtaniej_errors', $_SESSION)) {
            wysylajtaniej_Admin::printMessages($_SESSION['wysylajtaniej_errors'], 'error');
            unset($_SESSION['wysylajtaniej_errors']);
?>
            <script>
                jQuery(document).ready(function() {
                    // Handler for .ready() called.
                    jQuery('html, body').animate({
                        scrollTop: jQuery('#adminPackageOrder').offset().top
                    }, 'slow');
                });
            </script>

        <?php
        }
        if (isset($_SESSION) && is_array($_SESSION) && array_key_exists('wysylajtaniej_success', $_SESSION)) {
            wysylajtaniej_Admin::printMessages($_SESSION['wysylajtaniej_success'], 'info');
            unset($_SESSION['wysylajtaniej_success']);
        ?>
            <script>
                jQuery(document).ready(function() {
                    // Handler for .ready() called.
                    jQuery('html, body').animate({
                        scrollTop: jQuery('#adminPackageOrder').offset().top
                    }, 'slow');
                });
            </script>

        <?php
        }
        ?>
        <table id="adminPackageOrder">
            <tr>
                <td>
                    <h3><?php _e('Sender info', 'wysylajtaniej') ?></h3>
                    <table class="form-table">
                        <?php
                        foreach ($sender_info as $tr) {
                            $this->generate_input_TR($tr['id'],  $tr['label'],  $tr['value'],  'wt_sender[' . $tr['name'] . ']',  '');
                        }
                        ?>
                    </table>
                </td>
                <td>
                    <h3><?php _e('Receiver info', 'wysylajtaniej') ?></h3>
                    <table class="form-table">
                        <?php
                        foreach ($receiver_info as $tr) {
                            $this->generate_input_TR($tr['id'],  $tr['label'],  $tr['value'],  'wt_receiver[' . $tr['name'] . ']',  '');
                        }
                        ?>
                    </table>
                </td>
                <td>
                    <h3><?php _e('Package info', 'wysylajtaniej'); ?></h3>
                    <table class="form-table">
                        <tr class="inputRow">
                            <th scope="row">
                                <label for="courier"><?php _e('Courier', 'wysylajtaniej'); ?></label>
                            </th>

                            <td>
                                <select name="wt_courier" id="courier">
                                    <?php
                                    $this->getCouriers();
                                    $couriers = wysylajtaniej_Admin::$couriers;
                                    $service = get_post_meta($post->ID, '_wysylajtaniejService', true);

                                    if (
                                        isset($order_array['Order']) &&
                                        isset($order_array['Order']['courier']) &&
                                        $order_array['Order']['courier']
                                    ) {
                                        $service =  $order_array['Order']['courier'];
                                    }

                                    if (!$service) $service = wysylajtaniej_Admin::getDKurier();

                                    echo  sprintf(
                                        '<option value="%1$s" %2$s>%3$s</option>',
                                        "",
                                        !$service ? 'selected' : '',
                                        __('--select courier--', 'wysylajtaniej')
                                    );

                                    if ($couriers) {
                                        foreach ($couriers as $courier) {
                                            echo  sprintf(
                                                '<option value="%1$s" %2$s>%3$s</option>',
                                                $courier['slug'],
                                                $courier['slug'] == $service ? 'selected' : '',
                                                $courier['name']
                                            );
                                        }
                                    }

                                    ?>
                                </select>
                            </td>
                        </tr>
                        <?php
                        $tr =
                            [
                                'id' => 'searchPoint',
                                'name' => '',
                                'label' => __('Search Point by address', 'wysylajtaniej'),
                                'value' => '',
                            ];
                        $this->generate_input_TR($tr['id'],  $tr['label'],  $tr['value'],  '' . $tr['name'] . '',  '',  '',  '',  false,  'extra_service Paczkomat PWR DPDPL');
                        ?>
                        <tr class="inputRow extra_service Paczkomat PWR DPDPL">
                            <th></th>
                            <td>
                                <button type="button" id="searchPointButton"><?php _e('Search', 'wysylajtaniej') ?></button>
                                <div id="searchPointResults">

                                </div>
                            </td>
                        </tr>

                        <?php
                        $value = '';
                        if (
                            isset($order_array['Order']) &&
                            isset($order_array['Order']['parcel']) &&
                            isset($order_array['Order']['parcel']['senderPoint']) &&
                            $order_array['Order']['parcel']['senderPoint']
                        ) {
                            $value = $order_array['Order']['parcel']['senderPoint'];
                        }
                        if ($service == 'PWR' && !$value) {
                            $value = wysylajtaniej_Admin::getDPWR();
                        }
                        if ($service == 'Paczkomat' && !$value) {
                            $value = wysylajtaniej_Admin::getDInpost();
                        }
                        if ($service == 'DPDPL' && !$value) {
                            $value = wysylajtaniej_Admin::getDDPD();
                        }
                        $tr =
                            [
                                'id' => 'senderPoint',
                                'name' => 'senderPoint',
                                'label' => __('Sender Point', 'wysylajtaniej'),
                                'value' => $value,
                            ];
                        $this->generate_input_TR($tr['id'],  $tr['label'],  $tr['value'],  '' . $tr['name'] . '',  '',  '',  '',  false,  'extra_service Paczkomat PWR DPDPL');
                        $value = '';
                        if (
                            isset($order_array['Order']) &&
                            isset($order_array['Order']['parcel']) &&
                            isset($order_array['Order']['parcel']['receiverPoint']) &&
                            $order_array['Order']['parcel']['receiverPoint']
                        ) {
                            $value = $order_array['Order']['parcel']['receiverPoint'];
                        }
                        if (!$value) {
                            $value = get_post_meta($post->ID, '_wysylajtaniejPoint', true);
                        }
                        $tr = [
                            'id' => 'receiverPoint',
                            'name' => 'receiverPoint',
                            'label' => __('Receiver Point', 'wysylajtaniej'),
                            'value' => $value,
                        ];
                        $this->generate_input_TR($tr['id'],  $tr['label'],  $tr['value'],  '' . $tr['name'] . '',  '',  '',  '',  false,  'extra_service Paczkomat PWR DPDPL');

                        ?>
                        <?php
                        foreach ($package_info as $tr) {
                            if (
                                isset($order_array['Order']) &&
                                isset($order_array['Order']['packages']) &&
                                isset($order_array['Order']['packages'][0]) &&
                                isset($order_array['Order']['packages'][0][$tr['name']]) &&
                                $order_array['Order']['packages'][0][$tr['name']]
                            ) {
                                $tr['value'] = $order_array['Order']['packages'][0][$tr['name']];
                            } else {
                                switch ($tr['name']) {
                                    case 'width':
                                        $tr['value'] = wysylajtaniej_Admin::getPackageSize("width", $order_data, $order_array);
                                        break;
                                    case 'height':
                                        $tr['value'] = wysylajtaniej_Admin::getPackageSize("height", $order_data, $order_array);
                                        break;
                                    case 'length':
                                        $tr['value'] = wysylajtaniej_Admin::getPackageSize("length", $order_data, $order_array);
                                        break;
                                    case 'weight':
                                        $tr['value'] = wysylajtaniej_Admin::getOrderWeight($order_data, $order_array);

                                        break;
                                    default:
                                        $tr['value'] = ""; // Domyślna wartość, jeśli nie pasuje do żadnej z powyższych opcji
                                        break;
                                }
                            }
                            $this->generate_input_TR($tr['id'],  $tr['label'],  $tr['value'],  'wt_package[' . $tr['name'] . ']',  '');
                        } ?>
                        <tr class="inputRow">
                            <th scope="row">
                                <label for="shape"><?php _e('Shape', 'wysylajtaniej'); ?></label>
                            </th>

                            <td>
                                <select name="wt_package[shape][slug]" id="shape">
                                    <?php
                                    $selected_shape = '';

                                    if (
                                        isset($order_array['Order']) &&
                                        isset($order_array['Order']['packages']) &&
                                        isset($order_array['Order']['packages'][0]) &&
                                        isset($order_array['Order']['packages'][0]['shape']) &&
                                        isset($order_array['Order']['packages'][0]['shape']['slug']) &&
                                        $order_array['Order']['packages'][0]['shape']['slug']
                                    ) {
                                        $selected_shape =  $order_array['Order']['packages'][0]['shape']['slug'];
                                    }

                                    if (self::SHAPES) {
                                        foreach (self::SHAPES as $shape) {
                                            echo  sprintf(
                                                '<option value="%1$s" %2$s>%3$s</option>',
                                                $shape,
                                                $shape == $selected_shape ? 'selected' : '',
                                                $shape
                                            );
                                        }
                                    }

                                    ?>
                                </select>
                            </td>
                        </tr>
                        <?php
                        $services = wysylajtaniej_Admin::getServices();

                        if (is_array($services) || is_object($services)) {
                            foreach ($services as $oneService) {
                                $this->generate_service_TR($oneService, false, $order_array, $order_data);
                            }
                        }
                        ?>
                    </table>
                </td>
            </tr>
        </table>
        <?php
        if (isset($order_array['savedObject'])) {
        ?>
            <input id="publish" class="button-primary" type="submit" value="<?php _e('Make new order', 'wysylajtaniej') ?>" accesskey="p" tabindex="5" name="newOrder">
            <script>
                jQuery(function($) {
                    jQuery('#wysylajtaniej_delivery input:not([type="submit"]),#wysylajtaniej_delivery select').attr('disabled', true);
                });

                function showExtraServices() {
                    jQuery('.extra_service').hide();
                    jQuery('.extra_service input').attr('disabled', true);
                    var val = jQuery('#courier').val();
                    if (val) {
                        jQuery('.extra_service').each(function() {
                            if (jQuery(this).hasClass(val)) {
                                jQuery(this).show();
                            }
                        });
                    }
                }
            </script>
        <?php
        } else {
        ?>
            <div class="submit-buttons">
                <input id="publish" class="button-secondary" type="submit" value="<?php _e('Check price', 'wysylajtaniej') ?>" accesskey="p" tabindex="5" name="checkPrice">

                <input id="publish" class="button-primary" type="submit" value="<?php _e('Send', 'wysylajtaniej') ?>" accesskey="p" tabindex="5" name="sendCourier">
            </div>
            <script>
                function showExtraServices() {
                    jQuery('.extra_service').hide();
                    jQuery('.extra_service input').attr('disabled', true);
                    var val = jQuery('#courier').val();
                    if (val) {
                        jQuery('.extra_service').each(function() {
                            if (jQuery(this).hasClass(val)) {
                                jQuery(this).find('input').attr('disabled', false);
                                jQuery(this).show();
                            }
                        });
                    }
                }
            </script>
        <?php
        }
        ?>
        <script>
            jQuery(function($) {
                showExtraServices();
                jQuery(document).on('change', '#courier', function() {
                    showExtraServices();
                });
                jQuery(document).on('click', '.buttonSelect', function() {
                    var target = jQuery(this).attr('data-taget');
                    var id = jQuery(this).attr('data-id');
                    jQuery('#' + target).val(id);
                });
                jQuery(document).on('click', '#searchPointButton', function() {
                    var courier = jQuery('#courier').val();
                    var search = jQuery('#searchPoint').val();
                    var resultDiv = jQuery('#searchPointResults');
                    resultDiv.html('');

                    if (courier && search) {
                        resultDiv.addClass('loading');
                        var data = {
                            'action': 'wysylajtaniej_getPoints',
                            'courier': courier,
                            'search': search,
                        };

                        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                        jQuery.post(ajaxurl, data, function(response) {
                            resultDiv.removeClass('loading');
                            if (response.success) {
                                if (response.data.parcels) {
                                    jQuery.each(response.data.parcels, function(key, value) {
                                        var line = '<div class="parcel-line">' +
                                            '<div class="parcel-name">' + (value['name']) + '</div>' +
                                            '<div class="parcel-location">' + (value['location']) + '</div>' +
                                            '<div class="parcel-point_id">' + (value['point_id']) + '</div>' +
                                            '<div class="buttonsSelect"> ' +
                                            '<button data-id="' + (value['point_id']) + '" data-taget="receiverPoint" class="buttonSelect buttonSelect2" type="button"><?php _e('Receiver Point', 'wysylajtaniej'); ?></button>' +
                                            '<button data-id="' + (value['point_id']) + '" data-taget="senderPoint" class="buttonSelect buttonSelect1" type="button"><?php _e('Sender Point', 'wysylajtaniej'); ?></button></div>' +

                                            '</div>';
                                        resultDiv.append(line);
                                    });
                                }
                            } else {
                                if (response.data.errors) {
                                    jQuery.each(response.data.errors, function(key, value) {
                                        var line = '<div class="parcel-error updated woocommerce-error inline">' +
                                            '<div class="parcel-error-name">' + (value) + '</div>' +
                                            ' </div>';
                                        resultDiv.append(line);
                                    });
                                }
                            }

                        });
                    }
                });

            });
        </script>
    <?php

    }

    /**
     * Get points from API
     *
     * @@since    1.0.0
     */
    public function get_points()
    {

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $courier = isset($_POST['courier']) ? sanitize_text_field($_POST['courier']) : '';

        $apiKey = wysylajtaniej_Admin::getApiKey();


        $args = array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'X-AUTH-TOKEN' => $apiKey,
                'cache-control' => 'no-cache'

            ),
            'httpversion' => '1.1'
        );

        $request = wp_remote_get(self::PARCEL_SEARCH_URL . '?query=' . $search . ($courier ? '&courier=' . $courier : ''), $args);


        if (is_wp_error($request)) {
            $this->errors[] = "cURL Error #";
        } else {
            $response_array = json_decode(wp_remote_retrieve_body($request), true);
            if (isset($response_array['error']) && isset($response_array['error']['count']) && ($response_array['error']['count'])) {
                $this->ittrErrors($response_array);
            } else {
                if (!$response_array) {
                    $this->errors[] = __('No results', 'wysylajtaniej');
                } else {
                    wp_send_json_success($response_array);
                }
            }
        }
        if ($this->errors) {
            wp_send_json_error(['errors' => $this->errors]);
        }

        wp_die(); // this is required to terminate immediately and return a proper response
    }
    /**
     * Save delivery info to Order
     *
     * @@since    1.0.0
     */
    public function delivery_save_postdata($post_id)
    {
        if (array_key_exists('sendCourier', $_POST) || array_key_exists('checkPrice', $_POST)) {

            $order_array = $this->makeOrderObject();
            if (!session_id()) {
                session_start();
            }
            if ($response = $this->validateOrder($order_array)) {

                if (array_key_exists('checkPrice', $_POST)) {
                    if ($response['price']) {
                        $_SESSION['wysylajtaniej_success'] = [__('Package will cost: ', 'wysylajtaniej') . round($response['price'] / 100, 2) . ' zł'];
                    } else {
                        $_SESSION['wysylajtaniej_errors'] = [__('Error occurred', 'wysylajtaniej')];
                    }
                } else {
                    if ($responseAdd = $this->addOrder($order_array)) {
                        if (isset($responseAdd['saved']) && $responseAdd['saved']) {
                            $order_array['savedObject'] = $responseAdd;
                            $_SESSION['wysylajtaniej_success'] = [__('Package send to wysylajtaniej', 'wysylajtaniej')];
                        }
                    } else {

                        if ($this->errors) {
                            $_SESSION['wysylajtaniej_errors'] = $this->errors;
                        } else {
                            $_SESSION['wysylajtaniej_errors'] = [__('No response from wysylajtaniej. Check all fields', 'wysylajtaniej')];
                        }
                    }
                }
            } else {
                if (!session_id()) {
                    session_start();
                }
                if ($this->errors) {
                    $_SESSION['wysylajtaniej_errors'] = $this->errors;
                } else {
                    $_SESSION['wysylajtaniej_errors'] = [__('No response from wysylajtaniej. Check all fields', 'wysylajtaniej')];
                }
            }
            update_post_meta($post_id, '_wysylajtaniejObject', $order_array);
        }
        if (array_key_exists('newOrder', $_POST)) {
            $order_array = get_post_meta($post_id, '_wysylajtaniejObject', true);
            unset($order_array['savedObject']);
            if (!session_id()) {
                session_start();
            }
            $_SESSION['wysylajtaniej_success'] = [__('Package reseted', 'wysylajtaniej')];

            update_post_meta($post_id, '_wysylajtaniejObject', $order_array);
        }

        return true;
    }

    /**
     * Prepare order object for API
     *
     * @@since    1.0.0
     */
    public function makeOrderObject()
    {
        //public static function makeOrderObject() {

        $order = [
            'Order' => [
                'courier' => sanitize_text_field($_POST['wt_courier']),
            ],
        ];
        if (isset($_POST['wt_sender']) && $_POST['wt_sender']) {
            foreach ($_POST['wt_sender'] as $key => $val) {
                $order['Order']['sender'][$key] = sanitize_text_field($val);
            }
        }
        if (isset($_POST['wt_receiver']) && $_POST['wt_receiver']) {
            foreach ($_POST['wt_receiver'] as $key => $val) {
                $order['Order']['receiver'][$key] = sanitize_text_field($val);
            }
        }
        if (isset($_POST['wt_package']) && $_POST['wt_package']) {
            $order['Order']['packages'][] = $this->makePackage($_POST['wt_package']);
        }
        if (isset($_POST['wt_service']) && $_POST['wt_service']) {
            $services = [];
            foreach ($_POST['wt_service'] as $key => $val) {
                $extra_fields = [];
                if (isset($_POST['wt_service_field']) && isset($_POST['wt_service_field'][$key])) {
                    foreach ($_POST['wt_service_field'][$key] as $service_key => $service_val) {
                        if ($service_val) {
                            $extra_fields[$service_key] = $service_val;
                        }
                    }
                }
                if (is_array($extra_fields) && sizeof($extra_fields) > 0) {
                    $services[] = [
                        'slug' => $key,
                        'values' => $extra_fields
                    ];
                } else {
                    $services[] = [
                        'slug' => $key
                    ];
                }
            }
            if (is_array($services) && sizeof($services) > 0) {
                $order['Order']['services'] = $services;
            }
        }
        if (isset($_POST['senderPoint']) && $_POST['senderPoint']) {
            $order['Order']['parcel']['senderPoint'] = $_POST['senderPoint'];
        }
        if (isset($_POST['receiverPoint']) && $_POST['receiverPoint']) {
            $order['Order']['parcel']['receiverPoint'] = $_POST['receiverPoint'];
        }
        return $order;
    }
    /**
     * Prepare package object for API
     *
     * @@since    1.0.0
     */
    private function makePackage($data)
    {
        $package = [];

        foreach ($data as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $key2 => $val2) {
                    $package[$key][$key2] = sanitize_text_field($val2);
                }
            } else {
                $val = sanitize_text_field($val);
                $val = str_replace(',', '.', $val);

                // Sprawdzamy, czy wartość jest liczbą
                if (is_numeric($val)) {
                    $package[$key] = ceil(floatval($val));
                } else {
                    $package[$key] = $val;  // zachowujemy wartość bez zaokrąglenia, jeśli nie jest liczbą
                }
            }
        }
        return $package;
    }


    /**
     * Get name of service
     *
     * @@since    1.0.0
     */
    private function getNameOfService($service)
    {
        switch ($service) {
            case "COD_VALUE":
                return __('COD Value', 'wysylajtaniej');
                break;
            case "ACCOUNT_NUMBER":
                return __('Account number', 'wysylajtaniej');
                break;
            case "UB_VALUE":
                return __('Insurance amount', 'wysylajtaniej');
                break;
            case "DATE_RANGE":
                return __('Date', 'wysylajtaniej');
                break;
            case "DESCRIPTION":
                return __('Description', 'wysylajtaniej');
                break;
            default:
                return $service;
                break;
        }
    }

    /**
     * Check if order made
     *
     * @@since    1.0.0
     */
    public function other_package_link($post)
    {
        $order_array = get_post_meta($post->ID, '_wysylajtaniejObject', true);
        if (isset($order_array['savedObject'])) {
            echo $order_array['Order']['courier'];
        }
    }

    /**
     * wysylajtaniej options page
     *
     * @@since    1.0.0
     */
    public function wysylajtaniej_options()
    {
        $reset = false;

        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $action = (isset($_POST['wysylajtaniejAction']) ? sanitize_text_field($_POST['wysylajtaniejAction']) : '');

        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'wysylajtaniej';

        if ($tab == 'delivery_config') {
            $this->getCouriers();
        }


        if (isset($action) && $action == 'saveSenderData') {
            $this->saveSenderData();
            $this->messages[] = __('Data saved', 'wysylajtaniej');
        }
        if (isset($action) && $action == 'saveDeliveryConnect') {
            $this->saveDeliveryConnect();
        }
        if (isset($action) && $action == 'resetCredentials') {
            $this->resetCredentials();
            $reset = true;
        }
        if (isset($action) && $action == 'createIntegration') {
            $this->createIntegration();
        }
        if (isset($_GET['action']) && $_GET['action'] == 'accessGranted') {
            $this->messages[] = __('Access granted', 'wysylajtaniej');
        }
        //view variables
        $wysylajtaniej_form_url = esc_url(get_admin_url(null, 'admin.php?page=' . $this->plugin_name));
        $wysylajtaniej_errors = $this->errors;
        $wysylajtaniej_messages = $this->messages;
        //include admin view
        include_once('partials/wysylajtaniej-admin-display.php');
    }


    /**
     * Save sender data
     *
     * @@since    1.0.0
     */
    private function saveSenderData()
    {
        $name = sanitize_text_field($_POST['name']);
        $surname = sanitize_text_field($_POST['surname']);
        $companyName = sanitize_text_field($_POST['companyName']);
        $postCode = sanitize_text_field($_POST['postCode']);
        $city = sanitize_text_field($_POST['city']);
        $street = sanitize_text_field($_POST['street']);
        $building = sanitize_text_field($_POST['building']);
        $local = sanitize_text_field($_POST['local']);
        $email = sanitize_email($_POST['email']);
        $telephone = sanitize_text_field($_POST['telephone']);
        $account = sanitize_text_field($_POST['account']);
        $ubez = sanitize_text_field($_POST['ubez']);

        update_option($this->plugin_name . '_sender_name', $name);
        update_option($this->plugin_name . '_sender_surname', $surname);
        update_option($this->plugin_name . '_sender_companyName', $companyName);
        update_option($this->plugin_name . '_sender_postCode', $postCode);
        update_option($this->plugin_name . '_sender_city', $city);
        update_option($this->plugin_name . '_sender_street', $street);
        update_option($this->plugin_name . '_sender_building', $building);
        update_option($this->plugin_name . '_sender_local', $local);
        update_option($this->plugin_name . '_sender_email', $email);
        update_option($this->plugin_name . '_sender_telephone', $telephone);
        update_option($this->plugin_name . '_sender_account', $account);
        update_option($this->plugin_name . '_sender_ubez', $ubez);
        update_option($this->plugin_name . '_sender_dkurier', sanitize_text_field($_POST['dkurier']));
        update_option($this->plugin_name . '_sender_dOpis', sanitize_text_field($_POST['dOpis']));
        update_option($this->plugin_name . '_sender_dWidth', sanitize_text_field($_POST['dWidth']));
        update_option($this->plugin_name . '_sender_dHeight', sanitize_text_field($_POST['dHeight']));
        update_option($this->plugin_name . '_sender_dLength', sanitize_text_field($_POST['dLength']));
        update_option($this->plugin_name . '_sender_dWeight', sanitize_text_field($_POST['dWeight']));
        update_option($this->plugin_name . '_sender_dPWR', sanitize_text_field($_POST['dPWR']));
        update_option($this->plugin_name . '_sender_dInpost', sanitize_text_field($_POST['dInpost']));
        update_option($this->plugin_name . '_sender_dDPD', sanitize_text_field($_POST['dDPD']));
        update_option($this->plugin_name . '_sender_dUrl', sanitize_text_field($_POST['dUrl']));

        $update_disable_dUrl = isset($_POST['disable_dUrl']) ? sanitize_text_field($_POST['disable_dUrl']) : false;
        update_option($this->plugin_name . '_disable_dUrl', $update_disable_dUrl);
    }

    /**
     * Save delicvary connection
     *
     * @@since    1.0.0
     */
    private function saveDeliveryConnect()
    {
        $connectToDelivery = isset($_POST['connectToDelivery']) ? $_POST['connectToDelivery'] : '';

        $result_array = array();
        $fail = false;
        if ($connectToDelivery) {
            foreach ($connectToDelivery as $type => $options) {
                foreach ($options as $option) {
                    if (isset($result_array[sanitize_text_field($option)])) {
                        $fail = true;
                        break 2;
                    }
                    $result_array[$option] = sanitize_text_field($type);
                }
            }
        }
        if ($fail) {
            $this->errors[] = __('Every delivery option can have just one courier attached.', 'wysylajtaniej');
            return;
        }
        update_option($this->plugin_name . '_deliveryToType', $result_array);
    }
    /**
     * Reset credentials
     *
     * @@since    1.0.0
     */
    private function resetCredentials()
    {

        delete_option($this->plugin_name . '_api_key');

        $this->messages[] = __('Account reseted', 'wysylajtaniej');
    }

    /**
     * Create Integration OAuth Application
     *
     * @@since    1.0.0
     */
    private function createIntegration()
    {
        $apiKey = sanitize_text_field($_POST['api_key']);

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'X-AUTH-TOKEN' => $apiKey,
                'cache-control' => 'no-cache'
            ),
            'httpversion' => '1.1'
        );

        $request = wp_remote_get(self::SERVICES_URL, $args);


        if (is_wp_error($request)) {
            $this->errors[] = "cURL Error #";
        } else {
            $response_array = json_decode(wp_remote_retrieve_body($request), true);
            if (isset($response_array['error'])) {
                $this->ittrErrors($response_array);
            } else {
                update_option($this->plugin_name . '_api_key', $apiKey);
                $this->messages[] = __('Access granted', 'wysylajtaniej');
            }
        }
    }
    /**
     * Validate Order
     *
     * @@since    1.0.0
     */
    private function validateOrder($order)
    {
        $apiKey = wysylajtaniej_Admin::getApiKey();

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-AUTH-TOKEN' => $apiKey,
                'cache-control' > 'no-cache'
            ),
            'httpversion' => '1.1',
            'body' => json_encode($order)
        );

        $request = wp_remote_post(self::ORDER_VALIDATE_URL, $args);


        if (is_wp_error($request)) {
            $this->errors[] = "cURL Error #";
        } else {
            $response_array = json_decode(wp_remote_retrieve_body($request), true);
            if (isset($response_array['error']) && isset($response_array['error']['count']) && ($response_array['error']['count'])) {
                $this->ittrErrors($response_array);
                return false;
            } else {
                return $response_array;
            }
        }
    }
    /**
     * Add Order
     *
     * @@since    1.0.0
     */
    private function addOrder($order)
    {
        $apiKey = wysylajtaniej_Admin::getApiKey();

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-AUTH-TOKEN' => $apiKey,
                'cache-control' > 'no-cache'
            ),
            'httpversion' => '1.1',
            'body' => json_encode($order)
        );

        $request = wp_remote_post(self::ORDER_ADD_URL, $args);


        if (is_wp_error($request)) {
            $this->errors[] = "cURL Error #";
        } else {
            $response_array = json_decode(wp_remote_retrieve_body($request), true);
            if (isset($response_array['error']) && isset($response_array['error']['count']) && ($response_array['error']['count'])) {
                $this->ittrErrors($response_array);
                return false;
            } else {
                return $response_array;
            }
        }
    }
    /**
     * Get availible couriers
     *
     * @@since    1.0.0
     */
    private function getCouriers()
    {
        $apiKey = wysylajtaniej_Admin::getApiKey();


        $args = array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'X-AUTH-TOKEN' => $apiKey,
                'cache-control' > 'no-cache'
            ),
            'httpversion' => '1.1'
        );

        $request = wp_remote_get(self::COURIERS_URL, $args);


        if (is_wp_error($request)) {
            return false; // Bail early
        } else {
            $response_array = json_decode(wp_remote_retrieve_body($request), true);
            if (isset($response_array['error'])) {
                $this->ittrErrors($response_array);
            } else {
                wysylajtaniej_Admin::$couriers = $response_array;
            }
        }
    }

    public static function getCouriersStatic()
    {

        $apiKey = wysylajtaniej_Admin::getApiKey();

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'X-AUTH-TOKEN' => $apiKey,
                'cache-control' > 'no-cache'
            ),
            'httpversion' => '1.1'
        );

        $request = wp_remote_get(self::COURIERS_URL, $args);

        if (is_wp_error($request)) {
            wysylajtaniej_Admin::$couriers = [];
        } else {
            $response_array = json_decode(wp_remote_retrieve_body($request), true);
            if (isset($response_array['error'])) {
                wysylajtaniej_Admin::$couriers = [];
            } else {
                wysylajtaniej_Admin::$couriers = $response_array;
            }
        }
    }
    /**
     * Generate multiselect with delivery options
     *
     * @@since    1.0.0
     */
    public static function deliveryAttachTo($type, $deliveryToType)
    {
        if (!$type) return;
        $options = '';

        $zones = WC_Shipping_Zones::get_zones();

        foreach ($zones as $zoneItem) {
            $shipping_methods = $zoneItem['shipping_methods'];

            foreach ($shipping_methods as $shipping_method) {
                $instance = $shipping_method->id . ':' . $shipping_method->instance_id;
                $options .= sprintf(
                    '<input type="checkbox" name="connectToDelivery[%4$s][] " value="%1$s" %2$s />%3$s<br/>',
                    $instance,
                    self::checkSelected($type, $instance, $deliveryToType) ? 'checked' : '',
                    $zoneItem['zone_name'] . ':' . $shipping_method->title,
                    $type
                );
            }
        }
        ob_start();
    ?>
        <!--        <select multiple size="6" name="connectToDelivery[--><?php //echo esc_html($type); 
                                                                            ?><!--][]">-->
        <?php echo $options; ?>
        <!--        </select>-->
    <?php
        return ob_get_clean();
    }
    /**
     * Check if delivery is attach to delivery option
     *
     * @@since    1.0.0
     */
    public static function checkSelected($type, $instance, $deliveryToType)
    {
        if (isset($_POST['connectToDelivery'])) {
            $connectToDelivery = isset($_POST['connectToDelivery']) ? $_POST['connectToDelivery'] : '';
            if (isset($connectToDelivery[$type]) && in_array($instance, $connectToDelivery[$type])) return true;
        } else {
            if (isset($deliveryToType[$instance]) && $deliveryToType[$instance] == $type) return true;
        }
        return false;
    }
    /**
     * Itterate erorrs
     *
     *  @@since    1.0.0
     *
     * @param $err
     *
     * @throws Exception
     */
    private function ittrErrors($err)
    {


        if (isset($err['error']) && $err['error'] &&  isset($err['error']['messages']) && $err['error']['messages']) {

            if (is_array($err['error']['messages']) && isset($err['error']['messages']['message'][0])) {

                $this->errors[] = $err['error']['messages']['message'][0];
            } elseif (is_array($err['error']['messages'])) {
                foreach ($err['error']['messages'] as $msg) {
                    $this->errors[] = $msg['message'];
                }
            }
        }
    }


    /**
     * Get order value  from order key
     *
     * @@since    1.0.0
     */
    private static function getValueFromOrder($order_array, $key, $name, $default)
    {
        if (
            isset($order_array['Order']) &&
            isset($order_array['Order'][$key]) &&
            isset($order_array['Order'][$key][$name])
        ) {
            return $order_array['Order'][$key][$name];
        } else {
            return $default;
        }
    }
    /**
     * Get sender data from options
     *
     * @@since    1.0.0
     */
    public static function getSender($order_array = null)
    {
        $data = [
            [
                'id' => 'name',
                'name' => 'name',
                'label' => __('Name', 'wysylajtaniej'),
                'value' => self::getValueFromOrder($order_array, 'sender', 'name', wysylajtaniej_Admin::getName()),
            ],
            [
                'id' => 'surname',
                'name' => 'surname',
                'label' => __('Surname', 'wysylajtaniej'),
                'value' => self::getValueFromOrder($order_array, 'sender', 'surname', wysylajtaniej_Admin::getSurname()),
            ],
            [
                'id' => 'companyName',
                'name' => 'companyName',
                'label' => __('Company', 'wysylajtaniej'),
                'value' => self::getValueFromOrder($order_array, 'sender', 'companyName', wysylajtaniej_Admin::getCompanyName()),
            ],
            [
                'id' => 'street',
                'name' => 'street',
                'label' => __('Street', 'wysylajtaniej'),
                'value' => self::getValueFromOrder($order_array, 'sender', 'street', wysylajtaniej_Admin::getStreet()),
            ],
            [
                'id' => 'building',
                'name' => 'building',
                'label' => __('Building', 'wysylajtaniej'),
                'value' => self::getValueFromOrder($order_array, 'sender', 'building', wysylajtaniej_Admin::getBuilding()),
            ],
            [
                'id' => 'local',
                'name' => 'local',
                'label' => __('Local', 'wysylajtaniej'),
                'value' => self::getValueFromOrder($order_array, 'sender', 'local', wysylajtaniej_Admin::getLocal()),
            ],
            [
                'id' => 'city',
                'name' => 'city',
                'label' => __('City', 'wysylajtaniej'),
                'value' => self::getValueFromOrder($order_array, 'sender', 'city', wysylajtaniej_Admin::getCity()),
            ],
            [
                'id' => 'postCode',
                'name' => 'postCode',
                'label' => __('Post code', 'wysylajtaniej'),
                'value' => self::getValueFromOrder($order_array, 'sender', 'postCode', wysylajtaniej_Admin::getPostCode()),
            ],
            [
                'id' => 'email',
                'name' => 'email',
                'label' => __('E-mail', 'wysylajtaniej'),
                'value' => self::getValueFromOrder($order_array, 'sender', 'email', wysylajtaniej_Admin::getSenderEmail()),
            ],
            [
                'id' => 'telephone',
                'name' => 'phoneNumber',
                'label' => __('Phone', 'wysylajtaniej'),
                'value' => self::getValueFromOrder($order_array, 'sender', 'phoneNumber', wysylajtaniej_Admin::getTelephone()),
            ],
        ];
        return $data;
    }

    /**
     * Get receiver from order
     *
     * @@since    1.0.0
     */
    public static function getReceiver($order_data, $order_array = null)
    {


        if ($order_data) {

            $data = [
                [
                    'id' => 'name',
                    'name' => 'name',
                    'label' => __('Name', 'wysylajtaniej'),
                    'value' => self::getValueFromOrder($order_array, 'receiver', 'name', $order_data->get_shipping_first_name()),
                ],
                [
                    'id' => 'surname',
                    'name' => 'surname',
                    'label' => __('Surname', 'wysylajtaniej'),
                    'value' => self::getValueFromOrder($order_array, 'receiver', 'surname', $order_data->get_shipping_last_name()),
                ],
                [
                    'id' => 'companyName',
                    'name' => 'companyName',
                    'label' => __('Company', 'wysylajtaniej'),
                    'value' => self::getValueFromOrder($order_array, 'receiver', 'companyName', $order_data->get_shipping_company()),
                ],
                [
                    'id' => 'street',
                    'name' => 'street',
                    'label' => __('Street', 'wysylajtaniej'),
                    'value' => self::getValueFromOrder($order_array, 'receiver', 'street', $order_data->get_shipping_address_1()),
                ],
                [
                    'id' => 'building',
                    'name' => 'building',
                    'label' => __('Building', 'wysylajtaniej'),
                    'value' => self::getValueFromOrder($order_array, 'receiver', 'building', $order_data->get_shipping_address_2()),
                ],
                [
                    'id' => 'local',
                    'name' => 'local',
                    'label' => __('Local', 'wysylajtaniej'),
                    'value' => self::getValueFromOrder($order_array, 'receiver', 'local', ''),
                ],
                [
                    'id' => 'city',
                    'name' => 'city',
                    'label' => __('City', 'wysylajtaniej'),
                    'value' => self::getValueFromOrder($order_array, 'receiver', 'city', $order_data->get_shipping_city()),
                ],
                [
                    'id' => 'postCode',
                    'name' => 'postCode',
                    'label' => __('Post code', 'wysylajtaniej'),
                    'value' => self::getValueFromOrder($order_array, 'receiver', 'postCode', $order_data->get_shipping_postcode()),
                ],
                [
                    'id' => 'email',
                    'name' => 'email',
                    'label' => __('E-mail', 'wysylajtaniej'),
                    'value' => self::getValueFromOrder($order_array, 'receiver', 'email', $order_data->get_billing_email()),
                ],
                [
                    'id' => 'telephone',
                    'name' => 'phoneNumber',
                    'label' => __('Phone', 'wysylajtaniej'),
                    'value' => self::getValueFromOrder($order_array, 'receiver', 'phoneNumber', $order_data->get_billing_phone()),
                ],
            ];
        } else {
            $data = [
                [
                    'id' => 'name',
                    'name' => 'name',
                    'label' => __('Name', 'wysylajtaniej'),
                    'value' => '',
                ],
                [
                    'id' => 'surname',
                    'name' => 'surname',
                    'label' => __('Surname', 'wysylajtaniej'),
                    'value' => '',
                ],
                [
                    'id' => 'companyName',
                    'name' => 'companyName',
                    'label' => __('Company', 'wysylajtaniej'),
                    'value' => '',
                ],
                [
                    'id' => 'street',
                    'name' => 'street',
                    'label' => __('Street', 'wysylajtaniej'),
                    'value' => '',
                ],
                [
                    'id' => 'building',
                    'name' => 'building',
                    'label' => __('Building', 'wysylajtaniej'),
                    'value' => '',
                ],
                [
                    'id' => 'local',
                    'name' => 'local',
                    'label' => __('Local', 'wysylajtaniej'),
                    'value' => '',
                ],
                [
                    'id' => 'city',
                    'name' => 'city',
                    'label' => __('City', 'wysylajtaniej'),
                    'value' => '',
                ],
                [
                    'id' => 'postCode',
                    'name' => 'postCode',
                    'label' => __('Post code', 'wysylajtaniej'),
                    'value' => '',
                ],
                [
                    'id' => 'email',
                    'name' => 'email',
                    'label' => __('E-mail', 'wysylajtaniej'),
                    'value' => '',
                ],
                [
                    'id' => 'telephone',
                    'name' => 'telephone',
                    'label' => __('Phone', 'wysylajtaniej'),
                    'value' => '',
                ],
            ];
        }
        return $data;
    }

    /**
     * Get wysylajtaniej service name from order
     *
     * @@since    1.0.0
     */
    public static function getService($order_data)
    {
        //dhl, dpd, fedex, ups, inpost, inpostkurier, poczta, kex, ruch, xpress

        $shipping_methods = $order_data->get_shipping_methods();
        $shipping_name = '';
        foreach ($shipping_methods as $shipping_method) {
            $data = $shipping_method->get_data();
            $shipping_name = $data['method_id'] . ':' . $data['instance_id'];
        }
        $deliveryToType = get_option(wysylajtaniej_PLUGIN_NAME . '_deliveryToType');
        if (isset($deliveryToType[$shipping_name])) {
            switch ($deliveryToType[$shipping_name]) {
                case "inpost":
                    return 'inpost';
                    break;
                case "poczta":
                    return 'poczta';
                    break;
                case "kiosk":
                    return 'ruch';
                    break;
                case "uap":
                    return 'ups';
                    break;
                case "dpd":
                    return 'dpd';
                    break;
                default:
                    return '';
                    break;
            }
        }
        if (!$order_data) return;

        return '';
    }

    /**
     * @@since    1.0.0
     */
    public static function getEmail()
    {
        return isset($_POST['email']) ? sanitize_email($_POST['email']) : get_option(wysylajtaniej_PLUGIN_NAME . '_email');
    }

    /**
     * @@since    1.0.0
     */
    public static function getClientID()
    {
        return isset($_POST['clientID']) ? sanitize_text_field($_POST['clientID']) : get_option(wysylajtaniej_PLUGIN_NAME . '_client_ID');
    }

    /**
     * @@since    1.0.0
     */
    public static function getClientBalance()
    {
        $access_token = get_option(wysylajtaniej_PLUGIN_NAME . '_access_token');

        try {
            $balance = self::getBalance($access_token);
            return $balance;
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return 0;
    }

    /**
     * @@since    1.0.0
     */
    public static function getClientSecret()
    {
        return isset($_POST['clientSecret']) ? sanitize_text_field($_POST['clientSecret']) : get_option(wysylajtaniej_PLUGIN_NAME . '_client_secret');
    }

    /**
     * @@since    1.0.0
     */
    public static function getApiKey()
    {
        return isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : get_option(wysylajtaniej_PLUGIN_NAME . '_api_key');
    }

    /**
     * @@since    1.0.0
     */
    public static function getTestMode()
    {
        $action = (isset($_POST['wysylajtaniejAction']) ? sanitize_text_field($_POST['wysylajtaniejAction']) : '');
        return (isset($action) && ($action == 'createIntegration')) ? (isset($_POST['isTestMode']) ? sanitize_text_field($_POST['isTestMode']) == 1 : false) : get_option(wysylajtaniej_PLUGIN_NAME . '_test_mode');
    }

    /**
     * @@since    1.0.0
     */
    public static function getName()
    {
        return isset($_POST['name']) ? sanitize_text_field($_POST['name']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_name');
    }
    /**
     * @@since    1.0.0
     */
    public static function getSurname()
    {
        return isset($_POST['surname']) ? sanitize_text_field($_POST['surname']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_surname');
    }

    /**
     * @@since    1.0.0
     */
    public static function getCompanyName()
    {
        return isset($_POST['companyName']) ? sanitize_text_field($_POST['companyName']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_companyName');
    }

    /**
     * @@since    1.0.0
     */
    public static function getPostCode()
    {
        return isset($_POST['postCode']) ? sanitize_text_field($_POST['postCode']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_postCode');
    }

    /**
     * @@since    1.0.0
     */
    public static function getCity()
    {
        return isset($_POST['city']) ? sanitize_text_field($_POST['city']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_city');
    }

    /**
     * @@since    1.0.0
     */

    public static function getStreet()
    {
        return isset($_POST['street']) ? sanitize_text_field($_POST['street']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_street');
    }
    /**
     * @@since    1.0.0
     */

    public static function getBuilding()
    {
        return isset($_POST['building']) ? sanitize_text_field($_POST['building']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_building');
    }
    /**
     * @@since    1.0.0
     */

    public static function getLocal()
    {
        return isset($_POST['local']) ? sanitize_text_field($_POST['local']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_local');
    }

    /**
     * @@since    1.0.0
     */
    public static function getSenderEmail()
    {
        return isset($_POST['email']) ? sanitize_email($_POST['email']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_email');
    }

    /**
     * @@since    1.0.0
     */
    public static function getTelephone()
    {
        return isset($_POST['telephone']) ? sanitize_text_field($_POST['telephone']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_telephone');
    }
    /**
     * @@since    1.0.0
     */
    public static function getAccount()
    {
        return isset($_POST['account']) ? sanitize_text_field($_POST['account']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_account');
    }
    /**
     * @@since    1.0.0
     */
    public static function getInsurance()
    {
        return isset($_POST['ubez']) ? sanitize_text_field($_POST['ubez']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_ubez');
    }
    /**
     * @@since    1.0.0
     */
    public static function getDKurier()
    {
        return isset($_POST['dkurier']) ? sanitize_text_field($_POST['dkurier']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_dkurier');
    }
    /**
     * @@since    1.0.0
     */
    public static function getDOpis()
    {
        return isset($_POST['dOpis']) ? sanitize_text_field($_POST['dOpis']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_dOpis');
    }
    /**
     * @@since    1.0.0
     */
    public static function getDWidth()
    {
        return isset($_POST['dWidth']) ? sanitize_text_field($_POST['dWidth']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_dWidth');
    }
    /**
     * @@since    1.0.0
     */
    public static function getDHeight()
    {
        return isset($_POST['dHeight']) ? sanitize_text_field($_POST['dHeight']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_dHeight');
    }
    /**
     * @@since    1.0.0
     */
    public static function getDLength()
    {
        return isset($_POST['dLength']) ? sanitize_text_field($_POST['dLength']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_dLength');
    }
    /**
     * @@since    1.0.0
     */
    public static function getDWeight()
    {
        return isset($_POST['dWeight']) ? sanitize_text_field($_POST['dWeight']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_dWeight');
    }
    /**
     * @@since    1.0.0
     */
    public static function getDPWR()
    {
        return isset($_POST['dPWR']) ? sanitize_text_field($_POST['dPWR']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_dPWR');
    }
    /**
     * @@since    1.0.0
     */
    public static function getDInpost()
    {
        return isset($_POST['dInpost']) ? sanitize_text_field($_POST['dInpost']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_dInpost');
    }
    /**
     * @@since    1.0.0
     */
    public static function getDDPD()
    {
        return isset($_POST['dDPD']) ? sanitize_text_field($_POST['dDPD']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_dDPD');
    }
    /**
     * @@since    1.0.0
     */
    public static function getDUrl()
    {
        return isset($_POST['dUrl']) ? sanitize_text_field($_POST['dUrl']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_dUrl');
    }
    /**
     * @@since    1.0.0
     */
    public static function getdisable_dUrl()
    {
        return isset($_POST['disable_dUrl']) ? sanitize_text_field($_POST['disable_dUrl']) : get_option(wysylajtaniej_PLUGIN_NAME . '_sender_disable_dUrl');
    }

    /**
     * @@since    1.0.0
     */
    public static function getIban()
    {
        return isset($_POST['iban']) ? sanitize_text_field($_POST['iban']) : get_option(wysylajtaniej_PLUGIN_NAME . '_cod_iban');
    }

    /**
     * @@since    1.0.0
     */
    public static function getSourceId()
    {
        return get_option(wysylajtaniej_PLUGIN_NAME . '_source_id');
    }

    /**
     * @@since    1.0.0
     */
    public static function getAccountOwner()
    {
        return isset($_POST['accountOwner']) ? sanitize_text_field($_POST['accountOwner']) : get_option(wysylajtaniej_PLUGIN_NAME . '_cod_accountOwner');
    }

    /**
     * @@since    1.0.0
     */
    public static function getGoogleMapsApiKey()
    {
        return isset($_POST['googleMapsApiKey']) ? sanitize_text_field($_POST['googleMapsApiKey']) : get_option(wysylajtaniej_PLUGIN_NAME . '_googleMapsApiKey');
    }

    /**
     * @@since    1.0.0
     */
    public static function getRedirectUri()
    {
        return admin_url('admin.php?page=wysylajtaniej&tab=wysylajtaniej&action=oAuthComplete');
    }
    /**
     * @@since    1.0.0
     */
    public static function getOAuthState()
    {
        return wp_create_nonce('wysylajtaniej_csrf');
    }
    /**
     * @@since    1.0.0
     */
    public static function getPackageSize($size, $order_data, $order_array = null)
    {
        if (!$size) return 0;

        $maxSize = 0;

        // Jeśli istnieje $order_array z wymiarami, użyj tych wartości.
        if (
            isset($order_array['Order']) &&
            isset($order_array['Order']['packages']) &&
            isset($order_array['Order']['packages'][0]) &&
            isset($order_array['Order']['packages'][0][$size])
        ) {
            $maxSize = $order_array['Order']['packages'][0][$size];
        }

        // Sprawdzenie wymiarów produktów w zamówieniu i wybór największego wymiaru.
        if ($order_data && method_exists($order_data, 'get_items') && sizeof($order_data->get_items()) > 0) {

            foreach ($order_data->get_items() as $item) {
                if ($item['product_id'] > 0) {
                    $_product = $item->get_product();
                    if (!$_product->is_virtual()) {
                        $productSize = 0;
                        switch ($size) {
                            case "width":
                                $productSize = $_product->get_width();
                                break;
                            case "height":
                                $productSize = $_product->get_height();
                                break;
                            case "length":
                                $productSize = $_product->get_length();
                                break;
                        }
                        $maxSize = max($maxSize, $productSize);
                    }
                }
            }
        }

        // Jeśli w ustawieniach wtyczki są podane wymiary, wybierz większy wymiar.
        $defaultSize = 0;
        switch ($size) {
            case "width":
                $defaultSize = wysylajtaniej_Admin::getDwidth();
                break;
            case "height":
                $defaultSize = wysylajtaniej_Admin::getDheight();
                break;
            case "length":
                $defaultSize = wysylajtaniej_Admin::getDlength();
                break;
        }
        $maxSize = max($maxSize, $defaultSize);

        // Konwersja jednostki wymiaru.
        $dimension_unit = get_option('woocommerce_dimension_unit', 1);
        switch ($dimension_unit) {
            case "mm":
                $maxSize = $maxSize / 10;
                break;
            case "in":
                $maxSize = 2.54 * $maxSize;
                break;
            case "yd":
                $maxSize = 91.44 * $maxSize;
                break;
            case "cm":
                // nic nie robimy, jest w cm
                break;
        }
        return ceil($maxSize);
    }


    /**
     * @@since    1.0.0
     */
    public static function getOrderWeight($order_data, $order_array = null)
    {
        if (
            isset($order_array['Order']) &&
            isset($order_array['Order']['packages']) &&
            isset($order_array['Order']['packages'][0]) &&
            isset($order_array['Order']['packages'][0]['weight'])
        ) {
            return $order_array['Order']['packages'][0]['weight'];

        }

        if (sizeof($order_data->get_items()) > 0) {
            $total_weight = 0; // Utworzenie tymczasowej zmiennej do przechowywania całkowitej wagi
            $weight = 0;
            //$weight = wysylajtaniej_Admin::getDweight();


            foreach ($order_data->get_items() as $item) {
                if ($item['product_id'] > 0) {
                    $_product = $item->get_product();

                    if (!$_product->is_virtual()) {
                        $item_weight = floatval($_product->get_weight());
                        $item_qty = intval($item['qty']);

                        if ($item_weight > 0) {
                            $total_weight += $item_weight * $item_qty; // Dodawanie wagi produktu do całkowitej wagi
                        } else {
                            $default_weight = wysylajtaniej_Admin::getDweight();
                            $default_weight = (is_null($default_weight) || $default_weight === "") ? 0 : floatval($default_weight);
                            $total_weight += $default_weight * $item_qty; // Dodawanie domyślnej wagi do całkowitej wagi
                        }
                    }
                }
            }

            // Po zakończeniu pętli zaokrąglamy w górę całkowitą wagę i dodajemy do zmiennej $weight
            $weight = floatval($weight) + ceil($total_weight);
        }

        $weight = floatval($weight); // Ensure $weight is a float value

        if (!is_numeric($weight)) {
            error_log("Error in getOrderWeight: Weight is not a valid number.");
            return 10;
        }

        $dimension_unit = get_option('woocommerce_weight_unit', 'kg');

        switch ($dimension_unit) {
            case "g":
                $weight = $weight / 1000;
                break;
            case "lbs":
                $weight = $weight * 0.45359237;
                break;
            case "oz":
                $weight = $weight * 0.0283495231;
                break;
            case "kg":
                // No conversion needed as weight is already in kg
                break;
            default:
                error_log("Error in getOrderWeight: Unknown weight unit '$dimension_unit'. Using default 'kg' unit.");
                // Optional: set a default conversion if desired
                break;
        }
        return ($weight);
    }


    /**
     * GET SERVICES
     *
     * @@since    1.0.0
     */
    private function getServices()
    {
        $apiKey = wysylajtaniej_Admin::getApiKey();

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'X-AUTH-TOKEN' => $apiKey,
                'cache-control' > 'no-cache'
            ),
            'httpversion' => '1.1'
        );

        $request = wp_remote_get(self::SERVICES_URL, $args);


        if (is_wp_error($request)) {
            return false; // Bail early
        } else {
            $response_array = json_decode(wp_remote_retrieve_body($request), true);
            if (isset($response_array['error'])) {
                return false;
            } else {
                return $response_array;
            }
        }
    }

    /**
     * Admin print messages
     *
     * @@since    1.0.0
     */
    public static function printMessages($messages, $type)
    {
        if (!$messages) return;

        if (!$type) $type = 'message';

        foreach ($messages as $message) {

            echo sprintf(
                '<div id="message" class="updated woocommerce-%1$s inline">
                        <p>%2$s</p>
                    </div>',
                $type,
                $message
            );
        }
    }

    /**
     * Check if account is active and enabled
     *
     * @@since    1.0.0
     */
    public static function isAccountActive()
    {
        if (!get_option(wysylajtaniej_PLUGIN_NAME . '_api_key')) return false;

        return true;
    }

    function wysylajtaniej_register_bulk_action($bulk_actions)
    {
        $bulk_actions['generate_shipments'] = __('Generuj przesyłki', 'wysylajtaniej');
        return $bulk_actions;
    }


    function wysylajtaniej_handle_bulk_action($redirect_to, $action, $post_ids)
    {

        $errors = array();
        if ($action !== 'generate_shipments') {
            return $redirect_to;
        }


        $admin_obj = new wysylajtaniej_Admin('wysylajtaniej_PLUGIN_NAME', 'wysylajtaniej_VERSION');

        //$admin_obj = new wysylajtaniej_Admin(); // Zakładam, że funkcja jest częścią klasy wysylajtaniej_Admin
        foreach ($post_ids as $post_id) {
            $error = $admin_obj->handle_bulk_action_generate_shipments($post_id);
        }


        update_option('wysylajtaniej_bulk_action_errors', $errors);


        $redirect_to = add_query_arg('generated_shipments', count($post_ids), $redirect_to);
        return $redirect_to;
    }
    function handle_bulk_action_generate_shipments($post_id)
    {

        global $error_pokaz; // Deklarujemy globalną zmienną
        $receiverPoint='';
        $orderObject='';
        $wysylajtaniejObject = '';
        $order_data = new WC_Order($post_id);

        $orderObject = $this->makeOrderObject();

        // Serializuj obiekt zamówienia
        $serializedOrder = serialize($orderObject);

        $order_data = new WC_Order($post_id);

        $wysylajtaniejObject = get_post_meta($post_id, '_wysylajtaniejObject', true);

        $api_url = send_url;
        $street = get_post_meta($post_id, '_shipping_address_1', true);
        $address_2 = get_post_meta($post_id, '_shipping_address_2', true);
        // Połącz obie części adresu, jeśli _shipping_address_2 jest dostępny
        $full_address = $street . (!empty($address_2) ? ' ' . $address_2 : '');
        $receiverPoint = get_post_meta($post_id, '_wysylajtaniejPoint', true);



        if (isset($wysylajtaniejObject['Order']['courier'])) {
            $courier = $wysylajtaniejObject['Order']['courier'];
        } else {
            $courier = get_post_meta($post_id, '_wysylajtaniejService', true);
        }

        if ($courier == 'PWR') {
            $senderPoint = wysylajtaniej_Admin::getDPWR();
        }
        if ($courier == 'Paczkomat') {
            $senderPoint = wysylajtaniej_Admin::getDInpost();
        }
        if ($courier == 'DPDPL') {
            $senderPoint = wysylajtaniej_Admin::getDDPD();
        }

        $packages = isset($wysylajtaniejObject['Order']['packages']) ? $wysylajtaniejObject['Order']['packages'] : null;

        $services = isset($wysylajtaniejObject['Order']['services']) ? $wysylajtaniejObject['Order']['services'] : null;


        if (!isset($wysylajtaniejObject['Order']['services']) || empty($wysylajtaniejObject['Order']['services'])) {
            $services = $this->fetch_service_values($order_array, $order_data);
        }
        if (!isset($wysylajtaniejObject['Order']['packages']) || empty($wysylajtaniejObject['Order']['packages'])) {
            $packages = $this->fetch_package_values($order_array, $order_data);
        }

        $order_data_api = [
            'Order' => [
                'courier' => $courier,
                'sender' => [
                    'name' => wysylajtaniej_Admin::getName(),
                    'surname' => wysylajtaniej_Admin::getSurname(),
                    'companyName' => wysylajtaniej_Admin::getCompanyName(),
                    'postCode' => wysylajtaniej_Admin::getPostCode(),
                    'city' => wysylajtaniej_Admin::getCity(),
                    'street' => wysylajtaniej_Admin::getStreet(),
                    'building' => wysylajtaniej_Admin::getBuilding(),
                    'local' => wysylajtaniej_Admin::getLocal(),
                    'email' => wysylajtaniej_Admin::getSenderEmail(),
                    'phoneNumber' => wysylajtaniej_Admin::getTelephone(),
                    'account' => wysylajtaniej_Admin::getAccount(),
                ],
                'receiver' => [
                    'name' => get_post_meta($post_id, '_shipping_first_name', true),
                    'surname' => get_post_meta($post_id, '_shipping_last_name', true),
                    'companyName' => get_post_meta($post_id, '_shipping_company', true),
                    'street' => $full_address,
                    'city' => get_post_meta($post_id, '_shipping_city', true),
                    'postCode' => get_post_meta($post_id, '_shipping_postcode', true),
                    'email' => get_post_meta($post_id, '_billing_email', true),
                    'phoneNumber' => get_post_meta($post_id, '_billing_phone', true),
                ],
                'parcel' => [
                    'senderPoint' => $senderPoint,
                    'receiverPoint' => $receiverPoint,
                ],
                'packages' => $packages,
                'services' => $services,
            ],
        ];


        $apiKey = wysylajtaniej_Admin::getApiKey();

        $headers = array(
            "Content-Type: application/json",
            "X-AUTH-TOKEN: $apiKey",
            "cache-control: no-cache"
        );

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data_api));

        $response = curl_exec($ch);
        curl_close($ch);

        $api_response = json_decode($response, true);


        //wiadomosć z api
        $error_message = $api_response['error']['messages']['message'][0];
        $imie_nazwisko_zamowienia = get_post_meta($post_id, '_shipping_first_name', true) . ' ' . get_post_meta($post_id, '_shipping_last_name', true);


        $order_data = new WC_Order($post_id);

        if (!is_array($wysylajtaniejObject)) {
            $wysylajtaniejObject = array();
        }
        $wysylajtaniejObject['_wysylajtaniejService'] = $courier;


        if (isset($api_response['error']) && $api_response['error']['count'] > 0) {

            // Sprawdzenie czy mamy tablicę błędów
            if (isset($api_response['error']['messages'][0])) {
                $errors = $api_response['error']['messages'];
            } else {
                $errors = array($api_response['error']['messages']);
            }

            $error_message = '';
            // Iteracja przez każdy błąd i dodawanie do zmiennej $error_pokaz
            foreach ($errors as $error) {
                $error_code = isset($error['code']) ? $error['code'] : '';
                $error_message = isset($error['message']) ? $error['message'] : '';

                if ($error_code == 2) {
                    $error_pokaz .= '<div class="notice notice-error is-dismissible"><p>Błąd w zamówieniu ' . $post_id . ': odbiorca : ' . $imie_nazwisko_zamowienia . ' = Brakujące dane przesyłki. Uzupełnij domyślne dane nadawcy w zakładce <a href="admin.php?page=wysylajtaniej&tab=sender_info">ustawienia wysylajtaniej</a>. 
                <br>Możesz też wygenerować przesyłkę zmianijąć dane w przesyłce <a href="post.php?post=' . $post_id . '&action=edit">Przesyłka nr ' . $post_id . '</a>.</p></div><br>';
                } else {
                    $error_message_content = is_array($error_message) ? implode(', ', $error_message) : $error_message;
                    $error_pokaz .= '<div class="notice notice-error is-dismissible"><p>Błąd w zamówieniu ' . $post_id . ': odbiorca : ' . $imie_nazwisko_zamowienia . ' =<b> ' . $error_message_content . '</b> (Kod błędu: ' . $error_code . ') Brakujące dane przesyłki. Uzupełnij domyślne dane nadawcy w zakładce <a href="admin.php?page=wysylajtaniej&tab=sender_info">ustawienia wysylajtaniej</a>. 
                <br>Możesz też wygenerować przesyłkę zmianijąć dane w przesyłce <a href="post.php?post=' . $post_id . '&action=edit">Przesyłka nr ' . $post_id . '</a>.</p></div><br>';
                }
            }

            // Aktualizacja metadanych posta
            $wysylajtaniejObject = get_post_meta($post_id, '_wysylajtaniejObject', true);
            if (is_array($wysylajtaniejObject)) {
                unset($wysylajtaniejObject['savedObject']); // Usuwa klucz 'savedObject' z tablicy
            }
            $result = update_post_meta($post_id, '_wysylajtaniejObject', $wysylajtaniejObject);
        } else {
            $error_pokaz .= '<div class="notice notice-success is-dismissible"><p>Dodano do <a href="https://www.wysylajtaniej.pl/user/orders/koszyk" target="_blank">koszyka</a> w wysylajtaniej.pl, zam: ' . $post_id . ': odbiorca: ' . $imie_nazwisko_zamowienia . '=' . $error_code . ' Wysyłanie masowe w opcji beta - sprawdź poprawnośc danych w koszyku wysylajtaniej. </p></div><br>';

            if (is_array($wysylajtaniejObject)) {
                $wysylajtaniejObject['savedObject'] = 1;
                $wysylajtaniejObject['Order']['courier'] = $courier;
                update_post_meta($post_id, '_wysylajtaniejObject', $wysylajtaniejObject);
            }
        }

        update_option('my_custom_error', $error_pokaz); // Zapisujemy wartość do opcji

    }


    function fetch_package_values($order_array, $order_data)
    {

        $packages = [];

        // Check if 'packages' data exists in the order_array
        if (isset($order_array['Order']['packages']) && is_array($order_array['Order']['packages'])) {
            $packages = $order_array['Order']['packages'];
        }

        // If 'packages' data is not available, fetch default values
        if (empty($packages)) {
            $default_packages = [
                [
                    'width' => wysylajtaniej_Admin::getPackageSize('width', $order_data),
                    'height' => wysylajtaniej_Admin::getPackageSize('height', $order_data),
                    'length' => wysylajtaniej_Admin::getPackageSize('length', $order_data),
                    'weight' => wysylajtaniej_Admin::getOrderWeight($order_data, $order_array),
                    'shape' => [
                        'slug' => 'BOX',
                    ],
                ],

            ];

            $packages = $default_packages;
        }

        return $packages;
    }




    function fetch_service_values($order_array, $order_data)
    {
        $services = [];

        $service_slugs = ['COD_VALUE', 'ACCOUNT_NUMBER', 'UB_VALUE', 'DESCRIPTION'];
		
        $cod_on = false;  // zdefiniuj $cod_on na początku
        $insurance_added = false;  // zdefiniuj, że ubezpieczenie nie zostało jeszcze dodane
		
        foreach ($service_slugs as $slug) {
            $value = '';

            // Check if the value already exists in order_array
            if (isset($order_array['Order']['services'][$slug])) {
                $value = $order_array['Order']['services'][$slug];
            }

            // Warunek dla zamówienia za pobraniem
            if ($slug == 'COD_VALUE' && !$value && $order_data->get_total() && $order_data->get_payment_method() == 'cod') {
                $usluga = 'COD';
                $value = str_replace('.', ',', $order_data->get_total());
                $usluga1 = 'ACCOUNT_NUMBER';
                $konto = wysylajtaniej_Admin::getAccount();
                $services[] = [
                    'slug' => $usluga,
                    'values' => [
                        'COD_VALUE' => $value,
                        'ACCOUNT_NUMBER' => $konto,
                    ],
                ];
                $cod_on = true;
            }

            // Warunek dla ubezpieczenia
            if (!$insurance_added && ($cod_on || wysylajtaniej_Admin::getInsurance() > 0)) {
                if (wysylajtaniej_Admin::getInsurance() > $order_data->get_total()) {
                    $value = wysylajtaniej_Admin::getInsurance();
                } else {
                    $value = str_replace('.', ',', (string)$order_data->get_total());
                }
                $usluga = 'UB';
                $slug = 'UB_VALUE';
                $services[] = [
                    'slug' => $usluga,
                    'values' => [
                        'UB_VALUE' => $value,
                    ],
                ];
                $insurance_added = true; // Oznacz, że ubezpieczenie zostało dodane
            }

            if ($slug == 'DESCRIPTION' && !$value) {
                $usluga = 'DESCRIPTION';
                $email_odbiorcy = $order_data->get_billing_email();
                $search  = ['{email}', '{id_order}'];
                $replace = [$email_odbiorcy, $order_data->get_id()];
                $subject = wysylajtaniej_Admin::getDOpis();
                $value = str_replace($search, $replace, $subject);
                $services[] =
                    [
                        'slug' => $usluga,
                        'values' => [
                            $slug => $value,
                        ],
                    ];
            }
        }
        return $services;
    }
}

function my_custom_admin_notice()
{

    $error_pokaz = get_option('my_custom_error', ''); // Pobieramy wartość z opcji
    $screen = get_current_screen();

    if ('edit-shop_order' === $screen->id && !empty($error_pokaz)) {
    ?>
        <p><?php echo $error_pokaz; ?></p>
<?php
        delete_option('my_custom_error'); // Usuwamy opcję po wyświetleniu

    }
}
add_action('admin_notices', 'my_custom_admin_notice');
