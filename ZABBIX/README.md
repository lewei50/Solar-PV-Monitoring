# ZABBIX IAMMETER Templates

Templates ->  Import -> Choose File: zbx_export_templates_iammeter_all.yaml -> Import -> Import

Configuration -> Hosts -> Create host ->Templates:	IAMMETER(3) by HTTP, Host groups:	Applications,

Macros:

| Macros          | Value                 |
| --------------- | --------------------- |
| {$IAMMETER.URL} | http://192.168.1.6:80 |

