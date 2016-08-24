<?php

namespace Pvc\Operations;

class CollectOperation extends AbstractOperation {

	protected $size;
	protected $callback;
	protected $operands = [];

	public function __construct($size, callable $callback) {
		$this->size = $size;
		$this->callback = $callback;
	}

	public function __clone() {
		parent::__clone();
		$this->operands = [];
	}

	public function push($operand, array $path = array()) {
		$this->operands[] = $operand;
		if (count($this->operands) >= $this->size) {
			$results = call_user_func($this->callback, $this->operands, $path);
			if ($this->next) {
				$this->next->push($results, $path);
			}
			$this->operands = [];
		}
		return $this;
	}

	public function flush(array $path = array()) {
		$results = call_user_func($this->callback, $this->operands, $path);
		if ($this->next) {
			$this->next->flush($results, $path);
		}
		$this->operands = [];
		return $this;
	}

	public function show($depth = 0) {
		$indent = str_repeat('    ', $depth);
		echo "${indent}<collection size=\"".$this->size."\">\n";
		foreach ($this->operands as $i => $operand) {
			echo "${indent}    <collection.operand>".json_encode($operand)."</collection.operand>\n";
		}

		if ($this->next) {
			$this->next->show($depth + 1);
		}
		echo "${indent}</collection>\n";
	}
}
