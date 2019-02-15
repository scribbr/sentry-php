--TEST--
Test that when restore_error_handler is called when we are handling an error, out handler doesn't get unregistered because: https://bugs.php.net/63206
--FILE--
<?php

namespace Sentry\Tests;

use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\State\Hub;
use Sentry\Transport\TransportInterface;

error_reporting(-1);

$vendor = __DIR__;

while (!file_exists($vendor . '/vendor')) {
    $vendor = \dirname($vendor);
}

require $vendor . '/vendor/autoload.php';

set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
    echo "I handle errors!\n";
});

$transport = new class implements TransportInterface {
    public function send(Event $event): ?string
    {
        set_error_handler(static function () {});
        restore_error_handler();

        echo "Transport called\n";

        return null;
    }
};

$client = ClientBuilder::create()
    ->setTransport($transport)
    ->getClient();

Hub::getCurrent()->bindClient($client);

@trigger_error('foo', E_USER_DEPRECATED);
@trigger_error('bar', E_USER_DEPRECATED);

?>
--EXPECTF--
Transport called
I handle errors!
Transport called
I handle errors!
