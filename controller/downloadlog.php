<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2015 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlog\controller;

use phpbb\exception\http_exception;
use phpbb\template\template;
use phpbb\user;
use phpbb\auth\auth;
use phpbb\db\driver\driver_interface as db_interface;
use phpbb\request\request_interface;
use phpbb\pagination;
use phpbb\controller\helper;
use phpbb\config\config;

class downloadlog
{
	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var auth */
	protected $auth;

	/** @var driver_interface */
	protected $db;

	/** @var request */
	protected $request;

	/** @var pagination */
	protected $pagination;

	/** @var helper */
	protected $helper;

	/** @var config */
	protected $config;

	/**
	* The database tables
	*
	* @var string
	*/
	protected $userdownloadslog_table;

	/**
	* Constructor
	*
	* @param template						$template
	* @param user							$user
	* @param auth							$auth
	* @param driver_interface				$db
	* @param request_interface 				$request
	* @param pagination						$pagination
	* @param helper							$helper
	* @param config							$config
	* @param string							$userdownloadslog_table
	*
	*/
	public function __construct(
		template $template,
		user $user,
		auth $auth,
		db_interface $db,
		request_interface	$request,
		pagination $pagination,
		helper $helper,
		config $config,
		$userdownloadslog_table
	)
	{
		$this->template 				= $template;
		$this->user 					= $user;
		$this->auth 					= $auth;
		$this->db 						= $db;
		$this->request 					= $request;
		$this->pagination 				= $pagination;
		$this->helper 					= $helper;
		$this->config 					= $config;
		$this->userdownloadslog_table 	= $userdownloadslog_table;
	}

	public function handle_downloadlog()
	{
		$this->user->add_lang_ext('dmzx/downloadlog', 'common');

		if (!$this->auth->acl_get('a_'))
		{
			throw new http_exception(403, 'DOWNLOADLOG_NOACCESS');
		}
		else
		{
			$fileid = $this->request->variable('file', 0);
			$start = $this->request->variable('start', 0);

			// Pagination number from ACP
			$dll = $this->config['downloadlog_value'];

			// Generate pagination
			$sql = 'SELECT COUNT(downloadslog_id) AS total_downloadlogs
				FROM ' . $this->userdownloadslog_table . '
				WHERE file_id = ' . (int) $fileid;
			$result = $this->db->sql_query($sql);
			$total_downloadlogs = $this->db->sql_fetchfield('total_downloadlogs');
			$this->db->sql_freeresult($result);

			$sql = 'SELECT d.user_id, d.down_date, d.downloadslog_counter_user, u.user_id, u.username, u.user_colour
				FROM ' . $this->userdownloadslog_table . ' d, ' . USERS_TABLE . ' u
				WHERE u.user_id = d.user_id
					AND file_id = ' . (int) $fileid . '
				ORDER BY d.down_date DESC';
			$top_result = $this->db->sql_query_limit($sql, $dll, $start);

			while ($row = $this->db->sql_fetchrow($top_result))
			{
				$this->template->assign_block_vars('downloaders',[
					'D_USERNAME'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'D_TIME'		=> $this->user->format_date($row['down_date']),
					'D_TIMES'		=> $row['downloadslog_counter_user'],
				]);
			}
			$this->db->sql_freeresult($top_result);
		}

		$pagination_url = $this->helper->route('dmzx_downloadlog_controller', ['file' => $fileid]);

		//Start pagination
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total_downloadlogs, $dll, $start);

		$this->template->assign_vars([
			'DOWNLOADERS_USERS'		=> $this->user->lang('DOWNLOADERS_COUNTS', (int) $total_downloadlogs),
		]);

		return $this->helper->render('downloadlog.html', $this->user->lang('DOWNLOADERS_LOG'));
	}
}
