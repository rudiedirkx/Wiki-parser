<?php

namespace dontstarve;

require 'inc.bootstrap.php';
require 'inc.dont-starve.php';

$wiki = file_get_contents('monster-meat.wiki');
include 'inc.parse.php';
