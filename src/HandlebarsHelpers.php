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

    public static function toAlphaNum () {

      return function ($context, $options) {

        return preg_replace("/[^0-9a-zA-Z]/", "", $context);

      };

    }

    public static function replace () {

      return function ($context, $options) {

        if (isset($options) &&
            isset($options["hash"]) &&
            isset($options["hash"]["with"]))
        {

          $search = $context;
          $replace = $options["hash"]["with"];
          $subject = $options["fn"]();

          return str_replace($search, $replace, $subject);

        }

        return "";

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
        $options["_this"]["_switch_break_"] = false;
        $inner = $options["fn"]();
        unset($options["_this"]["_switch_value_"]);
        unset($options["_this"]["_switch_break_"]);
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

          $options["_this"]["_switch_break_"] = true;
          return $options["fn"]();

        } else {

          return "";

        }

      };

    }

    public static function default () {
      // FROM: https://github.com/wycats/handlebars.js/issues/927#issuecomment-200784792
      // (rewritten in php)

      return function () {

        $args = func_get_args();

        $options = array_pop($args);
        $break = $options["_this"]["_switch_break_"];

        if ($break == false) {

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

        if (isset($options["hash"]["withSlug"])) {

          $slug = $options["hash"]["withSlug"];
          $stories = $client->getStoriesWithSlug($slug);
          $options["_this"][$options["hash"]["assign"]] = $stories;

        } else if(isset($options["hash"]["startsWithSlug"])) {

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

          $imageService = "/public/images/";
          $resource = str_replace("//a.storyblok.com", "/a.storyblok.com", $image);
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

    public static function isActiveStory () {

      return function ($context, $options) {

        $currentSlug = $options["data"]["root"]["slug"];
        $url = $context;

        if (isset($options) &&
            isset($options["hash"]) &&
            isset($options["hash"]["isUrl"]) &&
            $options["hash"]["isUrl"] === true
        ) {

          $config = $options["data"]["root"]["config"];

          $client = new SchornIO\StaticWebsiteGenerator\Storyblok($config["version"], $config["token"]);
          $link = $client->getLinkById($context["id"]);

          $url = "/" . $link["slug"];

        }

        // Ignore trailing `/`
        if (substr($url, strlen($url) - 1, 1) === '/') {
          $url = substr($url, 0, strlen($url) - 1);
        }

        if (isset($options) &&
            isset($options["hash"]) &&
            isset($options["hash"]["exact"]) &&
            $options["hash"]["exact"] === true &&
            $url === $currentSlug
        ) {

          return $options["fn"]();

        }

        if (substr($currentSlug, 0, strlen($url)) === $url) {

          return $options["fn"]();

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
