<?php

namespace App\Http\Controllers\Api\V1;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Twilio\Rest\Client;
use Illuminate\Support\Str;
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

        $code = rand(1111, 9999);; // Generate a random 4-digit code

        $user->update(['reset_code' => $code]);
        $this->sendSms($user->phone, $code);

        return response()->json(['message' => 'Reset code sent successfully']);
    }

    private function sendSms($to, $code)
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');
        $twilioPhoneNumber = config('services.twilio.from');

        $client = new Client($sid, $token);

        $client->messages->create(
            $to,
            [
                'from' => $twilioPhoneNumber,
                'body' => "Your reset code is: $code",
            ]
        );
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
