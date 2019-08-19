<?php

namespace Foxworth42\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Okta extends AbstractProvider
{
    use BearerAuthorizationTrait;
    
    public $issuer = '';
    
    /**
     * Get authorization url to begin OAuth flow
     *
     * @link https://developer.okta.com/docs/reference/api/oidc/#authorize
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->issuer.'/v1/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @link https://developer.okta.com/docs/reference/api/oidc/#token
     * @param  array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->issuer.'/v1/token';
    }

    /**
     * Get provider url to fetch user details
     *
     * @link https://developer.okta.com/docs/reference/api/oidc/#userinfo
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->issuer.'/v1/userinfo';
    }

    protected function getAuthorizationParameters(array $options)
    {
        return parent::getAuthorizationParameters($options);
    }

    protected function getDefaultScopes()
    {
        // "openid" MUST be the first scope in the list.
        return [
            'openid',
            'email',
            'profile',
        ];
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        // @codeCoverageIgnoreStart
        if (empty($data['error'])) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $code = 0;
        $error = $data['error'];

        if (is_array($error)) {
            $code = $error['code'];
            $error = $error['message'];
        }

        throw new IdentityProviderException($error, $code, $data);
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new OktaUser($response);

        return $user;
    }
}
