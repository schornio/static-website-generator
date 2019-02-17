<?php namespace SchornIO\StaticWebsiteGenerator;

  class HandlebarsHelpers {

    public static function echo () {

      return function ($context, $options) {

        return $context;

      };

    }

    public static function join () {

      return function ($context, $options) {

        if (is_array($context)) {

          $delimiter = " ";

          if (isset($options) &&
              isset($options["hash"]) &&
              isset($options["hash"]["delimiter"]))
          {

            $delimiter = $options["hash"]["delimiter"];

          }

          return implode($delimiter, $context);

        } else {

          return $context;

        }

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

    public static function switch () {
      // FROM: https://github.com/wycats/handlebars.js/issues/927#issuecomment-200784792
      // (rewritten in php)

      return function ($context, $options) {

        $options["_this"]["_switch_value_"] = $context;
        $inner = $options["fn"]();
        unset($options["_this"]["_switch_value_"]);
        return $inner;

      };

    }

    public static function case () {
      // FROM: https://github.com/wycats/handlebars.js/issues/927#issuecomment-200784792
      // (rewritten in php)

      return function () {

        $args = func_get_args();

        $options = array_pop($args);
        $value = $options["_this"]["_switch_value_"];

        if (in_array($value, $args)) {

          return $options["fn"]();

        } else {

          return "";

        }

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

        $config = $options["data"]["root"]["config"];
        $client = new SchornIO\StaticWebsiteGenerator\Storyblok($config["version"], $config["token"]);

        if (isset($options["hash"]["getBySlug"])) {

          $story = $client->getStoryBySlug($options["hash"]["getBySlug"]);
          $options["_this"][$options["hash"]["assign"]] = $story;

        } else if (isset($options["hash"]["getById"])) {

          $link = $client->getLinkById($options["hash"]["getById"]);
          $story = $client->getStoryBySlug($link["slug"]);
          $options["_this"][$options["hash"]["assign"]] = $story;

        }

      };

    }

    public static function getStories () {

      return function ($options) {

        $config = $options["data"]["root"]["config"];
        $client = new SchornIO\StaticWebsiteGenerator\Storyblok($config["version"], $config["token"]);

        if (isset($options["hash"]["startsWithSlug"])) {

          $slug = $options["hash"]["startsWithSlug"];
          $stories = $client->getStoriesStartingWithSlug($slug);
          $options["_this"][$options["hash"]["assign"]] = $stories;

        } else if (isset($options["hash"]["getByTags"])) {

          $tags = $options["hash"]["getByTags"];
          $stories = $client->getStoriesByTags(explode(",", $tags));
          $options["_this"][$options["hash"]["assign"]] = $stories;

        } else {

          $stories = $client->getAllStories();
          $options["_this"][$options["hash"]["assign"]] = $stories;

        }

      };

    }

    public static function url () {

      return function ($context, $options) {

        if (isset($context) && isset($context["linktype"])) {

          if ($context["linktype"] === "url") {

            return $context["url"];

          }

          if ($context["linktype"] === "story") {


              $config = $options["data"]["root"]["config"];

              $client = new SchornIO\StaticWebsiteGenerator\Storyblok($config["version"], $config["token"]);
              $link = $client->getLinkById($context["id"]);

              return "/" . $link["slug"];

          }

        }

        return "";

      };

    }

    public static function resize () {

      return function ($image, $param) {

        $isSvg = substr($image, -4) === ".svg"; // ".svg".length === 4

        if (!$isSvg) {

          $imageService = "//img2.storyblok.com/";
          $resource = str_replace("//a.storyblok.com", "", $image);
          return $imageService . $param . $resource;

        }

        return $image;

      };

    }

    public static function useDynamic () {

      return function ($options) {

        global $renderDynamicContent;
        $renderDynamicContent = true;

      };

    }

    public static function resolveSlug () {

      return function ($slug) {

        if ($slug === "home") {

          return "";

        } else {

          return $slug;

        }

      };

    }

    public static function renderTimestamp () {

      return function () {

        return floor(microtime(true) * 1000);

      };

    }

  }

?>
