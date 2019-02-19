<?php

/**
 * Pack and unpack data following a binary model.
 */
class BinaryData
{
	/** Data types */
	const T_STRING = 1;
	const T_INT8 = 2;
	const T_INT16 = 3;
	const T_BITFIELD = 4;

	public function __construct(array $model) {
		$this->model = $model;
	}

	public function pack(array $data): string
	{
		// Initialize the function output
		$pack = '';

		// Build the output in a sequential way, following the model
		foreach ($this->model as $modelField) {

			// Handle the special T_BITFIELD type
			if ($modelField['type'] === self::T_BITFIELD) {
				$bitNames = $modelField['bitfield'];
				$bitField = 0x00;
				foreach ($bitNames as $n => $bitName) {
					$bitValue = !!($data[$bitName] ?? false);
					$bitField |= $bitValue << $n;
				}
				$pack .= chr($bitField);
				continue;
			}

			// Handle all other types:

			// Get the value to pack
			$value = $data[$modelField['name']];

			// Is it an array ?
			$isArray = $modelField['array'] ?? false;
			if ($isArray) {
				// Push the size of the array (1 byte = max size: 255)
				$pack .= chr(count($value));
				// Push each value of the array
				foreach ($value as $val) {
					$pack .= $this->packValue($modelField, $val);
				}
			}
		   	else { // Not an array ?
				// Push the single value
				$pack .= $this->packValue($modelField, $value);
			}
		}

		return $pack;
	}

	public function unpack(string $data): array
	{
		$unpack = [];
		$I = 0;
		foreach ($this->model as $modelField) {
			if ($modelField['type'] === self::T_BITFIELD) {
				$bitNames = $modelField['bitfield'];
				$bitField = ord($data[$I]);
				foreach ($bitNames as $n => $bitName) {
					$value = !!($bitField & (1 << $n));
					$unpack[$bitName] = $value;
				}
				++$I;
				continue;
			}

			$isArray = $modelField['array'] ?? false;
			$name = $modelField['name'];

			if ($isArray) {
				$unpack[$name] = [];
				$count = ord($data[$I++]);
				for ($i = 0; $i < $count; $i++) {
					$unpack[$name][] = $this->unpackValue($modelField, $data, $I);
				}
			} else {
				$unpack[$name] = $this->unpackValue($modelField, $data, $I);
			}
		}

		return $unpack;
	}

	protected function packValue(array $modelField, $value): string
	{
		switch ($modelField['type']) {
			case self::T_STRING: return chr(strlen($value)) . $value;
			case self::T_INT8:   return chr($value);
			case self::T_INT16:  return chr(($value >> 8) & 0xff) . chr($value & 0xff);
		}
	}

	protected function unpackValue(array $modelField, string $data, int &$I)
	{
		switch ($modelField['type']) {
			case self::T_STRING:
				$length = ord($data[$I++]);
				$value = substr($data, $I, $length);
				$I += $length;
				return $value;
			case self::T_INT8:
				return ord($data[$I++]);
			case self::T_INT16:
				$value = (ord($data[$I]) << 8) + ord($data[$I+1]);
				$I += 2;
				return $value;
		}
	}

}
