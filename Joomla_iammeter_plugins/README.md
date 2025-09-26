

- [1. Integrating IAMMETER-Cloud API with Joomla CMS](#1-integrating-iammeter-cloud-api-with-joomla-cms)
  - [1.1. Overview](#11-overview)
  - [1.2. Deploy Joomla CMS](#12-deploy-joomla-cms)
  - [1.3. Modify the `plg_content_iammeter` Plugin](#13-modify-the-plg_content_iammeter-plugin)
  - [1.4. Install the Plugin in Joomla](#14-install-the-plugin-in-joomla)
  - [1.5. Configure IAMMETER API Token](#15-configure-iammeter-api-token)
  - [1.6. Display IAMMETER Data in Joomla Articles](#16-display-iammeter-data-in-joomla-articles)
  - [1.7. Example Result](#17-example-result)
  - [1.8. Use Cases](#18-use-cases)
- [2. References](#2-references)


# 1. Integrating IAMMETER-Cloud API with Joomla CMS

This guide explains how to use the **IAMMETER-Cloud API** to retrieve real-time data from your IAMMETER energy meters and display it directly inside the **Joomla CMS** through a custom plugin.



------

## 1.1. Overview

By integrating IAMMETER with Joomla, you can:

- Fetch live energy data (such as solar production and grid import/export) from IAMMETER-Cloud.
- Display this data inside Joomla articles using a simple shortcode.
- Provide end users with a seamless dashboard inside Joomla without external tools.

------

## 1.2. Deploy Joomla CMS

You can quickly set up Joomla CMS by following the official documentation or using Docker:

- [Joomla Official Website](https://www.joomla.org/)
- [Joomla Docker Image](https://hub.docker.com/_/joomla)

------

## 1.3. Modify the `plg_content_iammeter` Plugin

Unzip the `plg_content_iammeter.zip` package, which contains `iammeter.php` and `iammeter.xml`.

Open **iammeter.php** and replace the placeholder serial numbers with your real IAMMETER device SNs.

Example before modification:

```
$meters = [
    'Solarproduktion' => 'your meter sn 1',
    'Netzeinspeisung' => 'your meter sn 2'
];
```

Example after modification (with real SNs):

```
$meters = [
    'Solarproduktion' => 'C12A35E1',
    'Netzeinspeisung' => 'E035A330'
];
```

After editing, repackage the files back into `plg_content_iammeter.zip`.

------

## 1.4. Install the Plugin in Joomla

1. Log in to the Joomla admin panel.
2. Go to **System → Install → Extensions**.
3. Upload the modified `plg_content_iammeter.zip` file.
4. After installation, go to **System → Manage → Plugins**.
5. Find and open `plg_content_iammeter`.

------

## 1.5. Configure IAMMETER API Token

The plugin requires an API token to access IAMMETER-Cloud data.

- You can get the token directly from the IAMMETER web system.
- Or, retrieve it via the IAMMETER-Cloud API. Reference: [How to Use IAMMETER-Cloud API](https://www.iammeter.com/docs/system-api).

Paste the token into the **API-Token** field in Joomla and set the plugin **Status** to **Enabled**. Save and close.

------

## 1.6. Display IAMMETER Data in Joomla Articles

Once the plugin is enabled, you can use the shortcode `{iammeter}` in any Joomla article.

Steps:

1. Navigate to **Content → Articles → New**.
2. Insert `{iammeter}` into the article body.
3. Save and publish the article.

Now, when you open the article in Joomla CMS, the IAMMETER energy data will be displayed in real time.

------

## 1.7. Example Result

Here is an example of how IAMMETER data is shown inside Joomla:

![IAMMETER data in Joomla](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250925140608847.png)

------

## 1.8. Use Cases

The IAMMETER-Cloud API integration with Joomla can be applied in multiple scenarios:

- **Solar PV Monitoring Website**
   Create a dedicated energy dashboard to show solar generation, self-consumption, and grid export values.
- **Energy Portal for Customers**
   Utilities or service providers can embed IAMMETER data into their Joomla-based portals, allowing end users to view real-time electricity usage.
- **Smart Home Integration**
   Combine IAMMETER energy data with other smart home modules in Joomla to provide a unified management platform.
- **Corporate Sustainability Reporting**
   Organizations can display real-time energy savings and carbon footprint reductions on their Joomla-based websites.

------

# 2. References

- [IAMMETER-Cloud API Documentation](https://www.iammeter.com/docs/system-api)
- [Joomla CMS Official Site](https://www.joomla.org/)