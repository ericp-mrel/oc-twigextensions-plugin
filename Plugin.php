<?php namespace VojtaSvoboda\TwigExtensions;

use App;
use Carbon\Carbon;
use Cms\Classes\Controller;
use Event;
use Symfony\Component\String\UnicodeString;
use System\Classes\PluginBase;
use Twig\Extension\StringLoaderExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\String\StringExtension;

/**
 * Twig Extensions Plugin.
 *
 * @see http://twig.sensiolabs.org/doc/extensions/index.html#extensions-install
 */
class Plugin extends PluginBase
{
    /**
     * @var boolean Determine if this plugin should have elevated privileges.
     */
    public $elevated = true;

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Twig Extensions',
            'description' => 'Add more Twig filters to your templates.',
            'author'      => 'Vojta Svoboda',
            'icon'        => 'icon-plus',
            'homepage'    => 'https://github.com/vojtasvoboda/oc-twigextensions-plugin',
        ];
    }

    public function boot()
    {
        /** @var \Twig\Environment $twig */
        //$twig = $this->app->make('twig.environment');
        //if (! $twig->hasExtension(IntlExtension::class)) {
        //    $twig->addExtension(new IntlExtension());
        //}
        //
        //if (! $twig->hasExtension(StringExtension::class)) {
        //    $twig->addExtension(new StringExtension());
        //}

        Event::listen('cms.page.beforeDisplay', function (Controller $controller, $page) {
            $twig = $controller->getTwig();

            if (! $twig->hasExtension(IntlExtension::class)) {
                $twig->addExtension(new IntlExtension());
            }

            if (! $twig->hasExtension(StringExtension::class)) {
                $twig->addExtension(new StringExtension());
            }
        });
    }

    /**
     * Add Twig extensions.
     *
     * @see Text extensions http://twig.sensiolabs.org/doc/extensions/text.html
     * @see Intl extensions http://twig.sensiolabs.org/doc/extensions/intl.html
     * @see Array extension http://twig.sensiolabs.org/doc/extensions/array.html
     * @see Time extension http://twig.sensiolabs.org/doc/extensions/date.html
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function registerMarkupTags(): array
    {
        $filters = [];
        $functions = [];

        // init Twig
        /** @var \Twig\Environment $twig */
        $twig = $this->app->make('twig.environment');

        // add String Loader functions
        $functions += $this->getStringLoaderFunctions($twig);

        // add Config function
        $functions += $this->getConfigFunction();

        // add Env function
        $functions += $this->getEnvFunction();

        // add Session function
        $functions += $this->getSessionFunction();

        // add Trans function
        $functions += $this->getTransFunction();

        // add var_dump function
        $functions += $this->getVarDumpFunction();

        // add Text extensions
        $filters += $this->getTextFilters();

        // add Intl extensions if php5-intl installed
        if (class_exists('IntlDateFormatter')) {
            $filters += $this->getLocalizedFilters($twig);
        }

        // add Array extensions
        $filters += $this->getArrayFilters();

        // add Time extensions
        $filters += $this->getTimeFilters();

        // add Sort by Field extensions
        //$filters += $this->getSortByField();

        // add Mail filters
        $filters += $this->getMailFilters();

        // add PHP functions
        $filters += $this->getPhpFunctions();

        // add File Version filter
        $filters += $this->getFileRevision();

        return [
            'filters'   => $filters,
            'functions' => $functions,
        ];
    }

    /**
     * Returns String Loader functions.
     *
     * @param \Twig\Environment $twig
     *
     * @return array
     */
    private function getStringLoaderFunctions(\Twig\Environment $twig): array
    {
        $stringLoader = new StringLoaderExtension();
        $stringLoaderFunc = $stringLoader->getFunctions();

        return [
            'template_from_string' => function ($template) use ($twig, $stringLoaderFunc) {
                $callable = $stringLoaderFunc[0]->getCallable();
                return $callable($twig, $template);
            }
        ];
    }

    /**
     * Returns Text filters.
     *
     * @return array
     */
    private function getTextFilters(): array
    {
        return [
            'truncate' => function ($value, $length = 30, $preserve = false, $separator = '...') {
                return (new UnicodeString($value))->truncate($length, $separator, !$preserve)->toString();
            },
            'wordwrap' => function ($value, $length = 80, $separator = "\n", $preserve = false) {
                return (new UnicodeString($value))->wordwrap($length, $separator, !$preserve)->toString();
            }
        ];
    }

    /**
     * Returns Intl filters.
     *
     * @param \Twig\Environment $twig
     *
     * @return array
     */
    private function getLocalizedFilters(\Twig\Environment $twig): array
    {
        $intlExtension = new IntlExtension();

        return [
            'localizeddate' => function ($date, $dateFormat = 'medium', $timeFormat = 'medium', $locale = null, $timezone = null, $format = '') use ($twig, $intlExtension) {
                return $intlExtension->formatDateTime($twig, $date, $dateFormat, $timeFormat, $format, $timezone, 'gregorian', $locale);
            },
            'localizednumber' => function ($number, $style = 'decimal', $type = 'default', $locale = null) use ($twig, $intlExtension) {
                return $intlExtension->formatNumber($number, [], $style, $type, $locale);
            },
            'localizedcurrency' => function ($number, $currency = null, $locale = null) use ($twig, $intlExtension) {
                return $intlExtension->formatCurrency($number, $currency, [], $locale);
            }
        ];
    }

    /**
     * Returns Array filters.
     *
     * @return array
     */
    private function getArrayFilters(): array
    {
        return [
            'shuffle' => function ($array) {
                if ($array instanceof \Traversable) {
                    $array = iterator_to_array($array, false);
                }

                shuffle($array);

                return $array;
            }
        ];
    }

    /**
     * Returns Date filters.
     *
     * @return array
     */
    private function getTimeFilters(): array
    {
        return [
            'time_diff' => function ($date, $now = null) {
                return Carbon::parse($date)->diffForHumans($now);
            }
        ];
    }

    /**
     * Returns Sort by Field filters.
     *
     * @return array
     */
    //private function getSortByField(): array
    //{
    //    $extension = new SortByFieldExtension();
    //    $filters = $extension->getFilters();
    //
    //    return [
    //        'sortbyfield' => function ($array, $sort_by = null, $direction = 'asc') use ($filters) {
    //            $callable = $filters[0]->getCallable();
    //            return $callable($array, $sort_by, $direction);
    //        }
    //    ];
    //}

    /**
     * Returns mail filters.
     *
     * @return array
     */
    private function getMailFilters(): array
    {
        return [
            'mailto' => function ($string, $link = true, $protected = true, $text = null, $class = "") {
                return $this->hideEmail($string, $link, $protected, $text, $class);
            }
        ];
    }

    /**
     * Returns plain PHP functions.
     *
     * @return array
     */
    private function getPhpFunctions(): array
    {
        return [
            'strftime' => function ($time, $format = '%d.%m.%Y %H:%M:%S') {
                $timeObj = new Carbon($time);
                return strftime($format, $timeObj->getTimestamp());
            },
            'uppercase' => function ($string) {
                return mb_convert_case($string, MB_CASE_UPPER, "UTF-8");
            },
            'lowercase' => function ($string) {
                return mb_convert_case($string, MB_CASE_LOWER, "UTF-8");
            },
            'ucfirst' => function ($string) {
                return ucfirst($string);
            },
            'lcfirst' => function ($string) {
                return lcfirst($string);
            },
            'ltrim' => function ($string, $charlist = " \t\n\r\0\x0B") {
                return ltrim($string, $charlist);
            },
            'rtrim' => function ($string, $charlist = " \t\n\r\0\x0B") {
                return rtrim($string, $charlist);
            },
            'str_repeat' => function ($string, $multiplier = 1) {
                return str_repeat($string, $multiplier);
            },
            'plural' => function ($string, $count = 2) {
                return str_plural($string, $count);
            },
            'strpad' => function ($string, $pad_length, $pad_string = ' ') {
                return str_pad($string, $pad_length, $pad_string, $pad_type = STR_PAD_BOTH);
            },
            'leftpad' => function ($string, $pad_length, $pad_string = ' ') {
                return str_pad($string, $pad_length, $pad_string, $pad_type = STR_PAD_LEFT);
            },
            'rightpad' => function ($string, $pad_length, $pad_string = ' ') {
                return str_pad($string, $pad_length, $pad_string, $pad_type = STR_PAD_RIGHT);
            },
            'rtl' => function ($string) {
                return strrev($string);
            },
            'str_replace' => function ($string, $search, $replace) {
                return str_replace($search, $replace, $string);
            },
            'strip_tags' => function ($string, $allow = '') {
                return strip_tags($string, $allow);
            },
            'var_dump' => function ($expression) {
                ob_start();
                var_dump($expression);

                return ob_get_clean();
            },
        ];
    }

    /**
     * Works like the config() helper function.
     *
     * @return array
     */
    private function getConfigFunction()
    {
        return [
            'config' => function ($key = null, $default = null) {
                return config($key, $default);
            },
        ];
    }

    /**
     * Works like the env() helper function.
     *
     * @return array
     */
    private function getEnvFunction()
    {
        return [
            'env' => function ($key, $default = null) {
                return env($key, $default);
            },
        ];
    }

    /**
     * Works like the session() helper function.
     *
     * @return array
     */
    private function getSessionFunction()
    {
        return [
            'session' => function ($key = null) {
                return session($key);
            },
        ];
    }

    /**
     * Works like the trans() helper function.
     *
     * @return array
     */
    private function getTransFunction()
    {
        return [
            'trans' => function ($key = null, $parameters = []) {
                return trans($key, $parameters);
            },
        ];
    }

    /**
     * Dumps information about a variable.
     *
     * @return array
     */
    private function getVarDumpFunction()
    {
        return [
            'var_dump' => function ($expression) {
                ob_start();
                var_dump($expression);

                return ob_get_clean();
            },
        ];
    }

    /**
     * Create protected link with mailto:
     *
     * @param string $email Email to render.
     * @param bool $link If email should be rendered as link.
     * @param bool $protected If email should be protected.
     * @param string $text Link text. Render email by default.
     *
     * @see http://www.maurits.vdschee.nl/php_hide_email/
     *
     * @return string
     */
    private function hideEmail($email, $link = true, $protected = true, $text = null, $class = "")
    {
        // email link text
        $linkText = $email;
        if ($text !== null) {
            $linkText = $text;
        }

        // if we want just unprotected link
        if (!$protected) {
            return $link ? '<a href="mailto:' . $email . '">' . $linkText . '</a>' : $linkText;
        }

        // turn on protection
        $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
        $key = str_shuffle($character_set);
        $cipher_text = '';
        $id = 'e' . rand(1, 999999999);
        for ($i = 0; $i < strlen($email); $i += 1) {
            $cipher_text .= $key[strpos($character_set, $email[$i])];
        }
        $script = 'var a="' . $key . '";var b=a.split("").sort().join("");var c="' . $cipher_text . '";var d=""; var cl="'.$class.'";';
        $script .= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));';
        $script .= 'var y = d;';
        if ($text !== null) {
            $script .= 'var y = "'.$text.'";';
        }
        if ($link) {
            $script .= 'document.getElementById("' . $id . '").innerHTML="<a class=\""+cl+"\" href=\\"mailto:"+d+"\\">"+y+"</a>"';
        } else {
            $script .= 'document.getElementById("' . $id . '").innerHTML=y';
        }
        $script = "eval(\"" . str_replace(array("\\", '"'), array("\\\\", '\"'), $script) . "\")";
        $script = '<script type="text/javascript">/*<![CDATA[*/' . $script . '/*]]>*/</script>';

        return '<span id="' . $id . '">[javascript protected email address]</span>' . $script;
    }

    /**
     * Appends this pattern: ? . {last modified date}
     * to an assets filename to force browser to reload
     * cached modified file.
     *
     * See: https://github.com/vojtasvoboda/oc-twigextensions-plugin/issues/25
     *
     * @return array
     */
    private function getFileRevision()
    {
        return [
            'revision' => function ($filename, $format = null) {
                // Remove http/web address from the file name if there is one to load it locally
                $prefix = url('/');
                $filename_ = trim(preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $filename), '/');
                if (file_exists($filename_)) {
                    $timestamp = filemtime($filename_);
                    $prepend = ($format) ? date($format, $timestamp) : $timestamp;

                    return $filename . "?" . $prepend;
                }

                return $filename;
            },
        ];
    }
}
