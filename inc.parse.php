<?php

use rdx\wikiparser\Parser;

$_time = microtime(1);
$parser = new Parser($wiki);
$_time = microtime(1) - $_time;

echo number_format($_time * 1000, 3) . " ms\n\n";

print_r($parser->parseDocument());
