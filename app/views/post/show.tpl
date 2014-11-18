<div class="post" id="<?= $post->id ?>">
    <div class="post-title"> <?= $post->title ?></div>
    <div class="post-content"> <?= $post->content ?></div>
</div>
<div class="comments-form">
<p>form goes here</p>
</div>
<div class="comments">
    <?php
        if (empty($comments))
        {
            echo '<p>No comments found. Be the first to express yourself</p>';
        }
        else
        {
            foreach ($comments as $comment)
            {
                echo '<div class="comment" id="' . $comment->id . '">' .
                        '<div class="comment-author">' . $comment->name . '</div>' .
                        '<div class="comment-message">' . $comment->message . '</div></div>';
            }
        }
?>
</div>