var commentsListSelection,
	commentsList     = document.getElementById('comments-list'),
	commentsDataList = new DataList({
		opUrl: 'index.php?op=eslist.commentsList',
		usePageNavigation: true,
		loadParameters: {type: commentsObjectType, id: commentsObjectId},
		onLoad: function(result)
		{
			document.querySelector(".page-control > span").innerHTML = (result.page + 1) + '/' + result.pages;

			while(commentsList.children.length)
			{
				commentsList.removeChild(commentsList.children[0]);
			}

			if(!result.count)
			{ return; }

			result.list.forEach(function(commentData)
			{
				commentsList.appendChild(createCommentPlank(commentData));
			});

			commentsListSelection.updateSelection();
		}
	});

window.addEventListener('load', function()
{
	commentsListSelection = new ListSelection({
		parent: commentsList,
		elementsClass: 'plank',
		selectByClick: true
	});

	commentsDataList.load();
	commentsListSelection.updateSelection();

	document.getElementById('prev-page').addEventListener('click', function()
	{
		commentsDataList.prevPage();
		commentsDataList.load();
	});

	document.getElementById('next-page').addEventListener('click', function()
	{
		commentsDataList.nextPage();
		commentsDataList.load();
	});

	document.getElementById('delete-selected').addEventListener('click', function()
	{
		(new Popup({
			title: 'Удаление комментариев',
			content: 'Вы действительно хотите удалить выбранные комментарии?',
			overlay: true,
			closeCross: true,
			buttons: [{
				text: 'Да', action: function()
				{
					var ids = [];

					commentsListSelection.selected.forEach(function(comm)
					{
						ids.push(comm.dataset.id);
					});

					sendRequest('POST', 'index.php?op=cp.deleteComment', {ids: ids}, function()
					{
						(new Notification({
							content: 'Выбранные комментарии успешно удалены.',
							styleClass: 'success',
							showTime: 3000
						})).show();

						commentsDataList.load();
					});
				}
			}, {text: 'Нет'}]
		})).show();
	});
});

function createCommentPlank(commentData)
{
	var plank          = document.createElement('section'),
		loginContainer = document.createElement('div'),
		userLoginSpan  = document.createElement('span'),
		avatar         = document.createElement('img'),
		contentSpan    = document.createElement('span'),
		time           = document.createElement('time'),
		buttonsPanel   = document.createElement('div'),
		deleteButton   = document.createElement('button'),
		editButton     = document.createElement('button'),
		deleteImg      = document.createElement('img'),
		editImg        = document.createElement('img');

	plank.className          = 'plank';
	loginContainer.className = 'login-container';
	avatar.className         = 'avatar';
	contentSpan.className    = 'description';
	buttonsPanel.className   = 'buttons-panel';
	editButton.className     = 'icon-button';
	deleteButton.className   = 'icon-button';

	userLoginSpan.appendChild(document.createTextNode(commentData.username));

	if(commentData.avatar_path)
	{
		avatar.src = commentData.avatar_path;
	}
	else
	{
		avatar.src = glImagesPath + 'user_cleaned.svg';
		avatar.className += ' no-avatar-pad';
	}

	loginContainer.appendChild(avatar);
	loginContainer.appendChild(userLoginSpan);

	contentSpan.appendChild(document.createTextNode(commentData.content));

	time.appendChild(document.createTextNode(commentData.human_time));
	time.setAttribute('datetime', commentData.machine_time);

	deleteImg.src = glImagesPath + 'delete_cleaned.svg';
	editImg.src   = glImagesPath + 'edit_cleaned.svg';

	deleteButton.appendChild(deleteImg);
	editButton.appendChild(editImg);

	buttonsPanel.appendChild(deleteButton);
	buttonsPanel.appendChild(editButton);

	plank.appendChild(loginContainer);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(contentSpan);
	plank.appendChild(time);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(buttonsPanel);

	createTooltip(deleteButton, 'Удалить комментарий', 'bottom', 20);
	createTooltip(editButton, 'Редактировать комментарий', 'bottom', 20);

	//Кнопка удаления
	deleteButton.addEventListener('click', function(event)
	{
		event.preventDefault();
		event.stopPropagation();

		(new Popup({
			title: 'Удаление комментария',
			content: 'Вы действительно хотите удалить выбранный комментарий?',
			overlay: true,
			closwCross: 'true',
			buttons: [{
				text: 'Да', action: function()
				{
					sendRequest('POST', 'index.php?op=cp.deleteComment', {id: commentData.id}, function(result)
					{
						if(result !== 0)
						{ return; }

						(new Notification({
							content: 'Комментарий успешно удален.',
							styleClass: 'success'
						})).show();

						commentsDataList.load();
					});
				}
			}, {text: 'Нет'}]
		})).show();
	});

	//Кнопка редактирования
	//TODO: реализовать редактирование комментария
	editButton.addEventListener('click', function(event)
	{
		event.preventDefault();
		event.stopPropagation();

		(new Popup({
			title: 'Редактирование комментария',
			content: 'Редактирование комментария временно недоступно.',
			overlay: true,
			buttons: [{text: 'Понятно'}]
		})).show();
	});

	plank.dataset.id = commentData.id;

	return plank;
}
