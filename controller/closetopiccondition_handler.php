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

	/** @var \phpbb\notification\manager */
//	protected $notification_manager;

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
    * @param \phpbb\db\driver\driver_interface	$db					DBAL object
	* @param \phpbb\user						$user				User object
	* @param string								$phpbb_root_path	phpbb_root_path
    * @param string								$php_ext			php_ext
    * @param \phpbb\request\request		$request	Request object
    * @param \phpbb\notification\manager          $notification_manager  Notification manager object
	* @param \phpbb\controller\helper		$controller_helper	Controller helper object
	* @param array								$return_error		array

	* @access public
	*/

	public function __construct(\phpbb\db\driver\driver_interface $db
                                                    , \phpbb\auth\auth $auth
                                                    , \phpbb\user $user
                                                    , \phpbb\user_loader $user_loader
                                                    , $phpbb_root_path
                                                    , $php_ext
                                                    , \phpbb\request\request_interface $request
                                                    , \phpbb\controller\helper $controller_helper
                                                    , $phpbb_container
                                                    , \phpbb\content_visibility $content_visibility
                                                    , \phpbb\config\config $config
                                                    , $users_table
                                                    , $groups_table
                                                    , $closetopiccondition_options_table
                                                )
	{
		$this->db = $db;
		$this->auth = $auth;
		$this->user = $user;
		$this->user_loader = $user_loader;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->request = $request;
//		$this->notification_manager = $notification_manager;
		$this->controller_helper = $controller_helper;
		$this->phpbb_container = $phpbb_container;
		$this->content_visibility = $content_visibility;
		$this->config = $config;
		$this->users_table = $users_table;
		$this->groups_table = $groups_table;
//		$this->notifications_table = $notifications_table;
		$this->closetopiccondition_options_table = $closetopiccondition_options_table;

		$this->return = array(); // save returned data in here
		$this->error = array(); // save errors in here

        //if (!defined('PARSE_AS_HTML'))
        //{
        //    define('PARSE_AS_HTML', 0);
        //}
        //if (!defined('PARSE_AS_BBCODE'))
        //{
        //    define('PARSE_AS_BBCODE', 1);
        //}
        //if (!defined('NOTY_BBCOD_OPTIONS'))
        //{
        //    define('NOTY_BBCOD_OPTIONS', 7);
        //}

	}


    public function live_search_forum()
	{
		//$phpbb_content_visibility = $phpbb_container->get('content.visibility');
		//$topic_visibility = $this->content_visibility->get_visibility_sql('topic', $forum_id, 't.');
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

		$sql = "SELECT user_id, username  FROM " . $this->users_table .
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

		$sql = "SELECT group_id, group_name, group_type  FROM " . $this->groups_table .
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
		//$limitposts_is_last_post = (int) $this->request->variable('forumlimitposts_is_last_post_name', 0) == 0 ? false :true;
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
			    'IS_NOTY_SEND'		=> false ,
			    'NOTY_SENDER_ID'		=> '' ,
			    'NOTY_SENDER_NAME'		=> '' ,
			    'forum_name_orig'		=> $forum_name ,
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
			   // 'LIMITPOSTS_PERIOD'		=> $row['limitposts_period'] ,
			    'CLOSE_ONLY_NORMAL_TOPICS'		=>  $row['close_only_normal_topics'] ,
			    'CLOSE_BY_EACH_CONDITION'		=>  $row['close_by_each_condition'] ,
			    'IS_LAST_POST'		=> (bool) $row['is_last_post'] ,
			    'LASTPOSTER_ID'		=> $row['lastposter_id'] ,
			    'LASTPOSTER_NAME'		=> $row['lastposter_name'] ,
			    'LAST_MESSAGE'		=> $last_message ,
			    'IS_NOTY_SEND'		=>  (bool) $row['is_noty_send'] ,
			    'TOPICPOSTER_MESSAGE'		=> $user_message ,
			    'MODERATOR_MESSAGE'		=> $moder_message ,
			    'NOTY_SENDER_ID'		=> $row['noty_sender_id'] ,
			    'NOTY_SENDER_NAME'		=> $row['noty_sender_name'] ,
			    //'last_message_from_db'		=> $row['limitposts_lastpost_msg'] ,
                //'MESSAGE'		=> $this->user->lang['ACP_limitpostsintopic_RESTORED'] ,
                //'NOTY_TITLE'		=> $row['noty_title'] ,
                //'NOTY_CONTENT'		=> $row['noty_content'] ,
                //'NOTY_PARSE_TYPE'   => $row['parse_type'] ,
		    );
        }
		$json_response = new \phpbb\json_response;
		$json_response->send($this->return);
	}
    
	public function save_options()
	{
        $this->user->add_lang_ext('alg/closetopiccondition', 'info_acp_closetopiccondition');
        $forum_id = $this->request->variable('forum_id', 0);
        $limitposts_number = $this->request->variable('limitposts_number', 0);
        $limittime_period = $this->request->variable('limittime_period', 0);
        //if ($limitposts_number == 0)
        //{
        //    $this->delete_options($forum_id);
        //    return;
        //}
        //$limitposts_period = $this->request->variable('limitposts_period', 0);
        $chkCloseOnlyNormalTopics = $this->request->variable('chkCloseOnlyNormalTopics', 1);
        $chkCloseByEachCondition = $this->request->variable('chkCloseByEachCondition', 1);
        $chkLastPost = $this->request->variable('chkLastPost', false);
        $chkNotySend = $this->request->variable('chkNotySend', false);
        $username='';
        $noty_sender_name='';
        $lastposter_color = '';
        $txtLastPost=$txtUser=$txtModer = '';
        $uid_lastpost = $bitfield_lastpost = $uid_user = $bitfield_user = $uid_moder = $bitfield_moder = '';
		$options_lastpost=$options_user=$options_moder=closetopiccondition_handler::NOTY_BBCOD_OPTIONS;
        $user_id_ary = array();
		$usernames = array();
        $lastposter_id = 0;
        $noty_sender_id = 0;
        $res = '';
        $txtTopicPoster = '';
        $txtModerators = '';

        if ($chkLastPost == true)
        {
		    $txtLastPost = utf8_normalize_nfc($this->request->variable('txtLastPost', '',true));
		    $username = utf8_normalize_nfc($this->request->variable('usersearch', '',true));
            if($txtLastPost != '' && $username != '')
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
        if($chkNotySend)
        {
		    $noty_sender_name = utf8_normalize_nfc($this->request->variable('sender_search', '',true));
		    $txtTopicPoster = utf8_normalize_nfc($this->request->variable('txtTopicPoster', '',true));
            if($txtTopicPoster != '')
            {
                $txtTopicPoster = $this->parse_text_by_parse_type(closetopiccondition_handler::PARSE_AS_BBCODE, $txtTopicPoster, $uid_user, $bitfield_user, $options_user);
            }
		    $txtModerators = utf8_normalize_nfc($this->request->variable('txtModerators', '',true));
            //$txtModer1 = $txtModer;
            if($txtModerators != '')
            {
                $txtModerators = $this->parse_text_by_parse_type(closetopiccondition_handler::PARSE_AS_BBCODE, $txtModerators, $uid_moder, $bitfield_moder, $options_moder);
            }
            if( ($txtTopicPoster != ''  || $txtModerators != '') && $noty_sender_name != '')
            {
                $usernames[] = $noty_sender_name;
				if (!function_exists('user_get_id_name'))
				{
					include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
				}
				//user_get_id_name($user_id_ary, $usernames, array(USER_NORMAL, USER_FOUNDER, USER_INACTIVE));
				$res = user_get_id_name($user_id_ary, $usernames);
                if ($res == 'NO_USERS')
                {
                    $message = sprintf($this->user->lang['ACP_CLOSETOPICCONDITION_NO_USERS'], $noty_sender_name);
                    $this->error[] = array('error' => sprintf($this->user->lang['ACP_CLOSETOPICCONDITION_NO_USERS'], $noty_sender_name));
			        $json_response = new \phpbb\json_response;
			        $json_response->send($this->error);
                }
                $noty_sender_id = $user_id_ary[0];
            }
        }
        //*****add/update db*****
        

        //$noty_parse_type = $this->request->variable('noty_parse_type', limitpostsintopic_handler::PARSE_AS_HTML);
        //$noty_title = utf8_normalize_nfc($this->request->variable('noty_title', '',true));
        //$noty_content =  utf8_normalize_nfc($this->request->variable('noty_content', '',true));
        //if ($noty_parse_type == limitpostsintopic_handler::PARSE_AS_HTML)
        //{
        //    $noty_content = htmlspecialchars_decode($noty_content);
        //}
        //$noty_create_time = time();
        $save_data = array(
            'forum_id'	=> $forum_id,
            'limitposts_number'	=>$limitposts_number,
            'limittime_period'	=> $limittime_period,
            'close_only_normal_topics'	=> $chkCloseOnlyNormalTopics ? 1 : 0,
            'close_by_each_condition'	=> $chkCloseByEachCondition ? 1 : 0,
            'is_last_post'	=> $chkLastPost ? 1 : 0,
            'lastposter_id'	=> $lastposter_id,
            'lastposter_name'	=> $username,
            'lastposter_color'	=> $lastposter_color,
            'lastpost_msg'	=> $txtLastPost,
            'lastpost_uid'	=> $uid_lastpost,
            'lastpost_bitfield'	=> $bitfield_lastpost,
            'lastpost_options'	=> $options_lastpost,
			'is_noty_send' => $chkNotySend ? 1 : 0,
 			'noty_sender_id' => $noty_sender_id,
 			'noty_sender_name' => $noty_sender_name,
 			'topicposter_msg' => $txtTopicPoster,
			'topicposter_uid' =>$uid_user,
			'topicposter_bitfield' => $bitfield_user,
			'topicposter_options' =>$options_user,
 			'moderator_msg' => $txtModerators,
			'moderator_uid' => $uid_moder,
			'moderator_bitfield' => $bitfield_moder,
			'moderator_options' => $options_moder,
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
        $forum_id = $this->request->variable('forum_id', 0);
		$sql = "DELETE FROM " . $this->closetopiccondition_options_table  . " WHERE forum_id=" . (int) $forum_id;
		$this->db->sql_query($sql);
		$result = $this->db->sql_affectedrows($sql);
        //if ($result == 0)
        //{
        //    $this->error[] = array('error' => $this->user->lang['INCORRECT_SEARCH']);
        //    return;
        //}
		$this->return = array(
			'MESSAGE'		=> $this->user->lang['ACP_CLOSETOPICCONDITION_DELETED'] ,
		);
		$json_response = new \phpbb\json_response;
		$json_response->send($this->return);
	}
    public function update_period()
    {
        $this->user->add_lang_ext('alg/closetopiccondition', 'info_acp_closetopiccondition');
        $limitposts_period = (int) $this->request->variable('limitposts_period', 1);   
        $this->config->set('closetopiccondition_period', $limitposts_period);
        $this->config->set('closetopic_gc', 60*60*24 / $limitposts_period);
        
        //debug crone
/*       
        $sql = "SELECT  lp.*, f.forum_name, f.forum_type, f.forum_status  FROM " . $this->closetopiccondition_options_table .
                    " lp JOIN " . FORUMS_TABLE . " f on lp.forum_id=f.forum_id" .
                    " WHERE f.forum_type=" . FORUM_POST . " AND f.forum_status <> " . ITEM_LOCKED;
        $result = $this->db->sql_query($sql);
        $forums_arr = array();
        $topics = array();
		$data = array();
        $limittime_period = 0;
        $sql1='';
        while ($row = $this->db->sql_fetchrow($result))
        {
            $forums_arr[] = $row;
        }
        $this->db->sql_freeresult($result);
        
        foreach ($forums_arr as $forum)
        {
            $forum_id = (int) $forum['forum_id'];
            $limitposts_number = (int) $forum['limitposts_number'];
            $limittime_period = (int) $forum['limittime_period'];
            $close_only_normal_topics = (int) $forum['close_only_normal_topics'];
            $close_by_each_condition = (int) $forum['close_by_each_condition'];
            $sql = "SELECT * FROM " . TOPICS_TABLE . 
                        " WHERE forum_id=" . $forum_id . 
                        " AND topic_status <> " . ITEM_LOCKED;
            if ( $limitposts_number > 0 && $limittime_period > 0)
            {
                $sub_months = '-' . $limittime_period . ' months';
                if ($close_by_each_condition > 0 )
                {
                    $sql .= " AND (topic_posts_approved >= " . $limitposts_number . " OR topic_last_post_time < " . strtotime($row['topic_last_post_time'] . $sub_months) . ")";
                }
                else
                {
                    $sql .= " AND (topic_posts_approved >= " . $limitposts_number . " AND topic_last_post_time < " . strtotime($row['topic_last_post_time'] . $sub_months) . ")";
                }
            }
            else
            {
                if ($limitposts_number > 0)
                {
                    $sql .= " AND topic_posts_approved >= " . $limitposts_number;
                }
                if ($limittime_period > 0)
                {
                    $sub_months = '-' . $limittime_period . ' months';
                    //$ddate1 = strtotime($row['topic_last_post_time'] . $sub_months);
                    $sql .= " AND topic_last_post_time < " . strtotime($row['topic_last_post_time'] . $sub_months);
                }
            }
            if ($close_only_normal_topics > 0)
            {
                $sql .= " AND topic_type = " . POST_NORMAL;
            }
                    
            $result = $this->db->sql_query($sql);
            
                while ($row = $this->db->sql_fetchrow($result))
                {
                    $topics[] = $row;
                    $post_id = $row['topic_last_post_id'];
                    if ($forum['is_last_post'] && $forum['lastposter_id'] && $forum['lastpost_msg'])
                    {
                        //create last post
                        $topic_id = $row['topic_id'];    
                        $this->last_post_replay($forum, $row, $data);
                        $post_id = $data['post_id'];
                        $msg = $forum['lastpost_msg'];
                        $msg1 = $msg;
                        $msgtest = "qwerty %2s 12345";
                        $msg4 = str_replace ('%1s', $row['topic_title'], $msgtest);
                        $msg5 = str_replace ('%2s', $forum['lastposter_name'], $msgtest);
                        $ava = $this->user_loader->get_avatar( 18534, 'username');
                        $topic_last_post_time = $row['topic_last_post_time'];
                    }
                    $this->close_topic($row);
                    if ($forum['is_noty_send'])
                    {
                        if ($forum['topicposter_msg'])
                        {
                            //create noty for topicposter
                            $ids = array();
                            $ids[] = $row['topic_poster'];
				            $notification_data = array(
				               // 'id'   => $row['topic_id'],
				                'item_id'   => $post_id,
				                'post_id'   => $post_id,
				                'topic_id'   => $row['topic_id'],
				                'forum_id'   => $row['forum_id'],
				                'poster_id'   => $row['topic_poster'],
				                'members_ids'   => $ids,
				                'from'   => $forum['noty_sender_id'] ,
				                //'from'   => 18534 ,
				                'noty_title'   => sprintf( $this->user->lang['NOTIFICATION_TYPE_TOPICPOSTER'], $row['topic_title']),
				                'noty_content'   => $forum['topicposter_msg'],
				                'noty_uid'   => $forum['topicposter_uid'],
				                'noty_bitfield'   => $forum['topicposter_bitfield'],
				                'noty_options'   => $forum['topicposter_options'],
			                );

		                    $this->add_notification($notification_data);
                       }
                        if ($forum['moderator_msg'])
                        {
                            $moderators = $this->get_moderators($row['forum_id']);
                            include_once($this->phpbb_root_path .  'includes/functions_display.' . $this->php_ext);
				            $notification_data = array(
				               // 'id'   => $row['topic_id'],
				                'item_id'   => $post_id,
				                'post_id'   => $post_id,
				                'topic_id'   => $row['topic_id'],
				                'forum_id'   => $row['forum_id'],
				                'poster_id'   => $row['topic_poster'],
				                'members_ids'   => $moderators,
				                'from'   => $forum['noty_sender_id'] ,
				                //'from'   => 18534 ,
				                'noty_title'   => sprintf( $this->user->lang['NOTIFICATION_TYPE_TOPICPOSTER'], $row['topic_title']),
				                'noty_content'   => $forum['moderator_msg'],
				                'noty_uid'   => $forum['moderator_uid'],
				                'noty_bitfield'   => $forum['moderator_bitfield'],
				                'noty_options'   => $forum['moderator_options'],
			                );
		                    $this->add_notification($notification_data, 'alg.closetopiccondition.notification.type.moder');
                            
                        }
                        
                    }
                }
                //add_log('mod', $post_data['forum_id'], $post_data['topic_id'], 'LOG_' . 'LOCK', $post_data['topic_title']);
            //}//end $limitposts_number
            $sql1='';
            //if($forum_id == 21)
            {
                $sql1 = $sql;
            }

        }
       
        //end debug crone
*/
        //$sub_months = '-' . $limittime_period . ' months';
        //$ddate1 = strtotime($row['topic_last_post_time'] . $sub_months);
        //$ddate = strtotime($row['topic_last_post_time'] .' -12 months');
		$this->return = array(
			'MESSAGE'		=> $this->user->lang['ACP_CLOSETOPICCONDITION_PERIOD_SAVED'] ,
            //'forums_arr'		=>$forums_arr ,
            //'$$topic_last_post_time'		=>$topic_last_post_time ,
            //'$ddate'		=>$ddate ,
           // '$ddate1'		=>$ddate1 ,
            //'topics'		=>$topics ,
            'LIMITPOSTS_PERIOD'		=>$limitposts_period ,
            //'redirect_url'		=>$redirect_url ,
            //'message'		=>$msg ,
            //'message1'		=>$msg1 ,
            ////'$msg2'		=>$msg2 ,
            ////'$$msg3'		=>$msg3 ,
            //'$$$msg4'		=>$msg4 ,
            //'ava'		=>$ava ,
            //'$forum'		=>$forum ,
           // 'data'		=>$data ,
            //'$notification_data'		=>$notification_data ,
            //'$$moderators'		=>$moderators ,
		);
		$json_response = new \phpbb\json_response;
		$json_response->send($this->return);
        
    }
	#region Notifications
	// Add notifications
	public function add_notification($notification_data, $notification_type_name = 'alg.closetopiccondition.notification.type.topicposter')
	{
                                        // print_r($notification_data);

//		$this->notification_manager->add_notifications($notification_type_name, $notification_data);
		//todo add record to log admin
	}

    public function notification_exists($notification_data, $notification_type_name)
    {
//        $notification_type_id = $this->notification_manager->get_notification_type_id($notification_type_name);
//       $sql = 'SELECT notification_id FROM ' . $this->notifications_table . '
//           WHERE notification_type_id = ' . (int) $notification_type_id . '
//                AND item_id = ' . (int) $commandgame_data['action_id'];
//        $result = $this->db->sql_query($sql);
//        $item_id = $this->db->sql_fetchfield('notification_id');
//       $this->db->sql_freeresult($result);
//
//        return ($item_id) ?: false;
        return false;
    }
//    public function get_item_id_notification($notification_type_name = 'alg.closetopiccondition.notification.type.topicposter')
//    {
//        $notification_type_id = $this->notification_manager->get_notification_type_id($notification_type_name);
//        $sql = 'SELECT  max(item_id) as max_item_id FROM ' . $this->notifications_table . '
//            WHERE notification_type_id = ' . (int) $notification_type_id ;
//            $result = $this->db->sql_query($sql);
//        $item_id = (int) $this->db->sql_fetchfield('max_item_id');
//        $this->db->sql_freeresult($result);
//        if (!$item_id)
//        {
//            return 1;
//        }
//        return (int) $item_id + 1;
//    }
//
//    public function notification_markread($item_ids)
//    {
//        // Mark post notifications read for this user in this topic
//        $this->notification_manager->mark_notifications_read(array(
//            'alg.closetopiccondition.notification.type.notification_manager ',
//        ), $item_ids, $this->user->data['user_id']);
//        
//    // Mark post notifications read for this user in this topic
//            $this->notification_manager->mark_notifications_read(array(
//                    'alg.closetopiccondition.notification.type.moder',
//                    'alg.closetopiccondition.notification.type.topicposter',
//            ), $item_ids, $this->user->data['user_id']);        
//    }

	#endregion

	private function get_user_ids_by_groups_ary(&$user_id_ary, $group_id_ary)
	{

		if (!$group_id_ary )
		{
			return '';
		}

		$user_id_ary = $username_ary = array();

		$sql = "SELECT u.user_id" .
					" FROM " . $this->users_table .
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
    public function read_saved_noty()
    {
        $notysaved = array();
		$sql = "SELECT * from " . $this->closetopiccondition_table . " ORDER BY create_time DESC";
		$result = $this->db->sql_query($sql);
		if ($result)
		{
			foreach ($result as $row)
			{
                $row_data  = array();
                $row_data['NOTY_ID'] = $row['noty_id'];
                $row_data['NOTY_TITLE'] = $row['noty_title'];
                $row_data['NOTY_CONTENT'] = $row['noty_content'];
                $row_data['NOTY_TOOLTIP'] =$this->character_limit($row['noty_content'],60);
                $row_data['CREATE_TIME'] =  $row['create_time'] ? $this->user->format_date($row['create_time'] ) :0;
                $row_data['PARSE_TYPE'] = $row['parse_type'];
                $notysaved[] = $row_data;
			}
		}
		$this->db->sql_freeresult($result);
        return $notysaved;
    }
    public function get_router_path($action)
    {
        $action_path = 'alg_closetopiccondition_controller_' . $action;
        //print_r('$action = ' . $action . '; $action_path = ' . $action_path);
        return $this->controller_helper->route($action_path);
    }
    function last_post_replay($forum_info, $topic_info, &$data)
    {
        $topic_id = $topic_info['topic_id'];   
        $forum_id = $topic_info['forum_id'];   
        $post_id = 0;

        $subject= $topic_info['topic_title'];
            $message = $forum_info['lastpost_msg'];
            //$message =  sprintf($message, '"' . $topic_info['topic_title'] . '"' , $forum_info['limitposts_lastposter_name']);
            $message = str_replace ('%1s', '"' . $topic_info['topic_title'] . '"', $message);
            $message = str_replace ('%2s',    $topic_info['topic_first_poster_name']   , $message);
            $mode = 'reply';
		    // HTML, BBCode, Smilies, Images and Flash status
		    $bbcode_status	= ($this->config['allow_bbcode'] ) ? true : false;
		    $smilies_status	= ($this->config['allow_smilies'] ) ? true : false;
		    $img_status		= ($bbcode_status ) ? true : false;
		    $url_status		= ($this->config['allow_post_links']) ? true : false;
		    $flash_status	= false;
		    $quote_status	= true;
		    if (!sizeof($this->error))
		    {
			    $data = array(
                    //'topic_first_post_id'	=> (isset($post_data['topic_first_post_id'])) ? (int) $post_data['topic_first_post_id'] : 0,
                    //'topic_last_post_id'	=> (isset($post_data['topic_last_post_id'])) ? (int) $post_data['topic_last_post_id'] : 0,
                    //'topic_time_limit'		=> (int) $post_data['topic_time_limit'],
                    //'topic_attachment'		=> (isset($post_data['topic_attachment'])) ? (int) $post_data['topic_attachment'] : 0,
				    'topic_title'			=> $topic_info['topic_title'],
				    'post_id'				=> 0,
				    'topic_id'				=> (int) $topic_info['topic_id'],
				    'forum_id'				=> (int) $topic_info['forum_id'],
				    'icon_id'				=> 0,
				    'poster_id'				=> (int) $forum_info['lastposter_id'],
				    'post_username'				=> $forum_info['lastposter_name'],
				    'poster_color'				=> $forum_info['lastposter_color'],
				    'forum_name'				=>  $forum_info['forum_name'],
				    'enable_sig'			=> (bool)(!$this->config['allow_sig'] || !$this->auth->acl_get('f_sigs', $topic_info['forum_id']) || !$this->auth->acl_get('u_sig')) ? false : ((isset($reply_data['attach_sig']) ) ? true : false),
				    'enable_bbcode'			=> (bool) $bbcode_status,
				    'enable_smilies'		=> (bool) $smilies_status,
				    'enable_urls'			=> (bool) $url_status,
                    'enable_indexing'		=> true,
                    'message_md5'			=>md5($message),
                    'post_time'				=> time(),
                    'post_checksum'			=> '',
				    'post_subject'			=> $topic_info['topic_title'],
                    'notify'				=> false,
                    'notify_set'			=> false,
                    'post_edit_locked'		=> 0,
				    'bbcode_bitfield'		=> $forum_info['lastpost_bitfield'],
				    'bbcode_uid'			=> $forum_info['lastpost_uid'],
                    'post_text'				=> $message,
				    'post_visibility'			=> 1,
				
				    'force_approved_state'  => true, // post has already been approved
			    );
			    //$post_data['username'] = $this->user->data['username'];
                //print_r($this->user);
                include_once($this->phpbb_root_path .  'includes/functions_posting.' . $this->php_ext);
                $poll='';
			    // The last parameter tells submit_post if search indexer has to be run
			   // $redirect_url = submit_post('reply', $topic_info['topic_title'], $forum_info['limitposts_lastposter_name'], POST_NORMAL, $poll, $data);
                $this->submit_new_post($data);
               // print_r($redirect_url);
		        //print_r('$redirect_url=' . $redirect_url);

                return $data;
                
                
                }	

     }
     private function submit_new_post(&$data)
    {
    //print_r($data['message']);
    //print_r($data['post_id']);
    //print_r($data);

		// Prepare new post data 
			$sql_data[POSTS_TABLE]['sql'] = array(
				'forum_id'			=> $data['forum_id'],
				'topic_id'			=> $data['topic_id'],
				'poster_id'			=>  $data['poster_id'],
				'icon_id'			=> $data['icon_id'],
				'poster_ip'			=> '127.0.0.1',
				'post_time'			=> $data['post_time'],
				'post_visibility'	=> ITEM_APPROVED,
				'enable_bbcode'		=> $data['enable_bbcode'],
				'enable_smilies'	=> $data['enable_smilies'],
				'enable_magic_url'	=> $data['enable_urls'],
				'enable_sig'		=> $data['enable_sig'],
				'post_username'		=>$data['post_username'],
				'post_subject'		=> $data['topic_title'],
				'post_text'			=> $data['post_text'],
				//'post_checksum'		=> $data['message_md5'],
				'post_checksum'		=> md5($data['post_text']),
				'post_attachment'	=>  0,
				'bbcode_bitfield'	=> $data['bbcode_bitfield'],
				'bbcode_uid'		=> $data['bbcode_uid'],
				'post_postcount'	=> ($this->auth->acl_get('f_postcount', $data['forum_id'])) ? 1 : 0,
				'post_edit_locked'	=> $data['post_edit_locked']
			);

        $post_visibility = ITEM_APPROVED;
		$sql_data[TOPICS_TABLE]['stat'][] = 'topic_last_view_time = ' . $data['post_time'] . ',
			topic_bumped = 0,
			topic_bumper = 0' .
			(($post_visibility == ITEM_APPROVED) ? ', topic_posts_approved = topic_posts_approved + 1' : '') .
			(($post_visibility == ITEM_UNAPPROVED) ? ', topic_posts_unapproved = topic_posts_unapproved + 1' : '') .
			(($post_visibility == ITEM_DELETED) ? ', topic_posts_softdeleted = topic_posts_softdeleted + 1' : '') .
			((!empty($data['attachment_data']) || (isset($data['topic_attachment']) && $data['topic_attachment'])) ? ', topic_attachment = 1' : '');

        $sql_data[FORUMS_TABLE]['sql'] = array(
			'forum_last_post_time'		=> $data['post_time'],
		);

		$sql_data[USERS_TABLE]['sql'] = array(
			'user_lastpost_time'		=> $data['post_time'],
		);
        $current_time = $data['post_time'];
		$sql_data[USERS_TABLE]['stat'][] = "user_lastpost_time = " . $data['post_time'] . (($this->auth->acl_get('f_postcount', $data['forum_id']) ) ? ', user_posts = user_posts + 1' : '');
		$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts_approved = forum_posts_approved + 1';


		//insert new post  into phpbb_posts
		$sql = 'INSERT INTO ' . POSTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_data[POSTS_TABLE]['sql']);
		$this->db->sql_query($sql);
		$data['post_id'] = $this->db->sql_nextid();

		$sql_data[TOPICS_TABLE]['sql'] = array(
			'topic_last_post_id'		=> $data['post_id'],
			'topic_last_post_time'		=> $data['post_time'],
			'topic_last_poster_id'		=> $data['poster_id'],
			'topic_last_poster_name'	=> $data['post_username'],
			'topic_last_poster_colour'	=>$data['poster_color'],
			'topic_last_post_subject'	=> (string) $data['topic_title'],
		);
        
        // Update total post count and forum information
        set_config_count('num_posts', 1, true);
        //update relation tables
 		$sql_data[FORUMS_TABLE]['stat'][] = 'forum_last_post_id = ' . $data['post_id'];
		$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_post_subject = '" . $this->db->sql_escape($data['topic_title']) . "'";
		$sql_data[FORUMS_TABLE]['stat'][] = 'forum_last_post_time = ' . $current_time;
		$sql_data[FORUMS_TABLE]['stat'][] = 'forum_last_poster_id = ' . (int) $data['poster_id'];
		$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_poster_name = '" . $this->db->sql_escape($data['post_username']). "'";
		$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_poster_colour = '" . $this->db->sql_escape($data['poster_color']) . "'";
 		
        unset($sql_data[POSTS_TABLE]['sql']);
      
	    // Update the topics table
	    if (isset($sql_data[TOPICS_TABLE]['sql']))
	    {
		    $sql = 'UPDATE ' . TOPICS_TABLE . '
			    SET ' . $this->db->sql_build_array('UPDATE', $sql_data[TOPICS_TABLE]['sql']) . '
			    WHERE topic_id = ' . $data['topic_id'];
		    $this->db->sql_query($sql);

		    unset($sql_data[TOPICS_TABLE]['sql']);
	    }
	    // Update the posts table ??? CHECK
	    if (isset($sql_data[POSTS_TABLE]['sql']))
	    {
		    $sql = 'UPDATE ' . POSTS_TABLE . '
			    SET ' . $db->sql_build_array('UPDATE', $sql_data[POSTS_TABLE]['sql']) . '
			    WHERE post_id = ' . $data['post_id'];
		    $db->sql_query($sql);

		    unset($sql_data[POSTS_TABLE]['sql']);
	    }
	    // Update forum stats
	    $where_sql = array(
		    POSTS_TABLE		=> 'post_id = ' . $data['post_id'],
		    TOPICS_TABLE	=> 'topic_id = ' . $data['topic_id'],
		    FORUMS_TABLE	=> 'forum_id = ' . $data['forum_id'],
		    USERS_TABLE		=> 'user_id = ' . $data['poster_id'],
	    );
	    foreach ($sql_data as $table => $update_ary)
	    {
		    if (isset($update_ary['stat']) && implode('', $update_ary['stat']))
		    {
			    $sql = "UPDATE $table SET " . implode(', ', $update_ary['stat']) . ' WHERE ' . $where_sql[$table];
			    $this->db->sql_query($sql);
		    }
	    }
	    // Committing the transaction before updating search index
	    $this->db->sql_transaction('commit');
        
        //// Mark this topic as posted to
        //markread('post', $data['forum_id'], $data['topic_id']);
        //// Mark this topic as read
        //// We do not use post_time here, this is intended (post_time can have a date in the past if editing a message)
        //markread('topic', $data['forum_id'], $data['topic_id'], time());
        
    //if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
    //{
    //    $sql = 'SELECT mark_time
    //        FROM ' . FORUMS_TRACK_TABLE . '
    //        WHERE user_id = ' . $data['poster_id'] . '
    //            AND forum_id = ' . $data['forum_id'];
    //    $result = $this->db->sql_query($sql);
    //    $f_mark_time = (int) $this->db->sql_fetchfield('mark_time');
    //    $this->db->sql_freeresult($result);
    //}
    //else if ($this->config['load_anon_lastread'] || $this->user->data['is_registered'])
    //{
    //    $f_mark_time = false;
    //}

	if (($this->config['load_db_lastread'] ) || $this->config['load_anon_lastread'])
	{
		// Update forum info
		$sql = 'SELECT forum_last_post_time
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $data['forum_id'];
		$result = $this->db->sql_query($sql);
		$forum_last_post_time = (int) $this->db->sql_fetchfield('forum_last_post_time');
		$this->db->sql_freeresult($result);

		//update_forum_tracking_info($data['forum_id'], $forum_last_post_time, $f_mark_time, false);
	}
	$params = $add_anchor = '';

    $params .= '&amp;t=' . $data['topic_id'];
    
    //if ($post_visibility == ITEM_APPROVED ||
    //    ($this->auth->acl_get('m_softdelete', $data['forum_id']) && $post_visibility == ITEM_DELETED) ||
    //    ($auth->acl_get('m_approve', $data['forum_id']) && in_array($post_visibility, array(ITEM_UNAPPROVED, ITEM_REAPPROVE))))
    //{
    //    $params .= '&amp;t=' . $data['topic_id'];

    //    if ($mode != 'post')
    //    {
    //        $params .= '&amp;p=' . $data['post_id'];
    //        $add_anchor = '#p' . $data['post_id'];
    //    }
    //}
    //else if ($mode != 'post' && $post_mode != 'edit_first_post' && $post_mode != 'edit_topic')
    //{
    //    $params .= '&amp;t=' . $data['topic_id'];
    //}

	$url = (!$params) ? "{$this->phpbb_root_path}viewforum.$this->php_ext" : "{$this->phpbb_root_path}viewtopic.$this->php_ext";
	$url = append_sid($url, 'f=' . $data['forum_id'] . $params) . $add_anchor;
	return $url;

    //############################################

		$sql = 'UPDATE ' . USERS_TABLE . '	SET ' . $this->db->sql_build_array('UPDATE', $sql_data[USERS_TABLE]['sql']) . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . FORUMS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_data[FORUMS_TABLE]['sql']) . ' WHERE forum_id = ' . (int) $data['forum_id'];
		$this->db->sql_query($sql);
        return;




//*************************************
	
}
    private function get_moderators($forum_id)
    {
	    $sql_array = array(
		    'SELECT'	=> 'm.*',

		    'FROM'		=> array(
			    MODERATOR_CACHE_TABLE	=> 'm',
		    ),

		    'LEFT_JOIN'	=> array(
			    array(
				    'FROM'	=> array(USERS_TABLE => 'u'),
				    'ON'	=> 'm.user_id = u.user_id',
			    ),
			    array(
				    'FROM'	=> array(GROUPS_TABLE => 'g'),
				    'ON'	=> 'm.group_id = g.group_id',
			    ),
		    ),

		    'WHERE'		=> 'forum_id='. $forum_id . '',
	    );

	    // We query every forum here because for caching we should not have any parameter.
	    $sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
        $ids = array();
        $ids_group = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
            if ((int) $row['user_id'] > 0)
            {
                $ids[] =  (int) $row['user_id'];
            }
            else
            {
                if ((int) $row['group_id'] > 0)
                {
                    $ids_group[] =  (int) $row['group_id'];
                }
            }
        }
        $sql1=$sql;
		$this->db->sql_freeresult($result);
        if (sizeof($ids_group))
        {
		    $sql = "SELECT u.user_id" .
					    " FROM " . USERS_TABLE .
					    " u JOIN " . USER_GROUP_TABLE . " ug on u.user_id = ug.user_id " .
					    " WHERE  u.user_type <>" . USER_IGNORE . " AND " .  $this->db->sql_in_set('ug.group_id', $ids_group);
		    $this->sql = $sql;
		    $result = $this->db->sql_query($sql);

		    while ($row = $this->db->sql_fetchrow($result))
		    {
			    $ids[] = $row['user_id'];
		    }
		    $this->db->sql_freeresult($result);
        }
        if (sizeof($ids))
        {
            $ids = array_unique($ids);
        }
        return $ids;
        //return $sql1;
        
        
    }
    public  function close_topic( $topic_data)
    {
       {
          //Lock topic and give error warning
          $sql = 'UPDATE ' . TOPICS_TABLE .
			    " SET topic_status = " . ITEM_LOCKED .
			    " WHERE topic_id = " . $topic_data['topic_id'];
          $this->db->sql_query($sql);

          add_log('mod', $topic_data['forum_id'], $topic_data['topic_id'], 'LOG_' . 'LOCK', $topic_data['topic_title']);

	    }
    }   

}
