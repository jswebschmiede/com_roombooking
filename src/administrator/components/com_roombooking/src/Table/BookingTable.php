<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\Event\DispatcherInterface;

/**
 * Booking table
 *
 * @since  1.0.0
 */
class BookingTable extends Table
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since   1.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver        $db          Database connector object
	 * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
	 *
	 * @since   1.0.0
	 */
	public function __construct(DatabaseDriver $db, DispatcherInterface $dispatcher = null)
	{
		parent::__construct('#__roombooking_bookings', 'id', $db, $dispatcher);

		$this->created = Factory::getDate()->toSql();
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Overloaded check function
	 */
	public function check(): bool
	{
		try {
			parent::check();
		} catch (\Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Set name
		$this->name = htmlspecialchars_decode($this->name, ENT_QUOTES);

		// Set created date if not set.
		if (!(int) $this->created) {
			$this->created = Factory::getDate()->toSql();
		}

		// Set ordering
		if ($this->state < 0) {
			// Set ordering to 0 if state is archived or trashed
			$this->ordering = 0;
		} elseif (empty($this->ordering)) {
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder($this->_db->quoteName('state') . ' >= 0');
		}

		return true;
	}

	/**
	 * Method to store a row
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 */
	public function store($updateNulls = true): bool
	{
		// Store the new row
		$result = parent::store($updateNulls);

		// Need to reorder ?
		if ($result && !empty($this->_reorderConditions)) {
			// Reorder the oldrow
			$this->reorder($this->_db->quoteName('state') . ' >= 0');
		}

		return $result;
	}
}
