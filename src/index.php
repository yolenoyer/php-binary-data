<?php

include 'BinaryData.php';


$model = [
	[
		'name' => 'id',
		'type' => BinaryData::T_STRING,
	], [
		'name' => 'index',
		'type' => BinaryData::T_INT8,
	], [
		'name' => 'width',
		'type' => BinaryData::T_INT16,
	], [
		'name' => 'height',
		'type' => BinaryData::T_INT16,
	], [
		'type' => BinaryData::T_BITFIELD,
		'bitfield' => [
			'crop',
		],
	], [
		'name' => 'watermark',
		'type' => BinaryData::T_INT8,
	], [
		'name' => 'params',
		'type' => BinaryData::T_STRING,
		'array' => true,
	]
];





$data = [
	'id' => 'FB1234',
	'index' => 7,
	'width' => 200,
	'height' => 300,
	'crop' => false,
	'watermark' => 10,
	'params' => [ 'Clio', 'Mercedes', 'AB-413-YW' ]
];


$bd = new BinaryData($model);

$packed = $bd->pack($data);
$json = json_encode($data);

$unpacked = $bd->unpack($packed);

echo "JSON size:   " . strlen($json) . "\n";
echo "Packed size: " . strlen($packed) . "\n";

echo "Unpacked:\n";
var_dump($unpacked);



/*
 * $rgb_model = [
 *     [
 *         'name' => 'r',
 *         'type' => BinaryData::T_INT8,
 *     ], [
 *         'name' => 'g',
 *         'type' => BinaryData::T_INT8,
 *     ], [
 *         'name' => 'b',
 *         'type' => BinaryData::T_INT8,
 *     ],
 * ];
 * 
 * 
 * 
 * 
 * $bd = new BinaryData($rgb_model);
 * 
 * $data = [ 'r' => 26, 'g' => 100, 'b' => 1 ];
 * 
 * echo $bd->pack($data);
 */

