<?php

namespace Pvc\Operations;

class BranchOperation extends AbstractOperation {

	protected $branchName;
	protected $callback;
	protected $branches = [];

	public function __construct($branchName, callable $callback) {
		$this->branchName = $branchName;
		$this->callback = $callback;
	}

	public function __clone() {
		parent::__clone();
		foreach ($this->branches as $discriminator => $operation) {
			$this->branches[$discriminator] = clone $operation;
		}
	}

	public function push($operand, array $path = array()) {
		$discriminator = call_user_func($this->callback, $operand);
		if (! array_key_exists($discriminator, $this->branches)) {
			$this->branches[$discriminator] = clone $this->next;
		}
		$path[$this->branchName] = $discriminator;
		$this->branches[$discriminator]->push($operand, $path);
		return $this;
	}

	public function flush(array $path = array()) {
		foreach ($this->branches as $discriminator => $operation) {
			$path[$this->branchName] = $discriminator;
			$operation->flush($path);
		}
		return $this;
	}

	public function show($depth = 0) {
		$indent = str_repeat('    ', $depth);
		echo "${indent}<branch name=\"".$this->branchName."\">\n";
		foreach ($this->branches as $discriminator => $operation) {
			echo "${indent}    <branch.suboperation discriminator=\"".$discriminator."\">\n";
			$operation->show($depth + 2);
			echo "${indent}    </branch.suboperation>\n";
		}
		echo "${indent}</branch>\n";
	}
}
