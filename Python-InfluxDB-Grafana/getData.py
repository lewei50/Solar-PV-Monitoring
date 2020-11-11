from influxdb import InfluxDBClient
import requests
from PVDataConverter import PVDataConverter
import time
# step 1:rewrite the GetData() to returen inverter_power,yield_energy,feedin_power,grid_consumption_energy,exported_energy
def Getdata():
    dataFetchUrl = 'http://admin:admin@192.168.1.215:5000/monitorjson'
    r = requests.get(dataFetchUrl)
    #{'method': 'uploadsn', 'mac': 'B0F8232BAF96', 'version': '1.73.5', 'server': 'em', 'SN': '28CD68B7', 'Datas': [[242.1, 0.9, 173.0, 35209.7, 0.0, 50, 1], [242.1, 0.9, 1013.0, 32810.19, 19278.09, 50, 1], [0.0, 0.0, 0.0, 0.0, 0.0, 50, 1]]}
    meterdata = r.json()['Datas']
    #get data needed
    inverter_power = meterdata[0][2]
    yield_energy = meterdata[0][3]
    feedin_power = meterdata[1][2]
    grid_consumption_energy = meterdata[1][3]
    exported_energy = meterdata[1][4]
    print(inverter_power,yield_energy,feedin_power,grid_consumption_energy,exported_energy)
    return(inverter_power,yield_energy,feedin_power,grid_consumption_energy,exported_energy)

# step 2: connect the influxDB (create the corresponding datebase of solarpv)
client = InfluxDBClient(host='192.168.1.215', port=8086, username='admin', password='admin',database='solarpv', ssl=False, verify_ssl=False)
converter = PVDataConverter(client)
while(1):
    inverter_power,yield_energy,feedin_power,grid_consumption_energy,exported_energy=Getdata()
    converter.store(inverter_power, yield_energy, feedin_power, grid_consumption_energy, exported_energy)
    time.sleep(60)
