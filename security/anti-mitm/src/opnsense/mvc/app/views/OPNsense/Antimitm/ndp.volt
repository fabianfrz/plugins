{#

    Copyright (C) 2017 Fabian Franz
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

    1. Redistributions of source code must retain the above copyright notice,
       this list of conditions and the following disclaimer.

    2. Redistributions in binary form must reproduce the above copyright
       notice, this list of conditions and the following disclaimer in the
       documentation and/or other materials provided with the distribution.

    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.


#}
<script type="text/javascript">

function ndp_update_status() {
    ajaxCall(url="/api/antimitm/service/ndpstatus", sendData={}, callback=function(data,status) {
        updateServiceStatusUI(data['status']);
    });
}

function reload_handler() {
    $(".reloadAct_progress").addClass("fa-spin");
    ajaxCall(url="/api/antimitm/service/reconfigure", sendData={}, callback=function(data,status) {
        ndp_update_status();
        $(".reloadAct_progress").removeClass("fa-spin");
    });
}

$( document ).ready(function() {
    var data_get_map = {
        'general': '/api/antimitm/ndp/get'
    };
    mapDataToFormUI(data_get_map).done(function(data){
        formatTokenizersUI();
        $('select').selectpicker('refresh');
    });

    // link save button to API set action
    [
      {'selector': '#generalsaveAct', 'endpoint': '/api/tor/general/set', 'formid': 'general'},
      {'selector': '#relaysaveAct', 'endpoint': '/api/tor/relay/set', 'formid': 'relay'}
    ].forEach(function (cfg) {
        $(cfg.selector).click(function(){
            saveFormToEndpoint(url=cfg.endpoint, formid=cfg.formid,callback_ok=function(){
                $(cfg.selector + " .saveAct_progress").addClass("fa fa-spinner fa-pulse");
                ajaxCall(url="/api/tor/service/reconfigure", sendData={}, callback=function(data,status) {
                    ndp_update_status();
                    $(cfg.selector + " .saveAct_progress").removeClass("fa fa-spinner fa-pulse");
                });
            });
        });
    });

    ndp_update_status();

    /* allow a user to manually reload the service (for forms which do not do it automatically) */
    $('.reload_btn').click(reload_handler);

    $("#grid-router").UIBootgrid(
        { 'search':'/api/antimitm/ndp/searchrouter',
          'get':'/api/antimitm/ndp/getrouter/',
          'set':'/api/antimitm/ndp/setrouter/',
          'add':'/api/antimitm/ndp/addrouter/',
          'del':'/api/antimitm/ndp/delrouter/',
          'toggle':'/api/antimitm/ndp/togglerouter/',
          'options':{selection:false, multiSelect:false}
        }
    );
    
    $("#grid-prefixes").UIBootgrid(
        { 'search':'/api/antimitm/ndp/searchprefix',
          'get':'/api/antimitm/ndp/getprefix/',
          'set':'/api/antimitm/ndp/setsetprefix/',
          'add':'/api/antimitm/ndp/addprefix/',
          'del':'/api/antimitm/ndp/delprefix/',
          'toggle':'/api/antimitm/ndp/toggleprefix/',
          'options':{selection:false, multiSelect:false}
        }
    );
});

</script>

<ul class="nav nav-tabs" data-tabs="tabs" id="maintabs">
    <li class="active"><a data-toggle="tab" href="#general">{{ lang._('General') }}</a></li>
    <li><a data-toggle="tab" href="#router">{{ lang._('Router') }}</a></li>
    <li><a data-toggle="tab" href="#prefixes">{{ lang._('Prefixes') }}</a></li>
</ul>

<div class="tab-content content-box tab-content" style="padding-bottom: 1.5em;">
    <div id="general" class="tab-pane fade in active">
        {{ partial("layout_partials/base_form",['fields': general,'id':'general'])}}
        <div class="col-md-12">
            <hr />
            <button class="btn btn-primary" id="generalsaveAct" type="button"><b>{{ lang._('Save') }}</b> <i class="saveAct_progress"></i></button>
        </div>
    </div>

    <div id="router" class="tab-pane fade in">
        <table id="grid-router" class="table table-responsive" data-editDialog="editrouter">
          <thead>
              <tr>
                  <th data-column-id="enabled" data-type="string" data-formatter="rowtoggle">{{ lang._('Enabled') }}</th>
                  <th data-column-id="name" data-type="string" data-visible="true">{{ lang._('Name') }}</th>
                  <th data-column-id="uuid" data-type="string" data-identifier="true" data-visible="false">{{ lang._('ID') }}</th>
                  <th data-column-id="commands" data-formatter="commands" data-sortable="false">{{ lang._('Commands') }}</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
          <tfoot>
              <tr>
                  <td colspan="3"></td>
                  <td>
                      <button data-action="add" type="button" class="btn btn-xs btn-default"><span class="fa fa-plus"></span></button>
                      <!-- <button data-action="deleteSelected" type="button" class="btn btn-xs btn-default"><span class="fa fa-trash-o"></span></button> -->
                      <button type="button" class="btn btn-xs reload_btn btn-primary"><span class="fa fa-refresh reloadAct_progress"></span> {{ lang._('Reload Service') }}</button>
                  </td>
              </tr>
          </tfoot>
      </table>
    </div>

    <div id="prefixes" class="tab-pane fade in">
        <table id="grid-prefixes" class="table table-responsive" data-editDialog="editprefix">
          <thead>
              <tr>
                  <th data-column-id="enabled" data-type="string" data-formatter="rowtoggle">{{ lang._('Enabled') }}</th>
                  <th data-column-id="address" data-type="string" data-visible="true">{{ lang._('Network Address') }}</th>
                  <th data-column-id="mask" data-type="string" data-visible="true">{{ lang._('Network Mask') }}</th>
                  <th data-column-id="uuid" data-type="string" data-identifier="true" data-visible="false">{{ lang._('ID') }}</th>
                  <th data-column-id="commands" data-formatter="commands" data-sortable="false">{{ lang._('Commands') }}</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
          <tfoot>
              <tr>
                  <td colspan="4"></td>
                  <td>
                      <button data-action="add" type="button" class="btn btn-xs btn-default"><span class="fa fa-plus"></span></button>
                      <!-- <button data-action="deleteSelected" type="button" class="btn btn-xs btn-default"><span class="fa fa-trash-o"></span></button> -->
                      <button type="button" class="btn btn-xs reload_btn btn-primary"><span class="fa fa-refresh reloadAct_progress"></span> {{ lang._('Reload Service') }}</button>
                  </td>
              </tr>
          </tfoot>
      </table>
    </div>
</div>

{{ partial("layout_partials/base_dialog",['fields': router,'id':'editrouter', 'label':lang._('Edit Router')])}}
{{ partial("layout_partials/base_dialog",['fields': prefix,'id':'editprefix', 'label':lang._('Edit Prefix')])}}
