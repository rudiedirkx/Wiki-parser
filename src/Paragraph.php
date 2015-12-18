<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Text;

class Paragraph extends Text {

	/**
	 *
	 */
	public function render() {
		$this->renderSectionStart();

		echo $this->parseText($this->content[0]);

		$this->renderSectionEnd();
	}

}
