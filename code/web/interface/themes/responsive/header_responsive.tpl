{strip}

	{* In mobile view this is the top div and spans across the screen *}
	{* Logo Div *}
    {if ($showDisplayNameInHeader && !empty($librarySystemName)) || !empty($headerText)}
		<div class="col-tn-12 col-xs-8 col-sm-8 col-md-3 col-lg-3" id="header-logo-container">
			<a href="{$logoLink}/">
				<img src="{if $responsiveLogo}{$responsiveLogo}{else}{img filename="logo_responsive.png"}{/if}" alt="{$librarySystemName}" title="{$logoAlt}" id="header-logo" {if $showDisplayNameInHeader && $librarySystemName}class="pull-left"{/if}>
			</a>
		</div>
		{* Heading Info Div *}
		<div id="headingInfo" class="hidden-xs hidden-sm col-md-5 col-lg-5">
			{if $showDisplayNameInHeader && $librarySystemName}
				<span id="library-name-header" class="hidden-xs visible-sm">
					{if strlen($librarySystemName) < 30}<br/>{/if} {* Move the library system name down a little if it won't wrap *}
					{$librarySystemName}
				</span>
			{/if}

			{if !empty($headerText)}
				<div id="headerTextDiv">{*An id of headerText would clash with the input textarea on the Admin Page*}
					{$headerText}
				</div>
			{/if}
		</div>
	{else}
		{* Show the logo full width *}
		<div class="col-tn-12 col-xs-8 col-sm-8 col-md-8 col-lg-8" id="header-logo-container">
			<a href="{$logoLink}/">
				<img src="{if $responsiveLogo}{$responsiveLogo}{else}{img filename="logo_responsive.png"}{/if}" alt="{$librarySystemName}" title="{$logoAlt}" id="header-logo" {if $showDisplayNameInHeader && $librarySystemName}class="pull-left"{/if}>
			</a>
		</div>
	{/if}
	{if count($validLanguages) > 1 && count($validLanguages) <= 2}
		<div id="language-selection-header" class="hidden-tn col-xs-4 col-sm-4 col-md-4 col-lg-4 pull-right">
			<div class="btn-group" role="group">
			{foreach from=$validLanguages key=languageCode item=language}
				<div class="availableLanguage btn btn-xs btn-default {if $userLang->code==$languageCode}active{/if}">
				{if $userLang->code!=$languageCode}
				<a onclick="return AspenDiscovery.setLanguage('{$languageCode}')">
				{/if}
					<div>
						{$language->displayName}
					</div>
				{if $userLang->code!=$languageCode}
				</a>
				{/if}
				</div>
			{/foreach}
			</div>
			{if $loggedIn && in_array('Translate Aspen', $userPermissions)}
				<div id="translationMode">
					{if $translationModeActive}
						<a onclick="return AspenDiscovery.changeTranslationMode(false)">{translate text="Exit Translation Mode"}</a>
					{else}
						<a onclick="return AspenDiscovery.changeTranslationMode(true)">{translate text="Start Translation Mode"}</a>
					{/if}
				</div>
			{/if}
		</div>
	{/if}
	{if count($validLanguages) >= 3}
		<div id="language-selection-header" class="hidden-tn col-xs-4 col-sm-4 col-md-4 col-lg-4 pull-right">
	<div class="dropdown">
		<button class="btn btn-default dropdown-toggle" type="button" id="language-selection-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
			{translate text="Translate"}&nbsp;<span class="caret"></span>
		</button>
		<ul id="select-language" class="dropdown-menu" aria-labelledby="language-selection-dropdown">
			{foreach from=$validLanguages key=languageCode item=language}
			<li><a onclick="return AspenDiscovery.setLanguage('{$languageCode}')">{$language->displayName}</li>
			{/foreach}
		</ul>
	</div>
		{if $loggedIn && in_array('Translate Aspen', $userPermissions)}
			<div id="translationMode">
				{if $translationModeActive}
					<a onclick="return AspenDiscovery.changeTranslationMode(false)" class="btn btn-primary btn-xs active">{translate text="Exit Translation Mode"}</a>
				{else}
					<a onclick="return AspenDiscovery.changeTranslationMode(true)" class="btn btn-primary btn-xs">{translate text="Start Translation Mode"}</a>
				{/if}
			</div>
		{/if}
		</div>
	{/if}
{/strip}