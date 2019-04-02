<?php namespace SchornIO\StaticWebsiteGenerator;

  class FileSystemHelpers {

    public static function createFile(string $path, string $content) {

      $result = file_put_contents($path, $content);
      return [
        "action" => "file_put_contents",
        "result" => $result,
        "path" => $path,
      ];

    }

    public static function createDirectory (string $path) {

      $result = mkdir($path, 0775, true);
      return [
        "action" => "mkdir",
        "result" => $result,
        "path" => $path,
      ];

    }

    public static function deleteFileOrDirectory (string $path) {

      $actionLog = [];

      if (is_dir($path)) {

        $subPaths = glob("$path/*");

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
      $subPaths = glob("$realPath/*");

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
