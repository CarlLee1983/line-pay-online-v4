<?php

declare(strict_types=1);

namespace LinePay\Online\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use LinePay\Core\Config\LinePayConfig;
use LinePay\Online\LinePayClient;

/**
 * LINE Pay Online Service Provider for Laravel.
 *
 * Registers LinePayClient as a singleton in the Laravel IoC container.
 *
 * @example
 * ```php
 * // In a controller or service
 * public function __construct(private LinePayClient $linePay) {}
 *
 * // Or using the facade
 * LinePay::requestPayment($builder);
 * ```
 */
class LinePayServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the LINE Pay services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/linepay.php',
            'linepay'
        );

        $this->app->singleton(LinePayClient::class, function (Application $app): LinePayClient {
            /** @var \Illuminate\Config\Repository $configRepo */
            $configRepo = $app->make('config');

            /** @var array{channel_id?: string, channel_secret?: string, env?: string, timeout?: int} $config */
            $config = $configRepo->get('linepay', []);

            $linePayConfig = new LinePayConfig(
                channelId: $config['channel_id'] ?? '',
                channelSecret: $config['channel_secret'] ?? '',
                env: $config['env'] ?? 'sandbox',
                timeout: $config['timeout'] ?? 20
            );

            return new LinePayClient($linePayConfig);
        });

        $this->app->alias(LinePayClient::class, 'linepay');
    }

    /**
     * Bootstrap the LINE Pay services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/linepay.php' => $this->app->configPath('linepay.php'),
            ], 'linepay-config');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            LinePayClient::class,
            'linepay',
        ];
    }
}
