<?php

namespace Foxworth42\OAuth2\Client\Test\Provider;

use Foxworth42\OAuth2\Client\Provider\Okta;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;

class OktaTest extends TestCase
{
    public function testAuthorizationUrl()
    {
        // Arrange
        $oktaProvider = $this->getOktaProvider();

        // Act
        $url = $oktaProvider->getAuthorizationUrl();

        // Assert
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);

        $this->assertContains('email', $query['scope']);
        $this->assertContains('profile', $query['scope']);
        $this->assertContains('openid', $query['scope']);

        $this->assertNotEmpty('state', $oktaProvider->getState());
    }

    public function testBaseAccessTokenUrl()
    {
        // Arrange
        $oktaProvider = $this->getOktaProvider();

        // Act
        $url = $oktaProvider->getBaseAccessTokenUrl([]);

        // Assert
        $uri = parse_url($url);

        $this->assertEquals('/oauth2/default/v1/token', $uri['path']);
    }

    public function testResourceOwnerDetailsUrl()
    {
        // Arrange
        $oktaProvider = $this->getOktaProvider();
        $token = $this->mockAccessToken();

        // Act
        $url = $oktaProvider->getResourceOwnerDetailsUrl($token);

        // Assert
        $this->assertEquals('https://demo.oktapreview.com/oauth2/default/v1/userinfo', $url);
    }

    public function testUserData()
    {
        // Arrange
        $response = [
            'sub' => '12345',
            'email' => 'mock.name@example.com',
            'name' => 'mock name',
            'given_name' => 'mock',
            'family_name' => 'name',
            'preferred_username' => 'mockusername',
            'zoneinfo' => 'America/Los_Angeles'
        ];
        $mockGuzzleResponses = new MockHandler([
            new Response(200, [], json_encode($response))
        ]);
        $mockGuzzleHandler = HandlerStack::create($mockGuzzleResponses);
        $guzzleClient = new Client(['handler' => $mockGuzzleHandler]);

        $token = $this->mockAccessToken();

        $oktaProvider = new Okta([], ["httpClient" => $guzzleClient]);

        // Act
        $user = $oktaProvider->getResourceOwner($token);

        // Assert
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
        // Arrange
        $error_json = '{"error": {"code": 400, "message": "I am an error"}}';
        $mockGuzzleResponses = new MockHandler([
            new Response(400, [], $error_json)
        ]);
        $mockGuzzleHandler = HandlerStack::create($mockGuzzleResponses);
        $guzzleClient = new Client(['handler' => $mockGuzzleHandler]);

        $token = $this->mockAccessToken();

        $oktaProvider = new Okta([], ["httpClient" => $guzzleClient]);

        // Assert
        $this->expectException(IdentityProviderException::class);

        // Act
        $oktaProvider->getResourceOwner($token);
    }
    
    
    public function testCanChangeOktaApiVersion()
    {
        // Arrange
        $oktaProvider = new Okta([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'issuer' => 'https://demo.oktapreview.com/oauth2/default',
            'apiVersion' => 'v2'
        ]);

        // Act
        $url = $oktaProvider->getBaseAccessTokenUrl([]);

        // Assert
        $uri = parse_url($url);
        
        $this->assertEquals('/oauth2/default/v2/token', $uri['path']);
    }

    private function mockAccessToken(): AccessToken
    {
        return new AccessToken([
            'access_token' => 'mock_access_token',
        ]);
    }

    protected function getOktaProvider(): Okta
    {
        return new Okta([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'issuer' => 'https://demo.oktapreview.com/oauth2/default'
        ]);
    }
}
