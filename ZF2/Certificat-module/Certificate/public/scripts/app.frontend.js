$(function () {
    // Enable datepicker
    $('.datepicker').datepicker();

    // Enable chosen select
    $('.chosen-select').chosen({
        width: '100%'
    });

    // Enable bootstrap popover
    $('[data-toggle="popover"]').popover();

    // Disable Empty Links
    $('body').on('click', '[href=#]', function (e) {
        e.preventDefault();
    });

    // Enable loading state element with data-loading-text on click
    $('body').on('click', '[data-loading-text]', function () {
        $(this).button('loading');
    });

    // Enable confirm links
    $('body').on('click', 'a.go-with-confirm', function (e) {
        e.preventDefault();
        var $obj = $(this);
        if (confirm($obj.data('confirm-text'))) {
            window.location = $obj.data('href');
        } else {
            if ($obj.data('loading-text').length) {
                $(this).button('reset');
            }
        }
    });

    // Reset and close the form by click on "Cancel" button
    $('body').on('click', 'button.form-reset', function (e) {
        e.preventDefault();
        var $form = $(this).closest('form');
        if ($form.length && $form[0].length) {
            $form[0].reset();

            // If in dropdown then close dropdown
            if ($form.closest('.dropdown.open').length) {
                $form.closest('.dropdown.open').removeClass('open');
            }
        }
    });

    //Enable jquery validate if needed
    $('form.jq-validate').each(function () {
        $(this).validate({
            ignore: ":hidden:not(select)", // Fix validate chosen-select
            ignoreTitle: true,
            errorPlacement: function (error, element) {
                var target = element;
                if (element.parent('.input-group').length) {
                    target = element.parent('.input-group');
                    error.addClass('after-input-group');
                } else if (element.parent('label').length) {
                    target = element.parent('label');
                } else if (element.hasClass('chosen-select')) {
                    element.change(function () {
                        element.closest('form').validate().element(element);
                    });
                    target = element.siblings('.chosen-container');
                }

                error.insertAfter(target);
            },
            invalidHandler: function (event, validator) {
                // Disable loading state element with data-loading-text
                $('[data-loading-text]').button('reset');
            }
        });
    });

    //Reload captcha by clicks
    $('body').on('click', '.reload-captcha', function (e) {
        e.preventDefault();
        var $form = $(this).closest('form');
        $.ajax({
            url: '/captcha-reload',
            success: function (data) {
                $('.img-captcha', $form).attr('src', data.src);
                $('.captcha-id', $form).attr('value', data.id);
            }
        });
    });

    // Page - organizations. Approve / Disapprove organization
    $('body').on('click', '.toggle-organization-approve:not(.disabled)', function (e) {
        e.preventDefault();
        var $obj = $(this);
        $obj.addClass('disabled');
        $.ajax({
            method: 'POST',
            url: $obj.data('href'),
            success: function (data) {
                if (data.success == true) {
                    $('.active', $obj).removeClass('active')
                            .siblings('span').addClass('active');
                }
            }
        }).always(function () {
            $obj.removeClass('disabled');
        });
    });

    // Page - organizations. 
    // If status = approved : on hover : show the red circle icon (refused)
    // If status = refused : on hover : show the green tick icon (approved)
    $('body').on('mouseenter', '.toggle-organization-approve:not(.disabled)', function () {
        var $obj = $(this);

        // Return active span element on mouseleave
        $obj.on('mouseleave', function () {
            $('.active', $obj).removeAttr('style')
                    .siblings('span').removeAttr('style');
        });

        $('.active', $obj).hide()
                .siblings('span').show();
    });

    // Page - competences. Not close the category form on click
    $('body').on('click', '.group-edit-box', function (e) {
        e.stopPropagation();
    });

    // Page - competences. Hide "Add competence" button and open the form
    $('body').on('click', '.add-competence', function (e) {
        e.preventDefault();
        $(this).closest('.actions').addClass('hide')
                .siblings('.panel-competence-new').removeClass('hide')
                .children('.panel-heading').trigger('click');
    });

    // Page - competences. Hide the form and show "Add competence" button 
    $('body').on('click', '.panel-competence-new button.form-reset', function (e) {
        e.preventDefault();
        var $obj = $(this).closest('.panel-collapse');

        // This event is fired when a collapse element has been hidden from the user (will wait for CSS transitions to complete).
        $obj.on('hidden.bs.collapse', function () {
            $obj.closest('.panel-competence-new').addClass('hide')
                    .siblings('.actions').removeClass('hide');
        });

        $obj.collapse('hide');
    });

    // Page - competences. Hide/show organization competences
    $('body').on('click', '#hide-organization-competences', function (e) {
        var $obj = $(this);
        if ($obj.hasClass('active')) {
            $('.custom', '#accordion').removeClass('hide');
            recalculateCounts();
            $obj.button('reset');
        } else {
            $('.custom', '#accordion').addClass('hide');
            recalculateCounts();
            $obj.button('alt');
        }
        $obj.blur();

        function recalculateCounts() {
            $('#accordion > .panel > .panel-heading > .bubble', '.competence-container').each(function () {
                var $obj = $(this),
                        $container = $(this).parent().siblings('.panel-collapse');

                $obj.text($('.panel:not(.hide)', $container).length);
            });
        }
    });

    // Page - templates. Add a new person
    $('body').on('click', '#add-person', function (e) {
        e.preventDefault();
        var $inputName = $('#add-person-name'),
                $inputFunction = $('#add-person-function'),
                $table = $('#person-table'),
                newIndex = $('tr.person-row').length,
                inputsValid, $personRow;

        // Add rules and validate
        $inputName.rules('add', 'required');
        $inputFunction.rules('add', 'required');
        inputsValid = $inputName.valid();
        inputsValid = $inputFunction.valid() && inputsValid;
        if (inputsValid) {
            $personRow = $('#empty-person', $table).clone();
            // Prepare row
            $personRow.removeAttr('id').removeClass('hide').addClass('person-row');
            $('.person-name', $personRow).text($inputName.val());
            $('.person-function', $personRow).text($inputFunction.val());
            $('input.hidden-name', $personRow).val($inputName.val())
                    .attr('name', 'persons_in_charge[' + newIndex + '][name]');
            $('input.hidden-function', $personRow).val($inputFunction.val())
                    .attr('name', 'persons_in_charge[' + newIndex + '][function]');

            // Insert in table
            $personRow.prependTo($table);

            // Clear inputs
            $inputName.val('');
            $inputFunction.val('');
        }

        // Remove validate rules
        $inputName.rules('remove', 'required');
        $inputFunction.rules('remove', 'required');
    });

    // Page - templates. Delete the person
    $('body').on('click', '.delete-person', function (e) {
        e.preventDefault();
        var $obj = $(this);
        if (confirm($obj.data('confirm-text'))) {
            $obj.closest('.person-row').remove();
        }
    });

    // Page - certificate form. Edit the participant
    $('body').on('click', '#certificate-participant-table .edit-participant', function (e) {
        e.preventDefault();
        var $row = $(this).closest('.participant-row');

        // Hide text row
        $('.participant-current-value', $row).addClass('hide');
        $('.default-actions', $row).addClass('hide');

        // Show row with inputs
        $('.participant-edit-value', $row).removeClass('hide');
        $('.edit-actions', $row).removeClass('hide');
    });

    // Page - certificate form. Close edit the participant with save or cancel
    $('body').on('click', '#certificate-participant-table .close-edit-participant', function (e) {
        e.preventDefault();
        var $obj = $(this), inputsValid = true,
                $row = $(this).closest('.participant-row');

        if ($obj.hasClass('cancel')) {
            // Get and insert into inputs original text
            $('input', $row).each(function () {
                $(this).val($(this).closest('td').children('.participant-current-value').text());
            });
        } else if ($obj.hasClass('save')) {
            $('input', $row).each(function () {
                inputsValid = $(this).valid() && inputsValid;
            });

            if (!inputsValid) {
                return;
            }

            // Get and show entered text
            $('.participant-current-value', $row).each(function () {
                $(this).text($(this).closest('td').find('input').val());
            });
        }

        // Hide row with inputs
        $('.participant-edit-value', $row).addClass('hide');
        $('.edit-actions', $row).addClass('hide');

        // Show text row
        $('.participant-current-value', $row).removeClass('hide');
        $('.default-actions', $row).removeClass('hide');
    });

    // Page - certificate form. Delete the participant from table
    $('body').on('click', '#certificate-participant-table .delete-participant', function (e) {
        e.preventDefault();
        var $obj = $(this);
        if (confirm($obj.data('confirm-text'))) {
            $obj.closest('.participant-row').remove();
        }
    });

    // Page - certificate form. Add new participant to table
    $('body').on('click', '#certificate-participant-table #add-participant', function (e) {
        e.preventDefault();
        var $obj = $(this),
                $row = $obj.closest('#new-participant-row'),
                participant = {}, inputsValid = true;

        // Add rules and validate
        $('input:not(.datepicker)', $row).each(function () {
            $(this).rules('add', 'required');
            inputsValid = $(this).valid() && inputsValid;
        });

        if (inputsValid) {
            $('input', $row).each(function () {
                participant[$(this).data('target-field')] = $(this).val();

                // Clear the input
                $(this).val('');
            });

            addParticipantToTable(participant);
        }

        $('input', $row).each(function () {
            // Remove validation rules
            $(this).rules('remove', 'required');
        });
    });

    // Page - certificate form. Enable popover for add participant comment
    $('body').on('click', '#certificate-participant-table .add-participant-comment:not(.popovered)', function (e) {
        e.preventDefault();
        $(this).popover({
            placement: 'top',
            html: true,
            content: function () {
                return $(this).siblings(".popover-content").html();
            }
        });
        $(this).popover('show');
        $(this).addClass('popovered');
    });

    // Page - certificate form. Preview participant certificate
    $('body').on('click', '#certificate-participant-table .preview-participant-certificate', function (e) {
        e.preventDefault();
        var $obj = $(this),
                rowIndex = $obj.closest('.participant-row').data('row-key');

        $.ajax({
            url: $obj.data('href') + '/id/' + rowIndex,
            method: 'POST',
            dataType: 'json',
            data: $('form#certificate').serializeArray(),
            processData: false,
            files: $('<input/>', {type: 'file'}), // Dirty fix. Iframe transport work only with file input
            iframe: true,
        });

        $('body').removeClass('wait');

        // Remove the iframe and form used for download file
        setTimeout(function () {
            $('body > iframe').remove();
            $('body > form[target]').remove();
        }, 5000);
    });

    // Page - certificate form. Close add the participant comment with save or cancel
    $('body').on('click', '#certificate-participant-table .close-participant-comment', function (e) {
        e.preventDefault();
        var $obj = $(this),
                $container = $obj.closest('.popover'),
                $openButton = $container.siblings('.add-participant-comment'),
                textareaValue;

        if ($obj.hasClass('save') && $container.find('textarea').valid()) {
            // Get and save entered value
            textareaValue = $container.find('textarea').val();
            $container.siblings('.popover-content.hide').find('textarea').html(textareaValue);

            // Set class for comment button
            if (textareaValue != '') {
                $openButton.addClass('text-success');
            } else {
                $openButton.removeClass('text-success');
            }

            // Close popover
            $openButton.popover('hide');
        } else if ($obj.hasClass('cancel')) {
            // Close popover
            $openButton.popover('hide');
        }
    });

    // Page - certificate form. Close add the participant comment with save or cancel
    $('body').on('click', '#certificate-csv-file-import', function (e) {
        e.preventDefault();
        var $obj = $(this), errorCount = 0, successCount = 0,
                $fileInput = $('#certificate-csv-file'),
                $importCountsObj = $('#import-counts'),
                key;

        // Add attr [name] for upload to server
        $fileInput.attr('name', 'file');

        if ($fileInput.val() == '') {
            alert($fileInput.data('empty-text'));

            // Disable loading state for button
            $obj.button('reset');
            return;
        }

        $.ajax($obj.data('href'), {
            method: 'POST',
            dataType: 'json',
            files: $fileInput,
            iframe: true,
        }).always(function () {
            // Disable loading state for button
            $obj.button('reset');

            // Remove attr [name] and disabled after upload
            $fileInput.removeAttr('name').removeAttr('disabled');
        }).done(function (response) {
            // Clear the file input
            $fileInput.val('');

            if (response.data.length === 0) {
                alert($fileInput.data('error-text'));
            } else {
                errorCount = response.errorCount;

                for (key in response.data) {
                    if (isUniqueEmail(response.data[key]['email'], 'participant-email')) {
                        addParticipantToTable(response.data[key]);
                        successCount++;
                    } else {
                        errorCount++;
                    }
                }

                // Show import complete message
                $('#success-count-import', $importCountsObj).text(successCount);
                $('#error-count-import', $importCountsObj).text(errorCount);
                $importCountsObj.removeClass('hide');
            }
        }).error(function () {
            alert($fileInput.data('error-text'));
        });
    });
});

function addParticipantToTable(participant) {
    var $table = $('#certificate-participant-table'),
            $participantRow = $('#empty-participant', $table).clone(),
            $targetColumn, field, currentIndex;

    for (field in participant) {
        currentIndex = $('tr.participant-row:not(.hide)').length;
        $targetColumn = $('.' + field + '_column', $participantRow);

        switch (field) {
            case 'comment':
                $('.actions .popover-content textarea', $participantRow)
                        .html(participant[field])
                        .attr('name', 'participant[' + currentIndex + '][' + field + ']');
                if (participant[field] != '') {
                    $('.add-participant-comment', $participantRow).addClass('text-success');
                }
                break
            case 'birthday':
                // Check date
                if (isDate(participant[field])) {
                    $('.participant-current-value', $targetColumn).text(participant[field]);
                    $('.participant-edit-value input', $targetColumn)
                            .val(participant[field]);
                }

                $('.participant-edit-value input', $targetColumn)
                        .attr('name', 'participant[' + currentIndex + '][' + field + ']')
                        .datepicker();
                break
            default:
                $('.participant-current-value', $targetColumn).text(participant[field]);
                $('.participant-edit-value input', $targetColumn)
                        .val(participant[field])
                        .attr('name', 'participant[' + currentIndex + '][' + field + ']');
                break
        }
    }

    // Prepare row and insert in table
    $participantRow.removeAttr('id')
            .removeClass('hide').attr('data-row-key', currentIndex)
            .appendTo($table);
}

// Check is date? Return true|false
function isDate(date) {
    return ((new Date(date) !== "Invalid Date" && !isNaN(new Date(date))));
}

// Check email is unique for this page? Return true|false
function isUniqueEmail(email, prefixAttr, element) {
    var selector = $.validator.format("[name][unique='{0}']", prefixAttr);
    var matches = new Array();
    $(selector).each(function (index, item) {
        if (email === $(item).val() && element != item) {
            matches.push(item);
        }
    });

    return matches.length === 0;
}

// A JavaScript equivalent of PHP’s nl2br() function
function nl2br(str) {
    if (typeof str != "string") {
        throw new Error('Only string parameter supported!');
    }
    
    return str.replace(/([^>])\n/g, '$1<br/>');
}

// A JavaScript equivalent of PHP’s number_format() function
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + (Math.round(n * k) / k).toFixed(prec);
            };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }

    return s.join(dec);
}

// Add "unique" method to jQuery validator
$.validator.addMethod("unique", function (value, element, params) {
    return isUniqueEmail(value, params, element);
});

$.validator.classRuleSettings.unique = {
    unique: true
};

// Ajax config default
$.ajaxSetup({
    dataType: 'json',
    beforeSend: function () {
        $('body').addClass('wait');
    },
    complete: function () {
        $('body').removeClass('wait');

        // Disable loading state element with data-loading-text
        $('[data-loading-text]').button('reset');
    }
});