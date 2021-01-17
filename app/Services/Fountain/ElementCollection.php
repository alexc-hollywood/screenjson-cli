<?php

namespace App\Services\Fountain;

// Derived from https://github.com/alexking/Fountain-PHP/blob/php/PHP/FountainParser.php

class ElementCollection {

	public $elements;
	public $types;

	/**
	 * Add and index the element
	 * @param Element $element
	 */
	public function add_element(Element $element) {

		// Add to the element array
		$this->elements[] = $element;

		// Add to the types array for quick searching
		$this->types[] = $element->type;

	}

	/**
	 * Convenience function for creating and adding a FountainElement
	 */
	public function create_and_add_element($type, $text, $extras = array()) {

		// Create
		$element = new Element($type, $text, $extras);

		// Add to the collection
		$this->add_element($element);

	}

	/**
	 * Find the most recent element by type
	 * @param  string 	$type 	type of element
	 * @return mixed 	FountainElement or FALSE
	 */
	public function &find_last_element_of_type($type) {

		// Reverse the index
		$types = array_reverse((array) $this->types, TRUE);

		// Find the last one
		$index = array_search($type, $types, TRUE);

		// Return if successful
		if ($index) {
			return $this->elements[$index];
		} else {
			return FALSE;
		}
	}

	/**
	 * Find the last element
	 * @return mixed 	FountainElement or FALSE
	 */
	public function &last_element() {
		if ($count = count($this->elements)) {
			return $this->elements[$count - 1];
		} else {
			return FALSE;
		}
	}

	/**
	 * Return the number of elements
	 * @return int
	 */
	public function count() {
		return count($this->elements);
	}

	/**
	 * Convert to string
	 */
	public function __toString() {

		$string = "";
		foreach ((array) $this->elements as $element) {
			$string .= (string) $element . "\n";
		}

		return $string;

	}

}
