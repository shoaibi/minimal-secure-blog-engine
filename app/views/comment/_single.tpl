<div class="comment" id="<?= $comment->getPkValue() ?>">
    <div class="comment-author"> <?= $comment->name ?> says:</div>
    <div class="comment-message"><?= nl2br($comment->message) ?></div>
</div>