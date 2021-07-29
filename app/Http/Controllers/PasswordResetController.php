<?php



namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Validator;
class PasswordResetController extends Controller
{
    /**
     * Create token password reset
     *
     * @param  [string] email
     * @return [string] message
     */
    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user)
            return response()->json([
                'message' => __('passwords.user')
            ], 404);

        $passwordReset = PasswordReset::updateOrCreate(['email' => $user->email], [
            'email' => $user->email,
            'token' => str_random(60)
        ]);

        if ($user && $passwordReset)
            $user->notify(new PasswordResetRequest($passwordReset->token));

        return response()->json([
            'message' => __('passwords.sent')
        ]);
    }

    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return [string] message
     * @return [json] passwordReset object
     */
    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset)
        {
            flash()->overlay('Invalid token', ' ');
            return view('errorpage');
        }
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            flash()->overlay('Invalid token', ' ');
            return view('errorpage');
        }
        return view('welcome', ['token' => $token,'email'=>$passwordReset["email"]]);
    }

    public function reset(Request $request)
    {
        $validator= Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6|confirmed',
            'token' => 'required|string'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset){
            flash()->overlay('Invalid token', ' ');
            return view('errorpage');
        }
        $user = User::where('email', $passwordReset->email)->first();

        if (!$user){
            flash()->overlay('We can\'t find a user with that e-mail address.', ' ');
            return view('errorpage');
        }

        $user->password = bcrypt($request->password);
        $user->save();

        $passwordReset->delete();
        flash('Your password has been changed successfully')->success();
       // flash('Your password has been changed successfully')->success();
        return view('welcome', ['token' => "",'email'=>"","success"=>true]);
    }

}

