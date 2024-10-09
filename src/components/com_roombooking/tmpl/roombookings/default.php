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

/** @var \Joomla\Component\Roombooking\Site\View\Roombookings\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('com_roombooking.main');
$wa->useStyle('com_roombooking.style');
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

	<?php foreach ($this->items as $item): ?>
		<div class="row">
			<div class="col-12 mb-4">
				<h2><?php echo $item->name; ?></h2>
				<div class="details small mb-3">
					<time datetime="<?php echo $item->created; ?>">
						<?php echo Text::_('JDATE'); ?>:
						<?php echo JDate::getInstance($item->created)->format('d.m.Y'); ?>
					</time>
					<div><?php echo Text::_('JAUTHOR'); ?>: <?php echo $item->author; ?></div>
				</div>
				<?php echo $item->description; ?>

				<a href="<?php echo $item->link; ?>" class="btn btn-primary">
					More
				</a>
			</div>
		</div>
	<?php endforeach; ?>

	<?php if ($this->pagination->pagesTotal > 1): ?>
		<div class="com-roombooking-rooms__pagination">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>
</div>