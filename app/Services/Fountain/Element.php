<?php

namespace App\Services\Fountain;

// Derived from https://github.com/alexking/Fountain-PHP/blob/php/PHP/FountainParser.php

class Element {

	public $type;
	public $text;
	public $extras;

	/**
	 * Construct a new FountainElement
	 * @param string  $type   Character, Dialog, etc.
	 * @param string  $text   Text for the element
	 * @param array   $extras Additional properties
	 */
	public function __construct($type, $text, $extras = array()) {

		// Assign the type and text
		$this->type = $type;
		$this->text = $text;

		// Assign the extras
		if (count($extras)) {
			foreach ((array) $extras as $key => $value) {
				$this->{$key} = $value;
			}
		}

	}

	/**
	 * Convert to String
	 * @return string
	 */
	public function __toString() {
		$string .= strtoupper($this->type) . ":" . $this->text;

		if ($this->dual_dialog) {
			$string .= "(DUAL)";
		}

		return $string;
	}

}
