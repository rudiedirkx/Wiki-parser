<?php

namespace dontstarve;

use rdx\wikiparser\Document;
use rdx\wikiparser\Parser;
use rdx\wikiparser\Linker;

require 'inc.bootstrap.php';
require 'inc.dont-starve.php';

$wiki = file_get_contents('food.wiki');

$document = new Document(
	new Parser,
	new Linker
);

$_time = microtime(1);
$document->parseSimple($wiki, $dontStarveComponents);
$_time = microtime(1) - $_time;

echo number_format($_time * 1000, 3) . " ms\n\n";

// print_r($document);
$document->render();
