<?php

class Notify_Event {

	private $data;

	public function __construct( $args = array() ) {

		$args = (object)$args;
		if ( ! empty( $args->id ) ) {
			$this->data = $this->get_by_id( $args->id );
		} else {
			$this->data = $args;
		}
	}

	/**
	 * Get the id for this event
	 */
	public function get_id() {
		if ( ! empty( $this->data->id ) )
			return $this->data->id;
		else
			return false;
	}

	/**
	 * Get the slug for this event
	 */
	public function get_slug() {
		if ( ! empty( $this->data->slug ) )
			return $this->data->slug;
		else
			return false;
	}

	/**
	 * Get the payload for this event
	 */
	public function get_payload() {
		if ( ! empty( $this->data->payload ) )
			return json_decode( $this->data->payload );
		else
			return false;
	}

	/**
	 * Get the remote IP address for this event
	 */
	public function get_remote_ip() {
		if ( ! empty( $this->data->remote_ip ) )
			return $this->data->remote_ip;
		else
			return false;
	}

	/**
	 * Save the details associated with this event
	 */
	public function save() {
		global $wpdb;

		$defaults = array(
				'id'             => null,
				'slug'           => null,
				'payload'        => null,
				'remote_host'    => null,
				'remote_ip'      => null,
				'timestamp'      => date( "Y-m-d H:i:s" ),
			);
		$data = array_merge( $defaults, (array)$this->data );

		if ( empty( $data['slug'] ) )
			return new WP_Error( 'invalid-arguments', "'slug' is a required argument." );

		if ( ! empty( $data['remote_ip'] ) )
			$data['remote_ip'] = ip2long( $data['remote_ip'] );

		if ( empty( $data['id'] ) ) {
			unset( $data['id'] );
			$ret = $wpdb->insert( $wpdb->notify_events, $data );
			$data['id'] = (int) $wpdb->insert_id;
		} else {
			$ret = $wpdb->update( $wpdb->notify_events, $data, array( 'id' => $data['id'] ) );
		}

		if ( ! empty( $data['remote_ip'] ) )
			$data['remote_ip'] = long2ip( $data['remote_ip'] );

		if ( $ret ) {
			$this->data = (object)$data;
			return true;
		} else {
			return new WP_Error( 'mysql-failure', "Couldn't insert/update event into database." );
		}
	}

	/**
	 * Get an event based on its id
	 */
	public static function get_by_id( $id ) {
		global $wpdb;

		$event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->notify_events WHERE id=%s", $id ) );
		if ( is_object( $event ) ) {
			if ( ! empty( $event->remote_ip ) )
				$event->remote_ip = long2ip( $event->remote_ip );
			return $event;
		} else {
			return false;
		}
	}

}