<?php

if (file_exists(CakePlugin::path('OAuth') . 'Config' . DS . 'oauth.php')) {
	Configure::load('OAuth.oauth');
}
