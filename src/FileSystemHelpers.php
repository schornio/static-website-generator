<?php namespace SchornIO\StaticWebsiteGenerator;

  class FileSystemHelpers {

    public static function deleteFileOrDirectory (string $path) {

      if (is_dir($path)) {

        $subPaths = glob("$path/*");

        foreach ($subPaths as $subPath) {

          FileSystemHelpers::deleteFileOrDirectory($subPath);

        }

        rmdir($path);

      }

      if (is_file($path)) {

        unlink($path);

      }

    }

    public static function cleanupDirectory (string $path, Array $excludePaths) {

      $realPath = realpath($path);
      $realExcludePaths = [];

      foreach ($excludePaths as $excludePath) {

        $realExcludePath = realpath("$realPath/$excludePath");
        array_push($realExcludePaths, $realExcludePath);

      }

      $subPaths = glob("$realPath/*");

      var_dump($subPaths);

      foreach ($subPaths as $subPath) {

        if (!in_array($subPath, $realExcludePaths)) {

          FileSystemHelpers::deleteFileOrDirectory($subPath);

        }

      }

    }

  }

?>
