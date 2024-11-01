<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wysylajtaniej.pl
 * @since      1.0.0
 *
 * @package    wysylajtaniej
 * @subpackage wysylajtaniej/admin/partials
 */

/**
 * /wp-content/plugins/wysylajtaniej/admin/class-wysylajtaniej-admin.php:168
 * available variables
 */
if (!defined('WPINC')) {
    die;
}
$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'wysylajtaniej';
$tab_api_account = 'wysylajtaniej';
$tab_sender_info = 'sender_info';
$tab_delivery_config = 'delivery_config';
$tab_delivery_connect = 'delivery_connect';

?>
<div class="wrap">

    <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="<?php echo get_admin_url('', '/admin.php?page=wysylajtaniej&tab=' . $tab_api_account) ?>" class="nav-tab <?php echo ($tab == $tab_api_account || !isset($tab)) ? "nav-tab-active" : ""; ?>"><?php _e('API account', 'wysylajtaniej'); ?></a>
        <?php if (wysylajtaniej_Admin::isAccountActive()) : ?>
            <a href="<?php echo get_admin_url('', '/admin.php?page=wysylajtaniej&tab=' . $tab_sender_info) ?>" class="nav-tab <?php echo ($tab == $tab_sender_info) ? "nav-tab-active" : ""; ?>"><?php _e('Sender info', 'wysylajtaniej'); ?></a>
            <a href="<?php echo get_admin_url('', '/admin.php?page=wysylajtaniej&tab=' . $tab_delivery_connect) ?>" class="nav-tab <?php echo ($tab == $tab_delivery_connect) ? "nav-tab-active" : ""; ?>"><?php _e('Connect delivery', 'wysylajtaniej'); ?></a>
        <?php endif; ?>
    </nav>

    <?php wysylajtaniej_Admin::printMessages($wysylajtaniej_errors, 'error'); ?>
    <?php wysylajtaniej_Admin::printMessages($wysylajtaniej_messages, 'message'); ?>

    <?php if ($tab == $tab_api_account || !isset($tab)) : ?>
        <h1 class="screen-reader-text"><?php _e('Main settings', 'wysylajtaniej'); ?></h1>
        <h2><?php _e('wysylajtaniej', 'wysylajtaniej'); ?></h2>
        <p>
            <?php _e('The wysylajtaniej.pl website enables the use of a wide range of courier services at very attractive prices without restrictions and the need to sign contracts.', 'wysylajtaniej'); ?>
            <br />
        </p>
        <?php if (wysylajtaniej_Admin::isAccountActive()) : ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="api_key"><?php _e('API key', 'wysylajtaniej'); ?></label></th>
                        <td><input name="api_key" type="text" id="api_key" value="<?php echo wysylajtaniej_Admin::getApiKey(); ?>" class="regular-text" required disabled></td>
                    </tr>
                </tbody>
            </table>
            <form method="post" action="<?php echo $wysylajtaniej_form_url . '&tab=' . $tab_api_account; ?>">
                <input type="hidden" name="wysylajtaniejAction" value="resetCredentials" />
                <p>
                    <?php _e('You can reset current account by clicking Reset button', 'wysylajtaniej'); ?>
                </p>
                <table class="form-table">

                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-secondary" value="<?php _e('Reset', 'wysylajtaniej'); ?>"></p>
                </table>
            </form>
        <?php else : ?>
            <h2><?php _e('Account', 'wysylajtaniej'); ?></h2>
            <form method="post" action="<?php echo $wysylajtaniej_form_url . '&tab=' . $tab_api_account; ?>">
                <input type="hidden" name="wysylajtaniejAction" value="createIntegration" />
                <table class="form-table">

                    <tbody>
                        <tr>
                            <th scope="row"><label for="api_key"><?php _e('API key', 'wysylajtaniej'); ?></label></th>
                            <td><input name="api_key" type="text" id="api_key" value="<?php echo isset($_POST['api_key']) ? esc_html($_POST['api_key']) : ''; ?>" class="regular-text" required></td>
                        </tr>
                    </tbody>
                </table>
                <p class="submit"><input type="submit" name="submit" class="button button-primary" value="<?php _e('Save', 'wysylajtaniej'); ?>"></p>
            </form>

        <?php endif; ?>
    <?php endif; ?>

    <?php if ($tab == $tab_sender_info && wysylajtaniej_Admin::isAccountActive()) : ?>
        <h1 class="screen-reader-text"><?php _e('Sender info', 'wysylajtaniej'); ?></h1>
        <h2><?php _e('Sender info', 'wysylajtaniej'); ?></h2>
        <form method="post" action="<?php echo $wysylajtaniej_form_url . '&tab=' . $tab_sender_info; ?>">
            <input type="hidden" name="wysylajtaniejAction" value="saveSenderData" />
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="name"><?php _e('Name', 'wysylajtaniej'); ?> *</label></th>
                        <td><input name="name" type="text" id="name" value="<?php echo wysylajtaniej_Admin::getName(); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="surname"><?php _e('Surname', 'wysylajtaniej'); ?> *</label></th>
                        <td><input name="surname" type="text" id="surname" value="<?php echo wysylajtaniej_Admin::getSurname(); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="companyName"><?php _e('Company', 'wysylajtaniej'); ?></label></th>
                        <td><input name="companyName" type="text" id="companyName" value="<?php echo wysylajtaniej_Admin::getCompanyName(); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="street"><?php _e('Street', 'wysylajtaniej'); ?> *</label></th>
                        <td><input name="street" type="text" id="street" value="<?php echo wysylajtaniej_Admin::getStreet(); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="building"><?php _e('Building', 'wysylajtaniej'); ?> *</label></th>
                        <td><input name="building" type="text" id="building" value="<?php echo wysylajtaniej_Admin::getBuilding(); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="local"><?php _e('Local', 'wysylajtaniej'); ?></label></th>
                        <td><input name="local" type="text" id="local" value="<?php echo wysylajtaniej_Admin::getLocal(); ?>" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="city"><?php _e('City', 'wysylajtaniej'); ?></label> *</th>
                        <td><input name="city" type="text" id="city" value="<?php echo wysylajtaniej_Admin::getCity(); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="postCode"><?php _e('Post code', 'wysylajtaniej'); ?> *</label></th>
                        <td><input name="postCode" type="text" id="postCode" value="<?php echo wysylajtaniej_Admin::getPostCode(); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="email"><?php _e('E-mail', 'wysylajtaniej'); ?></label> *</th>
                        <td><input name="email" type="text" id="email" value="<?php echo wysylajtaniej_Admin::getSenderEmail(); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="telephone"><?php _e('Phone', 'wysylajtaniej'); ?> *</label></th>
                        <td><input name="telephone" type="text" id="telephone" value="<?php echo wysylajtaniej_Admin::getTelephone(); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="account"><?php _e('Account', 'wysylajtaniej'); ?> *</label></th>
                        <td><input name="account" type="text" id="account" value="<?php echo wysylajtaniej_Admin::getAccount(); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ubez"><?php _e('Insurance amount', 'wysylajtaniej'); ?></label></th>
                        <td><input name="ubez" type="text" id="ubez" value="<?php echo wysylajtaniej_Admin::getInsurance(); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dkurier"><?php _e('Default courier', 'wysylajtaniej'); ?></label></th>
                        <td>
                            <select name="dkurier" id="courier">
                                <?php
                                wysylajtaniej_Admin::getCouriersStatic();
                                $couriers = wysylajtaniej_Admin::$couriers;
                                $service = wysylajtaniej_Admin::getDKurier();

                                echo  sprintf(
                                    '<option value="%1$s" %2$s>%3$s</option>',
                                    "",
                                    !$service ? 'selected' : '',
                                    __('--select courier--', 'wysylajtaniej')
                                );

                                if ($couriers) {
                                    if (is_array($couriers) || is_object($couriers)) {
                                        foreach ($couriers as $courier) {
                                            echo  sprintf(
                                                '<option value="%1$s" %2$s>%3$s</option>',
                                                $courier['slug'],
                                                $courier['slug'] == $service ? 'selected' : '',
                                                $courier['name']
                                            );
                                        }
                                    }
                                }

                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dW"><?php _e('Default description', 'wysylajtaniej'); ?></label></th>
                        <td><input name="dOpis" type="text" id="dOpis" value="<?php echo wysylajtaniej_Admin::getDOpis(); ?>" class="regular-text">
                            zmienne {id_order} {email}</td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dWidth"><?php _e('Default width', 'wysylajtaniej'); ?></label></th>
                        <td><input name="dWidth" type="text" id="dWidth" value="<?php echo wysylajtaniej_Admin::getDWidth(); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dHeight"><?php _e('Default Height', 'wysylajtaniej'); ?></label></th>
                        <td><input name="dHeight" type="text" id="dHeight" value="<?php echo wysylajtaniej_Admin::getDHeight(); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dLength"><?php _e('Default length', 'wysylajtaniej'); ?></label></th>
                        <td><input name="dLength" type="text" id="dLength" value="<?php echo wysylajtaniej_Admin::getDLength(); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dWeight"><?php _e('Default weight', 'wysylajtaniej'); ?></label></th>
                        <td><input name="dWeight" type="text" id="dWeight" value="<?php echo wysylajtaniej_Admin::getDWeight(); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dPWR"><?php _e('Paczka w ruchu Point', 'wysylajtaniej'); ?></label></th>
                        <td><input name="dPWR" type="text" id="dPWR" value="<?php echo wysylajtaniej_Admin::getDPWR(); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dInpost"><?php _e('Inpost Point', 'wysylajtaniej'); ?></label></th>
                        <td><input name="dInpost" type="text" id="dInpost" value="<?php echo wysylajtaniej_Admin::getDInpost(); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dDPD"><?php _e('DPD Point', 'wysylajtaniej'); ?></label></th>
                        <td><input name="dDPD" type="text" id="dDPD" value="<?php echo wysylajtaniej_Admin::getDDPD(); ?>" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="dUrl"><?php _e('Opis na stronie produktu', 'wysylajtaniej'); ?></label>
                        </th>
                        <td>
                            <input name="dUrl" type="text" id="dUrl" value="<?php echo wysylajtaniej_Admin::getDUrl(); ?>" class="regular-text">
                            <br>

                            <label>
                                <input name="disable_dUrl" type="checkbox" id="disable_dUrl" <?php if (wysylajtaniej_Admin::getDisable_dUrl()) echo 'checked="checked"'; ?> value="1"> <?php _e('Wyłącz wyświetlanie tekstu', 'wysylajtaniej'); ?>
                            </label>

                        </td>
                    </tr>



                </tbody>
            </table>
            <p class="submit"><input type="submit" name="submit" class="button button-primary" value="<?php _e('Save', 'wysylajtaniej'); ?>"></p>
        </form>
    <?php endif; ?>

    <?php if ($tab == $tab_delivery_connect && wysylajtaniej_Admin::isAccountActive()) : ?>
        <h1 class="screen-reader-text"><?php _e('Connect delivery option to wysylajtaniej courier', 'wysylajtaniej'); ?></h1>

        <p>
            <?php _e('Please remember! You can only connect to flat rates delivery option. Every delivery option can have just one option.', 'wysylajtaniej'); ?>

        </p>
        <form method="post" action="<?php echo $wysylajtaniej_form_url . '&tab=' . $tab_delivery_connect; ?>">

            <input type="hidden" name="wysylajtaniejAction" value="saveDeliveryConnect" />
            <?php
            $deliveryToType = get_option(wysylajtaniej_PLUGIN_NAME . '_deliveryToType');
            ?>
            <table class="form-table">
                <tbody>

                    <?php
                    wysylajtaniej_Admin::getCouriersStatic();
                    $couriers = wysylajtaniej_Admin::$couriers;
                    $service = wysylajtaniej_Admin::getDKurier();



                    if ($couriers) {
                        foreach ($couriers as $courier) {
                            echo  sprintf(
                                '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td>%3$s</td></tr>',
                                $courier['slug'],
                                $courier['name'],
                                wysylajtaniej_Admin::deliveryAttachTo($courier['slug'], $deliveryToType)
                            );
                        }
                    }

                    ?>
                    <tr>
                        <th scope="row"><label for=""></label></th>
                        <td>
                        </td>
                    </tr>
                    <tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" name="submit" class="button button-primary" value="<?php _e('Save', 'wysylajtaniej'); ?>"></p>
        </form>
    <?php endif; ?>
</div>