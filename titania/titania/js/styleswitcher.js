
function fontsizeup(event)
{
	// Skip tabs; 9 being the ASCII code for a tab
	if (event && getKeyCode(event) == 9)
	{
		return true;
	}

	var active = getActiveStyleSheet();

	switch (active)
	{
		case 'A--':
			setActiveStyleSheet('A-');
		break;

		case 'A-':
			setActiveStyleSheet('A');
		break;

		case 'A':
			setActiveStyleSheet('A+');
		break;

		case 'A+':
			setActiveStyleSheet('A++');
		break;

		case 'A++':
			setActiveStyleSheet('A');
		break;

		default:
			setActiveStyleSheet('A+');
		break;
	}

	return false;
}

function fontsizedown(event)
{
	// Skip tabs
	if (event && getKeyCode(event) == 9)
	{
		return true;
	}

	var active = getActiveStyleSheet();

	switch (active)
	{
		case 'A++' :
			setActiveStyleSheet('A+');
		break;

		case 'A+' :
			setActiveStyleSheet('A');
		break;

		case 'A' :
			setActiveStyleSheet('A-');
		break;

		case 'A-' :
			setActiveStyleSheet('A--');
		break;

		case 'A--' :
		break;

		default :
			setActiveStyleSheet('A--');
		break;
	}

	return false;
}

function getKeyCode(event)
{
	// IE doesn't fire the onkeypress event for tabs
	// Reference: http://www.quirksmode.org/js/keys.html

	var code = (event.keyCode) ? event.keyCode : 0;

	// Probably using FF
	if (!code && event.charCode)
	{
		code = event.charCode;
	}

	return code;
}

function setActiveStyleSheet(title)
{
	var i, a, main;

	for (i = 0; (a = document.getElementsByTagName('link')[i]); i++)
	{
		if (a.getAttribute('rel').indexOf('style') != -1 && a.getAttribute('title'))
		{
			a.disabled = true;
			if (a.getAttribute('title') == title)
			{
				a.disabled = false;
			}
		}
	}
}

function getActiveStyleSheet()
{
	var i, a;

	for (i = 0; (a = document.getElementsByTagName('link')[i]); i++)
	{
		if (a.getAttribute('rel').indexOf('style') != -1 && a.getAttribute('title') && !a.disabled)
		{
			return a.getAttribute('title');
		}
	}

	return null;
}

function getPreferredStyleSheet()
{
	return ('A-');
}

function createCookie(name, value, days)
{
	if (days)
	{
		var date = new Date();
		date.setTime(date.getTime() + (days*24*60*60*1000));
		var expires = '; expires=' + date.toGMTString();
	}
	else
	{
		expires = '';
	}

	document.cookie = name + '=' + value + expires + style_cookie_settings;
}

function readCookie(name)
{
	var nameEQ = name + '=';
	var ca = document.cookie.split(';');

	for (var i = 0; i < ca.length; i++)
	{
		var c = ca[i];

		while (c.charAt(0) == ' ')
		{
			c = c.substring(1, c.length);
		}

		if (c.indexOf(nameEQ) == 0)
		{
			return c.substring(nameEQ.length, c.length);
		}
	}

	return null;
}

function load_cookie()
{
	var cookie = readCookie('style_cookie');
	var title = cookie ? cookie : getPreferredStyleSheet();
	setActiveStyleSheet(title);
}

function unload_cookie()
{
	var title = getActiveStyleSheet();
	createCookie('style_cookie', title, 365);
}

$(document).ready(function() {
	load_cookie();
});

$(document).unload(function() {
	unload_cookie();
});

/*
var cookie = readCookie("style");
var title = cookie ? cookie : getPreferredStyleSheet();
setActiveStyleSheet(title);
*/
