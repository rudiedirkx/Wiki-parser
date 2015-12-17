<?php

namespace rdx\wikiparser;

// Method 1: [x] create nested arrays for every component
// Method 2: [x] create Components with content[] with Components
// Method 3: [.] real stream, find complete component markup, create as whole, create subs in there
// Method 4: [ ] real stream, create Component after the first property (component type)

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
		// @todo Make this real streaming, because a | outside a {{component}} doesn't mean anything
		$parts = preg_split('#\s*({{|}}|\|)\s*#', $this->text, -1, PREG_SPLIT_DELIM_CAPTURE);

		$tree = new Component;
		$branch = $tree;
		foreach ( $parts as $part ) {
			// New component, add in current branch
			if ( $part == '{{' ) {
				$branch = $branch->add();
				$branch->newProperty();
			}

			// End component, back to previous
			elseif ( $part == '}}' ) {
				$branch = $branch->parent;
			}

			// Ignore property delimiters
			elseif ( $part == '|' ) {
				$branch->newProperty();
			}

			// Add properties (inside components) and inline text (outside components)
			elseif ( strlen($part = trim($part)) ) {
				$branch->stream($part);
			}
		}

		return $tree;
	}

}
