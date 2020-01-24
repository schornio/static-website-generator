<?php namespace SchornIO\StaticWebsiteGenerator;

  use \LightnCandy\LightnCandy;

  class TemplateCompiler {

    private static function getComponentTemplates(string $componentBasePath) {

      $partials = [];
      $componentRealBasePath = realpath($componentBasePath);

      $componentPaths = glob("$componentRealBasePath/**/template.*.hbs");

      foreach ($componentPaths as $componentPath) {

        $componentMatch = [];
        preg_match("/\/([^\/]+)\/[^\/]*$/", $componentPath, $componentMatch);

        $componentName = $componentMatch[1];
        $componentTemplateFile = file_get_contents($componentPath);

        $partials[$componentName] = $componentTemplateFile;

      }

      return $partials;

    }

    public static function compile (
      string $templateIndex = "{{> page}}" ,
      string $componentsPath = "./components"
    ) {

      $flags =
        LightnCandy::FLAG_ERROR_EXCEPTION |
        LightnCandy::FLAG_HANDLEBARSJS |
        LightnCandy::FLAG_RUNTIMEPARTIAL |
        LightnCandy::FLAG_ADVARNAME;

      $helpers = Array(
        'echo' => HandlebarsHelpers::echo(),
        'join' => HandlebarsHelpers::join(),
        'replace' => HandlebarsHelpers::replace(),
        'toJSON' => HandlebarsHelpers::toJSON(),
        'toAlphaNum' => HandlebarsHelpers::toAlphaNum(),
        'switch' => HandlebarsHelpers::switch(),
        'case' => HandlebarsHelpers::case(),
        'default' => HandlebarsHelpers::default(),
        'useDynamic' => HandlebarsHelpers::useDynamic(),
        'markdown' => HandlebarsHelpers::markdown(),
        'getStory' => HandlebarsHelpers::getStory(),
        'getStories' => HandlebarsHelpers::getStories(),
        'url' => HandlebarsHelpers::url(),
        'resize' => HandlebarsHelpers::resize(),
        'resolveSlug' => HandlebarsHelpers::resolveSlug(),
        'isActiveStory' => HandlebarsHelpers::isActiveStory(),
        'renderTimestamp' => HandlebarsHelpers::renderTimestamp(),
        'storyblokToken' => HandlebarsHelpers::storyblokToken(),
      );

      $partials = TemplateCompiler::getComponentTemplates($componentsPath);
      $compiledTemplate = LightnCandy::compile($templateIndex, array(

        "flags" => $flags,
        "helpers" => $helpers,
        "partials" => $partials,

      ));

      return "<?php $compiledTemplate ?>";

    }

  }

?>
