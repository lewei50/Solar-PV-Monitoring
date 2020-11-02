# Building an Open Local PV monitoring system by Home Assistant 



demo: http://ha.iammeter.com:13000/  

user name: iammeter

password: iammeter



There are four steps

- A bi-directional Wi-Fi energy meter that can measure import/export energy simultaneously.
- Calculate the extra parameter(such as energy_hourly and energy_daily etc..) by HomeAssistant 
- use InfluxDB for storage 
- show the data in Garafana and do some simple calculation there to get the parameter such as "direct self-use, self-sufficient rate"

![garafana_influx_HA_1](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/iammeter/tmpliu/tmpgarafana_influx_HA_1.png)

## step 1  A bi-directional Wi-Fi energy meter

https://www.home-assistant.io/integrations/iammeter/

## step 2 Calculate the extra parameter by Home Assistant

https://leweidoc.oss-cn-hangzhou.aliyuncs.com/openPV/Solar%20System-13295.json

![image-20201102094612014](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/iammeter/tmpliu/tmpimage-20201102094612014.png)

## step 3 use InfluxDB for storage of Home Assistant



## step 4 show the data in Garafana

[Garafana ID 13295](https://grafana.com/grafana/dashboards/13295?src=twitter.com&mdm=social&cnt=buffera6a03&camp=buffer&pg=prod-ent&plcmt=contact-banner)

![Wiring 1](https://leweidoc.oss-cn-hangzhou.aliyuncs.com/lewei50/img/lwkits/YNM3000image)



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