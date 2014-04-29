<?php

require_once '../lib/Bem.php';

class BemText extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		Bem::restoreDefaults();
	}

	public function testGetters()
	{
		$bem = new Bem('menu', 'item', 'active');

		$this->assertEquals($bem->getBlock(), 'menu');
		$this->assertEquals($bem->getElement(), 'item');
		$this->assertEquals($bem->getModifier(), 'active');
	}

	public function testDefaultClassNameGeneration()
	{
		$bem = new Bem('menu', 'item', 'active');
		$this->assertEquals($bem->getClassName(), 'menu__item--active');

		$bem = new Bem('menu', null, 'wide');
		$this->assertEquals($bem->getClassName(), 'menu--wide');

		$bem = new Bem('menu', 'item');
		$this->assertEquals($bem->getClassName(), 'menu__item');
	}

	public function testModifiedSeparatorClassNameGeneration()
	{
		Bem::setElementPrefix('_');
		Bem::setModifierPrefix('-');

		$bem = new Bem('menu', 'item', 'active');
		$this->assertEquals($bem->getClassName(), 'menu_item-active');
	}

	public function testFilteredBlockClassNameGeneration()
	{
		Bem::addFilter('block', function($block) {
			return strtoupper($block);
		});

		$bem = new Bem('menu', 'item', 'active');
		$this->assertEquals($bem->getClassName(), 'MENU__item--active');
	}

	public function testFilteredElementClassNameGeneration()
	{
		Bem::addFilter('element', function($element) {
			return strtoupper($element);
		});

		$bem = new Bem('menu', 'item', 'active');
		$this->assertEquals($bem->getClassName(), 'menu__ITEM--active');
	}

	public function testFilteredModifierClassNameGeneration()
	{
		Bem::addFilter('modifier', function($modifier) {
			return strtoupper($modifier);
		});

		$bem = new Bem('menu', 'item', 'active');
		$this->assertEquals($bem->getClassName(), 'menu__item--ACTIVE');
	}

	public function testStaticHelperMethods()
	{
		$this->assertEquals(Bem::bem('menu', 'item', 'active'), 'menu__item--active');
		$this->assertEquals(Bem::bem('menu', 'item'), 'menu__item');
		$this->assertEquals(Bem::bm('menu', 'active'), 'menu--active');
	}
}