<?php
/**
 * A way of notifying HipChat when something happens
 */
class Notify_HipChat_Action {

	private $hipchat_api_url = 'https://api.hipchat.com';

	public function __construct( $auth_token, $user = 'notify-bot' ) {

		$this->auth_token = $auth_token;
		$this->user = $user;
	}

	/**
	 * Send a message to a room.
	 * 
	 * @see https://www.hipchat.com/docs/api/method/rooms/message
	 */
	public function send_message_to_room( $message, $room_id, $options = array() ) {

		$defaults = array(
				'from'           => $this->user,
				'message_format' => 'text', // 'html' permits basic HTML
				'notify'         => 0, // '1' notifies everyone in the room
				'color'          => 'yellow', // One of "yellow", "red", "green", "purple", "gray", or "random".
			);
		$options = array_merge( $defaults, $options );

		$query_args = array(
				'auth_token' => $this->auth_token,
				'format' => 'json',
			);
		$api_endpoint = $this->hipchat_api_url . '/v1/rooms/message';
		$endpoint = add_query_arg( $query_args, $api_endpoint );

		$options['message'] = $message;
		$options['room_id'] = $room_id;
		$response = wp_remote_post( $endpoint, array( 'body' => $options ) );
		
		if ( 200 == wp_remote_retrieve_response_code( $response ) )
			return true;
		else
			return false;

	}

}