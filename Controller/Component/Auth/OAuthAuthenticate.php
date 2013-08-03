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
 * Authenticate a user based on the request information
 *
 * @see BaseAuthenticate
 */
	public function authenticate(CakeRequest $request, CakeResponse $response) {
		if (isset($request->query['client_id'])) {
			return $this->getUser($request);
		}
		return false;
	}

/**
 * Gets a user based on information in the request.
 *
 * @param CakeRequest $request Request object
 * @return mixed Either false or an array of user information
 * @see OAuth2::getBearerToken()
 */
	public function getUser($request) {
		$token = $this->OAuthUtility->getBearerToken();
		if (!$token) {
			return false;
		}

		$hashedToken = $this->OAuthUtility->hash($token);
		$AccessToken = ClassRegistry::init('OAuth.AccessToken');
		$accessToken = $AccessToken->find('first', array(
			'conditions' => array(
				'oauth_token' => $hashedToken,
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
