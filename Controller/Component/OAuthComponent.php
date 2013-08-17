<?php

/**
 * CakePHP OAuth Server Plugin
 *
 * This is the main component.
 *
 * It provides:
 *	- Cakey interface to the OAuth2-php library
 *	- AuthComponent like action allow/deny's
 *	- Easy access to user associated to an access token
 *	- More!?
 *
 * @author Thom Seddon <thom@seddonmedia.co.uk>
 * @see https://github.com/thomseddon/cakephp-oauth-server
 *
 */

App::uses('OAuthUtility', 'OAuth.Lib');
App::uses('Component', 'Controller');
App::uses('Router', 'Routing');
App::uses('Security', 'Utility');
App::uses('Hash', 'Utility');
App::uses('AuthComponent', 'Controller');

class OAuthComponent extends Component {

/**
 * Array of allowed actions
 *
 * @var array
 */
	protected $allowedActions = array('token', 'authorize', 'login');

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
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);
		$this->OAuthUtility = new OAuthUtility();
	}

/**
 * Initializes OAuthComponent for use in the controller
 *
 * @param Controller $controller A reference to the instantiating controller object
 * @return void
 */
	public function initialize(Controller $controller) {
		$this->request = $controller->request;
		$this->response = $controller->response;
		$this->_methods = $controller->methods;

		if (Configure::read('debug') > 0) {
			Debugger::checkSecurityKeys();
		}
	}

/**
 * Main engine that checks valid access_token and stores the associated user for retrival
 *
 * @see AuthComponent::startup()
 *
 * @param type $controller
 * @return boolean
 */
	public function startup(Controller $controller) {
		$methods = array_flip(array_map('strtolower', $controller->methods));
		$action = strtolower($controller->request->params['action']);

		$this->authenticate = Hash::merge($this->_authDefaults, $this->authenticate);
		$this->User = ClassRegistry::init(array(
			'class' => $this->authenticate['userModel'],
			'alias' => $this->authenticate['userModel']
			));

		$isMissingAction = (
			$controller->scaffold === false &&
			!isset($methods[$action])
		);
		if ($isMissingAction) {
			return true;
		}

		$allowedActions = $this->allowedActions;
		$isAllowed = (
			$this->allowedActions == array('*') ||
			in_array($action, array_map('strtolower', $allowedActions))
		);
		if ($isAllowed) {
			return true;
		}

		try {
			$this->isAuthorized();
			$this->user(null, $this->OAuthUtility->AccessToken->id);
		} catch (OAuth2AuthenticateException $e) {
			$e->sendHttpResponse();
			return false;
		}
		return true;
	}

/**
 * Checks if user is valid using OAuth2-php library
 *
 * @see OAuth2::getBearerToken()
 * @see OAuth2::verifyAccessToken()
 *
 * @return boolean true if carrying valid token, false if not
 */
	public function isAuthorized() {
		try {
			$this->OAuthUtility->AccessToken->id = $this->OAuthUtility->getBearerToken();
			$this->OAuthUtility->verifyAccessToken($this->OAuthUtility->AccessToken->id);
		} catch (OAuth2AuthenticateException $e) {
			return false;
		}
		return true;
	}

/**
 * Takes a list of actions in the current controller for which authentication is not required, or
 * no parameters to allow all actions.
 *
 * You can use allow with either an array, or var args.
 *
 * `$this->OAuth->allow(array('edit', 'add'));` or
 * `$this->OAuth->allow('edit', 'add');` or
 * `$this->OAuth->allow();` to allow all actions.
 *
 * @param string|array $action,... Controller action name or array of actions
 * @return void
 */
	public function allow($action = null) {
		$args = func_get_args();
		if (empty($args) || $action === null) {
			$this->allowedActions = $this->_methods;
		} else {
			if (isset($args[0]) && is_array($args[0])) {
				$args = $args[0];
			}
			$this->allowedActions = array_merge($this->allowedActions, $args);
		}
	}

/**
 * Removes items from the list of allowed/no authentication required actions.
 *
 * You can use deny with either an array, or var args.
 *
 * `$this->OAuth->deny(array('edit', 'add'));` or
 * `$this->OAuth->deny('edit', 'add');` or
 * `$this->OAuth->deny();` to remove all items from the allowed list
 *
 * @param string|array $action,... Controller action name or array of actions
 * @return void
 * @see OAuthComponent::allow()
 */
	public function deny($action = null) {
		$args = func_get_args();
		if (empty($args) || $action === null) {
			$this->allowedActions = array();
		} else {
			if (isset($args[0]) && is_array($args[0])) {
				$args = $args[0];
			}
			foreach ($args as $arg) {
				$i = array_search($arg, $this->allowedActions);
				if (is_int($i)) {
					unset($this->allowedActions[$i]);
				}
			}
			$this->allowedActions = array_values($this->allowedActions);
		}
	}
/**
 * Gets the user associated to the current access token.
 *
 * Will return array of all user fields by default
 * You can specify specific fields like so:
 *
 * $id = $this->OAuth->user('id');
 *
 * @param type $field
 * @return mixed array of user fields if $field is blank, string value if $field is set and $fields is avaliable, false on failure
 */
	public function user($field = null, $token = null) {
		if (!$this->_user) {
			$this->OAuthUtility->AccessToken->bindModel(array(
				'belongsTo' => array(
				'User' => array(
					'className' => $this->authenticate['userModel'],
					'foreignKey' => 'user_id'
					)
				)
				));
			$token = empty($token) ? $this->OAuthUtility->getBearerToken() : $token;
			$data = $this->AccessToken->find('first', array(
				'conditions' => array('oauth_token' => OAuthUtility::hash($token)),
				'recursive' => 1
			));
			if (!$data) {
				return false;
			}
			$this->_user = $data['User'];
		}
		if (empty($field)) {
			return $this->_user;
		} elseif (isset($this->_user[$field])) {
			return $this->_user[$field];
		}
		return false;
	}

/**
 * Convenience function for hashing client_secret (or whatever else)
 *
 * @param string $password
 * @return string Hashed password
 * @deprecated Will be removed in future version
 */
	public static function hash($password) {
		return Security::hash($password, null, true);
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
 * Fakes the OAuth2.php vendor class extension for variables
 *
 * @param string $name
 * @return mixed
 */
	public function __get($name) {
		if (isset($this->OAuthUtility->OAuth2->{$name})) {
			try {
				return $this->OAuthUtility->OAuth2->{$name};
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
		if (method_exists($this->OAuthUtility, $name)) {
			try {
				return call_user_func_array(array($this->OAuthUtility, $name), $arguments);
			} catch (Exception $e) {
				if (method_exists($e, 'sendHttpResponse')) {
					$e->sendHttpResponse();
				}
				throw $e;
			}
		}
	}

}
