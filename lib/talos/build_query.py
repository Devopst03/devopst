#!/usr/bin/env python

import sys, json

def formatquery(params):
    tblhost        = 'host';
    tblproduct     = 'product';
    tblservice     = 'service';
    tbldatacenter  = 'data_center';
    tblenv         = 'env';
    tblhostenv     = 'host_env';
    tblhostservice = 'host_service';

    tableNames = '';
    where      = '';
    tableData  = '';
    notFound = 0;

    if params['entity'] == 'host':
        if params.get('dc'):
            where+=" and "+tbldatacenter+".name='"+params['dc']+"'";
        if params.get('product'):
            where+=" and "+tblproduct+".name='"+params['product']+"'";
        if params.get('env'):
            where+=" AND "+tblenv+".name='"+params['env']+"'";
        if params.get('service'):
            where+=" AND "+tblservice+".name='"+params['service']+"'";
        if params.get('name'):
            where+=" AND "+tblhost+".name='"+params['name']+"'";

        tableNames=""+tblhost+" LEFT OUTER JOIN "+tblhostservice+" ON host.id = "+tblhostservice+".host_id AND "+tblhostservice+".status=1 LEFT JOIN "+tblservice+" ON "+tblservice+".id = "+tblhostservice+".service_id AND "+tblservice+".status = 1, "+tblproduct+", "+tbldatacenter+", "+tblenv+", "+tblhostenv+"";

        where = ""+tblhost+".status=1 AND "+tbldatacenter+".status=1 AND "+tblproduct+".status=1 AND "+tblhostenv+".status=1 AND "+tblenv+".status=1 AND "+tbldatacenter+".id="+tblhost+".data_center_id AND "+tblproduct+".id="+tblhost+".product_id AND "+tblhost+".id="+tblhostenv+".host_id AND "+tblhostenv+".env_id = "+tblenv+".id "+where;

        if not params.has_key('sort') or params['sort'] in ('', 'default'):
            where += " ORDER BY "+tblhost+".id DESC";
            tableData=""+tblhost+".name AS HostName,"+tblenv+".name AS Environment,"+tblservice+".name AS Service,"+tblproduct+".name AS Product,"+tbldatacenter+".name AS Datacenter";
        else:
            expHostColumns = params['hostcolumns'].split(",")

            validItems = []
            for eachItem in params['sort'].split(","):
                if eachItem not in expHostColumns:
                    notFound = 1
                    break
                validItems.append(eachItem)

            appendStr = ",".join(validItems)

            where = where + " ORDER BY " + appendStr + " DESC";
            tableData=""+tblhost+".name AS HostName," + appendStr + ","+tblservice+".name AS Service,"+tblproduct+".name AS Product";
    elif params['entity'] == 'datacenter':
        where=" "+tbldatacenter+".status=1 ";
        if params.get('name'):
            where=" "+tbldatacenter+".status=1 AND "+tbldatacenter+".name='"+params['name']+"'";
        tableNames=tbldatacenter;
        tableData=""+tbldatacenter+".id AS ID,"+tbldatacenter+".name AS DataCenterName";
    elif params['entity'] == 'product':
        where=" "+tblproduct+".status=1 ";
        if params.get('name'):
            where=" "+tblproduct+".status=1 AND "+tblproduct+".name='"+params['name']+"'";
        tableNames=tblproduct;
        tableData=""+tblproduct+".id AS ID,"+tblproduct+".name AS ProductName";
    elif params['entity'] == 'service':
        where=" "+tblservice+".status=1 ";
        if params.get('name'):
            where=" "+tblservice+".status=1 AND "+tblservice+".name='"+params['name']+"'";
        tableNames=tblservice
        tableData=""+tblservice+".id AS ID,"+tblservice+".name AS ServiceName";
    elif params['entity'] == 'env':
        where=" "+tblenv+".status=1 ";
        if params.get('name'):
            where=" "+tblenv+".status=1 AND "+tblenv+".name='"+params['name']+"'";
        tableNames=tblenv;
        tableData=tblenv+".id AS ID,"+tblenv+".name AS EnvironmentName";
    else:
        print "Unknown entity: %s" % params['entity']

    return {
        'tables': tableNames,
        'where': where,
        'notfound': notFound,
        'tabledata': tableData,
    }
