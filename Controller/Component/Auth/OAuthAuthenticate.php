<?php

App::uses('BaseAuthenticate', 'Controller/Component/Auth');
App::uses('OAuthUtility', 'OAuth.Lib');

/**
 * An authentication adapter for OAuth2
 *
 * @author rchavik@gmail.com
 * @licent MIT
 */
class OAuthAuthenticate extends BaseAuthenticate {

/**
 * Constructor
 */
	public function __construct(ComponentCollection $collection, $settings) {
		parent::__construct($collection, $settings);
		$this->OAuthUtility = new OAuthUtility();
	}

/**
 * Checks wether request has credential data
 *
 * @param CakeRequest $request Request object
 * @return bool True when request has token/bearer data
 */
	protected function _hasCredentials(CakeRequest $request) {
		return isset($request->query['access_token']) || $request->header('Authorization');
	}

/**
 * Authenticate a user based on the request information
 *
 * @see BaseAuthenticate
 */
	public function authenticate(CakeRequest $request, CakeResponse $response) {
		return $this->getUser($request);
	}

/**
 * Gets a user based on information in the request.
 *
 * @param CakeRequest $request Request object
 * @return mixed Either false or an array of user information
 * @see OAuth2::getBearerToken()
 */
	public function getUser(CakeRequest $request) {
		if (!$this->_hasCredentials($request)) {
			return false;
		}
		$token = $this->OAuthUtility->getBearerToken();
		if (!$token) {
			return false;
		}

		$AccessToken = ClassRegistry::init('OAuth.AccessToken');
		$accessToken = $AccessToken->find('first', array(
			'conditions' => array(
				'oauth_token' => $token,
			),
		));

		if (empty($accessToken['AccessToken']['user_id'])) {
			return false;
		}

		$fields = $this->settings['fields'];
		list($plugin, $model) = pluginSplit($this->settings['userModel']);
		$User = ClassRegistry::init($this->settings['userModel']);

		$conditions = array(
			$model . '.' . $User->primaryKey => $accessToken['AccessToken']['user_id'],
		);

		$result = $User->find('first', array(
			'conditions' => $conditions,
			'recursive' => (int)$this->settings['recursive'],
			'contain' => $this->settings['contain'],
		));
		if (empty($result[$model])) {
			return false;
		}
		$user = $result[$model];
		unset($user[$fields['password']]);
		unset($result[$model]);
		return array_merge($user, $result);
	}

}
