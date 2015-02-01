#WordPress BEM

A plugin that hooks into natively generated WordPress template code and converts class names to `block__element--modifier` (BEM) notation.

##What is BEM?

BEM (block__element--modifier) is a CSS naming convention aimed at producing scalable, modular and decoupled CSS code. There are other people out there who can explain this development methodology a lot more clearly and concisely than me. I particularly suggest reading [Harry Roberts' blog post on the topic](http://csswizardry.com/2013/01/mindbemding-getting-your-head-round-bem-syntax/) for a gentle introduction.

##Who should use this plugin?

If you use (or want to use) BEM notation in your WordPress projects but are put off or held back by default class names generated by WordPress template tags.

##Examples

```php
	<?php
	
	// native WP template tag that generates HTML
	wp_nav_menu(array(
		'theme_location' => 'header',
		'menu_class'     => 'main-menu',
	));
	
	// before BEM conversion
	<li class="menu-item current-menu-item menu-item-123">Home</li>
	
	// after
	<li class="main-menu__item main-menu__item--current main-menu__item--123">Home</li>
```

##Limitations & known issues

There is no universally accepted BEM standard: each developer may have his or her own individual style. I've made this plugin according to the way I like to write code. Sorry if this isn't exactly how you would have made it. Nevertheless, suggestions and criticisms are welcome.

This plugin should only really be used by developers creating a WordPress theme from scratch (unless they're happy to refactor a bunch of CSS). Installing it on a site with an existing theme is likely to break the frontend.

As WordPress BEM hooks into native WordPress functions and alters code on the fly, it may overwrite – or be overwritten by – class names generated by existing plugin or theme code.

This plugin is a work in progress, meaning some template tags may have been overlooked during development. If you spot a template tag that this plugin is yet to BEM-ify, please raise an issue and I'll fix it as soon as I can.

Bear in mind that some template tags are harder than others to alter. Particularly `comment_form()`, which directly outputs predefined class names to the browser without exposing any hooks to change its behaviour. That was a fun one to override. So much so that `search_form()` has been specifically not included (see usage section below for details).

This plugin requires PHP version 5.3 and above.

##Usage

The best way to use this plugin is install it and have a look at the code being output. Specific implementation details can be found below.

###`wp_nav_menu()`

By default, the `menu_class` parameter passed to this function will be used as the block name. This may not always be desirable, especially when passing multiple class names. For this reason you can override this behaviour by using the special `wpbem_block` argument as well.

Both of these examples will prefix all menu items with `main-menu__`.

	<?php
	
	wp_nav_menu(array(
		'theme_location' => 'main',
		'menu_class'     => 'main-menu',
	));
	
	wp_nav_menu(array(
		'theme_location' => 'main',
		'menu_class'     => 'main-menu  nav  list',
		'wpbem_block'    => 'main-menu',
	));

This function will fall back to `wp_page_menu()` if `theme_location` is not specified or is non-existent. See the next section for details.

The following class names can be generated – assuming the block name `main-menu` is passed:

	main-menu__item
	main-menu__item--42 // where 42 is the post's ID
	main-menu__item--page // where page is the post type
	main-menu__item--current
	main-menu__item--parent
	main-menu__item--ancestor
	main-menu__item--home
	main-menu__item--has-children

To customise the element (in the above case, 'item'), please use the `wpbem_nav_menu_element` filter.

###`wp_page_menu()` and `wp_list_pages()`

As with `wp_nav_menu()`, the block name can be specified using either `menu_class` or `wpbem_block` in the `$args` array.

Assuming a block name of `page-menu`, the following possible class names exist:

	page-menu__item
	page-menu__item--page-42 // where 42 is the page's ID
	page-menu__item--depth-0 // where 0 denotes a root node
	page-menu__item--current
	page-menu__item--has-children

To customise the element (in the above case, 'item'), please use the `wpbem_page_menu_element` filter.

###`body_class()`

This plugin will simply prefix all class names with `body--`. This block name can be overridden using the `wpbem_body_block` filter.

###`post_class()`

This plugin will simply prefix all class names with `post--`. This block name can be overridden using the `wpbem_post_block` filter.

###`comment_form()`

As WordPress doesn't expose any filters when generating comment form class names, the whole form has to be captured and parsed using PHP's DomDocument library, amending the markup on-the-fly. It's not an ideal solution but I've never been a fan of the default class names, so I thought it was important to include this feature anyway.

Hopefully in a future release of WordPress the default comment form template will be a bit more flexible and will allow me to revisit and simplify this feature.

There are two configuration options that are available here to modify the block name for the form's container and the form itself. To change them, just use `add_filter()` to override `wpbem_comment_container_block` and `wpbem_comment_form_block`. For example:

	add_filter('wpbem_comment_form_block', function() {
		return 'new-class-here';
	});

Amending the default WordPress class names was a bit of a hack, so I've made it possible to disable this functionality altogether. Just paste this code into your theme's functions.php file to turn this off if it causes more problems than it solves.

	add_filter('wpbem_amend_comment_form', '__return_false');

I'll welcome any pull requests from developers who fancy implementing a complete rewrite of the comment form markup. I'll do this myself someday otherwise.

###`get_search_form()`

This plugin doesn't touch the `get_search_form()` function for the same reasons the `comment_form()` modification was so painful (see previous section).

This is an easy one to override yourself. Just creating a file called searchform.php in your theme directory will achieve this.

##Customisation & configuration

###Class names

The Bem class generates all class names used by this plugin and allows the customisation of separators and formatting of blocks, elements and modifiers.

Code should be placed in your theme's functions.php file.

	// customises the element prefix, default is __ (two underscores)
	Bem::setElementPrefix($prefix);
	
	// customises the modifier prefix, default is -- (two hyphens)
	Bem::setModifierPrefix($prefix);
	
	// adds an output filter to format blocks
	// the first parameter can be either block, element or modifier
	Bem::addFilter('block', function($block) {
		return strtoupper($block);
	});

###Hook priorities

By default all actions and filters used by this plugin are added with priority 30. You may find that another plugin uses a higher priority hook which modifies WordPress BEM's behaviour.

No need to panic, just use any of the following filters to change the priority of the hook:

	wpbem_nav_menu_priority
	wpbem_page_menu_priority
	wpbem_body_class_priority
	wpbem_post_class_priority
	wpbem_comment_form_priority

##Todo

- Implement a completely custom comment form containing more flexible markup.
- More graceful handling of nested navigation menus, especially class names on child lists.
