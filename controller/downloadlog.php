<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2015 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlog\controller;

class downloadlog
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\config\config */
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
	* @param \phpbb\template\template			$template
	* @param \phpbb\user						$user
	* @param \phpbb\auth\auth					$auth
	* @param \phpbb\db\driver\driver_interface	$db
	* @param \phpbb\request\request				$request
	* @param \phpbb\pagination					$pagination
	* @param \phpbb\controller\helper			$helper
	* @param \phpbb\config\config				$config
	* @param string								$userdownloadslog_table
	*
	*/
	public function __construct(
		\phpbb\template\template $template,
		\phpbb\log\log_interface $log,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request $request,
		\phpbb\pagination $pagination,
		\phpbb\controller\helper $helper,
		\phpbb\config\config $config,
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
		// Add lang file
		$this->user->add_lang_ext('dmzx/downloadlog', 'common');

		if (!$this->auth->acl_get('a_'))
		{
			throw new \phpbb\exception\http_exception(403, 'DOWNLOADLOG_NOACCESS');
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

			$sql = 'SELECT d.user_id, d.down_date, u.user_id, u.username, u.user_colour
				FROM ' . $this->userdownloadslog_table . ' d, ' . USERS_TABLE . ' u
				WHERE u.user_id = d.user_id
					AND file_id = ' . (int) $fileid . '
				ORDER BY d.down_date DESC';
			$top_result = $this->db->sql_query_limit($sql, $dll, $start);

			while ($row = $this->db->sql_fetchrow($top_result))
			{
				$this->template->assign_block_vars('downloaders',array(
					'D_USERNAME'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'D_TIME'		=> $this->user->format_date($row['down_date'])
				));
			}
			$this->db->sql_freeresult($top_result);
		}

		$pagination_url = $this->helper->route('dmzx_downloadlog_controller', array('file' => $fileid));

		//Start pagination
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total_downloadlogs, $dll, $start);
		$this->template->assign_vars(array(
			'DOWNLOADERS_USERS'		=> ($total_downloadlogs == 1) ? $this->user->lang['DOWNLOADERS_COUNT'] : sprintf($this->user->lang['DOWNLOADERS_COUNTS'], $total_downloadlogs),
			'DOWNLOADERS_VERSION'	=> $this->config['downloadlog_version'],
		));

		return $this->helper->render('DownloadLog.html', $this->user->lang('DOWNLOADERS_LOG'));
	}
}
