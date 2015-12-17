<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Component;
use rdx\wikiparser\Property;
use rdx\wikiparser\components\Unknown;
use rdx\wikiparser\components\Citation;
use rdx\wikiparser\components\Conversion;
use rdx\wikiparser\components\Picture;

class Component {

	public $parent; // rdx\wikiparser\Component
	public $type = '';
	public $content = array();
	public $streamingProperty; // rdx\wikiparser\Component

	/**
	 *
	 */
	public function __construct( $parent = null ) {
		if ( $parent ) {
			$this->parent = $parent;
		}
	}

	/**
	 *
	 */
	public function add() {
		return $this->content[] = new $this($this);
	}

	/**
	 *
	 */
	public function newProperty() {
		$this->streamingProperty = $this->content[] = new Property($this);
		$this->streamingProperty->streamType('property');
		return $this->streamingProperty;
	}

	/**
	 *
	 */
	public function stream( $property ) {
		if ( !$this->parent ) {
			return $this->streamContent($property);
		}

		if ( !$this->type ) {
			return $this->streamType($property);
		}

		list($name, $value) = preg_split('#\s*=\s*#', $property . '=');
		return $this->streamProperty($name, $value);
	}

	/**
	 *
	 */
	public function streamContent( $content ) {
		$this->content[] = $content;
	}

	/**
	 *
	 */
	public function streamType( $type ) {
		$this->type = $type;
	}

	/**
	 *
	 */
	public function streamProperty( $name, $value = '' ) {
		$name = $this->decode($name);
		$value = $this->decode($value);
		$this->streamingProperty->streamContent($name . ':' . $value);
		// $this->content[$this->contentIndex][] = array($name, $value);
	}

	/**
	 *
	 */
	public function decode( $value ) {
		return strtr(html_entity_decode($value), array(
			'&times;' => 'x',
		));
	}

}
