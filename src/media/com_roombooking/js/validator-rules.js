window.onload = (event) => {
    // Function to update the required attribute
    function updateRecurrenceEndDateRequired() {
        let recurringRadio = document.querySelector('input[name="jform[recurring]"]:checked');
        let endDateField = document.querySelector('input[name="jform[recurrence_end_date]"]');
        let feedback = document.querySelector('.form-control-feedback');
        let label = document.querySelector('#jform_recurrence_end_date-lbl');

        if (recurringRadio && recurringRadio.value === '1') {
            endDateField.setAttribute('required', 'required');
        } else {
            endDateField.removeAttribute('required');
            endDateField.classList.remove('form-control-danger', 'invalid');
            endDateField.parentElement.classList.remove('has-danger');
            if (label) {
                label.classList.remove('invalid');
            }
            if (feedback) {
                feedback.remove();
            }
        }
    }

    // Define a custom validation rule for the recurrence type select
    document.formvalidator.setHandler('validRecurrenceType', function (value) {
        return value !== 'none';
    });

    // Add event listeners to the recurring radio buttons
    let recurringRadios = document.querySelectorAll('input[name="jform[recurring]"]');
    recurringRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            updateRecurrenceEndDateRequired();
        });
    });

    // Initial call to set the correct state
    updateRecurrenceEndDateRequired();
};
