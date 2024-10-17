<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Administrator\View\Mailtemplate;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

/**
 * View class for a list of rooms.
 * @property-read Form $form
 * @property-read object $item
 * @property-read Registry $state
 *
 * @since   1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An object of item
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    Registry
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Filter form
	 *
	 * @var    \JForm
	 * @since  1.6
	 */
	protected $form;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  \JObject
	 */
	protected $canDo;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function display($tpl = null): void
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');

		if (count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		$user = $this->getCurrentUser();
		$isNew = ($this->item->id == 0);
		$toolbar = Toolbar::getInstance();

		$canDo = ContentHelper::getActions('com_roombooking');

		ToolbarHelper::title($isNew ? Text::_('COM_ROOMBOOKING_MANAGER_EMAILTEMPLATE_NEW') : Text::_('COM_ROOMBOOKING_MANAGER_EMAILTEMPLATE_EDIT'), 'bookmark mailtemplate');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit') || $canDo->get('core.create')) {
			$toolbar->apply('mailtemplate.apply');
			$toolbar->save('mailtemplate.save');
		}

		if (empty($this->item->id)) {
			$toolbar->cancel('mailtemplate.cancel', 'JTOOLBAR_CANCEL');
		} else {
			$toolbar->cancel('mailtemplate.cancel');
		}
	}
}
