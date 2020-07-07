# Visual Testing

## How it works

Core to this tool is [BackstopJS](https://garris.github.io/BackstopJS). Backstop creates "Reference" images and compares them to "Test" images and calculates the percentage of the image that has changed.

The report it generates at `/tests/backstopjs/reports/html/index.html` is a static html site with a UI for comparing the before and after. This can be viewed in any web browser.

## How to run it

### Locally with Lando

1. Generate the reference images: `$ lando backstop reference`
2. Compare against Test images: `$ lando backstop test`
3. View the report: `$ start tests/backstopjs/reports/html/index.html`

## Which pages does it test?

The canonical list of pages that it checks is a JSON file at `/backstopjs/scenarios.json`. There are two arrays. The first is `urls` and just takes a list of urls.

The second is `advanced` and is an array of objects that can be customized with values from [https://github.com/garris/BackstopJS#advanced-scenarios]

Each page that it checks must have its own entry with:
* `label` - Also used for screenshot naming.
* `url` - Required. Tells BackstopJS what endpoint/document you want to test. This can be an absolute URL or local to your current working directory.

## Errors

When `backstop test` is performed, if there are ANY tests that exceed the `misMatchThreshold` set in `/backstopjs/backstop.js`, it will show them as "Failed" and display an error result on the command-line.

Basically, when you see "Failed", or "Error", that just means "There is a significant difference. Look at it." If it is undesirable, probably a bug fix is in order.
