<?php

namespace App\Lib\TwigExtensions {

	use Twig\Extension\AbstractExtension;
	use Twig\TwigFunction;

	final class TwigGlobalExtension extends AbstractExtension
	{
		public function getFunctions()
		{
			return [
				new TwigFunction('app', 'twig_global', ['is_safe' => ['html'], 'needs_context' => true, 'needs_environment' => true, 'is_variadic' => true]),
			];
		}
	}

	class_alias('App\Lib\TwigExtensions\TwigGlobalExtension', 'Twig_Extension_Global');
}

namespace {
	use Twig\Environment;
	use Twig\Template;
	use Twig\TemplateWrapper;

	function twig_global(Environment $env, $context, ...$vars)
	{
		return $_SERVER;
	}
}