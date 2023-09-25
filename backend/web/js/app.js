/**
 * Variables used for comparing modified data in forms for button 'Back'
 */
let oldEncodedForm;
let newEncodedForm;

/**
 * Get the initial form data
 * @param msg
 * @param formId
 */
function reloadFunction(msg, formId) {
    let oldData = $('#' + formId).serializeArray();
    if (oldData.length === 0) {
        oldData = ['reload'];
    }

    $('#reload_button').attr('onclick', "confirmCancelModifications('" + msg + "', " + JSON.stringify(oldData) + ", '" + formId + "')");
}

/**
 * @description The function refresh all data in the form, with an alert message if the form's data has been changed,
 * and without alert message if no data change has been made
 * @param msg
 * @param oldData
 * @param formID
 */
function confirmCancelModifications(msg, oldData, formID) {
    let differentData = false;
    let newData = $('#' + formID).serializeArray();
    let excludeControlTypes = ['button', 'submit', 'hidden'];

    for (let i = 0; i <= newData.length; i++) {
        if (oldData[i] === undefined && newData[i] === undefined) {
            continue;
        }
        if (
            (oldData[i] === undefined && newData[i] !== undefined)
            || (oldData[i] !== undefined && newData[i] === undefined)
        ) {
            differentData = true;
            continue;
        }
        if (oldData[i]['value'] !== newData[i]['value']) {
            differentData = true;
        }
    }

    if (!differentData) {
        document.location.reload();
    } else {
        bootbox.confirm({
            message: msg,
            buttons: {
                confirm: {
                    label: 'Da',
                    className: 'btn-primary'
                },
                cancel: {
                    label: 'Nu'
                }
            },
            callback: function (result) {
                if (result) {
                    $('#' + formID + ' :input').each(function () {
                        if (
                            !excludeControlTypes.includes($(this).attr('type'))
                            && !$(this).prop('disabled')
                        ) {
                            $(this).val('').trigger('change').trigger('depdrop:change');
                        }
                    });
                    $('.alert').remove();
                }
            }
        });
    }
}

/**
 * First we escape the string using encodeURIComponent to get the UTF-8 encoding of the characters,
 * then we convert the percent encodings into raw bytes, and finally feed it to btoa() function.
 * @param str
 * @returns {string}
 */
function base64EncodeUnicode(str) {
    let utf8Bytes = encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function (match, p1) {
        return String.fromCharCode('0x' + p1);
    });

    return btoa(utf8Bytes);
}

/**
 * Get the initial form data in base64 encoding
 * @param msg
 * @param formId
 * @param url
 *
 * @param initAfter
 */
function backFunction(msg, formId, url, initAfter = 800) {
    setTimeout(function () {
        let oldData = $('#' + formId).serializeArray();
        oldEncodedForm = base64EncodeUnicode(JSON.stringify(oldData));
    }, initAfter);

    $('#back_button').attr('onclick', "goBack('" + msg + "', '" + formId + "', '" + url + "')");
}

/**
 * Comparing the initial data of form with the modified one using base64 encoding
 * @param msg
 * @param formID
 * @param url
 */
function goBack(msg, formID, url) {
    let newData = $('#' + formID).serializeArray();

    newEncodedForm = base64EncodeUnicode(JSON.stringify(newData));

    if (newEncodedForm === oldEncodedForm) {
        if (url !== '' || url === undefined) {
            location.href = url;
        } else {
            window.history.back();
        }
    } else {
        bootbox.confirm({
            message: msg,
            buttons: {
                confirm: {
                    label: 'Da',
                    className: 'btn-primary'
                },
                cancel: {
                    label: 'Nu'
                }
            },
            callback: function (result) {
                if (result) {
                    if (url !== '' || url === undefined) {
                        location.href = url;
                    } else {
                        window.history.back();
                    }
                }
            }
        });
    }
}

/**
 * Used to send delete action after bootbox confirm.
 * Will use this method on all delete/activate actions
 * Before displaying the confirmation modal the action btn`s are disabled
 * to prevent users to accidentally click again.
 * If user click's 'No' the action btn`s are enabled back.
 * If clicks 'Yes' the page loading overlay will be displayed,  the page reload will remove the disabled state & overlay
 * To be able to disable btn`s their parent container should have css class 'item_action_btns_container'
 * @param msg
 * @param url
 * Added the functionality to disable btn`s and open the page loading overlay
 */
function deleteActivateRecord(msg, url) {
    $('.item_action_btns_container a, .item_action_btns_container button').addClass('disabled');
    bootbox.confirm({
        message: msg,
        buttons: {
            confirm: {
                label: 'Da',
                className: 'btn-danger'
            },
            cancel: {
                label: 'Nu'
            }
        },
        callback: function (result) {
            if (result) {
                $('#app-preloader').fadeToggle(100, function () {
                    $.post(url);
                });
            } else {
                $('.item_action_btns_container a, .item_action_btns_container button').removeClass('disabled');
            }
        }
    });
}

/**
 * Resets GridView::widget top filters to "All" or first element in the select list
 * @param resetElements list of elements id to reset separated by ","
 */
function resetFilters(resetElements) {
    var resetList = resetElements.split(',');
    $.each(resetList, function (index, el) {
        $('#' + el + ' option:eq(0)').prop('selected', true);
    });
}

/**
 * reset filters on search bar (form)
 * change url parameters accordingly
 * and submit form
 * @param resetElements list of ids' elements to reset, separated by ","
 * @param formId
 */
function resetFiltersAndSubmit(resetElements, formId) {
    var resetList = resetElements.split(',');
    $.each(resetList, function (index, el) {
        $('#' + el + ' option:eq(0)').prop('selected', true);
    });
    $(formId).submit();
}

function dynamicFilter(formID) {
    document.getElementById(formID).submit();
}

function changeActivityAssignedPhase(checkedPhase, page) {
    let parentTr = $(checkedPhase).parents('tr');
    let checkBoxId = checkedPhase.substring(1);
    let checkBoxValue = document.getElementById(checkBoxId).checked;

    if (checkedPhase.includes('completeness')) {
        if (!checkBoxValue) {
            parentTr.find('.compl_phase_select_container').removeClass('bg-success');
        } else {
            parentTr.find('.compl_phase_select_container').addClass('bg-success');
        }
    } else if (checkedPhase.includes('correctness')) {
        if (!checkBoxValue) {
            parentTr.find('.corec_phase_select_container').removeClass('bg-success');
        } else {
            parentTr.find('.corec_phase_select_container').addClass('bg-success');
        }
    } else if (
        checkedPhase.includes('quantity_list_dtac')
        || checkedPhase.includes('quantity_list_ptde')
    ) {
        if (!checkBoxValue) {
            parentTr.find('.ql_phase_select_container').removeClass('bg-success');
        } else {
            parentTr.find('.ql_phase_select_container').addClass('bg-success');
        }
    } else {
        let parentsTd = parentTr.find('.phase_select_container');
        parentsTd.each(function () {
            let input = $(this).find("input[type='checkbox']");
            if (`#${input.attr('id')}` === checkedPhase && checkBoxValue) {
                $(this).addClass('bg-success');
            } else {
                input.prop('checked', false);
                $(this).removeClass('bg-success');
            }
        });
    }

    if (page !== 'index') {
        changeActivityTime();
    }
}

function searchFocus() {
    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });
    $(document).on('select2:close', () => {
        if (document.querySelector('.select2-search__field')) {
            document.querySelector('.select2-search__field').focus();
        }
    });
}

function selectSearchBar(inputSizeClass = 'input-sm') {
    $(".different_search_method").select2({
        matcher: selectSearchMatchData,
    });

    $('.select2-container').width('auto')
        .removeClass('select2-container--default')
        .addClass('select2-container--krajee-bs4')
        .addClass(inputSizeClass);
}

function selectSearchMatchData(params, data) {
    if ($.trim(params.term) === '') {
        return data;
    }

    if (typeof data.text === 'undefined') {
        return null;
    }

    let searchedText = params['term'].toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, '');
    let searchedTextWordsArray = [];
    let startValue = 0;
    let numberOfWords = 1;
    for (let i = 0; i <= searchedText.length; i++) {
        searchedTextWordsArray[numberOfWords] = searchedText.substr(startValue, i - startValue);
        if (searchedText[i] === ' ') {
            startValue = i + 1;
            numberOfWords += 1;
        }
    }

    let numberOfMatchedWords = 0;
    let numberOfEmptyWords = 0;
    for (let i = 0; i <= searchedTextWordsArray.length; i++) {
        if (searchedTextWordsArray[i] === '') {
            numberOfEmptyWords += 1;
        } else {
            if (data['text'].toLowerCase().includes(searchedTextWordsArray[i])) {
                numberOfMatchedWords += 1;
            }
            if (numberOfMatchedWords === searchedTextWordsArray.length - 1 - numberOfEmptyWords) {
                return $.extend({}, data, true);
            }
        }
    }

    return null;
}


/**
 * The function is used to calculate the total price for all price history entities
 * based on the values of material and labour prices
 */
function calculateTotalPriceFieldValue() {
    let materialPriceVal = $('#material_price').val();
    if (materialPriceVal === '') {
        materialPriceVal = 0;
    }

    let labourPriceVal = $('#labour_price').val();
    if (labourPriceVal === '') {
        labourPriceVal = 0;
    }

    let totalPriceVal = formatNumber4DecWithThousands(parseFloat(materialPriceVal) + parseFloat(labourPriceVal));
    $('#total_price').val(totalPriceVal);
}

/**
 * prevent users to insert in selected inputs values like "e", "+" & "-"
 * this method is used on various places like build/centralizer-article, build/centralizer-equipment, etc.
 * evt.keyCode 8 // keycode for backspace
 * evt.keyCode 46 // keycode for delete
 */
function preventInvalidInputsInsert(elList, maxValue) {
    $(elList).on('keydown', function (evt) {
        if (evt.key === 'e') {
            evt.preventDefault();
        }
        if (evt.key === '+') {
            evt.preventDefault();
        }
        if (evt.key === '-') {
            evt.preventDefault();
        }
        if ($(this).val() > maxValue && evt.keyCode !== 46 && evt.keyCode !== 8) {
            evt.preventDefault();
            $(this).val(maxValue);
        }
    })
}

/**
 * prevent users to insert in selected inputs values like "e", "+" & "-"
 * make user to insert correct values for indexes inputs
 * evt.keyCode 8 // keycode for backspace
 * evt.keyCode 9 // keycode for TAB
 * evt.keyCode 13 // keycode for enter
 * evt.keyCode 46 // keycode for delete
 * evt.keyCode 37 // keycode for left arrow
 * evt.keyCode 39 // keycode for right arrow
 */
function preventInvalidIndexInsert(elList) {
    $(elList).on('keydown', function (evt) {
        let permittedKeyCodesList = [8, 9, 13, 46, 37, 39];
        let isTextSelected = window.getSelection();

        if (evt.key === '+') {
            evt.preventDefault();
        }
        if (evt.key === '-') {
            evt.preventDefault();
        }
        if (
            !evt.key.match(/^[\d+\.\/]+$/)
            && $.inArray(evt.keyCode, permittedKeyCodesList) === -1
        ) {
            evt.preventDefault();
        }
        if (
            window.getSelection
            && isTextSelected.type === 'Caret'
            && $(this).val().length > 5
            && $.inArray(evt.keyCode, permittedKeyCodesList) === -1
        ) {
            evt.preventDefault();
        }
        if (
            $(this).val().length === 1
            && $.inArray(evt.keyCode, permittedKeyCodesList) === -1
            && evt.key !== '.'
        ) {
            evt.preventDefault();
        }
        if (
            $(this).val().length === 0
            && $.inArray(evt.keyCode, permittedKeyCodesList) === -1
            && evt.key === '.'
        ) {
            evt.preventDefault();
        }
        if (evt.key === '.' && $(this).val().indexOf('.') !== -1) {
            evt.preventDefault();
        }
    });
}

/**
 * enables back the btn's that have been disabled to prevent accidentally duble clicks
 * @param el
 */
function enableDisabledEl(el) {
    $(el).on('afterValidate', function (event, messages, errorAttributes) {
        if (errorAttributes.length === 0) {
            $('.prevent_multi_click').removeClass('disabled');
        }
    });
}

/**
 * `#app-preloader` is used every time a page events `beforeunload` and `laod` is triggered to display an overlay over the entire page.
 * This implementation will try to prevent user to click other menus until the previous is not ready,
 * and also to have a feedback from app because not all users has the knowledge to look on the browser tab.
 */
window.addEventListener('beforeunload', function (e) {
    if (document.activeElement.classList.contains('no_app_preloader')) {
        return false;
    }

    showAppPreloader();
});

window.addEventListener('load', function (e) {
    hideAppPreloader();
    /**
     * prevent users to insert in selected inputs values like "e", "+" & "-"
     */
    $('#labour_price, #material_price').on('keydown', function (evt) {
        if (evt.key === 'e') {
            evt.preventDefault();
        }
        if (evt.key === '+') {
            evt.preventDefault();
        }
        if (evt.key === '-') {
            evt.preventDefault();
        }
    });
    //disable all elements that have 'disable_on_click' class
    $('.prevent_multi_click').on('click', function () {
        $(this).addClass('disabled');
    })
});

/**
 * modify activate button in save button if the user change an input from form
 */
$('form').on('change', function viewButtonSaveOrActivate() {
    if ($('#button_activate').length !== 0) {
        $('#button_activate').addClass('display-buttons-save-activate');
    }
});

function to_upper() {
    $('.to_upper').keyup(function () {
        this.value = this.value.toUpperCase();
    })
}

//accept only text
function acceptOnlyText() {
    $('.accept_only_text').on('keypress', function (evt) {
        var pattern = /[A-Za-z-\s]/
        if (!evt.key.match(pattern)) {
            evt.preventDefault();
        }
    })
}

//phone number validation
function acceptOnlyNumbers() {
    $('.accept_only_numbers').on('keypress', function (evt) {
        var pattern = /[0-9+?]/
        if (!evt.key.match(pattern)) {
            evt.preventDefault();
        }
    })
}

function getRndInteger(min, max) {
    return Math.floor(Math.random() * (max - min)) + min;
}

function getAppPreloaderImage() {
    return '/images/loadings/' + getRndInteger(1, 28) + '.gif';
}

function getAppPreloaderLottie() {
    let specialEventsImg = getAppPreloaderLottieSpecialEvents();
    if (specialEventsImg !== '') {
        return specialEventsImg;
    }

    let seasonImg = getAppPreloaderLottieSeason();
    if (seasonImg !== '') {
        return seasonImg;
    }

    return '';
}

function getAppPreloaderLottieSpecialEvents() {
    let date = moment().format('DD-MM');
    let eightMarch = '08-03';
    let childrenDay = '01-06';
    let easter = {start: new Date('2023-04-13'), end: new Date('2023-04-19')};

    if (date === eightMarch) {
        return '/lottie-reloads/08-march/' + getRndInteger(1, 7) + '.json';
    }

    if (
        new Date() >= easter.start
        && new Date() <= easter.end
    ) {
        return '/lottie-reloads/easter/' + getRndInteger(1, 7) + '.json';
    }

    if (date === childrenDay) {
        return '/lottie-reloads/01-june/' + getRndInteger(1, 26) + '.json';
    }

    return '';
}

function getAppPreloaderLottieSeason() {
    let mount = parseInt(moment().format('MM'));

    if ([3, 4, 5].includes(mount)) {
        return '/lottie-reloads/spring/' + getRndInteger(1, 40) + '.json';
    }

    if ([6, 7, 8].includes(mount)) {
        return '/lottie-reloads/summer/' + getRndInteger(1, 38) + '.json';
    }

    if ([9, 10, 11].includes(mount)) {
        return '/lottie-reloads/autumn/' + getRndInteger(1, 37) + '.json';
    }

    if ([12, 1, 2].includes(mount)) {
        return '/lottie-reloads/winter/' + getRndInteger(1, 14) + '.json';
    }

    return '';
}

function showAppPreloader() {
    let appPreloaderLottie = getAppPreloaderLottie();
    if (appPreloaderLottie !== '') {
        document.cookie = "app-preloader-img-src=" + appPreloaderLottie + "; max-age=" + 30 * 24 * 60 * 60;
    } else {
        document.cookie = "app-preloader-img-src=" + getAppPreloaderImage() + "; max-age=" + 30 * 24 * 60 * 60;
    }

    $('#app-preloader').fadeIn(100);
}

function hideAppPreloader() {
    $('#app-preloader').fadeOut(500);
}


/**
 * Calculate the quantity where input field contains math operations like "+", "-", "*" and "/"
 * Set the result of input field
 */
function calculateQuantity(selectedInput) {
    let mathOperations = ['+', '-', '*', '/'];
    let calculatedValue = 0;
    let selectedInputValue = selectedInput.val();
    let count = 0;
    let valuesToBeCalculated = [];
    let recalculatedNumbers = [];
    let recalculatedNumbersCounter = 0;
    valuesToBeCalculated[count] = '';

    for (let j = 0; j < selectedInputValue.length; j++) {
        if ($.inArray(selectedInputValue[j], mathOperations) === -1) {
            valuesToBeCalculated[count] += selectedInputValue[j];
        } else {
            count += 1;
            valuesToBeCalculated[count] = '';
            if ($.inArray(selectedInputValue[j], mathOperations) !== -1) {
                valuesToBeCalculated[count] += selectedInputValue[j];
            }
        }
    }

    for (let j = 0; j < valuesToBeCalculated.length; j++) {
        if (valuesToBeCalculated[j].slice(0, 1) === '*') {
            valuesToBeCalculated[j] = parseFloat(valuesToBeCalculated[j].slice(1, valuesToBeCalculated[j].length)) * parseFloat(valuesToBeCalculated[j - 1]);
            recalculatedNumbers[recalculatedNumbersCounter - 1] = valuesToBeCalculated[j].toString();
        } else if (valuesToBeCalculated[j].slice(0, 1) === '/') {
            valuesToBeCalculated[j] = parseFloat(valuesToBeCalculated[j - 1]) / parseFloat(valuesToBeCalculated[j].slice(1, valuesToBeCalculated[j].length));
            recalculatedNumbers[recalculatedNumbersCounter - 1] = valuesToBeCalculated[j].toString();
        } else {
            recalculatedNumbers[recalculatedNumbersCounter] = valuesToBeCalculated[j];
            recalculatedNumbersCounter += 1;
        }
    }

    for (let j = 0; j < recalculatedNumbers.length; j++) {
        if (recalculatedNumbers[j] !== '') {
            if (recalculatedNumbers[j].slice(0, 1) === '*') {
                calculatedValue *= parseFloat(recalculatedNumbers[j].slice(1, recalculatedNumbers[j].length));
            } else if (recalculatedNumbers[j].slice(0, 1) === '/') {
                calculatedValue /= parseFloat(recalculatedNumbers[j].slice(1, recalculatedNumbers[j].length));
            } else {
                calculatedValue += parseFloat(recalculatedNumbers[j]);
            }
        }
    }
    if (parseInt(calculatedValue) < 0) {
        calculatedValue = 0;
    }

    selectedInput.val(parseFloat(calculatedValue).toFixed(2));
}

/**
 * Calculate the basic quantity for quantity list forms
 * Also color ptde quantity with red if necessary quantity is greater than ptde quantity
 */
function setBasicQuantity(selectedInput, ptdeQuantityErrorMsg) {
    let inputChangedFieldID;
    let basicQuantity = 0.00;
    if (selectedInput.attr('id').indexOf('ptde_quantity') !== -1) {
        inputChangedFieldID = selectedInput.attr('id').replace('ptde_quantity-', '');
    } else {
        inputChangedFieldID = selectedInput.attr('id').replace('necessary_quantity-', '');
    }

    let ptdeValue = parseFloat($('#ptde_quantity-' + inputChangedFieldID).val());
    if (isNaN(ptdeValue) || parseInt(ptdeValue) < 0) {
        ptdeValue = 0;
    }

    let necessaryValue = parseFloat($('#necessary_quantity-' + inputChangedFieldID).val());
    if (isNaN(necessaryValue) || parseInt(necessaryValue) < 0) {
        necessaryValue = 0;
    }

    if (ptdeValue >= necessaryValue) {
        basicQuantity = ptdeValue - necessaryValue;
        $('#ptde_quantity-' + inputChangedFieldID).removeClass('bg-danger');
        $('#ptde_quantity-' + inputChangedFieldID).removeAttr('title');
    } else {
        $('#ptde_quantity-' + inputChangedFieldID).addClass('bg-danger');
        $('#ptde_quantity-' + inputChangedFieldID).attr('title', ptdeQuantityErrorMsg);
    }

    $('#basic_quantity-' + inputChangedFieldID).val(parseFloat(basicQuantity).toFixed(2));
}

/**
 * Insert into code field the code according to inserted name
 */
function insertCodeIntoField() {
    $('.generate_code').on("keyup", function () {
        let nameVal = $(this).val();
        let codeVal = nameVal.toLowerCase().replaceAll(' ', "_");

        $('.insert_code').val(codeVal);
    })
}

/**
 * Function is used to select all text/value inside an input when you click on it
 * To have access to that functionality we just need to appeal it with php->register whenever we want to
 */
function selectTextOnClick() {
    $("input").on("click", function () {
        $(this).select();
    });
}

/**
 * prevent users to insert in selected inputs all values but a number, ".", "+", "-", "*" and "/"
 * this method will be used in article-quantity and equipment-quantity forms
 * evt.keyCode 8 // keycode for backspace
 * evt.keyCode 9 // keycode for TAB
 * evt.keyCode 13 // keycode for enter
 * evt.keyCode 46 // keycode for delete
 */
function preventInsertingInvalidValues(elClass) {
    let permittedKeyCodesList = [8, 9, 13, 46];
    $('.' + elClass).on('keydown', function (evt) {
        if (
            !evt.key.match(/^[\d+\.\+\-\*\/]+$/)
            && $.inArray(evt.keyCode, permittedKeyCodesList) === -1
        ) {
            evt.preventDefault();
        }
    });
}


/**
 * this function prevents the input from being set to zero to 2 decimal places (example if 0.01 is entered the error is not called,
 * if 0.001 is entered the error is called and 1 is set automatically)
 * this function has 2 parameters, error message and an optional parameter, to call another data saving function in certain specific situations
 *
 * @param errorMessage
 * @param centralizerId
 * Get rid of 'event' and use 'this' instead
 */
function preventInputZero(errorMessage, centralizerId = undefined) {
    $(".prevent-input-zero").on('change', function () {
        if (parseFloat($(this).val()).toFixed(4) === '0.0000') {
            $(this).val('1.0000');
            bootbox.alert({
                title: 'Error',
                message: errorMessage,
                className: 'error-message'
            }).find('.modal-body');
        }
        if (centralizerId !== undefined) {
            saveIndex($(this).attr('id'), centralizerId);
        }
    })
}

/**
 * The function is used just for select2, to remove the error if we change the value of dropDown.
 * In form whenever we use DepDrops dropDowns we'll set the ''validateOnChange' => false',
 * to assure no errors are validated before the submit button is pressed
 * @todo To find a better solution, maybe some validation on form submit/change... using yii2
 */
window.addEventListener('load', function () {
    $(`.required`).on('select2:select', function () {
        if ($(this).hasClass('has-error')) {
            $(this).removeClass('has-error');
        }
    })
})

/**
 * Replace apostrophe in input field inserted by user
 */
function replaceApostrophe() {
    $('.replace-apostrophe').on("keyup", function () {
        let nameVal = $(this).val();
        let replaceVal = nameVal.replaceAll("'", "`");
        $(this).val(replaceVal);
    })
}

/**
 * This function is used to filter the grid view by deleted column, adding/replacing in url needed parameter(FeatureLevelSearch[deleted])
 * @param deletedParamUrl - string that represents the url deleted parameter, the grid view will be filtered by
 */
function viewToggleChange(deletedParamUrl) {
    let deletedEntities = 1;
    $('#switch_view_toggle_id').on("change", function () {
        if ($(this).is(":checked")) {
            deletedEntities = 0;
        }

        $('#toggle-status').val(deletedEntities);
    })
}

/**
 * This function is used to set the text label (Active/Deleted) whenever we switch the toggle
 * @param showActiveEntities - represents a boolean parameter. true/1 - shows the active entities(text=Active),
 * false/0 - show deleted entities(text=Deleted)
 *
 * @param viewToggle
 * @param columnPosition
 */
function activeDeletedLabel(showActiveEntities, viewToggle, columnPosition = 1) {
    let label = '<label class="m-0">Șterse</label>';
    let labelPosition = '';
    let togglePosition = '';
    let colPosition = parseInt(columnPosition) - 1

    if (!showActiveEntities) {
        label = '<label class="m-0">Acțiuni</label>';
    }

    labelPosition = 'th:eq(' + colPosition + ')';
    togglePosition = 'td:eq(' + colPosition + ')';

    $('#w0-headers').children(labelPosition).addClass('text-center').html(label);
    $('#w0-filters').children(togglePosition).addClass('text-center').html(viewToggle);
}

/**
 * this function format the number with a comma in thousands and a dot in decimals
 */
function formatNumberWithThousands(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatNumberDecimalWithThousands(number) {
    return number.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ',');
}

function formatNumber2DecWithThousands(number) {
    return formatNumberDecWithThousands(number, 2);
}

function formatNumber4DecWithThousands(number) {
    return formatNumberDecWithThousands(number, 4);
}

function formatNumberDecWithThousands(number, digits) {
    return formatNumberDecimalWithThousands(parseFloat(number).toFixed(digits));
}

function formatNumber2DecWithoutThousands(number) {
    return formatNumberDecWithoutThousands(number, 2);
}

function formatNumber4DecWithoutThousands(number) {
    return formatNumberDecWithoutThousands(number, 4);
}

function formatNumberDecWithoutThousands(number, digits) {
    return parseFloat(formatNumberWithoutThousands(number)).toFixed(digits);
}

function formatNumberWithoutThousands(number) {
    return number.toString().replaceAll(',', '');
}

function roundTo2Decimal(number) {
    return roundToDecimal(number, 2);
}

function roundTo4Decimal(number) {
    return roundToDecimal(number, 4);
}

function roundToDecimal(number, digits) {
    let digitsFactor = Math.pow(10, digits);

    number = number * digitsFactor;
    if (
        parseInt(number) > 0
        && parseFloat((number - parseInt(number)).toFixed(digits)) > 0
    ) {
        let dif = parseFloat((number - parseInt(number)).toFixed(digits));
        if (dif >= 0.5) {
            number = Math.ceil(number);
        } else {
            number = Math.floor(number);
        }
    }

    return number / digitsFactor;
}

/**
 * This function set page size in GridView
 */
function setPageSize(pageSize) {
    $('.page-size').on('change', function () {
        let url = new URL(window.location);
        if (url.searchParams.has(pageSize)) {
            url.searchParams.set(pageSize, $(this).val());
        } else {
            url.searchParams.append(pageSize, $(this).val());
        }
        window.location = url;
    })
}

/**
 * Show all objects for specific Estimate where article/equipment appear
 */
function infoObjectsEntities() {
    let selectedElement;

    $('.hover-pointer').on("click", function () {
        selectedElement = $(this).parent('th');
        let articleID = $(selectedElement).attr('id').replace('entity_', '');

        $('#entity_code').empty();
        $('#entity_code').append($(selectedElement).text());
        $('#objects_codes_list').empty();
        for (let i = 0; i < objects[articleID].length; i++) {
            $('#objects_codes_list').append('<div>' + objects[articleID][i] + '</div>');
        }

        setModalWindowPosition();
    })

    $('.container-table').scroll(function () {
        if (selectedElement !== undefined) {
            setModalWindowPosition();
        }
    })

    $('.close-modal-window').on("click", function () {
        closeModalWindow();
    })

    $(document).on("click", function () {
        if (!event.target.matches('.hover-pointer, .modal-window-objects-info, .modal-window-objects-info *')) {
            closeModalWindow();
        }
    })

    function setModalWindowPosition() {
        let position = selectedElement.offset();
        let leftPos = parseFloat(position.left) + parseFloat(selectedElement.outerWidth());
        let positionTopFullHeightCell = parseFloat(position.top) + parseFloat(selectedElement.outerHeight());
        let limitUpPos = parseFloat($('.name-section').offset().top) + parseFloat($('.name-section').outerHeight());
        let limitDownPos = parseFloat($('.container-table').offset().top) + parseFloat($('.container-table').outerHeight());
        let positionTopFullHeightModalWindow = parseFloat(position.top) + parseFloat($('.modal-window-objects-info').outerHeight());
        let horizontalScrollBarHeight = 17

        // Check if the cell left the table upper or downer. If so hide the modal window
        if (position.top + 1 >= limitUpPos && positionTopFullHeightCell <= limitDownPos) {
            $('.modal-window-objects-info').removeClass('d-none');
            $('.modal-window-objects-info').attr('style', 'top: ' + position.top + 'px; left: ' + leftPos + 'px;');
        } else {
            $('.modal-window-objects-info').addClass('d-none');
        }

        // Check if the modal window leave bellow the table. If so move modal window up till end of table
        if (positionTopFullHeightModalWindow >= limitDownPos) {
            let newPosition = parseFloat(limitDownPos) - parseFloat($('.modal-window-objects-info').outerHeight()) - horizontalScrollBarHeight;
            $('.modal-window-objects-info').attr('style', 'top: ' + newPosition + 'px; left: ' + leftPos + 'px;');
        }
    }

    function closeModalWindow() {
        $('.modal-window-objects-info').addClass('d-none');
        selectedElement = undefined;
    }
}

// Hide/Show columns on button click, used in al centralizers form, estimate forms...
function hideShowColumnsPersonalized() {
    $('.hide-show-columns-icon').on("click", function () {
        $(this).toggleClass('fa-chevron-circle-right fa-chevron-circle-left');
        $('._js-hide-show-columns').toggleClass('d-none');
    })
}

/**
 *
 * @param elList
 */
function preventInvalidInputsInsertTime(elList) {
    let permittedKeyCodesList = [8, 9, 13, 46, 37, 39];
    $(elList).on('keydown', function (evt) {
        if ($.inArray(evt.keyCode, permittedKeyCodesList) === -1) {
            if (evt.key === 'e') {
                evt.preventDefault();
            }
            if (evt.key === '+') {
                evt.preventDefault();
            }
            if (evt.key === '-') {
                evt.preventDefault();
            }
            if ($(this).val().length > 6) {
                evt.preventDefault();
            }
        }
    })
}

function setFlashJS(msg, type = 'danger', timeout = 5000) {
    let flashClasses = {
        error: 'alert-danger',
        danger: 'alert-danger',
        success: 'alert-success',
        info: 'alert-info',
        warning: 'alert-warning'
    };

    let flashClass = type;
    if (typeof (flashClasses[type]) !== 'undefined') {
        flashClass = flashClasses[type];
    }

    $(`.${flashClass}`).remove();
    let element = $('.container-fluid .row .col-12');
    if (element.length === 0) {
        return;
    }

    let flashElement = `<div class="alert ${flashClass} alert-dismissible fade show mb-0 mt-2" id="js-flash">
                            ${msg}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                        </div>`;

    element.prepend(flashElement);

    $('html, body').animate({scrollTop: 0}, 'slow');

    if (timeout > 0) {
        setTimeout(function () {
            $('#js-flash').alert('close');
        }, timeout);
    }
}