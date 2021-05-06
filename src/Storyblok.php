<?php namespace SchornIO\StaticWebsiteGenerator;

  use \GuzzleHttp\Client;

  class Storyblok {

    private static $storyCache = null;
    private static $linkCache = null;

    function __construct($version, $token) {

      $this->version = $version;
      $this->token = $token;

    }

    public function getStoryBySlug ($slug, $options = []) {

      $stories = $this->getCachedStories();

      foreach ($stories as $story) {

        if ($story["full_slug"] === $slug) {

          return $story;

        }

      }

      return null;

    }

    public function getStoriesByTags ($tags = [], $options = []) {

      $stories = $this->getCachedStories();
      $storiesWithTags = [];

      foreach ($stories as $story) {

        if (!empty(array_intersect($tags, $story["tag_list"]))) {
          array_push($storiesWithTags, $story);
        }

      }

      return $storiesWithTags;

    }

    public function getStoriesWithSlug ($slug, $options = []) {

      $stories = $this->getCachedStories();
      $storiesWithSlug = [];

      $slugLength = strlen($slug);

      foreach ($stories as $story) {

        if (substr($story["full_slug"], 0, $slugLength) === $slug) {
          $pathWithoutSlug = substr($story["full_slug"], $slugLength);
          if (strpos($pathWithoutSlug, "/") === FALSE) {
            array_push($storiesWithSlug, $story);
          }
        }

      }

      return $storiesWithSlug;

    }

    public function getStoriesStartingWithSlug ($slug, $options = []) {

      $stories = $this->getCachedStories();
      $storiesStartingWithSlug = [];

      $slugLength = strlen($slug);

      foreach ($stories as $story) {

        if (substr($story["full_slug"], 0, $slugLength) === $slug) {
          array_push($storiesStartingWithSlug, $story);
        }

      }

      return $storiesStartingWithSlug;

    }

    public function getLinkById ($linkId, $options = []) {

      $links = $this->getCachedLinks();

      foreach ($links as $link) {

        if ($link["uuid"] === $linkId) {

          return $link;

        }

      }

      return null;

    }

    public function getAllStories () {

      $stories = $this->getCachedStories();
      return $stories;

    }

    public static function validateRequest($credentials, $previewToken) {

      $spaceId = $credentials["space_id"];
      $timestamp =  $credentials["timestamp"];

      $validateToken = $credentials["token"];

      return
        $validateToken === sha1("$spaceId:$previewToken:$timestamp") &&
        abs(time() - intval($timestamp)) < 60 * 60;

    }

    private function getStoriesFromAPI($page = 1, $perPage = 100) {
      if ($page > 10) {
        // max 1000 records
        return [];
      }

      $client = new Client([
        "base_uri" => "https://api.storyblok.com/v1/cdn/stories/",
        "http_errors" => false,
      ]);

      $slug = "";

      $options["cv"] = floor(microtime(true) * 1000);
      $options["version"] = $this->version;
      $options["token"] = $this->token;
      $options["per_page"] = $perPage;
      $options["page"] = $page;

      $response = $client->request(
        "GET",
        $slug,
        [
          "query" => $options
        ]
      );

      if ($response->getStatusCode() == 200) {

        $data = json_decode((string)$response->getBody(), true);

        if (is_array($data["stories"])) {

          $stories = $data["stories"];

          foreach ($stories as &$story) {

            $story["full_slug"] = Storyblok::getSlug($story["full_slug"]);

          }

          if (count($stories) === $perPage) {
            return array_merge($stories, $this->getStoriesFromAPI($page + 1, $perPage));
          } else {
            return $stories;
          }

        }

      }

      return [];

    }

    private function getCachedStories () {

      if (Storyblok::$storyCache === null) {

        $links = $this->getCachedLinks();
        $stories = $this->getStoriesFromAPI();

        $sortedStories = Storyblok::sortStoriesByStoryblokOrder($stories, $links);

        Storyblok::$storyCache = $sortedStories;
      }

      return Storyblok::$storyCache;

    }

    private function getCachedLinks () {

      if (Storyblok::$linkCache === null) {

        $client = new Client([
          "base_uri" => "https://api.storyblok.com/v1/cdn/links/",
          "http_errors" => false,
        ]);

        $linkId = "";

        $options["cv"] = floor(microtime(true) * 1000);
        $options["version"] = $this->version;
        $options["token"] = $this->token;
        $options["per_page"] = 100;

        $response = $client->request(
          "GET",
          $linkId,
          [
            "query" => $options
          ]
        );

        if ($response->getStatusCode() == 200) {

          $data = json_decode((string)$response->getBody(), true);

          if (is_array($data["links"])) {

            Storyblok::$linkCache = $data["links"];

          }

        }

      }

      return Storyblok::$linkCache;

    }

    public static function getSlug ($url) {

      $urlObject = parse_url($url);

      $slug = $urlObject["path"];

      // remove leading "/"
      if (substr($slug, 0, 1) === "/") {
        $slug = substr($slug, 1);
      }

      // remove trailing "/"
      if (substr($slug, -1, 1) === "/") {
        $slug = substr($slug, 0, -1);
      }

      if ($slug === "") {
        $slug = "home";
      }

      return $slug;

    }

    public static function getStoryblokBridge ($previewToken) {

        $storyblokBridgeHTML = "\n";
        $storyblokBridgeHTML .= "<script src=\"//app.storyblok.com/f/storyblok-latest.js?t=$previewToken\" type=\"text/javascript\"></script>\n";
        $storyblokBridgeHTML .= "<script type=\"text/javascript\">\n";
        $storyblokBridgeHTML .= "  storyblok.on(['published', 'change'], function() {\n";
        $storyblokBridgeHTML .= "    location.reload(true);\n";
        $storyblokBridgeHTML .= "  })\n";
        $storyblokBridgeHTML .= "</script>\n";

        return $storyblokBridgeHTML;

    }

    public static function sortStoriesByStoryblokOrder ($storyArray, $linkArray) {
      $folderPositionMap = [];

      foreach ($linkArray as $link) {
        if ($link["is_folder"]) {
          $folderPositionMap[$link["slug"]] = $link["position"];
        }
      }

      $storyPositionArray = [];

      foreach ($storyArray as $story) {
        $storySlugComponentArray = explode("/", $story["full_slug"]);

        if (!$story["is_startpage"]) {
          array_pop($storySlugComponentArray);
        }

        $currentStoryPositionArray = [];
        $currentParentSlug = "";

        foreach ($storySlugComponentArray as $storySlugComponent) {
          $currentParentSlug .= $storySlugComponent;

          $position = $folderPositionMap[$currentParentSlug];
          array_push($currentStoryPositionArray, $position);

          $currentParentSlug .= "/";
        }

        array_push($currentStoryPositionArray, $story["position"]);

        $storyPosition = [
          "story" => $story,
          "positionArray" => $currentStoryPositionArray,
        ];

        array_push($storyPositionArray, $storyPosition);
      }

      usort($storyPositionArray, function ($storyPositionA, $storyPositionB) {
        $positionArrayA = $storyPositionA["positionArray"];
        $positionArrayB = $storyPositionB["positionArray"];
        $positionArrayALength = count($positionArrayA);

        for ($i=0; $i < $positionArrayALength; $i++) {
          if (isset($positionArrayB[$i])) {
            if ($positionArrayA[$i] === $positionArrayB[$i]) {
              continue;
            } else {
              return $positionArrayA[$i] - $positionArrayB[$i];
            }
          } else {
            return 1;
          }
        }

        return 0;
      });

      $sortedStoryArray = [];

      foreach ($storyPositionArray as $storyPosition) {
        array_push($sortedStoryArray, $storyPosition["story"]);
      }

      return $sortedStoryArray;
    }

    public static function createStory($story, $spaceId, $managementToken)
    {
      $client = new Client([
        "base_uri" => "https://mapi.storyblok.com/",
        "http_errors" => false,
      ]);
      $response = $client->request(
        "POST",
        "v1/spaces/$spaceId/stories/",
        [
          'json' => ["story" => $story],
          'headers' => [
            'Authorization' => $managementToken,
          ]
        ]
      );
      $statusCode = $response->getStatusCode();
      return $statusCode === 200 || $statusCode === 201;
    }

  }

?>
