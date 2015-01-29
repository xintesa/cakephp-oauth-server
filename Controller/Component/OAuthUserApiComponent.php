<?php

App::uses('BaseApiComponent', 'Croogo.Controller/Component');
App::uses('OAuthUtility', 'OAuth.Lib');

/**
 * OAuthUserApiComponent
 *
 * @author rchavik@xintesa.com
 * @license MIT
 */
class OAuthUserApiComponent extends BaseApiComponent {

/**
 * API version
 */
	protected $_apiVersion = 'v1.0';

/**
 * API methods
 */
	protected $_apiMethods = array(
		'me',
	);

	public function startup(Controller $controller) {
		$authorizeComponent = 'OAuth.OAuth';
		if (!in_array($authorizeComponent, $controller->Auth->authenticate) &&
			!isset($controller->Auth->authenticate[$authorizeComponent]))
		{
			$controller->Auth->authenticate[] = 'OAuth.OAuth';
		}
	}

/**
 * Retrieve current user info
 */
	public function me(Controller $controller) {
		$OAuthUtility = new OAuthUtility();
		$accessToken = $OAuthUtility->getAccessToken($OAuthUtility->getBearerToken());
		$userId = $accessToken['user_id'];
		if (empty($userId)) {
			throw new InvalidArgumentException();
		}
		$user = $controller->User->findById($userId);
		if ($user) {
			$fields = array(
				'id', 'username', 'name', 'website', 'image', 'bio', 'timezone',
				'created', 'updated', 'email',
			);
			$fields = array_combine($fields, array_fill(0, count($fields), null));
			$user = array_intersect_key($user['User'], $fields);
		}

		$controller->set('user', array('user' => $user));
		$controller->set('_serialize', 'user');
	}

}
