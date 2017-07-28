
/**
 * Создает объект DOM для отображения информации о эпизоде.
 * @param {object} ep Данные эпизода.
 * @param {boolean} displayNumber Отображать номер эпизода.
 * @returns {Element} Объект эпизода.
 */
function createEpisodeObject(ep, displayNumber)
{
	var plank        = document.createElement('div'),
		epImgLink    = document.createElement('a'),
		epImg        = document.createElement('img'),
		nameP        = document.createElement('p'),
		nameLink     = document.createElement('a'),
		channelP      = document.createElement('p'),
		channelLink   = document.createElement('a'),
		infoDiv      = document.createElement('div'),
		likesSpan    = document.createElement('span'),
		commentsSpan = document.createElement('span'),
		viewsSpan    = document.createElement('span');

	plank.className   = 'episode';
	epImg.className   = 'preview';
	nameP.className   = 'name';
	channelP.className = 'channel';
	infoDiv.className = 'info';

	epImg.src      = ep.preview_path;
	epImgLink.href = 'index.php?page=episode&id=' + ep.id;
	epImgLink.appendChild(epImg);

	nameLink.href = 'index.php?page=episode&id=' + ep.id;
	nameLink.appendChild(document.createTextNode(ep.name));
	nameP.appendChild(nameLink);

	channelLink.href = 'index.php?page=channel&id=' + ep.channel_id;
	channelLink.appendChild(document.createTextNode(ep.channel_name));
	channelP.appendChild(channelLink);

	viewsSpan.appendChild(document.createTextNode(ep.views));
	commentsSpan.appendChild(document.createTextNode(ep.comments));
	likesSpan.appendChild(document.createTextNode(ep.likes));

	plank.appendChild(epImgLink);
	plank.appendChild(nameP);
	plank.appendChild(channelP);
	plank.appendChild(infoDiv);

	if(displayNumber)
	{
		var numSpan = document.createElement('span');
		numSpan.className = 'small exp';
		numSpan.appendChild(document.createTextNode(' ' + ep.number));
		nameP.appendChild(numSpan);
	}

	SVG.loadFromURL(glImagesPath + '/eye.svg', function(svg)
	{
		infoDiv.appendChild(viewsSpan);
		infoDiv.appendChild(svg);
	});

	SVG.loadFromURL(glImagesPath + '/comment.svg', function(svg)
	{
		infoDiv.appendChild(commentsSpan);
		infoDiv.appendChild(svg);
	});

	SVG.loadFromURL(glImagesPath + '/heart.svg', function(svg)
	{
		infoDiv.appendChild(likesSpan);
		infoDiv.appendChild(svg);
	});

	return plank;
}