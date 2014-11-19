<?php
namespace GGS\Helpers;

/**
 * Utility class for handling various form functions
 * Class FormHelper
 * @package GGS\Helpers
 */
abstract class FormHelper
{
    /**
     * Resolve a form's name provided the model it represents
     * @param $modelClass
     * @return mixed
     */
    public static function getName($modelClass)
    {
        return StringHelper::getNameWithoutNamespaces($modelClass);
    }

    /**
     * Resolve input id
     * @param $formName
     * @param $attribute
     * @return string
     */
    public static function resolveInputId($formName, $attribute)
    {
        return "{$formName}_{$attribute}";
    }

    /**
     * Resolve input name
     * @param $formName
     * @param $attribute
     * @return string
     */
    public static function resolveInputName($formName, $attribute)
    {
        return "{$formName}[{$attribute}]";
    }

    /**
     * Resolve input's error message's control id
     * @param $formName
     * @param $attribute
     * @return string
     */
    public static function resolveInputErrorMessageId($formName, $attribute)
    {
        return "{$formName}_{$attribute}_error";
    }

    /**
     * Resolve input's error message's control's css style
     * @param \GGS\Components\Model $model
     * @param $attribute
     * @return string
     */
    public static function resolveInputErrorMessageStyle(\GGS\Components\Model $model, $attribute)
    {
        if (!$model->hasError($attribute))
        {
            return 'display:none';
        }
    }

    /**
     * Resolve error for provided input. A small wrapper around model's functions
     * @param \GGS\Components\Model $model
     * @param $attribute
     * @return mixed
     */
    public static function resolveInputErrorMessage(\GGS\Components\Model $model, $attribute)
    {
        if ($model->hasError($attribute))
        {
            return $model->getError($attribute);
        }
    }

    /**
     * Render a form input with container, label and error message control
     * @param \GGS\Components\Model $model
     * @param $formName
     * @param $attribute
     * @param $inputType
     * @param bool $required
     * @param bool $visible
     * @param null $label
     * @param string $inputDivClass
     * @return string
     */
    public static function renderInput(\GGS\Components\Model $model, $formName, $attribute, $inputType,
                                       $required = true, $visible = true, $label = null, $inputDivClass = 'form-input')
    {
        $inputId            = static::resolveInputId($formName, $attribute);
        $inputName          = static::resolveInputName($formName, $attribute);
        $inputValue         = $model->$attribute;
        $errorMessageId     = static::resolveInputErrorMessageId($formName, $attribute);
        $errorMessageStyle  = static::resolveInputErrorMessageStyle($model, $attribute);
        $errorMessage       = static::resolveInputErrorMessage($model, $attribute);
        // resolve label
        $label              = (isset($label)) ? $label : $model->resolveAttributeLabel($attribute);
        // if the type is hidden or visible is false we set the wrapper's style to display:none
        $divStyle           = ($inputType === 'hidden' || !$visible) ? 'display:none' : null;
        // if the attribute is required we set the required property for html5 aware browsers
        $openingTagSuffix   = ($required) ? 'required>' : '>';
        $content            = static::renderFormInputWithData($inputType, $inputId, $inputName, $inputValue, $label,
                                                                $errorMessageId, $errorMessageStyle, $errorMessage,
                                                                $inputDivClass, $divStyle, $openingTagSuffix);
        return $content;
    }


    /**
     * Render Anti-Spam inputs, used for rendering honey pot inputs, csrf, etc.
     * @param $inputName
     * @param string $inputType
     * @param null $inputValue
     * @return string
     */
    public static function renderAntiSpamInput($inputName, $inputType = 'text', $inputValue = null)
    {
        $inputId            = $inputName;
        $errorMessageId     = '_error';
        $label              = null;
        // hide the error message control
        $errorMessageStyle  = 'display:none';
        $errorMessage       = null;
        $inputDivClass      = 'form_input';
        // hide the wrapper containing anti spam input
        $divStyle           = 'display:none';
        // anti spam inputs do not need any user input hence no required property set in openening tag's suffix
        $openingTagSuffix   = '>';
        $content            = static::renderFormInputWithData($inputType, $inputId, $inputName, $inputValue, $label,
                                                                $errorMessageId, $errorMessageStyle, $errorMessage,
                                                                $inputDivClass, $divStyle, $openingTagSuffix);
        return $content;

    }

    /**
     * Render form input
     * @param $inputType
     * @param $inputId
     * @param $inputName
     * @param $inputValue
     * @param $label
     * @param $errorMessageId
     * @param string $errorMessageStyle
     * @param null $errorMessage
     * @param string $inputDivClass
     * @param string $divStyle
     * @param string $openingTagSuffix
     * @return string
     */
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