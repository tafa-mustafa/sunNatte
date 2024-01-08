<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use Validator;
use App\Models\User;
use App\Models\Number;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
       public function register(Request $request)
    {
        
    
        $validator = Validator::make($request->all(),[

            'nom' => 'required',
            'prenom' => 'required',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:4',
            'phone'=> 'required|unique:users,phone',
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
            'email'              => $request->email,
            'password'           => bcrypt($request->password),
            'phone'         =>$request->phone,
            'role_id'       =>$request->role_id= 2
            
            ]);

        $success['token'] = $user->createToken('sunuNatte_app')->plainTextToken;
        $success['nom'] = $user->nom;
        $success['id'] = $user->id;
        $success['email'] = $user->email;
        $success['phone'] = $user->phone;
        $success['statut'] = $user->statut; 

        $response =[
      
            'message' => 'Inscription reussit'
        ];


        return response()->json($response, 200);

     

       
    }
     public function mobile(Request $request)
{
    $code = rand(1111, 9999);

    $phoneNumber = $request->phone; // Use $request->phone instead of $request->telephone

    $user = new Number;
    $user->phone = $phoneNumber;
    $user->code_verification = $code;
    $user->save();

    $account_sid = config("services.twilio.sid");
    $auth_token = config("services.twilio.auth_token");
    $twilio_number = config("services.twilio.from");

    $client = new Client($account_sid,  $auth_token);

    try {
        $msg = 'Verify your code: ' . $code;

        $message = $client->messages->create(
            $phoneNumber,
            [
                'from' => $twilio_number, // Use the Twilio number from the configuration
                'body' => "$msg !",
            ]
        );

        $response = [
            'success' => true,
            'data' => $phoneNumber,
            'message' => 'SMS Sent Successfully',
        ];

        return response()->json($response, 200);
    } catch (\Exception $e) {
        $response = [
            'success' => false,
            'message' => 'SMS Sending Failed: ' . $e->getMessage(),
        ];

        return response()->json($response, 400);
    }
}


    public function verify(Request $request){


        $check = Number::where('code_verification', $request->code_verification)->first();

        if ($check) {
            $check->statut = 1;
            $check->code_verification = "ok";
            $check->save();
        $response =[
                'success' => true,
                'data' => 'Verification done for'. "  ". $check->telephone,
                'message' => 'code  correct'
            ];
            
            return response()->json($response, 200);

        }
        else{


            $response =[
                'success' => false,
                'message' => 'code  Incorrect'
            ];
            
            return response()->json($response, 400);
        }
    }


   


}
