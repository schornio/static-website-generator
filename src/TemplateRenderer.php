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
      $data = $client->getAllStories();

      $excludePaths = [ "public", "private" ];
      FileSystemHelpers::cleanupDirectory($distPath, $excludePaths);

      foreach ($data["stories"] as $story) {

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

        $renderedStory = $renderer($renderData);

        if ($renderDynamicContent) {

          $fileName = "/index.php";

        }

        file_put_contents($distPath . $fullSlug . $fileName, $renderedStory);

      }

    }

  }

?>
