
tutorial : Build a local solar PV plant monitoring system with Home Assistant and Garafana

# 1. Introduction

This tutorial introduces an open source project, that you can build your own solar PV plant monitoring system by using bi-directional WiFi energy meter and some open source platforms including Home Assistant, InfluxDB and Garafana.

# 2. Project overview

## 2.1 System demo

http://ha.iammeter.com:13000/ or [http://grafana.iammeter.com](http://grafana.iammeter.com/)

User name: iammeter

Password: iammeter

![System Overview](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/iammeter/tmpliu/tmp1.jpg)

## 2.2 Parameter description 

Power: Active Power

Exported energy: The surplus solar energy exported to grid

Direct self-use: The solar energy consumed by your home load

Yield Energy: The solar energy produced by solar PV system

Grid Consumption: The energy consumed from grid





# 3. Project introduction

You need to finish the below steps to build your own solar PV plant monitoring system.

1). Install and setup a bi-directional meter supporting integration with Home Assistant, in your sopar PV plant.

Meter Example: https://www.home-assistant.io/integrations/iammeter/

![](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/iammeter-33-20190809-L2.jpg)



![image-20201130101148125](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/iammeter/tmpliu/tmpimage-20201130101148125.png)


1 Install a bi-directional wifi energy meter supporting integration with Home Assistant in your solar PV system




2 Calculate the energy_hourly and energy_daily by Home Assistant based on data uploaded by meter and store all these data in InfluxDB

3 View the data on Garafana (garafana ID:13295)


# 4. Quick start step by step

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
