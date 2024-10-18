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
?>

<form
	action="<?php echo Route::_('index.php?option=com_roombooking&view=booking&layout=edit&id=' . (int) $this->item->id); ?>"
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
				<?php echo $this->form->renderField('room_id'); ?>
				<?php echo $this->form->renderField('booking_dates'); ?>
				<?php echo $this->form->renderField('confirmed'); ?>
				<?php echo $this->form->renderField('payment_status'); ?>
				<?php echo $this->form->renderField('recurring'); ?>
				<?php echo $this->form->renderField('recurrence_type'); ?>
				<?php echo $this->form->renderField('recurrence_end_date'); ?>
				<?php echo $this->form->renderField('total_amount'); ?>
			</div>
			<div class="col-lg-3">
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'customer', Text::_('COM_ROOMBOOKING_CUSTOMER_INFO')); ?>
		<div class="row">
			<div class="col-lg-9">
				<?php echo $this->form->renderField('customer_name'); ?>
				<?php echo $this->form->renderField('customer_address'); ?>
				<?php echo $this->form->renderField('customer_phone'); ?>
				<?php echo $this->form->renderField('customer_email'); ?>
				<?php echo $this->form->renderField('privacy_accepted'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>