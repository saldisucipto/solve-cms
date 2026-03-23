<?php

namespace App\Core;

class Validate
{
    protected $errors = [];

    public function check($data, $rules)
    {
        foreach ($rules as $field => $rule) {
            $ruleArr = explode('|', $rule);
            foreach ($ruleArr as $r) {
                if ($r === 'required' && empty($data[$field])) {
                    $this->errors[$field][] = 'The ' . $field . ' field is required.';
                }
                if ($r === 'email' && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = 'The ' . $field . ' field must be a valid email address.';
                }
                if (strpos($r, 'min:') === 0) {
                    $min = (int) substr($r, 4);
                    if (strlen($data[$field]) < $min) {
                        $this->errors[$field][] = 'The ' . $field . ' field must be at least ' . $min . ' characters.';
                    }
                }
                if (strpos($r, 'max:') === 0) {
                    $max = (int) substr($r, 4);
                    if (strlen($data[$field]) > $max) {
                        $this->errors[$field][] = 'The ' . $field . ' field must not exceed ' . $max . ' characters.';
                    }
                }
            }
        }
        return empty($this->errors);
    }

    public function errors()
    {
        return $this->errors;
    }
}
