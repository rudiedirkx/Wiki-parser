<?php

namespace rdx\wikiparser\v3;

class Heading {

	public $text;

	public function __construct( $text ) {
		$this->text = $text;
	}

	public function render() {
		return "<h2>" . strip_tags($this->text) . "</h2>";
	}

}
