# Apple News

The Apple News module allows you to publish your content to
[Apple News](https://developer.apple.com/news-publisher/).

## Important Disclaimer

Please note that at this time the Drupal 8 / 9 version of the module is under
active development. The module in its current state requires developer effort
to completely transform content into the Apple News format. To help with this
effort and monitor progress, check the
[issue queue](https://www.drupal.org/project/issues/applenews?categories=All).

## Table of contents

1. [TL;DR](#tldr)
2. [Configuration](#configuration)
    1. [General settings](#settings)
    2. [Template configuration](#template)
    2. [Channel configuration](#channel)
    3. [Field configuration](#field)
3. [Publish content](#publish)
    1. [Preview an article before publishing](#preview)
    2. [Delete an article from Apple News](#delete)
4. [Appendix A - Sample Apple News template YAML](#appendix-a)

## <a name="tldr"></a>TL;DR

1. Install the latest version of
   [Apple News](https://www.drupal.org/project/applenews) module using
   [composer](https://getcomposer.org/).
   ```shell
   composer require drupal/applenews
   ```
2. Enable the module
3. Configure the module
   1. Go to `/admin/config/services/applenews/settings` and add Apple News
     credentials. Read the
     [Use your CMS](https://support.apple.com/en-ca/guide/news-publisher/apd88c8447e6/icloud)
     section of the News Publisher User Guide for more details
   2. Go to `/admin/config/services/applenews` and add a template
   3. Go to `/admin/config/services/applenews/channel` to add one or more
      Channel ID(s)
   4. Go to the fields page of the entity bundle you wish to publish to Apple
      News (e.g. `/admin/structure/types/manage/artice/fields`) and add a new
      field of type `Apple News`
4. Publish content
   1. Create a new entity
   2. Check `Publish to Apple News` checkbox under `Applenews settings` tab
   3. Select template, channel and section(s)
   4. Save node as usual

## <a name="configuration"></a>Configuration

Follow these configuration instructions to start publishing your content. The
following assumes that you have already applied and been approved for your
Apple News channel. For more general information regarding Apple News and the
onboarding process, see the
[News Publisher Guide](https://support.apple.com/en-ca/guide/news-publisher/welcome/icloud).

### <a name="settings"></a>General settings

1. Login to [icloud.com](https://www.icloud.com/) and open the News Publisher
   app. Select your Channel and click Settings. Click Connect CMS in the right
   sidebar. Click Get an API key and note your Channel ID, Key ID and Secret.
2. In your Drupal site, navigate to the "Apple news credentials page"
   (`admin/config/content/applenews/settings`). Enter your Apple News
   credentials from step 1.

### <a name="channel"></a>Channel configuration

1. In your Drupal site, navigate to the "Apple news channels page"
   (`admin/config/content/applenews/settings/channels`) and enter the Channel ID
   from step 1. The Channel IDs are validated by the credentials that you
   entered on the [Settings](#settings) tab. If valid, it will fetch the channel
   information and add them to your site's list of channels.

### <a name="template"></a>Template configuration

Apple News "templates" direct how content is translated into the
[Apple News Format](https://developer.apple.com/documentation/apple_news/apple_news_format),
as Apple News components, for publishing on Apple News. These are stored as
config entities, and so can be defined in the UI or as YAML in your custom
module (for a sample template in YAML, see [Appendix A](#appendix-a) under
the Developer Documentation below).

1. In your Drupal site, navigate to `/admin/config/services/applenews`
2. Click Add Apple News Template
3. Give your template a label, select the Content type and fill out the Layout
   fields.
4. Add one or more Components. The module ships with a set of default
   components. Most components allow you to map a field to an Apple News
   components, with some allowing configuration of their own. Developers can add
   their own components, mapping arbitrary entity data to one or more Apple
   News components (see [Components](#components) under the Developer
   Documentation below).

### <a name="field"></a>Field configuration

In order to publish content to Apple News, the next step is to configure a field
of type Apple News.

1. Go to the fields page of the content bundle you wish to publish to Apple
   News (e.g. `/admin/structure/types/manage/artice/fields`) and add a new
   field of type `Apple News`.
2. Be sure to select a default value for the template your content will use, as
   well as the channel and section(s) to publish to. This isn't required, but
   will save editors work.
3. Go to the form display page (e.g.
   `/admin/structure/types/manage/artice/form-display`) and drag the Apple News
   field you added in step 1 out of the Disabled section.

## <a name="publish"></a>Publish content

After fully configuring the module (see the [previous section](#configuration)),
you're ready to publish content to Apple News.

1. Create content for a content type that you've configured to publish to Apple
   News.
2. Fill in your content as you typically would.
3. Check Publish to Apple News.
4. Select a Template.
5. Select a Channel as well as one or more Sections beneath it.
6. Check the Preview box if you'd like to publish to Apple News as a Preview.
7. Save the content. Your content will be published to Apple News.
8. Login to [icloud.com](https://www.icloud.com/) and open the News Publisher
   app, to verify the content was published.

### <a name="preview"></a>Preview an article before publishing

If you want to preview a post before sending it to Apple, you will need to first
download and install the Apple
[News Preview](https://developer.apple.com/news-preview/) app.

1. After saving your content, return to the content edit page.
2. Find the Apple News field, and click the Download link. This will download a
   folder containing the
   [Apple News Format](https://developer.apple.com/documentation/apple_news/apple_news_format) JSON
   document needed by the News Preview App.
3. Drag the whole folder into the App icon to open, and it will display the page
   just as the Apple News App will be displaying it.

Note that if you are using custom fonts, you will need to include the fonts in
the extracted folder alongside the Apple News Format document in order for
the News Preview app to properly format your article.

### <a name="delete"></a>Delete an article from Apple News

If you want to delete an article from a channel, but not delete the content from
Drupal, there is a **Delete** link in the Apple News field. Alternatively you
can uncheck Publish to Apple News.

## Developer Documentation

### Components

The module comes with a set of default components as defined by the
Apple News documentation. Each one is mapped to a Component class from
[chapter-three/AppleNewsAPI](https://github.com/chapter-three/AppleNewsAPI).

Each component has a "meta-type" that defines what it predominantly
displays. Currently, there are 4 types:

- text
- image
- nested
- divider

These are mainly used to determine which normalizer should be used during
serialization. You can define your own "meta-type" by writing a custom
ComponentType annotation (see below) and by adding the appropriate schema.

Here is the schema for the text type, as an example:

```
applenews.component_data.default_text:*:
  type: mapping
  mapping:
    text:
      type: applenews.field_mapping
    format:
      type: string
      label: 'Format for included text (none, html, or markdown)'
```

You can define your own Apple News component option by writing a plugin
in `my_module/src/Plugin/applenews/ComponentType`, extending
`ApplenewsComponentTypeBase`, and using the correct annotation.

```
@ApplenewsComponentType(
 id = "your_component_id",
 label = @Translation("Your component label"),
 description = @Translation("Your component description"),
 component_type = "image",
)
```

Component plugins can be altered via
hook_applenews_component_type_plugin_info_alter().

### Normalizers

The module makes use of Drupal's Serialization API by defining several
custom normalizers. These are applicable with the format "applenews".

Overriding a normalizer is another way your module can provide additional
customization. In your *.services.yml file, declare your normalizer
service and give it a priority higher than the one you are trying to
override from `applenews.services.yml`.

It is recommended, though not required, to have your normalizer class
extend one of the Apple News base normalizers.

## <a name="appendix-a"></a>Appendix A - Sample Apple News template YAML

```
uuid: 4650c85e-ec8c-4ebd-a9f5-d13b61622610
langcode: en
status: true
dependencies: {  }
id: test
label: test
node_type: page
columns: 7
width: 1024
margin: 60
gutter: 20
components:
  ea6c4106-88ea-4171-ad5d-8bfd04664c8d:
    uuid: ea6c4106-88ea-4171-ad5d-8bfd04664c8d
    id: 'default_text:author'
    weight: -10
    component_layout:
      column_start: 0
      column_span: null
      margin_top: 0
      margin_bottom: 0
      ignore_margin: none
      ignore_gutter: none
      minimum_height: 10
      minimum_height_unit: points
    component_data:
      text:
        field_name: title
        field_property: base
      format: none
  4f2c21df-d3cf-4bca-85f3-b45f7862c617:
    uuid: 4f2c21df-d3cf-4bca-85f3-b45f7862c617
    id: 'default_image:photo'
    weight: -9
    component_layout:
      column_start: 0
      column_span: null
      margin_top: 0
      margin_bottom: 0
    component_data:
      URL:
        field_name: title
        field_property: base
      caption:
        field_name: title
        field_property: base
```
