# Styla Oxid Plugin

This plugin provides the magazine functionality to your oxid shop. It accepts all requests on the configurated base directory and generate a dynamic response that includes the shop template containing the magazine JavaScript snippet (that can’t usually be crawled by search engines) and the crawlable content including meta information. Additional it provides a product feed to use your own products in the styla editor.

## Installation

1. Place the *StylaFeed* and *StylaSEO* folders under the modules directory inside the *modules* directory on your OXID installation.

2. Activate both plugins via the OXID admin interface under **Extensions -> Module Settings**:
![Activate Feed Plugin](/readme/readme_activate_feed.png)
![Activate Seo Plugin](/readme/readme_activate_seo.png)

3. Configurate your styla username (provided by your account manager) in the Styla Seo plugin:
![Configure Username](/readme/readme_styla_username.png)

4. Configurate a random string (should be between 6 and 30 characters) as api key in the Styla Feed plugin (please send this to your account manager - so we can enable your products in the styla editor):
![Configurate Api Key](/readme/readme_api_key.png)