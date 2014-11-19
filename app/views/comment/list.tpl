<h3 class="comments">Comments</h3>
<div id="comments">
    <?php
        $listData               = compact('models', 'page', 'postId', 'renderNextLink');
        $listData['selector']   = 'div#comments';
        \GGS\Components\WebApplication::$view->renderPartial('comment/_list', $listData);
        \GGS\Components\WebApplication::$view->renderPartial('common/_autorefresh', array(
                                                                                'containerSelector' => 'div#comments',
                                                                                'itemSelector' => 'div.comment',
                                                                                'refreshUrl' => $refreshUrl,
                                                                                'data' => compact('postId')));
    ?>
</div>