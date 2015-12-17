<?php

require 'inc.bootstrap.php';

$wiki = file_get_contents('monster-meat.wiki');
$parser = new $Parser($wiki);

print_r($parser->structure());
