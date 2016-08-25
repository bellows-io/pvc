<?php

namespace Pvc\Renderers;

use Pvc\Pipeline;
use Pvc\Operations\AbstractOperation;
use Pvc\Operations\BranchOperation;
use Pvc\Operations\CollectOperation;
use Pvc\Operations\ExpandOperation;
use Pvc\Operations\FilterOperation;
use Pvc\Operations\TransformOperation;

class XmlRenderer {

	protected $indentation;
	protected $classHandlers = [];

	public function __construct($indentation = "\t") {
		$this->indentation = $indentation;
	}

	public function registerClassHandler($className, $callback) {
		$this->classHandlers[$className] = $callback;
		return $this;
	}

	public static function loadStandardHandlers(XmlRenderer $renderer) {
		return $renderer
			->registerClassHandler(Pipeline::class, function(Pipeline $pipeline, $prefix) use ($renderer) {
				return $renderer->renderNode("pipeline", $pipeline, $prefix);
			})
			->registerClassHandler(BranchOperation::class, function(BranchOperation $branch, $prefix) use ($renderer) {
				return $renderer->renderNode("branch", $branch, $prefix, false, ['name' => 'branchName'], ['suboperation' => 'branches']  );
			})
			->registerClassHandler(CollectOperation::class, function(CollectOperation $collect, $prefix) use ($renderer) {
				return $renderer->renderNode("collect", $collect, $prefix, true, [ 'size' => 'size' ], [ 'operand' => 'operands' ]);
			})
			->registerClassHandler(ExpandOperation::class, function(ExpandOperation $expand, $prefix) use ($renderer) {
				return $renderer->renderNode('expand', $expand, $prefix);
			})
			->registerClassHandler(FilterOperation::class, function(FilterOperation $filter, $prefix) use ($renderer) {
				return $renderer->renderNode('filter', $filter, $prefix);
			})
			->registerClassHandler(TransformOperation::class, function(TransformOperation $transform, $prefix) use ($renderer) {
				return $renderer->renderNode('transform', $transform, $prefix);
			});
	}

	public static function makeStandard($indentation = "\t") {
		return self::loadStandardHandlers(new self($indentation));
	}

	protected static function getProtectedValue($obj, $name) {
		$array = (array)$obj;
		$prefix = chr(0).'*'.chr(0);
		return $array[$prefix.$name];
	}

	public function render(Pipeline $pipeline) {
		return $this->renderWithPrefix($this->getProtectedValue($pipeline, 'source'), '', $this->indentation);
	}

	public function renderWithPrefix(AbstractOperation $operation, $prefix) {

		$targetClass = get_class($operation);
		foreach ($this->classHandlers as $className => $handler) {
			if ($className == $targetClass) {
				return call_user_func_array($handler, [ $operation, $prefix, $this->indentation ]);
			}
		}

		throw new \Exception("Unsupported operation type: $targetClass");

	}

	public function renderNode($tag, AbstractOperation $operation, $prefix, $showNext = true, $attributes = [], $subElements = []) {
		$str = '';
		$str .= $prefix.'<'.$tag;
		foreach ($attributes as $key => $source) {
			$value = $this->getProtectedValue($operation, $source);
			$str .= sprintf(' %s="%s"', $key, $value);
		}
		$str .= ">\n";

		foreach ($subElements as $type => $source) {
			$values = $this->getProtectedValue($operation, $source);
			foreach ($values as $key => $value) {
				$str .= $prefix.$this->indentation."<$tag.$type name=\"$key\">\n";
				if ($value instanceof AbstractOperation) {
					$str .= $this->renderWithPrefix($value, $prefix.$this->indentation.$this->indentation);
				} else {
					$str .= $prefix.$this->indentation.$this->indentation.json_encode($value)."\n";
				}
				$str .= $prefix.$this->indentation."</${tag}.${key}>\n";
			}
		}

		if ($showNext && $next = $this->getProtectedValue($operation, 'next')) {
			$str .= $this->renderWithPrefix($next, $prefix.$this->indentation);
		}

		$str .= "$prefix</${tag}>\n";
		return $str;
	}
}
