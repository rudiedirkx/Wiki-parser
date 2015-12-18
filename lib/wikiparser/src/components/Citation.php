<?php

namespace rdx\wikiparser\components;

use rdx\wikiparser\Component;

class Citation extends Component {

	/**
	 *
	 */
	public function render() {
		echo '<u title="' . $this->document->parseText($this->properties['title']) . '">&nbsp;*&nbsp;</u> ';
	}

}
