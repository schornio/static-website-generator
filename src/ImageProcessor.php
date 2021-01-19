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
            imagealphablending($image, false);
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

              "x" => floor($cropX),
              "y" => floor($cropY),

              "width" => floor($cropWidth),
              "height" => floor($cropHeight),

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

            // `dimensions + 1` because of resizing artifacts: 1px black border right and bottom
            $image = imagescale($image, floor($resizeWidth) + 1, floor($resizeHeight) + 1, IMG_BESSEL);
            $image = imagecrop($image, [

              "x" => 0,
              "y" => 0,

              "width" => floor($resizeWidth),
              "height" => floor($resizeHeight),

            ]);

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
          imagejpeg($image, $imageRelativePath, 95);
          imagejpeg($image, NULL, 95);
          break;

        case "image/png":
          imagesavealpha($image, true);

          imagepng($image, $imageRelativePath);
          imagepng($image);
          break;

        default:
          return null;

      }

    }

  }

?>
