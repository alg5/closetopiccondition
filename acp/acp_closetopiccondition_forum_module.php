<?php
/**
*
* @author Alg
* @version 1.0.0.0
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace alg\closetopiccondition\acp;

/**
* @package acp
*/
class acp_closetopiccondition_forum_module
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $template, $config, $phpbb_container;
		$controller = $phpbb_container->get('alg.closetopiccondition.closetopiccondition_handler');

		$this->tpl_name = 'acp_closetopiccondition_forum';
		$this->page_title = 'ACP_CLOSETOPICCONDITION_SETTINGS';

		$template->assign_vars(array(
			'U_ACTION'								=> 'add',
			'S_CLOSETOPICCONDITION_PAGE'				=>true,
			'S_FORUM_OPTIONS'						=> make_forum_select(false, false, true, false, false),
			'U_CLOSETOPICCONDITION_PATH_FORUM'		=> $controller->get_router_path('forum'),
			'U_CLOSETOPICCONDITION_PATH_GET'			=> $controller->get_router_path('get'),
			'U_CLOSETOPICCONDITION_PATH_USER'			=> $controller->get_router_path('user'),
			'U_CLOSETOPICCONDITION_PATH_SAVE'			=> $controller->get_router_path('save'),
		));
	}
}
