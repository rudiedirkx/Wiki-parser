<?php

spl_autoload_register(function($class) {
	if ( file_exists($file = __DIR__ . '/' . str_replace('\\', '/', str_replace('rdx\\wikiparser\\', 'src\\', $class)) . '.php') ) {
		include $file;
	}
});

header('Content-type: text/html; charset=utf-8');
