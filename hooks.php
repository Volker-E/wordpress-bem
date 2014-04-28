<?php

add_filter('nav_menu_css_class', function($classes, $item, $args) {

	$block   = isset($args->wpbem_block) ? $args->wpbem_block : $args->menu_class;
	$element = apply_filters('wpbem_nav_menu_element', 'item');

	$classes = array();

	$classes[] = Bem::bem($block, $element);
	$classes[] = Bem::bem($block, $element, $item->ID);
	$classes[] = Bem::bem($block, $element, $item->object);

	if($item->current) {
		$classes[] = Bem::bem($block, $element, 'current');
	}

	if($item->current_item_ancestor) {
		$classes[] = Bem::bem($block, $element, 'ancestor');
	}

	if($item->current_item_parent) {
		$classes[] = Bem::bem($block, $element, 'parent');
	}

	if(in_array('menu-item-has-children', $item->classes)) {
		$classes[] = Bem::bem($block, $element, 'has-children');
	}

	if(in_array('menu-item-home', $item->classes)) {
		$classes[] = Bem::bem($block, $element, 'home');
	}

	return $classes;

}, apply_filters('wpbem_nav_menu_priority', 30), 3);

add_filter('page_css_class', function($class, $page, $depth, $args, $current_page) {

	$block   = isset($args['wpbem_block']) ? $args['wpbem_block'] : $args['menu_class'];
	$element = apply_filters('wpbem_page_menu_element', 'item');

	$classes = array();

	$classes[] = Bem::bem($block, $element);
	$classes[] = Bem::bem($block, $element, 'page-' . $page->ID);
	$classes[] = Bem::bem($block, $element, 'depth-' . $depth);

	if($current_page == $page->ID) {
		$classes[] = Bem::bem($block, $element, 'current');
	}

	if(in_array('page_item_has_children', $class)) {
		$classes[] = Bem::bem($block, $element, 'has-children');
	}

	return $classes;

}, apply_filters('wpbem_page_menu_priority', 30), 5);

add_filter('body_class', function($classes) {

	$block = apply_filters('wpbem_body_block', 'body');

	$classes = array_map(function($class) use($block) {
		return Bem::bm($block, $class);
	}, $classes);

	return $classes;

}, apply_filters('wpbem_body_class_priority', 30), 1);

add_filter('post_class', function($classes) {

	$block = apply_filters('wpbem_post_block', 'post');

	$classes = array_map(function($class) use($block) {

		if('post' == $class) {
			return $block;
		}

		if('post-' == substr($class, 0, 5)) {
			$class = substr($class, 5);
		}

		return Bem::bm($block, $class);

	}, $classes);

	return $classes;

}, apply_filters('wpbem_post_class_priority', 30), 1);

if(apply_filters('wpbem_amend_comment_form', true)) {

	add_action('comment_form_before', function() {
		ob_start();
	}, apply_filters('wpbem_comment_form_priority', 30));

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
		$title->setAttribute('class', Bem::bem($container_class, 'title'));
		$title->getElementsByTagName('a')->item(0)->setAttribute('class', Bem::bem($container_class, 'cancel-link'));

		$form = $dom->getElementById('commentform');
		$form->setAttribute('class', $form_class);

		foreach($form->getElementsByTagName('p') as $p) {

			$current_class = $p->getAttribute('class');

			if('comment-form-' == substr($current_class, 0, 13)) {
				$p->setAttribute('class', sprintf('%s %s', Bem::bem($form_class, 'row'), Bem::bem($form_class, 'row', substr($current_class, 13))));
			}

		}

		foreach($form->getElementsByTagName('label') as $label) {
			$label->setAttribute('class', Bem::bem($form_class, 'label'));
		}

		foreach($form->getElementsByTagName('span') as $span) {
			if('required' == $span->getAttribute('class')) {
				$span->setAttribute('class', Bem::bem($form_class, 'required'));
			}
		}

		foreach($form->getElementsByTagName('input') as $input) {

			$input_classes = array();

			switch($input->getAttribute('type')) {
				case 'hidden':
					break;
				case 'email':
					$input_classes[] = Bem::bem($form_class, 'input');
					$input_classes[] = Bem::bem($form_class, 'input', 'email');
					break;
				case 'url':
					$input_classes[] = Bem::bem($form_class, 'input');
					$input_classes[] = Bem::bem($form_class, 'input', 'url');
					break;
				case 'submit':
					$input_classes[] = Bem::bem($form_class, 'button');
					$input_classes[] = Bem::bem($form_class, 'button', 'submit');
					break;
				case 'reset':
					$input_classes[] = Bem::bem($form_class, 'button');
					$input_classes[] = Bem::bem($form_class, 'button', 'reset');
					break;
				case 'button':
					$input_classes[] = Bem::bem($form_class, 'button');
					break;
				default:
					$input_classes[] = Bem::bem($form_class, 'input');
					$input_classes[] = Bem::bem($form_class, 'input', 'text');
					break;
			}

			$input->setAttribute('class', implode(' ', $input_classes));

		}

		if($textarea = $dom->getElementById('comment')) {
			$textarea->setAttribute('class', implode(' ', array(
				Bem::bem($form_class, 'textarea'),
				Bem::bem($form_class, 'input', 'textarea'),
				Bem::bem($form_class, 'comments-box'),
			)));
		}

		echo $dom->saveHTML($root);

	}, apply_filters('wpbem_comment_form_priority', 30));

}