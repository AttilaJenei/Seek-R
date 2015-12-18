<?php

/**
 * @author Attila Jenei
 * @since May 3, 2013
 * @copyright 2013 Attila Jenei
 * @link http://www.attilajenei.com
 */

final class SeekR_Table extends SeekR_Optioned
{
	/**
	 * @var string
	 */
	protected $_autoIncrement;

	/**
	 * @var SeekR_Db
	 */
	protected $_db;

	/**
	 * @var string
	 */
	protected $_name;

	/**
	 * @var array
	 */
	protected $_primary = array();

	/**
	 * __construct
	 *
	 * @param array $options
	 * @throws Exception
	 */
	final public function __construct(array $options)
	{
		parent::__construct($options);

		// Checks
		if (empty($this->_db))
		{
			throw new Exception('DB is not specified');
		}

		if (empty($this->_primary))
		{
			throw new Exception('Primary is not specified');
		}
	}

	/**
	 * Create row
	 *
	 * @param array $data
	 * @return SeekR_Row
	 */
	final public function createRow(array $data)
	{
		return new SeekR_Row(array(
			'table' => $this,
			'stored' => false,
			'data' => $data,
		));
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	final public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get primary
	 *
	 * @return array
	 */
	final public function getPrimary()
	{
		return $this->_primary;
	}

	/**
	 * Set auto-increment
	 *
	 * @param string $autoIncrement
	 * @return SeekR_Table
	 */
	final public function setAutoIncrement($autoIncrement)
	{
		$this->_autoIncrement = $autoIncrement;

		return $this;
	}

	/**
	 * Set DB
	 *
	 * @param SeekR_Db $db
	 * @return SeekR_Table
	 */
	final public function setDb(SeekR_Db $db)
	{
		$this->_db = $db;

		return $this;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 * @return SeekR_Table
	 */
	final public function setName($name)
	{
		$this->_name = $name;

		return $this;
	}

	/**
	 * Set primary
	 *
	 * @param array $primary
	 * @return SeekR_Table
	 */
	final public function setPrimary(array $primary)
	{
		$this->_primary = $primary;

		return $this;
	}

	/**
	 * Build where
	 *
	 * @param array $wheres
	 * @return string
	 */
	final public function buildWhere(array $wheres)
	{
		$tags = array();

		foreach ($wheres as $key => $value)
		{
			$tags[] = is_int($key)
				? $value
				: $this->_db->quoteIdentifier($key) . ($value === NULL ? ' IS NULL' : (' = ' . $this->_db->quote($value)));
		}

		return implode("\n\tAND ", $tags);
	}


	/**
	 * Fetch row
	 *
	 * @param string|array $where
	 * @return SeekR_Row|NULL
	 */
	final public function fetchRow($where)
	{
		if ($data = $this->_db->query("SELECT *\nFROM {$this->_db->quoteIdentifier($this->_name)}\nWHERE " . (is_array($where) ? $this->buildWhere($where) : $where))->fetch(PDO::FETCH_ASSOC))
		{
			return new SeekR_Row(array(
				'table' => $this,
				'stored' => true,
				'data' => $data,
			));
		}

		return null;
	}

	/**
	 * Insert row
	 *
	 * @param SeekR_Row $row
	 * @return string
	 */
	final public function insertRow(SeekR_Row $row)
	{
		// Insert
		$this->_db->query("INSERT INTO {$this->_db->quoteIdentifier($this->_name)}\nSET " . $this->_setValues($row->toArray()));

		// Auto-increment value
		if ($autoIncrement = $this->_autoIncrement)
		{
			$data = $row->toArray();
			$data[$autoIncrement] = $this->_db->query("SELECT last_insert_id()")->fetchColumn();
			$row->setData($data);
		}

		// Refresh
		$this->refreshRow($row);
	}

	/**
	 * Refresh row
	 *
	 * @param SeekR_Row $row
	 */
	final public function refreshRow(SeekR_Row $row)
	{
		// Reload content
		if ($data = $this->_db->query("SELECT *\nFROM {$this->_db->quoteIdentifier($this->_name)}\nWHERE " . $this->buildWhere($row->getPrimaryKey()))->fetch(PDO::FETCH_ASSOC))
		{
			$row
				->setData($data)
				->setStored(true);
		}
		else
		// Error
		{
			throw new Exception("Can't refresh data");
		}
	}

	/**
	 * Update row
	 *
	 * @param SeekR_Row $row
	 */
	final public function updateRow(SeekR_Row $row)
	{
		// Insert
		$this->_db->query("UPDATE {$this->_db->quoteIdentifier($this->_name)}\nSET " . $this->_setValues(array_diff_assoc($row->toArray(), $row->getPrimaryKey())) . "\nWHERE " . $this->buildWhere($row->getPrimaryKey()));

		// Refresh
		$this->refreshRow($row);
	}

	/**
	 * Set values
	 *
	 * @param array $data
	 * @return string
	 */
	final protected function _setValues(array $data)
	{
		$items = array();

		foreach ($data as $key => $value)
		{
			$items[] = $this->_db->quoteIdentifier($key) . ' = ' . $this->_db->quote($value);
		}

		return implode(",\n\t", $items);
	}
}
