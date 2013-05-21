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

	}

	private function includes() {

		// Basic object classes
		require_once( $this->plugin_dir . 'inc/class-notify-event.php' );
		require_once( $this->plugin_dir . 'inc/class-notify-recipe.php' );

		// Reusable actions
		require_once( $this->plugin_dir . 'inc/class-notify-irc-action.php' );

		require_once( $this->plugin_dir . 'inc/class-notify-humans-of-events.php' );

	}

	private function setup_actions() {

		if ( ! function_exists( 'hm_add_rewrite_rule' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notices_missing_hm_rewrites' ) );
			return;
		}

		add_action( 'init', array( $this, 'action_init_register_tables' ) );

		do_action_ref_array( 'notify_humans_after_setup_actions', array( &$this ) );
	}

	/**
	 * A way of notifying ourselves of an event.
	 * Useful for when the action execution (e.g. IRC)
	 * may take longer than we want.
	 */
	public function of( $event, $payload = false ) {

		$args = array(
				'blocking'     => false,
			);
		if ( $payload )
			$args['body'] = $payload;
		wp_remote_post( home_url( '/of/' . $event . '/' ), $args );
	}

	/**
	 * Start a new recipe
	 *
	 * @param string      $event     Event to start the recipe with
	 */
	public function if_event( $event ) {
		$recipe = new Notify_Recipe;
		$recipe->set_event( $event );
		$this->data['current_recipe'] = $recipe;
		return self::$instance;
	}

	/**
	 * Add an action to the current recipe, then save it
	 *
	 * @param            $action    Action to end the recipe with
	 */
	public function then_action( $action ) {

		$recipe = $this->data['current_recipe'];
		if ( empty( $recipe ) )
			return false;

		unset( $this->data['current_recipe'] );
		$recipe->set_action( $action );

		if ( ! isset( $this->data['recipes'] ) )
			$this->data['recipes'] = array();

		$this->data['recipes'][] = $recipe;
		return true;
	}

	/**
	 * Do actions for an event.
	 *
	 * @todo return the WP_Errors we potentially collected
	 *
	 * @param obj              $event     The event object
	 * @return true|WP_Error   $result    True on success, WP_Error on a failure.
	 */
	public function do_event_actions( $event ) {

		if ( ! is_a( $event, 'Notify_Event' ) )
			return new WP_Error( 'invalid-event', __( 'Event is not valid.', 'notify-humans' ) );

		$actions = wp_filter_object_list( $this->data['recipes'], array( 'event' => $event->get_slug() ) );

		$ret = array();
		foreach( $actions as $recipe ) {
			$func_ret = call_user_func_array( $recipe->action, array( $event ) );
			if ( is_wp_error( $func_ret ) )
				$ret[] = $func_ret;
		}

		if ( empty( $ret ) )
			return true;
	}

	/**
	 * To log our events and actions in the database, we must ensure the
	 * database exists
	 */
	public function action_init_register_tables() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'notify_events';
		if ( is_admin() ) {
			// Maybe create the table(s)
			if ( ! (bool)$wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) ) {
				$wpdb->query( "CREATE TABLE `{$table_name}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL DEFAULT '',
  `payload` text,
  `remote_host` varchar(255) DEFAULT NULL,
  `remote_ip` int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;" );
			}
		}

		$wpdb->tables[] = 'notify_events';
		$wpdb->notify_events = $table_name;
	}

	/**
	 * Notify Humans is dependent on HM Rewrites
	 */
	public function admin_notices_missing_hm_rewrites() {
		echo '<div class="error"><p>' . __( 'Please install HM Rewrites for Notify Humans to work properly', 'notify-humans' ) . '</p></div>';
	}

	/**
	 * Do a response to a request
	 *
	 * @param int     $status         HTTP status header
	 * @param string  $message        Message to include with response (optional)
	 */
	public static function do_response( $status, $message = '' ) {
		HM_Rewrite::do_response( $status, $message );
	}

}

function Notify_Humans() {
	return Notify_Humans::get_instance();
}
add_action( 'plugins_loaded', 'Notify_Humans' );