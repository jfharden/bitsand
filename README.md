# bitsand

Bitsand is an online booking system for live role play events originally written by Russell Phillips for the Lions Lorien Trust faction.  Since it's creation it has been modified so that it can be used for any faction or guild.

Registration information can be pulled between different Bitsand installations if the person booking uses the same e-mail and password on all installations.

Bugs, feature requests, questions and any other items can be done by creating an Issue at [Bitsand Github page](https://github.com/PeteAUK/bitsand/issues).

It is recommended that you ["Watch" the Github repository](https://github.com/PeteAUK/bitsand/subscription) so that you will be kept up to date with new releases and updates.  Alternativly if you're not a Github user, sign up to our [Mailing List](http://www.freelists.org/list/bitsand) to receive notifications of new releases.

Please note, the new location of the Systems file (for registration import) is: https://cdn.rawgit.com/PeteAUK/bitsand/NON_WEB/systems and not the old Googlecode one.

Bitsand is known to be in use by:

* [Lions](http://bookings.lionsfaction.co.uk/)
* [Harts](https://harts.sanctioned-events.com/)
* [Jackals](http://www.jackalfaction.com/booking/)
* [Bears](http://kaitain.vm.bytemark.co.uk/bears/)
* [Vipers](http://www.viperfaction.co.uk/booking/)
* [Dragons](http://events.dragonsfaction.org)
* [Incantors](https://incantors.sanctioned-events.com/)
* [Healers](https://www.hartsofalbion.co.uk/healers-booking/)
* [Alchemists](https://www.hartsofalbion.co.uk/alchemists-booking/)
* [Wardens](https://www.hartsofalbion.co.uk/wardens-booking/)
* [Reality Checkpoint](https://www.hartsofalbion.co.uk/reality-booking/)
* [World Plot](https://world-plot.sanctioned-events.com/export.php)

## Installation Instructions

### For new installations

1. Create a database user and database (in mysql, this is done outside of bitsand)
2. Copy inc/inc\_config\_dist.php to inc/inc\_config.php and fill in config details (leave root user as NULL for now)
3. Copy inc/admin\_dist.css to inc/admin.css
4. Copy inc/body\_dist.css to inc/body.css
5. Copy inc/help\_dist.css to inc/help.css
6. Copy terms\_dist.php to terms.php and update them!
7. Load /install in a browser
    1. Click on **Create tables** and follow instructions
    2. Click on **Initial configuration** and follow instructions
    3. Click on **Configuration file & database test** and make sure all things are ok, resolve any issues before continuing
8. Delete /install and /NON\_WEB directories
9. Register a user and make him root
    1. Register a user
    2. Log in as that user and look at the footer of the page to find that users player id (it says *Logged in with Player ID X*)
    3. Edit inc/inc\_config.php and set the player id of the root user to the one you just register (look for *define ('ROOT_USER_ID', NULL);*)
