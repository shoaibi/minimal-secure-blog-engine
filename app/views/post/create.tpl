<?php
$attributeToInputTypeMapping    = array(
            'title'     => 'text',
            'email'     => 'email',
            'content'   => 'textarea'
            );
\GGS\Components\Application::$view->renderPartial('common/_form', compact('model', 'formName', 'attributeToInputTypeMapping'));
