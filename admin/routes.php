<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File admin/routes.php
 ||    Summary: This holds all of the routes for the admin part of Bitsand
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
	array('GET|POST', 'edit_event.html?event=[i:event_id]', 'events/event/edit'),
	array('GET|POST', 'create_event.html', 'events/event/create'),
	array('GET', 'view_event.html?event=[i:event_id]', 'events/view'),

	array('GET', 'manage_payments.html?event=[i:event_id]', 'events/payment/outstanding'),
	array('GET', 'manage_queue.html?event=[i:event_id]', 'events/booking/queue'),
	array('GET', 'add_booking.html?event=[i:event_id]', 'events/booking/add'),
	array('GET', 'booking_status.html?event=[i:event_id]', 'events/booking/status'),
	array('GET', 'manage_bunks.html?event=[i:event_id]', 'events/booking/bunks'),
	array('GET', 'manage_meals.html?event[i:event_id]', 'events/booking/meals'),
	array('GET', 'manage_marshals.html?event[i:event_id]', 'events/booking/marshal'),

	array('GET', 'report_cards.html?event[i:event_id]', 'events/report/cards'),
	array('GET', 'report_bookings.html?event[i:event_id]&output=csv', 'events/report/bookings'),
	array('GET', 'report_purchases.html?event[i:event_id]&output=csv', 'events/report/purchases'),
	array('GET', 'report_signin.html?event[i:event_id]&output=pdf', 'events/report/signin'),
	array('GET', 'report_medical.html?event[i:event_id]&output=pdf', 'events/report/medical'),
	array('GET', 'report_diet.html?event[i:event_id]&output=pdf', 'events/report/diet'),

	array('GET', 'delete_event.html?event[i:event_id]', 'events/booking/delete'),
	array('GET', 'final_confirmation.html?event[i:event_id]', 'events/final_confirmation')
));