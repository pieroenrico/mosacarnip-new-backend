<?php

namespace App\Http\Controllers\API;

use App\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Response;
use \Laravel\Passport\Http\Controllers\AccessTokenController as ATC;

class AccessTokenController extends ATC
{
    public function issueToken(ServerRequestInterface $request)
    {
        try {
            //get username (default is :email)
            $username = $request->getParsedBody()['username'];

            //get user
            //change to 'email' if you want
            $user = User::where('email', '=', $username)->first();

            if( ! $user )
                throw new OAuthServerException('User not found', 6, 'invalid_credentials', 401);

            // if( ! $user->verified )
            //    throw new OAuthServerException('User is not verified.', 6, 'not_verified', 401);

            //generate token
            $tokenResponse = parent::issueToken($request);

            //convert response to json string
            $content = $tokenResponse->getContent();

            //convert json to array
            $data = json_decode($content, true);

            //if(isset($data["error"]) || ! $user->active)
            //    throw new OAuthServerException('The user credentials were incorrect.', 6, 'invalid_credentials', 401);
            if(isset($data["error"]))
                throw new OAuthServerException('The user credentials were incorrect.', 6, 'invalid_credentials', 401);

            //add access token to user
            $user = collect($user);
            $user->put('access_token', $data['access_token']);

            return response()->json([
                'token_type' => 'Bearer',
                'expires_in' => $data['expires_in'],
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
            ]);
        }
        catch (ModelNotFoundException $e) { // email notfound
            //return error message
            return response(["message" => "User not found"], 400);
        }
        catch (OAuthServerException $e) { //password not correct..token not granted
            //return error message
            if($e->getErrorType() == "not_verified")
            {
                return response(["message" => "The user was not verified.', 6, 'not_verified"], 403);
            }
            elseif ($e->getErrorType() == "invalid_credentials" )
            {
                return response(["message" => "The user credentials were incorrect.', 6, 'invalid_credentials"], 401);
            }
        }
        catch (Exception $e) {
            ////return error message
            return response(["message" => "Internal server error"], 500);
        }
    }
}
