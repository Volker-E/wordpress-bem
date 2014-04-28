<?php

/*
Plugin Name: WordPress BEM
Plugin URI:  http://github.com/decodedigital/wordpress-bem
Description: A plugin that hooks into natively generated WordPress template code and converts class names to block__element--modifier (BEM) notation.
Version:     0.1
Author:      Sam Hastings
Author URI:  http://decodehq.co.uk
License:     GPL v3

WordPress BEM Plugin
Copyright (C) 2013, Sam Hastings - sam@decodehq.co.uk

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define('WPBEM_MIN_PHP_VERSION', '5.3.0');

if(!version_compare(PHP_VERSION, WPBEM_MIN_PHP_VERSION, '>=')) {

	deactivate_plugins(plugin_basename(__FILE__));

	wp_die(sprintf(
		'<h1>Unable to activate plugin</h1><p>WordPress BEM requires PHP version %s or later. You are currently running version %s.</p><p>This plugin has been deactivated.</p><a href="%s" class="button  button-large">Return to the plugins page</a>',
		WPBEM_MIN_PHP_VERSION,
		PHP_VERSION,
		esc_url(get_admin_url(null, 'plugins.php'))
	));

}

require_once 'lib/Bem.php';
require_once 'hooks.php';