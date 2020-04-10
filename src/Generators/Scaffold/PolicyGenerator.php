<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Common\EBoundTo;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class PolicyGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $migrationPath;

    /** @var string */
    private $policyPath;

    /** @var string */
    private $templateType;

    /** @var array */
    private $fields;

    /** @var string */
    private $fileName;


    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->policyPath = $commandData->config->pathPolicies;
        $this->migrationPath = config('infyom.laravel_generator.path.migration', base_path('database/migrations/'));
        $this->templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');
    }

    public function generate()
    {
        if (!file_exists($this->migrationPath)) {
            mkdir($this->migrationPath, 0755, true);
        }
        if (!file_exists($this->policyPath)) {
            mkdir($this->policyPath, 0755, true);
        }

        $this->commandData->commandComment("\nGenerating Policy...");

        $this->generatePolicy();
        $this->generateMigration();
    }

    private function generatePolicy(): void
    {
        $own = $this->commandData->isOwnedByUser;
        $project = $this->commandData->boundTo !== EBoundTo::NONE;
        $fileName = $this->commandData->dynamicVars['$MODEL_NAME$'] . 'Policy.php';

        $vars = $this->commandData->dynamicVars;
        if ($project === TRUE)
        {
            $vars['$PATH_TO_TYPE$'] = $this->commandData->boundTo === EBoundTo::PROJECT ? 'project->projectType' : 'projectType';
        }

        $templateData = get_template('policy.policy' . ($own ? '_own' : '') . ($project ? '_project_type' : ''), $this->templateType);
        $templateData = fill_template($vars, $templateData);

        FileUtil::createFile($this->policyPath, $fileName, $templateData);
        $this->commandData->commandInfo('Policy created');
    }

    private function generateMigration(): void
    {
        $own = $this->commandData->isOwnedByUser;
        $modelSnake = $this->commandData->dynamicVars['$MODEL_NAME_SNAKE$'];

        $fileName = date('Y_m_d_His') . '_create_' . $modelSnake . '_permissions.php';

        $templateData = get_template('policy.migration' . ($own ? '_own' : ''), $this->templateType);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->migrationPath, $fileName, $templateData);
        $this->commandData->commandInfo('Policy migration created');
    }


    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Form file deleted: '.$this->fileName);
        }
    }
}
