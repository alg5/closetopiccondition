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

class closetopiccondition_4_install_schema_add extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'closetopiccondition_options') && $this->db_tools->sql_column_exists($this->table_prefix . 'closetopiccondition_options', 'archive_forum_id');
	}
	static public function depends_on()
	{
		return array('\alg\closetopiccondition\migrations\closetopiccondition_3_install_cron');
	}
	public function update_schema()
	{
		return  array(
			'add_columns' => [
					$this->table_prefix . 'closetopiccondition_options' => [
						'archive_forum_id'	=> ['UINT', 0],
						'leave_shadow'		=> ['TINT:1', 0],
					],
				],
		);

	}
}
