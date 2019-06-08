<?php namespace SchornIO\StaticWebsiteGenerator;

  class FileSystemHelpers {

    public static function mimeTypeFromPath (string $path) {

      preg_match("/\.([^\.]+)$/", $path, $pathComponents);

      $extension = strtolower($pathComponents[1]);

      switch ($extension) {

        case "jpg":
        case "jpeg":
          return "image/jpeg";

        case "png":
          return "image/png";

        default:
          return "";

      }

    }

    public static function createFile(string $path, string $content) {

      $result = file_put_contents($path, $content);
      return [
        "action" => "file_put_contents",
        "result" => $result,
        "path" => $path,
      ];

    }

    public static function createDirectory (string $path) {

      $result = "exists";

      if (!is_dir($path)) {

        $result = @mkdir($path, 0775, true);

      }

      return [
        "action" => "mkdir",
        "result" => $result,
        "path" => $path,
      ];

    }

    public static function deleteFileOrDirectory (string $path) {

      $actionLog = [];

      if (is_dir($path)) {

        // https://stackoverflow.com/a/49031383
        $subPaths = glob("$path/{*,.[!.]*,..?*}", GLOB_BRACE);

        foreach ($subPaths as $subPath) {

          $subActionLog = FileSystemHelpers::deleteFileOrDirectory($subPath);
          $actionLog = array_merge($actionLog, $subActionLog);

        }

        $result = rmdir($path);
        array_push($actionLog, [
          "action" => "rmdir",
          "result" => $result,
          "path" => $path,
        ]);

      }

      if (is_file($path)) {

        $result = unlink($path);
        array_push($actionLog, [
          "action" => "unlink",
          "result" => $result,
          "path" => $path,
        ]);

      }

      return $actionLog;

    }

    public static function cleanupDirectory (string $path, Array $excludePaths) {

      $realPath = realpath($path);
      $realExcludePaths = [];

      foreach ($excludePaths as $excludePath) {

        $realExcludePath = realpath("$realPath/$excludePath");
        array_push($realExcludePaths, $realExcludePath);

      }

      $actionLog = [];
      // https://stackoverflow.com/a/49031383
      $subPaths = glob("$realPath/{*,.[!.]*,..?*}", GLOB_BRACE);

      foreach ($subPaths as $subPath) {

        if (!in_array($subPath, $realExcludePaths)) {

          $subActionLog = FileSystemHelpers::deleteFileOrDirectory($subPath);
          $actionLog = array_merge($actionLog, $subActionLog);

        }

      }

      return $actionLog;

    }

  }

?>
