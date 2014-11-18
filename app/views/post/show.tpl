<?php
$summary = false;
\GGS\Components\Application::$view->renderPartial('post/_single', compact('post', 'summary'));
\GGS\Components\Application::$view->renderPartial('comment/create', compact('commentForm', 'formName'));
?>
<div class="comments">
    <h3 class="comments">Comments</h3>
    <?php
        if (empty($comments))
        {
            echo '<p>No comments found. Be the first to express yourself</p>';
        }
        else
        {
            foreach ($comments as $comment)
            {
                \GGS\Components\Application::$view->renderPartial('comment/_single', compact('comment'));
            }
        }
?>
</div>