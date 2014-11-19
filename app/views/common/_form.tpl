<?php
$action = (!empty($action))? $action : null;
?>
<div id="form-messages"></div>
<div class="form">
    <?php
        if ($formTitle)
        {
            echo "<h3 class='form-title'>{$formTitle}</h3>";
        }
    ?>
    <form id="<?= $formName ?>" name="<?= $formName ?>" action="<?= $action ?>" method="post">
        <?php
        echo \GGS\Helpers\CsrfHelper::renderInput($token);
        echo \GGS\Helpers\HoneyPotInputHelper::renderInput();
        foreach ($attributeToInputTypeMapping as $attribute => $inputType)
        {
        echo \GGS\Helpers\FormHelper::renderInput($model, $formName, $attribute, $inputType);
        }
        ?>
        <div>
            <input type="submit" id="submit" value="Submit">
        </div>
    </form>
</div>