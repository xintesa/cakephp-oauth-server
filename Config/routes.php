<?php

Router::connect('/:api/:prefix/users/me', array(
	'plugin' => 'users',
	'controller' => 'users',
	'action' => 'me',
), array(
	'routeClass' => 'ApiRoute',
));

Router::connect('/oauth/:action/*', array('controller' => 'OAuth', 'plugin' => 'o_auth'));
