# SchornIO\\StaticWebsiteGenerator

```
composer require schornio/static-website-generator
```

## Scripts

- **sio-swg-compile**: compiles `./components` to stdout php-script
- **sio-swg-generator**: each component has the opportunity to run a script at render time (`generator.php`, `function <component_name>_generator ()`)
- **sio-swg-render**: overwrites `./dist` with given Storyblok content

## Handlebar helpers

- `echo`
- `join`
- `replace`
- `toJSON`
- `toAlphaNum`
- `switch`
- `case`
- `useDynamic`
- `markdown`
- `getStory`
- `getStories`
- `url`
- `resize`
- `resolveSlug`
- `isActiveStory`
- `renderTimestamp`
- (`storyblokBridge`)

## Image Processor must be located at `public/images`

```
RewriteCond %{REQUEST_URI} ^/public/images/.+
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^.*$ public/imageProcessor.php [L]
```

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
