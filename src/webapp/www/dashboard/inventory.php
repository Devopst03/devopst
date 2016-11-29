<?php
    $request = $_REQUEST;
    $product = !empty ($_REQUEST['product']) ? $_REQUEST['product'] : '';
    $env = !empty ($_REQUEST['env']) ? $_REQUEST['env'] : '';
    $service = !empty ($_REQUEST['service']) ? $_REQUEST['service'] : '';
    $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';
    
    $inventoryCmd = '/home/prod/talos/src/wrapper/talos show';

   $inventoryParams = ''; 
    if ($product)
        $inventoryParams .= ' product='. $product;
    if ($env)
        $inventoryParams .= ' env='. $env;
    if ($service)
        $inventoryParams .= ' service='. $service;
    $cmd = '';
    switch($type) {
        case 'product':
              $selectOption = 'Environment';
              $inventoryAttribute = 'env';
              $cmd =  "$inventoryCmd $inventoryAttribute $inventoryParams";
        break;
        case 'env':
                $selectOption = 'Service';
                $inventoryAttribute = 'service';
                $cmd =  "$inventoryCmd $inventoryAttribute $inventoryParams";
        break;             
    }
       
    $cmd .= ' --json';
    $all = shell_exec($cmd);
    $all = json_decode($all, true);

    $option = '';
    $option .= '<option value="">--Select ' . $selectOption . '--</option>';
    if(!empty($all)) {
        foreach ($all as $key => $row) {
            $entity[$key]  = $row['name'];
        }
        array_multisort($entity, SORT_ASC, $all);
        foreach($all as $value) {
            $option .= '<option value=' . $value['name'] . '>'. ucfirst($value['name']) .'</option>';
        }
    }
    echo $option;
    exit;
?>