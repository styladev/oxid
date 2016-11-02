# Styla Oxid Plugin

This plugin provides the magazine functionality to your oxid shop. It accepts all requests on the configurated base directory and generate a dynamic response that includes the shop template containing the magazine JavaScript snippet (that can’t usually be crawled by search engines) and the crawlable content including meta information. Additional it provides a product feed to use your own products in the styla editor.

## Installation

1. Place the *StylaFeed* and *StylaSEO* folders under the modules directory inside the *modules* directory on your OXID installation.

2. Activate both plugins via the OXID admin interface under settings **Extensions -> Modules**:
![Activate Feed Plugin](/readme/readme_activate_feed.png)
![Activate Seo Plugin](/readme/readme_activate_seo.png)

3. Configurate your styla username (provided by your account manager) in the Styla Seo plugin:
![Configure Username](/readme/readme_styla_username.png)

4. Configurate a random string (should be between 6 and 30 characters) as api key in the Styla Feed plugin (please send this to your account manager - so we can enable your products in the styla editor):
![Configurate Api Key](/readme/readme_api_key.png)

[Read more about extending the plugin!](modules/StylaFeed/Extending.md)

## Release Notes

### V1.3.1
- Bugfix: Always append version to script and css

### V1.3.0
- Extension: Moving the Styla snippet into the `<head>``
- Extension: Version Endpoint
- Extension: API Key
- Extension: Adminconfig for API Domain URL
- Bugfix: StylaFeed - _getProductDetails wrong check for parent article- Bufix
- Task: External Search Best Pratice
- Task: Remove deprecated "Source URL" in admin panel

### V1.2.0
- Task: Meta title is not replaced correctly
- Task: Use seo server to fetch content
- Task: Generate a random api key
- Task: Styla Patch in Version einbinden

### V1.4.0
- Task: Breadcrumb Link is not working
