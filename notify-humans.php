<?php
/*
Plugin Name: Notify Humans
Version: 0.1-alpha
Description: If Then, Then That... for your applications.
Author: danielbachhuber, humanmade
Author URI: http://hmn.md/
Plugin URI: http://wordpress.org/extend/plugins/notify-humans/
Text Domain: notify-humans
Domain Path: /languages
*/

class Notify_Humans {

	private $data;

	private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Notify_Humans;
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	private function __construct() {
		/** Prevent the class from being loaded more than once **/
	}

	public function __isset( $key ) {
		return isset( $this->data[$key] );
	}

	public function __get( $key ) {
		return isset( $this->data[$key] ) ? $this->data[$key] : null;
	}

	public function __set( $key, $value ) {
		$this->data[$key] = $value;
	}

	private function setup_globals() {

		$this->file           = __FILE__;
		$this->basename       = apply_filters( 'notify_humans_plugin_basenname', plugin_basename( $this->file ) );
		$this->plugin_dir     = apply_filters( 'notify_humans_plugin_dir_path',  plugin_dir_path( $this->file ) );
		$this->plugin_url     = apply_filters( 'notify_humans_plugin_dir_url',   plugin_dir_url ( $this->file ) );

		$this->extend         = new stdClass();

	}

	private function includes() {

	}

	private function setup_actions() {

		do_action_ref_array( 'notify_humans_after_setup_actions', array( &$this ) );
	}

}

function Notify_Humans() {
	return Notify_Humans::get_instance();
}
add_action( 'plugins_loaded', 'Notify_Humans' );