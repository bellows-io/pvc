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
}
