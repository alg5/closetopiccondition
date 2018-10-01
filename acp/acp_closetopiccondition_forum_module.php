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
		global $db, $user, $auth, $template, $request, $phpbb_container;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx, $phpbb_log;
		$controller = $phpbb_container->get('alg.closetopiccondition.closetopiccondition_handler');

		$this->tpl_name = 'acp_closetopiccondition_forum';
		$this->page_title = 'ACP_CLOSETOPICCONDITION_SETTINGS';
		$action	= $request->variable('action', '');
//		$submit = (isset($_POST['submit'])) ? true : false;

		//$form_key = 'limitpostsintopic_forum';
		//add_form_key($form_key);
		//$error = array();
		//// We validate the complete config if whished

		//if ($submit && !check_form_key($form_key))
		//{
		//	$error[] = $user->lang['FORM_INVALID'];
		//}
		//// Do not write values if there is an error
		//if (sizeof($error))
		//{
		//	$submit = false;
		//}
		$template->assign_vars(array(
			//'U_ACTION'			=> $this->u_action . '&amp;action=add',
			'U_ACTION'			=> 'add',
			'S_CLOSETOPICCONDITION_PAGE'			=>true,
			'S_FORUM_OPTIONS'	=> make_forum_select(false, false, true, false, false),
			'U_CLOSETOPICCONDITION_PATH_FORUM'				=> $controller->get_router_path('forum'),
			'U_CLOSETOPICCONDITION_PATH_GET'				=> $controller->get_router_path('get'),
			'U_CLOSETOPICCONDITION_PATH_USER'				=> $controller->get_router_path('user'),
			'U_CLOSETOPICCONDITION_PATH_SAVE'				=> $controller->get_router_path('save'),
		));
	}
}
