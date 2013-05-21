<?php

class Notify_IRC_Action {

	private $data;

	public function __construct( $host, $user, $nick, $pass = '', $port = 6697 ) {

		$this->data = new stdClass;

		foreach( array( 'host', 'user', 'nick', 'pass', 'port' ) as $field ) {
			$this->data->$field = $$field;
		}
	}

	/**
	 * Send a message to a specific room
	 */
	public function send_message_to_room( $message, $room ) {

		set_time_limit( 120 );

		$connection = fsockopen( $this->data->host, $this->data->port );
		if ( ! $connection )
			return new WP_Error( 'irc-connection', __( 'Could not connect to IRC.', 'notify-humans' ) );

		if ( $this->data->pass )
			fwrite( $connection, "PASS " . $this->data->pass . PHP_EOL );

		fwrite( $connection, "USER " . $this->data->user . PHP_EOL );
		fwrite( $connection, "NICK " . $this->data->nick . PHP_EOL );

		fwrite( $connection, "JOIN " . $room . PHP_EOL );
		fwrite( $connection, "PRIVMSG " . $room . " :" . $message . PHP_EOL );

		while ( $data = fgets( $connection, 128 ) ) {

			if ( strpos( $data, 'End of MOTD command' ) )
				break;

			$response = explode( ' ', $data );
			if ( 'ERROR' == $response[0] ) {
				return new WP_Error( 'irc-connection', $data );
			} else if ( "PING" == $response[0] ) {
				fwrite( $connection, "PONG " . $data[1] . PHP_EOL );
			}

		}

		fclose( $connection );
	}

}