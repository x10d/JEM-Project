<?php
/**
 * @version 1.9.6
 * @package JEM
 * @copyright (C) 2013-2014 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * JEM Component attendee Model
 *
 * @package JEM
 *
 */
class JEMModelAttendee extends JModelLegacy
{
	/**
	 * attendee id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Category data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid', 0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int category identifier
	 */
	function setId($id)
	{
		// Set category id and wipe data
		$this->_id = $id;
		$this->_data = null;
	}

	/**
	 * Method to get content category data
	 *
	 * @access	public
	 * @return	array
	 *
	 */
	function &getData()
	{
		if (!$this->_loadData()) {
			$this->_initData();
		}

		return $this->_data;
	}

	/**
	 * Method to load content event data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 *
	 */
	protected function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT r.*, u.username '
					. ' FROM #__jem_register AS r '
					. ' LEFT JOIN #__users AS u ON u.id = r.uid '
					. ' WHERE r.id = '.$this->_id
					;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();

			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the category data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 *
	 */
	protected function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data)) {
			$data = JTable::getInstance('jem_register', '');
			$data->username = null;
			$this->_data = $data;
		}
		return true;
	}

	function toggle()
	{
		$attendee = $this->getData();

		if (!$attendee->id) {
			$this->setError('COM_JEM_MISSING_ATTENDEE_ID');
			return false;
		}

		$row = JTable::getInstance('jem_register', '');
		$row->bind($attendee);
		$row->waiting = $attendee->waiting ? 0 : 1;
		return $row->store();
	}

/**
	 * Method to store the attendee
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function store($data)
	{
		$eventid = $data['event'];

		$row  = $this->getTable('jem_register', '');

		// bind it to the table
		if (!$row->bind($data)) {
			JError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}

		// sanitise id field
		$row->id = (int) $row->id;

		// Are we saving from an item edit?
		if (!$row->id) {
			$row->uregdate 		= gmdate('Y-m-d H:i:s');

			$query = ' SELECT e.maxplaces, e.waitinglist, COUNT(r.id) as booked '
			       . ' FROM #__jem_events AS e '
			       . ' INNER JOIN #__jem_register AS r ON r.event = e.id '
			       . ' WHERE e.id = ' . $this->_db->Quote($eventid)
			       . '   AND r.waiting = 0 '
			       . ' GROUP BY e.id ';
			$this->_db->setQuery($query);
			$details = $this->_db->loadObject();

			// put on waiting list ?
			if ($details->maxplaces > 0) // there is a max
			{
				// check if the user should go on waiting list
				if ($details->booked >= $details->maxplaces)
				{
					if (!$details->waitinglist) {
						JError::raiseWarning(0, JText::_('COM_JEM_ERROR_REGISTER_EVENT_IS_FULL'));
						return false;
					}
					$row->waiting = 1;
				}
			}
		}

		// Make sure the data is valid
		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		// Store it in the db
		if (!$row->store()) {
			JError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}

		return $row;
	}
}
?>