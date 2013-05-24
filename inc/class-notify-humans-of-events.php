<?php

class Notify_Humans_Of_Events extends Notify_Humans {

	public function __construct() {
		add_action( 'notify_humans_after_setup_actions', array( $this, 'setup_actions' ) );
	}

	public function setup_actions() {

		add_action( 'init', array( $this, 'action_init_register_rewrite' ) );
	}

	public function action_init_register_rewrite() {

		$args = array(
				'regex'             => '^of/([^/]+)/?$',
				'query'             => 'notify_event=$matches[1]',
				'disable_canonical' => true,
				'request_methods'   => array( 'post', 'put' ),
				'request_callback'  => array( $this, 'handle_notify_event' ),
			);
		hm_add_rewrite_rule( $args );
	}

	public function handle_notify_event( $wp ) {

		$notify = sanitize_key( $wp->query_vars['notify_event'] );
		// @todo validate against a whitelist of events
		if ( ! $notify )
			Notify_Humans()->do_json_response( 'error', __( 'Invalid event specified.', 'notify-humans' ) );


		// Parse the payload. Because we don't know what it is necessarily,
		// we'll need to handle it safely later.

		if ( ! empty( $_POST ) ) {
			$payload = stripslashes_deep( $_POST );
		} elseif ( $stdin = file_get_contents( 'php://input' ) ) {
			$payload = $stdin;
		} elseif ( ! empty( $_GET ) ) {
			$payload = stripslashes_deep( $_GET );
		} else {
			$payload = null;
		}

		$payload = json_encode( $payload );

		$event_args = array(
				'slug'       => $notify,
				'payload'     => $payload,
				'remote_ip'   => filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP ),
			);

		// @todo validate the sender is who they say they are
		if ( ! empty( $_SERVER['REMOTE_HOST'] ) )
			$event_args['remote_host'] = sanitize_text_field( $_SERVER['REMOTE_HOST'] );

		$event = new Notify_Event( $event_args );
		$ret = $event->save();
		if ( is_wp_error( $ret ) )
			Notify_Humans()->do_response( 500, $ret->get_error_message() );

		$ret = Notify_Humans()->do_event_actions( $event );
		if ( is_wp_error( $ret ) )
			Notify_Humans()->do_response( 500, $ret->get_error_message() );

		Notify_Humans()->do_response( 200, __( 'Event logged and actions triggered. Thanks for telling us about it.', 'notify-humans' ) );		
	}

}

Notify_Humans()->of_events = new Notify_Humans_Of_Events();