<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OtpController
{
    /**
     * Generate and send an OTP to the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function generateAndSend(Request $request)
    {
        $request->validate([
            'mobile' => 'required|exists:users,mobile',
        ], [
            'mobile.exists' => 'This mobile number is not registered.',
        ]);

        // Find the user
        $user = User::where('mobile', $request->mobile)->first();

        //delete expired otps
        Otp::where('otp_expires_at', '<', now())->delete();

        $otpRecord = Otp::where('mobile', $user->mobile)
            ->where('otp_expires_at', '>=', now())
            ->first();

        // Generate OTP (6-digit numeric) and if not expired resend
        $otp = $otpRecord ? $otpRecord->otp : mt_rand(100000, 999999);

        if (!$otpRecord) {
            // Store OTP in the database with expiration time
            $otpRecord = Otp::create([
                'mobile' => $user->mobile,  // Mobile number for the OTP
                'otp' => $otp, // The generate random OTP value
                'otp_expires_at' => Carbon::now()->addMinutes(5),  // OTP expires after 5 minutes
            ]);
        }

        return response()->json([
            'otp' => $otpRecord,
            'success' => true,
        ], 200);

        $otpResult = $this->sendOTPtoSender($otp, $user->mobile);

        if ($otpResult) {
            return response()->json([
                'message' => 'OTP sent to your mobile.',
                'otp_expires_at' => $otpRecord->otp_expires_at,
                'success' => true,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Error inserting otp to otp sender',
                'success' => false,
            ], 200);
        }
    }

    /**
     * Verify the OTP entered by the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string']);

        $otpRecord = Otp::where('mobile', $request->mobile)
            ->where('otp_expires_at', '>=', now())
            ->where('otp', $request->otp)
            ->first();

        $user = User::where('mobile', $request->mobile)->first();

        if (!$otpRecord) {
            return response()->json([
                'message' => 'Invalid or expired OTP.',
                'success' => false
            ]);
        }

        Auth::guard('web')->login($user);

        $token = $user->createToken($user->token . 'Auth-Token')->plainTextToken;
        // $user->tokens->last()->update(['expires_at' => now()->addDay()]);
        $user->tokens->last()->update(['expires_at' => now()->addDay()]);

        // Mark OTP as used (or delete it)
        $otpRecord->delete();

        return response()->json([
            'message' => 'OTP verification successful.',
            'token_type' => 'Bearer',
            'token' => $token,
            'user' =>  [
                'name' => $user->name,
                'email' => $user->email
            ],
            'success' => true
        ])->cookie(
            'auth_token',
            $token,
            60 * 24,  // Cookie duration (1 day)
            '/',
            null,
            false,  // Secure = false for HTTP
            true,   // HttpOnly
            false,  // SameSite
            null    // No SameSite restriction
        );
    }

    public function sendOTPtoSender($otp, $mobile)
    {
        //database config
        $conn = 'dynamic_connection';
        Config::set("database.connections.{$conn}", [
            'driver' => 'sqlsrv',
            'host' => 'dbsvr207.servep2p.com',
            'port' => '5050',
            'database' => 'eFastVITAL',
            'username' => 'efast_api',
            'password' => 'N*b57)#p<[K>',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        // Insert data into the dynamically selected database connection
        $insertOTP = DB::connection($conn)->table('tblsmsOUTsmart_ONE')->insert([
            'oDateTimeIn' => now(),
            'oMobileNo' => $mobile,
            'oRepliedMSG' => "Your OTP code is: " . $otp . ". Please use this code to complete your verification. The code is valid for 5 minutes.",
            'oPriority' => 2
        ]);

        return $insertOTP;
    }
}
