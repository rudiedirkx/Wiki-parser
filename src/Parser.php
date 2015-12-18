<?php

namespace rdx\wikiparser;

// Method 1: [x] create nested arrays for every component
// Method 2: [x] create Components with content[] with Components
// Method 3: [.] real stream, find complete component markup, create as whole, create subs in there
// Method 4: [ ] real stream, create Component after the first property (component type)

use rdx\wikiparser\Component;
use rdx\wikiparser\Text;

class Parser {

	protected $text = '';

	/**
	 *
	 */
	public function __construct( $text = '' ) {
		$this->text = trim($text);
	}

	/**
	 *
	 */
	public function parseDocument( $text = '' ) {
		$text or $text = $this->text;

		$components = array();

		$length = strlen($text);
		$last2 = '';
		$depth = $start = $end = 0;
		for ( $i = 0; $i < $length; $i++ ) {
			$char = $text[$i];
			$last2 = substr($last2 . $char, -2);

			if ( $last2 == '{{' ) {
				$depth++;

				// Start a top level component
				if ( $depth == 1 ) {
					$start = $i - 1;

					// Save pre-text
					if ( $component = trim(substr($text, $end, $start - $end)) ) {
						$components[] = $this->createText($component);
					}
				}
			}
			elseif ( $last2 == '}}' ) {
				$depth--;

				// End a top level component
				if ( $depth == 0 ) {
					$end = $i + 1;
					if ( $component = trim(substr($text, $start, $end - $start)) ) {
						$components[] = $this->createComponent($component);
					}
				}
			}
		}

		// Save last text
		if ( $component = trim(substr($text, $end)) ) {
			$components[] = $this->createText($component);
		}

		return $components;
	}

	/**
	 *
	 */
	public function parseProperties( $text = '' ) {
		$text or $text = $this->text;

		$properties = array();

		$length = strlen($text);
		$last2 = '';
		$depth = $end = 0;
		for ( $i = 0; $i < $length; $i++ ) {
			$char = $text[$i];
			$last2 = substr($last2 . $char, -2);

			// Start sub component
			if ( in_array($last2, array('{{', '[[')) ) {
				$depth++;
			}

			// End sub component
			elseif ( in_array($last2, array('}}', ']]')) ) {
				$depth--;
			}

			// End of first level property
			elseif ( $char == '|' && $depth == 0 ) {
				$property = trim(substr($text, $end, $i - $end));
				$end = $i + 1;

				$this->createProperty($properties, $property);
			}
		}

		// Add the last property
		if ( $property = trim(substr($text, $end)) ) {
			$this->createProperty($properties, $property);
		}

		return $properties;
	}

	/**
	 *
	 */
	protected function createProperty( &$properties, $property ) {
		$equaled = explode('=', $property, 2);
		$name = trim($equaled[0]);
		if ( isset($equaled[1]) ) {
			$value = trim($equaled[1]);
			$properties[$name] = $value;
		}
		else {
			$properties[] = $name;
		}
	}

	/**
	 *
	 */
	protected function createText( $text ) {
		return new Text($text);
	}

	/**
	 *
	 */
	protected function createComponent( $text ) {
		return Component::load($text);
	}

}
