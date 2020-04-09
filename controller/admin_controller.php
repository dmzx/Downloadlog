<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2020 dmzx - https://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlog\controller;

use phpbb\config\config;
use phpbb\template\template;
use phpbb\log\log_interface;
use phpbb\user;
use phpbb\db\driver\driver_interface as db_interface;
use phpbb\request\request_interface;
use phpbb\language\language;
use phpbb\pagination;
use phpbb\cache\service;

class admin_controller
{
	/** @var config */
	protected $config;

	/** @var template */
	protected $template;

	/** @var log_interface */
	protected $log;

	/** @var \phpbb\user */
	protected $user;

	/** @var db_interface */
	protected $db;

	/** @var request_interface */
	protected $request;

	/** @var language */
	protected $language;

	/** @var pagination */
	protected $pagination;

	/** @var cache */
	protected $cache;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	* The database tables
	*
	* @var string
	*/
	protected $userdownloadslog_table;

	protected $attachments_table;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor
	 *
	 * @param config				$config
	 * @param template				$template
	 * @param log_interface			$log
	 * @param user					$user
	 * @param db_interface			$db
	 * @param request_interface		$request
	 * @param language				$language
	 * @param pagination			$pagination
	 * @param service				$cache
	 * @param string 				$root_path
	 * @param string 				$php_ext
	 * @param string				$userdownloadslog_table
	 * @param string				$attachments_table
	 */
	public function __construct(
		config $config,
		template $template,
		log_interface $log,
		user $user,
		db_interface $db,
		request_interface $request,
		language $language,
		pagination $pagination,
		service $cache,
		$root_path,
		$php_ext,
		$userdownloadslog_table,
		$attachments_table
	)
	{
		$this->config 					= $config;
		$this->template 				= $template;
		$this->log 						= $log;
		$this->user 					= $user;
		$this->db 						= $db;
		$this->request 					= $request;
		$this->language					= $language;
		$this->pagination 				= $pagination;
		$this->cache					= $cache;
		$this->root_path 				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->userdownloadslog_table 	= $userdownloadslog_table;
		$this->attachments_table		= $attachments_table;
	}

	public function display_downloadlog()
	{
		add_form_key('acp_downloadlog');

		$start		= $this->request->variable('start', 0);
		$number		= $this->config['downloadlog_value'];

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('acp_downloadlog'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action));
			}

			$this->set_options();

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DOWNLOADLOG_SETTINGS');

			trigger_error($this->language->lang('ACP_DOWNLOADLOG_SETTINGS_SAVED') . adm_back_link($this->u_action));
		}

		$sql = 'SELECT d.user_id, d.down_date, d.downloadslog_counter_user, u.user_id, u.username, u.user_colour, a.*,	t.topic_title
			FROM ' . $this->userdownloadslog_table . ' d, ' . USERS_TABLE . ' u, ' . $this->attachments_table . ' a
			LEFT JOIN ' . TOPICS_TABLE . ' t ON (a.topic_id = t.topic_id)
			WHERE u.user_id = d.user_id
				AND d.file_id = a.attach_id
				AND a.mimetype != "image/png"
			ORDER BY d.down_date DESC';
		$result = $this->db->sql_query_limit($sql, $number, $start);

		$extensions = $this->cache->obtain_attach_extensions(true);
		$this->user->add_lang(array('acp/attachments'));

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('downloaders',[
				'D_USERNAME'			=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'D_NAME'				=> ucfirst(str_replace('_', ' ', preg_replace('#^(.*)\..*$#', '\1', $row['real_filename']))),
				'D_EXT'					=> $row['extension'],
				'D_SIZE'				=> get_formatted_filesize($row['filesize']),
				'D_TIME'				=> $this->user->format_date($row['down_date']),
				'D_TIMES'				=> $row['downloadslog_counter_user'],
				'D_EXT_GROUP_NAME'		=> $this->language->is_set('EXT_GROUP_' . utf8_strtoupper($extensions[$row['extension']]['group_name'])) ?	$this->language->lang('EXT_GROUP_' . utf8_strtoupper($extensions[$row['extension']]['group_name'])) : $extensions[$row['extension']]['group_name'],
				'D_TOPIC_TITLE'			=> $row['topic_title'],
				'D_U_VIEW_TOPIC'		=> append_sid("{$this->root_path}viewtopic.{$this->php_ext}", "t={$row['topic_id']}&amp;p={$row['post_msg_id']}") . "#p{$row['post_msg_id']}",
				'D_U_FILE'				=> append_sid($this->root_path . 'download/file.' . $this->php_ext, 'mode=view&amp;id=' . $row['attach_id']),
			]);
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(d.downloadslog_id) AS total_downloadlogs, a.*
			FROM ' . $this->userdownloadslog_table . ' d, ' . $this->attachments_table . ' a
			WHERE d.file_id = a.attach_id
			AND a.mimetype != "image/png"';
		$result = $this->db->sql_query($sql);
		$total_downloads = $this->db->sql_fetchfield('total_downloadlogs');
		$this->db->sql_freeresult($result);

		$base_url = $this->u_action;
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_downloads, $number, $start);

		$this->template->assign_vars([
			'DOWLOADLOG_PAGINATION_VALUE'		=> $this->config['downloadlog_value'],
			'ACP_TOTAL_IMAGES'					=> $this->user->lang('ACP_DOWNLOAD_DOWNLOADS', (int) $total_downloads),
			'ACP_DOWNLOADLOG_VERSION'			=> $this->config['downloadlog_version'],
			'U_ACTION'							=> $this->u_action,
		]);
	}

	protected function set_options()
	{
		$this->config->set('downloadlog_value', $this->request->variable('downloadlog_value', ''));
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
