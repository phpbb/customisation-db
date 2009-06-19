function ratingHover(cnt, name)
{
	for (var i = 1; i <= max_rating; i++)
	{
		star = document.getElementById(name + '_' + i);

		if (i <= cnt)
		{
			star.src = red_star.src;
		}
		else
		{
			star.src = grey_star.src;
		}
	}
}

function ratingUnHover(cnt, name)
{
	for (var i = 1; i <= max_rating; i++)
	{
		star = document.getElementById(name + '_' + i);

		if (i <= Math.round(cnt))
		{
			star.src = orange_star.src;
		}
		else
		{
			star.src = grey_star.src;
		}
	}
}

function ratingDown(id, name)
{
	for (var i = 1; i <= max_rating; i++)
	{
		star = document.getElementById(name + '_' + i);

		if (i <= cnt)
		{
			star.src = green_star.src;
		}
		else
		{
			star.src = grey_star.src;
		}
	}
}