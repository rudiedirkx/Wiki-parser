<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Parser;
use rdx\wikiparser\Linker;

use rdx\wikiparser\Component;
use rdx\wikiparser\Text;
use rdx\wikiparser\Heading;
use rdx\wikiparser\Paragraph;
use rdx\wikiparser\UList;

class Document {

	public $parser; // rdx\wikiparser\Parser
	public $linker; // rdx\wikiparser\Linker

	public $content = array();

	/**
	 *
	 */
	public function __construct( Parser $parser, Linker $linker ) {
		$this->parser = $parser;
		$this->parser->document = $this;

		$this->linker = $linker;
		$this->linker->document = $this;
	}

	/**
	 *
	 */
	public function parse( $text ) {
		$content = $this->parser->parseDocument($text);
		$this->load($content);
	}

	/**
	 *
	 */
	public function parseSimple( $text, $allow = array() ) {
		$content = $this->parser->parseDocumentSimple($text, $allow);
		$this->load($content);
	}

	/**
	 *
	 */
	public function load( array $content ) {
		$this->content = $content;
	}

	/**
	 *
	 */
	public function render() {
		foreach ( $this->content as $component ) {
			$component->render();
		}
	}

	/**
	 *
	 */
	public function createParagraph( $text ) {
		return new Paragraph($this, $text);
	}

	/**
	 *
	 */
	public function createList() {
		return new UList($this);
	}

	/**
	 *
	 */
	public function createText( $text ) {
		if ( preg_match('#^=(=+)#', $text, $match) ) {
			$section = trim($text, '= ');
			return $this->createHeading($section, strlen($match[1]) + 1);
		}

		return new Text($this, $text);
	}

	/**
	 *
	 */
	public function createHeading( $text, $level ) {
		return new Heading($this, $text, $level);
	}

	/**
	 *
	 */
	public function createComponent( $text ) {
		return Component::load($this, $text);
	}

	/**
	 *
	 */
	public function parseText( $text ) {
		// Linked articles
		$text = preg_replace_callback('#\[\[([^\|\]]+)(?:\|([^\|\]]+))?\]\]#', function($match) {
			if ( isset($match[2]) ) {
				$article = $match[1];
				$label = $match[2];
			}
			else {
				$article = $label = $match[1];
			}

			return $this->linker->toArticle($article, $label);
		}, $text);

		// Bold & italic
		$text = preg_replace("#'''''(.+?)'''''#", '<b><i>$1</i></b>', $text);

		// Bold
		$text = preg_replace("#'''(.+?)'''#", '<b>$1</b>', $text);

		// Italic
		$text = preg_replace("#''(.+?)''#", '<i>$1</i>', $text);

		// Lists
		// $text = preg_replace('#(^|[\r\n])\*#', '<br>* ', $text);

		return $text;
	}

}
