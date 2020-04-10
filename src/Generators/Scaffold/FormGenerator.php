<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Common\EBoundTo;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class FormGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

    /** @var array */
    private $fields;

    /** @var string */
    private $fileName;


    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathForms;
        $this->templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');
        $this->fileName = $this->commandData->modelName.'FormFactory.php';
    }

    public function generate()
    {
        if (!file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }

        $this->commandData->commandComment("\nGenerating Form...");

        $this->generateFields();
    }

    private function generateFields()
    {
        $this->fields = [];

        foreach ($this->commandData->fields as $field) {
            if (!$field->inForm) {
                continue;
            }

            $fieldTemplate = get_template('scaffold.fields.form_field', $this->templateType);

            if (!empty($fieldTemplate)) {
                $fieldTemplate = fill_template_with_field_data(
                    $this->commandData->dynamicVars,
                    $this->commandData->fieldNamesMapping,
                    $fieldTemplate,
                    $field
                );
                $this->fields[] = $fieldTemplate;
            }
        }

        $templateData = get_template('scaffold.formFactory', $this->templateType);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        switch ($this->commandData->boundTo)
        {
            case EBoundTo::NONE:
                $baseFactoryClass = 'BaseFormFactory';
                $createParams = '$action, $method';
                break;
            case EBoundTo::PROJECT:
                $baseFactoryClass = 'BaseProjectFormFactory';
                $createParams = '$action, $method, \'.' . $this->commandData->dynamicVars['$MODEL_NAME_SNAKE$'] . '.create\', $' . $this->commandData->dynamicVars['$MODEL_NAME_CAMEL$'] . '->id === NULL';
                break;
            case EBoundTo::PROJECT_TYPE:
                $baseFactoryClass = 'BaseProjectTypeFormFactory';
                $createParams = '$action, $method, \'.' . $this->commandData->dynamicVars['$MODEL_NAME_SNAKE$'] . '.create\', $' . $this->commandData->dynamicVars['$MODEL_NAME_CAMEL$'] . '->id === NULL';
                break;
        }

        $templateData = str_replace('$FIELDS$', trim(implode('', $this->fields)), $templateData);
        $templateData = str_replace('$BASE_FACTORY_CLASS$', $baseFactoryClass, $templateData);
        $templateData = str_replace('$BASE_FACTORY_PARAMS$', $createParams, $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);
        $this->commandData->commandInfo('Form created');
    }


    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Form file deleted: '.$this->fileName);
        }
    }
}
