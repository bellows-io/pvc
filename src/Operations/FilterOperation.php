<?php

namespace Pvc\Operations;

class FilterOperation extends AbstractOperation {

	protected $callback;

	public function __construct(callable $callback) {
		$this->callback = $callback;
	}

	public function push($operand, array $path = array()) {
		$results = call_user_func_array($this->callback, [ $operand, $path ]);
		if ($results && $this->next) {
			$this->next->push($operand, $path);
		}
		return $this;
	}
}
