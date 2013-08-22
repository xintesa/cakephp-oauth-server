<?php

App::uses('Security', 'Utility');

class OAuthUtility {

/**
 * Convenience method to encrypt strings using Security::rijndael()
 *
 * @param string $text Text to encrypt
 * @param string $key Encryption key.  When null, `Security.salt` will be used
 * @return string Encrypted string
 */
	public static function encrypt($text, $key = null) {
		$key = $key === null ? Configure::read('Security.salt') : $key;
		return base64_encode(Security::rijndael($text, $key, 'encrypt'));
	}

/**
 * Convenience method to decrypt strings using Security::rijndael()
 *
 * @param string $text Text to decrypt
 * @param string $key Decryption key.  When null, `Security.salt` will be used
 * @return string Decrypted string
 */
	public static function decrypt($text, $key = null) {
		$key = $key === null ? Configure::read('Security.salt') : $key;
		return Security::rijndael(base64_decode($text), $key, 'decrypt');
	}

/**
 * Convenience method to Security::hash()
 *
 * @param string $text String to secure
 * @return string Hashed string
 */
	public static function hash($text) {
		return Security::hash($text, null, true);
	}

}
