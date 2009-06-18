function ratingHover(id, name)
{
	for (var i = 1; i <= max_rating; i++)
	{
		star = document.getElementById(name + '_' + i);

		if (i <= id)
		{
			star.src = red_star.src;
		}
		else
		{
			star.src = grey_star.src;
		}
	}
}

function ratingUnHover(id, name)
{
	for (var i = 1; i <= max_rating; i++)
	{
		star = document.getElementById(name + '_' + i);

		if (i <= id)
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

		if (i <= id)
		{
			star.src = green_star.src;
		}
		else
		{
			star.src = grey_star.src;
		}
	}
}