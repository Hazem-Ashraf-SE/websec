<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function chargeCredit(Request $request, User $user)
    {
        // Check if the current user is an employee
        if (!auth()->user()->hasRole('Employee')) {
            return redirect()->back()->with('error', 'Only employees can charge credit.');
        }

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

            // Call the stored procedure to charge credit
            DB::select('CALL charge_customer_credit(?, ?, ?)', [
                auth()->id(),
                $user->id,
                $request->amount
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
        // Check if the current user is an employee
        if (!auth()->user()->hasRole('Employee')) {
            return redirect()->back()->with('error', 'Only employees can reset credit.');
        }

        try {
            DB::beginTransaction();

            // Call the stored procedure to reset credit
            DB::select('CALL reset_customer_credit(?, ?)', [
                auth()->id(),
                $user->id
            ]);

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