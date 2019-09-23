# splunk-tracer-php

[![Latest Stable Version](https://poser.pugx.org/splunk/tracer/v/stable)](https://packagist.org/packages/splunk/tracer)
[![Circle CI](https://circleci.com/gh/splunk/splunk-tracer-php.svg?style=shield)](https://circleci.com/gh/splunk/splunk-tracer-php)
[![MIT license](http://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

The SplunkTracing distributed tracing library for PHP.

## Installation

```bash
composer require splunk/tracer
```

The `splunk/tracer` package is [available here on packagist.org](https://packagist.org/packages/splunk/tracer).

## Getting started

```php
<?php

require __DIR__ . '/vendor/autoload.php';

SplunkTracing::initGlobalTracer('examples/trivial_process', '{your_access_token}');

$span = SplunkTracing::startSpan("trivial/loop");
for ($i = 0; $i < 10; $i++) {
    $span->logEvent("loop_iteration", $i);
    echo "The current unix time is " . time() . "\n";
    usleep(1e5);
    $child = SplunkTracing::startSpan("child_span", array(parent => $span));
    usleep(2e5);
    $child->logEvent("hello world");
    $child->finish();
    usleep(1e5);
}
$span->finish();
```

See `lib/api.php` for detailed API documentation.

## Developer Setup

```
brew install composer
make install
make test
```