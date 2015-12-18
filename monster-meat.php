<?php

namespace dontstarve;

use rdx\wikiparser\Component;
use dontstarve\FoodInfobox;
use dontstarve\Picture;

require 'inc.bootstrap.php';

Component::register(function(&$type) {
	switch ( $type ) {
		case 'Food Infobox':
			return FoodInfobox::class;

		case 'pic':
			return Picture::class;
	}
});

class FoodInfobox extends Component {
	public function __construct( $properties, $type ) {
		parent::__construct($properties, $type);

		$parser = $this->getParser();

		// Extend properties that have components
		foreach ($this->properties as $name => $value) {
			if ( strstr($value, '{{') ) {
				$this->properties[$name] = $parser->parseDocument($value);
			}
		}
	}
}

class Picture extends Component {

}

$wiki = file_get_contents('monster-meat.wiki');
include 'inc.parse.php';
