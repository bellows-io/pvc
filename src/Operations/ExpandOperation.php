<?php

namespace Pvc\Operations;

class ExpandOperation extends AbstractOperation {

	public function push($operand, array $path = array()) {
		if ($this->next) {
			foreach ($operand as $subOperand) {
				$this->next->push($subOperand, $path);
			}
		}
		return $this;
	}

	public function flush(array $path = array()) {
		if ($this->next) {
			$this->next->flush($path);
		}
		return $this;
	}

	public function show($depth = 0) {
		$indent = str_repeat('    ', $depth);
		echo "${indent}<expand>\n";
		if ($this->next) {
			$this->next->show($depth + 1);
		}
		echo "${indent}</expand>\n";
	}
}
