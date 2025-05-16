<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
                    // Use the PFX certificate to sign the data
                    $pfxPath = storage_path('app/private/useremail@domain.com.pfx');
                    $pfxPassword = '12345678';
                    
                    // Check if the PFX file exists
                    if (!file_exists($pfxPath)) {
                        throw new \Exception('Certificate file not found');
                    }
                    
                    // Load the PFX certificate
                    $certData = file_get_contents($pfxPath);
                    $certs = [];
                    
                    // Extract the private key from the PFX
                    if (!openssl_pkcs12_read($certData, $certs, $pfxPassword)) {
                        throw new \Exception('Unable to read the certificate. Invalid password or certificate format.');
                    }
                    
                    // Get the private key
                    $privateKey = $certs['pkey'];
                    
                    // Create a signature
                    $signature = null;
                    if (!openssl_sign($textData, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                        throw new \Exception('Failed to create signature');
                    }
                    
                    // Base64 encode the signature for display
                    $result = base64_encode($signature);
                    break;
                case 'verify':
                    $signatureOrHash = $request->input('result_field');
                    
                    // Try to decode as base64 to check if it's a signature
                    $decodedSignature = base64_decode($signatureOrHash, true);
                    
                    // If it's a valid base64 string and likely a signature
                    if ($decodedSignature !== false && strlen($decodedSignature) > 20) {
                        // This appears to be a certificate-based signature
                        $pfxPath = storage_path('app/private/useremail@domain.com.pfx');
                        $pfxPassword = '12345678';
                        
                        // Check if the PFX file exists
                        if (!file_exists($pfxPath)) {
                            throw new \Exception('Certificate file not found');
                        }
                        
                        // Load the PFX certificate
                        $certData = file_get_contents($pfxPath);
                        $certs = [];
                        
                        // Extract the certificate from the PFX
                        if (!openssl_pkcs12_read($certData, $certs, $pfxPassword)) {
                            throw new \Exception('Unable to read the certificate. Invalid password or certificate format.');
                        }
                        
                        // Get the certificate
                        $cert = $certs['cert'];
                        
                        // Extract the public key from the certificate
                        $publicKey = openssl_pkey_get_public($cert);
                        if (!$publicKey) {
                            throw new \Exception('Failed to extract public key from certificate');
                        }
                        
                        // Verify the signature
                        $isValid = openssl_verify($textData, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256);
                        
                        if ($isValid === 1) {
                            $result = 'Certificate signature verification successful';
                            $status = 'success';
                        } elseif ($isValid === 0) {
                            $result = 'Certificate signature verification failed';
                            $status = 'failed';
                        } else {
                            throw new \Exception('Error during signature verification');
                        }
                        
                        // Free the key resource
                        openssl_free_key($publicKey);
                    } else {
                        // Regular hash verification
                        $originalHash = $signatureOrHash;
                        $isValid = Hash::check($textData, $originalHash);
                        $result = $isValid ? 'Verification successful' : 'Verification failed';
                        $status = $isValid ? 'success' : 'failed';
                    }
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
