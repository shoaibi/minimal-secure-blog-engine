<div class="comment" id="<?= $model->getPkValue() ?>">
    <div class="comment-author"> <?= $model->name ?> says:</div>
    <div class="comment-message"><?= nl2br($model->message) ?></div>
</div>