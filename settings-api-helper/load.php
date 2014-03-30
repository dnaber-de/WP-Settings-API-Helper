<?php

/**
 * WP Settings API helper
 *
 * autoloads some classes to make the settings api more easier to work with
 *
 * @link https://github.com/dnaber-de/WP-Settings-API-Helper
 * @package Wordpress
 * @subpackage Settings API Helper
 * @author David Naber <kontakt@dnaber.de>
 * @version 2014.03.30
 */

if ( ! class_exists( 'Settings_Api_Autoload' ) ) {

	class Settings_API_Autoload {

		/**
		 * include paths
		 *
		 * @var array
		 */
		protected static $include_paths = array();

		/**
		 * add include paths
		 *
		 * @param array|string $paths
		 * @return void
		 */
		public static function add_path( $paths ) {

			if ( is_array( $paths ) ) {
				foreach ( $paths as $path ) {
					if ( is_dir( $path ) ) {
						self :: $include_paths[] = $path;
					}
				}
			} else {
				if ( is_dir( (string)$paths ) ) {
					self :: $include_paths[] = $paths;
				}
			}
		}

		/**
		 * autoloader
		 *
		 * @param string $class
		 * @return void
		 */
		public static function load( $class ) {

			$class = 'class-' . $class . '.php';
			foreach ( self::$include_paths as $path ) {
				# remove trailing slash
				$path = rtrim( $path, '/' );
				if ( file_exists( $path . '/' . $class ) ) {
					require_once $path . '/' .$class ;
					return;
				}
			}
		}
	}
	Settings_API_Autoload::add_path( dirname( __FILE__ ) );
	spl_autoload_register( array( 'Settings_API_Autoload', 'load' ) );
	#functions
	require_once dirname( __FILE__ ) . '/settings-api-helper-functions.php';
}
