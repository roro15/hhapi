<?php

namespace entity;

abstract class SecureEntity extends Entity {
    protected function getResponse() {
        return $this->client->secureRequest(
                $this->getUri(),
                $this->getMethod(),
                $this->getHeaders(),
                $this->getContent()
        );
    }
} 