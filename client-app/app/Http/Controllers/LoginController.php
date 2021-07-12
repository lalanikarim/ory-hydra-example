<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use \League\OAuth2\Client\Provider\GenericProvider;
use \Jumbojett\OpenIDConnectClient;

class LoginController extends Controller
{
  private GenericProvider $provider;  
  private OpenIDConnectClient $oidc;

  public function __construct()
  {
    $this->provider  = new GenericProvider([
      'clientId'=> env('OAUTH2_CLIENT_ID'),
      'clientSecret' => env('OAUTH2_CLIENT_SECRET'),
      'redirectUri' => 'http://127.0.0.1:8001/login',
      'urlAuthorize' => env('OAUTH2_AUTHORIZE_URL'),
      'urlAccessToken' => env('OAUTH2_ACCESS_TOKEN_URL'),
      'urlResourceOwnerDetails' => env('OAUTH2_RESOURCE_URL'),
      'scopes' => 'openid offline' 
    ]);

    $this->oidc = new OpenIDConnectClient(env('OAUTH2_ISSUER_URL'),env('OAUTH2_CLIENT_ID'),env('OAUTH2_CLIENT_SECRET'));
    $this->oidc->providerConfigParam([
      'token_endpoint' => env('OAUTH2_ACCESS_TOKEN_URL'),
      'auth_endpoint' => env('OAUTH2_AUTHORIZE_URL'),
#      'token_endpoint_auth_methods_supported' => ['client_secret_post'],
    ]);
    $this->oidc->addScope('openid');
    $this->oidc->addScope('offline');
    $this->oidc->setRedirectURL('http://127.0.0.1:8001/login');
  }
  
  public function login()
  {
    $provider = $this->provider;
    if(!request()->query->has('code'))
    {
      $authorizationUrl = $provider->getAuthorizationUrl();
      request()->session()->put('oauth2state',$provider->getState());
      return redirect($authorizationUrl);
    }
    else 
    {
      if(empty(request()->query('code'))||
        ((null !== request()->session()->get('oauth2state')) && 
          request()->query('state') !== request()->session()->get('oauth2state')
        )
      )
      {
        if(null !== request()->session()->get('oauth2state'))
        {
          request()->session()->remove('oauth2state');
        }
        return "Invalid state";
      }
      else
      {
        try {
          $accessToken = $provider->getAccessToken('authorization_code',['code'=> request()->query('code')]);
          $response = 'Access Token: ' . $accessToken->getToken() . '<br>' . 'Refresh Token: ' . $accessToken->getRefreshToken() . '<br>' . 'Expires in: ' . $accessToken->getExpires() . '<br>' . '<a href="'. env('OAUTH2_LOGOUT_URL') .'">Logout</a>';
          return $response;
        } 
        catch (IdentityProviderException $e)
        {
          dd($e);
        }
      }
    }
  }

  public function loginoidc()
  {
    $this->oidc->authenticate();
    $accessToken = $this->oidc->getAccessToken();
    $idToken = $this->oidc->getIdToken();
    $refreshToken = $this->oidc->getRefreshToken();
    $givenName = $this->oidc->requestUserInfo('givenname');
    $subject = $this->oidc->requestUserInfo('sub');
    $response = 'Given Name: ' . $givenName . '<br>' . 
      'Subject: ' . $subject . '<br>' .
      'Access Token: ' . $accessToken . '<br>' . 
      'Id Token:' . $idToken . '<br>'. 
      'Refresh Token: ' . $refreshToken . '<br>' . 
      'Expires in: ' . '<br>' . '<a href="/logout">Logout</a>';

    request()->session()->put('access_token',$accessToken);
    request()->session()->put('id_token',$idToken);
    request()->session()->put('refresh_token',$refreshToken);

    return $response;
  }

  public function logout()
  {
    if(request()->session()->has('access_token'))
    {
      $accessToken = request()->session()->get('access_token');
      $idToken = request()->session()->get('id_token');
      $this->oidc->setAccessToken($accessToken);

      $this->oidc->signOut($idToken, 'http://127.0.0.1:8001/');
    }
    return redirect("/");
  }

  public function index()
  {
    #$this->oidc->authenticate();
    if(request()->query->has('state') && request()->query('state') == '') {
      request()->session()->flush();
    }
    if(request()->session()->has('access_token'))
    {
      $accessToken = request()->session()->get('access_token');
      $idToken = request()->session()->get('id_token');
      $refreshToken = request()->session()->get('refresh_token');
    
      $this->oidc->setAccessToken($accessToken);
      

      $subject = $this->oidc->requestUserInfo('sub');
      $givenName = $this->oidc->requestUserInfo('givenname');
      $response = 'Given Name: ' . $givenName . '<br>' . 
        'Subject: ' . $subject . '<br>' .
        'Access Token: ' . $accessToken . '<br>' . 
        'Id Token:' . $idToken . '<br>'. 
        'Refresh Token: ' . $refreshToken . '<br>' . 
        'Expires in: ' . '<br>' . '<a href="/logout">Logout</a>';
      
      return $response;
    }
    else
    {
      return redirect('/login');
    }
  }
}
