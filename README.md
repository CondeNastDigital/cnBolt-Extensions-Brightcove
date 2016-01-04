# cnBolt-Extensions-Brightcove

A special file type that stores a reference to a Broightcove video and can copy connected meta properties of the video from brightcove to other configurable fields.
This extension also imports the video thumbnail and provides a template for the default brightcove player.

## Installation
This extension is hosted in a private git repository and has to be installed manually.

1.) Edit your extensions/composer.json file and add the **cnd-brightcove** repository:
```
    "repositories": {
        "packagist": false,
        "bolt": {
            "type": "composer",
            "url": "https://extensions.bolt.cm/satis/"
        },
        "cnd-brightcove": {
            "type": "git",
            "url": "https://github.com/CondeNastDigital/cnBolt-Extensions-Brightcove.git"
        }
    },
```
2.) Change to the extensions folder and install via composer.
```
composer require cnd/brightcove
```
Installing or updating via the Bolt admin interface is also possible but would require the web-server's user to have proper access to the GitHup repository. This is usually not the case.

## Configuration
To use the extension, you have to configure the extension itself and create a proper contenttype for it. You can then use a twig filter to generate the player or just write your own.

** Sample **
```
service:
    authapi: https://oauth.brightcove.com/v3/access_token?grant_type=client_credentials
    cmsapi: https://cms.api.brightcove.com/v1/accounts/{account}/

    account: 1234567890
    client: 1234567890
    secret: 1234567890-1234567890-1234567890

    search-filter:
        tags: can-be-used
        custom.category: SuperStuff
```

### Contenttype
To use the extension inside a contenttype, it has to be configured properly. Create a contenttype for your video objects and add a field of type **brightcove** to it.

The **import** property of that field defines which data is copied to which field of your contenttype.
The fields under **text** are copied as strings. 
The fields under **images** are treated as image urls, downloaded and stored into your fields of type image.
 
All changes are only applied when your page is saved, not before. Only the still is stored under files upon import, but not yet saved into your image field.

** Sample **
```
video:
    name: Videos
    singular_name: Video
    fields:
        title:
            type: text
            label: Titel
        bcid:
            type: brightcove
            label: Brightcove Video
            import:
                text:
                    title: name
                    description: description
                    category: custom_fields.category
                images:
                    image: images.thumbnail.src
        category:
            type: text
        title:
            type: text
        description:
            type: textarea
        image:
            type: image
```

### Templating
A twig filter with name **brightcovePlayer** is provided. It takes an Brightcove id and generates a HTML5 player for it.

You can of course also just ignore the filter and create your own player.

```
<div>
    {{ record.brightcove|brightcovePlayer({some: options}) }}
</div>
``

## ToDo / Wishlist

  * **Nice To Have** Use a working alternative to *Bolt.conf("paths.root")* to get web root inside js scripts
  * **Nice to Have** Find a better way to update the image preview after change from importer
  * **Major** Create the twig function for the brightcove player
  * **Minor** Make the player template overridable by theme