<?php

namespace demo;

use rdx\wikiparser\Parser;

header('Content-type: text/plain; charset=utf-8');

$parser = new Parser;
$parser->parseDocument($wiki);
