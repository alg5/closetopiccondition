<?php
/**
*
* @author Alg
* @version v 1.0.0. Alg$
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace alg\closetopiccondition\acp;

class acp_closetopiccondition_forum_info
{
	function module()
	{
		return array(
			'filename'	=> '\alg\closetopiccondition\acp\acp_closetopiccondition_forum_module',
			'title'		=> 'ACP_CLOSETOPICCONDITION_SETTINGS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'closetopiccondition'			=> array('title' => 'ACP_CLOSETOPICCONDITION_SETTINGS', 'auth' => 'ext_alg/closetopiccondition && acl_a_board', 'cat' => array('ACP_CLOSETOPICCONDITION')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
