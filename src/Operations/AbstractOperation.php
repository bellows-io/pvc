<?php

namespace Pvc\Operations;

abstract class AbstractOperation {

	protected $next;

	/**
	 * Pushes an operand into the pipeline
	 * @param  mixed $operand
	 * @param  array $path
	 * @return AbstractOperation
	 */
	abstract public function push($operand, array $path = array());

	/**
	 * Flushes all data out of the operator.
	 * Usually only called at processing completion to ensure all data has been addressed
	 * @param  array  $path [description]
	 * @return AbstractOperation
	 */
	public function flush(array $path = array()) {
		if ($this->next) {
			$this->next->flush($path);
		}
		return $this;
	}

	protected function then(AbstractOperation $next) {
		$this->next = $next;
		return $next;
	}

	public function __clone() {
		if ($this->next) {
			$this->next = clone $this->next;
		}
	}

	public function branch($branchName, callable $callback) {
		return $this->then(new BranchOperation($branchName, $callback));
	}

	/**
	 * Waits for a certain number of operands to accumulate then runs a batch operation.
	 * Also responsds to flush events
	 * @param  number        $quantity The number of operands to wait for to trigger the next operation
	 * @param  callable|null $callback
	 * @return AbstractOperation
	 */
	public function collect($quantity, callable $callback = null) {
		if (is_null($callback)) {
			$callback = function($data) { return $data; };
		}
		return $this->then(new CollectOperation($quantity, $callback));
	}

	/**
	 * Evaluates all values to come through and only allows those that pass the callback
	 * @param  callable|null [$callback] Filer function. If null, evaulates on truthiness
	 * @return AbstractOperation
	 */
	public function filter(callable $callback = null) {
		if (is_null($callback)) {
			$callback = function($data) { return $data; };
		}
		return $this->then(new FilterOperation($callback));
	}

	/**
	 * Transforms a value into another
	 * @param  callable $callback
	 * @return AbstractOperation
	 */
	public function transform(callable $callback) {
		return $this->then(new TransformOperation($callback));
	}

	/**
	 * Expands an array into individual records for processing
	 * @return AbstractOperation
	 */
	public function expand() {
		return $this->then(new ExpandOperation);
	}

}
