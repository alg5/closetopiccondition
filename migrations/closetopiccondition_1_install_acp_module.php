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

class closetopiccondition_1_install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['closetopiccondition']) && version_compare($this->config['closetopiccondition'], '1.0.*', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('closetopiccondition', '1.0.0')),

			// Add ACP modules
			array('module.add', array('acp', 'ACP_CAT_DOT_MODS', 'ACP_CLOSETOPICCONDITION')),

            
            array('module.add', array('acp', 'ACP_CLOSETOPICCONDITION', array(
					'module_basename'	=> '\alg\closetopiccondition\acp\acp_closetopiccondition_forum_module',
					'module_langname'	=> 'ACP_CLOSETOPICCONDITION_SETTINGS',
					'module_mode'		=> 'closetopiccondition_forum',
					'module_auth'		=> 'ext_alg/closetopiccondition && acl_a_board',
				))),
            
            array('module.add', array('acp', 'ACP_CLOSETOPICCONDITION', array(
					'module_basename'	=> '\alg\closetopiccondition\acp\acp_closetopiccondition_common_module',
					'module_langname'	=> 'ACP_CLOSETOPICCONDITION_COMMON_SETTINGS',
					'module_mode'		=> 'closetopiccondition_common',
					'module_auth'		=> 'ext_alg/closetopiccondition && acl_a_board',
				))),
		);
	}
	public function revert_data()
	{
		return array(
			// Current version
				array('config.remove', array('closetopiccondition')),

			// remove from ACP modules
			array('if', array(
				array('module.exists', array('acp', 'ACP_CLOSETOPICCONDITION', array(
					'module_basename'	=> '\alg\closetopiccondition\acp\acp_closetopiccondition_forum_module',
					'module_langname'	=> 'ACP_CLOSETOPICCONDITION_SETTINGS',
					'module_mode'		=> 'closetopiccondition',
					'module_auth'		=> 'ext_alg/closetopiccondition && acl_a_board',
					),
				)),
				array('module.remove', array('acp', 'ACP_CLOSETOPICCONDITION', array(
					'module_basename'	=> '\alg\closetopiccondition\acp\acp_closetopiccondition_forum_module',
					'module_langname'	=> 'ACP_CLOSETOPICCONDITION_SETTINGS',
					'module_mode'		=> 'closetopiccondition',
					'module_auth'		=> 'ext_alg/closetopiccondition && acl_a_board',
					),
				)),
			)),
			array('if', array(
				array('module.exists', array('acp', 'ACP_CLOSETOPICCONDITION', array(
					'module_basename'	=> '\alg\closetopiccondition\acp\acp_closetopiccondition_common_module',
					'module_langname'	=> 'ACP_CLOSETOPICCONDITION_COMMON_SETTINGS',
					'module_mode'		=> 'closetopiccondition_common',
					'module_auth'		=> 'ext_alg/closetopiccondition && acl_a_board',
					),
				)),
				array('module.remove', array('acp', 'ACP_CLOSETOPICCONDITION', array(
					'module_basename'	=> '\alg\closetopiccondition\acp\acp_closetopiccondition_module',
					'module_langname'	=> 'ACP_CLOSETOPICCONDITION_COMMON_SETTINGS',
					'module_mode'		=> 'closetopiccondition',
					'module_auth'		=> 'ext_alg/closetopiccondition && acl_a_board',
					),
				)),
			)),			
            array('module.remove', array('acp', 'ACP_CAT_DOT_MODS', 'ACP_CLOSETOPICCONDITION')),
		);
	}

    
}
