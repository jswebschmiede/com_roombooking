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
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Component\Roombooking\Site\Helper\TokenHelper;

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
    private array $mailPlaceholders = [
        '{{customer_name}}',
        '{{customer_email}}',
        '{{customer_phone}}',
        '{{customer_address}}',
        '{{booking_date}}',
        '{{recurring}}',
        '{{recurrence_type}}',
        '{{recurrence_end_date}}',
        '{{total_amount}}',
        '{{room_id}}',
        '{{room_name}}'
    ];

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

        foreach ($this->mailPlaceholders as $placeholder) {
            $key = trim($placeholder, '{}');
            if (isset($data[$key])) {
                $template->body = str_replace($placeholder, $data[$key], $template->body);
                $template->subject = str_replace($placeholder, $data[$key], $template->subject);
            }

            // Check if booking_details placeholder exists in template body
            if (strpos($template->body, '{{booking_details}}') !== false) {

                // Prepare booking details string
                $bookingDetails = $this->prepareBookingDetails($data);

                // Replace booking_details placeholder with prepared string
                $template->body = str_replace('{{booking_details}}', $bookingDetails, $template->body);
            }
        }

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
        $bookingDetails = "<p><strong>Buchungsdatum:</strong> {$data['booking_date']}</p>";
        $bookingDetails .= "<p><strong>Wiederholend:</strong> " . ($data['recurring'] ? "Ja" : "Nein") . "</p>";
        if ($data['recurring']) {
            $bookingDetails .= "<p><strong>Wiederholungstyp:</strong> {$data['recurrence_type']}</p>";
            $bookingDetails .= "<p><strong>Enddatum der Wiederholung:</strong> {$data['recurrence_end_date']}</p>";
        }
        $bookingDetails .= "<p><strong>Gesamtbetrag:</strong> {$data['total_amount']}</p>";

        $bookingDetails .= "<h3>Kundendaten:</h3>";
        $bookingDetails .= "<p><strong>Kundenname:</strong> {$data['customer_name']}</p>";
        $bookingDetails .= "<p><strong>E-Mail:</strong> {$data['customer_email']}</p>";
        $bookingDetails .= "<p><strong>Telefon:</strong> {$data['customer_phone']}</p>";
        $bookingDetails .= "<p><strong>Adresse:</strong> {$data['customer_address']}</p>";
        $bookingDetails .= "<p><strong>Raumname:</strong> {$data['room_name']}</p>";

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
                $token = TokenHelper::getTokenByBookingId($db, $bookingId, 'email_confirmation');

                if (!$token) {
                    throw new \RuntimeException('No confirmation token found for this booking.');
                }

                $confirmLink = $this->generateConfirmationLink($token);

                $customerMailTemplate->body = str_replace('{{confirm_link}}', $confirmLink, $customerMailTemplate->body);

                // TODO: send email to admin
                $this->sendEmail($adminMailTemplate, $validData, 'admin');

                // TODO: send email to customer
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
            $view->setData('No token found for this booking.', 'danger');
            $view->display();
            return;
        }

        $bookingId = $tokenObject->booking_id;

        if ($tokenObject->type === 'email_confirmation') {
            if (
                TokenHelper::deleteToken($db, $token) &&
                $bookingModel->confirmBooking($bookingId)
            ) {
                $view->setData('Booking confirmed successfully', 'success');
            } else {
                $view->setData('Booking confirmation failed', 'danger');
            }

            $view->display();
            return;
        }

        if ($tokenObject->type === 'booking_cancellation') {
            // TODO: cancel booking
            $view->setData('Booking cancelled successfully', 'success');
            $view->display();
            return;
        }

    }
}
