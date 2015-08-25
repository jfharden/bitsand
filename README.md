# bitsand

Bitsand is an online booking system for live role play events originally written by Russell Phillips for the Lions Lorien Trust faction.  Since it's creation it has been modified so that it can be used for any faction or guild.

Registration information can be pulled between different Bitsand installations if the person booking uses the same e-mail and password on all installations.

Bugs, feature requests, questions and any other items can be done by creating an Issue at [Bitsand Github page](https://github.com/PeteAUK/bitsand/issues).

It is recommended that you ["Watch" the Github repository](https://github.com/PeteAUK/bitsand/subscription) so that you will be kept up to date with new releases and updates.  Alternativly if you're not a Github user, sign up to our [Mailing List](http://www.freelists.org/list/bitsand) to receive notifications of new releases.

Please note, the new location of the Systems file (for registration import) is: https://cdn.rawgit.com/PeteAUK/bitsand/NON_WEB/systems and not the old Googlecode one.

Bitsand is known to be in use by:

* [Lions](http://bookings.lionsfaction.co.uk/)
* [Harts](http://albion.leynexus.net/booking/)
* [Jackals](http://www.jackalfaction.com/booking/)
* [Bears](http://kaitain.vm.bytemark.co.uk/bears/)
* [Vipers](http://www.viperfaction.co.uk/booking/)
* [Dragons](http://events.dragonsfaction.org)

# Version 9

Bitsand version 9 was a complete rewrite of the source code from the ground up, implementing a MVC based system to allow better management of the code and easier modification of the client-side views.  The new source code was written to utilise the Bitsand version 8.x database with no modifications, allowing roll back to version 8.x should the user encounter an issue or prefer the older layout.  Additionally the MVC system in use allows Bitsand to be exapanded to cover other systems or bespoke modifications of the Lorien Trust booking system without breaking the core code.  Bitsand version 9 does however raise the minimum requirement to PHP 5.5 and greater.

The redesigned client front-end and admin back-end views are responsive HTML 5 designs and should work on the majority of devices, which hopefully will make life easier.  At some point I intend to create an offline tool that event organisers can use to grant Marshals and Refs the ability to see character stats and player information, although this is very much a future milestone!

A full list of changes can be found within the NON_WEB/ChangeLog_8-2_to_9-0.txt file

# Note on PHP versions

We have tested Bitsand on PHP 5.5 and PHP 5.6 and found it works without any problems.  Some testing was undertaken in PHP 5.4 and Bitsand worked for 90% of the actions, lacking only in a handful of CURL operations.  The following table displays the [published support dates](http://php.net/supported-versions.php) which should be adhered to.

| Branch | Active Support Until | Security Support Until |
|--------|----------------------|------------------------|
| 5.4    | 14 September 2014    | 14 September 2015      |
| 5.5    | 10 July 2015         | 10 July 2016           |
| 5.6    | 28 August 2016       | 28 August 2017         |
