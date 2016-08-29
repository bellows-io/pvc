<?php

namespace Pvc;

use Pvc\Operations\AbstractOperation;
use Pvc\Operations\BranchOperation;
use Pvc\Operations\CollectOperation;
use Pvc\Operations\ExpandOperation;
use Pvc\Operations\FilterOperation;
use Pvc\Operations\MergeOperation;
use Pvc\Operations\TransformOperation;

class Pipeline {

	/** @var AbstractOperation|null The first operation in the pipeline. Null if no operations */
	protected $source;
	/** @var AbstractOperation|null The last operation in the pipeline. Null if no operations */
	protected $mouth;

	/**
	 * Adds an operand to the pipeline.
	 * @param  mixed    $operand
	 * @return Pipeline
	 */
	public function push($operand) {
		if ($this->source) {
			$this->source->push($operand, []);
		}
		return $this;
	}

	/**
	 * Executes any remaining data in the pipeline
	 * @return Pipeline
	 */
	public function flush() {
		if ($this->source) {
			$this->source->flush([]);
		}
		return $this;
	}

	/**
	 * Adds another operation to the pipeline.
	 * @param  AbstractOperation $next
	 * @return Pipeline
	 */
	protected function then(AbstractOperation $next) {
		if (is_null($this->source)) {
			$this->source = $next;
			$this->mouth = $next;
		} else {
			$this->mouth->setNext($next);
			$this->mouth = $next;
		}
		return $this;
	}

	/**
	 * Sends data various ways down the pipeline to duplicate paths.
	 * The callback returns the discriminator which identifies the path taken at $branchName
	 * @param  string   $branchName The name of the branch
	 * @param  callable $callback   The discriminator function
	 * @return Pipeline
	 */
	public function branch($branchName, callable $callback) {
		return $this->then(new BranchOperation($branchName, $callback));
	}

	/**
	 * All branched data will be merged together here.
	 * @return Pipeline
	 */
	public function merge() {
		return $this->then(new MergeOperation);
	}

	/**
	 * Waits for a certain number of operands to accumulate then runs a batch operation.
	 * Also responsds to flush events
	 * @param  number        $quantity The number of operands to wait for to trigger the next operation
	 * @param  callable|null $callback
	 * @return Pipeline
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
	 * @return Pipeline
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
	 * @return Pipeline
	 */
	public function transform(callable $callback) {
		return $this->then(new TransformOperation($callback));
	}

	/**
	 * Expands an array into individual records for processing
	 * @return Pipeline
	 */
	public function expand() {
		return $this->then(new ExpandOperation);
	}

}
