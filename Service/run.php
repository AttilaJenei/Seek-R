<?php
/**
 * @author Attila Jenei
 * @since May 2, 2013
 * @copyright 2013 Attila Jenei
 * @link http://www.attilajenei.com
 */

fwrite(STDOUT, "AJ's Seek-R, Version 1.00\n");
fwrite(STDOUT, "	Product website: http://www.seek-r.com\n");
fwrite(STDOUT, "	Website: http://www.attilajenei.com\n\n");

function start()
{
	require(__DIR__ . '/Classes/SeekR/Optioned.php');
	require(__DIR__ . '/Classes/SeekR/Expression.php');
	require(__DIR__ . '/Classes/SeekR/Db.php');
	require(__DIR__ . '/Classes/SeekR/Row.php');
	require(__DIR__ . '/Classes/SeekR/Table.php');
	require(__DIR__ . '/Classes/SeekR.php');

	$hostname = preg_replace('[^A-Za-z0-9\.]', '_', gethostname());
	$options = require('Config/' . $hostname . '.php');

	$seekR = new SeekR($options);
	$seekR->run();
}

start();
