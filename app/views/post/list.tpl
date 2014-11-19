<div id="posts">
<h3 class="posts">Posts</h3>
<?php
\GGS\Components\WebApplication::$view->renderPartial('post/_list', compact('posts', 'page'));
\GGS\Components\WebApplication::$view->renderPartial('common/_jscroll', array('selector' => 'div#posts'));
?>
</div>