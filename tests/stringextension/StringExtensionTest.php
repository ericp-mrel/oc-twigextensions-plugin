<?php

namespace VojtaSvoboda\TwigExtensions\Tests\StringExtension;

use System\Classes\PluginManager;

class StringExtensionTest extends \PluginTestCase
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

    public function test_it_adds_twig_string_extension()
    {
        $twig = $this->getTwig();

        $this->assertTrue($twig->hasExtension(\Twig\Extra\String\StringExtension::class));
    }

    public function test_wordwrap_filter_for_five()
    {
        $twig = $this->getTwig();

        $template = "{{ 'Symfony String + Twig = <3'|u.wordwrap(5)|raw }}";

        $twigTemplate = $twig->createTemplate($template);
        $this->assertEquals("Symfony\nString\n+\nTwig\n= <3", $twigTemplate->render());
    }
}
