<?php
if (empty($posts))
{
    echo ($page == 1) ? "<p>No posts found</p>" : '';
}
else
{
    $summary = true;
    foreach ($posts as $post)
    {
        \GGS\Components\WebApplication::$view->renderPartial('post/_single', compact('post', 'summary'));
    }
    echo '<a class="pager" id="next" href="' . \GGS\Components\Controller::createUrl('post', 'list', array('page' => ++$page)).'">Older</a>';
}