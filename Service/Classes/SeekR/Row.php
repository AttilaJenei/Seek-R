<?php

/**
 * @author Attila Jenei
 * @since May 3, 2013
 * @copyright 2013 Attila Jenei
 * @link http://www.attilajenei.com
 */

final class SeekR_Row extends SeekR_Optioned
{
	/**
	 * @var array
	 */
	protected $_data = array();

	/**
	 * @var array
	 */
	protected $_primary = array();

	/**
	 * @var boolean
	 */
	protected $_stored = false;

	/**
	 * @var SeekR_Table
	 */
	protected $_table;

	/**
	 * __get
	 *
	 * @param string $columnName
	 * @throws Exception
	 * @return mixed
	 */
	final public function __get($columnName)
	{
		// Check
		if (!array_key_exists($columnName, $this->_data))
		{
			throw new Exception("Unknown column: {$columnName}");
		}

		return $this->_data[$columnName];
	}

	/**
	 * __set
	 *
	 * @param string $columnName
	 * @param mixed $value
	 * @throws Exception
	 */
	final public function __set($columnName, $value)
	{
		// Check
		if (!array_key_exists($columnName, $this->_data))
		{
			throw new Exception("Unknown column: {$columnName}");
		}

		$this->_data[$columnName] = $value;
	}

	/**
	 * __toString
	 *
	 * @return string
	 */
	final public function __toString()
	{
		return 'Row: '
			. (empty($this->_table) ? 'Table not specified' : $this->_table->getName())
			. "\n\tData: "
			. str_replace("\n", "\n\t", print_r($this->_data, true))
			. "\n";
	}

	/**
	 * Get primary key
	 *
	 * @return array
	 */
	final public function getPrimaryKey()
	{
		return array_intersect_key($this->_data, array_flip($this->_primary));
	}

	/**
	 * Set data
	 *
	 * @param array $data
	 * @return SeekR_Row
	 */
	final public function setData(array $data)
	{
		$this->_data = $data;

		return $this;
	}

	/**
	 * Set from array
	 *
	 * @param array $data
	 * @return SeekR_Row
	 */
	final public function setFromArray(array $data)
	{
		foreach ($data as $key => $value)
		{
			$this->$key = $value;
		}

		return $this;
	}

	/**
	 * Set stored
	 *
	 * @param boolean $stored
	 * @return SeekR_Row
	 */
	final public function setStored($stored)
	{
		$this->_stored = (boolean) $stored;

		return $this;
	}

	/**
	 * Set table
	 *
	 * @param SeekR_Table $table
	 * @return SeekR_Row
	 */
	final public function setTable(SeekR_Table $table)
	{
		$this->_table = $table;
		$this->_primary = $this->_table->getPrimary();

		return $this;
	}

	/**
	 * To array
	 *
	 * @return array
	 */
	final public function toArray()
	{
		return $this->_data;
	}

	/**
	 * Refresh
	 *
	 * @return SeekR_Row
	 */
	final public function refresh()
	{
		$this->_table->refreshRow($this);

		return $this;
	}

	/**
	 * Save
	 *
	 * @throws Exception
	 * @return array
	 */
	final public function save()
	{
		if (empty($this->_table))
		{
			throw new Exception('Table is not specified');
		}

		// Update
		if ($this->_stored)
		{
			$this->_table->updateRow($this);
		}
		else
		// Insert
		{
			$this->_table->insertRow($this);
		}

		return $this->getPrimaryKey();
	}
}
