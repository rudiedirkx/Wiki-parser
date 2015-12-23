<?php

namespace rdx\wikiparser;

use Exception;
// use rdx\wikiparser\Component;
// use rdx\wikiparser\Text;
// use rdx\wikiparser\Heading;

class ParsingException extends Exception {}

class Parser {

	const TOKEN_PARAGRAPH = '---PARAGRAPH|||';

	public $document; // rdx\wikiparser\Document
	public $text = '';

	/**
	 *
	 */
	public function __construct( $text = '' ) {
		$this->text = trim($text);
	}

	/**
	 * v3
	 */
	public function streamParse( $text ) {
		// {{ ... }}
		// [[ ... ]]
		// {| ... |}
		// == ... ==
		// EOL.EOL ...
		// * ...

		$eol = is_int(strpos($text, "\r\n")) ? '\r\n' : is_int(strpos($text, "\n")) ? '\n' : '\r';

		$delims = [
			'{|' => 'table',
			'|}' => 'table',
			'{{' => 'component',
			'}}' => 'component',
			'[[' => 'element',
			']]' => 'element',
		];

		$literalDelimiters = array_map('preg_quote', array_keys($delims));
		$regexDelimiters = ['(?<=[\r\n])\*\s*', '={2,}', '(?:' . $eol . '){2,}'];
		$delimiters = array_merge($literalDelimiters, $regexDelimiters);

		$regex = '/(' . implode('|', $delimiters) . ')/';
		$parts = preg_split($regex, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		$parts = array_filter($parts, function($part) {
			return strlen($part);
		});
		$parts = array_values($parts);

		// class Text {}
		// class Paragraph {}

		$stack = [];
		$document = [''];
		for ( $i = 0; $i < count($parts); $i++ ) {
			$part = $parts[$i];

			$type = @$delims[$part] ?: '';
			$curType = $stack ? $stack[ count($stack) - 1 ] : '';

			// Paragraph match if only newlines, can't trim, because spaces are NOT special
			if ( preg_match('#^[\r\n]+$#', $part) ) {
				$part = self::TOKEN_PARAGRAPH;
			}

			switch ( trim($part) ) {
				// Open component
				case '{{':
				case '{|':
				case '[[':
					$stack[] = $type;
					if ( count($stack) == 1 ) {
						$document[] = '';
// echo "start, new component\n";
					}

					$document[ count($document) - 1 ] .= $part;
					break;

				// Close component
				case '}}':
				case '|}':
				case ']]':
					if ( $curType != $type ) {
						throw new ParsingException(sprintf("Closing a `%s`, but the current type is `%s`.", $type, $curType));
					}

					$document[ count($document) - 1 ] .= $part;

					array_pop($stack);
					if ( count($stack) == 0 ) {
						$document[] = '';
// echo "end, new component\n";
					}
					break;

				// New paragraph
				case self::TOKEN_PARAGRAPH:
					$document[] = '';
// echo "new paragraph\n";
					break;

				// Heading
				case '==':
				case '===':
				case '====':
				case '=====':
					// Close heading
					if ( $curType == 'heading' ) {
						$document[ count($document) - 1 ] .= $part;

						array_pop($stack);
						if ( count($stack) == 0 ) {
							$document[] = '';
// echo "close heading, new component\n";
						}
					}
					// Open heading
					else {
						$stack[] = 'heading';
						if ( count($stack) == 1 ) {
							$document[] = '';
// echo "start heading, new component\n";
						}

						$document[ count($document) - 1 ] .= $part;
					}
					break;

				// List item
				case '*':
					$document[] = '* ';
// echo "new component, list item\n";
					break;

				// Add ... to the current element
				default:
					$document[ count($document) - 1 ] .= $part;
					break;
			}
		}

		// $document = array_map('trim', $document);
		// $document = array_filter($document, 'strlen');
		// $document = array_values($document);

print_r($document);

		// @todo Do another round to glue together paragraph parts..? How to distinguish between
		// `__NOTOC__ + {{Food Infobox` (new P) and `And another with [[complex words]]` (same P) ??
		// Direct stream parse elements could be block or inline...

	}

	/**
	 * v2
	 */
	public function parseDocumentSimple( $text = '', $allow = array() ) {
		$text or $text = $this->text;

		$parts = preg_split('#(\{\{|\}\}|\[\[|\]\])#', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

		// Strip components
		$discarding = 0;
		$opening = false;
		foreach ( $parts as $i => $part ) {
			switch ( $part ) {
				case '{{':
					if ( $discarding ) {
						$discarding++;

						unset($parts[$i]);
					}
					else {
						$opening = true;
					}
					break;

				case '}}';
					if ( $discarding ) {
						$discarding--;

						unset($parts[$i]);
					}

					break;

				default:
					if ( $discarding ) {
						unset($parts[$i]);
					}
					elseif ( $opening ) {
						$opening = false;
						$type = trim(explode('|', $part)[0]);
						if ( !isset($allow[$type]) ) {
							$discarding++;

							unset($parts[$i], $parts[$i-1]);
						}
					}
					break;
			}
		}

		// Replace tables
		$text = preg_replace_callback('#\{\|[\s\S]+?\|\}#', function($match) {

		}, $text);

		// Replace simple components
		$text = implode($parts);
		$text = preg_replace_callback('#\{\{[\s\S]+?\}\}#', function($match) use ($allow) {
			$component = substr(trim($match[0]), 2, -2);
			$parts = array_map('trim', explode('|', $component));
			$type = array_shift($parts);

			$properties = array();
			foreach ($parts as $property) {
				$this->createProperty($properties, $property);
			}

			return call_user_func($allow[$type], $properties, $type);
		}, $text);

		// Create renderable paragraphs
		$sections = preg_split('#[\r\n]+#', $text);
		$document = array();
		$list = null;
		foreach ( $sections as $section ) {
			if ( $section = trim($section) ) {
				if ( preg_match('#^=(=+)#', $section, $match) ) {
					$title = trim($section, '= ');
					$component = $this->document->createHeading($title, strlen($match[1]) + 1);

					$list = null;
				}
				elseif ( $section[0] == '*' ) {
					if ( !$list ) {
						$component = $list = $this->document->createList();
					}
					else {
						$component = null;
					}
					$list->content[] = ltrim($section, '* ');
				}
				else {
					$component = $this->document->createParagraph($section);

					$list = null;
				}

				$component and $document[] = $component;
			}
		}

		return $document;
	}

	/**
	 * v1
	 */
	public function parseDocument( $text = '' ) {
		$text or $text = $this->text;

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

		return $document;
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
		return $this->document->createText($text);
	}

	/**
	 *
	 */
	protected function createComponent( $text ) {
		return $this->document->createComponent($text);
	}

}
