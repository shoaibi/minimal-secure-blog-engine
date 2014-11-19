<div id="posts">
    <?php
        $listData               = compact('models', 'page', 'renderNextLink');
        $listData['selector']   = 'div#posts';
        \GGS\Components\WebApplication::$view->renderPartial('post/_list', $listData);
        \GGS\Components\WebApplication::$view->renderPartial('common/_autorefresh', array(
                                                                                    'containerSelector' => 'div#posts',
                                                                                    'itemSelector' => 'div.post',
                                                                                    'refreshUrl' => $refreshUrl));
    ?>
</div>