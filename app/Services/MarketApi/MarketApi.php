<?php

namespace App\Services\MarketApi;

use App\Enums\MarketEnum;
use App\Exceptions\ApiInactiveException;
use App\Exceptions\SystemApiInactiveException;
use App\Exceptions\MessageException;
use App\Models\Apikey;
use App\Models\Freefunds;
use App\Models\Instrument;
use App\User;

abstract class MarketApi implements MarketApiInterface
{
    public $user;
    public $market;

    public $systemMarketKey;
    public $systemMarketSecret;
    public $userMainSecret;
    public $userMainUrl;
    public $userMarketKey;
    public $userMarketSecret;
    public $instruments;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function isInsufficientFunds(Instrument $instrument, $amount)
    {
        $freeFundsBalance = 0;
        $portfolioDeposit = 0;

        $freefunds = Freefunds::where('user_id', $this->user->id)
            ->where('instrument_id', $instrument->id)
            ->first();

        if ($freefunds instanceof Freefunds) {
            $freeFundsBalance = $freefunds->amount;
        }

        $assets = $this->getAssets();

        if (count($assets)) {
            foreach ($assets as $asset) {
                if ($instrument->id == $asset['instrument']['id']) {
                    $earlyReservedFunds = bcadd($freeFundsBalance, $portfolioDeposit, 8);
                    $freefundsBalance = bcsub($asset['balance'], $earlyReservedFunds, 8);
                    if ($freefundsBalance >= $amount) {
                        return false;
                    }
                }
            }
        }

        throw new \Exception('Insufficient funds.');
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ApiInactiveException
     * @throws MessageException
     */
    protected function runInternalApiQuery(array $data = [])
    {
        $string = $this->getUserMainSecret();

        foreach ($data as $key => $val) {
            $string .= $key . $val;
        }

        $hash = hash('sha512', $string);

        $data = ['hash' => $hash] + $data;

        $ch = curl_init($this->userMainUrl . '/');

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);

        if ($info['http_code'] != 200) {
            throw new MessageException(__('messages.curl.not200OK') . ' ' . $info['http_code']);
        }

        if ($error) {
            throw new MessageException($error . ', ' . $info['http_code']);
        }

        return $response;
    }

    protected function getMarketInstruments()
    {
        if (is_null($this->instruments)) {
            $this->instruments = Instrument::where('market', $this->market)
                ->where('is_active', true)
                ->orderBy('ticker')
                ->get();
        }

        return $this->instruments;
    }

    /**
     * @return mixed
     * @throws ApiInactiveException
     */
    protected function getUserMarketSecret()
    {
        if (is_null($this->userMarketSecret)) {
            $this->setUserMarketApi();
        }

        return $this->userMarketSecret;
    }

    /**
     * @return mixed
     * @throws ApiInactiveException
     */
    protected function getUserMarketKey()
    {
        if (is_null($this->userMarketKey)) {
            $this->setUserMarketApi();
        }

        return $this->userMarketKey;
    }

    /**
     * @throws ApiInactiveException
     */
    protected function setUserMarketApi()
    {
        $apikey = Apikey::where('user_id', $this->user->id)
            ->where('market', $this->market)
            ->first();

        if (! $apikey instanceof Apikey || ! $apikey->is_active) {
            throw new ApiInactiveException();
        }

        //if ($apikey->is_active && ! $apikey->is_advanced) {
            $this->userMarketKey = $apikey->key;
            $this->userMarketSecret = $apikey->secret;
        //}
        if ($this->market == MarketEnum::WEBMONEY) {
            $this->userMarketKey = $apikey->key;
        }
        // if need remote request extends this on child method
    }

    /**
     * @return mixed
     * @throws ApiInactiveException
     */
    protected function getUserMainUrl()
    {
        if (is_null($this->userMainUrl)) {
            $this->setUserMainApi();
        }

        return $this->userMainSecret;
    }

    /**
     * @return mixed
     * @throws ApiInactiveException
     */
    protected function getUserMainSecret()
    {
        if (is_null($this->userMainSecret)) {
            $this->setUserMainApi();
        }

        return $this->userMainSecret;
    }

    /**
     * @throws ApiInactiveException
     */
    private function setUserMainApi()
    {
        $apikey = Apikey::where('user_id', $this->user->id)
            ->where('market', 0)
            //->where('is_active', true)
            ->first();

        if (! $apikey instanceof Apikey || ! $apikey->is_active) {
            throw new ApiInactiveException();
        }

        $this->userMainSecret = $apikey->secret;
        $this->userMainUrl = $apikey->url;
    }

    /**
     * @return mixed
     * @throws SystemApiInactiveException
     */
    protected function getSystemMarketSecret()
    {
        if (is_null($this->systemMarketSecret)) {
            $this->setSystemMarketApi();
        }

        return $this->systemMarketSecret;
    }

    /**
     * @return mixed
     * @throws SystemApiInactiveException
     */
    protected function getSystemMarketKey()
    {
        if (is_null($this->systemMarketKey)) {
            $this->setSystemMarketApi();
        }

        return $this->systemMarketKey;
    }

    private function setSystemMarketApi()
    {
        $apikey = Apikey::where('user_id', 0)
            ->where('market', $this->market)
            ->first();

        if (! $apikey instanceof Apikey) {
            throw new SystemApiInactiveException(__('messages.apikey.system-error'));
        }

        $this->systemMarketKey = ($apikey) ? $apikey->key : '';
        $this->systemMarketSecret = ($apikey) ? $apikey->secret : '';
    }
}