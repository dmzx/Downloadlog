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
	/**
	* The database tables
	*
	* @var string
	*/
	protected $userdownloadslog_table;

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

	/**
	 * Constructor
	 *
	 * @param \phpbb\template\template			$template
	 * @param \phpbb\user						$user
	 * @param \phpbb\auth\auth					$auth
	 * @param \phpbb\db\driver\driver_interface	$db
	 * @param \phpbb\request\request			$request
	 */
	public function __construct(\phpbb\template\template $template, \phpbb\log\log_interface $log, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request,$userdownloadslog_table)
	{
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->db = $db;
		$this->request = $request;
		$this->userdownloadslog_table = $userdownloadslog_table;
	}

	public function handle_downloadlog()
	{
		if (!$this->auth->acl_get('a_'))
		{
			trigger_error('Access Denied');
		}
		else
		{
			$this->user->add_lang_ext('dmzx/downloadlog', 'common');

			$fileid = $this->request->variable('file', 0);
			$sql = 'SELECT d.user_id, d.down_date, u.user_id, u.username, u.user_colour
				FROM ' . $this->userdownloadslog_table . ' d, ' . USERS_TABLE . ' u
				WHERE u.user_id = d.user_id AND file_id = '. $fileid .'
				ORDER BY d.down_date DESC';
			$top_result = $this->db->sql_query_limit($sql, 50);

			while($row = $this->db->sql_fetchrow($top_result))
			{
				$this->template->assign_block_vars('downloaders',array(
					'D_USERNAME'			=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'D_TIME'				=> $this->user->format_date($row['down_date'])
				));
			}
		}

		page_header('Downloaders Log', false);
		$this->template->set_filenames(array(
			'body' => 'DownloadLog.html')
		);

		page_footer();
	}
}	