<?php
$action = (!empty($action))? $action : null;
?>
<p id="form-messages">
</p>
<div class="form">
    <form id="<?= $formName ?>" name="<?= $formName ?>" action="<?= $action ?>" method="post">
        <?php
        foreach ($attributeToInputTypeMapping as $attribute => $inputType)
        {
        echo \GGS\Helpers\FormUtils::renderInput($model, $formName, $attribute, $inputType);
        }
        ?>
        <div>
            <input type="submit" id="submit" value="Submit">
        </div>
    </form>
</div>