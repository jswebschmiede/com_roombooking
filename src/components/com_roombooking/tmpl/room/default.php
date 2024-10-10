<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/** @var \Joomla\Component\Roombooking\Site\View\Room\HtmlView $this */

/** @var \Joomla\CMS\Document\HtmlDocument $doc */
$doc = Factory::getApplication()->getDocument();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $doc->getWebAssetManager();
$wa->useScript('com_roombooking.main');
$wa->useStyle('com_roombooking.style');
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

// Demo booking data
$bookedDates = json_encode([
	[
		'start' => date('Y-m-d', strtotime('+3 days')),
	],
	[
		'start' => date('Y-m-d', strtotime('+10 days')),
	],
	[
		'start' => date('Y-m-d', strtotime('+20 days')),
	]
]);

// Calculate the end date (3 years from today)
$endDate = date('Y-m-d', strtotime('+3 years'));

$doc->addScriptOptions('com_roombooking', [
	'bookedDates' => $bookedDates,
	'endDate' => $endDate
]);
?>

<?php if ($this->params->get('show_page_heading')): ?>
	<div class="page-header">
		<h1>
			<?php if ($this->escape($this->params->get('page_heading'))): ?>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			<?php else: ?>
				<?php echo $this->escape($this->params->get('page_title')); ?>
			<?php endif; ?>
		</h1>
	</div>
<?php endif; ?>

<div class="com_roombooking room-<?php echo $this->item->id; ?> room-item pb-5">
	<div class="container">
		<div class="row">
			<div class="col-12">
				<figure class="room-image figure position-relative">
					<?php if (!empty($this->item->image)): ?>
						<?php echo HTMLHelper::_('image', $this->item->image, $this->item->name, ['class' => 'figure-img img-fluid']); ?>
					<?php endif; ?>

					<div class="position-absolute top-0 end-0 p-2">
						<div class="d-flex flex-wrap gap-2">
							<span class="badge bg-primary hasTooltip larger-badge"
								title="<?php echo Text::_('COM_ROOMBOOKING_MAX_CAPACITY'); ?>">
								<i class="fas fa-users me-1"></i>
								<?php echo $this->item->capacity; ?> <?php echo Text::_('COM_ROOMBOOKING_PERSONS'); ?>
							</span>
							<span class="badge bg-primary hasTooltip larger-badge"
								title="<?php echo Text::_('COM_ROOMBOOKING_ROOM_SIZE'); ?>">
								<i class="fas fa-ruler-combined me-1"></i>
								<?php echo $this->item->size; ?> m²
							</span>
							<span class="badge bg-primary hasTooltip larger-badge"
								title="<?php echo Text::_('COM_ROOMBOOKING_ROOM_PRICE'); ?>">
								<i class="fas fa-money-bill-wave me-1"></i>
								<?php echo $this->item->price; ?>
								<?php echo Text::_('COM_ROOMBOOKING_CURRENCY_PER_DAY'); ?>
							</span>
						</div>
					</div>
				</figure>
			</div>

			<div class="col-12">
				<h2 class="mb-4"><?php echo $this->item->name; ?></h2>
				<div class="room-description">
					<?php echo $this->item->description; ?>
				</div>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-12">
				<form
					action="<?php echo Route::_('index.php?option=com_roombooking&task=room.booking&id=' . $this->item->id); ?>"
					method="post" class="room-booking-form card">
					<div class="card-header ">
						<h3 class="card-title">Raum buchen</h3>
					</div>

					<div class="card-body">
						<div class="row gx-5">
							<div class="col-md-5">
								<div class="room-sidebar">
									<div id="booking-calendar"></div>
									<p class="small text-muted mt-1">
										Bitte wählen Sie ein Datum aus, um den gewünschten Raum zu buchen. Rot markierte
										Tage sind bereits gebucht.
									</p>
								</div>
							</div>
							<div class="col-md-7">
								<div class="mt-4">
									<div class="recurring-booking mt-3">
										<div class="form-check">
											<input type="checkbox" class="form-check-input" id="recurring-booking"
												name="recurring-booking">
											<label for="recurring-booking" class="form-check-label">
												Wiederkehrende Buchung
											</label>
										</div>
									</div>

								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-12 text-end">
								<button type="submit" class="btn btn-primary">Kostenpflichtig buchen</button>
							</div>
						</div>
					</div>
					<input type="hidden" id="booking-date" name="booking_date">
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			</div>
		</div>
	</div>
</div>