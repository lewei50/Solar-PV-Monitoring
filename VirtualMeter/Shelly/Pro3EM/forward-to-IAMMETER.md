## Apply for a "Virtual Meter" from IAMMETER

First, visit the IAMMETER Cloud website to apply for a Virtual Meter serial number (SN). For detailed steps, please refer to the following link:  
[Apply for a Virtual Meter](https://www.iammeter.com/newsshow/virtual-meter-push-api-postman#apply-for-a-virtual-meter)

Next, select **PushApi** as the Data Source and click **Save**. See the screenshot below:

![Select the data source as "PushAPI"](https://raw.githubusercontent.com/lewei50/Solar-PV-Monitoring/refs/heads/virtual-meter/VirtualMeter/Shelly/Pro3EM/images/set-pushapi-for-iammeter-virtual-meter.png)

---

## Scripting on Shelly

Open the local configuration interface of your Shelly Pro3EM device. Navigate to the **Scripts** tab and click the **Create Script** button, as shown in the image below:

![Open the Scripts page to add a script](https://raw.githubusercontent.com/lewei50/Solar-PV-Monitoring/refs/heads/virtual-meter/VirtualMeter/Shelly/Pro3EM/images/create-script.png)

Then, use the script code available at the following link:  
[forward-to-IAMMETER.js](https://raw.githubusercontent.com/lewei50/Solar-PV-Monitoring/refs/heads/virtual-meter/VirtualMeter/Shelly/Pro3EM/forward-to-IAMMETER.js)

Make sure to replace the placeholder with the SN you applied for earlier. Finally, click **Save**. Refer to the following screenshot:

![Configure the script](https://raw.githubusercontent.com/lewei50/Solar-PV-Monitoring/refs/heads/virtual-meter/VirtualMeter/Shelly/Pro3EM/images/copy-script-and-save.png)

---

## Using IAMMETER Cloud Features

After completing these settings, you can use IAMMETER Cloud to monitor your meter data. Compared to Shelly's native interface, IAMMETER Cloud offers several enhanced features:

- Enhanced data visualization and extended data retention.
- Flexible settings for various electricity and feed-in tariffs
- Robust billing functionalities
- Comprehensive report analysis
- Smarter automation and control features

Discover even more powerful functionalities by visiting the [IAMMETER website](https://www.iammeter.com).
