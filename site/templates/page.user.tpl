<main>
	<header class="block block-shadow">
		<img class="avatar block-shadow{if noavatar} no-avatar-pad inline-svg{/if}" src="{avatar_path}" alt="Аватар">
		<div class="info">
			<p>
				<span class="username">{login}</span>
				{if show_logout}
					<a class="tooltip logout-link" data-tooltip="Выход">
						<img class="inline-svg" src="{gl_images_path}logout_icon.svg">
					</a>
				{/if}
			</p>
			{if show_name}<p>{name}</p>{/if}
			{if show_email}<p>{email}</p>{/if}
		</div>
	</header>
	<section class="subscriptions block block-shadow" id="channels-list-container">
		<h2>Подписки <span id="subs-count" class="medium tooltip information" data-tooltip="Количество подписок" data-tt-pos="right">(0)</span></h2>
		<hr>
	</section>
</main>