# splunk-tracer-php

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

This library is the Splunk binding for [OpenTracing](http://opentracing.io/). See the [OpenTracing PHP API](https://github.com/opentracing/opentracing-php) for additional detail.

## License

The Splunk Tracer for PHP is licensed under the MIT License. Details can be found in the LICENSE file.

### Third-party libraries

This is a fork of the PHP tracer from Lightstep, which is also licensed under the MIT License. Links to the original repository and license are below:

* [lightstep-tracer-php][lightstep]: [MIT][lightstep-license]

[lightstep]:                      https://github.com/lightstep/lightstep-tracer-php
[lightstep-license]:              https://github.com/lightstep/lightstep-tracer-php/blob/master/LICENSE