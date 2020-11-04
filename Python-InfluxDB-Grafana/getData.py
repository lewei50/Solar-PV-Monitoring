from influxdb import InfluxDBClient
import requests
from PVDataConverter import PVDataConverter

client = InfluxDBClient(host='192.168.2.2', port=8086, username='admin', password='admin',database='power_meter', ssl=False, verify_ssl=False)

dataFetchUrl = 'http://admin:admin@127.0.0.1:80/monitorjson'
r = requests.get(dataFetchUrl)
#{'method': 'uploadsn', 'mac': 'B0F8232BAF96', 'version': '1.73.5', 'server': 'em', 'SN': '28CD68B7', 'Datas': [[242.1, 0.9, 173.0, 35209.7, 0.0, 50, 1], [242.1, 0.9, 1013.0, 32810.19, 19278.09, 50, 1], [0.0, 0.0, 0.0, 0.0, 0.0, 50, 1]]}
meterdata = r.json()['Datas']
#get data needed
inverter_power = meterdata[0][2]
yield_energy = meterdata[0][3]
feedin_power = meterdata[1][2]
grid_consumption_energy = meterdata[1][3]
exported_energy = meterdata[1][4]

converter = PVDataConverter(client)
converter.store(inverter_power, yield_energy, feedin_power, grid_consumption_energy, exported_energy)
