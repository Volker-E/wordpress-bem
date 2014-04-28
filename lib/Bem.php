<?php

/**
 * A class to generate block__element--modifier class names.
 *
 * Example usage:

   Bem::setElementPrefix('__');
   Bem::setModifierPrefix('--');

   Bem::addFilter('block', function($block) {
       return strtoupper($block);
   });

   Bem::addFilter('element', function($element) {
       return ucfirst(strtolower($element));
   });

   echo Bem::bem('main-menu', 'item'); // MAIN-MENU__item
   echo Bem::bem('main-menu', null, 'hidden'); // MAIN-MENU--Hidden
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

	/**
	 * @param string $block
	 * @param string $element
	 * @param string $modifier
	 */
	public function __construct($block, $element = null, $modifier = null)
	{
		$this->setBlock($block);
		$this->setElement($element);
		$this->setModifier($modifier);
	}

	/**
	 * Returns the block, with any filters applied to it.
	 *
	 * @return string
	 */
	public function getBlock()
	{
		return self::applyFilters('block', $this->block);
	}

	/**
	 * Sets the block.
	 *
	 * @param string $block
	 */
	public function setBlock($block)
	{
		$this->block = $block;
	}

	/**
	 * Returns the element, with any filters applied to it.
	 *
	 * @return string
	 */
	public function getElement()
	{
		return self::applyFilters('element', $this->element);
	}

	/**
	 * Sets the element.
	 *
	 * @param string $element
	 */
	public function setElement($element)
	{
		$this->element = $element;
	}

	/**
	 * Returns the modifier, with any filters applied to it.
	 *
	 * @return string
	 */
	public function getModifier()
	{
		return self::applyFilters('modifier', $this->modifier);
	}

	/**
	 * Sets the modifier.
	 *
	 * @param string $modifier
	 */
	public function setModifier($modifier)
	{
		$this->modifier = $modifier;
	}

	/**
	 * Returns the full class name.
	 *
	 * @return string
	 */
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

	/**
	 * Returns the full class name. Called whenever instances are explicitly or
	 * implicitly converted to strings.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getClassName();
	}

	/**
	 * Applies all current filters to the desired component of the class name.
	 *
	 * @param string $part
	 * @param string $name
	 * @return string
	 */
	protected function applyFilters($part, $name)
	{
		if(!empty(self::$filters[$part])) {
			foreach(self::$filters[$part] as $filter) {
				$name = call_user_func($filter, $name);
			}
		}

		return $name;
	}

	/**
	 * Adds a filter to the given component of the class name.
	 *
	 * @param string $part
	 * @param Callable $callback
	 */
	public static function addFilter($part, $callback)
	{
		if(!is_callable($callback)) {
			return false;
		}

		self::$filters[$part][] = $callback;
	}

	/**
	 * Sets the prefix used for elements.
	 *
	 * @param string $prefix
	 */
	public static function setElementPrefix($prefix)
	{
		self::$elementPrefix = $prefix;
	}

	/**
	 * Sets the prefix used for modifiers.
	 *
	 * @param string $prefix
	 */
	public static function setModifierPrefix($prefix)
	{
		self::$modifierPrefix = $prefix;
	}

	/**
	 * Restores prefixes to their default states and removes all filters.
	 */
	public static function restoreDefaults()
	{
		self::setElementPrefix('__');
		self::setModifierPrefix('--');

		self::$filters = array();
	}

	/**
	 * Helper method to return a full class name.
	 *
	 * @param string $block
	 * @param string $element
	 * @param string $modifier
	 * @return string
	 */
	public static function bem($block, $element = null, $modifier = null) {
		$bem = new Bem($block, $element, $modifier);
		return $bem->getClassName();
	}

	/**
	 * Helper method to return a full class name without the element.
	 *
	 * @param string $block
	 * @param string $modifier
	 * @return string
	 */
	public static function bm($block, $modifier) {
		$bem = new Bem($block, null, $modifier);
		return $bem->getClassName();
	}
}