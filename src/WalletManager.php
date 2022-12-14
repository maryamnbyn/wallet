<?php

namespace App\Packages\wallet\wallet\src;

use App\Containers\AppSection\Transaction\Models\Transaction;
use App\Packages\wallet\wallet\src\Provider\AsanPardakhtProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use InvalidArgumentException;

/**
 *
 */
class WalletManager extends Manager implements Contracts\Factory
{
    /**
     * runtime driver configuration
     *
     * @var array
     */
    protected array $runtimeConfig;

    /**
     * @var string
     */
    protected string $environment;

    /**
     * @var string
     */
    protected string $cellNumber;

    /**
     * @var Transaction|null
     */
    public Transaction|null $transaction;


    /**
     * @param string $driver
     * @param array $config
     * @param Transaction|null $transaction
     * @param string $mobileNumber
     * @return mixed
     */
    public function with(string $driver, array $config, Transaction|null $transaction, string $mobileNumber): mixed
    {
        $this->transaction = $transaction;
        $this->cellNumber = $mobileNumber;

        if (!empty($config)) {
            $this->runtimeConfig = $config;
        }

        return $this->driver($driver);
    }


    /**
     * @return AsanPardakhtProvider|mixed
     */
    protected function createAsanPardakhtDriver(): mixed
    {
        $config = $this->getConfig('asanpardakht');

        return $this->buildProvider(
            AsanPardakhtProvider::class,
            $config,
            $this->cellNumber
        );
    }


    /**
     * @param $provider
     * @param array $config
     * @param string $mobileNumber
     * @return mixed
     */
    public function buildProvider($provider, array $config, string $mobileNumber)
    {
        return new $provider(
            $config,
            Arr::get($config, 'mode', config('wallet.mode', 'production')),
            $this->transaction,
            $mobileNumber
        );
    }

    /**
     * Get the default driver name.
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function getDefaultDriver(): string
    {
        throw new InvalidArgumentException('No Shaparak driver was specified.');
    }

    /**
     * get provider configuration runtime array or config based configuration
     *
     * @param string $driver
     *
     * @return array
     */
    protected function getConfig(string $driver): array
    {
        if (empty($this->runtimeConfig)) {
            return $this->container['config']["shaparak.providers.{$driver}"];
        }

        return $this->runtimeConfig;
    }

    /**
     * @param string $message
     * @param array $params
     * @param string $level
     */
    public static function log(string $message, array $params = [], string $level = 'debug'): void
    {
        $message = "WALLET -> " . $message;

        forward_static_call(['Tartan\Log\Facades\XLog', $level], $message, $params);
    }
}
