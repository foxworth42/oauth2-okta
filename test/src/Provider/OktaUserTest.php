<?php

namespace Foxworth42\OAuth2\Client\Test\Provider;

use Foxworth42\OAuth2\Client\Provider\OktaUser;
use PHPUnit\Framework\TestCase;

class OktaUserTest extends TestCase
{
    public function testUserDefaults()
    {
        // Mock
        $user = new OktaUser([
            'sub' => '12345',
            'email' => 'mock.name@example.com',
            'name' => 'mock name',
            'given_name' => 'mock',
            'family_name' => 'name',
            'picture' => 'mock_image_url',
            'hd' => 'example.com',
            'locale' => 'en',
        ]);

        $this->assertEquals(12345, $user->getId());
        $this->assertEquals('mock name', $user->getName());
        $this->assertEquals('mock', $user->getFirstName());
        $this->assertEquals('name', $user->getLastName());
        $this->assertEquals('en', $user->getLocale());
        $this->assertEquals('mock.name@example.com', $user->getEmail());
        $this->assertEquals('example.com', $user->getHostedDomain());
        $this->assertEquals('mock_image_url', $user->getAvatar());
    }

    public function testUserPartialData()
    {
        $user = new OktaUser([
            'sub' => '12345',
            'name' => 'mock name',
            'given_name' => 'mock',
            'family_name' => 'name',
        ]);

        $this->assertEquals(null, $user->getEmail());
        $this->assertEquals(null, $user->getHostedDomain());
        $this->assertEquals(null, $user->getAvatar());
        $this->assertEquals(null, $user->getLocale());
    }
}
