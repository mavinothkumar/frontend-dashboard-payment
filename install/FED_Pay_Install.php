<?php

namespace FED_Payment;

use FED_Log;

if ( ! class_exists('FED_Pay_Install')) {
    /**
     * Class FED_Pay_Install
     *
     * @package FED_Payment
     */
    class FED_Pay_Install
    {
        public function __construct()
        {
            add_action('admin_init', array($this, 'fed_pay_install'));
            add_filter('fed_status_get_table_status', array($this, 'fed_status_get_table_status'));
        }

        public function fed_status_get_table_status($table)
        {
            global $wpdb;

            $payment = $wpdb->prefix.BC_FED_PAY_PAYMENT_TABLE;
            $plan    = $wpdb->prefix.BC_FED_PAY_PAYMENT_PLAN_TABLE;

            $table['payment'] = array(
                    'title'       => 'Payment',
                    'status'      => $wpdb->get_var("SHOW TABLES LIKE '$payment'") != $payment ? fed_enable_disable(false) : fed_enable_disable(true),
                    'plugin_name' => BC_FED_PAY_APP_NAME,
                    'position'    => 0,
            );

            $table['plan'] = array(
                    'title'       => 'Plan',
                    'status'      => $wpdb->get_var("SHOW TABLES LIKE '$plan'") != $plan ? fed_enable_disable(false) : fed_enable_disable(true),
                    'plugin_name' => BC_FED_PAY_APP_NAME,
                    'position'    => 0,
            );

            return $table;
        }


        public function fed_pay_install()
        {

            $new_version = BC_FED_PAY_PLUGIN_VERSION;
            $old_version = get_option('fed_pay_plugin_version', '0');

            if ($old_version == $new_version) {
                return;
            }

            $this->install();
            $this->upgrade();

            update_option('fed_pay_plugin_version', $new_version);
        }

        public function install()
        {
            global $wpdb;

            require_once(ABSPATH.'wp-admin/includes/upgrade.php');
            $payment_table = $wpdb->prefix.BC_FED_PAY_PAYMENT_TABLE;
            $plan_table    = $wpdb->prefix.BC_FED_PAY_PAYMENT_PLAN_TABLE;


            $charset_collate = $wpdb->get_charset_collate();

            /**
             * Payment Table Structure
             *
             * User ID
             * Plan Name
             * Transaction ID
             * Invoice Number
             * Payer ID
             * Payment Source
             * Created at
             * Updated at
             * Trail Ends at
             * Ends at
             */

            if ($wpdb->get_var("SHOW TABLES LIKE '{$payment_table}'") != $payment_table) {
                $payment = "CREATE TABLE `".$payment_table."` (
		  id BIGINT(20) NOT NULL AUTO_INCREMENT,
		  user_id BIGINT(20) NOT NULL,
		  plan_name VARCHAR(255) NOT NULL,
		  plan_id VARCHAR(255) NOT NULL,
		  plan_type VARCHAR(255) NOT NULL,
		  payment_id VARCHAR(255) NOT NULL,
		  invoice_number VARCHAR(255) NULL,
		  payer_id VARCHAR(255) NULL,
		  payment_source VARCHAR(255) NOT NULL DEFAULT 'paypal',
		  created TIMESTAMP NOT NULL,
		  updated TIMESTAMP NOT NULL,
		  trail_ends TIMESTAMP NULL,
		  ends TIMESTAMP NULL,
		  PRIMARY KEY  (id)
		  ) $charset_collate;";

                $payment_log = dbDelta($payment);
                FED_Log::writeLog($payment_log);
            }

            if ($wpdb->get_var("SHOW TABLES LIKE '{$plan_table}'") != $plan_table) {
                $plan = "CREATE TABLE `".$plan_table."` (
		  id BIGINT(20) NOT NULL AUTO_INCREMENT,
		  user_id BIGINT(20) NOT NULL,
		  plan_id VARCHAR(255) NOT NULL,
		  plan_type VARCHAR(255) NOT NULL,
		  plan_name VARCHAR(255) NOT NULL,
		  description TEXT NOT NULL,
		  item_lists TEXT NOT NULL,
		  amount TEXT NOT NULL,
		  note_to_payee TEXT NOT NULL,
		  invoice_number VARCHAR(255) NOT NULL,
		  reference_id VARCHAR(255) NOT NULL,
		  purchase_order VARCHAR(255) NOT NULL,
		  status VARCHAR(255) NOT NULL,
		  positions BIGINT(20) NULL,
		  created TIMESTAMP NOT NULL,
		  updated TIMESTAMP NOT NULL,
		  PRIMARY KEY  (id)
		  ) $charset_collate;";

                $plan_log = dbDelta($plan);
                FED_Log::writeLog($plan_log);
            }
        }

        public function upgrade()
        {
            return '';
        }
    }

    new FED_Pay_Install();
}