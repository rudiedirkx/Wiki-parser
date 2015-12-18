<?php

$_time = microtime(1);
$document = $parser->parseDocument($wiki);
$_time = microtime(1) - $_time;

echo number_format($_time * 1000, 3) . " ms\n\n";

// print_r($document);

$document->render();
