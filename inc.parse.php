<?php

use rdx\wikiparser\Parser;

$parser = new $Parser($wiki);

print_r($parser->structure());
