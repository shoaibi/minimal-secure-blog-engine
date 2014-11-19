<?php
$isAjaxRequest      = \GGS\Components\WebApplication::$request->isAjaxRequest();
if (empty($models))
{
    echo (!$isAjaxRequest) ? "<p id='no-content'>{$noModelsText}</p>" : '';
}
else
{
    $summary = true;
    foreach ($models as $model)
    {
        \GGS\Components\WebApplication::$view->renderPartial($singlePartialDir . '/_single', compact('model', 'summary'));
    }

    if ($renderNextLink)
    {
        echo "<a class='pager' id='next' href='{$nextUrl}'>Older</a>";
        if (!$isAjaxRequest)
        {
            \GGS\Components\WebApplication::$view->renderPartial('common/_jscroll', compact('selector'));
        }
    }
}