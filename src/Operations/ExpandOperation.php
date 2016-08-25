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
}
