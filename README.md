# Styla Oxid Module

This module provides Styla magazine functionality to your OXID shop. It accepts all requests on the configured base directory and generates a dynamic response that includes the shop template containing the magazine JavaScript snippet (that can’t usually be crawled by search engines) and the crawlable HTML content including meta information. The module also provides an API with product data from OXID for you to use in Styla editor (backoffice) and callbacks for the users to add the products from the magazine to OXID cart.

The first diagram on [this page](https://styladocs.atlassian.net/wiki/spaces/CO/pages/9961481/Technical+Integration) should provide you an overview of what the module does and how it exchanges data with Styla. 

## Table of Contents  
[Requirements](#requirements)   
[Installation](#installation)   
[Updating to the latest version](#updating-to-the-latest-version)   
[Database fields used by the module](#database-fields-used-by-the-plugin)   
[Known interactions with other OXID modules](#known-interactions-with-other-oxid-modules)   
[Custom extensions or modifications](#custom-extensions-or-modifications)    
[Release Notes](#release-notes)    

## Requirements

OXID version 5.1.1 or later.

Since OXID itself does not run on PHP7, we haven't tested on this PHP version.

## Installation

1. Copy the contents of *copy_to_modules* into the *modules* directory on your OXID installation.

2. Activate the module via the OXID admin interface under settings **Extensions -> Modules**:
![Activate Module](/readme/readme_activate.png)

3. Configurate your styla username (provided by your account manager) in the Styla module:
![Configure Username](/readme/readme_styla_username.png)

4. Enable product api and configurate a random string (should be between 6 and 30 characters) as api key in the Styla Feed module (please send this to your account manager - so we can enable your products in the styla editor):
![Configurate Api Key](/readme/readme_api_key.png)


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

## Release Notes

### V1.5.6
- Task: Prüfung Produktsuche
- Task: Neues Feld "priceTemplate" beim Einzelprodukt-Endpunkt
- Task: Neues Objekt “tax” beim Einzelprodukt-Endpunkt
- Task: E-Mail "Farbennamen für Einzelprodukte in OXID"

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
