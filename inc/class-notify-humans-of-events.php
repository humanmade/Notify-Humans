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
				'regex' 			=> '^of/([^/]+)/?$',
				'disable_canonical' => true,
				'request_methods'	=> array( 'post', 'put' ),
				'request_callback'	=> array( $this, 'handle_notify_event' ),
			);
		hm_add_rewrite_rule( $args );
	}

	public function handle_notify_event( $wp ) {

		
	}

}

Notify_Humans()->of_events = new Notify_Humans_Of_Events();