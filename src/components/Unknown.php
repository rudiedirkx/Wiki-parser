<?php

namespace rdx\wikiparser\components;

use rdx\wikiparser\Component;

class Unknown extends Component {

	public function render() {
		echo ' ?? ';
	}

}
