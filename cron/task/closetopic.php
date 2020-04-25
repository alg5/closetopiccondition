<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace alg\closetopiccondition\cron\task;

/**
 * Acme demo cron task.
 */
class closetopic extends \phpbb\cron\task\base
{
	/**
	 * How often we run the cron (in seconds).
	 * @var int
	 */
	//protected $cron_frequency = 86400;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	* Constructor
	*
	* @param \phpbb\config\config $config Config object
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, \phpbb\user $user, $phpbb_root_path, $php_ext, $closetopiccondition_options_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->user = $user;
//		$this->notification_manager = $notification_manager;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->closetopiccondition_options_table = $closetopiccondition_options_table;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		// Run your cron actions here...
		// Update the cron task run time here if it hasn't
		// already been done by your cron actions.
		//$this->config->set('acme_cron_last_run', time(), false);

		$this->close_topic_crone();
		$this->config->set('closetopic_last_gc', time(), true);

}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* For example, a cron task that prunes forums can only run when
	* forum pruning is enabled.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return true;
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['closetopic_last_gc'] < time() - $this->config['closetopic_gc'];
	}

	function close_topic_crone()
	{
		$sql = "SELECT  lp.*, f.forum_name, f.forum_type, f.forum_status  FROM " . $this->closetopiccondition_options_table .
					" lp JOIN " . FORUMS_TABLE . " f on lp.forum_id=f.forum_id" .
					" WHERE f.forum_type=" . FORUM_POST . " AND f.forum_status <> " . ITEM_LOCKED;
		$result = $this->db->sql_query($sql);
		$forums_arr = array();
		$topics = array();
		$data = array();
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
//                        $topic_id = $row['topic_id'];
					$this->last_post_replay($forum, $row, $data);
					$post_id = $data['post_id'];
				 }
//			$this->config->set('closetopic_debug_2', 'qwerty');
				$this->close_topic($row);
			}
				//add_log('mod', $post_data['forum_id'], $post_data['topic_id'], 'LOG_' . 'LOCK', $post_data['topic_title']);
			//}//end $limitposts_number
		}
	}
	function last_post_replay($forum_info, $topic_info, &$data)
	{
		$mode = 'reply';
		// HTML, BBCode, Smilies, Images and Flash status
		$bbcode_status	= ($this->config['allow_bbcode'] ) ? true : false;
		$smilies_status	= ($this->config['allow_smilies'] ) ? true : false;
		$img_status		= ($bbcode_status ) ? true : false;
		$url_status		= ($this->config['allow_post_links']) ? true : false;
		$flash_status	= false;
		$quote_status	= true;
		// if (!sizeof($this->error))
		if (true)
		{
			$data = array(
				'topic_title'			=> $topic_info['topic_title'],
				'post_id'				=> 0,
				'topic_id'				=> (int) $topic_info['topic_id'],
				'forum_id'				=> (int) $topic_info['forum_id'],
				'icon_id'				=> 0,
				'poster_id'				=> (int) $forum_info['lastposter_id'],
				'post_username'				=> $forum_info['lastposter_name'],
				'poster_color'			=> $forum_info['lastposter_color'],
				'forum_name'			=> $forum_info['forum_name'],
				'enable_sig'			=> (bool) (!$this->config['allow_sig'] || !$this->auth->acl_get('f_sigs', $topic_info['forum_id']) || !$this->auth->acl_get('u_sig')) ? false : ((isset($reply_data['attach_sig'])) ? true : false),
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
			include_once($this->phpbb_root_path .  'includes/functions_posting.' . $this->php_ext);
			 $this->submit_new_post($data);
			 return $data;
		}
	}
	private function submit_new_post(&$data)
	{
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

	if (($this->config['load_db_lastread'] ) || $this->config['load_anon_lastread'])
	{
		// Update forum info
		$sql = 'SELECT forum_last_post_time
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $data['forum_id'];
		$result = $this->db->sql_query($sql);
		$forum_last_post_time = (int) $this->db->sql_fetchfield('forum_last_post_time');
		$this->db->sql_freeresult($result);
	}
	$params = $add_anchor = '';
	$params .= '&amp;t=' . $data['topic_id'];
	$url = (!$params) ? "{$this->phpbb_root_path}viewforum.$this->php_ext" : "{$this->phpbb_root_path}viewtopic.$this->php_ext";
	$url = append_sid($url, 'f=' . $data['forum_id'] . $params) . $add_anchor;
	return $url;

//		$sql = 'UPDATE ' . USERS_TABLE . '	SET ' . $this->db->sql_build_array('UPDATE', $sql_data[USERS_TABLE]['sql']) . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
//		$this->db->sql_query($sql);
//
//		$sql = 'UPDATE ' . FORUMS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_data[FORUMS_TABLE]['sql']) . ' WHERE forum_id = ' . (int) $data['forum_id'];
//		$this->db->sql_query($sql);
//		return;

	}
	public function close_topic($topic_data)
	{
		//Lock topic and give error warning
		$sql = 'UPDATE ' . TOPICS_TABLE .
			  " SET topic_status = " . ITEM_LOCKED .
			  " WHERE topic_id = " . $topic_data['topic_id'];
		$this->db->sql_query($sql);
		 // $this->config->set('closetopic_debug_3', $topic_data['topic_id'], true);

		add_log('mod', $topic_data['forum_id'], $topic_data['topic_id'], 'LOG_' . 'LOCK', $topic_data['topic_title']);
	}
 }
