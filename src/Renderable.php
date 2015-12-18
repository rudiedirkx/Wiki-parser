<?php

namespace rdx\wikiparser;

abstract class Renderable {

	public $document; // rdx\wikiparser\Document
	public $ignore = false;
	public $sectionStart = false;
	public $sectionEnd = false;

	/**
	 *
	 */
	protected function getParser() {
		return $this->document->getParser();
	}

	/**
	 *
	 */
	protected function getLinker() {
		return $this->document->getLinker();
	}

	/**
	 *
	 */
	abstract public function render();

	/**
	 *
	 */
	public function renderSectionStart() {
		echo '<p>';
	}

	/**
	 *
	 */
	public function renderSectionEnd() {
		echo '</p>';
	}

}
