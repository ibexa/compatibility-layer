<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

class ComposerRebranding implements RebrandingInterface
{
    protected const REPOSITORY_MAP = [
        // 'ezsystems/behatbundle' => 'ibexa/behat',
        'ezsystems/ezplatform-automated-translation' => 'ibexa/automated-translation',
        'ezsystems/ezplatform-core' => 'ibexa/core-extensions',
        'ezsystems/ezplatform-i18n' => 'ibexa/i18n',
        'ezsystems/ezplatform-admin-ui' => 'ibexa/admin-ui',
        'ezsystems/ezplatform-admin-ui-assets' => 'ibexa/admin-ui-assets',
        'ezsystems/ezplatform-calendar' => 'ibexa/calendar',
        'ezsystems/ezcommerce-admin-ui' => 'ibexa/commerce-admin-ui',
        'ezsystems/ezcommerce-base-design' => 'ibexa/commerce-base-design',
        'ezsystems/ezcommerce-checkout' => 'ibexa/commerce-checkout',
        'ezsystems/ezcommerce-erp-admin' => 'ibexa/commerce-erp-admin',
        'ezsystems/ezcommerce-fieldtypes' => 'ibexa/commerce-fieldtypes',
        'ezsystems/ezcommerce-order-history' => 'ibexa/commerce-order-history',
        'ezsystems/ezcommerce-page-builder' => 'ibexa/commerce-page-builder',
        'ezsystems/ezcommerce-price-engine' => 'ibexa/commerce-price-engine',
        'ezsystems/ezcommerce-rest' => 'ibexa/commerce-rest',
        'ezsystems/ezcommerce-shop' => 'ibexa/commerce-shop',
        'ezsystems/ezcommerce-shop-ui' => 'ibexa/commerce-shop-ui',
        'ezsystems/ezcommerce-transaction' => 'ibexa/commerce-transaction',
        'ezsystems/ezplatform-connector-dam' => 'ibexa/connector-dam',
        'ezsystems/ezplatform-content-forms' => 'ibexa/content-forms',
        'ezsystems/ezplatform-kernel' => 'ibexa/core',
        'ezsystems/ezplatform-cron' => 'ibexa/cron',
        'ezsystems/date-based-publisher' => 'ibexa/scheduler',
        'ezsystems/ezplatform-design-engine' => 'ibexa/design-engine',
        'ezsystems/doctrine-dbal-schema' => 'ibexa/doctrine-dbal-schema',
        'ezsystems/ezplatform-elastic-search-engine' => 'ibexa/elasticsearch',
        'ezsystems/ezplatform-form-builder' => 'ibexa/form-builder',
        'ezsystems/ezplatform-graphql' => 'ibexa/graphql',
        'ezsystems/ezplatform-http-cache' => 'ibexa/http-cache',
        'ezsystems/ezplatform-http-cache-fastly' => 'ibexa/fastly',
        'ezsystems/ezplatform-icons' => 'ibexa/icons',
        'ezsystems/ezplatform-matrix-fieldtype' => 'ibexa/fieldtype-matrix',
        'ezsystems/ezplatform-page-builder' => 'ibexa/page-builder',
        'ezsystems/ezplatform-page-fieldtype' => 'ibexa/fieldtype-page',
        'ezsystems/ezplatform-permissions' => 'ibexa/permissions',
        'ezsystems/ezplatform-personalization' => 'ibexa/personalization',
        'ezsystems/ezplatform-query-fieldtype' => 'ibexa/fieldtype-query',
        'ezsystems/ezplatform-rest' => 'ibexa/rest',
        'ezsystems/ezplatform-richtext' => 'ibexa/fieldtype-richtext',
        'ezsystems/ezplatform-search' => 'ibexa/search',
        'ezsystems/ezplatform-segmentation' => 'ibexa/segmentation',
        'ezsystems/ezplatform-site-factory' => 'ibexa/site-factory',
        'ezsystems/ezplatform-solr-search-engine' => 'ibexa/solr',
        'ezsystems/ezplatform-standard-design' => 'ibexa/standard-design',
        'ezsystems/ez-support-tools' => 'ibexa/system-info',
        'ezsystems/ezplatform-user' => 'ibexa/user',
        'ezsystems/ezplatform-version-comparison' => 'ibexa/version-comparison',
        'ezsystems/ezplatform-workflow' => 'ibexa/workflow',
        'ezsystems/ezplatform-connector-unsplash' => 'ibexa/connector-unsplash',
    ];

    public function getFileNamePatterns(): array
    {
        return [
            'composer.json',
        ];
    }

    public function rebrand(string $input): string
    {
        $output = $input;

        foreach (self::REPOSITORY_MAP as $old => $new) {
            $output = preg_replace(
                sprintf('/"%s": "[a-zA-Z0-9@-^\.]+"/', preg_quote($old, '/')),
                sprintf('"%s": "^4.0@dev"', $new),
                $output
            );
        }

        $output = preg_replace(
            '/"php": ".+"/',
            '"php": "^7.4 || ^8.0"',
            $output
        );

        $output = preg_replace(
            '/"ezsystems\/ezplatform-code-style": ".+"/',
            '"ibexa/code-style": "^1.0"',
            $output
        );

        return $output;
    }
}
