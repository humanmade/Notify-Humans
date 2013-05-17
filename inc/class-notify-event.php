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
	 * Save the details associated with this event
	 */
	public function save() {
		global $wpdb;

		$defaults = array(
				'id'             => null,
				'event'          => null,
				'detail'         => null,
				'message'        => null,
				'host'           => null,
				'timestamp'      => date( "Y-m-d H:i:s" ),
			);
		$data = array_merge( $defaults, (array)$this->data );

		if ( empty( $data['event'] ) )
			return new WP_Error( 'invalid-arguments', "'event' is a required argument." );

		if ( empty( $data['id'] ) ) {
			$ret = $wpdb->insert( $wpdb->notify_events, $data );
			$data['id'] = (int) $wpdb->insert_id;
		} else {
			$ret = $wpdb->update( $wpdb->notify_events, $data, array( 'id' => $data['id'] ) );
		}

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
		if ( is_object( $event ) )
			return $event;
		else
			return false;
	}

}