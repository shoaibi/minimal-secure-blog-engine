<?php
$summary = false;
\GGS\Components\WebApplication::$view->renderPartial('post/_single', compact('model', 'summary'));
\GGS\Components\WebApplication::$view->renderPartial('comment/create', compact('commentForm', 'formName', 'token'));
$listData           = compact('page', 'postId', 'renderNextLink', 'refreshUrl');
$listData['models'] = $comments;
\GGS\Components\WebApplication::$view->renderPartial('comment/list', $listData);