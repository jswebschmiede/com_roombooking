<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Form\FormFactoryInterface;

defined('_JEXEC') or die;

/**
 * Methods supporting a single room record.
 *
 * @since  1.0.0
 */
class MailtemplateModel extends AdminModel
{
	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	public $typeAlias = 'com_roombooking.mailtemplate';
	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = [], $loadData = true): bool|Form
	{
		// Get the form.
		$form = $this->loadForm(
			'com_roombooking.mailtemplate',
			'mailtemplate',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Summary of getTable
	 * @param mixed $name
	 * @param mixed $prefix
	 * @param mixed $options
	 * @throws \Exception
	 * @return bool|Table
	 */
	public function getTable($name = '', $prefix = '', $options = []): bool|Table
	{
		$name = 'mailtemplate';
		$prefix = 'Table';

		if ($table = $this->_createTable($name, $prefix, $options)) {
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.0.0
	 */
	protected function loadFormData(): mixed
	{
		$app = Factory::getApplication();

		// Check the session for previously entered form data.
		$data = $app->getUserState(
			'com_roombooking.edit.mailtemplate.data',
			[]
		);

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   1.0.0
	 */
	public function save($data): bool
	{
		return parent::save($data);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null): array|bool
	{
		// Check if the template type is 'customer'
		if (isset($data['template_type']) && $data['template_type'] === 'customer') {
			// Remove the 'required' attribute for 'to_email' field
			$form->setFieldAttribute('to_email', 'required', 'false');
		}

		return parent::validate($form, $data, $group);
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