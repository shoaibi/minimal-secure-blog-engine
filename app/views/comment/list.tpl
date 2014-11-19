<div id="comments">
    <h3 class="comments">Comments</h3>
    <?php
        \GGS\Components\WebApplication::$view->renderPartial('comment/_list', compact('comments', 'page'));
        \GGS\Components\WebApplication::$view->renderPartial('common/_jscroll', array('selector' => 'div#comments'));
    ?>
</div>