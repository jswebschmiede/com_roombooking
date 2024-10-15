<?php

namespace Joomla\Component\Roombooking\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Mailtemplates list controller class.
 *
 * @since  1.0.0
 */
class MailtemplatesController extends AdminController
{
	/**
	 * Proxy for getModel
	 * @since    1.6
	 *
	 * @param string $name
	 * @param string $prefix
	 * @param array $config
	 *
	 * @return BaseDatabaseModel|bool
	 */
	public function getModel($name = 'Mailtemplate', $prefix = 'Administrator', $config = []): BaseDatabaseModel|bool
	{
		return parent::getModel($name, $prefix, ['ignore_request' => true]);
	}
}
