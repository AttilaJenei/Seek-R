<?php

/**
 * @author Attila Jenei
 * @since May 2, 2013
 * @copyright 2013 Attila Jenei
 * @link http://www.attilajenei.com
 */

abstract class SeekR_Optioned
{
	/**
	 * __construct
	 *
	 * @param array $options
	 */
	public function __construct(array $options)
	{
		$this->setOptions($options);
	}

	/**
	 * Set options
	 *
	 * @param array $options
	 * @throws Exception
	 * @return SeekR_Optioned
	 */
	final public function setOptions(array $options)
	{
		foreach ($options as $key => $value)
		{
			if (method_exists($this, $method = 'set' . ucfirst($key)))
			{
				$this->$method($value);
			}
			else
			{
				throw new Exception('Unknown option: ' . $key);
			}
		}

		return $this;
	}
}
