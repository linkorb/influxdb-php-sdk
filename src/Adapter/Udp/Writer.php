<?php
namespace InfluxDB\Adapter\Udp;

use InfluxDB\Adapter\WriterTrait;
use InfluxDB\Adapter\Udp\Options;
use InfluxDB\Adapter\WritableInterface;

class Writer implements WritableInterface
{
    use WriterTrait;

    private $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }
    public function send(array $message)
    {
        $message = $this->messageToLineProtocol($message, $this->getOptions()->getTags());

        $this->write($message);
    }

    public function write($message)
    {
        // Create a handler in order to handle the 'Host is down' message
        set_error_handler(function() {
            // Suppress the error, this is the UDP adapter and if we can't send
            // it then we shouldn't inturrupt their application.
        });

        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($socket, $message, strlen($message), 0, $this->getOptions()->getHost(), $this->getOptions()->getPort());
        socket_close($socket);

        // Remove our error handler.
        restore_error_handler();
    }
}
