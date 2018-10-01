<?php
/**
*
* closetopiccondition extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace alg\closetopiccondition\notification;

/**
* Thanks for posts notifications class
* This class handles notifying users when they have been thanked for a post
*/

class topicposter extends \phpbb\notification\type\base
{
	//const PARSE_AS_HTML = 0;
	//const PARSE_AS_BBCODE = 1;
	const NOTY_BBCOD_OPTIONS = 7;
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		//return 'alg.closetopiccondition.notification.type.topicposter';
		return 'alg.closetopiccondition.notification.type.topicposter';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_TOPICPOSTER';
	/**
	* Inherit notification read status from post.
	*
	* @var bool
	*/
	protected $inherit_read_status = true;

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_TOPICPOSTER',
		'group'	=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
	);
	/** @var string */
	protected $notifications_table;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	public function set_notifications_table($notifications_table)
	{
		$this->notifications_table = $notifications_table;
	}
	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}
	/**
	* Is available
	*/
	public function is_available()
	{
		return true;
	}
	/**
	* Get the id of the item
	*
	* @param array $thanks_data The data from the thank
	*/
	public static function get_item_id($data)
	{
		return (int) $data['post_id'];
	}

	/**
	* Get the id of the parent
	*
	* @param array $$commandgame_data The data from the commandcame actions
	*/
	public static function get_item_parent_id($data)
	{
		//return (int) $data['forum_id'];
		return (int) $data['topic_id'];
	}
	/**
	* Find the users who want to receive notifications
	*
	* @param array $fromadmin_data The data from the fromadmin
	* @param array $options Options for finding users for notification
	*
	* @return array
	*/
	public function find_users_for_notification($data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);
$notification = $this->notification_manager->get_item_type_class($this->get_type(), $data);
		$users = array((int) $data['poster_id']);
		return $this->check_user_notification_options($users, $options);
	}

	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('from'), 'username');
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		return $this->get_data('noty_title') . 'asdf';
	}
	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		$users = array(
			$this->get_data('from'),
		);

		return $users;

	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return '';
	}

	/**
	* {inheritDoc}
	*/
	public function get_redirect_url()
	{
		return $this->get_url();
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		//return '@alg_suki/commandgame';
		return false;   //todo
	}

	/**
	* Get the HTML formatted reference of the notification
	*
	* @return string
	*/
	public function get_reference()
	{
		$noty_content = generate_text_for_display(
			$this->get_data('noty_content'),
			$this->get_data('noty_uid'),
			$this->get_data('noty_bitfield'),
			$this->get_data('noty_options')
		);
		//return $this->user->lang(
		//	'LIMITPOSTSINTOPIC_NOTIFICATION_REFERENCE',
		//	censor_text($this->get_data('topic_name'))
		//);
	   // return '<div id="an_text"  class="an_text" >' .  $noty_content . '</div>';
		//return $noty_content . 'qwerty2';
		return $this->get_data('noty_content') . 'qwerty2';
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()  //todo
	{
		return null;
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $thanks_data Data from insert_thanks
	* @param array $pre_create_data Data from pre_create_insert_array()
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('from', $data['from']);
		$this->set_data('topic_id', $data['topic_id']);
		$this->set_data('forum_id', $data['forum_id']);
		$this->set_data('poster_id', $data['poster_id']);
		$this->set_data('noty_title', $data['noty_title']);
		$this->set_data('noty_content', $data['noty_content']);
		$this->set_data('noty_uid', $data['noty_uid']);
		$this->set_data('noty_bitfield', $data['noty_bitfield']);
		$this->set_data('noty_options', $data['noty_options']);

		parent::create_insert_array($data, $pre_create_data);
	}

	/**
	* Function for preparing the data for update in an SQL query
	* (The service handles insertion)
	*
	* @param array $thanks_data Data unique to this notification type
	* @return array Array of data ready to be updated in the database
	*/
	public function create_update_array($fromadmin_data)
	{
		$sql = 'SELECT notification_data
			FROM ' . $this->notifications_table . '
			WHERE notification_type_id = ' . (int) $this->notification_type_id . '
				AND item_id = ' . (int) self::get_item_id($fromadmin_data);
		$result = $this->db->sql_query($sql);
		if ($row = $this->db->sql_fetchrow($result))
		{
			$data = $row['notification_data'];
		}
		return $this->create_insert_array($fromadmin_data);
	}
}
