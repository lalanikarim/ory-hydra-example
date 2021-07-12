<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Ory\Hydra\Client\Configuration;
use \Ory\Hydra\Client\Api\AdminApi;

class LoginController extends Controller
{
  private Configuration $config;
  private AdminApi $admin;

  function __construct()
  {
    $this->config = new Configuration();
    $this->config->setHost(env('ORY_HYDRA_ADMIN','http://127.0.0.1:4445'));
    $this->admin = new AdminApi(null,$this->config);
  }

    //
  public function login()
  {
    $query = request()->query;
    $challenge = $query->get('login_challenge');

    $loginRequest = $this->admin->getLoginRequest($challenge);
    if($loginRequest->getSkip())
    {
      try{
        $result = $this->admin->acceptLoginRequest($loginRequest->getChallenge(),['subject' => $loginRequest->getSubject()]);

        return redirect($result->getRedirectTo());
      }
      catch(Exception $e)
      {
      }
    }
    
    return view('login')->with(['challenge'=>$challenge]);
  }

  public function authenticate(Request $request)
  {
    $challenge = $request->input('challenge');
    $username = $request->input('username');
    $password = $request->input('password');

    if($username !== $password)
    {
      return redirect()->route('login',['login_challenge'=>$challenge])->with(compact('challenge'));
    }

    try{
      $result = $this->admin->acceptLoginRequest($challenge, ['subject' => $username, 'remember' => false]);
      return redirect($result->getRedirectTo());
    }
    catch(Exception $e)
    {
      dd($e);
    }
  }

  public function consent()
  {
    if(!request()->query->has('consent_challenge') || empty(request()->query('consent_challenge')))
    {
      return "Expected a concent challenge to be sent out but received none.";
    }

    $challenge = request()->query('consent_challenge');

    $consentRequest = $this->admin->getConsentRequest($challenge);
    $requested_scope = $consentRequest->getRequestedScope();
    $user = $consentRequest->getSubject();
    $client = $consentRequest->getClient();
    if($consentRequest->getSkip())
    {
      try{
      $result = $this->admin->acceptConsentRequest($challenge,[
        'grant_scope' => $requested_scope,
        'grant_access_token_audience' => $consentRequest->getRequestedAccessTokenAudience(),
        'session' => [
          'access_token' => [
            'sub' => $user,
          ],
          'id_token' => [
            'givenname' => $user,
          ]
        ]
      ]);
      return redirect($result->getRedirectTo());
      }
      catch(Exception $e)
      {
        dd($e);
      }
    }

    $action = URL::to('/consent');

    return view('consent')->with(compact(['challenge','requested_scope','user','client','action']));
  }

  public function approval(Request $request)
  {
    $challenge = $request->input('challenge');
    $submit = $request->input('submit');
    if($submit === 'Deny access')
    {
      try{
        $result = $this->admin->rejectConsentRequest($challenge,[
          'error' => 'access_denied',
          'error_description' => 'The resource owner denied the request'
        ]);
        return redirect($result->getRedirectTo());
      }
      catch(Exception $e)
      {
        return $e->getTraceAsString();
      }
    }

    $grantScope = $request->input('grant_scope');

    try{
      $consentRequest = $this->admin->getConsentRequest($challenge);
      $result = $this->admin->acceptConsentRequest($challenge, [
        'grant_scope' => $grantScope,
        'session' => [
          'access_token' => [
            'sub' => $consentRequest->getSubject(),
          ],
          'id_token' => [
            'givenname' => $consentRequest->getSubject(),
          ]
        ],
        'remember' => false,
      ]);
      return redirect($result->getRedirectTo());
    }
    catch(Exception $e)
    {
      dd([$e,$grantScope]);
      return $e->getTraceAsString();
    }
  }

  public function logout()
  {
    if(!request()->query->has('logout_challenge'))
    {
      return "Expected a logout challenge to be set but received none!";
    }

    $challenge = request()->query('logout_challenge');
    try{
      $result = $this->admin->getLogoutRequest($challenge);
      return view('logout')->with(compact('challenge'));
    }
    catch(Exception $e)
    {
      dd($e);
    }
  }

  public function endsession(Request $request)
  {
    $challenge = $request->input('challenge');
    $logout = $request->input('logout');

    if("Yes" === $logout)
    {
      $result = $this->admin->acceptLogoutRequest($challenge);
      return redirect($result->getRedirectTo());
    }
    else
    {
      $this->admin->rejectLogoutRequest($challenge);
      return redirect("/");
    }
  }
}
