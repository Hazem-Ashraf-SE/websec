<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Find existing user
            $user = User::where('provider_id', $socialUser->getId())
                       ->where('provider', $provider)
                       ->first();

            if (!$user) {
                // Check if user exists with same email
                $user = User::where('email', $socialUser->getEmail())->first();
                
                if (!$user) {
                    // Create new user
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                        'credit' => 0, // Default credit for new users
                    ]);
                    
                    // Assign default role (customer)
                    $user->assignRole('customer');
                }
            }

            Auth::login($user);
            return redirect()->intended('/dashboard');

        } catch (Exception $e) {
            return redirect()->route('login')
                           ->with('error', 'Something went wrong with ' . ucfirst($provider) . ' login');
        }
    }
}
