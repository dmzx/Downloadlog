<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2015 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlog\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\config\config;
use phpbb\template\template;
use phpbb\user;
use phpbb\db\driver\driver_interface as db_interface;
use phpbb\controller\helper;
use phpbb\request\request_interface;

class listener implements EventSubscriberInterface
{
	/** @var config */
	protected $config;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var db_interface */
	protected $db;

	/** @var helper */
	protected $helper;

	/** @var request */
	protected $request;

	/**
	* The database tables
	*
	* @var string
	*/
	protected $userdownloadslog_table;

	/**
	* Constructor
	*
	* @param config					$config
	* @param template				$template
	* @param user					$user
	* @param db_interface			$db
	* @param helper					$helper
	* @param request_interface		$request
	* @param string			 		$userdownloadslog_table
	*
	*/
	public function __construct(
		config $config,
		template $template,
		user $user,
		db_interface $db,
		helper $helper,
		request_interface $request,
		$userdownloadslog_table
	)
	{
		$this->config 					= $config;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->db 						= $db;
		$this->helper 					= $helper;
		$this->request 					= $request;
		$this->userdownloadslog_table 	= $userdownloadslog_table;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.parse_attachments_modify_template_data' 	=> 'parse_attachments_modify_template_data',
			'core.viewtopic_assign_template_vars_before'	=> 'viewtopic_assign_template_vars_before',
			'core.download_file_send_to_browser_before'		=> 'download_file_send_to_browser_before',
		);
	}

	public function parse_attachments_modify_template_data($event)
	{
		$block_array = $event['block_array'];
		$attachment = $event['attachment']['attach_id'];
		$block_array['U_FILE_ID'] = $attachment;
		$event['block_array'] = $block_array;
	}

	public function viewtopic_assign_template_vars_before($event)
	{
		$this->user->add_lang_ext('dmzx/downloadlog', 'common');
		$this->template->assign_vars([
			'U_DOWNLOADLOG' => $this->helper->route('dmzx_downloadlog_controller'),
		]);
	}

	public function download_file_send_to_browser_before($event)
	{
		if ($this->user->data['is_registered'] && strpos($event['attachment']['mimetype'], 'image') !== 0)
		{
			$attach_id = $this->request->variable('id', 0);
			$sql = 'SELECT file_id, downloadslog_counter_user
				FROM ' . $this->userdownloadslog_table . '
				WHERE user_id = ' . (int) $this->user->data['user_id'] . '
					AND file_id = ' . (int) $attach_id;
			$result = $this->db->sql_query($sql);
			$dlrecord = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$dlrecord)
			{
				$sql_ary = [
					'user_id'					=> $this->user->data['user_id'],
					'file_id'					=> $attach_id,
					'downloadslog_counter_user'	=> 1,
					'down_date'					=> time(),
				];
				$sql = 'INSERT INTO ' . $this->userdownloadslog_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
				$this->db->sql_query($sql);
			}
			else
			{
				$downloadslog_counter_user = $dlrecord['downloadslog_counter_user'] + 1;

				$sql_ary = [
					'downloadslog_counter_user'	=> (int) $downloadslog_counter_user,
					'down_date'					=> time(),
				];

				$sql_insert = 'UPDATE ' . $this->userdownloadslog_table . '
					SET	' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE user_id = ' . (int) $this->user->data['user_id'] . '
						AND file_id = ' . (int) $attach_id;
				$this->db->sql_query($sql_insert);
			}
		}
	}
}
