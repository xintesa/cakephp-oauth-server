<?php

/**
 * OAuthUtility class
 *
 * @author Thom Seddon <thom@seddonmedia.co.uk>
 * @see https://github.com/thomseddon/cakephp-oauth-server
 *
 */
App::uses('Security', 'Utility');

App::uses('Hash', 'Utility');

App::import('Vendor', 'OAuth.oauth2-php/lib/OAuth2');
App::import('Vendor', 'OAuth.oauth2-php/lib/IOAuth2Storage');
App::import('Vendor', 'OAuth.oauth2-php/lib/IOAuth2RefreshTokens');
App::import('Vendor', 'OAuth.oauth2-php/lib/IOAuth2GrantUser');
App::import('Vendor', 'OAuth.oauth2-php/lib/IOAuth2GrantCode');

class OAuthUtility extends Object implements
	IOAuth2Storage,
	IOAuth2RefreshTokens,
	IOAuth2GrantUser,
	IOAuth2GrantCode
{

/**
 * AccessToken object.
 *
 * @var object
 */
	public $AccessToken;

/**
 * An array containing the model and fields to authenticate users against
 *
 * Inherits theses defaults:
 *
 * $this->OAuth->authenticate = array(
 *	'userModel' => 'User',
 *	'fields' => array(
 *		'username' => 'username',
 *		'password' => 'password'
 *	)
 * );
 *
 * Which can be overridden in your beforeFilter:
 *
 * $this->OAuth->authenticate = array(
 *	'fields' => array(
 *		'username' => 'email'
 *	)
 * );
 *
 *
 * $this->OAuth->authenticate
 *
 * @var array
 */
	public $authenticate;

/**
 * Defaults for $authenticate
 *
 * @var array
 */
	protected $_authDefaults = array(
		'userModel' => 'User',
		'fields' => array('username' => 'username', 'password' => 'password')
		);

/**
 * AuthCode object.
 *
 * @var object
 */
	public $AuthCode;

/**
 * Clients object.
 *
 * @var object
 */
	public $Client;

/**
 * Array of globally supported grant types
 *
 * By default = array('authorization_code', 'refresh_token', 'password');
 * Other grant mechanisms are not supported in the current release
 *
 * @var array
 */
	public $grantTypes = array('authorization_code', 'refresh_token', 'password');

/**
 * OAuth2 Object
 *
 * @var object
 */
	public $OAuth2;

/**
 * RefreshToken object.
 *
 * @var object
 */
	public $RefreshToken;

/**
 * User object
 *
 * @var object
 */
	public $User;

/**
 * Static storage for current user
 *
 * @var array
 */
	protected $_user = false;

/**
 * Constructor - Adds class associations
 *
 * @see OAuth2::__construct().
 */
	public function __construct() {
		$this->OAuth2 = new OAuth2($this);
		$this->AccessToken = ClassRegistry::init(array('class' => 'OAuth.AccessToken', 'alias' => 'AccessToken'));
		$this->AuthCode = ClassRegistry::init(array('class' => 'OAuth.AuthCode', 'alias' => 'AuthCode'));
		$this->Client = ClassRegistry::init(array('class' => 'OAuth.Client', 'alias' => 'Client'));
		$this->RefreshToken = ClassRegistry::init(array('class' => 'OAuth.RefreshToken', 'alias' => 'RefreshToken'));
	}

/**
 * Fakes the OAuth2.php vendor class extension for variables
 *
 * @param string $name
 * @return mixed
 */
	public function __get($name) {
		if (isset($this->OAuth2->{$name})) {
			try {
				return $this->OAuth2->{$name};
			} catch (Exception $e) {
				$e->sendHttpResponse();
			}
		}
	}

/**
 * Fakes the OAuth2.php vendor class extension for methods
 *
 * @param string $name
 * @param mixed $arguments
 * @return mixed
 * @throws Exception
 */
	public function __call($name, $arguments) {
		if (method_exists($this->OAuth2, $name)) {
			try {
				return call_user_func_array(array($this->OAuth2, $name), $arguments);
			} catch (Exception $e) {
				if (method_exists($e, 'sendHttpResponse')) {
					$e->sendHttpResponse();
				}
				throw $e;
			}
		}
	}

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

/**
 * Convenience function to invalidate all a users tokens, for example when they change their password
 *
 * @param int $user_id
 * @param string $tokens 'both' (default) to remove both AccessTokens and RefreshTokens or remove just one type using 'access' or 'refresh'
 */
	public function invalidateUserTokens($user_id, $tokens = 'both') {
		if ($tokens == 'access' || $tokens == 'both') {
			$this->AccessToken->deleteAll(array('user_id' => $user_id), false);
		}
		if ($tokens == 'refresh' || $tokens == 'both') {
			$this->RefreshToken->deleteAll(array('user_id' => $user_id), false);
		}
	}

/**
 * Below are the library interface implementations
 *
 */

/**
 * Check client details are valid
 *
 * @see IOAuth2Storage::checkClientCredentials().
 *
 * @param string $client_id
 * @param string $client_secret
 * @return mixed array of client credentials if valid, false if not
 */
	public function checkClientCredentials($client_id, $client_secret = null) {
		$conditions = array('client_id' => $client_id);
		$client = $this->Client->find('first', array(
			'conditions' => $conditions,
			'recursive' => -1
		));
		if ($client) {
			$decrypted = self::decrypt($client['Client']['client_secret']);
			if ($decrypted == $client_secret) {
				return $client['Client'];
			} else {
				return false;
			}
		};
		return false;
	}

/**
 * Get client details
 *
 * @see IOAuth2Storage::getClientDetails().
 *
 * @param string $client_id
 * @return boolean
 */
	public function getClientDetails($client_id) {
		$client = $this->Client->find('first', array(
			'conditions' => array('client_id' => $client_id),
			'fields' => array('client_id', 'redirect_uri'),
			'recursive' => -1
		));
		if ($client) {
			return $client['Client'];
		}
		return false;
	}

/**
 * Retrieve access token
 *
 * @see IOAuth2Storage::getAccessToken().
 *
 * @param string $oauth_token
 * @return mixed AccessToken array if valid, null if not
 */
	public function getAccessToken($oauth_token) {
		$accessToken = $this->AccessToken->find('first', array(
			'conditions' => array('oauth_token' => self::hash($oauth_token)),
			'recursive' => -1,
		));
		if ($accessToken) {
			return $accessToken['AccessToken'];
		}
		return null;
	}

/**
 * Set access token
 *
 * @see IOAuth2Storage::setAccessToken().
 *
 * @param string $oauth_token
 * @param string $client_id
 * @param int $user_id
 * @param string $expires
 * @param string $scope
 * @return boolean true if successfull, false if failed
 */
	public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = null) {
		$data = array(
			'oauth_token' => $oauth_token,
			'client_id' => $client_id,
			'user_id' => $user_id,
			'expires' => $expires,
			'scope' => $scope
		);
		$this->AccessToken->create();
		return $this->AccessToken->save(array('AccessToken' => $data));
	}

/**
 * Partial implementation, just checks globally avaliable grant types
 *
 * @see IOAuth2Storage::checkRestrictedGrantType()
 *
 * @param string $client_id
 * @param string $grant_type
 * @return boolean If grant type is availiable to client
 */
	public function checkRestrictedGrantType($client_id, $grant_type) {
		return in_array($grant_type, $this->grantTypes);
	}

/**
 * Grant type: refresh_token
 *
 * @see IOAuth2RefreshTokens::getRefreshToken()
 *
 * @param string $refresh_token
 * @return mixed RefreshToken if valid, null if not
 */
	public function getRefreshToken($refresh_token) {
		$refreshToken = $this->RefreshToken->find('first', array(
			'conditions' => array('refresh_token' => self::hash($refresh_token)),
			'recursive' => -1
		));
		if ($refreshToken) {
			return $refreshToken['RefreshToken'];
		}
		return null;
	}

/**
 * Grant type: refresh_token
 *
 * @see IOAuth2RefreshTokens::setRefreshToken()
 *
 * @param string $refresh_token
 * @param int $client_id
 * @param string $user_id
 * @param string $expires
 * @param string $scope
 * @return boolean true if successfull, false if fail
 */
	public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null) {
		$data = array(
			'refresh_token' => $refresh_token,
			'client_id' => $client_id,
			'user_id' => $user_id,
			'expires' => $expires,
			'scope' => $scope
		);
		$this->RefreshToken->create();
		return $this->RefreshToken->save(array('RefreshToken' => $data));
	}

/**
 * Grant type: refresh_token
 *
 * @see IOAuth2RefreshTokens::unsetRefreshToken()
 *
 * @param string $refresh_token
 * @return boolean true if successfull, false if not
 */
	public function unsetRefreshToken($refresh_token) {
		return $this->RefreshToken->delete($refresh_token);
	}

/**
 * Grant type: user_credentials
 *
 * @see IOAuth2GrantUser::checkUserCredentials()
 *
 * @param type $client_id
 * @param type $username
 * @param type $password
 */
	public function checkUserCredentials($client_id, $username, $password) {
		$user = $this->User->find('first', array(
			'conditions' => array(
				$this->authenticate['fields']['username'] => $username,
				$this->authenticate['fields']['password'] => AuthComponent::password($password)
			),
			'recursive' => -1
		));
		if ($user) {
			return array('user_id' => $user['User'][$this->User->primaryKey]);
		}
		return false;
	}

/**
 * Grant type: authorization_code
 *
 * @see IOAuth2GrantCode::getAuthCode()
 *
 * @param string $code
 * @return AuthCode if valid, null of not
 */
	public function getAuthCode($code) {
		$authCode = $this->AuthCode->find('first', array(
			'conditions' => array('code' => self::hash($code)),
			'recursive' => -1
		));
		if ($authCode) {
			return $authCode['AuthCode'];
		}
		return null;
	}

/**
 * Grant type: authorization_code
 *
 * @see IOAuth2GrantCode::setAuthCode().
 *
 * @param string $code
 * @param string $client_id
 * @param int $user_id
 * @param string $redirect_uri
 * @param string $expires
 * @param string $scope
 * @return boolean true if successfull, otherwise false
 */
	public function setAuthCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null) {
		$data = array(
			'code' => $code,
			'client_id' => $client_id,
			'user_id' => $user_id,
			'redirect_uri' => $redirect_uri,
			'expires' => $expires,
			'scope' => $scope
		);
		$this->AuthCode->create();
		return $this->AuthCode->save(array('AuthCode' => $data));
	}

}
