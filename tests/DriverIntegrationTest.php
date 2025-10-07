<?php

namespace JalalLinuX\PriceFeed\Tests;

use Illuminate\Support\Facades\Cache;
use JalalLinuX\PriceFeed\Enums\Currency;
use JalalLinuX\PriceFeed\Facades\PriceFeed;

class DriverIntegrationTest extends TestCase
{
    /**
     * Test all drivers defined in config file
     * Calls getPrices with all currencies from config and verifies responses
     */
    public function test_all_drivers_with_config_currencies()
    {
        // Clear cache before testing
        Cache::flush();

        $driversConfig = config('price-feed.drivers');

        $this->assertNotEmpty($driversConfig, 'Should have at least one driver configured');

        foreach ($driversConfig as $driverName => $config) {
            // Get driver instance
            $driver = PriceFeed::driver($driverName);

            // Get currencies from config
            $configuredCurrencies = $config['currencies'] ?? [];

            $this->assertNotEmpty(
                $configuredCurrencies,
                "Driver {$driverName} should have currencies defined in config"
            );

            // Test getPrices with all configured currencies
            try {
                $prices = $driver->getPrices($configuredCurrencies);

                $this->assertInstanceOf(
                    \Illuminate\Support\Collection::class,
                    $prices,
                    "Driver {$driverName} getPrices should return a Collection"
                );

                $this->assertCount(
                    count($configuredCurrencies),
                    $prices,
                    "Driver {$driverName} should return prices for all ".count($configuredCurrencies).' configured currencies'
                );

                // Verify each currency
                foreach ($configuredCurrencies as $currency) {
                    $this->assertTrue(
                        $prices->has($currency->value),
                        "Driver {$driverName} should have price for {$currency->value}"
                    );

                    $priceData = $prices->get($currency->value);

                    $this->assertInstanceOf(
                        \JalalLinuX\PriceFeed\DataTransferObjects\PriceData::class,
                        $priceData,
                        "Driver {$driverName} price data for {$currency->value} should be PriceData instance"
                    );

                    $this->assertEquals(
                        $currency,
                        $priceData->currency,
                        "Driver {$driverName} currency should match for {$currency->value}"
                    );

                    $this->assertNotNull(
                        $priceData->price,
                        "Driver {$driverName} price should not be null for {$currency->value}"
                    );

                    $this->assertIsFloat(
                        $priceData->price,
                        "Driver {$driverName} price should be float for {$currency->value}"
                    );

                    $this->assertGreaterThan(
                        0,
                        $priceData->price,
                        "Driver {$driverName} price should be greater than 0 for {$currency->value}"
                    );
                }

                $this->printTestSuccess($driverName, $configuredCurrencies);

            } catch (\Exception $e) {
                $this->fail(
                    "Driver {$driverName} failed to fetch prices: ".
                    $e->getMessage().
                    "\nFile: ".$e->getFile().
                    "\nLine: ".$e->getLine().
                    "\nTrace:\n".$e->getTraceAsString()
                );
            }
        }
    }

    /**
     * Print test success message
     */
    protected function printTestSuccess(string $driverName, array $currencies): void
    {
        fwrite(STDERR, "✓ Driver [$driverName] passed for currencies: ".implode(', ', array_map(fn ($c) => $c->value, $currencies))."\n");
    }

    /**
     * Test individual driver supports method
     */
    public function test_driver_supports_configured_currencies()
    {
        $driversConfig = config('price-feed.drivers');

        foreach ($driversConfig as $driverName => $config) {
            $driver = PriceFeed::driver($driverName);
            $configuredCurrencies = $config['currencies'] ?? [];

            foreach ($configuredCurrencies as $currency) {
                $this->assertTrue(
                    $driver->supports($currency),
                    "Driver {$driverName} should support {$currency->value} as it's in config"
                );
            }
        }
    }
}
