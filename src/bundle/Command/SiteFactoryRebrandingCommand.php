<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\Command;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;
use Ibexa\Contracts\SiteFactory\Values\Query\Criterion\MatchAll;
use Ibexa\Contracts\SiteFactory\Values\Site\PublicAccess;
use Ibexa\Contracts\SiteFactory\Values\Site\SiteQuery;
use Ibexa\Contracts\SiteFactory\Values\Site\SiteUpdateStruct;
use Ibexa\SiteFactory\Persistence\Site\Gateway\AbstractGateway;
use Ibexa\SiteFactory\SiteDomainMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SiteFactoryRebrandingCommand extends Command
{
    protected static $defaultName = 'ibexa:rebranding:site-factory';

    private SymfonyStyle $style;

    private AbstractGateway $gateway;

    private SiteDomainMapper $siteMapper;

    private array $configResolverNamespacesMap;

    private array $containerParametersMap;

    public function __construct(
        AbstractGateway $gateway,
        SiteDomainMapper $siteMapper
    ) {
        $this->gateway = $gateway;
        $this->siteMapper = $siteMapper;
        $this->containerParametersMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'container-parameters-map.php';
        $this->configResolverNamespacesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'config-resolver-namespaces-map.php';

        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->style = new SymfonyStyle($input, $output);

        $query = new SiteQuery();
        $query->criteria = new MatchAll();

        $count = $this->gateway->count($query->criteria);

        $this->style->info('Rebranding ' . $count . ' sites configuration');

        $progressBar = new ProgressBar($this->style, $count);
        $progressBar->display();

        for ($offset = 0; $offset <= $count; $offset += $query->limit) {
            $sites = $this->gateway->find(
                $query->criteria,
                $offset,
                $query->limit,
            );

            foreach ($sites['rows'] as $key => $site) {
                $sites['rows'][$key]['config'] = $this->replaceOldParameters($site['config']);
            }

            /** @var \Ibexa\Contracts\SiteFactory\Values\Site\Site[] $sitesList */
            $sitesList = $this->siteMapper->buildSitesDomainObjectList($sites['rows']);
            foreach ($sitesList as $site) {
                $this->style->info('Rebranding:  ' . $site->name);

                $newPublicAccesses = [];
                foreach ($site->publicAccesses as $publicAccess) {
                    $newPublicAccess = new PublicAccess(
                        $publicAccess->identifier,
                        $publicAccess->getSiteId(),
                        $publicAccess->getSiteAccessGroup(),
                        $publicAccess->getMatcherConfiguration(),
                        $publicAccess->getSiteConfiguration(),
                        $publicAccess->getStatus(),
                    );
                    $newPublicAccesses[] = $newPublicAccess;
                }
                $this->gateway->update($site->id, new SiteUpdateStruct($site->name, $newPublicAccesses));
                $progressBar->advance();
                $progressBar->display();
            }
        }
        $this->style->success('Success.');

        return Command::SUCCESS;
    }

    public function replaceOldParameters(string $json): string
    {
        $config = json_decode($json, true);
        $newConfig = [];
        foreach ($config as $param => $value) {
            if (array_key_exists($param, $this->containerParametersMap)) {
                $newConfig[$this->containerParametersMap[$param]] = $value;
            } else {
                $newConfig[$this->replaceNamespaces($param)] = $value;
            }
        }

        return json_encode($newConfig);
    }

    private function replaceNamespaces(string $parameterName): string
    {
        foreach ($this->configResolverNamespacesMap as $oldNamespace => $newNamespace) {
            if ((strpos($parameterName, $oldNamespace) === 0)) {
                return str_replace($oldNamespace, $newNamespace, $parameterName);
            }
        }

        return $parameterName;
    }
}
