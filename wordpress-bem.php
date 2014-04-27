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

	$root = sprintf('%s__item', isset($args->wpbem_block) ? $args->wpbem_block : $args->menu_class);

	$classes = array();

	$classes[] = $root;
	$classes[] = sprintf('%s--%s', $root, $item->ID);
	$classes[] = sprintf('%s--%s', $root, $item->object);

	if($item->current) {
		$classes[] = sprintf('%s--current', $root);
	}

	if($item->current_item_ancestor) {
		$classes[] = sprintf('%s--ancestor', $root);
	}

	if($item->current_item_parent) {
		$classes[] = sprintf('%s--parent', $root);
	}
	
	if(in_array('menu-item-has-children', $item->classes)) {
		$classes[] = sprintf('%s--has-children', $root);
	}
	
	if(in_array('menu-item-home', $item->classes)) {
		$classes[] = sprintf('%s--home', $root);
	}

	return $classes;

}, 10, 3);

add_filter('page_css_class', function($class, $page, $depth, $args, $current_page) {

	$root = sprintf('%s__item', isset($args['wpbem_block']) ? $args['wpbem_block'] : $args['menu_class']);

	$classes = array();

	$classes[] = $root;
	$classes[] = sprintf('%s--%s', $root, $page->ID);
	$classes[] = sprintf('%s--%s', $root, $depth);

	if($current_page == $page->ID) {
		$classes[] = sprintf('%s--current', $root);
	}
	
	if(in_array('page_item_has_children', $class)) {
		$classes[] = sprintf('%s--has-children', $root);
	}

	return $classes;
	
}, 10, 5);

add_filter('body_class', function($classes) {

	$block = apply_filters('wpbem_body_block', 'body');

	$classes = array_map(function($class) use($block) {
		return sprintf('%s--%s', $block, $class);
	}, $classes);

	return $classes;

}, 10, 1);

add_filter('post_class', function($classes) {

	$block = apply_filters('wpbem_post_block', 'post');

	$classes = array_map(function($class) use($block) {

		if('post' == $class) {
			return $block;
		}

		if('post-' == substr($class, 0, 5)) {
			$class = substr($class, 5);
		}

		return sprintf('%s--%s', $block, $class);

	}, $classes);

	return $classes;

}, 10, 1);

if(apply_filters('wpbem_amend_comment_form', true)) {

	add_action('comment_form_before', function() {
		ob_start();
	});
	
	add_action('comment_form_after', function() {
	
		$container_class = apply_filters('wpbem_comment_container_block', 'comments');
		$form_class      = apply_filters('wpbem_comment_form_block',      'comment-form');
	
		$form = ob_get_contents();
		ob_end_clean();
	
		$dom = new DomDocument;
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = false;
	
		$dom->loadHTML($form);
	
		$root = $dom->getElementById('respond');
		$root->setAttribute('class', $container_class);
	
		$title = $dom->getElementById('reply-title');
		$title->setAttribute('class', sprintf('%s__title', $container_class));
		$title->getElementsByTagName('a')->item(0)->setAttribute('class', sprintf('%s__cancel-link', $container_class));
	
		$form = $dom->getElementById('commentform');
		$form->setAttribute('class', $form_class);
	
		foreach($form->getElementsByTagName('p') as $p) {
	
			$current_class = $p->getAttribute('class');
	
			if('comment-form-' == substr($current_class, 0, 13)) {
				$p->setAttribute('class', sprintf('%1$s__row  %1$s__row--%2$s', $form_class, substr($current_class, 13)));
			}
	
		}
	
		foreach($form->getElementsByTagName('label') as $label) {
			$label->setAttribute('class', sprintf('%s__label', $form_class));
		}
	
		foreach($form->getElementsByTagName('span') as $span) {
			if('required' == $span->getAttribute('class')) {
				$span->setAttribute('class', sprintf('%s__required', $form_class));
			}
		}
	
		foreach($form->getElementsByTagName('input') as $input) {
	
			$input_classes = array();
	
			switch($input->getAttribute('type')) {
				case 'hidden':
					break;
				case 'email':
					$input_classes[] = sprintf('%s__input', $form_class);
					$input_classes[] = sprintf('%s__input--email', $form_class);
					break;
				case 'url':
					$input_classes[] = sprintf('%s__input', $form_class);
					$input_classes[] = sprintf('%s__input--url', $form_class);
					break;
				case 'submit':
					$input_classes[] = sprintf('%s__button', $form_class);
					$input_classes[] = sprintf('%s__button--submit', $form_class);
					break;
				case 'reset':
					$input_classes[] = sprintf('%s__button', $form_class);
					$input_classes[] = sprintf('%s__button--reset', $form_class);
					break;
				case 'button':
					$input_classes[] = sprintf('%s__button', $form_class);
					break;
				default:
					$input_classes[] = sprintf('%s__input', $form_class);
					$input_classes[] = sprintf('%s__input--text', $form_class);
					break;
			}
	
			$input->setAttribute('class', implode(' ', $input_classes));
	
		}
	
		if($textarea = $dom->getElementById('comment')) {
			$textarea->setAttribute('class', implode(' ', array(
				sprintf('%s__textarea', $form_class),
				sprintf('%s__input--textarea', $form_class),
				sprintf('%s__comments-box', $form_class),
			)));
		}
	
		echo $dom->saveHTML($root);
	
	});

}