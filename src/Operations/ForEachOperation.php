<?php

namespace Pvc\Operations;

class ForEachOperation extends AbstractOperation {

	protected $callback;

	public function __construct(callable $callback) {
		$this->callback = $callback;
	}

	public function push($operand, array $path = array()) {
		call_user_func($this->callback, $operand, $path);
		if ($this->next) {
			$this->next->push($operand, $path);
		}
		return $this;
	}
}
