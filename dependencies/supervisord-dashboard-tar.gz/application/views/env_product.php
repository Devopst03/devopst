<div class="row">
    <?php
    $alert = false;
    foreach($list as $name=>$procs){
        $parsed_url = parse_url($cfg[$name]['url']);
        if ( isset($cfg[$name]['username']) && isset($cfg[$name]['password']) ){
            $base_url = 'http://' . $cfg[$name]['username'] . ':' . $cfg[$name]['password'] . '@';
        }else{
            $base_url = 'http://';
        }
        $ui_url = $base_url . $parsed_url['host'] . ':' . $cfg[$name]['port']. '/';
    ?>
    <div class="span<?php echo ($this->config->item('supervisor_cols')==2?'6':'4');?>">
    <table class="table table-bordered table-condensed table-striped">
        <tr><th colspan="4">
            <a href="<?php echo $ui_url; ?>"><?php echo $name; ?></a> <?php if($this->config->item('show_host')){ ?><i><?php echo $parsed_url['host']; ?></i><?php } ?>
            <?php
            if(isset($cfg[$name]['username'])){echo '<i class="icon-lock icon-green" style="color:blue" title="Authenticated server connection"></i>';}
            if(!isset($procs['error'])){
            ?>
            <span class="server-btns pull-right">
                <a href="<?php echo site_url('/control/stopall/'.$name); ?>" class="btn btn-mini btn-inverse" type="button"><i class="icon-stop icon-white"></i> Stop all</a>
                <a href="<?php echo site_url('/control/startall/'.$name); ?>" class="btn btn-mini btn-success" type="button"><i class="icon-play icon-white"></i> Start all</a>
                <a href="<?php echo site_url('/control/restartall/'.$name); ?>" class="btn btn-mini btn-primary" type="button"><i class="icon icon-refresh icon-white"></i> Restart all</a>
            </span>
            <?php
            }
            ?>
        </th></tr>
        <?php
        $CI = &get_instance();
        foreach($procs as $item){

            if($item['group'] != $item['name']) $item_name = $item['group'].":".$item['name'];
            else $item_name = $item['name'];

            $check = $CI->_request($name,'readProcessStderrLog',array($item_name,-1000,0));
            if(is_array($check)) $check = print_r($check,1);

            if(!is_array($item)){
                    // Not having array means that we have error.
                    echo '<tr><td colspan="4">'.$item.'</td></tr>';
                    echo '<tr><td colspan="4">For Troubleshooting <a href="https://github.com/mlazarov/supervisord-monitor#troubleshooting" target="_blank">check this guide</a></td></tr>';
                    continue;
            }

            $pid = $uptime = '&nbsp;';
            $status = $item['statename'];
            if($status=='RUNNING'){
                $class = 'success';
                list($pid,$uptime) = explode(",",$item['description']);
            }
            elseif($status=='STARTING') $class = 'info';
            elseif($status=='FATAL') $class = 'important';
            elseif($status=='STOPPED') $class = 'inverse';
            else $class = 'error';

            $uptime = str_replace("uptime ","",$uptime);
            ?>
            <tr>
                <td><?php
                    echo $item_name;
                    if($check){
                        $alert = true;
                        echo '<span class="pull-right"><a href="'.site_url('/control/clear/'.$name.'/'.$item_name).'" id="'.$name.'_'.$item_name.
                                '" onclick="return false" data-toggle="popover" data-message="'.htmlspecialchars($check).'" data-original-title="'.
                                $item_name.'@'.$name.'" class="pop btn btn-mini btn-danger">
                                <img src="'.site_url('/img/alert_icon.png').'" /></a></span>';
                    }
                    ?>
                </td>
                <td width="10"><span class="label label-<?php echo $class;?>"><?php echo $status;?></span></td>
                <td width="80" style="text-align:right"><?php echo $uptime;?></td>
                <td style="width:1%">
                    <!--div class="btn-group">
                        <button class="btn btn-mini">Action</button>
                        <button class="btn btn-mini dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="test">Restart</a></li>
                            <li><a href="zz">Stop</a></li>
                        </ul>
                    </div//-->
                    <?php if($status=='RUNNING'){ ?>
                    <a href="<?php echo site_url('/control/stop/'.$name.'/'.$item_name);?>" class="btn btn-mini btn-inverse" type="button"><i class="icon-stop icon-white"></i></a>
                    <?php } if($status=='STOPPED' || $status == 'EXITED'){ ?>
                    <a href="<?php echo site_url('/control/start/'.$name.'/'.$item_name);?>" class="btn btn-mini btn-success" type="button"><i class="icon-play icon-white"></i></a>
                    <?php } ?>
                </td>
            </tr>
            <?php
        }

        ?>
    </table>
</div>
    <?php
    }
    if($alert && !$muted && $this->config->item('enable_alarm')){
        echo '<embed height="0" width="0" src="'.site_url('/sounds/alert.mp3').'">';
    }
    if($alert){
        echo '<title>!!! WARNING !!!</title>';
    }else{
        echo '<title>Support center</title>';
    }

    ?>
</div>