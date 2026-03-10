<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\Command;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SettingService;
use Ibexa\Contracts\Core\Repository\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommerceSettingRebrandingCommand extends Command
{
    protected static $defaultName = 'ibexa:rebranding:commerce-setting';

    private SettingService $settingService;

    private PermissionResolver $permissionResolver;

    private UserService $userService;

    private array $containerParametersMap;

    private array $configResolverNamespacesMap;

    private AggregateResolver $nameResolver;

    private array $servicesMap;

    public function __construct(
        SettingService $settingService,
        PermissionResolver $permissionResolver,
        UserService $userService
    ) {
        $this->settingService = $settingService;
        $this->permissionResolver = $permissionResolver;

        $this->containerParametersMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'container-parameters-map.php';
        $this->configResolverNamespacesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'config-resolver-namespaces-map.php';

        $this->nameResolver = new AggregateResolver([
            new ClassMapResolver(),
            new PSR4PrefixResolver(),
        ]);
        $this->servicesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'services-to-fqcn-map.php';

        parent::__construct();
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin($input->getOption('user'))
        );
    }

    protected function configure()
    {
        $this
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Ibexa username (with Role containing at least `setting` `update` permission)',
                'admin'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->style = new SymfonyStyle($input, $output);

        try {
            $setting = $this->settingService->loadSetting('commerce', 'config');
        } catch (NotFoundException $e) {
            $this->style->error('Commerce settings not found, is Commerce Edition installed?');

            return Command::FAILURE;
        }

        $newValues = $this->replaceKeysAndValues($setting->value);

        $updateStruct = $this->settingService->newSettingUpdateStruct([
            'group' => 'commerce',
            'identifier' => 'config',
            'value' => $newValues,
        ]);
        $this->settingService->updateSetting($setting, $updateStruct);

        $this->style->success('Commerce settings updated');

        return Command::SUCCESS;
    }

    public function replaceKeysAndValues(array $config): array
    {
        $newConfig = [];
        foreach ($config as $param => $value) {
            if (array_key_exists($param, $this->containerParametersMap)) {
                $newParam = $this->containerParametersMap[$param];
            } else {
                $newParam = $this->replaceNamespaceInFullParameter(
                    $param
                );
            }
            $newValues = [];
            if (is_array($value)) {
                foreach ($value as $key => $singleValue) {
                    $newValues[$key] = $this->replaceValue($singleValue);
                }
            } else {
                $newValues = $this->replaceValue($value);
            }
            $newConfig[$newParam] = $newValues;
        }

        return $newConfig;
    }

    private function replaceNamespaceInFullParameter(string $parameterName): string
    {
        $parameterParts = explode('.', $parameterName, 3);
        if (count($parameterParts) < 3) {
            return $parameterName;
        }
        [$namespace, $scope, $dynamicParameterName] = $parameterParts;

        if (isset($this->configResolverNamespacesMap[$namespace])) {
            return "{$this->configResolverNamespacesMap[$namespace]}.$scope.$dynamicParameterName";
        }

        return $parameterName;
    }

    private function replaceValue($singleValue)
    {
        if (!is_string($singleValue) || $singleValue === '') {
            return $singleValue;
        }
        if (isset($this->servicesMap[$singleValue])) {
            return $this->servicesMap[$singleValue];
        }
        $possibleNewClass = $this->nameResolver->resolve($singleValue);
        if ($possibleNewClass !== null) {
            return $possibleNewClass;
        }

        return $singleValue;
    }
}
