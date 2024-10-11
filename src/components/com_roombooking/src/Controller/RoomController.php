<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_roombooking
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Roombooking\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Roombooking\Site\Helper\RouteHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Roombooking Controller
 *
 * @since  1.0.0
 */
class RoomController extends BaseController
{
    /**
     * Submit task for the booking form
     *
     * @return void
     */
    public function submit(): void
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $currentUrl = Uri::getInstance()->toString();

        /** @var \Joomla\Component\Roombooking\Site\Model\RoomModel $model */
        $model = $this->getModel('room');
        $form = $model->getForm([], false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');
        }

        $data = $this->input->post->get('jform', array(), 'array');
        $validData = $model->validate($form, $data);

        if (!$validData) {
            $errors = $model->getErrors();

            foreach ($errors as $error) {
                ($error instanceof \Exception) ?
                    $app->enqueueMessage($error->getMessage(), 'warning') :
                    $app->enqueueMessage($error, 'warning');
            }

            // Save the form data in the session, using a unique identifier
            $app->setUserState('com_roombooking.booking', $data);

            $this->setRedirect($currentUrl);

        } else {
            /** @var \Joomla\Component\Roombooking\Site\Model\RoomModel $model */
            $model = $this->getModel('room');

            if (!$model->save($validData)) {
                $app->enqueueMessage("Error saving booking to database", 'error');
                $this->setRedirect($currentUrl);
            }

            // TODO: send email to admin


            // Clear the form data in the session
            $app->setUserState('com_roombooking.booking', null);
            $app->enqueueMessage(Text::_('COM_ROOMBOOKING_BOOKING_SUCCESS'), 'success');

            $this->setRedirect($currentUrl);
        }
    }
}
