<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{

    /**
     * Add amount to the current balance
     *
     * @param Request $request
     * @param Account $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBalance(Request $request, Account $account)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|gt:0'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account->balance += $request->amount;
        $account->save();

        return response()->json(['data' => $account]);
    }
}
