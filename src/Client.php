<?php

declare(strict_types=1);

namespace ZdenekGebauer\CnbClient;

use DateTimeImmutable;
use DateTimeZone;

use function array_slice;
use function in_array;

class Client
{
    /** @codingStandardsIgnoreStart */
    private const ENDPOINT_BASE_CURRENCIES = 'https://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt?date=%s',
        ENDPOINT_OTHER_CURRENCIES = 'https://www.cnb.cz/cs/financni-trhy/devizovy-trh/kurzy-ostatnich-men/kurzy-ostatnich-men/kurzy.txt?rok=%d&mesic=%d';
    /** @codingStandardsIgnoreEnd */

    private const BASE_CURRENCIES = [
        'AUD',
        'BRL',
        'BGN',
        'CNY',
        'DKK',
        'EUR',
        'PHP',
        'HKD',
        'HRK',
        'INR',
        'IDR',
        'ISK',
        'ILS',
        'JPY',
        'ZAR',
        'CAD',
        'KRW',
        'HUF',
        'MYR',
        'MXN',
        'XDR',
        'NOK',
        'NZD',
        'PLN',
        'RON',
        'SGD',
        'SEK',
        'CHF',
        'THB',
        'TRY',
        'USD',
        'GBP',
    ];

    /**
     * @var array<string, array<string, Rate>>
     */
    private ?array $cacheRates = null;

    public function getRate(string $currency, ?\DateTimeInterface $date = null): Rate
    {
        $date = ($date ?? new DateTimeImmutable('now', new DateTimeZone('UTC')));
        $dateString = $date->format('Y-m-d');
        if ($this->cacheRates === null || !isset($this->cacheRates[$currency])) {
            $endpoint = sprintf(self::ENDPOINT_OTHER_CURRENCIES, $date->format('Y'), $date->format('m'));
            if (in_array($currency, self::BASE_CURRENCIES, true)) {
                $endpoint = sprintf(self::ENDPOINT_BASE_CURRENCIES, $date->format('d.m.Y'));
            }
            $response = $this->curlExec($endpoint);
            $this->parseResponse($response, $date);
        }
        if (isset($this->cacheRates[$currency][$dateString])) {
            return $this->cacheRates[$currency][$dateString];
        }
        throw new Exception('Exchange rate of currency ' . $currency . ' not found');
    }

    protected function curlExec(string $endpoint): string
    {
        $ch = curl_init($endpoint);
        if (!$ch) {
            throw new Exception('cannot open ' . $endpoint);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $curlErrNo = curl_errno($ch);
        curl_close($ch);
        unset($ch);

        if ($curlErrNo > 0) {
            throw new Exception($curlError, $curlErrNo);
        }
        return trim((string)$response);
    }

    private function parseResponse(string $response, \DateTimeInterface $date): void
    {
        $lines = explode("\n", $response);

        [$responseDate] = explode(' ', $lines[0]);
        $responseDate = DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $responseDate . ' 00:00:00');
        if (!$responseDate) {
            throw new Exception('Cannot parse date from response: ' . substr($response, 0, 100));
        }

        $dateString = $date->format('Y-m-d');

        $lines = array_slice($lines, 2); // skip header
        foreach ($lines as $line) {
            $values = explode('|', $line);
            if (isset($values[4])) {
                $currency = $values[3];
                $ratio = (float)str_replace(',', '.', $values[4]);
                $quantity = (int)$values[2];
                $this->cacheRates[$currency][$dateString] = new Rate($currency, $quantity, $ratio, $responseDate);
            }
        }
    }
}
