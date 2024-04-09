<?php

namespace App\Messages;

class EskizMessage
{
    public string $content;

    public ?string $from = null;

    public string $type = '0';

    /*
     * callback url
     */
    public ?string $statusCallback = null;

    public string $clientReference = '';

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * @return $this
     */
    public function content(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return $this
     */
    public function from(string $from): static
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return $this
     */
    public function unicode(): static
    {
        $this->type = '1';

        return $this;
    }

    /**
     * Set the client reference (up to 40 characters).
     *
     * @return $this
     */
    public function clientReference(string $clientReference): static
    {
        $this->clientReference = $clientReference;

        return $this;
    }

    /**
     * @return $this
     */
    public function statusCallback(string $callback): static
    {
        $this->statusCallback = $callback;

        return $this;
    }
}
