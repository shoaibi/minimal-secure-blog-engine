<?php
$partialData                        = compact('selector', 'renderNextLink', 'models');
$partialData['nextUrl']             = \GGS\Components\WebApplication::$request->createUrl('post', 'list', array('page' => ++$page));
$partialData['noModelsText']        = 'No posts found';
$partialData['singlePartialDir']    = 'post';

\GGS\Components\WebApplication::$view->renderPartial('common/_list', $partialData);
