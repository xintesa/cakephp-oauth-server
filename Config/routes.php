<?php

Router::connect('/oauth/me/*', array(
	'plugin' => 'o_auth',
	'controller' => 'o_auth_users',
	'action' => 'me',
));

Router::connect('/oauth/:action/*', array('controller' => 'OAuth', 'plugin' => 'o_auth'));
