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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Roombooking\Site\Helper\TokenHelper;
use Joomla\Component\Roombooking\Site\Helper\RoombookingHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Roombooking Controller
 *
 * @since  1.0.0
 */
class BookingController extends BaseController
{
    /**
     * Replace the placeholders in the mail template with the booking data
     *
     * @param object $template
     * @param array $data
     * @return object
     */
    private function replacePlaceholders(object $template, array $data): object
    {
        $bookingDetails = '';

        /** @var \Joomla\Component\Roombooking\Site\Model\RoomModel $roomModel */
        $roomModel = $this->getModel('room', 'Site');
        $room = $roomModel->getItem($data['room_id']);

        $data['room_name'] = $room->name;

        foreach (RoombookingHelper::getMailPlaceholders() as $placeholder => $label) {
            $placeholderWithoutBraces = trim($placeholder, '{}');

            if (isset($data[$placeholderWithoutBraces])) {
                $template->body = str_replace($placeholder, $data[$placeholderWithoutBraces], $template->body);
                $template->subject = str_replace($placeholder, $data[$placeholderWithoutBraces], $template->subject);
            }

            // Check if booking_details placeholder exists in template body
            if (strpos($template->body, '{{booking_details}}') !== false) {

                // Prepare booking details string
                $bookingDetails = $this->prepareBookingDetails($data);

                // Replace booking_details placeholder with prepared string
                $template->body = str_replace('{{booking_details}}', $bookingDetails, $template->body);
            }
        }

        // Convert markdown to html
        $parsedown = new \Parsedown();
        $template->body = $parsedown->text($template->body);

        return $template;
    }

    /**
     * Prepare the booking details string
     *
     * @param array $data
     * @return string
     */
    private function prepareBookingDetails(array $data): string
    {
        $bookingDetails = "**" . Text::_('COM_ROOMBOOKING_BOOKING_DATE_LBL') . ":** {$data['booking_date']}\n\n";
        $bookingDetails .= "**" . Text::_('COM_ROOMBOOKING_BOOKING_RECURRING_LBL') . ":** " . ($data['recurring'] ? Text::_('JYES') : Text::_('JNO')) . "\n\n";
        if ($data['recurring']) {
            $bookingDetails .= "**" . Text::_('COM_ROOMBOOKING_BOOKING_RECURRENCE_TYPE_LBL') . ":** " . RoombookingHelper::translateRecurrenceType($data['recurrence_type']) . "\n\n";
            $bookingDetails .= '**' . Text::_('COM_ROOMBOOKING_BOOKING_RECURRENCE_END_DATE_LBL') . ':** ' . HTMLHelper::_('date', $data['recurrence_end_date'], Text::_('DATE_FORMAT_LC4')) . "\n\n";
        }
        $bookingDetails .= "**" . Text::_('COM_ROOMBOOKING_BOOKING_TOTAL_AMOUNT_LBL') . ":** {$data['total_amount']}\n\n";

        $bookingDetails .= "### " . Text::_('COM_ROOMBOOKING_CUSTOMER_INFO') . ":\n\n";
        $bookingDetails .= "**" . Text::_('COM_ROOMBOOKING_BOOKING_CUSTOMER_NAME_LBL') . ":** {$data['customer_name']}\n\n";
        $bookingDetails .= "**" . Text::_('COM_ROOMBOOKING_BOOKING_CUSTOMER_EMAIL_LBL') . ":** {$data['customer_email']}\n\n";
        $bookingDetails .= "**" . Text::_('COM_ROOMBOOKING_BOOKING_CUSTOMER_PHONE_LBL') . ":** {$data['customer_phone']}\n\n";
        $bookingDetails .= "**" . Text::_('COM_ROOMBOOKING_BOOKING_CUSTOMER_ADDRESS_LBL') . ":** {$data['customer_address']}\n\n";
        $bookingDetails .= "**" . Text::_('COM_ROOMBOOKING_ROOM_NAME_LBL') . ":** {$data['room_name']}\n";

        return $bookingDetails;
    }

    /**
     * Generate the confirmation link
     *
     * @param string $token
     * @return string
     */
    public function generateConfirmationLink(string $token): string
    {
        return Uri::root() . "index.php?option=com_roombooking&task=booking.confirm&token=" . $token;
    }

    /**
     * Generate the cancellation link
     *
     * @param string $token
     * @return string
     */
    public function generateCancellationLink(string $token): string
    {
        return Uri::root() . "index.php?option=com_roombooking&task=booking.cancel&token=" . $token;
    }

    /**
     * Send an email
     *
     * @param object $mailTemplate
     * @param array $data
     * @return void
     */
    private function sendEmail(object $mailTemplate, array $data, string $type): void
    {
        /** @var \Joomla\CMS\Mail\Mail $mailer */
        $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
        $mailer->setSubject($mailTemplate->subject);
        $mailer->setBody($mailTemplate->body);

        if ($type === 'admin') {
            $mailer->addRecipient($mailTemplate->to_email, $this->app->get('sitename'));

            if (isset($mailTemplate->cc) && !empty($mailTemplate->cc)) {
                $mailer->addCc($mailTemplate->cc, $this->app->get('sitename'));
            }
            if (isset($mailTemplate->bcc) && !empty($mailTemplate->bcc)) {
                $mailer->addBcc($mailTemplate->bcc, $this->app->get('sitename'));
            }
        } else {
            $mailer->addRecipient($data['customer_email'], $data['customer_name']);
        }

        $mailer->setFrom($mailTemplate->from_email, $this->app->get('sitename'));
        $mailer->isHtml(true);
        $mailer->Encoding = 'base64';

        if (!$mailer->send()) {
            $this->app->enqueueMessage('Error sending email', 'error');
        }
    }

    /**
     * Submit task for the booking form
     *
     * @return void
     */
    public function submit(): void
    {
        $this->checkToken();

        $currentUrl = Uri::getInstance()->toString();

        /** @var \Joomla\Database\DatabaseDriver $db */
        $db = Factory::getContainer()->get('DatabaseDriver');

        /** @var \Joomla\Component\Roombooking\Site\Model\RoomModel $roomModel */
        $roomModel = $this->getModel('room', 'Site');

        /** @var \Joomla\Component\Roombooking\Site\Model\BookingModel $bookingModel */
        $bookingModel = $this->getModel('booking', 'Site');

        $form = $roomModel->getForm([], false);

        if (!$form) {
            $this->app->enqueueMessage($roomModel->getError(), 'error');
            $this->setRedirect($currentUrl);
        }

        $data = $this->input->post->get('jform', array(), 'array');
        $validData = $roomModel->validate($form, $data);

        if (!$validData) {
            $errors = $roomModel->getErrors();

            foreach ($errors as $error) {
                ($error instanceof \Exception) ?
                    $this->app->enqueueMessage($error->getMessage(), 'warning') :
                    $this->app->enqueueMessage($error, 'warning');
            }

            // Save the form data in the session, using a unique identifier
            $this->app->setUserState('com_roombooking.booking', $data);
            $this->setRedirect($currentUrl);

        } else {
            if (!$bookingModel->save($validData)) {
                $this->app->enqueueMessage("Error saving booking to database", 'error');
                $this->setRedirect($currentUrl);
            } else {
                // Get the mail templates
                $adminMailTemplate = $bookingModel->getMailTemplate('admin');
                $customerMailTemplate = $bookingModel->getMailTemplate('customer');

                // Replace the placeholders in the mail templates
                $adminMailTemplate = $this->replacePlaceholders($adminMailTemplate, $validData);
                $customerMailTemplate = $this->replacePlaceholders($customerMailTemplate, $validData);

                // Get the booking ID from the session
                $bookingId = $this->app->getUserState('com_roombooking.booking.id');
                $token = TokenHelper::getTokenByBookingId($db, $bookingId);

                if (!$token) {
                    throw new \RuntimeException('No confirmation token found for this booking.');
                }

                $confirmLink = $this->generateConfirmationLink($token);
                $customerMailTemplate->body = str_replace('{{confirm_link}}', $confirmLink, $customerMailTemplate->body);

                $cancellationLink = $this->generateCancellationLink($token);
                $customerMailTemplate->body = str_replace('{{cancel_link}}', $cancellationLink, $customerMailTemplate->body);

                $this->sendEmail($adminMailTemplate, $validData, 'admin');
                $this->sendEmail($customerMailTemplate, $validData, 'customer');

                // Clear the form data in the session
                $this->app->setUserState('com_roombooking.booking', null);
                $this->app->enqueueMessage(Text::_(string: 'COM_ROOMBOOKING_BOOKING_SUCCESS'), 'success');

                $this->setRedirect($currentUrl);
            }
        }
    }

    /**
     * Confirm task for the booking
     * 
     * @return void
     */
    public function confirm(): void
    {
        /** @var \Joomla\Database\DatabaseDriver $db */
        $db = Factory::getContainer()->get('DatabaseDriver');

        /** @var \Joomla\Component\Roombooking\Site\Model\BookingModel $bookingModel */
        $bookingModel = $this->getModel('booking', 'Site');

        /** @var \Joomla\Component\Roombooking\Site\View\BookingConfirm\HtmlView $view */
        $view = $this->getView('bookingConfirm', 'html');
        $token = $this->input->get('token');
        $tokenObject = TokenHelper::getValidTokenInfo($db, $token);

        if (!$tokenObject) {
            $view->setData(Text::_('COM_ROOMBOOKING_BOOKING_CONFIRMATION_NO_TOKEN'), 'danger');
            $view->display();
            return;
        }

        $bookingId = $tokenObject->booking_id;

        if (
            TokenHelper::deleteToken($db, $token) &&
            $bookingModel->confirmBooking($bookingId)
        ) {
            $view->setData(Text::_('COM_ROOMBOOKING_BOOKING_CONFIRMED_SUCCESSFULLY'), 'success');
        } else {
            $view->setData(Text::_('COM_ROOMBOOKING_BOOKING_CONFIRMATION_FAILED'), 'danger');
        }

        $view->display();
        return;

    }

    public function cancel(): void
    {
        /** @var \Joomla\Database\DatabaseDriver $db */
        $db = Factory::getContainer()->get('DatabaseDriver');

        /** @var \Joomla\Component\Roombooking\Site\Model\BookingModel $bookingModel */
        $bookingModel = $this->getModel('booking', 'Site');

        /** @var \Joomla\Component\Roombooking\Site\View\BookingConfirm\HtmlView $view */
        $view = $this->getView('bookingConfirm', 'html');

        $token = $this->input->get('token');
        $tokenObject = TokenHelper::getValidTokenInfo($db, $token);

        if (!$tokenObject) {
            $view->setData(Text::_('COM_ROOMBOOKING_BOOKING_CONFIRMATION_NO_TOKEN'), 'danger');
            $view->display();
            return;
        }

        $bookingId = $tokenObject->booking_id;

        if (
            TokenHelper::deleteToken($db, $token) &&
            $bookingModel->cancelBooking($bookingId)
        ) {
            $view->setData(Text::_('COM_ROOMBOOKING_BOOKING_CANCELLED_SUCCESSFULLY'), 'success');
        } else {
            $view->setData(Text::_('COM_ROOMBOOKING_BOOKING_CANCEL_FAILED'), 'danger');
        }

        $view->display();
        return;
    }
}
