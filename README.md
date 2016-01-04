# cnBolt-Extensions-Shortcodes

Introduces a special tag that allows the in place rendering of a different object inside a block of text.
Also provides a CKEDITOR plugin to search content and generate shortcode tags automatically.

## Installation
This extension is hosted in a private git repository and has to be installed manually.

1.) Edit your extensions/composer.json file and add the **cnd-shortcodes** repository:
```
    "repositories": {
        "packagist": false,
        "bolt": {
            "type": "composer",
            "url": "https://extensions.bolt.cm/satis/"
        },
        "cnd-shortcodes": {
            "type": "git",
            "url": "https://github.com/CondeNastDigital/cnBolt-Extensions-Shortcodes.git"
        }
    },
```
2.) Change to the extensions folder and install via composer.
```
composer require cnd/shortcodes
```
Installing or updating via the Bolt admin interface is also possible but would require the web-server's user to have proper access to the GitHup repository. This is usually not the case.

## Configuration
To use the extension inside a contenttype, it has to be configured properly.

1.) Set the template to use for any embeddable object in one of these places. The list will be checked from top to bottom, the last possible configuration value will be used:
- Set a global embedding template for all types inside the main config.yml **general/embedding_template**
- Set a theme specific template for all types inside a config.yml of a theme **theme/embedding_template**
- Set a contenttype specific template inside the contenttype config **embedding_template**
- Set a selectbox for a contenttype for the embedding templates in a field with type **templateselect** and type **embedding** (Not tested!)
 
2.) Add the shortCodes filter inside the template for the attribute containing possible shortcodes.
The parameter inside the filters parenthesis is **optional** and limits shortcodes to certain contenttypes.
```
<div class="somecontent">
{{ attribute(record, somecontent)|shortCodes({"contenttypes": ["page","entry"]}) }}
</div>
```

Since the filter does not know which field and contenttype it is beeing applied to, it cant just use the config from step 3's configuration.

3.) **Optional** If you want to use the CKEDITOR plugin, it needs to be added to the respective contenttypes toolbars. Since
Bolt's CKEDITOR config uses item-by-item toolbars, plugins wont be added automatically. You need to setup toolbars from scratch.

Open the appropriate contenttype's field in your **contenttypes.yml** and add the configuration:
```
        # A field inside a contenttype:
        somefield:
            type: html
            height: 300px
            options:
                shortcode:
                    allowedTypes: ["pages", "entries"]     # allows selecting these two contenttypes in the CKEDITOR plugin. Specify the key from your contenttypes.yml here
                ckeditor:
                    extraPlugins: 'shortcode'              # Tell CKEDITOR to load our plugin
                    toolbar:                               # Configure all toolbars from scratch. Sadly we can't just add things here
                        - {'name':'Basic','items':['Bold', 'Italic', "Underline", "Strike"]}
                        - {'name':'List','items':["NumberedList", "BulletedList"]}
                        - {'name':'Link','items':["Link", "Unlink"]}
                        - {'name':'Tools','items':["RemoveFormat", "Maximize"]}
                        - {'name':'Plugins','items':["addShortCode"]}    # Our new toolbar group
```

## Usage
Now the editor can enter appropriate shortcodes in his text boxes for the fields changed in step 2.

**Syntax**:
```
[shortcode:<slug>|template=<template>]
```

* *slug* - slug of the contentobject to insert here
* *template* - optionaly use this template to render the object (Avoid this, this leads to chaotic content!)

**Sample**
```
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Qui est in parvis malis. Quod vestri non item. Satisne ergo pudori consulat, si quis sine teste libidini pareat? Qui ita affectus, beatum esse numquam probabis;

[shortcode:/page/sit-enim-idem-caecus-debilis|template=page_embedding2]

Quae iam oratio non a philosopho aliquo, sed a censore opprimenda est.

[shortcode:/news/somethingelse]
```
