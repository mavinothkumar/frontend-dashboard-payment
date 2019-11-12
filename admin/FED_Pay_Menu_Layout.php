<?php

namespace FED_PayPal_Admin;

if ( ! class_exists( 'FED_Pay_Menu_Layout' ) ) {
	/**
	 * Class FEDE_Menu
	 */
	class FED_Pay_Menu_Layout {
		/**
		 * FEDE_Menu constructor.
		 */
		public function __construct() {


			add_filter( 'fed_add_main_sub_menu', array(
				$this,
				'fed_pay_add_main_sub_menu'
			) );

			add_filter( 'fed_admin_dashboard_settings_menu_header', array(
				$this,
				'fed_pay_admin_dashboard_settings_menu_header'
			) );

			add_filter( 'fed_admin_script_loading_pages', array(
				$this,
				'fed_pay_admin_script_loading_pages'
			) );

			add_action( 'fed_enqueue_script_style_admin', array( $this, 'script_style_admin' ) );

			add_action( 'fed_enqueue_script_style_frontend', array( $this, 'frontend_script_style_admin' ) );

			add_action( 'wp_ajax_fed_admin_paypal_setting_form', array( $this, 'fed_pay_admin_paypal_api_save' ) );
		}

		public function fed_pay_admin_paypal_api_save() {
			$request                                 = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$fed_admin_settings_paypal_api           = get_option( 'fed_admin_settings_payments' );
			$fed_admin_settings_paypal_api['paypal'] = array(
				'api' => array(
					'type'               => fed_isset_sanitize( $request['paypal']['api']['type'] ),
					'success_url'        => fed_isset_sanitize( $request['paypal']['api']['success_url'] ),
					'cancel_url'         => fed_isset_sanitize( $request['paypal']['api']['cancel_url'] ),
					'sandbox_client_id'  => fed_isset_sanitize( $request['paypal']['api']['sandbox_client_id'] ),
					'sandbox_secrete_id' => fed_isset_sanitize( $request['paypal']['api']['sandbox_secrete_id'] ),
					'live_client_id'     => fed_isset_sanitize( $request['paypal']['api']['live_client_id'] ),
					'live_secrete_id'    => fed_isset_sanitize( $request['paypal']['api']['live_secrete_id'] ),
				)
			);

			$new_settings = apply_filters( 'fed_admin_settings_payments_paypal_api_save', $fed_admin_settings_paypal_api, $request );

			update_option( 'fed_admin_settings_payments', $new_settings );

			wp_send_json_success( array(
				'message' => __( 'PayPal API Updated Successfully ' )
			) );
		}


		/**
		 * @param $menu
		 *
		 * @return mixed
		 */
		public function fed_pay_add_main_sub_menu( $menu ) {
			$menu['fed_payment_menu'] = array(
				'page_title' => __( 'Payment', 'frontend-dashboard-payment' ),
				'menu_title' => __( 'Payment', 'frontend-dashboard-payment' ),
				'capability' => 'manage_options',
				'callback'   => array( new FED_Pay_Recurring(), 'fed_payment_menu' ),
				'position'   => 30
			);

			return $menu;
		}

		/**
		 * Top Menu Header
		 *
		 * @param array $menu
		 *
		 * @return array
		 */
		public function fed_pay_admin_dashboard_settings_menu_header( $menu ) {
			$settings        = get_option( 'fed_admin_settings_payments' );
			$menu['payment'] = array(
				'icon_class' => 'fa fa-credit-card',
				'name'       => __( 'Payments', 'frontend-dashboard-payment-payment' ),
				'callable'   => array(
					'object' => new FED_Pay_Recurring(),
					'method' => 'fed_pay_admin_payment_options_tab',
					'parameters' => array( 'settings' => $settings ),
				),
			);

			return $menu;
		}

		/**
		 * Scripts
		 *
		 * @param $pages
		 *
		 * @return array
		 */
		public function fed_pay_admin_script_loading_pages( $pages ) {

			return array_merge( $pages, array( 'fed_payment_menu' ) );
		}

		/**
		 * Style
		 */
		public function script_style_admin() {
			wp_enqueue_style( 'fed_pay_admin_style',
				plugins_url( 'assets/fed_paypal.css', BC_FED_PAY_PLUGIN ),
				array(), BC_FED_PAY_PLUGIN_VERSION, 'all' );

			wp_enqueue_script( 'fed_pay_admin_script', plugins_url( 'assets/fed_paypal.js', BC_FED_PAY_PLUGIN ), array() );
		}

		public function frontend_script_style_admin() {
            wp_enqueue_style( 'fed_pay_admin_style_global',
                    plugins_url( 'assets/fed_paypal_global.css', BC_FED_PAY_PLUGIN ),
                    array(), BC_FED_PAY_PLUGIN_VERSION, 'all' );

			wp_enqueue_script( 'fed_pay_admin_script_global', plugins_url( 'assets/fed_paypal_global.js', BC_FED_PAY_PLUGIN ), array() );
		}

	}

	new FED_Pay_Menu_Layout();
}