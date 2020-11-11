import time

class PVDataConverter(object):
    """docstring for [object Object]."""
    def __init__(self, db):
        self.client = db

    def store(self, inverterPower, yieldEnergy, feedinPower, gridConsumptionEnergy, exportedEnergy):
        inverter_power = inverterPower
        yield_energy = yieldEnergy
        feedin_power = feedinPower
        grid_consumption_energy = gridConsumptionEnergy
        exported_energy = exportedEnergy

        yieldEnergyTemp = 0
        selfUseEnergyTemp = 0

        self.saveMeterValue('inverter_power',inverter_power)
        self.saveMeterValue('feedin_power',-feedin_power)
        self.saveMeterValue('load_power',inverter_power + feedin_power)
        self.saveMeterValue('grid_consumption_energy',grid_consumption_energy)
        self.saveMeterValue('exported_energy',exported_energy)
        self.saveMeterValue('yield_energy',yield_energy)
        self.saveMeterValue('selfuse_energy',yield_energy-exported_energy)
        self.saveMeterValue('load_energy',yield_energy + grid_consumption_energy - exported_energy)
        self.saveMeterValue('self_consumption_rate',(yield_energy - exported_energy) / yield_energy)

        self.checkValuePoint('grid_consumption_energy', grid_consumption_energy, 'meter_importenergy','hourly')
        self.checkValuePoint('exported_energy', exported_energy, 'exported_energy','hourly')
        self.checkValuePoint('yield_energy', yield_energy, 'yield_energy','hourly')
        self.checkValuePoint('selfuse_energy', yield_energy - exported_energy, 'selfuse_energy','hourly')
        self.checkValuePoint('load_energy', yield_energy + grid_consumption_energy - exported_energy, 'load_energy','hourly')

        self.checkValuePoint('grid_consumption_energy', grid_consumption_energy, 'meter_importenergy','daily')
        self.checkValuePoint('exported_energy', exported_energy, 'exported_energy','daily')
        self.checkValuePoint('yield_energy', yield_energy, 'yield_energy','daily')
        self.checkValuePoint('selfuse_energy', yield_energy - exported_energy, 'selfuse_energy','daily')
        self.checkValuePoint('load_energy', yield_energy + grid_consumption_energy - exported_energy, 'load_energy','daily')


        self.checkValuePoint('grid_consumption_energy', grid_consumption_energy, 'meter_importenergy','monthly')
        self.checkValuePoint('exported_energy', exported_energy, 'exported_energy','monthly')
        self.checkValuePoint('yield_energy', yield_energy, 'yield_energy','monthly')
        self.checkValuePoint('selfuse_energy', yield_energy - exported_energy, 'selfuse_energy','monthly')
        self.checkValuePoint('load_energy', yield_energy + grid_consumption_energy - exported_energy, 'load_energy','monthly')



    def checkValuePoint(self, valueName,currentValue,meterType,refreshType):
        global yieldEnergyTemp, selfUseEnergyTemp
        currentPeriodTimeStamp = time.strftime("%Y%m%d%H0000", time.localtime())
        timeUnit = 'h'
        if(refreshType == 'daily'):
            currentPeriodTimeStamp = time.strftime("%Y%m%d000000", time.localtime())
            timeUnit = 'd'
        elif(refreshType == 'monthly'):
            currentPeriodTimeStamp = time.strftime("%Y%m00000000", time.localtime())
            timeUnit = 'm'
        qry = ('SELECT * FROM "kWh" WHERE "last_reset"=\'{}\' AND "entity_id"=\'{}_{}\' GROUP BY * ORDER BY ASC LIMIT 1').format(currentPeriodTimeStamp,valueName,refreshType)
        # print(qry)
        qResult = self.client.query(qry)
        points = list(qResult.get_points())
        pointLastPeriod = 0.0
        pointLastReset = ''
        pointValue = 0.0
        firstValue = currentValue
        try:
            pointLastReset = points[0]['last_reset']
            pointValue = float(points[0]['value'])
            firstValue = float(points[0]['first_value'])
        except:
            print("no record",valueName,meterType,refreshType)

        if pointLastReset != currentPeriodTimeStamp:
            print("need to reset time")
            # find the data in previous time cycle.
            qry = ('SELECT * FROM "kWh" WHERE "entity_id"=\'{}_{}\' AND time > now() -1{} GROUP BY * ORDER BY DESC LIMIT 1').format(valueName,refreshType, timeUnit)
            # print(qry)
            qLastPeriodResult = self.client.query(qry)
            lastPoint = list(qLastPeriodResult.get_points())
            try:
                print("get last period data")
                lastCurrentValue = float(lastPoint[0]['current_value'])
                pointValue = "{:.2f}".format(currentValue - lastCurrentValue)
                firstValue = lastCurrentValue
            except:
                print("last period data not found")
                pointValue = 0.0
                firstValue = currentValue
            pointLastReset = currentPeriodTimeStamp
        else:
            pointValue = "{:.2f}".format(currentValue - firstValue)
        if(valueName == 'yield_energy'):
            yieldEnergyTemp = pointValue
        elif(valueName == 'selfuse_energy'):
            selfUseEnergyTemp = pointValue
            self.storeSelfConsumptionRate(selfUseEnergyTemp,yieldEnergyTemp,meterType,refreshType)
        w_json = [{
            "measurement": 'kWh',
            # "time":int(time.time()),
            "tags":{
                'entity_id':'{}_{}'.format(valueName,refreshType),
                'friendly_name_str':valueName,
                'meter_period_str':refreshType,
            },
            "fields":{
                'last_reset':pointLastReset,
                'value':float(pointValue),
                'first_value':firstValue,
                'current_value':currentValue
            }
            }]

        #print(w_json)
        self.client.write_points(w_json)

    def storeSelfConsumptionRate(self, selfUseEnergyValue,yieldEnergyValue,meterType,refreshType):
        if(float(yieldEnergyValue)==0):
            return
        w_json = [{
        "measurement": '%',
        "tags":{
            'entity_id':'self_consumption_rate_{}'.format(refreshType),
            'friendly_name_str':'self_consumption_rate_{}'.format(refreshType),
            'meter_period_str':refreshType,
        },
        "fields":{
            'last_reset':'',
            'value':float(selfUseEnergyValue)/float(yieldEnergyValue),
            'first_value':0.0
        }
        }]

        #print(w_json)
        self.client.write_points(w_json)

    def saveMeterValue(self, id,value):
        w_json = [{
            "measurement": 'W',
            "tags":{
                'domain':'sensor',
                'icon':'mdi:flash'
                },
            "fields":{
                'entity_id':id,
                'value':value
            }
            }]
        self.client.write_points(w_json)
