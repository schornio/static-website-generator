<?php namespace SchornIO\StaticWebsiteGenerator;

  use \GuzzleHttp\Client;

  class ImageProcessor {

    public static function process (string $path, Array $allowedOrigins) {

      preg_match("/^([^\/]+)\/([^\/]+)\/(.*)/", $path, $pathComponents);

      $instructions = $pathComponents[1];
      $origin = $pathComponents[2];
      $originPath = $pathComponents[3];
      $fileType = FileSystemHelpers::mimeTypeFromPath($path);

      if (in_array($origin, $allowedOrigins)) {

        preg_match("/(\d+)x(\d+)/", $instructions, $instructionComponents);

        $resizeWidth = intval($instructionComponents[1]);
        $resizeHeight = intval($instructionComponents[2]);

        switch ($fileType) {

          case "image/jpeg":
            $image = imagecreatefromjpeg("http://" . $origin . "/" . $originPath);
            break;

          case "image/png":
            $image = imagecreatefrompng("http://" . $origin . "/" . $originPath);
            imagesavealpha($image, true);
            break;

          default:
            return null;

        }

        if ($resizeWidth !== 0 || $resizeHeight !== 0) {

          $imageWidth = imagesx($image);
          $imageHeight = imagesy($image);

          if ($resizeWidth !== 0 && $resizeHeight !== 0 ) {


            $cropAspectRatio = $resizeWidth / $resizeHeight;
            $imageAspectRatio = $imageWidth / $imageHeight;

            if ($cropAspectRatio > $imageAspectRatio ) {

              $cropWidth = $imageWidth;
              $cropHeight = $imageHeight * $imageAspectRatio / $cropAspectRatio;

            } else {

              $cropWidth = $imageWidth * $cropAspectRatio / $imageAspectRatio;
              $cropHeight = $imageHeight;

            }

            $cropX = ($imageWidth - $cropWidth) / 2;
            $cropY = ($imageHeight - $cropHeight) / 2;

            $image = imagecrop($image, [

              "x" => $cropX,
              "y" => $cropY,

              "width" => $cropWidth,
              "height" => $cropHeight,

            ]);

          }

          if ($resizeWidth === 0) {

            $aspectRatio = $imageWidth / $imageHeight;
            $resizeWidth = $aspectRatio * $resizeHeight;

          }

          if ($resizeHeight === 0) {

            $aspectRatio = $imageHeight / $imageWidth;
            $resizeHeight = $aspectRatio * $resizeWidth;

          }

          if ($resizeWidth < $imageWidth && $resizeHeight < $imageHeight) {

            $image = imagescale($image, $resizeWidth, $resizeHeight);

          }

          return [
            "image" => $image,
            "type" => $fileType,
          ];

        }

      }

    }

    public static function persistAndRespond ($imageResult, $imageRelativePath) {

      $fileType = $imageResult["type"];
      $image = $imageResult["image"];

      header("Content-Type: $fileType");
      header("X-Generated: Script");

      switch ($fileType) {

        case "image/jpeg":
          imagejpeg($image, $imageRelativePath);
          imagejpeg($image);;
          break;

        case "image/png":
          imagepng($image, $imageRelativePath);
          imagepng($image);
          break;

        default:
          return null;

      }

    }

  }

?>
