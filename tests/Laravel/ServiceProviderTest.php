<?php

declare(strict_types=1);

namespace LinePay\Online\Tests\Laravel;

use Illuminate\Config\Repository;
use LinePay\Online\Laravel\LinePayServiceProvider;
use LinePay\Online\LinePayClient;
use Orchestra\Testbench\TestCase;

class ServiceProviderTest extends TestCase
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [LinePayServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        /** @var Repository $config */
        $config = $app['config'];
        $config->set('linepay.channel_id', 'test-channel-id');
        $config->set('linepay.channel_secret', 'test-channel-secret');
        $config->set('linepay.env', 'sandbox');
        $config->set('linepay.timeout', 30);
    }

    public function testServiceProviderRegistersLinePayClient(): void
    {
        $app = $this->app;
        $this->assertNotNull($app);
        $this->assertTrue($app->bound(LinePayClient::class));
    }

    public function testLinePayClientIsSingleton(): void
    {
        $app = $this->app;
        $this->assertNotNull($app);

        $client1 = $app->make(LinePayClient::class);
        $client2 = $app->make(LinePayClient::class);

        $this->assertSame($client1, $client2);
    }

    public function testLinePayAlias(): void
    {
        $app = $this->app;
        $this->assertNotNull($app);

        $this->assertTrue($app->bound('linepay'));

        $client = $app->make('linepay');
        $this->assertInstanceOf(LinePayClient::class, $client);
    }

    public function testConfigIsLoaded(): void
    {
        $this->assertEquals('test-channel-id', config('linepay.channel_id'));
        $this->assertEquals('test-channel-secret', config('linepay.channel_secret'));
        $this->assertEquals('sandbox', config('linepay.env'));
        $this->assertEquals(30, config('linepay.timeout'));
    }
}
