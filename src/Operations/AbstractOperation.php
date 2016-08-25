<?php

namespace Pvc\Operations;

abstract class AbstractOperation {

	protected $next;

	public function getNext() {
		return $this->next;
	}

	public function setNext(AbstractOperation $next) {
		$this->next = $next;
		return $this;
	}

	/**
	 * Pushes an operand into the pipeline
	 * @param  mixed $operand
	 * @param  array $path
	 * @return AbstractOperation
	 */
	abstract public function push($operand, array $path);

	/**
	 * Flushes all data out of the operator.
	 * Usually only called at processing completion to ensure all data has been addressed
	 * @param  array  $path [description]
	 * @return AbstractOperation
	 */
	public function flush(array $path) {
		if ($this->next) {
			$this->next->flush($path);
		}
		return $this;
	}

	public function __clone() {
		if ($this->next) {
			$this->next = clone $this->next;
		}
	}

}
