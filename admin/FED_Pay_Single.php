<?php

namespace FED_PayPal_Admin;

use FED_Log;

if ( ! class_exists( 'FED_Pay_Single' ) ) {
	/**
	 * Class FED_Pay_Single
	 * @package FED_PayPal_Admin
	 */
	class FED_Pay_Single {

		public function __construct() {
			add_action( 'wp_ajax_fed_pay_single_list', array( $this, 'single_list' ) );
			add_action( 'wp_ajax_fed_pay_single_show', array( $this, 'single_show' ) );
			add_action( 'wp_ajax_fed_pay_single_save', array( $this, 'single_save' ) );
			add_action( 'wp_ajax_fed_pay_single_edit', array( $this, 'single_edit' ) );
			add_action( 'wp_ajax_fed_pay_single_update', array( $this, 'single_update' ) );
			add_action( 'wp_ajax_fed_pay_single_update_status', array( $this, 'single_update_status' ) );
			add_action( 'wp_ajax_fed_pay_single_delete', array( $this, 'single_delete' ) );
//			add_action( 'wp_ajax_fed_pay_single_edit', array( $this, 'single_edit' ) );

			/**
			 * Adding Items
			 */
			add_action( 'wp_ajax_fed_pay_single_show_add_items', array( $this, 'add_items' ) );
			add_action( 'wp_ajax_fed_pay_single_show_get_item', array( $this, 'get_item' ) );
		}

		public function single_update_status() {
            $request = fed_sanitize_text_field($_REQUEST);
			global $wpdb;
			$table_name = $wpdb->prefix . BC_FED_PAY_PAYMENT_PLAN_TABLE;
			fed_verify_nonce( $request );

			if ( ! isset( $request['status'] ) || ! isset( $request['id'] ) ) {
				wp_send_json_error( array( 'message' => 'Something went wrong, please reload the page' ) );
			}

			$status = $wpdb->update( $table_name, array(
				'status'  => $request['status'],
				'updated' => current_time( 'mysql' )
			), array( 'id' => (int) $request['id'] ) );
			if ( $status ) {
				wp_send_json_success( array(
					'message' => 'Successfully updated the status',
					'html'    => $this->fetch_single_plan_list()
				) );
			}

			FED_Log::writeLog( $status );
			wp_send_json_error( array( 'message' => 'Something went wrong' ) );
		}

		public function single_update() {
            $request = fed_sanitize_text_field($_REQUEST);
			global $wpdb;
			$table_name = $wpdb->prefix . BC_FED_PAY_PAYMENT_PLAN_TABLE;
			fed_verify_nonce( $request );

			if ( ! isset( $request['status'] ) || ! isset( $request['id'] ) ) {
				wp_send_json_error( array( 'message' => 'Something went wrong, please reload the page' ) );
			}

			$status = $wpdb->update( $table_name, array(
				'status'  => $request['status'],
				'updated' => current_time( 'mysql' )
			), array( 'id' => (int) $request['id'] ) );
			if ( $status ) {
				wp_send_json_success( array(
					'message' => 'Successfully updated the status',
					'html'    => $this->fetch_single_plan_list()
				) );
			}

			FED_Log::writeLog( $status );
			wp_send_json_error( array( 'message' => 'Something went wrong' ) );
		}

		public function single_edit() {
            $request = fed_sanitize_text_field($_REQUEST);
			fed_verify_nonce( $request );
			if ( ! isset( $request['id'] ) ) {
				wp_send_json_error( array( 'message' => 'Something went wrong, please reload the page' ) );
			}
			$list = fed_fetch_table_row_by_id( BC_FED_PAY_PAYMENT_PLAN_TABLE, (int) $request['id'] );

			wp_send_json_success( array( 'html' => $this->single_edit_html( $list ) ) );
		}

		/**
		 * @param $list
		 *
		 * @return string
		 */
		public function single_edit_html( $list ) {
			$item_lists = unserialize( $list['item_lists'] );
			$amount     = unserialize( $list['amount'] );
			$message    = '';

			$message .= '
			<div class="row padd_top_20">
				<div class="col-md-12">
					<button data-url="' . admin_url( 'admin-ajax.php?action=fed_pay_payment_index&fed_nonce=' . wp_create_nonce( 'fed_nonce' ) ) . '" class="btn btn-secondary pull-right fed_replace_ajax"><span class="fas fa-redo"></span> Back to Payment Dashboard</button>
					
					<button data-url="' . admin_url( 'admin-ajax.php?action=fed_pay_single_list&fed_nonce=' . wp_create_nonce( 'fed_nonce' ) ) . '" class="btn btn-secondary pull-right fed_replace_ajax m-r-10""><span class="fas fa-redo"></span> Back to Manage One Time Plan</button>
				</div>
				<form class="fed_update_single_plan_form fed_ajax" action="' . admin_url( 'admin-ajax.php?action=fed_pay_single_update&fed_nonce=' . wp_create_nonce( "fed_nonce" ) ) . '" method="post">
					<div class="col-md-12 padd_top_20">
				<div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">View ' . esc_attr( $list['plan_name'] ) . ' [ View Only ]</h3>
                </div>
                <div class="panel-body">
                    <div class="fed_pay_single_payments">
                        <div class="fed_pay_single_transactions">
                            <div class="fed_pay_single_transaction">
                                <div class="fed_pay_single_item_list">
	                                <div class="fed_pay_single_transaction_wrapper">
	                                <div class="fed_pay_single_amount">
	                                <div class="row">
	                                <div class="col-md-4">
	                                <div class="fed_pay_single_name">
	                                    <div class="form-group">
	                                        <label>Name</label>
											' . fed_get_input_details( array(
					'placeholder' => 'Please enter Name for this Plan',
					'input_meta'  => 'name',
					'user_value'  => $list['plan_name'],
					'input_type'  => 'single_line',
				) ) . '
	                                    </div>
	                                </div>	                                
	                                </div>
	                                <div class="col-md-8">
	                                <div class="fed_pay_single_description">
	                                    <div class="form-group">
	                                        <label>Description</label>
											' . fed_get_input_details( array(
					'placeholder' => 'Please enter description for Plan',
					'input_meta'  => 'description',
					'user_value'  => isset( $list['description'] ) ? $list['description'] : '',
					'input_type'  => 'single_line',
				) ) . '
	                                    </div>
	                                </div>
</div>
	                                <div class="col-md-3">
	                                <div class="form-group">
	                                        <label>Currency</label>
			                                ' . fed_get_input_details( array(
					'input_value' => fed_get_currency_type_key_value(),
					'input_meta'  => 'amount[currency]',
					'user_value'  => isset( $amount['currency'] ) ? $amount['currency'] : '',
					'input_type'  => 'select',
				) ) . '
	                                    </div>
									</div>
									<div class="col-md-4">
									<div class="form-group">
	                                        <label>Shipping</label>
			                                ' . fed_get_input_details( array(
					'placeholder' => 'Shipping Cost',
					'input_meta'  => 'amount[details][shipping]',
					'user_value'  => isset( $amount['details']['shipping'] ) ? $amount['details']['shipping'] : '',
					'input_type'  => 'single_line',
				) ) . '
	                                    </div>
	                                    </div>
	                                    <div class="col-md-5">
	                                    <div class="form-group">
	                                        <label>Handling Fee</label>
			                                ' . fed_get_input_details( array(
					'placeholder' => 'Shipping Cost',
					'input_meta'  => 'amount[details][handling_fee]',
					'user_value'  => isset( $amount['details']['handling_fee'] ) ? $amount['details']['handling_fee'] : '',
					'input_type'  => 'single_line',
				) ) . '
	                                    </div>
	                                    </div>
	                                    
	                                    <div class="col-md-4">
	                                    <div class="form-group">
	                                        <label>Tax</label>
			                                ' . fed_get_input_group( array(
					fed_get_input_details( array(
						'placeholder' => 'Tax',
						'input_meta'  => 'amount[details][tax]',
						'user_value'  => isset( $amount['details']['tax'] ) ? $amount['details']['tax'] : '',
						'input_type'  => 'single_line',
					) ),
					fed_get_input_details( array(
						'input_value' => array( 'percentage' => '%', 'fixed' => 'Fixed Amount' ),
						'input_meta'  => 'amount[details][tax_type]',
						'user_value'  => isset( $amount['details']['tax_type'] ) ? $amount['details']['tax_type'] : '',
						'input_type'  => 'select',
					) )
				) ) . '
	                                    </div>
	                                    </div>
	                                    <div class="col-md-4">
	                                    <div class="form-group">
	                                        <label>Shipping Discount</label>
			                                ' . fed_get_input_group( array(
					fed_get_input_details( array(
						'placeholder' => 'Shipping Discount',
						'input_meta'  => 'amount[details][shipping_discount]',
						'user_value'  => isset( $amount['details']['shipping_discount'] ) ? $amount['details']['shipping_discount'] : '',
						'input_type'  => 'single_line',
					) ),
					fed_get_input_details( array(
						'input_value' => array( 'percentage' => '%', 'fixed' => 'Fixed Amount' ),
						'input_meta'  => 'amount[details][shipping_discount_type]',
						'user_value'  => isset( $amount['details']['shipping_discount_type'] ) ? $amount['details']['shipping_discount_type'] : '',
						'input_type'  => 'select',
					) )
				) ) . '
	                                    </div>
	                                    </div>
	                                    <div class="col-md-4">
	                                    <div class="form-group">
	                                        <label>Insurance</label>
			                                ' . fed_get_input_group( array(
					fed_get_input_details( array(
						'placeholder' => 'Insurance',
						'input_meta'  => 'amount[details][insurance]',
						'user_value'  => isset( $amount['details']['insurance'] ) ? $amount['details']['insurance'] : '',
						'input_type'  => 'single_line',
					) ),
					fed_get_input_details( array(
						'input_value' => array( 'percentage' => '%', 'fixed' => 'Fixed Amount' ),
						'input_meta'  => 'amount[details][insurance_type]',
						'user_value'  => isset( $amount['details']['insurance_type'] ) ? $amount['details']['insurance_type'] : '',
						'input_type'  => 'select',
					) )
				) ) . '
	                                    </div>
	                                    
							</div>
							</div>
									</div>
	                                
	                                <div class="fed_pay_single_note_to_payee">
	                                    <div class="form-group">
	                                        <label>Note to Payee</label>
			                                ' . fed_get_input_details( array(
					'placeholder' => 'Please enter Note to Payee',
					'input_meta'  => 'note_to_payee',
					'user_value'  => isset( $list['note_to_payee'] ) ? $list['note_to_payee'] : '',
					'input_type'  => 'multi_line',
				) ) . '
	                                    </div>
	                                </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="fed_pay_single_item_wrapper">';

			foreach ( $item_lists as $item ) {
				$message .= $this->item( $item );
			}
			$message .= ' </div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="fed_pay_single_status">
                            <div class="form-group">
                                <label>Status</label>
								' . fed_get_input_details( array(
					'input_value' => array( 'INACTIVE' => 'INACTIVE', 'ACTIVE' => 'ACTIVE' ),
					'input_meta'  => 'status',
					'user_value'  => isset( $list['status'] ) ? $list['status'] : '',
					'input_type'  => 'select',
				) ) . '
                            </div>

                        </div>
                    </div>
                </div>
            </div>
</div>
				</form>
			</div>
			';

			return $message;

		}

		/**
		 * @return string
		 */
		public function fetch_single_plan_list() {
			$html = '';
			$list = fed_fetch_rows_by_table( BC_FED_PAY_PAYMENT_PLAN_TABLE );

			$html .= '
				<div class="row padd_top_20">
				<div class="col-md-12">
				<button data-url="' . admin_url( 'admin-ajax.php?action=fed_pay_payment_index&fed_nonce=' . wp_create_nonce( 'fed_nonce' ) ) . '" class="btn btn-secondary pull-right fed_replace_ajax"><span class="fas fa-redo"></span> Back to Payment Dashboard</button>
				</div>
				
				<div class="col-md-12 padd_top_20">
				<div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">One Time Plan List</h3>
                </div>
                <div class="panel-body">
				<div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>status</th>
                        <th>Created</th>
                        <th>Updated</th>
                    </tr>
                    </thead>
                    <tbody>';
			foreach ( $list as $item ) {
				$html .= '<tr>
                        <td>' . $item['plan_id'] . '</td>
                        <td>' . $item['plan_name'] . '</td>
                        <td>' . $item['status'] . '</td>
                        <td>' . $item['created'] . '</td>
                        <td>' . $item['updated'] . '</td>
                        <td>';
				if ( $item['status'] === 'ACTIVE' ) {
					$html .= '
<button data-url="' . admin_url( 'admin-ajax.php?action=fed_pay_single_update_status&status=INACTIVE&id=' . $item['id'] . '&fed_nonce=' . wp_create_nonce( "fed_nonce" ) ) . '" class="btn btn-secondary m-r-10 fed_alert_replace_ajax">INACTIVATE</button>
				';
				}
				if ( $item['status'] === 'INACTIVE' ) {
					$html .= '
				<button data-url="' . admin_url( 'admin-ajax.php?action=fed_pay_single_update_status&status=ACTIVE&id=' . $item['id'] . '&fed_nonce=' . wp_create_nonce( "fed_nonce" ) ) . '" class="btn btn-primary m-r-10 fed_alert_replace_ajax" >ACTIVATE</button>
				';
				}
				$html .= '
<button data-url="' . admin_url( 'admin-ajax.php?action=fed_pay_single_edit&id=' . $item['id'] . '&fed_nonce=' . wp_create_nonce( "fed_nonce" ) ) . '" class="btn btn-primary m-r-10 fed_replace_ajax" >VIEW</button>
</td>
</tr>';
			}
			$html .= '</tbody>
                </table>
            </div>
            </div>
            </div>
</div>';

			return $html;
		}

		/**
		 * @param bool $echo
		 */
		public function single_list() {
			$html = $this->fetch_single_plan_list();
			wp_send_json_success( array( 'html' => $html ) );
		}

		public function single_show() {

			$message = '
			<div class="row padd_top_20">
				<div class="col-md-12">
					<button data-url="' . admin_url( 'admin-ajax.php?action=fed_pay_payment_index&fed_nonce=' . wp_create_nonce( 'fed_nonce' ) ) . '" class="btn btn-secondary pull-right fed_replace_ajax"><span class="fas fa-redo"></span> Back to Payment Dashboard</button>
				</div>
				<form class="fed_save_single_plan_form fed_ajax" action="' . admin_url( 'admin-ajax.php?action=fed_pay_single_save&fed_nonce=' . wp_create_nonce( "fed_nonce" ) ) . '" method="post">
					<div class="col-md-12 padd_top_20">
				<div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Add New One Time Plan</h3>
                </div>
                <div class="panel-body">
                    <div class="fed_pay_single_payments">
                        <div class="fed_pay_single_transactions">
                            <div class="fed_pay_single_transaction">
                                <div class="fed_pay_single_item_list">
	                                <div class="fed_pay_single_transaction_wrapper">
	                                <div class="fed_pay_single_amount">
	                                <div class="row">
	                                <div class="col-md-4">
	                                <div class="fed_pay_single_name">
	                                    <div class="form-group">
	                                        <label>Name</label>
											' . fed_get_input_details( array(
					'placeholder' => 'Please enter Name for this Plan',
					'input_meta'  => 'name',
					'user_value'  => '',
					'input_type'  => 'single_line',
				) ) . '
	                                    </div>
	                                </div>	                                
	                                </div>
	                                <div class="col-md-8">
	                                <div class="fed_pay_single_description">
	                                    <div class="form-group">
	                                        <label>Description</label>
											' . fed_get_input_details( array(
					'placeholder' => 'Please enter description for Plan',
					'input_meta'  => 'description',
					'user_value'  => '',
					'input_type'  => 'single_line',
				) ) . '
	                                    </div>
	                                </div>
</div>
	                                <div class="col-md-3">
	                                <div class="form-group">
	                                        <label>Currency</label>
			                                ' . fed_get_input_details( array(
					'input_value' => fed_get_currency_type_key_value(),
					'input_meta'  => 'amount[currency]',
					'user_value'  => '',
					'input_type'  => 'select',
				) ) . '
	                                    </div>
									</div>
									<div class="col-md-4">
									<div class="form-group">
	                                        <label>Shipping</label>
			                                ' . fed_get_input_details( array(
					'placeholder' => 'Shipping Cost',
					'input_meta'  => 'amount[details][shipping]',
					'user_value'  => '',
					'input_type'  => 'single_line',
				) ) . '
	                                    </div>
	                                    </div>
	                                    
	                                    <div class="col-md-5">
	                                    <div class="form-group">
	                                        <label>Handling Fee</label>
			                                ' . fed_get_input_details( array(
					'placeholder' => 'Shipping Cost',
					'input_meta'  => 'amount[details][handling_fee]',
					'user_value'  => '',
					'input_type'  => 'single_line',
				) ) . '
	                                    </div>
	                                    </div>
	                                    
	                                    <div class="col-md-4">
	                                    <div class="form-group">
	                                        <label>Tax</label>
			                                ' . fed_get_input_group( array(
					fed_get_input_details( array(
						'placeholder' => 'Tax',
						'input_meta'  => 'amount[details][tax]',
						'user_value'  => '',
						'input_type'  => 'single_line',
					) ),
					fed_get_input_details( array(
						'input_value' => array( 'percentage' => '%', 'fixed' => 'Fixed Amount' ),
						'input_meta'  => 'amount[details][tax_type]',
						'user_value'  => '',
						'input_type'  => 'select',
					) )
				) ) . '
	                                    </div>
	                                    </div>
	                                    <div class="col-md-4">
	                                    <div class="form-group">
	                                        <label>Shipping Discount</label>
			                                ' . fed_get_input_group( array(
					fed_get_input_details( array(
						'placeholder' => 'Shipping Discount',
						'input_meta'  => 'amount[details][shipping_discount]',
						'user_value'  => '',
						'input_type'  => 'single_line',
					) ),
					fed_get_input_details( array(
						'input_value' => array( 'percentage' => '%', 'fixed' => 'Fixed Amount' ),
						'input_meta'  => 'amount[details][shipping_discount_type]',
						'user_value'  => '',
						'input_type'  => 'select',
					) )
				) ) . '
	                                    </div>
	                                    </div>
	                                    <div class="col-md-4">
	                                    <div class="form-group">
	                                        <label>Insurance</label>
			                                ' . fed_get_input_group( array(
					fed_get_input_details( array(
						'placeholder' => 'Insurance',
						'input_meta'  => 'amount[details][insurance]',
						'user_value'  => '',
						'input_type'  => 'single_line',
					) ),
					fed_get_input_details( array(
						'input_value' => array( 'percentage' => '%', 'fixed' => 'Fixed Amount' ),
						'input_meta'  => 'amount[details][insurance_type]',
						'user_value'  => '',
						'input_type'  => 'select',
					) )
				) ) . '
	                                    </div>
	                                    
							</div>
							</div>
									</div>
	                                
	                                <div class="fed_pay_single_note_to_payee">
	                                    <div class="form-group">
	                                        <label>Note to Payee</label>
			                                ' . fed_get_input_details( array(
					'placeholder' => 'Please enter Note to Payee',
					'input_meta'  => 'note_to_payee',
					'user_value'  => '',
					'input_type'  => 'multi_line',
				) ) . '
	                                    </div>
	                                </div>
                                </div>
                                
                                <button  data-url="' . admin_url( 'admin-ajax.php?action=fed_pay_single_show_get_item&fed_nonce=' . wp_create_nonce( 'fed_nonce' ) ) . '"  class="btn btn-secondary pull-right fed_add_new_single_item m-b-20">Add New Item</button>
                                <div class="clearfix"></div>
                                <div class="fed_pay_single_item_wrapper">
                                    ' . $this->item() . '
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="fed_pay_single_status">
                            <div class="form-group">
                                <label>Status</label>
								' . fed_get_input_details( array(
					'input_value' => array( 'INACTIVE' => 'INACTIVE', 'ACTIVE' => 'ACTIVE' ),
					'input_meta'  => 'status',
					'user_value'  => '',
					'input_type'  => 'select',
				) ) . '
                            </div>

                        </div>
                        
                        <div class="fed_submit_button">
                        <button class="btn btn-primary" type="submit">Submit</button>
</div>
                    </div>
                </div>
            </div>
</div>
				</form>
			</div>
			';
			wp_send_json_success( array( 'html' => $message ) );
		}

		public function single_save() {
            $request = fed_sanitize_text_field($_REQUEST);
			fed_verify_nonce( $request );
			$list   = $amount = array();
			$format = [
				'payments' => [
//				'intent'        => '',
//				'payer'         => '',
					'status'       => '',
//				'redirect_urls' => [
//					'return_url' => '',
//					'cancel_url' => '',
//				],
					'transactions' => [
						'transaction1' => [


							'item_list'      => [
								'item1' => [
									'name'        => '',
									'currency'    => '',
									'description' => '',
									'quantity'    => '',
									'url'         => '',
									'sku'         => '',
									'price'       => '',
									'tax'         => '',
								],
								'item2' => [
									'name'        => '',
									'currency'    => '',
									'description' => '',
									'quantity'    => '',
									'url'         => '',
									'sku'         => '',
									'price'       => '',
									'tax'         => '',
								]
							],
							'amount'         => [
								'currency' => '',
								'total'    => '',
								'details'  => [
									'shipping'          => '',
									'tax'               => '',
									'sub_total'         => '',
									'handling_fee'      => '',
									'shipping_discount' => '',
									'insurance'         => '',
//									'gift_wrap'         => '',
								],
							],
							'description'    => '',
							'invoice_number' => '',
							'reference_id'   => '',
							'note_to_payee'  => '',
							'purchase_order' => '',
						]
					]
				]
			];

			/**
			 * (
			 * [action] =&gt; fed_pay_single_save
			 * [fed_nonce] =&gt; b300b0d31d
			 * [item] =&gt; Array
			 * (
			 * [60521] =&gt; Array
			 * (
			 * [plan_name] =&gt; test
			 * [description] =&gt; tessdd
			 * [quantity] =&gt; 1
			 * [price] =&gt; 1
			 * [sku] =&gt; 3232
			 * [url] =&gt;
			 * [tax] =&gt; 22
			 * [tax_type] =&gt; fixed
			 * )
			 *
			 * )
			 *
			 * [currency] =&gt; USD
			 * [shipping] =&gt; 2
			 * [gift_wrap] =&gt; 3
			 * [handling_fee] =&gt; 3
			 * [tax] =&gt; 3
			 * [tax_type] =&gt; percentage
			 * [shipping_discount] =&gt; 4
			 * [shipping_discount_type] =&gt; fixed
			 * [insurance] =&gt; 2
			 * [insurance_type] =&gt; percentage
			 * [description] =&gt; asdf
			 * [note_to_payee] =&gt; asdf
			 * [status] =&gt; Active
			 * )
			 */

			if ( ! isset( $request['item'] ) || ! isset( $request['amount'] ) ) {
				wp_send_json_error( array( 'message' => 'At least one item and amount fields are required to create a plan' ) );
			}
			foreach ( $request['item'] as $item ) {
				$list[] = array(
					'name'        => fed_sanitize_text_field( $item['name'] ),
					'description' => fed_sanitize_text_field( $item['description'] ),
					'quantity'    => fed_sanitize_text_field( $item['quantity'] ),
					'url'         => fed_sanitize_text_field( $item['url'] ),
					'sku'         => fed_sanitize_text_field( $item['sku'] ),
					'price'       => (float) ( $item['price'] ),
					'tax'         => (float) ( $item['tax'] ),
					'tax_type'    => fed_sanitize_text_field( $item['tax_type'] ),
				);
			}

			$amount = array(
				'currency' => fed_sanitize_text_field( $request['amount']['currency'] ),
				'details'  => array(
					'shipping'               => (float) ( $request['amount']['details']['shipping'] ),
					'tax'                    => (float) ( $request['amount']['details']['tax'] ),
					'tax_type'               => fed_sanitize_text_field( $request['amount']['details']['tax_type'] ),
					'handling_fee'           => (float) ( $request['amount']['details']['handling_fee'] ),
					'shipping_discount'      => (float) ( $request['amount']['details']['shipping_discount'] ),
					'shipping_discount_type' => fed_sanitize_text_field( $request['amount']['details']['shipping_discount_type'] ),
					'insurance'              => (float) ( $request['amount']['details']['insurance'] ),
					'insurance_type'         => fed_sanitize_text_field( $request['amount']['details']['insurance_type'] ),
//					'gift_wrap'              => (float) ( $request['amount']['details']['gift_wrap'] ),
				),
			);

			$value = array(
				'user_id'        => get_current_user_id(),
				'plan_id'        => fed_get_random_string( 12 ),
				'plan_type'      => 'single',
				'plan_name'      => fed_sanitize_text_field( $request['name'] ),
				'description'    => fed_sanitize_text_field( $request['description'] ),
				'item_lists'     => serialize( $list ),
				'amount'         => serialize( $amount ),
				'note_to_payee'  => fed_sanitize_text_field( $request['note_to_payee'] ),
				'invoice_number' => current_time( 'YmdHis' ) . '_' . fed_get_random_string( 10 ),
				'reference_id'   => '',
				'purchase_order' => '',
				'positions'      => 0,
				'status'         => fed_sanitize_text_field( $request['status'] ),
				'created'        => current_time( 'mysql' ),
				'updated'        => current_time( 'mysql' ),
			);

			$status = fed_insert_new_row( BC_FED_PAY_PAYMENT_PLAN_TABLE, $value );
			if ( $status ) {
				wp_send_json_success( array( 'message' => 'Successfully Saved' ) );
			}

			wp_send_json_error( array( 'message' => 'Something Went Wrong' ) );

		}

		public function single_delete() {

		}

		public function add_items() {

		}

		/**
		 * @param null $item
		 *
		 * @return string
		 */
		public function item( $item = null ) {
			$random = mt_rand( 99, 99999 );
			$html   = '';

			$html .= '<div class="fed_pay_single_item">';

			if ( $item === null ) {
				$html .= '<div class="fed_pay_close_container_item">
<div class="fed_pay_close">
X
</div>
</div>';
			}
			$html .= '<div class="row">
                                    <div class="col-md-4">
                                    <div class="form-group">
                                            <label>Plan Name</label>
		                                    ' . fed_get_input_details( array(
					'placeholder' => 'Plan Name',
					'input_meta'  => 'item[' . $random . '][name]',
					'user_value'  => $item !== null ? $item['name'] : '',
					'input_type'  => 'single_line',
				) ) . '
                                        </div>
                                        </div>
									<div class="col-md-8">                                     
                                        <div class="form-group">
                                            <label>Description</label>
		                                    ' . fed_get_input_details( array(
					'placeholder' => 'Please enter description for Item',
					'input_meta'  => 'item[' . $random . '][description]',
					'user_value'  => $item !== null ? $item['description'] : '',
					'input_type'  => 'single_line',
				) ) . '
                                        </div>
                                        </div>
                                        <div class="col-md-4">  
                                        <div class="form-group">
                                            <label>Quantity</label>
		                                    ' . fed_get_input_details( array(
					'placeholder' => 'Please enter quantity for Item',
					'input_meta'  => 'item[' . $random . '][quantity]',
					'user_value'  => $item !== null ? $item['quantity'] : '',
					'input_type'  => 'single_line',
				) ) . '
                                        </div>
                                        </div>
                                        <div class="col-md-4">
                                        
                                        <div class="form-group">
                                            <label>Price</label>
		                                    ' . fed_get_input_details( array(
					'placeholder' => 'Please enter Price for this Item',
					'input_meta'  => 'item[' . $random . '][price]',
					'user_value'  => $item !== null ? $item['price'] : '',
					'input_type'  => 'single_line',
				) ) . '
                                        </div>
                                        </div>
                                        <div class="col-md-4">  
                                        <div class="form-group">
                                            <label>SKU</label>
		                                    ' . fed_get_input_details( array(
					'placeholder' => 'Please enter SKU for this Item',
					'input_meta'  => 'item[' . $random . '][sku]',
					'user_value'  => $item !== null ? $item['sku'] : '',
					'input_type'  => 'single_line',
				) ) . '
                                        </div>
                                        </div>
                                        <div class="col-md-6">  
                                        <div class="form-group">
                                            <label>URL</label>
		                                    ' . fed_get_input_details( array(
					'placeholder' => 'Please enter Item URL',
					'input_meta'  => 'item[' . $random . '][url]',
					'user_value'  => $item !== null ? $item['url'] : '',
					'input_type'  => 'url',
				) ) . '
                                        </div>
                                        </div>        
                                        <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tax</label>
                                            ' . fed_get_input_group( array(
					fed_get_input_details( array(
						'placeholder' => 'Please enter Tax for this Item',
						'input_meta'  => 'item[' . $random . '][tax]',
						'user_value'  => $item !== null ? $item['tax'] : '',
						'input_type'  => 'single_line',
					) ),
					fed_get_input_details( array(
						'input_value' => array( 'percentage' => '%', 'fixed' => 'Fixed Amount' ),
						'input_meta'  => 'item[' . $random . '][tax_type]',
						'user_value'  => $item !== null ? $item['tax_type'] : '',
						'input_type'  => 'select',
					) )
				) ) . '
		                                     
                                        </div>
                                        </div>
                                    </div>
                                    </div>';

			return $html;
		}

		public function get_item() {
			wp_send_json_success( array( 'html' => $this->item() ) );
		}

	}

	new FED_Pay_Single();
}


