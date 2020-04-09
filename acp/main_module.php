<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2020 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlog\acp;

class main_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container, $user;

		// Add the ACP lang file
		$user->add_lang_ext('dmzx/downloadlog', 'acp_downloadlog');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('dmzx.downloadlog.admin.controller');

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		switch($mode)
		{
			case 'manage':
				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'downloadlog';
				// Set the page title for our ACP page
				$this->page_title = $user->lang['ACP_DOWNLOADLOG_TITLE'];
				// Load the display_downloadlog options handle in the admin controller
				$admin_controller->display_downloadlog();
			break;
		}
	}
}
