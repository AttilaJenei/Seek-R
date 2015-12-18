<?php

/**
 * @author Attila Jenei
 * @since May 18, 2013
 * @copyright 2013 Attila Jenei
 * @link http://www.attilajenei.com
 */

return array(
	// Summary mail template
	'summaryMailTemplate' => array(
		'from' => 'www.yourdomain.com <no-reply@yourdomain.com>',
		'subject' => 'Seek-r #%scanID% [%result%]',
		'to' => array(
			'admin@yourdomain.com',
		),
		'body' => '<!DOCTYPE html>
<html>
	<head>
		<style>
body {
	background: #fff;
	color: #444;
	font-size: 12px;
	font-family: "HelveticaNeue", "Helvetica Neue", "Helvetica", Arial;
}

h3 {
	border-bottom: 1px solid #ccc;
	padding-bottom: 2px;
	margin: 20px 0 2px 0;

	font-size: 140%;
	font-weight: bold;
	line-height: 120%;
}

hr {
	border: none;
	border-bottom: 1px solid #ccc;
	width: 100%;
	height: 1px;
}

table {
	border-collapse: separate;
	border-spacing: 0;
	empty-cells: show;
	min-width: 632px;
	width: 100%;
	margin-top: 10px;
}

tbody tr {
	background: #fff8f0;
}

tbody tr:nth-child(2n) {
	background: #f8f0e8;
}

th {
	padding: 0 8px;
	font-weight: bold;
	color: #444;
	text-align: left;
	line-height: 150%;
}

td {
	padding: 0 8px;

	color: #444;
	white-space: nowrap;
	line-height: 150%;
}

tbody td {
	border-top: 1px solid #ccc;
}

tbody td:first-child {
	border-left: 1px solid #888;
}

tbody tr:first-child td {
	border-top: 1px solid #888;
}

tbody td:last-child {
	border-right: 1px solid #888;
}

tbody tr:first-child td:first-child {
	border-top-left-radius: 4px;
}

tbody tr:first-child td:last-child {
	border-top-right-radius: 4px;
}

tbody tr:last-child td {
	border-bottom: 1px solid #888;
}

tbody tr:last-child td:first-child {
	border-bottom-left-radius: 4px;
}

tbody tr:last-child td:last-child {
	border-bottom-right-radius: 4px;
}

tbody tr.Directory {
	background: #e8f0f8;
}

tbody tr.Directory td {
	border-top: 1px solid #888;
}

tbody tr.DirectoryData {
	background: #e8f0f8;
}

tbody tr.DirectoryData td {
	border-top: none;

}

tbody tr.File {
}

.Numeric {
	text-align: right;
}

.Date {
	text-align: center;
}

td.Path {
	font-weight: bold;
}

td.Name {
	padding-left: 32px;
}

.Permissions {
}

.Content {
}

table.Scans {
}

table.Scans td.Numeric {
	color: #888;
}

table.Scans td.Highlight {
	color: #000;
	font-weight: bold;
}

table.Errors {
}

table.Changes{
}

table.DateOnlyChanges {
}

		</style>
	</head>
	<body>
		Hi,<br />
%content%
		<br />
		<hr />
		Host: ' . gethostname() . '<br />
	</body>
</html>'
	),

	// Error mail template
	'errorMailTemplate' => array(
		'from' => 'www.yourdomain.com <no-reply@yourdomain.com>',
		'subject' => 'Error of Seek-r',
		'to' => array(
			'admin@yourdomain.com',
		),
		'body' => '<!DOCTYPE html>
<html>
	<head>
	</head>
	<body>
		Hi,<br />
		<br />
		The following error occured:<br />
		<br />
%error%
	</body>
</html>'
	),

	// Mail settings
	'sendMailOn' => SeekR::SEND_MAIL_ON_CONTENT_CHANGES,
	'sendMailOnDateChange' => true,
	'mailDateTimezone' => new DateTimeZone('America/Los_Angeles'),

	// Content hash
	'alwaysCheckContent' => true,

	// Skip patterns for scanner
	'skipPatterns' => array(
		'*/access.log',
		'*/access.log.*',
		'*/ssl.access.log',
		'*/ssl.access.log.*',
	),

	// Database connection parameters
	'connection' => array(
		'type' => 'mysql',
		'host' => 'localhost',
		'dbname' => 'seek-r',
		'username' => 'seek-r',
		'password' => '--PASSWORD--',
	),

	// Storage for file versions
	'storagePath' => '/path/to/Seek-r/Storage',
	'storageDirectoryMode' => 0750,
	'copyWithDate' => true,
	'naming' => '%to%%relativePath%%filename%.%scanID%',

	// Scan roots
	'roots' => array(
		'/var/www/' => 'www',
	),
);
