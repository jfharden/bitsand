<?php

$router->addRoutes(array(
	array('GET', 'download.html', 'common/download'),
	array('GET|POST', 'login.html', 'user/login'),
	array('GET|POST', 'forgotten.html', 'user/forgotten'),
	array('GET|POST', 'register.html', 'user/register'),
	array('GET', 'terms.html', 'common/terms'),
	array('GET', 'booking_feed.rss', 'feed/booking-rss'),
	array('GET|POST', 'events/[a:event]', 'event/event')
));