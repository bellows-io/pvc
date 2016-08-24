<?php

namespace Pvc\Operations;

abstract class AbstractOperation {

	protected $next;

	abstract public function push($operand, array $path = array());
	abstract public function flush(array $path = array());
	abstract public function show($depth = 0);

	protected function setNext(AbstractOperation $next) {
		$this->next = $next;
		return $next;
	}

	public function __clone() {
		if ($this->next) {
			$this->next = clone $this->next;
		}
	}

	public function branch($branchName, callable $callback) {
		return $this->setNext(new BranchOperation($branchName, $callback));
	}

	public function collect($quantity, callable $callback = null) {
		if (is_null($callback)) {
			$callback = function($data) { return $data; };
		}
		return $this->setNext(new CollectOperation($quantity, $callback));
	}

	/**
	 * Transforms a value into another
	 * @param  callable $callback
	 * @return AbstractOperation
	 */
	public function transform(callable $callback) {
		return $this->setNext(new TransformOperation($callback));
	}

	/**
	 * Expands an array into individual records for processing
	 * @return AbstractOperation
	 */
	public function expand() {
		return $this->setNext(new ExpandOperation);
	}

}
