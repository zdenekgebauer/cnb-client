<?php

declare(strict_types=1);

namespace ZdenekGebauer\CnbClient;

use Codeception\Test\Unit;
use UnitTester;

class ClientTest extends Unit
{
    protected UnitTester $tester;

    public function testGetRateBaseCurrency(): void
    {
        $client = new class() extends Client {
            protected function curlExec(string $endpoint): string
            {
                return file_get_contents(codecept_data_dir('denni_kurz.txt'));
            }
        };

        $rate = $client->getRate('USD');
        $this->tester->assertEquals('USD', $rate->currency);
        $this->tester->assertEquals(24.065, $rate->rate);
        $this->tester->assertEquals(1, $rate->quantity);
        $this->tester->assertEquals('2022-07-22 00:00:00', $rate->date->format('Y-m-d H:i:s'));

        $rate = $client->getRate('HUF');
        $this->tester->assertEquals('HUF', $rate->currency);
        $this->tester->assertEquals(6.154, $rate->rate);
        $this->tester->assertEquals(100, $rate->quantity);
    }

    public function testGetRateOtherCurrency(): void
    {
        $client = new class() extends Client {
            protected function curlExec(string $endpoint): string
            {
                return file_get_contents(codecept_data_dir('kurzy.txt'));
            }
        };

        $rate = $client->getRate('AFN');
        $this->tester->assertEquals('AFN', $rate->currency);
        $this->tester->assertEquals(25.951, $rate->rate);
        $this->tester->assertEquals(100, $rate->quantity);
        $this->tester->assertEquals('2022-05-31 00:00:00', $rate->date->format('Y-m-d H:i:s'));
    }

    public function testGetRateUnkownCurrency(): void
    {
        $client = new class() extends Client {
            protected function curlExec(string $endpoint): string
            {
                return file_get_contents(codecept_data_dir('denni_kurz.txt'));
            }
        };

        $this->tester->expectThrowable(new Exception('Exchange rate of currency QQQ not found'), static function() use ($client) {
            $client->getRate('QQQ');
        });
    }

    public function testGetRateInvalidResponse(): void
    {
        $client = new class() extends Client {
            protected function curlExec(string $endpoint): string
            {
                return 'invalid response';
            }
        };

        $this->tester->expectThrowable(new Exception('Cannot parse date from response: invalid response'), static function() use ($client) {
            $client->getRate('USD');
        });
    }

    public function testGetRateConnectionFailed(): void
    {
        $client = new class() extends Client {
            protected function curlExec(string $endpoint): string
            {
                throw new Exception('connection failed', 123);
            }
        };

        $this->tester->expectThrowable(new Exception('connection failed', 123), static function() use ($client) {
            $client->getRate('USD');
        });
    }


}
