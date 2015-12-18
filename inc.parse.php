<?php

namespace demo;

use rdx\wikiparser\Document;
use rdx\wikiparser\Parser;
use rdx\wikiparser\Linker;

use demo\CustomLinker;

class CustomLinker extends Linker {
	public function articleURL( $article ) {
		return '?article=' . $article;
	}
}

$document = new Document(
	new Parser,
	new CustomLinker
);

$_time = microtime(1);
$document->parse($wiki);
$_time = microtime(1) - $_time;

echo number_format($_time * 1000, 3) . " ms\n\n";

// print_r($document);
$document->render();
