<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

function opserverimport_config() {
    $configarray = array(
        "name" => 'OnePortal Server Import',
        "description" => 'This module gives admins the ability to import some or all available Dedicated Server Products and associated configurable options automatically, using the OnePortal API.',
        "version" => "1.0",
        "author" => '<a href="https://one.limestonenetworks.com" target="_blank">Limestone Networks Inc</a>',
        "language" => "english",
        "fields" => array()
    );
    return $configarray;
}

function file_get_contents_curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function opserverimport_activate() {
    return array("status" => "success", "description" => "opserverimport has been activated.");
}

function opserverimport_deactivate() {
    return array("status" => "success", "description" => "opserverimport has been deactivated, and table(s) has been removed from your database.");
}

function opserverimport_output($vars) {
    global $aInt, $whmcs, $CONFIG;
    $step2 = '';
    $step3 = '';
    if (isset($_REQUEST['step2'])) {
        $importt = '';
        $clist = '';
        $gids = '';
        $elist = '';
        $checke = Capsule::table('tbladdonmodules')->where('module', 'opserverimport')->where('setting', 'import-history')->count();
        if ($checke > 0) {
            $importt = '<option value="insert">Insert Data</option>
                        <option value="update">Update Data</option>';
        } else {
            $importt = '<option value="insert">Insert Data</option>';
        }
        $citems = Capsule::table('tblcurrencies')->select('id', 'code')->get();
        foreach ($citems as $value) {
            $clist .= '<option value="' . $value->id . '" ' . (($value->code == 'USD') ? 'Selected="selected"' : '') . '>' . $value->code . '</option>';
        }
        $gitems = Capsule::table('tblproductgroups')->select('id', 'name')->get();
        foreach ($gitems as $value) {
            $gids .= '<option value="' . $value->id . '">' . $value->name . '</option>';
        }
        $eitems = Capsule::table('tblemailtemplates')->where('type', 'product')->select('id', 'name')->get();
        foreach ($eitems as $value) {
            $elist .= '<option value="' . $value->id . '" ' . (($value->id == '17') ? 'Selected="selected"' : '') . '>' . $value->name . '</option>';
        }
        $step2 = '<form method="post" action="addonmodules.php?module=opserverimport">
                                    <input type="hidden" name="step3" value="1">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>STEP 2</strong></div>
                    <div class="panel-body">
                        <table width="100%" cellspacing="3" cellpadding="4" border="0" class="form">
                            <tbody>
                            <!--
                                <tr>
                                    <td style="width: 30%;" class="fieldlabel" id="font_size">Import type :</td>
                                    <td class="fieldarea" id="font_bold">
                                        <select name="itype" class="form-control input-400 input-inline">
                                            ' . $importt . '
                                        </select>
                                    </td>
                                </tr>
                                -->
                                <tr>
                                    <td style="width: 30%;" class="fieldlabel" id="font_size">Product Group</td>
                                    <td class="fieldarea" id="font_bold">
                                        <select name="gid" class="form-control input-400 input-inline">
                                            ' . $gids . '
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="width: 30%;" class="fieldlabel" id="font_size">Currency (USD) :</td>
                                    <td class="fieldarea">
                                        <select name="currency" class="form-control input-400 input-inline">
                                            ' . $clist . '
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%;" class="fieldlabel" id="font_size">Welcome Email :</td>
                                    <td class="fieldarea">
                                        <select name="wemail" class="form-control input-400 input-inline">
                                            ' . $elist . '
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%;" class="fieldlabel">Stock Control :</td>
                                    <td class="fieldarea">
                                        <select name="instock" class="form-control input-400 input-inline">
                                            <option value="1">Import All products</option>
                                            <option value="2">Import just in stock</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
<table class="form module-settings" width="100%" border="0" cellspacing="2" cellpadding="3" id="tblModuleSettings">
    <tbody>
        <tr>
            <td class="fieldlabel" width="20%">API Key</td>
            <td class="fieldarea">
                <input type="password" autocomplete="off" name="packageconfigoption[1]" class="form-control input-inline input-400" value="">
            </td>
            <td class="fieldlabel" width="20%">OnePortal API URL</td>
            <td class="fieldarea">
                <input type="text" name="packageconfigoption[2]" class="form-control input-inline input-400" value="https://one.limestonenetworks.com/webservices/clientapi.php">
            </td>
        </tr>
        <tr>
            <td class="fieldlabel" width="20%">Bandwidth Graph</td>
            <td class="fieldarea">
                <label class="checkbox-inline">
                    <input type="hidden" name="packageconfigoption[3]" value="">
                    <input type="checkbox" name="packageconfigoption[3]"> Tick to allow the bandwidth graph to be displayed client-side</label>
            </td>
            <td class="fieldlabel" width="20%">Hardware List</td>
            <td class="fieldarea">
                <label class="checkbox-inline">
                    <input type="hidden" name="packageconfigoption[4]" value="">
                    <input type="checkbox" name="packageconfigoption[4]"> Tick to allow the hardware list to be displayed client-side</label>
            </td>
        </tr>
        <tr>
            <td class="fieldlabel" width="20%">Power Controls</td>
            <td class="fieldarea">
                <label class="checkbox-inline">
                    <input type="hidden" name="packageconfigoption[5]" value="">
                    <input type="checkbox" name="packageconfigoption[5]"> Tick to allow the power controls to be displayed client-side</label>
            </td>
            <td class="fieldlabel" width="20%">rDNS Domain</td>
            <td class="fieldarea">
                <input type="text" name="packageconfigoption[6]" class="form-control input-inline input-300" value="">
            </td>
        </tr>
        <tr>
            <td class="fieldlabel" width="20%">IP Addressses</td>
            <td class="fieldarea">
                <label class="checkbox-inline">
                    <input type="hidden" name="packageconfigoption[7]" value="">
                    <input type="checkbox" name="packageconfigoption[7]"> Tick to allow assigned IP Addresses to be displayed client-side</label>
            </td>
        </tr>
    </tbody>
</table></div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-success btn-lg">Start importing proccess</button>
                    </div>
                </div>
            </form>';
    }
    if (isset($_REQUEST['step3'])) {
        if (isset($_REQUEST['gid']) && is_numeric($_REQUEST['gid'])) {
            if (!ini_get('safe_mode')) {
                set_time_limit(0);
            }
            $string = file_get_contents_curl('https://one.limestonenetworks.com/webservices/rss/products.php');
            $products = new SimpleXMLElement($string);
            $i = 0;
            $iarray = array();
            $slist = $whmcs->get_req_var('packageconfigoption');
            $configlist = array('configoption1' => '', 'configoption2' => '', 'configoption3' => '', 'configoption4' => '', 'configoption5' => '', 'configoption6' => '', 'configoption7' => '',);
            if ($slist) {
                for ($t = 1; $t <= 7; $t++) {
                    if (isset($slist[$t])) {
                        $configlist['configoption' . $t] = $slist[$t];
                    }
                }
            }
            foreach ($products as $product) {
                if ($i == 800)
                    break;
                $not_allowed = array('69', '73', '74', '78', '79', '85', '86', '90', '91');
                if (in_array($product['id'], $not_allowed)) {
                    continue;
                }
                if ($product['id'] <= 52) {
                    continue;
                }
                if ($_REQUEST['instock'] == '2') {
                    if (trim($product->pricing->instock) == '0')
                        continue;
                }
                $components = $product->components;
                $gid = Capsule::table('tblproductconfiggroups')->insertGetId([
                    'name' => trim($product['name']),
                    'description' => trim($product['name']),
                ]);
                foreach ($components as $category) {
                    foreach ($category as $value) {
                        $cid = Capsule::table('tblproductconfigoptions')->insertGetId([
                            "gid" => $gid,
                            "optionname" => trim($value['name']),
                            "optiontype" => '1',
                            "qtyminimum" => '0',
                            "qtymaximum" => '0'
                        ]);
                        $ib = 0;
                        foreach ($value as $val) {
                            $msp = str_replace('.000', '.00', trim($val['setup']));
                            $mp = str_replace('.000', '.00', trim($val['monthly']));
                            $opid = Capsule::table('tblproductconfigoptionssub')->insertGetId([
                                "configid" => $cid,
                                "optionname" => $val,
                                "sortorder" => $ib,
                                "hidden" => '0'
                            ]);
                            Capsule::table('tblpricing')->insertGetId([
                                "type" => "configoptions",
                                "currency" => $_REQUEST['currency'],
                                "relid" => $opid,
                                "msetupfee" => $msp,
                                "qsetupfee" => '-1',
                                "ssetupfee" => '-1',
                                "asetupfee" => '-1',
                                "bsetupfee" => '-1',
                                "tsetupfee" => '-1',
                                "monthly" => $mp,
                                "quarterly" => '-1',
                                "semiannually" => '-1',
                                "annually" => '-1',
                                "biennially" => '-1',
                                "triennially" => '-1'
                            ]);
                            $ib++;
                        }
                    }
                }
                $description = '';
                $descs = json_decode(json_encode($product->details), true);
                unset($descs['name']);
                unset($descs['codename']);
                foreach ($descs as $desc => $vd) {
                    $description .= $desc . ' : ' . $vd . "\t\n";
                }
                $command = 'AddProduct';
                $postData = array(
                    'type' => 'server',
                    'gid' => $_REQUEST['gid'],
                    'welcomeemail' => $_REQUEST['wemail'],
                    'description' => $description,
                    'stockcontrol' => '0',
                    'servertype' => 'opserverimport',
                    'autosetup' => '',
                    'name' => trim($product['name']),
                    'paytype' => 'recurring',
                );
                $admindat = Capsule::table('tbladmins')->where('roleid', '1')->where('disabled', '0')->select('username')->first();
                $adminUsername = $admindat->id;
                $results = localAPI($command, $postData, $adminUsername);
                if (isset($results['pid'])) {
                    $msp = str_replace('.000', '.00', trim($product->pricing->setup));
                    $mp = str_replace('.000', '.00', trim($product->pricing->monthly));
                    Capsule::table('tblproductconfiglinks')->insert([
                        "gid" => $gid,
                        "pid" => $results['pid']
                    ]);
                    Capsule::table('tblpricing')->insert([
                        "type" => "product", "currency" => $_REQUEST['currency'], "relid" => $results['pid'], "msetupfee" => $msp, "qsetupfee" => '-1', "ssetupfee" => '-1', "asetupfee" => '-1', "bsetupfee" => '-1', "tsetupfee" => '-1', "monthly" => $mp, "quarterly" => '-1', "semiannually" => '-1', "annually" => '-1', "biennially" => '-1', "triennially" => '-1'
                    ]);
                    Capsule::table('tblproducts')->where('id', $results['pid'])->update([
                        'servertype' => 'oneportal'
                    ]);
                    Capsule::table('tblproducts')->where('id', $results['pid'])->update($configlist);
                    Capsule::table('tblcustomfields')->insert([
                        "type" => "product",
                        "relid" => $results['pid'],
                        "fieldname" => 'Server ID',
                        "fieldtype" => 'text',
                        "description" => 'Server ID',
                        "fieldoptions" => '',
                        "regexpr" => '',
                        "adminonly" => 'on',
                        "required" => '',
                        "showorder" => '',
                        "showinvoice" => '',
                        "sortorder" => ''
                    ]);
                    $step3 .= '<b>' . trim($product['name']) . '</b> add to product list successfully<br>';
                    $iarray['p' . $product['id']] = $results['pid'];
                }
                $i++;
            }
        }
        $step3 = '<div class="panel panel-default">
                <div class="panel-heading"><strong>Products imported successfully</strong></div>
                <div class="panel-body">
                    Congratulations, Below products added with successfully.<br>
                    ' . $step3 . '
                    You can see what added in your system by click : <a href="configproducts.php">product list</a>
                </div>
            </div>';
    }
    echo '
    <link rel="stylesheet" href="../modules/addons/opserverimport/assets/style.css?' . rand(1, 10000) . '">
<div>
    <div class="row">
        <div class="col-xs-12">
            <ul class="nav nav-pills nav-justified thumbnail setup-panel">
                <li class="' . ((isset($_REQUEST['step3'])) ? 'disabled' : 'active') . '">
                    <a href="#step-1">
                        <h4 class="list-group-item-heading">Welcome</h4>
                        <p class="list-group-item-text">Pre import products</p>
                    </a>
                </li>
                <li class="' . ((!isset($_REQUEST['step2'])) ? 'disabled' : 'active') . '">
                    <a href="#step-2">
                        <h4 class="list-group-item-heading">Set Criteria</h4>
                        <p class="list-group-item-text">Products and configurations</p>
                    </a>
                </li>
                <li class="' . ((!isset($_REQUEST['step3'])) ? 'disabled' : 'active') . '">
                    <a href="#step-3">
                        <h4 class="list-group-item-heading">Finish</h4>
                        <p class="list-group-item-text">Final step and import data</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="row setup-content" id="step-1">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>STEP 1</strong></div>
                <div class="panel-body">

                    This utility creates products and configurable options based upon Dedicated Server products available from Limestone Networks.
                    <br /> Configure the Client Area features and insert OnePortal API key on the next step.
                    <br /> Before accessing the product import, create a product group (WHMCS Admin->Setup->Products/Services->Products/Services->Create Product Group).
                    <br /> Group can be named "Dedicated Servers" or any other name.

                </div>
                    <form method="post" action="addonmodules.php?module=opserverimport">
                        <input type="hidden" name="step2" value="1">
                        <button type="submit" id="activate-step-2" class="btn btn-primary btn-lg">Continue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row setup-content" id="step-2">
        <div class="col-xs-12">
            ' . $step2 . '
        </div>
    </div>
    <div class="row setup-content" id="step-3">
        <div class="col-xs-12">
            ' . $step3 . '
        </div>
    </div>
</div>';
    echo '<script type="text/javascript" src="../modules/addons/opserverimport/assets/scripts.js?' . rand(1, 10000) . '"></script>';
}
