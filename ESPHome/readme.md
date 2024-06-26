If your esphome is a version before 2024.6.0, please use [scr-485.yaml](https://github.com/lewei50/Solar-PV-Monitoring/blob/master/ESPHome/scr-485.yaml)(version: "1.1.6").  

If your esphome is version 2024.6.0 or later, please use [scr-485-new.yaml](https://github.com/lewei50/Solar-PV-Monitoring/blob/master/ESPHome/scr-485-new.yaml)(version: "1.2.1" or later). 

 

If you use the built-in auto_mode of scr-485, remember to change the time zone to your local time zone.

servers: [pool.ntp.org: the internet cluster of ntp servers (ntppool.org)](https://www.ntppool.org/en/)

timezone: [List of tz database time zones - Wikipedia](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones)

```
time:
  - platform: sntp
    id: sntp_time
    servers: au.pool.ntp.org
    timezone: Australia/Brisbane
```

