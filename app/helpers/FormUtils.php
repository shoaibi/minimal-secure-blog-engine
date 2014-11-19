<?php
namespace GGS\Helpers;

abstract class FormUtils
{
    const SPAM_CHECK_INPUT_NAME     = 'check';

    public static function resolveInputId($formName, $attribute)
    {
        return "{$formName}_{$attribute}";
    }

    public static function resolveInputName($formName, $attribute)
    {
        return "{$formName}[{$attribute}]";
    }

    public static function resolveInputErrorMessageId($formName, $attribute)
    {
        return "{$formName}_{$attribute}_error";
    }

    public static function resolveInputErrorMessageStyle(\GGS\Components\Model $model, $attribute)
    {
        if (!$model->hasError($attribute))
        {
            return 'display:none';
        }
    }

    public static function resolveInputErrorMessage(\GGS\Components\Model $model, $attribute)
    {
        if ($model->hasError($attribute))
        {
            return $model->getError($attribute);
        }
    }

    public static function renderInput(\GGS\Components\Model $model, $formName, $attribute, $inputType,
                                       $required = true, $visible = true, $label = null, $inputDivClass = 'form-input')
    {
        $inputId            = static::resolveInputId($formName, $attribute);
        $inputName          = static::resolveInputName($formName, $attribute);
        $inputValue         = $model->$attribute;
        $errorMessageId     = static::resolveInputErrorMessageId($formName, $attribute);
        $errorMessageStyle  = static::resolveInputErrorMessageStyle($model, $attribute);
        $errorMessage       = static::resolveInputErrorMessage($model, $attribute);
        $label              = (isset($label)) ? $label : $model->resolveAttributeLabel($attribute);
        $divStyle           = ($inputType === 'hidden' || !$visible) ? 'display:none' : null;
        $openingTagSuffix   = ($required) ? 'required>' : '>';
        $content            = static::renderFormInputWithData($inputType, $inputId, $inputName, $inputValue, $label,
                                                                $errorMessageId, $errorMessageStyle, $errorMessage,
                                                                $inputDivClass, $divStyle, $openingTagSuffix);
        return $content;
    }

    public static function renderSpamCheckInput($inputType = 'text', $inputValue = null)
    {
        return static::renderAntiSpamInput(static::SPAM_CHECK_INPUT_NAME, $inputType, $inputValue);
    }

    public static function renderAntiSpamInput($inputName, $inputType = 'text', $inputValue = null)
    {
        $inputId            = $inputName;
        $errorMessageId     = '_error';
        $label              = null;
        $errorMessageStyle  = 'display:none';
        $errorMessage       = null;
        $inputDivClass      = 'form_input';
        $divStyle           = 'display:none';
        $openingTagSuffix   = '>';
        $content            = static::renderFormInputWithData($inputType, $inputId, $inputName, $inputValue, $label,
                                                                $errorMessageId, $errorMessageStyle, $errorMessage,
                                                                $inputDivClass, $divStyle, $openingTagSuffix);
        return $content;

    }

    protected static function renderFormInputWithData($inputType, $inputId, $inputName, $inputValue, $label,
                                                        $errorMessageId, $errorMessageStyle = 'display:none',
                                                        $errorMessage = null, $inputDivClass = 'form_input',
                                                        $divStyle = '', $openingTagSuffix = '>')
    {
        $content            = "<div class='{$inputDivClass}' style='{$divStyle}'>";
        $content            .= "<label for='{$inputId}'>{$label}</label>";
        if (in_array($inputType, array('text', 'password', 'hidden', 'email')))
        {
            $content        .= "<input type='{$inputType}' name='{$inputName}' id='{$inputId}' value='{$inputValue}' {$openingTagSuffix}";
        }
        else if ($inputType === 'textarea')
        {
            $content        .= "<{$inputType} rows='4' cols='50' name='{$inputName}' id='{$inputId}' {$openingTagSuffix}{$inputValue}</{$inputType}>";
        }

        $content            .= "<p class='errorMessage' id='{$errorMessageId}' style='{$errorMessageStyle}'>{$errorMessage}</p>";
        $content            .= "</div>";
        return $content;
    }
}