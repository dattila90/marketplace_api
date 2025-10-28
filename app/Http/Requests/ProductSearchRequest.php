<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'string', 'max:50'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'sort_by' => ['nullable', 'string', 'in:relevance,price,rating,popularity,date'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'max_price.gte' => 'Maximum price must be greater than or equal to minimum price.',
            'per_page.max' => 'Cannot retrieve more than 100 items per page.',
            'sort_by.in' => 'Sort by must be one of: relevance, price, rating, popularity, date.',
        ];
    }

    /**
     * Get validated and prepared data for service layer
     */
    public function getSearchCriteria(): array
    {
        return $this->only([
            'search',
            'category_id',
            'min_price',
            'max_price',
            'sort_by',
            'sort_direction',
            'page',
            'per_page'
        ]);
    }
}
