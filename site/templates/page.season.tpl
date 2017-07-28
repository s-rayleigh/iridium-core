<main>
	<section class="block block-shadow">
		<div class="name-container">
			<span class="name">{name} <span class="small exp tooltip" data-tooltip="Номер сезона" data-tt-pos="right">{number}</span></span>
			<div id="subscribe-container" class="subscribe-container"></div>
		</div>
		<a href="index.php?page=channel&id={channel_id}" class="tooltip" data-tooltip="Канал">{channel_name}</a>
		<hr>
		{description}
	</section>

	<section class="block block-shadow episodes">
		<h2>Эпизоды <span class="medium tooltip information" id="episodes-count" data-tooltip="Количество эпизодов" data-tt-pos="right">(0)</span></h2>
		<hr>
		<div class="episodes-container" id="episodes-list"></div>
	</section>
</main>