<?php

namespace App\Http\Controllers\Api\V1;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ForgotPasswordController extends Controller
{
    public function sendResetCode(Request $request)
    {
        $this->validate($request, ['phone' => 'required|exists:users,phone']);

        $user = User::where('phone', $request->input('phone'))->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $code = rand(1111, 9999); // Generate a random 4-digit code

        $user->update(['reset_code' => $code]);
        app(\App\Services\SmsGateway::class)->send(
            $user->phone,
            'Votre code Sunu Natt : '.$code
        );

        return response()->json(['message' => 'Reset code sent successfully']);
    }


    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required|exists:users,phone',
            'code' => 'required',
            'password' => 'required|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:4'
        ]);

        $tl =  Auth::user();

        $user = User::where('phone', $request->input('phone'))
            ->where('reset_code', $request->input('code'))
            ->first();

        if ($user) {
            $user->update([
                'password' => bcrypt($request->input('password')),
                'reset_code' => null,
            ]);

            return response()->json(['message' => 'Password reset successfully']);
        }

        return response()->json(['error' => 'Invalid code'], 422);
    }
}
