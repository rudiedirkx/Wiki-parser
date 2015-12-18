<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Parser;
use rdx\wikiparser\Linker;

class Document {

	public $parser; // rdx\wikiparser\Parser
	public $content = array();

	/**
	 *
	 */
	public function __construct( array $content = array(), Parser $parser = null ) {
		if ( $content ) {
			$this->load($content);
		}

		if ( $parser ) {
			$this->parser = $parser;
		}
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
	public function getParser() {
		return $this->parser ?: new Parser;
	}

	/**
	 *
	 */
	public function getLinker() {
		return new Linker;
	}

}
