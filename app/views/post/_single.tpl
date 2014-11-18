<div class="post" id="<?= $post->id ?>">
    <div class="post-title">
        <?php
            if ($summary)
            {
                echo '<a href="' . \GGS\Components\Controller::createUrl('post', 'show', array('id' => $post->id)).'">' . $post->title .'</a>';
            }
            else
            {
                echo $post->title . ' | <a href="' . \GGS\Components\Controller::createUrl('post', 'edit', array('id' => $post->id)).'">Edit</a>';
            }
        ?>
    </div>
    <div class="post-content">
        <?php
            if ($summary)
            {
                $content    = \GGS\Helpers\StringUtils::getChoppedStringContent($post->content, 700);
            }
            else
            {
                $content    = $post->content;
            }
        echo nl2br($content);
        ?>
    </div>
</div>