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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/** @var \Joomla\Component\Roombooking\Site\View\Rooms\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('com_roombooking.main');
$wa->useStyle('com_roombooking.style');
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
?>

<div class="com_roombooking rooms">
	<?php if ($this->params->get('show_page_heading')): ?>
		<div class="row">
			<div class="page-header mb-4">
				<h1>
					<?php if ($this->escape($this->params->get('page_heading'))): ?>
						<?php echo $this->escape($this->params->get('page_heading')); ?>
					<?php else: ?>
						<?php echo $this->escape($this->params->get('page_title')); ?>
					<?php endif; ?>
				</h1>
			</div>
		</div>
	<?php endif; ?>


	<div class="container">
		<div class="row row-cols-1 row-cols-md-3 g-4">
			<?php foreach ($this->items as $item): ?>
				<div class="col">
					<div class="card h-100 shadow-sm">
						<div class="position-relative">
							<?php echo HTMLHelper::_('image', $item->image, $item->name, ['class' => 'img-fluid card-img-top']); ?>
							<div class="position-absolute top-0 end-0 p-2">
								<span class="badge bg-primary">
									<span><?php echo $item->price; ?>
										<?php echo Text::_('COM_ROOMBOOKING_CURRENCY_PER_DAY'); ?></span>
								</span>
							</div>
						</div>
						<div class="card-body d-flex flex-column">
							<h5 class="card-title mb-3"><?php echo $item->name; ?></h5>
							<div class="card-text flex-grow-1">
								<ul class="list-unstyled d-flex flex-direction-row justify-content-between">
									<li class="d-flex align-items-center mb-2">
										<i class="fas fa-users me-2 text-primary hasTooltip"
											title="<?php echo Text::_('COM_ROOMBOOKING_MAX_CAPACITY'); ?>"></i>
										<span><?php echo $item->capacity; ?>
											<?php echo Text::_('COM_ROOMBOOKING_PERSONS'); ?></span>
									</li>
									<li class="d-flex align-items-center mb-2">
										<i class="fas fa-ruler-combined me-2 text-primary hasTooltip"
											title="<?php echo Text::_('COM_ROOMBOOKING_ROOM_SIZE'); ?>"></i>
										<span><?php echo $item->size; ?> m²</span>
									</li>
								</ul>
							</div>
							<a href="<?php echo $item->link; ?>" class="btn btn-outline-primary mt-auto">
								<?php echo Text::_('COM_ROOMBOOKING_MORE_DETAILS'); ?>
							</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ($this->pagination->pagesTotal > 1): ?>
		<div class="com-roombooking-rooms__pagination">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>
</div>