<?php

namespace rdx\wikiparser\v3;

class Element {

	public $content;

	public function __construct( $content ) {
		$this->content = $content;
	}

	public function render() {
		return "[[{$this->content}]]";
	}

}
