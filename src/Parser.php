<?php

namespace rdx\wikiparser;

use rdx\wikiparser\Component;

class Parser {

	protected $text = '';

	/**
	 *
	 */
	public function __construct( $text ) {
		$this->text = $text;
	}

	/**
	 *
	 */
	public function structure() {
		$modules = $this->modules();
	}

	/**
	 *
	 */
	public function modules() {
		$parts = preg_split('#({{|}})#', $this->text, -1, PREG_SPLIT_DELIM_CAPTURE);

		$tree = array();
		$location = array();
		foreach ( $parts as $part ) {
			// Get current branch in $tree from $location
			if ( $location ) {
				eval('$branch = &$tree[' . implode('][', $location) . '];');
			}
			else {
				$branch = &$tree;
			}

			if ( $part == '{{' ) {
				// Create new branch IN current branch and extend location
				$branch[] = array();
				$location[] = count($branch) - 1;
			}
			elseif ( $part == '}}' ) {
				// End branch, by shortening the current location
				array_pop($location);
			}
			elseif ( strlen($part = trim($part)) ) {
				// Add parsed component to current branch
				$branch[] = Component::load($part);
			}

			unset($branch);
		}

print_r($tree);
print_r($parts);
		return $tree;
	}

}
