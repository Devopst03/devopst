#!/usr/bin/python
 
import json,sys;
with open('/home/prod/yoko/releases/live/conf/product.conf') as data_file:    
    data = json.load(data_file)

# Python for loop for key,value using dict data type
for services in data["services"]:
    if "monitored" in services:
        print services["name"] 
