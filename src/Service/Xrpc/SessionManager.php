<?php

namespace Blugen\Service\Xrpc;

class SessionManager
{
    private array $session = [];

    public function setSession(array $session): void
    {
        $this->session = $session;
    }

    public function getSession(): array
    {
        return $this->session;
    }

    public function hasValidSession(): bool
    {
        return isset($this->session['accessJwt']) && isset($this->session['refreshJwt']);
    }

    public function getAccessToken(): ?string
    {
        return $this->session['accessJwt'] ?? null;
    }

    public function getRefreshToken(): ?string
    {
        return $this->session['refreshJwt'] ?? null;
    }

    public function clearSession(): void
    {
        $this->session = [];
    }

    public function updateFromResponse(array $responseData): void
    {
        $this->session = array_merge($this->session, $responseData);
    }
}