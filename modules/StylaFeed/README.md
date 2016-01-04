# Styla Feed OXID Module (v0.9.2) 
## Installation How-to
#### Author: Mark Mulder
#### Last updated: 12.11.2014

--
1. Place the *StylaFeed* folder under the modules directory inside the *modules* directory on your OXID installation.

2. Place the image file at *out/admin/img* in the corresponding path on your OXID installation. Create the folder if it’s missing.

3. Once the code is in place, access your OXID administration page. The Styla Feed OXID module can be configured and activated under **Extensions -> Module Settings**.

4. Enter your **API Key at Settings -> API Settings**. Enter new values for the Feed Settings if you wish you use something else than default.

5. If all is working, the feeds will be visible at :

    - **http://[yourwebsite.com]/amazinefeed/?api_key=[your_api_key]** (Default feed)
    - **http://[yourwebsite.com]/amazinefeed/category/?api_key=[your_api_key]** (Category tree)
    - **http://[yourwebsite.com]/amazinefeed/product/?api_key=[your_api_key]&sku=[product_Prod_No]** (Product feed)
    - **http://[yourwebsite.com]/amazinefeed/product/?api_key=[your_api_key]&category=[oxid_category_id]** (Category feed - includes products from sub categories)

