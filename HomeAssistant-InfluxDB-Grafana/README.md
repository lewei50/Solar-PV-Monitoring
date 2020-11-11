# Building an Open Local PV monitoring system with Home Assistant 



demo: 

http://ha.iammeter.com:13000/   or [grafana.iammeter.com](grafana.iammeter.com)

[http://ha.iammeter.com:18123/](http://ha.iammeter.com:18123/)   or [homeassistant.iammeter.com](homeassistant.iammeter.com)

user name: iammeter

password: iammeter

![image-20201111141144666](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/iammeter/tmpliu/tmpimage-20201111141144666.png)



There are four steps

- A bi-directional Wi-Fi energy meter that can measure import/export energy simultaneously.
- Calculate the extra parameter(such as energy_hourly and energy_daily etc..) by HomeAssistant 
- use InfluxDB for storage 
- show the data in Grafana 

![garafana_influx_HA_1](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/iammeter/tmpliu/tmpgarafana_influx_HA_1.png)

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
