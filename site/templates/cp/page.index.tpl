<main class="parts-container">
	<h1>Главная страница</h1>
	<div class="blocks-container">
		{foreach info_block in info_blocks}
			<section class="info-block">
				<h2>{info_block['title']}</h2>
				<dl>
					{foreach element in info_block['data']}
						<dt>{element[0]}</dt>
						<dd>{element[1]}</dd>
					{/foreach}
				</dl>
			</section>
		{/foreach}
	</div>
</main>