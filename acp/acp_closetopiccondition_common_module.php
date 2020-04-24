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
class acp_closetopiccondition_common_module
{
//	var $u_action;
//	var $new_config = array();

	function main($id, $mode)
	{
		global  $config, $template, $phpbb_container;
		$controller = $phpbb_container->get('alg.closetopiccondition.closetopiccondition_handler');

		$this->tpl_name = 'acp_closetopiccondition_common';
		$this->page_title = 'ACP_CLOSETOPICCONDITION_COMMON_SETTINGS';

		$template->assign_vars(array(
			//'U_ACTION'			=> 'add',
			'S_CLOSETOPICCONDITION_PAGE'			=> true,
			'U_CLOSETOPICCONDITION_PATH_PERIOD'	=> $controller->get_router_path('period'),
			'CLOSETOPICCONDITION_PERIOD'			=>  isset($config['closetopiccondition_period']) ? (int) $config['closetopiccondition_period']  : 1,
            
		));
	}
}
