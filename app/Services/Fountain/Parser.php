<?php

namespace App\Services\Fountain;

// Derived from https://github.com/alexking/Fountain-PHP/blob/php/PHP/FountainParser.php

/**
 * FountainParser
 * Based off the FastFountainParser.m
 *
 * @author Alex King (PHP port)
 * @author Nima Yousefi & John August (original Objective-C version)
 */
class Parser {

	/**
	 * Parse a string into a collection of elements
	 *
	 * @todo add > centering < support
	 * @todo improve transition handling (when surrounded by blank lines)
	 * @todo add title parsing
	 *
	 * @param  string $contents Fountain formated text
	 */
	public function parse($contents) {

		// Trim newlines from the document
		$contents = trim($contents);

		// Convert \r\n or \r style newlines to \n
		$contents = preg_replace("/\r\n|\r/", "\n", $contents);

		// Add two line breaks to the end of the page (ref FastFountainParser.m:53)
		$contents .= "\n\n";



		// Keep track of preceding newlines
		$newlines_before = 0;
		$newline = FALSE;

		// Keep track of whether we are inside a comment block, and what its text is
		$comment_block = FALSE;
		$comment_text = "";

		// Keep track of whether we are inside a dialog block
		$dialog_block = FALSE;

		// Break into lines
		$lines = explode("\n", $contents);

		// Process each line
		foreach ($lines as $line_number => $line) {

			// Reset the newline count if necessary
        	if (!$newline) {
        		$newlines_before = 0;
        	}

			// Check for a blank line (is empty, or has whitespace characters)
			if (($line == "" || preg_match("/^\s*$/", $line)) && !$comment_block) {

				// Blank lines end dialog blocks
				$dialog_block = FALSE;

				// Increment newline count
				$newlines_before ++;
				$newline = TRUE;

				// No further processing of this line is needed
				continue;

			} else {

				// Note that this isn't a newline
				$newline = FALSE;
			}


			// Comment Blocks
			// Check whether a comment starts or ends on this line
			$comment_start = preg_match("/^\/\*/", $line);
			$comment_end = preg_match("/\*\/\s*$/", $line);

			// If this is the start, middle, or end of a comment block
			if ($comment_start || $comment_end || $comment_block) {

				// If it starts on this line
				if ($comment_start) {

					// Note this as the start of a comment block
					$comment_block = TRUE;

				}

				// If the comment continues on this line
				if ($comment_block) {

					// Add this line to the comment text
					$comment_text .= "$line\n";

				}

				// If the comment ends on this line
				if ($comment_end) {

					// Remove /* and */
					$comment_text = str_replace(array("/*", "*/"), "", $comment_text);

					// Add a Boneyard Element
					$this->add_element("Boneyard", $comment_text);

					// Note that we aren't in a comment anymore and reset the text
					$comment_block = FALSE;
					$comment_text = "";

				}

				// No further processing of this line is needed
				continue;

			}

			// Page Breaks (===)
			if (preg_match("/^={3,}\s*$/", $line)) {

				// Add a page break element
				$this->add_element("Page Break", $line);

				continue;
			}

			// Synopsis - if there aren't any preceding newlines, and there's a "="
			if ($newlines_before && substr(trim($line), 0, 1) == "=") {

				// Find the text of the synopsis
				preg_match("/^\s*={1}(.*)/", $line, $matches);

				// Add a synopsis element
				$this->add_element("Synopsis", $matches[1]);

				continue;
			}

			// Comment [[ ]] start spaces [[ spaces capture
			if ($newlines_before && preg_match("/^\s*\[{2}\s*([^\]\n])+\s*\]{2}\s*$/", $line)) {

				// Trim whitespace and [[ ]]
				$text = trim(str_replace(array("[[", "]]"), "", $line));

				// Add a comment element
				$this->add_element("Comment", $text);

				continue;
			}

			// Section heading - check if this line starts with a #
			if ($newlines_before && substr(trim($line), 0, 1) == "#") {

				// Find the number of # (##, ###, etc.) and the text
				preg_match("/^\s*(#+)\s*(.*)/", $line, $matches);
				list ($raw, $depth, $text) = $matches;

				// Convert depth to a number
				$depth = strlen($depth);

				// Add a Section Heading
				$this->add_element("Section Heading", $text, array(
					"depth" => $depth
				));

				continue;

			}

			// Scene Headings
			// Check if this is a forced or normal scene heading
			$forced_scene_heading = preg_match("/^\.[^\.]/", $line);
			$scene_heading = preg_match("/^(INT|EXT|EST|I\/??E)[\.\-\s]/i", $line, $scene_heading_matches);

			if ($forced_scene_heading || $scene_heading) {

				// Remove the prefix
				if ($forced_scene_heading) {
					$prefix_length = 1;
				} else {
					$prefix_length = strlen($scene_heading_matches[0]);
				}

				$line_without_prefix = substr($line, $prefix_length);

				// Find the text and optional scene number
				if (preg_match("/^(.*?)(?:|\s*#([^\n#]*?)#\s*)$/", $line_without_prefix, $matches)) {

					list($raw, $text, $scene_number) = $matches;

					// Add a scene heading element
					$this->add_element("Scene Heading", $text, array(
						"scene_number" => $scene_number,
					));

				}

				continue;
			}

			// Transition - check whether it is in a list of transitions
			$transitions = array(
				"CUT TO:",
				"FADE OUT.",
				"SMASH CUT TO:",
				"CUT TO BLACK.",
				"MATCH CUT TO:"
			);

			if (in_array(trim($line), $transitions)) {

				// Add a transition element
				$this->add_element("Transition", $line);

				continue;
			}

			// Forced Transition - check whether the line starts with > (and doesn't end with <, which would make it a centered action)
			if (substr($line, 0, 1) == ">" && substr($line, strlen($line) - 1) != "<") {

				// Find the text
				$text = substr($line, 1);

				// Add a transition element
				$this->add_element("Transition", $text);

				continue;
			}


   			// Character - check if there is a newline preceding, and consists of entirely uppercase characters
   			if ($newlines_before && preg_match("/^[^a-z]+$/", $line)) {

   				// Make sure the next line isn't blank or non-existent
   				if (isset($lines[$line_number + 1]) && $lines[$line_number + 1] != "") {

   					// This is a character, check if it's dual dialog
   					$dual_dialog = FALSE;

   					if (preg_match("/\^\s*$/", $line)) {

   						// It is dual dialog,
   						$dual_dialog = TRUE;

   						// Check for a previous character - grab it by reference if it exists
   						if ($previous_character = &$this->elements()->find_last_element_of_type("Character")) {

   							// Set it to dual dialog
   							$previous_character->dual_dialog = TRUE;

   						}

   					}

   					// Add a character element
   					$this->add_element("Character", $line, array(
   						"dual_dialog" => $dual_dialog
   					));

   					// Note that we're within a dialog block
   					$dialog_block = TRUE;

   					continue;
   				}
   			}

   			// Dialog (and Parentheticals) - check if we're inside a dialog block
   			if ($dialog_block) {

   				// Check if there are newlines preceding, and if there is a (
   				if (!$newlines_before && preg_match("/^\s*\(/", $line)) {

   					// Add a parenthetical element
   					$this->add_element("Parenthetical", $line);

   				} else {

   					// Check if the previous element was dialogue
   					$last_element = &$this->elements()->last_element();
   					if ($last_element->type == "Dialogue") {

   						// The previous element was dialogue, so we'll combine the text and set it
   						$last_element->text .= "\n" . $line;

   					} else {

   						// Create a new dialogue element
   						$this->add_element("Dialogue", $line);

   					}

   				}

   				continue;

   			}


   			// If there were no newlines, and this isn't our first element
   			if (!$newlines_before && $this->elements()->count()) {

   				// Find the previous element
   				$last_element = &$this->elements()->last_element();

   				// Add this line to it and save it back
   				$last_element->text .= "\n" . $line;

   				continue;

   			} else {

   				// Add an action element
   				$this->add_element("Action", $line);

   				continue;
   			}


		}

	}

	public function parse_file($filepath) {

		// Load the file, and parse
		$this->parse(file_get_contents($filepath));

	}

	/**
	 * Element Collection
	 * @var ElementCollection
	 */
	protected $_elements;

	public function elements() {

		if (!$this->_elements) {
			$this->_elements = new ElementCollection;
		}

		return $this->_elements;
	}

	public function add_element($type, $text, $extras = array()) {

		$this->elements()->create_and_add_element($type, $text, $extras);
	}



}
