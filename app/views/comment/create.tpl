<?php
$model  = $commentForm;
$attributeToInputTypeMapping    = array(
            'name'     => 'text',
            'email'     => 'email',
            'message'   => 'textarea'
            );
\GGS\Components\WebApplication::$view->renderPartial('common/_form', compact('model', 'formName', 'attributeToInputTypeMapping'));
?>

<script type="text/javascript">
    $(function()
    {
        var formId      = '<?= $formName ?>';
        var form        = $('#' + formId);
        var formMessages = $('#form-messages');
        $(formMessages).removeClass();
        $(form).submit(function(event)
        {
            var formData = $(form).serialize();
            $.ajax({
                type    : $(form).attr('method'),
                url     : $(form).attr('action'),
                data    : formData,
                dataType: 'json',
                success : function(data, status, request)
                            {
                                $('.errorMessage').text('').css('display', 'none');
                                $(formMessages).addClass(data.status);
                                $(formMessages).text(data.message);

                                if (data.status == 'success')
                                {
                                    $(form).trigger("reset");
                                }
                                if (data.status == 'error' && 'errors' in data)
                                {
                                    //console.log(data.errors);

                                    for (var attributeName in data.errors) {
                                        if (data.errors.hasOwnProperty(attributeName)) {
                                            console.log(attributeName);
                                            console.log(data.errors[attributeName]);
                                            $('#' + attributeName).siblings('.errorMessage').text(data.errors[attributeName]).css('display', '');
                                        }
                                    }
                                }
                            },
                error:      function(request, status, error)
                            {
                                $(formMessages).addClass('error');
                                $(formMessages).text('Oops! An error occured and your message could not be sent.');
                            }
                });
            event.preventDefault();
        });
    });
</script>