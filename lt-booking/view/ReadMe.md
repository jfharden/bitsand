**booking/view/ Folder**

The `booking/view/` folder contains the theme files used within Bitsand.  It is split simply into the `default` and `custom` folders.

You should never modify any files within `default`, instead duplicate the file you wish to edit in the `custom` folder, maintaining the same folder and file names.  Bitsand will then use this in preference to the theme file located within the `default` folder.

Stylesheets belong in the `styles` folder and any scripts belong in the `scripts` folder.  The other folders relate to specific views and contain .phtml files - PHP enabled HTML

If you do decide to customise the view, then you will need to ensure that you replicate any changes made to .phtml files within the `default` folder.

It is possible to develop a custom theme in another folder, this will be covered in a WIKI article at some point in the future.