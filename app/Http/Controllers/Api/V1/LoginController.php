<?php

namespace App\Http\Controllers\Api\V1;


use Auth;
use App\Http\Helpers\Helper;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Exception;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
     public function login(LoginRequest $request){

        if (!Auth::attempt($request->only(['telephone', 'password','statut' => 1]))){
            Helper::sendError('telephone ou password invalide');

        }
    
            $user = Auth::user();


            

            $success['token'] = $user->createToken('sunuNatte_app')->plainTextToken;
                $success['id'] = $user->id;
                $success['nom'] = $user->nom;
                $success['prenom'] = $user->prenom;
                $success['email'] = $user->email;
                $success['telephone'] = $user->telephone;
                $success['statut'] = $user->statut; 
         return response()->json($success);
  
    

    }
}
