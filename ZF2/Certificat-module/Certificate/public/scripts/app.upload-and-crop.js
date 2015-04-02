$(function () {
    $('body').on('click', '.ce-file-upload-btn', function (e) {
        e.preventDefault();
        var $obj = $(this),
                $fileInput = $('.ce-file-upload-input');

        // Add attr [name] for upload to server
        $fileInput.attr('name', 'file');

        if ($fileInput.val() == '') {
            // Disable loading state for button
            $obj.button('reset');

            alert($fileInput.data('empty-text'));
            return;
        }

        $.ajax($obj.data('href'), {
            method: 'POST',
            dataType: 'json',
            files: $fileInput,
            iframe: true
        }).always(function () {
            // Disable loading state for button
            $obj.button('reset');

            // Remove attr [name] and enable after upload
            $fileInput.removeAttr('name').removeAttr('disabled').val('');
        }).done(function (response) {
            // If there are errors
            if (response.success === false) {
                alert(response.errors.join("\r\n"));
                return;
            }

            var showHeight = 220,
                    maxHeight = 198,
                    maxWidth = 154,
                    imgRatio, showWidth, cRatio,
                    prLeft, prRight, prTop, prBottom;

            imgRatio = response.img.width / response.img.height;
            showWidth = showHeight * imgRatio;
            cRatio = response.img.height / showHeight;
            prLeft = (showWidth - maxWidth) / 2;
            prRight = prLeft + maxWidth;
            prTop = (showHeight - maxHeight) / 2;
            prBottom = prTop + maxHeight;

            // Create and insert image to #crop-modal
            $('<img/>', {
                'id': 'crop-image',
                'src': response.img.src,
                'width': showWidth,
                'height': showHeight
            }).appendTo('#crop-modal .modal-body');

            // Set crop form action
            $('form#crop', '#crop-modal').attr('action', $('form#crop', '#crop-modal').data('action') + '/id/' + response.img.id);

            // Init the imgAreaSelect on shown modal
            $('#crop-modal').on('shown.bs.modal', function (e) {
                $('#crop-image', $(this)).imgAreaSelect({
                    aspectRatio: '7:9',
                    handles: true,
                    onInit: preview,
                    onSelectEnd: preview,
                    x1: number_format(prLeft),
                    y1: number_format(prTop),
                    x2: number_format(prRight),
                    y2: number_format(prBottom)
                });
            });

            // Show the crop modal
            $('#crop-modal').modal('show');

            function preview(img, selection) {
                cRatio = number_format(cRatio, 6);
                $('#x1').val(Math.round(selection.x1 * cRatio));
                $('#y1').val(Math.round(selection.y1 * cRatio));
                $('#x2').val(Math.round(selection.x2 * cRatio));
                $('#y2').val(Math.round(selection.y2 * cRatio));
                $('#w').val(Math.round(selection.width * cRatio));
                $('#h').val(Math.round(selection.height * cRatio));
            }
        }).error(function () {
            alert($fileInput.data('error-text'));
        });
    });

    // Remove imgAreaSelect on hide modal
    $('#crop-modal').on('hide.bs.modal', function (e) {
        $('#crop-image').imgAreaSelect({remove: true});
        $('#crop-image').remove();
    });

    // Remove image on hidden modal
    $('#crop-modal').on('hidden.bs.modal', function (e) {
        $('#crop-image').remove();
    });

    // Crop the image by click on modal button
    $('body').on('click', '#crop-do', function (e) {
        e.preventDefault();
        var $form = $(this).closest('form'), 
            target;

        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: $form.serializeArray(),
            dataType: 'json'
        }).done(function (response) {
            if (response.success) {
                target = $('.ce-file-upload-btn').data('target');
                $(target).find('img').attr('src', response.img.src);
                
                // Set new image in header profile picture
                $('.user-box .profile-picture img').attr('src', response.img.src);
            } else {
                alert(response.errors.join("\r\n"));
            }
        });
    });
});