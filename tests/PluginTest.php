<?php namespace VojtaSvoboda\TwigExtensions\Tests;

use App;
use Carbon\Carbon;
use Config;
use PluginTestCase;

class PluginTest extends PluginTestCase
{
    public function setUp() : void
    {
        parent::setUp();

        $this->app->setLocale('en');
    }

    /**
     * Return Twig environment
     *
     * @return \Twig\Environment
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getTwig(): \Twig\Environment
    {
        return $this->app->make('twig.environment');
    }

    public function testTemplateFromStringFunction()
    {
        $twig = $this->getTwig();

        $template = "{% set name = 'John' %}";
        $template .= '{{ include(template_from_string("Hello {{ name }}")) }}';

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('Hello John', $twigTemplate->render());
    }

    public function testTruncateFilterForFive()
    {
        $twig = $this->getTwig();

        $template = "{{ 'Gordon Freeman' | truncate(5) }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('Gordo...', $twigTemplate->render());
    }

    public function testTruncateFilterForDefault()
    {
        $twig = $this->getTwig();

        $template = "{{ 'Lorem ipsum dolor sit amet, consectetur adipiscing elit' | truncate }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('Lorem ipsum dolor sit amet, co...', $twigTemplate->render());
    }

    public function testTruncateFilterWithSeparator()
    {
        $twig = $this->getTwig();

        $template = "{{ 'Hello World!'|truncate(7, true) }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('Hello World!', $twigTemplate->render());

        $template = "{{ 'Hello World!'|truncate(7, false, '??') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('Hello W??', $twigTemplate->render());
    }

    public function testWordWrapFilter()
    {
        $twig = $this->getTwig();

        $template = "{{ 'Lorem ipsum dolor sit amet, consectetur adipiscing elit' | wordwrap(10) }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals("Lorem ipsu\nm dolor si\nt amet, co\nnsectetur \nadipiscing\n elit", $twigTemplate->render());
    }

    public function testShuffleFilter()
    {
        $twig = $this->getTwig();

        $template = "{{ [1, 2, 3] | shuffle }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->expectException('Twig_Error_Runtime', 'Array to string conversion');
        $twigTemplate->render();
    }

    public function testShuffleFilterForeach()
    {
        $twig = $this->getTwig();

        $template = "{% for i in [1, 2, 3] | shuffle %}{{ i }}{% endfor %}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals(3, strlen($twigTemplate->render()));
    }

    public function testTimeDiffFunction()
    {
        $twig = $this->getTwig();

        $now = Carbon::now()->subMinute();
        $template = "{{ '" . $now->format('Y-m-d H:i:s') . "' | time_diff }}";

        // this test fails at TravisCI and I don't know why
        $twigTemplate = $twig->createTemplate($template);
         $this->assertEquals('1 minute ago', $twigTemplate->render());
    }

    public function testStrftimeFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ '2016-03-24 23:05' | strftime('%d.%m.%Y %H:%M:%S') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('24.03.2016 23:05:00', $twigTemplate->render());
    }

    public function testUppercaseFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'Hello Jack' | uppercase }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('HELLO JACK', $twigTemplate->render());
    }

    public function testLowercaseFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'Hello JACK' | lowercase }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('hello jack', $twigTemplate->render());
    }

    public function testUcfirstFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'heLLo jack' | ucfirst }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('HeLLo jack', $twigTemplate->render());
    }

    public function testLcfirstFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'HEllO JACK' | lcfirst }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('hEllO JACK', $twigTemplate->render());
    }

    public function testLtrimFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ ' jack' | ltrim }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('jack', $twigTemplate->render());
    }

    public function testRtrimFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'jack ' | rtrim }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('jack', $twigTemplate->render());
    }

    public function testStrRepeatFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ ' best' | str_repeat(3) }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals(' best best best', $twigTemplate->render());
    }

    public function testPluralFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'mail' | plural(count) }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('mails', $twigTemplate->render());
    }

    public function testStrpadFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'test' | strpad(10) }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('   test   ', $twigTemplate->render());
    }

    public function testStrReplaceFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'test' | str_replace('test', 'tset') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('tset', $twigTemplate->render());
    }

    public function testStripTagsFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ '<p><b>text</b></p>' | strip_tags('<p>') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('<p>text</p>', $twigTemplate->render());
    }

    public function testLeftpadFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'test' | leftpad(7) }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('   test', $twigTemplate->render());
    }

    public function testRightpadFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'test' | rightpad(7, 'o') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('testooo', $twigTemplate->render());
    }

    public function testRtlFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ 'Hello world!' | rtl }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('!dlrow olleH', $twigTemplate->render());
    }

    //public function testSortByFieldFunction()
    //{
    //    $twig = $this->getTwig();
    //
    //    // sort by name
    //    $template = "{% set data = [{'name': 'David', 'age': 31}, {'name': 'John', 'age': 28}] %}";
    //    $template .= "{% for item in data | sortbyfield('name') %}{{ item.name }}{% endfor %}";
    //    $twigTemplate = $twig->createTemplate($template);
    //    $this->assertEquals($twigTemplate->render(), 'DavidJohn');
    //
    //    // sort by age
    //    $template = "{% set data = [{'name': 'David', 'age': 31}, {'name': 'John', 'age': 28}] %}";
    //    $template .= "{% for item in data | sortbyfield('age') %}{{ item.name }}{% endfor %}";
    //    $twigTemplate = $twig->createTemplate($template);
    //    $this->assertEquals($twigTemplate->render(), 'JohnDavid');
    //}

    public function testMailtoFilter()
    {
        $twig = $this->getTwig();

        // same as mailto(true, true)
        $template = "{{ 'vojtasvoboda.cz@gmail.com' | mailto }}";
        $twigTemplate = $twig->createTemplate($template);

        $this->assertStringNotContainsString('vojtasvoboda.cz@gmail.com', $twigTemplate->render());
        $this->assertStringContainsString('mailto:', $twigTemplate->render());

        // mailto(false, false) eg. without link and unprotected
        $template = "{{ 'vojtasvoboda.cz@gmail.com' | mailto(false, false) }}";
        $twigTemplate = $twig->createTemplate($template);
        $this->assertStringContainsString('vojtasvoboda.cz@gmail.com', $twigTemplate->render());
        $this->assertStringNotContainsString('mailto:', $twigTemplate->render());

        // mailto(true, false) eg. with link but unprotected
        $template = "{{ 'vojtasvoboda.cz@gmail.com' | mailto(true, false) }}";
        $twigTemplate = $twig->createTemplate($template);
        $this->assertStringContainsString('vojtasvoboda.cz@gmail.com', $twigTemplate->render());
        $this->assertStringContainsString('mailto', $twigTemplate->render());

        // mailto(false, true) eg. without link and protected
        $template = "{{ 'vojtasvoboda.cz@gmail.com' | mailto(false, true) }}";
        $twigTemplate = $twig->createTemplate($template);
        $this->assertStringNotContainsString('vojtasvoboda.cz@gmail.com', $twigTemplate->render());
        $this->assertStringNotContainsString('mailto', $twigTemplate->render());

        // mailto(true, true, 'Let me know') eg. with link, protected and with non-crypted text
        $template = "{{ 'vojtasvoboda.cz@gmail.com' | mailto(false, true, 'Let me know') }}";
        $twigTemplate = $twig->createTemplate($template);
        $this->assertStringNotContainsString('vojtasvoboda.cz@gmail.com', $twigTemplate->render());
        $this->assertStringNotContainsString('mailto', $twigTemplate->render());
        $this->assertStringContainsString('Let me know', $twigTemplate->render());
    }

    public function testVardumpFunction()
    {
        $twig = $this->getTwig();

        $template = "{{ var_dump('test') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertStringContainsString('string(4) "test"', $twigTemplate->render());
    }

    public function testVardumpFilter()
    {
        $twig = $this->getTwig();

        $template = "{{ 'test' | var_dump }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertStringContainsString('string(4) "test"', $twigTemplate->render());
    }

    public function testConfigFunction()
    {
        $twig = $this->getTwig();

        $key = 'app.custom.key';
        $value = 'test value';
        Config::set($key, $value);
        $template = "{{ config('" . $key . "') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals($twigTemplate->render(), $value);
    }

    public function testEnvFunction()
    {
        $twig = $this->getTwig();

        $key = 'env.custom.key';
        $value = 'test value';
        putenv($key.'='.$value);
        $template = "{{ env('" . $key . "') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals($twigTemplate->render(), $value);
    }

    public function testSessionFunction()
    {
        $twig = $this->getTwig();

        session(['my.session.key' => 'test value']);

        $template = "{{ session('my.session.key') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('test value', $twigTemplate->render());
    }

    public function testTransFunction()
    {
        $twig = $this->getTwig();
        Config::set('app.locale', 'en');

        $template = "{{ trans('validation.accepted') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('The :attribute must be accepted.', $twigTemplate->render());
    }

    public function testTransFunctionWithParam()
    {
        $twig = $this->getTwig();
        Config::set('app.locale', 'en');

        $template = "{{ trans('backend::lang.access_log.hint', {'days': 60}) }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertStringContainsString('60 days', $twigTemplate->render());
    }
}
