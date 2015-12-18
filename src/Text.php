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
		$linker = $this->getLinker();

		// Linked articles
		$text = preg_replace_callback('#\[\[([^\|\]]+)(?:\|([^\|\]]+))?\]\]#', function($match) use ($linker) {
			if ( isset($match[2]) ) {
				$article = $match[1];
				$label = $match[2];
			}
			else {
				$article = $label = $match[1];
			}

			return $linker->toArticle($article, $label);
		}, $text);

		// Bold & italic
		$text = preg_replace("#'''''(.+?)'''''#", '<b><i>$1</i></b>', $text);

		// Bold
		$text = preg_replace("#'''(.+?)'''#", '<b>$1</b>', $text);

		// Italic
		$text = preg_replace("#''(.+?)''#", '<i>$1</i>', $text);

		return $text;
	}

}
