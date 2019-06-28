<?php

namespace App\Http\Traits;

use App\KeyContainer;

trait KeyContainerTrait {

    /**
     * Generate key container
     * @param  string  $keyType
     * @return \Illuminate\Http\Response
     */
	public function generateContainer($keyType)
	{
		return strtoupper($keyType[0]).mt_rand(1000, 99999);
	}

	/**
     * Count keys
     * @param  array  $keys
     * @return \Illuminate\Http\Response
     */
	public function countKeys($keys)
	{
		return count($keys);
	}
}

?>