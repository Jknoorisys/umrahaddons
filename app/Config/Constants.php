<?php

//--------------------------------------------------------------------
// App Namespace
//--------------------------------------------------------------------
// This defines the default Namespace that is used throughout
// CodeIgniter to refer to the Application directory. Change
// this constant to change the namespace that all application
// classes should use.
//
// NOTE: changing this will require manually modifying the
// existing namespaces of App\* namespaced-classes.
//
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
|--------------------------------------------------------------------------
| Composer Path
|--------------------------------------------------------------------------
|
| The path that Composer's autoload file is expected to live. By default,
| the vendor folder is in the Root directory, but you can customize that here.
*/
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
|--------------------------------------------------------------------------
| Timing Constants
|--------------------------------------------------------------------------
|
| Provide simple ways to work with the myriad of PHP functions that
| require information to be in seconds.
*/
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2592000);
defined('YEAR')   || define('YEAR', 31536000);
defined('DECADE') || define('DECADE', 315360000);

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
defined('PER_PAGE') || define('PER_PAGE', 10);
defined('PER_PAGE_FOR_PROVIDER') || define('PER_PAGE_FOR_PROVIDER', 9);

defined('FPDF_VERSION')     || define('FPDF_VERSION', '1.81'); 
defined('UPLOAD_PATH')      || define('UPLOAD_PATH', 'C:/xampp/htdocs/umrah/'); 
// defined("STRIPE_KEY") || define('STRIPE_KEY',getenv('STRIPE_KEY','') );
// defined("STRIPE_SECRET") || define('STRIPE_SECRET',getenv('STRIPE_SECRET','') );

defined("STRIPE_KEY") || define('STRIPE_KEY', "pk_test_51NY56dJcWpwScJc8jEj6Gl5BvglsvwgO9cTyWazLhbB6WqrEnigauFcjUf5zQQ8LCkHijEch9Z9L6tsmgB99LJXo00qfCWqmOI");
defined("STRIPE_SECRET") || define('STRIPE_SECRET', "sk_test_51NY56dJcWpwScJc8xc3cJdxVepr7qWvyBynKfHmz1sght4Kb6nyznFAlAQMG8ekbrkaVLlLS7b288CccoIDWfaY6005ZssfGnd");

// defined("STRIPE_KEY") || define('STRIPE_KEY', "pk_test_51LV7hmSDWgD43vfLdVU50kl2d0ZBqsafnh084EbZx5IOQ0oZhKxwQMerl73zqImgs6GWKevV9Nmv9FwZAW9PQix400wj8xeIvU");
// defined("STRIPE_SECRET") || define('STRIPE_SECRET', "sk_test_51LV7hmSDWgD43vfLeNXrFRtKPwXhfv8XWRZVC2CK8HqIR11AmFsa3e3jGJ7VSoLdTYUuu3s7k8gCbhuSnNRu61NF00r30TJSFH");
// php mail
// defined("HOST") || define('HOST', "mail.nooridev.in");
// defined("USERNAME") || define('USERNAME', "info@nooridev.in");
// defined("PASSWORD") || define('PASSWORD', "HsF9l5PEctZ8");
// defined("FROM_EMAIL") || define('FROM_EMAIL', "info@nooridev.in");
// defined("FROM_NAME") || define('FROM_NAME', "Umrah Plus");

defined("HOST") || define('HOST', "smtp.gmail.com");
defined("USERNAME") || define('USERNAME', "Noorisys Technologies Pvt Ltd");
defined("PASSWORD") || define('PASSWORD', "mfhvxyvoiihvfmxe");
defined("FROM_EMAIL") || define('FROM_EMAIL', "noori.developer@gmail.com");
defined("FROM_NAME") || define('FROM_NAME', "Umrah Plus");

defined('PAGE_LENGTH') || define('PAGE_LENGTH', 0);
defined('LANGUAGES')   || define('LANGUAGES', 'en,ar,ur');