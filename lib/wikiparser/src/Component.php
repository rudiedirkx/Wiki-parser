<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Document;
use rdx\wikiparser\Renderable;
use rdx\wikiparser\Parser;
use rdx\wikiparser\components\Unknown;
use rdx\wikiparser\components\Citation;

abstract class Component extends Renderable {

	public $type = '';
	public $properties = array();

	/**
	 *
	 */
	public function __construct( Document $document, $properties, $type ) {
		$this->document = $document;
		$this->type = $type;
		$this->properties = $this->parseProperties($properties);
	}

	/**
	 *
	 */
	protected function parseProperties( $properties ) {
		$parser = $this->getParser();
		return $parser->parseProperties($properties);
	}

	/**
	 *
	 */
	public function decode( $value ) {
		return strtr(html_entity_decode($value), array(
			'&times;' => 'x',
		));
	}



	static public $loaders = [];

	/**
	 *
	 */
	static public function load( Document $document, $text ) {
		// Remove {{ and }}
		$text = trim(substr($text, 2, -2));

		// Split type and properties
		$piped = explode('|', $text, 2);
		$type = trim($piped[0]);
		$properties = trim(@$piped[1]);

		// Create type specific Component object
		$class = static::loader($type);
		return new $class($document, $properties, $type);
	}

	/**
	 *
	 */
	static protected function loader( &$type ) {
		$loaders = static::$loaders;
		$loaders[] = __CLASS__ . '::_loader';

		foreach ( $loaders as $callback ) {
			if ( $class = call_user_func_array($callback, array(&$type)) ) {
				return $class;
			}
		}

		return Unknown::class;
	}

	/**
	 *
	 */
	static public function register( callable $callback ) {
		static::$loaders[] = $callback;
	}

	/**
	 *
	 */
	static protected function _loader( &$type ) {
		switch ( $type ) {
			case 'cite book':
			case 'cite web':
			case 'cite journal':
				$type = substr($type, 5);
				return Citation::class;
		}
	}

}
