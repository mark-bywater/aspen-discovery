{strip}
<div id="listEntry{$listEntryId}" class="resultsList listEntry" data-order="{$resultIndex}" data-list_entry_id="{$listEntryId}">
	<div class="row">
		{if $listEditAllowed}
			<div class="selectTitle col-xs-12 col-sm-1">
				<input type="checkbox" name="selected[{$listEntryId}]" class="titleSelect" id="selected{$listEntryId}">
			</div>
		{/if}
		{if $showCovers}
			<div class="col-xs-3 col-sm-3 col-md-3 col-lg-2 text-center">
				<a href="/Person/{$summId}">
					{if $summPicture}
					<img src="/files/thumbnail/{$summPicture}" class="alignleft listResultImage" alt="{translate text='Picture' inAttribute=true}"/><br />
					{else}
					<img src="/interface/themes/responsive/images/person.png" class="alignleft listResultImage" alt="{translate text='No Cover Image' inAttribute=true}"/><br />
					{/if}
				</a>
			</div>
		{/if}


		<div class="{if !$showCovers}col-xs-9 col-sm-9 col-md-9 col-lg-10{elseif $listEditAllowed}col-xs-6 col-sm-6 col-md-6 col-lg-7{else}col-xs-6 col-sm-6 col-md-6 col-lg-8{/if}">
		<div class="row">
				<div class="col-xs-12">
					<span class="result-index">{$resultIndex})</span>&nbsp;

					<a href="/Person/{$summId}" class="result-title notranslate">
						{if !$summTitle}{translate text='Title not available'}{else}{$summTitle|removeTrailingPunctuation|truncate:180:"..."|highlight}{/if}
					</a>
					{if isset($summScore)}
						&nbsp;(<a href="#" onclick="return AspenDiscovery.showElementInPopup('Score Explanation', '#scoreExplanationValue{$summId|escape}');">{$summScore}</a>)
					{/if}
				</div>
			</div>

			<div class="resultDetails col-md-9">
				{if $birthDate}
					<div class="row">
						<div class='result-label col-md-3'>{translate text="Born"} </div>
						<div class="col-md-9 result-value">{$birthDate}</div>
					</div>
				{/if}
				{if $deathDate}
					<div class="row">
						<div class='result-label col-md-3'>{translate text="Died"} </div>
						<div class="col-md-9 result-value">{$deathDate}</div>
					</div>
				{/if}
				{if $numObits}
					<div class="row">
						<div class='result-label col-md-3'>{translate text="Num. Obits"} </div>
						<div class="col-md-9 result-value">{$numObits}</div>
					</div>
				{/if}
				{if $dateAdded}
					<div class="row">
						<div class='result-label col-md-3'>{translate text="Added"} </div>
						<div class="col-md-9 result-value">{$dateAdded|date_format}</div>
					</div>
				{/if}
				{if $lastUpdate}
					<div class="row">
						<div class='result-label col-md-3'>{translate text="Last Updated"} </div>
						<div class="col-md-9 result-value">{$lastUpdate|date_format}</div>
					</div>
				{/if}
			</div>

			{if empty($viewingCombinedResults)}
				<div class="row">
					<div class="col-xs-12">
						{include file='Genealogy/result-tools-horizontal.tpl' recordUrl=$summUrl showMoreInfo=true}
					</div>
				</div>
			{/if}
		</div>

		<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">
			{if $listEditAllowed}
				<div class="btn-group-vertical" role="group">
					{if $userSort && $resultIndex != '1'}<span class="btn btn-xs btn-default" onclick="return AspenDiscovery.Lists.changeWeight('{$listEntryId}', 'up');" title="{translate text="Move Up"}">&#x25B2;</span>{/if}
					<a href="/MyAccount/Edit?listEntryId={$listEntryId|escape:"url"}{if !is_null($listSelected)}&amp;listId={$listSelected|escape:"url"}{/if}" class="btn btn-default">{translate text='Edit'}</a>
					{* Use a different delete URL if we're removing from a specific list or the overall favorites: *}
					<a href="/MyAccount/MyList/{$listSelected|escape:"url"}?delete={$listEntryId|escape:"url"}" onclick="return confirm('Are you sure you want to delete this?');" class="btn btn-danger">{translate text='Delete'}</a>
					{if $userSort && ($resultIndex != $listEntryCount)}<span class="btn btn-xs btn-default" onclick="return AspenDiscovery.Lists.changeWeight('{$listEntryId}', 'down');" title="{translate text="Move Down"}">&#x25BC;</span>{/if}
				</div>

			{/if}
		</div>
	</div>
</div>
{/strip}