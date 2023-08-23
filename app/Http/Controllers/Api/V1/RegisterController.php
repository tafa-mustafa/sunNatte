<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
       public function register(Request $request)
    {
        
    
        $validator = Validator::make($request->all(),[

            'nom' => 'required',
            'prenom' => 'required',
            'genre'        => 'required',
            'adresse'       => 'required',
            'date_naissance' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6',
            'telephone'=> 'required|unique:users,telephone',
            'role_id' =>'',

        ]);

        if($validator->fails()){

            $response =[
                'success' => false,
                'message' => $validator->errors()
            ];

            return response()->json($response, 400);
        }

       

        $user = User::create([
            
            'nom'               => $request->nom,
            'prenom'               => $request->prenom,
            'genre'             => $request->genre,
            'adresse'            => $request->adresse,
            'date_naissance'    => $request -> date_naissance,
            'email'              => $request->email,
            'password'           => bcrypt($request->password),
            'telephone'         =>$request->telephone,
            'role_id'       =>$request->role_id= 3
            
            ]);

        $success['token'] = $user->createToken('sunuNatte_app')->plainTextToken;
        $success['nom'] = $user->nom;
        $success['id'] = $user->id;
        $success['email'] = $user->email;
        $success['telephone'] = $user->telephone;
        $success['statut'] = $user->statut; 

        $response =[
      
            'message' => 'Inscription reussit'
        ];


        return response()->json($response, 200);

     

       
    }

}
