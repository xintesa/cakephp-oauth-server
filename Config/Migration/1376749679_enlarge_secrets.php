<?php

class EnlargeSecrets extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 * @access public
 */
	public $description = '';

/**
 * Actions to be performed
 *
 * @var array $migration
 * @access public
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'access_tokens' => array(
					'oauth_token' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 132, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
				'auth_codes' => array(
					'code' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 132, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
				'clients' => array(
					'client_secret' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 132, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'after' => 'client_id'),
				),
				'refresh_tokens' => array(
					'refresh_token' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 132, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
			),
		),

		'down' => array(
			'alter_field' => array(
				'access_tokens' => array(
					'oauth_token' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
				'auth_codes' => array(
					'code' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
				'clients' => array(
					'client_secret' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'after' => 'client_id'),
				),
				'refresh_tokens' => array(
					'refresh_token' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function after($direction) {
		return true;
	}

}
