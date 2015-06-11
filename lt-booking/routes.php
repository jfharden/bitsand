<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File lt-booking/routes.php
 ||    Summary: This holds all of the routes for the lt-booking front-end of
 ||             Bitsand.
 ||
 ||     Author: Pete Allison
 ||  Copyright: (C) 2006 - 2015 The Bitsand Project
 ||             (http://github.com/PeteAUK/bitsand)
 ||
 || Bitsand is free software; you can redistribute it and/or modify it under the
 || terms of the GNU General Public License as published by the Free Software
 || Foundation, either version 3 of the License, or (at your option) any later
 || version.
 ||
 || Bitsand is distributed in the hope that it will be useful, but WITHOUT ANY
 || WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 || FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 || details.
 ||
 || You should have received a copy of the GNU General Public License along with
 || Bitsand.  If not, see <http://www.gnu.org/licenses/>.
 ++--------------------------------------------------------------------------*/


$router->addRoutes(array(
	array('GET', 'download.html', 'common/download'),
	array('GET', 'login.html', 'user/login'),
	array('POST', 'login.html', 'user/login/login'),
	array('GET|POST', 'logout.html', 'user/login/logout'),
	array('GET', 'forgotten.html', 'user/forgotten'),
	array('POST', 'forgotten.html', 'user/forgotten/send-link'),
	array('GET|POST', 'reset-password.html?token=[a:token]', 'user/reset/forgotten'),
	array('GET|POST', 'details/reset-password.html', 'user/reset'),
	array('GET|POST', 'register.html', 'user/register'),
	array('GET|POST', 'details/mail-settings.html', 'user/mailing'),
	array('GET|POST', 'details/change-email.html', 'user/change-mail'),
	array('GET', 'change-email.html?token=[a:token]', 'user/change-mail/change'),
	array('GET', 'account.html', 'user/account'),
	array('GET', 'terms.html', 'common/terms'),
	array('GET', 'booking_feed.rss', 'feed/booking-rss'),
	array('GET', 'events/', 'event/list'),
	array('GET|POST', 'events/[a:event]', 'event/event'),
	array('GET', 'icalendar/', 'feed/icalendar'),

	array('GET|POST', 'details/ooc.html', 'user/details-personal'),

	array('GET|POST', 'details/ic.html', 'user/details-character'),
));