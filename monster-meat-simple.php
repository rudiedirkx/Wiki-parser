<?php

namespace dontstarve;

use rdx\wikiparser\Document;
use rdx\wikiparser\Parser;
use rdx\wikiparser\Linker;

require 'inc.bootstrap.php';

$wiki = file_get_contents('monster-meat.wiki');

$document = new Document(
	new Parser,
	new Linker
);

$_time = microtime(1);
$document->parseSimple($wiki, array(
	'Quote' => function($properties) {
		return '<blockquote><p>' . $properties[0] . '</p><p><em>' . $properties[1] . '</em></p></blockquote>';
	},
	'pic' => function($properties) {
		return ' &lt;' . $properties[1] . '&gt; ';
	},
	'Recipe' => function($properties) {
		$operations = array_keys(array_filter($properties, function($value) {
			return $value === 'yes';
		}));
		$operations = array_map(function($operation) {
			return ' + ' . $operation;
		}, $operations);
		return '<p>&lt;' . $properties['item'] . implode($operations) . ' = ' . $properties['result'] . '&gt;</p> ';
	},
));
$_time = microtime(1) - $_time;

echo number_format($_time * 1000, 3) . " ms\n\n";

// print_r($document);
$document->render();
