<?php
/**
 * Plugin Name: WP Settings API Helper Example
 * Plugin URI:  http://dnaber.de/
 * Author:      David Naber
 * Author URI:  http://dnaber.de/
 * Version:     0.1
 * Description: Only a test for the Settings API Helper
 */

add_action( 'init', array( 'WP_Settings_API_Helper_Test', 'init' ) );

class WP_Settings_API_Helper_Test {

	function init() {
		require_once dirname( __FILE__ ) . '/settings-api-helper/class-Settings_API_Helper.php';
		require_once dirname( __FILE__ ) . '/settings-api-helper/class-Settings_API_Field.php';
		new self;
	}

	function __construct() {

		add_action( 'admin_init', array( $this, 'init_settings' ) );
	}

	function init_settings() {

		$settings = new Settings_API_Helper(
			'my_option_key',    # the key for the option table
			'general',          # the settings page, the section is append to
			__( 'Hello World Description', 'my_textdomain' ), # headline
			'This is my Description' #description,
		);

		# a text field
		$settings->add_text(
			'example',
			__( 'New Field', 'my_textdomain' ),
			array(
				'default' => '',
				'class'   => 'large-text' # may also be an array
			)
		);

		# a set of radio buttons
		$settings->add_radio(
			'pizza',
			__( 'Pizza', 'my_textdomain' ),
			array(
				'default' => 'salame',
				'options' => array(
					'salame' => 'Salame grande',
					'funghi' => 'Funghi',
					'magarita' => 'Magarita'
				)
			)
		);

		$settings->add_select(
			'my_select',
			__( 'Pasta', 'my_textdomain' ),
			array(
				'default' => '',
				'options' => array(
					'',
					'napoli' => 'Napoli',
					'bolognese' => 'Bolognese',
					'aglio-e-olio' => 'Aglio e Olio'
				)
			)
		);

		$settings->add_textarea(
			'my_ta',
			__( 'Address', 'my_textdomain' ),
			array(
				'required' => TRUE
			)
		);

		$settings->add_checkbox(
			'my_cb',
			__( 'Express', 'my_textdomain' ),
			array(
				'value' => 'express'
			)
		);

	}

}
