<?php namespace SchornIO\StaticWebsiteGenerator;

  use PHPMailer\PHPMailer\PHPMailer;

  class Mail {

    public static function create () {

      return new PHPMailer();

    }

  }
