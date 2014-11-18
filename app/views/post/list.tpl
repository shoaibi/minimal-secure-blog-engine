<div id="posts">
<h3 class="posts">Posts</h3>
<?php
if (empty($posts))
{
    echo "<p>No posts found</p>";
}
else
{
    $summary = true;
    foreach ($posts as $post)
    {
        \GGS\Components\Application::$view->renderPartial('post/_single', compact('post', 'summary'));
    }
}
?>
</div>