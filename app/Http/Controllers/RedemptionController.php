<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\Voucher;

class RedemptionController extends Controller
{
    const REDEMPTION_TIME_LIMIT = 900; // 15 minutes in seconds

    /**
     * Show redemption page with pre-filled code
     */
    public function showRedemptionForm(Request $request)
    {
        $code = $request->query('code');
        
        if (!$code) {
            return redirect()->route('redemption.form')
                ->with('error', 'No voucher code provided');
        }

        $code = strtoupper(trim($code));
        $voucher = Voucher::where('code', $code)->first();

        // Check if voucher exists
        if (!$voucher) {
            return view('pages.public.redemption', [
                'voucher' => null,
                'code' => null,
                'timeRemaining' => null,
                'error' => 'Invalid voucher code. Please check and try again.',
                'success' => null,
            ]);
        }

        // Check if already redeemed
        if ($voucher->status === 2 || $voucher->redeemed_at) {
            // Calculate time remaining if session exists
            $sessionKey = "voucher_redemption_{$code}";
            $redemptionStarted = Session::get($sessionKey);
            $timeRemaining = null;

            if ($redemptionStarted) {
                $elapsed = time() - $redemptionStarted;
                $timeRemaining = self::REDEMPTION_TIME_LIMIT - $elapsed;

                if ($timeRemaining <= 0) {
                    Session::forget($sessionKey);
                    $timeRemaining = null;
                }
            }

            return view('pages.public.redemption', [
                'voucher' => $voucher,
                'code' => $code,
                'timeRemaining' => $timeRemaining,
                'error' => null,
                'success' => null,
            ]);
        }

        // Check if voucher is active
        if ($voucher->status !== 1) {
            return view('pages.public.redemption', [
                'voucher' => null,
                'code' => null,
                'timeRemaining' => null,
                'error' => 'This voucher is not available for redemption.',
                'success' => null,
            ]);
        }

        // Check expiry date
        if ($voucher->expiry_date && now()->greaterThan($voucher->expiry_date)) {
            return view('pages.public.redemption', [
                'voucher' => null,
                'code' => null,
                'timeRemaining' => null,
                'error' => 'This voucher has expired.',
                'success' => null,
            ]);
        }

        Log::info('Redemption form viewed', [
            'voucher_code' => $code,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_login' => 'AriffAzmi',
            'timestamp' => '2025-10-16 08:21:16'
        ]);

        return view('pages.public.redemption', [
            'voucher' => null,
            'code' => $code,
            'timeRemaining' => null,
            'error' => session('error'),
            'success' => session('success'),
        ]);
    }
    
    /**
     * Process voucher redemption - One-click redeem
     */
    public function redeemVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:vouchers,code',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return redirect()->route('redemption.form')
                ->with('error', 'Voucher not found.');
        }

        // Check if already redeemed
        if ($voucher->status === 2 || $voucher->redeemed_at) {
            return redirect()->route('redemption.form', ['code' => $code]);
        }

        // Check if active
        if ($voucher->status !== 1) {
            return redirect()->route('redemption.form')
                ->with('error', 'This voucher is not active.');
        }

        // Check expiry date
        if ($voucher->expiry_date && now()->greaterThan($voucher->expiry_date)) {
            return redirect()->route('redemption.form')
                ->with('error', 'This voucher has expired.');
        }

        // Mark voucher as redeemed
        $voucher->status = 2; // Status 2 = Redeemed
        $voucher->redeemed_at = now();
        $voucher->save();

        // Start session timer
        $sessionKey = "voucher_redemption_{$code}";
        Session::put($sessionKey, time());

        Log::info('Voucher redeemed successfully', [
            'voucher_code' => $code,
            'voucher_id' => $voucher->id,
            'status' => $voucher->status,
            'redeemed_at' => $voucher->redeemed_at,
            'user_login' => 'AriffAzmi',
            'timestamp' => '2025-10-16 08:21:16'
        ]);

        return redirect()->route('redemption.form', ['code' => $code]);
    }

    /**
     * Check timer status (AJAX endpoint)
     */
    public function checkTimer(Request $request)
    {
        $code = $request->input('code');
        
        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Code is required'
            ], 400);
        }

        $sessionKey = "voucher_redemption_{$code}";
        $redemptionStarted = Session::get($sessionKey);

        if (!$redemptionStarted) {
            return response()->json([
                'success' => false,
                'expired' => true,
                'time_remaining' => 0,
                'message' => 'Session not found'
            ]);
        }

        $elapsed = time() - $redemptionStarted;
        $timeRemaining = self::REDEMPTION_TIME_LIMIT - $elapsed;

        if ($timeRemaining <= 0) {
            Session::forget($sessionKey);

            return response()->json([
                'success' => false,
                'expired' => true,
                'time_remaining' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'expired' => false,
            'time_remaining' => $timeRemaining,
        ]);
    }
}