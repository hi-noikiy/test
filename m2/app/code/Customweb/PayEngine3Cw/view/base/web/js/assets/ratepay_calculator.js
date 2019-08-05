var calculationResult = null;
var payengine3Options;

var payengine3CalculatorIds = {
	totalAmount : 'payengine3TotalAmount',
	amount : 'payengine3Amount',
	interestAmount : 'payengine3InterestAmount',
	serviceCharge : 'payengine3ServiceCharge',
	interestRate : 'payengine3InterestRate',
	annualPercentageRate : 'payengine3AnnualPercentageRate',
	monthlyDebitInterest : 'payengine3MonthlyDebitInterest',
	numberOfRates : 'payengine3NumberOfRates',
	rate : 'payengine3Rate',
	lastRate : 'payengine3LastRate',
	calculationRateContainer : 'payengine3CalculationRateContainer',
	calculationMonthContainer : 'payengine3CalculationMonthContainer',
	calculationRate : 'payengine3CalculationRate',
	calculationMonth : 'payengine3CalculationMonth',
	calculationResult : 'payengine3CalculationResult',
	generalError : 'payengine3GeneralError',
	validationError : 'payengine3ValidationError',
	loading : 'payengine3Loading',
	calculatorConfig : 'payengine3CalculatorConfig',
	monthText : 'payengine3MonthText',
	lastRateText : 'payengine3LastRateText',
	allMonthText : 'payengine3AllMonthText',
	allRate : 'payengine3AllRate',
	rateInput : 'rp-rate-value',
	calculateButton : 'rp-btn-calculate-rate'
}, payengine3CalculationTypes = {
	calculationTime : 'calculation-time',
	calculationRate : 'calculation-rate',
}, payengine3StyleValues = {
	block : 'block',
	inlineBlock: 'inline-block',
	tableRow: 'table-row',
	none : 'none'
}
calculationType = 'calculation-time',
		payengine3CalculatorPercentage = '\u0025';

function payengine3SetCalculable() {
	var value = document.getElementById(payengine3CalculatorIds.rateInput).value;
	if (value) {
		document.getElementById(payengine3CalculatorIds.calculateButton)
				.removeAttribute('disabled');
	} else {
		document.getElementById(payengine3CalculatorIds.calculateButton)
				.setAttribute('disabled', 'disabled');
	}
}

function payengine3FormatMoney(cents) {
	formattedCents = parseFloat(cents / 100).toFixed(2);
	formattedCents = formattedCents.replace('.', ',');
	return formattedCents;
}

function payengine3FormatPercentage(percent) {
	formattedPercent = parseFloat(percent).toFixed(2);
	formattedPercent = formattedPercent.replace('.', ',');
	return formattedPercent;
}

function payengine3SetGeneralErrorDisplay(isBlock) {
	var generalErrorElement = document
			.getElementById(payengine3CalculatorIds.generalError);
	generalErrorElement.style.display = isBlock ? payengine3StyleValues.block
			: payengine3StyleValues.none;
}

function payengine3HideLoading() {
	var loadingElement = document
			.getElementById(payengine3CalculatorIds.loading);
	loadingElement.style.display = payengine3StyleValues.none;
}

function payengine3SetValidationError(isDisplayBlock, msg, param) {
	var validationError = document
			.getElementById(payengine3CalculatorIds.validationError);
	if (!isDisplayBlock) {
		validationError.style.display = payengine3StyleValues.none;
		return;
	}
	validationError.innerText = msg + ' ' + param;
	validationError.style.display = payengine3StyleValues.inlineBlock;
}

function payengine3ValidateCalulationParams() {
	var isFormValid = true, calculationMonthElement = document
			.getElementById(payengine3CalculatorIds.calculationMonth), calculationMonth = parseInt(calculationMonthElement.value), calculationMonthMin = calculationMonthElement.min, calculationMonthMax = calculationMonthElement.max, calculationRateElement = document
			.getElementById(payengine3CalculatorIds.calculationRate), calculationRateMin = calculationRateElement.min;

	userInputRate = calculationRateElement.value;
	userInputRate = userInputRate.replace(',', '.');
	var calculationRate = userInputRate * 100;
	calculationRate = parseInt(calculationRate);

	if (calculationType === payengine3CalculationTypes.calculationRate
			&& isNaN(calculationRate)) {
		isFormValid = false;
		payengine3SetValidationError(true,
				payengine3Options.validationErrors.rateRequired, '');
	}

	if (calculationType === payengine3CalculationTypes.calculationRate
			&& calculationRate < calculationRateMin) {
		isFormValid = false;
		payengine3SetValidationError(true,
				payengine3Options.validationErrors.minRate,
				payengine3FormatMoney(calculationRateMin)
						+ payengine3Options.calculatorCurrency);
	}

	if (calculationType === payengine3CalculationTypes.calculationTime
			&& isNaN(calculationMonth)) {
		isFormValid = false;
		payengine3SetValidationError(true,
				payengine3Options.validationErrors.monthRequired, '');
	}

	if (calculationType === payengine3CalculationTypes.calculationTime
			&& calculationMonth < calculationMonthMin) {
		isFormValid = false;
		payengine3SetValidationError(true,
				payengine3Options.validationErrors.minTime, calculationMonthMin);
	}

	if (calculationType === payengine3CalculationTypes.calculationTime
			&& calculationMonth > calculationMonthMax) {
		isFormValid = false;
		payengine3SetValidationError(true,
				payengine3Options.validationErrors.maxTime, calculationMonthMax);
	}

	if (isFormValid) {
		payengine3SetValidationError(false);
	}

	return isFormValid;
}

function payengine3CalculationCallback(error, result) {
	if (!result) {
		payengine3SetGeneralErrorDisplay(true);
		return;
	}
	payengine3SetGeneralErrorDisplay(false);
	payengine3ShowCalculationResult(result);
}

function payengine3CalculateInstallment() {
	if (!payengine3ValidateCalulationParams()) {
		return;
	}
	var calculationRequest = {};
	calculationRequest.amount = payengine3Options.initialAmount;
	calculationRequest.calculationType = calculationType;
	if (calculationRequest.calculationType === payengine3CalculationTypes.calculationRate) {
		userInputRate = document
				.getElementById(payengine3CalculatorIds.calculationRate).value;
		userInputRate = userInputRate.replace(',', '.');
		calculationRequest.calculationRate = userInputRate * 100;
	} else {
		calculationRequest.calculationTime = document
				.getElementById(payengine3CalculatorIds.calculationMonth).value;
	}
	PayEngine.getRatepayInstallmentCalculation(calculationRequest,
			payengine3CalculationCallback);
}

function payengine3CalculateRateInstallment(ev) {
	if(ev && typeof ev.preventDefault){
		ev.preventDefault();
	}
	payengine3DisableRateButtons();
	document.getElementById(payengine3CalculatorIds.calculationRate).value = document.getElementById(payengine3CalculatorIds.rateInput).value;
	payengine3SetValidationError(false);
	calculationType = payengine3CalculationTypes.calculationRate;
	payengine3CalculateInstallment();
}

function payengine3CalculateDurationInstallment(ev) {
	if(ev && typeof ev.preventDefault){
		ev.preventDefault();
	}
	payengine3DisableRateButtons();
	if(ev && typeof ev.srcElement) {
		ev.srcElement.setAttribute('class', ev.srcElement.getAttribute('class')
				+ ' cwbs-active');
		document.getElementById(payengine3CalculatorIds.calculationMonth)
		.setAttribute('value', ev.srcElement.getAttribute('data-bind'));
	}
	payengine3SetGeneralErrorDisplay(false);
	calculationType = payengine3CalculationTypes.calculationTime;
	payengine3CalculateInstallment();
	return false;
}

function payengine3DisableRateButtons() {
	var elements = document.getElementsByClassName('rp-btn-runtime');
	for (var i = 0; i < elements.length; i++) {
		var e = elements[i];
		e.setAttribute('class', e.getAttribute('class').replace(' cwbs-active',
				''));
	}
}

function payengine3ShowCalculationResult(result) {
	document.getElementById(payengine3CalculatorIds.calculationResult)
			.setAttribute('style', 'display: block');
	calculationResult = result;

	document.getElementById(payengine3CalculatorIds.totalAmount).innerText = payengine3FormatMoney(result.totalAmount)
			+ payengine3Options.calculatorCurrency;
	document.getElementById(payengine3CalculatorIds.amount).innerText = payengine3FormatMoney(result.amount)
			+ payengine3Options.calculatorCurrency;
	document.getElementById(payengine3CalculatorIds.interestAmount).innerText = payengine3FormatMoney(result.interestAmount)
			+ payengine3Options.calculatorCurrency;
	document.getElementById(payengine3CalculatorIds.serviceCharge).innerText = payengine3FormatMoney(result.serviceCharge)
			+ payengine3Options.calculatorCurrency;
	document.getElementById(payengine3CalculatorIds.monthlyDebitInterest).innerText = payengine3FormatPercentage(result.monthlyDebitInterest)
			+ payengine3Options.calculatorCurrency;

	var monthElement = document
			.getElementById(payengine3CalculatorIds.monthText);
	monthElement.innerText = monthElement.getAttribute('data-original-text')
			.replace('{n-1}', result.numberOfRates - 1);
	var lastElement = document
			.getElementById(payengine3CalculatorIds.lastRateText);
	lastElement.innerText = lastElement.getAttribute('data-original-text')
			.replace('{n}', result.numberOfRates);
	var allMonthElement = document
			.getElementById(payengine3CalculatorIds.allMonthText);
	allMonthElement.innerText = allMonthElement.getAttribute(
			'data-original-text').replace('{n}', result.numberOfRates);

	document.getElementById(payengine3CalculatorIds.rate).innerText = payengine3FormatMoney(result.rate)
			+ payengine3Options.calculatorCurrency;
	document.getElementById(payengine3CalculatorIds.lastRate).innerText = payengine3FormatMoney(result.lastRate)
			+ payengine3Options.calculatorCurrency;
	document.getElementById(payengine3CalculatorIds.allRate).innerText = payengine3FormatMoney(result.rate)
			+ payengine3Options.calculatorCurrency;

	payengine3PrepareRateResult(result);
	payengine3EnablePaymentSelection();
}

function payengine3ConfigCallback(error, result) {
	payengine3HideLoading();
	payengine3EnsurePaymentToggleable();
	if (!result) {
		payengine3SetGeneralErrorDisplay(true);
		return;
	}
	payengine3SetGeneralErrorDisplay(false);
	____module___AddRateRange(result.monthNumberMin, result.monthNumberMax);
	____module___SetRateMinimum(result.rateMinNormal);
}

function payengine3InitInstallmentCalculator(publishableKey, options) {
	payengine3Options = options;
	PayEngine.setPublishableKey(publishableKey);
	PayEngine.getRatepayInstallmentConfig(payengine3ConfigCallback);
}

function payengine3EnablePaymentSelection() {
	document.getElementById('payengine3-payment-selection').setAttribute('style', 'display:block');
}

function payengine3PrepareRateResult(result) {
	document.getElementById(payengine3Options.rateResultId).value = btoa(JSON.stringify(result));
}

function ____module___SetRateMinimum(minimum) {
	document.getElementById(payengine3CalculatorIds.calculationRate).setAttribute('min', minimum);
}

function ____module___AddRateRange(startRate, endRate) {
	document.getElementById(payengine3CalculatorIds.calculationMonth)
			.setAttribute('min', startRate);
	document.getElementById(payengine3CalculatorIds.calculationMonth)
			.setAttribute('max', endRate);
	var months = document.getElementById('rate-months');
	while (months.firstChild) {
		months.removeChild(months.firstChild);
	}
	var rates = [ 3, 6, 12, 24, 36, 48 ];
	for (var i = 0; i < rates.length; i++) {
		if (rates[i] < startRate || rates[i] > endRate) {
			break;
		}
		____module___AddRateMonthField(rates[i]);
	}
}

function ____module___AddRateMonthField(rate) {
	var element = document.createElement('div');
	element.setAttribute('class', 'cwbs-btn-group cwbs-btn-group-sm');
	element.setAttribute('role', 'group');
	var button = document.createElement('button');
	button.setAttribute('class', 'cwbs-btn cwbs-btn-default rp-btn-runtime');
	button.setAttribute('role', 'button');
	button.setAttribute('data-bind', rate);
	button.innerText = rate;
	button.addEventListener('click',
					payengine3CalculateDurationInstallment);
	element.appendChild(button);
	document.getElementById('rate-months').appendChild(element);
}

function payengine3ShowCalculation() {
	payengine3SetDisplayOnClass('rp-installment-plan-details', payengine3StyleValues.tableRow);
	payengine3SetDisplayOnClass('rp-installment-plan-no-details', payengine3StyleValues.none);
	document.getElementById('rp-show-installment-plan-details').setAttribute(
			'style', 'display: none');
	document.getElementById('rp-hide-installment-plan-details').setAttribute(
			'style', 'display: block');
}

function payengine3HideCalculation() {
	payengine3SetDisplayOnClass('rp-installment-plan-details', payengine3StyleValues.none);
	payengine3SetDisplayOnClass('rp-installment-plan-no-details', payengine3StyleValues.tableRow);
	document.getElementById('rp-show-installment-plan-details').setAttribute(
			'style', 'display: block');
	document.getElementById('rp-hide-installment-plan-details').setAttribute(
			'style', 'display: none');
}

function payengine3ToggleInstallmentPaymentType() {
	var type = 'invoice';
	if(document.getElementById('installment_type_debit').checked) {
		type = 'debit';
		payengine3EnableDebitForm();
		payengine3SetIban();
		payengine3SetMandate();
	}
	else {
		payengine3DisableDebitForm();
		document.getElementById(payengine3Options.ibanId).value = 'invoice_selected';
		document.getElementById(payengine3Options.mandateId).value = 'invoice_selected';
	}
	document.getElementById(payengine3Options.rateTypeId).value = type;
}

function payengine3EnsurePaymentToggleable() {
	var choices = document.getElementsByName('installment_type_tmp');
	if(choices.length == 0) {
		document.getElementById('installment_type_debit').setAttribute('name', 'installment_type_tmp');
		document.getElementById('installment_type_invoice').setAttribute('name', 'installment_type_tmp');
	}
}

function payengine3EnableDebitForm() {
	document.getElementById('payengine3-debit-form').setAttribute('style', 'display: block;');
	document.getElementById('payengine3-iban-temp').setAttribute('required', 'required');
	document.getElementById('payengine3-mandate-temp').setAttribute('required', 'required');
}

function payengine3DisableDebitForm() {
	document.getElementById('payengine3-debit-form').setAttribute('style', 'display: none;');
	document.getElementById('payengine3-iban-temp').removeAttribute('required');
	document.getElementById('payengine3-mandate-temp').removeAttribute('required');
}

function payengine3SetDisplayOnClass(cssClass, display) {
	var elements = document.getElementsByClassName(cssClass);
	for (var i = 0; i < elements.length; i++) {
		elements[i].setAttribute('style', 'display: ' + display);
	}
}
function payengine3SetIban(ev) {
	document.getElementById(payengine3Options.ibanId).value = document.getElementById('payengine3-iban-temp').value;
}
function payengine3SetBic(ev) {
	document.getElementById(payengine3Options.bicId).value = document.getElementById('payengine3-bic-temp').value;
}
function payengine3SetMandate(ev) {
	document.getElementById(payengine3Options.mandateId).value = document.getElementById('payengine3-mandate-temp').checked ? 'true' : 'false';
}