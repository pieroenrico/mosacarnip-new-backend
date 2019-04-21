<?php

namespace App\Http\Controllers\API;

use App\Admin\PasswordReset;
use App\Admin\VerifyUser;
use App\Http\Resources\UserResource;
use App\Mail\ActivateMail;
use App\Mail\ForgotMail;
use App\Mail\NotifyRegistration;
use App\Mail\ResetMail;
use App\Mail\WelcomeMail;
use App\Models\Admin\Subscription;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OAuthController extends Controller
{
    public function login(Request $request)
    {
        $http = new Client;
        try {
            $response = $http->post(config('services.passport.login_endpoint'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('services.passport.client_id'),
                    'client_secret' => config('services.passport.client_secret'),
                    'username' => $request->username,
                    'password' => $request->password,
                ]
            ]);
            return $this->success(json_decode($response->getBody()->getContents()));
        }
        catch ( BadResponseException $e )
        {
            if ( $e->getCode() == 400)
            {
                return $this->error([
                    'message' => 'Invalid Request. Please enter a username or password.',
                    'errors' => []
                ], $e->getCode());
            }
            elseif( $e->getCode() == 401 )
            {
                return $this->error([
                    'message' => 'Usuario o contraseña erróneos. Intente nuevamente.',
                    'errors' => []
                ], $e->getCode());
            }
            elseif( $e->getCode() == 403 )
            {
                return $this->error([
                    'message' => 'You have not verified your email. Please verify before logging in.',
                    'errors' => []
                ], $e->getCode());
            }
            return $this->error([
                'message' => 'User not found. Please try again.',
                'errors' => []
            ], $e->getCode());
        }

    }

    public function logout()
    {

        auth()->user()->tokens->each( function ($token, $key) {
            $token->delete();
        });

        return $this->success([
            'message' => 'Logged out successfully',
        ]);
    }




    private function success($body, $code = 200)
    {
        return response()->json([
            'success' => true,
            'body' => $body,
        ], $code);
    }

    private function error($body, $code)
    {
        return response()->json([
            'success' => false,
            'body' => $body,
        ], $code);
    }


}
