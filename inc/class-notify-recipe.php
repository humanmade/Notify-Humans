<?php
/**
 * Recipes tie events to actions
 */
class Notify_Recipe {

	public $event;
	public $action;
	public $occurrences;
	public $filter_callback;

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

	public function set_occurrences( $occurrences, $timeframe ) {
		$this->occurrences = array( $occurrences, $timeframe );
	}

	public function set_filter_callback( $filter ) {
		$this->filter_callback = $filter;
	}

	/**
	 * Set an action for this recipe
	 */
	public function set_action( $action ) {
		$this->action = $action;
	}
}