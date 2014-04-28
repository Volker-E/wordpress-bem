<?php

/**
 * A class to generate block__element--modifier class names.
 *
 * Example usage:
 
   Bem::setElementPrefix('__');
   Bem::setModifierPrefix('--');

   Bem::addFilter('block', function($modifier) {
       return strtoupper($modifier);
   });

   Bem::addFilter('element', function($modifier) {
       return ucfirst(strtolower($modifier));
   });

   echo Bem::bem('main-menu', 'item');
   echo Bem::bem('main-menu', null, 'hidden');
 *
 */
class Bem
{
	protected static $elementPrefix  = '__';
	protected static $modifierPrefix = '--';

	protected static $filters = array();

	protected $block;
	protected $element;
	protected $modifier;

	public function __construct($block, $element = null, $modifier = null)
	{
		$this->setBlock($block);
		$this->setElement($element);
		$this->setModifier($modifier);
	}

	public function getBlock()
	{
		return self::applyFilters('block', $this->block);
	}

	public function setBlock($block)
	{
		$this->block = $block;
	}

	public function getElement()
	{
		return self::applyFilters('element', $this->element);
	}

	public function setElement($element)
	{
		$this->element = $element;
	}

	public function getModifier()
	{
		return self::applyFilters('modifier', $this->modifier);
	}

	public function setModifier($modifier)
	{
		$this->modifier = $modifier;
	}

	public function getClassName()
	{
		$block    = $this->getBlock();
		$element  = $this->getElement();
		$modifier = $this->getModifier();

		$className = $block;

		if($element) {
			$className = sprintf('%s%s%s', $className, self::$elementPrefix, $element);
		}

		if($modifier) {
			$className = sprintf('%s%s%s', $className, self::$modifierPrefix, $modifier);
		}

		return $className;
	}

	public function __toString()
	{
		return $this->getClassName();
	}

	protected function applyFilters($part, $name)
	{
		if(!empty(self::$filters[$part])) {
			foreach(self::$filters[$part] as $filter) {
				$name = call_user_func($filter, $name);
			}
		}

		return $name;
	}

	public static function addFilter($part, $callback)
	{
		if(!is_callable($callback)) {
			return false;
		}

		self::$filters[$part][] = $callback;
	}

	public static function setElementPrefix($prefix)
	{
		self::$elementPrefix = $prefix;
	}

	public static function setModifierPrefix($prefix)
	{
		self::$modifierPrefix = $prefix;
	}

	public static function bem($block, $element = null, $modifier = null) {
		$bem = new Bem($block, $element, $modifier);
		return $bem->getClassName();
	}

	public static function bm($block, $modifier) {
		$bem = new Bem($block, null, $modifier);
		return $bem->getClassName();
	}
}