<?php

namespace rdx\wikiparser\components;

use rdx\wikiparser\Component;

class Ignore extends Component {

	public $ignore = true;

	/**
	 *
	 */
	public function render() {
		// Don't do anything
	}

}
