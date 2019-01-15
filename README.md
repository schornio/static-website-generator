# SchornIO\\StaticWebsiteGenerator

```
composer require schornio/static-website-generator
```

## Scripts

- **sio-swg-compile**: compiles `./components` to stdout php-script
- **sio-swg-render**: overwrites `./dist` with given Storyblok content

## Handlebar helpers

- `echo`
- `toJSON`
- `switch`
- `case`
- `useDynamic`
- `markdown`
- `getStory`
- `url`
- (`storyblokBridge`)

## Difference between static (`.html`) and dynamic (`.php`) content

The hbs-helper `{{useDynamic}}` markes a template as dynamic content. If at least one template in the render-chain is marked as dynamic then the resulting file will end in `.php` instead of `.html`.

```php
{{useDynamic}}

<?php
// Dynamic content here

$data = json_decode("{{{toJSON data}}}");

?>
```

## Special slug `--fileextension-`

If a slug contains `(filename)--fileextension-(extension)` the file will be stored as `(filename).(extension)`. Eg `sitemap--fileextension-xml` will be converted to `sitemap.xml`
