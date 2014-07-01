{literal}
	<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
	<link rel="stylesheet" href="modules/servers/oneportalcloud/includes/css/clientArea.css"/>
{/literal}

{if $params.configoption6 eq 'on'}
	<h3>Power Controls</h3>
	{if $status->booted eq 'on'}
		<div id="actionbuttons">
			<p>Power currently on.</p>
			<form method="post" action="clientarea.php?action=productdetails">
				<input type="hidden" name="id" value="{$params.serviceid}" />
				<input type="hidden" name="modop" value="custom" />
				<input type="hidden" name="a" value="reboot" />
				<input class="btn btn-primary" type="submit" value="Reboot Server" />
			</form>

			<form method="post" action="clientarea.php?action=productdetails">
				<input type="hidden" name="id" value="{$params.serviceid}" />
				<input type="hidden" name="modop" value="custom" />
				<input type="hidden" name="a" value="turnoff" />
				<input class="btn btn-primary" type="submit" value="Turn Off Server" />
			</form>
		</div>

	{else}
		<div id="actionbuttons">
			<p>Power currently off.</p>
			<form method="post" action="clientarea.php?action=productdetails">
				<input type="hidden" name="id" value="{$params.serviceid}" />
				<input type="hidden" name="modop" value="custom" />
				<input type="hidden" name="a" value="turnon" />
				<input class="btn btn-primary" type="submit" value="Turn On Server" />
			</form>
		</div>
	{/if}
{/if}

{if $params.configoption5 eq 'on'}
	{if !isset($hardware->error)}
		<h3>Hardware</h3>
		<table style='width: 100%' id="serverhwtable"><tr><th>Category</th><th>Item</th></tr>
			{foreach from=$hardware  item=item}
				<tr><td>{$item->category}</td><td>{$item->option}</td></tr>
			{/foreach}
		</table>
	{/if}
{/if}

{if $params.configoption8 eq 'on'}
	{if !empty($ips)}
		<form method="post" action="clientarea.php?action=productdetails">
			<input type="hidden" name="id" value="{$params.serviceid}" />
			<input type="hidden" name="modop" value="custom" />
			<input type="hidden" name="a" value="saverdns" />
			<h3>IP Addressses</h3>
			<table id="serveriptable"><tr><th>Network</th><th>IP Address</th><th>IP Type</th><th>Reverse DNS</th></tr>
				{foreach from=$ips key=nettype item=network}
					{foreach from=$network item=ip}
						{if $nettype|ucfirst == 'Private' || $ip.type == 'network' || $ip.type == 'gateway' || $ip.type == 'broadcast'}
							<tr><td>{$nettype|ucfirst}</td><td>{$ip.ipaddress}</td><td>{$ip.type}</td><td>&nbsp;</td></tr>
						{else}
							<tr><td>{$nettype|ucfirst}</td><td>{$ip.ipaddress}</td><td>{$ip.type}</td><td><input type="text" name="ipaddress[{$ip.ipaddress}]" value="{$ip.ptr}" /></td></tr>
						{/if}
					{/foreach}
				{/foreach}
			</table>
			<input class="btn btn-primary" type="submit" value="Save Reverse DNS Changes" /></form>

	{else}
		<h3>IP Addresses</h3><p>Unable to determine IP Addresses</p>
	{/if}

{/if}

{if $params.configoption13 eq 'on'}
	<div style="margin-top: 20px;">
		<h3>Console</h3>
		<a class="btn btn-primary" href='{$params.configoption3}/server/{$server->server_id}/console' target='_blank'>Open Server Console</a>
	</div>
{/if}

{if $params.configoption14 eq 'on'}
	<br>
	<form method="post" action="clientarea.php?action=productdetails" id="save_fw_form">
		<input type="hidden" name="id" value="{$params.serviceid}" />
		<input type="hidden" name="modop" value="custom" />
		<input type="hidden" name="a" value="firewallsave" />
	{foreach from=$fw_rules->firewall_by_network key=id item=network}
	<h3>Firewall Rules for {$fw_rules->network_interface_obj->$id->label}</h3>
	<table class="serverfwtable" id="fw_rules_{$id}"><thead><tr><th>Address</th><th>Command</th><th>Port</th><th>Protocol</th><th >Action</th></tr></thead>
		<tbody>
		{foreach from=$network item=fw}
			<tr data-value='{$fw->id}' data-rule-position='{$fw->position}' id='rule_{$fw->id}' class='fw_rule'><td>{$fw->address}</td><td>{$fw->command}</td><td>{$fw->port}</td><td>{$fw->protocol}</td><td><button type='button' class='btn btn-small btn-danger btn-submit rule_remove' id='{$fw->id}' data-network-id='{$id}'><span class="icon icon-white icon-trash"></span></button></td></tr>
		{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4"></td>
				<td colspan="1">
					<button class="add-row btn btn-small btn-success btn-submit"  type="button" data-network-id="{$id}"><span class="icon icon-white icon-plus"></span></button>
				</td>
			</tr>
		</tfoot>
	</table>
	{/foreach}
	{foreach from=$fw_rules->network_interface_obj item=nic}
		{assign var=id value=$nic->id}

		{if isset($fw_rules->firewall_by_network->$id)}

		{else}
		<h3>Firewall Rules for {$nic->label}</h3>
		<table class="serverfwtable" id="fw_rules_{$id}"><thead><tr><th>Address</th><th>Command</th><th>Port</th><th>Protocol</th><th >Action</th></tr></thead><tbody>
		<tr id="sry">
			<td colspan="5">You have no additional rules configured.</td>
		</tr>

		</tbody>
			<tfoot>
			<tr>
				<td colspan="4"></td>
				<td colspan="1">
					<button class="add-row btn btn-small btn-success btn-submit"  type="button" data-network-id="{$id}"><span class="icon icon-white icon-plus"></span></button>
				</td>
			</tr>
			</tfoot>
		</table>
		{/if}
	{/foreach}
	<table class='serverdeftable'>
		<thead><tr><th>Network Interface</th><th>Default Firewall Rule</th></tr></thead>
		<tbody>
		{foreach from=$fw_rules->network_interface_obj item=nic}
			{if $nic->default_firewall_rule eq 'ACCEPT'}
				{assign var=accept_select value='selected'}
				{assign var=drop_select value=''}
			{else}
				{assign var=accept_select value=''}
				{assign var=drop_select value='selected'}
			{/if}
			<tr>
				<td>{$nic->label}</td><td>
					<select name='default_rule_{$nic->id}' data-network-id='{$nic->id}'>
					<option {$accept_select} value='accept'>ACCEPT</option>
					<option {$drop_select} value='drop'>DROP</option>
					</select>
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<div align='center'><button id="save-changes" type='submit' class="fw_save btn btn-primary" >Save Firewall changes</button><br><span><small>Firewall changes may take up to 5 minutes to become visible.</small></span></div>
	<div id='save_dialog' title='Save Firewall changes?'>
		<p>Are you sure you want to make these changes to the firewall? Changes may take up to 5 minutes to become visible.</p>
	</div>

	<input type="hidden" name="deletedRules" value="" id="deletedRules"/>
	<input type="hidden" name="params" value="" id="form_params"/>
	</form>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
	<script type="text/javascript" src="modules/servers/oneportalcloud/includes/js/clientArea.js"></script>



{/if}