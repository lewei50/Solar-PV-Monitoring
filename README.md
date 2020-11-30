[toc]

 tutorial : How to build a local PV monitoring system with Home Assistant and Garafana

# system Effect

## demo show

http://ha.iammeter.com:13000/ or [http://grafana.iammeter.com](http://grafana.iammeter.com/)

user name: iammeter

password: iammeter

![1](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/iammeter/tmpliu/tmp1.jpg)

## Parameter introduction 

Power:

Exported energy:

Direct self-use

Yield Energy:

Grid Consumption:





# sketch map



![image-20201130101148125](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/iammeter/tmpliu/tmpimage-20201130101148125.png)

1 a bidirectional wifi energy meter that support Home Assistant

https://www.home-assistant.io/integrations/iammeter/

2 calculate the energy_hourly and energy_daily by Home assistant and store all the parameter in InfluxDB

3 garafana ID (13295)

# step by step

https://github.com/lewei50/Solar-PV-Monitoring/tree/master/HomeAssistant-InfluxDB-Grafana

## Step 1:  A bi-directional Wi-Fi energy meter

Such as https://www.home-assistant.io/integrations/iammeter/

## Step 2: Calculate the extra parameter by Home Assistant

[solariammeter.yaml](solariammeter.yaml)

![image-20201102094612014](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/iammeter/tmpliu/tmpimage-20201102094612014.png)

## Step 3: use InfluxDB for storage of Home Assistant

Replace the Default storage to InfluxDB,Full details of the Home Assistant integration can be found here: https://www.home-assistant.io/components/influxdb/

## Step 4: show the data in Garafana

[Garafana ID 13295](https://grafana.com/grafana/dashboards/13295?src=twitter.com&mdm=social&cnt=buffera6a03&camp=buffer&pg=prod-ent&plcmt=contact-banner)



You need to get the following data from infixdb

| inverter_power                  |
| ------------------------------- |
| feedin_power                    |
| load_power                      |
|                                 |
| grid_consumption_energy         |
| exported_energy                 |
| yield_energy                    |
| selfuse_energy                  |
| load_energy                     |
| self_consumption_rate           |
|                                 |
| grid_consumption_energy_hourly  |
| exported_energy_hourly          |
| yield_energy_hourly             |
| selfuse_energy_hourly           |
| load_energy_hourly              |
| self_consumption_rate_hourly    |
| grid_consumption_energy_daily   |
| exported_energy_daily           |
| yield_energy_daily              |
| selfuse_energy_daily            |
| load_energy_daily               |
| self_consumption_rate_daily     |
| grid_consumption_energy_monthly |
| exported_energy_monthly         |
| yield_energy_monthly            |
| selfuse_energy_monthly          |
| load_energy_monthly             |
| self_consumption_rate_monthly   |
