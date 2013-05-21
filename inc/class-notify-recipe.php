<?php
/**
 * Recipes tie events to actions
 */
class Notify_Recipe {

	public $event;
	public $action;

	public function __construct() {
		/** Silence is golden. **/
	}

	/**
	 * Set an event for this recipe
	 *
	 * @param string     $event     The event to watch for
	 */
	public function set_event( $event ) {
		$this->event = $event;
	}

	/**
	 * Set an action for this recipe
	 */
	public function set_action( $action ) {
		$this->action = $action;
	}
}