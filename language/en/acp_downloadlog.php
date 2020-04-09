<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2020 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'ACP_DOWNLOADLOG_SETTINGS_SAVED'			=> 'Downloadlog saved.',
	'ACP_DOWLOADLOG_PAGINATION_VALUE'			=> 'Pagination setting downloadlog',
	'ACP_DOWNLOADLOG_DOWNLOADS'					=> 'Downloads',
	'ACP_DOWNLOADLOG_TITLE_EXPLAIN'				=> 'Total downloadlog for each member.',
	'ACP_DOWLOADLOG_PAGINATION_VALUE_EXPLAIN'	=> 'Value adjustable from 1 till 255 users.<br>For forum and ACP pagination.',
	'ACP_DOWNLOAD_USERNAME' 					=> 'Username',
	'ACP_DOWNLOAD_DOWNLOADED' 					=> 'Downloaded',
	'ACP_DOWNLOAD_NAME' 						=> 'File name',
	'ACP_DOWNLOAD_EXT_TYPE' 					=> 'Extension',
	'ACP_DOWNLOAD_SIZE' 						=> 'File Size',
	'ACP_DOWNLOAD_TIME' 						=> 'Time',
	'ACP_DOWNLOAD_TOPIC' 						=> 'Topic',
	'ACP_DOWNLOAD_GROUP' 						=> 'Extension group',
	'ACP_DOWNLOAD_DOWNLOADS'		=>	[
		1 => '%s download',
		2 => '%s downloads',
	],
]);
