<?php

/**
 * Plugin Name: WordPress BEM
 * Plugin URI:  http://decodehq.co.uk/wordpress/bem/
 * Description: A plugin that hooks into natively generated WordPress template code and converts class names to block__element--modifier (BEM) notation.
 * Version:     0.1
 * Author:      Sam Hastings
 * Author URI:  http://decodehq.co.uk
 * License:     GPL v3
 */

/*
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

add_filter('nav_menu_css_class', function($classes, $item, $args) {

	$root = $args->menu_class . '__' . 'item';

	$classes = array();

	$classes[] = $root;
	$classes[] = $root . '--' . $item->ID;
	$classes[] = $root . '--' . $item->type;
	$classes[] = $root . '--' . $item->object;

	if($item->current) {
		$classes[] = $root . '--current';
	}

	if($item->current_item_ancestor) {
		$classes[] = $root . '--ancestor';
	}

	if($item->current_item_parent) {
		$classes[] = $root . '--parent';
	}

	return $classes;

}, 10, 3);

add_filter('body_class', function($classes) {

	array_walk($classes, function(&$class) {
		$class = 'body--' . $class;
	});

	return $classes;

}, 10, 1);

add_filter('post_class', function($classes) {

	array_walk($classes, function(&$class) {

		if('post' == $class) {
			return;
		}

		if('post-' == substr($class, 0, 5)) {
			$class = substr($class, 5);
		}

		$class = 'post--' . $class;

	});

	return $classes;

}, 10, 1);

add_filter('wp_list_categories', function($output, $args) {

	$root = isset($args['wpbem_class']) ? $args['wpbem_class'] : 'categories';

	/*$classes = array(
		$root . '--item',
	);

	return str_replace('<li>', implode(' ', $classes), $output);*/

	return $output;

}, 10, 2);

add_action('comment_form_before', function() {
	ob_start();
});

add_action('comment_form_after', function() {

	$containerClass = apply_filters('wpbem_comment_container_class', 'comments');
	$formClass      = apply_filters('wpbem_comment_form_class',      'comment-form');

	$form = ob_get_contents();
	ob_end_clean();

	$dom = new DomDocument;
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = false;

	$dom->loadHTML($form);

	$root = $dom->getElementById('respond');
	$root->setAttribute('class', $containerClass);

	$title = $dom->getElementById('reply-title');
	$title->setAttribute('class', $containerClass . '__title');
	$title->getElementsByTagName('a')->item(0)->setAttribute('class', $containerClass . '__cancel-link');

	$form = $dom->getElementById('commentform');
	$form->setAttribute('class', $formClass);

	foreach($form->getElementsByTagName('p') as $p) {

		$currentClass = $p->getAttribute('class');

		if('comment-form-' == substr($currentClass, 0, 13)) {
			$p->setAttribute('class', $formClass . '__row ' . $formClass . '__row--' . substr($currentClass, 13));
		}

	}

	foreach($form->getElementsByTagName('label') as $label) {
		$label->setAttribute('class', $formClass . '__label');
	}

	foreach($form->getElementsByTagName('span') as $span) {
		if('required' == $span->getAttribute('class')) {
			$span->setAttribute('class', $formClass . '__required');
		}
	}

	foreach($form->getElementsByTagName('input') as $input) {

		$inputClasses = array();

		switch($input->getAttribute('type')) {
			case 'hidden':
				break;
			case 'email':
				$inputClasses[] = $formClass . '__input';
				$inputClasses[] = $formClass . '__input--email';
				break;
			case 'url':
				$inputClasses[] = $formClass . '__input';
				$inputClasses[] = $formClass . '__input--url';
				break;
			case 'submit':
				$inputClasses[] = $formClass . '__button';
				$inputClasses[] = $formClass . '__button--submit';
				break;
			case 'reset':
				$inputClasses[] = $formClass . '__button';
				$inputClasses[] = $formClass . '__button--reset';
				break;
			case 'button':
				$inputClasses[] = $formClass . '__button';
				break;
			default:
				$inputClasses[] = $formClass . '__input';
				$inputClasses[] = $formClass . '__input--text';
				break;
		}

		$input->setAttribute('class', implode(' ', $inputClasses));

	}

	if($textarea = $dom->getElementById('comment')) {
		$textarea->setAttribute('class', implode(' ', array(
			$formClass . '__textarea',
			$formClass . '__input--textarea',
			$formClass . '__comments-box',
		)));
	}

	echo $dom->saveHTML($root);

});