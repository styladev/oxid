# Styla Oxid Module

This module provides Styla magazine functionality to your OXID shop. It accepts all requests on the configured base directory and generates a dynamic response that includes the shop template containing the magazine JavaScript snippet (that can’t usually be crawled by search engines) and the crawlable HTML content including meta information. The module also provides an API with product data from OXID for you to use in Styla editor (backoffice) and callbacks for the users to add the products from the magazine to OXID cart.

[This documentation page](https://docs.styla.com/) should provide you an overview of how Styla works in general. 

## Table of Contents  
[Requirements](#requirements)   
[Installation](#installation)   
[Updating to the latest version](#updating-to-the-latest-version)   
[Database fields used by the module](#database-fields-used-by-the-plugin)   
[Known interactions with other OXID modules](#known-interactions-with-other-oxid-modules)   
[Custom extensions or modifications](#custom-extensions-or-modifications)     
[Setup Process](#setup-process)   
[Release Notes](#release-notes)    

## Requirements

OXID version 5.x.

Since OXID itself does not run on PHP7, we haven't tested on this PHP version.

## Installation

1. Copy the contents of *copy_to_modules* into the *modules* directory on your OXID installation.

2. Activate the module via the OXID admin interface under settings **Extensions -> Modules**:
![Activate Module](/readme/readme_activate.png)

3. Configure your styla username (provided by your account manager) in the Styla module:
![Configure Username](/readme/readme_styla_username.png)

4. Change the base directory (URL in which the magazine will be displayed, screen shot above)

5. Enable product api and configure a random string (should be between 6 and 30 characters) as api key in the Styla Feed module (please send this to your account manager - so we can enable your products in the styla editor):
![Configure Api Key](/readme/readme_api_key.png)


### Please do not create any subpages in your CMS or directories for your magazine. The module itself will take care of setting up the /magazine/ (or any other) page on which the magazine will appear and of the roouting as well.

## Updating to the latest version

The sequence when updating shoud be as follows:

1. Install the new module
2. Activate the new module
3. Enter the settings from the previous version
4. Check the 'Enable Product API' box
5. Deactivate the old module
6. Uninstall the old module

### Important if updating to V1.5.0 or later

Until V1.5.0 the module consisted of two modules installed separately. They were merged to just one module in V1.5.0 and will stay so. If the two modules are already installed then you should stick to the following sequence when updating to V1.5.0 or later:

1. Install the new module:
![Install the new module](/readme/0-initial.png)
2. Activate the new module:
![Activate the new module](/readme/1-Activate_1.5.png)
3. Enter the settings from the previous version:
![Enter the settings from the previous version](/readme/2-configure.png)
4. Check the 'Enable Product API' box (as on screenshot in 4. above)
5. Deactivate the old modules:
![Deactivate the old modules](/readme/3-deativate_old_modules.png)
6. Uninstall the old modules:
![Uninstall the old modules](/readme/4-remove_old_modules.png)

If you go through these steps in a different sequence, this might lead to problems.  

### Important if updating to V1.5.2 or later

If you have recently updated the module and product search in Styla Backoffice returns all products, even those not matching your search query, please simply save the module settings in OXID admin panel in: Extensions > Modules > Styla > Settings > SAVE. The search behaviour should then go back to normal and return only products matching your query.

## SEO Content from Styla's SEO API

The module uses data from Styla's SEO API to:
* generate tags like: meta tags including `<title>`, canonical link, og:tags, static content inserted into <body>, `robots` instructions
* insert these tags accordingly into HTML of the template the page with Styla content uses
  
This is done to provide search engine bots with data to crawl and index all Styal URLs, which are in fact a Single-Page-Application.

Once you install and configure the module, please open source of the page on which your Styla content is embedded and check if none of the tags mentioned below are duplicated. In case `robots`or `link rel="canonical"` or any other are in the HTML twice, make sure to remove the original ones coming from your default template. Otherwise search engine bots might not be able to crawl all the Styla content or crawl it incorrectly. 

You can finde more information on the SEO API on [this page](https://styladocs.atlassian.net/wiki/spaces/CO/pages/9961486/SEO+API+and+Sitemaps+Integration)

## Database fields used by the module

The following database fields of the standard OXID shop system are being
used by the StylaFeed module:

### For the generation of an article list

```
oxarticles.oxparent, oxarticles.oxactive, oxarticles.oxactivefrom, oxarticles.oxactiveto,
oxarticles.oxsearch (=1), oxarticles.oxpic != '', oxarticles.oxartnum join oxobject2category
(assigned categories) order by oxarticles.oxinsert / oxarticles.oxtimestam
```

### For the display of a product

```
oxarticles.oxid, oxarticles.oxtitle, oxartextends.oxlongdesc (Long Description),
oxarticles.oxshortdesc, oxarticles.oxprice (standard getPrice() ), oxarticles.oxstock ( > 0),
oxarticles.oxpic1, oxseo.oxseourl (assigned Seo-urls)
```

### For the display of variants

```
oxarticles.oxid, oxarticles.oxprice (standard getPrice() ), oxarticles.oxtprice (getTPrice()
), oxarticles.oxstock, oxarticles.oxvarname, oxarticles.oxvarselect
```

The logic for getPrice() and getTPrice() is quite complex and can be different for each
project. Generally the following fields are being used: oxarticles.oxprice, oxarticles.oxtprice,
oxarticles.oxvat, oxvarminprice.

### For the display of a category

```
oxcategories.oxid, oxcategories.oxtitle, oxcategories.oxshortdesc, sub-kategorien,
oxseo.oxseourl (assigned seo-url)
```

### For SEO URLs

The methods stylaSEO onActivate and stylaFEED onActivate create static SEO URLS, which
are being written into the oxseo table. These are the following (depending on the module
settings in the backend of OXID):

stylaSEO:
```
magazine/, magazine/tag/, magazine/story/
```
stylaFEED:
```
magazine/index/, magazine/index/category/, magazine/index/product/
```

## Known interactions with other OXID modules

Generally speaking, external modules do not cause problems with the Styla module as this module does work relatively independently.

However, some shop-specific changes can lead to deviations between estimated and actual behavior, especially in the following cases:

*	Usage of external search solutions (Celebros, Findologic, etc.) may cause different search results in the Styla feed than with the same search phrase using the shops own search

*	Adjustments in the product logic like variants, prices, availability/saleability. To display such adjustments in Styla, the relevant standard OXID methods must be  extended

*	(`getSqlActiveSnippet`, `getVariantsQuery` and similar)

*	If specific adjustments are desired in the Styla feed, the relevant methods can also be extended in the OXID way in `\Styla_Feed`

There are no known blockers or conflicts making the installation of the module impossible.

## Custom extensions or modifications

[Read more about extending the module!](/copy_to_modules/Styla/Extending.md)

## Setup Process

The process of setting up your Content Hub(s) usually goes as follows:

1. Install and configure the module on your stage using Content Hub ID(s) shared by Styla
2. Share the stage URL, credentials with Styla
4. Styla integrates product data from endpoints provided by the module, tests your stage Content Hub and asks additional questions, if needed
5. Install and configure the module on production, without linking to the Content Hub(s) there and, again, share the URL with Styla
6. Make sure your content is ready to go live
7. Styla conducts final User Acceptance Tests before the go-live
8. Go-live (you link to the Content Hub embedded on your production)

## Release Notes

### V1.7.5
- Removed data-rootpath to be able to have full path seo api calls

### V1.7.4
- Feature: Add to cart logic update

### V1.7.3
- moved seo api html to allow html hydration

### V1.7.2
- added missing trailing slash for picture url

### V1.7.1
- Do not show empty categories at the /category/-endpoint
- Locale-Parameter for Productlinks

### V1.7.0
- Extension: Product URL for each picture in search
- Bugfix: Product API Error-Handling

### V1.6.0
- Task: Added data-rootpath handling to allow one content hub on multiple countries

### V1.5.9
- Bugfix: Create proper object ids for seo urls
- Bugfix: Use breadcrumb link depending on language

### V1.5.8
- Task: Allow to use ${language} in styla username to support multiple languages

### V1.5.7
- Bugfix: Products of subcategories cannot be found
- Extension: Changed default API domain, JS Spippet, SEO server URL
- Task: Adjustments for Styla Productfeed
- Task: isArticleSaleable OXID standard

### V1.5.6
- Feature: New field "priceTemplate" for product details endpoint
- Feature: New object “tax” for product details endpoint

### V1.5.5
- Bugfix: Changed module name back to Styla to make sure previous configuration stays available

### V1.5.4
- Bugfix: Changed version endpoint structure
- Bugfix: Changed module name and description

### V1.5.3
- Feature: Added category id's to product details response

### V1.5.2
- Feature: "Enable Product API" activated automatically, checkbox not displayed
- Feature: Field for Styla-backend productsearch
- Feature: Include manufacturer/vendor in productname
- Feature: Variant sorting
- Feature: Endpoint for to check module-version at `/styla-plugin-version/`
- Bugfix: Feed-Productview has to show variants

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
