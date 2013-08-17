<?php

App::uses('Security', 'Utility');

class OAuthUtility {

/**
 * Convenience method to encrypt strings using Security::rijndael()
 *
 * @param string $text Text to encrypt
 * @return string Encrypted string
 */
	public static function encrypt($text) {
		return base64_encode(Security::rijndael($text, Configure::read('Security.salt'), 'encrypt'));
	}

/**
 * Convenience method to decrypt strings using Security::rijndael()
 *
 * @param string $text Text to decrypt
 * @return string Decrypted string
 */
	public static function decrypt($text) {
		return Security::rijndael(base64_decode($text), Configure::read('Security.salt'), 'decrypt');
	}

/**
 * Convenience method to Security::hash()
 *
 * Included only for backward compatibility.
 * @param string $password
 * @return string Hashed password
 * @deprecated Will be removed in future version
 */
	public static function secure($text) {
		if (Configure::read('OAuth.encrypt')) {
			return self::encrypt($text);
		}
		return Security::hash($text, null, true);
	}

}
