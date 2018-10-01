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

namespace alg\closetopiccondition;

/**
 * Class ext
 *
 * It is recommended to remove this file from
 * an extension if it is not going to be used.
 */
class ext extends \phpbb\extension\base
{
	/**
	* Enable notifications for the extension
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	* @access public
	*/
	public function enable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->enable_notifications('alg.closetopiccondition.notification.type.topicposter');
				$phpbb_notifications->enable_notifications('alg.closetopiccondition.notification.type.moder');
				return 'notification';

			break;

			default:

				return parent::enable_step($old_state);

			break;
		}
	}

	/**
	* Disable notifications for the extension
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	* @access public
	*/
	public function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->disable_notifications('alg.closetopiccondition.notification.type.topicposter');
				$phpbb_notifications->disable_notifications('alg.closetopiccondition.notification.type.moder');
				return 'notification';

			break;

			default:

				return parent::disable_step($old_state);

			break;
		}
	}

	/**
	* Purge notifications for the extension
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	* @access public
	*/
	public function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->purge_notifications('alg.closetopiccondition.notification.type.topicposter');
				$phpbb_notifications->purge_notifications('alg.closetopiccondition.notification.type.moder');
				return 'notification';

			break;

			default:

				return parent::purge_step($old_state);

			break;
		}
	}
}
