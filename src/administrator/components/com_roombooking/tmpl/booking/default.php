<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Roombooking\Administrator\View\Booking\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_contenthistory');
$wa->useScript('keepalive')
	->useScript('form.validate')
	->useScript('com_contenthistory.admin-history-versions');

$state = $this->getState();
$item = $this->getItem();
$form = $this->getForm();
?>

<form
	action="<?php echo Route::_('index.php?option=com_roombooking&view=booking&layout=edit&id=' . (int) $item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="booking-form" class="form-validate">

	<div id="validation-form-failed" data-backend-detail="booking"
		data-message="<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>">
	</div>

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_ROOMBOOKING_BOOKING_DETAILS')); ?>
		<div class="row">
			<div class="col-lg-9">
				<?php echo $form->renderField('room_id'); ?>
				<?php echo $form->renderField('booking_date'); ?>
				<?php echo $form->renderField('confirmed'); ?>
				<?php echo $form->renderField('payment_status'); ?>
				<?php echo $form->renderField('recurring'); ?>
				<?php echo $form->renderField('recurrence_type'); ?>
				<?php echo $form->renderField('recurrence_end_date'); ?>

			</div>
			<div class="col-lg-3">
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'customer', Text::_('COM_ROOMBOOKING_CUSTOMER_INFO')); ?>
		<div class="row">
			<div class="col-lg-9">
				<?php echo $form->renderField('customer_name'); ?>
				<?php echo $form->renderField('customer_address'); ?>
				<?php echo $form->renderField('customer_phone'); ?>
				<?php echo $form->renderField('customer_email'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>