{strip}
	<h2 class="hiddenTitle" id="mobileNav">{translate text="Navigation"}</h2>
	<div class="menu-section menu-section-left">
		{if $useHomeLink == '1' || $useHomeLink == '3'}
			<a href="{$homeLink}" id="homeLink" class="menu-icon menu-bar-option" title="{translate text="Return to $homeLinkText" inAttribute=true}" aria-label="{translate text="Return to $homeLinkText" inAttribute=true}">
				<i class="fas fa-home fa-lg"></i><span class="menu-bar-label hidden-inline-block-xs"></span>
			</a>
		{/if}
		<a href="{if $useHomeLink == '0' || $useHomeLink == '2'}/{else}/Search/Home{/if}" id="homeLink" class="menu-icon menu-bar-option" title="{translate text='Browse the Catalog' inAttribute=true}" aria-label="{translate text='Browse the Catalog' inAttribute=true}">
			<i class="fas {if ($useHomeLink == '1' || $useHomeLink == '3') || ($showBookIcon == '1' && ($useHomeLink == '0' || $useHomeLink == '2'))}fa-book-open{else}fa-home{/if} fa-lg"></i>{if $useHomeLink == '1' || $useHomeLink == '3'}<span class="menu-bar-label visible-inline-block-lg">{$browseLinkText}</span>{else}{/if}
		</a>
		{foreach from=$libraryLinks key=categoryName item=menuCategory}
			{assign var=topCategory value=$menuCategory|@reset}
			{if $topCategory->showInTopMenu || $topCategory->alwaysShowIconInTopMenu}
				{if count($menuCategory) > 1}
					<a id="{$topCategory->getEscapedCategory()}-menu-trigger" onclick="return AspenDiscovery.showCustomMenu('{$topCategory->getEscapedCategory()}')" class="menu-icon menu-bar-option {if !$topCategory->alwaysShowIconInTopMenu}visible-inline-block-lg{/if}" title="{translate text=$categoryName inAttribute=true}" aria-label="{translate text=$categoryName inAttribute=true}">
						{if !empty($topCategory->iconName)}
							<i class="fas fa-{$topCategory->iconName} fa-lg"></i>
						{/if}
						<span class="menu-bar-label visible-inline-block-lg">
							{if $topCategory->published == 0}<em>{/if}
							{$topCategory->category|translate}
							{if $topCategory->published == 0}</em>{/if}
						</span>
					</a>
					<div id="{$topCategory->getEscapedCategory()}-menu" class="dropdownMenu" style="display: none">
						{foreach from=$menuCategory item=link key=linkName}
							{* Only render HTML contents in the header menu *}
							{if empty($link->htmlContents)}
								<div class="header-menu-option childMenuItem">
									<a href="{$link->url}" {if $link->openInNewTab}target="_blank"{/if}>
										{if $link->published == 0}<em>{/if}
										{$linkName|translate}
										{if $link->published == 0}</em>{/if}
									</a>
								</div>
							{/if}
						{/foreach}
					</div>
				{else}
					<a href="{$topCategory->url}" class="menu-icon menu-bar-option {if !$topCategory->alwaysShowIconInTopMenu}visible-inline-block-lg{/if}" title="{translate text=$categoryName inAttribute=true}" aria-label="{translate text=$categoryName inAttribute=true}" {if $topCategory->openInNewTab}target="_blank"{/if}>
						{if !empty($topCategory->iconName)}
							<i class="fas fa-{$topCategory->iconName} fa-lg"></i>
						{/if}
						<span class="menu-bar-label visible-inline-block-lg">
							{if $topCategory->published == 0}<em>{/if}
							{$topCategory->category|translate}
							{if $topCategory->published == 0}</em>{/if}
						</span>
					</a>
				{/if}
			{/if}
		{/foreach}
	</div>
	<div class="menu-section menu-section-right">
		{if $loggedIn}{* Logged In *}
			<a id="accountMenuToggleButton" href="/MyAccount/Home" onclick="return AspenDiscovery.toggleAccountMenu();" id="mobile-menu-account-icon" class="menu-icon menu-bar-option" title="Account">
				{if $masqueradeMode}
					<i class="fas fa-theater-masks fa-lg"></i>
				{else}
					<i class="fas fa-user fa-lg"></i>
				{/if}
				<span class="menu-bar-label hidden-inline-block-xs">
					{if $masqueradeMode}
						{translate text="Acting As %1%" 1=$userDisplayName}
					{else}
						{$userDisplayName}
					{/if}
				</span>
			</a>
			{include file="account-menu.tpl"}
		{else} {* Not Logged In *}
			{if $showLoginButton}
			<a href="/MyAccount/Home" id="loginLink" onclick="{if $isLoginPage}$('#username').focus();return false;{else}return AspenDiscovery.Account.followLinkIfLoggedIn(this);{/if}" data-login="true" class="menu-icon menu-bar-option" title="{translate text='Login' inAttribute=true}">
				<i id="loginLinkIcon" class="fas fa-sign-in-alt fa-lg"></i><span class="menu-bar-label hidden-inline-block-xs" id="login-button-label">{translate text="Sign in"}</span>
			</a>
			{/if}
		{/if}

		<a id="menuToggleButton" onclick="return AspenDiscovery.toggleMenu();" class="menu-icon menu-bar-option" title="{translate text="Show Menu" inAttribute=true}">
			<i class="fas fa-bars fa-lg"></i>
		</a>
		{include file="header-menu.tpl"}
	</div>
{/strip}