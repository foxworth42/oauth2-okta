<?php

namespace Foxworth42\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Okta extends AbstractProvider
{
    use BearerAuthorizationTrait;
    
    protected $issuer = '';
    protected $apiVersion = 'v1';
    
    
    public function getBaseApiUrl()
    {
        return $this->issuer . '/' . $this->apiVersion;
    }
    
    /**
     * Get authorization url to begin OAuth flow
     *
     * @link https://developer.okta.com/docs/reference/api/oidc/#authorize
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->getBaseApiUrl().'/authorize';
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
        return $this->getBaseApiUrl().'/token';
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
        return $this->getBaseApiUrl().'/userinfo';
    }

    protected function getAuthorizationParameters(array $options)
    {
        return parent::getAuthorizationParameters($options);
    }

    /**
     * @retrun array
     **/
    protected function getDefaultScopes()
    {
        return [
            'openid',
            'email',
            'profile'
        ];
    }

    /**
     * @retrun string
     **/
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * @retrun void
     **/
    protected function checkResponse(ResponseInterface $response, $data)
    {
        // @codeCoverageIgnoreStart
        if (empty($data['error'])) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $code = $response->getStatusCode();
        $error = $data['error'];

        if (is_array($error)) {
            $code = $error['code'];
            $error = $error['message'];
        }

        throw new IdentityProviderException($error, $code, $data);
    }

    /**
     * @retrun ResourceOwnerInterface
     **/
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new OktaUser($response);

        return $user;
    }
}
