{strip}
<div>
	<div id="createSpotlightComments">
		<p class="alert alert-info">
			{if count($existingCollectionSpotlights) > 0}
				You may either add this {$source} to an existing collection spotlight as a new tab, <br> or you may create a new spotlight to display this {$source} in.
			{else}
			{translate text="Please enter a name for the spotlight to be created."}
			{/if}
		</p>
	</div>
	<form method="post" name="addSpotlight" id="addSpotlight" action="/Admin/CreateCollectionSpotlight" class="form-horizontal">
		<input type="hidden" name="source" value="{$source}">
		<input type="hidden" name="id" value="{$id}">
		{if count($existingCollectionSpotlights) > 0}
			<div class="form-group">
				<label for="collectionSpotlightId" class="col-sm-4">{translate text="Select a collection spotlight"}</label>
				<div class="col-sm-8">
					<select id="collectionSpotlightId" name="collectionSpotlightId" class="form-control" onchange="return AspenDiscovery.CollectionSpotlights.updateSpotlightFields();">
						<option value="-1">{translate text="Create a new spotlight"}</option>
						{foreach from=$existingCollectionSpotlights item=spotlightName key=spotlightId}
							<option value="{$spotlightId}">{$spotlightName}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group" id="replaceExistingRadios" style="display: none">
			<label for="replaceExisting" class="col-sm-4">{translate text="Replace Existing Spotlight / Tab?"}</label>
			<div class="col-sm-8">
				<div class="checkbox">
					<input type="checkbox" name="replaceExisting" id="replaceExisting" value="replaceExisting" onchange="return AspenDiscovery.CollectionSpotlights.updateSpotlightFields();"> {translate text="Yes, replace existing spotlight with current search"}
				</div>
			</div>
			</div>
		{if count($existingCollectionSpotlightLists) > 1}
			<div class="form-group" id="existingSpotlightName" style="display: none">
				<label for="spotlightName" class="col-sm-4">{translate text="Select a Spotlight / Tab to Replace"}</label>
				<div class="col-sm-8">
					<select id="collectionSpotlightListId" name="collectionSpotlightListId" class="form-control">
						<option value="-1.0" disabled>Select a Spotlight</option>
						{foreach from=$existingCollectionSpotlightLists item="spotlight" key="id"}
							<option value="{$spotlight->collectionSpotlightId}.{$spotlight->id}">{$spotlight->name}</option>
						{/foreach}
					</select>
				</div>
			</div>
		{/if}
		{/if}
		<div class="form-group" id="newSpotlightName">
			<label for="spotlightName" class="col-sm-4">{translate text="New Spotlight Name / New Tab Name"}</label>
			<div class="col-sm-8">
				<input type="text" id="spotlightName" name="spotlightName" value="" class="form-control required">
			</div>
		</div>
	</form>
	<script type="text/javascript">
		$(function(){ldelim}
			$("#addSpotlight").validate();
		{rdelim});
	</script>
</div>
{/strip}