<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2015 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlog\migrations;

class downloadlog_v102 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\dmzx\downloadlog\migrations\downloadlog_schema',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('downloadlog_version', '1.0.2')),
		);
	}
}
