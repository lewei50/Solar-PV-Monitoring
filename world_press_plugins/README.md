# WordPress IAMMETER Plugin

[toc]

This article is the **second part** in our series on plugins developed using the **IAMMETER-Cloud API**.
 The first article introduced the [IAMMETER-Cloud Joomla Plugin](https://www.iammeter.com/newsshow/joomla-plugin-solar-pv-energy-meter).

In this article, we’ll cover the **IAMMETER-Cloud WordPress plugin**, which allows you to display real-time power and energy data directly within your WordPress website.

IAMMETER-Cloud provides rich [API functions](https://www.iammeter.com/docs/advanced-function), enabling users to access real-time energy data managed by IAMMETER-Cloud.

These energy data sets can represent:

- A solar PV system’s generation and consumption, or
- Grid energy usage in a home, business, or industrial facility.

We have developed a **WordPress plugin** based on these APIs and made the **source code publicly available**.

This guide explains how to use the plugin to **display real-time metering data** from IAMMETER inside your WordPress site.

![WordPress IAMMETER plugin real-time solar energy dashboard](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930101739713.png)

------

## WordPress Deployment

Reference: [Blog Tool, Publishing Platform, and CMS – WordPress.org](https://wordpress.org/)

------

## Installing the IAMMETER Plugin

Go to your **WordPress admin dashboard**, and navigate to:
 **Plugins → Add New → Upload Plugin**

![Upload IAMMETER plugin from WordPress admin dashboard](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930095502813.png)

![Select IAMMETER plugin ZIP file for upload](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930095755810.png)

Then upload and install **iammeter.zip**.

![Install IAMMETER WordPress plugin](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930095914592.png)

After installation, activate the plugin.

![Activate IAMMETER plugin in WordPress](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930100004514.png)

You will now see the IAMMETER plugin listed among your installed plugins.

![IAMMETER plugin displayed in WordPress plugin list](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930100147516.png)

------

## Configure the IAMMETER Plugin

Go to **Settings → IAMMETER**.

![IAMMETER plugin settings page in WordPress admin](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930100441972.png)

Obtain your API token directly from the IAMMETER-Cloud web interface.

![Retrieve IAMMETER Cloud API token from the web system](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20230921163110697.png)

> You can also refer to this guide for how to get the token via API:
>  [How to Use IAMMETER-Cloud More Efficiently by API](https://www.iammeter.com/docs/system-api)

Paste the obtained **API Token** and **Serial Number (SN)** in their respective fields, and then enable the meter.

**Single-phase meter:**

![Single-phase IAMMETER configuration in WordPress plugin](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930100805153.png)

**Three-phase meter:**

![Three-phase IAMMETER configuration in WordPress plugin](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930101043353.png)

Click **Add New Meter** if you wish to add multiple meters.

![Add new IAMMETER device configuration in WordPress](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930101123843.png)

![Configure multiple IAMMETER meters within WordPress plugin](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930101245428.png)

Remember to set **Cache Time** to **300 seconds**, since IAMMETER Cloud API limits requests to once every 5 minutes.
 After all configurations are complete, click **Save Settings**.

![Save IAMMETER plugin configuration settings in WordPress](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930101846907.png)

Now your plugin is fully configured, and you can display your IAMMETER energy data in WordPress posts using the shortcode `[iammeter]`.

![Display IAMMETER energy monitoring data using shortcode in WordPress editor](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930101412026.png)

------

## Displaying IAMMETER Data in WordPress Posts

Go to **Posts → Add New**.

![Create a new WordPress post for IAMMETER data display](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930101511170.png)

Insert `[iammeter]` in the post content, and click **Publish**.

![Insert IAMMETER shortcode in WordPress post editor](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930101626800.png)

Open your WordPress post, and you’ll see the real-time IAMMETER energy data displayed — including **voltage, current, power (bi-directional), power factor, grid consumption energy, and feed-in energy**.
 IAMMETER-Cloud updates the data **every minute**.

![IAMMETER WordPress plugin displaying real-time solar PV and energy consumption data](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20250930101739713.png)