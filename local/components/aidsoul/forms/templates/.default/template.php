<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Form data from component -> $arResult['FORM']
 * Current form params -> $arResult['CURRENT_PARAMS']
 */
$formId = $arResult['FORM']['ID'] ?? 'ajax-form-' . uniqid();
$formClass = $arResult['FORM']['CLASS'] ?? 'ajax-form';
$formName = $arResult['FORM']['NAME'] ?? 'main';
$formAction = $arResult['FORM']['ACTION'] ?? '';
$currentParams = $arResult['CURRENT_PARAMS'] ?? [];
$isAjax = $arResult['FORM']['AJAX'] ?? true;
$submitText = $arResult['FORM']['SUBMIT_TEXT'] ?? 'Отправить';
$formTitle = $arResult['FORM']['TITLE'] ?? '';
?>

<?php if ($formTitle): ?>
    <h3 class="form-title"><?= htmlspecialcharsbx($formTitle) ?></h3>
<?php endif; ?>

<form 
    id="<?= htmlspecialcharsbx($formId) ?>" 
    name="form_<?= htmlspecialcharsbx($formName) ?>" 
    class="<?= htmlspecialcharsbx($formClass) ?>"
    action="<?= htmlspecialcharsbx($formAction) ?>"
    method="post"
    enctype="multipart/form-data"
    data-form-name="<?= htmlspecialcharsbx($formName) ?>"
    <?= $isAjax ? 'data-ajax="true"' : '' ?>
>
    <?php foreach ($currentParams as $fieldCode => $fieldParams): ?>
        <?php
        $fieldType = $fieldParams['type'] ?? 'text';
        $fieldLabel = $fieldParams['name'] ?? $fieldCode;
        $fieldRequired = $fieldParams['required'] ?? false;
        $fieldPlaceholder = $fieldParams['placeholder'] ?? '';
        $fieldValue = $fieldParams['value'] ?? '';
        $fieldClass = $fieldParams['class'] ?? 'form-field';
        $fieldId = $fieldParams['id'] ?? 'field_' . $fieldCode;
        
        $inputClass = $fieldRequired ? $fieldClass . ' required' : $fieldClass;
        ?>
        
        <div class="form-group field-<?= htmlspecialcharsbx($fieldCode) ?>">
            <?php if ($fieldType !== 'hidden' && $fieldLabel): ?>
                <label for="<?= htmlspecialcharsbx($fieldId) ?>">
                    <?= htmlspecialcharsbx($fieldLabel) ?>
                    <?php if ($fieldRequired): ?>
                        <span class="required-mark">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            
            <?php switch ($fieldType):
                case 'textarea': ?>
                    <textarea 
                        id="<?= htmlspecialcharsbx($fieldId) ?>"
                        name="<?= htmlspecialcharsbx($fieldCode) ?>"
                        class="<?= htmlspecialcharsbx($inputClass) ?>"
                        placeholder="<?= htmlspecialcharsbx($fieldPlaceholder) ?>"
                        <?= $fieldRequired ? 'required' : '' ?>
                    ><?= htmlspecialcharsbx($fieldValue) ?></textarea>
                    <?php break; ?>
                    
                case 'select': ?>
                    <select 
                        id="<?= htmlspecialcharsbx($fieldId) ?>"
                        name="<?= htmlspecialcharsbx($fieldCode) ?>"
                        class="<?= htmlspecialcharsbx($inputClass) ?>"
                        <?= $fieldRequired ? 'required' : '' ?>
                    >
                        <option value=""><?= htmlspecialcharsbx($fieldPlaceholder ?: 'Выберите...') ?></option>
                        <?php if (!empty($fieldParams['options'])): ?>
                            <?php foreach ($fieldParams['options'] as $optionValue => $optionLabel): ?>
                                <option 
                                    value="<?= htmlspecialcharsbx($optionValue) ?>"
                                    <?= ($fieldValue == $optionValue) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialcharsbx($optionLabel) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php break; ?>
                    
                case 'checkbox': ?>
                    <div class="checkbox-group">
                        <input 
                            type="checkbox"
                            id="<?= htmlspecialcharsbx($fieldId) ?>"
                            name="<?= htmlspecialcharsbx($fieldCode) ?>"
                            value="Y"
                            class="<?= htmlspecialcharsbx($inputClass) ?>"
                            <?= ($fieldValue === 'Y') ? 'checked' : '' ?>
                        />
                        <label for="<?= htmlspecialcharsbx($fieldId) ?>">
                            <?= htmlspecialcharsbx($fieldPlaceholder ?: $fieldLabel) ?>
                        </label>
                    </div>
                    <?php break; ?>
                    
                case 'file': ?>
                    <input 
                        type="file"
                        id="<?= htmlspecialcharsbx($fieldId) ?>"
                        name="<?= htmlspecialcharsbx($fieldCode) ?>"
                        class="<?= htmlspecialcharsbx($inputClass) ?>"
                        <?= !empty($fieldParams['multiple']) ? 'multiple' : '' ?>
                        <?= !empty($fieldParams['accept']) ? 'accept="' . htmlspecialcharsbx($fieldParams['accept']) . '"' : '' ?>
                    />
                    <?php break; ?>
                    
                case 'hidden': ?>
                    <input 
                        type="hidden"
                        id="<?= htmlspecialcharsbx($fieldId) ?>"
                        name="<?= htmlspecialcharsbx($fieldCode) ?>"
                        value="<?= htmlspecialcharsbx($fieldValue) ?>"
                    />
                    <?php break; ?>
                    
                case 'submit': ?>
                    <button 
                        type="submit"
                        id="<?= htmlspecialcharsbx($fieldId) ?>"
                        class="<?= htmlspecialcharsbx($inputClass) ?>"
                    >
                        <?= htmlspecialcharsbx($fieldLabel ?: $submitText) ?>
                    </button>
                    <?php break; ?>
                    
                default: ?>
                    <input 
                        type="<?= htmlspecialcharsbx($fieldType) ?>"
                        id="<?= htmlspecialcharsbx($fieldId) ?>"
                        name="<?= htmlspecialcharsbx($fieldCode) ?>"
                        class="<?= htmlspecialcharsbx($inputClass) ?>"
                        value="<?= htmlspecialcharsbx($fieldValue) ?>"
                        placeholder="<?= htmlspecialcharsbx($fieldPlaceholder) ?>"
                        <?= $fieldRequired ? 'required' : '' ?>
                    />
            <?php endswitch; ?>
            
            <span class="field-error" id="error_<?= htmlspecialcharsbx($fieldCode) ?>"></span>
        </div>
    <?php endforeach; ?>
    
    <div class="form-messages">
        <div class="form-success" style="display: none;"></div>
        <div class="form-error" style="display: none;"></div>
    </div>
    
    <?php if (!empty($arResult['FORM']['POLITIC_URL'])): ?>
        <div class="form-politic">
            <label>
                <input type="checkbox" name="politic" value="Y" required />
                <a href="<?= htmlspecialcharsbx($arResult['FORM']['POLITIC_URL']) ?>" target="_blank">
                    Я согласен с политикой обработки персональных данных
                </a>
            </label>
        </div>
    <?php endif; ?>
    
    <?php
    // Add hidden fields for AJAX
    if ($isAjax): ?>
        <input type="hidden" name="form" value="<?= htmlspecialcharsbx($formName) ?>" />
        <input type="hidden" name="ajax" value="Y" />
    <?php endif; ?>
</form>

<?php if ($isAjax): ?>
    <script>
        if (typeof FormComponentAjax !== 'undefined') {
            new FormComponentAjax('<?= htmlspecialcharsbx($formId) ?>');
        }
    </script>
<?php endif; ?>
