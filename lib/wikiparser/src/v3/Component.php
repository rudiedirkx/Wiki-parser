<?php

namespace rdx\wikiparser\v3;

class Component {

	public $type;
	public $content;

	public function __construct( $content, $type ) {
		$this->content = $content;
		$this->type = $type;
	}

	public function render() {
		return "{{{$this->type}}}";
	}

}
