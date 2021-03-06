<?php
$formTitle  = 'Add Comment';
$model      = $commentForm;
$attributeToInputTypeMapping    = array(
            'name'     => 'text',
            'email'     => 'email',
            'message'   => 'textarea'
            );
\GGS\Components\WebApplication::$view->renderPartial('common/_form', compact('model', 'formName', 'formTitle', 'token', 'attributeToInputTypeMapping'));
?>

<script type="text/javascript">
    $(function()
    {
        var formId      = '<?= $formName ?>';
        var form        = $('#' + formId);
        var formMessages = $('#form-messages');
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
                                $(formMessages).removeClass();
                                $(formMessages).show().addClass(data.status).text(data.message);

                                if (data.status == 'success')
                                {
                                    //$(form).trigger("reset");
                                    $(form).parent().hide().html('');
                                    $(formMessages).fadeOut(1500);
                                }
                                if (data.status == 'error' && 'errors' in data)
                                {
                                    for (var attributeName in data.errors) {
                                        if (data.errors.hasOwnProperty(attributeName)) {
                                            $('#' + attributeName).siblings('.errorMessage').text(data.errors[attributeName]).show();;
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