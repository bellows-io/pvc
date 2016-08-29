<?php

namespace Pvc\Operations;

class MergeOperation extends AbstractOperation {

	public function __clone() {
		// don't clone. You merge.
	}

	public function push($operand, array $path = array()) {
		if ($this->next) {
			$this->next->push($operand, []);
		}
		return $this;
	}

	public function flush(array $path = array()) {
		if ($this->next) {
			$this->next->flush($path);
		}
		return $this;
	}
}
