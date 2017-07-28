var logout_button = document.getElementById('logout');

logout_button.addEventListener('click', function()
{
	sendRequest('POST', 'index.php?op=logout', '', function(resp)
	{
		if(resp === 0)
		{
			(new Popup({
				content: 'После нажатия на кнопку "Ок" вы будете перенаправлены на главную страницу.',
				title: 'Выход выполнен успешно!',
				overlay: true,
				closeCross: true,
				buttons: [{text: 'Ок'}],
				onHide: function()
				{
					window.location = "index.php";
				}
			})).show();
		}
	});
});