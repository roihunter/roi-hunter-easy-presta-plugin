{*
 * ROI Hunter - configuration
 *
 * LICENSE: The buyer can free use/edit/modify this software in anyway
 * The buyer is NOT allowed to redistribute this module in anyway or resell it
 * or redistribute it to third party
 *
 * @author    ROI Hunter Easy
 * @copyright 2019 ROI Hunter
 * @license   EULA
 * @version   1.0
 * @link      https://easy.roihunter.com/
*}

{if $shopContent.multishop == true && $shopContent.context != 'shop'}
	<div class="panel">
		<h3>{l s='Multishop detected. Please switch to a specific shop!' mod='roihunter'}</h3>
	</div>
{else}
	<div class="panel">
		<h3><i class="icon icon-credit-card"></i> {l s='ROI Hunter Easy' mod='roihunter'}</h3>

		<br />
		<p>
			{l s='Create powerful ads for Google and Facebook with ROI Hunter Easy plugin for PrestaShop. Convert past visitors with retargeting and bring new traffic with search ads.' mod='roihunter'}
		</p>
		<table class="table">
			<tr><td>rhStateApiBaseUrl</td><td>{$base}</td></tr>
			<tr><td>state endpoint</td><td>{$base}state.php</td></tr>
			<tr><td>check   endpoint</td><td>{$base}check.php</td></tr>
			<tr><td>products endpoint</td><td>{$base}products.php</td></tr>
			<tr><td>active profile</td><td>{$activeBeProfile}</td></tr>
			{foreach from=$storageItems key=storageKey item=storageValue}
				<tr><td>{$storageKey}</td><td>{$storageValue}</td></tr>
			{/foreach}
		</table>
	</div>
{/if}
