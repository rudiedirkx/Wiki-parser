<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Renderable;
use rdx\wikiparser\Document;

class Text extends Renderable {

	public $content = array();

	/**
	 *
	 */
	public function __construct( Document $document, $text ) {
		$this->document = $document;
		$this->content[] = $text;
	}

	/**
	 *
	 */
	public function render() {
		$this->sectionStart && $this->renderSectionStart();

		foreach ( $this->content as $content ) {
			if ( $content instanceof Renderable ) {
				echo $content->render();
			}
			else {
				echo $this->parseText($content);
			}
		}

		$this->sectionEnd && $this->renderSectionEnd();
	}

	/**
	 *
	 */
	public function parseText( $text ) {
		return $this->document->parseText($text);
	}

}
