<?php
$formTitle  = $pageTitle;
$attributeToInputTypeMapping    = array(
            'title'     => 'text',
            'email'     => 'email',
            'content'   => 'textarea'
            );
\GGS\Components\WebApplication::$view->renderPartial('common/_form', compact('model', 'formName', 'formTitle', 'token', 'attributeToInputTypeMapping'));
