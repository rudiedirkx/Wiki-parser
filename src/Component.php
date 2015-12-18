<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Parser;
use rdx\wikiparser\Component;
use rdx\wikiparser\components\Unknown;
use rdx\wikiparser\components\Citation;

class Component {

	public $type = '';
	public $properties = array();

	/**
	 *
	 */
	public function __construct( $properties, $type ) {
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
	protected function getParser() {
		return new Parser;
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
	static public function load( $text ) {
		// Remove {{ and }}
		$text = trim(substr($text, 2, -2));

		// Split type and properties
		$piped = explode('|', $text, 2);
		$type = trim($piped[0]);
		$properties = trim(@$piped[1]);

		// Create type specific Component object
		$class = static::loader($type);
		return new $class($properties, $type);
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
