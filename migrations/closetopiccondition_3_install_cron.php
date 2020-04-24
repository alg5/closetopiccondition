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

class closetopiccondition_3_install_cron extends \phpbb\db\migration\migration
{

	static public function depends_on()
	{
		return array('\alg\closetopiccondition\migrations\closetopiccondition_2_install_schema_data');
	}

	public function update_data()
	{
		return array(
				//array('config.add', array('closetopic_gc', (60 * 60 * 24), '0')), 
				array('config.add', array('closetopic_gc', (60 * 60 ), '0')), 
				array('config.add', array('closetopic_last_gc', '0', '1')), 
				array('config.add', array('closetopic_debug', '0', '1')), 
				array('config.add', array('closetopic_debug_2', '0', '1')), 
				array('config.add', array('closetopic_debug_3', '0', '1')), 
		);
	}
    public function revert_data()
	{
		return array(
			// remove from configs
			array('config.remove', array('closetopic_gc')),
			array('config.remove', array('closetopic_last_gc')),
			array('config.remove', array('closetopic_debug')),
			array('config.remove', array('closetopic_debug_2')),
			array('config.remove', array('closetopic_debug_3')),
		);
	}

}
