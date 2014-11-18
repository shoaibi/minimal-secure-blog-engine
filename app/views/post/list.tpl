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
        echo '<div class="post" id="' . $post->id . '">' .
                '<div class="post-title">' .
                    '<a href="' . \GGS\Components\Controller::createUrl('post', 'show', array('id' => $post->id)) . '">' .
                        $post->title .
                    '</a>' .
                '</div>' .
                '<div class="post-content">' .
                    \GGS\Helpers\StringUtils::getChoppedStringContent($post->content, 700) .
                '</div>' .
            '</div>';
    }
}
?>
</div>