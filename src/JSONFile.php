<?php namespace SchornIO\StaticWebsiteGenerator;

  class JSONFile {

    public static function get (string $jsonFilePath) {

      $jsonFile = file_get_contents($jsonFilePath);
      $jsonAssoc = json_decode($jsonFile, true);
      return $jsonAssoc;

    }

  }

?>
