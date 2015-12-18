<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Document;
use rdx\wikiparser\Component;
use rdx\wikiparser\Text;
use rdx\wikiparser\Heading;

class Parser {

	public $document; // rdx\wikiparser\Document
	public $text = '';

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

		$this->document = $this->createDocument();

		$eol = is_int(strpos($text, "\r\n")) ? '\r\n' : is_int(strpos($text, "\n")) ? '\n' : '\r';
		$sections = preg_split('#([' . $eol . ']{2,}|=={1,4}[' . $eol . '])#', $text);

		$document = array();
		foreach ( $sections as $section ) {
			if ( substr($section, 0, 2) == '==' ) {
				$component = $this->createText($section);
				$document[] = $component;
			}
			else {
				$components = $this->parseSection($section);
				$components[0]->sectionStart = true;
				$components[ count($components) - 1 ]->sectionEnd = true;
				foreach ( $components as $component ) {
					$document[] = $component;
				}
			}
		}

		$this->document->load($document);
		return $this->document;
	}

	/**
	 *
	 */
	public function createDocument() {
		return new Document(array(), $this);
	}

	/**
	 *
	 */
	public function parseSection( $text = '' ) {
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
		if ( preg_match('#^=(=+)#', $text, $match) ) {
			$section = trim($text, '= ');
			$heading = new Heading($this->document, $section, 1 + strlen($match[1]));
			return $heading;
		}

		return new Text($this->document, $text);
	}

	/**
	 *
	 */
	protected function createComponent( $text ) {



if ( !$this->document ) {
	print_r(debug_backtrace());
	// print_r($this);
	exit;
}



		return Component::load($this->document, $text);
	}

}
