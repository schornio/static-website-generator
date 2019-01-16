<?php namespace SchornIO\StaticWebsiteGenerator;

  use \GuzzleHttp\Client;

  class Storyblok {

    function __construct($version, $token) {

      $this->version = $version;
      $this->token = $token;

      $this->client = new Client([
        "base_uri" => "https://api.storyblok.com/v1/cdn/stories/",
        "http_errors" => false,
      ]);

      $this->linkClient = new Client([
        "base_uri" => "https://api.storyblok.com/v1/cdn/links/",
        "http_errors" => false,
      ]);

    }

    public function getStoryBySlug ($slug, $options = []) {

      $options["cv"] = floor(microtime(true) * 1000);
      $options["version"] = $this->version;
      $options["token"] = $this->token;

      $response = $this->client->request(
        "GET",
        $slug,
        [
          "query" => $options
        ]
      );

      if ($response->getStatusCode() == 200) {

        $data = json_decode((string)$response->getBody(), true);
        return $data;

      }

      return null;

    }

    public function getLinkById ($linkId, $options = []) {

      $options["token"] = $this->token;

      $response = $this->linkClient->request(
        "GET",
        $linkId,
        [
          "query" => $options
        ]
      );

      if ($response->getStatusCode() == 200) {

        $data = json_decode((string)$response->getBody(), true);
        return $data["link"];

      }

      return null;

    }

    public function getAllStories() {

      return $this->getStoryBySlug("");

    }

    public static function getCurrentSlug () {

      $url = $_SERVER['REQUEST_URI'];
      $urlObject = parse_url($url);

      $path = $urlObject["path"];

      $slug = substr($path, 1); // remove leading "/"

      if ($slug == "") {
        $slug = "home";
      }

      return $slug;

    }

    public function validateRequest($credentials, $previewToken) {

      $spaceId = $credentials["space_id"];
      $timestamp =  $credentials["timestamp"];

      $validateToken = $credentials["token"];

      return
        $validateToken === sha1("$spaceId:$previewToken:$timestamp") &&
        abs(time() - intval($timestamp)) < 60;

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

  }

?>
