<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CryptographyController extends Controller
{
    public function index()
    {
        return view('cryptography');
    }

    public function process(Request $request)
    {
        $textData = $request->input('text_data');
        $operation = $request->input('operation');
        $result = '';
        $status = 'success';

        try {
            switch ($operation) {
                case 'encrypt':
                    $result = Crypt::encryptString($textData);
                    break;
                case 'decrypt':
                    $result = Crypt::decryptString($textData);
                    break;
                case 'hash':
                    $result = Hash::make($textData);
                    break;
                case 'sign':
                    // For signature, we'd typically use private key to sign
                    // This is a simplified implementation
                    $result = Hash::make($textData);
                    break;
                case 'verify':
                    $originalHash = $request->input('result_field');
                    $isValid = Hash::check($textData, $originalHash);
                    $result = $isValid ? 'Verification successful' : 'Verification failed';
                    $status = $isValid ? 'success' : 'failed';
                    break;
                case 'rsa':
                    // For demonstration purposes, we'll use Laravel's built-in encryption
                    // In a real application, you would use proper RSA key generation
                    $privateKey = Str::random(64);
                    $publicKey = Str::random(32);
                    $result = "Private Key:\n{$privateKey}\n\nPublic Key:\n{$publicKey}";
                    break;
                case 'key_send':
                    // Simulate RSA encryption with Laravel's encryption
                    $publicKey = $request->input('result_field');
                    if (empty($publicKey)) {
                        throw new \Exception('Public key is required for RSA encryption');
                    }
                    // In a real application, you would use the public key for encryption
                    // Here we're using Laravel's encryption as a simulation
                    $result = Crypt::encryptString($textData);
                    break;
                case 'key_receive':
                    // Simulate RSA decryption with Laravel's decryption
                    $privateKey = $request->input('result_field');
                    if (empty($privateKey)) {
                        throw new \Exception('Private key is required for RSA decryption');
                    }
                    // In a real application, you would use the private key for decryption
                    // Here we're using Laravel's decryption as a simulation
                    try {
                        $result = Crypt::decryptString($textData);
                    } catch (\Exception $e) {
                        throw new \Exception('Invalid encrypted data or key');
                    }
                    break;
                default:
                    throw new \Exception('Invalid operation');
            }
        } catch (\Exception $e) {
            $result = 'Error: ' . $e->getMessage();
            $status = 'failed';
        }

        return response()->json([
            'result' => $result,
            'status' => $status
        ]);
    }
}
