<div id="posts">
<?php
if (empty($posts))
{
    echo "<p>No posts found</p>";
}
else
{
    foreach ($posts as $post)
    {
        \GGS\Components\Application::$view->renderPartial('post/_single', compact('post'));
    }
}
?>
</div>