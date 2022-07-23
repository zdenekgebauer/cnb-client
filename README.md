# Cryptowatch HTTP client 

![](https://github.com/zdenekgebauer/cnb-client/workflows/build/badge.svg)

Client for retrieving  exchange rate of the Czech currency against foreign currencies from Czech National Bank (https://wwww.cnb.cz/)

Official information: https://www.cnb.cz/cs/financni-trhy/devizovy-trh/kurzy-devizoveho-trhu/kurzy-devizoveho-trhu/

## Installation
```bash
composer require zdenekgebauer/cnb-client
````

## Usage
```php
use \ZdenekGebauer\CnbClient\Client;
use \ZdenekGebauer\CnbClient\Exception;

$client = new Client(); 

// retrieve current rate
try { 
    $rate = $client->getRate('USD'));
} catch (\ZdenekGebauer\CnbClient\Exception) {
    // exception about unsupported currency or failed connection
}

var_dump($rate->currency) // HUF
var_dump($rate->quantity) // 100 
var_dump($rate->rate) // 6.154 means 6.154 CZK/100 HUF  
var_dump($rate->date) // date of rate from API  

// retrieve rate for specific date
$rate = $client->getRate('HUF', new \DateTime('2022-06-22')));
```

## Licence
Released under the [WTFPL license](copying.txt) http://www.wtfpl.net/about/.
