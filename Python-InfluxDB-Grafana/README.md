use [getData.py](getData.py) to import solar-PV data to influxdb, then show in [Grafana](https://grafana.com/grafana/dashboards/13295)



 step 1:rewrite the GetData() to returen inverter_power,yield_energy,feedin_power,grid_consumption_energy,exported_energy

```
def Getdata(): # take WEM3080T as example

  dataFetchUrl = 'http://admin:admin@192.168.1.215:5000/monitorjson'

  r = requests.get(dataFetchUrl)

  meterdata = r.json()['Datas']

  \#get data needed

  inverter_power = meterdata[0][2]

  yield_energy = meterdata[0][3]

  feedin_power = meterdata[1][2]

  grid_consumption_energy = meterdata[1][3]

  exported_energy = meterdata[1][4]

  print(inverter_power,yield_energy,feedin_power,grid_consumption_energy,exported_energy)

  return(inverter_power,yield_energy,feedin_power,grid_consumption_energy,exported_energy)
```



\# step 2: connect the influxDB 

Create the corresponding datebase of solarpv and replace the corresponding parameter to your own.

```
client = InfluxDBClient(host='192.168.1.215', port=8086, username='admin', password='admin',database='solarpv', ssl=False, verify_ssl=False)
```

