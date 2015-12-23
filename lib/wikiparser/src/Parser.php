<?php

namespace rdx\wikiparser;

use Exception;

class ParsingException extends Exception {}

class Parser {

	const TOKEN_PARAGRAPH = '---PARAGRAPH|||';

	/**
	 *
	 */
	public function parse( $text ) {
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

}
