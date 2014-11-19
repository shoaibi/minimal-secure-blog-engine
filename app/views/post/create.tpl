<?php
$attributeToInputTypeMapping    = array(
            'title'     => 'text',
            'email'     => 'email',
            'content'   => 'textarea'
            );
\GGS\Components\WebApplication::$view->renderPartial('common/_form', compact('model', 'formName', 'token', 'attributeToInputTypeMapping'));
