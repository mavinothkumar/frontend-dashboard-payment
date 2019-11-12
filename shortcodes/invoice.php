<?php
if ( ! shortcode_exists( 'fed_invoice' ) && ! function_exists( 'fed_invoice' ) ) {
	/**
	 * Add Shortcode to the page.
	 *
	 * @return string
	 */
	function fed_invoice( ) {

		$templates = new FED_Template_Loader(BC_FED_PAY_PLUGIN_DIR);
		ob_start();
		$templates->get_template_part( 'fed_invoice' );
		return ob_get_clean();
	}

	add_shortcode( 'fed_invoice', 'fed_invoice' );
}