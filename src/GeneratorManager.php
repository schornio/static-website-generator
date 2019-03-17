<?php namespace SchornIO\StaticWebsiteGenerator;

  class GeneratorManager {

    public static function runAllGenerators (
      string $version,
      string $token,
      string $componentBasePath = "./components",
      string $distPath = "./dist"
    ) {

      $distPath = realpath($distPath);
      $generatorPaths = glob("$componentBasePath/**/generator.php");

      $client = new Storyblok($version, $token);
      $stories = $client->getAllStories();

      foreach ($generatorPaths as $generatorPath) {

        $componentNameResult = null;
        preg_match("/([^\/]+)\/generator\.php$/", $generatorPath, $componentNameResult);
        $componentName = $componentNameResult[1];

        $generatorFunction = $componentName . "_generator";

        require($generatorPath);
        $generatorFunction($distPath, $stories);

      }

    }

  }

?>
