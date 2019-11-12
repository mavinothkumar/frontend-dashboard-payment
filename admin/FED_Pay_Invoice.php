<?php

namespace FED_PayPal_Admin;


use FED_Log;
use FED_PayPal\FED_PayPal;
use PayPal\Api\Agreement;
use PayPal\Api\Payment;

if ( ! class_exists('FED_Pay_Invoice')) {
    /**
     * Class FED_Pay_Invoice
     *
     * @package FED_PayPal_Admin
     */
    class FED_Pay_Invoice
    {
        public function __construct()
        {
            add_action('wp_ajax_fed_admin_payment_invoice_details', array($this, 'save_invoice_details'));
            add_action('wp_ajax_fed_admin_payment_invoice_details', array($this, 'save_invoice_templates'));
            add_action('wp_ajax_fed_p_popup_invoice', array($this, 'popup_invoice'));

        }

        public function popup_invoice()
        {
            $request = fed_sanitize_text_field($_REQUEST);
            fed_verify_nonce($request);
            $paypal = new FED_PayPal();
            if ($request['type'] === 'single') {
                $transaction = $paypal->get_payment_by_id($request['transaction_id']);
                $html        = $this->format_single_invoice_string($transaction);
//                FED_Log::writeLog('$transaction');
//                FED_Log::writeLog($transaction);
            }
            if ($request['type'] === 'subscription') {
                $transaction = $paypal->get_billing_agreement($request['transaction_id']);
                $html        = $this->format_subscription_invoice_string($transaction);
//                FED_Log::writeLog('$transaction');
//                FED_Log::writeLog($transaction);
            }


            wp_send_json_success(array('html' => $html));

        }

        public function save_invoice_details()
        {
            $request                            = fed_sanitize_text_field($_REQUEST);
            $invoice_details_options            = get_option('fed_admin_settings_payments');
            $invoice_details_options['invoice'] = array(
                    'details' => array(
                            'logo'         => isset($request['invoice']['logo']) ? (int)$request['invoice']['logo'] : '',
                            'width'        => isset($request['invoice']['width']) ? fed_sanitize_text_field($request['invoice']['width']) : '',
                            'height'       => isset($request['invoice']['height']) ? fed_sanitize_text_field($request['invoice']['height']) : '',
                            'country'      => isset($request['invoice']['country']) ? fed_sanitize_text_field($request['invoice']['country']) : '',
                            'postal_code'  => isset($request['invoice']['postal_code']) ? fed_sanitize_text_field($request['invoice']['postal_code']) : '',
                            'state'        => isset($request['invoice']['state']) ? fed_sanitize_text_field($request['invoice']['state']) : '',
                            'city'         => isset($request['invoice']['city']) ? fed_sanitize_text_field($request['invoice']['city']) : '',
                            'street_name'  => isset($request['invoice']['street_name']) ? fed_sanitize_text_field($request['invoice']['street_name']) : '',
                            'door_number'  => isset($request['invoice']['door_number']) ? fed_sanitize_text_field($request['invoice']['door_number']) : '',
                            'company_name' => isset($request['invoice']['company_name']) ? fed_sanitize_text_field($request['invoice']['company_name']) : '',
                    ),
            );

            $new_settings = apply_filters('fed_admin_settings_payments_invoice_details_save', $invoice_details_options,
                    $request);

            update_option('fed_admin_settings_payments', $new_settings);

            wp_send_json_success(array(
                    'message' => __('Invoice Details Updated Successfully '),
            ));

        }

        public function save_invoice_templates()
        {
            $request                            = fed_sanitize_text_field($_REQUEST);
            $invoice_details_options            = get_option('fed_admin_settings_payments');
            $invoice_details_options['invoice'] = array(
                    'template' => array(
                            'logo'         => isset($request['invoice']['logo']) ? (int)$request['invoice']['logo'] : '',
                            'width'        => isset($request['invoice']['width']) ? fed_sanitize_text_field($request['invoice']['width']) : '',
                            'height'       => isset($request['invoice']['height']) ? fed_sanitize_text_field($request['invoice']['height']) : '',
                            'country'      => isset($request['invoice']['country']) ? fed_sanitize_text_field($request['invoice']['country']) : '',
                            'postal_code'  => isset($request['invoice']['postal_code']) ? fed_sanitize_text_field($request['invoice']['postal_code']) : '',
                            'state'        => isset($request['invoice']['state']) ? fed_sanitize_text_field($request['invoice']['state']) : '',
                            'city'         => isset($request['invoice']['city']) ? fed_sanitize_text_field($request['invoice']['city']) : '',
                            'street_name'  => isset($request['invoice']['street_name']) ? fed_sanitize_text_field($request['invoice']['street_name']) : '',
                            'door_number'  => isset($request['invoice']['door_number']) ? fed_sanitize_text_field($request['invoice']['door_number']) : '',
                            'company_name' => isset($request['invoice']['company_name']) ? fed_sanitize_text_field($request['invoice']['company_name']) : '',
                    ),
            );

            $new_settings = apply_filters('fed_admin_settings_payments_invoice_details_save', $invoice_details_options,
                    $request);

            update_option('fed_admin_settings_payments', $new_settings);

            wp_send_json_success(array(
                    'message' => __('Invoice Details Updated Successfully '),
            ));

        }


        /**
         * @param $settings
         */
        public function fed_pay_admin_invoice_details_tab($settings)
        {
            $array = array(
                    'form'  => array(
                            'method' => '',
                            'class'  => 'fed_admin_menu fed_ajax',
                            'attr'   => '',
                            'action' => array('url' => '', 'action' => 'fed_admin_payment_invoice_details'),
                            'nonce'  => array('action' => '', 'name' => ''),
                            'loader' => '',
                    ),
                    'input' => array(
                            'Company Logo' => array(
                                    'col'          => 'col-md-12',
                                    'name'         => __('Company Logo', 'frontend-dashboard-payment'),
                                    'input'        => fed_get_input_details(array(
                                            'input_meta' => 'invoice[logo]',
                                            'user_value' => isset($settings['settings']['invoice']['details']['logo']) ? $settings['settings']['invoice']['details']['logo'] : '',
                                            'input_type' => 'file',
                                    )),
                                    'help_message' => fed_show_help_message(array('content' => "Company Logo")),
                            ),
                            'Logo Width'   => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('Logo Width (px)', 'frontend-dashboard-payment'),
                                    'input'        =>
                                            fed_get_input_details(array(
                                                    'placeholder' => __('Logo Width in Pixel',
                                                            'frontend-dashboard-payment'),
                                                    'input_meta'  => 'invoice[width]',
                                                    'user_value'  => isset($settings['settings']['invoice']['details']['width']) ? $settings['settings']['invoice']['details']['width'] : '',
                                                    'input_type'  => 'single_line',
                                            )),
                                    'help_message' => fed_show_help_message(array('content' => "Logo Width in Pixel")),
                            ),
                            'Logo Height'  => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('Logo Height (px)', 'frontend-dashboard-payment'),
                                    'input'        =>
                                            fed_get_input_details(array(
                                                    'placeholder' => __('Logo Height in Pixel',
                                                            'frontend-dashboard-payment'),
                                                    'input_meta'  => 'invoice[height]',
                                                    'user_value'  => isset($settings['settings']['invoice']['details']['height']) ? $settings['settings']['invoice']['details']['height'] : '',
                                                    'input_type'  => 'single_line',
                                            )),
                                    'help_message' => fed_show_help_message(array('content' => "Logo Height in Pixel")),
                            ),
                            'Company Name' => array(
                                    'col'          => 'col-md-12',
                                    'name'         => __('Company Name', 'frontend-dashboard-payment'),
                                    'input'        =>
                                            fed_get_input_details(array(
                                                    'placeholder' => __('Company Name', 'frontend-dashboard-payment'),
                                                    'input_meta'  => 'invoice[company_name]',
                                                    'user_value'  => isset($settings['settings']['invoice']['details']['company_name']) ? $settings['settings']['invoice']['details']['company_name'] : '',
                                                    'input_type'  => 'single_line',
                                            )),
                                    'help_message' => fed_show_help_message(array('content' => "Company Name")),
                            ),
                            'Door Number'  => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('Door Number', 'frontend-dashboard-payment'),
                                    'input'        =>
                                            fed_get_input_details(array(
                                                    'placeholder' => __('Door Number', 'frontend-dashboard-payment'),
                                                    'input_meta'  => 'invoice[door_number]',
                                                    'user_value'  => isset($settings['settings']['invoice']['details']['door_number']) ? $settings['settings']['invoice']['details']['door_number'] : '',
                                                    'input_type'  => 'single_line',
                                            )),
                                    'help_message' => fed_show_help_message(array('content' => "Door Number")),
                            ),
                            'Street Name'  => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('Street Name', 'frontend-dashboard-payment'),
                                    'input'        =>
                                            fed_get_input_details(array(
                                                    'placeholder' => __('Street Name', 'frontend-dashboard-payment'),
                                                    'input_meta'  => 'invoice[street_name]',
                                                    'user_value'  => isset($settings['settings']['invoice']['details']['street_name']) ? $settings['settings']['invoice']['details']['street_name'] : '',
                                                    'input_type'  => 'single_line',
                                            )),
                                    'help_message' => fed_show_help_message(array('content' => "Street Name")),
                            ),
                            'City'         => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('City', 'frontend-dashboard-payment'),
                                    'input'        =>
                                            fed_get_input_details(array(
                                                    'placeholder' => __('City', 'frontend-dashboard-payment'),
                                                    'input_meta'  => 'invoice[city]',
                                                    'user_value'  => isset($settings['settings']['invoice']['details']['city']) ? $settings['settings']['invoice']['details']['city'] : '',
                                                    'input_type'  => 'single_line',
                                            )),
                                    'help_message' => fed_show_help_message(array('content' => "City")),
                            ),
                            'State'        => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('State', 'frontend-dashboard-payment'),
                                    'input'        =>
                                            fed_get_input_details(array(
                                                    'placeholder' => __('State', 'frontend-dashboard-payment'),
                                                    'input_meta'  => 'invoice[state]',
                                                    'user_value'  => isset($settings['settings']['invoice']['details']['state']) ? $settings['settings']['invoice']['details']['state'] : '',
                                                    'input_type'  => 'single_line',
                                            )),
                                    'help_message' => fed_show_help_message(array('content' => "State")),
                            ),
                            'Postal Code'  => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('Postal Code', 'frontend-dashboard-payment'),
                                    'input'        =>
                                            fed_get_input_details(array(
                                                    'placeholder' => __('Postal Code', 'frontend-dashboard-payment'),
                                                    'input_meta'  => 'invoice[postal_code]',
                                                    'user_value'  => isset($settings['settings']['invoice']['details']['postal_code']) ? $settings['settings']['invoice']['details']['postal_code'] : '',
                                                    'input_type'  => 'single_line',
                                            )),
                                    'help_message' => fed_show_help_message(array('content' => "Postal Code")),
                            ),
                            'Country'      => array(
                                    'col'          => 'col-md-6',
                                    'name'         => __('Country', 'frontend-dashboard-payment'),
                                    'input'        =>
                                            fed_get_input_details(array(
                                                    'placeholder' => __('Country', 'frontend-dashboard-payment'),
                                                    'input_value' => fed_get_country_code(),
                                                    'input_meta'  => 'invoice[country]',
                                                    'user_value'  => isset($settings['settings']['invoice']['details']['country']) ? $settings['settings']['invoice']['details']['country'] : '',
                                                    'input_type'  => 'select',
                                            )),
                                    'help_message' => fed_show_help_message(array('content' => "Country")),
                            ),

                    ),
            );

            $new_value = apply_filters('fed_pay_admin_payment_invoice_details', $array, $settings);
            fed_common_simple_layout($new_value);
        }

        /**
         * @param $settings
         */
        public function fed_pay_admin_invoice_templates_tab($settings)
        {
            $templates = fed_pay_invoice_templates();
            ?>
            <div class="p-20">
                <div class="row">
                    <?php foreach ($templates as $template) { ?>
                        <div class="col-md-6">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><?php echo $template['name'].' ( '.$template['version'].' )' ?></h3>
                                </div>
                                <div class="panel-body">
                                    <a target="_blank" href="<?php echo $template['image_full_url']; ?>">
                                        <img class="img-responsive" src="<?php echo $template['image_thumb_url']; ?>"/>
                                    </a>
                                    <div class="text-center padd_top_20">
                                        <button class="btn btn-secondary">
                                            <i class="fa fa-check"></i>
                                            Selected
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php
        }

        /**
         * @param \PayPal\Api\Agreement $transaction
         *
         * @return string
         */
        private function format_subscription_invoice_string(Agreement $transaction)
        {
            $local = fed_p_get_payment_by_id($transaction->getId());

            $invoice_settings = get_option('fed_admin_settings_payments');

            $invoice_details = $invoice_settings ? $invoice_settings['invoice']['details'] : array();

            if (count($local) > 0) {

                $payee = $transaction->getShippingAddress();

                $name = $transaction->getPayer()->getPayerInfo();

                $total = 0;

                $currency = '';

                foreach ($transaction->getPlan()->getPaymentDefinitions() as $pd) {
                    foreach ($pd->getChargeModels() as $chargeModel) {
                        $cm =
                                array(
                                        $chargeModel->getType() => array(
                                                'type'     => $chargeModel->getType(),
                                                'value'    => $chargeModel->getAmount()->getValue(),
                                                'currency' => $chargeModel->getAmount()->getCurrency(),
                                        ),
                                );
                    }

                    $payment_definition[] = array(
                            'type'      => $pd->getType(),
                            'frequency' => $pd->getFrequency(),
                            'amount'    => $pd->getAmount()->getValue(),
                            'currency'  => $pd->getAmount()->getCurrency(),
                            'tax'       => isset($cm['TAX']) ? $cm['TAX']['value'].' '.$cm['TAX']['currency'] : '',
                            'shipping'  => isset($cm['SHIPPING']) ? $cm['SHIPPING']['value'].' '.$cm['SHIPPING']['currency'] : '',
                    );

                    $total    = $total + $pd->getAmount()->getValue();
                    $currency = $pd->getAmount()->getCurrency();
                }

                $array = array(
                        'plan_name'          => $local['plan_name'],
                        'total'              => $total.' ' .$currency,
                        'description'        => $transaction->description,
                        'company'            => array(
                                'company_name' => $invoice_details['company_name'],
                                'logo'         => wp_get_attachment_url($invoice_details['logo']),
                                'width'        => ! empty($invoice_details['width']) ? 'width="'.$invoice_details['width'].'px"' : '',
                                'height'       => ! empty($invoice_details['height']) ? 'height="'.$invoice_details['height'].'px"' : '',
                                'door_number'  => $invoice_details['door_number'],
                                'street_name'  => $invoice_details['street_name'],
                                'city'         => $invoice_details['city'],
                                'state'        => $invoice_details['state'],
                                'postal_code'  => $invoice_details['postal_code'],
                                'country'      => $invoice_details['country'],
                        ),
                        'payee'              => array(
                                'recipient_name' => $name->getFirstName().' '.$name->getLastName(),
                                'line1'          => $payee->getLine1(),
                                'city'           => $payee->getCity(),
                                'state'          => $payee->getState(),
                                'postal_code'    => $payee->getPostalCode(),
                                'country_code'   => $payee->getCountryCode(),
                        ),
                        'invoice_number'     => $local['invoice_number'],
                        'invoice_date'       => $transaction->getStartDate(),
                        'payment_definition' => $payment_definition,
                );

                $html = $this->invoice_html_format($array);

                return $html;
            }

            return 'SOMETHING WENT WRONG';
        }

        /**
         * @param \PayPal\Api\Payment $transaction
         *
         * @return string
         */
        private function format_single_invoice_string(Payment $transaction)
        {
            $local = fed_p_get_payment_by_id($transaction->getId());

            $invoice_settings = get_option('fed_admin_settings_payments');

            $invoice_details = $invoice_settings ? $invoice_settings['invoice']['details'] : array();

            if (count($local) > 0) {

                $payee = $transaction->getTransactions()[0]->getItemList()->getShippingAddress();

                $name = $transaction->getPayer()->getPayerInfo();

                $amount = $list_items = array();

                foreach ($transaction->getTransactions() as $trans) {
                    $amount = array(
                            'total' => $trans->getAmount()->getTotal(),
                            'currency' => $trans->getAmount()->getCurrency(),
                            'subtotal' => $trans->getAmount()->getDetails()->getSubtotal(),
                            'tax' => $trans->getAmount()->getDetails()->getTax(),
                            'shipping' => $trans->getAmount()->getDetails()->getShipping(),
                            'insurance' => $trans->getAmount()->getDetails()->getInsurance(),
                            'handling_fee' => $trans->getAmount()->getDetails()->getHandlingFee(),
                            'shipping_discount' => $trans->getAmount()->getDetails()->getShippingDiscount(),
                    );

                    foreach($trans->getItemList()->getItems() as $items){
                        $list_items[] = array(
                                'name'=>$items->getName(),
                                'description'=>$items->getDescription(),
                                'price'=>$items->getPrice(),
                                'currency'=>$items->getCurrency(),
                                'tax'=>$items->getTax(),
                                'quantity'=>$items->getQuantity(),
                        );
                    }

                }

                $array = array(
                        'plan_name'          => $local['plan_name'],
                        'amount'              => $amount,
                        'company'            => array(
                                'company_name' => $invoice_details['company_name'],
                                'logo'         => wp_get_attachment_url($invoice_details['logo']),
                                'width'        => ! empty($invoice_details['width']) ? 'width="'.$invoice_details['width'].'px"' : '',
                                'height'       => ! empty($invoice_details['height']) ? 'height="'.$invoice_details['height'].'px"' : '',
                                'door_number'  => $invoice_details['door_number'],
                                'street_name'  => $invoice_details['street_name'],
                                'city'         => $invoice_details['city'],
                                'state'        => $invoice_details['state'],
                                'postal_code'  => $invoice_details['postal_code'],
                                'country'      => $invoice_details['country'],
                        ),
                        'payee'              => array(
                                'recipient_name' => $name->getFirstName().' '.$name->getLastName(),
                                'line1'          => $payee->getLine1(),
                                'city'           => $payee->getCity(),
                                'state'          => $payee->getState(),
                                'postal_code'    => $payee->getPostalCode(),
                                'country_code'   => $payee->getCountryCode(),
                        ),
                        'invoice_number'     => $local['invoice_number'],
                        'invoice_date'       => $transaction->getTransactions()[0]->getRelatedResources()[0]->getSale()->getCreateTime(),
                        'list_items' => $list_items,
                );

                $html = $this->invoice_html_single_format($array);

                return $html;
            }

            return 'SOMETHING WENT WRONG';
        }

        /**
         * @param $array
         *
         * @return string
         */
        private function invoice_html_format($array)
        {


            $html = '';

            $html .= '<div class="container" id="print">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="padding-top text-left" id="logo">
                            <img src="'.$array['company']['logo'].'" '.$array['company']['width'].' '.$array['company']['height'].' />
                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <h4>'.$array['company']['company_name'].'</h4>
                        <p>'.$array['company']['door_number'].' '.$array['company']['street_name'].'</p>
                        <p>'.$array['company']['city'].' '.$array['company']['state'].'</p>
                        <p>'.$array['company']['country'].' '.$array['company']['postal_code'].'</p>
                    </div>
                </div>
                <hr/>
                <div class="row text-uppercase">
                    <div class="col-sm-6 text-left">
                        <h3>Client Details</h3>
                        <h4 style="display: block;">'.$array['payee']['recipient_name'].'</h4>
                        <p>'.$array['payee']['line1'].'</p>
                        <p>'.$array['payee']['city'].' '.$array['payee']['state'].'</p>
                        <p>'.$array['payee']['country_code'].' '.$array['payee']['postal_code'].'</p>
                    </div>
                    <div class="col-sm-6 text-right">
                        <div class="invoice-color">
                            <h3>Invoice Number: '.$array['invoice_number'].'</h3>
                            <h4 style="display: block;">Invoice date: '.$array['invoice_date'].'</h4>
                            <h1 style="display: block;" class="big-font">'.$array['total'].'</h1>
                        </div>
                    </div>
                </div>
                <div class="row tablecss">
                    <div class="col-md-12">
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr style="display: table-row;" class="success">
                                <th>Name</th>
                                <th>Type</th>
                                <th>Frequency</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>';

            foreach ($array['payment_definition'] as $pd) {
                $html .= '<tr style="display: table-row;">
                                <td>'.$array['plan_name'].'</td>
                                <td>'.$pd['type'].'</td>
                                <td>'.$pd['frequency'].'</td>
                                <td>'.$pd['amount'].' '.$pd['currency'].'</td>
                            </tr>';
            }
            $html .= '<tr style="display: table-row;">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr style="display: table-row;" class="info">
                                <td colspan="3" class="text-right">
                                    <b>Total</b>
                                </td>
                                <td>
                                    <b>'.$array['total'].'</b>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <p class="invoice-color">                           
                        </p>
                    </div>
                </div>
            </div>';

            return $html;

        }

        /**
         * @param $array
         *
         * @return string
         */
        private function invoice_html_single_format($array)
        {


            $html = '';

            $html .= '<div class="container" id="print">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="padding-top text-left" id="logo">
                            <img src="'.$array['company']['logo'].'" '.$array['company']['width'].' '.$array['company']['height'].' />
                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <h4>'.$array['company']['company_name'].'</h4>
                        <p>'.$array['company']['door_number'].' '.$array['company']['street_name'].'</p>
                        <p>'.$array['company']['city'].' '.$array['company']['state'].'</p>
                        <p>'.$array['company']['country'].' '.$array['company']['postal_code'].'</p>
                    </div>
                </div>
                <hr/>
                <div class="row text-uppercase">
                    <div class="col-sm-6 text-left">
                        <h3>Client Details</h3>
                        <h4 style="display: block;">'.$array['payee']['recipient_name'].'</h4>
                        <p>'.$array['payee']['line1'].'</p>
                        <p>'.$array['payee']['city'].' '.$array['payee']['state'].'</p>
                        <p>'.$array['payee']['country_code'].' '.$array['payee']['postal_code'].'</p>
                    </div>
                    <div class="col-sm-6 text-right">
                        <div class="invoice-color">
                            <h3>Invoice Number: '.$array['invoice_number'].'</h3>
                            <h4 style="display: block;">Invoice date: '.$array['invoice_date'].'</h4>
                            <h1 style="display: block;" class="big-font">'.$array['amount']['total']. $array['amount']['currency'] .'</h1>
                        </div>
                    </div>
                </div>
                <div class="row tablecss">
                    <div class="col-md-12">
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr style="display: table-row;" class="success">
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>';

            foreach ($array['list_items'] as $list) {
                $html .= '<tr style="display: table-row;">
                                <td>'.$list['name'].'</td>
                                <td>'.$list['quantity'].'</td>
                                <td>'.$list['price'].' '.$list['currency'].'</td>
                            </tr>';
            }
            $html .= '<tr style="display: table-row;">
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr style="display: table-row;" class="info">
                                <td colspan="2" class="text-right">
                                    <b>SubTotal</b>
                                </td>
                                <td>
                                    <b>'.$array['amount']['subtotal']. ' ' . $array['amount']['currency'] .'</b>
                                </td>
                            </tr>
                            <tr style="display: table-row;" class="info">
                                <td colspan="2" class="text-right">
                                    <b>Tax</b>
                                </td>
                                <td>
                                    <b>'.$array['amount']['tax']. ' ' . $array['amount']['currency'] .'</b>
                                </td>
                            </tr>
                            <tr style="display: table-row;" class="info">
                                <td colspan="2" class="text-right">
                                    <b>Shipping</b>
                                </td>
                                <td>
                                    <b>'.$array['amount']['shipping']. ' ' . $array['amount']['currency'] .'</b>
                                </td>
                            </tr>
                            <tr style="display: table-row;" class="info">
                                <td colspan="2" class="text-right">
                                    <b>Insurance</b>
                                </td>
                                <td>
                                    <b>'.$array['amount']['insurance']. ' ' . $array['amount']['currency'] .'</b>
                                </td>
                            </tr>
                            <tr style="display: table-row;" class="info">
                                <td colspan="2" class="text-right">
                                    <b>Handling Fee</b>
                                </td>
                                <td>
                                    <b>'.$array['amount']['handling_fee']. ' ' . $array['amount']['currency'] .'</b>
                                </td>
                            </tr>
                            
                            <tr style="display: table-row;" class="info">
                                <td colspan="2" class="text-right">
                                    <b>Shipping Discount</b>
                                </td>
                                <td>
                                    <b>-'.$array['amount']['shipping_discount']. ' ' . $array['amount']['currency'] .'</b>
                                </td>
                            </tr>
                            
                            <tr style="display: table-row;" class="info">
                                <td colspan="2" class="text-right">
                                    <b>Total</b>
                                </td>
                                <td>
                                    <b>'.$array['amount']['total']. ' ' . $array['amount']['currency'] .'</b>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <p class="invoice-color">                           
                        </p>
                    </div>
                </div>
            </div>';

            return $html;

        }

    }

    new FED_Pay_Invoice();
}