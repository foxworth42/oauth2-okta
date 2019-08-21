<?php

namespace Foxworth42\OAuth2\Client\Test\Provider;

use Eloquent\Phony\Phpunit\Phony;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Foxworth42\OAuth2\Client\Provider\Okta as OktaProvider;
use League\OAuth2\Client\Provider\OktaUser;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;

class OktaTest extends TestCase
{
    /** @var OktaProvider */
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new OktaProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'issuer' => 'https://demo.oktapreview.com/oauth2/default'
        ]);
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);

        $this->assertContains('email', $query['scope']);
        $this->assertContains('profile', $query['scope']);
        $this->assertContains('openid', $query['scope']);

        $this->assertNotEmpty('state', $this->provider->getState());
    }

    public function testBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);

        $this->assertEquals('/oauth2/default/v1/token', $uri['path']);
    }

    public function testResourceOwnerDetailsUrl()
    {
        $token = $this->mockAccessToken();

        $url = $this->provider->getResourceOwnerDetailsUrl($token);

        $this->assertEquals('https://demo.oktapreview.com/oauth2/default/v1/userinfo', $url);
    }

    public function testUserData()
    {
        // Mock
        $response = [
            'sub' => '12345',
            'email' => 'mock.name@example.com',
            'name' => 'mock name',
            'given_name' => 'mock',
            'family_name' => 'name',
            'preferred_username' => 'mockusername',
            'zoneinfo' => 'America/Los_Angeles'
        ];

        $token = $this->mockAccessToken();

        $provider = Phony::partialMock(OktaProvider::class);
        $provider->fetchResourceOwnerDetails->returns($response);
        $google = $provider->get();

        // Execute
        $user = $google->getResourceOwner($token);

        // Verify
        Phony::inOrder(
            $provider->fetchResourceOwnerDetails->called()
        );

        $this->assertInstanceOf('League\OAuth2\Client\Provider\ResourceOwnerInterface', $user);

        $this->assertEquals(12345, $user->getId());
        $this->assertEquals('mock name', $user->getName());
        $this->assertEquals('mock', $user->getFirstName());
        $this->assertEquals('name', $user->getLastName());
        $this->assertEquals('mockusername', $user->getPreferredUsername());
        $this->assertEquals('America/Los_Angeles', $user->getZoneInfo());
        $this->assertEquals('mock.name@example.com', $user->getEmail());

        $user = $user->toArray();

        $this->assertArrayHasKey('sub', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('family_name', $user);
    }

    public function testErrorResponse()
    {
        // Mock
        $error_json = '{"error": {"code": 400, "message": "I am an error"}}';

        $response = Phony::mock('GuzzleHttp\Psr7\Response');
        $response->getHeader->returns(['application/json']);
        $response->getBody->returns($error_json);

        $provider = Phony::partialMock(OktaProvider::class);
        $provider->getResponse->returns($response);

        $google = $provider->get();

        $token = $this->mockAccessToken();

        // Expect
        $this->expectException(IdentityProviderException::class);

        // Execute
        $user = $google->getResourceOwner($token);

        // Verify
        Phony::inOrder(
            $provider->getResponse->calledWith($this->instanceOf('GuzzleHttp\Psr7\Request')),
            $response->getHeader->called(),
            $response->getBody->called()
        );
    }
    
    
    public function testCanChangeOktaApiVersion()
    {
        $this->provider = new OktaProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'issuer' => 'https://demo.oktapreview.com/oauth2/default',
            'apiVersion' => 'v2'
        ]);
        
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);
        
        $this->assertEquals('/oauth2/default/v2/token', $uri['path']);
    }

    /**
     * @return AccessToken
     */
    private function mockAccessToken()
    {
        return new AccessToken([
            'access_token' => 'mock_access_token',
        ]);
    }
}
