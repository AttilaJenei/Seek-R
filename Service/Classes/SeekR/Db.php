<?php

/**
 * @author Attila Jenei
 * @since May 3, 2013
 * @copyright 2013 Attila Jenei
 * @link http://www.attilajenei.com
 */

final class SeekR_Db
{
	/**
	 * @var PDO
	 */
	protected $_pdo;

	/**
	 * __construct
	 *
	 * @param array $options
	 * @throws Exception
	 */
	final public function __construct(array $options)
	{
		$this->_pdo = new PDO(
			"{$options['type']}:host={$options['host']};dbname={$options['dbname']};charset=utf8",
			$options['username'],
			$options['password'],
			array(
				PDO::ATTR_EMULATE_PREPARES => false,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			)
		);

		if (empty($this->_pdo))
		{
			throw new Exception('Could not connect to database');
		}
	}

	/**
	 * Begin transaction
	 *
	 * @return SeekR_Db
	 */
	final public function beginTransaction()
	{
		$this->_pdo->beginTransaction();

		return $this;
	}

	/**
	 * Commit
	 *
	 * @return SeekR_Db
	 */
	final public function commit()
	{
		$this->_pdo->commit();

		return $this;
	}

	/**
	 * Query
	 *
	 * @param string $statement
	 * @return PDOStatement
	 */
	final public function query($statement)
	{
		return $this->_pdo->query($statement);
	}

	/**
	 * Quote
	 *
	 * @param mixed $value
	 * @return string
	 */
	final public function quote($value)
	{
		return $value instanceof SeekR_Expression
			? (string) $value
			: (is_null($value)
				? 'NULL'
				: (is_array($value)
					? implode(',', array_map(array($this, 'quote'), $value))
					: (is_int($value) || is_float($value)
						? (string) $value
						: $this->_pdo->quote($value))
				)
			);
	}

	/**
	 * Quote identifier
	 *
	 * @param mixed $identifier
	 * @return string
	 */
	final public function quoteIdentifier($identifier)
	{
		return '`' . addcslashes((string) $identifier, '`') . '`';
	}

	/**
	 * Roll back
	 *
	 * @return SeekR_Db
	 */
	final public function rollBack()
	{
		$this->_pdo->rollBack();

		return $this;
	}
}
