<?php

require_once('src/Operations/AbstractOperation.php');
require_once('src/Operations/BranchOperation.php');
require_once('src/Operations/CollectOperation.php');
require_once('src/Operations/ExpandOperation.php');
require_once('src/Operations/TransformOperation.php');
require_once('src/Pipeline.php');

$pipeline = new \Pvc\Pipeline;

$results = array_fill_keys([0, 1, 2], []);

$pipeline
	->branch('type', function($value) {
		return $value['type'];
	})
	->transform(function($value) {
		return $value['id'];
	})
	->collect(3, function($ids, $path) use (&$results) {
		$results[$path['type']][] = $ids;
	});

$id = 0;
for ($i = 0; $i < 20; $i ++) {
	$type = rand(0, 2);
	$pipeline->push([ 'type' => $type, 'id' => ++ $id ]);
	echo "\n\n\nADDING $id of type $type\n";
	$pipeline->show();
}

$pipeline->flush();

print_r($results);
