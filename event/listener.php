<?php
/**
*
* @package phpBB Extension - Close Topic By Conditions
* @copyright (c) 2017 alg
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace alg\closetopiccondition\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
/**
* Assign functions defined in this class to event listeners in the core
*
* @return array
* @static
* @access public
*/
	static public function getSubscribedEvents()
	{
		return array(
		);
	}

	/** @var \phpbb\template\template */
	protected $template;

	//** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/**
	* Constructor
	*/
	public function __construct($phpbb_root_path, \phpbb\template\template $template)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->template = $template;
	}
}
