<?php

namespace VojtaSvoboda\TwigExtensions\Tests\IntlExtension;

use NumberFormatter;
use ScssPhp\ScssPhp\Node\Number;
use System\Classes\PluginManager;

class IntlExtensionTest extends \PluginTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('en');

        $plugin = PluginManager::instance()->findByNamespace('VojtaSvoboda.TwigExtensions');
        PluginManager::instance()->bootPlugin($plugin);
    }

    private function getTwig(): \Twig\Environment
    {
        return $this->app->make('twig.environment');
    }

    public function test_it_adds_twig_intl_extension()
    {
        $twig = $this->getTwig();

        $this->assertTrue($twig->hasExtension(\Twig\Extra\Intl\IntlExtension::class));
    }

    public function test_it_formats_number_as_percentage()
    {
        $twig = $this->getTwig();

        $template = "{{ '12.345'|format_number(style='percent') }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals('1,234%', $twigTemplate->render());
    }
}
