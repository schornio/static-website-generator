<?php namespace SchornIO\StaticWebsiteGenerator;

  class TemplateRenderer {

    public static function renderAndSaveAllPages (
      string $version,
      string $token,
      string $distPath = "./dist"
    ) {

      $renderStartDate = new \DateTime();

      $distPath = realpath($distPath);

      $rendererPath = realpath("$distPath/private/render.php");
      $renderer = require_once($rendererPath);

      $client = new Storyblok($version, $token);
      $stories = $client->getAllStories();

      $excludePaths = [ "public", "private", ".htaccess" ];
      $fileActionLog = FileSystemHelpers::cleanupDirectory($distPath, $excludePaths);

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

          $fileActionResult = FileSystemHelpers::createDirectory($distPath . $fullSlug);
          array_push($fileActionLog, $fileActionResult);

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

        $fileActionResult = FileSystemHelpers::createFile($distPath . $fullSlug . $fileName, $renderedStory);
        array_push($fileActionLog, $fileActionResult);

      }

      $renderEndDate = new \DateTime();
      $deployLogString = json_encode([
        "renderStart" => $renderStartDate->format("c.u"),
        "renderFinished" => $renderEndDate->format("c.u"),
        "renderDuration" => $renderEndDate->diff($renderStartDate)->format("%I:%S.%F"),
        "fileActions" => $fileActionLog,
      ], JSON_PRETTY_PRINT);
      FileSystemHelpers::createFile("$distPath/private/deploy.log.json", $deployLogString);

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
