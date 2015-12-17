<?php

namespace rdx\wikiparser;

use rdx\wikiparser\components\Unknown;
use rdx\wikiparser\components\Citation;
use rdx\wikiparser\components\Conversion;
use rdx\wikiparser\components\Picture;

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
	public function parseProperties( $properties ) {
		return $properties;
	}



	static protected $loaders = [];

	/**
	 *
	 */
	static public function load( $text ) {
		$properties = array_map('trim', explode('|', trim($text)));
		$type = array_shift($properties);

		$class = static::loader($type);
		return new $class($properties, $type);
	}

	/**
	 *
	 */
	static public function loader( &$type ) {
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
	static public function _loader( &$type ) {
		switch ( $type ) {
			case 'cite book':
			case 'cite web':
			case 'cite journal':
				$type = substr($type, 5);
				return Citation::class;

			case 'convert':
				return Conversion::class;

			case 'pic':
				return Picture::class;
		}
	}

}
