<main>
	<section class="block episode">
		<video class="video-js vjs-16-9 vjs-default-skin" poster="{preview_path}" data-setup="" preload="auto" controls>
			{foreach video in videos}
				<source src="{video['path']}" type="video/{video['extension']}">
			{/foreach}
		</video>
	</section>

	<div class="episode-info">
		<section class="block block-shadow info">
			<div class="name-container">
				<div class="name">
					{name}
				</div>
				<div class="views tooltip" data-tooltip="Количество просмотров" data-tt-pos="bottom" data-tt-pos-margin="3">
					<img src="{gl_images_path}/eye.svg" class="inline-svg">
					<span id="views-count">{views}</span>
				</div>
				<div id="likes-container" class="likes">
					<span id="likes-count">{likes}</span>
				</div>
				<div id="subscribe-container" class="subscribe-container"></div>
			</div>
			<a href="index.php?page=channel&id={channel_id}" class="tooltip" data-tooltip="Канал">{channel_name}</a>{if is_season} / <a href="index.php?page=season&id={season_id}" class="tooltip" data-tooltip="Сезон">{season_name}</a>{/if}
			<hr>
			{description}
			<br>
			Опубликован <time datetime="{machine_time}">{time}</time>
		</section>

		<section class="block block-shadow comments">
			{if logged}
			<form id="comment-form" method="POST" action="index.php?op=comment">
				<input type="hidden" name="id" value="{episode_id}">
				<input type="hidden" name="type" value="{comment_type}">
				<img src="{avatar_path}" class="avatar{if noavatar} no-avatar-pad{/if}">
				<div class="right">
					<span class="username">{username}</span>
					<textarea name="content" id="comment-content" placeholder="Введите текст комментария" required></textarea>
					<button type="submit">
						<img src="{gl_images_path}/comment.svg">
						Комментировать
					</button>
				</div>
			</form>
			{/if}
			<div id="comments-container"></div>
			<button class="load-more" id="load-more-comments">Загрузить еще</button>
		</section>
	</div>

	<section class="block block-shadow episodes" id="episodes-container"></section>
</main>