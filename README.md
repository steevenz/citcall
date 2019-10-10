# Citcall API

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/steevenz/citcall/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/steevenz/citcall/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/steevenz/citcall/badges/build.png?b=master)](https://scrutinizer-ci.com/g/steevenz/citcall/build-status/master)
[![Latest Stable Version](https://poser.pugx.org/steevenz/citcall/v/stable)](https://packagist.org/packages/steevenz/citcall)
[![Total Downloads](https://poser.pugx.org/steevenz/citcall/downloads)](https://packagist.org/packages/steevenz/citcall)
[![License](https://poser.pugx.org/steevenz/citcall/license)](https://packagist.org/packages/steevenz/citcall)

Citcall API PHP Class Library berfungsi untuk melakukan request API pengiriman SMS dan Call menggunakan [Citcall](http://www.citcall.com/).

Instalasi
---------
Cara terbaik untuk melakukan instalasi library ini adalah dengan menggunakan [Composer](https://getcomposer.org)
```
composer require steevenz/citcall
```

Penggunaan
----------
```php
use Steevenz\Citcall;

/*
 * --------------------------------------------------------------
 * Inisiasi Class Citcall
 *
 * @param string Username
 * @param string API Key
 * --------------------------------------------------------------
 */
 $citcall = new Citcall([
   'version'  => 'v3', // default v3
   'userId'   => 'USERID',
   'senderId' => 'SENDERID',
   'apiKey'   => 'APIKEY',
   'retry'    => 5, // default 5
]);

/*
 * --------------------------------------------------------------
 * Melakukan send sms
 *
 * @param string Phone Number
 * @param string Text
 *
 * @return object|bool
 * --------------------------------------------------------------
 */
 $result = $citcall->send('082123456789','Testing Citcall SMS API');

/*
 * --------------------------------------------------------------
 * Melakukan missed call
 *
 * @param string Phone Number
 * @param int    Gateway number (1-5)
 * @param bool   Asyncronous Missed Call (false by default)
 *
 * @return object|bool
 * --------------------------------------------------------------
 */
 $result = $citcall->call('082123456789', 1, false);

/*
 * --------------------------------------------------------------
 * Melakukan send sms otp
 *
 * @param string  Phone Number
 * @param string  Token
 * @param seconds Expires
 *
 * @return object|bool
 * --------------------------------------------------------------
 */
 $result = $citcall->sendOtp('082123456789','KODE123', 3600);

/*
 * --------------------------------------------------------------
 * Melakukan verifikasi otp
 *
 * @param string  Transaction ID (TRXID)
 * @param string  Phone Number
 * @param string  Token
 *
 * @return object|bool
 * --------------------------------------------------------------
 */
 $result = $citcall->verifyOtp(123,'082123456789','KODE123');

/*
 * --------------------------------------------------------------
 * Mendapatkan callback result
 *
 * @return object
 * --------------------------------------------------------------
 */
$result = $citcall->getCallback();
```

Ide, Kritik dan Saran
---------------------
Jika anda memiliki ide, kritik ataupun saran, anda dapat mengirimkan email ke [steevenz@stevenz.com](mailto:steevenz@steevenz.com). 
Anda juga dapat mengunjungi situs pribadi saya di [steevenz.com](http://steevenz.com)

Bugs and Issues
---------------
Jika anda menemukan bugs atau issue, anda dapat mempostingnya di [Github Issues](http://github.com/steevenz/citcall/issues).

Requirements
------------
- PHP 7.2+
- [Composer](https://getcomposer.org)
- [O2System Curl](http://github.com/o2system/curl)