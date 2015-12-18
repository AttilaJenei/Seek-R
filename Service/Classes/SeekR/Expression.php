<?php

/**
 * @author Attila Jenei
 * @since May 3, 2013
 * @copyright 2013 Attila Jenei
 * @link http://www.attilajenei.com
 */

final class SeekR_Expression
{
	/**
	 * @var mixed
	 */
	protected $_content;

	/**
	 * __construct
	 *
	 * @param unknown_type $content
	 */
	final public function __construct($content)
	{
		$this->_content = $content;
	}

	/**
	 * __toString
	 *
	 * @return string
	 */
	final public function __toString()
	{
		return (string) $this->_content;
	}
}
