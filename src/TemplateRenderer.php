<?php namespace SchornIO\StaticWebsiteGenerator;

  class TemplateRenderer {

    public static function renderAndSaveAllPages (
      string $version,
      string $token,
      string $distPath = "./dist"
    ) {

      $distPath = realpath($distPath);

      $rendererPath = realpath("$distPath/private/render.php");
      $renderer = require_once($rendererPath);

      $client = new Storyblok($version, $token);
      $stories = $client->getAllStories();

      $excludePaths = [ "public", "private", ".htaccess" ];
      FileSystemHelpers::cleanupDirectory($distPath, $excludePaths);

      foreach ($stories as $story) {

        $fileName = "/index.html";
        $fullSlug = "/" . $story["full_slug"];

        if ($fullSlug == "/home") {

          $fullSlug = "";

        }

        $specialSlugMatch = null;

        if (preg_match("/(.*)?(\/[\w-]*)--fileextension-(\w+)$/", $fullSlug, $specialSlugMatch)) {

          $fullSlug = $specialSlugMatch[1];
          $fileName = $specialSlugMatch[2] . "." . $specialSlugMatch[3];

        }

        if ($fullSlug != "") {

          FileSystemHelpers::createDirectory($distPath . $fullSlug);

        }

        $config = [
          "token" => $token,
          "version" => $version,
        ];

        $renderData = [
          "story" => $story,
          "config" => $config,
        ];

        if ($version == "draft") {

          $renderData["storyblokBridge"] = Storyblok::getStoryblokBridge($token);

        }

        // Better solutions welcome
        global $renderDynamicContent;
        $renderDynamicContent = false;

        $renderedStory = ltrim($renderer($renderData));

        if ($renderDynamicContent) {

          $fileName = "/index.php";

        }

        file_put_contents($distPath . $fullSlug . $fileName, $renderedStory);

      }

    }

    public static function renderPage (
      string $version,
      string $token,
      string $distPath = "./dist"
    ) {

      $distPath = realpath($distPath);

      $rendererPath = realpath("$distPath/private/render.php");
      $renderer = require_once($rendererPath);

      $client = new Storyblok($version, $token);
      $slug = Storyblok::getSlug($_SERVER['REQUEST_URI']);
      $story = $client->getStoryBySlug($slug);

      $config = [
        "token" => $token,
        "version" => $version,
      ];

      $renderData = [
        "story" => $story,
        "config" => $config,
      ];

      if ($version == "draft") {

        $renderData["storyblokBridge"] = Storyblok::getStoryblokBridge($token);

      }

      // Better solutions welcome
      global $renderDynamicContent;
      $renderDynamicContent = false;

      $renderedStory = ltrim($renderer($renderData));

      if ($renderDynamicContent) {

        $tmpFileName = tempnam("/tmp", "sio-swg-renderer");
        file_put_contents($tmpFileName, $renderedStory);

        require($tmpFileName);

        unlink($tmpFileName);

      } else {

        echo($renderedStory);

      }

    }

  }

?>
