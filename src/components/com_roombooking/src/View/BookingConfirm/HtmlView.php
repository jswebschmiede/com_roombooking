<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Site\View\BookingConfirm;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * HTML Room View class for the Roombooking component
 *
 * @property-read string $message
 * @property-read string $type
 * 
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected string $message;
	protected string $type;

	/**
	 * Set the data for the view.
	 *
	 * @param string $message The message to display.
	 * @param string $type The type of message (e.g. 'success', 'danger', 'info').
	 * @return void
	 */
	public function setData(string $message, string $type): void
	{
		$this->message = $message;
		$this->type = $type;
	}
}
