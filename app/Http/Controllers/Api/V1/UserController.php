<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Document;
use App\Models\Tontine;
use App\Http\Controllers\Api\V1\AdhesionController;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
//use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{

    


    public function update(Request $request, User $user)
    {
        try {

            $user->update($request->all());
            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                /**if ($user->avatar) {
                    Storage::delete($user->avatar);
                } ***/

                $file_name = time() . '.' . $request->avatar->extension();
                $destinationPath = public_path('avatars');
                $request->avatar->move($destinationPath, $file_name);
                $path = "avatars/$file_name";
                $user->avatar = $path;
                $user->save();
           }

            // Prepare success response
            $success = [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'phone2' => $user->phone2,
                'avatar' => $user->avatar,
                'adresse' => $user->adresse,
                'profession' => $user->profession,
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
    public function update_pass(Request $request)
    {
        try {

            $request->validate([
                'current_password' => ['required', new MatchOldPassword],
                'new_password' => ['required'],
                'new_confirm_password' => ['same:new_password'],
            ]);

            $user = User::find(auth()->user()->id)->update(['password' => Hash::make($request->new_password)]);
            return response()->json(['message' => 'password updated successfully'], 201);

        } catch (\Exception $e) {

            return response()->json([

                'statut' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }

    }

    public function markasRead(Request $request)
    {

        $request->user()->notifications()->update(['read_at' => now()]);
    }
    public function read(Request $request, $id)
    {
        return $request->user()->notifications()->find($id)->update(['read_at' => now()]);
    }

    public function list(Request $request)
    {

        $notificationData = [];

        /* @var User */
        $user = $request->user();
        $notifications= $user->unreadNotifications;
   
        return response()->json([$notifications]);

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
        $success['completeness'] = app(\App\Services\ProfileCompletenessService::class)
            ->inspect($user, $user->documents()->count());

        return $success;
    }

    public function completeness(Request $request)
    {
        $user = $request->user();

        return response()->json(
            app(\App\Services\ProfileCompletenessService::class)
                ->inspect($user, $user->documents()->count())
        );
    }



    public function show(Request $request, User $user)
    {
        $users = User::find($user);

        if (!$users) {
            return response()->json(['message' => 'user not found'], 404);
        }
        $success = [
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'phone2' => $user->phone2,
            'avatar' => $user->avatar,
            'adresse' => $user->adresse,
            'profession' => $user->profession,
            'num_cni' => $user->num_cni,
            'num_passport' => $user->num_passport,
            'phone' => $user->phone,
        ];
        return response()->json($success);
    }


    public function demande(Request $request, Tontine $tontine)
    {
        $request->merge(['tontine_id' => $tontine->id]);

        return app(AdhesionController::class)->store($request);
    }
}