<?php

class CroogoFields extends CakeMigration {

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

					'created_by' => array(
						'type' => 'integer',
						'after' => 'user_id',
					),

					'modified_by' => array(
						'type' => 'integer',
						'after' => 'created',
					),

				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'clients' => array(
					'created_by',
					'modified_by',
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
