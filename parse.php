<?php

namespace demo;

use rdx\wikiparser\Parser;

require 'inc.bootstrap.php';

if ( empty($_GET['wiki']) || !file_exists($file = __DIR__ . '/' . basename($_GET['wiki']) . '.wiki') ) {
	exit('<p>Choose a wiki: <code>?wiki=XXXX</code>, one of:</p><ul>' . implode(array_map(function($file) {
		return '<li>' . substr(basename($file), 0, -5) . '</li>';
	}, glob(__DIR__ . '/*.wiki'))) . '</ul>');
}

header('Content-type: text/plain; charset=utf-8');

$wiki = file_get_contents($file);

$parser = new Parser;
$parser->parse($wiki);
