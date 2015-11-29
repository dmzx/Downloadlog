<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2015 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlog\migrations;

class downloadlog_schema extends \phpbb\db\migration\migration
{
	var $ext_version = '1.0.1';

	public function update_data()
	{
		return array(
			// Add configs
			array('config.add', array('downloadlog_value', 10)),
			array('config.add', array('downloadlog_version', $this->ext_version)),
		);
	}

	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'userdownloadslog'	=> array(
					'COLUMNS'	=> array(
					'downloadslog_id'	=> array('UINT', null, 'auto_increment'),
					'user_id'			=> array('VCHAR:8', ''),
					'file_id'			=> array('VCHAR:8', ''),
					'down_date'			=> array('INT:11', 0),
				),
				'PRIMARY_KEY' => 'downloadslog_id',
			),
		));
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'userdownloadslog',
			),
		);
	}
}
