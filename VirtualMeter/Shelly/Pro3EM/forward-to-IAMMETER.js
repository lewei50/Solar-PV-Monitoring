/**
 * @file forward-to-IAMMETER.js
 * @description This script retrieves Shelly status data, processes it, and uploads it to the IAMMETER Cloud server.
 *              It extracts three-phase measurements including voltage, current, power, active energy, reactive energy,
 *              frequency, and power factor. Active and reactive energy values are divided by 1000.
 *              The device serial number (SN) is hard-coded for user convenience.
 * @author IAMMETER
 * @version 1.0
 */

// Define initial upload data object with a hard-coded SN value.
var data = {
    "version": "pro3em-1.0",
    "SN": "Your SN",  // Replace this with your actual device serial number.
    "Datas": []  // This array will be populated later with status data.
  };
  
  /**
   * Function: updateAndUpload
   * ---------------------------
   * Retrieves the device status using the Shelly.GetStatus RPC, processes the returned data to extract
   * three-phase measurements, and then uploads the formatted data to the IAMMETER Cloud server.
   */
  function updateAndUpload() {
    // Retrieve Shelly status data via the Shelly.GetStatus RPC.
    Shelly.call("Shelly.GetStatus", {}, function(r) {
      if (r) {
        // Extract the 'em' and 'emdata' objects from the returned data.
        // If these objects are arrays, use the first element (index 0).
        // Alternatively, if the keys are formatted as "em:0", use that key.
        let em0 = (r.em && r.em[0]) ? r.em[0] : (r["em:0"] ? r["em:0"] : {});
        let emdata0 = (r.emdata && r.emdata[0]) ? r.emdata[0] : (r["emdata:0"] ? r["emdata:0"] : {});
        
        // Assemble the three-phase data in the following order:
        // [voltage, current, power, active energy, reactive energy, frequency, power factor]
        // Note: active and reactive energy values are divided by 1000.
        data.Datas = [
          [
            em0.a_voltage || 0,
            em0.a_current || 0,
            em0.a_act_power || 0,
            (emdata0.a_total_act_energy || 0) / 1000,
            (emdata0.a_total_act_ret_energy || 0) / 1000,
            em0.a_freq || 0,
            em0.a_pf || 0
          ],
          [
            em0.b_voltage || 0,
            em0.b_current || 0,
            em0.b_act_power || 0,
            (emdata0.b_total_act_energy || 0) / 1000,
            (emdata0.b_total_act_ret_energy || 0) / 1000,
            em0.b_freq || 0,
            em0.b_pf || 0
          ],
          [
            em0.c_voltage || 0,
            em0.c_current || 0,
            em0.c_act_power || 0,
            (emdata0.c_total_act_energy || 0) / 1000,
            (emdata0.c_total_act_ret_energy || 0) / 1000,
            em0.c_freq || 0,
            em0.c_pf || 0
          ]
        ];
        
        print("Updated data object: ", JSON.stringify(data));
        
        // After updating the data, call the upload function to send the data to IAMMETER Cloud.
        uploadSensor();
      } else {
        print("Failed to retrieve device status.");
      }
    });
  }
  
  /**
   * Function: uploadSensor
   * ------------------------
   * Uploads the processed data via an HTTP POST request to the IAMMETER Cloud server.
   */
  function uploadSensor(){
    Shelly.call("HTTP.POST", {
      "url": "https://cloud.iammeter.com/api/V1/sensor/UploadSensor",
      "body": JSON.stringify(data)
    }, function(result) {
      if (result && result.body) {
        let response = JSON.parse(result.body);
        print("Upload response: ", result.body);
      }
    });
  }
  
  // Immediately call updateAndUpload to start the process,
  // and set a timer to call updateAndUpload every 60000 milliseconds (1 minute).
  updateAndUpload();
  Timer.set(60000, true, updateAndUpload);