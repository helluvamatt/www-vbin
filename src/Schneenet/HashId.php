<?php

namespace Schneenet;

class HashId
{
	public static final function create($data, $salt = null, $length = 8)
	{
		if (isset($salt) == false) $salt = time() . rand();
		if ($length > 8) $length = 8;
		if ($length < 5) $length = 5;
		$hash = \gmp_init(hash('sha512', $salt . $data), 16);
		return \str_pad(\substr(\gmp_strval($hash, 62), 0, $length), $length, '0', STR_PAD_LEFT);
	}
}
