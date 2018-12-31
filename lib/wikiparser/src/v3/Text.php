<?php

namespace rdx\wikiparser\v3;

class Text {

	public $text;

	public function __construct( $text ) {
		$this->text = $text;
	}

	public function render() {
		return strip_tags($this->text);
	}

}
