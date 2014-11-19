<?php
$summary = false;
\GGS\Components\WebApplication::$view->renderPartial('post/_single', compact('post', 'summary'));
\GGS\Components\WebApplication::$view->renderPartial('comment/create', compact('commentForm', 'formName', 'token'));
\GGS\Components\WebApplication::$view->renderPartial('comment/list', compact('comments', 'page'));