<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\CompatibilityLayer\Command;

use Ibexa\Bundle\CompatibilityLayer\Command\CommerceSettingRebrandingCommand;
use Ibexa\Contracts\Core\Repository\SettingService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Core\Repository\Permission\PermissionResolver;
use PHPUnit\Framework\TestCase;

class CommerceSettingRebrandingCommandTest extends TestCase
{
    private CommerceSettingRebrandingCommand $command;

    protected function setUp(): void
    {
        $this->command = new CommerceSettingRebrandingCommand(
            $this->createMock(SettingService::class),
            $this->createMock(PermissionResolver::class),
            $this->createMock(UserService::class)
        );
    }

    public function testReplaceKeysAndValues()
    {
        $inputMap = [
            'siso_core.default.category_view' => 'product_list',
            'silver_eshop.default.last_viewed_products_in_session_limit' => 10,
            'silver_eshop.default.catalog_description_limit' => 50,
            'siso_core.default.currency_rate_changed_at' => '01.01.2018',
            'siso_core.default.automatic_currency_conversion' => true,
            'siso_core.default.currency_list' => [
                'EUR' => 1,
                'USD' => 1.23625,
                'GBP' => 0.86466,
                'CAD' => 1.55686,
            ],
            'siso_core.default.standard_price_factory.fallback_currency' => 'EUR',
            'siso_core.default.standard_price_factory.base_currency' => 'EUR',
            'siso_price.default.price_service_chain.product_list' => [
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.product_detail' => [
                'siso_price.price_provider.shop',
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.slider_product_list' => [
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.basket' => [
                'siso_price.price_provider.shop',
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.basket_variant' => [
                'siso_price.price_provider.shop',
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.stored_basket' => [
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.wish_list' => [
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.quick_order' => [
                'siso_price.price_provider.shop',
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.quick_order_line_preview' => [
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.comparison' => [
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.search_list' => [
                'siso_price.price_provider.shop',
            ],
            'siso_price.default.price_service_chain.bestseller_list' => [
                'siso_price.price_provider.shop',
            ],
            'siso_core.default.marketing.olark_chat.activated' => false,
            'siso_core.default.marketing.olark_chat.id' => '6295-386-10-7457',
            'siso_basket.default.discontinued_products_listener_active' => true,
            'siso_basket.default.discontinued_products_listener_consider_packaging_unit' => true,
            'siso_checkout.default.order_confirmation.sales_email_address' => '',
            'siso_newsletter.default.newsletter_active' => false,
            'siso_newsletter.default.unsubscribe_globally' => true,
            'siso_newsletter.default.display_newsletter_box_for_logged_in_users' => true,
            'siso_newsletter.default.newsletter2go_username' => '',
            'siso_newsletter.default.newsletter2go_password' => '',
            'siso_newsletter.default.newsletter2go_auth_key' => '',
            'ses_basket.default.validHours' => 120,
            'ses_basket.default.refreshCatalogElementAfter' => '1 hours',
            'ses_basket.default.stock_in_column' => true,
            'ses_basket.default.description_limit' => 50,
            'ses_basket.default.additional_text_for_basket_line' => false,
            'ses_basket.default.additional_text_for_basket_line_input_limit' => 30,
            'ses_stored_basket.default.stock_in_column' => true,
            'ses_stored_basket.default.description_limit' => 50,
            'ses_wishlist.default.description_limit' => 50,
            'siso_core.default.template_debitor_country' => 'DE',
            'siso_core.default.enable_customer_number_login' => false,
            'siso_core.default.use_template_debitor_customer_number' => true,
            'siso_core.default.use_template_debitor_contact_number' => false,
            'siso_core.default.price_requests_without_customer_number' => true,
            'ses_basket.default.recalculatePricesAfter' => '3 hours',
            'silver_eshop.default.erp.variant_handling' => 'SKU_ONLY',
            'siso_erp.default.web_connector.service_location' => 'http://webconnproxy.silver-eshop.de?config=harmony_wc3_noop_mapping',
            'silver_eshop.default.webconnector.username' => 'admin',
            'silver_eshop.default.webconnector.password' => 'passwo',
            'silver_eshop.default.webconnector.soapTimeout' => 5,
            'silver_eshop.default.webconnector.erpTimeout' => 5,
            'siso_core.default.bestseller_limit_on_bestseller_page' => 6,
            'siso_core.default.bestseller_limit_on_catalog_page' => 6,
            'siso_core.default.bestseller_limit_in_silver_module' => 6,
            'siso_core.default.bestseller_threshold' => 1,
            'siso_checkout.default.payment_method.paypal_express_checkout' => true,
            'siso_checkout.default.payment_method.invoice' => true,
            'siso_checkout.default.shipping_method.standard' => true,
            'siso_checkout.default.shipping_method.express_delivery' => false,
            'siso_local_order_management.default.shipping_cost' => '',
            'siso_local_order_management.default.shipping_free' => '',
            'siso_core.default.shipping_vat_code' => 19,
            'siso_core.en.standard_price_factory.fallback_currency' => 'EUR',
            'siso_checkout.en.payment_method.paypal_express_checkout' => true,
            'siso_checkout.en.payment_method.invoice' => true,
            'siso_checkout.en.shipping_method.standard' => true,
            'siso_checkout.en.shipping_method.express_delivery' => true,
            'siso_core.de.standard_price_factory.fallback_currency' => 'EUR',
            'siso_checkout.de.payment_method.paypal_express_checkout' => true,
            'siso_checkout.de.payment_method.invoice' => true,
            'siso_checkout.de.shipping_method.standard' => true,
            'siso_checkout.de.shipping_method.express_delivery' => true,
        ];

        $expectedMap = [
            'ibexa.commerce.site_access.config.core.default.category_view' => 'product_list',
            'ibexa.commerce.site_access.config.eshop.default.last_viewed_products_in_session_limit' => 10,
            'ibexa.commerce.site_access.config.eshop.default.catalog_description_limit' => 50,
            'ibexa.commerce.site_access.config.core.default.currency_rate_changed_at' => '01.01.2018',
            'ibexa.commerce.site_access.config.core.default.automatic_currency_conversion' => true,
            'ibexa.commerce.site_access.config.core.default.currency_list' => [
                'EUR' => 1,
                'USD' => 1.23625,
                'GBP' => 0.86466,
                'CAD' => 1.55686,
            ],
            'ibexa.commerce.site_access.config.core.default.standard_price_factory.fallback_currency' => 'EUR',
            'ibexa.commerce.site_access.config.core.default.standard_price_factory.base_currency' => 'EUR',
            'ibexa.commerce.site_access.config.price.default.price_service_chain.product_list' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.product_detail' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.slider_product_list' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.basket' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.basket_variant' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.stored_basket' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.wish_list' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.quick_order' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.quick_order_line_preview' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.comparison' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.search_list' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.price.default.price_service_chain.bestseller_list' => [
                'Ibexa\Bundle\Commerce\PriceEngine\Service\ShopPriceProvider',
            ],
            'ibexa.commerce.site_access.config.core.default.marketing.olark_chat.activated' => false,
            'ibexa.commerce.site_access.config.core.default.marketing.olark_chat.id' => '6295-386-10-7457',
            'ibexa.commerce.site_access.config.basket.default.discontinued_products_listener_active' => true,
            'ibexa.commerce.site_access.config.basket.default.discontinued_products_listener_consider_packaging_unit' => true,
            'ibexa.commerce.site_access.config.checkout.default.order_confirmation.sales_email_address' => '',
            'ibexa.commerce.site_access.config.newsletter.default.newsletter_active' => false,
            'ibexa.commerce.site_access.config.newsletter.default.unsubscribe_globally' => true,
            'ibexa.commerce.site_access.config.newsletter.default.display_newsletter_box_for_logged_in_users' => true,
            'ibexa.commerce.site_access.config.newsletter.default.newsletter2go_username' => '',
            'ibexa.commerce.site_access.config.newsletter.default.newsletter2go_password' => '',
            'ibexa.commerce.site_access.config.newsletter.default.newsletter2go_auth_key' => '',
            'ibexa.commerce.site_access.config.basket.default.validHours' => 120,
            'ibexa.commerce.site_access.config.basket.default.refreshCatalogElementAfter' => '1 hours',
            'ibexa.commerce.site_access.config.basket.default.stock_in_column' => true,
            'ibexa.commerce.site_access.config.basket.default.description_limit' => 50,
            'ibexa.commerce.site_access.config.basket.default.additional_text_for_basket_line' => false,
            'ibexa.commerce.site_access.config.basket.default.additional_text_for_basket_line_input_limit' => 30,
            'ibexa.commerce.site_access.config.basket.stored.default.stock_in_column' => true,
            'ibexa.commerce.site_access.config.basket.stored.default.description_limit' => 50,
            'ibexa.commerce.site_access.config.wishlist.default.description_limit' => 50,
            'ibexa.commerce.site_access.config.core.default.template_debitor_country' => 'DE',
            'ibexa.commerce.site_access.config.core.default.enable_customer_number_login' => false,
            'ibexa.commerce.site_access.config.core.default.use_template_debitor_customer_number' => true,
            'ibexa.commerce.site_access.config.core.default.use_template_debitor_contact_number' => false,
            'ibexa.commerce.site_access.config.core.default.price_requests_without_customer_number' => true,
            'ibexa.commerce.site_access.config.basket.default.recalculatePricesAfter' => '3 hours',
            'ibexa.commerce.site_access.config.eshop.default.erp.variant_handling' => 'SKU_ONLY',
            'ibexa.commerce.site_access.config.erp.default.web_connector.service_location' => 'http://webconnproxy.silver-eshop.de?config=harmony_wc3_noop_mapping',
            'ibexa.commerce.site_access.config.eshop.default.webconnector.username' => 'admin',
            'ibexa.commerce.site_access.config.eshop.default.webconnector.password' => 'passwo',
            'ibexa.commerce.site_access.config.eshop.default.webconnector.soapTimeout' => 5,
            'ibexa.commerce.site_access.config.eshop.default.webconnector.erpTimeout' => 5,
            'ibexa.commerce.site_access.config.core.default.bestseller_limit_on_bestseller_page' => 6,
            'ibexa.commerce.site_access.config.core.default.bestseller_limit_on_catalog_page' => 6,
            'ibexa.commerce.site_access.config.core.default.bestseller_limit_in_silver_module' => 6,
            'ibexa.commerce.site_access.config.core.default.bestseller_threshold' => 1,
            'ibexa.commerce.site_access.config.checkout.default.payment_method.paypal_express_checkout' => true,
            'ibexa.commerce.site_access.config.checkout.default.payment_method.invoice' => true,
            'ibexa.commerce.site_access.config.checkout.default.shipping_method.standard' => true,
            'ibexa.commerce.site_access.config.checkout.default.shipping_method.express_delivery' => false,
            'ibexa.commerce.site_access.config.order.management.local.default.shipping_cost' => '',
            'ibexa.commerce.site_access.config.order.management.local.default.shipping_free' => '',
            'ibexa.commerce.site_access.config.core.default.shipping_vat_code' => 19,
            'ibexa.commerce.site_access.config.core.en.standard_price_factory.fallback_currency' => 'EUR',
            'ibexa.commerce.site_access.config.checkout.en.payment_method.paypal_express_checkout' => true,
            'ibexa.commerce.site_access.config.checkout.en.payment_method.invoice' => true,
            'ibexa.commerce.site_access.config.checkout.en.shipping_method.standard' => true,
            'ibexa.commerce.site_access.config.checkout.en.shipping_method.express_delivery' => true,
            'ibexa.commerce.site_access.config.core.de.standard_price_factory.fallback_currency' => 'EUR',
            'ibexa.commerce.site_access.config.checkout.de.payment_method.paypal_express_checkout' => true,
            'ibexa.commerce.site_access.config.checkout.de.payment_method.invoice' => true,
            'ibexa.commerce.site_access.config.checkout.de.shipping_method.standard' => true,
            'ibexa.commerce.site_access.config.checkout.de.shipping_method.express_delivery' => true,
        ];

        $result = $this->command->replaceKeysAndValues($inputMap);
        self::assertEquals($expectedMap, $result);
    }
}
