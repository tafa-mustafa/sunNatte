<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends Controller
{
     public function update(Request $request, $id)
    {

        
         try {

           
                    $validatedData= Validator::make($request->all(),[

                        'nom' => '',
                        'prenom'        => '',
                        'phone' => 'required|phone|unique:users,id,' .$request->user()->id,                    
                        'avatar' => ''
                    ]) ;       
                    if($validatedData->fails()){

                        $error =$validatedData->errors()->all()[0];

                        return response()->json(['statut'=>'false', 'message'=> $error, 'data'=>[]], 422);
                    }
                    else{

                    $user = User::find($id);

                    $user-> nom= $request->nom;
                    $user-> prenom= $request->prenom;
                    $user-> date_naissance= $request->date_naissance;
                    $user-> phone= $request->phone;
                    $user-> password= bcrypt($request->password);


                    if($request->avatar && $request->avatar->isValid()){

                        $file_name =time(). '.'.$request->avatar->extension();
                        $destinationPath = public_path('avatars');
                        $request->avatar->move($destinationPath , $file_name);
                        $path = "public/avatars/$file_name";
                        $user->avatar = $path;
                    }

                    
                    $user->update();
                    $success['nom'] = $user->nom;
                    $success['prenom'] = $user->prenom;
                    $success['phone'] = $user->phone;
                    $success['role'] = $user->role->name;
                    $success['avatar'] = $user->avatar;

                    return response($success);
                } 
             }
         catch(\Exception $e){

            return response()->json([

                'statut'=> false,
                    'message'=> $e->getMessage() ,
                    'data'=>[]
            ], 500);

          }


    }
    public function logout(Request $request)

    {
        $user = Auth::user();
      
        $user->tokens()->delete();
        $success['status'] = "logged out";
        return response()->json($success);
    }
public function update_pass(Request $request){
 try {

      $request->validate([
                    'current_password' => ['required', new MatchOldPassword],
                    'new_password' => ['required'],
                    'new_confirm_password' => ['same:new_password'],
            ]);

      $user=  User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
        return response()->json(['message' => 'password updated successfully'],201);

 } catch(\Exception $e){

    return response()->json([

        'statut'=> false,
            'message'=> $e->getMessage() ,
            'data'=>[]
    ], 500);
 }
                   
     }



public function moi(Request $request)
    {
        $user = $request->user();
        $success['nom'] = $user->nom;
                    $success['prenom'] = $user->prenom;
                    $success['phone'] = $user->phone;
                    $success['avatar'] = $user->avatar;
        return $success;

    }
}
