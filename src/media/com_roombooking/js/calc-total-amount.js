window.onload = (event) => {
    const options = Joomla.getOptions('com_roombooking');
    const price = options.price;

    // TODO: get vat rate from options in component
    const vatRate = options.vatRate || 0.19;
    const numericPrice = parseFloat(price.replace(',', '.'));
    const totalAmount = numericPrice * (1 + vatRate);

    const totalAmountInput = document.getElementById('jform_total_amount');

    if (totalAmountInput) {
        totalAmountInput.value = totalAmount.toFixed(2);
    }
};
