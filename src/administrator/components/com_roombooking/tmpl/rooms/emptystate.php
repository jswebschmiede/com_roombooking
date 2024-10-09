<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_roombooking
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Roombooking\Administrator\View\Roombookings\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_ROOMBOOKING',
    'formURL' => 'index.php?option=com_roombooking&view=rooms',
    'icon' => 'icon-bookmark rooms',
];

$user = $this->getCurrentUser();

if ($user->authorise('core.create', 'com_roombooking') || count($user->getAuthorisedCategories('com_roombooking', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_roombooking&task=room.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
