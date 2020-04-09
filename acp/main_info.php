<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2020 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlog\acp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\dmzx\downloadlog\acp\main_module',
			'title'		=> 'ACP_DOWNLOADLOG_TITLE',
			'modes'		=> [
				'manage'	=> [
					'title'	=> 'ACP_DOWNLOADLOG_SETTINGS',
					'auth'	=> 'ext_dmzx/downloadlog && acl_a_board',
					'cat'	=> ['ACP_DOWNLOADLOG_TITLE']
				],
			],
		];
	}
}
