<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2020 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlog\migrations;

class downloadlog_v104 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return [
			'\dmzx\downloadlog\migrations\downloadlog_v103',
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['downloadlog_version', '1.0.4']],
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_DOWNLOADLOG_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_DOWNLOADLOG_TITLE',
				[
					'module_basename'	=> '\dmzx\downloadlog\acp\main_module',
					'modes'				=> ['manage'],
				],
			]],
		];
	}
}
