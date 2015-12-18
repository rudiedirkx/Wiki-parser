<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Text;

class UList extends Text {

	/**
	 *
	 */
	public function __construct( Document $document ) {
		$this->document = $document;
	}

	/**
	 *
	 */
	public function render() {
		echo '<ul>';
		foreach ($this->content as $content) {
			echo '<li>' . $this->parseText($content) . "</li>";
		}
		echo "</ul>\n";
	}

}
