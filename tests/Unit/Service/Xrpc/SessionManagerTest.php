<?php

namespace Blugen\Tests\Unit\Service\Xrpc;

use Blugen\Service\Xrpc\SessionManager;
use Blugen\Tests\TestCase;

class SessionManagerTest extends TestCase
{
    private SessionManager $sessionManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionManager = new SessionManager();
    }

    public function test_set_and_get_session(): void
    {
        $session = [
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token',
            'handle' => 'test.user'
        ];

        $this->sessionManager->setSession($session);

        $this->assertEquals($session, $this->sessionManager->getSession());
    }

    public function test_get_session_returns_empty_array_by_default(): void
    {
        $this->assertEquals([], $this->sessionManager->getSession());
    }

    public function test_has_valid_session_returns_true_with_both_tokens(): void
    {
        $session = [
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token'
        ];

        $this->sessionManager->setSession($session);

        $this->assertTrue($this->sessionManager->hasValidSession());
    }

    public function test_has_valid_session_returns_false_without_access_token(): void
    {
        $session = [
            'refreshJwt' => 'refresh-token'
        ];

        $this->sessionManager->setSession($session);

        $this->assertFalse($this->sessionManager->hasValidSession());
    }

    public function test_has_valid_session_returns_false_without_refresh_token(): void
    {
        $session = [
            'accessJwt' => 'access-token'
        ];

        $this->sessionManager->setSession($session);

        $this->assertFalse($this->sessionManager->hasValidSession());
    }

    public function test_has_valid_session_returns_false_with_empty_session(): void
    {
        $this->assertFalse($this->sessionManager->hasValidSession());
    }

    public function test_get_access_token_returns_token(): void
    {
        $session = [
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token'
        ];

        $this->sessionManager->setSession($session);

        $this->assertEquals('access-token', $this->sessionManager->getAccessToken());
    }

    public function test_get_access_token_returns_null_when_not_set(): void
    {
        $this->assertNull($this->sessionManager->getAccessToken());
    }

    public function test_get_refresh_token_returns_token(): void
    {
        $session = [
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token'
        ];

        $this->sessionManager->setSession($session);

        $this->assertEquals('refresh-token', $this->sessionManager->getRefreshToken());
    }

    public function test_get_refresh_token_returns_null_when_not_set(): void
    {
        $this->assertNull($this->sessionManager->getRefreshToken());
    }

    public function test_clear_session_removes_all_data(): void
    {
        $session = [
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token',
            'handle' => 'test.user'
        ];

        $this->sessionManager->setSession($session);
        $this->assertEquals($session, $this->sessionManager->getSession());

        $this->sessionManager->clearSession();

        $this->assertEquals([], $this->sessionManager->getSession());
        $this->assertFalse($this->sessionManager->hasValidSession());
        $this->assertNull($this->sessionManager->getAccessToken());
        $this->assertNull($this->sessionManager->getRefreshToken());
    }

    public function test_update_from_response_merges_data(): void
    {
        $initialSession = [
            'accessJwt' => 'old-access',
            'refreshJwt' => 'old-refresh',
            'handle' => 'test.user'
        ];

        $responseData = [
            'accessJwt' => 'new-access',
            'did' => 'did:plc:123456'
        ];

        $expectedSession = [
            'accessJwt' => 'new-access',  // Updated
            'refreshJwt' => 'old-refresh',  // Preserved
            'handle' => 'test.user',        // Preserved
            'did' => 'did:plc:123456'       // Added
        ];

        $this->sessionManager->setSession($initialSession);
        $this->sessionManager->updateFromResponse($responseData);

        $this->assertEquals($expectedSession, $this->sessionManager->getSession());
    }

    public function test_update_from_response_overwrites_existing_keys(): void
    {
        $initialSession = [
            'accessJwt' => 'old-access',
            'handle' => 'old.handle'
        ];

        $responseData = [
            'accessJwt' => 'new-access',
            'handle' => 'new.handle'
        ];

        $this->sessionManager->setSession($initialSession);
        $this->sessionManager->updateFromResponse($responseData);

        $this->assertEquals('new-access', $this->sessionManager->getAccessToken());
        $this->assertEquals('new.handle', $this->sessionManager->getSession()['handle']);
    }

    public function test_update_from_response_with_empty_session(): void
    {
        $responseData = [
            'accessJwt' => 'new-access',
            'refreshJwt' => 'new-refresh'
        ];

        $this->sessionManager->updateFromResponse($responseData);

        $this->assertEquals($responseData, $this->sessionManager->getSession());
        $this->assertTrue($this->sessionManager->hasValidSession());
    }
}
