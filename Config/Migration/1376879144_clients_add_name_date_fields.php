<?php

class ClientsAddNameDateFields extends CakeMigration {

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
			'create_field' => array(
				'clients' => array(
					'name' => array(
						'type' => 'string',
						'null' => false,
						'default' => null,
						'length' => 256,
						'collate' =>
						'utf8_general_ci',
						'charset' => 'utf8',
						'after' => 'client_id',
					),
					'created' => array(
						'type' => 'datetime',
						'after' => 'user_id',
						'null' => true,
					),
					'modified' => array(
						'type' => 'datetime',
						'after' => 'created',
						'null' => true,
					),
				),
			),
		),

		'down' => array(
			'drop_field' => array(
				'clients' => array(
					'name',
					'created',
					'modified',
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
