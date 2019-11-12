<?php
/**
 * Created by Buffercode.
 * User: M A Vinoth Kumar
 */

fed_verify_nonce($_REQUEST);

require_once BC_FED_PAY_PLUGIN_DIR . '/admin/FED_Pay_Recurring.php';

$current_user = get_current_user_id();

$temp          = 'FED_PayPal_Admin\FED_Pay_Recurring';
$template      = new $temp();
echo $template->payment_page($current_user);

