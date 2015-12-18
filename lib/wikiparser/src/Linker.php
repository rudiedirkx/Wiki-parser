<?php

namespace rdx\wikiparser;

class Linker {

	/**
	 *
	 */
	public function toArticle( $article, $label ) {
		$url = $this->articleURL($article);
		return '<a href="' . $url . '">' . $label . '</a>';
	}

	/**
	 *
	 */
	public function articleURL( $article ) {
		return '/wiki/' . ucfirst(str_replace(' ', '_', $article));
	}

}
