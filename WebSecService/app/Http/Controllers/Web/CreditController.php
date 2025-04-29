<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function chargeCredit(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999999.99'
        ], [
            'amount.required' => 'Please enter an amount to charge.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least $0.01.',
            'amount.max' => 'The amount cannot exceed $999,999,999.99.'
        ]);

        try {
            DB::beginTransaction();
            
            // Directly update the user's credit instead of using the stored procedure
            $user->credit += $request->amount;
            $user->save();
            
            // Log the transaction
            DB::table('credit_transactions')->insert([
                'employee_id' => auth()->id(),
                'customer_id' => $user->id,
                'amount' => $request->amount,
                'transaction_date' => now()
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Credit charged successfully: $' . number_format($request->amount, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to charge credit: ' . $e->getMessage());
        }
    }

    public function resetCredit(Request $request, User $user)
    {
        try {
            DB::beginTransaction();
            
            // Get current credit amount for transaction record
            $currentCredit = $user->credit;
            
            // Directly reset the user's credit instead of using the stored procedure
            $user->credit = 0;
            $user->save();
            
            // Log the transaction (negative amount to show credit was removed)
            if ($currentCredit > 0) {
                DB::table('credit_transactions')->insert([
                    'employee_id' => auth()->id(),
                    'customer_id' => $user->id,
                    'amount' => -$currentCredit, // Negative amount to show credit was reset
                    'transaction_date' => now()
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Credit reset successfully to $0.00');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reset credit: ' . $e->getMessage());
        }
    }

    public function insufficientCredit()
    {
        // Get all insufficient credit errors
        $insufficientCreditErrors = DB::select('SELECT * FROM insufficient_credit_errors');
        return view('credit.insufficient', compact('insufficientCreditErrors'));
    }

    public function transactionHistory()
    {
        // Get credit transaction history
        $transactions = DB::select('
            SELECT 
                ct.*,
                e.name as employee_name,
                c.name as customer_name
            FROM credit_transactions ct
            JOIN users e ON ct.employee_id = e.id
            JOIN users c ON ct.customer_id = c.id
            ORDER BY ct.transaction_date DESC
        ');

        return view('credit.history', compact('transactions'));
    }
} 