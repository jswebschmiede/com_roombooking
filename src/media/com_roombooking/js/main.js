import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import deLocale from '@fullcalendar/core/locales/de';
import interactionPlugin from '@fullcalendar/interaction';
import bootstrap5Plugin from '@fullcalendar/bootstrap5';
import calcTotalAmount from './calc-total-amount';
import { initValidatorRules } from './validator-rules';

import 'bootstrap-icons/font/bootstrap-icons.css';
import '../css/global.css';

document.addEventListener('DOMContentLoaded', function () {
    const options = Joomla.getOptions('com_roombooking');
    const bookedDates = JSON.parse(options.bookedDates);
    const endDate = options.endDate;

    let calendarEl = document.getElementById('booking-calendar');
    if (calendarEl) {
        let calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin, bootstrap5Plugin],
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap5',
            height: 'auto',
            locale: deLocale,
            selectable: false,
            selectMirror: false,
            selectOverlap: false,
            unselectAuto: false,
            firstDay: 1,
            events: bookedDates,
            validRange: {
                start: new Date(),
                end: endDate,
            },
            headerToolbar: {
                right: 'title',
                left: 'prev,next today',
            },
            buttonText: {
                today: 'Heute',
                month: 'Monat',
            },
            dayMaxEvents: 0,
            displayEventTime: false,
            eventDisplay: 'background',
            selectConstraint: {
                start: new Date(),
                end: endDate,
            },
            dateClick: function (info) {
                if (isDateSelectable(info.date) && !isDateBooked(info.date)) {
                    calendar.unselect(); // Clear any existing selection
                    calendar.select(info.date); // Select only the clicked date
                    let inputDate = document.getElementById('jform_booking_date');
                    if (inputDate) {
                        let formattedDate = info.date.toLocaleDateString('de-DE', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                        });
                        inputDate.value = formattedDate;
                    }
                }
            },
            dayCellClassNames: function (arg) {
                if (isDateBooked(arg.date)) {
                    return ['booked-date'];
                } else if (!isDateSelectable(arg.date)) {
                    return ['unavailable-date'];
                }
                return [];
            },
            selectAllow: function (selectInfo) {
                return isDateSelectable(selectInfo.start) && !isDateBooked(selectInfo.start);
            },
        });
        calendar.render();

        function isDateBooked(date) {
            return bookedDates.some(
                (bookedDate) => new Date(bookedDate.start).toDateString() === date.toDateString(),
            );
        }

        function isDateSelectable(date) {
            let today = new Date();
            today.setHours(0, 0, 0, 0);
            let endDateObj = new Date(endDate);
            return date >= today && date <= endDateObj;
        }
    }

    // Calculate total amount
    calcTotalAmount(options);
});

window.onload = function () {
    initValidatorRules();
};
