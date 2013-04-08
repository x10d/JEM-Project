<?php
/**
 * @version 1.1 $Id$
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2009 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined( '_JEXEC' ) or die;

jimport('joomla.application.component.controller');

/**
 * EventList Component Cleanup Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class EventListControllerCleanup extends EventListController
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'cleaneventimg', 	'delete' );
		$this->registerTask( 'cleanvenueimg', 	'delete' );
		$this->registerTask( 'cleancategoryimg', 	'delete' );
	}

	/**
	 * logic to massdelete unassigned images
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function delete()
	{
		$task = JRequest::getCmd('task');

		if ($task == 'cleaneventimg') {
			$type = JText::_('COM_JEM_EVENT');
		} 
		
		if ($task == 'cleanvenueimg') {
			$type = JText::_('COM_JEM_VENUE');
		} 
		
		if ($task == 'cleancategoryimg') {
			$type = JText::_('COM_JEM_CATEGORY');
		} 
		


		$model = $this->getModel('cleanup');

		$total = $model->delete();

		$link = 'index.php?option=com_jem&view=cleanup';

		$msg = $total.' '.$type.' '.JText::_( 'COM_JEM_IMAGES_DELETED');

		$this->setRedirect( $link, $msg );
 	}
 	
  /**
   * logic to massdelete unassigned images
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function triggerarchive()
  {
    ELHelper::cleanup(1);

    $link = 'index.php?option=com_jem&view=cleanup';

    $msg = JText::_( 'COM_JEM_AUTOARCHIVE_DONE');

    $this->setRedirect( $link, $msg );
  }
}
?>