<?php

namespace Pvc;

use Pvc\Operations\AbstractOperation;

class Pipeline extends AbstractOperation {

	public function push($operand, array $path = array()) {
		if ($this->next) {
			$this->next->push($operand, $path);
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
		echo "${indent}<pipeline>\n";
		if ($this->next) {
			$this->next->show($depth + 1);
		}
		echo "${indent}</pipeline>\n";
	}

}
