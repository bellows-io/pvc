<?php

namespace Pvc\Operations;

class TransformOperation extends AbstractOperation {

	protected $callback;

	public function __construct(callable $callback) {
		$this->callback = $callback;
	}

	public function push($operand, array $path = array()) {
		$results = call_user_func($this->callback, $operand, $path);
		if ($this->next) {
			$this->next->push($results, $path);
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
		echo "${indent}<transform>\n";
		if ($this->next) {
			$this->next->show($depth + 1);
		}
		echo "${indent}</transform>\n";
	}
}
