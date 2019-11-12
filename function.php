<?php
/**
 * Created by Buffercode.
 * User: M A Vinoth Kumar
 */

/**
 * Append the Version -- Pages
 */
add_filter('fed_plugin_versions', function ($version) {
    return array_merge($version, array('payment' => __('Payment','frontend-dashboard-payment')));
});


add_action('wp_ajax_fedp_pay_payment_page', 'fedp_payment_page');

function fedp_payment_page()
{
    echo 'Under Construction';
}

/**
 * @return array
 */
function fed_pay_invoice_templates()
{
    $templates = array(
            'template_1' => array(
                    'name'            => 'Default',
                    'version'         => '1.0',
                    'image_full_url'  => plugins_url('assets/images/template_1.png', BC_FED_PAY_PLUGIN),
                    'image_thumb_url' => plugins_url('assets/images/template_1_thumb.png', BC_FED_PAY_PLUGIN),
                    'type'            => 'one_time',
            ),
    );
    $templates = apply_filters('fed_pay_invoice_templates_filter', $templates);

    return $templates;
}

/**
 * @param bool $user_id
 *
 * @return array
 */
function fed_p_get_payments($user_id = false)
{
    global $wpdb;
    $payment = $wpdb->prefix.BC_FED_PAY_PAYMENT_TABLE;
    $user    = $wpdb->prefix.'users';


    if ( ! $user_id) {
        $query = $wpdb->get_results("SELECT *
            FROM $payment AS p
            JOIN $user AS s on p.user_id = s.id
            ORDER by p.id", ARRAY_A);
    }
    if( $user_id){
        $query = $wpdb->get_results("SELECT *
            FROM $payment AS p
            JOIN $user AS s on p.user_id = s.id
            WHERE p.user_id = $user_id
            ORDER by p.id", ARRAY_A);
    }


    return $query;
}

/**
 * @param $payment_id
 *
 * @return array|null|object
 */
function fed_p_get_payment_by_id($payment_id)
{
    global $wpdb;
    $payment = $wpdb->prefix.BC_FED_PAY_PAYMENT_TABLE;
    $user    = $wpdb->prefix.'users';
    $paymentID = fed_sanitize_text_field($payment_id);

    $query = $wpdb->get_results("SELECT *
            FROM $payment AS p
            JOIN $user AS s on p.user_id = s.id
            WHERE p.payment_id = '{$paymentID}'
            ", ARRAY_A);

    return count($query) > 0 ? $query[0] : array();
}


add_filter('fed_shortcode_lists',function($shortcodes){
    return $shortcodes + array('fed_invoice');
});
