/**
 * Логика страницы просмотра эпизода
 * @module episode
 */

var loaded = false,
	heartIcon,		//Иконка лайка
	cancelIcon;		//Иконка отмены

window.addEventListener('load', function()
{
	//Загрузка иконки отмены
	SVG.loadFromURL(glImagesPath + '/x_icon_cleaned.svg', function(result)
	{
		cancelIcon = result;

		if(loaded)
		{
			updateLikes(liked);
			return;
		}

		loaded = true;
	});

	//Загрузка иконки лайка
	SVG.loadFromURL(glImagesPath + '/heart.svg', function(result)
	{
		heartIcon = result;

		if(loaded)
		{
			updateLikes(liked);
			return;
		}

		loaded = true;
	});

	var commentForm        = document.getElementById('comment-form'),
		commentsContainer  = document.getElementById('comments-container'),
		loadCommentsButton = document.getElementById('load-more-comments'),
		commentsDataList   = new DataList({
			opUrl: 'index.php?op=eslist.commentsList',
			loadParameters: {type: commentType, id: episodeId},
			onLoad: function(data)
			{
				if(!data.count) { return; }

				//Следующие комментарии будут старше (по времени) последнего загруженного
				commentsDataList.updateLoadParameters({ time: data.list[data.list.length - 1].time });

				data.list.forEach(function(comment)
				{
					commentsContainer.appendChild(createCommentObject(comment));
				});
			}
		}),
		episodesContainer = document.getElementById('episodes-container'),
		episodesDataList = new DataList({
			opUrl: 'index.php?op=eslist.episodesList',
			loadParameters: {chanid: channelId, exclid: episodeId},
			onLoad: function(data)
			{
				if(!data.count)
				{
					episodesContainer.style.display = 'none';
					return;
				}
				else
				{
					episodesContainer.style.display = '';
				}

				data.list.forEach(function(episodeData)
				{
					episodesContainer.appendChild(createEpisodeObject(episodeData));
				});
			}
		});

	commentsDataList.load();
	episodesDataList.load();

	loadCommentsButton.addEventListener('click', function()
	{
		commentsDataList.load();
	});

	if(commentForm)
	{
		//Добавление комментария
		addFormValidation(commentForm, function(result)
		{
			if(!result.human_time)
			{ return; }

			var commentField = document.getElementById('comment-content'),
				commentData  = [];

			commentData.content  = commentField.value;
			commentData.username = username;
			commentData.user_id  = user_id;

			if(this.avatarPath)
			{
				commentData.avatar_path = avatarPath;
			}

			updateObject(commentData, result);

			commentsContainer.insertBefore(createCommentObject(commentData), commentsContainer.firstChild);

			commentField.value = '';

			(new Notification({
				content: 'Вы прокомментировали этот эпизод.'
			})).show();
		});
	}


});

/**
 * Обновляет или создает кнопку "Понравилось".
 * @param {boolean} like
 * @param {object} button
 */
function updateLikes(like, button)
{
	var buttonContainer = document.getElementById('likes-container'),
		insButton       = !button,
		likesCount      = document.getElementById('likes-count');
	button              = button || document.createElement('button');

	function cleanButton(button)
	{
		while(button.hasChildNodes())
		{
			button.removeChild(button.firstChild);
		}
	}

	function updateLikesCount(plus)
	{
		likesCount.firstChild.nodeValue = parseInt(likesCount.firstChild.nodeValue) + plus;
	}

	if(like)
	{
		button.appendChild(heartIcon);
		button.appendChild(cancelIcon);
		button.className = 'cancel';
		button.addEventListener('click', function unlike()
		{
			sendRequest('POST', 'index.php?op=like', {unlike: 1, id: episodeId}, function(result)
			{
				if(result !== 0)
				{ return; }

				updateLikesCount(-1);
				cleanButton(button);
				button.removeEventListener('click', unlike);
				updateLikes(false, button);
			});
		});
	}
	else
	{
		button.appendChild(heartIcon);
		button.className = '';
		button.addEventListener('click', function like()
		{
			sendRequest('POST', 'index.php?op=like', {id: episodeId}, function(result)
			{
				if(result !== 0)
				{ return; }

				updateLikesCount(+1);
				cleanButton(button);
				button.removeEventListener('click', like);
				updateLikes(true, button);
			});
		});
	}

	if(insButton)
	{ buttonContainer.insertBefore(button, buttonContainer.firstChild); }
}

function createCommentObject(commentData)
{
	var comment            = document.createElement('div'),
		userLink           = document.createElement('a'),
		avatarImg          = document.createElement('img'),
		usernameSpan       = document.createElement('span'),
		commentContentSpan = document.createElement('span'),
		time               = document.createElement('time');

	comment.className            = 'comment';
	userLink.className           = 'user'
	avatarImg.className          = 'avatar';
	usernameSpan.className       = 'username';
	commentContentSpan.className = 'content';

	if(commentData.avatar_path)
	{
		avatarImg.src = commentData.avatar_path;
	}
	else
	{
		avatarImg.src = glImagesPath + 'user_cleaned.svg';
		addClass(avatarImg, 'no-avatar-pad');
	}

	usernameSpan.appendChild(document.createTextNode(commentData.username));

	userLink.href = 'index.php?page=user&id=' + commentData.user_id;

	time.setAttribute('datetime', commentData.machine_time);
	time.appendChild(document.createTextNode(commentData.human_time));

	commentContentSpan.appendChild(document.createTextNode(commentData.content));
	commentContentSpan.appendChild(document.createElement('br'));
	commentContentSpan.appendChild(time);

	userLink.appendChild(avatarImg);
	userLink.appendChild(usernameSpan);

	comment.appendChild(userLink);
	comment.appendChild(commentContentSpan);

	return comment;
}