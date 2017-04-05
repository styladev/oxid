# Styla Oxid Plugin

This plugin provides the magazine functionality to your oxid shop. It accepts all requests on the configured base directory and generate a dynamic response that includes the shop template containing the magazine JavaScript snippet (that can’t usually be crawled by search engines) and the crawlable content including meta information. Additional it provides a product feed to use your own products in the styla editor.

The first diagram on [this page](https://styladocs.atlassian.net/wiki/spaces/CO/pages/9961481/Technical+Integration) should provide you an overview of what the plugin does and how it exchanges data with Styla. 

## Requirements

OXID version 5.1.1 or later

## Installation

1. Copy the contents of *copy_to_modules* into the *modules* directory on your OXID installation.

2. Activate the plugin via the OXID admin interface under settings **Extensions -> Modules**:
![Activate Plugin](/readme/readme_activate.png)

3. Configurate your styla username (provided by your account manager) in the Styla plugin:
![Configure Username](/readme/readme_styla_username.png)

4. Enable product api and configurate a random string (should be between 6 and 30 characters) as api key in the Styla Feed plugin (please send this to your account manager - so we can enable your products in the styla editor):
![Configurate Api Key](/readme/readme_api_key.png)

[Read more about extending the plugin!](/copy_to_modules/Styla/Extending.md)

### Please do not create any subpages in your CMS or directories for your magazine. The plugin itself will take care of setting up the /magazine/ (or any other) page on which the magazine will appear and of the roouting as well.

## Updating to the latest version

### Important if updating to V1.5.0 or later

Until V1.5.0 the plugin consisted of two modules installed separately. They were merged to just one module in V1.5.0 and will stay so. If the two modules are already installed then you should stick to the following sequence when updating to V1.5.0 or later:

1. Install the new module:
![Install the new module](/readme/0-initial.png)
2. Activate the new module:
![Activate the new module](/readme/1-Activate_1.5.png)
3. Enter the settings from the previous version
![Enter the settings from the previous version](/readme/2-configure.png)
4. Check the 'Enable Product API' box (as on screenshot in 4. above):
5. Deactivate the old modules:
![Deactivate the old modules](/readme/3-deativate_old_modules.png)
6. Uninstall the old modules:
![Uninstall the old modules](/readme/4-remove_old_modules.png)

If you go through these steps in a different sequence, this might lead to problems.  

## Release Notes

### V1.5.1
- Bugfix: Improve product images via sku / product id

### V1.5.0
- Merged both modules StylaFeed and StylaSEO into one module
- Task: Improve product images via sku / product id

### V1.4.0
- Bugfix: Breadcrumb Link is not working
- Task: Added seo pagination for feeds
- Task: Set status code according to seo api response

### V1.3.1
- Bugfix: Always append version to script and css

### V1.3.0
- Extension: Moving the Styla snippet into the `<head>`
- Extension: Version Endpoint
- Extension: API Key
- Extension: Adminconfig for API Domain URL
- Bugfix: StylaFeed - `_getProductDetails` wrong check for parent article- Bugfix
- Task: External Search Best Pratice
- Task: Remove deprecated "Source URL" in admin panel

### V1.2.0
- Task: Meta title is not replaced correctly
- Task: Use seo server to fetch content
- Task: Generate a random api key
- Task: Styla Patch in Version einbinden
