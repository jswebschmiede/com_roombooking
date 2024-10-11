export default function calcTotalAmount(options) {
    // Calculate total amount
    const price = options.price;
    const vatRate = options.vatRate || 0.19;
    const numericPrice = parseFloat(price.replace(',', '.'));
    const totalAmount = numericPrice * (1 + vatRate);

    const totalAmountInput = document.getElementById('jform_total_amount');

    if (totalAmountInput) {
        totalAmountInput.value = totalAmount.toFixed(2);
    }
}
