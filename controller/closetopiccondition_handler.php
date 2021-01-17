<?php
/**
*
 * @package closetopiccondition
 * @copyright (c) 2015 Alg
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace alg\closetopiccondition\controller;

class closetopiccondition_handler
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $php_ext;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var array */
	protected $thankers = array();

	//Constants
	const PARSE_AS_HTML = 0;
	const PARSE_AS_BBCODE = 1;
	const NOTY_BBCOD_OPTIONS = 7;
		/**
	* Constructor
	* @param \phpbb\db\driver\driver_interface	$db		DBAL object
	* @param \phpbb\user							$user				User object
	* @param string								$phpbb_root_path	phpbb_root_path
	* @param string								$php_ext			php_ext
	* @param \phpbb\request\request					$request	Request object
	* @param \phpbb\controller\helper					$controller_helper	Controller helper object
	* @param array								$return_error		array

	* @access public
	*/

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $phpbb_root_path, $php_ext, \phpbb\request\request_interface $request, \phpbb\controller\helper $controller_helper, \phpbb\config\config $config, $closetopiccondition_options_table)
	{
		$this->db = $db;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->request = $request;
		$this->controller_helper = $controller_helper;
		$this->config = $config;
		$this->closetopiccondition_options_table = $closetopiccondition_options_table;

		$this->return = array(); // save returned data in here
		$this->error = array(); // save errors in here
	}


	public function live_search_forum()
	{
		$q = utf8_strtoupper(utf8_normalize_nfc($this->request->variable('q', '',true)));
		$sql = "SELECT  f.forum_id, f.forum_name, pf.forum_name as forum_parent_name  " .
				" FROM " . FORUMS_TABLE . " f LEFT JOIN " . FORUMS_TABLE . " pf on f.parent_id = pf.forum_id " .
				" WHERE UPPER(f.forum_name) " . $this->db->sql_like_expression($this->db->get_any_char()  . $this->db->sql_escape($q) . $this->db->get_any_char() ) .
				" ORDER BY f.forum_name";
		$result = $this->db->sql_query($sql);
		$arr_res = $arr_priority1 = $arr_priority2 = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			//if ($this->auth->acl_get('f_read', $row['forum_id']) )
			{
				$pos = strpos(utf8_strtoupper($row['forum_name']), $q);
				if ($pos !== false )
				{
					$row['pos'] = $pos;
					if ($pos == 0)
					{
						$arr_priority1[] = $row;
					}
					else
					{
						$arr_priority2[] = $row;
					}
				}
			}
		}
		$this->db->sql_freeresult($result);

		$arr_res = array_merge((array) $arr_priority1, (array) $arr_priority2);
		$message = '';
		foreach ($arr_res as $forum_info)
		{
			$forum_id = $forum_info['forum_id'];
			$key = $forum_info['forum_name'] ;
			if ($forum_info['forum_parent_name'] )
			{
				$key .= ' (' . $forum_info['forum_parent_name'] . ')'  ;
			}
			$message .=  $key . "|$forum_id\n";
		}
		$json_response = new \phpbb\json_response;
			$json_response->send($message);
	}
	public function live_search_user()
	{
		$q = utf8_strtoupper(utf8_normalize_nfc($this->request->variable('q', '',true)));

		$sql = "SELECT user_id, username  FROM " . USERS_TABLE .
					" WHERE user_type <> " . USER_IGNORE .
					" AND username_clean " . $this->db->sql_like_expression(utf8_clean_string( $this->db->sql_escape($q)) . $this->db->get_any_char());
					" ORDER BY username";

		$result = $this->db->sql_query($sql);
		$message='';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_id = $row['user_id'];
			$key = htmlspecialchars_decode($row['username']	);
			$message .=  $key . "|$user_id\n";
		}
		$json_response = new \phpbb\json_response;
		$json_response->send($message);

	}
	public function live_search_group()
	{
		$q = utf8_strtoupper(utf8_normalize_nfc($this->request->variable('q', '',true)));

		$sql = "SELECT group_id, group_name, group_type  FROM " . GROUPS_TABLE .
					" ORDER BY group_type DESC, group_name ASC";
		$result = $this->db->sql_query($sql);
		$message='';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$key = $row['group_type'] == GROUP_SPECIAL ?  $this->user->lang['G_' . $row['group_name']] : htmlspecialchars_decode($row['group_name']	);
			if (strpos(utf8_strtoupper($key), $q) == 0)
			{
				$group_id=$row['group_id'];
				$message .= $key . "|$group_id\n";
			}
		}
		$json_response = new \phpbb\json_response;
		$json_response->send($message);

	}

	public function get_forum_options()
	{
		$this->user->add_lang_ext('alg/closetopiccondition', 'info_acp_closetopiccondition');
		$forum_id = $this->request->variable('forum_id', 0);
		$forum_name = $this->request->variable('forum_name', '', true);
		$user_message = $moder_message = '';
		$sql = "SELECT * FROM " . $this->closetopiccondition_options_table  . " WHERE forum_id=" . $forum_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		if (!$row)
		{
			$this->return = array(
				'forum_id'		=> $forum_id ,
				'IS_CONDITIONS_EXISTS'		=> 0 ,
				'FORUM_NAME'		=> censor_text(str_replace('&quot;', '"', $forum_name))  ,
				'LIMITPOSTS_NUMBER'		=> 0 ,
				'LIMITTIME_PERIOD'		=> 0 ,
				'CLOSE_ONLY_NORMAL_TOPICS'		=> 1 ,
				'CLOSE_BY_EACH_CONDITION'		=> 1 ,
				'IS_LAST_POST'		=> false ,
				'LASTPOSTER_ID'		=> 0 ,
				'LASTPOSTER_NAME'		=> '' ,
				'LAST_MESSAGE'		=> '' ,
				'ARCHIVE_FORUM_ID'	=> 0 ,
				'LEAVE_SHADOW'		=> 0 ,
			);
		}
		else
		{
			$last_message = '';
			$is_last_post =  (bool) $row['is_last_post'];
			$is_noty_send =  (bool) $row['is_noty_send'];
			if ($is_last_post)
			{
				$ret = generate_text_for_edit(
					$row['lastpost_msg'],
					$row['lastpost_uid'],
					$row['lastpost_options']
				);
				$last_message = $ret['text'];
			}
			if ($is_noty_send)
			{
				$ret = generate_text_for_edit(
					$row['topicposter_msg'],
					$row['topicposter_uid'],
					$row['topicposter_options']
				);
				$user_message = $ret['text'];
				$ret = generate_text_for_edit(
					$row['moderator_msg'],
					$row['moderator_uid'],
					$row['moderator_options']
				);
				$moder_message = $ret['text'];
			}

			$this->return = array(
				'forum_id'		=> $forum_id ,
				'IS_CONDITIONS_EXISTS'		=> 1 ,
				'FORUM_NAME'		=> sprintf($this->user->lang['ACP_CLOSETOPICCONDITION_FORUM_SELECTED'], $forum_name) ,
				'FORUM_NAME'		=> censor_text($forum_name) ,
				'LIMITPOSTS_NUMBER'		=> $row['limitposts_number'] ,
				'LIMITTIME_PERIOD'		=> $row['limittime_period'] ,
				'CLOSE_ONLY_NORMAL_TOPICS'		=>  $row['close_only_normal_topics'] ,
				'CLOSE_BY_EACH_CONDITION'		=>  $row['close_by_each_condition'] ,
				'IS_LAST_POST'		=> (bool) $row['is_last_post'] ,
				'LASTPOSTER_ID'		=> $row['lastposter_id'] ,
				'LASTPOSTER_NAME'		=> $row['lastposter_name'] ,
				'LAST_MESSAGE'		=> $last_message ,
				'ARCHIVE_FORUM_ID'		=> $row['archive_forum_id']  ,
				'LEAVE_SHADOW'		=> (bool) $row['leave_shadow'] ,
			);
		}
		$json_response = new \phpbb\json_response;
		$json_response->send($this->return);
	}
	public function save_options()
	{
		$this->user->add_lang_ext('alg/closetopiccondition', 'info_acp_closetopiccondition');
		$forum_id = $this->request->variable('forum_id', 0);
		$archive_forum_id = $this->request->variable('forum_archive', 0);
		$limitposts_number = $this->request->variable('limitposts_number', 0);
		$limittime_period = $this->request->variable('limittime_period', 0);

		$chkCloseOnlyNormalTopics = $this->request->variable('chkCloseOnlyNormalTopics', 1);
		$chkCloseByEachCondition = $this->request->variable('chkCloseByEachCondition', 1);
		$chkLastPost = $this->request->variable('chkLastPost', false);
		$chkLeaveShadow = $this->request->variable('chkLeaveShadow', false);
		$username='';
		$lastposter_color = '';
		$txtLastPost=$txtUser=$txtModer = '';
		$uid_lastpost = $bitfield_lastpost = $uid_user = $bitfield_user = $uid_moder = $bitfield_moder = '';
		$options_lastpost=$options_user=$options_moder=closetopiccondition_handler::NOTY_BBCOD_OPTIONS;
		$user_id_ary = array();
		$usernames = array();
		$lastposter_id = 0;
		$noty_sender_id = 0;
		$res = '';

		if ($chkLastPost == true)
		{
			$txtLastPost = utf8_normalize_nfc($this->request->variable('txtLastPost', '',true));
			$username = utf8_normalize_nfc($this->request->variable('usersearch', '',true));
			if ($txtLastPost != '' && $username != '')
			{
				$usernames[] = $username;
				if (!function_exists('user_get_id_name'))
				{
					include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
				}
				//user_get_id_name($user_id_ary, $usernames, array(USER_NORMAL, USER_FOUNDER, USER_INACTIVE));
				$res = user_get_id_name($user_id_ary, $usernames);
				if ($res == 'NO_USERS')
				{
					$message = sprintf($this->user->lang['ACP_CLOSETOPICCONDITION_NO_USERS'], $username);
					$this->error[] = array('error' => sprintf($this->user->lang['ACP_CLOSETOPICCONDITION_NO_USERS'], $username));
					$json_response = new \phpbb\json_response;
					$json_response->send($this->error);
				}
				$lastposter_id = $user_id_ary[0];
				$txtLastPost = $this->parse_text_by_parse_type(closetopiccondition_handler::PARSE_AS_BBCODE, $txtLastPost, $uid_lastpost, $bitfield_lastpost, $options_lastpost);
				//get lastposter_data
				$sql = "SELECT user_colour FROM " . USERS_TABLE . " WHERE user_id=" . (int) $lastposter_id;
				$result = $this->db->sql_query($sql);
				$lastposter_color = $this->db->sql_fetchfield('user_colour');
				$this->db->sql_freeresult($result);

			}
			else
			{
				$txtLastPost = '';
				$username='';
			}

		}

		$save_data = array(
			'forum_id'				=> $forum_id,
			'limitposts_number'		=>$limitposts_number,
			'limittime_period'		=> $limittime_period,
			'close_only_normal_topics'	=> $chkCloseOnlyNormalTopics ? 1 : 0,
			'close_by_each_condition'	=> $chkCloseByEachCondition ? 1 : 0,
			'is_last_post'			=> $chkLastPost ? 1 : 0,
			'lastposter_id'			=> $lastposter_id,
			'lastposter_name'		=> $username,
			'lastposter_color'		=> $lastposter_color,
			'lastpost_msg'			=> $txtLastPost,
			'lastpost_uid'			=> $uid_lastpost,
			'lastpost_bitfield'		=> $bitfield_lastpost,
			'lastpost_options'		=> $options_lastpost,
			'archive_forum_id'		=> $archive_forum_id,
			'leave_shadow'			=> $chkLeaveShadow ? 1 : 0,
			);
		 $save_data_upd = $save_data;
		 unset($save_data_upd['forum_id']);
		//add to DB
		$sql = 'INSERT INTO ' . $this->closetopiccondition_options_table . ' ' . $this->db->sql_build_array('INSERT', $save_data) .
		' ON DUPLICATE KEY UPDATE ' . $this->db->sql_build_array('UPDATE', $save_data_upd);
				$this->db->sql_query($sql);
		//$noty_id = $this->db->sql_nextid();
		$this->return = array(
			'MESSAGE'		=> $this->user->lang['ACP_CLOSETOPICCONDITION_SAVED'] ,
		);
		$json_response = new \phpbb\json_response;
		$json_response->send($this->return);
	}

	public function delete_options()
	{
		$this->user->add_lang_ext('alg/closetopiccondition', 'info_acp_closetopiccondition');
		$forum_id = $this->request->variable('forum_id', 0);
		$sql = "DELETE FROM " . $this->closetopiccondition_options_table  . " WHERE forum_id=" . (int) $forum_id;
		$this->db->sql_query($sql);
		$result = $this->db->sql_affectedrows($sql);
		$this->return = array(
			'MESSAGE'		=> $this->user->lang['ACP_CLOSETOPICCONDITION_DELETED'] ,
		);
		$json_response = new \phpbb\json_response;
		$json_response->send($this->return);
	}
	public function update_period()
	{
		$this->user->add_lang_ext('alg/closetopiccondition', 'info_acp_closetopiccondition');
		$closetopiccondition_period = (int) $this->request->variable('closetopiccondition_period', 1);
		$this->config->set('closetopiccondition_period', $closetopiccondition_period);
		$this->config->set('closetopic_gc', 60*60*24 / $closetopiccondition_period);

		$this->return = array(
			'MESSAGE'		=> $this->user->lang['ACP_CLOSETOPICCONDITION_PERIOD_SAVED'] ,
	//			'closetopiccondition_period_config'		=>$this->config['closetopiccondition_period'] ,
		);
		$json_response = new \phpbb\json_response;
		$json_response->send($this->return);

	}
	private function get_user_ids_by_groups_ary(&$user_id_ary, $group_id_ary)
	{

		if (!$group_id_ary )
		{
			return '';
		}

		$user_id_ary = $username_ary = array();

		$sql = "SELECT u.user_id" .
					" FROM " . USERS_TABLE .
					" u JOIN " . USER_GROUP_TABLE . " ug on u.user_id = ug.user_id " .
					" WHERE  u.user_type <>" . USER_IGNORE . " AND " .  $this->db->sql_in_set('ug.group_id', $group_id_ary);
		$this->sql = $sql;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_id_ary[] = $row['user_id'];
		}

		$this->db->sql_freeresult($result);
		$this->ids_groups = $user_id_ary;
	}
	private function parse_text_by_parse_type($parse_type, $text_src, &$uid, &$bitfield, $options)
	{
		$text_dst ='';
		if ($parse_type == closetopiccondition_handler::PARSE_AS_HTML)
		{
			$text_dst = htmlspecialchars_decode(utf8_normalize_nfc($text_src));
		}
		else
		{
				//PARSE_AS_BBCODE
				$text_dst = $text_src;
			generate_text_for_storage(
				$text_dst,
				$uid,
				$bitfield,
				$options,
				true,
				true,
				true
			);
		}
		return $text_dst;
	}
	public function character_limit(&$title, $limit = 0)
	{
		$title = censor_text($title);
		if ($limit > 0)
		{
			return (utf8_strlen($title) > $limit + 3) ? truncate_string($title, $limit) . '...' : $title;
		}
		else
		{
			return $title;
		}
	}

	public function get_router_path($action)
	{
		$action_path = 'alg_closetopiccondition_controller_' . $action;
		//print_r('$action = ' . $action . '; $action_path = ' . $action_path);
		return $this->controller_helper->route($action_path);
	}
}
