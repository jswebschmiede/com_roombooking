// Function to update the required attribute
export function updateRecurrenceEndDateRequired() {
    let recurringRadio = document.querySelector('input[name="jform[recurring]"]:checked');
    let endDateField = document.querySelector('input[name="jform[recurrence_end_date]"]');
    let recurrenceTypeSelect = document.querySelector('select[name="jform[recurrence_type]"]');
    let feedback = document.querySelector('.form-control-feedback');

    if (recurringRadio && recurringRadio.value === '1') {
        if (endDateField) {
            endDateField.setAttribute('required', 'required');
        }
        if (recurrenceTypeSelect) {
            recurrenceTypeSelect.setAttribute('required', 'required');
            recurrenceTypeSelect.classList.add('validate-validRecurrenceType');
        }
    } else {
        if (endDateField) {
            endDateField.removeAttribute('required');
        }
        if (recurrenceTypeSelect) {
            recurrenceTypeSelect.removeAttribute('required');
            recurrenceTypeSelect.classList.remove('validate-validRecurrenceType');
            recurrenceTypeSelect.selectedIndex = 0;
        }
        if (feedback) {
            feedback.remove();
        }

        if (typeof document.formvalidator !== 'undefined') {
            if (endDateField) {
                document.formvalidator.markValid(endDateField);
            }
            if (recurrenceTypeSelect) {
                document.formvalidator.markValid(recurrenceTypeSelect);
            }
        }
    }
}

// Define a custom validation rule for the recurrence type select
export function setValidRecurrenceTypeHandler() {
    if (typeof document.formvalidator !== 'undefined') {
        document.formvalidator.setHandler('validRecurrenceType', function (value) {
            return value !== 'none';
        });
    }
}

// Add event listeners to the recurring radio buttons
export function addRecurringRadioListeners() {
    let recurringRadios = document.querySelectorAll('input[name="jform[recurring]"]');
    recurringRadios.forEach(function (radio) {
        radio.addEventListener('change', updateRecurrenceEndDateRequired);
    });
}

// Main initialization function
export function initValidatorRules() {
    setValidRecurrenceTypeHandler();
    addRecurringRadioListeners();
    updateRecurrenceEndDateRequired();
}
