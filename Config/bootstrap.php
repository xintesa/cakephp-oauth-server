<?php

CroogoNav::add('extensions.children.oauth', array(
	'title' => 'OAuth',
	'url' => '#',
	'children' => array(
		'clients' => array(
			'title' => 'Clients',
			'url' => array(
				'admin' => true,
				'plugin' => 'o_auth',
				'controller' => 'clients',
				'action' => 'index',
			),
		),
	),
));
