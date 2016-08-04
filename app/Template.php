<?php
/**
 * Created by PhpStorm.
 * User: tthrockmorton
 * Date: 8/4/2016
 * Time: 11:04 AM
 */

namespace App;

use Doctrine\Instantiator\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class Template
{
    private static $name;
    private static $path;
    private static $configPrefix;

    public function __construct($name)
    {
        self::$name = $name;
        self::checkTemplateExists($name);
    }

    private static function splitToParagraphs($input) {
        return explode('\n',$input);
    }

    private static function checkTemplateExists($template) {
        $path = '../resources/views/emails';
        if(file_exists("$path/phishing/$template.blade.php")) {
            self::$path = "$path/phishing";
            self::$configPrefix = 'emails.phishing';
        } else if(file_exists("$path/edu/$template.blade.php")) {
            self::$path = "$path/edu";
            self::$configPrefix = 'emails.edu';
        } else {
            throw new FileNotFoundException("Failed to find template: $template");
        }
    }

    private static function create($input,$path) {
        if(self::validate($input)) {
            $content = '';
            $paragraphs = self::splitToParagraphs($input);

            for($i = 0; $i < sizeof($paragraphs); $i++) {
                $content .= "<p>" . $paragraphs[$i] . "</p>";
            }
            if(!empty($content)) {
                \File::put($path,$content);
                \File::delete('../resources/views/emails/.blade.php');
            } else {
                throw new FileException("Failed to create template: $path");
            }
        } else {
            throw new UnexpectedValueException("Input is not valid. You may not use '<>'. " .
                "Input provided: " . var_export($input));
        }
    }

    private static function validate($input) {
        return filter_var(
            $input,
            FILTER_VALIDATE_REGEXP,
            array(
                "options"=>
                    array(
                        "regexp"=>"/([^<>])/ig"
                    )
            )
        );
    }

    public static function createPhish($input,$name) {
        self::create($input,"../resources/views/emails/phishing/$name.blade.php");
    }

    public static function createEdu($input,$name) {
        self::create($input,"../resources/views/emails/edu/$name.blade.php");
    }
}