<?php

namespace App\Exceptions;

use Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ValidateException extends ValidationException
{
    /**
     * Create a new exception instance.
     *
     * @param array $messages
     */
    public function __construct(array $messages)
    {
        $validator = tap(Validator::make([], []), function ($validator) use ($messages) {
            foreach ($messages as $key => $value) {
                foreach (Arr::wrap($value) as $message) {
                    $validator->errors()->add($key, $message);
                }
            }
        });

        parent::__construct($validator);
    }
}
