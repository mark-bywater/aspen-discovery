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

			</div>
		{/if}
		<div class="{if !$showCovers}col-xs-9 col-sm-9 col-md-9 col-lg-10{else}col-xs-6 col-sm-6 col-md-6 col-lg-7{/if}">
			<div class="row">
				<div class="col-xs-12">
					<span class="result-index">{$resultIndex})</span>&nbsp;
					{if !empty($deletedEntryTitle)}
						<span class="result-title">{$deletedEntryTitle|ucwords}</span>
						<div>{translate text="This entry no longer exists in the catalog"}</div>
					{else}
						<span class="result-title">{translate text="This entry no longer exists in the catalog"}</span>
					{/if}
				</div>
			</div>
		</div>

		<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">
			{if $listEditAllowed}
				<div class="btn-group-vertical" role="group">
					<a href="/MyAccount/Edit?listEntryId={$listEntryId|escape:"url"}{if !is_null($listSelected)}&amp;listId={$listSelected|escape:"url"}{/if}" class="btn btn-default">{translate text='Edit'}</a>
					{* Use a different delete URL if we're removing from a specific list or the overall favorites: *}
					<a href="/MyAccount/MyList/{$listSelected|escape:"url"}?delete={$listEntryId|escape:"url"}" onclick="return confirm('Are you sure you want to delete this?');" class="btn btn-default">{translate text='Delete'}</a>
				</div>

			{/if}
		</div>
	</div>
</div>
{/strip}