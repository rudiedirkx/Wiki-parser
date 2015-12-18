<?php

namespace dontstarve;

use rdx\wikiparser\Document;
use rdx\wikiparser\Component;
use rdx\wikiparser\components\Ignore;

use dontstarve\Picture;
use dontstarve\FoodInfobox;
use dontstarve\Quote;
use dontstarve\Recipe;

require 'inc.bootstrap.php';

Component::register(function(&$type) {
	$components = array(
		'pic' => Picture::class,
		'Food Infobox' => FoodInfobox::class,
		'Quote' => Quote::class,
		'Recipe' => Recipe::class,
		'Mob Dropped Items' => Ignore::class,
		'Edible Items' => Ignore::class,
	);
	return @$components[$type];
});

class FoodInfobox extends Ignore {
	public function __construct( Document $document, $properties, $type ) {
		parent::__construct($document, $properties, $type);

		$parser = $this->getParser();

		// Extend properties that have components
		foreach ($this->properties as $name => $value) {
			if ( strstr($value, '{{') ) {
				$this->properties[$name] = $parser->parseSection($value);
			}
		}
	}
}

class Quote extends Component {
	public function render() {
		echo '<blockquote><p>' . $this->properties[0] . '</p><p><em>' . $this->properties[1] . '</em></p></blockquote>';
	}
}

class Picture extends Component {
	public function render() {
		echo ' &lt;' . $this->properties[1] . '&gt; ';
	}
}

class Recipe extends Component {
	public function render() {
		$operations = array_keys(array_filter($this->properties, function($value) {
			return $value === 'yes';
		}));
		$operations = array_map(function($operation) {
			return ' + ' . $operation;
		}, $operations);
		echo '<p>&lt;' . $this->properties['item'] . implode($operations) . ' = ' . $this->properties['result'] . '&gt;</p> ';
	}
}

$wiki = file_get_contents('monster-meat.wiki');
include 'inc.parse.php';
