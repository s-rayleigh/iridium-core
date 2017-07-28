/**
 * Создает объект DOM канала.
 * @param {object} channelData Данные канала.
 * @returns {Element} Объект DOM канала.
 */
function createChannelObject(channelData)
{
	var plank           = document.createElement('div'),
		imgLink         = document.createElement('a'),
		logoImg         = document.createElement('img'),
		infoDiv         = document.createElement('div'),
		nameContainer   = document.createElement('div'),
		nameLink        = document.createElement('a'),
		descriptionSpan = document.createElement('span');

	var channelLink = 'index.php?page=channel&id=' + channelData.id;

	plank.className           = 'plank';
	nameContainer.className   = 'name-container';
	nameLink.className        = 'name';
	descriptionSpan.className = 'description';

	imgLink.href  = channelLink;
	nameLink.href = channelLink;

	logoImg.src = channelData.logo_path;
	imgLink.appendChild(logoImg);

	nameLink.appendChild(document.createTextNode(channelData.name));
	descriptionSpan.appendChild(document.createTextNode(channelData.description));

	nameContainer.appendChild(nameLink);

	infoDiv.appendChild(nameContainer);
	infoDiv.appendChild(descriptionSpan);

	plank.appendChild(imgLink);
	plank.appendChild(infoDiv);

	if(channelData.subscribed != null)
	{
		var subscribeButton = createSubscribeButton(channelData.id, Boolean(Number(channelData.subscribed)));
		addClass(subscribeButton, 'btn-subscribe');
		nameContainer.appendChild(subscribeButton);
	}

	return plank;
}