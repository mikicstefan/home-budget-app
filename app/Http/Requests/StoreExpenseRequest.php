<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable',
            'expense_date' => 'required|date_format:d.m.Y',
            'amount' => 'required|numeric|gt:0'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!empty($this->account_id)) {
                $account = Account::whereId($this->account_id)->first();

                if (!empty($account) and is_numeric($this->amount)) {
                    if ($account->balance - $this->amount < 0) {
                        return $validator->errors()->add(
                            'amount', 'Amount too big! There are not enough funds in the account'
                        );
                    }
                }
            }
        });
    }
}
