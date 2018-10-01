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

namespace alg\closetopiccondition\migrations;

class closetopiccondition_2_install_schema_data extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\alg\closetopiccondition\migrations\closetopiccondition_1_install_acp_module');
	}
	public function update_schema()
	{
		return 	array
		(
			'add_tables' => array
			(
					$this->table_prefix . 'closetopiccondition_options' => array(
						'COLUMNS'		=> array(
							'forum_id'		=> array('UINT:8',  0),
							'limitposts_number'		=> array('UINT:4',  0),
							'limittime_period'		=> array('UINT:4',  0),
							'close_only_normal_topics' => array('TINT:1', 1),
							'close_by_each_condition' => array('TINT:1', 1),
							'is_last_post' => array('TINT:1', 0),
							'lastposter_id' => array('UINT:8', '0'),
							'lastposter_name' => array('VCHAR:255', ''),
							'lastposter_color' => array('VCHAR:6', ''),
							'lastpost_msg' => array('VCHAR:500', ''),
							'lastpost_uid' => array('VCHAR:8', ''),
							'lastpost_bitfield' => array('VCHAR:255', ''),
							'lastpost_options' => array('UINT:11', 7),
							'is_noty_send' => array('TINT:1', '1'),
							'noty_sender_id' => array('UINT:8', '0'),
							'noty_sender_name' => array('VCHAR:255', ''),
 							'topicposter_msg' => array('VCHAR:500', ''),
							'topicposter_uid' => array('VCHAR:8', ''),
							'topicposter_bitfield' => array('VCHAR:255', ''),
							'topicposter_options' => array('UINT:11', 7),
 							'moderator_msg' => array('VCHAR:500', ''),
							'moderator_uid' => array('VCHAR:8', ''),
							'moderator_bitfield' => array('VCHAR:255', ''),
							'moderator_options' => array('UINT:11', 7),
					   ),
						'PRIMARY_KEY'	=> array('forum_id'),
					),
			),			
		);
	}

	public function revert_schema()
	{
		return 	array(
		   // 'drop_tables'	=> array($this->table_prefix . 'closetopiccondition_options'),
		);
	}
	
		public function update_data()
	{
		return array(
			// Add configs
			array('config.add', array('closetopiccondition_period', 1)),

			// Current version
			array('config.add', array('closetopiccondition', '1.0.*')),
		);
	}
	public function revert_data()
	{
		return array(
			//array('config.remove', array('closetopiccondition_period')),
			array('config.remove', array('closetopiccondition')),
		);
	}


}
