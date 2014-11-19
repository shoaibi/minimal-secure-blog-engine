<div class="post" id="<?= $post->getPkValue() ?>">
    <div class="post-title">
        <?php
            if ($summary)
            {
                echo '<a href="' . \GGS\Components\WebApplication::$request->createUrl('post', 'show', array('id' => $post->getPkValue())).'">' . $post->title .'</a>';
            }
            else
            {
                echo $post->title . ' | <a href="' . \GGS\Components\WebApplication::$request->createUrl('post', 'edit', array('id' => $post->getPkValue())).'">Edit</a>';
            }
        ?>
    </div>
    <div class="post-content">
        <?php
            if ($summary)
            {
                $content    = \GGS\Helpers\StringHelper::getChoppedStringContent($post->content, 1000);
            }
            else
            {
                $content    = $post->content;
            }
        echo nl2br($content);
        ?>
    </div>
</div>