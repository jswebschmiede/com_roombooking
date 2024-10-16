<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Component\Roombooking\Site\Helper\RoombookingHelper;

/** @var \Joomla\Component\Roombooking\Administrator\View\Mailtemplate\HtmlView $this */

/** @var Joomla\CMS\Document\HtmlDocument $doc */
$doc = Factory::getApplication()->getDocument();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->useScript('component.roombooking.admin')
	->useStyle('component.roombooking.admin');

$doc->addScriptOptions('com_roombooking', [
	'placeholders' => RoombookingHelper::getMailPlaceholders(),
]);
?>

<form
	action="<?php echo Route::_('index.php?option=com_roombooking&view=mailtemplate&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="mailtemplate-form" class="form-validate">

	<div id="validation-form-failed" data-backend-detail="mailtemplate"
		data-message="<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>">
	</div>

	<div class="row title-alias form-vertical mb-3">
		<div class="col-12 col-md-6">
			<?php echo $this->form->renderField('name'); ?>
		</div>
	</div>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_ROOMBOOKING_MAILTEMPLATE_DETAILS')); ?>
		<div class="row">
			<div class="col-lg-9">
				<?php echo $this->form->renderField('subject'); ?>
				<?php echo $this->form->renderField('body'); ?>
				<?php echo $this->form->renderField('from_email'); ?>
				<?php if ($this->item->template_type == 'admin'): ?>
					<?php echo $this->form->renderField('to_email'); ?>
					<?php echo $this->form->renderField('cc'); ?>
					<?php echo $this->form->renderField('bcc'); ?>
				<?php endif ?>
			</div>
			<div class="col-lg-3">
				<?php if (Multilanguage::isEnabled()): ?>
					<?php echo $this->form->renderField('language'); ?>
				<?php endif; ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>

	<?php echo $this->form->renderField('template_type'); ?>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>