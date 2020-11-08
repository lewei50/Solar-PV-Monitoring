

|                      | unit | Description                                                  | how to get    | formula                        |
| -------------------- | ---- | ------------------------------------------------------------ | ------------- | ------------------------------ |
| inverter power       | W    |                                                              | read directly |                                |
| feed in power        | W    | positive: inverter power>load  power<br>negative:inverter power<load power | read directly |                                |
| load power           | W    |                                                              | calculate     | inverter power - feedin power  |
| yield energy         | Kwh  |                                                              | read directly |                                |
| imported energy      | Kwh  |                                                              | read directly |                                |
| exported energy      | Kwh  |                                                              | read directly |                                |
| self use energy      | Kwh  |                                                              | calculate     | yield energy - exported energy |
| self-sufficient rate | %    |                                                              | calculate     |                                |