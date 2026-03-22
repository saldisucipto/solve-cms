<?php
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

    public static function input($type, $name, $placeholder = '', $error = null, $value = '', $attrs = [])
    {
        $attrString = self::buildAttributes($attrs);
        $errorClass = $error ? 'is-invalid' : '';

        $value = $_POST[$name] ?? $value;

        $html = "
        <div class='mb-3'>
            <input 
                type='{$type}' 
                name='{$name}' 
                value='{$value}' 
                placeholder='{$placeholder}' 
                class='form-control {$errorClass}'
                {$attrString}
            >";

        if ($error) {
            $html .= "<div class='invalid-feedback'>{$error}</div>";
        }

        $html .= "</div>";

        return $html;
    }

    public static function textarea($name, $placeholder = '', $error = null, $value = '', $attrs = [])
    {
        $attrString = self::buildAttributes($attrs);
        $errorClass = $error ? 'is-invalid' : '';

        $value = $_POST[$name] ?? $value;

        $html = "
        <div class='mb-3'>
            <textarea 
                name='{$name}' 
                placeholder='{$placeholder}' 
                class='form-control {$errorClass}'
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
        <button type='{$type}' class='btn btn-{$color}' {$attrString}>
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
