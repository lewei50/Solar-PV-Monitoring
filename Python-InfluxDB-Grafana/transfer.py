from influxdb import InfluxDBClient
import requests
import time

#influxdb config
influxDBHost = '192.168.2.2'
influxDBPort = 8086
influxDBUser = 'admin'
influxDBPass = 'admin'
influxDBDatabase = 'power_meter'
influxDBSSL = False

dataFetchUrl = 'http://admin:admin@127.0.0.1:80/monitorjson'
r = requests.get(dataFetchUrl)
meterdata = r.json()['Datas']

# dataFetchUrl = 'https://www.yourmetersite.com/api/v1/site/meterdata/C384789C?token=dc0aeb9c07e24368b5fd9bb3b42a3095'
# r = requests.get(dataFetchUrl)
# meterdata = r.json()['data']['values']
print(meterdata)
# w_json = [{
#     "measurement": 'meter',
#     "tags":{'name':'3phases'},
#     "fields":{
#         'a_voltage':meterdata[0][0],
#         'b_voltage':meterdata[1][0],
#         'c_voltage':meterdata[2][0],
#         'a_current':meterdata[0][1],
#         'b_current':meterdata[1][1],
#         'c_current':meterdata[2][1],
#         'a_power':meterdata[0][2],
#         'b_power':meterdata[1][2],
#         'c_power':meterdata[2][2],
#         'a_power_consumption':meterdata[0][3],
#         'b_power_consumption':meterdata[1][3],
#         'c_power_consumption':meterdata[2][3],
#         'a_power_supply':meterdata[0][4],
#         'b_power_supply':meterdata[1][4],
#         'c_power_supply':meterdata[2][4]
#     }
#     }]

inverter_power = meterdata[0][2]
yield_energy = meterdata[0][3]
feedin_power = meterdata[1][2]
grid_consumption_energy = meterdata[1][3]
exported_energy = meterdata[1][4]

yieldEnergyTemp = 0
selfUseEnergyTemp = 0

client = InfluxDBClient(host=influxDBHost, port=influxDBPort, username=influxDBUser, password=influxDBPass,database=influxDBDatabase, ssl=influxDBSSL, verify_ssl=False)
# # client.create_database('kWh')
# # print(client.get_list_database())
# w_json = [{
#     "measurement": 'meter',
#     "tags": {
#         'name': '名字',
#         'categories': '类型'
#         },
#     "fields": {
#         'price': "价格",
#         'unit': "单位",
#         }
#     }]
# 写入数据库
#client.write_points(w_json)

def checkValuePoint(valueName,currentValue,meterType,refreshType):
    global yieldEnergyTemp, selfUseEnergyTemp
    currentHourlyTimeStamp = time.strftime("%Y%m%d%H0000", time.localtime())
    if(refreshType == 'daily'):
        currentHourlyTimeStamp = time.strftime("%Y%m%d000000", time.localtime())
    elif(refreshType == 'monthly'):
        currentHourlyTimeStamp = time.strftime("%Y%m00000000", time.localtime())
    qry = ('SELECT * FROM "kWh" WHERE "last_reset"=\'{}\' AND "entity_id"=\'{}_{}\' GROUP BY * ORDER BY ASC LIMIT 1').format(currentHourlyTimeStamp,valueName,refreshType)
    #print(qry)
    qResult = client.query(qry)
    points = list(qResult.get_points())
    pointLastPeriod = 0.0
    pointLastReset = ''
    pointLastResetStr = ''
    pointValue = 0.0
    needReset = False
    firstValue = currentValue
    try:
        #print("Result: {0}".format(points[0]['time']))
        pointLastPeriod = float(points[0]['last_period'])
        pointLastReset = points[0]['last_reset']
        pointLastResetStr = points[0]['last_reset_str']
        pointValue = float(points[0]['value'])
        firstValue = float(points[0]['first_value'])
    except:
        print("no record",valueName,meterType,refreshType)

    if pointLastReset != currentHourlyTimeStamp:
        print("need to reset time")
        pointLastReset = currentHourlyTimeStamp
        needReset = True
        pointValue = 0.0
    else:
        pointValue = "{:.2f}".format(currentValue - firstValue)
    if(valueName == 'yield_energy'):
        yieldEnergyTemp = pointValue
    elif(valueName == 'selfuse_energy'):
        selfUseEnergyTemp = pointValue
        storeSelfConsumptionRate(selfUseEnergyTemp,yieldEnergyTemp,meterType,refreshType)
    w_json = [{
        "measurement": 'kWh',
        # "time":int(time.time()),
        "tags":{
            'domain':'sensor',
            'entity_id':'{}_{}'.format(valueName,refreshType),
            'friendly_name_str':valueName,
            'icon_str':'mdi:counter',
            'meter_period_str':refreshType,
            'source_str':'sensor.{}'.format(meterType),
            'status_str':'collecting'
        },
        "fields":{
            'last_period':0,
            'last_reset':pointLastReset,
            'last_reset_str':'',
            'value':float(pointValue),
            'first_value':firstValue
        }
        }]

    #print(w_json)
    client.write_points(w_json)

def storeSelfConsumptionRate(selfUseEnergyValue,yieldEnergyValue,meterType,refreshType):
    if(float(yieldEnergyValue)==0):
        return
    w_json = [{
    "measurement": '%',
    # "time":int(time.time()),
    "tags":{
        'domain':'sensor',
        'entity_id':'self_consumption_rate_{}'.format(refreshType),
        'friendly_name_str':'self_consumption_rate_{}'.format(refreshType),
        'icon_str':'mdi:counter',
        'meter_period_str':refreshType,
        'source_str':'sensor.{}'.format(meterType),
        'status_str':'collecting'
    },
    "fields":{
        'last_period':0,
        'last_reset':'',
        'last_reset_str':'',
        'value':float(selfUseEnergyValue)/float(yieldEnergyValue),
        'first_value':0.0
    }
    }]

    #print(w_json)
    client.write_points(w_json)

def saveMeterValue(id,value):
    w_json = [{
        "measurement": 'W',
        # "time":int(time.time()),
        "tags":{
            'domain':'sensor',
            'icon':'mdi:flash'
            },
        "fields":{
            'entity_id':id,
            'value':value
        }
        }]
    client.write_points(w_json)


saveMeterValue('inverter_power',inverter_power)
saveMeterValue('feedin_power',-feedin_power)
saveMeterValue('load_power',inverter_power + feedin_power)
saveMeterValue('grid_consumption_energy',grid_consumption_energy)
saveMeterValue('exported_energy',exported_energy)
saveMeterValue('yield_energy',yield_energy)
saveMeterValue('selfuse_energy',yield_energy-exported_energy)
saveMeterValue('load_energy',yield_energy + grid_consumption_energy - exported_energy)
saveMeterValue('self_consumption_rate',(yield_energy - exported_energy) / yield_energy)

checkValuePoint('grid_consumption_energy', grid_consumption_energy, 'meter_importenergy','hourly')
checkValuePoint('exported_energy', exported_energy, 'exported_energy','hourly')
checkValuePoint('yield_energy', yield_energy, 'yield_energy','hourly')
checkValuePoint('selfuse_energy', yield_energy - exported_energy, 'selfuse_energy','hourly')
checkValuePoint('load_energy', yield_energy + grid_consumption_energy - exported_energy, 'load_energy','hourly')

checkValuePoint('grid_consumption_energy', grid_consumption_energy, 'meter_importenergy','daily')
checkValuePoint('exported_energy', exported_energy, 'exported_energy','daily')
checkValuePoint('yield_energy', yield_energy, 'yield_energy','daily')
checkValuePoint('selfuse_energy', yield_energy - exported_energy, 'selfuse_energy','daily')
checkValuePoint('load_energy', yield_energy + grid_consumption_energy - exported_energy, 'load_energy','daily')


checkValuePoint('grid_consumption_energy', grid_consumption_energy, 'meter_importenergy','monthly')
checkValuePoint('exported_energy', exported_energy, 'exported_energy','monthly')
checkValuePoint('yield_energy', yield_energy, 'yield_energy','monthly')
checkValuePoint('selfuse_energy', yield_energy - exported_energy, 'selfuse_energy','monthly')
checkValuePoint('load_energy', yield_energy + grid_consumption_energy - exported_energy, 'load_energy','monthly')
