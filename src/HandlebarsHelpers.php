<?php namespace SchornIO\StaticWebsiteGenerator;

  class HandlebarsHelpers {

    public static function echo () {

      return function ($context, $options) {

        return $context;

      };

    }

    public static function toJSON () {

      return function ($context, $options) {

        $jsonOptions = 0;

        if (isset($options) &&
            isset($options["hash"]) &&
            isset($options["hash"]["pretty"]) &&
            $options["hash"]["pretty"] == true) {

          $jsonOptions |= JSON_PRETTY_PRINT;

        }

        return json_encode($context, $jsonOptions);

      };

    }

    public static function markdown () {

      return function ($context, $options) {

        $parsedown = new Parsedown();

        return $parsedown->text($context);

      };

    }

    public static function getStory () {

      return function ($options) {

        $root = $options["data"]["root"];
        $config = $options["data"]["root"]["config"];

        $client = new SchornIO\StaticWebsiteGenerator\Storyblok($config["version"], $config["token"]);
        $story = $client->getStoryBySlug($options["hash"]["getBySlug"]);

        $options["_this"][$options["hash"]["assign"]] = $story["story"];

      };

    }

    public static function useDynamic () {

      return function ($options) {

        global $renderDynamicContent;
        $renderDynamicContent = true;

      };

    }

  }

?>
