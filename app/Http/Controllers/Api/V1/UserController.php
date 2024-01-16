<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use App\Http\Requests\UpdateUserRequest;
  use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

public function update(UpdateUserRequest $request, User $user)
{
    try {
        // Start a database transaction
        DB::beginTransaction();

        // Update the user attributes
        $user->update($request->only([
            'nom', 'prenom', 'phone', 'email', 'password', 'avatar',
            'statut', 'profession', 'adresse', 'num_cni', 'num_passport',
            'phone2', 'bank', 'preuve_fond'
        ]));

        // Update avatar if provided
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $file_name = time() . '.' . $request->avatar->extension();
            $destinationPath = public_path('avatars');
            $request->avatar->move($destinationPath, $file_name);
            $user->update(['avatar' => "avatars/$file_name"]);
        }

       if ($request->hasFile('preuve_fond') && $request->file('preuve_fond')->isValid()) {
            $file_name = time() . '.' . $request->preuve_fond->extension();
            $destinationPath = public_path('users');
            $request->preuve_fond->move($destinationPath, $file_name);
            $user->preuve_fond = "users/$file_name";
        }

        // Save the changes to the user model
        $user->save();

        // Commit the transaction
        DB::commit();

        // Fetch the updated user
        $user = $user->fresh();

        // Prepare success response
        $success = [
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'phone2' => $user->phone2,
            'avatar' => $user->avatar,
            'adresse' => $user->adresse,
            'profession' => $user->profession,
            'preuve_fond' => $user->preuve_fond,
            'num_cni' => $user->num_cni,
            'num_passport' => $user->num_passport,
            'phone' => $user->phone,
        ];

        return response()->json(['status' => true, 'message' => 'User updated successfully', 'data' => $success]);
    } catch (\Exception $e) {
        // Rollback the transaction in case of an exception
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
            'data' => [],
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
                    $success['profession'] = $user->profession;
                    $success['adresse'] = $user->adresse;
                    $success['avatar'] = $user->avatar;
        return $success;

    }
}
