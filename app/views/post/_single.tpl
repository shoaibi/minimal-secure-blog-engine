<div class="post" id="<?= $model->getPkValue() ?>">
    <div class="post-title">
        <?php
            if ($summary)
            {
                echo '<a href="' . \GGS\Components\WebApplication::$request->createUrl('post', 'show', array('id' => $model->getPkValue())).'">' . $model->title .'</a>';
            }
            else
            {
                echo $model->title . ' <span><a class="edit" href="' . \GGS\Components\WebApplication::$request->createUrl('post', 'edit', array('id' => $model->getPkValue())).'">Edit</a></span>';
            }
        ?>
    </div>
    <div class="post-content">
        <?php
            if ($summary)
            {
                $content    = \GGS\Helpers\StringHelper::getChoppedStringContent($model->content, 1000);
            }
            else
            {
                $content    = $model->content;
            }
        echo nl2br($content);
        ?>
    </div>
</div>