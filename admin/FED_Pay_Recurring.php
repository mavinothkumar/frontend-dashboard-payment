<?php

namespace FED_PayPal_Admin;

use FED_Log;
use FED_PayPal\FED_PayPal;
use PayPal\Api\Agreement;
use PayPal\Api\Payment;


if ( ! class_exists('FED_Pay_Recurring')) {
    /**
     * Class FED_Pay_Recurring
     *
     * @package FED_PayPal_Admin
     */
    class FED_Pay_Recurring
    {
        public function __construct()
        {
            add_action('wp_ajax_fed_pay_payment_index', array($this, 'get_payments_index_html'));
            add_action('wp_ajax_fed_pay_payment_page', array($this, 'payment_page'));
            add_action('wp_ajax_fed_pay_recurring_payments', array($this, 'recurring_page'));
            add_action('wp_ajax_fed_pay_add_plan', array($this, 'add_new_plan'));
            add_action('wp_ajax_fed_save_plan', array($this, 'fed_save_plan'));
            add_action('wp_ajax_fed_pay_change_plan', array($this, 'fed_pay_change_plan'));
            add_action('wp_ajax_fed_add_new_payment_definition_html', array(
                    $this,
                    'fed_add_new_payment_definition_html',
            ));
            add_action('wp_ajax_nopriv_fed_pay_payment_page', array($this, 'ajax_error'));
        }

        /**
         *
         */
        public function ajax_error()
        {
            wp_send_json_error('error');
        }

        /**
         * @param $fed_admin_options
         */
        public function fed_pay_admin_payment_options_tab($fed_admin_options)
        {
            $tabs = $this->fed_pay_paypal_api_options($fed_admin_options);
            fed_common_layouts_admin_settings($fed_admin_options, $tabs);
        }

        /**
         * @param $fed_admin_options
         *
         * @return mixed
         */
        public function fed_pay_paypal_api_options($fed_admin_options)
        {
            $options = array(
                    'fed_pay_admin_paypal_settings'  => array(
                            'icon'      => 'fab fa-paypal',
                            'name'      => __('PayPal', 'frontend-dashboard-payment-payment'),
                            'callable'  => array('object' => $this, 'method' => 'fed_pay_admin_paypal_api_tab'),
                            'arguments' => $fed_admin_options,
                    ),
                    'fed_pay_admin_invoice_details'  => array(
                            'icon'      => 'fa fa-file-invoice',
                            'name'      => __('Invoice Details', 'frontend-dashboard-payment-payment'),
                            'callable'  => array(
                                    'object' => new FED_PP_Invoice(),
                                    'method' => 'fed_pay_admin_invoice_details_tab',
                            ),
                            'arguments' => $fed_admin_options,
                    ),
                    'fed_pay_admin_invoice_template' => array(
                            'icon'      => 'fa fa-file-invoice-dollar',
                            'name'      => __('Invoice Templates', 'frontend-dashboard-payment-payment'),
                            'callable'  => array(
                                    'object' => new FED_PP_Invoice(),
                                    'method' => 'fed_pay_admin_invoice_templates_tab',
                            ),
                            'arguments' => $fed_admin_options,
                    ),
            );

            return apply_filters('fed_customize_admin_payment_layout_options', $options, $fed_admin_options);
        }

        /**
         * @param $fed_admin_options
         */
        public function fed_pay_admin_paypal_api_tab($fed_admin_options)
        {

            $array = array(
                    'form'  => array(
                            'method' => '',
                            'class'  => 'fed_admin_menu fed_ajax',
                            'attr'   => '',
                            'action' => array('url' => '', 'action' => 'fed_admin_paypal_setting_form'),
                            'nonce'  => array('action' => '', 'name' => ''),
                            'loader' => '',
                    ),
                    'input' => array(
                            'PayPal API'                => array(
                                    'col'          => 'col-md-12',
                                    'name'         => __('PayPal API', 'frontend-dashboard-payment'),
                                    'input'        => fed_get_input_details(array(
                                            'input_meta'  => 'paypal[api][type]',
                                            'input_value' => array('Sandbox' => 'Sandbox', 'Live' => 'Live'),
                                            'user_value'  => isset($fed_admin_options['settings']['paypal']['api']['type']) ? $fed_admin_options['settings']['paypal']['api']['type'] : '',
                                            'input_type'  => 'select',
                                    )),
                                    'help_message' => fed_show_help_message(array('content' => "Please check What is <a href='https://developer.paypal.com/docs/classic/lifecycle/ug_sandbox/'>Sandbox</a> | <a href='https://developer.paypal.com/docs/classic/lifecycle/goingLive/'>Live</a>")),
                            ),
                            'PayPal Success URL'        => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('PayPal Success URL', 'frontend-dashboard-payment'),
                                    'input'        =>
                                            wp_dropdown_pages(array(
                                                    'name'  => 'paypal[api][success_url]',
                                                    'class' => 'form-control',
                                                    'echo'  => false,
                                            )),
                                    'help_message' => fed_show_help_message(array('content' => "After Payment Success it will be redirect to this page")),
                            ),
                            'PayPal Cancel URL'         => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('PayPal Cancel URL', 'frontend-dashboard-payment'),
                                    'input'        => wp_dropdown_pages(array(
                                            'name'  => 'paypal[api][cancel_url]',
                                            'class' => 'form-control',
                                            'echo'  => false,
                                    )),
                                    'help_message' => fed_show_help_message(array('content' => "After Payment Cancelled it will be redirect to this page")),
                            ),
                            'PayPal Sandbox Client ID'  => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('PayPal Sandbox Client ID', 'frontend-dashboard-payment'),
                                    'input'        => fed_get_input_details(array(
                                            'placeholder' => __('Please enter PayPal Sandbox Client ID',
                                                    'frontend-dashboard-payment'),
                                            'input_meta'  => 'paypal[api][sandbox_client_id]',
                                            'user_value'  => isset($fed_admin_options['settings']['paypal']['api']['sandbox_client_id']) ? $fed_admin_options['settings']['paypal']['api']['sandbox_client_id'] : '',
                                            'input_type'  => 'single_line',
                                    )),
                                    'help_message' => fed_show_help_message(array('content' => "Please login in to PayPal and use this <a href='https://developer.paypal.com/developer/applications/'>PayPal API</a> to create the REST API apps")),
                            ),
                            'PayPal Sandbox Secrete ID' => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('PayPal Sandbox Secrete ID', 'frontend-dashboard-payment'),
                                    'input'        => fed_get_input_details(array(
                                            'placeholder' => __('Please enter PayPal Sandbox Secrete ID',
                                                    'frontend-dashboard-payment'),
                                            'input_meta'  => 'paypal[api][sandbox_secrete_id]',
                                            'user_value'  => isset($fed_admin_options['settings']['paypal']['api']['sandbox_secrete_id']) ? $fed_admin_options['settings']['paypal']['api']['sandbox_secrete_id'] : '',
                                            'input_type'  => 'single_line',
                                    )),
                                    'help_message' => fed_show_help_message(array('content' => "Please login in to PayPal and use this <a href='https://developer.paypal.com/developer/applications/'>PayPal API</a> to create the REST API apps")),
                            ),
                            'PayPal Live Client ID'     => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('PayPal Live Client ID', 'frontend-dashboard-payment'),
                                    'input'        => fed_get_input_details(array(
                                            'placeholder' => __('Please enter PayPal Live Client ID',
                                                    'frontend-dashboard-payment'),
                                            'input_meta'  => 'paypal[api][live_client_id]',
                                            'user_value'  => isset($fed_admin_options['settings']['paypal']['api']['live_client_id']) ? $fed_admin_options['settings']['paypal']['api']['live_client_id'] : '',
                                            'input_type'  => 'single_line',
                                    )),
                                    'help_message' => fed_show_help_message(array('content' => "Please login in to PayPal and use this <a href='https://developer.paypal.com/developer/applications/'>PayPal API</a> to create the REST API apps")),
                            ),
                            'PayPal Live Secrete ID'    => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('PayPal Live Secrete ID', 'frontend-dashboard-payment'),
                                    'input'        => fed_get_input_details(array(
                                            'placeholder' => __('Please enter PayPal Live Secrete ID',
                                                    'frontend-dashboard-payment'),
                                            'input_meta'  => 'paypal[api][live_secrete_id]',
                                            'user_value'  => isset($fed_admin_options['settings']['paypal']['api']['live_secrete_id']) ? $fed_admin_options['settings']['paypal']['api']['live_secrete_id'] : '',
                                            'input_type'  => 'single_line',
                                    )),
                                    'help_message' => fed_show_help_message(array('content' => "Please login in to PayPal and use this <a href='https://developer.paypal.com/developer/applications/'>PayPal API</a> to create the REST API apps")),
                            ),
                    ),
            );

            $new_value = apply_filters('fed_pay_admin_paypal_settings_tab_extra', $array, $fed_admin_options);
            fed_common_simple_layout($new_value);
        }

        /**
         * Payment Index Page
         */
        public function fed_payment_menu()
        {
            $paypal = new FED_PayPal();

            if ($paypal->is_true()) {
                echo '<div class="bc_fed container">
'.fed_loader('hide', 'Please wait, it may take some time to load from PayPal').'
<div class="fed_ajax_replace_container bc_fed_wrapper">
'.$this->get_payment_index(false).'
            </div>
            </div>';
            } else {
                ?>
                <div class="bc_fed container">
                    <div class="row">
                        <div class="col-md-12 flex-center min_height_400px">
                            <h1 class="text-center">Please add the PayPal Setting in <br> Frontend Dashboard >> Payments
                                >> PayPal</h1>
                        </div>
                    </div>
                </div>
                <?php
            }
        }


        /**
         *
         */
        public function recurring_page()
        {
            $request = fed_sanitize_text_field($_REQUEST);
            fed_verify_nonce($request);
            $status = fed_isset_request($request, 'status', 'ACTIVE');
            $paypal = new FED_PayPal();
            if ($paypal->is_true()) {
                $plans = $paypal->list_plans($status);
                FED_Log::writeLog($plans);
                $plans_ = $plans->plans;
                usort($plans_, 'fed_array_of_object_sort_key');
            }

            $html = '';

            $html .= '<div class="row padd_top_20">
                <div class="col-md-12">
                <div class="fed_page_title">
                                    <h3 class="fed_header_font_color">
                                    Plan Details
                                    <button data-url="'.admin_url('admin-ajax.php?action=fed_pay_add_plan&fed_nonce='.wp_create_nonce('fed_nonce')).'" class="btn btn-primary fed_replace_ajax"><i class="fa fa-plus"></i> Add New Plan</button>
                                    <button data-url="'.admin_url('admin-ajax.php?action=fed_pay_payment_index&fed_nonce='.wp_create_nonce('fed_nonce')).'" class="btn btn-secondary pull-right fed_replace_ajax"><span class="fas fa-redo"></span> Back to Payment Dashboard</button>
                                    </h3>
                                </div>
                </div>
            </div>';

            $html .= '<div class="row">
<div class="col-md-12">
<button data-url="'.admin_url('admin-ajax.php?action=fed_pay_recurring_payments&status=CREATED&fed_nonce='.wp_create_nonce("fed_nonce")).'" class="btn btn-secondary m-r-10 fed_pay_get_recurring  fed_replace_ajax">Show CREATED</button>
<button data-url="'.admin_url('admin-ajax.php?action=fed_pay_recurring_payments&status=ACTIVE&fed_nonce='.wp_create_nonce("fed_nonce")).'" class="btn btn-secondary m-r-10 fed_pay_get_recurring fed_replace_ajax">Show ACTIVE</button>
<button data-url="'.admin_url('admin-ajax.php?action=fed_pay_recurring_payments&status=INACTIVE&fed_nonce='.wp_create_nonce("fed_nonce")).'" class="btn btn-secondary m-r-10 fed_pay_get_recurring fed_replace_ajax">Show INACTIVE</button>
</div>
</div>';

            ?>

            <?php
            $html .= '<div class="row padd_top_20">
                <div class="col-md-12">
                <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Billing '.$status.' Plan</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>State</th>
                                <th>Type</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>';
            if (isset($plans_) && count($plans_) > 0) {
                foreach ($plans_ as $plan) {
                    $html .= '<tr>';
                    $html .= '<td>'.fed_isset($plan->id, '').'</td>';
                    $html .= '<td>'.fed_isset($plan->name, '').'</td>';
                    $html .= '<td>'.fed_isset($plan->state, '').'</td>';
                    $html .= '<td>'.fed_isset($plan->type, '').'</td>';
                    $html .= '<td>'.date_i18n(get_option('date_format'), strtotime($plan->create_time)).'</td>';
                    $html .= '<td>'.date_i18n(get_option('date_format'), strtotime($plan->update_time)).'</td>';
                    $html .= '<td>';
                    if ($status === 'CREATED' || $status === 'INACTIVE') {
                        $html .= '<button data-url="'.admin_url('admin-ajax.php?action=fed_pay_change_plan&plan=active&fed_nonce='.wp_create_nonce('fed_nonce')).'&id='.$plan->id.'" class="btn btn-primary m-r-10 fed_pay_ajax_response">ACTIVATE</button>';
                        $html .= '<button data-url="'.admin_url('admin-ajax.php?action=fed_pay_change_plan&plan=deleted&fed_nonce='.wp_create_nonce('fed_nonce')).'&id='.$plan->id.'" class="btn btn-danger m-r-10 fed_pay_ajax_response">DELETE</button>';
                    } else {
                        $html .= '<button data-url="'.admin_url('admin-ajax.php?action=fed_pay_change_plan&plan=inactive&fed_nonce='.wp_create_nonce('fed_nonce')).'&id='.$plan->id.'" class="btn btn-primary m-r-10 fed_pay_ajax_response">INACTIVATE</button>';
                        $html .= '<button data-url="'.admin_url('admin-ajax.php?action=fed_pay_change_plan&plan=deleted&fed_nonce='.wp_create_nonce('fed_nonce')).'&id='.$plan->id.'" class="btn btn-danger m-r-10 fed_pay_ajax_response">DELETE</button>';
                    }

                    $html .= '</td>';
                    $html .= '</tr>';
                }
            }
            $html .= '</tbody>
                      </table>
                        </div>
                </div>
            </div>
                    
                        </div>
                        </div>';

            wp_send_json_success(['html' => $html]);
        }


        /**
         * @param bool $return
         *
         * @return string
         */
        public function get_payments_index_html()
        {

            $this->get_payment_index(true);
        }

        /**
         * @param bool $return
         *
         * @return string
         */
        public function get_payment_index($return = true)
        {
            $html = '';

            $html .= '<div class="row padd_top_20">
                <div class="col-md-12">
                <div class="dropdown fed_payment_dropdown open">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="paypal"
                            data-toggle="dropdown">
                        <i class="fab fa-paypal" aria-hidden="true"></i> PayPal
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="paypal">
                    <li role="separator" class="divider"></li>
                    <li class="disabled bg-primary"><a href="#">Payments</a></li>
                    <li role="separator" class="divider"></li>
                    <li role="presentation"><a role="menuitem" class="fed_pay_get_recurring fed_replace_ajax" data-url="'.admin_url('admin-ajax.php?action=fed_pay_payment_page&fed_nonce='.wp_create_nonce("fed_nonce")).'" ><i class="far fa-money-bill-alt" aria-hidden="true"></i>  Manage Payments</a></li>
                    <li role="separator" class="divider"></li>
                    <li class="disabled bg-primary"><a href="#">Recurring Plan</a></li>
                    <li role="separator" class="divider"></li>
                        <li role="presentation"><a role="menuitem"  data-url="'.admin_url('admin-ajax.php?action=fed_pay_add_plan&fed_nonce='.wp_create_nonce('fed_nonce')).'" class="fed_replace_ajax" ><i class="fa fa-plus" aria-hidden="true"></i>  Create New Recurring Plan</a></li>
                        <li role="presentation"><a role="menuitem"  class="fed_pay_get_recurring fed_replace_ajax"  data-url="'.admin_url('admin-ajax.php?action=fed_pay_recurring_payments&fed_nonce='.wp_create_nonce("fed_nonce")).'"><i class="fa fa-building" aria-hidden="true"></i>  Manage Recurring Plans</a></li>
                        <li role="separator" class="divider"></li>
                        <li class="disabled bg-primary"><a href="#">One Time Plan</a></li>
                    <li role="separator" class="divider"></li>
                        <li role="presentation"><a role="menuitem"  data-url="'.admin_url('admin-ajax.php?action=fed_pay_single_show&fed_nonce='.wp_create_nonce('fed_nonce')).'" class="fed_replace_ajax" ><i class="fa fa-plus" aria-hidden="true"></i>  Create New One Time Plan</a></li>
                        <li role="presentation"><a role="menuitem"  class="fed_pay_single_list fed_replace_ajax"  data-url="'.admin_url('admin-ajax.php?action=fed_pay_single_list&fed_nonce='.wp_create_nonce("fed_nonce")).'"><i class="fa fa-building" aria-hidden="true"></i>  Manage One Time Plans</a></li>
                    </ul>
                </div>
                </div>
            </div>';
            if ($return) {
                wp_send_json_success(['html' => $html]);
            }

            return $html;
        }

        /**
         * @param bool $user_id
         *
         * @return string
         */
        public function payment_page($user_id = false)
        {
            $request = fed_sanitize_text_field($_REQUEST);
            fed_verify_nonce($request);

            $paypal = fed_p_get_payments($user_id);

            if (count($paypal) > 0) {
                $html = '';

                $html .= '
                        <div class="row padd_top_20">
                            <div class="col-md-12">';
                if (defined('DOING_AJAX') && DOING_AJAX) {
                    $html .= '<div class="fed_page_title">
                                    <h3 class="fed_header_font_color">
                                    Payment Details
                                    <button data-url="'.admin_url('admin-ajax.php?action=fed_pay_payment_index&fed_nonce='.wp_create_nonce('fed_nonce')).'" class="btn btn-secondary pull-right fed_replace_ajax"><span class="fas fa-redo"></span> Back to Payment Dashboard</button>
                                    </h3>
                                </div>';
                }
                $html .= '<div class="fed_payment_list  padd_top_20">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Plan Name</th>
                                                <th>Payment ID</th>
                                                <th>Type</th>
                                                <th>Invoice #</th>
                                                <th>Payment Source</th>
                                                <th>Date</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody>';

                foreach ($paypal as $payment) {
                    $name = isset($payment['display_name']) && ! empty($payment['display_name']) ? $payment['display_name'] : $payment['user_nicename'];
                    $html .= '<tr>';
                    $html .= '<td>'.$name.'</td>';
                    $html .= '<td>'.$payment['plan_name'].'</td>';
                    $html .= '<td>'.$payment['payment_id'].'</td>';
                    $html .= '<td>'.$payment['plan_type'].'</td>';
                    $html .= '<td>'.$payment['invoice_number'].'</td>';
                    $html .= '<td>'.$payment['payment_source'].'</td>';
                    $html .= '<td>'.$payment['created'].'</td>';
                    $html .= '<td>';
                    $html .= '<button class="btn btn-primary fed_ajax_show_content_in_popup" data-url="'.fed_generate_url(array(
                                    'transaction_id' => $payment['payment_id'],
                                    'action'         => 'fed_p_popup_invoice',
                                    'type'           => $payment['plan_type'],
                            ), admin_url('admin-ajax.php')).'">
				<i class="fa fa-print" aria-hidden="true"></i>
				</button>';
                    $html .= '</td>';
                    $html .= '</tr>';
                }

                $html .= '</tbody>
                        </table>
                        </div>
                        </div>
                        </div>
                        </div>';

                $html .= '<div class="modal fade" id="fed_p_popup">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;
                                </button>
                                <h4 class="modal-title">Invoice</h4>
                            </div>
                            <div class="modal-body">

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onClick="window.print();return false">Print</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>';
                if (defined('DOING_AJAX') && DOING_AJAX) {
                    wp_send_json_success(['html' => $html]);
                }

                return $html;
            }
            if (defined('DOING_AJAX') && DOING_AJAX) {
                $html = '<div class="fed_page_title">
                                    <h3 class="fed_header_font_color">
                                    Payment Details
                                    <button data-url="'.admin_url('admin-ajax.php?action=fed_pay_payment_index&fed_nonce='.wp_create_nonce('fed_nonce')).'" class="btn btn-secondary pull-right fed_replace_ajax"><span class="fas fa-redo"></span> Back to Payment Dashboard</button>
                                    </h3>
                                </div>
                                <div class="fed_payment_list  padd_top_20">
                                Sorry you don\'t have any transactions
</div>';
                wp_send_json_success(['html' => $html]);
            }
            wp_send_json_success(['html' => 'Something went wrong']);
        }
        /**
         * Hidden for Not receiving properly from PayPal
         */
//		public function payment_page() {
//			fed_verify_nonce( $_REQUEST );
//			$paypal = new FED_PayPal();
//			if ( $paypal->is_true() ) {
//				$count = fed_isset_request( $_GET, 'payment_count', 100 );
//				$index = fed_isset_request( $_GET, 'payment_index', 1 );
//
//				$payments = $paypal->get_payments( $count, $index )->toArray();
//
//
//				$html = '';
//
//				$html .= '
//                        <div class="row padd_top_20">
//                            <div class="col-md-12">
//                                <div class="fed_page_title">
//                                    <h3 class="fed_header_font_color">
//                                    Payment Details
//                                    <button data-url="' . admin_url( 'admin-ajax.php?action=fed_pay_payment_index&fed_nonce=' . wp_create_nonce( 'fed_nonce' ) ) . '" class="btn btn-secondary pull-right fed_replace_ajax"><span class="fas fa-redo"></span> Back to Payment Dashboard</button>
//                                    </h3>
//                                </div>
//                                <div class="fed_payment_list  padd_top_20">
//                                    <div class="table-responsive">
//                                        <table class="table table-striped table-bordered">
//                                            <thead>
//                                            <tr>
//                                                <th>Name</th>
//                                                <th>Email</th>
//                                                <th>Amount</th>
//                                                <th>Invoice #</th>
//                                                <th>Status</th>
//                                                <th>Date</th>
//                                                <th></th>
//                                            </tr>
//                                            </thead>
//                                            <tbody>';
//
//				if ( count( $payments ) > 0 ) {
//					foreach ( $payments['payments'] as $payment ) {
//						foreach ( $payment['transactions'] as $transactions ) {
//							$first_name = isset( $payment['payer']['payer_info']['first_name'] ) ? $payment['payer']['payer_info']['first_name'] : "";
//							$last_name  = isset( $payment['payer']['payer_info']['last_name'] ) ? $payment['payer']['payer_info']['last_name'] : "";
//							$email      = isset( $payment['payer']['payer_info']['email'] ) ? $payment['payer']['payer_info']['email'] : '';
//							$total      = isset( $transactions['amount']['total'] ) ? $transactions['amount']['total'] : "";
//							$html       .= '<tr>';
//							$html       .= '<td>' . $first_name . $last_name . '</td>';
//							$html       .= '<td>' . $email . '</td>';
//							$html       .= '<td>' . $total . '</td>';
//							$html       .= '<td>' . $transactions['invoice_number'] . '</td>';
//							$html       .= '<td>' . $payment['state'] . '</td>';
////							$html       .= '<td>' . date_i18n( get_option( 'date_format' ), strtotime( $payment['create_time'] ) ) . '</td>';
//							$html       .= '<td>' . $payment['create_time'] . '</td>';
//							$html       .= '<td>';
//							$html       .= '<a class="btn btn-primary" href="' . fed_generate_url( array(
//									'transaction_id' => $payment['id'],
//									'action'         => 'payment_details'
//								), menu_page_url( 'fed_payment_menu', false ) ) . '">
//				<i class="fa fa-print" aria-hidden="true"></i>
//				</a>';
//							$html       .= '</td>';
//							$html       .= '</tr>';
//						}
//					}
//				}
//
//				$html .= '</tbody>
//                        </table>
//                        </div>
//                        </div>
//                        </div>
//                        </div>';
//
//				wp_send_json_success( [ 'html' => $html ] );
//			}
//			wp_send_json_error( [ 'message' => 'Please fill the PayPal API Details' ] );
//		}

        /**
         * @return string
         */
        public function add_new_plan()
        {
            $html = '';
            $html .=
                    '                       
				<form class="fed_save_plan_form" action="'.admin_url('admin-ajax.php?action=fed_save_plan&fed_nonce='.wp_create_nonce("fed_nonce")).'" method="post">
				
<div class="row padd_top_20">
<div class="col-md-12 m-b-10">
<button class="btn btn-secondary fed_pay_get_recurring fed_replace_ajax pull-right" data-url="'.admin_url('admin-ajax.php?action=fed_pay_recurring_payments&fed_nonce='.wp_create_nonce("fed_nonce")).'"> <i class="fas fa-redo" aria-hidden="true"></i> Back to Plan List</button>
</div>
<div class="col-md-12">
<div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Add New Plan</h3>
                </div>
                <div class="panel-body">
                <div class="fed_pay_recurring_details_wrapper">
                    <div class="row">
                        <div class="col-md-4">
                            <!--                    Name-->
                            <div class="form-group">
                                <label class="control-label">Name</label>
                                '.fed_get_input_details(array(
                            'input_type' => 'single_line',
                            'input_meta' => 'name',
                    )).'
                            </div>
                            </div>
                            <!--                    Type-->
                            <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Type</label>
                                '.fed_get_input_details(array(
                            'input_type'  => 'select',
                            'input_meta'  => 'type',
                            'input_value' => array(
                                    'FIXED'    => 'FIXED',
                                    'INFINITE' => 'INFINITE',
                            ),
                    )).'
                            </div>
                            </div>
                            <!--                    Description-->
                            <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Description</label>
                                '.fed_get_input_details(array(
                            'input_type' => 'multi_line',
                            'input_meta' => 'description',
                            'rows'       => '1',
                    )).'
                            </div>
                            </div>
                            
                    </div>
                    <div class="preference_container">
                                        <h4>Preferences</h4>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label class="control-label">Auto Billing?</label>
                                                    '.fed_get_input_details(array(
                            'input_type'  => 'select',
                            'input_meta'  => 'auto_billing',
                            'input_value' => fed_yes_no('ASC'),
                    )).'
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                            <div class="form-group">
                                                    <label class="control-label">Fail Action</label>
                                                    '.fed_get_input_details(array(
                            'input_type'  => 'select',
                            'input_meta'  => 'fail_amount_action',
                            'input_value' => array(
                                    'CONTINUE' => 'CONTINUE',
                                    'CANCEL'   => 'CANCEL',
                            ),
                    )).'
                                                </div>
                                        </div>
                                        <div class="col-md-3">
                            <!--                    Name-->
                            <div class="form-group">
                                <label class="control-label">Maximum Fail Attempt</label>
                                '.fed_get_input_details(array(
                            'input_type'  => 'single_line',
                            'input_meta'  => 'max_fail_attempt',
                            'placeholder' => '0 for Infinity',
                    )).'
                            </div>
                            </div>
                            <div class="col-md-2">
                            <!--                    Name-->
                            <div class="form-group">
                                <label class="control-label">Setup Fee</label>
                                '.fed_get_input_details(array(
                            'input_type'  => 'single_line',
                            'input_meta'  => 'setup_fee',
                            'placeholder' => 'Add Setup fee if any',
                    )).'
                            </div>
                            </div>
                            <div class="col-md-2">
                                    <div class="form-group">
                                            <label class="control-label">Currency</label>
                                            '.fed_get_input_details(array(
                            'input_type'  => 'select',
                            'input_meta'  => 'setup_fee_currency',
                            'input_value' => fed_get_currency_type_key_value(),
                    )).'
                                        </div>
</div>

                                        </div>
                                    </div>
                                    </div>
                                    <div class="fed_payment_definition_container">
                                    <h3 class="m-b-20">Payment Definition <button data-url="'.admin_url('admin-ajax.php?action=fed_add_new_payment_definition_html&fed_nonce='.wp_create_nonce("fed_nonce")).'" class="fed_add_new_payment_definition btn btn-secondary"><i class="fa fa-plus" aria-hidden="true"></i> Add New Payment Definition</button></h3>
                                <div class="fed_payment_definition">
                                '.$this->get_preference_container().'
                                    </div>
                                    
                                </div>
                    
                            </div>
                            <div class="row">
                            <div class="col-md-12">
                            <button class="btn btn-primary text-center" type="submit">Add New Plan</button>
</div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
</div>
</div>
</form>';

            wp_send_json_success(array('html' => $html));
        }

        /**
         *
         */
        public function fed_save_plan()
        {
            $request = fed_sanitize_text_field($_REQUEST);
            /**
             * TODO:save Plan
             */
            fed_verify_nonce($request);
            $plan = new FED_PayPal();

            $status = $plan->create_plan(fed_sanitize_text_field($request));
            FED_Log::writeLog(['inside' => $status]);

            if ($status instanceof Exception) {
                $error   = $status->getData();
                $message = json_decode($error, true);
                wp_send_json_success(['message' => $message['details'][0]['issue'], 'type' => 'error']);
            }
            wp_send_json_success(['message' => 'Plan Created Successfully']);
        }

        /**
         *
         */
        public function fed_add_new_payment_definition_html()
        {
            wp_send_json_success(array('html' => $this->get_preference_container()));
        }

        /**
         * @return string
         */
        public function get_preference_container()
        {
            $random = mt_rand(99, 999);
            $html   = '';
            $html   .= '<div class="payment_definition_wrapper fed_pay_close_wrapper p-10">
<div class="fed_pay_close_container">
<div class="fed_pay_close">
X
</div>
</div>
                                    <div class="payment_definition p-10">
                                    <div class="row">
                                    <div class="col-md-4">
                                    <div class="form-group">
                                            <label class="control-label">Name</label>
                                            <input type="text" name="payment_definition['.$random.'][name]" class="form-control"
                                                   requiredd="requiredd"
                                                   placeholder="Payment Definition Name"/>
                                        </div>
</div>
                                    <div class="col-md-4">
                                    <div class="form-group">
                                            <label class="control-label">Type</label>
                                            <select class="form-control" name="payment_definition['.$random.'][type]">
                                                <option value="TRIAL">TRIAL</option>
                                                <option value="REGULAR">REGULAR</option>
                                            </select>
                                        </div>
</div>
                                    <div class="col-md-4">
                                    <div class="form-group">
                                            <label class="control-label">Frequency</label>
                                            <select class="form-control" name="payment_definition['.$random.'][frequency]">
                                                <option value="YEAR">YEAR</option>
                                                <option value="MONTH">MONTH</option>
                                                <option value="WEEK">WEEK</option>
                                                <option value="DAY">DAY</option>
                                            </select>
                                        </div>
</div>
                                    <div class="col-md-4">
                                    <div class="form-group">
                                            <label class="control-label">Frequency Interval</label>
                                            <input type="text" name="payment_definition['.$random.'][freq_interval]"
                                                   class="form-control"
                                                   requiredd="requiredd"
                                                   placeholder="Frequency Interval (eg 1 or 3 or any intervals)"/>
                                        </div>
</div>
                                    <div class="col-md-3">
                                    <div class="form-group">
                                            <label class="control-label">Cycle</label>
                                            <input type="text" name="payment_definition['.$random.'][cycle]" class="form-control"
                                                   requiredd="requiredd"
                                                   placeholder="Cycle (eg 1 or 3 or any number, 0 for Unlimited)"/>
                                        </div>
</div>
                                    <div class="col-md-3">
                                    <div class="form-group">
                                            <label class="control-label">Amount</label>
                                            <input type="number" name="payment_definition['.$random.'][amount]" class="form-control"
                                                   requiredd="requiredd"
                                                   placeholder="Amount in Number"/>
                                        </div>
</div>
                                    
                                    <div class="col-md-2">
                                    <div class="form-group">
                                            <label class="control-label">Currency</label>
                                            '.fed_get_input_details(array(
                            'input_type'  => 'select',
                            'input_meta'  => 'payment_definition['.$random.'][currency]',
                            'input_value' => fed_get_currency_type_key_value(),
                    )).'
                                        </div>
</div>
                                    </div>
                                    </div>
                                    <div class="charge_container">

                                        <h3>Charges</h3>
                                        <div class="row">
                                            <div class="col-md-6">
                                            <div class="fed_shipping_container">
                                                <div class="form-group">
                                                    <label class="control-label">Enable Shipping</label>
                                                    <input type="checkbox"
                                                           name="payment_definition['.$random.'][shipping][type]"
                                                           value="shipping"/>
                                                </div>
                                                <div class="row">
                                                <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Amount</label>
                                                    <input type="number"
                                                           name="payment_definition['.$random.'][shipping][amount]"
                                                           class="form-control"
                                                           placeholder="Amount in Number"/>
                                                </div>
</div>
                                                <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Currency</label>
                                                    '.fed_get_input_details(array(
                            'input_type'  => 'select',
                            'input_meta'  => 'payment_definition['.$random.'][shipping][currency]',
                            'input_value' => fed_get_currency_type_key_value(),
                    )).'
                                                </div>
</div>
                                            </div>
                                                </div>
                                                </div>
                                                <div class="col-md-6">
                                                <div class="fed_shipping_container">
                                                <div class="form-group">
                                                    <label class="control-label">Enable Tax</label>
                                                    <input type="checkbox" name="payment_definition['.$random.'][tax][type]"
                                                           value="tax"/>
                                                </div>
                                                <div class="row">
                                                <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Tax</label>
                                                    <input type="number" name="payment_definition['.$random.'][tax][amount]"
                                                           class="form-control"
                                                           placeholder="Amount in Number"/>
                                                </div>
</div>
                                                <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Currency</label>
                                                    '.fed_get_input_details(array(
                            'input_type'  => 'select',
                            'input_meta'  => 'payment_definition['.$random.'][tax][currency]',
                            'input_value' => fed_get_currency_type_key_value(),
                    )).'
                                                </div>
</div>
                                            </div>
                                            </div>
                                                </div>
                                                </div>
                                        </div>
                                        </div>';

            return $html;
        }

        /**
         *
         */
        public function fed_pay_change_plan()
        {
            $request = fed_sanitize_text_field($_REQUEST);
            fed_verify_nonce($request);
            if (fed_isset_request($request, 'id', false) && fed_isset_request($request, 'plan',
                            false) && is_admin()) {
                $plan = new FED_PayPal();
//                FED_Log::writeLog($request['plan']);
                $status = $plan->activate_plan($request['id'], strtoupper($request['plan']));
                if ($status instanceof Exception) {
                    $error   = $status->getData();
                    $message = json_decode($error, true);
                    wp_send_json_error(['message' => $message['details'][0]['issue'], 'type' => 'error']);
                }

                wp_send_json_success(['message' => 'Plan '.strtoupper($request['plan']).' Successfully']);
            }
            wp_send_json_error(['message' => 'Something went wrongly']);
        }
    }

    new FED_Pay_Recurring();
}