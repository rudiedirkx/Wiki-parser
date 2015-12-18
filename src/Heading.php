<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Document;

class Heading extends Text {

	public $level = 0;

	/**
	 *
	 */
	public function __construct( Document $document, $text, $level = 2 ) {
		$this->document = $document;
		$this->level = $level;

		// Simple heading
		if ( !is_int(strpos($text, '{{')) ) {
			parent::__construct($document, $text);
		}

		// Contains components, so parse
		else {
			$parser = $this->getParser();
			$components = $parser->parseSection($text);
			foreach ( $components as $component ) {
				$this->content[] = $component;
			}
		}
	}

	/**
	 *
	 */
	public function render() {
		echo '<h' . $this->level . '>';

		foreach ( $this->content as $content ) {
			if ( $content instanceof Renderable ) {
				echo $content->render();
			}
			else {
				echo $this->parseText($content);
			}
		}

		echo '</h' . $this->level . '>';
	}

}
