<?php

App::uses('CroogoAppController', 'Croogo.Controller');

/**
 * OAuthUsersController
 *
 * @author rchavik@xintesa.com
 * @license MIT
 */
class OAuthUsersController extends CroogoAppController {

	public $components = array(
		'Auth',
		'RequestHandler',
		'OAuth.OAuthToken' => array(
			'prefix' => 'v1',
			'whitelist' => 'me',
		),
	);

	public $uses = array(
		'Users.User',
	);

	public function me() {
		$accessToken = $this->OAuthToken->accessToken();
		$userId = $accessToken['AccessToken']['user_id'];
		if (empty($userId)) {
			throw new InvalidArgumentException();
		}
		$user = $this->User->findById($userId);
		if ($user) {
			$fields = array(
				'id', 'username', 'name', 'website', 'image', 'bio', 'timezone',
				'created', 'updated',
			);
			$fields = array_combine($fields, array_fill(0, count($fields), null));
			$user = array_intersect_key($user['User'], $fields);
		}
		$this->set(compact('user'));
		$this->set('_serialize', 'user');
	}

}
