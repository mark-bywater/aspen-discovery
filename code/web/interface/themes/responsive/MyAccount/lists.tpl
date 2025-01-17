{strip}
	<h1>{translate text="My Lists"}</h1>
	{if empty($lists)}
		<div class="alert alert-info">
			{translate text="You have not created any lists yet."}
		</div>
	{else}
		<div class="row">
				<select id="results-sort" name="sort" aria-label="{translate text='Sort'}" onchange="document.location.href = this.options[this.selectedIndex].value;" class="input-medium">
					<option value="?sort=title"{if $sortedBy == "title"} selected="selected"{/if}>{translate text='Sort by'} {translate text='Title'}</option>
					<option value="?sort=created"{if $sortedBy == "created"} selected="selected"{/if}>{translate text='Sort by'} {translate text='Most Recently Created'}</option>
					<option value="?sort=dateUpdated"{if $sortedBy == "dateUpdated"} selected="selected"{/if}>{translate text='Sort by'} {translate text='Most Recently Updated'}</option>
				</select>

			<div id="selected-browse-label">
				<div class="btn-group" id="hideSearchCoversSwitch"{if $displayMode != 'list'} style="display: none;"{/if}>
					<label for="hideCovers" class="checkbox{* control-label*}"> {translate text='Hide Covers'}
						<input id="hideCovers" type="checkbox" onclick="AspenDiscovery.Account.toggleShowCovers(!$(this).is(':checked'))" {if $showCovers == false}checked="checked"{/if}>
					</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<div class="btn-toolbar">
					<button class="btn btn-sm btn-default" onclick="return AspenDiscovery.Account.showCreateListForm('{$source|escape:"url"}', '{$sourceId|escape:"url"}')">{translate text="Create a New List"}</button>
					{if count($lists) > 0}
						<button onclick="return AspenDiscovery.Account.deleteSelectedLists({$listSelected})" class="btn btn-sm btn-danger">{translate text="Delete Selected Lists"}</button>
					{/if}
				</div>
			</div>
		</div>

		{foreach from=$lists item="list" key="resultIndex"}
			<div class="row">

					<div class="selectList col-xs-12 col-sm-1">
						<input type="checkbox" name="selected[{$list->id}]" class="listSelect" id="selected{$list->id}">
					</div>

				{if $showCovers == true}
				<div class="coversColumn col-xs-3 col-sm-3 col-md-3 col-lg-2 text-center">
						<a href="/MyAccount/MyList/{$list->id}" class="alignleft listResultImage" aria-hidden="true">
							<img src="/bookcover.php?type=list&amp;id={$list->id}&amp;size=medium" class="listResultImage img-thumbnail" alt="{translate text='Cover Image' inAttribute=true}">
						</a>
				</div>
				{/if}

				<div class="{if !$showCovers}col-xs-11{else}col-xs-8 col-sm-8 col-md-8 col-lg-9{/if}">{* May turn out to be more than one situation to consider here *}
					{* Title Row *}

					<div class="row">
						<div class="col-xs-12">
							<span class="result-index">{$resultIndex+1})</span>&nbsp;
							<a href="/MyAccount/MyList/{$list->id}" class="result-title notranslate">
								{$list->title}
							</a>
						</div>
					</div>

					<div class="row">
						<div class="result-label col-tn-3">{translate text="Number of Titles"} </div>
						<div class="result-value col-tn-9 notranslate">
							{translate text="%1% titles are in this list." 1=$list->numValidListItems()}
						</div>
					</div>

					{* Description Section *}
					{if $list->description}
						<div class="row visible-xs">
							<div class="result-label col-tn-3 col-xs-3">{translate text="Description"}</div>
							<div class="result-value col-tn-9 col-xs-9"><a id="descriptionLink{$list->id|escape}" href="#" onclick="$('#descriptionValue{$list->id|escape},#descriptionLink{$list->id|escape}').toggleClass('hidden-xs');return false;">Click to view</a></div>
						</div>

						<div class="row">
							{* Hide in mobile view *}
							<div class="result-value hidden-xs col-sm-12" id="descriptionValue{$list->id|escape}">
								{$list->description|truncate_html:450:"..."}
							</div>
						</div>
					{/if}
					<div class="row">

						<div class="col-xs-12">
							<p class="text-muted"><small>{translate text='Created on'} {$list->created|date_format:"%B %e, %Y %l:%M %p"}<br>
									{translate text='Last Updated'} {$list->dateUpdated|date_format:"%B %e, %Y %l:%M %p"}</small></p>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12"><span class="badge">{if $list->public == '0'}{translate text="Private"}{else}{translate text="Public"}{/if}</span> {if $list->searchable == '1'}<span class="badge">{translate text="Searchable"}</span>{/if}</div>
					</div>

				</div>
			</div>
		{/foreach}

		{if $pageLinks.all}<div class="pagination">{$pageLinks.all}</div>{/if}
	{/if}
{/strip}