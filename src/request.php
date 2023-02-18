<?php

class request {

    public array $server;
    public array $get;
    public array $headers;

    function __construct(
        array $server,
        array $get,
        array $headers
    ) {
        $this->server = $server;
        $this->get = $get;
        $this->headers = $headers;
    }

    function method() {
        return $this->server['REQUEST_METHOD'];
    }
}
