<?php

namespace App\Helpers;

use App\Core\Session;

class Form
{
    protected static $method;
    protected static $action;

    public static function begin($method = 'POST', $action = "#", $enctype = "multipart/form-data", $attrs = [])
    {
        self::$method = strtoupper($method);
        self::$action = $action;

        $attrString = self::buildAttributes($attrs);
        $enctypeAttr = $enctype ? 'enctype="' . htmlspecialchars($enctype) . '"' : '';
        echo "<form method='" . self::$method . "' action='" . self::$action . "' $enctypeAttr $attrString>";
    }

    public static function end()
    {
        echo "</form>";
    }

    public static function csrf()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf'] = $token;
        return "<input type='hidden' name='_token' value='" . htmlspecialchars($token) . "'>";
    }

    public static function input(
        $type,
        $name,
        $placeholder = '',
        $error = null,
        $label_input = "Label Input",
        $value = '',
        $attrs = []
    ) {
        $attrString = self::buildAttributes($attrs);
        $errorClass = $error
            ? 'border-red-500 focus:border-red-500'
            : 'border-slate-200 focus:border-blue-500';

        $value = Session::get("_old_{$name}") ?? $value;
        Session::forget("_old_{$name}");

        $errorHtml = "";
        if (is_array($error)) {
            foreach ($error as $er) {
                $errorHtml .= "<p class='mt-1 text-sm text-red-600 italic'>{$er}</p>";
            }
        }
        return "
        <div class='mb-3 flex flex-col gap-2'>
            
            <label for='{$name}' class='font-semibold text-slate-700'>
                {$label_input}
            </label>

            <div>
                <input 
                    type='{$type}' 
                    name='{$name}' 
                    id='{$name}'
                    value='{$value}' 
                    placeholder='{$placeholder}' 
                    class='w-full bg-slate-50 border {$errorClass} px-4 py-3 rounded-lg focus:bg-white focus:outline-none transition-all font-medium text-slate-700 placeholder:text-slate-300 text-sm'
                    {$attrString}
                >
                
                {$errorHtml}
            </div>

        </div>";
    }

    public static function textarea($name, $placeholder = '', $error = null, $label_input = "Label Input", $value = '', $attrs = [])
    {
        $attrString = self::buildAttributes($attrs);
        $errorClass = $error ? 'is-invalid' : '';

        $value = $_POST[$name] ?? $value;

        $html = "
        <div class='mb-3 flex flex-col gap-3'>
            <label for='{$name}' class='mb-1 font-semibold text-slate-700'>{$label_input}</label>
            <textarea 
                name='{$name}' 
                id='{$name}'
                placeholder='{$placeholder}' 
                class='w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-lg focus:border-blue-500 focus:bg-white focus:outline-none transition-all font-medium text-slate-700 placeholder:text-slate-300 text-sm  {$errorClass}'
                {$attrString}
            >{$value}</textarea>";

        if ($error) {
            $html .= "<div class='invalid-feedback'>{$error}</div>";
        }

        $html .= "</div>";

        return $html;
    }

    public static function editor($name, $placeholder = '', $error = null, $value = '')
    {
        $id = "editor_" . $name;
        $errorClass = $error ? 'is-invalid' : '';

        $html = "
        $value = $_POST[$name] ?? $value;
        <div class='mb-3'>
            <textarea 
                id='{$id}' 
                name='{$name}' 
                placeholder='{$placeholder}' 
                class='form-control {$errorClass}'
            >{$value}</textarea>";

        if ($error) {
            $html .= "<div class='invalid-feedback'>{$error}</div>";
        }

        $html .= "</div>

        <script>
            if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.replace('{$id}');
            }
        </script>
        ";

        return $html;
    }

    public static function button($text, $type = 'submit', $color = 'primary', $attrs = [])
    {
        $attrString = self::buildAttributes($attrs);

        return "
        <button type='{$type}' class='bg-blue-600 text-white  px-5 py-2 rounded-lg pt-5 ' {$attrString}>
            {$text}
        </button>
        ";
    }

    private static function buildAttributes($attrs)
    {
        $string = '';

        foreach ($attrs as $key => $val) {
            $string .= "{$key}='{$val}' ";
        }

        return $string;
    }
}
