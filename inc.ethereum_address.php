<?php

namespace myeth;

class ethereum_address {
	
	/*
	 * Left-pad string or byte array with specified character or byte
	 */
	static private function pad($str, $min_chars, $pad_with = '0') {
		$len = strlen($str);
		if ($len < $min_chars)
			$str = str_repeat($pad_with, $min_chars-$len).$str;
		return $str;
	}
	
	/*
	 * Get secp256k1 curve for private key $curve_pt_d
	 */
	static private function getCurve($curve_pt_d = false) {
		$config = ['curve_name'=>'secp256k1'];
		if ($curve_pt_d !== false)
			$config['d'] = gmp_export($curve_pt_d);
		
		$openssl_key = openssl_pkey_new(['ec'=>$config]);
		if (!$openssl_key)
			return ['error'=>'ERROR: openssl failed'. openssl_error_string()];
		$key_detail = openssl_pkey_get_details($openssl_key)['ec'] ?? false;
		if ($key_detail === false)
			return ['error'=>"ERROR: can't get openssl key details"];
		return $key_detail;
	}
	
	/*
	 * Convert private key $pk to ethereum address
	 */
	static function fromPK($pk) {
		// filter input
		$pk = str_replace("0x", "", trim($pk, "\n\r\t "));
		$pk = strtolower($pk);
		$pk = self::pad($pk, 64);
		$curve_pt_d = gmp_init($pk, 16);	// treat PK as (a hex) number
		
		// generate curve from point p (private key)
		$key_detail = self::getCurve($curve_pt_d);
		if (isset($key_detail['error']))
			return $key_detail;
		
		// generate address, validate
		$x = self::pad($key_detail['x'], 32, chr(0));
		$y = self::pad($key_detail['y'], 32, chr(0));
		$xy = "{$x}{$y}";
		$address = substr(Keccak::hash($xy, 256), -40);
		
		$pk_cmp = self::pad(bin2hex($key_detail['d']), 64);
		if ( strcasecmp( $pk_cmp, $pk) != 0 )
			return ['error'=>"ERROR: key isn't matching, unknown issue {$pk_cmp} {$pk}"];
		return ['address'=>'0x'.$address, 'pk'=>$pk_cmp];
	}
	
	/*
	 * Generate new ethereum address with corresponding private key
	 */
	static function generate() {
		// generate rendom curve
		$key_detail = self::getCurve();
		if (isset($key_detail['error']))
			return $key_detail;
		
		// generate address, validate
		$x = self::pad($key_detail['x'], 32, chr(0));
		$y = self::pad($key_detail['y'], 32, chr(0));
		$xy = "{$x}{$y}";
		$address = substr(Keccak::hash($xy, 256), -40);
		$pk = self::pad(bin2hex($key_detail['d']), 64);
		return ['address'=>'0x'.$address, 'pk'=>$pk];
	}
}