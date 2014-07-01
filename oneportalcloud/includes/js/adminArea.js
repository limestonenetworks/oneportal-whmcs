var rows = 0;

$(document).ready(function(){
    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };
    $('.add-row').on('click',addRow);
    $('.serverfwtable tbody').sortable({ helper: fixHelper });
    $('.rule_remove').on('click',removeRule);

    $("#save_dialog").dialog({
        autoOpen: false,
        resizable: false,
        width: 450,
        modal: true,
        buttons: {'Yes': function(e) {
            saveRules(e);
            $(this).dialog('close');
            location.reload();
        },'No': function() {
            $(this).dialog('close');
        }}
    });
    $('.fw_save').on('click',function(e){
        e.preventDefault();
        $("#save_dialog").dialog('open');
    });
});
function addRow(){
    jQuery("#sry").remove();
    var id = $(this).data('network-id');
    $('#fw_rules_'+id+' > tbody:last').append('<tr class="fw_rule" data-rule-position="new_'+rows+'" data-value="new_'+rows+'" id="rule_new_'+rows+'">'+
    '<td><input  type="text" name="ip"></td>'+
        '<td><select name="cmd">'+
            '<option value="accept">ACCEPT</option>'+
            '<option value="drop">DROP</option>'+
            '</select></td>'+
        '<td><input  type="text" name="port"></td>'+
            '<td><select  name="protocol">'+
                '<option value="tcp">TCP</option>'+
                '<option value="udp">UDP</option>'+
                '</select></td>' +
            '<td><button class="btn btn-small btn-danger rule-remove" id="new_'+rows+'" type="button" data-network-id="'+id+'"><span class="icon icon-trash">Remove</span></button></td>'+
            '</tr>');
        $("#new_"+rows).on('click',removeRule);
        rows = parseInt(rows) + 1;
        }

        function removeRule(e){
            var rule = "rule_" + e.target.id;
            var network = $(this).data('network-id');
            var len = $('#fw_rules_'+network+' tbody').children('tr').length;
            if($('#deletedRules').val().length == 0){
            if(e.target.id.indexOf("new") == -1 )
            $('#deletedRules').val(network+':'+e.target.id);
            }
        else{
            if(e.target.id.indexOf("new") == -1 )
            $('#deletedRules').val($('#deletedRules').val() + "," + network+':'+e.target.id);
            }
        $("#"+rule).remove();
        if(len == 1){
            $('#fw_rules_'+network+' > tbody:last').append('<tr class="" id="sry">'+
                '<td colspan="6">You have no additional rules configured.</td>'+
                '</tr>');
            }
        }
        function saveRules(e) {
            e.preventDefault();

            var new_rules = new Object();
            var deleted_rules = "";
            var rule_order = new Object();
            $(".serverfwtable").each(function(i) {
            var id = this.id;
            new_rules[id] = new Object();
            var ruleorder="";
            $('#'+id+ ' tbody tr').each(function(e){
            if($(this).attr('id').match('new_')){
            var rule_ar = new Object();
            $('#'+this.id + " :input").each(function() {
            rule_ar[arguments[1].name] = arguments[1].value;
            });
        new_rules[id][$(this).data('value')] = rule_ar;
        }
        if (ruleorder==''){
            ruleorder = $(this).data('value')+":"+$(this).data('rule-position');
            }
        else{
            ruleorder += "," + $(this).data('value')+":"+$(this).data('rule-position');
            }
        });
        rule_order[id] = ruleorder;
        });
        var default_settings = new Object();
        $(".serverdeftable tbody tr td select").each(function(i){
            default_settings[$(this).data('network-id')] = this.value;
            });
        if($('#deletedRules').val().length > 0){
            deleted_rules = $('#deletedRules').val();
            }
        var params = { deleted_rules: deleted_rules, defaults: default_settings, new_rules: new_rules, order: rule_order}
        runModuleCommandBetter('custom','firewallsave',JSON.stringify(params));
        }
        function runModuleCommandBetter(cmd,custom,params){
            var token = $('input[name=token]').first().attr('value');
            var reqstr = "userid="+userid+"&id="+serviceid+"&modop="+cmd+"&token="+token;
            if (custom) reqstr += "&ac="+custom;
            if (params) reqstr += "&params="+params;
            else if (cmd=="suspend") reqstr += "&suspreason="+encodeURIComponent($("#suspreason").val())+"&suspemail="+$("#suspemail").is(":checked");
            $.post("clientsservices.php", reqstr,
            function(data){
            if (data.substr(0,9)=="redirect|") {
            window.location = data.substr(9);
            } else {
            $("#servicecontent").html(data);
            }
        },'json');

        }