<?php
$partialData                        = compact('selector', 'renderNextLink', 'models', 'postId');
$partialData['nextUrl']             = \GGS\Components\WebApplication::$request->createUrl('post', 'comments', array('postId' => $postId, 'page' => ++$page));
$partialData['noModelsText']        = 'No comments found. Be the first to express yourself';
$partialData['singlePartialDir']    = 'comment';

\GGS\Components\WebApplication::$view->renderPartial('common/_list', $partialData);
