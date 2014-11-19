<?php
    if (empty($comments))
    {
        echo ($page == 1) ? '<p>No comments found. Be the first to express yourself</p>' : '';
}
else
{
    foreach ($comments as $comment)
    {
        \GGS\Components\WebApplication::$view->renderPartial('comment/_single', compact('comment'));
    }
    echo '<a class="pager" id="next" href="' . \GGS\Components\Controller::createUrl('post', 'comments', array('postId' => $comment->postId, 'page' => ++$page)).'">Older</a>';
}