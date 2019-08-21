<?php

namespace Foxworth42\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class OktaUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['sub'];
    }

    /**
     * Get preferred display name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->response['name'];
    }

    /**
     * Get preferred first name.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->response['given_name'];
    }

    /**
     * Get preferred last name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->response['family_name'];
    }

    /**
     * Get locale.
     *
     * @return string|null
     */
    public function getLocale()
    {
        if (array_key_exists('locale', $this->response)) {
            return $this->response['locale'];
        }
        return null;
    }

    /**
     * Get email address.
     *
     * @return string|null
     */
    public function getEmail()
    {
        if (array_key_exists('email', $this->response)) {
            return $this->response['email'];
        }
        return null;
    }
    
    /**
     * Get preferred username.
     *
     * @return string|null
     */
    public function getPreferredUsername()
    {
        if (array_key_exists('preferred_username', $this->response)) {
            return $this->response['preferred_username'];
        }
        return null;
    }
    
    /**
     * Get timezone for user.
     *
     * @return string|null
     */
    public function getZoneInfo()
    {
        if (array_key_exists('zoneinfo', $this->response)) {
            return $this->response['zoneinfo'];
        }
        return null;
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
