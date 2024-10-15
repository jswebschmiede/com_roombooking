<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormFactoryInterface;

/**
 * Methods supporting a list of room records.
 *
 * @since  1.0.0
 */
class MailtemplatesModel extends ListModel
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   1.0.0
	 */
	protected function getListQuery(): DatabaseQuery
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				[
					$db->quoteName('a.id'),
					$db->quoteName('a.template_type'),
					$db->quoteName('a.name'),
					$db->quoteName('a.subject'),
					$db->quoteName('a.body'),
					$db->quoteName('a.from_email'),
					$db->quoteName('a.to_email'),
					$db->quoteName('a.cc'),
					$db->quoteName('a.bcc'),
					$db->quoteName('a.language'),
					$db->quoteName('a.state'),
				]
			)
		)
			->from($db->quoteName('#__roombooking_mail_templates', 'a'));

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol === 'a.id') {
			$ordering = $db->quoteName('a.id') . ' ' . $db->escape($orderDirn);
		} else {
			$ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
		}

		$query->order($ordering);

		return $query;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Mailtemplate', $prefix = 'Administrator', $config = []): Table
	{
		return parent::getTable($type, $prefix, $config);
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.id', $direction = 'asc')
	{
		// Load the parameters.
		$this->setState('params', ComponentHelper::getParams('com_roombooking'));

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Summary of getItems
	 * @return array
	 */
	public function getItems(): array
	{
		return parent::getItems();
	}

	/**
	 * Summary of getFormFactory
	 * @return FormFactoryInterface
	 */
	public function getFormFactory(): FormFactoryInterface
	{
		return parent::getFormFactory();
	}
}