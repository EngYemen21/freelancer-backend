<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use App\Events\EventService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    // register a new user method
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Password reset link sent'])
            : response()->json(['error' => 'Unable to send reset link'], 400);
    }

    private function broker()
    {
        return Password::broker();
    }


    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $recaptchaResponse = $request->input('recaptcha');
        $recaptchaSecret = '6LfOcwEqAAAAAP2fEu1KSX-UpkfDtS7Pby0IMCSB';

      

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $info = array(
            'secret' => '6LfOcwEqAAAAAP2fEu1KSX-UpkfDtS7Pby0IMCSB',
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        );
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($info)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);



        $user = User::create([
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        try {
            $this->emailverify($user->email);


            $token = $user->createToken('auth_token')->plainTextToken;

            if ($token) {
                $cookie = cookie('token', $token, 60 * 24); // 1 day
                // $body = $response->json();
                $captcha = json_decode($result, TRUE);
                if ($result === FALSE) {
                    return response()->json(['error' => 'reCAPTCHA validation failed'], 422);
                }


                // if ($body['success']) {
                // reCAPTCHA validation passed, process the form
                // ...
                return response()->json([
                    'user' => new UserResource($user),
                    'token' => $token,
                    'message' => $captcha,
                ])->withCookie($cookie);
                // }
                // return response()->json(['message' => 'Form submitted successfully']);
                // } else {
                //     return response()->json(['error' => 'reCAPTCHA validation failed'], 422);
                // }
                // }
                // else {
                //         return response()->json(['error' => 'reCAPTCHA validation failed'], 422);
                //     }


            } else {
                return response()->json(['error' => 'Token generation failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function emailverify($email)
    {

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->emailverification();
            return response()->json(['status' => 'success', 'message' => 'Verify Your Email Address']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'User Not Found']);
        }
    }

    public function verifyEmail(Request $request)
    {
        $token = $request->input('token');

        $user = User::where('email_verification_token', $token)->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->email_verification_token = null;
            $user->email_verified = 1;
            $user->save();

            return response()->json(['status' => 'success', 'token' => $token, 'message' => 'Email verified successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Email already verified']);
    }
    //  public function resendVerifyEmail(Request $request)
    //  {
    //      if ($request->user()->hasVerifiedEmail()) {
    //         return response()->json(['status' => 'error', 'message' => 'Email already verified']);

    //      }

    //      $request->user()->emailverification();

    //      return response()->json([
    //          'message' => 'Verification email resent.',
    //          'status' => 'success'
    //      ]);
    //  }
    public function forgotpassword(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if ($user) {
            $password = Str::random(10);
            Mail::send([], [], function ($message) use ($email, $password) {
                $message->to($email)
                    ->subject("Reset Password")
                    ->html("<p>Use This New Password for you Email : </p><br/>" . $password);
            });
            User::where('email', $email)->update(['password' => Hash::make($password)]);
            return response()->json(['status' => 'success', 'message' => 'New Password send in your email']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'User Not Found']);
        }
    }

    public function changepassword(Request $request)
    {
        $userId = $request->userId;
        $currentPassword =  $request->cpassword;
        $newpassword = $request->npassword;

        $user = User::find($userId);
        if ($user) {
            if (Hash::check($currentPassword, $user->password)) {
                User::where('id', $userId)->update(['password' => Hash::make($newpassword)]);

                return response()->json(['status' => 'success', 'message' => 'Password Change Successfully']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Current Password Not Match']);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'User Not Found']);
        }
    }


    // login a user method
    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Email or password is incorrect!'
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        $cookie = cookie('token', $token, 60 * 24);
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token

        ])->withCookie($cookie);
    }

    // logout a user method
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $cookie = cookie()->forget('token');

        return response()->json([
            'message' => 'Logged out successfully!'
        ])->withCookie($cookie);
    }

    // get the authenticated user method
    public function user(Request $request)
    {
        return new UserResource($request->user());
    }
    public function index()
    {
        $users = User::where('is_banned', 0)->get();
        return response()->json($users);
    }
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user);
    }
    public function banUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->is_banned = true;
            $user->save();
            return response()->json(['message' => 'User has been banned'], 200);
        }
        return response()->json(['message' => 'User not found'], 404);
    }
    public function unBanUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->is_banned = false;
            $user->save();
            return response()->json(['message' => 'User has been unbanned']);
        }
        return response()->json(['message' => 'User not found']);
    }
    public function getBlockedUser()
    {
        $user = User::where('is_banned', true)->get();

        return response()->json(['user' => $user]);
    }
    public function update(Request $request, $id)
    {
        if ($request->input('firstName')) {
            $validatedData = $request->validate([

                'firstName' => 'required|string',
                'lastName' => 'string',
                'email' => 'required|email',
                'phone_number' => 'string',
                'city' => 'string',


            ]);
            $user = User::find($id);
            $user->firstName = $validatedData['firstName'];
            $user->lastName = $request->input('lastName');
            $user->email = $validatedData['email'];
            $user->phone_number = $validatedData['phone_number'];
            $user->city = $validatedData['city'];
            $user->date_of_birth = $request->input('date_of_birth');
            $user->save();

            return response()->json(['message' => 'تم تحديث بيانات المستخدم بنجاح']);
        } else if ($request->input('username')) {
            $validatedData = $request->validate([

                'username' => 'required|string',

                'field' => 'string',
                'user_type' => 'string',
                'Specialization' => 'string'

            ]);
            $user = User::find($id);
            $user->username = $validatedData['username'];
            $user->field = $validatedData['field'];
            $user->user_type = $validatedData['user_type'];
            $user->Specialization = $validatedData['Specialization'];
            $user->save();

            return response()->json(['message' => 'تم تحديث بيانات المستخدم بنجاح']);
        }
    }

    public function UpdateUSerByAdmin(Request $request, $id)
    {
        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            // قم بإضافة باقي الحقول التي تحتاج إلى التحقق هنا
        ]);

        $user = User::find($id);

        if ($user) {
            $user->firstName = $request->firstName;
            $user->lastName = $request->lastName;
            $user->email = $request->email;
            // قم بتحديث باقي الحقول هنا
            $user->save();

            return response()->json(['message' => 'User updated successfully'], 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
