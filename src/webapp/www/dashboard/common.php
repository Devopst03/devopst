<?php

/**
  * Method to display all product, env and services.
  *
  * @return Array
  */

function getAllEntity($param) {
     $all = shell_exec("/home/prod/talos/src/wrapper/talos show $param --json");
     $all = json_decode($all, true);

     foreach ($all as $key => $row) {
        $entity[$key]  = $row['name'];
     }
     array_multisort($entity, SORT_ASC, $all);
     return $all;
}

?>