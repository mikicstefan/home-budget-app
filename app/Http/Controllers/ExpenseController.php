<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{

    /**
     * Index
     *
     * List all categories
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $categoryId = $request->has('category_id') ? $request->category_id : null;
        $price = $request->has('price') ? $request->price : null;
        $startDate = $request->has('start_date') ? Carbon::parse($request->start_date) : null;
        $endDate = $request->has('start_date') ? Carbon::parse($request->end_date) : null;

        $account = Auth::user()->accounts()->first();

        $query = Expense::where('account_id', $account->id);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($price) {
            if ($price == 'min')
            {
                $query->where('amount', $query->min('amount'));
            }
            elseif ($price == 'max')
            {
                $query->where('amount', $query->max('amount'));
            }
        }

        if ($startDate) {
            $query->where('expense_date', '>=', $startDate);

            if ($endDate) {
                $query->where('expense_date', '<=', $endDate);
            }
        }

        return response()->json(['data' => $query->get()]);
    }

    /**
     * Aggregation
     *
     * Get expenses for some period of time. Possible values: month, quarter, year
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function aggregation(Request $request)
    {
        $period = $request->has('period') ? $request->period : 'month';

        $account = Auth::user()->accounts()->first();

        $query = Expense::where('account_id', $account->id);

        if ($period == 'month') {
            $startDate = Carbon::now()->startOfMonth();
        }

        if ($period == 'quarter') {
            $startDate = Carbon::now()->subMonths(3);
        }

        if ($period == 'year') {
            $startDate = Carbon::now()->subMonths(12);
        }

        $query = $query->where('expense_date', '>=', $startDate);

        $aggregationData = $query->get();
        $totalExpenses = $query->sum('amount');

        return response()->json(['period' => $period, 'total' => $totalExpenses, 'data' => $aggregationData]);
    }

    /**
     * Store
     *
     * Create and store new categories
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable',
            'expense_date' => 'required|date_format:d.m.Y',
            'amount' => 'required|numeric|gt:0'
        ]);

        $validator->after(function ($validator) use ($request){
            if (!empty($request->account_id)) {
                $account = Account::whereId($request->account_id)->first();

                if (!empty($account)) {
                    if ($account->balance - $request->amount < 0) {
                        return $validator->errors()->add(
                            'amount', 'Amount too big! There are not enough funds in the account'
                        );
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $expense = Expense::create([
            'name' => $request->name,
            'account_id' => $request->account_id,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'expense_date' => Carbon::parse($request->expense_date),
            'amount' => $request->amount
        ]);

        return response()->json(['data' => $expense]);
    }

    /**
     * Update
     *
     * Update existing category
     *
     * @param Request $request
     * @param Expense $expense
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Expense $expense)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable',
            'expense_date' => 'required|date_format:d.m.Y',
            'amount' => 'required|numeric|gt:0'
        ]);

        $validator->after(function ($validator) use ($expense, $request) {
            if (!empty($request->account_id)) {
                $account = Account::whereId($request->account_id)->first();

                if (!empty($account)) {
                    if ($account->balance + $expense->amount - $request->amount < 0) {
                        return $validator->errors()->add(
                            'amount', 'Amount too big! There are not enough funds in the account'
                        );
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $expense->name = $request->name;
        $expense->account_id = $request->account_id;
        $expense->category_id = $request->category_id;
        $expense->description = $request->description;
        $expense->expense_date = $request->expense_date;
        $expense->amount = $request->amount;
        $expense->save();

        return response()->json(['data' => $expense]);
    }

    /**
     * Delete
     *
     * Delete category. Only possible if category does not have expanses
     *
     * @param Request $request
     * @param Expense $expense
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Expense $expense)
    {
        $expense->delete();

        return response()->json(['message' => 'Expense successfully deleted']);
    }
}
