<?php

/**
 * @author Attila Jenei
 * @since May 2, 2013
 * @copyright 2013 Attila Jenei
 * @link http://www.attilajenei.com
 */

final class SeekR extends SeekR_Optioned
{
	/**
	 * @var string
	 */
	const
		LOG_INFO = 'info',
		LOG_ERROR = 'error';

	/**
	 * @var string
	 */
	const
		OPERATION_NEW = 'new',
		OPERATION_MODIFIED = 'modified',
		OPERATION_REMOVED = 'removed';

	/**
	 * @var string
	 */
	const
		STATUS_ACTIVE = 'active',
		STATUS_REMOVED = 'removed';

	/**
	 * @var int
	 */
	const
		SEND_MAIL_ON_CONTENT_CHANGES = 1,
		SEND_MAIL_ON_ALL_CHANGES = 2,
		SEND_MAIL_ON_SCAN = 3;

	/**
	 * @var bool
	 */
	protected $_alwaysCheckContent = false;

	/**
	 * @var array
	 */
	protected $_changeListColumns = array(
		'name' => array('title' => 'Name'),
		'operation' => array('title' => ''),
		'modifyDate' => array('title' => 'Date', 'class' => 'Date'),
		'size' => array('title' => 'Size', 'class' => 'Numeric'),
		'contentHash' => array('title' => 'Content', 'class' => 'Content'),
		'owner' => array('title' => 'Owner'),
		'group' => array('title' => 'Group'),
		'permissions' => array('title' => 'Permissions'),
		'linkTarget' => array('title' => 'Link'),
	);

	/**
	 * @var array
	 */
	protected $_connection;

	/**
	 * @var bool
	 */
	protected $_copyWithDate = true;

	/**
	 * @var array
	 */
	protected $_currentRoot;

	/**
	 * @var array
	 */
	protected $_currentTo;

	/**
	 * @var string
	 */
	protected $_dateFormat = 'Y-m-d H:i:s';

	/**
	 * @var array
	 */
	protected $_dateOnlyChangeListColumns = array(
		'name' => array('title' => 'Name'),
		'modifyDate' => array('title' => 'Date', 'class' => 'Date'),
	);

	/**
	 * @var SeekR_Db
	 */
	protected $_db;

	/**
	 * @var SeekR_Table
	 */
	protected $_directoryTable;

	/**
	 * @var SeekR_Table
	 */
	protected $_directoryHistoryTable;

	/**
	 * @var array
	 */
	static protected $_errorCodes = array(
		E_ERROR => 'Error',
		E_PARSE => 'Parse error',
		E_NOTICE => 'Notice',
		E_DEPRECATED => 'Deprecated',
		E_RECOVERABLE_ERROR => 'Recoverable error',
		E_STRICT => 'Strict',
		E_USER_ERROR => 'User error',
		E_USER_NOTICE => 'User notice',
		E_USER_WARNING => 'User warning',
		E_USER_DEPRECATED => 'User deprecated',
		E_WARNING => 'Warning',
		E_CORE_ERROR => 'Core error',
		E_CORE_WARNING => 'Core warning',
		E_COMPILE_ERROR => 'Compile error',
		E_COMPILE_WARNING => 'Compile warning',
	);

	/**
	 * @var array
	 */
	protected $_errorListColumns = array(
		'date' => array('title' => 'Date', 'class' => 'Date'),
		'text' => array('title' => 'Problem'),
	);

	/**
	 * @var string
	 */
	protected $_errorMailTemplate;

	/**
	 * @var SeekR_Table
	 */
	protected $_fileTable;

	/**
	 * @var SeekR_Table
	 */
	protected $_fileHistoryTable;

	/**
	 * @var array
	 */
	protected $_groups = array();

	/**
	 * @var SeekR_Table
	 */
	protected $_logTable;

	/**
	 * @var string
	 */
	protected $_mailDateFormat = 'Y-m-d H:i:s';

	/**
	 * @var DateTimeZone
	 */
	protected $_mailDateTimezone;

	/**
	 * @var int
	 */
	protected $_mailListSize = 100;

	/**
	 * @var string
	 */
	protected $_naming;

	/**
	 * @var array
	 */
	protected $_permissionTypes = array(
		0xC000 => 's',
		0xA000 => 'l',
		0x8000 => '-',
		0x6000 => 'b',
		0x4000 => 'd',
		0x2000 => 'c',
		0x1000 => 'p',
	);

	/**
	 * @var array
	 */
	protected $_roots;

	/**
	 * @var array
	 */
	protected $_scanListColumns = array(
		'scanID' => array('title' => 'Scan', 'class' => 'Scan Numeric'),
		'startDate' => array('title' => 'Start', 'class' => 'Date'),
		'endDate' => array('title' => 'End', 'class' => 'Date'),
		'errors' => array('title' => 'Errors', 'class' => 'Numeric'),
		'fileChanges' => array('title' => 'Files', 'class' => 'Numeric'),
		'directoryChanges' => array('title' => 'Dirs', 'class' => 'Numeric'),
		'fileDateOnlyChanges' => array('title' => 'Files d-o', 'class' => 'Numeric'),
		'directoryDateOnlyChanges' => array('title' => 'Dirs d-o', 'class' => 'Numeric'),
	);

	/**
	 * @var SeekR_Row
	 */
	protected $_scanRow;

	/**
	 * @var SeekR_Table
	 */
	protected $_scanTable;

	/**
	 * @var int
	 */
	protected $_sendMailOn = self::SEND_MAIL_ON_ALL_CHANGES;

	/**
	 * @var bool
	 */
	protected $_sendMailOnDateChange = true;

	/**
	 * @var array
	 */
	protected $_skipPatterns = array();

	/**
	 * @var string
	 */
	protected $_storageDirectoryMode = 0700;

	/**
	 * @var string
	 */
	protected $_storagePath;

	/**
	 * @var string
	 */
	protected $_summaryMailTemplate;

	/**
	 * @var array
	 */
	protected $_tableDefinitions = array(
		'scan' => array(
			'primary' => array('scanID'),
			'autoIncrement' => 'scanID',
		),
		'log' => array(
			'primary' => array('logID'),
			'autoIncrement' => 'logID',
		),
		'directory' => array(
			'primary' => array('directoryID'),
			'autoIncrement' => 'directoryID',
		),
		'directoryHistory' => array(
			'primary' => array('directoryID', 'scanID'),
		),
		'file' => array(
			'primary' => array('fileID'),
			'autoIncrement' => 'fileID',
		),
		'fileHistory' => array(
			'primary' => array('fileID', 'scanID'),
		),
	);

	/**
	 * @var array
	 */
	protected $_users = array();

	/**
	 * __construct
	 *
	 * @param array $options
	 * @throws Exception
	 */
	final public function __construct(array $options)
	{
		// Handlers
		set_error_handler(array($this, 'errorHandler'), E_ALL);
		set_exception_handler(array($this, 'exceptionHandler'));
		register_shutdown_function(array($this, 'shutdownHandler'));

		// Parent
		parent::__construct($options);

		// DB
		if (empty($this->_connection))
		{
			throw new Exception('Connection parameters are missing.');
		}

		$this->_db = new SeekR_Db($this->_connection);

		fwrite(STDOUT, "Connected to database.\n");

		// Tables
		foreach ($this->_tableDefinitions as $table => $defintion)
		{
			$this->{"_{$table}Table"} = new SeekR_Table(array(
				'db' => $this->_db,
				'name' => $table,
				'primary' => $defintion['primary'],
				'autoIncrement' => isset($defintion['autoIncrement']) ? $defintion['autoIncrement'] : null,
			));
		}
	}

	/**
	 * Set always check content
	 *
	 * @param bool $alwaysCheckContent
	 * @return SeekR
	 */
	final public function setAlwaysCheckContent($alwaysCheckContent)
	{
		$this->_alwaysCheckContent = $alwaysCheckContent;

		return $this;
	}

	/**
	 * Set columns of change list
	 *
	 * @param array $changeListColumns
	 * @return SeekR
	 */
	final public function setChangeListColumns(array $changeListColumns)
	{
		$this->_changeListColumns = $changeListColumns;

		return $this;
	}

	/**
	 * Set connection
	 *
	 * @param array $connection
	 * @throws Exception
	 * @return SeekR
	 */
	final public function setConnection(array $connection)
	{
		// Check
		if (!empty($this->_db))
		{
			throw new Exception('Connection to DB is already established.');
		}

		$this->_connection = $connection;

		return $this;
	}

	/**
	 * Set copy with date
	 *
	 * @param bool $copyWithDate
	 * @return SeekR
	 */
	final public function setCopyWithDate($copyWithDate)
	{
		$this->_copyWithDate = $copyWithDate;

		return $this;
	}

	/**
	 * Set date format
	 *
	 * @param string $dateFormat
	 * @return SeekR
	 */
	final public function setDateFormat($dateFormat)
	{
		$this->_dateFormat = $dateFormat;

		return $this;
	}

	/**
	 * Set columns of date only change list
	 *
	 * @param array $dateOnlyChangeListColumns
	 * @return SeekR
	 */
	final public function setDateOnlyChangeListColumns(array $dateOnlyChangeListColumns)
	{
		$this->_dateOnlyChangeListColumns = $dateOnlyChangeListColumns;

		return $this;
	}

	/**
	 * Set columns of error list
	 *
	 * @param array $errorListColumns
	 * @return SeekR
	 */
	final public function setErrorListColumns(array $errorListColumns)
	{
		$this->_errorListColumns = $errorListColumns;

		return $this;
	}

	/**
	 * Set error mail template
	 *
	 * @param string $errorMailTemplate
	 * @return SeekR
	 */
	final public function setErrorMailTemplate($errorMailTemplate)
	{
		$this->_errorMailTemplate = $errorMailTemplate;

		return $this;
	}

	/**
	 * Set mail date format
	 *
	 * @param string $mailDateFormat
	 * @return SeekR
	 */
	final public function setMailDateFormat($mailDateFormat)
	{
		$this->_mailDateFormat = $mailDateFormat;

		return $this;
	}

	/**
	 * Set mail date timezone
	 *
	 * @param DateTimeZone $mailDateTimezone
	 * @return SeekR
	 */
	final public function setMailDateTimezone(DateTimeZone $mailDateTimezone)
	{
		$this->_mailDateTimezone = $mailDateTimezone;

		return $this;
	}

	/**
	 * Set size of mail lists
	 *
	 * @param int $mailListSize
	 * @return SeekR
	 */
	final public function setMailListSize($mailListSize)
	{
		$this->_mailListSize = $mailListSize;

		return $this;
	}

	/**
	 * Set naming
	 *
	 * @param string $naming
	 * @return SeekR
	 */
	final public function setNaming($naming)
	{
		$this->_naming = $naming;

		return $this;
	}

	/**
	 * Set roots
	 *
	 * @param array $roots
	 * @return SeekR
	 */
	final public function setRoots(array $roots)
	{
		$this->_roots = $roots;

		return $this;
	}

	/**
	 * Set columns of scan list
	 *
	 * @param array $scanListColumns
	 * @return SeekR
	 */
	final public function setScanListColumns(array $scanListColumns)
	{
		$this->_scanListColumns = $scanListColumns;

		return $this;
	}

	/**
	 * Set send mail on
	 *
	 * @param int $sendMailOn
	 * @return SeekR
	 */
	final public function setSendMailOn($sendMailOn)
	{
		$this->_sendMailOn = $sendMailOn;

		return $this;
	}

	/**
	 * Set send mail on date change
	 *
	 * @param bool $sendMailOnDateChange
	 * @return SeekR
	 */
	final public function setSendMailOnDateChange($sendMailOnDateChange)
	{
		$this->_sendMailOnDateChange = $sendMailOnDateChange;

		return $this;
	}

	/**
	 * Set skip patterns
	 *
	 * @param array $skipPatterns
	 * @return SeekR
	 */
	final public function setSkipPatterns(array $skipPatterns)
	{
		$this->_skipPatterns = $skipPatterns;

		return $this;
	}

	/**
	 * Set storage directory mode
	 *
	 * @param int $storageDirectoryMode
	 * @return SeekR
	 */
	final public function setStorageDirectoryMode($storageDirectoryMode)
	{
		$this->_storageDirectoryMode = $storageDirectoryMode;

		return $this;
	}

	/**
	 * Set storage path
	 *
	 * @param string $storagePath
	 * @return SeekR
	 */
	final public function setStoragePath($storagePath)
	{
		$this->_storagePath = $this->_normalizePath($storagePath);

		return $this;
	}

	/**
	 * Set summary mail template
	 *
	 * @param string $summaryMailTemplate
	 * @return SeekR
	 */
	final public function setSummaryMailTemplate($summaryMailTemplate)
	{
		$this->_summaryMailTemplate = $summaryMailTemplate;

		return $this;
	}

	/**
	 * Error handler
	 *
	 * @param number $code
	 * @param string $text
	 * @param string $file
	 * @param number $line
	 */
	final public function errorHandler($code, $text, $file, $line)
	{
		// Trace
		$backtrace = debug_backtrace();
		array_shift($backtrace);

		// Out
		$infoText = self::$_errorCodes[$code] . " {$text}";
		$infoAt = " in {$file} on line {$line}\n{$this->_traceList($backtrace)}";
		fwrite(STDERR, $infoText . $infoAt);

		// Mail
		$this->_sendMail($this->_errorMailTemplate, array(
			'error' => "<span style=\"font-weight: bold\">{$this->_textToHtml($infoText)}</span>{$this->_textToHtml($infoAt)}"
		));

		exit;
	}

	/**
	 * Exception handler
	 *
	 * @param Exception $exception
	 */
	final public function exceptionHandler($exception)
	{
		// Out
		$infoText = $exception->getMessage();
		$infoAt = " in {$exception->getFile()} on line {$exception->getLine()}\n{$this->_traceList($exception->getTrace())}";
		fwrite(STDERR, $infoText . $infoAt);

		// Mail
		$this->_sendMail($this->_errorMailTemplate, array(
			'error' => "<span style=\"font-weight: bold\">{$this->_textToHtml($infoText)}</span>{$this->_textToHtml($infoAt)}"
		));

		exit;
	}

	/**
	 * Run
	 *
	 */
	final public function run()
	{
		// Open scan
		$this->_openScan();

		// Roots
		foreach ($this->_roots as $from => $to)
		{
			$this->_processRoot($from, $to);
		}

		// Close scan
		$this->_closeScan();

		// Summary mail
		if (is_array($this->_summaryMailTemplate))
		{
			$this->_sendSummaryMail();
		}

		// Log
		$this->_log(self::LOG_INFO, "---");
	}

	/**
	 * Shutdown handler
	 *
	 */
	final public function shutdownHandler()
	{
		// Testing last error
		$error = error_get_last();

		if (isset($error) && ($error['type'] & (E_ERROR | E_PARSE | E_USER_ERROR | E_COMPILE_ERROR | E_CORE_ERROR)))
		{
			// Out
			$infoText = self::$_errorCodes[$error['type']] . " {$error['message']}";
			$infoAt = " in {$error['file']} on line {$error['line']}\n";
			fwrite(STDERR, $infoText . $infoAt);

			// Mail
			$this->_sendMail($this->_errorMailTemplate, array(
				'error' => "<span style=\"font-weight: bold\">{$this->_textToHtml($infoText)}</span>{$this->_textToHtml($infoAt)}"
			));
		}
	}

	/**
	 * Test directory data
	 *
	 * @param SeekR_Row $row
	 * @param array $newValues
	 * @return boolean
	 */
	final protected function _isDirectoryChanged(SeekR_Row $row, array $newValues)
	{
		// Compare values
		$oldValues = $row->toArray();
		$operation = $oldValues['status'] == self::STATUS_REMOVED ? self::OPERATION_NEW : self::OPERATION_MODIFIED;

		return (bool) array_diff_assoc($newValues, $oldValues) || ($operation == self::OPERATION_NEW);
	}

	/**
	 * Entry matches any of the skip patterns
	 *
	 * @param string $pathname
	 * @return boolean
	 */
	final protected function _isEntryOfSkipPatterns($pathname)
	{
		foreach ($this->_skipPatterns as $pattern)
		{
			if (fnmatch($pattern, $pathname))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Argument info
	 *
	 * @param mixed $value
	 * @return string
	 */
	final static public function _argInfo($value)
	{
		// Object
		if (is_object($value))
		{
			return 'class: ' . get_class($value);
		}

		// Array
		if (is_array($value))
		{
			$list = array();

			foreach ($value as $key => $subvalue)
			{
				$list[] = self::_argInfo($key) . ' => ' . str_replace("\n", "\n    ", self::_argInfo($subvalue));
			}

			return 'array(' . (count($list) ? "\n    " . implode(",\n    ", $list) . "\n" : '') . ')';
		}

		// Null
		if (is_null($value))
		{
			return 'null';
		}

		// Bool
		if (is_bool($value))
		{
			return $value ? 'true' : 'false';
		}

		// Int, float
		if (is_int($value) || is_float($value))
		{
			return $value;
		}

		// String
		return str_replace(
			array('\n', '\t', "\n", "\t", '…'),
			array('\\n', '\\t', '\n', '\t', '…'),
			var_export(mb_strlen($value) > 100 ? mb_substr($value, 0, 100 - 1) . '…' : $value, true)
		);
	}

	/**
	 * Convert changes
	 *
	 * @param array $data
	 * @param array $columns
	 * @return array
	 */
	final protected function _changes(array $data, array $columns)
	{
		$list = array();
		$row = array();

		// Directory
		if (isset($data['path']))
		{
			if (count($columns) == 1)
			{
				$row = array(
					':class' => 'Directory',
					'name' => array('class' => 'Path', 'value' => $data['path'] . ' - ' . $data['scanID'])
				);
			}
			else
			{
				$list[] = array(
					':class' => 'Directory',
					'name' => array('colspan' => count($columns) + 1, 'class' => 'Path', 'value' => $data['path'] . ' - ' . $data['scanID']),
				);

				$row = array(':class' => 'DirectoryData', 'name' => '');
			}
		}
		else
		// File
		{
			$row = array(
				':class' => 'File',
				'name' => array('class' => 'Name', 'value' => $data['name']),
			);
		}

		foreach ($columns as $key)
		{
			if (isset($data[$key]))
			{
				$value = $data[$key];

				switch ($key)
				{
					case 'modifyDate':
						$value = $this->_formatMailDate($value);
						break;

					case 'size':
						$value = $value . ' B';
						break;

					case 'permissions':
						$value = $this->_normalizePermissions($value);
						break;

					case 'contentHash':
						$value = 'content';
						break;
				}

				$row[$key] = $value;
			}
			else
			{
				$row[$key] = null;
			}
		}

		if (count($row) > 2)
		{
			$list[] = $row;
		}

		return $list;
	}

	/**
	 * Close scan
	 *
	 */
	final protected function _closeScan()
	{
		$db = $this->_db;
		$scanRow = $this->_scanRow;
		$scanID = $scanRow->scanID;

		// Log
		$this->_log(self::LOG_INFO, 'Scan finished, summarizing.');

		// Data
		$scanRow->status = 'done';
		$scanRow->lastOperationDate = new SeekR_Expression('NOW()');
		$scanRow->endDate = new SeekR_Expression('NOW()');

		// Directory changes
		$scanRow->directoryChanges = new SeekR_Expression("
			(SELECT COUNT(*)
			FROM `directoryHistory`
			WHERE `scanID` = {$db->quote($scanID)}
				AND `dateOnly` = 'no')
		");

		// Directory date changes
		$scanRow->directoryDateOnlyChanges = new SeekR_Expression("
			(SELECT COUNT(*)
			FROM `directoryHistory`
			WHERE `scanID` = {$db->quote($scanID)}
				AND `dateOnly` = 'yes')
		");

		// File changes
		$scanRow->fileChanges = new SeekR_Expression("
			(SELECT COUNT(*)
			FROM `fileHistory`
			WHERE `scanID` = {$db->quote($scanID)}
				AND `dateOnly` = 'no')
		");

		// File date only changes
		$scanRow->fileDateOnlyChanges = new SeekR_Expression("
			(SELECT COUNT(*)
			FROM `fileHistory`
			WHERE `scanID` = {$db->quote($scanID)}
				AND `dateOnly` = 'yes')
		");

		// Errors
		$scanRow->errors = new SeekR_Expression("
			(SELECT COUNT(*)
			FROM `log`
			WHERE `scanID` = {$db->quote($scanID)}
				AND `type` = {$db->quote(self::LOG_ERROR)})
		");

		// Save
		$scanRow->save();

		// Log
		$this->_log(self::LOG_INFO, 'Scan closed.');
	}

	/**
	 * Copy file
	 *
	 * @param int $fileID
	 * @param DirectoryIterator $entries
	 * @return void|string
	 */
	final protected function _copyFile($fileID, DirectoryIterator $entries)
	{
		// Generate storage filename
		$storeAs = str_replace(
			array(
				'%scanID%',
				'%fileID%',
				'%fullPath%',
				'%relativePath%',
				'%filename%',
				'%to%',
			),
			array(
				$this->_scanRow->scanID,
				$fileID,
				$entries->getPath() . '/',
				ltrim(mb_substr($entries->getPath() . '/', mb_strlen($this->_currentRoot)), '/'),
				$entries->getFilename(),
				$this->_currentTo,
			),
			$this->_naming
		);

		// Destination
		$destination = $this->_storagePath . $storeAs;

		// Check destination directory
		$path = dirname($destination);

		if (!realpath($path))
		{
			// Create
			if (!mkdir($path, $this->_storageDirectoryMode, true))
			{
				$this->_log(self::LOG_ERROR, "	Can't create {$path}");

				return;
			}
		}

		// Copy
		copy($entries->getPathname(), $destination);

		// Date
		if ($this->_copyWithDate)
		{
			touch($destination, $entries->getMTime());
		}

		return '/' . $storeAs;
	}

	/**
	 * Count label
	 *
	 * @param number $count
	 * @param string $singular
	 * @param string $plural
	 * @return string
	 */
	final protected function _countLabel($count, $singular, $plural, $showZero)
	{
		return $showZero || $count <> 0 ? ("{$count} " . ($count == 1 ? $singular : $plural)) : null;
	}

	/**
	 * Create directory row
	 *
	 * @param array $data
	 * @return SeekR_Row
	 */
	final protected function _createDirectoryRow(array $data)
	{
		$scanID = $this->_scanRow->scanID;

		// Transaction
		$this->_db->beginTransaction();

		// Directory row
		$row = $this->_directoryTable->createRow(array(
			'status' => self::STATUS_ACTIVE,
			'firstDate' => $data['modifyDate'],
			'firstScanID' => $scanID,
			'modifyScanID' => $scanID,
		) + $data);
		$row->save();

		// History row
		unset($data['path']);

		$this->_directoryHistoryTable->createRow(array(
			'directoryID' => $row->directoryID,
			'scanID' => $scanID,
			'operation' => self::OPERATION_NEW,
			'dateOnly' => 'no',
		) + $data)->save();

		// Transaction
		$this->_db->commit();

		return $row;
	}

	/**
	 * Create file row
	 *
	 * @param array $data
	 * @return SeekR_Row
	 */
	final protected function _createFileRow(array $data)
	{
		$scanID = $this->_scanRow->scanID;

		// Row
		$row = $this->_fileTable->createRow(array(
			'status' => self::STATUS_ACTIVE,
			'firstDate' => $data['modifyDate'],
			'firstScanID' => $scanID,
			'modifyScanID' => $scanID,
			'lastScanID' => $scanID,
		) + $data);
		$row->save();

		// History row
		unset($data['name']);
		unset($data['directoryID']);

		$this->_fileHistoryTable->createRow(array(
			'fileID' => $row->fileID,
			'scanID' => $scanID,
			'operation' => self::OPERATION_NEW,
			'dateOnly' => 'no',
		) + $data)->save();

		return $row;
	}

	/**
	 * Get change list
	 *
	 * @param array $scanIDs
	 * @param bool $dateOnly
	 * @param int $limit
	 * @return string
	 */
	final protected function _getChangeList($scanIDs, $dateOnly, $limit)
	{
		$db = $this->_db;
		$columns = $dateOnly ? array('modifyDate') : array('operation', 'modifyDate', 'size', 'contentHash', 'owner', 'group', 'permissions', 'linkTarget');
		$dateOnlyString = $dateOnly ? 'yes' : 'no';

		// Get IDs
		$query = $db->query("
			SELECT `directory`.`directoryID`, `file`.`fileID`
			FROM `directory`
			LEFT JOIN `file`
				ON `file`.`directoryID` = `directory`.`directoryID`
				AND EXISTS(
					SELECT *
					FROM `fileHistory`
					WHERE `fileHistory`.`fileID` = `file`.`fileID`
						AND `scanID` IN ({$db->quote($scanIDs)})
						AND `dateOnly` = {$db->quote($dateOnlyString)})
			WHERE EXISTS(
					SELECT *
					FROM `directoryHistory`
					WHERE `directoryHistory`.`directoryID` = `directory`.`directoryID`
						AND `scanID` IN ({$db->quote($scanIDs)})
						AND `dateOnly` = {$db->quote($dateOnlyString)})
				OR `file`.`fileID` IS NOT NULL
			LIMIT {$limit}
		");

		$directoryIDs = array();
		$fileIDs = array();

		while ($row = $query->fetch(PDO::FETCH_NUM))
		{
			$directoryIDs[$row[0]] = 1;

			if (isset($row[1]))
			{
				$fileIDs[$row[1]] = 1;
			}
		}

		// Directories
		$directories = array();
		$directoriesForFiles = array();

		if ($directoryIDs)
		{
			$query = $db->query("
				SELECT
					`directory`.`directoryID`,
					`directory`.`path`,
					`directoryHistory`.`scanID`,
					`directoryHistory`.`modifyDate`
" . (!$dateOnly ? ",
					`directoryHistory`.`operation`,
					`directoryHistory`.`owner`,
					`directoryHistory`.`group`,
					`directoryHistory`.`permissions`,
					`directoryHistory`.`linkTarget`" : '') . "
				FROM `directory`
				LEFT JOIN `directoryHistory`
					ON `directoryHistory`.`directoryID` = `directory`.`directoryID`
					AND `scanID` IN ({$db->quote($scanIDs)})
					AND `dateOnly` = {$db->quote($dateOnlyString)}
				WHERE `directory`.`directoryID` IN ({$db->quote(array_keys($directoryIDs))})
			");

			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				$data = array(
					'changes' => $this->_changes($row, $columns),
					'files' => array()
				);

				if ($row['scanID'])
				{
					$directories["{$row['directoryID']}:{$row['scanID']}"] = $data;
				}
				else
				{
					$directoriesForFiles[$row['directoryID']] = $data;
				}
			}
		}

		// Files
		if ($fileIDs)
		{
			$query = $db->query("
				SELECT
					`file`.`directoryID`,
					`file`.`name`,
					`fileHistory`.`scanID`,
					`fileHistory`.`modifyDate`
" . (!$dateOnly ? ",
					`fileHistory`.`operation`,
					`fileHistory`.`owner`,
					`fileHistory`.`group`,
					`fileHistory`.`permissions`,
					`fileHistory`.`linkTarget`,
					`fileHistory`.`size`,
					`fileHistory`.`contentHash`" : '') . "
				FROM `fileHistory`
				JOIN `file`
					ON `file`.`fileID` = `fileHistory`.`fileID`
				JOIN `directory`
					ON `directory`.`directoryID` = `file`.`directoryID`
				WHERE `scanID` IN ({$db->quote($scanIDs)})
					AND `fileHistory`.`fileID` IN ({$db->quote(array_keys($fileIDs))})
			");

			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				$key = "{$row['directoryID']}:{$row['scanID']}";

				if (!array_key_exists($key, $directories))
				{
					$directories[$key] = $directoriesForFiles[$row['directoryID']];
					$directories[$key]['changes'][0]['name']['value'] .= $row['scanID'];
				}

				$directories[$key]['files'] = array_merge($directories[$key]['files'], $this->_changes($row, $columns));
			}
		}

		// Build list
		ksort($directories);
		$list = array();
		foreach ($directories as $directory)
		{
			$list = array_merge($list, $directory['changes']);
			$list = array_merge($list, $directory['files']);
		}

		return $dateOnly
			? $this->_table('DateOnlyChanges', $this->_dateOnlyChangeListColumns, $list)
			: $this->_table('Changes', $this->_changeListColumns, $list);
	}

	/**
	 * Get error list
	 *
	 * @param int $limit
	 * @return string
	 */
	final protected function _getErrorList($limit)
	{
		$db = $this->_db;

		return $this->_table('Errors', $this->_errorListColumns, $db->query("
			SELECT `date`, `text`
			FROM `log`
			WHERE `scanID` = {$db->quote($this->_scanRow->scanID)}
				AND `type` = {$db->quote(self::LOG_ERROR)}
			LIMIT {$limit}
		")->fetchAll(PDO::FETCH_ASSOC));
	}

	/**
	 * Get scan list for mail
	 *
	 * @return string
	 */
	final protected function _getScanListForMail()
	{
		$summary = array(
			'ids' => array(),
			'errors' => 0,
			'fileChanges' => 0,
			'fileDateOnlyChanges' => 0,
			'directoryChanges' => 0,
			'directoryDateOnlyChanges' => 0,
		);

		$list = array();

		foreach ($this->_db->query("
			SELECT
				`scanID`,
				`startDate`,
				`endDate`,
				`errors`,
				`fileChanges`,
				`fileDateOnlyChanges`,
				`directoryChanges`,
				`directoryDateOnlyChanges`
			FROM `scan`
			WHERE `status` = 'done'
				AND `mailSent` = 'no'
		")->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			// Result
			$summary['ids'][] = $row['scanID'];
			$summary['errors'] += $row['errors'];
			$summary['fileChanges'] += $row['fileChanges'];
			$summary['fileDateOnlyChanges'] += $row['fileDateOnlyChanges'];
			$summary['directoryChanges'] += $row['directoryChanges'];
			$summary['directoryDateOnlyChanges'] += $row['directoryDateOnlyChanges'];

			// Date
			$row['startDate'] = $this->_formatMailDate($row['startDate']);
			$end = explode(' ', $this->_formatMailDate($row['endDate']));
			$row['endDate'] = $end[1];

			// Numeric values
			foreach ($row as $key => $value)
			{
				if (is_numeric($value) && $value != 0)
				{
					$row[$key] = array(
						'class' => 'Highlight',
						'value' => $value,
					);
				}
			}

			$list[] = $row;
		}

		// Footer data
		$footer = array_merge(array(
			'scanID' => null,
			'startDate' => null,
			'endDate' => null,
		), $summary);

		// Numeric values
		foreach ($footer as $key => $value)
		{
			if (is_numeric($value) && $value != 0)
			{
				$footer[$key] = array(
					'class' => 'Highlight',
					'value' => $value,
				);
			}
		}

		// Generate content
		$summary['content'] = $this->_table('Scans', $this->_scanListColumns, $list, array($footer));

		return $summary;
	}

	/**
	 * Format mail date
	 *
	 * @param string $date
	 * @return string
	 */
	final protected function _formatMailDate($date)
	{
		$date = new DateTime($date);
		return $date
			->setTimezone($this->_mailDateTimezone)
			->format($this->_mailDateFormat);
	}

	/**
	 * Log
	 *
	 * @param string $type
	 * @param string $text
	 */
	final protected function _log($type, $text)
	{
		// Out to standard out or error
		fwrite($type == self::LOG_ERROR ? STDERR : STDOUT, '[' . date($this->_dateFormat) . '] ' . $text . "\n");

		// To database
		$this->_logTable->createRow(array(
			'scanID' => $this->_scanRow->scanID,
			'date' => new SeekR_Expression('NOW()'),
			'type' => $type,
			'text' => $text,
		))->save();
	}

	/**
	 * Normalize date
	 *
	 * @param int $timestamp
	 * @return string
	 */
	final protected function _normalizeDate($timestamp)
	{
		return date($this->_dateFormat, $timestamp);
	}

	/**
	 * Normalize group
	 *
	 * @param int $gid
	 * @return string
	 */
	final protected function _normalizeGroup($gid)
	{
		// Cached
		if (isset($this->_groups[$gid]))
		{
			return $this->_groups[$gid];
		}

		// Get
		$group = posix_getgrgid($gid);
		$this->_groups[$gid] = $group['name'];

		return $group['name'];
	}

	/**
	 * Normalize path
	 *
	 * @param string $path
	 * @return string
	 */
	final protected function _normalizePath($path)
	{
		return mb_substr($path, -1) != '/' ? $path . '/' : $path;
	}

	/**
	 * Normalize permissions
	 *
	 * @param int $permissions
	 * @return string
	 */
	final protected function _normalizePermissions($permissions)
	{
		// Owner
		$info = ($permissions & 0x0100 ? 'r' : '-')
			. ($permissions & 0x0080 ? 'w' : '-')
			. ($permissions & 0x0040
				? ($permissions & 0x0800 ? 's' : 'x')
				: ($permissions & 0x0800 ? 'S' : '-'))

		// Group
			. ($permissions & 0x0020 ? 'r' : '-')
			. ($permissions & 0x0010 ? 'w' : '-')
			. ($permissions & 0x0008
				? ($permissions & 0x0400 ? 's' : 'x')
				: ($permissions & 0x0400 ? 'S' : '-'))

		// World
			. ($permissions & 0x0004 ? 'r' : '-')
			. ($permissions & 0x0002 ? 'w' : '-')
			. ($permissions & 0x0001
				? ($permissions & 0x0200 ? 't' : 'x')
				: ($permissions & 0x0200 ? 'T' : '-'));

		return $this->_permissionTypes[$permissions & 0xf000] . $info;
	}

	/**
	 * Normalize user
	 *
	 * @param int $uid
	 * @return string
	 */
	final protected function _normalizeUser($uid)
	{
		// Cached
		if (isset($this->_users[$uid]))
		{
			return $this->_users[$uid];
		}

		// Get
		$user = posix_getpwuid($uid);
		$this->_users[$uid] = $user['name'];

		return $user['name'];
	}

	/**
	 * Open scane
	 *
	 */
	final protected function _openScan()
	{
		// Scan row
		if (!($this->_scanRow = $this->_scanTable->fetchRow(array('status' => 'progress'))))
		{
			// Create
			$this->_scanRow = $this->_scanTable->createRow(array(
				'startDate' => new SeekR_Expression('NOW()'),
				'lastOperationDate' => new SeekR_Expression('NOW()'),
				'status' => 'progress',
			));
			$this->_scanRow->save();

			// Log
			$this->_log(self::LOG_INFO, 'Scan started: #' . $this->_scanRow->scanID);
		}
		else
		// Continue
		{
			// Update
			$this->_scanRow->lastOperationDate = new SeekR_Expression('NOW()');
			$this->_scanRow->save();

			// Log
			$this->_log(self::LOG_INFO, 'Scan resumed: #' . $this->_scanRow->scanID);
		}
	}

	/**
	 * Process directory
	 *
	 * @param string $path
	 */
	final protected function _processDirectory($path)
	{
		$scanID = $this->_scanRow->scanID;

		// Load row when exists
		$directoryRow = $this->_directoryTable->fetchRow(array('path' => $path));

		// Already scanned
		if ($directoryRow && $directoryRow->lastScanID == $scanID)
		{
			return;
		}

		// Existing directory
		if (is_dir($path))
		{
			// Accessible
			if (is_readable($path))
			{
				$this->_processDirectoryActive($path, $directoryRow);
			}
			else
			// Non-accessible
			{
				$this->_log(self::LOG_ERROR, "	{$path} cannot be read.");
			}
		}
		else
		// Removed directory
		if ($directoryRow)
		{
			fwrite(STDOUT, "	{$path} has been removed.\n");

			$this->_processDirectoryRemoved($directoryRow);
		}

		// List removed files
		if ($directoryRow)
		{
			$db = $this->_db;

			$query = $db->query("
				SELECT `name`
				FROM `file`
				WHERE `directoryID` = {$db->quote($directoryRow->directoryID)}
					AND `modifyScanID` = {$db->quote($scanID)}
					AND `status` = {$db->quote(self::STATUS_REMOVED)}
			");

			while ($row = $query->fetch(PDO::FETCH_ASSOC))
			{
				fwrite(STDOUT, "	{$path}{$row['name']} has been removed.\n");
			}
		}
	}

	/**
	 * Process active directory
	 *
	 * @param string $path
	 * @param SeekR_Row $directoryRow
	 */
	final protected function _processDirectoryActive($path, SeekR_Row $directoryRow = null)
	{
		$entries = new DirectoryIterator($path);
		$directoryChanged = false;

		$data = array(
			'modifyDate' => $this->_normalizeDate(filemtime($path)),
			'owner' => $this->_normalizeUser(fileowner($path)),
			'group' => $this->_normalizeGroup(filegroup($path)),
			'permissions' => fileperms($path),
			'linkTarget' => is_link($path) ? readlink($path) : null,
		);

		// New directory row
		if (!$directoryRow)
		{
			fwrite(STDOUT, "	{$path} is new.\n");

			$directoryRow = $this->_createDirectoryRow(array(
				'path' => $path,
			) + $data);
		}
		else
		// Check directory data
		if ($this->_isDirectoryChanged($directoryRow, $data))
		{
			fwrite(STDOUT, "	{$path} has been changed.\n");

			$directoryChanged = true;
		}

		// Entries of non-linked directories
		if (!is_link($path))
		{
			foreach ($entries as $entry)
			{
				$pathname = $entries->getPathname();

			 	if (!$entries->isDot() && !$this->_isEntryOfSkipPatterns($pathname))
			 	{
			 		// Subdirectory
			 		if ($entries->isDir())
			 		{
			 			$this->_processDirectory($pathname . '/');
			 		}
			 		else
			 		// File
			 		if ($entries->isFile())
			 		{
			 			$this->_processFile($directoryRow->directoryID, $entries);
			 		}
			 	}
			}
		}

		// Removed files
		$this->_processDirectoryRemovedFiles($directoryRow);

		// Transaction
		$this->_db->beginTransaction();

		// Update row
		if ($directoryChanged)
		{
			$this->_updateDirectoryRow($directoryRow, $data);
		}

		$directoryRow->lastScanID = $this->_scanRow->scanID;
		$directoryRow->save();

		// Scan row update
		$this->_scanRow->lastOperationDate = new SeekR_Expression('NOW()');
		$this->_scanRow->save();

		// Transaction
		$this->_db->commit();
	}

	/**
	 * Process removed directory
	 *
	 * @param SeekR_Row $directoryRow
	 */
	final protected function _processDirectoryRemoved(SeekR_Row $directoryRow)
	{
		$scanID = $this->_scanRow->scanID;
		$db = $this->_db;

		// Transaction
		$db->beginTransaction();

		// Set directory
		$directoryRow
			->setFromArray(array(
				'status' => self::STATUS_REMOVED,
				'modifyDate' => new SeekR_Expression('NOW()'),

				'modifyScanID' => $scanID,
				'lastScanID' => $scanID,
			))
			->save();

		// Add directory history entry
		$this->_directoryHistoryTable->createRow(array(
			'directoryID' => $directoryRow->directoryID,
			'scanID' => $scanID,
			'operation' => self::OPERATION_REMOVED,
			'modifyDate' => new SeekR_Expression('NOW()'),
		))->save();

		// Set files
		$db->query("
			UPDATE `file`
			SET `status` = {$db->quote(self::STATUS_REMOVED)},
				`modifyDate` = NOW(),
				`modifyScanID` = {$db->quote($scanID)},
				`lastScanID` = {$db->quote($scanID)}
			WHERE `directoryID` = {$db->quote($directoryRow->directoryID)}
		");

		// Add history entry for files
		$db->query("
			INSERT INTO `fileHistory` (`fileID`, `scanID`, `operation`, `modifyDate`)
			SELECT `fileID`, {$db->quote($scanID)}, {$db->quote(self::OPERATION_REMOVED)}, NOW()
			FROM `file`
			WHERE `directoryID` = {$db->quote($directoryRow->directoryID)}");

		// Transaction
		$db->commit();
	}

	/**
	 * Process removed files of directory
	 *
	 * @param SeekR_Row $row
	 */
	final protected function _processDirectoryRemovedFiles(SeekR_Row $directoryRow)
	{
		$scanID = $this->_scanRow->scanID;
		$db = $this->_db;

		// Transaction
		$db->beginTransaction();

		// Add history entry for files
		$db->query("
			INSERT INTO `fileHistory` (`fileID`, `scanID`, `operation`, `modifyDate`)
			SELECT `fileID`, {$db->quote($scanID)}, {$db->quote(self::OPERATION_REMOVED)}, NOW()
			FROM `file`
			WHERE `directoryID` = {$db->quote($directoryRow->directoryID)}
				AND `status` <> {$db->quote(self::STATUS_REMOVED)}
				AND `lastScanID` < {$db->quote($scanID)}
			");

		// Set files
		$db->query("
			UPDATE `file`
			SET `status` = {$db->quote(self::STATUS_REMOVED)},
				`modifyDate` = NOW(),
				`modifyScanID` = {$db->quote($scanID)},
				`lastScanID` = {$db->quote($scanID)}
			WHERE `directoryID` = {$db->quote($directoryRow->directoryID)}
				AND `status` <> {$db->quote(self::STATUS_REMOVED)}
				AND `lastScanID` < {$db->quote($scanID)}
		");

		// Transaction
		$db->commit();
	}

	/**
	 * Process file
	 *
	 * @param int $directoryID
	 * @param DirectoryIterator $entries
	 */
	final protected function _processFile($directoryID, DirectoryIterator $entries)
	{
		$scanID = $this->_scanRow->scanID;

		// Load row when exists
		$fileRow = $this->_fileTable->fetchRow(array(
			'directoryID' => $directoryID,
			'name' => $entries->getFilename(),
		));

		// Already scanned
		if ($fileRow && $fileRow->lastScanID == $scanID)
		{
			return;
		}

		$pathname = $entries->getPathname();

		// Data
		$data = array(
			'modifyDate' => $this->_normalizeDate($entries->getMTime()),
			'owner' => $this->_normalizeUser($entries->getOwner()),
			'group' => $this->_normalizeGroup($entries->getGroup()),
			'permissions' => $entries->getPerms(),
			'size' => $entries->getSize(),
			'linkTarget' => $entries->isLink() ? $entries->getLinkTarget() : null,
		);

		// Content
		$contentHash = null;

		if (!$entries->isLink()
			&& ($this->_alwaysCheckContent
				|| !$fileRow
				|| $fileRow->modifyDate != $data['modifyDate']
				|| $fileRow->size != $data['size']))
		{
			// Non-accessible file
			if (!is_readable($pathname))
			{
				$this->_log(self::LOG_ERROR, "	{$pathname} cannot be read.");
				if ($fileRow)
				{
					$contentHash = $fileRow->contentHash;
				}
			}
			else
			// Get hash
			{
				$contentHash = md5_file($pathname);
			}
		}

		// Transaction
		$this->_db->beginTransaction();

		// New row
		if ($newRow = !$fileRow)
		{
			fwrite(STDOUT, "	{$pathname} is new.\n");

			$fileRow = $this->_createFileRow(array(
				'directoryID' => $directoryID,
				'name' => $entries->getFilename(),
			) + $data);
		}

		// Store values
		$oldValues = $fileRow->toArray();

		// Content
		if ($fileRow->contentHash != $contentHash)
		{
			$data['contentHash'] = $contentHash;

			if ($this->_storagePath)
			{
				$data['storedAs'] = $this->_copyFile($fileRow->fileID, $entries);
			}
		}

		// Update row
		$this->_updateFileRow($pathname, $fileRow, $data);

		$fileRow->lastScanID = $scanID;
		$fileRow->save();

		// Scan row update
		$this->_scanRow->lastOperationDate = new SeekR_Expression('NOW()');
		$this->_scanRow->save();

		// Transaction
		$this->_db->commit();
	}

	/**
	 * Process root
	 *
	 * @param string $from
	 * @param string $to
	 */
	final protected function _processRoot($from, $to)
	{
		$from = $this->_normalizePath($from);
		$this->_currentRoot = $from;
		$this->_currentTo = $this->_normalizePath($to);

		// Source
		if (($fromPath = realpath($from)) === false)
		{
			$this->_log(self::LOG_ERROR, "Processing {$from}: source problem.");

			return;
		}

		$fromPath .= '/';

		// Log
		$this->_log(self::LOG_INFO, "Processing {$from}");
		$this->_log(self::LOG_INFO, "	Resolved as {$fromPath}");

		// Process recursive
		$this->_processDirectory($fromPath);

		// Check lost directories
		$db = $this->_db;
		$scanID = $this->_scanRow->scanID;

		while ($row = $this->_directoryTable->fetchRow("
			`status` <> {$db->quote(self::STATUS_REMOVED)}
				AND `lastScanID` < {$db->quote($scanID)}
				AND LEFT(`path`, {$db->quote(mb_strlen($from))}) = {$db->quote($from)}
			ORDER BY `path`")
		)
		{
			$this->_processDirectory($row->path);
		}

		// Log
		$this->_log(self::LOG_INFO, "Processing of {$from} finished.");
	}

	/**
	 * Send mail
	 *
	 * @param array $template
	 * @param array $replaceValues
	 * @return boolean
	 */
	final protected function _sendMail(array $template, array $replaceValues)
	{
		$from = explode('<', $template['from']);
		$headers = array(
			'From: ' . mb_encode_mimeheader($from[0]) . (isset($from[1]) ? '<' . $from[1] : ''),
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset="utf-8"',
			'Content-Transfer-Encoding: 8bit',
		);

		$keys = array_map(function ($key) { return "%{$key}%"; }, array_keys($replaceValues));
		$values = array_values($replaceValues);

		return mb_send_mail(
			is_array($template['to']) ? implode(', ', $template['to']) : $template['to'],
			str_replace($keys, $values, $template['subject']),
			str_replace($keys, $values, $template['body']),
			implode("\n", $headers)
		);
	}

	/**
	 * Send summary mail
	 *
	 */
	final protected function _sendSummaryMail()
	{
		$scanRow = $this->_scanRow;

		// Send decision
		$sendSummary = $this->_sendMailOn == self::SEND_MAIL_ON_SCAN || $scanRow->scanID == 1 || $scanRow->errors;

		if (!$sendSummary)
		{
			// Modes
			switch ($this->_sendMailOn)
			{
				// Only changes
				case self::SEND_MAIL_ON_CONTENT_CHANGES:
					$sendSummary = $scanRow->fileChanges || $scanRow->directoryChanges;
					break;

				// Changes and date-only changes
				case self::SEND_MAIL_ON_ALL_CHANGES:
					$sendSummary = $scanRow->fileChanges || $scanRow->fileDateOnlyChanges || $scanRow->directoryChanges || $scanRow->directoryDateOnlyChanges;
			}

			// Date change
			if (!$sendSummary && $this->_sendMailOnDateChange)
			{
				$currentDate = new DateTime($scanRow->startDate);
				$currentDate->setTimezone($this->_mailDateTimezone);

				$previousScanRow = $this->_scanTable->fetchRow(array('scanID' => $scanRow->scanID - 1));
				$previousDate = new DateTime($previousScanRow->startDate);
				$previousDate->setTimezone($this->_mailDateTimezone);

				$sendSummary = $currentDate->format('Y-m-d') != $previousDate->format('Y-m-d');
			}
		}

		// Skip unwanted mail
		if (!$sendSummary)
		{
			$this->_log(self::LOG_INFO, "Mail skipped.");
			return;
		}

		// Init
		$db = $this->_db;
		$mailListSize = $this->_mailListSize;
		$content = '<h3>Summary</h3>';

		// List of scans without sent mail
		$scans = $this->_getScanListForMail();
		$content .= $scans['content'];

		// Get errors
		if ($scanRow->errors)
		{
			$content .= '<div class="List"><h3>Errors</h3> '
				. $scanRow->errors
				. $this->_getErrorList($mailListSize)
				. ($scanRow->errors > $mailListSize ? "<br />Partial list. More than {$mailListSize} errors, check full list.<br />" : '')
				. '</div>';
		}

		// No list on first run
		if ($scanRow->scanID > 1)
		{
			// Changes
			if ($count = $scans['directoryChanges'] + $scans['fileChanges'])
			{
				$content .= '<div class="List"><h3>Changes</h3> '
					. $this->_countLabel($scans['fileChanges'], 'file', 'files', true)
					. ' / '
					. $this->_countLabel($scans['directoryChanges'], 'directory', 'directories', true)
					. $this->_getChangeList($scans['ids'], false, $mailListSize)
					. ($count > $mailListSize ? "<br />Partial list. More than {$mailListSize} changes, check full list.<br />" : '')
					. '</div>';
			}

			// Date-only changes
			if ($count = $scans['directoryDateOnlyChanges'] + $scans['fileDateOnlyChanges'])
			{
				$content .= '<div class="List"><h3>Date-only changes</h3> '
					. $this->_countLabel($scans['fileDateOnlyChanges'], 'file', 'files', true)
					. ' / '
					. $this->_countLabel($scans['directoryDateOnlyChanges'], 'directory', 'directories', true)
					. $this->_getChangeList($scans['ids'], true, $mailListSize)
					. ($count > $mailListSize ? "<br />Partial list. More than {$mailListSize} changes, check full list.<br />" : '')
					. '</div>';
			}
		}

		// Result
		$result = implode(', ', array_filter(array(
			$this->_countLabel($scanRow->errors, 'error', 'errors', false),
			$this->_countLabel($scans['fileChanges'], 'file', 'files', false),
			$this->_countLabel($scans['directoryChanges'], 'directory', 'directories', false),
			$this->_countLabel($scans['fileDateOnlyChanges'], 'd-o file', 'd-o files', false),
			$this->_countLabel($scans['directoryDateOnlyChanges'], 'd-o directory', 'd-o directories', false),
		)));

		// Transaction
		$db->beginTransaction();

		// Send mail
		if ($this->_sendMail($this->_summaryMailTemplate, array(
			'scanID' => $scanRow->scanID,
			'start' => $scanRow->startDate,
			'end' => $scanRow->endDate,
			'result' => $result ? $result : 'no changes',
			'errors' => $scanRow->errors,
			'directoryChanges' => $this->_countLabel($scans['directoryChanges'], 'directory', 'directories', true),
			'directoryDateOnlyChanges' => $this->_countLabel($scans['directoryDateOnlyChanges'], 'directory', 'directories', true),
			'fileChanges' => $this->_countLabel($scans['fileChanges'], 'file', 'files', true),
			'fileDateOnlyChanges' => $this->_countLabel($scans['fileDateOnlyChanges'], 'file', 'files', true),
			'content' => $content,
		)))
		{
			$this->_log(self::LOG_INFO, "Mail sent.");

			// Scan - mailSent
			$db->query("
				UPDATE `scan`
				SET `mailSent` = 'yes'
				WHERE `mailSent` = 'no'
			");

			// Transaction
			$db->commit();
		}
		else
		{
			$this->_log(self::LOG_ERROR, "Can't send mail.");

			// Transaction
			$db->rollBack();
		}
	}

	/**
	 * Generate table
	 *
	 * @param string $class
	 * @param array $columns
	 * @param array $list
	 * @param array $footerList
	 * @return string
	 */
	final protected function _table($tableClass, array $columns, array $list, array $footerList = array())
	{
		// Header
		$header = array();

		foreach ($columns as $column)
		{
			// Cell
			$header[] .= '<th' . (isset($column['class']) ? ' class="' . $column['class'] . '"' : '') . ">{$this->_textToHtml($column['title'])}</th>";
		}

		// Rows
		$rows = $this->_tableRows($columns, $list);

		// Footer
		$footerRows = $this->_tableRows($columns, $footerList);

		// Build
		return
			"<table class=\"{$tableClass}\">"
				. '<thead><tr>' . implode("\n", $header) . '</tr></thead>'
				. ($footerRows ? '<tfoot>' . implode("\n", $footerRows) . '</tfoot>' : '')
				. '<tbody>' . implode("\n", $rows) . '</tbody>'
			. '</table>';
	}

	/**
	 * Generate table - rows
	 *
	 * @param array $columns
	 * @param array $list
	 * @return array
	 */
	final protected function _tableRows(array $columns, array $list)
	{
		// Rows
		$rows = array();

		foreach ($list as $row)
		{
			// Cells
			$html = '';
			foreach ($columns as $key => $column)
			{
				if (array_key_exists($key, $row))
				{
					// Cell value
					$value = $row[$key];

					// Cell attributes
					$class = isset($column['class']) ? array($column['class']) : array();
					$attributes = '';

					if (is_array($value))
					{
						if (isset($value['class'])) { $class[] = $value['class']; }
						if (isset($value['colspan'])) { $attributes .= ' colspan="' . $value['colspan'] . '"'; }

						$value = $value['value'];
					}

					if ($class)
					{
						$attributes .= ' class="' . implode(' ', $class) . '"';
					}

					// Cell
					$html .= "<td{$attributes}>{$this->_textToHtml($value)}</td>";
				}
			}

			// Row
			$rows[] = '<tr' . (isset($row[':class']) ? ' class="' . $row[':class'] . '"' : '') . ">{$html}</tr>";
		}

		return $rows;
	}

	/**
	 * Convert text to HTML
	 *
	 * @param string $text
	 * @return string
	 */
	final protected function _textToHtml($text)
	{
		return nl2br(str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', htmlspecialchars($text)));
	}

	/**
	 * Trace list builder
	 *
	 * @param array $trace
	 * @return string
	 */
	final protected function _traceList(array $trace)
	{
		$list = '';

		// Callings
		foreach ($trace as $id => $caller)
		{
			if (is_array($args = $caller['args']))
			{
				$list .= "\t$id: "
					// File or class
					. (isset($caller['file']) ? "{$caller['file']} #{$caller['line']}: " : "({$caller['class']}){$caller['type']}")

					// Function
					. $caller['function'] . '(';

				// Parameters
				$parameters = array();
				foreach ($args as $value)
				{
					$parameters[] = str_replace("\n", "\n\t\t\t", self::_argInfo($value));
				}

				// Multi or single-parametered
				$list .= (count($parameters) > 1 ? "\n\t\t" . implode(",\n\t\t", $parameters) . "\n\t\t" : reset($parameters)) . ")\n";
			}
		}

		return $list;
	}

	/**
	 * Update directory row
	 *
	 * @param SeekR_Row $row
	 * @param array $newValues
	 */
	final protected function _updateDirectoryRow(SeekR_Row $row, array $newValues)
	{
		// Data
		$oldValues = $row->toArray();

		if ($oldValues['status'] == self::STATUS_REMOVED)
		{
			$operation = self::OPERATION_NEW;
			$oldValues = array();
		}
		else
		{
			$operation = self::OPERATION_MODIFIED;
		}

		$difference = array_diff_assoc($newValues, $oldValues);
		$scanID = $this->_scanRow->scanID;

		// Has history row for this scan
		if ($historyRow = $this->_directoryHistoryTable->fetchRow(array(
			'directoryID' => $row->directoryID,
			'scanID' => $scanID,
		)))
		{
			$historyRow
				->setFromArray($difference)
				->save();
		}
		else
		// Store changes in new history row
		{
			$this->_directoryHistoryTable->createRow(array(
				'directoryID' => $row->directoryID,
				'scanID' => $scanID,
				'operation' => $operation,
				'dateOnly' => count($difference) == 1 && isset($difference['modifyDate']) ? 'yes' : 'no'
			) + $difference)->save();
		}

		// Directory row update
		$row
			->setFromArray(array(
				'modifyScanID' => $scanID,
				'status' => self::STATUS_ACTIVE,
			) + $difference);
	}

	/**
	 * Update file row
	 *
	 * @param string $pathname
	 * @param SeekR_Row $row
	 * @param array $newValues
	 */
	final protected function _updateFileRow($pathname, SeekR_Row $row, array $newValues)
	{
		// Data
		$oldValues = $row->toArray();

		if ($oldValues['status'] == self::STATUS_REMOVED)
		{
			$operation = self::OPERATION_NEW;
			$oldValues = array();
		}
		else
		{
			$operation = self::OPERATION_MODIFIED;
		}

		$difference = array_diff_assoc($newValues, $oldValues);
		$scanID = $this->_scanRow->scanID;

		// Changed
		if ($difference || $operation == self::OPERATION_NEW)
		{
			// Has history row for this scan
			if ($historyRow = $this->_fileHistoryTable->fetchRow(array(
				'fileID' => $row->fileID,
				'scanID' => $scanID,
			)))
			{
				$historyRow
					->setFromArray($difference)
					->save();
			}
			else
			// Store changes in new history row
			{
				if ($operation != self::OPERATION_NEW)
				{
					fwrite(STDOUT, "	{$pathname} has been changed.\n");
				}

				$this->_fileHistoryTable->createRow(array(
					'fileID' => $row->fileID,
					'scanID' => $scanID,
					'operation' => $operation,
					'dateOnly' => count($difference) == 1 && isset($difference['modifyDate']) ? 'yes' : 'no'
				) + $difference)->save();
			}

			// Row update
			$row->setFromArray(array(
				'modifyScanID' => $scanID,
				'status' => self::STATUS_ACTIVE,
				) + $difference);
		}
	}
}
