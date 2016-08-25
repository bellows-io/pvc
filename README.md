# PVC

This PHP library should be used to optimize batched data flow. It was created with the idea with reducing the number of round trips to and from a database or web api.

## Collect:

PVC is built with batches in mind. If reduction in round trips is what you need, then you need to make your payload bigger. Instead of querying an api one record at a time, PVC makes it easier to group objects for a payload

```php
# Batch operations of 40

$pipeline = new \Pvc\Pipeline;

$pipeline->collect(40, function($batch) {
	echo "Batch size: ".count($batch)."\n";
});

for ($i = 0; $i < 100; $i++) {
	$pipeline->push($i);
}
$pipeline->flush();

// Batch size: 40
// Batch size: 40
// Batch size: 20

```

## Branch

If you have data that needs to be split into more than one pathway, PVC can help.

```php
# Branched batches

$pipeline = new \Pvc\Pipeline;
$pipeline
	->branch('switch', function($datum) {
		return $datum['type'];
	})
	// the second argument is always the path this data took
	->collect(30, function($data, $path) {
		echo "For type ".$path['switch'].": ".count($data)." records\n";
	});

$type = 0;
for ($i = 0; $i < 100; $i++) {
	$pipeline->push(['type' => ($type++) % 3, 'id' => $i ]);
};
$pipeline->flush();

// For type 0: 30
// For type 1: 30
// For type 2: 30
// For type 0: 4
// For type 1: 3
// For type 2: 3
```

## Transform

Transforming data mid way may be useful for your application, so PVC provides a `transform` operation, which allows you to change the data mid-stream.

```php

$pipeline = new \Pvc\Pipeline;
$pipeline->transform(function($data) use (&$lookupTable) {
	return $lookupTable[$data->getId()];
});

```

## Filter

You can filter out undesirable or irrelevant data with the use of the `filter` operation.

```php

$pipeline = new \Pvc\Pipeline;
$pipeline->filter(function($data) use (&$lookupTable) {
	return $data instanceof GoodData;
});

```

## Expand

The opposite of collect. When the data in your stream is an array, `expand` will iterate over that array and make subsequent operations treat that data
