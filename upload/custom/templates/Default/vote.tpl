{include file='header.tpl'}
{include file='navbar.tpl'}

<div class="container">
	<div class="row">
    
    {if count($WIDGETS_LEFT)}
	  <div class="col-md-3">
		{foreach from=$WIDGETS_LEFT item=widget}
		  {$widget}
		  <br />
		{/foreach}
	  </div>
	{/if}
    
    <div class="col-md-{if count($WIDGETS_LEFT) && count($WIDGETS_RIGHT)}6{elseif count($WIDGETS_RIGHT) || count($WIDGETS_LEFT)}9{else}12{/if}">
      <div class="card card-default">
        <div class="card-body">
            {if isset($MESSAGE_ENABLED)}
            <div class="alert alert-info"><center>{$MESSAGE}</center></div>
            {/if}
            
            <div class="row">
                {foreach from=$SITES item=site}
                  <div class="col-6">
                    <a class="btn btn-lg btn-block btn-primary" href="{$site.url}" target="_blank" role="button">{$site.name}</a>
                    </br>
                  </div>
                {/foreach}
            </div>
        </div>
      </div>
    </div>

    {if count($WIDGETS_RIGHT)}
      <div class="col-md-3">
		{foreach from=$WIDGETS_RIGHT item=widget}
		  {$widget}
		  <br />
		{/foreach}
      </div>
	{/if}

    </div>
</div>

{include file='footer.tpl'}