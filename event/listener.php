<?php
/**
*
* @package phpBB Extension - Downloadlog
* @copyright (c) 2015 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\downloadlog\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\request\request */
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
	* @param \phpbb\config\config				$config
	* @param \phpbb\template\template			$template
	* @param \phpbb\user						$user
	* @param \phpbb\db\driver\driver_interface	$db
	* @param \phpbb\controller\helper			$controller_helper
	* @param \phpbb\request\request			 	$request
	* @param string			 					$userdownloadslog_table
	*
	*/
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $controller_helper,
		\phpbb\request\request $request,
		$userdownloadslog_table
	)
	{
		$this->config 					= $config;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->db 						= $db;
		$this->controller_helper 		= $controller_helper;
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
		$this->template->assign_vars(array(
			'U_DOWNLOADLOG' => $this->controller_helper->route('dmzx_downloadlog_controller'),
		));
	}

	public function download_file_send_to_browser_before($event)
	{
		if ($this->user->data['is_registered'])
		{
			$attach_id = $this->request->variable('id', 0);
			$sql = 'SELECT file_id
				FROM ' . $this->userdownloadslog_table . '
				WHERE user_id = ' . $this->user->data['user_id'] . '
					AND file_id = ' . (int) $attach_id;
			$result = $this->db->sql_query($sql);
			$dlrecord = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$dlrecord)
			{
				$sql_ary = array(
					'user_id'		=> $this->user->data['user_id'],
					'file_id'		=> $attach_id,
					'down_date'		=> time(),
				);
				$sql = 'INSERT INTO ' . $this->userdownloadslog_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
				$this->db->sql_query($sql);
			}
		}
	}
}
