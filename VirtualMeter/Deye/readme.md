- [Introduction](#introduction)
- [Architecture Overview](#architecture-overview)
- [Step 1: Retrieve Deye Inverter Data in Home Assistant](#step-1-retrieve-deye-inverter-data-in-home-assistant)
  - [1.1 Deye Inverter Integration for Home Assistant](#11-deye-inverter-integration-for-home-assistant)
  - [1.2 Available Sensors in Home Assistant](#12-available-sensors-in-home-assistant)
- [Step 2: Map and Convert Data to IAMMETER Format](#step-2-map-and-convert-data-to-iammeter-format)
  - [2.1 IAMMETER Virtual Meter Data Model](#21-iammeter-virtual-meter-data-model)
  - [2.2 Mapping Home Assistant Sensors to IAMMETER Phases](#22-mapping-home-assistant-sensors-to-iammeter-phases)
    - [Power and Energy Mapping](#power-and-energy-mapping)
    - [SOC Mapping](#soc-mapping)
  - [2.3 JSON Payload Structure Explanation](#23-json-payload-structure-explanation)
- [Step 3: Upload Data to IAMMETER-Cloud](#step-3-upload-data-to-iammeter-cloud)
  - [3.1 Create a Virtual Meter (SN) in IAMMETER-Cloud](#31-create-a-virtual-meter-sn-in-iammeter-cloud)
  - [3.2 Configure Place and Meter Roles](#32-configure-place-and-meter-roles)
  - [3.3 Configure Home Assistant REST Command](#33-configure-home-assistant-rest-command)
  - [3.4 Automate Periodic Data Upload](#34-automate-periodic-data-upload)
- [Verification and Result](#verification-and-result)
- [Notes and References](#notes-and-references)

## Introduction

This tutorial explains how to retrieve data from a **Deye hybrid inverter** via **Home Assistant**, convert the data into the required JSON format, and upload it to **IAMMETER-Cloud** using a **Virtual Meter**.

This solution is suitable for users who want to:

- Integrate a Deye (or Sunsynk-compatible) hybrid inverter into IAMMETER
- Combine grid, battery, and inverter data into a unified energy model
- Visualize and analyze energy flows in IAMMETER-Cloud

---

## Architecture Overview

The overall data flow is shown below:

Deye Hybrid Inverter
 ↓ (Modbus/TCP)
 Home Assistant
 ↓ (REST API / JSON)
 IAMMETER-Cloud Virtual Meter

Home Assistant acts as the data collector and protocol adapter, while IAMMETER-Cloud provides long-term storage, visualization, and energy analysis.

---

## Step 1: Retrieve Deye Inverter Data in Home Assistant

### 1.1 Deye Inverter Integration for Home Assistant

Use the following Home Assistant integration to read data from the Deye hybrid inverter via **Modbus/TCP**:

GitHub repository:
 https://github.com/jlopez77/DeyeInverter

This integration is compatible with Deye, Sunsynk, and other Deye-based hybrid inverters.

**Special thanks to the author of this GitHub project for providing a reliable local integration method, which makes it possible to access inverter data directly without relying on cloud services.**

After installation and configuration, Home Assistant will expose multiple sensors for grid, PV, battery, and inverter data.

---

### 1.2 Available Sensors in Home Assistant

The table below lists example sensors retrieved from the Deye inverter and their relationship to IAMMETER JSON fields.

| Description                  | Example Value | Phase | Index | IAMMETER JSON | Home Assistant Sensor  |
| ---------------------------- | ------------- | ----- | ----- | ------------- | ---------------------- |
| Running Status               | 2             |       |       |               | running_status         |
| Total Grid Production (kWh)  | 0             | C     | 4     | Datas[2][3]   | total_grid_production  |
| Daily Energy Bought (kWh)    | 0             |       |       |               | daily_energy_bought    |
| Daily Energy Sold (kWh)      | 15            |       |       |               | daily_energy_sold      |
| Total Energy Bought (kWh)    | 0             | A     | 4     | Datas[0][3]   | total_energy_bought    |
| Total Energy Sold (kWh)      | 0             | A     | 5     | Datas[0][4]   | total_energy_sold      |
| Daily Load Consumption (kWh) | 1.2           |       |       |               | daily_load_consumption |
| Total Load Consumption (kWh) | 0             |       |       |               | total_load_consumption |
| DC Temperature (°C)         | 149.5         |       |       |               | dc_temperature         |
| AC Temperature (°C)         | 152.1         |       |       |               | ac_temperature         |
| Total Production (kWh)       | 0             |       |       |               | total_production       |
| Daily Production (kWh)       | 16.9          |       |       |               | daily_production       |
| PV1 Voltage (V)              | 342.2         |       |       |               | pv1_voltage            |
| PV1 Current (A)              | 7.8           |       |       |               | pv1_current            |
| Grid Voltage L1 (V)          | 241.3         | A     | 1     | Datas[0][0]   | grid_voltage_l1        |
| Total Grid Power (W)         | -2365         | A     | 3     | Datas[0][2]   | total_grid_power       |
| Inverter Total Power (W)     | 2558          | C     | 3     | Datas[2][2]   | total_power            |
| Battery Voltage (V)          | 10.14         | B     | 1     | Datas[1][0]   | battery_voltage        |
| Battery Current (A)          | -0.01         | B     | 2     | Datas[1][1]   | battery_current        |
| Battery Power (W)            | 0             | B     | 3     | Datas[1][2]   | battery_power          |
| Battery SOC (%)              | 0             | B     | SOC   | EA.SOC[1]     | battery_soc            |

IAMMETER JSON format reference:
 https://www.iammeter.com/newsshow/energy-meter-json-value

---

## Step 2: Map and Convert Data to IAMMETER Format

### 2.1 IAMMETER Virtual Meter Data Model

IAMMETER Virtual Meter uses a **three-phase energy model**.

In this tutorial, the phases are defined as:

- Phase A → Grid
- Phase B → Battery
- Phase C → Inverter

Each phase supports voltage, current, power, and energy values.
 Battery SOC is transmitted separately via the `EA` field.

---

### 2.2 Mapping Home Assistant Sensors to IAMMETER Phases

#### Power and Energy Mapping

| Parameter            | Phase A (Grid)      | Phase B (Battery) | Phase C (Inverter)    |
| -------------------- | ------------------- | ----------------- | --------------------- |
| Voltage              | grid_voltage_l1     | battery_voltage   |                       |
| Current              |                     | battery_current   |                       |
| Power                | total_grid_power    | battery_power     | total_power           |
| Forward Energy (kWh) | total_energy_bought |                   | total_grid_production |
| Reverse Energy (kWh) | total_energy_sold   |                   |                       |

#### SOC Mapping

| Parameter | Phase A | Phase B     | Phase C |
| --------- | ------- | ----------- | ------- |
| SOC       |         | battery_soc |         |

---

### 2.3 JSON Payload Structure Explanation

The `Datas` array uses the following structure:

- voltage
- current
- power
- forward energy (kWh)
- reverse energy (kWh)

Battery SOC is included in:

EA.SOC = [Phase A, Phase B, Phase C]

---

## Step 3: Upload Data to IAMMETER-Cloud

### 3.1 Create a Virtual Meter (SN) in IAMMETER-Cloud

1. Create a new **Virtual Meter**
   ![img](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20240124093300622.png)
2. Set **Data Source** to **PushApi** and save
   ![img](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20240124095458969.png)
3. After saving, a **Serial Number (SN)** will be generated
   ![img](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20240124095851393.png)

---

### 3.2 Configure Place and Meter Roles

1. Create a **New Place**
   ![img](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20240124101230225.png)
2. Add the SN and required information
   ![img](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20240124101730280.png)
3. Set the meter **Type** to **Three Phase**
   ![img](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20240124102754092.png)
4. Assign meter roles:

| SN   | Type     |
| ---- | -------- |
| SN_a | Grid     |
| SN_b | Battery  |
| SN_c | Inverter |

![img](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20240124102939966.png)

---

### 3.3 Configure Home Assistant REST Command

Add the following configuration to `configuration.yaml`:

[`configuration.yaml`](configuration.yaml)

> your_sn: iammeter cloud creat the sn
>
> sensor.xxx: Change to the actual name of your sensor
>
> array definition: [voltage, current, power, Forward Kwh, Reverse Kwh]
>
> Other sensors can be added according to your own needs.

---

### 3.4 Automate Periodic Data Upload

Use a time-based automation to upload data every minute using the REST command.

---

## Verification and Result

Once data is uploaded successfully, IAMMETER-Cloud will correctly display grid, battery, and inverter energy flows.

![img](https://iammeterglobal.oss-accelerate.aliyuncs.com/img/image-20240124105839330.png)

---

## Notes and References

- `your_sn`: SN created in IAMMETER-Cloud
- `sensor.xxx`: Replace with your actual Home Assistant sensor entity IDs
- Array format: `[voltage, current, power, forward_kWh, reverse_kWh]`
- IAMMETER JSON reference:
  https://www.iammeter.com/newsshow/energy-meter-json-value
- Deye Home Assistant integration:
  https://github.com/jlopez77/DeyeInverter
